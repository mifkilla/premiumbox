<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Adgroup[:en_US][ru_RU:]Adgroup[:ru_RU]
description: [en_US:]Adgroup automatic payouts[:en_US][ru_RU:]авто выплаты Adgroup[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_adgroup')){
	class paymerchant_adgroup extends AutoPayut_Premiumbox{ 
		function __construct($file, $title)
		{
			parent::__construct($file, $title);
		}

		function get_map(){
			$map = array(
				'CLIENT_ID'  => array(
					'title' => '[en_US:]Client ID[:en_US][ru_RU:]Client ID[:ru_RU]',
					'view' => 'input',	
				),
				'CLIENT_SECRET'  => array(
					'title' => '[en_US:]Client Secret[:en_US][ru_RU:]Client Secret[:ru_RU]',
					'view' => 'input',
				),
				'CLIENT_PIN'  => array(
					'title' => '[en_US:]Account password[:en_US][ru_RU:]Пароль от аккаунта[:ru_RU]',
					'view' => 'input',
				),
				'USER_ID'  => array(
					'title' => '[en_US:]User ID[:en_US][ru_RU:]User ID[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('CLIENT_ID','CLIENT_SECRET','CLIENT_PIN','USER_ID');
			return $arrs;
		}

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'error_status');

			$options['now_pay_method'] = array(
				'view' => 'select',
				'title' => __('Transaction type','pn'),
				'options' => array('0'=> __('Card','pn'),'1'=> __('Qiwi','pn'),'2'=> __('Webmoney','pn'),'3'=> __('Yandex Money','pn'),'4'=> __('Yandex Money 2','pn'), '5' => __('Mobile phone','pn')),
				'default' => is_isset($data, 'now_pay_method'),
				'name' => 'now_pay_method',
				'work' => 'input',
			);	
			$options['now_country_code'] = array(
				'view' => 'input',
				'title' => __('Country code','pn'),
				'default' => is_isset($data, 'now_country_code'),
				'name' => 'now_country_code',
				'work' => 'input',
			);			
			
			return $options;
		}	

		function get_reserve_lists($m_id, $m_defin){
			$purses = array(
				$m_id . '_1' => 'RUB',
				$m_id . '_2' => 'KZT',
			);	
			return $purses;
		}
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
					
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if($purse){
				try {					
					$class = new AP_ADGROUP_API(is_deffin($m_defin,'CLIENT_ID'), is_deffin($m_defin,'CLIENT_SECRET'), is_deffin($m_defin,'CLIENT_PIN'));
					$balances = $class->get_balances(is_deffin($m_defin,'USER_ID'));					
					
					$rezerv = '-1';
								
					foreach($balances as $pursename => $amount){
						if( $pursename == $purse ){
							$rezerv = trim((string)$amount);
							break;
						}
					}
							
					if($rezerv != '-1'){
						$sum = $rezerv;
					}						
				}
				catch (Exception $e)
				{
					$this->logs($e->getMessage());				
				} 				
			}
			
			return $sum;
		}		
		
		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){

			$trans_id = 0;
			$item_id = $item->id;
			
			$currency_code_get = mb_strtoupper($item->currency_code_get);
			$currency_code_get = str_replace('RUR','RUB',$currency_code_get);
					
			$enable = array('RUB','KZT');
			if(!in_array($currency_code_get, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}		

			$account = $item->account_get;
						
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
			
			$now_pay_method = intval(is_isset($paymerch_data, 'now_pay_method'));
			if(!$now_pay_method){ $now_pay_method = 0; }

			$now_country_code = trim(is_isset($paymerch_data, 'now_country_code'));
			
			$minsum = '0';
			if($now_pay_method == 0){
				$minsum = '3500';
			}
			if($now_pay_method == 2){
				$minsum = '10';
			}
			if($sum < $minsum){
				$error[] = sprintf(__('Minimum payment amount is %s','pn'), $minsum);
			}			
				
			$sender_fname = trim(is_isset($unmetas,'sender_fname'));	
			$sender_lname = trim(is_isset($unmetas,'sender_lname'));
			$sender_address = trim(is_isset($unmetas,'sender_address'));
			$sender_city = trim(is_isset($unmetas,'sender_city'));
			$sender_country = trim(is_isset($unmetas,'sender_country'));
			$receiver_fname = trim(is_isset($unmetas,'receiver_fname'));
			$receiver_lname = trim(is_isset($unmetas,'receiver_lname'));
			$country_code = trim(is_isset($unmetas,'country_code'));
			if(!$country_code){ $country_code = $now_country_code; }
				
			if(count($error) == 0){
				$result = $this->set_ap_status($item, $test);	
				if($result){
					try {

						$class = new AP_ADGROUP_API(is_deffin($m_defin,'CLIENT_ID'), is_deffin($m_defin,'CLIENT_SECRET'), is_deffin($m_defin,'CLIENT_PIN'));
						$res = $class->send_money($account, $sum, $currency_code_get, $now_pay_method, $sender_fname, $sender_lname, $sender_address, $sender_city, $sender_country, $receiver_fname, $receiver_lname, $country_code);
						
						if($res['error'] == 1){
							$error[] = __('Payout error','pn');
							$pay_error = 1;
						} else {
							$trans_id = $res['trans_id'];
						}					
							
					}
					catch (Exception $e)
					{
						$error[] = $e->getMessage();
						$pay_error = 1;
					} 
				} else {
					$error[] = 'Database error'; 
				}		
			}		
			
			if(count($error) > 0){
				
				$this->reset_ap_status($error, $pay_error, $item, $place, $test);
							
			} else { 
						
				$params = array(
					'from_account' => is_deffin($m_defin,'CLIENT_ID'),
					'trans_out' => $trans_id,
					'm_place' => $modul_place . ' ' . $m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('success', $item_id, $params, $direction); 
						
				if($place == 'admin'){
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
				} 		
			}								
		}			
	}
}

new paymerchant_adgroup(__FILE__, 'Adgroup');