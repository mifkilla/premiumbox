<?php 
if( !defined( 'ABSPATH')){ exit(); } 

/*
https://www.notion.so/API-Docs-For-Web-services-ffc3cf6a90bc4c8fa1214a6e7f3becec
*/

if(!class_exists('AMLClass')){
	class AMLClass {
		
		private $access_id = "";
		private $access_key = "";
		
		function __construct($access_id, $access_key){
			$this->access_id = trim($access_id);
			$this->access_key = trim($access_key);
		}

		function verify_trans($address, $currency, $trans_id, $type=0){
			
			$type = intval($type);
			
			$data = array();
			$data['accessId'] = $this->access_id;
			$data['locale'] = 'en_US';
			$data['hash'] = $trans_id;
			$data['address'] = $address;
			if($type == 1){
				$data['direction'] = 'withdrawal';
			} else {
				$data['direction'] = 'deposit';
			}
			$data['asset'] = $currency;
			
			$res = $this->request('https://extrnlapiendpoint.silencatech.com', $data);
			return $res;
		}

		function verify_address($address, $currency){
			
			$data = array();
			$data['accessId'] = $this->access_id;
			$data['locale'] = 'en_US';
			$data['hash'] = $address;
			$data['asset'] = $currency;
			
			$res = $this->request('https://extrnlapiendpoint.silencatech.com', $data);
			return $res;
		}		
		
		function request($url, $data=''){

			$curl = curl_init();
		
			$curl_array = array(
				CURLOPT_URL => $url, 
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLINFO_HEADER_OUT => false,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
			);	
			
			if(is_array($data)){
				$data['token'] = md5($data['hash'] . ':' . $this->access_key . ':' . $this->access_id);
				
				$data_req = http_build_query($data);
				$curl_array[CURLOPT_POST] = true;
				$curl_array[CURLOPT_POSTFIELDS] = $data_req;
			}
			
			curl_setopt_array($curl, $curl_array);
			
			$result = curl_exec($curl);	
			$info = curl_getinfo($curl);
			
			$out = @json_decode($result, true);
			return $out;
		}
	}
}