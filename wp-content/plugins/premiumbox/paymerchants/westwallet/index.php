<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]WestWallet[:en_US][ru_RU:]WestWallet[:ru_RU]
description: [en_US:]WestWallet automatic payouts[:en_US][ru_RU:]авто выплаты WestWallet[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_westwallet')){
	class paymerchant_westwallet extends AutoPayut_Premiumbox {
		
		public $currencies = array('BTC','BTG','BSV','TRX','XLM','XRP','BNB','ETH','LTC','USDT','ETC','DOGE','BCH','DASH','EOS','ZEC','XMR','LEO','LINK','HT','MKR','USDC','TUSD','XTZ','PAX','ADA','USDTTRC');
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);		
		}		
		
		function get_map(){
			$map = array(
				'PUBLIC_KEY'  => array(
					'title' => '[en_US:]Public key[:en_US][ru_RU:]Пубилчный ключ[:ru_RU]',
					'view' => 'input',
				),	
				'PRIVATE_KEY'  => array(
					'title' => '[en_US:]Private key[:en_US][ru_RU:]Приватный ключ[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('PUBLIC_KEY','PRIVATE_KEY');
			return $arrs;
		}		
		
		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'note');		

			$opts = array(
				'low' => 'low',
				'medium' => 'medium',
				'high' => 'high',
			);
			
			$priority = trim(is_isset($data, 'priority'));			
            if(!$priority){ $priority = 'medium'; }
			
			$options['priority'] = array(
				'view' => 'select',
				'title' => __('Transaction fee','pn'),
				'options' => $opts,
				'default' => $priority,
				'name' => 'priority',
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
			$purses = array();
			$list = $this->currencies;
			foreach($list as $li){
				$purses[$m_id . '_' . strtolower($li)] = $li;
			}	
			return $purses;
		}	

		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if($purse){
				try {
					
					$public_key = is_deffin($m_defin,'PUBLIC_KEY');
					$private_key = is_deffin($m_defin,'PRIVATE_KEY');
							
					$class = new AP_WestWallet($public_key, $private_key);
					$res = $class->get_balans();
					$purse = mb_strtoupper($purse);
					
					$rezerv = '-1';
					if(is_array($res)){
						foreach($res as $k => $v){
							if($purse == $k){
								$rezerv = $v;
							}
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
			
			$currency = mb_strtoupper($item->currency_code_get);
			
			if($currency == 'USDT'){
				global $wpdb;
				$currency_id_get = intval($item->currency_id_get);
				$currency_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE id='$currency_id_get'");
				if(isset($currency_data->id)){
					$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
					if($xml_value == 'USDTERC'){
						$currency = 'USDT';
					} elseif($xml_value == 'USDTTRC'){	
						$currency = 'USDTTRC';
					}
				}
			}
			
			$enable = $this->currencies;	
			if(!in_array($currency, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}						
						
			$account = $item->account_get;
			if (!$account) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data));
										
			$public_key = is_deffin($m_defin,'PUBLIC_KEY');
			$private_key = is_deffin($m_defin,'PRIVATE_KEY');
					
			$dest_tag = trim(is_isset($unmetas,'dest_tag'));		
			
			$priority = trim(is_isset($paymerch_data, 'priority'));
			$prio = array('low','medium','high');
			if(!in_array($priority, $prio)){
				$priority = 'medium';
			}			
			
			if(count($error) == 0){

				$result = $this->set_ap_status($item, $test);
				if($result){
					try{
						$class = new AP_WestWallet($public_key, $private_key);
						$res = $class->send_money($currency, $sum, $account, $dest_tag, $item_id, $priority);
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
					'trans_out' => $trans_id,
					'm_place' => $modul_place. ' ' . $m_id,
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
			
			$public_key = is_deffin($m_defin,'PUBLIC_KEY');
			$private_key = is_deffin($m_defin,'PRIVATE_KEY');

			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
			foreach($items as $item){
				$trans_id = trim($item->trans_out);
				if($trans_id){ 
					try {
						$class = new AP_WestWallet($public_key, $private_key);
						$result = $class->get_search($trans_id);
						if(isset($result['status'])){
							$check_status = pn_strip_input($result['status']);
							$txt_id = pn_strip_input(is_isset($result,'blockchain_hash'));
							if($check_status == 'completed'){
								$params = array(
									'trans_out' => $txt_id,
									'system' => 'system',
									'bid_status' => array('coldsuccess'),
									'm_place' => 'cron ' .$m_id.'_cron',
									'm_id' => $m_id,
									'm_defin' => $m_defin,
									'm_data' => $m_data,
								);
								set_bid_status('success', $item->id, $params);														
							} elseif($check_status == 'network_error') {
								
								$this->reset_cron_status($item, $error_status, $m_id);
								
							}
						}	
					}
					catch( Exception $e ) {
										
					}
				}
			}
		}		
	}
}

new paymerchant_westwallet(__FILE__, 'WestWallet');