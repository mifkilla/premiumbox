<?php
if( !defined( 'ABSPATH')){ exit(); } 

/*
title: [en_US:]Kuna[:en_US][ru_RU:]Kuna[:ru_RU]
description: [en_US:]Kuna automatic payouts[:en_US][ru_RU:]авто выплаты Kuna[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_kuna')){
	class paymerchant_kuna extends AutoPayut_Premiumbox {
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$this->curr = array(
				'1'=>'UAH', 
				'42'=>'USD', 
				'43'=> 'RUB', 
				'41'=> 'USDT',
				'2'=> 'BTC',
				'6'=> 'ETH',
				'16'=> 'XRP',
				'8'=> 'BCH',
				'3'=> 'KUN',
				'23'=> 'LTC',
				'25'=> 'ZEC',
				'28'=> 'XEM',
				'24'=> 'DASH',
				'22'=> 'XLM',
				'7'=> 'WAVES',
				'17'=> 'EOS',
				'29'=> 'REM',
				'4'=> 'GOL',
				'21'=> 'TUSD',
				'44'=> 'DREAM',
				'45'=> 'PTI',
				'46'=> 'BNB',
				'47'=> 'GOLOS',	
				'48'=> 'CYBER',
				'49'=> 'USDC',
				'50'=> 'DAI',
				'51'=> 'UAX',				
			); //https://api.kuna.io/v3/currencies
		}		
		
		function get_map(){
			$map = array(
				'API_KEY'  => array(
					'title' => '[en_US:]Public Key[:en_US][ru_RU:]Публичный ключ[:ru_RU]',
					'view' => 'input',	
				),
				'SECRET_KEY'  => array(
					'title' => '[en_US:]Secret Key[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
					'view' => 'input',
				),					
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('API_KEY','SECRET_KEY');
			return $arrs;
		}				
		
		function options($options, $data, $id, $place){ 
			
			$options = pn_array_unset($options, array('note','checkpay'));
			
			$options['paycomiss'] = array(
				'view' => 'select',
				'title' => __('Who pays fee','pn'),
				'options' => array('0'=> __('Exchanger','pn'), '1'=> __('User','pn')),
				'default' => is_isset($data, 'paycomiss'),
				'name' => 'paycomiss',
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
			foreach($this->curr as $curr){
				$purses[$m_id.'_'.strtolower($curr)] = $curr;
			}	
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$currency = trim(is_isset($purses, $code));
			if($currency){
				try{
					$class = new Kuna_AP(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin, 'SECRET_KEY'));
					$res = $class->get_balans();
					if(is_array($res)){
						$rezerv = '-1';
						foreach($res as $curr => $amount){
							if($currency == $curr){
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
				} 				
			}
			return $sum;			
		}	

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){

			$trans_id = 0;
			$item_id = $item->id;
				
			$paycomiss = intval(is_isset($paymerch_data, 'paycomiss'));

			$vtype = $currency_code_get = mb_strtoupper($item->currency_code_get);

			$enable = $this->curr;
			if(!in_array($vtype, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}		
			
			$network = '';
			$currency_id_get = $item->currency_id_get;
			if($currency_code_get == 'USDT'){
				global $wpdb;
				$currency_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE id='$currency_id_get'");
				if(isset($currency_data->id)){
					$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
					if($xml_value == 'USDT'){
						$network = 'omni';
					} elseif($xml_value == 'USDTERC'){
						$network = 'eth';
					}
				}
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
							
			if(count($error) == 0){
				$result = $this->set_ap_status($item, $test);				
				if($result){				
		
					try {
						$class = new Kuna_AP(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin, 'SECRET_KEY'));
						$res = $class->create_payout($vtype, $sum, $account, $dest_tag, $paycomiss, $network); 
						if(is_array($res[0])){
							$trans_id = $res[0]['withdrawal_id'];
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
			
			$class = new Kuna_AP(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin, 'SECRET_KEY'));
			$time = current_time('timestamp');
			$start_time = $time - (2 * DAY_IN_SECONDS);
			$end_time = $time;
			$orders = $class->get_history_payouts($start_time, $end_time);
			
			if(is_array($orders)){
				$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
				foreach($items as $item){
					$trans_out = trim($item->trans_out);
					if($trans_out){
						if(isset($orders[$trans_out])){
							$res = $orders[$trans_out];
							$res_status = $res['status'];
							$res_currency = $res['currency'];
							$res_txid = trim($res['txid']);
							$currency = is_isset($this->curr, $res_currency);
							$st_success = array('done');
							$st_error = array('canceled','unknown');
							if(in_array($res_status, $st_success)){
								
								$params = array(
									'system' => 'system',
									'bid_status' => array('coldsuccess'),
									'm_place' => 'cron ' .$m_id .'_cron',
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
						}		
					}
				}
			}
		}		
	}
}

new paymerchant_kuna(__FILE__, 'Kuna');