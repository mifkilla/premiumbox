<?php
/*
https://garantexio.github.io/#347f4bc1c5
*/

if(!class_exists('JWT')){
	class JWT
	{
		function __construct(){
			
		}	
		
		function encode($payload, $key)
		{
			$algo = 'RS256';
			$header = array('typ' => 'JWT', 'alg' => $algo);

			$segments = array();
			$segments[] = $this->urlsafeB64Encode($this->jsonEncode($header));
			$segments[] = $this->urlsafeB64Encode($this->jsonEncode($payload));
			$signing_input = implode('.', $segments);

			$signature = $this->sign($signing_input, $key);
			$segments[] = $this->urlsafeB64Encode($signature);

			return implode('.', $segments);
		}

		function sign($payload, $key)
		{
			$passphrase = '';
			
			$algo = OPENSSL_ALGO_SHA256;
			$key_type = OPENSSL_KEYTYPE_RSA;
			
			$privateKey = openssl_pkey_get_private($key, $passphrase);
			
			if (is_bool($privateKey)) {
				$error = openssl_error_string();
				throw new Exception($error);
			}

			$details = openssl_pkey_get_details($privateKey);
			
			if (!array_key_exists('key', $details) || $details['type'] !== $key_type) {
				throw new Exception("Invalid key provided");
			}
			
			$signature = '';

			if (!openssl_sign($payload, $signature, $privateKey, $algo)) {
				$error = openssl_error_string();
				throw new Exception($error);
			}

			return $signature;
		}

		function jsonDecode($input)
		{
			$obj = json_decode($input);
			if (function_exists('json_last_error') && $errno = json_last_error()) {
				$this->_handleJsonError($errno);
			} else if ($obj === null && $input !== 'null') {
				throw new Exception('Null result with non-null input');
			}
			return $obj;
		}

		function jsonEncode($input)
		{
			$json = json_encode($input);
			if (function_exists('json_last_error') && $errno = json_last_error()) {
				$this->_handleJsonError($errno);
			} else if ($json === 'null' && $input !== null) {
				throw new Exception('Null result with non-null input');
			}
			return $json;
		}

		function urlsafeB64Decode($input)
		{
			$remainder = strlen($input) % 4;
			if ($remainder) {
				$padlen = 4 - $remainder;
				$input .= str_repeat('=', $padlen);
			}
			return base64_decode(strtr($input, '-_', '+/'));
		}

		function urlsafeB64Encode($input)
		{
			return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
		}

		function _handleJsonError($errno)
		{
			$messages = array(
				JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
				JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
				JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
			);
			throw new Exception(
				isset($messages[$errno])
				? $messages[$errno]
				: 'Unknown JSON error: ' . $errno
			);
		}
	}
}

if(!class_exists('Garantex_Crypto')){
	class Garantex_Crypto {
		private $token = "";
		public $private_key = '';
		public $uid = '';
		public $host = 'garantex.io';
		
		/*
		ERC20 :: usdt
		OMNI :: usdt-omni
		TRON :: usdt-tron 
		*/
		
		function __construct($private_key='', $uid=''){
			$this->private_key = trim($private_key);
			$this->uid = trim($uid);
			$this->set_token();
		}
		
		function set_token(){
			$token = trim($this->token);
			if(!$token){
				$request = array('exp' => time() + 3600, 'jti' => bin2hex(random_bytes(12)));
				$class = new JWT();
				$payload = $class->encode($request, base64_decode($this->private_key, true));
				$post_data = array('kid' => $this->uid, 'jwt_token' => $payload);
				
				$ch = curl_init("https://dauth.{$this->host}/api/v1/sessions/generate_jwt");
				curl_setopt_array($ch, array(
					CURLOPT_POST => true,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_HEADER => false,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_TIMEOUT => 20,
					CURLOPT_CONNECTTIMEOUT => 20,
					CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
					CURLOPT_POSTFIELDS => json_encode($post_data)
				));

				$response = curl_exec($ch);
				$result = @json_decode($response, true);
				
				do_action('save_merchant_error', 'garantex', 'data:' . print_r($post_data, true) . 'result:' . print_r($result, true));

				if(isset($result['token'])){
					$this->token = $result['token'];
				}
			}
		}
		
		function create_address($currency){
			$currency = trim(strtolower($currency));
			
			$post = array(
				'currency' => $currency,
			);
			$res = $this->request('/api/v2/deposit_address', $post);
			
			$data = array();
			
			if(isset($res['id'])){
				$id = intval($res['id']);
				if($id){
					sleep(5);
					
					$res2 = $this->request('/api/v2/deposit_address/details?id=' . $id, array());
					
					if(isset($res2['address'])){
						$data['address'] = $res2['address'];
					}
				}
			}
			return $data;
		}

		function get_history_deposits($limit){
			$limit = intval($limit);
			$res = $this->request('/api/v2/deposits?limit=' . $limit, array());
			$trans = array();
			if(is_array($res) and !isset($res['error'])){
				$trans = $res;
			}
			return $trans;
		}		
		
		function request($method, $post=array()){

			$curl = curl_init();
			$curl_array = array(
				CURLOPT_URL => 'https://' . $this->host . $method,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLINFO_HEADER_OUT => false,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_CONNECTTIMEOUT => 20,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0',
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json",
					"Authorization: Bearer {$this->token}"
				)
			);	
			
			if(is_array($post) and count($post) > 0){
				$json = json_encode($post);
				$curl_array[CURLOPT_POST] = true;
				$curl_array[CURLOPT_POSTFIELDS] = $json;
			}
			
			curl_setopt_array($curl, $curl_array);
			
			$result = curl_exec($curl);	
			
			$s = curl_getinfo($curl);
			
			$out = @json_decode($result, true);
			
			do_action('save_merchant_error', 'garantex', 'method: '. $method .', json:' . print_r($post, true) . 'result:' . print_r($result, true));

			return $out;
		}		
	}
}