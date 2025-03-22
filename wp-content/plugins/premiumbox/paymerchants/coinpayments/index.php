<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Coinpayments[:en_US][ru_RU:]Coinpayments[:ru_RU]
description: [en_US:]Coinpayments automatic payouts[:en_US][ru_RU:]авто выплаты Coinpayments[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_coinpayments')){
	class paymerchant_coinpayments extends AutoPayut_Premiumbox{

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
		}	
		
		function get_map(){
			$map = array(
				'PUBLIC_KEY'  => array(
					'title' => '[en_US:]Public key[:en_US][ru_RU:]Публичный Ключ[:ru_RU]',
					'view' => 'input',	
				),
				'PRIVAT_KEY'  => array(
					'title' => '[en_US:]Privat key[:en_US][ru_RU:]Приватный Ключ[:ru_RU]',
					'view' => 'input',	
				),
				'BTC'  => array(
					'title' => '[en_US:]BTC address from Coinpayments[:en_US][ru_RU:]BTC адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'LTC'  => array(
					'title' => '[en_US:]LTC address from Coinpayments[:en_US][ru_RU:]LTC адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'XRP'  => array(
					'title' => '[en_US:]XRP address from Coinpayments[:en_US][ru_RU:]XRP адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'DASH'  => array(
					'title' => '[en_US:]DASH address from Coinpayments[:en_US][ru_RU:]DASH адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'DOGE'  => array(
					'title' => '[en_US:]DOGE address from Coinpayments[:en_US][ru_RU:]DOGE адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'ETC'  => array(
					'title' => '[en_US:]ETC address from Coinpayments[:en_US][ru_RU:]ETC адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'ETH'  => array(
					'title' => '[en_US:]ETH address from Coinpayments[:en_US][ru_RU:]ETH адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'NMC'  => array(
					'title' => '[en_US:]NMC address from Coinpayments[:en_US][ru_RU:]NMC адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'PPC'  => array(
					'title' => '[en_US:]PPC address from Coinpayments[:en_US][ru_RU:]PPC адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'USDT'  => array(
					'title' => '[en_US:]USDT address from Coinpayments[:en_US][ru_RU:]USDT адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'WAVES'  => array(
					'title' => '[en_US:]WAVES address from Coinpayments[:en_US][ru_RU:]WAVES адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'XMR'  => array(
					'title' => '[en_US:]XMR address from Coinpayments[:en_US][ru_RU:]XMR адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'ZEC'  => array(
					'title' => '[en_US:]ZEC address from Coinpayments[:en_US][ru_RU:]ZEC адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'USDT'  => array(
					'title' => '[en_US:]USDT address from Coinpayments[:en_US][ru_RU:]USDT адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'BCH'  => array(
					'title' => '[en_US:]BCH address from Coinpayments[:en_US][ru_RU:]BCH адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'NEO'  => array(
					'title' => '[en_US:]NEO address from Coinpayments[:en_US][ru_RU:]NEO адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'QTUM'  => array(
					'title' => '[en_US:]QTUM address from Coinpayments[:en_US][ru_RU:]QTUM адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'TUSD'  => array(
					'title' => '[en_US:]TUSD address from Coinpayments[:en_US][ru_RU:]TUSD адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'XEM'  => array(
					'title' => '[en_US:]XEM address from Coinpayments[:en_US][ru_RU:]XEM адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'TRX'  => array(
					'title' => '[en_US:]TRX address from Coinpayments[:en_US][ru_RU:]TRX адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'BSV'  => array(
					'title' => '[en_US:]BSV address from Coinpayments[:en_US][ru_RU:]BSV адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'BCH'  => array(
					'title' => '[en_US:]BCH address from Coinpayments[:en_US][ru_RU:]BCH адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'XVG'  => array(
					'title' => '[en_US:]XVG address from Coinpayments[:en_US][ru_RU:]XVG адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),
				'USDTERC20'  => array(
					'title' => '[en_US:]USDT.ERC20 address from Coinpayments[:en_US][ru_RU:]USDT.ERC20 адрес из аккаунта Coinpayments[:ru_RU]',
					'view' => 'input',	
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('PUBLIC_KEY','PRIVAT_KEY');
			return $arrs;
		}				

		function options($options, $data, $id, $place){
						
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'note');
			
			$options['addtxfee'] = array(
				'view' => 'select',
				'title' => __('Exchanger pays transaction fee','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => is_isset($data, 'addtxfee'),
				'name' => 'addtxfee',
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
			$keys = array('BTC','LTC','XRP','DASH','DOGE','ETC','ETH','NMC','PPC','USDT','WAVES','XMR','ZEC','USDT','BCH','NEO','QTUM','TUSD','XEM','TRX','BSV','BCH','XVG','USDTERC20');
			$purses = array();
			$r = 0;
			foreach($keys as $key){ $r++;
				$key = trim($key);
				if($key){
					$purses[$m_id.'_'.$r] = $key;
				}	
			}
			
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$api = trim(is_isset($purses, $code));
			if($api){
						
				try {
					$PUBLIC_KEY = is_deffin($m_defin,'PUBLIC_KEY');
					$PRIVAT_KEY = is_deffin($m_defin,'PRIVAT_KEY');
							
					$class = new AP_CoinPaymentsAPI($PRIVAT_KEY, $PUBLIC_KEY);
					$result = $class->get_balans();
					
					$rezerv = '-1';
					
					if(isset($result['error']) and $result['error'] == 'ok'){
						$res = $result['result'];
						if(is_array($res)){
							foreach($res as $k => $v){
								if($api == $k){
									$rezerv = $res[$k]['balancef'];
								}
							}
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
			$vtype = mb_strtoupper($item->currency_code_get);
					
			$addtxfee = intval(is_isset($paymerch_data, 'addtxfee'));		
					
			$enable = array('BTC','LTC','XRP','DASH','DOGE','ETC','ETH','NMC','PPC','USDT','WAVES','XMR','ZEC','USDT','BCH','NEO','QTUM','TUSD','XEM','TRX','BSV','BCH','XVG','USDT.ERC20');		
			if(!in_array($vtype, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}					
					
			$account = $item->account_get;
			if (!$account) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}				
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data));
			
			$minsum = '0.0004';
			if($sum < $minsum){
				$error[] = sprintf(__('Minimum payment amount is %s','pn'), $minsum);
			}		
					
			$PUBLIC_KEY = is_deffin($m_defin,'PUBLIC_KEY');
			$PRIVAT_KEY = is_deffin($m_defin,'PRIVAT_KEY');
						
			if(count($error) == 0){

				$result = $this->set_ap_status($item, $test);				
				if($result){				
					try{
						$class = new AP_CoinPaymentsAPI($PRIVAT_KEY, $PUBLIC_KEY);
						$auto_confirm = 1;
						
						$params = array();
						if($addtxfee){
							$params['add_tx_fee'] = 1;
						}
						$dest_tag = trim(is_isset($unmetas,'dest_tag'));
						if($dest_tag){
							$params['dest_tag'] = $dest_tag;
						}
	
						$result = $class->get_transfer($sum, $vtype, $account, $auto_confirm, $params);
						if(isset($result['result']) and isset($result['result']['id'])){
							$trans_id = $result['result']['id'];
						} else {
							$error[] = $result['error'];
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
			
			$PUBLIC_KEY = is_deffin($m_defin,'PUBLIC_KEY');
			$PRIVAT_KEY = is_deffin($m_defin,'PRIVAT_KEY');
			
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
			foreach($items as $item){
				$currency = mb_strtoupper($item->currency_code_get);
				$trans_id = trim($item->trans_out);
				if($trans_id){
					try {
						$class = new AP_CoinPaymentsAPI($PRIVAT_KEY, $PUBLIC_KEY);
						$result = $class->get_transfer_info($trans_id);
						if(isset($result['result']) and isset($result['result']['status'])){
							$check_status = intval($result['result']['status']);
							$txt_id = pn_strip_input($result['result']['send_txid']);
							if($check_status == 2){
								
								$params = array(
									'trans_out' => $txt_id,
									'system' => 'system',
									'bid_status' => array('coldsuccess'),
									'm_place' => 'cron ' . $m_id .'_cron',
									'm_id' => $m_id,
									'm_defin' => $m_defin,
									'm_data' => $m_data,
								);
								set_bid_status('success', $item->id, $params);
								
							} elseif($check_status == '-1') {
								
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
new paymerchant_coinpayments(__FILE__, 'Coinpayments');