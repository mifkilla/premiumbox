<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]PlasmaPay[:en_US][ru_RU:]PlasmaPay[:ru_RU]
description: [en_US:]PlasmaPay automatic payouts[:en_US][ru_RU:]авто выплаты PlasmaPay[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_plasmapay')){
	class paymerchant_plasmapay extends AutoPayut_Premiumbox{
		private $curr_list = array('USDP','EURP','RUBP');
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title);					
		}

		function get_map(){
			$map = array(
				'API_KEY'  => array(
					'title' => '[en_US:]API key[:en_US][ru_RU:]API ключ[:ru_RU]',
					'view' => 'input',	
				),
				'USERNAME'  => array(
					'title' => '[en_US:]Plasma wallet without @[:en_US][ru_RU:]Кошелек Plasma без @[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('API_KEY','USERNAME');
			return $arrs;
		}	

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'error_status');
			
			$n_options[] = array(
				'view' => 'warning',
				'default' => sprintf(__('Use only latin symbols in payment notes. Maximum: %s characters.','pn'), 100),
			);		
			$opt = array(
				'0' => __('Exchanger','pn'),
				'1' => __('User','pn'),
			);
			$n_options['feepayer'] = array(
				'view' => 'select',
				'title' => __('Who pays fee','pn'),
				'options' => $opt,
				'default' => intval(is_isset($data, 'feepayer')),
				'name' => 'feepayer',
				'work' => 'int',
			);				
			$options = pn_array_insert($options, 'note', $n_options); 
			
			return $options;
		}	

		function get_reserve_lists($m_id, $m_defin){
			
			$currencies = $this->curr_list;
			
			$purses = array();
			foreach($currencies as $curr){
				$purses[$m_id.'_'.strtolower($curr)] = strtoupper($curr);
			}
				
			return $purses; 
		}

		function update_reserve($code, $m_id, $m_defin){
			$sum = 0;
			
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if($purse){
				try {	
					$class = new PLASMAPAY_API(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin,'USERNAME'));
					$res = $class->get_balans();
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
		
		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){

			$trans_id = 0;
			$item_id = $item->id;
			
			$feepayer = intval(is_isset($paymerch_data,'feepayer'));
			$arrs = array('0' => 'myself', '1' => 'receiver');
			$fee = is_isset($arrs, $feepayer);
			
			$currency = mb_strtoupper($item->currency_code_get);
			
			$currencies = $this->curr_list;		
			if(!in_array($currency, $currencies)){
				$error[] = __('Wrong currency code','pn'); 
			}
					
			$account = trim($item->account_get);
			if(!$account){
				$error[] = __('Client wallet type does not match with currency code','pn');						
			}
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);

			if(count($error) == 0){
					
				$result = $this->set_ap_status($item, $test);	
				if($result){
					
					$notice = get_text_paymerch($m_id, $item);
					if(!$notice){ $notice = sprintf(__('ID order %s','pn'), $item->id); }
					$notice = trim(pn_maxf($notice,100));
						
					try {
						
						$class = new PLASMAPAY_API(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin,'USERNAME'));
						$res = $class->send_money($sum, $account, $fee, $notice, $currency);
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
					'from_account' => is_deffin($m_defin,'USERNAME'),
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

new paymerchant_plasmapay(__FILE__, 'PlasmaPay');