<?php
if(!class_exists('ADGROUP_API')){
class ADGROUP_API {
	
    private $client_id = "";
	private $client_secret = "";
	private $pin = "";
	
    function __construct($client_id, $client_secret, $pin=''){
		$this->client_id = trim($client_id);
		$this->client_secret = trim($client_secret);
		$this->pin = trim($pin);
    }
	
    function get_history($limit=100) { 
		
		$limit = intval($limit);
		
		$json = array(
			'header' => array(
				'txName' => 'fetchMerchTx'
			),
			'reqData' => array(
				'start' => 0,
				'limit' => 200,
				'tx_status' => array('APPROVED'),
				'tx_type' => array('EXTERNAL-MERCHANT'),
				'protocol_type' => array('TRANSFER','BILL'),
				'universal' => 1
			)
		);
	
		$result = $this->request('https://api.adgroup.finance/transfer/get-merchant-tx', $json);
		$trans = array();
		
		if(isset($result['responseData']['transactions']) and is_array($result['responseData']['transactions'])){
			foreach($result['responseData']['transactions'] as $d){
				if(isset($d['note']) AND is_string($d['note'])){
					
					$trans_id = preg_replace( '/[^0-9]/', '', $d['note']);
					$trans[] = array(
						'trans_id' => $trans_id,
						'id' => (string)$d['_id'],
						'date' => (string)$d['ctime'],
						'status' => (string)$d['tx_status'],
						'source_address' => (string)$d['source_address'],
						'dest_address' => (string)$d['dest_address'],
						'sum_amount' => (string)$d['amount'],
						'sum_currency' => (string)$d['currency'],
						'comment' => (string)$d['note'],
						'data' => $d
					);
					
				}
			}
		}
		
		return $trans;		
    }	

	function create_link_qiwi($amount=0, $currency='RUB', $payment_method, $bids_data, $vd1, $vd2){
		$payment_method = trim($payment_method);

		$json = array(
			'header' => array(
				'txName' => 'CreateBillInvoice'
			),
			'reqData' => array(
				'platform' => 'QIWI',
				'amount' => $amount,
				'currency' => $currency,
				'ip' => pn_real_ip(),
				'client' => $bids_data->account_give,
				'address' => mb_substr($bids_data->account_give, -4),
				'recipient' => $bids_data->account_get,
				'c_from' => $vd1->xml_value,
				'c_to' => $vd2->xml_value,
				'date' => current_time('Y-m-d') . 'T' . current_time('H:i:s'),
				'txn' => $bids_data->id,
				'user_agent' => pn_maxf(pn_strip_input(is_isset($_SERVER,'HTTP_USER_AGENT')), 500),
				'email' => $bids_data->user_email,
				'cid' => $bids_data->user_id,
				'extra' => array(),
				'paySource' => $payment_method,
			)
		);

		$result = $this->request('https://api.adgroup.finance/bill-payment/invoice/create', $json);
		
		$link_data = array();
		
		if(isset($result['responseData'])){
			$link_data = array(
				'id' => is_isset($result['responseData'],'_id'),
				'comment' => is_isset($result['responseData'],'comment'),
				'link' => is_isset($result['responseData'],'paymentLink'),
			);
		}
		
		return $link_data;		
	}

	function create_link_yandex($amount=0, $currency='RUB', $payment_method, $user_id='', $bids_data){
		$user_id = trim($user_id);
		$payment_method = trim($payment_method);
		
		$json = array(
			'header' => array(
				'txName' => 'p2pInvoiceRequest'
			),
			'reqData' => array(
				'platform' => 'YANDEX',
				'amount' => $amount,
				'currency' => $currency,
				'payment_method' => $payment_method,
				'returnUrl' => get_bids_url($bids_data->hashed)
			)
		);
		
		if($user_id){
			$json['reqData']['user_id'] = $user_id;	
		}		
		
		$result = $this->request('https://api.adgroup.finance/transfer/tx-merchant-wallet', $json);
		
		$link_data = array();
		
		if(isset($result['responseData'])){
			$link_data = array(
				'id' => is_isset($result['responseData'],'_id'),
				'comment' => is_isset($result['responseData'],'comment'),
				'link' => is_isset($result['responseData'],'paymentLink'),
			);
		}
		
		return $link_data;		
	}	
	
    function request($request_url, $json){
		
		$curl = curl_init();
	
		$json = json_encode($json);
		
		$auth = base64_encode($this->client_id . ':' . $this->client_secret);
		
		curl_setopt_array($curl, array(
			CURLOPT_URL => $request_url,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $json,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLINFO_HEADER_OUT => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json",
				"Authorization: Basic $auth"
			)
		));
		
		$result = curl_exec($curl);	
		
		do_action('save_merchant_error', 'adgroup', 'json:' . print_r($json, true) . 'result:' . print_r($result, true));
		
		if(curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200 and $out = json_decode($result, true) and json_last_error() == JSON_ERROR_NONE and is_array($out) and isset($out['result'], $out['result']['status']) and $out['result']['status'] == 1){
			return $out;
		} 	
		
		return '';
    }
}
}