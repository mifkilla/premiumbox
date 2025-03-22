<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]BillLine[:en_US][ru_RU:]BillLine[:ru_RU]
description: [en_US:]BillLine merchant[:en_US][ru_RU:]мерчант BillLine[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_billline')){
	class merchant_billline extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
				add_action('premium_merchant_'. $id .'_fail', array($this,'merchant_fail'));
				add_action('premium_merchant_'. $id .'_success', array($this,'merchant_success'));
			}
		}

		function get_map(){
			$map = array(
				'MERCH_ID'  => array(
					'title' => '[en_US:]Merchant ID[:en_US][ru_RU:]ID мерчанта[:ru_RU]',
					'view' => 'input',	
				),
				'SECRET_KEY'  => array(
					'title' => '[en_US:]Secret key[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
					'view' => 'input',
				),
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('MERCH_ID','SECRET_KEY');
			return $arrs;
		}		

		function options($options, $data, $id, $place){ 

			$options = pn_array_unset($options, array('pagenote','show_error','check_api'));			
			
			$text = '
			<div><strong>RETURN URL:</strong> <a href="'. get_mlink($id.'_status' . hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_status' . hash_url($id)) .'</a></div>
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

		function bidform($temp, $m_id, $pay_sum, $item, $direction){
			$script = get_mscript($m_id);
			if($script and $script == $this->name){
				$m_defin = $this->get_file_data($m_id);
				
				$data = get_merch_data($m_id);
				
				$currency = mb_strtoupper($item->currency_code_give);
				$country = 'RU';
				if($currency == 'UAH'){
					$country = 'UA';
				}
					
				$pay_sum = is_sum($pay_sum, 2);	
				
				$text_pay = get_text_pay($m_id, $item, $pay_sum);	
	
				$temp = '
				<form method="POST" action="https://api.billline.net/payment/form">
				
					<input type="hidden" name="merchant" value="'. is_deffin($m_defin,'MERCH_ID') .'" />
					<input type="hidden" name="item_name" value="'. $text_pay .'" />
					<input type="hidden" name="order" value="'. $item->id .'" />
					<input type="hidden" name="amount" value="'.  $pay_sum .'" />
					<input type="hidden" name="currency" value="'. $currency .'" />
					<input type="hidden" name="first_name" value="'. $item->first_name .'" />
					<input type="hidden" name="last_name" value="'. $item->last_name .'" />
					<input type="hidden" name="country" value="'. $country .'" />
					<input type="hidden" name="ip" value="'. pn_real_ip() .'" />
					<input type="hidden" name="custom" value=""/>
					<input type="hidden" name="process_url" value="'. get_mlink($m_id .'_status' . hash_url($m_id)) . '" />
					<input type="hidden" name="success_url" value="'. get_mlink($m_id.'_success') .'" />
					<input type="hidden" name="fail_url" value="'. get_mlink($m_id.'_fail') .'" />
			
					<input type="submit" value="'. __('Make a payment','pn') .'" />
				</form>					
				';	
				
			}	
			return $temp;				
		}

		function merchant_fail(){
			$id = get_payment_id('order_no');
			redirect_merchant_action($id, $this->name);
		}

		function merchant_success(){
			$id = get_payment_id('order_no');
			redirect_merchant_action($id, $this->name, 1);
		}

		function merchant_status(){
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);		
	
			$secret_key = is_deffin($m_defin,'SECRET_KEY');
			$merch_id = is_deffin($m_defin,'MERCH_ID');
			
			$co_post = array();
			if(is_array($_POST)){
				foreach($_POST as $post_key => $post_val){
					if(strstr($post_key, 'co_')){
						$co_post[$post_key] = $post_val;
					}
				}
			}
			
			$sign = trim(is_isset($co_post,'co_sign'));
			
			if(isset($co_post['co_sign'])){
				unset($co_post['co_sign']);
			}
			ksort($co_post, SORT_STRING);
			
			array_push($co_post, $secret_key);
			$signString = implode(':', $co_post);
			$calc_sign = base64_encode(md5($signString, true));
	
			if(strlen($sign) > 0 and $calc_sign != $sign or strlen($sign) < 1){
				$this->logs('Invalid control signature');
				die('Invalid control signature');
			}
			
			$co_merchant_uuid = trim(is_isset($co_post,'co_merchant_uuid'));
			if($co_merchant_uuid != $merch_id){
				$this->logs('merchantUuid is not site');
				die('merchantUuid is not site');
			}			
			
			$payStatus = trim(strtolower(is_isset($co_post,'co_inv_st')));
			if($payStatus != 'success'){
				$this->logs('not success');
				echo 'OK';
				exit;
			}			
	
			$trans_id = trim(is_isset($co_post,'co_inv_id'));
			$order_id = intval(is_isset($co_post,'co_order_no'));
			$currency = trim(is_isset($co_post,'co_cur'));
			$amount = trim(is_isset($co_post,'co_amount'));
			
			if(check_trans_in($m_id, $trans_id, $order_id)){
				$this->logs($order_id . ' Error check trans in!');
				echo 'OK';
				exit;
			}			
			
			$id = $order_id;
			$data = get_data_merchant_for_id($id);
				
			$in_sum = $amount;	
			$in_sum = is_sum($in_sum, 2);
			
			$bid_err = $data['err'];
			$bid_status = $data['status'];
			$bid_m_id = $data['m_id'];
			$bid_m_script = $data['m_script'];
			
			if($bid_err > 0){
				$this->logs($id.' The application does not exist or the wrong ID');
				echo 'OK';
				exit;
			}			
			
			if($bid_m_script and $bid_m_script != $this->name or !$bid_m_script){	
				$this->logs($id.' wrong script');
				echo 'OK';
				exit;
			}			
			
			if($bid_m_id and $m_id != $bid_m_id or !$bid_m_id){
				$this->logs($id.' not a faithful merchant');
				echo 'OK';
				exit;				
			}			
			
			$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
				
			$bid_currency = $data['currency'];
			
			$bid_sum = is_sum($data['pay_sum'], 2);
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
				
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));				
			
			if($bid_status == 'new'){ 
				if($bid_currency == $currency or $invalid_ctype > 0){
					if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){		
						
						$params = array(
							'pay_purse' => $pay_purse,
							'sum' => $in_sum,
							'bid_sum' => $bid_sum,
							'bid_corr_sum' => $bid_corr_sum,
							'bid_status' => array('new'),
							'to_account' => $merch_id,
							'trans_in' => $trans_id,
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
						set_bid_status('realpay', $id, $params, $data['direction_data']);  	 								
			
						echo 'OK';
						exit;
						
					} else {
						$this->logs($id.' The payment amount is less than the provisions');
						echo 'OK';
						exit;
					}
				} else {
					$this->logs($id.' Wrong type of currency');
					echo 'OK';
					exit;
				}
			} else {
				$this->logs($id.' In the application the wrong status');
				echo 'OK';
				exit;
			}	
		}
	}
}

new merchant_billline(__FILE__, 'BillLine');