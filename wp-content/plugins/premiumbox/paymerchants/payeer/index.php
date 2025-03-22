<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Payeer[:en_US][ru_RU:]Payeer[:ru_RU]
description: [en_US:]Payeer automatic payouts[:en_US][ru_RU:]авто выплаты Payeer[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_payeer')){
	class paymerchant_payeer extends AutoPayut_Premiumbox{
		function __construct($file, $title)
		{
			parent::__construct($file, $title);
		}

		function get_map(){
			$map = array(
				'ACCOUNT_NUMBER'  => array(
					'title' => '[en_US:]Wallet number[:en_US][ru_RU:]Номер кошелька[:ru_RU]',
					'view' => 'input',	
				),
				'API_ID'  => array(
					'title' => '[en_US:]API ID[:en_US][ru_RU:]API ID[:ru_RU]',
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
			$arrs[] = array('ACCOUNT_NUMBER','API_ID','API_KEY');
			return $arrs;
		}

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'error_status');
						
			$n_options = array();
			$n_options[] = array(
				'view' => 'warning',
				'default' => sprintf(__('Use only latin symbols in payment notes. Maximum: %s characters.','pn'), 100),
			);		
			$options = pn_array_insert($options, 'note', $n_options);
			
			return $options;
		}	

		function get_reserve_lists($m_id, $m_defin){
			
			$purses = array(
				$m_id.'_1' => 'EUR',
				$m_id.'_2' => 'USD',
				$m_id.'_3' => 'RUB',
			);
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
				
			$purses = $this->get_reserve_lists($m_id, $m_defin);	
			$purse = trim(is_isset($purses, $code));
			if($purse){
				try {
					
					$payeer = new AP_Payeer(is_deffin($m_defin,'ACCOUNT_NUMBER'), is_deffin($m_defin,'API_ID'), is_deffin($m_defin,'API_KEY'));
					if ($payeer->isAuth())
					{
						$rezerv = '-1';
								
						$arBalance = $payeer->getBalance();
						$rezerv = trim((string)$arBalance['balance'][$purse]['BUDGET']);
								
						if($rezerv != '-1'){
							$sum = $rezerv;
						}								
					} 
						
				}
				catch (Exception $e)
				{
							
				} 				
			}

			return $sum;
		}		

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){
		
			$item_id = $item->id;
			$trans_id = 0;			
			
			$vtype = mb_strtoupper($item->currency_code_get);
			$vtype = str_replace(array('RUR'),'RUB',$vtype);
					
			$enable = array('USD','RUB','EUR');
			if(!in_array($vtype, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}						
						
			$account = $item->account_get;
			$account = mb_strtoupper($account);
			if (!$account) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}							

			$trans_sum = is_paymerch_sum($item, $paymerch_data);
			
			$sum = 0;
			if($trans_sum > 0){
				//$sum = $trans_sum / 0.9905;
				$sum = $trans_sum * 1.0095;
			}

			$sum = is_sum($sum, 2, 'up');
							
			if(count($error) == 0){

				$result = $this->set_ap_status($item, $test);
				if($result){
					
					$notice = get_text_paymerch($m_id, $item);
					if(!$notice){ $notice = sprintf(__('ID order %s','pn'), $item->id); }
					$notice = trim(pn_maxf($notice,100));
						
					try{
						
						$payeer = new AP_Payeer(is_deffin($m_defin,'ACCOUNT_NUMBER'), is_deffin($m_defin,'API_ID'), is_deffin($m_defin,'API_KEY'));
						if ($payeer->isAuth()){
									
							$arTransfer = $payeer->transfer(array(
								'curIn' => $vtype,
								'sum' => $sum,
								'curOut' => $vtype,
								//'to' => 'richkeeper@gmail.com',
								//'to' => '+01112223344',
								'to' => $account,
								'comment' => $notice,
								//'anonim' => 'Y',
								//'protect' => 'Y',
								//'protectPeriod' => '3',
								//'protectCode' => '12345',
							));								
									
							if (empty($arTransfer['errors']) and isset($arTransfer['historyId'])) {
								$trans_id = $arTransfer['historyId'];
							} else {
								$this->logs(print_r($arTransfer, true), $item->id);
								$error[] = __('Payout error','pn');
								$pay_error = 1;
							}								
						} else {
							$pay_error = 1;
							$error[] = 'Error interfaice';
						}
					}
					catch (Exception $e)
					{
						$this->logs(print_r($e->getMessage(), true), $item->id);
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
					'from_account' => is_deffin($m_defin,'ACCOUNT_NUMBER'),
					'trans_out' => $trans_id,
					'system' => 'user',
					'm_place' => $modul_place. ' ' .$m_id,
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

new paymerchant_payeer(__FILE__, 'Payeer');