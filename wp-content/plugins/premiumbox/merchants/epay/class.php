<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('EPay')){
	class EPay {
		private $account, $name, $api;
		
		function __construct($account, $name, $api) {
			$this->account = trim($account);
			$this->name = trim($name);
			$this->api = trim($api);
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
			
			do_action('save_merchant_error', 'epay', $outs);
			
			$results = @json_decode($outs, true);
		
			$data = array();
			$data['error'] = 1;
			
			if(isset($results['RETURN_MSG']) and $results['RETURN_MSG'] == 'success' or isset($results['RETURN_MSG']) and $results['RETURN_MSG'] == 'SUCCESS'){
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
			} else {
				do_action('save_merchant_error', 'epay', $results);
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
    
		function request($method, array $data = array() ) {
        
			$url = 'https://api.epay.com/paymentApi/'.$method;
			$c_options = array(
				CURLOPT_POST => true,					
				CURLOPT_POSTFIELDS => http_build_query($data),					
			);				
			
			$c_result = get_curl_parser($url, $c_options, 'merchant', 'epay');
			
			do_action('save_merchant_error', 'epay', 'post:' . print_r($data, true) . 'result:' . print_r($c_result, true));
			
			$err  = $c_result['err'];
			$out = $c_result['output'];
			if(!$err){	
				return $out;
			} 
				return '';
		}
	}
}