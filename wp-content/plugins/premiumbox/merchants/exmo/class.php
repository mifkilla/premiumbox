<?php
if(!class_exists('ExmoApi')){
class ExmoApi{
	
    private $key = "";
    private $secret="";
	
    function __construct($key,$secret)
    {
        $this->key = trim($key);
        $this->secret = trim($secret);
    }	
	
	public function redeem_voucher($code){
		
		$request = $this->request('excode_load', array('code'=>$code));
		$res = @json_decode($request);
		if(is_object($res) and $res->result == 1){
			return $res;
		}
		/* или данные или пустота */
		return '';
	}

	public function get_history(){
		
		$now_time = current_time('timestamp');
		$now_time_yesterday = $now_time - (1 * DAY_IN_SECONDS);

		$history = array();
		
		$request = $this->request('wallet_history', array());
		$res = @json_decode($request, true);
		if(is_array($res) and isset($res['history'], $res['result']) and $res['result'] == 1){
			$history = array_merge($history, $res['history']);
		}
		$request = $this->request('wallet_history', array('date'=>$now_time_yesterday));
		$res = @json_decode($request, true);
		if(is_array($res) and isset($res['history'], $res['result']) and $res['result'] == 1){
			$history = array_merge($history, $res['history']);
		}	
		
		$n_history = array();
		$s = -1;
		foreach($history as $his){ $s++;
			$n_history[$s] = $his;
			$n_history[$s]['date'] = date('d.m.Y H:i:s', $his['dt']);
		}
		
		$trans = array();
		foreach($n_history as $his){
			$status = mb_strtolower($his['status']);
			if($his['type'] == 'deposit' and $status == 'transferred'){ 
				$trans[] = $his;
			}
		}

			return $trans;
	}	
	
	public function request($api_name, $req = array()){ 
		
		$mt = explode(' ', microtime());
		
		global $pn_exmo_nonce;
		if($pn_exmo_nonce){
			$pn_exmo_nonce = $pn_exmo_nonce + 1;
		} else {
			$pn_exmo_nonce = $mt[1] . substr($mt[0], 2, 6);
		}
		$NONCE = $pn_exmo_nonce;		

		$url = "https://api.exmo.com/v1/$api_name";

		$req['nonce'] = $NONCE;

		$post_data = http_build_query($req, '', '&');

		$sign = hash_hmac('sha512', $post_data, $this->secret);

		$headers = array(
			'Sign: ' . $sign,
			'Key: ' . $this->key,
		);

		static $ch = null;
		
		$c_options = array(
			CURLOPT_POSTFIELDS => $post_data,
			CURLOPT_HTTPHEADER => $headers,
		);		
		$result = get_curl_parser($url, $c_options, 'merchant', 'exmo');
		
		do_action('save_merchant_error', 'exmo', 'headers:' . print_r($headers, true) . 'post:' . print_r($post_data, true) . 'result:' . print_r($result, true));
		
		$err  = $result['err'];
		$out = $result['output'];
		if(!$err){	
			return $out;
		} 		
		
	}
	
}    
}