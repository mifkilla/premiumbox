<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]PayKassa[:en_US][ru_RU:]PayKassa[:ru_RU]
description: [en_US:]PayKassa merchant[:en_US][ru_RU:]мерчант PayKassa[:ru_RU]
version: 2.2
*/

if(!class_exists('merchant_paykassa')){
	class merchant_paykassa extends Merchant_Premiumbox {
		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
				add_action('premium_merchant_'. $id .'_fail', array($this,'merchant_fail'));
				add_action('premium_merchant_'. $id .'_success', array($this,'merchant_success'));
			}
		}
		
		function get_map(){
			$map = array(
				'SHOP_ID'  => array(
					'title' => '[en_US:]Shop ID[:en_US][ru_RU:]ID магазина[:ru_RU]',
					'view' => 'input',	
				),
				'SHOP_PASS'  => array(
					'title' => '[en_US:]Shop secret key[:en_US][ru_RU:]Секеретный ключ магазина[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}		
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('SHOP_ID','SHOP_PASS');
			return $arrs;
		}						
		
		function options($options, $data, $id, $place){ 
			
			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, 'check_api');
			
			$paymethods = array(
				'1' => 'payeer',
				'2' => 'perfectmoney',
				'4' => 'advcash',
				'7' => 'berty',
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
				'26' => 'waves',
				'27' => 'tron',
				'28' => 'stellar',
			);			
			
			$options['paymethod'] = array(
				'view' => 'select',
				'title' => __('Payment method','pn'),
				'options' => $paymethods,
				'default' => is_isset($data, 'paymethod'),
				'name' => 'paymethod',
				'work' => 'int',
			);			
			
			$text = '
			<div><strong>Status URL:</strong> <a href="'. get_mlink($id.'_status' . hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_status' . hash_url($id)) .'</a></div>
			<div><strong>Success URL:</strong> <a href="'. get_mlink($id.'_success') .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_success') .'</a></div>
			<div><strong>Fail URL:</strong> <a href="'. get_mlink($id.'_fail') .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_fail') .'</a></div>		
			';

			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;	
		}

		function bidform($temp, $m_id, $pay_sum, $item, $direction){
			$script = get_mscript($m_id);
			if($script and $script == $this->name){
				$m_defin = $this->get_file_data($m_id);

				$amount = is_sum($pay_sum);
				$text_pay = get_text_pay($m_id, $item, $amount);		
				
				$m_data = get_merch_data($m_id);
				$show_error = intval(is_isset($m_data, 'show_error'));
				
				$system_id = intval(is_isset($m_data, 'paymethod'));
				if(!$system_id){ $system_id = 1; }
				
				$currency = pn_strip_input(str_replace('RUR','RUB',$item->currency_code_give));
				
				$res = '';
				
				try {
					$paykassa = new PayKassaSCI(is_deffin($m_defin,'SHOP_ID'),is_deffin($m_defin,'SHOP_PASS'));
					
					$res = $paykassa->sci_create_order(
						$amount,
						$currency,  
						$item->id,  
						$text_pay,   
						$system_id 
					);				
				}
				catch( Exception $e ) {
					$this->logs($e->getMessage());
					if($show_error and current_user_can('administrator')){
						die( __('Error!','pn') . ' ' .$e->getMessage() );
					} 
				}				
					
				if(isset($res["data"]) and isset($res["data"]["url"])){ 
					$temp = '
					<form action="'. $res["data"]["url"] .'" method="POST">
						<input type="submit" value="'. __('Make a payment','pn') .'" />
					</form>
					';
				} else {
					$this->logs($res);
				}				
			}
			return $temp;
		}

		function merchant_fail(){
			$id = get_payment_id('ac_order_id');
			redirect_merchant_action($id, $this->name);
		}

		function merchant_success(){
			$id = get_payment_id('ac_order_id');
			redirect_merchant_action($id, $this->name, 1);
		}
	
		function merchant_status(){
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);
	
			$show_error = intval(is_isset($m_data, 'show_error'));
	
			try {
				$paykassa = new PayKassaSCI(is_deffin($m_defin,'SHOP_ID'),is_deffin($m_defin,'SHOP_PASS'));

				$res = $paykassa->sci_confirm_order();

				if (isset($res['error']) and $res['error']) {        // $res['error'] - true если ошибка
					$this->logs($res['message']);
					if($show_error){
						echo $res['message']; 	// $res['message'] - текст сообщения об ошибке
					} else {
						_e('Error!','pn');
					}					
				} elseif(isset($res['data'])) {
					$id = (int)$res["data"]["order_id"];        // уникальный числовой идентификатор платежа в вашем системе, пример: 150800
					$transaction = $res["data"]["transaction"]; // номер транзакции в системе paykassa: 96401
					$hash = $res["data"]["hash"];               // hash, пример: bde834a2f48143f733fcc9684e4ae0212b370d015cf6d3f769c9bc695ab078d1
					$currency = $res["data"]["currency"];       // валюта платежа, пример: RUB, USD
					$amount = $res["data"]["amount"];           // сумма платежа, пример: 100.50
					$system = $res["data"]["system"];           // система, пример: AdvCash
					$address = $res["data"]["address"];			// a cryptocurrency wallet address, for example: Xybb9RNvdMx8vq7z24srfr1FQCAFbFGWLg

					if(check_trans_in($m_id, $transaction, $id)){
						$this->logs('Error check trans in!');
						die('Error check trans in!');
					}	

					$data = get_data_merchant_for_id($id);
			
					$in_sum = $amount;
					$in_sum = is_sum($in_sum);
					$bid_err = $data['err'];
					$bid_status = $data['status'];
					$bid_m_id = $data['m_id'];
					$bid_m_script = $data['m_script'];
							
					if($bid_err > 0){
						$this->logs($id.' The application does not exist or the wrong ID');
						die('The application does not exist or the wrong ID');
					}					
					
					if($bid_m_script and $bid_m_script != $this->name or !$bid_m_script){	
						$this->logs('wrong script');
						die('wrong script');
					}			
					
					if($bid_m_id and $m_id != $bid_m_id or !$bid_m_id){
						$this->logs('not a faithful merchant');
						die('not a faithful merchant');				
					}					
			
					$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
			
					$bid_currency = $data['currency'];
					$bid_currency = str_replace('RUR','RUB',$bid_currency);
			
					$bid_sum = is_sum($data['pay_sum']);
					$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
			
					$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
					$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
					$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
					$invalid_check = intval(is_isset($m_data, 'check'));
			
					$en_status = array('new','techpay','coldpay');
					if(in_array($bid_status, $en_status)){ 
						if($bid_currency == $currency or $invalid_ctype > 0){
							if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){		
								$now_status = 'realpay';

								if($now_status){	
									$params = array(
										'sum' => $in_sum,
										'bid_sum' => $bid_sum,
										'bid_corr_sum' => $bid_corr_sum,
										'bid_status' => array('new','techpay','coldpay'),
										'pay_purse' => $pay_purse,
										'to_account' => $address, //is_deffin($m_defin,'SHOP_ID')
										'trans_in' => $transaction,
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
								}
										
								echo $id.'|success'; // обязательно, для подтверждения зачисления платежа
										
							} else {
								$this->logs('The payment amount is less than the provisions');
								die('The payment amount is less than the provisions');
							}
						} else {
							$this->logs('Wrong type of currency');
							die('Wrong type of currency');
						}
					} else {
						$this->logs('In the application the wrong status');
						die('In the application the wrong status');
					}					
				}

			}
			catch( Exception $e ) {
				$this->logs($e->getMessage());
				if($show_error and current_user_can('administrator')){
					die( __('Error!','pn') . ' ' .$e->getMessage() );
				} else {
					die(__('Error!','pn'));
				}
			}			
		}		
	}
}

new merchant_paykassa(__FILE__, 'PayKassa');		