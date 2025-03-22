<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('QIWI_API')){
class QIWI_API {
	
    private $api_wallet = "";
	private $api_token_key = "";	
	private $m_id = '';
	
    function __construct($m_id, $api_wallet, $api_token_key){
		$this->api_wallet = trim($api_wallet);
		$this->api_token_key = trim($api_token_key);
		$this->m_id = trim($m_id);
    }
	
    public function get_history($start_date, $end_date, $qcom=0) { 
		$qcom = intval($qcom);
		
		$cur_syms = array(
			'643'=>'RUB',
			'840'=>'USD',
			'978'=>'EUR'
		);
	
		$curl_data = array(
			'rows'=> 50,
			'operation'=>'IN', //OUT
			'startDate'=> $start_date,
			'endDate'=> $end_date
		);
	
		$result = $this->request('https://edge.qiwi.com/payment-history/v2/persons/'. $this->api_wallet .'/payments?'.http_build_query($curl_data));
		
		$trans = array();
		
		if(isset($result['data']) and is_array($result['data'])){
			foreach($result['data'] as $d){
				if(isset($d['comment']) AND is_string($d['comment'])){
					
					$trans_id = 0;
					if($qcom != 1){
						if(preg_match('/\((.*?)\)/is', $d['comment'], $item)){	
							$trans_id = trim(is_isset($item, 1));
							$trans_id = intval($trans_id);
						}
					} else {
						$trans_id = preg_replace("/[^0-9]/", '', $d['comment']);
					}
					$trans[$trans_id] = array(
						'trans_id'=> $trans_id,
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
		}
		
		return $trans;		
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
		
		$result = get_curl_parser($request_url, $c_options, 'merchant', 'qiwinew', $this->m_id);
		
		do_action('save_merchant_error', 'qiwinew', 'headers:' . print_r($headers, true) . ' post:' . print_r($post_data, true) . ' result:' . print_r($result, true));
		
		$output = $result['output'];
		
		if(!(!empty($output) and $output = json_decode($output, true) and is_array($output) and !isset($output['errorCode']) AND !isset($output['message']))){
			$output = false;
		}	

		return $output;
	
    }
}
}