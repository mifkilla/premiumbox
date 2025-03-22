<?php

if(!class_exists('AP_XForta')){
	class AP_XForta {
		
		private $token = "";
		
		function __construct($token=''){
			$this->token = trim($token);
		}

		function send($amount, $card, $id){
			$amount = is_sum($amount) * 100;
			
			$post = array();
			$post['order_id'] = $id;
			$post['cards'][0] = array(
				'cardnumber' => trim($card),
				'amount' => $amount,
			);
			
			$res = $this->request('transferToCard', $post);
			if(isset($res['id'])){
				return intval($res['id']);
			}	
			return 0;
		}

		function check($id){
			$id = intval($id);
			$res = $this->request('transferToCard?order_id='. $id, array());
			return $res;
		}

		function get_balance(){
			$res = $this->request('transferBalance', array());
			$balance = '-1';
			if(isset($res['available_balance'])){
				$balance = is_sum($res['available_balance']);
			}	
				return $balance;
		}		
		
		function request($method, $data=array()){

			$headers = array(
				"Content-Type: application/json",
				'Authorization: Token: ' . $this->token
			);

			$curl = curl_init();
		
			$curl_array = array(
				CURLOPT_URL => 'https://xforta.com/api/' . $method, 
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLINFO_HEADER_OUT => false,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_CONNECTTIMEOUT => 20,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
				CURLOPT_HTTPHEADER => $headers,
			);	
			
			if(is_array($data) and count($data) > 0){
				$data_req = json_encode($data);
				$curl_array[CURLOPT_POST] = true;
				$curl_array[CURLOPT_POSTFIELDS] = $data_req;
			}
			
			curl_setopt_array($curl, $curl_array);
			
			$result = curl_exec($curl);	
			$info = curl_getinfo($curl);
			
			do_action('save_merchant_error', 'xforta', 'data:' . print_r($data, true) . 'result:' . print_r($result, true));
			
			$out = @json_decode($result, true);
			return $out;
		}
	}
}