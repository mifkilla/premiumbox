<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('AP_WestWallet')){
	class AP_WestWallet {
		
		private $public_key = "";
		private $private_key = "";		
		
		function __construct($public_key, $private_key=''){
			$this->public_key = trim($public_key);
			$this->private_key = trim($private_key);
		}

		function get_balans(){
			$res = $this->request('wallet/balances', '');
			$balans = array();
			
			if(is_array($res) and !isset($res['message'])){
				foreach($res as $b_key => $b_sum){
					$balans[mb_strtoupper($b_key)] = is_sum($b_sum);
				}
			}
			
			return $balans;
		}

		function get_search($id){
			$id = trim($id);
			$data = array('id' => $id);
			$res = $this->request('wallet/transaction', $data);
			
			if(isset($res['id']) and $res['id'] and $res['id'] == $id){
				return $res;
			}
			return array();
		}

		function send_money($currency, $amount, $address, $dest_tag='', $description='', $priority=''){
			
			$data = array();
			$data['error'] = 1;
			$data['trans_id'] = 0;		
			
			$dest_tag = trim($dest_tag);
			$description = trim($description);
			$address = trim($address);
			$priority = trim($priority);
			
			$json = array(
				'currency' => $currency,
				'amount' => $amount,
				'address' => $address
			);
			
			if($dest_tag){
				$json['dest_tag'] = $dest_tag;
			}
	
			if($description){
				$json['description'] = $description;
			}
			
			if($priority){
				$json['priority'] = $priority;
			}
			
			$res = $this->request('wallet/create_withdrawal', $json);
			if(isset($res['id'], $res['status'])){
				$data['error'] = 0;
				$data['trans_id'] = $res['id'];
			}		

			return $data;
		}		
		
		function request($method, $json=''){
			
			$url = 'https://api.westwallet.io/' . $method;
			$ts = current_time('timestamp'); //gmdate('U');
			
			$curl_options = array(
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLINFO_HEADER_OUT => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
				CURLOPT_HTTPHEADER => array(),
			);			
			
			if(is_array($json)){
				if($method == 'wallet/transaction'){
					$json = json_encode($json, JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES);
				} else {
					$json = json_encode($json, JSON_UNESCAPED_SLASHES);
				}
				$curl_options[CURLOPT_POSTFIELDS] = $json;
				$curl_options[CURLOPT_POST] = true;
				$curl_options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
			}
			
			$headers = array(
				'X-API-KEY: '. $this->public_key,
				'X-ACCESS-TIMESTAMP: '. $ts,
				'X-ACCESS-SIGN: '.hash_hmac("sha256", $ts. $json, $this->private_key),
			);
	
			foreach($headers as $h){
				$curl_options[CURLOPT_HTTPHEADER][] = $h;
			}		

			$curl = curl_init($url);
	
			curl_setopt_array($curl, $curl_options);
	
			$result = curl_exec($curl);
			
			do_action('save_paymerchant_error', 'westwallet', 'post:' . print_r($json, true) . 'result:' . print_r($result, true));
			
			$res = @json_decode($result, true);
			
			return $res;
		}	
	}
}