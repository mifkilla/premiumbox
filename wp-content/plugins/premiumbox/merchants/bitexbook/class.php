<?php

if(!class_exists('BitexBookApi')){
class BitexBookApi {
	
    private $api_url = "https://api.bitexbook.com";  //https://api-stage-01.bitexbook.com
    private $token = "";

    function __construct($token)
    {
        $this->token = trim($token);
    }	
	
	public function redeem_voucher($code, $pin){
		
		$params = array(
			'code' => trim($code),
			'pin' => trim($pin)	
		);
		
		$res = $this->request('bitexcode/confirm', array(), $params);
		if(isset($res['data'])){
			return $res['data'];
			
			/*
			[id] => 30 - Идентификатор кода
            [code] => NZJO1zJlRJ9rdLJy5 - Bitex-код
            [timestamp] => 1549959946.0000 - Время создания кода
            [wallet_id] => 15 - Id кошелька валюты, обеспечивающей данный код
            [sum] => 21.6 - Сумма, привязанная к данному коду
            [active] => 1 - 1 – код активен, 0 – код неактивен (уже использован)
            [pin] =>  - Pin-код (не передается, поле содержит пустую строку)
            [type] => 0 - Тип операции 0 – пополнение, 1 -снятие
            [currency] => rub - Валюта
			*/
		}
		
		return '';
	}

	public function check_voucher($code, $pin){
		
		$params = array(
			'code' => trim($code),
			'pin' => trim($pin)	
		);
		
		$res = $this->request('bitexcode', array(), $params);
		if(isset($res['data'])){
			return $res['data'];
			
			/*
			[id] => 30 - Идентификатор кода
            [code] => NZJO1zJlRJ9rdLJy5 - Bitex-код
            [timestamp] => 1549959946.0000 - Время создания кода
            [wallet_id] => 15 - Id кошелька валюты, обеспечивающей данный код
            [sum] => 21.6 - Сумма, привязанная к данному коду
            [active] => 1 - 1 – код активен, 0 – код неактивен (уже использован)
            [pin] =>  - Pin-код (не передается, поле содержит пустую строку)
            [type] => 0 - Тип операции 0 – пополнение, 1 -снятие
            [currency] => rub - Валюта
			*/
		}
		
		return '';
	}
	
	public function request($api_name, $posts_data = array(), $puts_data = array()){ 
		
		$curl = curl_init();		
		
		$url = $this->api_url . '/api/v2/'. $api_name;
		
		$headers = array(
			"Content-Type: application/json",
			"X-Auth-Token: " . $this->token
		);
		
		$c_options = array(
			CURLOPT_HTTPHEADER => $headers,
			CURLINFO_HEADER_OUT => true,
		);		
		
		if(is_array($posts_data) and count($posts_data) > 0){
			$c_options[CURLOPT_POST] = true;
			$c_options[CURLOPT_POSTFIELDS] = json_encode($posts_data, JSON_NUMERIC_CHECK);
		}
		
		if(is_array($puts_data) and count($puts_data) > 0){
			$c_options[CURLOPT_CUSTOMREQUEST] = 'PUT';
			$c_options[CURLOPT_POSTFIELDS] = json_encode($puts_data, JSON_NUMERIC_CHECK);
			
			do_action('save_merchant_error', 'bitexbook', 'put:' . print_r($puts_data, true));
		}		
		
		$result = get_curl_parser($url, $c_options, 'merchant', 'bitexbook');
		
		$err  = $result['err'];
		$out = $result['output'];
		
		do_action('save_merchant_error', 'bitexbook', 'headers:' . print_r($headers, true) . 'post:' . print_r($posts_data, true) . 'result:' . print_r($result, true));	
		
		if(!$err){
			$http_code = $result['code'];
			
			$result = $out;
			
			if($http_code == 200 AND $result = json_decode($result, true) AND json_last_error() == JSON_ERROR_NONE AND isset($result['status'])){
				return $result;
			}
		} 	
		
	}
}    
}