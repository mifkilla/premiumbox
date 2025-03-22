<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]E-Pay[:en_US][ru_RU:]E-Pay[:ru_RU]
description: [en_US:]E-Pay automatic payouts[:en_US][ru_RU:]авто выплаты E-Pay[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_epay')){
	class paymerchant_epay extends AutoPayut_Premiumbox{
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title);
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
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'error_status');
			
			$n_options = array();
			$n_options['warning'] = array(
				'view' => 'warning',
				'default' => sprintf(__('Use only latin symbols in payment notes. Maximum: %s characters.','pn'), 100),
			);						
			$options = pn_array_insert($options, 'note', $n_options);
									
			$opt = array(
				'0' => __('E-Pay','pn'),
				'1' => __('Perfect Money','pn'),
				'2' => __('Webmoney','pn'),
				'4' => __('Payeer','pn'),
				'5' => __('AdvCash','pn'),
				'7' => __('PayPal','pn'),
				'8' => __('FasaPay','pn'),
			);
			$options['variant'] = array(
				'view' => 'select',
				'title' => __('Transaction type','pn'),
				'options' => $opt,
				'default' => intval(is_isset($data, 'variant')),
				'name' => 'variant',
				'work' => 'int',
			);					
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin){
			
			$purses = array(
				$m_id.'_1' => 'USD',
				$m_id.'_2' => 'EUR',
				$m_id.'_3' => 'HKD',
				$m_id.'_4' => 'GBP',
				$m_id.'_5' => 'JPY',
			);
			
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
				
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if($purse){	
				try{
					$class = new AP_EPay(is_deffin($m_defin,'PAYEE_ACCOUNT'),is_deffin($m_defin,'PAYEE_NAME'),is_deffin($m_defin,'API_KEY'));
					$res = $class->getBalans();
					if(is_array($res)){
								
						$rezerv = '-1';
								
						foreach($res as $pursename => $amount){
							if( $pursename == $purse ){
								$rezerv = trim((string)$amount);
								break;
							}
						}
								
						if($rezerv != '-1'){
							$sum = $rezerv;
						}							
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
			
			$item_id = $item->id;
			$trans_id = 0;				
			
			$variant = intval(is_isset($paymerch_data,'variant'));
			
			$vtype = mb_strtoupper($item->currency_code_get);
					
			$enable = array('USD', 'EUR', 'HKD', 'GBP', 'JPY');
			if(!in_array($vtype, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}						
						
			$account = $item->account_get;
			if (!$account){
				$error[] = __('Client wallet type does not match with currency code','pn') . ','. $account;						
			}
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
		
			$pay_status = 0;
				
			if(count($error) == 0){

				$result = $this->set_ap_status($item, $test);	
				if($result){
					
					$notice = get_text_paymerch($m_id, $item);
					if(!$notice){ $notice = sprintf(__('ID order %s','pn'), $item->id); }
					$notice = trim(pn_maxf($notice,100));
						
					try {
						
						$class = new AP_EPay(is_deffin($m_defin,'PAYEE_ACCOUNT'),is_deffin($m_defin,'PAYEE_NAME'),is_deffin($m_defin,'API_KEY'));
						if($variant == 0){
							$res = $class->SendMoney($vtype, $account, $sum, $item_id, $notice);
						} else {
							$res = $class->ESendMoney($vtype, $account, $sum, $item_id, $notice, $variant);
						}
						if($res['error'] == 1){
							$error[] = __('Payout error','pn');
							$pay_error = 1;
						} else {
							$trans_id = $res['trans_id'];
							$pay_status = $res['trans_status'];
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
					'from_account' => is_deffin($m_defin,'PAYEE_ACCOUNT'),
					'trans_out' => $trans_id,
					'system' => 'user',
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);						
						
				if($pay_status == 1){
						
					set_bid_status('success', $item_id, $params, $direction);  
					
					if($place == 'admin'){
						pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
					}							
				} else {
						
					set_bid_status('coldsuccess', $item_id, $params, $direction);
						
					if($place == 'admin'){
						pn_display_mess(__('Payment is successfully created. Waiting for confirmation from E-pay.','pn'),__('Payment is successfully created. Waiting for confirmation from E-pay.','pn'),'true');
					}							
				}
						
			}
		}				
		
	}
}
new paymerchant_epay(__FILE__, 'E-Pay');