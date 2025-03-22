<?php
if( !defined( 'ABSPATH')){ exit(); } 

/*
title: [en_US:]WhiteBit Crypto[:en_US][ru_RU:]WhiteBit Crypto[:ru_RU]
description: [en_US:]WhiteBit Crypto automatic payouts[:en_US][ru_RU:]авто выплаты WhiteBit Crypto[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_whitebit_crypto')){
	class paymerchant_whitebit_crypto extends AutoPayut_Premiumbox {
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$this->curr = array('BTC','ETH','USD','LTC','ETC','BCH','DASH','NEO','UAH','XLM','OMG','BNB','USDT','BAT','LOT','KEY','WAVES','EUR','RUB','XRC','BCCN','QBIT','REM','SON','MB8','ILC','CCOH','MEXC','GBC','VITAE','STREAM','TELOS','TRX','MPC','DEXR','ROX','VC','AYA','XST','CSC','SGC','GFG','BTG','MATIC','SCAP','XRP','CVA','SHARK','CDL','BTT','NRG','POLIS','KIM','CICX','PGPAY','FLT','PAC','AGRS','TERN','IEOS','USDT_ETH','USDT_TRON','TUSD','PAX','CCXX','USDC','GLEEC','BEER','LINK','SOLO','CAP','SNB','BSV','ZEC','VNDC','DBTC','DUSDT','TLW','TNC','CBUCKS','MARTK','MWC','USDT_OMNI');
			
		}		
		
		function get_map(){
			$map = array(
				'KEY'  => array(
					'title' => '[en_US:]Public Key[:en_US][ru_RU:]Публичный ключ[:ru_RU]',
					'view' => 'input',	
				),
				'SECRET'  => array(
					'title' => '[en_US:]Secret Key[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
					'view' => 'input',
				),					
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('KEY','SECRET');
			return $arrs;
		}				
		
		function options($options, $data, $id, $place){ 
			
			$options = pn_array_unset($options, array('note','checkpay'));			
			
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
			foreach($this->curr as $curr){
				$purses[$m_id.'_'.$curr] = $curr;
			}
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if($purse){						
				try {					
					$class = new AP_WHITEBIT_crypto(is_deffin($m_defin,'KEY'),is_deffin($m_defin,'SECRET'));
					$res = $class->get_balance();
					if(is_array($res)){								
						$rezerv = '-1';								
						foreach($res as $pursename => $amount){
							if($pursename == $purse){
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

			$trans_id = 0;
			$item_id = $item->id;
			
			$currency_code_give = strtoupper($item->currency_code_give);
			$currency_code_get = strtoupper($item->currency_code_get);
			
			$currency_id_give = intval($item->currency_id_give);
			$currency_id_get = intval($item->currency_id_get);

			$enable = $this->curr;
			if(!in_array($currency_code_get, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}		
					
			$account = str_replace(' ', '', trim($item->account_get));
			if (!$account) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}		
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 12);

			$min = 0;
			
			if($sum < $min){
				$error[] = sprintf(__('Minimum payment amount is %s','pn'), $min);
			}				

			$dest_tag = trim(is_isset($unmetas,'dest_tag'));
				
			$network_send = '';
			if($currency_code_get == 'USDT'){
				global $wpdb;
				$currency_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE id='$currency_id_get'");
				if(isset($currency_data->id)){
					$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
					if($xml_value == 'USDT'){
						$network_send = 'OMNI';
					} elseif($xml_value == 'USDTERC'){
						$network_send = 'ERC20';
					} elseif($xml_value == 'USDTTRC'){	
						$network_send = 'TRC20';
					}
				}
			}			
				
			if(count($error) == 0){
				$result = $this->set_ap_status($item, $test);				
				if($result){				
		
					try {
						$class = new AP_WHITEBIT_crypto(is_deffin($m_defin,'KEY'),is_deffin($m_defin,'SECRET'));
						$res = $class->create_order($currency_code_get, $sum, $account, $dest_tag, $item_id, $network_send); 
						if($res == 1){
							$trans_id = '';
						} else {
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
			
			$class = new AP_WHITEBIT_crypto(is_deffin($m_defin,'KEY'),is_deffin($m_defin,'SECRET'));
			
			$records = $class->get_history(2, 50);
			
			if(is_array($records) and isset($records['records']) and is_array($records['records']) and count($records['records']) > 0){
				$orders = $records['records'];
			
				$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
				foreach($items as $item){
					$id = $item->id;
					foreach($orders as $order_key => $order){
						$unique_id = intval(is_isset($order,'uniqueId'));
						if($unique_id and $unique_id == $id){

							unset($orders[$order_key]);

							$res_status = $order['status'];
							$res_txid = trim($order['transactionHash']);
							
							$st_success = array('3','7');
							$st_error = array('4','5','9','12');
							if(in_array($res_status, $st_success)){
								
								$params = array(
									'system' => 'system',
									'bid_status' => array('coldsuccess'),
									'm_place' => 'cron ' .$m_id,
									'm_id' => $m_id,
									'm_defin' => $m_defin,
									'm_data' => $m_data,
								);
								if($res_txid){
									$params['trans_out'] = $res_txid;
								}
								set_bid_status('success', $item->id, $params);
								
							} elseif(in_array($res_status, $st_error)){
								
								$this->reset_cron_status($item, $error_status, $m_id);
							}
							
							break;
							
						}
					}
				}
			}
		}		
	}
}

new paymerchant_whitebit_crypto(__FILE__, 'WhiteBit crypto');