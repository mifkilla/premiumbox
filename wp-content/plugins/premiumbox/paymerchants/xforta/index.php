<?php
if( !defined( 'ABSPATH')){ exit(); } 

/*
title: [en_US:]Xforta[:en_US][ru_RU:]Xforta[:ru_RU]
description: [en_US:]Xforta automatic payouts[:en_US][ru_RU:]авто выплаты Xforta[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_xforta')){
	class paymerchant_xforta extends AutoPayut_Premiumbox {
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);	
		}		
		
		function get_map(){
			$map = array(
				'TOKEN'  => array(
					'title' => '[en_US:]Token[:en_US][ru_RU:]Token[:ru_RU]',
					'view' => 'input',	
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('TOKEN');
			return $arrs;
		}				
		
		function options($options, $data, $id, $place){ 
			
			$options = pn_array_unset($options, array('checkpay','note'));				
			
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
				$m_id.'_1' => 'RUB' . ' (' . $m_id . ')',
			);	
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			
			if($code == $m_id.'_1'){
				try {
					
					$class = new AP_XForta(is_deffin($m_defin,'TOKEN'));
					$balance = $class->get_balance();
					
					$rezerv = $balance;

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
				
			$vtype = mb_strtoupper($item->currency_code_get);
			$vtype = str_replace('RUR', 'RUB', $vtype);

			$enable = array('RUB');
			if(!in_array($vtype, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}		
					
			$account = str_replace(' ', '', $item->account_get);
			if (!$account) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}		
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2, 'ceil');

			$min = 100;
			
			if($sum < $min){
				$error[] = sprintf(__('Minimum payment amount is %s','pn'), $min);
			}									
						
			if(count($error) == 0){

				$result = $this->set_ap_status($item, $test);				
				if($result){				
						
					try {
						$class = new AP_XForta(is_deffin($m_defin,'TOKEN'));
						$trans_id = $class->send($sum, $account, $item_id);
						if(!$trans_id){
							$error[] = __('Not create order','pn');
							$pay_error = 1;							
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
					pn_display_mess(__('Payment is successfully created. Waiting for confirmation.','pn'),__('Payment is successfully created. Waiting for confirmation.','pn'),'true');
				} 		
			}	
		}

		function cron($m_id, $m_defin, $m_data){
		global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$class = new AP_XForta(is_deffin($m_defin,'TOKEN'));
			
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id' AND currency_code_get IN('RUB','RUR')");
			foreach($items as $item){
				$item_id = $item->id;
				$trans_out = trim($item->trans_out);
				if($trans_out){
					try {
						$res = $class->check($item->id);
						if(isset($res['order_id'], $res['id'], $res['cards']) and $res['order_id'] == $item_id and $res['id'] == $trans_out){
							if(isset($res['cards'][0]['status'])){
								$status = mb_strtoupper($res['cards'][0]['status']);
								
								$st_success = array('STATUS_PAID');
								$st_error = array('STATUS_ERROR');
								if(in_array($status, $st_success)){
									
									$params = array(
										'system' => 'system',
										'bid_status' => array('coldsuccess'),
										'm_place' => 'cron ' .$m_id.'_cron',
										'm_id' => $m_id,
										'm_defin' => $m_defin,
										'm_data' => $m_data,
									);
									set_bid_status('success', $item->id, $params);
									
								} elseif(in_array($status, $st_error)){
									
									$this->reset_cron_status($item, $error_status, $m_id);
									
								}
							}	
						}	
					}
					catch( Exception $e ){
							
					}
				}
			}
		}		
	}
}

new paymerchant_xforta(__FILE__, 'Xforta');