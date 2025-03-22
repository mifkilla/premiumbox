<?php
/*
https://geo-pay.net/api-docs/v3/
*/

if(!class_exists('GeoPay')){
	class GeoPay
	{
		private $api_key = '';
		private $prkey = '';
		private $url = 'https://partners.geo-pay.net';

		function __construct($api_key, $prkey)
		{
			$this->api_key = trim($api_key);
			$this->prkey = trim($prkey);
		}
		
		function create_invoice($currency, $amount, $reason, $id){
			$currency = strtolower(trim($currency)); //grn - гривна rubg - рубль eurg - евро
			$post = array(
				'equivalent' => $currency,
				'amount' => $amount,
				'reason' => $reason,
				'partner_transaction_id' => $id,
			);
			$res = $this->request('/api/v3/invoices/', $post);
			$data = array(
				'id' => 0,
				'url' => '',
			);
			if(isset($res['code'], $res['data']) and $res['code'] == 0){
				$res_data = @json_decode($res['data'], true);
				if(isset($res_data['invoice'],$res_data['invoice']['uuid'],$res_data['invoice']['url'])){
					$data = array(
						'id' => $res_data['invoice']['uuid'],
						'url' => $res_data['invoice']['url'],
					);
				}
			}
				return $data;
		}		
		
		function status_invoice($trans_id){
			$trans_id = trim($trans_id);
			$res = $this->request('/api/v3/invoices/'. $trans_id .'/?api_key=' . $this->api_key, array());
			$data = array(
				'id' => 0,
				'amount' => 0,
				'status' => 0,
				'currency' => '',
			);
			if(isset($res['code'], $res['data']) and $res['code'] == 0){
				$res_data = @json_decode($res['data'], true);
				if(isset($res_data['invoice'],$res_data['invoice']['partner_transaction_id'],$res_data['invoice']['amount'],$res_data['invoice']['status'],$res_data['invoice']['equivalent']['ticker'])){
					$data = array(
						'id' => $res_data['invoice']['partner_transaction_id'],
						'amount' => $res_data['invoice']['amount'],
						'status' => $res_data['invoice']['status'],
						'currency' => $res_data['invoice']['equivalent']['ticker'],
					);
				}
			}
				return $data;
		}		
		
		function request($method, $post){
			
			$ch = curl_init();
			
			curl_setopt_array($ch, array(
				CURLOPT_URL => $this->url . $method,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLINFO_HEADER_OUT => false,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
			));	
			
			$post_data_string = '';
			
			if(is_array($post) and count($post) > 0){
				
				$post['api_key'] = $this->api_key;
				
				$sig = $this->sig(json_encode($post));
				
				$post_data = array(
					'data' => json_encode($post),
					'sig' => $sig,
				);
				
				$post_data_string = json_encode($post_data, JSON_UNESCAPED_SLASHES);
				
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_string);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array( 
					'Content-Type: application/json',
				));

			} else {
				
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'SIG: ' . $this->sig($method),
				));	
				
			}			
			
			$res = curl_exec($ch);
			
			do_action('save_merchant_error', 'geopay', 'method: '. $method .', post:' . $post_data_string . ', result: ' . print_r($res, true));
			
			$result = @json_decode($res, true);
			
			return $result;		
		}	

		function sig($string){
			$signature = '';
			openssl_sign($string, $signature, $this->prkey, OPENSSL_ALGO_SHA256);
			$signature = base64_encode($signature);
			return $signature;
		}
		
	}
}