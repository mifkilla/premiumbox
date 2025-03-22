<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('PrivatBank')){
class PrivatBank{
	private $merchant_id = "";
	private $password = "";
	private $m_id = "";
	private $test = 0;

    function __construct($m_id, $merchant_id, $password)
    {
        $this->merchant_id = trim($merchant_id);
		$this->password = trim($password);
		$this->m_id = trim($m_id);
    }	
	
	public function get_order($order_id){
		
		$data = '<oper>cmt</oper><wait>0</wait><test>'. $this->test .'</test><payment><prop name="order" value="'. $order_id .'" /></payment>';
		
		$request = $this->request('ishop_pstatus', $data);
		$res = @simplexml_load_string($request);
		$data = array();
		if(is_object($res) and isset($res->data) and isset($res->data->payment)){
			foreach($res->data->payment->attributes() as $key => $val){
				$data[$key] = (string)$val;
			}
		}		
		
		return $data;		
		
	}	

	public function request($action, $data){

		$pass = $this->password;
		$sign=sha1(md5($data.$pass));
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?><request version="1.0"><merchant><id>'. $this->merchant_id .'</id><signature>'. $sign .'</signature></merchant><data>'. $data .'</data></request>';	
		
		$c_options = array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $xml,
			CURLOPT_HTTPHEADER => array( 'Content-Type: text/xml' )
		);
		$result = get_curl_parser('https://api.privatbank.ua/p24api/'.$action, $c_options, 'merchant', 'privat', $this->m_id);
		
		do_action('save_merchant_error', 'privat', 'xml:' . print_r($xml, true) . 'result:' . print_r($result, true));
		
		$err  = $result['err'];
		$out = $result['output'];
		if(!$err){	
			return $out; 
		} 
	}
	
}
}