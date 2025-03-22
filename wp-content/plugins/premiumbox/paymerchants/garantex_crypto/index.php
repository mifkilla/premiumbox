<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Garantex Crypto[:en_US][ru_RU:]Garantex Crypto[:ru_RU]
description: [en_US:]Garantex Crypto automatic payouts[:en_US][ru_RU:]авто выплаты Garantex Crypto[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_garantex_crypto')){
	class paymerchant_garantex_crypto extends AutoPayut_Premiumbox {
		
		private $currency_lists = array('AFF','BTC','DAI','ETH','USDT');
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
		}

		function get_map(){
			$map = array(
				'PRIVATE_KEY'  => array(
					'title' => '[en_US:]Private Key[:en_US][ru_RU:]Private Key[:ru_RU]',
					'view' => 'input',	
				),
				'UID'  => array(
					'title' => '[en_US:]UID[:en_US][ru_RU:]UID[:ru_RU]',
					'view' => 'input',
				),					
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('PRIVATE_KEY','UID');
			return $arrs;
		}
		
		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'note');			
			
			$options['line_error_status'] = array(
				'view' => 'line',
			);			
			
			$options['buy'] = array(
				'view' => 'select',
				'title' => __('Buy additional amount of crypto missing on balance','pn'),
				'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn'), '2'=>__('Buy and do not withdrawal','pn'), '3'=>__('Buy entire amount','pn')),
				'default' => intval(is_isset($data, 'buy')),
				'name' => 'buy',
				'work' => 'int',
			);			
			
			$options['buysymbols'] = array(
				'view' => 'select',
				'title' => __('Determine trading code','pn'),
				'options' => array('0'=>__('Automatically','pn'), '1'=>__('Manually','pn')),
				'default' => intval(is_isset($data, 'buysymbols')),
				'name' => 'buysymbols',
				'work' => 'int',
			);			
			
			$buycurr = array(
				'AFF' => 'AFF',
				'BTC' => 'BTC',
				'DAI' => 'DAI',
				'ETH' => 'ETH',
				'RUB' => 'RUB',
				'UAH' => 'UAH',
				'USD' => 'USD',
				'USDT' => 'USDT',
			);
			
			$options['buycurr'] = array(
				'view' => 'select',
				'title' => __('Trading operation code (if Manually)','pn'),
				'options' => $buycurr,
				'default' => is_isset($data, 'buycurr'),
				'name' => 'buycurr',
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
			
			$currencies = $this->currency_lists;
			
			$purses = array();
			
			foreach($currencies as $currency){
				$purses[$m_id . '_' . strtolower($currency)] = strtoupper($currency);
			} 
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			$purse = strtoupper(trim(str_replace($m_id . '_','',$code))); 
			if($purse){
				try {
					$class = new AP_Garantex_Crypto(is_deffin($m_defin,'PRIVATE_KEY'), is_deffin($m_defin, 'UID'));
					$res = $class->get_balance();
					if(is_array($res)){
						$rezerv = '-1';
						foreach($res as $pursename => $sum){
							$pursename = strtoupper($pursename);
							if($pursename == $purse){
								$rezerv = trim((string)$sum);
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
			
			$item_id = $item->id;			
			$trans_id = 0;				
			
			$currency_code_give = strtoupper($item->currency_code_give);
			$currency_code_get = strtoupper($item->currency_code_get);
			
			$currency_id_give = intval($item->currency_id_give);
			$currency_id_get = intval($item->currency_id_get);			
							
			$account = $item->account_get;
					
			if(!$account){
				$error[] = __('Client wallet type does not match with currency code','pn');
			}			
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data));			
			
			$buy = intval(is_isset($paymerch_data, 'buy')); /* settings */
			
			$class = new AP_Garantex_Crypto(is_deffin($m_defin,'PRIVATE_KEY'), is_deffin($m_defin, 'UID'));
			
			$currency_send = $currency_code_get;
			$sum_send = $sum;
			
			if($currency_code_get == 'USDT'){
				global $wpdb;
				$currency_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE id='$currency_id_get'");
				if(isset($currency_data->id)){
					$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
					if($xml_value == 'USDT'){
						$currency_send = 'USDT-OMNI';
					} elseif($xml_value == 'USDTERC'){
						$currency_send = 'USDT';
					} elseif($xml_value == 'USDTTRC'){	
						$currency_send = 'USDT-TRON';
					}
				}
			}			
			
			if(count($error) == 0){
				if($buy > 0){
					
					$balanced = $class->get_balance();
					$balance = is_sum(is_isset($balanced, $currency_code_get));
					
					$buysymbols = intval(is_isset($paymerch_data, 'buysymbols'));
					$buycurr = is_xml_value(is_isset($paymerch_data, 'buycurr'));
					if(!$buycurr){ $buycurr = 'USDT'; }
					
					$symbol = '';
					if($buysymbols == 1){
						$symbol = strtolower($currency_code_get . $buycurr);
					} else {	
						$symbol = strtolower($currency_code_get . $currency_code_give);
					}
					
					$res = $class->get_payout_fee();
					$comiss = $class->get_fee($currency_code_get, $sum_send, $res);
					
					$need_sum_send = is_sum($sum_send + $comiss);
					
					$buy_sum = 0;
					if($buy == 1 or $buy == 2){
						if($need_sum_send > $balance){
							$buy_sum = $need_sum_send - $balance;
						}	
					}	
					if($buy == 3){
						$buy_sum = $need_sum_send;
					}	
					
					$buy_sum = is_sum($buy_sum, 12);
					if($buy_sum > 0){
						
						$pairs = $class->get_pairs();	
						if(isset($pairs[$symbol])){

							$res = $class->get_trading_fee();
							$trading_comiss = is_sum(is_isset($res, $symbol));
						
							$buy_sum = $buy_sum / (1 - $trading_comiss);
							
							$currencies = $class->get_currency();
							$curr = is_isset($currencies, $currency_code_get);
						
							$symb = 8;
							if(isset($currencies[$curr], $currencies[$curr]['precision'])){
								$symb = intval($currencies[$curr]['precision']);
							}
							
							$buy_sum = is_sum($buy_sum, $symb, 'up');

							$order_id = $class->set_order($symbol, $buy_sum, 'buy');
							if($order_id){
								sleep(10);
							} else {
								$error[] = __('Failed to buy cryptocurrency','pn');
							}
							
						} else {
							$error[] = 'pair not trading';
						}
						
					}
				
					if($buy == 2){
						$error[] = __('Cryptocurrency only','pn');
					}	
				}
			}			
					
			$dest_tag = trim(is_isset($unmetas,'dest_tag'));		
						
			if(count($error) == 0){
				$result = $this->set_ap_status($item, $test);
				if($result){
					try {
						$trans_id = $class->create_payout($currency_send, $sum_send, $account);
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

		function cron($m_id, $m_defin, $m_data){
		global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$class = new AP_Garantex_Crypto(is_deffin($m_defin,'PRIVATE_KEY'), is_deffin($m_defin, 'UID'));
			$transactions = $class->get_history_payouts(100);
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
			
			foreach($items as $item){
				$currency = mb_strtoupper($item->currency_code_get);
				$trans_id = trim($item->trans_out);
				if($trans_id){
					if(isset($transactions[$trans_id])){
						$check_status = strtolower($transactions[$trans_id]['state']); 
						$txt_id = pn_strip_input(is_isset($transactions[$trans_id],'txid'));
						if($check_status == 'succeed' and $txt_id){
							
							$params = array(
								'trans_out' => $txt_id,
								'system' => 'system',
								'bid_status' => array('coldsuccess'),
								'm_place' => 'cron ' .$m_id,
								'm_id' => $m_id,
								'm_defin' => $m_defin,
								'm_data' => $m_data,
							);
							set_bid_status('success', $item->id, $params);
							
						} elseif(in_array($check_status, array('failed','rejected'))){
							
							$this->reset_cron_status($item, $error_status, $m_id);
							
						}
					}	
				}
			}
		}		
	}
}

new paymerchant_garantex_crypto(__FILE__, 'Garantex Crypto');