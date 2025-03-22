<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]BlockIo[:en_US][ru_RU:]BlockIo[:ru_RU]
description: [en_US:]Block.io merchant[:en_US][ru_RU:]мерчант Block.io[:ru_RU]
version: 2.2
*/

/* 
if (!extension_loaded('gmp')) {
    return;
}

if (!extension_loaded('mcrypt')) {
    return;
}

if (!extension_loaded('curl')) {
    return;
}
*/

if(!class_exists('merchant_blockio')){
	class merchant_blockio extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			 
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_archive_cron' . hash_url($id), array($this,'merchant_archive_cron'));
			}			 
			 
			add_filter('list_user_notify',array($this,'user_mailtemp'));
			add_filter('list_admin_notify',array($this,'admin_mailtemp'));
			add_filter('list_notify_tags_generate_address1_blockio',array($this,'mailtemp_tags'));
			add_filter('list_notify_tags_generate_address2_blockio',array($this,'mailtemp_tags'));
			
			add_filter('bcc_keys',array($this,'set_keys'));
			add_filter('qr_keys',array($this,'set_keys'));
		}	
		
		function get_map(){
			$map = array(
				'BLOCKIO_CV'  => array(
					'title' => '[en_US:]The required number of transaction confirmations[:en_US][ru_RU:]Количество подтверждения платежа, чтобы считать его выполненым[:ru_RU]',
					'view' => 'input',
				),	
				'BLOCKIO_PIN'  => array(
					'title' => '[en_US:]Secret PIN[:en_US][ru_RU:]Ваш Secret PIN[:ru_RU]',
					'view' => 'input',
				),
				'BLOCKIO_BTC'  => array(
					'title' => '[en_US:]Bitcoin API key[:en_US][ru_RU:]Ваш API Key для Bitcoin[:ru_RU]',
					'view' => 'input',
				),
				'BLOCKIO_LTC'  => array(
					'title' => '[en_US:]Litecoin API key[:en_US][ru_RU:]Ваш API Key для Litecoin[:ru_RU]',
					'view' => 'input',
				),
				'BLOCKIO_DOGE'  => array(
					'title' => '[en_US:]Dogecoin API key[:en_US][ru_RU:]Ваш API Key для Dogecoin[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('BLOCKIO_PIN');
			return $arrs;
		}		
		
		function user_mailtemp($places_admin){
			$places_admin['generate_address1_blockio'] = sprintf(__('Address generation for %s','pn'), 'BlockIo');
			return $places_admin;
		}

		function admin_mailtemp($places_admin){
			$places_admin['generate_address2_blockio'] = sprintf(__('Address generation for %s','pn'), 'BlockIo');
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
			$options = pn_array_unset($options, array('help_type','enableip'));		

			$text = '
			<div><strong>CRON URL:</strong> <a href="'. get_mlink($id.'_cron'. hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_cron'. hash_url($id)) .'</a></div>
			<div><strong>CRON ARCHIVE URL:</strong> <a href="'. get_mlink($id.'_archive_cron' . hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_archive_cron' . hash_url($id)) .'</a></div>	
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
				$currency = $bids_data->currency_code_give;
				$currency_m = strtolower($currency);
					
				$to_account = pn_strip_input($bids_data->to_account);
				if(!$to_account){
					
					$show_error = intval(is_isset($m_data, 'show_error'));
					
					$api = 0;
					if($currency == 'BTC'){
						$api = is_deffin($m_defin,'BLOCKIO_BTC');
					} elseif($currency == 'LTC'){
						$api = is_deffin($m_defin,'BLOCKIO_LTC');
					} elseif($currency == 'DOGE'){
						$api = is_deffin($m_defin,'BLOCKIO_DOGE');
					}				

					try{
						$block_io = new BlockIo($api, is_deffin($m_defin,'BLOCKIO_PIN'),2);
						$res = $block_io->get_new_address();	
						$this->logs($res);
						if(isset($res->status) and $res->status == 'success' and isset($res->data->address)){
							$to_account = pn_strip_input($res->data->address);
						}		
					}
					catch (Exception $e)
					{
						$this->logs($e->getMessage());
						if($show_error and current_user_can('administrator')){
							die($e->getMessage());
						}	
					}	

					if($to_account){
						
						$bids_data = update_bid_tb($item_id, 'to_account', $to_account, $bids_data);
						
						$notify_tags = array();
						$notify_tags['[sitename]'] = pn_site_name();
						$notify_tags['[bid_id]'] = $item_id;
						$notify_tags['[address]'] = $to_account;
						$notify_tags['[sum]'] = $pay_sum;
						$notify_tags['[currency_code_give]'] = $bids_data->currency_code_give;
						$notify_tags['[count]'] = intval(is_deffin($m_defin,'BLOCKIO_CV'));
						$notify_tags = apply_filters('notify_tags_generate_address_blockio', $notify_tags);		

						$user_send_data = array();
						$result_mail = apply_filters('premium_send_message', 0, 'generate_address2_blockio', $notify_tags, $user_send_data, get_admin_lang()); 
						
						$user_send_data = array(
							'user_email' => $bids_data->user_email,
						);	
						$user_send_data = apply_filters('user_send_data', $user_send_data, 'generate_address1_blockio', $bids_data);		
						$result_mail = apply_filters('premium_send_message', 0, 'generate_address1_blockio', $notify_tags, $user_send_data, $bids_data->bid_locale);					
						
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
					
					$descr = apply_filters('merchant_confirmations_text', sprintf(__('The order status changes to "Paid" when we get <b>%1$s</b> confirmations','pn'), is_deffin($m_defin,'BLOCKIO_CV')), $bids_data);
					
					$temp .= $this->zone_table($pagenote, $list_data, $descr);					
			
				} else { 
					$temp .= $this->zone_error(__('Error','pn'));
				} 
			}
			return $temp;							
		}  

		function merchant_archive_cron(){
			global $wpdb;

			$m_id = key_for_url('_archive');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			$this->archive_request($m_id, $m_defin, 50);
			
			_e('Done', 'pn');
		}

		function cron($m_id, $m_defin, $m_data){
			global $wpdb;

			$currencies = array('BTC','LTC','DOGE');
			foreach($currencies as $curr){
				$api = is_deffin($m_defin,'BLOCKIO_'.$curr);
				if($api){
				
					$show_error = intval(is_isset($m_data, 'show_error'));
				
					try {
						
						$block_io = new BlockIo($api, is_deffin($m_defin,'BLOCKIO_PIN'), 2);
						$res = $block_io->get_transactions(array('type' => 'received'));
						$this->logs($res);
						if(isset($res->status) and $res->status == 'success' and isset($res->data->network) and isset($res->data->txs)){
							if($curr == $res->data->network){			
								$n_conf = intval(is_deffin($m_defin,'BLOCKIO_CV'));
						
								foreach($res->data->txs as $data){
									$confirmations = $data->confirmations;
										
									$sender = '';
									if(isset($data->senders[0])){
										$sender = $data->senders[0];
									}
										
									$amount = '0';
									if(isset($data->amounts_received[0]->amount)){
										$amount = is_sum($data->amounts_received[0]->amount);
									}

									$address = '';
									if(isset($data->amounts_received[0]->recipient)){
										$address = $data->amounts_received[0]->recipient;
									}	
									
									$trans_id = 0;
									if(isset($data->txid)){
										$trans_id = $data->txid;
									}									

									if($amount > 0 and $address){
										$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status IN ('new','coldpay') AND currency_code_give='$curr' AND to_account='$address' AND m_in='$m_id'");
										if(isset($item->id)){
											$bid_status = $item->status;
												
											$pay_purse = apply_filters('pay_purse_merchant', $sender, $m_data, $m_id);
											
											$id = $item->id;
											$bid_data = get_data_merchant_for_id($id, $item);
											
											$bid_sum = $bid_data['pay_sum'];
											$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $m_id);
											
											$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
											$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
											$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
											$invalid_check = intval(is_isset($m_data, 'check'));
											
											if(!check_trans_in($m_id, $trans_id, $item->id)){
												if($amount >= $bid_corr_sum or $invalid_minsum > 0){
														
													do_action('merchant_confirm_count', $item->id, $confirmations, $item, $bid_data['direction_data'], $n_conf, $this->name);	

													$now_status = '';
													
													if($confirmations >= $n_conf){
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
															'sum' => $amount,
															'bid_sum' => $bid_sum,
															'bid_status' => array('new','coldpay'),
															'bid_corr_sum' => $bid_corr_sum,
															'trans_in' => $trans_id,
															'invalid_ctype' => $invalid_ctype,
															'invalid_minsum' => $invalid_minsum,
															'invalid_maxsum' => $invalid_maxsum,
															'invalid_check' => $invalid_check,	
															'm_place' => $m_id,
															'm_id' => $m_id,
															'm_data' => $m_data,
															'm_defin' => $m_defin,
														);
														set_bid_status($now_status, $item->id, $params, $bid_data['direction_data']);	 												
													}
												} else {
													$this->logs($item->id . ' bid error');
												}
											} else {
												$this->logs($item->id . ' Error check trans in!');
											}
										}
									}
								}
							}
						} 
					}
					catch (Exception $e)
					{
						$this->logs($e->getMessage());
						if($show_error and current_user_can('administrator')){
							die($e->getMessage());
						}
					}		
				}
			}
		}
		
		function archive_request($m_id, $m_defin, $limit=20){
			global $wpdb;
			
			$limit = intval($limit);
			$apis = array();
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'success' AND to_account != '' AND m_in='$m_id' ORDER BY id DESC LIMIT $limit");
			foreach($items as $item){
				$currency = trim(mb_strtoupper($item->currency_code_give));
				$to_account = pn_strip_input($item->to_account);
				if($to_account){
					$apis[$currency][] = $to_account;
				}
			}
			
			foreach($apis as $curr => $datas){
				$api_key = trim(is_deffin($m_defin,'BLOCKIO_'.$curr));
				if($api_key and is_array($datas) and count($datas) > 0){
					$addresses = join(',', $datas);
					$result = get_curl_parser('https://block.io/api/v2/archive_addresses/?api_key='. $api_key .'&addresses='.$addresses, array(), 'merchant', 'blockio');
					$this->logs($result);
				}
			}
		}		
		
	}
}

new merchant_blockio(__FILE__, 'BlockIo');