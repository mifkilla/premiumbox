<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]BlockIo[:en_US][ru_RU:]BlockIo[:ru_RU]
description: [en_US:]BlockIo automatic payouts[:en_US][ru_RU:]авто выплаты BlockIo[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_blockio')){
	class paymerchant_blockio extends AutoPayut_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);	
		}	

		function get_map(){
			$map = array(
				'AP_BLOCKIO_PIN'  => array(
					'title' => '[en_US:]Secret PIN[:en_US][ru_RU:]Ваш Secret PIN[:ru_RU]',
					'view' => 'input',	
				),
				'AP_BLOCKIO_BTC'  => array(
					'title' => '[en_US:]Bitcoin API key[:en_US][ru_RU:]Ваш API Key для Bitcoin[:ru_RU]',
					'view' => 'input',	
				),
				'AP_BLOCKIO_LTC'  => array(
					'title' => '[en_US:]Litecoin API key[:en_US][ru_RU:]Ваш API Key для Litecoin[:ru_RU]',
					'view' => 'input',	
				),
				'AP_BLOCKIO_DOGE'  => array(
					'title' => '[en_US:]Dogecoin API key[:en_US][ru_RU:]Ваш API Key для Dogecoin[:ru_RU]',
					'view' => 'input',	
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('AP_BLOCKIO_PIN');
			return $arrs;
		}		

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'error_status'); 

			$opt = array(
				'low' => 'low',
				'medium' => 'medium',
				'high' => 'high',
				'manual' => __('manually','pn'),
			);
			$priority = trim(is_isset($data, 'priority'));
			if(!$priority){ $priority = 'low'; }
			
			$options['priority'] = array(
				'view' => 'select',
				'title' => __('Transaction fee','pn'),
				'options' => $opt,
				'default' => $priority,
				'name' => 'priority',
				'work' => 'input',
			);
			$options['prioBTC'] = array(
				'view' => 'input',
				'title' => sprintf(__('Customize fee manually (in bytes) %s','pn'), 'BTC'),
				'default' => is_isset($data, 'prioBTC'),
				'name' => 'prioBTC',
				'work' => 'sum',
			);
			$options['prioLTC'] = array(
				'view' => 'input',
				'title' => sprintf(__('Customize fee manually (in bytes) %s','pn'), 'LTC'),
				'default' => is_isset($data, 'prioLTC'),
				'name' => 'prioLTC',
				'work' => 'sum',
			);
			$options['prioDOGE'] = array(
				'view' => 'input',
				'title' => sprintf(__('Customize fee manually (in bytes) %s','pn'), 'DOGE'),
				'default' => is_isset($data, 'prioDOGE'),
				'name' => 'prioDOGE',
				'work' => 'sum',
			);			
			
			return $options;
		}				

		function get_reserve_lists($m_id, $m_defin){
			
			$purses = array(
				$m_id.'_1' => 'BTC',
				$m_id.'_2' => 'LTC',
				$m_id.'_3' => 'DOGE',
			);
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;

			$purses = array(
				$m_id.'_1' => is_deffin($m_defin,'AP_BLOCKIO_BTC'),
				$m_id.'_2' => is_deffin($m_defin,'AP_BLOCKIO_LTC'),
				$m_id.'_3' => is_deffin($m_defin,'AP_BLOCKIO_DOGE'),
			);
					
			$api = trim(is_isset($purses, $code));
			if($api){
						
				try{
					
					$block_io = new BlockIo($api, is_deffin($m_defin,'AP_BLOCKIO_PIN'), 2);
					$res = $block_io->get_balance();	
					if(isset($res->status) and $res->status == 'success' and isset($res->data->available_balance)){
						$rezerv = (string)$res->data->available_balance;
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
					
			$enable = array('BTC','LTC','DOGE');		
			if(!in_array($vtype, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}					
					
			$accounts = explode(',',$item->account_get);
			$account = trim(is_isset($accounts, 0));
			if (!$account) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}				
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data));
			
			$minsum = '0.00005';
			if($sum < $minsum){
				$error[] = sprintf(__('Minimum payment amount is %s','pn'), $minsum);
			}		
					
			$api = 0;
			if($vtype == 'BTC' and is_deffin($m_defin,'AP_BLOCKIO_BTC')){
				$api = is_deffin($m_defin,'AP_BLOCKIO_BTC');
			} elseif($vtype == 'LTC' and is_deffin($m_defin,'AP_BLOCKIO_LTC')){
				$api = is_deffin($m_defin,'AP_BLOCKIO_LTC');
			} elseif($vtype == 'DOGE' and is_deffin($m_defin,'AP_BLOCKIO_DOGE')){
				$api = is_deffin($m_defin,'AP_BLOCKIO_DOGE');
			}
					
			if(!$api){	
				$error[] = 'Error interfaice';
			}
						
			if(count($error) == 0){

				$result = $this->set_ap_status($item, $test);				
				if($result){				
					
					try{
							
						$block_io = new BlockIo($api, is_deffin($m_defin,'AP_BLOCKIO_PIN'), 2);
								
						$priority = trim(is_isset($paymerch_data, 'priority'));
						$prio = array('low','medium','high','manual');
						if(!in_array($priority, $prio)){
							$priority = 'low';
						}
				
						$res_data = array('amounts' => $sum, 'to_addresses' => $account, 'pin' => is_deffin($m_defin,'AP_BLOCKIO_PIN'));
						
						if($priority == 'manual'){
							$res_data['priority'] = 'custom';
							$res_data['custom_network_fee'] = is_sum(is_isset($paymerch_data, 'prio' . $vtype));
						} else {
							$res_data['priority'] = $priority;
						}
						
						$res = $block_io->withdraw($res_data);
						if(isset($res->data->txid)){
							$trans_id = $res->data->txid;
						}								
						if(!isset($res->status) or $res->status != 'success' or !isset($res->data->amount_sent)){
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
				set_bid_status('success', $item_id, $params, $direction);  					
						 
				if($place == 'admin'){
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
				} 
			}			
		}				
		
	}
}

new paymerchant_blockio(__FILE__, 'BlockIo');