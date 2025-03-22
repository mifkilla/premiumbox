<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]GeoPay[:en_US][ru_RU:]GeoPay[:ru_RU]
description: [en_US:]GeoPay merchant[:en_US][ru_RU:]мерчант GeoPay[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_geopay')){
	class merchant_geopay extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 0);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
			}			
			
			add_filter('merchant_pay_button', array($this,'pay_button'),99,7);
		}

		function get_map(){
			$map = array(
				'PRIVATE_KEY'  => array(
					'title' => '[en_US:]Private Key[:en_US][ru_RU:]Приватный ключ[:ru_RU]',
					'view' => 'textarea',	
				),
				'API_KEY'  => array(
					'title' => '[en_US:]API key[:en_US][ru_RU:]API ключ[:ru_RU]',
					'view' => 'input',	
				),				
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('PRIVATE_KEY','API_KEY');
			return $arrs;
		}

		function options($options, $data, $id, $place){ 
			
			$m_defin = $this->get_file_data($id);
			
			$options = pn_array_unset($options, array('pagenote','check_api'));
			
			$text = '
			<div><strong>Callback URL:</strong> <a href="'. get_mlink($id .'_status' . hash_url($id)) .'" target="_blank">'. get_mlink($id .'_status' . hash_url($id)) .'</a></div>			
			';

			$options[] = array(
				'view' => 'line',
			);			
			$options[] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;	
		}				

		function pay_button($merchant_pay_button, $m_in, $sum_to_pay, $now_bids_data, $direction, $vd1, $vd2){
		global $bids_data;	
			
			$script = get_mscript($m_in);
			if($script and $script == $this->name){
				$m_defin = $this->get_file_data($m_in);
				$m_data = get_merch_data($m_in);
				$show_error = intval(is_isset($m_data, 'show_error'));
			
				$pay_link = trim(get_bids_meta($bids_data->id, 'pay_link'));
				if(!$pay_link){
					
					$pay_sum = is_sum($sum_to_pay, 2); 
					$currency = mb_strtolower($bids_data->currency_code_give);
					$currency = str_replace('uah','grn', $currency);
					$currency = str_replace(array('rur','rub'),'rubg', $currency);
					$currency = str_replace('eur','eurg', $currency);
					
					$text_pay = get_text_pay($m_in, $bids_data, $pay_sum);
				
					try {
						$class = new GeoPay(is_deffin($m_defin, 'API_KEY'), is_deffin($m_defin, 'PRIVATE_KEY'));
						$res = $class->create_invoice($currency, $pay_sum, $text_pay, $bids_data->id);
						if(isset($res['id'], $res['url']) and $res['url']){
							
							$params = array(
								'sum' => 0,
								'm_place' => $m_in,
								'system' => 'user',
								'trans_in' => $res['id'],
								'm_id' => $m_in,
								'm_data' => $m_data,
								'm_defin' => $m_defin,
							);
							set_bid_status('techpay', $bids_data->id, $params, $direction);
							
							$pay_link = pn_strip_input($res['url']);
							update_bids_meta($bids_data->id, 'pay_link', $pay_link);
						} 
					}
					catch (Exception $e)
					{
						$this->logs($e->getMessage());
						if($show_error and current_user_can('administrator')){
							die($e->getMessage());
						}
					}					
				}

				if($pay_link){
					$pay_link = str_replace('http:','https://',$pay_link);
					$merchant_pay_button = '<a href="'. $pay_link .'" target="_blank" class="success_paybutton">'. __('Make a payment','pn') .'</a>';
				} else {
					$merchant_pay_button = '<div class="resultfalse paybutton_error">'. __('Error! Please contact website technical support', 'pn') .'</div>';
				}
			
			}
			return $merchant_pay_button;			
		}
		
		function merchant_status(){
		global $wpdb;	
			
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			$callback = file_get_contents('php://input');
			$post = @json_decode($callback, true);
			
			do_action('merchant_logs', $this->name, $post, $m_id, $m_defin, $m_data);
			
			$partner_transaction_id = intval(is_isset($post,'partner_transaction_id'));
			$status = mb_strtoupper(pn_strip_input(is_isset($post,'status')));
			
			if($status and $status == 'DONE' and $partner_transaction_id){
				
				$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE status IN('new','techpay','coldpay') AND m_in = '$m_id' AND id='$partner_transaction_id'");
				if(isset($item->id)){
					
					$transaction_id = $item->trans_in;
					
					$class = new GeoPay(is_deffin($m_defin, 'API_KEY'), is_deffin($m_defin, 'PRIVATE_KEY'));
					$res = $class->status_invoice($transaction_id);

					if($res['status'] == 1){
						
						$currency = mb_strtoupper($res['currency']);
						$currency = str_replace('GRN','UAH', $currency);
						$currency = str_replace('RUBG','RUB', $currency);
						$currency = str_replace('EURG','EUR', $currency);
						
						$order_id = intval($res['id']);
						$data = get_data_merchant_for_id($order_id, $item);
						$bid_m_id = $data['m_id'];
						$bid_m_script = $data['m_script'];
						$bid_err = $data['err'];
						
						if($bid_err > 0){
							$this->logs($order_id.' The application does not exist or the wrong ID');
							die('The application does not exist or the wrong ID');
						}
						
						if($bid_m_script and $bid_m_script != $this->name or !$bid_m_script){	
							$this->logs($order_id.' wrong script');
							die('wrong script');
						}			
						
						if($bid_m_id and $m_id != $bid_m_id or !$bid_m_id){
							$this->logs($order_id.' not a faithful merchant');
							die('not a faithful merchant');				
						}
						
						if(check_trans_in($m_id, $transaction_id, $order_id)){
							$this->logs($order_id.' Error check trans in!');
							die('Error check trans in!');
						}
						
						$in_sum = $res['amount'];
						$in_sum = is_sum($in_sum,2);
						$bid_status = $data['status'];
						
						$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
						
						$bid_currency = $data['currency'];
						
						$bid_sum = is_sum($data['pay_sum'],2);
						$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
						
						$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
						$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
						$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
						$invalid_check = intval(is_isset($m_data, 'check'));
						 
						if($bid_currency == $currency or $invalid_ctype > 0){
							if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){		
		
								$params = array(
									'sum' => $in_sum,
									'bid_sum' => $bid_sum,
									'bid_status' => array('new','techpay','coldpay'),
									'bid_corr_sum' => $bid_corr_sum,
									'pay_purse' => $pay_purse,
									'currency' => $currency,
									'bid_currency' => $bid_currency,
									'invalid_ctype' => $invalid_ctype,
									'invalid_minsum' => $invalid_minsum,
									'invalid_maxsum' => $invalid_maxsum,
									'invalid_check' => $invalid_check,
									'm_place' => $bid_m_id,
									'm_id' => $m_id,
									'm_data' => $m_data,
									'm_defin' => $m_defin,
								);
								set_bid_status('realpay', $order_id, $params, $data['direction_data']);  
								
								echo 'OK';
								exit;
							} else {
								$this->logs($order_id.' The payment amount is less than the provisions');
								die('The payment amount is less than the provisions');
							}
						} else {
							$this->logs($order_id.' Wrong type of currency');
							die('Wrong type of currency');
						}					
					}
				
				}
			} 
				
			echo 'Error!';
			exit;						
		}				
	}
}
new merchant_geopay(__FILE__, 'GeoPay');