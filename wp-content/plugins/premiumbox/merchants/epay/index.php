<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]E-Pay[:en_US][ru_RU:]E-Pay[:ru_RU]
description: [en_US:]E-Pay merchant[:en_US][ru_RU:]мерчант E-Pay[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_epay')){
	class merchant_epay extends Merchant_Premiumbox {

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
				'PAYEE_ACCOUNT'  => array(
					'title' => '[en_US:]Login[:en_US][ru_RU:]Логин[:ru_RU]',
					'view' => 'input',	
				),
				'PAYEE_NAME'  => array(
					'title' => '[en_US:]Payee name (arbitrary)[:en_US][ru_RU:]Имя продавца (произвольное)[:ru_RU]',
					'view' => 'input',
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
			$arrs[] = array('PAYEE_ACCOUNT','PAYEE_NAME','API_KEY');
			return $arrs;
		}		
		
		function options($options, $data, $id, $place){  
			
			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, 'show_error');						
			
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

				$currency = pn_strip_input($item->currency_code_give);
				$pay_sum = is_sum($pay_sum,2);				
				$text_pay = get_text_pay($m_id, $item, $pay_sum);
				
				$PAYEE_ACCOUNT = is_deffin($m_defin,'PAYEE_ACCOUNT');
				$PAYEE_NAME = is_deffin($m_defin,'PAYEE_NAME');
				$PAYMENT_AMOUNT = $pay_sum;
				$PAYMENT_UNITS = $currency;
				$PAYMENT_ID = $item->id;
				$API_KEY = is_deffin($m_defin,'API_KEY');
				$V2_HASH = MD5($PAYEE_ACCOUNT.':'.$PAYMENT_AMOUNT.':'.$PAYMENT_UNITS.':'.$API_KEY);			
					
				$temp = '
				<form method="post" action="https://api.epay.com/paymentApi/merReceive" >
					<input name="PAYEE_ACCOUNT" type="hidden" value="'. $PAYEE_ACCOUNT .'" />
					<input name="PAYEE_NAME" type="hidden" value="'. $PAYEE_NAME .'" />
					<input name="PAYMENT_AMOUNT" type="hidden" value="'. $PAYMENT_AMOUNT .'" />
					<input name="PAYMENT_UNITS" type="hidden" value="'. $PAYMENT_UNITS .'" />
					<input name="PAYMENT_ID" type="hidden" value="'. $PAYMENT_ID .'" />
					<input name="STATUS_URL" type="hidden" value="'. get_mlink($m_id.'_status' . hash_url($m_id)) . '" />
					<input name="PAYMENT_URL" type="hidden" value="'. get_mlink($m_id.'_success') .'" />
					<input name="NOPAYMENT_URL" type="hidden" value="'. get_mlink($m_id.'_fail') .'" />
					<input name="BAGGAGE_FIELDS" type="hidden" value="" />
					<input name="KEY_CODE" type="hidden" value="" />
					<input name="BATCH_NUM" type="hidden" value="" />
					<input name="SUGGESTED_MEMO" type="hidden" value="'. $text_pay .'" />
					<input name="FORCED_PAYER_ACCOUNT" type="hidden" value="" />
					<input name="INTERFACE_LANGUAGE" type="hidden" value="" />
					<input name="CHARACTER_ENCODING" type="hidden" value="" />
					<input name="V2_HASH" type="hidden" value="'. $V2_HASH .'" />
					<input type="submit" formtarget="_top" value="'. __('Make a payment','pn') .'" />	
				</form>								
				';				
			}	
			return $temp;				
		}

		function merchant_fail(){
			$id = get_payment_id('PAYMENT_ID');
			redirect_merchant_action($id, $this->name);
		}

		function merchant_success(){
			$id = get_payment_id('PAYMENT_ID');
			redirect_merchant_action($id, $this->name, 1);
		}

		function merchant_status(){
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);	
	
			$PAYEE_ACCOUNT = is_deffin($m_defin,'PAYEE_ACCOUNT');
			$PAYEE_NAME = is_deffin($m_defin,'PAYEE_NAME');
			$API_KEY = is_deffin($m_defin,'API_KEY');	
	
			$sPayeeAccount = is_param_post('PAYEE_ACCOUNT');
			$iPaymentID = is_param_post('PAYMENT_ID');
			$dPaymentAmount = is_param_post('PAYMENT_AMOUNT');
			$currency = is_param_post('PAYMENT_UNITS');
			$iPaymentBatch = is_param_post('ORDER_NUM');
			$sPayerAccount = is_param_post('PAYER_ACCOUNT');
			$sTimeStampGMT = is_param_post('TIMESTAMPGMT');
			$sV2Hash2 = is_param_post('V2_HASH2');
			$Now_status = is_param_post('STATUS');

			$V2_HASH2= MD5($iPaymentID.':'. $iPaymentBatch .':'. $sPayeeAccount .':'. $dPaymentAmount .':'. $currency .':'. $sPayerAccount .':'. $Now_status .':'. $sTimeStampGMT .':'. $API_KEY);
			
			if($V2_HASH2 != $sV2Hash2){
				$this->logs('Invalid control signature');
				die( 'Invalid control signature' );
			}			
			
			$check_history = intval(is_isset($m_data, 'check_api'));
			$show_error = intval(is_isset($m_data, 'show_error'));
			if($check_history == 1){
				try {
					$class = new EPay( $PAYEE_ACCOUNT, $PAYEE_NAME, $API_KEY );
					$hres = $class->getHistory( $iPaymentBatch, 'prihod' );
					if($hres['error'] == 0){
						$histories = $hres['responce'];
						if(isset($histories[$iPaymentBatch])){
							$h = $histories[$iPaymentBatch];
							$sPayerAccount = trim($h['PAYER']); //счет плательщика
							$sPayeeAccount = trim($h['PAYEE']); //счет получателя
							$dPaymentAmount = trim($h['AMOUNT']); //сумма платежа
							$currency = trim($h['CURRENCY']); //валюта платежа
							$Now_status = trim($h['STATUS']); //статус платежа
						} else {
							$this->logs('Wrong pay');
							die('Wrong pay');
						}
					} else {
						$this->logs('Error history');
						die( 'Error history' );
					}
				}
				catch( Exception $e ) {
					$this->logs($e->getMessage());
					if($show_error and current_user_can('administrator')){
						die( 'Фатальная ошибка: '.$e->getMessage() );
					} else {
						die( 'Фатальная ошибка');
					}
				}		
			}
			
			if( $sPayeeAccount != $PAYEE_ACCOUNT ){
				$this->logs('Invalid the seller s account');
				die( 'Invalid the seller s account' );
			}		

			if(check_trans_in($m_id, $iPaymentBatch, $iPaymentID)){
				$this->logs($iPaymentID.' Error check trans in!');
				die('Error check trans in!');
			}
			
			$id = $iPaymentID;
			$data = get_data_merchant_for_id($id);
				
			$in_sum = $dPaymentAmount;	
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
				$this->logs($id.' wrong script');
				die('wrong script');
			}			
			
			if($bid_m_id and $m_id != $bid_m_id or !$bid_m_id){
				$this->logs($id.' not a faithful merchant');
				die('not a faithful merchant');				
			}				
				
			$pay_purse = is_pay_purse($sPayerAccount, $m_data, $bid_m_id);
				
			$bid_currency = $data['currency'];
			
			$bid_sum = is_sum($data['pay_sum'],2);
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
			
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));			
			
			$pending_arr = array('10','60','61','70');
			
			if($bid_status == 'new' or $bid_status == 'coldpay'){
				if($bid_currency == $currency or $invalid_ctype > 0){
					if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){		
						
						$params = array(
							'pay_purse' => $pay_purse,
							'sum' => $in_sum,
							'bid_sum' => $bid_sum,
							'bid_status' => array('new','techpay','coldpay'),
							'bid_corr_sum' => $bid_corr_sum,
							'to_account' => $sPayeeAccount,
							'trans_in' => $iPaymentBatch,
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
						
						if($Now_status == 1){		
							set_bid_status('realpay', $id, $params, $data['direction_data']);  			
						} elseif(in_array($Now_status, $pending_arr)) {		
							set_bid_status('coldpay', $id, $params, $data['direction_data']);			
						}

						die('Completed');
									
					} else {
						$this->logs($id.' The payment amount is less than the provisions');
						die('The payment amount is less than the provisions');
					}
				} else {
					$this->logs($id.' Wrong type of currency');
					die('Wrong type of currency');
				}
			} else {
				$this->logs($id.' In the application the wrong status');
				die( 'In the application the wrong status' );
			}	
		}
	}
}

new merchant_epay(__FILE__, 'E-Pay');