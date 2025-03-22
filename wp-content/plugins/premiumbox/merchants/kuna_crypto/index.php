<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Kuna Crypto[:en_US][ru_RU:]Kuna Crypto[:ru_RU]
description: [en_US:]Kuna Crypto merchant[:en_US][ru_RU:]мерчант Kuna Crypto[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_kuna_crypto')){
	class merchant_kuna_crypto extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$this->curr = array(
				'1'=>'UAH', 
				'42'=>'USD', 
				'43'=> 'RUB', 
				'41'=> 'USDT',
				'2'=> 'BTC',
				'6'=> 'ETH',
				'16'=> 'XRP',
				'8'=> 'BCH',
				'3'=> 'KUN',
				'23'=> 'LTC',
				'25'=> 'ZEC',
				'28'=> 'XEM',
				'24'=> 'DASH',
				'22'=> 'XLM',
				'7'=> 'WAVES',
				'17'=> 'EOS',
				'29'=> 'REM',
				'4'=> 'GOL',
				'21'=> 'TUSD',
				'44'=> 'DREAM',
				'45'=> 'PTI',
				'46'=> 'BNB',
				'47'=> 'GOLOS',	
				'48'=> 'CYBER',
				'49'=> 'USDC',
				'50'=> 'DAI',
				'51'=> 'UAX',				
			); //https://api.kuna.io/v3/currencies
			
			add_filter('list_user_notify',array($this,'user_mailtemp'));
			add_filter('list_admin_notify',array($this,'admin_mailtemp'));
			add_filter('list_notify_tags_generate_address1_kunacrypto',array($this,'mailtemp_tags'));
			add_filter('list_notify_tags_generate_address2_kunacrypto',array($this,'mailtemp_tags'));
			
			add_filter('bcc_keys',array($this,'set_keys'));
			add_filter('qr_keys',array($this,'set_keys'));
		}		

		function get_map(){
			$map = array(
				'API_KEY'  => array(
					'title' => '[en_US:]Public Key[:en_US][ru_RU:]Публичный ключ[:ru_RU]',
					'view' => 'input',	
				),
				'SECRET_KEY'  => array(
					'title' => '[en_US:]Secret Key[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
					'view' => 'input',
				),					
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('API_KEY','SECRET_KEY');
			return $arrs;
		}
		
		function user_mailtemp($places_admin){
			$places_admin['generate_address1_kunacrypto'] = sprintf(__('Address generation for %s','pn'), 'Kuna Crypto');
			return $places_admin;
		}

		function admin_mailtemp($places_admin){
			$places_admin['generate_address2_kunacrypto'] = sprintf(__('Address generation for %s','pn'), 'Kuna Crypto');
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
			$tags['dest_tag'] = array(
				'title' => __('Destination tag','pn'),
				'start' => '[dest_tag]',
			);			
			$tags['currency_code_give'] = array(
				'title' => __('Currency code','pn'),
				'start' => '[currency_code_give]',
			);			
			
			return $tags;
		}		

		function options($options, $data, $id, $place){ 
			$m_defin = $this->get_file_data($id);
			
			$options = pn_array_unset($options, array('check_api', 'note'));
			
			$text = '
			<div><strong>Cron:</strong> <a href="'. get_mlink($id.'_cron' . hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_cron' . hash_url($id)) .'</a></div>			
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
					
				$dest_tag = get_bids_meta($item_id, 'dest_tag');	
					
				$to_account = pn_strip_input($bids_data->to_account);
				if(!$to_account){
					
					$show_error = intval(is_isset($m_data, 'show_error'));
					$trans_in = '';
					
					$network = '';
					$currency_id_give = $bids_data->currency_id_give;
					$currency_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE id='$currency_id_give'");
					if(isset($currency_data->id)){
						$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
						if($xml_value == 'USDT'){
							$network = 'omni';
						} elseif($xml_value == 'USDTERC'){
							$network = 'eth';
						}
					}					
					
					try {
						$class = new Kuna_Crypto(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin, 'SECRET_KEY'));
						$result = $class->create_address($currency, $network);
						if(isset($result['address'])){ 
							$to_account = pn_strip_input($result['address']);
							$dest_tag = pn_strip_input(is_isset($result, 'memo'));
							$trans_in = pn_strip_input($result['sn']);
						} else {
							if($show_error and current_user_can('administrator')){
								print_r($result);
							}	
						}
					} catch (Exception $e) { 
						$this->logs($e->getMessage());
						if($show_error and current_user_can('administrator')){
							die($e->getMessage());
						}		
					}
					if($to_account and $trans_in){
						
						update_bids_meta($item_id, 'dest_tag', $dest_tag);
						
						$arr = array();
						$arr['to_account'] = $to_account;
						$arr['trans_in'] = $trans_in;
						$bids_data = update_bid_tb_array($item_id, $arr, $bids_data);
						
						$notify_tags = array();
						$notify_tags['[sitename]'] = pn_site_name();
						$notify_tags['[bid_id]'] = $item_id;
						$notify_tags['[address]'] = $to_account;
						$notify_tags['[sum]'] = $pay_sum;
						$notify_tags['[dest_tag]'] = $dest_tag;
						$notify_tags['[currency_code_give]'] = $bids_data->currency_code_give;
						$notify_tags = apply_filters('notify_tags_generate_address_kunacrypto', $notify_tags);		

						$user_send_data = array();
						$result_mail = apply_filters('premium_send_message', 0, 'generate_address2_kunacrypto', $notify_tags, $user_send_data, get_admin_lang()); 
						
						$user_send_data = array(
							'user_email' => $item->user_email,
						);	
						$user_send_data = apply_filters('user_send_data', $user_send_data, 'generate_address1_kunacrypto', $item);	
						$result_mail = apply_filters('premium_send_message', 0, 'generate_address1_kunacrypto', $notify_tags, $user_send_data, $item->bid_locale);					
						
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
					
					$descr = '';
					
					$temp .= $this->zone_table($pagenote, $list_data, $descr);										
					
				} else { 
					$temp .= $this->zone_error(__('Error','pn'));
				} 
			}
			return $temp;					
		} 

		function cron($m_id, $m_defin, $m_data){
			global $wpdb;

			$show_error = intval(is_isset($m_data, 'show_error'));	
			
			try {
				$class = new Kuna_Crypto(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin, 'SECRET_KEY'));
				$time = current_time('timestamp');
				$start_time = $time - (2 * DAY_IN_SECONDS);
				$end_time = $time;
				$orders = $class->get_history_orders($start_time, $end_time);
				
				if(is_array($orders)){
					$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'new' AND LENGTH(trans_in) > 1 AND m_in = '$m_id'");
					foreach($items as $item){
						$id = $item->id;
						
						$trans_in = pn_strip_input(is_isset($item,'trans_in'));
						$to_account = pn_strip_input(is_isset($item,'to_account'));
						$dest_tag = pn_strip_input(get_bids_meta($id, 'dest_tag'));
						
						foreach($orders as $order_key => $order){
							$res_address = trim($order['destination']);
							$memo = pn_strip_input($order['memo']);
							if(!$memo or $memo == $dest_tag){
								if($res_address and $res_address == $to_account){
									$res = $order;
									$res_status = $res['status'];
									$res_currency = $res['currency'];
									$res_txid = $res['txid'];
									$currency = is_isset($this->curr, $res_currency);
									if($res_status == 'done'){
										$data = get_data_merchant_for_id($id, $item);
											
										$in_sum = $res['amount'];
										$in_sum = is_sum($in_sum, 12);
										$err = $data['err'];
										$status = $data['status'];
										$bid_m_id = $data['m_id'];
										$bid_m_script = $data['m_script'];  
											
										$bid_currency = $data['currency'];
											
										$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
												
										$bid_sum = is_sum($data['pay_sum'], 12);	
										$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
											
										$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
										$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
										$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
										$invalid_check = intval(is_isset($m_data, 'check'));								
											
										if(!check_trans_in($bid_m_id, $res_txid, $id)){									
											if($err == 0 and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name){
												if($bid_currency == $currency or $invalid_ctype > 0){
													if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){
														
														unset($orders[$order_key]);
														
														$params = array( 
															'pay_purse' => $pay_purse,
															'trans_in' => $res_txid,
															'sum' => $in_sum,
															'bid_sum' => $bid_sum,
															'bid_status' => array('new','techpay','coldpay'),
															'bid_corr_sum' => $bid_corr_sum,
															'currency' => $currency,
															'bid_currency' => $bid_currency,
															'invalid_ctype' => $invalid_ctype,
															'invalid_minsum' => $invalid_minsum,
															'invalid_maxsum' => $invalid_maxsum,
															'invalid_check' => $invalid_check,
															'm_place' => $bid_m_id.'_cron',	
															'm_id' => $m_id,
															'm_data' => $m_data,
															'm_defin' => $m_defin,
														);
														set_bid_status('realpay', $id, $params, $data['direction_data']);  	

														break;
													} else {
														$this->logs($id . ' The payment amount is less than the provisions');
													}
												} else {
													$this->logs($id.' In the application the wrong status');
												}										
											} else {
												$this->logs($id . ' bid error');
											}
										} else {
											$this->logs($id . ' Error check trans in!');
										}
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

new merchant_kuna_crypto(__FILE__, 'Kuna Crypto');