<?php
/*
title: [en_US:]PerfectMoney[:en_US][ru_RU:]PerfectMoney[:ru_RU]
description: [en_US:]Checking account for verification[:en_US][ru_RU:]Проверка кошелька на верификацию[:ru_RU]
version: 2.2
*/

if(!class_exists('Wchecks_Premiumbox')){ return; }

if(!class_exists('wchecks_perfectmoney')){
	class wchecks_perfectmoney extends Wchecks_Premiumbox {
		function __construct($file, $title)
		{
			$map = array();
			parent::__construct($file, $map, $title);						
		}
		
		function set_check_account($ind, $purse, $check_id){
			if($ind == 0 and $check_id and $check_id == $this->name){
				$result = $this->check_purse($purse);
				if(isset($result['type'])){
					if($result['type'] == 'verified'){
						return 1;
					}
				} 	
			}
			return $ind;
		} 

		function premium_action_test(){
			only_post();
			pn_only_caps(array('administrator','pn_merchants'));			
			
			$purse = pn_maxf_mb(pn_strip_input(is_param_post('purse')),250);
			$result = $this->check_purse($purse);
			if(isset($result['type'])){
				pn_display_mess($result['type'],$result['type'],'true');
			} else {
				pn_display_mess(__('Script is unable to determine the type','pn'));
			}
		}		
		
		function check_purse($purse, $r=1){
			
			$purse = strtoupper($purse);
			$fz = substr($purse, 0, 1 );
			if($fz == 'G'){
				$currency = 'OAU';
			} elseif($fz == 'E'){
				$currency = 'EUR';
			} elseif($fz == 'B'){
				$currency = 'BTC';				
			} else {
				$currency = 'USD';
			}			
			
			$perfetcmoney_domain = apply_filters('perfetcmoney_domain', 'perfectmoney.is');
			$set_cookie='details=1920x1200;Path=/;Domain='. $perfetcmoney_domain .';expires=Mon, 13-Oct-24 13:08:16 GMT';
			
			$my_dir = wp_upload_dir();
			$path = $my_dir['basedir'].'/';			
			
			$c_options = array(
				CURLOPT_COOKIEFILE => $path . 'pmcheck_cookie.txt',
				CURLOPT_COOKIEJAR => $path . 'pmcheck_cookie.txt',
				CURLOPT_COOKIE => $set_cookie,
				CURLOPT_FAILONERROR => 1,
				CURLOPT_FOLLOWLOCATION => 0,
				CURLOPT_HEADER => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 10,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => "PAYEE_ACCOUNT=". $purse ."&PAYEE_NAME=check_pm&PAYMENT_AMOUNT=0.1&PAYMENT_UNITS=". $currency ."&PAYMENT_ID=1&PAYMENT_URL=http://check_pm.ru&NOPAYMENT_URL=http://check_pm.ru",
			);
			
			$perfetcmoney_domain = apply_filters('perfetcmoney_domain', 'perfectmoney.is');
			$result = get_curl_parser('https://'. $perfetcmoney_domain .'/api/step1.asp', $c_options, 'wchecks', 'perfectmoney');
			$out = $result['output'];
			if(mb_strpos($out,'Account type:')){
				$out = mb_substr($out,mb_strpos($out,'Account type:'),mb_strlen($out));
				$out = mb_substr($out,mb_strpos($out,'<font'),mb_strlen($out));
				$out = mb_substr($out,0,mb_strpos($out,' Trust Score'));
				$out = strip_tags($out);
				$out = explode(',',$out);
				return array("type" => strtolower($out[0]), "TS" => (float)$out[1]);
			} else {
				if($r > 5){
					return array("error"=>"not account type");
				} else {
					return $this->check_purse($purse, ($r+1));
				}
			}
		}
	}
}

new wchecks_perfectmoney(__FILE__, 'Perfectmoney');