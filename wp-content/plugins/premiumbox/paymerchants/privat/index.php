<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Privat24[:en_US][ru_RU:]Privat24[:ru_RU]
description: [en_US:]Privat24 automatic payouts[:en_US][ru_RU:]авто выплаты Privat24[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_privatbank')){
	class paymerchant_privatbank extends AutoPayut_Premiumbox{
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
		}

		function get_map(){
			$map = array(
				'AP_PRIVAT24_MERCHANT_ID_UAH'  => array(
					'title' => '[en_US:]Merchant ID[:en_US][ru_RU:]ID мерчант[:ru_RU]',
					'view' => 'input',	
				),
				'AP_PRIVAT24_MERCHANT_KEY_UAH'  => array(
					'title' => '[en_US:]Merchant key[:en_US][ru_RU:]Ключ-пароль от мерчанта[:ru_RU]',
					'view' => 'input',	
				),
				'AP_PRIVAT24_MERCHANT_CARD_UAH'  => array(
					'title' => '[en_US:]Merchant card number[:en_US][ru_RU:]Номер карты[:ru_RU]',
					'view' => 'input',	
				),		
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('AP_PRIVAT24_MERCHANT_ID_UAH','AP_PRIVAT24_MERCHANT_KEY_UAH','AP_PRIVAT24_MERCHANT_CARD_UAH');
			return $arrs;
		}

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');		

			$opt = array(
				'0' => __('Privat24','pn'),
				'1' => __('Privat24 Visa','pn'),
			);
			$options['variant'] = array(
				'view' => 'select',
				'title' => __('Transaction type','pn'),
				'options' => $opt,
				'default' => intval(is_isset($data, 'variant')),
				'name' => 'variant',
				'work' => 'int',
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
				$m_id.'_1' => is_deffin($m_defin,'AP_PRIVAT24_MERCHANT_CARD_UAH'),
			);
			
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;

			if($code == $m_id.'_1'){
				$merchant_id = is_deffin($m_defin,'AP_PRIVAT24_MERCHANT_ID_UAH');
				$merchant_pass = is_deffin($m_defin,'AP_PRIVAT24_MERCHANT_KEY_UAH');
				$card = is_deffin($m_defin,'AP_PRIVAT24_MERCHANT_CARD_UAH');
			} 

			if($merchant_id and $merchant_pass and $card){		
				try {
					$oClass = new AP_PrivatBank($m_id, $merchant_id,$merchant_pass);
					$res = $oClass->get_balans($card);
					if(is_array($res)){		
						$rezerv = '-1';
								
						foreach($res as $pursename => $amount){
							if( $pursename == $card ){
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
			$item_id = $item->id;
			$trans_id = 0;
				
			$vtype = mb_strtoupper($item->currency_code_get);

			$enable = array('UAH');
			if(!in_array($vtype, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}		
					
			$account = $item->account_get;
			$account = mb_strtoupper($account);
			if (!preg_match("/^[0-9]{7,25}$/", $account, $matches )) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}		
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
		
			$merchant_id = is_deffin($m_defin,'AP_PRIVAT24_MERCHANT_ID_'.$vtype);
			$merchant_pass = is_deffin($m_defin,'AP_PRIVAT24_MERCHANT_KEY_'.$vtype);
			$merchant_card = is_deffin($m_defin,'AP_PRIVAT24_MERCHANT_CARD_'.$vtype);
					
			if(!$merchant_id or !$merchant_pass){
				$error[] = 'Error interfaice';
			}
					
			$variant = intval(is_isset($paymerch_data, 'variant'));
			if($variant == 1){
				$fio_str = trim(is_isset($unmetas,'user_fio'));
				if(!$fio_str){
					$fio = array($item->last_name, $item->first_name, $item->second_name);
					$fio = array_unique($fio);
					$fio_str = trim(join(' ',$fio));
				}
				if(!$fio_str){
					$error[] = 'Error FIO';
				}						
			}
				
			if(count($error) == 0){

				$result = $this->set_ap_status($item, $test);			
				if($result){				
					
					$notice = get_text_paymerch($m_id, $item);
					if(!$notice){ $notice = sprintf(__('ID order %s','pn'), $item->id); }
					$notice = trim(pn_maxf($notice,150));
						
					try {
						
						$oClass = new AP_PrivatBank($m_id, $merchant_id,$merchant_pass);
						if($variant == 0){
							$res = $oClass->make_order($item_id, $account, $sum, $vtype, $notice);
						} else {
							$res = $oClass->make_order_visa($item_id, $account, $sum, $vtype, $notice, $fio_str);
						}
						if($res['error'] == 1){
							$error[] = __('Payment error','pn');
							$pay_error = 1;
						} else {
							$trans_id = $res['id'];
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
					'from_account' => $merchant_card,
					'trans_out' => $trans_id,
					'system' => 'user',
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('coldsuccess', $item_id, $params, $direction);	 					
						
				if($place == 'admin'){
					pn_display_mess(__('Payment is successfully created. Waiting for confirmation from Privat24.','pn'),__('Payment is successfully created. Waiting for confirmation from Privat24.','pn'),'true');
				} 
						
			}
		}

		function cron($m_id, $m_defin, $m_data){
		global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$en_currency = array('UAH');
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
			foreach($items as $item){
				
				$currency = mb_strtoupper($item->currency_code_get);
				if(in_array($currency, $en_currency)){
				
					$merchant_id = is_deffin($m_defin,'AP_PRIVAT24_MERCHANT_ID_'.$currency);
					$merchant_key = is_deffin($m_defin,'AP_PRIVAT24_MERCHANT_KEY_'.$currency);
				
					if($merchant_id and $merchant_key){
				
						try {
						
							$oClass = new AP_PrivatBank($m_id, $merchant_id,$merchant_key);
							$res = $oClass->check_order($item->id);
							if(isset($res['status'])){
								if($res['status'] == 'ok'){
									$params = array(
										'system' => 'system',
										'bid_status' => array('coldsuccess'),
										'm_place' => 'cron ' .$m_id,
										'm_id' => $m_id,
										'm_defin' => $m_defin,
										'm_data' => $m_data,
									);
									set_bid_status('success', $item->id, $params);														
								} elseif($res['status'] != 'snd') {
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
}

new paymerchant_privatbank(__FILE__, 'Privat24');