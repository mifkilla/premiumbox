<?php
if(!class_exists('YaMoney')){
class YaMoney
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
		
		$file = $premiumbox->plugin_dir . 'merchants/'. $this->name .'/dostup/access_token_'. $this->m_id .'.php';
		if(!file_exists($file)){
			$file = $premiumbox->plugin_dir . 'merchants/'. $this->name .'/dostup/access_token.php';
		}	
		$token = '';
		if(file_exists($file)){
			$token = @file_get_contents($file);
		}
		
		return trim($token);
	}
	
	public function update_token($token){
		global $premiumbox;
		
		$token = trim(esc_html(strip_tags($token)));
		$file = $premiumbox->plugin_dir . 'merchants/'. $this->name .'/dostup/access_token_'. $this->m_id .'.php';
		file_put_contents($file, $token);
	}	
	
	public function accountInfo($token=''){
		return $this->request('https://money.yandex.ru/api/account-info', array(), $token);
	}

	public function operationHistory( $sType = null, $sLabel = null, $sFromDate = null, $sTillDate = null, $iStartRecord = null, $iReconds = null, $bDetails = true ) {
    
		return $this->request( 'https://money.yandex.ru/api/operation-history', array(
			'type' => $sType,
			'label' => $sLabel,
			'from' => $sFromDate,
			'till' => $sTillDate,
			'start_record' => $iStartRecord,
			'records' => $iReconds,
			'details' => $bDetails ? 'true' : 'false'
		));
		
	}	
	
	public function auth() {
		$code = is_param_get('code');
	
		$res = $this->request( 'https://money.yandex.ru/oauth/token', array(
			'code' => $code,
			'client_id' => $this->app_id,
			'grant_type' => 'authorization_code',
			'redirect_uri' => get_mlink($this->m_id .'_verify'),
			'client_secret' => $this->app_key,
		));
		if( isset($res['access_token']) ){
			return $res['access_token'];
		}
		return '';
	}	
	
	public function request($url, $data, $now_token=''){
		
		$data = (array)$data;
		
		$c_options = array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query( $data ),
		);
		
		$token = '';
		if($now_token){
			$token = $now_token;
		} elseif($this->token){
			$token = $this->token;
		}	
				
		if($token){	
			$c_options[CURLOPT_HTTPHEADER] = array( 'Authorization: Bearer '. $token );
		}		
		
		//CURLOPT_SSL_VERIFYPEER => true,
		//CURLOPT_CAINFO => PN_PLUGIN_DIR . 'merchants/'. $this->name .'/dostup/yandexmoney.crt',
		//CURLOPT_HEADER => true,		
		
		$result = get_curl_parser($url, $c_options, 'merchant', 'yamoney', $this->m_id);
		
		do_action('save_merchant_error', 'yamoney', 'post:' . print_r($data, true) . 'token:' . $token . 'result:' . print_r($result, true));
		
		$err  = $result['err'];
		$out = $result['output'];
		if(!$err and $out != ''){
			if($res = @json_decode( $out, true )){
				return $res;
			} 
		}	
			return '';
	}
}
}