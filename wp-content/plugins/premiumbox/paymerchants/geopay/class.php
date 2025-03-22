<?php
/*
https://geo-pay.net/api-docs/v3/
*/

if(!class_exists('AP_GeoPay')){
	class AP_GeoPay
	{
		private $api_key = '';
		private $prkey = '';
		private $url = 'https://partners.geo-pay.net';

		function __construct($api_key, $prkey)
		{
			$this->api_key = trim($api_key);
			$this->prkey = trim($prkey);
		}
		
		function create_payout($currency, $amount, $card, $id){
			$currency = strtolower(trim($currency));
			$card = trim($card);
			$post = array(
				'equivalent' => $currency,
				'amount' => $amount,
				'card_number' => $card,
				'partner_transaction_id' => 'ap' . $id,
			);
			$res = $this->request('/api/v3/payment-systems/withdraw/', $post);
			$trans_id = 0;
			if(isset($res['code'], $res['data']) and $res['code'] == 0){
				$res_data = @json_decode($res['data'], true);
				if(isset($res_data['transaction_uuid'])){
					$trans_id = $res_data['transaction_uuid'];
				}
			}
				return $trans_id;
		}		
		
		function status_payout($trans_id){
			$trans_id = trim($trans_id);
			$post = array(
				'partner_transaction_id' => $trans_id,
			);			
			$res = $this->request('/api/v3/payment-systems/withdraw/status/', $post);
			$data = array(
				'amount' => 0,
				'status' => 0,
			);
			if(isset($res['code'], $res['data']) and $res['code'] == 0){
				$res_data = @json_decode($res['data'], true);
				if(isset($res_data['status'],$res_data['amount'])){
					$data = array(
						'amount' => $res_data['amount'],
						'status' => $res_data['status'],
					);
				}
			}
				return $data;
		}		
		
		function get_balance($currency){
			$currency = strtolower(trim($currency)); //grn - гривна rubg - рубль eurg - евро
			$post = array(
				'equivalent' => $currency,
			);
			$res = $this->request('/api/v3/account/user/balance/', $post);
			$balance = '-1';
			if(isset($res['code'], $res['data'])){
				$res_data = @json_decode($res['data'], true);
				if(isset($res_data['balance'])){
					$balance = is_sum($res_data['balance']);
				}
			}
				return $balance;
		}		
		
		function request($method, $post){
			
			$ch = curl_init();
			
			curl_setopt_array($ch, array(
				CURLOPT_URL => $this->url . $method,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLINFO_HEADER_OUT => false,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
			));	
			
			$post_data_string = '';
			
			if(is_array($post) and count($post) > 0){
				
				$post['api_key'] = $this->api_key;
				
				$sig = $this->sig(json_encode($post));
				
				$post_data = array(
					'data' => json_encode($post),
					'sig' => $sig,
				);
				
				$post_data_string = json_encode($post_data, JSON_UNESCAPED_SLASHES);
				
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_string);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array( 
					'Content-Type: application/json',
				));

			} else {
				
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'SIG: ' . $this->sig($method),
				));	
				
			}			
			
			$res = curl_exec($ch);
			
			do_action('save_paymerchant_error', 'geopay', 'method: '. $method .', post:' . $post_data_string . ', result: ' . print_r($res, true));
			
			$result = @json_decode($res, true);
			
			return $result;		
		}	

		function sig($string){
			$signature = '';
			openssl_sign($string, $signature, $this->prkey, OPENSSL_ALGO_SHA256);
			$signature = base64_encode($signature);
			return $signature;
		}
		
	}
}