<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Adgroup[:en_US][ru_RU:]Adgroup[:ru_RU]
description: [en_US:]Adgroup merchant[:en_US][ru_RU:]мерчант Adgroup[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_adgroup')){
	class merchant_adgroup extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			add_filter('merchant_pay_button', array($this,'pay_button'),99,7);
		}

		function get_map(){
			$map = array(
				'CLIENT_ID'  => array(
					'title' => '[en_US:]Client ID[:en_US][ru_RU:]Client ID[:ru_RU]',
					'view' => 'input',	
				),
				'CLIENT_SECRET'  => array(
					'title' => '[en_US:]Client Secret[:en_US][ru_RU:]Client Secret[:ru_RU]',
					'view' => 'input',
				),			
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('CLIENT_ID','CLIENT_SECRET');
			return $arrs;
		}

		function options($options, $data, $id, $place){ 
			
			$options = pn_array_unset($options, 'personal_secret');
			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'check_api');			
			
			$options['paymethod'] = array(
				'view' => 'select',
				'title' => __('Payment method','pn'),
				'options' => array('0'=> 'Yandex', '1'=> 'Yandex card', '2'=> 'QIWI', '3'=> 'QIWI card'),
				'default' => is_isset($data, 'paymethod'),
				'name' => 'paymethod',
				'work' => 'int',
			);			
			
			$text = '
			<div><strong>Cron:</strong> <a href="'. get_mlink($id.'_cron' . hash_url($id)) .'" target="_blank">'. get_mlink($id.'_cron' . hash_url($id)) .'</a></div>			
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
			
				$paymethod = intval(is_isset($m_data, 'paymethod'));
				
				$pay_link = trim(get_bids_meta($bids_data->id, 'pay_link'));
				if(!$pay_link){
					$pay_sum = is_sum($sum_to_pay, 2); 
					$currency = mb_strtoupper($bids_data->currency_code_give);
				
					try {
						$class = new ADGROUP_API(is_deffin($m_defin,'CLIENT_ID'), is_deffin($m_defin,'CLIENT_SECRET'));
						$res = '';
						if($paymethod == 1){ 
							$res = $class->create_link_yandex($pay_sum, $currency, 'AC', '', $bids_data);
						} elseif($paymethod == 2){	
							$res = $class->create_link_qiwi($pay_sum, $currency, 'qw', $bids_data, $vd1, $vd2);
						} elseif($paymethod == 3){	
							$res = $class->create_link_qiwi($pay_sum, $currency, 'card', $bids_data, $vd1, $vd2);	
						} else { 
							$res = $class->create_link_yandex($pay_sum, $currency, 'PC', '', $bids_data);
						}
						
						if(isset($res['id'], $res['link'])){
							
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
							
							$pay_link = $res['link'];
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
					$merchant_pay_button = '<a href="'. $pay_link .'" target="_blank" class="success_paybutton">'. __('Make a payment','pn') .'</a>';
				} else {
					$merchant_pay_button = '<div class="resultfalse paybutton_error">'. __('Error! Please contact website technical support', 'pn') .'</div>';
				}
			
			}
			return $merchant_pay_button;			
		}

		function cron($m_id, $m_defin, $m_data){
			global $wpdb;
			$show_error = intval(is_isset($m_data, 'show_error'));	
			
			try {
				$class = new ADGROUP_API(is_deffin($m_defin,'CLIENT_ID'), is_deffin($m_defin,'CLIENT_SECRET'));
				$orders = $class->get_history(50);
				
				if(is_array($orders)){
					$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'techpay' AND m_in = '$m_id'");
					foreach($items as $item){
						foreach($orders as $order_key => $res){
							$item_id = $item->id;
							$trans_id = $res['id'];
							$to_account = pn_maxf_mb(pn_strip_input($res['dest_address']),500);
							$trans_in = $item->trans_in;
							if($trans_in and $trans_id and $trans_in == $trans_id){
								$data = get_data_merchant_for_id($item_id, $item);
								
								$currency = $res['sum_currency'];
								
								$in_sum = $res['sum_amount'];
								$in_sum = is_sum($in_sum,2);
								$err = $data['err'];
								$status = $data['status'];
								$bid_m_id = $data['m_id'];
								$bid_m_script = $data['m_script']; 
								
								$bid_currency = $data['currency'];
								
								$pay_purse = is_pay_purse(is_isset($res, 'source_address'), $m_data, $bid_m_id);
									
								$bid_sum = is_sum($data['pay_sum'],2);	
								$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
								
								$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
								$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
								$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
								$invalid_check = intval(is_isset($m_data, 'check'));								
								
								if(!check_trans_in($bid_m_id, $res['id'], $item_id)){
									if($err == 0 and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name){
										if($bid_currency == $currency or $invalid_ctype > 0){
											if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){
												$params = array( 
													'pay_purse' => $pay_purse,
													'sum' => $in_sum,
													'bid_sum' => $bid_sum,
													'bid_status' => array('techpay'),
													'bid_corr_sum' => $bid_corr_sum,
													'to_account' => $to_account,
													'trans_in' => $res['id'],
													'invalid_ctype' => $invalid_ctype,
													'invalid_minsum' => $invalid_minsum,
													'invalid_maxsum' => $invalid_maxsum,
													'invalid_check' => $invalid_check,
													'm_place' => $m_id,
													'm_id' => $m_id,
													'm_data' => $m_data,
													'm_defin' => $m_defin,
												);
												
												set_bid_status('realpay', $item_id, $params, $data['direction_data']); 
												
												unset($orders[$order_key]);
												break;
												
											} else {
												$this->logs($item_id . ' The payment amount is less than the provisions');
											}
										} else {
											$this->logs($item_id . ' Wrong type of currency');
										}		 		 
									} else {
										$this->logs($item_id . ' bid error');
									}
								} else {
									unset($orders[$order_key]);
									$this->logs($item_id . ' Error check trans in!');
								}
							}
						}
					}
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
	}
}

new merchant_adgroup(__FILE__, 'Adgroup');