<?php 
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]WhiteBit[:en_US][ru_RU:]WhiteBit[:ru_RU]
description: [en_US:]WhiteBit automatic payouts[:en_US][ru_RU:]авто выплаты WhiteBit[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_whitebit')){
	class paymerchant_whitebit extends AutoPayut_Premiumbox{

		function __construct($file, $title)
		{
			parent::__construct($file, $title);	
			
			$this->curr = array('BTC','ETH','USD','LTC','ETC','BCH','DASH','NEO','UAH','XLM','OMG','BNB','USDT','BAT','LOT','KEY','WAVES','EUR','RUB','XRC','BCCN','QBIT','REM','SON','MB8','ILC','CCOH','MEXC','GBC','VITAE','STREAM','TELOS','TRX','MPC','DEXR','ROX','VC','AYA','XST','CSC','SGC','GFG','BTG','MATIC','SCAP','XRP','CVA','SHARK','CDL','BTT','NRG','POLIS','KIM','CICX','PGPAY','FLT','PAC','AGRS','TERN','IEOS','USDT_ETH','USDT_TRON','TUSD','PAX','CCXX','USDC','GLEEC','BEER','LINK','SOLO','CAP','SNB','BSV','ZEC','VNDC','DBTC','DUSDT','TLW','TNC','CBUCKS','MARTK','MWC','USDT_OMNI');
			
			add_filter('list_user_notify',array($this,'user_mailtemp')); 
			add_filter('list_notify_tags_whitebit_paycoupon',array($this,'mailtemp_tags_paycoupon'));
		}
		
		function get_map(){
			$map = array(
				'KEY'  => array(
					'title' => '[en_US:]API Key[:en_US][ru_RU:]API ключ[:ru_RU]',
					'view' => 'input',	
				),
				'SECRET'  => array(
					'title' => '[en_US:]Secret Key[:en_US][ru_RU:]Секрет ключ[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('KEY','SECRET');
			return $arrs;
		}

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'resulturl');	
			$options = pn_array_unset($options, 'error_status');
			
			$options['bindlogin'] = array(
				'view' => 'select',
				'title' => __('User email','pn'),
				'options' => array('0' => __('Account get','pn'),'1' => __('User email','pn')),
				'default' => intval(is_isset($data, 'bindlogin')),
				'name' => 'bindlogin',
				'work' => 'int',
			);			
			
			return $options;
		}		
		
		function user_mailtemp($places_admin){
			$places_admin['whitebit_paycoupon'] = sprintf(__('%s automatic payout','pn'), 'WhiteBit');
			return $places_admin;
		}

		function mailtemp_tags_paycoupon($tags){
			$tags['id'] = array(
				'title' => __('Coupon code','pn'),
				'start' => '[id]',
			);
			$tags['bid_id'] = array(
				'title' => __('Order ID','pn'),
				'start' => '[bid_id]',
			);
			return $tags;
		}		

		function get_reserve_lists($m_id, $m_defin){
			$purses = array();
			foreach($this->curr as $curr){
				$purses[$m_id.'_'.$curr] = $curr;
			}
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if($purse){						
				try {					
					$class = new AP_WHITEBIT(is_deffin($m_defin,'KEY'),is_deffin($m_defin,'SECRET'));
					$res = $class->get_balance();
					if(is_array($res)){								
						$rezerv = '-1';								
						foreach($res as $pursename => $amount){
							if($pursename == $purse){
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
			$item_id = $item->id;
			
			$trans_id = 0;			
			$coupon = '';			

			$bindlogin = intval(is_isset($paymerch_data, 'bindlogin'));

			$vtype = mb_strtoupper($item->currency_code_get);
			$vtype = str_replace('RUR','RUB',$vtype);						
				
			if($bindlogin == 1){
				$account = $item->user_email;
			} else {
				$account = $item->account_get;
			}
				
			if (!is_email($account)) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}				
					
			$sum = is_paymerch_sum($item, $paymerch_data);
				
			$two = array('USD','EUR','RUR','UAH');
			if(in_array($vtype, $two)){
				$sum = is_sum($sum, 2);
			} else {
				$sum = is_sum($sum);
			}
					
			if(count($error) == 0){

				$result = $this->set_ap_status($item, $test);				
				if($result){				
					
					try {
						$class = new AP_WHITEBIT(is_deffin($m_defin,'KEY'),is_deffin($m_defin,'SECRET'));
						$res = $class->make_voucher($sum, $vtype);
						if($res['error'] == 1){
							$error[] = __('Payout error','pn');
							$pay_error = 1;
						} else {
							$coupon = $res['coupon'];
							$trans_id = '';
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
						
				$notify_tags = array();
				$notify_tags['[sitename]'] = pn_site_name();
				$notify_tags['[id]'] = $coupon;
				$notify_tags['[bid_id]'] = $item_id;
				$notify_tags = apply_filters('notify_tags_whitebit_paycoupon', $notify_tags);		

				$user_send_data = array(
					'user_email' => $account,
				);
				$user_send_data = apply_filters('user_send_data', $user_send_data, 'whitebit_paycoupon', $item);
				$result_mail = apply_filters('premium_send_message', 0, 'whitebit_paycoupon', $notify_tags, $user_send_data, $item->bid_locale);									
						
				$coupon_data = array(
					'coupon' => $coupon,
				);
				do_action('merchant_create_coupon', $coupon_data, $item, 'whitebit', $place);
				
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

new paymerchant_whitebit(__FILE__, 'WhiteBit');