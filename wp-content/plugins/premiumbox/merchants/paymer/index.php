<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Paymer[:en_US][ru_RU:]Paymer[:ru_RU]
description: [en_US:]Paymer merchant[:en_US][ru_RU:]мерчант Paymer[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_paymer')){
	class merchant_paymer extends Merchant_Premiumbox{

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
				'PAYMER_MERCHANT_ID'  => array(
					'title' => '[en_US:]Merchant ID[:en_US][ru_RU:]Уникальный ID магазина[:ru_RU]',
					'view' => 'input',	
				),
				'PAYMER_SECRET_KEY'  => array(
					'title' => '[en_US:]Merchant password[:en_US][ru_RU:]Пароль мерчанта[:ru_RU]',
					'view' => 'input',
				),
				'PAYMER_LOGIN'  => array(
					'title' => '[en_US:]Login from paymer.com/merchant/[:en_US][ru_RU:]Имя пользователя от paymer.com/merchant/[:ru_RU]',
					'view' => 'input',
				),
				'PAYMER_PASSWORD'  => array(
					'title' => '[en_US:]Password from paymer.com/merchant/[:en_US][ru_RU:]Пароль от paymer.com/merchant/[:ru_RU]',
					'view' => 'input',
				),
				'PAYMER_WMZ_PURSE'  => array(
					'title' => '[en_US:]WMZ wallet number[:en_US][ru_RU:]WMZ кошелек[:ru_RU]',
					'view' => 'input',
				),
				'PAYMER_WMR_PURSE'  => array(
					'title' => '[en_US:]WMR wallet number[:en_US][ru_RU:]WMR кошелек[:ru_RU]',
					'view' => 'input',
				),
				'PAYMER_WME_PURSE'  => array(
					'title' => '[en_US:]WME wallet number[:en_US][ru_RU:]WME кошелек[:ru_RU]',
					'view' => 'input',
				),
				'PAYMER_WMU_PURSE'  => array(
					'title' => '[en_US:]WMU wallet number[:en_US][ru_RU:]WMU кошелек[:ru_RU]',
					'view' => 'input',
				),
				'PAYMER_WMB_PURSE'  => array(
					'title' => '[en_US:]WMB wallet number[:en_US][ru_RU:]WMB кошелек[:ru_RU]',
					'view' => 'input',
				),
				'PAYMER_WMY_PURSE'  => array(
					'title' => '[en_US:]WMY wallet number[:en_US][ru_RU:]WMY кошелек[:ru_RU]',
					'view' => 'input',
				),
				'PAYMER_WMG_PURSE'  => array(
					'title' => '[en_US:]WMG wallet number[:en_US][ru_RU:]WMG кошелек[:ru_RU]',
					'view' => 'input',
				),
				'PAYMER_WMX_PURSE'  => array(
					'title' => '[en_US:]WMX wallet number[:en_US][ru_RU:]WMX кошелек[:ru_RU]',
					'view' => 'input',
				),
				'PAYMER_WMK_PURSE'  => array(
					'title' => '[en_US:]WMK wallet number[:en_US][ru_RU:]WMK кошелек[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('PAYMER_MERCHANT_ID','PAYMER_SECRET_KEY');
			return $arrs;
		}		

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, 'show_error');
			$options = pn_array_unset($options, 'check_api');
			
			$options['private_line'] = array(
				'view' => 'line',
			);			
			
			$options['redeem'] = array(
				'view' => 'select',
				'title' => __('Automatic redemption','pn'),
				'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
				'default' => is_isset($data, 'redeem'),
				'name' => 'redeem',
				'work' => 'int',
			);			
			
			$text = '
			<div><strong>RETURN URL:</strong> <a href="'. get_mlink($id.'_status' . hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_status' . hash_url($id)) .'</a></div>
			<div><strong>SUCCESS URL:</strong> <a href="'. get_mlink($id.'_success') .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_success') .'</a></div>
			<div><strong>FAIL URL:</strong> <a href="'. get_mlink($id.'_fail') .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_fail') .'</a></div>			
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
				$currency = str_replace('USD','WMZ',$currency);
				$currency = str_replace(array('RUR','RUB'),'WMR',$currency);
				$currency = str_replace('EUR','WME',$currency);
				$currency = str_replace('UAH','WMU',$currency);
				
				$pay_sum = is_sum($pay_sum,2);					
				$text_pay = get_text_pay($m_id, $item, $pay_sum);
						
				$temp = '
				<form action="https://www.paymer.com/merchant/pay/merchant.aspx?lang=ru-RU" method="post">
					<input name="PM_PAYMERCH_ID" type="hidden" value="'. is_deffin($m_defin,'PAYMER_MERCHANT_ID') .'" />
					<input name="PM_PAYMENT_NO" type="hidden" value="'. $item->id .'" />
					<input name="PM_PAYMENT_AMOUNT" type="hidden" value="'. $pay_sum .'" />
					<input name="PM_PAYMENT_ATYPE" type="hidden" value="'. $currency .'" />
					<input name="PM_PAYMENT_DESC" type="hidden" value="'. $text_pay .'"  />
					<input type="submit" value="'. __('Make a payment','pn') .'" />
				</form>													
				';	
			
			}
			return $temp;				
		}

		function merchant_fail(){
			$id = get_payment_id('PM_PAYMENT_NO');
			redirect_merchant_action($id, $this->name);
		}

		function merchant_success(){
			$id = get_payment_id('PM_PAYMENT_NO');
			redirect_merchant_action($id, $this->name, 1);
		}

		function redeem_request($vtype, $trans_id, $m_defin){
			$reply = 0;
			$post_data = array();
			$post_data['PMS_LOGIN'] = is_deffin($m_defin,'PAYMER_LOGIN');
			$post_data['PMS_PASSWORD'] = is_deffin($m_defin,'PAYMER_PASSWORD');
			$post_data['PMS_TRANS_NO'] = $trans_id;
			$post_data['PMS_PURSE'] = is_deffin($m_defin,'PAYMER_'. $vtype .'_PURSE');
			
			$c_options = array(
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $post_data,
			);
			$result = get_curl_parser('https://www.paymer.com/merchant/pay/redeem.aspx', $c_options, 'merchant', 'paymer');
			
			$err  = $result['err'];
			$out = $result['output'];
			if(!$err){
				if(strstr($out,'<pms.response>')){
					$object = @simplexml_load_string($out);
					if(is_object($object) and isset($object->error)){
						$error = intval($object->error);
						if($error < 1){
							$reply = 1;
						}
					}
				}
				$this->logs($out);
			} else {
				$this->logs('curl error:' . $err);
			}				
				return $reply;
		}
		
		function merchant_status(){
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);	
	
			$iOrderID = isset( $_POST['PM_PAYMENT_NO'] ) ? $_POST['PM_PAYMENT_NO'] - 0 : 0;
			$iMerchantID = isset( $_POST['PM_PAYMERCH_ID'] ) ? $_POST['PM_PAYMERCH_ID'] - 0 : 0;
			$currency = isset( $_POST['PM_PAYMENT_ATYPE'] ) ? $_POST['PM_PAYMENT_ATYPE'] : null;
			$dAmount = isset( $_POST['PM_PAYMENT_AMOUNT'] ) ? $_POST['PM_PAYMENT_AMOUNT'] : 0;
			$iTestMode = isset( $_POST['PM_PAYTEST_MODE'] ) ? $_POST['PM_PAYTEST_MODE'] - 0 : 0;
			$iTransNo = isset( $_POST['PM_PAYSYS_TRANS_NO'] ) ? $_POST['PM_PAYSYS_TRANS_NO'] - 0 : 0;
			$sTransDate = isset( $_POST['PM_PAYSYS_TRANS_DATE'] ) ? $_POST['PM_PAYSYS_TRANS_DATE'] : null;
			$sSignature = isset( $_POST['PM_PAYHASH'] ) ? $_POST['PM_PAYHASH'] : null;

			if( $iMerchantID != is_deffin($m_defin,'PAYMER_MERCHANT_ID') ){
				$this->logs('bad merchant id');
				die( 'bad merchant id' );
			}

			if( $iTestMode != 0 ){
				$this->logs('bad test mode');
				die( 'bad test mode' );
			}

			if( $sSignature != strtoupper( md5( $iMerchantID.$dAmount.$currency.$iOrderID.$iTestMode.$iTransNo.$sTransDate. is_deffin($m_defin,'PAYMER_SECRET_KEY') ) ) ){
				$this->logs('bad signature');
				die( 'bad signature' );
			}

			// $iOrderID - № заказа
			// $dAmount - сумма
			// $currency - валюта
			// $iTransNo - уникальный № транзакции

			if(check_trans_in($m_id, $iTransNo, $iOrderID)){
				$this->logs('Error check trans in!');
				die('Error check trans in!');
			}			
			
			$id = $iOrderID;
			$data = get_data_merchant_for_id($id);
			
			$in_sum = $dAmount;
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
			
			$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
			
			$bid_currency = $data['currency'];
			$bid_currency = str_replace('USD','WMZ',$bid_currency);
			$bid_currency = str_replace(array('RUR','RUB'),'WMR',$bid_currency);
			$bid_currency = str_replace('EUR','WME',$bid_currency);
			$bid_currency = str_replace('UAH','WMU',$bid_currency);		
			
			$to_account = is_deffin($m_defin,'PAYMER_'. $bid_currency .'_PURSE');
			
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
								 
						$redeem = intval(is_isset($m_data, 'redeem'));
						if($redeem == 1){
							$redeem_res = $this->redeem_request($bid_currency, $iTransNo, $m_defin);
							if($redeem_res == 1){
								$now_status = 'realpay';										
							}
						}
								
						$params = array(
							'pay_purse' => $pay_purse,
							'sum' => $in_sum,
							'bid_sum' => $bid_sum,
							'bid_corr_sum' => $bid_corr_sum,
							'bid_status' => array('new','techpay','coldpay'),
							'to_account' => $to_account,
							'trans_in' => $iTransNo,
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
											
						die( 'Completed' );
								
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

new merchant_paymer(__FILE__, 'Paymer');