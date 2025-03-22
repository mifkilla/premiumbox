<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('WestWallet')){
	class WestWallet {
		
		private $public_key = "";
		private $private_key = "";		
		
		function __construct($public_key, $private_key=''){
			$this->public_key = trim($public_key);
			$this->private_key = trim($private_key);
		}

		function generate_adress($currency, $ipn_url='', $label=''){
			$currency = trim($currency);
			$ipn_url = trim($ipn_url);
			$label = trim($label);
			
			$address = array(
				'address' => '',
				'dest_tag' => '',
			);
			
			$data = array('currency' => $currency);
			if($ipn_url){
				$data['ipn_url'] = $ipn_url;
			}
			if($label){
				$data['label'] = $label;
			}
			
			$res = $this->request('address/generate', $data);
			if(is_array($res) and isset($res['address'], $res['dest_tag']) and $res['address']){
				$address = array(
					'address' => trim($res['address']),
					'dest_tag' => trim($res['dest_tag']),
				);
			}
			
			return $address;
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
			
			do_action('save_merchant_error', 'westwallet', 'post:' . print_r($json, true) . 'result:' . print_r($result, true));
			
			$res = @json_decode($result, true);
			
			return $res;
		}	
	}
}