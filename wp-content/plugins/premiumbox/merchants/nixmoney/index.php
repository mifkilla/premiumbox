<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Nixmoney[:en_US][ru_RU:]Nixmoney[:ru_RU]
description: [en_US:]Nixmoney merchant[:en_US][ru_RU:]мерчант Nixmoney[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_nixmoney')){
	class merchant_nixmoney extends Merchant_Premiumbox{
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
				'NIXMONEY_PASSWORD'  => array(
					'title' => '[en_US:]Account password[:en_US][ru_RU:]Пароль от аккаунта NixMoney[:ru_RU]',
					'view' => 'input',	
				),
				'NIXMONEY_ACCOUNT'  => array(
					'title' => '[en_US:]Account e-mail[:en_US][ru_RU:]E-mail от аккаунта NixMoney[:ru_RU]',
					'view' => 'input',
				),
				'NIXMONEY_USD'  => array(
					'title' => '[en_US:]USD wallet number[:en_US][ru_RU:]USD номер счета[:ru_RU]',
					'view' => 'input',
				),
				'NIXMONEY_EUR'  => array(
					'title' => '[en_US:]EUR wallet number[:en_US][ru_RU:]EUR номер счета[:ru_RU]',
					'view' => 'input',
				),
				'NIXMONEY_BTC'  => array(
					'title' => '[en_US:]BTC wallet number[:en_US][ru_RU:]BTC номер счета[:ru_RU]',
					'view' => 'input',
				),
				'NIXMONEY_LTC'  => array(
					'title' => '[en_US:]LTC wallet number[:en_US][ru_RU:]LTC номер счета[:ru_RU]',
					'view' => 'input',
				),
				'NIXMONEY_PPC'  => array(
					'title' => '[en_US:]PPC wallet number[:en_US][ru_RU:]PPC номер счета[:ru_RU]',
					'view' => 'input',
				),
				'NIXMONEY_FTC'  => array(
					'title' => '[en_US:]FTC wallet number[:en_US][ru_RU:]FTC номер счета[:ru_RU]',
					'view' => 'input',
				),
				'NIXMONEY_CRT'  => array(
					'title' => '[en_US:]CRT wallet number[:en_US][ru_RU:]CRT номер счета[:ru_RU]',
					'view' => 'input',
				),
				'NIXMONEY_GBC'  => array(
					'title' => '[en_US:]GBC wallet number[:en_US][ru_RU:]GBC номер счета[:ru_RU]',
					'view' => 'input',
				),
				'NIXMONEY_DOGE'  => array(
					'title' => '[en_US:]DOGE wallet number[:en_US][ru_RU:]DOGE номер счета[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('NIXMONEY_PASSWORD');
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
				
				$PAYEE_ACCOUNT = 0;
						
				if($currency == 'USD'){
					$PAYEE_ACCOUNT = is_deffin($m_defin,'NIXMONEY_USD');
				} elseif($currency == 'EUR'){
					$PAYEE_ACCOUNT = is_deffin($m_defin,'NIXMONEY_EUR');
				} elseif($currency == 'BTC'){
					$PAYEE_ACCOUNT = is_deffin($m_defin,'NIXMONEY_BTC');
				} elseif($currency == 'LTC'){
					$PAYEE_ACCOUNT = is_deffin($m_defin,'NIXMONEY_LTC');
				} elseif($currency == 'PPC'){
					$PAYEE_ACCOUNT = is_deffin($m_defin,'NIXMONEY_PPC');
				} elseif($currency == 'FTC'){
					$PAYEE_ACCOUNT = is_deffin($m_defin,'NIXMONEY_FTC');	
				} elseif($currency == 'CRT'){
					$PAYEE_ACCOUNT = is_deffin($m_defin,'NIXMONEY_CRT');	
				} elseif($currency == 'GBC'){
					$PAYEE_ACCOUNT = is_deffin($m_defin,'NIXMONEY_GBC');
				} elseif($currency == 'DOGE'){
					$PAYEE_ACCOUNT = is_deffin($m_defin,'NIXMONEY_DOGE');					
				}		
					
				$pay_sum = is_sum($pay_sum,2);				
				$text_pay = get_text_pay($m_id, $item, $pay_sum);
							
				$temp = '
				<form action="https://nixmoney.com/merchant.jsp" method="post" target="_blank">
					<input type="hidden" name="PAYEE_ACCOUNT" value="'. $PAYEE_ACCOUNT .'" />
					<input type="hidden" name="PAYEE_NAME" value="'. $text_pay .'" />
					<input type="hidden" name="PAYMENT_AMOUNT" value="'. $pay_sum .'" />
					<input type="hidden" name="PAYMENT_URL" value="'. get_mlink($m_id.'_success') .'" />
					<input type="hidden" name="NOPAYMENT_URL" value="'. get_mlink($m_id.'_fail') .'" />
					<input type="hidden" name="BAGGAGE_FIELDS" value="PAYMENT_ID" />
					<input type="hidden" name="PAYMENT_ID" value="'. $item->id .'" />
					<input type="hidden" name="STATUS_URL" value="'. get_mlink($m_id.'_status' . hash_url($m_id)) . '" />
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
	
			if(!isset($_POST['PAYMENT_ID'])){
				$this->logs('no id');
				die('no id');
			}
			if(!isset($_POST['V2_HASH'])){
				$this->logs('no hash');
				die('no hash');
			}

			$string= $_POST['PAYMENT_ID'].':'.$_POST['PAYEE_ACCOUNT'].':'.$_POST['PAYMENT_AMOUNT'].':'.$_POST['PAYMENT_UNITS'].':'.$_POST['PAYMENT_BATCH_NUM'].':'.$_POST['PAYER_ACCOUNT'].':'.strtoupper(md5(is_deffin($m_defin,'NIXMONEY_PASSWORD'))).':'.$_POST['TIMESTAMPGMT'];
			 
			$v2key = $_POST['V2_HASH'];
			$hash=strtoupper(md5($string));
		  
			if($hash != $v2key){
				$this->logs('Invalid control signature');
				die( 'Invalid control signature' );
			}			
				
			$iPaymentBatch = $_POST['PAYMENT_BATCH_NUM'];
			$iPaymentID = $_POST['PAYMENT_ID'];
			$dPaymentAmount = $_POST['PAYMENT_AMOUNT'];
			$sPayerAccount = $_POST['PAYER_ACCOUNT'];
			$currency = strtoupper($_POST['PAYMENT_UNITS']);
			$sPayeeAccount = $_POST['PAYEE_ACCOUNT'];
				
			$check_history = intval(is_isset($m_data, 'check_api'));
			$show_error = intval(is_isset($m_data, 'show_error'));
			if($check_history == 1){
				
				try {
					$class = new NixMoney( is_deffin($m_defin,'NIXMONEY_ACCOUNT'), is_deffin($m_defin,'NIXMONEY_PASSWORD') );
					$hres = $class->getHistory( date( 'd.m.Y', strtotime( '-2 day' ) ), date( 'd.m.Y', strtotime( '+2 day' ) ), 'batchid', 'prihod' );
					if($hres['error'] == 0){
						$histories = $hres['responce'];
						if(isset($histories[$iPaymentBatch])){
							$h = $histories[$iPaymentBatch];
							$sPayerAccount = trim($h['sender']); //счет плательщика
							$sPayeeAccount = trim($h['receiver']); //счет получателя
							$dPaymentAmount = trim($h['amount']); //сумма платежа
							$currency = trim($h['currency']); //валюта платежа (USD/EUR/OAU)	
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
						die( 'Фатальная ошибка: '.$e->getMessage() );
					} else {
						die( 'Фатальная ошибка');
					}
				}		
				
			}				
				
			if(check_trans_in($m_id, $iPaymentBatch, $iPaymentID)){
				$this->logs('Error check trans in!');
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
				$this->logs('The application does not exist or the wrong ID');
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
				
			$pay_purse = is_pay_purse($sPayerAccount, $m_data, $bid_m_id);
				
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
						set_bid_status('realpay', $id, $params, $data['direction_data']);  					
		
						die( 'ok' );
								
					} else {
						$this->logs('The payment amount is less than the provisions');
						die('The payment amount is less than the provisions');
					}
				} else {
					$this->logs('Wrong type of currency');
					die('Wrong type of currency');
				}
			} else {
				$this->logs('In the application the wrong status');
				die( 'In the application the wrong status' );
			}						
		}
	}
}

new merchant_nixmoney(__FILE__, 'Nixmoney');