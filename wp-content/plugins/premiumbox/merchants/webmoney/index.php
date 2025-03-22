<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Webmoney[:en_US][ru_RU:]Webmoney[:ru_RU]
description: [en_US:]webmoney merchant[:en_US][ru_RU:]мерчант webmoney[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_webmoney')){
	class merchant_webmoney extends Merchant_Premiumbox{
		
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
				'WEBMONEY_WMZ_PURSE'  => array(
					'title' => '[en_US:]WMZ wallet number[:en_US][ru_RU:]WMZ кошелек[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMZ_KEY'  => array(
					'title' => '[en_US:]Secret key Z wallet[:en_US][ru_RU:]Secret Key WMZ кошелька[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMR_PURSE'  => array(
					'title' => '[en_US:]WMR wallet number[:en_US][ru_RU:]WMR кошелек[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMR_KEY'  => array(
					'title' => '[en_US:]Secret key WMR wallet[:en_US][ru_RU:]Secret Key WMR кошелька[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WME_PURSE'  => array(
					'title' => '[en_US:]WME wallet number[:en_US][ru_RU:]WME кошелек[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WME_KEY'  => array(
					'title' => '[en_US:]Secret key WME wallet[:en_US][ru_RU:]Secret Key WME кошелька[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMU_PURSE'  => array(
					'title' => '[en_US:]WMU wallet number[:en_US][ru_RU:]WMU кошелек[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMU_KEY'  => array(
					'title' => '[en_US:]Secret key WMU wallet[:en_US][ru_RU:]Secret Key WMU кошелька[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMB_PURSE'  => array(
					'title' => '[en_US:]WMB wallet number[:en_US][ru_RU:]WMB кошелек[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMB_KEY'  => array(
					'title' => '[en_US:]Secret key WMB wallet[:en_US][ru_RU:]Secret Key WMB кошелька[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMY_PURSE'  => array(
					'title' => '[en_US:]WMY wallet number[:en_US][ru_RU:]WMY кошелек[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMY_KEY'  => array(
					'title' => '[en_US:]Secret key WMY wallet[:en_US][ru_RU:]Secret Key WMY кошелька[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMG_PURSE'  => array(
					'title' => '[en_US:]WMG wallet number[:en_US][ru_RU:]WMG кошелек[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMG_KEY'  => array(
					'title' => '[en_US:]Secret key WMG wallet[:en_US][ru_RU:]Secret Key WMG кошелька[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMX_PURSE'  => array(
					'title' => '[en_US:]WMX wallet number[:en_US][ru_RU:]WMX кошелек[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMX_KEY'  => array(
					'title' => '[en_US:]Secret key WMX wallet[:en_US][ru_RU:]Secret Key WMX кошелька[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMK_PURSE'  => array(
					'title' => '[en_US:]WMK wallet number[:en_US][ru_RU:]WMK кошелек[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMK_KEY'  => array(
					'title' => '[en_US:]Secret key WMK wallet[:en_US][ru_RU:]Secret Key WMK кошелька[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WML_PURSE'  => array(
					'title' => '[en_US:]WML wallet number[:en_US][ru_RU:]WML кошелек[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WML_KEY'  => array(
					'title' => '[en_US:]Secret key WML wallet[:en_US][ru_RU:]Secret Key WML кошелька[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMH_PURSE'  => array(
					'title' => '[en_US:]WMH wallet number[:en_US][ru_RU:]WMH кошелек[:ru_RU]',
					'view' => 'input',
				),
				'WEBMONEY_WMH_KEY'  => array(
					'title' => '[en_US:]Secret key WMH wallet[:en_US][ru_RU:]Secret Key WMH кошелька[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('WEBMONEY_WMZ_PURSE','WEBMONEY_WMZ_KEY');
			$arrs[] = array('WEBMONEY_WMR_PURSE','WEBMONEY_WMR_KEY');
			$arrs[] = array('WEBMONEY_WME_PURSE','WEBMONEY_WME_KEY');
			$arrs[] = array('WEBMONEY_WMU_PURSE','WEBMONEY_WMU_KEY');
			$arrs[] = array('WEBMONEY_WMB_PURSE','WEBMONEY_WMB_KEY');
			$arrs[] = array('WEBMONEY_WMY_PURSE','WEBMONEY_WMY_KEY');
			$arrs[] = array('WEBMONEY_WMG_PURSE','WEBMONEY_WMG_KEY');
			$arrs[] = array('WEBMONEY_WMK_PURSE','WEBMONEY_WMK_KEY');
			$arrs[] = array('WEBMONEY_WML_PURSE','WEBMONEY_WML_KEY');
			$arrs[] = array('WEBMONEY_WMH_PURSE','WEBMONEY_WMH_KEY');
			return $arrs;
		}			

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, 'check_api');			
			
			$text = '
			<div><strong>Result URL:</strong> <a href="'. get_mlink($id.'_status' . hash_url($id)) .'" target="_blank">'. get_mlink($id.'_status' . hash_url($id)) .'</a></div>
			<div><strong>Success URL:</strong> <a href="'. get_mlink($id.'_success') .'" target="_blank">'. get_mlink($id.'_success') .'</a></div>
			<div><strong>Fail URL:</strong> <a href="'. get_mlink($id.'_fail') .'" target="_blank">'. get_mlink($id.'_fail') .'</a></div>		
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
				$currency = str_replace(array('WMZ'),'USD',$currency);
				$currency = str_replace(array('RUR','WMR'),'RUB',$currency);
				$currency = str_replace(array('WME'),'EUR',$currency);
				$currency = str_replace(array('WMU'),'UAH',$currency);
				$currency = str_replace(array('WMB'),'BYR',$currency);
				$currency = str_replace(array('WMY'),'UZS',$currency);
				$currency = str_replace(array('WMG'),'GLD',$currency);
				$currency = str_replace(array('WMX'),'BTC',$currency);
				$currency = str_replace(array('WMK'),'KZT',$currency);
				$currency = str_replace(array('WML'),'LTC',$currency);
				$currency = str_replace(array('WMH'),'BCH',$currency);
						
				$LMI_PAYEE_PURSE = 0;
						
				if($currency == 'USD'){
					$LMI_PAYEE_PURSE = is_deffin($m_defin,'WEBMONEY_WMZ_PURSE');
				} elseif($currency == 'RUB'){
					$LMI_PAYEE_PURSE = is_deffin($m_defin,'WEBMONEY_WMR_PURSE');
				} elseif($currency == 'EUR'){
					$LMI_PAYEE_PURSE = is_deffin($m_defin,'WEBMONEY_WME_PURSE');
				} elseif($currency == 'UAH'){
					$LMI_PAYEE_PURSE = is_deffin($m_defin,'WEBMONEY_WMU_PURSE');
				} elseif($currency == 'BYR'){
					$LMI_PAYEE_PURSE = is_deffin($m_defin,'WEBMONEY_WMB_PURSE');
				} elseif($currency == 'UZS'){
					$LMI_PAYEE_PURSE = is_deffin($m_defin,'WEBMONEY_WMY_PURSE');	
				} elseif($currency == 'GLD'){
					$LMI_PAYEE_PURSE = is_deffin($m_defin,'WEBMONEY_WMG_PURSE');
				} elseif($currency == 'BTC'){
					$LMI_PAYEE_PURSE = is_deffin($m_defin,'WEBMONEY_WMX_PURSE');
				} elseif($currency == 'KZT'){
					$LMI_PAYEE_PURSE = is_deffin($m_defin,'WEBMONEY_WMK_PURSE');			
				} elseif($currency == 'LTC'){
					$LMI_PAYEE_PURSE = is_deffin($m_defin,'WEBMONEY_WML_PURSE');
				} elseif($currency == 'BCH'){
					$LMI_PAYEE_PURSE = is_deffin($m_defin,'WEBMONEY_WMH_PURSE');				
				}		


				$pay_sum = is_sum($pay_sum,2);		
				$text_pay = get_text_pay($m_id, $item, $pay_sum);
							
				$temp = '
				<form name="MerchantPay" action="https://merchant.webmoney.ru/lmi/payment.asp" method="post" accept-charset="windows-1251">
					<input type="hidden" name="LMI_RESULT_URL" value="'. get_mlink($m_id.'_status' . hash_url($m_id)) .'" />
					<input type="hidden" name="LMI_SUCCESS_URL" value="'. get_mlink($m_id.'_success') .'" />
					<input type="hidden" name="LMI_SUCCESS_METHOD" value="POST" />
					<input type="hidden" name="LMI_FAIL_URL" value="'. get_mlink($m_id.'_fail') .'" />
					<input type="hidden" name="LMI_FAIL_METHOD" value="POST" />			    
					<input name="LMI_PAYMENT_NO" type="hidden" value="'. $item->id .'" />
					<input name="LMI_PAYMENT_AMOUNT" type="hidden" value="'. $pay_sum .'" />
					<input name="LMI_PAYEE_PURSE" type="hidden" value="'. $LMI_PAYEE_PURSE .'" />
					<input name="LMI_PAYMENT_DESC" type="hidden" value="'. $text_pay .'" />
					<input name="sEmail" type="hidden" value="'. is_email($item->user_email) .'" />				

					<input type="submit" value="Pay" />
				</form>			
				';				
			
			}
			return $temp;
		}

		function merchant_fail(){
			$id = get_payment_id('LMI_PAYMENT_NO');
			redirect_merchant_action($id, $this->name);	
		}

		function merchant_success(){	
			$id = get_payment_id('LMI_PAYMENT_NO');
			redirect_merchant_action($id, $this->name, 1);	
		}

		function merchant_status(){
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);
	
			$dPaymentAmount = trim(is_param_post('LMI_PAYMENT_AMOUNT'));
			$iPaymentID = trim(is_param_post('LMI_PAYMENT_NO'));
			$bPaymentMode = trim(is_param_post('LMI_MODE'));
			$iPayerWMID = trim(is_param_post('LMI_PAYER_WM'));
			$sPayerPurse = trim(is_param_post('LMI_PAYER_PURSE'));
			$sEmail = trim(is_param_post('sEmail'));

			if( $bPaymentMode != 0 ) {
				$this->logs('Payments are not permitted in test mode');
				die( 'Payments are not permitted in test mode' );
			}

			if( isset( $_POST['LMI_PREREQUEST'] ) ){
				$this->logs('LMI_PREREQUEST');
				die( 'YES' );
			}

			$iSysInvsID = trim(is_param_post('LMI_SYS_INVS_NO'));
			$iSysTransID = trim(is_param_post('LMI_SYS_TRANS_NO'));
			$sSignature = trim(is_param_post('LMI_HASH'));
			$sSysTransDate = trim(is_param_post('LMI_SYS_TRANS_DATE'));

			if(!$sPayerPurse){
				$this->logs('Purse empty');
				die('Purse empty');
			}
	
			$constant = is_deffin($m_defin,'WEBMONEY_WM'. substr( $sPayerPurse, 0, 1 ) .'_PURSE');
			$constant2 = is_deffin($m_defin,'WEBMONEY_WM'. substr( $sPayerPurse, 0, 1 ) .'_KEY');
	
			if( $sSignature != strtoupper( hash( 'sha256', implode(  '', array( $constant, $dPaymentAmount, $iPaymentID, $bPaymentMode, $iSysInvsID, $iSysTransID, $sSysTransDate, $constant2, $sPayerPurse, $iPayerWMID ) ) ) ) ) {
				$this->logs('Invalid control signature');
				die( 'Invalid control signature' );
			}

			// $iPaymentID - номер заказа
			// $dPaymentAmount - сумма платежа
			// $iPayerWMID - WMID плательщика
			// $sPayerPurse - кошелек плательщика
			// $sEmail - E-mail адрес плательщика
			// $iSysInvsID - уникальный номер счета
			// $iSysTransID - уникальный номер транзакции
	
			if(check_trans_in($m_id, $iSysTransID, $iPaymentID)){
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
			
			$pay_purse = is_pay_purse($sPayerPurse, $m_data, $bid_m_id);
			
			$bid_currency = $data['currency'];
			$bid_currency = str_replace(array('WMZ','USD'),'Z',$bid_currency);
			$bid_currency = str_replace(array('RUR','WMR','RUB'),'R',$bid_currency);
			$bid_currency = str_replace(array('WME','EUR'),'E',$bid_currency);
			$bid_currency = str_replace(array('WMU','UAH'),'U',$bid_currency);
			$bid_currency = str_replace(array('WMB','BYR'),'B',$bid_currency);
			$bid_currency = str_replace(array('WMY','UZS'),'Y',$bid_currency);
			$bid_currency = str_replace(array('WMG','GLD'),'G',$bid_currency);
			$bid_currency = str_replace(array('WMX','BTC'),'X',$bid_currency);
			$bid_currency = str_replace(array('WMK','KZT'),'K',$bid_currency);	
			$bid_currency = str_replace(array('WML','LTC'),'L',$bid_currency);	
			$bid_currency = str_replace(array('WMH','BCH'),'H',$bid_currency);	
	
			$bid_sum = is_sum($data['pay_sum'],2);
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
	
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));	
	
			$fl = substr($sPayerPurse, 0, 1 ); 
	
			if($bid_status == 'new'){  
				if($bid_currency == $fl or $invalid_ctype > 0){
					if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){		
					
						$params = array(
							'pay_purse' => $pay_purse,
							'sum' => $in_sum,
							'bid_sum' => $bid_sum,
							'bid_corr_sum' => $bid_corr_sum,
							'bid_status' => array('new'),
							'to_account' => $constant,
							'trans_in' => $iSysTransID,
							'currency' => $fl,
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
						$this->logs($id.' In the application the wrong status');
						die('The payment amount is less than the provisions');
					}
				} else {
					$this->logs($id.' In the application the wrong status');
					die('Wrong type of currency');
				}
			} else {
				$this->logs($id.' In the application the wrong status');
				die( 'In the application the wrong status' );
			}
		}		
	}
}

new merchant_webmoney(__FILE__, 'Webmoney');