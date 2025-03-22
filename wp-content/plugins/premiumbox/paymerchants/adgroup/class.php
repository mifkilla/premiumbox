<?php
if(!class_exists('AP_ADGROUP_API')){
class AP_ADGROUP_API {
	
    private $client_id = "";
	private $client_secret = "";
	private $pin = "";
	
    function __construct($client_id, $client_secret, $pin=''){
		$this->client_id = trim($client_id);
		$this->client_secret = trim($client_secret);
		$this->pin = trim($pin);
    }	
	
	function get_balances($user_id=''){
		$user_id = trim($user_id);
		
		$json = array(
			'header'=>array(
				'txName'=>'MultiBalanceAPI'
			),
			'reqData'=>array(
			)
		);
		
		$balances = $this->request('https://api.adgroup.finance/accounts/fetch-all-balance', $json);
		
		$balances_data = array();
		
		if(isset($balances['responseData']['result'])){
			foreach($balances['responseData']['result'] as $b){
				$user_id_w = is_isset($b, 'user_id');
				if($user_id_w == $user_id and isset($b['accounts']) and is_array($b['accounts'])){
					foreach($b['accounts'] as $h){
						$provider = is_isset($h, 'provider');
						$sum = trim(is_isset($h,'sum'));
						$currency = trim(is_isset($h, 'currency'));
						if($provider == 'QIWI'){
							$balances_data[$currency] = $sum;
						}
					}
				}
			}
		}
		
		return $balances_data;		
	}	
	
	/*
	$sender_fname=false - имя отправителя
	$sender_lname=false - фамилия отправителя
	$sender_address=false - почтовый адрес отправителя
	$sender_city=false - город отправителя
	$sender_country=false - страна отправителя
	$receiver_fname=false - имя получателя
	$receiver_lname=false - фамилия получателя
	*/
	
	function send_money($address, $amount, $currency='', $pay_method=0, $sender_fname='', $sender_lname='', $sender_address='', $sender_city='', $sender_country='', $receiver_fname='', $receiver_lname='', $country_code='') {
		
		$pay_method = intval($pay_method);
		
		$data = array();
		$data['error'] = 1;
		$data['trans_id'] = 0;
		
		$url = '';

		if($pay_method == 0){
			$json = array(
				'header' => array(
					'txName' => 'P2Card',
				),
			);		
			$json['reqData']['platform'] = 'QIWI';
			$url = 'https://api.adgroup.finance/transfer/send-card-external';
		} 
		
		if($pay_method == 5){
			$json = array(
				'header' => array(
					'txName' => 'QiwiPayout'
				),
			);		
			$country_code = trim($country_code);
			$json['reqData']['platform'] = 'QIWI';
			$json['reqData']['country_code'] = $country_code;
			$url = 'https://api.adgroup.finance/transfer/send-mobile-external';	
			$address = preg_replace( '/[^0-9]/', '', $address);
			$address = '+'.str_replace('+','',$address);			
		}			
			
		if($pay_method == 2){
			$json = array(
				'header'=>array(
					'txName'=>'QiwiPayout'
				),
			);			
			$json['reqData']['platform'] = 'QIWI';
			$url = 'https://api.adgroup.finance/transfer/send-webmoney-external';			
		}
		
		if($pay_method == 3){
			$json = array(
				'header'=>array(
					'txName'=>'QiwiPayout'
				),
			);			
			$json['reqData']['platform'] = 'QIWI';
			$url = 'https://api.adgroup.finance/transfer/send-yandex-external';			
		}
		
		if($pay_method == 4){
			$json = array(
				'header'=>array(
					'txName'=>'YandexPayout'
				),
			);			
			$json['reqData']['platform'] = 'YANDEX';
			$url = 'https://api.adgroup.finance/transfer/send-wallet-external';			
		}

		if($pay_method == 1){
			$json = array(
				'header'=>array(
					'txName'=>'QiwiPayout'
				),
			);			
			$json['reqData']['platform'] = 'QIWI';
			$url = 'https://api.adgroup.finance/transfer/send-wallet-external';			
		}		

		$json['reqData']['amount'] = $amount;
		$json['reqData']['pin'] = $this->pin;
		$json['reqData']['address'] = $address;
		$json['reqData']['currency'] = $currency;
		
		if($sender_fname){
			$json['reqData']['sender_fname'] = $sender_fname;
		}
		if($sender_lname){
			$json['reqData']['sender_lname'] = $sender_lname;
		}
		if($sender_address){
			$json['reqData']['sender_address'] = $sender_address;
		}
		if($sender_city){
			$json['reqData']['sender_city'] = $sender_city;
		}
		if($sender_country){
			$json['reqData']['sender_country'] = $sender_country;
		}
		if($receiver_fname){
			$json['reqData']['receiver_fname'] = $receiver_fname;				
		}
		if($receiver_lname){				
			$json['reqData']['receiver_lname'] = $receiver_lname;				
		}		
	
		$result = $this->request($url, $json);
		if(isset($result['responseData'], $result['responseData']['status']) and $result['responseData']['status'] == 'APPROVED'){
			$data['error'] = 0;
			$data['trans_id'] = $result['responseData']['_id']; //extra_id, ref_id
		}			

		return $data;
	}	
	
    function request($request_url, $json) {
		
		$curl = curl_init();
	
		$json = json_encode($json);
		
		$auth = base64_encode($this->client_id.':'.$this->client_secret);
		
		curl_setopt_array($curl, array(
			CURLOPT_URL => $request_url,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $json,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLINFO_HEADER_OUT => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json",
				"Authorization: Basic $auth"
			)
		));
		
		$result = curl_exec($curl);	

		do_action('save_paymerchant_error', 'adgroup', 'json: ' . print_r($json, true) . 'result: ' . print_r($result, true));

		if(curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200 and $out = json_decode($result, true) and json_last_error() == JSON_ERROR_NONE and is_array($out) and isset($out['result'], $out['result']['status']) and $out['result']['status'] == 1){
			return $out;
		} 		
		
		return '';
    }
}
}