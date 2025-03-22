<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]WhiteBit Crypto[:en_US][ru_RU:]WhiteBit Crypto[:ru_RU]
description: [en_US:]WhiteBit Crypto merchant[:en_US][ru_RU:]мерчант WhiteBit Crypto[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_whitebit_crypto')){
	class merchant_whitebit_crypto extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			add_filter('list_user_notify',array($this,'user_mailtemp'));
			add_filter('list_admin_notify',array($this,'admin_mailtemp'));
			add_filter('list_notify_tags_generate_address1_whitebitcrypto',array($this,'mailtemp_tags'));
			add_filter('list_notify_tags_generate_address2_whitebitcrypto',array($this,'mailtemp_tags'));
			
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
			$places_admin['generate_address1_whitebitcrypto'] = sprintf(__('Address generation for %s','pn'), 'Whitebit Crypto');
			return $places_admin;
		}

		function admin_mailtemp($places_admin){
			$places_admin['generate_address2_whitebitcrypto'] = sprintf(__('Address generation for %s','pn'), 'Whitebit Crypto');
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
			
			return $tags;
		}		

		function options($options, $data, $id, $place){ 
		
			$m_defin = $this->get_file_data($id);
			
			$options = pn_array_unset($options, array('check_api', 'note'));
			
			$options['need_confirm'] = array(
				'view' => 'input',
				'title' => __('Required number of transaction confirmations','pn'),
				'default' => is_isset($data, 'need_confirm'),
				'name' => 'need_confirm',
				'work' => 'int',
			);
			$options['need_confirm_warning'] = array(
				'view' => 'warning',
				'default' => __('(Recommended!) Set the value to 0 so that the order is considered paid only after receiving the required number of confirmations on the stock! <br /> (NOT recommended!) If you set a value other than 0, the exchanger will change the status of the order to "Paid" according to this setting, regardless of the transaction status that is displayed in the exchanges payment history.','pn'),
			);			
			
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

				$need_confirm = intval(is_isset($m_data, 'need_confirm'));

				$item_id = $bids_data->id;		
				$currency = $bids_data->currency_code_give;
					
				$dest_tag = get_bids_meta($item_id, 'dest_tag');	
					
				$to_account = pn_strip_input($bids_data->to_account);
				if(!$to_account){
					
					$show_error = intval(is_isset($m_data, 'show_error'));
					
					$network = '';
					$currency_id_give = $bids_data->currency_id_give;
					$currency_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE id='$currency_id_give'");
					if(isset($currency_data->id)){
						$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
						if($xml_value == 'USDT'){
							$network = 'OMNI';
						} elseif($xml_value == 'USDTERC'){
							$network = 'ERC20';
						} elseif($xml_value == 'USDTTRC'){	
							$network = 'TRC20';
						}
					}
					
					try {
						$class = new WHITEBIT_Crypto(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin, 'SECRET_KEY'));
						$result = $class->create_address($currency, $network);
						if(isset($result['account'])){ 
							$info = $result['account'];
							$to_account = pn_strip_input(is_isset($info, 'address'));
							$dest_tag = pn_strip_input(is_isset($info, 'memo'));
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
					if($to_account){
						
						update_bids_meta($item_id, 'dest_tag', $dest_tag);
						
						$arr = array();
						$arr['to_account'] = $to_account;
						$bids_data = update_bid_tb_array($item_id, $arr, $bids_data);
						
						$notify_tags = array();
						$notify_tags['[sitename]'] = pn_site_name();
						$notify_tags['[bid_id]'] = $item_id;
						$notify_tags['[address]'] = $to_account;
						$notify_tags['[sum]'] = $pay_sum;
						$notify_tags['[dest_tag]'] = $dest_tag;
						$notify_tags = apply_filters('notify_tags_generate_address_whitebitcrypto', $notify_tags);		

						$user_send_data = array();
						$result_mail = apply_filters('premium_send_message', 0, 'generate_address2_whitebitcrypto', $notify_tags, $user_send_data, get_admin_lang()); 
						
						$user_send_data = array(
							'user_email' => $bids_data->user_email,
						);	
						$user_send_data = apply_filters('user_send_data', $user_send_data, 'generate_address1_whitebitcrypto', $bids_data);	
						$result_mail = apply_filters('premium_send_message', 0, 'generate_address1_whitebitcrypto', $notify_tags, $user_send_data, $bids_data->bid_locale);					
						
					} 
				}
				
				if($to_account){
					
					$pagenote = get_pagenote($m_id, $bids_data, $pay_sum);
					if($pagenote){
						$temp .= '<div class="zone_pagenote">'. apply_filters('comment_text', $pagenote) .'</div>';
					}		
					
					$temp .= '		
					<div class="zone_table"> 			
						<div class="zone_div">
							<div class="zone_title"><div class="zone_copy" data-clipboard-text="'. $pay_sum .'"><div class="zone_copy_abs">'. __('copied to clipboard','pn') .'</div>'. __('Amount','pn') .'</div></div>
							<div class="zone_text">'. $pay_sum .' '. $currency .'</div>					
						</div>				
						<div class="zone_div">
							<div class="zone_title"><div class="zone_copy" data-clipboard-text="'. $to_account .'"><div class="zone_copy_abs">'. __('copied to clipboard','pn') .'</div>'. __('Address','pn') .'</div></div>
							<div class="zone_text">'. $to_account .'</div>					
						</div>
					</div>				
					';
					
					if($dest_tag){	
						$temp .= '
						<div class="zone_div">
							<div class="zone_title"><div class="zone_copy" data-clipboard-text="'. $dest_tag .'"><div class="zone_copy_abs">'. __('copied to clipboard','pn') .'</div>'. __('Destination tag','pn') .'</div></div>
							<div class="zone_text">'. $dest_tag .'</div>					
						</div>						
						';	
					}

					if($need_confirm > 0){
						$temp .= '<div class="zone_descr">' . apply_filters('merchant_confirmations_text', sprintf(__('The order status changes to "Paid" when we get <b>%1$s</b> confirmations','pn'), $need_confirm), $bids_data) . '</div>';
					}					
					
				} else { 
					$temp .= '
					<div class="error_div">'. __('Error','pn') .'</div>
					';
				} 
			}
			return $temp;					
		} 

		function cron($m_id, $m_defin, $m_data){
			global $wpdb;

			$show_error = intval(is_isset($m_data, 'show_error'));	
			$need_confirm = intval(is_isset($m_data, 'need_confirm'));
			
			try {
				$class = new WHITEBIT_Crypto(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin, 'SECRET_KEY'));
				$records = $class->get_history(1, 100);

				if(is_array($records) and isset($records['records']) and is_array($records['records']) and count($records['records']) > 0){
					$orders = $records['records'];
					
					$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status IN('new','coldpay') AND m_in = '$m_id'");
					foreach($items as $item){
						
						$id = $item->id;
						$trans_in = pn_strip_input(is_isset($item,'trans_in'));
						$to_account = pn_strip_input(is_isset($item,'to_account'));
						$dest_tag = pn_strip_input(get_bids_meta($id, 'dest_tag'));
						
						foreach($orders as $order_key => $order){
							$res_address = trim(is_isset($order,'address'));
							$memo = pn_strip_input(is_isset($order,'memo'));
							if(!$memo or $memo == $dest_tag){
								if($res_address and $res_address == $to_account){
									$res_status = $order['status'];
									$currency = $order['ticker'];
									$res_txid = $order['transactionHash'];
									$confirmations = 0;
									if(isset($order['confirmations'], $order['confirmations']['actual'])){
										$confirmations = intval($order['confirmations']['actual']);
									}
									
									$realpay_st = array('3','7');
									$coldpay_st = array('0','1','2','6','10','11','13','14','15');
									
									$data = get_data_merchant_for_id($id, $item);
									
									$now_status = '';
									if(in_array($res_status, $realpay_st)){
										$now_status = 'realpay';
									}
									if(in_array($res_status, $coldpay_st)){
										$now_status = 'coldpay';
									}				
									if($res_status == '15' and $confirmations >= $need_confirm and $need_confirm > 0){
										$now_status = 'realpay';
									}
									
									do_action('merchant_confirm_count', $id, $confirmations, $data['bids_data'], $data['direction_data'], $need_confirm, $this->name);
									
									if($now_status){
											
										$in_sum = $order['amount'];
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
														set_bid_status($now_status, $id, $params, $data['direction_data']);  
														
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

new merchant_whitebit_crypto(__FILE__, 'WhiteBit Crypto');