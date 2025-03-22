<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]PlasmaPay[:en_US][ru_RU:]PlasmaPay[:ru_RU]
description: [en_US:]PlasmaPay merchant[:en_US][ru_RU:]мерчант PlasmaPay[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_plasmapay')){
	class merchant_plasmapay extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 0);
			
			add_action('before_merchant_admin', array($this,'before_merchant_admin'), 10, 3);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_register', array($this,'merchant_register'));
				add_action('premium_merchant_'. $id .'_unregister', array($this,'merchant_unregister'));
				add_action('premium_merchant_'. $id .'_webhook' . hash_url($id), array($this,'merchant_webhook'));
			}
			
			add_filter('qr_keys',array($this,'set_keys'));
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

		function before_merchant_admin($now_script, $data, $data_id){
			if($now_script and $now_script == $this->name){
				$wh = get_option('wh_plasmapay');
				if(!is_array($wh)){ $wh = array(); }
				
				echo '<div class="premium_reply pn_error">'. sprintf(__('<a href="%1s" target="_blank">Webhook registration</a>','pn'), get_mlink($data_id.'_register')) .'</div>';	
				if(isset($wh[$data_id])){
					echo '<div class="premium_reply pn_error">'. sprintf(__('<a href="%1s" target="_blank">Webhook removal</a>','pn'), get_mlink($data_id.'_unregister')) .'</div>';
				}	
			}
		}
		
		function merchant_register(){
			$m_id = key_for_url('_register');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			if(current_user_can('administrator') or current_user_can('pn_merchants')){
				
				$class = new PLASMAPAY_API(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin,'USERNAME'));
				$web_url = get_mlink($m_id.'_webhook');
				$res = $class->set_webhook($web_url);
				if(isset($res['id'])){
					$wh = get_option('wh_plasmapay');
					if(!is_array($wh)){ $wh = array(); }
					$wh[$m_id] = $res['id'];
					update_option('wh_plasmapay', $wh);
					
					pn_display_mess(__('Webhook successfully registered','pn'), __('Webhook successfully registered','pn'), 'true');
				} else {
					print_r($res);
				}
				
			} else {
				pn_display_mess(__('Error! Insufficient privileges','pn'));	
			}
		}
		
		function merchant_unregister(){
			$m_id = key_for_url('_unregister');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			
			if(current_user_can('administrator') or current_user_can('pn_merchants')){
				$class = new PLASMAPAY_API(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin,'USERNAME'));
				$wh = get_option('wh_plasmapay');
				if(!is_array($wh)){ $wh = array(); }
				$web_id = is_isset($wh, $m_id);
				$res = $class->delete_webhook($web_id);
				if(isset($res['success'])){
					if(isset($wh[$this->name])){
						unset($wh[$this->name]);
					}
					update_option('wh_plasmapay', $wh);
					
					pn_display_mess(__('Webhook successfully delete','pn'), __('Webhook successfully delete','pn'), 'true');
				} else {
					print_r($res);
				}
			} else {
				pn_display_mess(__('Error! Insufficient privileges','pn'));	
			}			
		}		
		
		function options($options, $data, $id, $place){ 
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'help_resulturl');
			$options = pn_array_unset($options, 'type');
			$options = pn_array_unset($options, 'check_api'); 
			$options = pn_array_unset($options, 'help_type'); 
			$options = pn_array_unset($options, 'show_error'); 
			$options = pn_array_unset($options, 'invalid_ctype'); 
			$options = pn_array_unset($options, 'check'); 
			$options = pn_array_unset($options, 'note');
			
			return $options;	
		}				

		function bidaction($temp, $m_id, $pay_sum, $item, $direction){
			global $wpdb, $bids_data;
			$script = get_mscript($m_id);
			if($script and $script == $this->name){
				$m_defin = $this->get_file_data($m_id);
				$m_data = get_merch_data($m_id);

				$item_id = $bids_data->id;
				$username = is_deffin($m_defin,'USERNAME');
				$currency = strtoupper($bids_data->currency_code_give);
				
				$to_account = pn_strip_input($bids_data->to_account);
				if(!$to_account){
					
					$to_account = pn_strip_input($username);
					$bids_data = update_bid_tb($item_id, 'to_account', $to_account, $bids_data);
				}
				
				$params = array(
					'sum' => 0,
					'bid_status' => array('new'),
					'm_place' => $m_id,
					'system' => 'user',
					'm_id' => $m_id,
					'm_data' => $m_data,
					'm_defin' => $m_defin,
				);
				set_bid_status('techpay', $bids_data->id, $params, $direction); 
				
				if($to_account){	
				
					$pagenote = get_pagenote($m_id, $bids_data, $pay_sum);
					
					$list_data = array(
						'amount' => array(
							'title' => __('Amount','pn'),
							'copy' => $pay_sum,
							'text' => $pay_sum .' '. $currency,
						),
						'account' => array(
							'title' => __('Address','pn'),
							'copy' => $to_account,
							'text' => $to_account,
						),								
					);
					
					$descr = '';
					
					$temp .= $this->zone_table($pagenote, $list_data, $descr);																			

				} else { 
					$temp .= $this->zone_error(__('Error','pn'));
				} 
			}
			return $temp;					
		}

		function merchant_webhook(){
		global $wpdb;
		
			$m_id = key_for_url('_webhook');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			$username = pn_strip_input(is_deffin($m_defin,'USERNAME'));
			
			$request = file_get_contents('php://input');
			$res = @json_decode($request, true);
			
			do_action('merchant_logs', $this->name, $res, $m_id, $m_defin, $m_data);
			
			if(isset($res['eventType'], $res['tx'], $res['tx']['txId']) and $res['eventType'] == 'confirm'){
				$tx_id = $res['tx']['txId'];
				
				if(check_trans_in($m_id, $tx_id, 0)){
					$this->logs('Error check trans in!');
					die('Error check trans in!');
				}
			
				$class = new PLASMAPAY_API(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin,'USERNAME'));
				$res = $class->get_search($tx_id);
				if(isset($res['type']) and $res['type'] == 'transfer'){
					$from = pn_strip_input($res['from']);
					$to = pn_strip_input($res['to']);
					$currency = pn_strip_input($res['currency']);
					$sum = is_sum($res['quantity']);
					if($username == $to){
						$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status IN('techpay') AND m_in = '$m_id' AND to_account = '$username' AND account_give = '$from' AND currency_code_give = '$currency'");
						foreach($items as $item){
							$item_id = $item->id;

							$data = get_data_merchant_for_id($item_id);
							$bid_m_id = $data['m_id'];
							$bid_m_script = $data['m_script'];
							$bid_err = $data['err'];
							
							$in_sum = $sum;
							$status = $data['status'];
										
							$bid_currency = mb_strtoupper($data['currency']);
										
							$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
											
							$bid_sum = is_sum($data['pay_sum']);	
							$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
										
							$invalid_ctype = 0;//intval(is_isset($m_data, 'invalid_ctype'));
							$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
							$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
							$invalid_check = 0;//intval(is_isset($m_data, 'check'));								
								
							$get_status = array('techpay');	
							if(in_array($status, $get_status)){
								if($bid_err == 0 and $bid_m_id and $m_id == $bid_m_id and $bid_m_script and $bid_m_script == $this->name){
									if($bid_currency == $currency or $invalid_ctype > 0){
										if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){
											$params = array(
												'pay_purse' => $pay_purse,
												'sum' => $in_sum,
												'bid_sum' => $bid_sum,
												'bid_corr_sum' => $bid_corr_sum,
												'bid_status' => array('techpay'),
												'trans_in' => $tx_id,
												'currency' => $currency,
												'bid_currency' => $bid_currency,
												'invalid_ctype' => $invalid_ctype,
												'invalid_minsum' => $invalid_minsum,
												'invalid_maxsum' => $invalid_maxsum,
												'invalid_check' => $invalid_check,
												'm_place' => $m_id,
												'm_id' => $m_id,
												'm_data' => $m_data,
												'm_defin' => $m_defin,
											);
											set_bid_status('realpay', $item_id, $params, $data['direction_data']);
											
											break;
										} else {
											$this->logs($item->id . ' The payment amount is less than the provisions');
										}		
									} else {
										$this->logs($item->id . ' Wrong type of currency');
									}
								} else {
									$this->logs($item->id.' error merchant');
								}
							}
						}	
					}
				}			
			}			
		}		
	}
}

new merchant_plasmapay(__FILE__, 'PlasmaPay');