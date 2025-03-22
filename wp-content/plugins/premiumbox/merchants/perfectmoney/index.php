<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Perfect Money[:en_US][ru_RU:]Perfect Money[:ru_RU]
description: [en_US:]Perfect Money merchant[:en_US][ru_RU:]мерчант Perfect Money[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_perfectmoney')){
	class merchant_perfectmoney extends Merchant_Premiumbox {

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
				'PM_ACCOUNT_ID'  => array(
					'title' => '[en_US:]Account ID[:en_US][ru_RU:]ID аккаунта[:ru_RU]',
					'view' => 'input',	
				),
				'PM_PHRASE'  => array(
					'title' => '[en_US:]Account password[:en_US][ru_RU:]Пароль от аккаунта[:ru_RU]',
					'view' => 'input',
				),
				'PM_U_ACCOUNT'  => array(
					'title' => '[en_US:]USD wallet number[:en_US][ru_RU:]USD счет[:ru_RU]',
					'view' => 'input',
				),
				'PM_E_ACCOUNT'  => array(
					'title' => '[en_US:]EUR wallet number[:en_US][ru_RU:]EUR счет[:ru_RU]',
					'view' => 'input',
				),
				'PM_G_ACCOUNT'  => array(
					'title' => '[en_US:]GOLD wallet nubmer[:en_US][ru_RU:]GOLD счет[:ru_RU]',
					'view' => 'input',
				),
				'PM_B_ACCOUNT'  => array(
					'title' => '[en_US:]BTC wallet number[:en_US][ru_RU:]BTC счет[:ru_RU]',
					'view' => 'input',
				),
				'PM_PAYEE_NAME'  => array(
					'title' => '[en_US:]Payee name (arbitrary)[:en_US][ru_RU:]Имя продавца (произвольное)[:ru_RU]',
					'view' => 'input',
				),
				'PM_ALTERNATE_PHRASE'  => array(
					'title' => '[en_US:]Alternative passphrase[:en_US][ru_RU:]Альтернативная кодовая фраза[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('PM_U_ACCOUNT');
			$arrs[] = array('PM_E_ACCOUNT');
			$arrs[] = array('PM_G_ACCOUNT');
			$arrs[] = array('PM_B_ACCOUNT');
			return $arrs;
		}		

		function options($options, $data, $id, $place){ 
			
			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, 'show_error');
			
			$options['private_line'] = array(
				'view' => 'line',
			);			
			
			$options['paymethod'] = array(
				'view' => 'select',
				'title' => __('Payment method','pn'),
				'options' => array('0'=>__('All','pn'), '1'=>__('Account','pn'), '2'=>__('E-Voucher','pn'), '3'=>__('SMS','pn'), '4'=>__('Wire','pn')),
				'default' => is_isset($data, 'paymethod'),
				'name' => 'paymethod',
				'work' => 'int',
			);			
			
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
				$currency = str_replace('GLD','OAU',$currency);
					
				$PAYEE_ACCOUNT = 0;
				$PAYMENT_UNITS = '';
					
				if($currency == 'USD'){
					$PAYMENT_UNITS = 'USD';
					$PAYEE_ACCOUNT = is_deffin($m_defin,'PM_U_ACCOUNT');
				} elseif($currency == 'EUR'){
					$PAYMENT_UNITS = 'EUR';
					$PAYEE_ACCOUNT = is_deffin($m_defin,'PM_E_ACCOUNT');
				} elseif($currency == 'OAU'){
					$PAYMENT_UNITS = 'OAU';
					$PAYEE_ACCOUNT = is_deffin($m_defin,'PM_G_ACCOUNT');			
				} elseif($currency == 'BTC'){
					$PAYMENT_UNITS = 'BTC';
					$PAYEE_ACCOUNT = is_deffin($m_defin,'PM_B_ACCOUNT');			
				}		

				$pay_sum = is_sum($pay_sum,2);				
				$text_pay = get_text_pay($m_id, $item, $pay_sum);
				
				$data = get_merch_data($m_id);
				
				$paymethod = intval(is_isset($data, 'paymethod'));
				$AVAILABLE_PAYMENT_METHODS = 'all';
				if($paymethod == 1){
					$AVAILABLE_PAYMENT_METHODS = 'account';
				} elseif($paymethod == 2){
					$AVAILABLE_PAYMENT_METHODS = 'voucher';
				} elseif($paymethod == 3){
					$AVAILABLE_PAYMENT_METHODS = 'sms';
				} elseif($paymethod == 4){			
					$AVAILABLE_PAYMENT_METHODS = 'wire';
				}
						
				$perfetcmoney_domain = apply_filters('perfetcmoney_domain', 'perfectmoney.is');		
				$temp = '
				<form name="MerchantPay" action="https://'. $perfetcmoney_domain .'/api/step1.asp" method="post">
					<input name="SUGGESTED_MEMO" type="hidden" value="'. $text_pay .'" />
					<input name="sEmail" type="hidden" value="'. is_email($item->user_email) .'" />
					<input name="PAYMENT_AMOUNT" type="hidden" value="'. $pay_sum .'" />
					<input name="PAYEE_ACCOUNT" type="hidden" value="'. $PAYEE_ACCOUNT .'" />								
										
					<input type="hidden" name="AVAILABLE_PAYMENT_METHODS" value="'. $AVAILABLE_PAYMENT_METHODS .'" />					
					<input type="hidden" name="PAYEE_NAME" value="'. is_deffin($m_defin,'PM_PAYEE_NAME') .'" />
					<input type="hidden" name="PAYMENT_UNITS" value="'. $PAYMENT_UNITS .'" />
					<input type="hidden" name="PAYMENT_ID" value="'. $item->id .'" />
					<input type="hidden" name="STATUS_URL" value="'. get_mlink($m_id .'_status' . hash_url($m_id)) . '" />
					<input type="hidden" name="PAYMENT_URL" value="'. get_mlink($m_id.'_success') .'" />
					<input type="hidden" name="PAYMENT_URL_METHOD" value="POST" />
					<input type="hidden" name="NOPAYMENT_URL" value="'. get_mlink($m_id.'_fail') .'" />
					<input type="hidden" name="NOPAYMENT_URL_METHOD" value="POST" />
					<input type="hidden" name="SUGGESTED_MEMO_NOCHANGE" value="1" />
					<input type="hidden" name="BAGGAGE_FIELDS" value="sEmail" />

					<input type="submit" value="'. __('Make a payment','pn') .'" />
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
	
			$sPayeeAccount = isset( $_POST['PAYEE_ACCOUNT'] ) ? trim( $_POST['PAYEE_ACCOUNT'] ) : null;
			$iPaymentID = isset( $_POST['PAYMENT_ID'] ) ? $_POST['PAYMENT_ID'] : null;
			$dPaymentAmount = isset( $_POST['PAYMENT_AMOUNT'] ) ? trim( $_POST['PAYMENT_AMOUNT'] ) : null;
			$sPaymentUnits = isset( $_POST['PAYMENT_UNITS'] ) ? trim( $_POST['PAYMENT_UNITS'] ) : null;
			$iPaymentBatch = isset( $_POST['PAYMENT_BATCH_NUM'] ) ? trim( $_POST['PAYMENT_BATCH_NUM'] ) : null;
			$sPayerAccount = isset( $_POST['PAYER_ACCOUNT'] ) ? trim( $_POST['PAYER_ACCOUNT'] ) : null;
			$sTimeStampGMT = isset( $_POST['TIMESTAMPGMT'] ) ? trim( $_POST['TIMESTAMPGMT'] ) : null;
			$sV2Hash = isset( $_POST['V2_HASH'] ) ? trim( $_POST['V2_HASH'] ) : null;
			
			if( !in_array( $sPaymentUnits, array( 'USD', 'EUR', 'OAU', 'BTC' ) ) ){
				$this->logs('Invalid currency of payment'); 
				die('Invalid currency of payment');
			}

			if( $sV2Hash != strtoupper( md5( $iPaymentID.':'.$sPayeeAccount.':'.$dPaymentAmount.':'.$sPaymentUnits.':'.$iPaymentBatch.':'.$sPayerAccount.':'.strtoupper( md5( is_deffin($m_defin,'PM_ALTERNATE_PHRASE') ) ).':'.$sTimeStampGMT ) ) ){
				$this->logs('Invalid control signature'); 
				die('Invalid control signature');
			}

			$constant = is_deffin($m_defin,'PM_'.substr( $sPayeeAccount, 0, 1 ).'_ACCOUNT');
			if( $sPayeeAccount != $constant ){
				$this->logs('Invalid the seller s account');
				die('Invalid the seller s account');
			}
			
			$check_history = intval(is_isset($m_data, 'check_api'));
			$show_error = intval(is_isset($m_data, 'show_error'));
			if($check_history == 1){
				try {
					$class = new PerfectMoney( is_deffin($m_defin,'PM_ACCOUNT_ID'), is_deffin($m_defin,'PM_PHRASE') );
					$hres = $class->getHistory( date( 'd.m.Y', strtotime( '-2 day' ) ), date( 'd.m.Y', strtotime( '+2 day' ) ), 'batchid', 'prihod' );
					if($hres['error'] == 0){
						$histories = $hres['responce'];
						if(isset($histories[$iPaymentBatch])){
							$h = $histories[$iPaymentBatch];
							$sPayerAccount = trim($h['sender']); //счет плательщика
							$sPayeeAccount = trim($h['receiver']); //счет получателя
							$dPaymentAmount = trim($h['amount']); //сумма платежа
							$sPaymentUnits = trim($h['currency']); //валюта платежа (USD/EUR/OAU/BTC)
							$iPaymentID = trim($h['payment_id']); //id заявки
						} else {
							$this->logs('Wrong pay');
							die( 'Wrong pay' );
						}
					} else {
						$this->logs('Error history');
						die( 'Error history' );
					}
				}
				catch( Exception $e ) {
					$this->logs($e->getMessage());
					if($show_error and current_user_can('administrator')){
						die( $e->getMessage() );
					} else {
						die( 'Fatal error');
					}
				}		
			}
			
			if(check_trans_in($m_id, $iPaymentBatch, $iPaymentID)){
				$this->logs($iPaymentID . ' Error check trans in!');
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
			$bid_currency = str_replace(array('GLD'),'OAU',$bid_currency);
			
			$bid_sum = is_sum($data['pay_sum'],2);
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
				
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));				
			
			if($bid_status == 'new'){ 
				if($bid_currency == $sPaymentUnits or $invalid_ctype > 0){
					if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){		
						
						$params = array(
							'pay_purse' => $pay_purse,
							'sum' => $in_sum,
							'bid_sum' => $bid_sum,
							'bid_corr_sum' => $bid_corr_sum,
							'bid_status' => array('new'),
							'to_account' => $sPayeeAccount,
							'trans_in' => $iPaymentBatch,
							'currency' => $sPaymentUnits,
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

new merchant_perfectmoney(__FILE__, 'Perfect Money');