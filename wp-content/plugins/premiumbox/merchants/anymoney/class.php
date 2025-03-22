<?php
//https://docs.any.money/ru/
if(!class_exists('AnyMoney')){
class AnyMoney
{
	private $api_key = '';
	private $merchant_id = '';

	function __construct($api_key, $merchant_id)
	{
		$this->api_key = trim($api_key);
		$this->merchant_id = trim($merchant_id);
	}

	function get_pwcurrency(){
		$data = array(
			"method" => "pwcurrency.list", 
			"jsonrpc" => "2.0",
			"id" => '1'
		);
		$res = $this->request($data);
		return $res;
	}

	function get_invoice($amount, $externalid, $client_email, $payway='', $in_curr='', $out_curr='', $callback_url='', $merchant_payfee='', $redirect_url=''){
		$in_curr = trim($in_curr);
		$out_curr = trim($out_curr);
		$payway = trim($payway);
		$amount = trim($amount);
		$amount = (string)$amount;
		$externalid = trim($externalid);
		$callback_url = trim($callback_url);
		$merchant_payfee = is_sum($merchant_payfee);
		$merchant_payfee = (string)$merchant_payfee;
		$redirect_url = trim($redirect_url);
		
		$data = array(
			"method" => "invoice.create", 
			"params" => array("amount" => $amount, 'externalid' => 'in_' . $externalid, 'payway' => $payway, 'in_curr' => $in_curr, 'client_email' => $client_email, 'merchant_payfee' => $merchant_payfee), 
			"jsonrpc" => "2.0",
			"id" => '1'
		);
		if($out_curr){
			$data['params']['out_curr'] = $out_curr;
		}
		if($callback_url){
			$data['params']['callback_url'] = $callback_url;
		}
		if($redirect_url){
			$data['params']['redirect_url'] =  $redirect_url;
		}
		
		$res = $this->request($data);
		return $res;
	}

	function get_history_invoice($count){
		$data = array(
			'method' => 'history.invoice',
			'params' => array('count' => $count),
			"jsonrpc" => "2.0",
			"id" => '1'
		);
		$res = $this->request($data);
		$data = array();
		if(isset($res['result'], $res['result']['data']) and is_array($res['result']['data'])){
			foreach($res['result']['data'] as $d){
				$data[$d['externalid']] = $d;
			}
		}
		return $data;
	}	

	function request($data){
		
		$utc_now = strval(((int)round(microtime(true) * 1000)));

		$data_string = json_encode($data);
		
		$params = is_isset($data, 'params');
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://api.any.money/',
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $data_string,
			CURLOPT_HEADER => false,
			CURLINFO_HEADER_OUT => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string),
				'x-merchant: ' . $this->merchant_id,
				'x-signature: ' . $this->sign_data($this->api_key, $params ?: array(), $utc_now),
				'x-utc-now-ms: ' . $utc_now
			)
		));	
		$res = curl_exec($curl);
		
		do_action('save_merchant_error', 'anymoney', 'post:' . print_r($data, true) . ', result: ' . print_r($res, true));
		
		$result = @json_decode($res, true);
		
		return $result;		
	}

	function sign_data($key, $data, $utc_now){
		ksort($data);
		$s = '';
		foreach($data as $k => $value) {
			if (in_array(gettype($value), array('array', 'object', 'NULL')) ){
				continue;
			}
			if(is_bool($value)){
				$s .= $value ? "true" : "false";
			} else {
				$s .= $value;
			}
		}
		$s .= $utc_now;
		return hash_hmac('sha512', strtolower($s), $key);
	}
}
}