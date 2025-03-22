<?php
/*
https://docs.kuna.io/docs/account-balance
*/
if(!class_exists('Kuna_AP')){
	class Kuna_AP {
		
		private $api_key = "";
		private $secret_key = "";

		function __construct($api_key, $secret_key) 
		{
			$this->api_key = trim($api_key);
			$this->secret_key = trim($secret_key);
		}	
		
		function get_history_payouts($date_from='', $date_to=''){
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
			$request = $this->request('v3/auth/assets-history/withdraws', $data); 
			$res = @json_decode($request, true);
			
			$trans = array();
			if(isset($res['items'])){
				foreach($res['items'] as $item){
					$trans[is_isset($item,'id')] = $item;
				}
			}
			
			return $trans;
		}	

		function create_payout($currency, $amount, $card, $payment_id='', $comis=1, $network=''){
			$currency = trim(strtolower($currency));
			$payment_id = trim($payment_id);
			$comis = intval($comis);
			$network = trim($network);
			
			$data = array(
				'withdraw_type' => $currency,
				'amount' => $amount,
				'withdrawall' => $comis, //1 - пользователь платит комиссию, 0 - обменник платит коммиссию
			);
			$en = array('usd','uah','rub'); 
			if(in_array($currency, $en)){
				$data['gateway'] = 'default';
				$data['withdraw_to'] = $card;
			} else {
				$data['address'] = $card;
				if($network){
					$data['blockchain'] = $network;
				}
				if($payment_id){
					$data['payment_id'] = $payment_id;
				}
			}
			
			$request = $this->request('v3/auth/merchant/withdraw', $data);
			$res = @json_decode($request, true);
			return $res;
		}	
		
		function get_balans(){
			$data = array();
			$request = $this->request('v3/auth/r/wallets', $data);
			$res = @json_decode($request, true);
			$balance = array();
			if(is_array($res)){
				foreach($res as $item){
					$null = trim(is_isset($item,'0'));
					$curr = mb_strtoupper(trim(is_isset($item,'1')));
					$amount = is_sum(is_isset($item,'4'));
					if($null == 'exchange'){
						$balance[$curr] = $amount;
					}
				}
			}
			
			return $balance;	
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
				
				do_action('save_paymerchant_error', 'kuna', 'headers: ' . print_r($headers, true) . 'post: ' . $body_string . 'result: ' . print_r($res, true));
				
				curl_close($ch);
				if(!$err){
					return $res;				
				} 
			}		
			
			return '';
		} 
	}
}