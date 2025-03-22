<?php
if(!class_exists('AP_BlockChain')){
class AP_BlockChain{
	
    private $wallet = '';
	private $api_key = '';
	private $pass = '';
	private $pass2 = '';
    
    function __construct($wallet, $api_key, $pass, $pass2) {
        $this->wallet = trim($wallet);
        $this->api_key = trim($api_key);
		$this->pass = trim($pass);
		$this->pass2 = trim($pass2);
    }
    
	function get_balans() {
		
		$curl = curl_init();
		
		do_action('save_paymerchant_error', 'blockchain', 'post: http://localhost:3000/merchant/'.urlencode($this->wallet).'/balance?password='.urlencode($this->pass).'&api_code='.urlencode($this->api_key));
		
		curl_setopt($curl, CURLOPT_URL, 'http://localhost:3000/merchant/'.urlencode($this->wallet).'/balance?password='.urlencode($this->pass).'&api_code='.urlencode($this->api_key));
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_HTTP200ALIASES, array(200));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		
		$result = curl_exec($curl);
		
		do_action('save_paymerchant_error', 'blockchain', 'result: '. print_r($result, true));
		
		if($result and $result = json_decode($result, true) and json_last_error() === JSON_ERROR_NONE and isset($result['balance'])){
			$balance = (string)$result['balance'];
			$balance = $balance / 100000000;
			return is_sum($balance, 12);
		} 
		
		return 0;			
	}
	
	function send_money($to, $amount, $fee_per_byte='') {
		
		$data = array();
		$data['error'] = 1;
		$data['trans_id'] = 0;
		
		$to = trim($to);
		$amount = $amount * 100000000;
		$fee_per_byte = intval($fee_per_byte);
		
		$query = array(
			'password' => $this->pass,
			'to' => $to,
			'amount' => $amount,
			'api_code' => $this->api_key
		);
	
		if($this->pass2){
			$query['second_password'] = $this->pass2;
		}
	
		if($fee_per_byte){
			$query['fee_per_byte'] = $fee_per_byte;
		}
	
		$query['from'] = 0;
		
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, 'http://localhost:3000/merchant/'.urlencode($this->wallet).'/payment?'.http_build_query($query));
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_HTTP200ALIASES, array(200));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	
		$result = curl_exec($curl);

		do_action('save_paymerchant_error', 'blockchain', 'result: '. print_r($result, true));

		if($result and $result = json_decode($result, true) and json_last_error() === JSON_ERROR_NONE AND isset($result['tx_hash'])){
			$data['error'] = 0;
			$data['trans_id'] = $result['tx_hash'];
		} 
	
		return $data;
	}
	
	function check_transaction($transaction_hash){
		$count = '-1';
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://blockchain.info/q/getblockcount');
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_HTTP200ALIASES, array(200));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		
		if(defined('CURLOPT_ENCODING')){
			curl_setopt($curl, CURLOPT_ENCODING, '');
		}
		
		$height = curl_exec($curl);
		
		if(!is_numeric($height)){
			return $count;
		}
		
		curl_setopt($curl, CURLOPT_URL, 'https://blockchain.info/rawtx/'.$transaction_hash);
		
		$transaction = curl_exec($curl);
		
		do_action('save_paymerchant_error', 'blockchain', 'result: '. print_r($transaction, true));
		
		if($transaction and $transaction = json_decode($transaction, true) and json_last_error() === JSON_ERROR_NONE and isset($transaction['block_height']) and is_numeric($transaction['block_height'])){
			$count = $height - $transaction['block_height'] + 1;
		}
		
		return $count;
	}

	function check_priority(){

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://api.blockchain.info/mempool/fees');
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		
		$result = curl_exec($curl);
		
		do_action('save_paymerchant_error', 'blockchain', 'result: '. print_r($result, true));
		
		$result = @json_decode($result, true);
		
		if(is_array($result)){
			return $result;
		}
		
		return array();
	}	
}
}