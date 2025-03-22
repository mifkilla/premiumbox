<?php
if( !defined( 'ABSPATH')){ exit(); }

/* https://commerce.coinbase.com/docs/api/ */
if(!class_exists('CoinBase')){
class CoinBase {
	
    private $api_key = "";
    private $api_secret="";

    public function __construct($api_key, $api_secret) {
		$this->api_key = trim($api_key);
		$this->api_secret = trim($api_secret);
    }
	
	/*
	$api_key - ключ API
	$name - название платежа(видно платильщику на странице оплаты)
	$description - описание платежа(видно платильщику на странице оплаты)
	$pricing_type - тип цены(no_price - любая цена, fixed_price - фиксированная цена)
	$local_price - массив с ценами в формате array("amount"=>"100.00", "currency"=>"USD"), где amount - число, currency - тип валюты(USD, BTC)
	$metadata - массив дополнительных полей в формате array("key"=>"value"...), где key - ключ, value - значение
	$redirect_url - ссылка для редиректа при оплате
	$cancel_url - ссылка для редиректа при отмене

	ответ:
	Ссылка для оплаты или false в случае ошибки
	*/	
	
	public function add_link($name, $description, $pricing_type, $local_price='', $metadata='', $redirect_url='', $cancel_url=''){
		
		$name = trim($name);
		$description = trim($description);
		$pricing_type = trim($pricing_type);
		$redirect_url = trim($redirect_url);
		$cancel_url = trim($cancel_url);
		
		$post = array(
			'name' => $name,
			'description' => $description,
			'pricing_type' => $pricing_type
		);
		
		if(is_array($local_price)){
			$post['local_price'] = $local_price;
		}
		
		if(is_array($metadata)){
			$post['metadata'] = $metadata;
		}
		
		if($redirect_url){
			$post['redirect_url'] = $redirect_url;
		}
		
		if($cancel_url){
			$post['cancel_url'] = $cancel_url;
		}
		
		$headers = array(
			'Content-Type: application/json',
			'X-CC-Api-Key: ' . $this->api_key,
			'X-CC-Version: 2018-03-22'
		);
		
		$c_options = array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($post),
			CURLOPT_HTTP200ALIASES => array(200),
			CURLOPT_HTTPHEADER => $headers,
			//CURLOPT_FOLLOWLOCATION => 0
			//CURLOPT_RETURNTRANSFER => 1
			//CURLOPT_HEADER => 0
		);
		
		$res = get_curl_parser('https://api.commerce.coinbase.com/charges' , $c_options, 'merchant', 'coinbase');
		$result = $res['output'];
		
		do_action('save_merchant_error', 'coinbase', 'headers:' . print_r($headers, true) . 'post:' . print_r($post, true) . 'result:' . print_r($res, true)); 
		
		if(!empty($result) and $result = json_decode($result, true) and json_last_error() === JSON_ERROR_NONE and isset($result['data']) and isset($result['data']['hosted_url'])){
			return $result['data']['hosted_url'];
		}
		
		return '';		
	}	
}
}