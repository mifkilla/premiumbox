<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Odysseq[:en_US][ru_RU:]Odysseq[:ru_RU]
description: [en_US:]Odysseq merchant[:en_US][ru_RU:]мерчант Odysseq[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_odysseq')){
	class merchant_odysseq extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			add_filter('merchant_pay_button', array($this,'pay_button'),99,7);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
			}			
		}

		function get_map(){
			$map = array(
				'TOKEN'  => array(
					'title' => '[en_US:]Token[:en_US][ru_RU:]Token[:ru_RU]',
					'view' => 'input',	
				),			
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('TOKEN');
			return $arrs;
		}

		function options($options, $data, $m_id, $place){ 
			
			$options = pn_array_unset($options, array('personal_secret','pagenote','note','check_api','enableip'));

			$options['paymethod'] = array(
				'view' => 'select',
				'title' => __('Payment method','pn'),
				'options' => array('0'=> 'Qiwi', '1'=> 'Qiwi card', '2'=> 'Contact'),
				'default' => is_isset($data, 'paymethod'),
				'name' => 'paymethod',
				'work' => 'int',
			);			
			
			$text = '
			<div><strong>Callback URL:</strong> <a href="'. get_mlink($m_id .'_status' . hash_url($m_id)) .'" target="_blank">'. get_mlink($m_id .'_status' . hash_url($m_id)) .'</a></div>
			<div><strong>Cron:</strong> <a href="'. get_mlink($m_id.'_cron' . hash_url($m_id)) .'" target="_blank">'. get_mlink($m_id.'_cron' . hash_url($m_id)) .'</a></div>		
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
				
					$order_id = $bids_data->id;
					$amount = $pay_sum;
					$card = $bids_data->account_give;
					$info = array(
						'userIp' => pn_real_ip(),
						'userAgent' => pn_maxf(pn_strip_input(is_isset($_SERVER,'HTTP_USER_AGENT')),250),
						'userEmail' => $bids_data->user_email,
						'clientWallet' => $bids_data->account_give,
						'currencyTo' => $vd2->xml_value,
						'recepientWallet' => $bids_data->account_get,
					);
					$successUrl = $failUrl = get_bids_url($bids_data->hashed);
				
					try {
						$class = new Odysseq($this->name, $m_in, is_deffin($m_defin,'TOKEN'));
						$res = '';
						if($paymethod == 1){ //card
							$res = $class->invoice_card($order_id, $pay_sum, $card, $info, $successUrl, $failUrl);
						} elseif($paymethod == 2){	//contact
							$res = $class->invoice_contact($order_id, $pay_sum, $card, $bids_data->first_name, $bids_data->second_name, $bids_data->last_name, $info, $successUrl, $failUrl);	
						} else { 
							$res = $class->invoice_wallet($order_id, $pay_sum, $info, $successUrl, $failUrl);
						}
						
						if(isset($res['paymentInfo'], $res['paymentInfo']['forwardingPayUrl'])){
							
							$params = array(
								'sum' => 0,
								'm_place' => $m_in,
								'system' => 'user',
								'm_id' => $m_in,
								'm_data' => $m_data,
								'm_defin' => $m_defin,
							);
							set_bid_status('techpay', $bids_data->id, $params, $direction); 
							
							$pay_link = pn_strip_input($res['paymentInfo']['forwardingPayUrl']);
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
			
			$paymethod = intval(is_isset($m_data, 'paymethod'));
			$pay_m = 'in.qiwi';
			if($paymethod == 1){
				$pay_m = 'in.card';
			} elseif($paymethod == 2){
				$pay_m = 'in.contact';
			}			
			
			try {
				$class = new Odysseq($this->name, $m_id, is_deffin($m_defin,'TOKEN'));
				
				$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status IN('techpay') AND m_in = '$m_id'");
				foreach($items as $item){
					
					$order_id = $item->id;
					$data = get_data_merchant_for_id($order_id, $item);
					$bid_m_id = $data['m_id'];
					$bid_m_script = $data['m_script'];
					$bid_err = $data['err'];
					
					if($bid_err > 0){
						$this->logs($order_id.' The application does not exist or the wrong ID');
					}
						
					if($bid_m_script and $bid_m_script != $this->name or !$bid_m_script){	
						$this->logs($order_id.' wrong script');
					}			
						
					if($bid_m_id and $m_id != $bid_m_id or !$bid_m_id){
						$this->logs($order_id.' not a faithful merchant');			
					}
					
					$system_data = $class->status($order_id);
						
					if(isset($system_data['paymentInfo'], $system_data['paymentInfo']['type'], $system_data['paymentInfo']['status'], $system_data['paymentInfo']['paymentType']) and $system_data['paymentInfo']['type'] == 'IN' and $system_data['paymentInfo']['paymentType'] == $pay_m){
						/*
						WAITING|SENDING|SUCCESS|CANCELED
						*/
						$pay_status = strtoupper($system_data['paymentInfo']['status']);
						if($pay_status == 'SUCCESS'){
						
							$currency = 'RUB';
							$in_sum = $system_data['paymentInfo']['amount'];
							$in_sum = is_sum($in_sum,2);
							$bid_status = $data['status'];
						
							$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
						
							$bid_currency = $data['currency'];
							$bid_currency = str_replace('RUR','RUB',$bid_currency);
						
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
										'bid_status' => array('techpay'),
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
									
								} else {
									$this->logs($order_id.' The payment amount is less than the provisions');
								}
							} else {
								$this->logs($order_id.' Wrong type of currency');
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
		
		function merchant_status(){
			
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			$callback = file_get_contents('php://input');
			$post = @json_decode($callback, true);
			
			do_action('merchant_logs', $this->name, $post, $m_id, $m_defin, $m_data);
			
			$paymethod = intval(is_isset($m_data, 'paymethod'));
			$pay_m = 'in.qiwi';
			if($paymethod == 1){
				$pay_m = 'in.card';
			} elseif($paymethod == 2){
				$pay_m = 'in.contact';
			}
			
			if(isset($post['orderId'], $post['type'])){
				if($post['type'] == 'IN'){
					
					$class = new Odysseq($this->name, $m_id, is_deffin($m_defin,'TOKEN'));
					$system_data = $class->status($post['orderId']);			
			
					if(isset($system_data['paymentInfo'], $system_data['paymentInfo']['type'], $system_data['paymentInfo']['paymentType']) and $system_data['paymentInfo']['type'] == 'IN' and $system_data['paymentInfo']['paymentType'] == $pay_m){
			
						$order_id = intval($post['orderId']);
						$data = get_data_merchant_for_id($order_id);
						$bid_m_id = $data['m_id'];
						$bid_m_script = $data['m_script'];
						$bid_err = $data['err'];
						
						if($bid_err > 0){
							$this->logs($order_id.' The application does not exist or the wrong ID');
						}
						
						if($bid_m_script and $bid_m_script != $this->name or !$bid_m_script){	
							$this->logs($order_id.' wrong script');
						}			
						
						if($bid_m_id and $m_id != $bid_m_id or !$bid_m_id){
							$this->logs($order_id.' not a faithful merchant');			
						}
						
						$currency = 'RUB';
						$pay_status = strtoupper($system_data['paymentInfo']['status']);
						$in_sum = $system_data['paymentInfo']['amount'];
						$in_sum = is_sum($in_sum,2);
						$bid_status = $data['status'];
						
						$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
						
						$bid_currency = $data['currency'];
						$bid_currency = str_replace('RUR','RUB',$bid_currency);
						
						$bid_sum = is_sum($data['pay_sum'],2);
						$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
						
						$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
						$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
						$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
						$invalid_check = intval(is_isset($m_data, 'check'));
						
						/*
						WAITING|SENDING|SUCCESS|CANCELED
						*/
						$en_status = array('new','techpay');
						if(in_array($bid_status, $en_status)){ 
							if($bid_currency == $currency or $invalid_ctype > 0){
								if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){		
									$now_status = '';
									if($pay_status == 'SUCCESS'){
										$now_status = 'realpay';
									}
									if($now_status){	
										$params = array(
											'sum' => $in_sum,
											'bid_sum' => $bid_sum,
											'bid_status' => array('new','techpay'),
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
										set_bid_status($now_status, $order_id, $params, $data['direction_data']);  
									}
								} else {
									$this->logs($order_id.' The payment amount is less than the provisions');
								}
							} else {
								$this->logs($order_id.' Wrong type of currency');
							}
						} else {
							$this->logs($order_id.' In the application the wrong status');
						}
					}
				}
			}			

			echo '{"status":200}';
			exit;
		}		
	}
}

new merchant_odysseq(__FILE__, 'Odysseq');