<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]WestWallet[:en_US][ru_RU:]WestWallet[:ru_RU]
description: [en_US:]WestWallet merchant[:en_US][ru_RU:]мерчант WestWallet[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_westwallet')){
	class merchant_westwallet extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
			}

			add_filter('list_user_notify',array($this,'user_mailtemp'));
			add_filter('list_admin_notify',array($this,'admin_mailtemp'));
			add_filter('list_notify_tags_generate_address1_westwallet',array($this,'mailtemp_tags'));
			add_filter('list_notify_tags_generate_address2_westwallet',array($this,'mailtemp_tags'));
			
			add_filter('bcc_keys',array($this,'set_keys'));
			add_filter('qr_keys',array($this,'set_keys'));			
		}		
		
		function get_map(){
			$map = array(
				'CONFIRM_COUNT'  => array(
					'title' => '[en_US:]The required number of transaction confirmations[:en_US][ru_RU:]Количество подтверждения платежа, чтобы считать его выполненым[:ru_RU]',
					'view' => 'input',	
				),
				'PUBLIC_KEY'  => array(
					'title' => '[en_US:]Public key[:en_US][ru_RU:]Публичный ключ[:ru_RU]',
					'view' => 'input',
				),	
				'PRIVATE_KEY'  => array(
					'title' => '[en_US:]Private key[:en_US][ru_RU:]Приватный ключ[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('CONFIRM_COUNT','PUBLIC_KEY','PRIVATE_KEY');
			return $arrs;
		}		
		
		function user_mailtemp($places_admin){
			$places_admin['generate_address1_westwallet'] = sprintf(__('Address generation for %s','pn'), 'WestWallet');
			return $places_admin;
		}

		function admin_mailtemp($places_admin){
			$places_admin['generate_address2_westwallet'] = sprintf(__('Address generation for %s','pn'), 'WestWallet');
			return $places_admin;
		}

		function mailtemp_tags($tags){
			
			$tags['bid_id'] = array(
				'title' => __('Order ID','pn'),
				'start' => '[bid_id]',
			);
			$tags['address'] = array(
				'title' => __('Address','pn'),
				'start' => '[address]',
			);
			$tags['sum'] = array(
				'title' => __('Amount','pn'),
				'start' => '[sum]',
			);
			$tags['currency_code_give'] = array(
				'title' => __('Currency code','pn'),
				'start' => '[currency_code_give]',
			);			
			$tags['count'] = array(
				'title' => __('Confirmations','pn'),
				'start' => '[count]',
			);
			$tags['dest_tag'] = array(
				'title' => __('Destination tag','pn'),
				'start' => '[dest_tag]',
			);			
			
			return $tags;
		}					
		
		function options($options, $data, $id, $place){ 
			$m_defin = $this->get_file_data($id);
			
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'check_api');
			$options = pn_array_unset($options, 'type');
			$options = pn_array_unset($options, 'help_type');			

			$text = '
			<div><strong>CALLBACK:</strong> <a href="'. get_mlink($id.'_status' . hash_url($id)) . '" target="_blank">'. get_mlink($id.'_status' . hash_url($id)) .'</a></div>
			';

			$options[] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);						
			
			return $options;
		}				
		
		function bidaction($temp, $m_id, $pay_sum, $item, $direction){
			global $wpdb, $bids_data;
			$script = get_mscript($m_id);
			if($script and $script == $this->name){
				$m_defin = $this->get_file_data($m_id);
				$m_data = get_merch_data($m_id);

				$item_id = $bids_data->id;		
				$currency = strtoupper($bids_data->currency_code_give);
				
				if($currency == 'USDT'){
					$currency_id_give = $bids_data->currency_id_give;
					$currency_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE id='$currency_id_give'");
					if(isset($currency_data->id)){
						$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
						if($xml_value == 'USDTERC'){
							$currency = 'USDT';
						} elseif($xml_value == 'USDTTRC'){	
							$currency = 'USDTTRC';
						}
					}		
				}
				
				$public_key = is_deffin($m_defin,'PUBLIC_KEY');
				$private_key = is_deffin($m_defin,'PRIVATE_KEY');
				
				$dest_tag = get_bids_meta($item_id, 'dest_tag');
				
				$to_account = pn_strip_input($bids_data->to_account);
				if(!$to_account){
					
					$show_error = intval(is_isset($m_data, 'show_error'));
					try {
						$class = new WestWallet($public_key, $private_key);
						$data = $class->generate_adress($currency, get_mlink($m_id.'_status' . hash_url($m_id)), $item_id);
						$to_account = pn_strip_input(is_isset($data, 'address'));
						$dest_tag = pn_strip_input(is_isset($data, 'dest_tag'));
					} catch (Exception $e) { 
						$this->logs($e->getMessage());
						if($show_error and current_user_can('administrator')){
							die($e->getMessage());
						}	
					}
					if($to_account){
						
						$to_account = pn_strip_input($to_account);
						$bids_data = update_bid_tb($item_id, 'to_account', $to_account, $bids_data);
						
						update_bids_meta($item_id, 'dest_tag', $dest_tag);
						
						$notify_tags = array();
						$notify_tags['[sitename]'] = pn_site_name();
						$notify_tags['[bid_id]'] = $item_id;
						$notify_tags['[address]'] = $to_account;
						$notify_tags['[sum]'] = $pay_sum;
						$notify_tags['[currency_code_give]'] = $bids_data->currency_code_give;
						$notify_tags['[dest_tag]'] = $dest_tag;
						$notify_tags['[count]'] = intval(is_deffin($m_defin,'CONFIRM_COUNT'));
						$notify_tags = apply_filters('notify_tags_generate_address_westwallet', $notify_tags);		

						$user_send_data = array();
						$result_mail = apply_filters('premium_send_message', 0, 'generate_address2_westwallet', $notify_tags, $user_send_data, get_admin_lang()); 
						
						$user_send_data = array(
							'user_email' => $bids_data->user_email,
						);	
						$user_send_data = apply_filters('user_send_data', $user_send_data, 'generate_address1_westwallet', $bids_data);	
						$result_mail = apply_filters('premium_send_message', 0, 'generate_address1_westwallet', $notify_tags, $user_send_data, $item->bid_locale);					
						
					} 
				}
				
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
						'dest_tag' => array(
							'title' => __('Destination tag','pn'),
							'copy' => $dest_tag,
							'text' => $dest_tag,
						),						
					);
					
					$descr = apply_filters('merchant_confirmations_text', sprintf(__('The order status changes to "Paid" when we get <b>%1$s</b> confirmations','pn'), is_deffin($m_defin,'CONFIRM_COUNT')), $bids_data);
					
					$temp .= $this->zone_table($pagenote, $list_data, $descr);																		
								
				} else { 
					$temp .= $this->zone_error(__('Error','pn'));
				} 
			}
			return $temp;
		} 
		
		function merchant_status(){
			global $wpdb;
		
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);
		
			$address = pn_sfilter(pn_maxf_mb(pn_strip_input(is_param_req('address')), 200)); 
			$id = intval(is_param_req('id'));
			$dest_tag = pn_strip_input(is_param_req('dest_tag'));
			$currency = strtoupper(is_param_req('currency'));
			$in_sum = is_sum(is_param_req('amount'));
			$status = pn_strip_input(is_param_req('status'));
			$confirmations = intval(is_param_req('blockchain_confirmations'));
			$blockchain_hash = pn_strip_input(is_param_req('blockchain_hash'));
			
			if($status != 'completed' and $status != 'pending'){
				$this->logs('Status not completted - ' . $status);
				die('Status not completted - '. $status);
			}
			
			$public_key = is_deffin($m_defin,'PUBLIC_KEY');
			$private_key = is_deffin($m_defin,'PRIVATE_KEY');
			$class = new WestWallet($public_key, $private_key);
			$sdata = $class->get_search($id);

			$address = pn_sfilter(pn_maxf_mb(pn_strip_input(is_isset($sdata, 'address')), 200)); 
			$id = pn_strip_input(is_isset($sdata, 'id'));
			$dest_tag = pn_strip_input(is_isset($sdata, 'dest_tag'));
			$currency = strtoupper(is_isset($sdata, 'currency'));
			$label = intval(is_isset($sdata, 'label'));
			$in_sum = is_sum(is_isset($sdata, 'amount'));
			$status = pn_strip_input(is_isset($sdata, 'status'));
			$confirmations = intval(is_isset($sdata, 'blockchain_confirmations'));
			$blockchain_hash = pn_strip_input(is_isset($sdata, 'blockchain_hash'));
			
			if($status != 'completed' and $status != 'pending'){
				$this->logs('Status not completted for history - '. $status);
				die('Status not completted for history - '. $status);
			}			
			
			$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE to_account='$address' AND id = '$label' AND status IN('new','techpay','coldpay')");
			$id = intval(is_isset($item, 'id'));
			$data = get_data_merchant_for_id($id, $item);
			
			$bid_err = $data['err'];
			$bid_status = $data['status'];
			$bid_m_id = $data['m_id'];
			$bid_m_script = $data['m_script'];
			
			if($bid_err > 0){
				$this->logs($id.' The application does not exist or the wrong ID');
				die('The application does not exist or the wrong ID');
			}			
			
			if($bid_m_script and $bid_m_script != $this->name or !$bid_m_script){	
				$this->logs($id.' wrong script');
				die('wrong script');
			}			
			
			if($bid_m_id and $m_id != $bid_m_id or !$bid_m_id){
				$this->logs($id.' not a faithful merchant');
				die('not a faithful merchant');				
			}

			if(check_trans_in($m_id, $blockchain_hash, $id)){
				$this->logs($id.' Error check trans in!');
				die('Error check trans in!');
			}			
			
			$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
			
			$bid_currency = $data['currency'];
			
			if($bid_currency == 'USDT' and isset($data['bids_data']->currency_id_give)){
				$currency_id_give = $data['bids_data']->currency_id_give;
				$currency_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE id='$currency_id_give'");
				if(isset($currency_data->id)){
					$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
					if($xml_value == 'USDTERC'){
						$bid_currency = 'USDT';
					} elseif($xml_value == 'USDTTRC'){	
						$bid_currency = 'USDTTRC';
					}
				}		
			}			
			
			$bid_sum = $data['pay_sum'];
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
			
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));			
				 
			if($bid_currency == $currency or $invalid_ctype > 0){
				if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){		
						
					$conf_count = intval(is_deffin($m_defin,'CONFIRM_COUNT'));
					do_action('merchant_confirm_count', $id, $confirmations, $data['bids_data'], $data['direction_data'], $conf_count, $this->name);
						
					$now_status = '';
						
					if($confirmations >= $conf_count and $status == 'completed') {
						if($bid_status == 'new' or $bid_status == 'coldpay'){ 
							$now_status = 'realpay';
						}
					} else {
						if($bid_status == 'new'){
							$now_status = 'coldpay';								
						}
					}	
					
					if($now_status){
						$params = array(
							'pay_purse' => $pay_purse,
							'sum' => $in_sum,
							'bid_sum' => $bid_sum,
							'bid_corr_sum' => $bid_corr_sum,
							'bid_status' => array('new','techpay','coldpay'),
							'trans_in' => $blockchain_hash,
							'currency' => $currency,
							'bid_currency' => $bid_currency,
							'invalid_ctype' => $invalid_ctype,
							'invalid_minsum' => $invalid_minsum,
							'invalid_maxsum' => $invalid_maxsum,
							'invalid_check' => $invalid_check,	
							'm_place' => $bid_m_id,
							'm_id' => $m_id,
							'm_data' => $m_data,
							'm_defin' => $m_defin,
						);
						
						set_bid_status($now_status, $id, $params, $data['direction_data']); 	
						die( 'ok' );								
					}
									
				} else {
					$this->logs($id.' The payment amount is less than the provisions');
					die('The payment amount is less than the provisions');
				}
			} else {
				$this->logs($id.' Wrong type of currency');
				die( 'Wrong type of currency' );
			}
		} 
	}
}
new merchant_westwallet(__FILE__, 'WestWallet');