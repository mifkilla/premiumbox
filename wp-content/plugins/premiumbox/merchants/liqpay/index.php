<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]LiqPay[:en_US][ru_RU:]LiqPay[:ru_RU]
description: [en_US:]LiqPay merchant[:en_US][ru_RU:]мерчант LiqPay[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_liqpay')){
	class merchant_liqpay extends Merchant_Premiumbox {
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
				add_action('premium_merchant_'. $id .'_fail', array($this,'merchant_fail'));
				add_action('premium_merchant_'. $id .'_success', array($this,'merchant_success'));
			}
		}

		function get_map(){
			$map = array(
				'ACCOUNT_ID'  => array(
					'title' => '[en_US:]Account id[:en_US][ru_RU:]Номер счета[:ru_RU]',
					'view' => 'input',
				),			
				'LIQPAY_PUBLIC_KEY'  => array(
					'title' => '[en_US:]Public key[:en_US][ru_RU:]Public key[:ru_RU]',
					'view' => 'input',
				),
				'LIQPAY_PRIVATE_KEY'  => array(
					'title' => '[en_US:]Private key[:en_US][ru_RU:]Private key[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('LIQPAY_PUBLIC_KEY','LIQPAY_PRIVATE_KEY');
			return $arrs;
		}

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'pagenote');
									
			$options['private_line'] = array(
				'view' => 'line',
			);
			$opt = array(
				'0' => __('shop settings','pn'),
				'1' => __('card payment','pn'),
				'2' => __('liqpay account','pn'),
				'3' => __('privat24 account','pn'),
				'4' => __('masterpass account','pn'),
				'5' => __('installments','pn'),
				'6' => __('cash','pn'),
				'7' => __('invoice to e-mail','pn'),
				'8' => __('qr code scanning','pn'),
			);
			$paytype = intval(is_isset($data, 'paytype'));
			$options[] = array(
				'view' => 'select',
				'title' => __('Payment method','pn'),
				'options' => $opt,
				'default' => $paytype,
				'name' => 'paytype',
				'work' => 'int',
			);			
			
			$text = '
			<div><strong>RETURN URL:</strong> <a href="'. get_mlink($id.'_status' . hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_status' . hash_url($id)) .'</a></div>
			<div><strong>SUCCESS URL:</strong> <a href="'. get_mlink($id.'_success') .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_success') .'</a></div>
			<div><strong>FAIL URL:</strong> <a href="'. get_mlink($id.'_fail') .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_fail') .'</a></div>	
			<div><strong>CRON:</strong> <a href="'. get_mlink($id.'_cron' . hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_cron' . hash_url($id)) .'</a></div>				
			';

			$options[] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;	
		}					

		function bidform($temp, $m_id, $pay_sum, $item, $direction){
			$script = get_mscript($m_id);
			if($script and $script == $this->name){
				$m_defin = $this->get_file_data($m_id);

				$params = array(
					'sum' => 0,
					'bid_status' => array('new'),
					'm_place' => $this->name,
					'system' => 'user',
				);		
				set_bid_status('techpay', $item->id, $params, $direction);	  	
			 
				$currency = pn_strip_input($item->currency_code_give);
						
				$locale = get_locale();
				if($locale == 'ru_RU'){
					$lang = 'ru';
				} else {
					$lang = 'en';
				}			
							
				$pay_sum = is_sum($pay_sum,2);		
				$text_pay = get_text_pay($m_id, $item, $pay_sum);
							
				$LIQPAY_RESULT_URL = get_mlink($m_id.'_success');
				$LIQPAY_SERVER_URL = get_mlink($m_id.'_status' . hash_url($m_id));
					
				$m_data = get_merch_data($m_id);
				$show_error = intval(is_isset($m_data, 'show_error'));	
						
				try {
					$liqpay = new LiqPay(is_deffin($m_defin,'LIQPAY_PUBLIC_KEY'), is_deffin($m_defin,'LIQPAY_PRIVATE_KEY'));
					$cnb_form = array(
						'version'        => 3,
						'action'         => 'pay',
						'amount'         => $pay_sum,
						'currency'       => $currency,
						'description'    => $text_pay,
						'order_id'       => $item->id,
						'language'       => $lang,
						'result_url'       => $LIQPAY_RESULT_URL,
						'server_url'       => $LIQPAY_SERVER_URL,
						'public_key' => is_deffin($m_defin,'LIQPAY_PUBLIC_KEY'),
					);
					
					$paytype = intval(is_isset($m_data, 'paytype'));			
					$opts = array(
						'1' => 'card',
						'2' => 'liqpay',
						'3' => 'privat24',
						'4' => 'masterpass',
						'5' => 'part',
						'6' => 'cash',
						'7' => 'invoice',
						'8' => 'qr',
					);
					$pt = trim(is_isset($opts, $paytype));
					if($pt){
						$cnb_form['paytypes'] = $pt;
					} 
					
					$temp = $liqpay->cnb_form($cnb_form);				
				}
				catch( Exception $e ) {
					$this->logs($e->getMessage());
					if($show_error and current_user_can('administrator')){
						$temp = $e->getMessage();
					}
				}
			}		
			return $temp;				
		}

		function merchant_fail(){
			$id = get_payment_id('order_id');
			redirect_merchant_action($id, $this->name);
		}

		function merchant_success(){
			$id = get_payment_id('order_id');
			redirect_merchant_action($id, $this->name, 1);
		}

		function merchant_status(){
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);	
	
			$def_signature = is_param_req('signature');
			$def_data = is_param_req('data');
	
			if(!$def_signature){
				$this->logs('bad signature');
				die( 'bad signature' );
			}
			
			$data = base64_decode($def_data);
			$datap = @json_decode($data, true);
			
			$this->logs($datap);
	
			$public_key = is_deffin($m_defin,'LIQPAY_PUBLIC_KEY');
			$private_key = is_deffin($m_defin,'LIQPAY_PRIVATE_KEY');

			$signature = base64_encode( sha1( $private_key . $def_data . $private_key, 1 ) );
			if($signature != $def_signature){
				$this->logs('bad sign in request');
				die( 'bad sign in request' );
			}
			
			$order_id = $datap['order_id'];
			$type = $datap['type'];/* buy */
			$action = $datap['action'];/* pay */
			$status = $datap['status'];
			$amount = $datap['amount'];
			$currency = $datap['currency'];
			$transaction_id = $datap['transaction_id'];
			
			$check_history = intval(is_isset($m_data, 'check_api'));
			$show_error = intval(is_isset($m_data, 'show_error'));
			if($check_history == 1){
			
				try {
					$liqpay = new LiqPay($public_key, $private_key);
					$res = $liqpay->api("request", array(
						'action' => 'status',
						'version' => '3',
						'order_id' => $order_id
					));	
					$this->logs($res);
					if(isset($res->result)){
						$type = $res->type;/* buy */
						$action = $res->action;/* pay */
						$status = $res->status;
						$amount = $res->amount;
						$currency = $res->currency;
						$transaction_id = $res->transaction_id;						
					} else {
						$this->logs('bad request');
						die( 'bad request' );
					}
				}
				catch( Exception $e ) {
					$this->logs($e->getMessage());
					if($show_error and current_user_can('administrator')){
						die('Фатальная ошибка: '.$e->getMessage());
					} else {
						die('Фатальная ошибка');
					}
				}		
			
			}	

			if($type != 'buy' or $action != 'pay'){
				$this->logs('bad data');
				die( 'bad data' );
			}
			
			if(check_trans_in($m_id, $transaction_id, $order_id)){
				$this->logs($order_id.' Error check trans in!');
				die('Error check trans in!');
			}			
			
			$id = $order_id;
			$data = get_data_merchant_for_id($id);
			
			$in_sum = $amount;	
			$in_sum = is_sum($in_sum,2);
			$bid_err = $data['err'];
			$bid_status = $data['status'];
			$bid_m_id = $data['m_id'];
			$bid_m_script = $data['m_script'];
			
			if($bid_err > 0){
				$this->logs($id.' The application does not exist or the wrong ID');
				die('The application does not exist or the wrong ID');
			}			
			
			if($bid_m_script and $bid_m_script != $this->name or !$bid_m_script){	
				$this->logs($id. ' wrong script');
				die('wrong script');
			}			
			
			if($bid_m_id and $m_id != $bid_m_id or !$bid_m_id){
				$this->logs($id. ' not a faithful merchant');
				die('not a faithful merchant');				
			}	
			
			$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
				
			$bid_currency = $data['currency'];
			$bid_currency = str_replace(array('GLD'),'OAU',$bid_currency);
			
			$bid_sum = is_sum($data['pay_sum'],2);
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
			
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));			
			
			if($bid_status == 'new' or $bid_status == 'coldpay'or $bid_status == 'techpay'){ 
				if($bid_currency == $currency or $invalid_ctype > 0){
					if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){		
						
						if($status == 'success'){		
							$now_status = 'realpay';
						} elseif($status == 'failure' or $status == 'error' or $status == 'reversed') {
							$now_status = 'error';																		
						} else {	
							$now_status = 'coldpay';								
						}
						$params = array(
							'pay_purse' => $pay_purse,
							'sum' => $in_sum,
							'bid_sum' => $bid_sum,
							'bid_corr_sum' => $bid_corr_sum,
							'bid_status' => array('new','techpay','coldpay'),
							'to_account' => is_deffin($m_defin,'ACCOUNT_ID'),
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
								
					} else {
						$this->logs($id. ' The payment amount is less than the provisions');
						die('The payment amount is less than the provisions');
					}
				} else {
					$this->logs($id. ' Wrong type of currency');
					die('Wrong type of currency');
				}
			} else {
				$this->logs($id. ' In the application the wrong status');
				die( 'In the application the wrong status' );
			}										
	
		}
		
		function cron($m_id, $m_defin, $m_data){
		global $wpdb;	

			$show_error = intval(is_isset($m_data, 'show_error'));
			
			$public_key = is_deffin($m_defin,'LIQPAY_PUBLIC_KEY');
			$private_key = is_deffin($m_defin,'LIQPAY_PRIVATE_KEY');
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status IN('coldpay','techpay') AND m_in='$m_id'");
			foreach($items as $item){
				$order_id = $item->id;
				try {
					$liqpay = new LiqPay($public_key, $private_key);
					$res = $liqpay->api("request", array(
						'action' => 'status',
						'version' => '3',
						'order_id' => $order_id
					));	
					$this->logs($order_id.' '. print_r($res, true));
					if(isset($res->result, $res->status)){ 
						$type = $res->type;
						$action = $res->action;
						$amount = $res->amount;
						$currency = $res->currency;
						$transaction_id = $res->transaction_id;
						$status = $res->status;

						$id = $order_id;
						$data = get_data_merchant_for_id($id, apply_filters('long_server', $item));
						
						$in_sum = $amount;
						$in_sum = is_sum($in_sum,2);
						$bid_err = $data['err'];
						$bid_status = $data['status'];
						$bid_m_id = $data['m_id'];
						$bid_m_script = $data['m_script'];
						
						$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
						
						$bid_currency = $data['currency'];
									
						$bid_sum = is_sum($data['pay_sum'],2);
						$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
						
						$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
						$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
						$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
						$invalid_check = intval(is_isset($m_data, 'check'));						
						
						$get_status = array('coldpay','techpay');
						if(in_array($bid_status, $get_status)){
							if($bid_err == 0 and $type == 'buy' and $action == 'pay'){
								if($bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name){
									if($bid_currency == $currency or $invalid_ctype > 0){	
										if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){	
									
											if($status == 'success'){
												$now_status = 'realpay';										
											} elseif($status == 'failure' or $status == 'error' or $status == 'reversed') {
												$now_status = 'error';									
											} else {
												$now_status = 'coldpay';																
											}							
											$params = array(
												'pay_purse' => $pay_purse,
												'sum' => $in_sum,
												'bid_sum' => $bid_sum,
												'bid_corr_sum' => $bid_corr_sum,
												'bid_status' => array('techpay','coldpay'),
												'to_account' => is_deffin($m_defin,'ACCOUNT_ID'),
												'trans_in' => $transaction_id,
												'currency' => $currency,
												'bid_currency' => $bid_currency,
												'invalid_ctype' => $invalid_ctype,
												'invalid_minsum' => $invalid_minsum,
												'invalid_maxsum' => $invalid_maxsum,
												'invalid_check' => $invalid_check,
												'system' => 'system',	
												'm_place' => $bid_m_id.'_cron',
												'm_id' => $m_id,
												'm_data' => $m_data,
												'm_defin' => $m_defin,
											);
											set_bid_status($now_status, $id, $params, $data['direction_data']);
											
										} else {
											$this->logs($order_id.' The payment amount is less than the provisions');
										}
									} else {
										$this->logs($order_id.' Wrong type of currency');
									}
								} else {
									$this->logs($order_id.' error merchant');
								}
							} else {
								$this->logs($order_id.' error type or action');
							}
						}	
					} else {
						$this->logs($order_id.' error status');
					}						
				}
				catch( Exception $e ) {
					$this->logs($e->getMessage());
					if($show_error and current_user_can('administrator')){
						die($e->getMessage());
					}	
				}
			}				
		}	
	}
}
new merchant_liqpay(__FILE__, 'LiqPay');