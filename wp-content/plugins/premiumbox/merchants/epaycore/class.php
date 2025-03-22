<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
https://wallet.epaycore.com/merchant/docs
*/

if(!class_exists('EpayCore')){
	class EpayCore
	{
		private $m_name = "";
		private $m_id = "";
		private $api_id = '';
		private $api_secret = '';

		function __construct($m_name, $m_id, $api_id, $api_secret)
		{
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->api_id = trim($api_id);
			$this->api_secret = trim($api_secret);
		}

		function payout($before_account, $after_account, $amount, $descr, $order_id){
			
			$order_id = trim($order_id);
			$before_account = trim($before_account);
			$after_account = trim($after_account);
			$descr = trim($descr);
			
			$pay_data = array();
			$pay_data['error'] = 1;
			$pay_data['trans_id'] = 0;		
			
			$data = array(
				'src_account' => $before_account,
				'dst_account' => $after_account,
				'amount' => $amount,
				'descr' => $descr,
				'payment_id' => $order_id,
			);
			$res = $this->request('/v1/api/transfer', $data);
			
			if(isset($res['batch'])){
				$pay_data['error'] = 0;
				$pay_data['trans_id'] = $res['batch'];
			}
			
			return $pay_data;		
		}

		function get_history_payout($limit=25){
			$limit = intval($limit);
			
			$data = array(
				'limit' => $limit,
				'order' => 'created_desc',
				'type' => 8,
			);
			$res = $this->request('/v1/api/history', $data);
			if(isset($res['history']) and is_array($res['history'])){
				$h = array();
				foreach($res['history'] as $his){
					$h[$his['batch']] = $his;
				}
				return $h;
			}
				return '';
		}

		function get_info($batch){
			$batch = trim($batch);
			
			$data = array(
				'batch' => $batch,
			);
			$res = $this->request('/v1/api/info', $data);
			if(isset($res[0])){
				return $res[0];
			}
				return '';
		}		

		function get_balance($account_number){
			$account_number = trim($account_number);
			$data = array(
				'account' => $account_number,
			);
			$res = $this->request('/v1/api/balance', $data);
			$balance = '-1';
			if(isset($res['account']['balance'])){
				$balance = is_sum($res['account']['balance']);
			}
			return $balance;
		}

		function request($path, $data){
			
			$data['api_id'] = $this->api_id;
			$data['api_secret'] = $this->api_secret;
			
			$json_data = json_encode($data);
			
			$url = 'https://wallet.epaycore.com' . $path;
			
			$headers = array(
				'Content-Type: application/json',
			);			
			
			if($ch = curl_init()){
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
				$ch = apply_filters('curl_merch', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				
				curl_close($ch);
				
				do_action('save_merchant_error', $this->m_name, 'url: '. $url .', headers: '. print_r($headers, true) .' post:' . print_r($data, true) . ', result: ' . print_r($result, true));
				
				$res = @json_decode($result, true);
		
				return $res;				 
			}			

			return '';
		}				
	}
}