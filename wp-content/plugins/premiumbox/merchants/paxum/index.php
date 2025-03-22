<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Paxum[:en_US][ru_RU:]Paxum[:ru_RU]
description: [en_US:]Paxum merchant[:en_US][ru_RU:]мерчант Paxum[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_paxum')){
	class merchant_paxum extends Merchant_Premiumbox {

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
				'PAXUM_EMAIL'  => array(
					'title' => '[en_US:]Paxum login[:en_US][ru_RU:]Ваш логин в Paxum[:ru_RU]',
					'view' => 'input',	
				),
				'PAXUM_SECRET'  => array(
					'title' => '[en_US:]IPN Shared Secret[:en_US][ru_RU:]Ваш IPN Shared Secret, который был отправлен на e-mail[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}		

		function settings_list(){
			$arrs = array();
			$arrs[] = array('PAXUM_SECRET','PAXUM_EMAIL');
			return $arrs;
		}		
		
		function options($options, $data, $id, $place){  
			
			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, 'show_error');
			$options = pn_array_unset($options, 'check_api');
			
			$text = '
			<div><strong>RESULT URL:</strong> <a href="'. get_mlink($id.'_status'. hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_status' . hash_url($id)) .'</a></div>		
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

				$currency = pn_strip_input($item->currency_code_give);
				$pay_sum = is_sum($pay_sum,2);							
				$text_pay = get_text_pay($m_id, $item, $pay_sum);
						
				$temp = '
				<form name="changer_form" action="https://www.paxum.com/payment/phrame.php?action=displayProcessPaymentLogin" target="_blank" rel="noreferrer noopener" method="post">
					<input type="hidden" name="business_email" value="'. is_deffin($m_defin,'PAXUM_EMAIL') .'" />
					<input type="hidden" name="button_type_id" value="1" />
					<input type="hidden" name="item_id" value="'. $item->id .'" />
					<input type="hidden" name="item_name" value="'. $text_pay .'" />
					<input type="hidden" name="amount" value="'. $pay_sum .'" />
					<input type="hidden" name="currency" value="'. $currency .'" />
					<input type="hidden" name="ask_shipping" value="1" />
					<input type="hidden" name="cancel_url" value="'. get_mlink($m_id.'_fail') .'" />
					<input type="hidden" name="finish_url" value="'. get_mlink($m_id.'_success') .'" />
					<input type="hidden" name="variables" value="notify_url='. get_mlink($m_id.'_status'. hash_url($m_id)) .'" />
					<input type="submit" value="'. __('Make a payment','pn') .'" />
				</form>													
				';				

			}
			return $temp;		
		}

		function merchant_fail(){	
			$id = get_payment_id('transaction_item_id');
			redirect_merchant_action($id, $this->name);
		}

		function merchant_success(){	
			$id = get_payment_id('transaction_item_id');
			redirect_merchant_action($id, $this->name, 1);	
		}

		function merchant_status(){
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);
	
			if(!isset($_POST['transaction_item_id']) or !isset($_POST['key'])){
				$this->logs('No id'); 
				die('No id');
			}		
			
			$rawPostedData = file_get_contents('php://input');

			$i = strpos($rawPostedData, "&key=");
			$fieldValuePairsData = substr($rawPostedData, 0, $i);

			$calculatedKey = md5($fieldValuePairsData . is_deffin($m_defin,'PAXUM_SECRET'));

			$isValid = $_POST["key"] == $calculatedKey ? true : false;

			if(!$isValid)
			{
				$this->logs("This is not a valid notification message"); 
				die("This is not a valid notification message");
			}

			/*
			$_POST['transaction_item_id'] - номер заказа который прописывался в $OrderID в index.php
			$_POST['transaction_amount'] - сумма прихода 
			$_POST['transaction_currency'] - валюта прихода (USD,EUR..)
			$_POST['transaction_status'] - если все ок то вернет done.
			*/
			$currency = is_param_post('transaction_currency');
			
			$id = is_param_post('transaction_item_id');
			$data = get_data_merchant_for_id($id);
			
			$in_sum = is_param_post('transaction_amount');
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
				$this->logs('wrong script');
				die('wrong script');
			}			
			
			if($bid_m_id and $m_id != $bid_m_id or !$bid_m_id){
				$this->logs('not a faithful merchant');
				die('not a faithful merchant');				
			}
			
			$transaction_status = is_param_post('transaction_status');
			
			$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
			
			$bid_currency = $data['currency'];
			
			$bid_sum = is_sum($data['pay_sum'],2);
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
											
						die( 'Completed' );
								 
					} else {
						$this->logs('The payment amount is less than the provisions');
						die('The payment amount is less than the provisions');
					}
				} else {
					$this->logs('In the application the wrong status');
					die('Wrong type of currency');
				}
			} else {
				$this->logs('In the application the wrong status');
				die( 'In the application the wrong status' );
			}	
		}
	}
}

new merchant_paxum(__FILE__, 'Paxum');