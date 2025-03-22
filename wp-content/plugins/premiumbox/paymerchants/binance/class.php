<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('AP_Binance')){
class AP_Binance {
	
    private $api_key = "";
	private $api_secret = "";

    function __construct($api_key, $api_secret)
    {
        $this->api_key = trim($api_key);
		$this->api_secret = trim($api_secret);
    }	
	
	public function get_balans(){
		$res = $this->request('/api/v3/account', array(), 0);
		$purses = array();
		if(isset($res['balances'])){
			foreach($res['balances'] as $value){
				$currency = strtoupper(trim(is_isset($value,'asset')));
				$amount = is_sum(is_isset($value,'free'));
				$purses[$currency] = $amount;
			}			
		}
			return $purses;
	}
	
	public function send_money($currency, $amount, $address, $network='', $addressTag=''){
		$currency = mb_strtoupper($currency);
		$addressTag = trim($addressTag);
		$network = trim($network);
		$data = array(
			'asset' => $currency,
			'address' => $address,
			'amount' => $amount
		);
		if($addressTag){
			$data['addressTag'] = $addressTag;
		}	
		if($network){
			$data['network'] = $network;
		}
		$res = $this->request('/wapi/v3/withdraw.html', $data, 1);
		return $res;
	}
	
	public function buy($execution_type, $symbol, $price, $start_volume, $timeInForce=''){
		$execution_type = intval($execution_type);
		if($execution_type == 1){ 
			$type = 'LIMIT'; 
		} else { 
			$type = 'MARKET'; 
		}
	
		$data = array(
			'newOrderRespType'=>'RESULT'
		);
		$data['symbol'] = $symbol;
		$data['side'] = 'BUY';
		$data['quantity'] = $start_volume;
		$data['type'] = $type;
		if($execution_type == 1){
			$data['price'] = $price;
			$data['timeInForce'] = $timeInForce;
		}
		
		$res = $this->request('/api/v3/order', $data, 1);
		return $res;
	}	
	
	public function get_payout_transactions($startTime='', $endTime='', $currency=''){
		$data = array();
		
		$currency = trim($currency);
		if($currency){
			$data['asset'] = $currency;
		}

		if($startTime){
			$data['startTime'] = $startTime . '000';
		}
		
		if($endTime){
			$data['endTime'] = $endTime.'000';
		}		
		
		$res = $this->request('/wapi/v3/withdrawHistory.html', $data, 0);
		
		$transactions = array();
		if(isset($res['withdrawList']) and is_array($res['withdrawList'])){
			foreach($res['withdrawList'] as $data){
				$transactions[$data['id']] = $data;
			}
		}
		return $transactions;
	}
	
	function coins_info(){
		$data = array();
		$res = $this->request('/sapi/v1/capital/config/getall', $data, 0);
		
		$info = array();
		if(is_array($res) and !isset($res['code'])){
			$info = $res;
		}
	
		return $info;
	}

	function tradeFee(){
		$data = array();
		$res = $this->request('/wapi/v3/tradeFee.html', $data, 0);
		if(isset($res['tradeFee']) and is_array($res['tradeFee'])){
			foreach($res['tradeFee'] as $fee){
				$data[$fee['symbol']] = $fee;
			}
		}
		return $data;
	}	
	
	public function request($api_name, $data = array(), $post=1){ 
		$post = intval($post);
		$api_name = trim($api_name);
		
		$data['timestamp'] = $this->get_time();
		
		$post_data = http_build_query($data, '', '&');
		$signature = hash_hmac('sha256', $post_data, $this->api_secret);
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL=>'https://api.binance.com'. $api_name .'?'. $post_data .'&signature='. $signature,
			CURLOPT_FOLLOWLOCATION=>false,
			CURLOPT_RETURNTRANSFER=>true,
			CURLOPT_HEADER=>false,
			CURLINFO_HEADER_OUT=>true,
			CURLOPT_SSL_VERIFYPEER=>false,
			CURLOPT_SSL_VERIFYHOST=>0,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_USERAGENT=>'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
			CURLOPT_HTTPHEADER=>array(
				'X-MBX-APIKEY: '. $this->api_key
			)
		));
		
		if($post){
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, '');
		}
		$result = curl_exec($curl);
		$res = @json_decode($result, true);
		
		do_action('save_paymerchant_error', 'binance', 'name: '. $api_name .', post:' . $post_data . ', result: ' . print_r($result, true));
		
		return $res;
	}
	
	function exchangeInfo(){
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL=>'https://api.binance.com/api/v3/exchangeInfo',
			CURLOPT_FOLLOWLOCATION=>false,
			CURLOPT_RETURNTRANSFER=>true,
			CURLOPT_HEADER=>false,
			CURLINFO_HEADER_OUT=>true,
			CURLOPT_SSL_VERIFYPEER=>false,
			CURLOPT_SSL_VERIFYHOST=>0,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_USERAGENT=>'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0'
		));
		$result = curl_exec($curl);
		$res = @json_decode($result, true);
		
		do_action('save_paymerchant_error', 'binance', 'name: exchangeInfo, result: ' . print_r($result, true));
		
		$info = array();
		if(isset($res['symbols']) and is_array($res['symbols'])){
			foreach($res['symbols'] as $d){
				$symbol = trim($d['symbol']);
				$status = trim($d['status']);
				$filters = array();
				foreach($d['filters'] as $dfi){
					$filters[$dfi['filterType']] = $dfi;
				}
				if($status == 'TRADING'){
					$info[$symbol]['filters'] = $filters;
					$info[$symbol]['symb'] = $d['baseAssetPrecision'];
				}
			}
		}
		return $info;
	}	
	
	function get_time(){
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL=>'https://api.binance.com/api/v1/time',
			CURLOPT_FOLLOWLOCATION=>false,
			CURLOPT_RETURNTRANSFER=>true,
			CURLOPT_HEADER=>false,
			CURLINFO_HEADER_OUT=>true,
			CURLOPT_SSL_VERIFYPEER=>false,
			CURLOPT_SSL_VERIFYHOST=>0,
			CURLOPT_USERAGENT=>'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0'
		));
		$result = curl_exec($curl);
		$res = @json_decode($result, true);
		return is_isset($res, 'serverTime');
	}	
}    
}