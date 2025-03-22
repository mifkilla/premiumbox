<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Qiwi new[:en_US][ru_RU:]Qiwi new[:ru_RU]
description: [en_US:]Qiwi new automatic payouts[:en_US][ru_RU:]авто выплаты Qiwi new[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_qiwinew')){
	class paymerchant_qiwinew extends AutoPayut_Premiumbox{
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
		}

		function get_map(){
			$map = array(
				'API_TOKEN_KEY'  => array(
					'title' => '[en_US:]Token[:en_US][ru_RU:]Токен[:ru_RU]',
					'view' => 'input',	
				),
				'API_WALLET'  => array(
					'title' => '[en_US:]Qiwi wallet number without +[:en_US][ru_RU:]Номер кошелька Qiwi без +[:ru_RU]',
					'view' => 'input',	
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('API_TOKEN_KEY','API_WALLET');
			return $arrs;
		}		

		function options($options, $data, $id, $place){
						
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'error_status');

			$options['qiwi_pay_method'] = array(
				'view' => 'select',
				'title' => __('Transaction type','pn'),
				'options' => array('0'=>'Qiwi Wallet','1963'=>'Visa(RU)','21013'=>'MasterCard(RU)','22351'=>'QIWI Visa Card', '31652' => 'Mir Card','100' => 'Visa(RU)/MasterCard(RU)'),
				'default' => is_isset($data, 'qiwi_pay_method'),
				'name' => 'qiwi_pay_method',
				'work' => 'input',
			);

			$text = '
			<div><strong>CRON:</strong> <a href="'. get_mlink('ap_'. $id .'_cron' . hash_url($id, 'ap')) .'" target="_blank">'. get_mlink('ap_'. $id .'_cron' . hash_url($id, 'ap')) .'</a></div>
			';
			$options[] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;
		}	

		function get_reserve_lists($m_id, $m_defin){
			$purses = array(
				$m_id.'_1' => 'RUB',
			);	
			return $purses;
		}

		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
					
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if($purse){
				try {							
					$class = new AP_QIWI_API($m_id, is_deffin($m_defin,'API_WALLET'), is_deffin($m_defin,'API_TOKEN_KEY'));
					$balances = $class->get_balances();					
					
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
							
				} 				
			}
			
			return $sum;
		}		
		
		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){

			$trans_id = 0;
			$item_id = $item->id;
			
			$currency = mb_strtoupper($item->currency_code_get);
			$currency = str_replace('RUR','RUB',$currency);
					
			$enable = array('RUB');
			if(!in_array($currency, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}		

			$account = str_replace('+','',$item->account_get);
						
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);

			$minsum = '1';
			if($sum < $minsum){
				$error[] = sprintf(__('Minimum payment amount is %s','pn'), $minsum);
			}			
				
			$qiwi_pay_method = intval(is_isset($paymerch_data, 'qiwi_pay_method'));
			if(!$qiwi_pay_method){ $qiwi_pay_method = 99; }
			
			if($qiwi_pay_method == 99){
				$account = '+' . $account;
			}
			
			if($qiwi_pay_method == 100){
				$scheme = trim(is_isset($item, 'card_scheme'));
				if($scheme == 'visa'){
					$qiwi_pay_method = 1963;
				} elseif($scheme == 'mastercard' or $scheme == 'maestro') {
					$qiwi_pay_method = 21013;
				} else {
					$error[] = __('Card is neither a VISA nor a MasterCard','pn');
				}
			}
				
			if(count($error) == 0){
					
				$result = $this->set_ap_status($item, $test);	
				if($result){
					
					$notice = get_text_paymerch($m_id, $item);
					$notice = trim(pn_maxf($notice,100));
						
					try {

						$class = new AP_QIWI_API($m_id, is_deffin($m_defin,'API_WALLET'), is_deffin($m_defin,'API_TOKEN_KEY'));
						$res = $class->send_money($account, $sum, $qiwi_pay_method, $notice);
						
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
					'from_account' => is_deffin($m_defin,'API_WALLET'),
					'trans_out' => $trans_id,
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				if($qiwi_pay_method == 99){
					set_bid_status('success', $item_id, $params, $direction);
				} else {
					set_bid_status('coldsuccess', $item_id, $params, $direction);
				}
							
				if($place == 'admin'){
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
				} 
							
			}								
		}
		
		function cron($m_id, $m_defin, $m_data){
		global $wpdb;
			
			$orders = array();
			
			try {
				$class = new AP_QIWI_API($m_id, is_deffin($m_defin,'API_WALLET'), is_deffin($m_defin,'API_TOKEN_KEY'));
				$orders = $class->get_history(date('c',strtotime('-30 days')),date('c',strtotime('+1 day')));
			}
			catch( Exception $e ) {
										
			}	

			if(is_array($orders)){
				$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
				foreach($items as $item){
					$currency = mb_strtoupper($item->currency_code_get);
					$trans_id = trim($item->trans_out);
					if($trans_id){
						if(isset($orders[$trans_id])){
							$check_status = mb_strtolower($orders[$trans_id]['status']);
							if($check_status == 'success'){
								$params = array(
									'system' => 'system',
									'bid_status' => array('coldsuccess'),
									'm_place' => 'cron ' .$m_id .'_cron',
									'm_id' => $m_id,
									'm_defin' => $m_defin,
									'm_data' => $m_data,
								);
								set_bid_status('success', $item->id, $params);														
							} 
						}
					}
				}
			}
		}		
						
	}
}

new paymerchant_qiwinew(__FILE__, 'Qiwi new');