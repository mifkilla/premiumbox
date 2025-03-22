<?php
/*
https://billline.net/ru/docs/
*/

if(!class_exists('AP_BillLine')){
class AP_BillLine
{
	private $merchant_id = "";
	private $secret_key = "";

	function __construct($merchant_id, $secret_key) 
	{
		$this->merchant_id = trim($merchant_id);
		$this->secret_key = trim($secret_key);
	}

	function get_balans($currency=''){
		$currency = strtoupper(trim($currency));
		$data = array(
			'currency' => $currency,
		);
		$res = $this->request('payment/balance', $data);
			
		$balance = '-1';
		if(isset($res['status'], $res['currency'], $res['balance']) and strtolower($res['status']) == 'success' and strtoupper($res['currency']) == $currency){
			return is_sum($res['balance'], 8);
		}
			
		return $balance;
	}

	function payout($amount, $currency, $id, $address){
		$currency = strtoupper(trim($currency));
		$method = 2;
		if($currency == 'UAH'){
			$method = 1;
		} elseif($currency == 'RUB'){
			$method = 3;
		} elseif($currency == 'EUR'){
			$method = 9;
		} elseif($currency == 'USD'){
			$method = 8;	
		}
		
		$pay_data = array();
		$pay_data['error'] = 1;
		$pay_data['trans_id'] = 0;
		
		$data = array(
			'method' => $method,
			'payout_id' => 'ap' . $id,
			'amount' => $amount,
			'account' => $address,
			'currency' => $currency,
		);
		$res = $this->request('merchant/api/payout_send', $data);
		if(isset($res['status'], $res['payout_id']) and strtolower($res['status']) != 'error' and $res['payout_id']){
			$pay_data['error'] = 0;
			$pay_data['trans_id'] = $res['payout_id'];
		}	
		return $pay_data;	
	}

	function get_history($currency, $start_time, $finish_time){
		$currency = strtoupper(trim($currency));
		$start_date = date('Y-m-d H:i:s', $start_time);
		$finish_date = date('Y-m-d H:i:s', $finish_time);
		$data = array(
			'currency' => $currency,
			'order' => '0',
			'start_date' => $start_date,
			'finish_date' => $finish_date,
			'status' => 'all',
			'type' => 'withdrawal', //deposit
		);
		$res = $this->request('api/payment-list', $data);
		$history = array();
		if(is_array($res)){
			foreach($res as $res_now){
				if(is_array($res_now)){
					$id = intval(is_isset($res_now, 'id'));
					if($id){
						foreach($res_now as $res_k => $res_v){
							$history[is_isset($res_now, 'order_id')][$res_k] = $res_v;
						}
					}
				}
			}
		}	
		return $history;		
	}	

	function request($action, $data=array()){
		$url = 'https://api.billline.net/' . $action;

		$data['merchant'] = $this->merchant_id;
			
		$dataSet = $data;
		ksort($dataSet, SORT_STRING);
		array_push($dataSet, $this->secret_key);
		$signString = implode(':', $dataSet);
		$calc_sign = base64_encode(md5($signString, true));
		$data['sign'] = $calc_sign;
			
		$body_string = http_build_query($data);

		if($ch = curl_init()){
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body_string);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
				
			$err  = curl_errno($ch);
			$res = curl_exec($ch);
				
			curl_close($ch);
				
			do_action('save_paymerchant_error', 'billline', 'action: '. $action .', post:' . print_r($data, true) . ', result: ' . print_r($res, true));	
				
			if(!$err){
				$result = @json_decode($res, true);
				return $result;				
			} 
		}		
			
			return '';
	}
}
}