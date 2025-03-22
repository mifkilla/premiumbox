<?php
/* 
https://cryptex.net/page/api_v1
*/
if(!class_exists('Cryptex')){
	class Cryptex {
		
		private $public_key = "";
		private $secret_key = "";
		private $user_id = "";
		
		function __construct($public_key, $secret_key='', $user_id=''){
			$this->public_key = trim($public_key);
			$this->secret_key = trim($secret_key);
			$this->user_id = trim($user_id);
		}
		
		function get_balance(){

			$json = array(
				'with_reserves' => true
			); 
			$res = $this->request('Info', $json);
			
			if(isset($res['status']) and $res['status'] == 'ok'){
				if(isset($res['data']['balances'])){
					$purses = array();
					if(is_array($res['data']['balances'])){
						foreach($res['data']['balances'] as $currency => $data){
							$currency = trim($currency);
							if($currency){
								$purses[$currency] = is_sum($data);
							}
						}
					}
				}
			}
			
			return $purses;
		}	
		
		function make_voucher($amount, $currency, $user_id=''){
			$user_id = trim($user_id);
			$amount = is_sum($amount);
			
			$data = array();
			$data['error'] = 1;
			$data['coupon'] = 0;		
			
			$currency = trim((string)$currency);
			
			$json = array(
				'amount' => $amount, 
				'currency' => $currency,
			);
			if($user_id){
				$json['recipient'] = $user_id;
			}

			$res = $this->request('CouponCreate', $json);
			if(isset($res['status']) and $res['status'] == 'ok'){
				if(isset($res['data']['code'], $res['data']['amount'])){
					if(is_sum($res['data']['amount']) == $amount){
						$data['error'] = 0;
						$data['coupon'] = trim($res['data']['code']);
					}
				}
			}
			
			return $data;
		}

		function redeem_voucher($code){
			$code_arr = explode('-', $code);
			$currency = trim(is_isset($code_arr, 1));
			
			$data = array(
				'currency' => $currency,
				'amount' => 0
			);
			
			$json = array(
				'code' => trim($code)
			);

			$res = $this->request('CouponRedeem', $json);
			if(isset($res['status']) and $res['status'] == 'ok'){
				if(isset($res['data']['amount'])){
					$data['amount'] = is_sum($res['data']['amount']);
				}	
			}	
			
			return $data;
		}	
		
		function request($method, $json){

			global $pn_cryptex_nonce;
			if($pn_cryptex_nonce){
				$pn_cryptex_nonce = $pn_cryptex_nonce + 1;
			} else {
				$pn_cryptex_nonce = time();
			}
			$nonce = $pn_cryptex_nonce;

			$json['nonce'] = $nonce;
			$json['method'] = $method;

			$fields = array();
			foreach($json as $key => $value) {
				$fields[] = $key . '=' . urlencode($value);
			}
			$fields = implode('&', $fields);

			$headers = array(
				'Key:' . $this->public_key,
				'Sign:' . strtoupper(hash_hmac('sha256', $nonce . $this->user_id . $this->public_key, $this->secret_key)),
			);

			$curl = curl_init();
		
			$curl_array = array(
				CURLOPT_URL => 'https://cryptex.net/api/v1',
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLINFO_HEADER_OUT => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $fields,
			);	
			
			curl_setopt_array($curl, $curl_array);
			
			$result = curl_exec($curl);	
			$info = curl_getinfo($curl);
			
			do_action('save_merchant_error', 'cryptex', 'headers: '. print_r($headers, true) .',json:' . print_r($json, true) . ',result:' . print_r($result, true));
			
			$res = json_decode($result, true);
			return $res;
		}
	}
}