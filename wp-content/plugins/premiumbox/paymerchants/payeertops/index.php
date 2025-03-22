<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Payeer (withdraw to PS)[:en_US][ru_RU:]Payeer (вывод на ПС)[:ru_RU]
description: [en_US:]Payeer (withdraw to PS) automatic payouts[:en_US][ru_RU:]авто выплаты Payeer (вывод на платежные системы, карты и т.п.)[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_payeertops')){
	class paymerchant_payeertops extends AutoPayut_Premiumbox{
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);	
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
			$options = pn_array_unset($options, 'note');
			
			if($place == 1){
				$m_defin = $this->get_file_data($id);
				try {
					$types = array();
					$types[0] = '--' . __('No','pn') . '--';
					$payeer = new AP_Payeer(is_deffin($m_defin,'ACCOUNT_NUMBER'), is_deffin($m_defin,'API_ID'), is_deffin($m_defin,'API_KEY'));
					$res = array();
					if ($payeer->isAuth())
					{
						$res = $payeer->getPaySystems();
							if(isset($res['list']) and is_array($res['list'])){
							foreach($res['list'] as $res_id => $res_data){
								$types[$res_id] = is_isset($res_data,'name') . ' [' . $res_id . ']';
							}
						}
					}
					$options[] = array(
						'view' => 'select',
						'title' => __('Transaction type','pn'),
						'options' => $types,
						'default' => is_isset($data, 'payment_type'),
						'name' => 'payment_type',
						'work' => 'input',
					);		
					/* 				
					$options['help_payment_type'] = array(
						'view' => 'help',
						'title' => __('More info','pn'),
						'default' => print_r($res, true),
					); 
					*/				
				}
				catch (Exception $e)
				{
					$options[] = array(
						'view' => 'textfield',
						'title' => '',
						'default' => $e,
					);							
				}
			} else {
				$options[] = array(
					'view' => 'select',
					'options' => array(),
					'default' => is_isset($data, 'payment_type'),
					'name' => 'payment_type',
					'work' => 'input',
				);					
			}
			
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
				try{
					
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
			if (!$account) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}					
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data));
					
			$payment_type = is_sum(is_isset($paymerch_data, 'payment_type'));
			if($payment_type == 0){
				$error[] = __('Transaction type is not selected','pn');
			}
				
			if(count($error) == 0){

				$result = $this->set_ap_status($item, $test);
				if($result){				
					try {
						$payeer = new AP_Payeer(is_deffin($m_defin,'ACCOUNT_NUMBER'), is_deffin($m_defin,'API_ID'), is_deffin($m_defin,'API_KEY'));
						if ($payeer->isAuth()){
									
							$arr = array();
							$arr['ps'] = $payment_type;
							$arr['curIn'] = $vtype;
							$arr['sumOut'] = $sum;
							$arr['curOut'] = $vtype;
							$arr['param_ACCOUNT_NUMBER'] = $account;
							$arTransfer = $payeer->output($arr);
									
							if (empty($arTransfer['errors']) and isset($arTransfer['historyId'])){
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
				set_bid_status('coldsuccess', $item_id, $params,$direction); 					
						 
				if($place == 'admin'){
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
				} 
			}			
		}

		function cron($m_id, $m_defin, $m_data){
		global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
			foreach($items as $item){
				$trans_id = trim($item->trans_out);
				if($trans_id){
					try {
						$payeer = new AP_Payeer(is_deffin($m_defin,'ACCOUNT_NUMBER'), is_deffin($m_defin,'API_ID'), is_deffin($m_defin,'API_KEY'));
						if($payeer->isAuth()){
							$arTransfer = $payeer->getHistoryInfo($trans_id);
							if (empty($arTransfer['errors']) and isset($arTransfer['info'])){
								$check_status = trim(is_isset($arTransfer['info'],'status'));
								if($check_status == 'execute'){
									
									$params = array(
										'system' => 'system',
										'bid_status' => array('coldsuccess'),
										'm_place' => 'cron ' .$m_id .'_cron',
										'm_id' => $m_id,
										'm_defin' => $m_defin,
										'm_data' => $m_data,
									);
									set_bid_status('success', $item->id, $params);
									
								} elseif($check_status != 'process' and $check_status != 'wait'){
									
									$this->reset_cron_status($item, $error_status, $m_id);
									
								}	
							}
						}	
					}
					catch( Exception $e ) {
										
					}
				}
			}
			
			/*
			execute - выполнен (конечный статус)
			process - в процессе выполнения (изменится на execute, cancel или hold)
			cancel - отменен (конечный статус)
			wait - в ожидании (например в ожидании оплаты) (изменится на execute, cancel или hold)
			hold - приостановлен (изменится на execute, cancel)
			black_list - операция остановлена из-за попадание под фильтр блэк-листа (может измениться на execute, cancel или hold)
			*/			
		}		
	}
}
new paymerchant_payeertops(__FILE__, 'Payeer to ps');