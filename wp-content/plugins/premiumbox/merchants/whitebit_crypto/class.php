<?php
if(!class_exists('WHITEBIT_Crypto')){
	class WHITEBIT_Crypto {
		
		private $public_key = "";
		private $secret_key = "";
		private $base_url = "https://whitebit.com";
		
		function __construct($public_key, $secret_key=''){
			$this->public_key = trim($public_key);
			$this->secret_key = trim($secret_key);
		}
		
		function create_address($currency, $network=''){
			$currency = trim($currency);
			$network = trim($network);
			
			$json = array(
				'ticker' => $currency,
			);
			if($network){
				$json['network'] = $network;
			}

			$res = $this->request('/api/v4/main-account/create-new-address', $json);
			
			return $res;
		}

		function get_history($method, $limit){ 
			$method = trim($method); /* 1 - deposits, 2 - widthd */
			$limit = trim($limit);
				
			$json = array(
				'transactionMethod' => $method,
				'limit' => $limit,
				'offset' => 0,
			);

			$res = $this->request('/api/v4/main-account/history', $json);
			
			/*
			1, ‘pending’
			2, 'pending'
			3, 'successful'
			4, 'canceled'
			5, 'unconfirmed_by_user'
			6, 'pending'
			7, 'successful'
			9, 'canceled_deposit'
			10, 'pending'
			11, 'pending'
			12, 'unsuccessful'
			13, 'pending'
			14, 'pending'
			15, 'pending'
			*/
			
			return $res;
		}		
		
		function get_nonce(){
			$nonce = intval(get_option('whitebit_nonce'));
			if($nonce){
				$nonce = $nonce + 1;
			} else {
				$nonce = (int)(microtime(true) * 1000000);
			}
			update_option('whitebit_nonce', $nonce);
		
			return (string)$nonce;
		}		
		
		function request($request, $json, $ind=0){

			$ind = intval($ind);

			$nonce = $this->get_nonce();

			$json['request'] = $request;
			$json['nonce'] = $nonce;

			$completeUrl = $this->base_url . $request;
			$dataJsonStr = json_encode($json, JSON_UNESCAPED_SLASHES);
			$payload = base64_encode($dataJsonStr);
			$signature = hash_hmac('sha512', $payload, $this->secret_key);

			$headers = array(
				'Content-type: application/json',
				'X-TXC-APIKEY:' . $this->public_key,
				'X-TXC-PAYLOAD:' . $payload,
				'X-TXC-SIGNATURE:' . $signature
			);

			$curl = curl_init();
		
			$curl_array = array(
				CURLOPT_URL => $completeUrl,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLINFO_HEADER_OUT => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $dataJsonStr,
			);	
			
			curl_setopt_array($curl, $curl_array);
			
			$result = curl_exec($curl);	
			$info = curl_getinfo($curl);
			
			do_action('save_merchant_error', 'whitebit', 'json:' . print_r($json, true) . 'result:' . print_r($result, true));
			
			if($ind == 1){
				$out = array(
					'out' => @json_decode($result, true),
					'http_code' => trim(is_isset($info, 'http_code')),
				);
			} else {
				$out = @json_decode($result, true);
			}
				return $out;
		}

	}
}