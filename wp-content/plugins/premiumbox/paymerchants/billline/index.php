<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]BillLine[:en_US][ru_RU:]BillLine[:ru_RU]
description: [en_US:]BillLine automatic payouts[:en_US][ru_RU:]авто выплаты BillLine[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_billline')){
	class paymerchant_billline extends AutoPayut_Premiumbox{
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_ap_'. $id .'_callback' . hash_url($id, 'ap'), array($this,'merchant_callback'));
			}			
		}	
		
		function get_map(){
			$map = array(
				'MERCH_ID'  => array(
					'title' => '[en_US:]Merchant ID[:en_US][ru_RU:]ID мерчанта[:ru_RU]',
					'view' => 'input',	
				),
				'SECRET_KEY'  => array(
					'title' => '[en_US:]Secret key[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
					'view' => 'input',
				),
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('MERCH_ID','SECRET_KEY');
			return $arrs;
		}

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, array('note','checkpay'));			
			
			$options['curr_type'] = array(
				'title' => __('Currency', 'pn'),
				'view' => 'select',
				'options' => array('0'=>'UAH', '1'=>'RUB','2'=>'USD','3'=>'EUR'),
				'default' => is_isset($data, 'curr_type'),
				'name' => 'curr_type',
				'work' => 'input',
			);				
			
			$text = '
			<div><strong>Callback URL:</strong> <a href="'. get_mlink('ap_' . $id .'_callback' . hash_url($id, 'ap')) .'" target="_blank" rel="noreferrer noopener">'. get_mlink('ap_' . $id .'_callback' . hash_url($id, 'ap')) .'</a></div>
			<div><strong>CRON:</strong> <a href="'. get_mlink('ap_'. $id .'_cron' . hash_url($id, 'ap')) .'" target="_blank" rel="noreferrer noopener">'. get_mlink('ap_'. $id .'_cron' . hash_url($id, 'ap')) .'</a></div>
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
			$purses[$m_id . '_currency'] = $this->curency_by_option($m_id);				
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			
			if($code == $m_id . '_currency'){
						
				try{
					
					$class = new AP_BillLine(is_deffin($m_defin, 'MERCH_ID'), is_deffin($m_defin, 'SECRET_KEY'));
					$currency = $this->curency_by_option($m_id);
					$rezerv = $class->get_balans($currency);
									
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
		
		function curency_by_option($m_id){
			$m_data = get_paymerch_data($m_id);
			$currency = 'UAH';
			$curr_type = intval(is_isset($m_data, 'curr_type'));
			if($curr_type == 1){
				$currency = 'RUB';
			} elseif($curr_type == 2){
				$currency = 'USD';
			} elseif($curr_type == 3){
				$currency = 'EUR';				
			}
			return $currency;
		}

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){
			
			$item_id = $item->id;
			$trans_id = 0;			
			
			$vtype = mb_strtoupper($item->currency_code_get);
			$vtype = str_replace('RUR','RUB',$vtype);						
					
			$account = $item->account_get;
			if (!$account) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}					
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
					
			if(count($error) == 0){
				$result = $this->set_ap_status($item, $test);
				if($result){				
					try {
						$class = new AP_BillLine(is_deffin($m_defin, 'MERCH_ID'), is_deffin($m_defin, 'SECRET_KEY'));
						$res = $class->payout($sum, $vtype, $item_id, $account);
						if($res['error'] == 1){
							$error[] = __('Payout error','pn');
							$pay_error = 1;
						} else {
							$trans_id = $res['trans_id'];
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
					'from_account' => is_deffin($m_defin,'MERCH_ID'),
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

		function merchant_callback(){
			$m_id = key_for_url('_callback', 'ap_');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_paymerch_data($m_id);
			
			$this->doing_cron($m_id, $m_defin, $m_data);
			
			echo 'OK';
			exit;
		}

		function cron($m_id, $m_defin, $m_data){
			$this->doing_cron($m_id, $m_defin, $m_data);	
		}

		function doing_cron($m_id, $m_defin, $m_data){
		global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$class = new AP_BillLine(is_deffin($m_defin, 'MERCH_ID'), is_deffin($m_defin, 'SECRET_KEY'));
			$currency = $this->curency_by_option($m_id);
			$start_time = current_time('timestamp') - (2 * DAY_IN_SECONDS);
			$finish_time = current_time('timestamp');
			$orders = $class->get_history($currency, $start_time, $finish_time);
			
			if(is_array($orders) and count($orders) > 0){
				$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
				foreach($items as $item){
					$item_id = $item->id;
					if(isset($orders['ap' . $item_id])){
						$order = $orders['ap' . $item_id];
						$check_status = strtolower($order['status']);
						
						if($check_status == 'success'){
							
							$params = array(
								'system' => 'system',
								'trans_out' => is_isset($order,'id'),
								'bid_status' => array('coldsuccess'),
								'm_place' => 'cron ' .$m_id,
								'm_id' => $m_id,
								'm_defin' => $m_defin,
								'm_data' => $m_data,
							);
							set_bid_status('success', $item->id, $params, 1);
								
						} elseif(!in_array($check_status, array('pending'))){
							
							$this->reset_cron_status($item, $error_status, $m_id);
								
						}	
					}
				}
			}			
		}		
	}
}
new paymerchant_billline(__FILE__, 'BillLine');