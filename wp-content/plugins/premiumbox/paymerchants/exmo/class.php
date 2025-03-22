<?php
/* 
https://exmo.com/ru/api_doc#/excode_api
*/

if(!class_exists('AP_ExmoApi')){
class AP_ExmoApi{
	
    private $key = "";
    private $secret="";

    function __construct($key,$secret)
    {
        $this->key = trim($key);
        $this->secret = trim($secret);
    }	
	
	public function make_voucher($amount, $currency, $user_login=''){
		$data = array();
		$data['error'] = 1;
		$data['trans_id'] = 0;
		$data['coupon'] = 0;		
		/*
		["USD","EUR","RUB","BTC","DOGE","DASH","ETH","LTC"]
		*/
		$amount = sprintf("%0.8F",$amount);
		$amount = rtrim($amount,'0');
		$amount = rtrim($amount,'.');
		
		$currency = trim((string)$currency);
		$user_login = trim($user_login);
		
		$req_data = array(
			'amount'=>$amount, 
			'currency' => $currency
		);
		if($user_login){
			$req_data['login'] = $user_login;
		}

		$request = $this->request('excode_create', $req_data);
		$res = @json_decode($request);
		if(is_object($res) and $res->result == 1){ 
			$code = trim((string)$res->code);
			if(strstr($code, 'EX-CODE')){ 
				$data['error'] = 0;
				$data['trans_id'] = trim((string)$res->task_id);
				$data['coupon'] = $code;
			}
		}
		return $data;
	}	

	public function get_balans(){
		
		$request = $this->request('user_info', array());
		$res = @json_decode($request);
		if(is_object($res) and isset($res->balances) and is_object($res->balances)){
			$purses = array();
			foreach($res->balances as $currency => $value){
				$currency = trim($currency);
				$value = trim($value);
				$purses[$currency] = $value;
			}
			return $purses;
		}
		/* или массив или пустота */
		return '';
	}	
	
	public function request($api_name, $req = array()){ 
		
		$mt = explode(' ', microtime());
		
		global $pn_exmo_nonce;
		if($pn_exmo_nonce){
			$pn_exmo_nonce = $pn_exmo_nonce + 1;
		} else {
			$pn_exmo_nonce = $mt[1] . substr($mt[0], 2, 6);
		}
		$NONCE = $pn_exmo_nonce;

		$url = "http://api.exmo.com/v1/$api_name";

		$req['nonce'] = $NONCE;

		$post_data = http_build_query($req, '', '&');

		$sign = hash_hmac('sha512', $post_data, $this->secret);

		$headers = array(
			'Sign: ' . $sign,
			'Key: ' . $this->key,
		);

		static $ch = null;
		if (is_null($ch)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$err  = curl_errno($ch);
		$out = curl_exec($ch);
		
		do_action('save_paymerchant_error', 'exmo', 'url: ' . $url . 'post: ' . print_r($post_data, true) . 'headers: ' . print_r($headers, true) . 'result: ' . print_r($out, true));
		
		if(!$err){	
			return $out;
		} 	
		return '';
	}
}    
}