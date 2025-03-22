<?php
/*
https://documenter.getpostman.com/view/7473075/SztBd9F6?version=latest
*/

if(!class_exists('WHITEBIT')){
class WHITEBIT {
	
    private $public_key = "";
	private $secret_key = "";
	private $base_url = "https://whitebit.com";
	
    function __construct($public_key, $secret_key=''){
		$this->public_key = trim($public_key);
		$this->secret_key = trim($secret_key);
    }

	function redeem_voucher($code){
		
		$json = array(
			'code' => trim($code)
		);

		$res = $this->request('/api/v4/main-account/codes/apply', $json);
		$reedem = 0;
		if(isset($res['message']) and is_string($res['message']) and strstr($res['message'], 'successfully')){
			$reedem = 1;
		}
		
		return $reedem;
	}

	function vaucher_history(){
		$json = array(
			'limit' => 50
		);
		$res = $this->request('/api/v4/main-account/codes/history', $json);
		
		$data = array();
		if(is_array($res['data'])){
			foreach($res['data'] as $d){
				$amount = is_sum(is_isset($d,'amount'));
				$code = trim(is_isset($d, 'code'));
				$status = mb_strtolower(pn_strip_input(is_isset($d, 'status')));
				$code = pn_strip_input(is_isset($d, 'code'));
				$currency = mb_strtoupper(pn_strip_input(is_isset($d, 'ticker')));
				$time = pn_strip_input(is_isset($d, 'date'));
				if($amount > 0 and $status == 'activated'){
					$data[$code] = array(
						'amount' => $amount,
						'currency' => $currency,
					);
				}
			}
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
		
		do_action('save_merchant_error', 'whitebit', 'json:' . print_r($json, true) . 'result:' . print_r($result, true));
		
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