<?php
if(!class_exists('Kuna')){
	class Kuna {
		
		private $api_key = "";
		private $secret_key = "";

		function __construct($api_key, $secret_key) 
		{
			$this->api_key = trim($api_key);
			$this->secret_key = trim($secret_key);
		}	
		
		function create_address($currency, $return_url=''){
			$currency = trim(strtolower($currency));
			$return_url = trim($return_url);
			$data = array(
				'currency' => $currency,
			);
			if($return_url){
				$data['callback_url'] = $return_url;
			}
			$request = $this->request('v3/auth/payment_requests/address', $data);
			$res = @json_decode($request, true);
			return $res;
		}		
		
		function create_order($currency, $amount, $return_url=''){
			$currency = trim(strtolower($currency));
			$return_url = trim($return_url);
			$data = array(
				'currency' => $currency,
				'amount' => $amount,
				'payment_service' => 'default',
			);
			if($return_url){
				$data['return_url'] = $return_url;
			}
			$request = $this->request('v3/auth/merchant/deposit', $data);
			$res = @json_decode($request, true);
			return $res;
		}
		
		function get_history_orders($date_from='', $date_to=''){
			$date_from = intval($date_from);
			$date_to = intval($date_to);
			$data = array(
				'per_page' => 100,
				'page' => 1,
			);
			if($date_from){
				$data['date_from'] = $date_from;
			}
			if($date_to){
				$data['date_to'] = $date_to;
			}
			$request = $this->request('v3/auth/assets-history/deposits', $data);
			$res = @json_decode($request, true);
			
			$trans = array();
			if(isset($res['items'])){
				foreach($res['items'] as $item){
					$trans[is_isset($item,'sn')] = $item;
				}
			}
			
			return $trans;
		}

		public function request($action, $data=array()){

			$body_string = http_build_query($data);

			$url = "https://api.kuna.io/" . $action;
			$nounce = round(microtime(true) * 1000);

			$signature = '/' . $action . $nounce . $body_string;
			$sig = hash_hmac('SHA384', $signature, $this->secret_key);

			$headers = array(
				'kun-nonce: ' . $nounce,
				'kun-apikey: ' . $this->api_key,
				'kun-signature: ' . $sig,
			);
			
			if($ch = curl_init()){
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Marinu666 BTCE PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 'POST');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body_string);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);		
				$err  = curl_errno($ch);
				$res = curl_exec($ch);
				
				do_action('save_merchant_error', 'kuna', 'headers: ' . print_r($headers, true) . 'post: ' . $body_string . 'result: ' . $res);
				
				curl_close($ch);
				if(!$err){
					return $res;				
				} 
			}		
			
			return '';
		} 
	}
}