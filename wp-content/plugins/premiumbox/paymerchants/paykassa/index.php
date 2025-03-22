<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]PayKassa[:en_US][ru_RU:]PayKassa[:ru_RU]
description: [en_US:]PayKassa automatic payouts[:en_US][ru_RU:]авто выплаты PayKassa[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_paykassa')){
	class paymerchant_paykassa extends AutoPayut_Premiumbox{
		function __construct($file, $title)
		{
			parent::__construct($file, $title);				
		}

		function get_map(){
			$map = array(
				'SHOP_ID'  => array(
					'title' => '[en_US:]Shop ID for payouts[:en_US][ru_RU:]ID магазина для выплат[:ru_RU]',
					'view' => 'input',	
				),
				'API_ID'  => array(
					'title' => '[en_US:]API ID[:en_US][ru_RU:]API ID[:ru_RU]',
					'view' => 'input',	
				),
				'API_PASS'  => array(
					'title' => '[en_US:]API password[:en_US][ru_RU:]API пароль[:ru_RU]',
					'view' => 'input',	
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('API_ID','API_PASS','SHOP_ID');
			return $arrs;
		}	
		
		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'error_status');
			
			$paymethods = array(
				'1' => 'payeer',
				'2' => 'perfectmoney',
				'4' => 'advcash',
				//'7' => 'berty',
				'11' => 'bitcoin',
				'12' => 'ethereum',
				'14' => 'litecoin',
				'15' => 'dogecoin',
				'16' => 'dash',
				'18' => 'bitcoincash',
				'19' => 'zcash',
				'20' => 'monero',
				'21' => 'ethereumclassic',
				'22' => 'ripple',
				'23' => 'neo',
				'24' => 'gas',
				'25' => 'bitcoinsv',
			);			
			
			$options['paymethod'] = array(
				'view' => 'select',
				'title' => __('Transaction type','pn'),
				'options' => $paymethods,
				'default' => is_isset($data, 'paymethod'),
				'name' => 'paymethod',
				'work' => 'int',
			);
			
			$pay_comis = array(
				'0' => __('Exchanger','pn'),
				'1' => __('User','pn'),
			);			
			$options['pay_comis'] = array(
				'view' => 'select',
				'title' => __('Who pays fee','pn'),
				'options' => $pay_comis,
				'default' => is_isset($data, 'pay_comis'),
				'name' => 'pay_comis',
				'work' => 'int',
			);							
			
			return $options;
		}			
		
		function get_reserve_lists($m_id, $m_defin){
			$keys = array(
				'payeer_rub',
				'advcash_rub',
				'payeer_usd',
				'perfectmoney_usd',
				'advcash_usd',
				'bitcoin_btc',
				'ethereum_eth',
				'litecoin_ltc',
				'dogecoin_doge',
				'dash_dash',
				'bitcoincash_bch',
				'zcash_zec',
				'monero_xmr',
				'ethereumclassic_etc',
				'ripple_xrp',
				'bitcoinsv_bsv',
			);
			$purses = array();
			foreach($keys as $key){
				$key = trim($key);
				if($key){
					$purses[$m_id.'_'.$key] = $key;
				}	
			}
			
			return $purses;
		}			

		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			try {
				$class = new PayKassaAPI(is_deffin($m_defin,'API_ID'), is_deffin($m_defin,'API_PASS'));
				$res = $class->api_get_shop_balance(is_deffin($m_defin,'SHOP_ID'));	
					
				$now_key = str_replace($m_id.'_', '', $code);
					
				$rezerv = '-1';
					
				if(isset($res['data']) and is_array($res['data'])){
					foreach($res['data'] as $k => $v){
						if($now_key == $k){
							$rezerv = $v;
						}
					}
				}
							
				if($rezerv != '-1'){
					$sum = $rezerv;
				}		
						
			}
			catch (Exception $e)
			{
							
			} 									
			
			return $sum;
		}		
		
		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){
			
			$item_id = $item->id;			
			$trans_id = 0;				
			
			$system_id = intval(is_isset($paymerch_data,'paymethod'));
			if(!$system_id){ $system_id = 1; }
			
			$pay_comis = intval(is_isset($paymerch_data,'pay_comis'));
			if($pay_comis == 1){
				$paid_commission = 'client';
			} else {
				$paid_commission = 'shop';
			}
			
			$vtype = mb_strtoupper($item->currency_code_get);
			$vtype = str_replace(array('RUR'),'RUB',$vtype);
		
			$account = $item->account_get;
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data));
						
			if(count($error) == 0){

				$result = $this->set_ap_status($item, $test);
				if($result){
					
					$notice = get_text_paymerch($m_id, $item);
					if(!$notice){ $notice = sprintf(__('Order ID %s','pn'), $item->id); }
					$notice = trim(pn_maxf($notice,100));
					
					$dest_tag = trim(is_isset($unmetas,'dest_tag'));
						
					try{
						
						$paykassa = new PayKassaAPI(is_deffin($m_defin,'API_ID'), is_deffin($m_defin,'API_PASS'));

						$res = $paykassa->api_payment(
							is_deffin($m_defin,'SHOP_ID'),      // обязательный параметр, id магазина с которого нужно сделать выплату
							$system_id,    // обязательный параметр, id платежного метода
							$account,                // обязательный параметр, номер кошелька на который отправляем деньги
							$sum,         // обязательный параметр, сумма платежа, сколько отправить
							$vtype,              // обязательный параметр, валюта платежа
							$notice,                // обязательный параметр, комметнарий к платежу, можно передать пустой
							$paid_commission,
							$dest_tag
						);

						if (isset($res['error']) and $res['error']) {        // $res['error'] - true если ошибка
							$error[] = $res['message'];   // $res['message'] - текст сообщения об ошибке
							$pay_error = 1;
						} elseif(isset($res['data'])) {
							$shop_id = $res['data']['shop_id'];                         // id магазина, с которого была сделана выплата, пример 122
							$transaction = $res['data']['transaction'];                 // номер транзакции платежа, пример 130236
							$trans_id = $res['data']['txid'];
							$amount = $res['data']['amount'];                           // сумма выплаты, сколько списалось с баланса магазина, 1066.00
							$amount_pay = $res['data']['amount_pay'];                   // сумма выплаты, столько пришло пользователю, пример: 1000.00
							$system = $res['data']['system'];                           // система выплаты, на какую платежную систему была сделана выплата, пример: Payeer
							$currency = $res['data']['currency'];                       // валюта выплаты, пример: RUB
							$number = $res['data']['number'];                           // номер кошелька, куда были отправлены средства, пример: P123456
							$comission_percent = $res['data']['shop_comission_percent'];// комиссия за перевод в процентах, пример: 6.5
							$comission_amount = $res['data']['shop_comission_amount'];  // комиссия за перевод сумма, пример: 1.00
							$paid_commission = $res['data']['paid_commission'];         // кто оплачивал комиссию, пример: shop
						} else {
							$error[] = 'Class error';
							$pay_error = 1;
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
				
				$params = array(
					'trans_out' => $trans_id,
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('success', $item_id, $params, $direction); 						
						
				if($place == 'admin'){
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'), 'true');
				} 	
			}
		}				
	}
}

new paymerchant_paykassa(__FILE__, 'PayKassa');