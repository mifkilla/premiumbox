<?php
if(!class_exists('AP_YaMoney')){
class AP_YaMoney
{
	private $token, $app_id, $app_key, $name, $m_id;
	
    function __construct($app_id, $app_key, $name, $m_id)
    {
		$this->app_id = trim($app_id);
		$this->app_key = trim($app_key);
		$this->name = trim($name);
		$this->m_id = trim($m_id);
		$this->token = $this->get_token();	
    }		
	
	function get_token(){
		global $premiumbox;
		
		$file = $premiumbox->plugin_dir . 'paymerchants/'. $this->name .'/dostup/access_token_'. $this->m_id .'.php';
		if(!file_exists($file)){
			$file = $premiumbox->plugin_dir . 'paymerchants/'. $this->name .'/dostup/access_token.php';
		}
		if(!file_exists($file)){
			@file_put_contents($file, ' ');
		}	
		$token = '';
		if(file_exists($file)){
			$token = @file_get_contents( $file );
		}
		return trim($token);
	}	
	
	public function update_token($token){
		global $premiumbox;
		
		$token = trim(esc_html(strip_tags($token)));
		$file = $premiumbox->plugin_dir . 'paymerchants/'. $this->name .'/dostup/access_token_'. $this->m_id .'.php';
		file_put_contents($file, $token);
		
	}		
	
	public function accountInfo($token=''){
		return $this->request('https://money.yandex.ru/api/account-info', array(), $token);
	}	
	
	public function get_card_key($account){
		$account = trim((string)$account);
		$card_key = '';
		
		$curl = curl_init('https://paymentcard.yamoney.ru/gates/card/storeCard');
		
		$post = array(
			'skr_destinationCardNumber' => $account,
			'skr_successUrl' => '',
			'skr_errorUrl' => ''
		);
		
		curl_setopt_array($curl, array(
			CURLOPT_POST =>true,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_POSTFIELDS => http_build_query($post),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => true,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2
		));
		
		do_action('save_paymerchant_error', 'yamoney', 'post: ' . print_r($post, true));
		
		$body = curl_exec($curl);
		
		do_action('save_paymerchant_error', 'yamoney', 'result: ' . print_r($body, true));
		
		if(preg_match('/Location: (.+)/i', $body, $loc) AND $loc = preg_replace('/.*\?/', '', $loc[1])){
			parse_str($loc, $locd);
			if(!empty($locd['skr_destinationCardSynonim'])){
				$card_key = $locd['skr_destinationCardSynonim'];
			}
		}		
		
		return $card_key;
	}
	
	public function addPay($purse, $sum, $pay_type=2, $comment, $label) {
		
		$array = array(
			'pattern_id' => 'p2p',
			'to' => $purse,
			'comment' => $comment,
			'message' => $comment,
			'label' => $label,
		);
		
		if($pay_type == 1){ //Сумма к оплате (столько заплатит отправитель)
			$array['amount'] = $sum;
		} else if($pay_type == 2){ //Сумма к получению (придет на счет получателя счет после оплаты)
			$array['amount_due'] = $sum;
		}				
		
		$res = $this->request( 'https://money.yandex.ru/api/request-payment', $array);
		
		if(isset($res['request_id'])){
			return $res['request_id'];
		}
		
			return 0;
	}

	public function processPay($request_id) {
		
		$data = array();
		$data['error'] = 1;
		$data['payment_id'] = 0;
		
		$res = $this->request( 'https://money.yandex.ru/api/process-payment', array(
			'request_id' => $request_id,
		));
		
		if(isset($res['payment_id'])){
			$data['error'] = 0;
			$data['payment_id'] = $res['payment_id'];
		}
		
		return $data;
	}	
	
	public function requestPay($card_key, $amount, $pay_type=2){

		$pay_type = intval($pay_type);
		$card_key = trim($card_key);
	
		$array = array(
			"pattern_id" => "6686",
			"skr_destinationCardSynonim" => $card_key,
		);
		
		if($pay_type == 1){ //Сумма к оплате (столько заплатит отправитель)
			$array['sum'] = $amount;
		} else if($pay_type == 2){ //Сумма к получению (придет на счет получателя счет после оплаты)
			$array['net_sum'] = $amount;
		}		
		
		$res = $this->request( 'https://money.yandex.ru/api/request-payment', $array);
	
		if(isset($res['request_id'])){
			return $res['request_id'];
		}
		
		return 0;	
	}
	
	public function auth(){
		$code = $_GET['code'];
	
		$res = $this->request( 'https://money.yandex.ru/oauth/token', array(
			'code' => $code,
			'client_id' => $this->app_id,
			'grant_type' => 'authorization_code',
			'redirect_uri' => get_merchant_link('ap_'. $this->m_id .'_verify'),
			'client_secret' => $this->app_key
		));
		if( isset($res['access_token']) ){
			return $res['access_token'];
		}
		return '';
	}	
	
	public function request($url, $data, $now_token=''){
		
		$data = (array)$data;
		
		$token = '';
		if($now_token){
			$token = $now_token;
		} elseif($this->token){
			$token = $this->token;
		}		
		
		do_action('save_paymerchant_error', 'yamoney', 'post:' . print_r($data, true));
		
		$c_options = array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($data),
		);	
				
		if($token){	
			$c_options[CURLOPT_HTTPHEADER] = array( 'Authorization: Bearer '. $token );
		}			
		
		do_action('save_paymerchant_error', 'yamoney', 'token:' . print_r($token, true));
			
		$c_result = get_curl_parser($url, $c_options, 'autopay', 'yamoney');
		
		do_action('save_paymerchant_error', 'yamoney', 'result:' . print_r($c_result, true));
		
		$err  = $c_result['err'];
		$out = $c_result['output'];		
		if(!$err and $out != ''){
			if($res = @json_decode( $out, true )){
				return $res;
			} 
		}	
		
		return '';
	}
	
}
}