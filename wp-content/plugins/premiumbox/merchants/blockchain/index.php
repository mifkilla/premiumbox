<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Blockchain[:en_US][ru_RU:]Blockchain[:ru_RU]
description: [en_US:]Blockchain merchant[:en_US][ru_RU:]мерчант Blockchain[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_blockchain')){
	class merchant_blockchain extends Merchant_Premiumbox{

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
			}

			add_filter('list_user_notify',array($this,'user_mailtemp'));
			add_filter('list_admin_notify',array($this,'admin_mailtemp'));
			add_filter('list_notify_tags_generate_address1_blockchain',array($this,'mailtemp_tags'));
			add_filter('list_notify_tags_generate_address2_blockchain',array($this,'mailtemp_tags'));
			
			add_filter('bcc_keys',array($this,'set_keys'));
			add_filter('qr_keys',array($this,'set_keys'));
		}	
		
		function get_map(){
			$map = array(
				'CONFIRM_COUNT'  => array(
					'title' => '[en_US:]The required number of transaction confirmations[:en_US][ru_RU:]Количество подтверждения платежа, чтобы считать его выполненым[:ru_RU]',
					'view' => 'input',	
				),
				'API_KEY'  => array(
					'title' => '[en_US:]API key[:en_US][ru_RU:]API key[:ru_RU]',
					'view' => 'input',
				),	
				'XPUB'  => array(
					'title' => '[en_US:]XPUB[:en_US][ru_RU:]XPUB[:ru_RU]',
					'view' => 'input',
				),
				'SECRET'  => array(
					'title' => '[en_US:]Password №1. Any characters with no spaces (responsible for the security of payment)[:en_US][ru_RU:]Пароль №1. Любые символы без пробелов (отвечает за безопасность платежа)[:ru_RU]',
					'view' => 'input',
				),
				'SECRET2'  => array(
					'title' => '[en_US:]Password №2. Any characters with no spaces (responsible for the security of payment)[:en_US][ru_RU:]Пароль №2. Любые символы без пробелов (отвечает за безопасность платежа)[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('CONFIRM_COUNT','API_KEY','XPUB','SECRET','SECRET2');
			return $arrs;
		}		
		
		function user_mailtemp($places_admin){
			$places_admin['generate_address1_blockchain'] = sprintf(__('Address generation for %s','pn'), 'Blockchain');
			return $places_admin;
		}

		function admin_mailtemp($places_admin){
			$places_admin['generate_address2_blockchain'] = sprintf(__('Address generation for %s','pn'), 'Blockchain');
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
			
			return $tags;
		}						
		
		function options($options, $data, $id, $place){ 
			$m_defin = $this->get_file_data($id);
			
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'check_api');
			$options = pn_array_unset($options, 'type');
			$options = pn_array_unset($options, 'help_type');
			
			$options['private_line'] = array(
				'view' => 'line',
			);
			$options['gap_limit'] = array(
				'view' => 'input',
				'title' => __('Gap limit','pn'),
				'default' => is_isset($data, 'gap_limit'),
				'name' => 'gap_limit',
				'work' => 'int',
			);	
			$options['help_gap_limit'] = array(
				'view' => 'help',
				'title' => __('More info','pn'),
				'default' => __('Recommended value of Gap limit is 100. Description of Gap limit from Blockchain API documentation: As defined in BIP 44, wallet software will not scan past 20 unused addresses. Given enough requests from this API that don’t have a matching payment, you could generate addresses past this horizon, which would make spending funds paid to those addresses quite difficult. For this reason, this API will return an error and refuse to generate new addresses if it detects it would create a gap of over 20 unused addresses. If you encounter this error, you will either need to switch to a new xPub (within the same wallet is fine), or receive a payment to one of the previous 20 created addresses You can control this behavior by optionally passing “Gap limit” as an extra parameter. Please note, this will not increase the number of addresses that will be monitored by our servers. Passing the “Gap limit” parameter changes the maximum allowed gap before the API will stop generating new addresses. Using this feature will require you understand the gap limitation and how to handle it.','pn'),
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
				$currency = $bids_data->currency_code_give;
				$currency_m = strtolower($currency);
					
				$my_xpub = is_deffin($m_defin,'XPUB');
				$my_api_key = is_deffin($m_defin,'API_KEY');
				
				$to_account = pn_strip_input($bids_data->to_account);
				if(!$to_account){
					
					$show_error = intval(is_isset($m_data, 'show_error'));
					$gap_limit = intval(is_isset($m_data, 'gap_limit'));

					$my_callback_url = get_mlink($m_id.'_status'. hash_url($m_id)) .'?invoice_id='. $item_id .'&secret='. urlencode(is_deffin($m_defin,'SECRET')) .'&secret2='. urlencode(is_deffin($m_defin,'SECRET2'));
					$root_url = 'https://api.blockchain.info/v2/receive';
					$parameters = 'xpub=' .$my_xpub. '&callback=' .urlencode($my_callback_url). '&key=' .$my_api_key . '&gap_limit=' . $gap_limit;
					$response1 = @file_get_contents($root_url . '?' . $parameters);
					$this->logs($response1);
					$response = @json_decode($response1);				
					$this->logs($response1);
					if(isset($response->address)){
						
						$to_account = pn_strip_input($response->address);
						$bids_data = update_bid_tb($item_id, 'to_account', $to_account, $bids_data);
						
						$notify_tags = array();
						$notify_tags['[sitename]'] = pn_site_name();
						$notify_tags['[bid_id]'] = $item_id;
						$notify_tags['[address]'] = $to_account;
						$notify_tags['[sum]'] = $pay_sum;
						$notify_tags['[currency_code_give]'] = $bids_data->currency_code_give;
						$notify_tags['[count]'] = intval(is_deffin($m_defin,'CONFIRM_COUNT'));
						$notify_tags = apply_filters('notify_tags_generate_address_blockchain', $notify_tags);		

						$user_send_data = array();
						$result_mail = apply_filters('premium_send_message', 0, 'generate_address2_blockchain', $notify_tags, $user_send_data, get_admin_lang()); 
						
						$user_send_data = array(
							'user_email' => $bids_data->user_email,
						);	
						$user_send_data = apply_filters('user_send_data', $user_send_data, 'generate_address1_blockchain', $bids_data);	
						$result_mail = apply_filters('premium_send_message', 0, 'generate_address1_blockchain', $notify_tags, $user_send_data, $bids_data->bid_locale);					
						
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
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);	
	
			$sAddress = isset( $_REQUEST['address'] ) ? $_REQUEST['address'] : null; 
			$secret = isset( $_REQUEST['secret'] ) ? $_REQUEST['secret'] : null; 
			$secret2 = isset( $_REQUEST['secret2'] ) ? $_REQUEST['secret2'] : null; 
			$invoice_id = isset( $_REQUEST['invoice_id'] ) ? $_REQUEST['invoice_id'] : null; 
			$sTransferHash = isset( $_REQUEST['transaction_hash'] ) ? $_REQUEST['transaction_hash'] : null;
			$iConfirmCount = isset( $_REQUEST['confirmations'] ) ? $_REQUEST['confirmations'] - 0 : 0;
			$in_sum = isset( $_REQUEST['value'] ) ? $_REQUEST['value'] / 100000000 : 0;

			if(urldecode($secret) != is_deffin($m_defin,'SECRET')){
				$this->logs('wrong secret!');
				die('wrong secret!');
			}

			if(urldecode($secret2) != is_deffin($m_defin,'SECRET2')){
				$this->logs('wrong secret!');
				die('wrong secret!');
			}
  
			$currency = 'BTC';
  
			$id = intval($invoice_id);
			$data = get_data_merchant_for_id($id);
			
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

			if(check_trans_in($m_id, $sTransferHash, $id)){
				$this->logs($id.' Error check trans in!');
				die('Error check trans in!');
			}			
			
			$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
			
			$bid_currency = $data['currency'];
			
			$bid_sum = $data['pay_sum'];
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
			
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));			
				 
			if($bid_currency == $currency or $invalid_ctype > 0){
				if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){		
						
					$conf_count = intval(is_deffin($m_defin,'CONFIRM_COUNT'));
					do_action('merchant_confirm_count', $id, $iConfirmCount, $data['bids_data'], $data['direction_data'], $conf_count, $this->name);
						
					$now_status = '';
						
					if( $iConfirmCount >= $conf_count ) {
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
							'bid_status' => array('new','techpay','coldpay'),
							'bid_corr_sum' => $bid_corr_sum,
							'trans_in' => $sTransferHash,
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
								
						if($now_status == 'realpay'){
							echo '*ok*';
							exit;
						}	
					}		
				} else {
					$this->logs($id.' Wrong type of currency');
					die('Wrong type of currency');
				}
			} else {
				$this->logs($id.' In the application the wrong status');
				die( 'In the application the wrong status' );
			}
		}
	}
}

new merchant_blockchain(__FILE__, 'Blockchain');