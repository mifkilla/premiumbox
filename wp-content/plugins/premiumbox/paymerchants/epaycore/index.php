<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]EpayCore[:en_US][ru_RU:]EpayCore[:ru_RU]
description: [en_US:]EpayCore automatic payouts[:en_US][ru_RU:]авто выплаты EpayCore[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_epaycore')){
	class paymerchant_epaycore extends AutoPayut_Premiumbox{
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);			
		}	
		
		function get_map(){
			$map = array(
				'API_ID'  => array(
					'title' => '[en_US:]Api id[:en_US][ru_RU:]Api id[:ru_RU]',
					'view' => 'input',	
				),
				'API_SECRET'  => array(
					'title' => '[en_US:]Api secret[:en_US][ru_RU:]Api secret[:ru_RU]',
					'view' => 'input',	
				),
				'U_ACCOUNT'  => array(
					'title' => '[en_US:]account number (USD)[:en_US][ru_RU:]Номер счета (USD)[:ru_RU]',
					'view' => 'input',	
				),
				'R_ACCOUNT'  => array(
					'title' => '[en_US:]account number (RUB)[:en_US][ru_RU:]Номер счета (RUB)[:ru_RU]',
					'view' => 'input',	
				),
				'H_ACCOUNT'  => array(
					'title' => '[en_US:]account number (UAH)[:en_US][ru_RU:]Номер счета (UAH)[:ru_RU]',
					'view' => 'input',	
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('API_ID','API_SECRET');
			return $arrs;
		}

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, array('checkpay'));							
			
			$text = '
			<div><strong>CRON:</strong> <a href="'. get_mlink('ap_'. $id .'_cron' . hash_url($id, 'ap')) .'" target="_blank" rel="noreferrer noopener">'. get_mlink('ap_'. $id .'_cron' . hash_url($id, 'ap')) .'</a></div>
			';
			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;
		}				

		function get_reserve_lists($m_id, $m_defin){
			$purses = array();
			$purses[$m_id . '_usd'] = 'USD -' . is_deffin($m_defin, 'U_ACCOUNT');
			$purses[$m_id . '_rub'] = 'RUB -' . is_deffin($m_defin, 'R_ACCOUNT');
			$purses[$m_id . '_uah'] = 'UAH -' . is_deffin($m_defin, 'H_ACCOUNT');			
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			
			$purse = trim(str_replace($m_id . '_','',$code));
			if($purse){
						
				$account = '';
				if($purse == 'usd'){
					$account = is_deffin($m_defin, 'U_ACCOUNT');
				} elseif($purse == 'rub'){
					$account = is_deffin($m_defin, 'R_ACCOUNT');
				} elseif($purse == 'uah'){
					$account = is_deffin($m_defin, 'H_ACCOUNT');
				}
				
				if($account){	
					try {
						
						$class = new AP_EpayCore($this->name, $m_id, is_deffin($m_defin, 'API_ID'), is_deffin($m_defin, 'API_SECRET'));
						$rezerv = $class->get_balance($account);
										
						if($rezerv != '-1'){
							$sum = $rezerv;
						}								 
							
					}
					catch (Exception $e)
					{
								
					} 				
				}
			}
			
			return $sum;
		}		

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){
			
			$item_id = $item->id;
			$trans_id = 0;			
			
			$currency = mb_strtoupper($item->currency_code_get);
			$currency = str_replace('RUR','RUB',$currency);

			$before_account = '';
			if($currency == 'USD'){
				$before_account = is_deffin($m_defin, 'U_ACCOUNT');
			} elseif($currency == 'RUB'){
				$before_account = is_deffin($m_defin, 'R_ACCOUNT');
			} elseif($currency == 'UAH'){
				$before_account = is_deffin($m_defin, 'H_ACCOUNT');
			}			
					
			$account = $item->account_get;
			if (!$account) {
				$error[] = __('No client wallet','pn');
			}					
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
					
			if(count($error) == 0){
				$result = $this->set_ap_status($item, $test);
				if($result){	

					$notice = get_text_paymerch($m_id, $item, $sum);
					if(!$notice){ $notice = sprintf(__('Order ID %s','pn'), $item->id); }
					$notice = trim(pn_maxf($notice, 200));
				
					try {
						$class = new AP_EpayCore($this->name, $m_id, is_deffin($m_defin, 'API_ID'), is_deffin($m_defin, 'API_SECRET'));
						$res = $class->payout($before_account, $account, $sum, $notice, $item_id);
						if($res['error'] == 1){
							$error[] = __('Payout error','pn');
							$pay_error = 1;
						} else {
							$trans_id = $res['trans_id'];
						}	
					}
					catch (Exception $e)
					{
						$error[] = $e;
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
					'trans_out' => $trans_id,
					'system' => 'user',
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('coldsuccess', $item_id, $params, $direction); 					
						 
				if($place == 'admin'){
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
				} 
			}			
		}

		function cron($m_id, $m_defin, $m_data){
		global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$class = new AP_EpayCore($this->name, $m_id, is_deffin($m_defin, 'API_ID'), is_deffin($m_defin, 'API_SECRET'));
			$orders = $class->get_history_payout(50);
			
			if(is_array($orders) and count($orders) > 0){
				$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
				foreach($items as $item){
					$item_id = $item->id;
					$trans_out = $item->trans_out;
					if(isset($orders[$trans_out])){
						$order = $orders[$trans_out];
						$check_status = intval($order['status']);
						
						if($check_status == 4){
							
							$params = array(
								'system' => 'system',
								'bid_status' => array('coldsuccess'),
								'm_place' => 'cron ' .$m_id,
								'm_id' => $m_id,
								'm_defin' => $m_defin,
								'm_data' => $m_data,
							);
							set_bid_status('success', $item->id, $params);
								
						} elseif($check_status == 2){
							
							$this->reset_cron_status($item, $error_status, $m_id);
								
						}	
					}
				}
			}			
		}		
	}
}
new paymerchant_epaycore(__FILE__, 'EpayCore');