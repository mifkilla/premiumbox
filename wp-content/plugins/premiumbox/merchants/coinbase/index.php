<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Coinbase[:en_US][ru_RU:]Coinbase[:ru_RU]
description: [en_US:]Coinbase merchant[:en_US][ru_RU:]мерчант Coinbase[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_coinbase')){
	class merchant_coinbase extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			add_filter('merchant_pay_button', array($this,'pay_button'),99,5);
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
				add_action('premium_merchant_'. $id .'_fail', array($this,'merchant_fail'));
				add_action('premium_merchant_'. $id .'_success', array($this,'merchant_success'));
			}
		}
		
		function get_map(){
			$map = array(
				'API'  => array(
					'title' => '[en_US:]API key[:en_US][ru_RU:]API key[:ru_RU]',
					'view' => 'input',	
				),
				'API_SECRET'  => array(
					'title' => '[en_US:]Webhook Shared Secret[:en_US][ru_RU:]Webhook Shared Secret[:ru_RU]',
					'view' => 'input',
				),		
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('API','API_SECRET');
			return $arrs;
		}		

		function options($options, $data, $id, $place){  
			
			$options = pn_array_unset($options, 'check_api');
			$options = pn_array_unset($options, 'check');
			$options = pn_array_unset($options, 'pagenote');

			$text = '
			<div><strong>RETURN URL:</strong> <a href="'. get_mlink($id.'_status' . hash_url($id)) . '" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_status' . hash_url($id)) .'</a></div>
			<div><strong>SUCCESS URL:</strong> <a href="'. get_mlink($id.'_success') .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_success') .'</a></div>
			<div><strong>FAIL URL:</strong> <a href="'. get_mlink($id.'_fail') .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_fail') .'</a></div>				
			';

			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;	
		}				

		function merchant_fail(){
			$id = get_payment_id('id');
			redirect_merchant_action($id, $this->name);
		}

		function merchant_success(){
			$id = get_payment_id('id');
			redirect_merchant_action($id, $this->name, 1);
		}		
		
		function pay_button($merchant_pay_button, $m_in, $sum_to_pay, $bids_data, $direction){
			$script = get_mscript($m_in);
			if($script and $script == $this->name){
				$m_defin = $this->get_file_data($m_in);
				
				$pay_sum = is_sum($sum_to_pay, 12); 
				$text_pay = get_text_pay($m_in, $bids_data, $pay_sum);						
					
				$currency = pn_strip_input(str_replace('RUB','RUR',$bids_data->currency_code_give));	
					
				$data = get_merch_data($m_in);
				$show_error = intval(is_isset($data, 'show_error'));
				
				$redirect_url = get_mlink($m_in.'_success');
				$cancel_url = get_mlink($m_in.'_fail');
				
				$pay_link = '';
				
				try {
					$pay_title = sprintf(__('Exchage ID %s','pn'), $bids_data->id);
					$class = new CoinBase( is_deffin($m_defin,'API'), is_deffin($m_defin,'API_SECRET') );
					$pay_link = $class->add_link($pay_title, $text_pay, 'fixed_price', array("amount" => $pay_sum, "currency" => $currency), array('bid_id'=> $bids_data->id), $redirect_url, $cancel_url);
				}
				catch(Exception $e){
					$this->logs($e->getMessage());
					if($show_error and current_user_can('administrator')){
						return $e->getMessage();
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

		function get_webhook_data($data, $webhook_shared_secret){
			
			$server_header = 'HTTP_'.str_replace('-', '_', strtoupper('X-CC-Webhook-Signature'));
	
			if(isset($_SERVER[$server_header])
				AND !empty($_SERVER[$server_header])
				AND is_string($data)
				AND !empty($data)
				AND hash_hmac('sha256', $data, $webhook_shared_secret) === $_SERVER[$server_header]
				AND $data = json_decode($data, true)
				AND json_last_error() === JSON_ERROR_NONE
				AND isset($data['event'], $data['event']['type'])
				AND $data['event']['resource'] === 'event'
			){
				return $data['event'];
			}
	
			return false;	
		}
		
		function merchant_status(){
			
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			$post_data = file_get_contents('php://input');
			$json_data = json_decode($post_data, true);
			
			do_action('merchant_logs', $this->name, $json_data, $m_id, $m_defin, $m_data);
			
			$webhook_shared_secret = trim(is_deffin($m_defin,'API_SECRET'));
			$res = $this->get_webhook_data($post_data, $webhook_shared_secret);
	
			if(!is_array($res)){
				$this->logs('Error data 1!');
				die('Error data 1!');
			}	
				
			$transaction_id = 0;
			
			if(!isset($res['data']['metadata']['bid_id'])){
				$this->logs('Error data 2!');
				die('Error data 2!');
			}			
			
			$order_id = intval($res['data']['metadata']['bid_id']);
			
			$pay_data = is_isset($res['data'], 'payments');
			$pay_info = is_isset($pay_data, 0);
			
			$currency = '';
			$amount = '';
			$pay_status = '';
			
			if(is_array($pay_info)){
				$transaction_id = trim($pay_info['block']['hash']);
				if(isset($pay_info['transaction_id'])){
					$transaction_id = trim($pay_info['transaction_id']);
				}
				$currency = $pay_info['value']['crypto']['currency'];
				$amount = $pay_info['value']['crypto']['amount'];
				$pay_status = $pay_info['status'];
			}
			
			/*
			"NEW" - пользователь перешел к оплате, 
			"PENDING" - транзакция создана, ожидание нужного кол-во подтверждений, 
			"CONFIRMED" - платеж прошел
			*/
			
			$all_pay_status = $res['type'];	

			$status_ins = array('charge:confirmed','charge:pending');
			if(!in_array($all_pay_status,$status_ins)){
				die('Error status!');
			}
			
			$id = $order_id;
			$data = get_data_merchant_for_id($id);
			
			$in_sum = $amount;
			$in_sum = is_sum($in_sum, 12);
			$bid_err = $data['err'];
			$bid_status = $data['status'];
			$bid_m_id = $data['m_id'];
			$bid_m_script = $data['m_script'];
			
			if($bid_err > 0){
				$this->logs($id . ' The application does not exist or the wrong ID');
				die('The application does not exist or the wrong ID');
			}
			
			if($bid_m_script and $bid_m_script != $this->name or !$bid_m_script){	
				$this->logs($id . ' wrong script');
				die('wrong script');
			}			
			
			if($bid_m_id and $m_id != $bid_m_id or !$bid_m_id){
				$this->logs($id . ' not a faithful merchant');
				die('not a faithful merchant');				
			}				
			
			$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
			
			$bid_currency = $data['currency'];
			$bid_currency = str_replace('RUB','RUR',$bid_currency);
			
			$bid_sum = is_sum($data['pay_sum'],2);
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
			
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));
			
			$en_status = array('new','techpay','coldpay');
			if(in_array($bid_status, $en_status)){ 
				if($bid_currency == $currency or $invalid_ctype > 0){
					if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){		

						$now_status = 'coldpay';
								
						if($pay_status == 'CONFIRMED' and $all_pay_status == 'charge:confirmed'){
							$now_status = 'realpay';
						}
						if($now_status){	
							$params = array(
								'sum' => $in_sum,
								'bid_sum' => $bid_sum,
								'bid_status' => array('new','techpay','coldpay'),
								'bid_corr_sum' => $bid_corr_sum,
								'pay_purse' => $pay_purse,
								'trans_in' => $transaction_id,
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
							set_bid_status($now_status, $id, $params, $data['direction_data']); 
						}
								
						die( 'Completed' );
					} else {
						$this->logs($id . ' The payment amount is less than the provisions');
						die('The payment amount is less than the provisions');
					}
				} else {
					$this->logs($id . ' Wrong type of currency');
					die('Wrong type of currency');
				}
			} else {
				$this->logs($id . ' In the application the wrong status');
				die( 'In the application the wrong status' );
			}
		}
	}
}

new merchant_coinbase(__FILE__, 'Coinbase');