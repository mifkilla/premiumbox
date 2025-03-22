<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]GeoPay[:en_US][ru_RU:]GeoPay[:ru_RU]
description: [en_US:]GeoPay automatic payouts[:en_US][ru_RU:]авто выплаты GeoPay[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_geopay')){
	class paymerchant_geopay extends AutoPayut_Premiumbox {
		
		private $currency_lists = array('GRN','RUBG','EURG');
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 0);
			
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_ap_'. $id .'_status' . hash_url($id,'ap'), array($this,'merchant_status'));
			}
		}

		function get_map(){
			$map = array(
				'PRIVATE_KEY'  => array(
					'title' => '[en_US:]Private Key[:en_US][ru_RU:]Приватный ключ[:ru_RU]',
					'view' => 'textarea',	
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
			$arrs[] = array('PRIVATE_KEY','API_KEY');
			return $arrs;
		}
		
		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, array('note','checkpay'));			

			$text = '
			<div><strong>CallBack URL:</strong> <a href="'. get_mlink('ap_'. $id .'_status' . hash_url($id, 'ap')) .'" target="_blank">'. get_mlink('ap_'. $id .'_status' . hash_url($id, 'ap')) .'</a></div>
			';
			$options[] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin){
			
			$currencies = $this->currency_lists;
			
			$purses = array();
			
			foreach($currencies as $currency){
				$purses[$m_id . '_' . strtolower($currency)] = strtoupper($currency);
			} 
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			$purse = trim(str_replace($m_id . '_','',$code)); 
			if($purse){
				try {
					$class = new AP_GeoPay(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin, 'PRIVATE_KEY'));
					$rezerv = $class->get_balance($purse);
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
			
			$item_id = $item->id;			
			$trans_id = 0;				
			
			$currency = mb_strtolower($item->currency_code_get);
			$currency = str_replace('uah','grn', $currency);
			$currency = str_replace(array('rur','rub'),'rubg', $currency);
			$currency = str_replace('eur','eurg', $currency);
						
			$account = $item->account_get;
					
			if(!$account){
				$error[] = __('Client wallet type does not match with currency code','pn');
			}			
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);			
			
			if(count($error) == 0){
				$result = $this->set_ap_status($item, $test);
				if($result){
					try {
						$class = new AP_GeoPay(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin, 'PRIVATE_KEY'));
						$trans_id = $class->create_payout($currency, $sum, $account, $item_id);
						if(!$trans_id){
							$error[] = __('Payout error','pn');
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
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
				} 		
			}
		}	

		function merchant_status(){
		global $wpdb;
		
			$m_id = key_for_url('_status', 'ap_');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_paymerch_data($m_id);
			
			$callback = file_get_contents('php://input');
			$post = @json_decode($callback, true);
			
			$this->logs(print_r($post, true));
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$partner_transaction_id = pn_strip_input(is_isset($post,'partner_transaction_id'));
			if($partner_transaction_id){
				$class = new AP_GeoPay(is_deffin($m_defin, 'API_KEY'), is_deffin($m_defin, 'PRIVATE_KEY'));
				$res = $class->status_payout($partner_transaction_id);
				
				$check_status = strtolower($res['status']);
				
				/*
				NEW - создан, ушло в обработку
				PENDING - в обработке
				REJECTED - отклонен, средства не вывелись
				PAID - успешно оплачен
				UNKNOWN
				*/
				
				if($check_status){
					$item_id = intval(str_replace('ap','',$partner_transaction_id));
					$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id' AND id='$item_id'");
					if(isset($item->id)){
						$check_status = strtolower($res['status']); 
						if($check_status == 'paid'){
								
							$params = array(
								'system' => 'system',
								'bid_status' => array('coldsuccess'),
								'm_place' => 'cron ' .$m_id,
								'm_id' => $m_id,
								'm_defin' => $m_defin,
								'm_data' => $m_data,
							);
							set_bid_status('success', $item->id, $params);
								
						} elseif(in_array($check_status, array('rejected'))){
								
							$this->reset_cron_status($item, $error_status, $m_id);
								
						}					
					}
				}
			}
			
			echo 'OK';
			exit;
		}		
	}
}

new paymerchant_geopay(__FILE__, 'GeoPay');