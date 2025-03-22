<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]BitexBook[:en_US][ru_RU:]BitexBook[:ru_RU]
description: [en_US:]BitexBook automatic payouts[:en_US][ru_RU:]авто выплаты BitexBook[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_bitexbook')){
	class paymerchant_bitexbook extends AutoPayut_Premiumbox {
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);	
			
			add_filter('list_user_notify',array($this,'user_mailtemp')); 
			add_filter('list_notify_tags_bitexbook_paycoupon',array($this,'mailtemp_tags_paycoupon'));			
		}

		function get_map(){
			$map = array(
				'TOKEN'  => array(
					'title' => '[en_US:]API token[:en_US][ru_RU:]API токен[:ru_RU]',
					'view' => 'input',	
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('TOKEN');
			return $arrs;
		}		
		
		function user_mailtemp($places_admin){
			$places_admin['bitexbook_paycoupon'] = sprintf(__('%s automatic payout','pn'), 'BitexBook coupons');
			return $places_admin;
		}

		function mailtemp_tags_paycoupon($tags){
			
			$tags['id'] = array(
				'title' => __('Coupon code','pn'),
				'start' => '[id]',
			);
			$tags['num'] = array(
				'title' => __('Activation code','pn'),
				'start' => '[num]',
			);			
			$tags['bid_id'] = array(
				'title' => __('Order ID','pn'),
				'start' => '[bid_id]',
			);
			
			return $tags;
		}		
		
		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'note');
					
			$opts = array(
				'0' => __('Trading operation','pn'),
				'1' => __('Coupon','pn'),
			);
			$options['variant'] = array(
				'view' => 'select',
				'title' => __('Transaction type','pn'),
				'options' => $opts,
				'default' => intval(is_isset($data, 'variant')),
				'name' => 'variant',
				'work' => 'int',
			);

			$options['line_error_status'] = array(
				'view' => 'line',
			);			
			
			$options['buy'] = array(
				'view' => 'select',
				'title' => __('Buy additional amount of crypto missing on balance','pn'),
				'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn'), '2'=>__('Buy and do not withdrawal','pn'), '3'=>__('Buy entire amount','pn')),
				'default' => intval(is_isset($data, 'buy')),
				'name' => 'buy',
				'work' => 'int',
			);
			$options['birg_pers'] = array(
				'view' => 'input',
				'title' => __('Trading fee of stock exchange (%)','pn'),
				'default' => is_isset($data, 'birg_pers'),
				'name' => 'birg_pers',
				'work' => 'sum',
			);			
			$options['buytype'] = array(
				'view' => 'select',
				'title' => __('Order type','pn'),
				'options' => array('0'=>__('Market','pn'), '1'=>__('Limit','pn')),
				'default' => intval(is_isset($data, 'buytype')),
				'name' => 'buytype',
				'work' => 'int',
			);
			$options['buysymbols'] = array(
				'view' => 'select',
				'title' => __('Determine trading code','pn'),
				'options' => array('0'=>__('Automatically','pn'), '1'=>__('Manually','pn')),
				'default' => intval(is_isset($data, 'buysymbols')),
				'name' => 'buysymbols',
				'work' => 'int',
			);
			$buycurr = array(
				'usd' => 'USD',
				'eur' => 'EUR',
				'rub' => 'RUB',
			);
			$options['buycurr'] = array(
				'view' => 'select',
				'title' => __('Trading operation code (if Manually)','pn'),
				'options' => $buycurr,
				'default' => intval(is_isset($data, 'buycurr')),
				'name' => 'buycurr',
				'work' => 'input',
			);

			$text = '
			<div><strong>CRON:</strong> <a href="'. get_mlink('ap_'. $id .'_cron' . hash_url($id, 'ap')) .'" target="_blank">'. get_mlink('ap_'. $id .'_cron' . hash_url($id, 'ap')) .'</a></div>
			';
			$options[] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin){
		
			$currencies = array(
				'banca','bch','bchbf','bcn','ben','bsm','btc','btg','btt','btw','bxb','byn','dash','dashbf',
				'doge','dogebf','drt','eth','flipbf','grif','igg','ltc','ltcbf','mfc','ocn',
				'rub','rubbf','spf','tgold','trx','twm','twx','usd','usdbf','usdt','win','xmr','zec','zen','znc',
			);
			
			$purses = array();
			
			foreach($currencies as $currency){
				$purses[$m_id . '_' . strtolower($currency)] = strtoupper($currency);
			} 
			
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			
			$purse = strtolower(trim(str_replace($m_id . '_','',$code)));
			if($purse){
						
				try{
					
					$oClass = new AP_BitexBookApi( is_deffin($m_defin, 'TOKEN') );
					$res = $oClass->get_balans();
					if(is_array($res)){
								
						$rezerv = '-1';
								
						foreach($res as $pursename => $val){
							$pursename = strtolower($pursename);
							if( $pursename == $purse ){
								$amount = is_isset($val, 'amount');
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
							
				} 				
			}
			
			return $sum;
		}		

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){
			
			$item_id = $item->id;			
			$coupon = '';
			$coupon_num = '';
			$trans_id = 0;				
			
			$variant = intval(is_isset($paymerch_data,'variant'));
			
			$currency_code_give = strtolower($item->currency_code_give);
			$currency_code_get = strtolower($item->currency_code_get);
							
			$account = $item->account_get;
					
			if($variant == 1){
				$account = is_email($account);
			} 
			
			if (!$account) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}			
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data));
					
			$wallet_id = 0;	
			$balans = 0;
				
			$oClass = new AP_BitexBookApi( is_deffin($m_defin, 'TOKEN') );
				
			try {
					
				$res = $oClass->get_balans();
				if(is_array($res)){
								
					foreach($res as $curr => $val){
						$curr = strtolower($curr);
						if($currency_code_get == $curr){
							$amount = is_isset($val, 'amount');
							$balans = is_sum($amount);
							
							$id = is_isset($val, 'id');
							$wallet_id = intval($id);							
							break;
						}
					}						
								
				} 
						
			}
			catch (Exception $e)
			{
				$error[] = $e->getMessage();			
			} 				
	
			$buy = intval(is_isset($paymerch_data, 'buy')); 
			$buy_arr = array('1','2');
			if(in_array($buy, $buy_arr) and $sum > $balans or $buy == 3){
				$execution_type = intval(is_isset($paymerch_data, 'buytype'));
				$buysymbols = intval(is_isset($paymerch_data, 'buysymbols'));
				$buycurr = pn_strip_input(is_isset($paymerch_data, 'buycurr'));
				if(!$buycurr){ $buycurr = 'USD'; }
				$birg_pers = is_sum(is_isset($paymerch_data, 'birg_pers')); if($birg_pers < 0){ $birg_pers = 0; }

				$symbol = '';
				if($buysymbols == 1){
					$symbol = strtolower($currency_code_get . $buycurr);
				} else {
					$symbol = strtolower($currency_code_get . $currency_code_give);
				}
				
				$course = is_sum($item->course_get);
				
				$sum_for_buy = is_sum($sum - $balans);
				if($buy == 3){ $sum_for_buy = $sum; }				
				
				$start_volume = pers_alter_sum($sum_for_buy, $birg_pers);
				if($start_volume < '0.0000001'){ $start_volume = '0.0000001'; }
					
				try {
					$res = $oClass->buy($execution_type, $symbol, $course, $start_volume);
					if(isset($res['volume'])){
						$balans = is_sum($balans + is_sum($res['volume']));
						sleep(5);
					} else {
						$error[] = __('Failed to buy cryptocurrency','pn');
					}					
				}
				catch( Exception $e ) {
					$error[] = $e->getMessage();
				}			
			}		
			
			if($sum > $balans){
				$error[] = __('Balance error','pn');
			}	
			
			if($buy == 2){
				$error[] = __('Cryptocurrency only','pn');
			}
					
			$dest_tag = trim(is_isset($unmetas,'dest_tag'));		
					
			if($variant == 1 and !$wallet_id){ 
				$error[] = __('Failed to get wallet_id','pn');
			}
					
			if(count($error) == 0){
				$result = $this->set_ap_status($item, $test);
				if($result){
					
					try {
						
						$oClass = new AP_BitexBookApi( is_deffin($m_defin, 'TOKEN') );
						if($variant == 0){
							$res = $oClass->send_money($currency_code_get, $sum, $account, $dest_tag);
						} else {
							$res = $oClass->make_voucher($sum, $wallet_id, $currency_code_get);
						}
						if($res['error'] == 1){
							$error[] = __('Payout error','pn');
							$pay_error = 1;
						} else {
							$trans_id = $res['trans_id'];
							if($variant == 1){
								$coupon = $res['code'];
								$coupon_num = $res['num'];
							}
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
						
				if($variant == 1){
						
					$notify_tags = array();
					$notify_tags['[sitename]'] = pn_site_name();
					$notify_tags['[id]'] = $coupon;
					$notify_tags['[num]'] = $coupon_num;
					$notify_tags['[bid_id]'] = $item_id;
					$notify_tags = apply_filters('notify_tags_bitexbook_paycoupon', $notify_tags);		

					$user_send_data = array(
						'user_email' => $account,
					);
					$user_send_data = apply_filters('user_send_data', $user_send_data, 'bitexbook_paycoupon', $item);
					$result_mail = apply_filters('premium_send_message', 0, 'bitexbook_paycoupon', $notify_tags, $user_send_data, $item->bid_locale);												
							
					$coupon_data = array(
						'coupon' => $coupon,
						'coupon_code' => $coupon_num,
					);							
					do_action('merchant_create_coupon', $coupon_data, $item, 'bitexbook', $place);						
							
				}	
						
				$params = array(
					'trans_out' => $trans_id,
					'system' => 'user',
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				if($variant == 0){
					set_bid_status('coldsuccess', $item_id, $params, $direction); 
				} else {
					set_bid_status('success', $item_id, $params, $direction);
				}	
						
				if($place == 'admin'){
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
				} 		
			}
		}				
		
		function cron($m_id, $m_defin, $m_data){
		global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
			foreach($items as $item){
				$currency = mb_strtoupper($item->currency_code_get);
				$trans_id = trim($item->trans_out);
				if($trans_id){
					try {
						$oClass = new AP_BitexBookApi( is_deffin($m_defin, 'TOKEN') );
						$result = $oClass->get_transfer_info($trans_id);
						if(isset($result[0], $result[0]['hash'], $result[0]['status'])){
							$check_status = intval($result[0]['status']);
							$txt_id = pn_strip_input($result[0]['hash']);
							if($check_status == 4){
								$params = array(
									'trans_out' => $txt_id,
									'system' => 'system',
									'bid_status' => array('coldsuccess'),
									'm_place' => 'cron ' .$m_id .'_cron',
									'm_id' => $m_id,
									'm_defin' => $m_defin,
									'm_data' => $m_data,
								);
								set_bid_status('success', $item->id, $params);														
							} elseif($check_status == 5) {
								
								$this->reset_cron_status($item, $error_status, $m_id);
								
							}
						}	
					}
					catch( Exception $e ) {
										
					}
				}
			}
		}		
	}
}

new paymerchant_bitexbook(__FILE__, 'BitexBook');