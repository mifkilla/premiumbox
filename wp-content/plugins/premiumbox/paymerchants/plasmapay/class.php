<?php
if(!class_exists('PLASMAPAY_API')){
	class PLASMAPAY_API {
		
		private $api_key = "";
		private $username = "";
		private $test = 0;
		
		function __construct($api_key, $username=''){
			$this->api_key = trim($api_key);
			$this->username = trim($username);
		}
		
		function set_webhook($webhook_url){
			$webhook_url = trim($webhook_url);
			
			$json = array(
				'username' => $this->username,
				'url' => $webhook_url
			);
			
			return $res = $this->request('webhooks', $json);
		}
		
		function delete_webhook($webhook_id){
			$webhook_id = trim($webhook_id);
			
			$json = array(
				'username' => $this->username,
			);
			
			return $res = $this->request('webhooks/'. $webhook_id, $json);
		}		
		
		function send_money($amount, $address, $fee_payer='myself', $note='', $tokenCode=false){
			
			$data = array();
			$data['error'] = 1;
			$data['trans_id'] = 0;		
			
			/*
			myself - отправитель, receiver - получатель
			*/
			
			$note = trim($note);
			$tokenCode = trim($tokenCode);
			$address = trim($address);
			
			$json = array(
				'amount' => $amount,
				'address' => $address,
				'feePayer' => $fee_payer
			);
		
			if($note){
				$json['note'] = $note;
			}
	
			if($tokenCode){
				$json['tokenCode'] = $tokenCode;
			} 
			
			$res = $this->request('transactions', $json);
			if(isset($res['id'])){
				$res = $this->request('transactions/'. $res['id'] . '/send', array());
				if(isset($res['referenceId'])){
					$data['error'] = 0;
					$data['trans_id'] = $res['referenceId'];
				}
			}		

			return $data;
		}		
		
		function get_search($tx_id){
			$tx_id = trim($tx_id);
			$res = $this->request('transactions/'.$tx_id, '');
			if(isset($res['events'], $res['events'][0])){
				return $res['events'][0];
			}
			return array();
		}		
		
		function get_balans(){

			$res = $this->request('balances/'. $this->username, '');
			$balans = array();
			
			if(is_array($res)){
				foreach($res as $data){
					if(isset($data['balance'], $data['currency'])){
						$balans[$data['currency']] = is_sum($data['balance']);
					}
				}
			}
			
			return $balans;
		}		
	
		function request($method, $json=''){
			
			$url = 'https://app.plasmapay.com/business/api/v1/wallet/' . $method;
			
			$headers = array(
				'authorization: Bearer '. $this->api_key
			);			
			
			$curl = curl_init();
			
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLINFO_HEADER_OUT => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0'
			));		

			if(strstr($method, 'webhooks/')){
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');				
			}
			
			if(defined('CURLOPT_ENCODING')){
				curl_setopt($curl, CURLOPT_ENCODING, '');
			}			
			
			if(is_array($json)){
				curl_setopt_array($curl, array(
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => @json_encode($json),
				));
				$headers[] = 'Content-Type: application/json';				
			}
			
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			
			$result = curl_exec($curl);
			
			do_action('save_paymerchant_error', 'plasmapay', 'post: ' . print_r($json, true) . 'headers: ' . print_r($headers, true) . 'result: ' . print_r($result, true));
			
			$res = @json_decode($result, true);
			
			if($this->test == 1){
				print_r($res);
			}	

			if(is_array($res)){
				return $res;
			} 
			
			return array();
		} 	
	}
}