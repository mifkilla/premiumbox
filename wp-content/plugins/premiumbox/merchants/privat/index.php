<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Privat24[:en_US][ru_RU:]Privat24[:ru_RU]
description: [en_US:]Privat24 merchant[:en_US][ru_RU:]мерчант Privat24[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_privat')){
	class merchant_privat extends Merchant_Premiumbox{

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			add_action('premium_merchant_'. $this->name .'_return', array($this,'merchant_return'));
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
			}
		}

		function get_map(){
			$map = array(
				'PRIVAT24_MERCHANT_ID_UAH'  => array(
					'title' => '[en_US:]Merchant ID[:en_US][ru_RU:]ID мерчант[:ru_RU]',
					'view' => 'input',	
				),
				'PRIVAT24_MERCHANT_KEY_UAH'  => array(
					'title' => '[en_US:]Merchant key[:en_US][ru_RU:]Ключ-пароль от мерчанта[:ru_RU]',
					'view' => 'input',
				),			
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('PRIVAT24_MERCHANT_ID_UAH','PRIVAT24_MERCHANT_KEY_UAH');
			return $arrs;
		}

		function options($options, $data, $id, $place){ 
			
			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, 'check_api');
			
			$text = '
			<div><strong>CRON:</strong> <a href="'. get_mlink($id.'_status' . hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_status' . hash_url($id)) .'</a></div>			
			';

			$options['text'] = array(
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

				$currency = pn_strip_input($item->currency_code_give);
					
				$merchant = 0;
				if($currency == 'UAH'){
					$merchant = is_deffin($m_defin,'PRIVAT24_MERCHANT_ID_UAH');
				} 	
						
				$pay_sum = is_sum($pay_sum,2);		
				$text_pay = get_text_pay($m_id, $item, $pay_sum);
						
				$params = array(
					'sum' => 0,
					'bid_status' => array('new'),
					'm_place' => $m_id,
					'system' => 'user',
				);
				set_bid_status('techpay', $item->id, $params, $direction);  								
						 
				$temp = '
				<form name="pay" action="https://api.privatbank.ua/p24api/ishop" method="post">
												
					<input type="hidden" name="merchant" value="'. $merchant .'" />
					<input type="hidden" name="pay_way" value="privat24" />
					<input type="hidden" name="server_url" value="'. get_mlink($m_id.'_status' . hash_url($m_id)) .'" />
					<input type="hidden" name="return_url" value="'. get_mlink($this->name.'_return') .'" />
					<input name="order" type="hidden" value="'. $item->id .'" />
					<input name="amt" type="hidden" value="'. $pay_sum .'" />
					<input name="ccy" type="hidden" value="'. $currency .'" />
					<input name="details" type="hidden" value="'. $text_pay .'" />
					<input name="ext_details" type="hidden" value="'. is_email($item->user_email) .'" />

					<input type="submit" value="'. __('Make a payment','pn') .'" />
				</form>												
				';					
		
			}
			return $temp;					
		}

		function merchant_return(){
	
			$payment = urldecode(is_param_post('payment'));
			parse_str($payment,$arr);
			
			$id = intval(is_isset($arr,'order'));
			$state = is_isset($arr,'state');

			if($state == 'ok'){
				redirect_merchant_action($id, $this->name, 1);
			} else {	
				redirect_merchant_action($id, $this->name, 1);
			}			
	
		}

		function merchant_status(){
		global $wpdb;
			
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);
			
			$show_error = intval(is_isset($m_data, 'show_error'));
			
			$en_currency = array('UAH');
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status IN('coldpay','techpay') AND m_in='$m_id'");
			foreach($items as $item){
				$currency = mb_strtoupper($item->currency_code_give);
				if(in_array($currency, $en_currency)){
					$merchant_id = is_deffin($m_defin,'PRIVAT24_MERCHANT_ID_' . $currency);
					$merchant_key = is_deffin($m_defin,'PRIVAT24_MERCHANT_KEY_' . $currency);
					if($merchant_id and $merchant_key){
						try {
							$oClass = new PrivatBank($m_id, $merchant_id,$merchant_key);
							$res = $oClass->get_order($item->id);
							$this->logs($item->id.' '. print_r($res, true));
							if(isset($res['state']) and $res['state'] == 'ok'){
								$currency = $res['ccy'];
								
								$id = $res['order'];
								$data = get_data_merchant_for_id($id, apply_filters('long_server', $item));
								
								$in_sum = $res['amt'];
								$in_sum = is_sum($in_sum,2);
								$err = $data['err'];
								$status = $data['status'];
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
								if(in_array($status, $get_status)){
									if(!check_trans_in($bid_m_id, is_isset($res,'payment_id'), $id)){
										if($err == 0 and $bid_m_id and $m_id == $bid_m_id and $bid_m_script and $bid_m_script == $this->name){
											if($bid_currency == $currency or $invalid_ctype > 0){
												if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){	
													$params = array(
														'pay_purse' => $pay_purse,
														'sum' => $in_sum,
														'bid_sum' => $bid_sum,
														'bid_corr_sum' => $bid_corr_sum,
														'bid_status' => array('techpay','coldpay'),
														'to_account' => $merchant_id,
														'trans_in' => is_isset($res,'payment_id'),
														'currency' => $currency,
														'bid_currency' => $bid_currency,
														'invalid_ctype' => $invalid_ctype,
														'invalid_minsum' => $invalid_minsum,
														'invalid_maxsum' => $invalid_maxsum,
														'invalid_check' => $invalid_check,
														'm_place' => $m_id,
														'm_id' => $m_id,
														'm_data' => $m_data,
														'm_defin' => $m_defin,
													);
													set_bid_status('realpay', $id, $params, $data['direction_data']);											
												} else {
													$this->logs($item->id . ' The payment amount is less than the provisions');
												}		
											} else {
												$this->logs($item->id . ' Wrong type of currency');
											}
										} else {
											$this->logs($item->id.' error merchant');
										}
									} else {
										$this->logs($item->id . ' Error check trans in!');
									}
								}
							} else {
								$this->logs($item->id.' error state');
							}
						}
						catch( Exception $e ) {
							$this->logs($e->getMessage());
							if($show_error and current_user_can('administrator')){
								echo $e->getMessage();
							}
						}
					} 
				}
			}
			_e('Done','pn');
		}
	}
}

new merchant_privat(__FILE__, 'Privat24');