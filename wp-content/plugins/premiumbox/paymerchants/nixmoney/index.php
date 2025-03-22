<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]NixMoney[:en_US][ru_RU:]NixMoney[:ru_RU]
description: [en_US:]NixMoney automatic payouts[:en_US][ru_RU:]авто выплаты NixMoney[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_nixmoney')){
	class paymerchant_nixmoney extends AutoPayut_Premiumbox{
		function __construct($file, $title)
		{
			parent::__construct($file, $title);	
		}

		function get_map(){
			$map = array(
				'AP_NIXMONEY_ACCOUNT'  => array(
					'title' => '[en_US:]Account password[:en_US][ru_RU:]Пароль от аккаунта NixMoney[:ru_RU]',
					'view' => 'input',
				),
				'AP_NIXMONEY_PASSWORD'  => array(
					'title' => '[en_US:]Account e-mail[:en_US][ru_RU:]E-mail от аккаунта NixMoney[:ru_RU]',
					'view' => 'input',
				),
				'AP_NIXMONEY_USD'  => array(
					'title' => '[en_US:]USD wallet number[:en_US][ru_RU:]USD номер счета[:ru_RU]',
					'view' => 'input',
				),
				'AP_NIXMONEY_EUR'  => array(
					'title' => '[en_US:]EUR wallet number[:en_US][ru_RU:]EUR номер счета[:ru_RU]',
					'view' => 'input',
				),
				'AP_NIXMONEY_BTC'  => array(
					'title' => '[en_US:]BTC wallet number[:en_US][ru_RU:]BTC номер счета[:ru_RU]',
					'view' => 'input',
				),
				'AP_NIXMONEY_LTC'  => array(
					'title' => '[en_US:]LTC wallet number[:en_US][ru_RU:]LTC номер счета[:ru_RU]',
					'view' => 'input',
				),
				'AP_NIXMONEY_PPC'  => array(
					'title' => '[en_US:]PPC wallet number[:en_US][ru_RU:]PPC номер счета[:ru_RU]',
					'view' => 'input',
				),
				'AP_NIXMONEY_FTC'  => array(
					'title' => '[en_US:]FTC wallet number[:en_US][ru_RU:]FTC номер счета[:ru_RU]',
					'view' => 'input',
				),
				'AP_NIXMONEY_CRT'  => array(
					'title' => '[en_US:]CRT wallet number[:en_US][ru_RU:]CRT номер счета[:ru_RU]',
					'view' => 'input',
				),
				'AP_NIXMONEY_GBC'  => array(
					'title' => '[en_US:]GBC wallet number[:en_US][ru_RU:]GBC номер счета[:ru_RU]',
					'view' => 'input',
				),
				'AP_NIXMONEY_DOGE'  => array(
					'title' => '[en_US:]DOGE wallet number[:en_US][ru_RU:]DOGE номер счета[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('AP_NIXMONEY_ACCOUNT','AP_NIXMONEY_PASSWORD');
			return $arrs;
		}		
		
		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'error_status');
			
			$n_options = array();
			$n_options['warning'] = array(
				'view' => 'warning',
				'default' => sprintf(__('Use only latin symbols in payment notes. Maximum: %s characters.','pn'), 100),
			);		
			$options = pn_array_insert($options, 'note', $n_options);
			
			return $options;
		}	

		function get_reserve_lists($m_id, $m_defin){
			
			$purses = array(
				$m_id.'_1' => is_deffin($m_defin,'AP_NIXMONEY_USD'),
				$m_id.'_2' => is_deffin($m_defin,'AP_NIXMONEY_EUR'),
				$m_id.'_3' => is_deffin($m_defin,'AP_NIXMONEY_BTC'),
				$m_id.'_4' => is_deffin($m_defin,'AP_NIXMONEY_LTC'),
				$m_id.'_5' => is_deffin($m_defin,'AP_NIXMONEY_PPC'),
				$m_id.'_6' => is_deffin($m_defin,'AP_NIXMONEY_FTC'),
				$m_id.'_7' => is_deffin($m_defin,'AP_NIXMONEY_CRT'),
				$m_id.'_8' => is_deffin($m_defin,'AP_NIXMONEY_GBC'),
				$m_id.'_9' => is_deffin($m_defin,'AP_NIXMONEY_DOGE'),
			);
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if($purse){	
				try{
					
					$oClass = new AP_NixMoney( is_deffin($m_defin,'AP_NIXMONEY_ACCOUNT'), is_deffin($m_defin,'AP_NIXMONEY_PASSWORD') );
					$res = $oClass->getBalans();
					if(is_array($res)){
								
						$rezerv = '-1';
									
						foreach($res as $pursename => $amount){
							if( $pursename == $purse ){
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

		function search_in_history($item_id, $m_defin){
			$search_text = '';
				
			try {
				$class = new AP_NixMoney( is_deffin($m_defin,'AP_NIXMONEY_ACCOUNT'), is_deffin($m_defin,'AP_NIXMONEY_PASSWORD') );
				$hres = $class->getHistory( date( 'd.m.Y', strtotime( '-2 day' ) ), date( 'd.m.Y', strtotime( '+2 day' ) ), 'paymentid', 'rashod' );
				if($hres['error'] == 0){
					$histories = $hres['responce'];
					if(isset($histories[$item_id])){
						$search_text = sprintf(__('Payment ID %s has already been paid','pn'), $item_id);	
					} 
				} else {
					$search_text = __('Failed to retrieve payment history 3','pn');
				}
			}
			catch( Exception $e ) {
				$search_text = $e->getMessage();
			}				
			
			return $search_text;
		}

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){
			$item_id = $item->id;
			$trans_id = 0;
				
			$vtype = mb_strtoupper($item->currency_code_get);
					
			$enable = array('USD','EUR','BTC','LTC','PPC','FTC','CRT','GBC','DOGE');
			if(!in_array($vtype, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}						
						
			$account = $item->account_get;
			$account = mb_strtoupper($account);				
					
			$site_purse = '';
			if($vtype == 'USD'){
				$site_purse = is_deffin($m_defin,'AP_NIXMONEY_USD');
			} elseif($vtype == 'EUR'){
				$site_purse = is_deffin($m_defin,'AP_NIXMONEY_EUR');
			} elseif($vtype == 'BTC'){
				$site_purse = is_deffin($m_defin,'AP_NIXMONEY_BTC');
			} elseif($vtype == 'LTC'){
				$site_purse = is_deffin($m_defin,'AP_NIXMONEY_LTC');
			} elseif($vtype == 'PPC'){
				$site_purse = is_deffin($m_defin,'AP_NIXMONEY_PPC');
			} elseif($vtype == 'FTC'){
				$site_purse = is_deffin($m_defin,'AP_NIXMONEY_FTC');
			} elseif($vtype == 'CRT'){
				$site_purse = is_deffin($m_defin,'AP_NIXMONEY_CRT');
			} elseif($vtype == 'GBC'){
				$site_purse = is_deffin($m_defin,'AP_NIXMONEY_GBC');
			} elseif($vtype == 'DOGE'){
				$site_purse = is_deffin($m_defin,'AP_NIXMONEY_DOGE');					
			} 
					
			$site_purse = mb_strtoupper($site_purse);
			if (!$site_purse) {
				$error[] = __('Your account set on website does not match with currency code','pn');
			}			

			$sum = is_paymerch_sum($item, $paymerch_data);
		
			$two = array('USD','EUR');
			if(in_array($vtype, $two)){
				$sum = is_sum($sum, 2);
			} else {
				$sum = is_sum($sum);
			}					
			
			if(count($error) == 0){

				$result = $this->set_ap_status($item,$test);			
				if($result){
					
					$notice = get_text_paymerch($m_id, $item);
					if(!$notice){ $notice = sprintf(__('ID order %s','pn'), $item->id); }
					$notice = trim(pn_maxf($notice,100));
						
					try{
						
						$oClass = new AP_NixMoney( is_deffin($m_defin,'AP_NIXMONEY_ACCOUNT'), is_deffin($m_defin,'AP_NIXMONEY_PASSWORD') );
						$res = $oClass->SendMoney($site_purse, $account, $sum, $item_id, $notice);
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
						
				$this->reset_ap_status($error, $pay_error, $item, $place,$test);
						
			} else {
				
				
				$params = array(
					'from_account' => $site_purse,
					'trans_out' => $trans_id,
					'system' => 'user',
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('success', $item_id, $params, $direction);  						
				
				
				if($place == 'admin'){
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
				}
						
			}		
		}				
		
	}
}

new paymerchant_nixmoney(__FILE__, 'NixMoney');