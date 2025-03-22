<?php
if(!class_exists('AP_QIWI_API')){
class AP_QIWI_API {
	
    private $api_wallet = "";
	private $api_token_key = "";
	private $m_id = '';	
	
    function __construct($m_id, $api_wallet, $api_token_key){
		$this->api_wallet = trim($api_wallet);
		$this->api_token_key = trim($api_token_key);
		$this->m_id = trim($m_id);
    }
	
    public function get_history($start_date, $end_date) { 
		
		$cur_syms = array(
			'643' => 'RUB',
			'840' => 'USD',
			'978' => 'EUR'
		);
	
		$curl_data = array(
			'rows'=> 50,
			'operation'=>'OUT', //IN
			'startDate'=> $start_date,
			'endDate'=> $end_date
		);
	
		$result = $this->request('https://edge.qiwi.com/payment-history/v2/persons/'. $this->api_wallet .'/payments?'.http_build_query($curl_data));
		
		$trans = array();
		
		if(isset($result['data']) and is_array($result['data'])){
			foreach($result['data'] as $d){
				
				$qiwi_id = (string)$d['txnId'];
				$trans[$qiwi_id] = array(
					'qiwi_id'=>(string)$d['txnId'],
					'date'=>(string)$d['date'],
					'status'=>(string)$d['status'],
					'client_id'=>(string)$d['trmTxnId'],
					'account'=>(string)$d['account'],
					'sum_amount'=>(string)$d['sum']['amount'],
					'sum_currency'=>(string)$d['sum']['currency'],
					'total_amount'=>(string)$d['total']['amount'],
					'total_currency'=>(string)$d['total']['currency'],
					'total_currency_sym'=>$cur_syms[(string)$d['total']['currency']],
					'comment'=>(string)$d['comment'],
					'data'=>$d
				);			
					
			}
		}
		
		return $trans;		
    }	
	
	public function get_balances(){
		
		$cur_syms = array(
			'643' => 'RUB',
			'840' => 'USD',
			'978' => 'EUR'
		);
		
		$balances = $this->request('https://edge.qiwi.com/funding-sources/v2/persons/'. $this->api_wallet .'/accounts');
		
		$balances_data = array();
		
		if(isset($balances['accounts'])){
			foreach($balances['accounts'] as $b){
				$defaultAccount = intval(is_isset($b,'defaultAccount'));
				$hasBalance = intval(is_isset($b,'hasBalance'));
				$currency_num = trim(is_isset($b, 'currency'));
				$currency = trim(is_isset($cur_syms, $currency_num));
				if($defaultAccount == 1 and $hasBalance == 1 and isset($b['balance']) and $currency){
					$balances_data[$currency] = $b['balance']['amount'];
				}
			}
		}
		
		return $balances_data;		
	}
	
	public function send_money($wallet, $amount, $pay_method='', $comment='') {
		
		$pay_method = intval($pay_method);
		if(!$pay_method){ $pay_method = 99; }
		
		$data = array();
		$data['error'] = 1;
		$data['trans_id'] = 0;
		
		$data_post = array(
			'id' => (string)round(microtime(true) * 10000),
			'sum' => array(
				'amount'=>(float)$amount,
				'currency'=>'643'
			),
			'paymentMethod' => array(
				'type'=>'Account',
				'accountId'=>'643'
			),
			'fields' => array(
				'account'=>(string)$wallet
			),
			'comment'=>(string)$comment
		);
	
		$link = 'https://edge.qiwi.com/sinap/api/v2/terms/'. $pay_method .'/payments';
	
		$send = $this->request($link, json_encode($data_post));
		if(isset($send['transaction']['id'])){
			$data['error'] = 0;
			$data['trans_id'] = $send['transaction']['id'];		
		}		

		return $data;
	}	
	
    private function request($request_url, $post_data=false) {
		
		$headers = array(
			'Accept: application/json',
			'Content-type: application/json',
			'Authorization: Bearer '. $this->api_token_key,
		);

		$c_options = array(
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_HTTPHEADER => $headers,
			
			CURLINFO_HEADER_OUT => true,
			CURLOPT_MAXREDIRS => 3,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
			CURLOPT_ENCODING => '',
			CURLOPT_PROTOCOLS => CURLPROTO_HTTP|CURLPROTO_HTTPS,
		);
		
		if($post_data !== false){
			$c_options[CURLOPT_CUSTOMREQUEST] = 'POST';
			$c_options[CURLOPT_POSTFIELDS] = $post_data;
		}					
		
		$result = get_curl_parser($request_url, $c_options, 'autopay', 'qiwinew', $this->m_id);
		
		do_action('save_paymerchant_error', 'qiwinew', 'post: ' . print_r($post_data, true) . 'result: ' . print_r($result, true));
		
		$output = $result['output'];
		
		if(!(!empty($output) and $output = json_decode($output, true) and is_array($output) and !isset($output['errorCode']) AND !isset($output['message']))){
			$output = false;
		}	

		return $output;
    }
}
}