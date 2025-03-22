<?php
if(!class_exists('AP_EPay')){
	class AP_EPay {
		private $account, $name, $api;
		
		function __construct($account, $name, $api) {
			$this->account = trim($account);
			$this->name = trim($name);
			$this->api = trim($api);
		}
	
		public function getBalans() {
			
			$V2_HASH = MD5($this->account .':'. $this->api);
			
			$sdata =  array( 
				'PAYER_ACCOUNT' => $this->account,
				'V2_HASH' => $V2_HASH,		
			);
			
			$outs = $this->request('getAccountBalance',$sdata);
			$results = @json_decode($outs, true);
			
			$data = array();
			
			if(isset($results['RETURN_MSG']) and $results['RETURN_MSG'] == 'SUCCESS'){
				$data['error'] = 0;
				$ACCOUNT_LIST = $results['ACCOUNT_LIST'];
				if(is_array($ACCOUNT_LIST)){
					foreach($ACCOUNT_LIST as $item){ /* USD, EUR, HKD, GBP, JPY */
						$data[$item['CURRENCY']] = $item['BALANCE'];
					}
				}
			} 
			
			return $data;	
		}	
	
		public function getHistory($batchid='', $what='prihod'){
        
			$batchid = trim($batchid);
		
			$V2_HASH = MD5($this->account .':'. $this->api);
			
			$sdata =  array( 
				'PAYER_ACCOUNT' => $this->account,
				'V2_HASH' => $V2_HASH,		
			);
			if($batchid){
				$sdata['TRAN_ID'] = $batchid;
			}
		
			$outs = $this->request('getTransactionRecords',$sdata);
			$results = @json_decode($outs, true);
		
			$data = array();
			$data['error'] = 1;
			
			if(isset($results['RETURN_MSG']) and $results['RETURN_MSG'] == 'SUCCESS'){
				$data['error'] = 0;
				$TRAN_LIST = $results['TRAN_LIST'];
				if(is_array($TRAN_LIST)){
					$r=0;
					foreach($TRAN_LIST as $tran){ 
						if($tran['PAYEE'] == $this->account and $what == 'prihod'){ $r++;
							$data['responce'][$batchid] = $tran;
						} elseif($tran['PAYER'] == $this->account and $what != 'prihod') { $r++;
							$data['responce'][$batchid] = $tran;
						}
					}
					if($r == 0){
						$data['responce'] = array();
					}
				}
			} 
			
			/* 
			1. Completed
			10. Pending
			60. Pending 
			61.Pending
			70. Pending
			*/
		
			return $data;
    	}	
	
		public function SendMoney($currency, $receiver, $amount, $item_id, $comment) {
			
			$data = array();
			$data['error'] = 1;	
			$data['trans_id'] = 0;
			$data['trans_status'] = 0; /* 0 - нет, 1-да, 2-в ожидании */
			
			$V2_HASH = MD5($this->account .':'. $amount .':'. $currency .':'. $receiver .':'.$this->api);
			
			$sdata =  array( 
				'PAYER_ACCOUNT' => $this->account,
				'PAYEE_NAME' => $this->name,
				'PAYMENT_AMOUNT' => $amount,
				'PAYMENT_UNITS' => $currency,
				'PAYMENT_ID' => $item_id,
				'MEMO' => $comment,
				'FORCED_PAYEE_ACCOUNT' => $receiver,
				'V2_HASH' => $V2_HASH,
			);
			
			$outs = $this->request('merPayment',$sdata);
			$results = @json_decode($outs, true);
			
			if(isset($results['RETURN_MSG']) and $results['RETURN_MSG'] == 'success'){
				$status = 0;
				if(isset($results['STATUS'])){
					$status = trim(is_isset($results, 'STATUS'));
				} elseif(isset($results['status'])) {
					$status = trim(is_isset($results, 'status'));
				}
				
				if($status == 2){
					$data['error'] = 0;	
					$data['trans_id'] = $results['ORDER_NUM'];
					$data['trans_status'] = 1;
				} 
			} 		
			
			return $data;
		}

		public function ESendMoney($currency, $receiver, $amount, $item_id, $comment, $type) {
			
			$data = array();
			$data['error'] = 1;	
			$data['trans_id'] = 0;	
			$data['trans_status'] = 0; /* 0 - нет, 1-да, 2-в ожидании */
			
			$receiver = trim($receiver);
			$type = trim($type); /* Transaction types(1-Perfect Money; 2-Webmoney; 3-OKPAY; 4-Payeer; 5-AdvCash; 7-PayPal; 8-FasaPay) */
			
			$V2_HASH = MD5($item_id .':'. $this->account .':'. $receiver .':'. $amount .':'. $currency .':'. $type .':'.$this->api);
			
			$sdata =  array( 
				'PAYER_ACCOUNT' => $this->account,
				'PAYMENT_AMOUNT' => $amount,
				'PAYMENT_UNITS' => $currency,
				'TYPE' => $type,
				'PAYMENT_ID' => $item_id,
				'MEMO' => $comment,
				'BANK_ACCOUNT' => $receiver,
				'V2_HASH' => $V2_HASH,
			);
			
			$outs = $this->request('eleWithdraw',$sdata);
			$results = @json_decode($outs, true);
			
			if(isset($results['RETURN_MSG']) and $results['RETURN_MSG'] == 'success'){
				$status = 0;
				if(isset($results['STATUS'])){
					$status = trim(is_isset($results, 'STATUS'));
				} elseif(isset($results['status'])) {
					$status = trim(is_isset($results, 'status'));
				}
				
				if($status == 2){
					$data['error'] = 0;	
					$data['trans_id'] = $results['ORDER_NUM'];
					$data['trans_status'] = 1;
				} elseif($status == 0){	
					$data['error'] = 0;	
					$data['trans_id'] = $results['ORDER_NUM'];
					$data['trans_status'] = 2;				
				} 
			} 		
			
			return $data;
		}		
    
		function request($method, array $data = array() ) {
        
			$url = 'https://api.epay.com/paymentApi/'.$method;
			$c_options = array(
				CURLOPT_POST => true,					
				CURLOPT_POSTFIELDS => http_build_query($data),					
			);				
			
			$c_result = get_curl_parser($url, $c_options, 'autopay', 'epay');
			
			do_action('save_paymerchant_error', 'epay', 'post: ' . print_r($data, true) . 'result: ' . print_r($c_result, true));
			
			$err  = $c_result['err'];
			$out = $c_result['output'];
			if(!$err){	
				return $out;
			} 
		
			return '';
		}
	}
}