<?php
/*
https://documenter.getpostman.com/view/7473075/SztBd9F6?version=latest
*/

if(!class_exists('AP_WHITEBIT')){
class AP_WHITEBIT {
	
    private $public_key = "";
	private $secret_key = "";
	private $base_url = "https://whitebit.com";
	
    function __construct($public_key, $secret_key=''){
		$this->public_key = trim($public_key);
		$this->secret_key = trim($secret_key);
    }

	function get_balance(){
		
		$json = array(); 
		$res = $this->request('/api/v4/main-account/balance', $json);
		$purses = array();
		$n = array();
		if(is_array($res)){
			foreach($res as $currency => $data){
				$currency = trim($currency);
				if($currency){
					$purses[$currency] = is_sum(is_isset($data, 'main_balance'));
				}
			}
		}
		
		return $purses;
	}

	function make_voucher($amount, $currency){
		
		$data = array();
		$data['error'] = 1;
		$data['coupon'] = 0;		
		
		$currency = trim((string)$currency);
		
		$json = array(
			'amount' => $amount, 
			'ticker' => $currency
		);

		$res = $this->request('/api/v4/main-account/codes', $json);
		if(isset($res['code'])){ 
			$data['error'] = 0;
			$data['coupon'] = trim($res['code']);
		}
		
		return $data;
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
			CURLOPT_TIMEOUT => 20,
			CURLOPT_CONNECTTIMEOUT => 20,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $dataJsonStr,
		);	
		
		curl_setopt_array($curl, $curl_array);
		
		$result = curl_exec($curl);	
		$info = curl_getinfo($curl);
		
		do_action('save_paymerchant_error', 'whitebit', 'json:' . print_r($json, true) . 'result:' . print_r($result, true));
		
		$out = @json_decode($result, true);
		
		if($ind == 1){
			return array(
				'out' => $out,
				'http_code' => trim(is_isset($info, 'http_code')),
			);
		} else {
			return $out;
		}
    }
}
}