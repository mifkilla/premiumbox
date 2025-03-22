<?php
if(!class_exists('AP_BitexBookApi')){
class AP_BitexBookApi {
	
    private $api_url = "https://api.bitexbook.com";  //https://api-stage-01.bitexbook.com
    private $token = "";

    function __construct($token)
    {
        $this->token = trim($token);
    }	
	
	public function check_voucher($code, $pin){
		
		$params = array(
			'code' => trim($code),
			'pin' => trim($pin)	
		);
		
		$res = $this->request('bitexcode', array(), $params);
		if(isset($res['data'])){
			return $res['data'];
			
			/*
			[id] => 30 - Идентификатор кода
            [code] => NZJO1zJlRJ9rdLJy5 - Bitex-код
            [timestamp] => 1549959946.0000 - Время создания кода
            [wallet_id] => 15 - Id кошелька валюты, обеспечивающей данный код
            [sum] => 21.6 - Сумма, привязанная к данному коду
            [active] => 1 - 1 – код активен, 0 – код неактивен (уже использован)
            [pin] =>  - Pin-код (не передается, поле содержит пустую строку)
            [type] => 0 - Тип операции 0 – пополнение, 1 -снятие
            [currency] => rub - Валюта
			*/
		}
		
		return '';
	}

	public function make_voucher($sum, $wallet_id, $currency){
		$wallet_id = intval($wallet_id);
		$currency = strtolower($currency);
		
		$data = array();
		$data['error'] = 1;
		$data['trans_id'] = 0;
		
		$params = array(
			'sum' => $sum,
			'wallet_id' => $wallet_id,
			'wallet_currency' => $currency	
		);
		
		$res = $this->request('bitexcode', $params, array());
		if(isset($res['data'], $res['data']['code'], $res['data']['pin'])){
			
			$code = trim($res['data']['code']);
			$pin = trim($res['data']['pin']);
			$res2 = $this->check_voucher($code, $pin);
			if(isset($res2['wallet_id'], $res2['sum'], $res2['currency'])){
				$n_wallet_id = (string)$res2['wallet_id'];
				$n_sum = (string)$res2['sum'];
				$n_currency = (string)$res2['currency'];
				if($n_wallet_id == $wallet_id and $n_sum == $sum and $currency == $n_currency){
					
					$data['error'] = 0;
					$data['trans_id'] = 0;
					$data['code'] = $code;
					$data['num'] = $pin;

					/*
					[code] => EQArhr9W9yOEUZrvL - Bitex-код
					[pin] => h1hfbE - Pin-код для использования Bitex-кода
					*/
				}
			}
			
		}
		
		return $data;
	}

	public function get_balans(){
		
		$res = $this->request('user/wallets', array(), array());
		
		if(isset($res['data']) and is_array($res['data'])){
			$purses = array();
			
			foreach($res['data'] as $currency => $value){
				$currency = strtolower(trim($currency));
				$purses[$currency] = array(
					'amount' => trim(is_isset($value, 'balance')),
					'id' => trim(is_isset($value, 'id')),
				);
			}
			
			return $purses;
			
			/*
			[data] => Array(
				[bch] => Array(
					[id] => 71397
					[balance] => 0
					[system_currency] => bch
					[tickets_balance] => 0
					[sort] => 0
				)

				[bcn] => Array(
					[id] => 88398
					[balance] => 200.08
					[system_currency] => bcn
					[tickets_balance] => 0
					[sort] => 0
				)
			*/
		}
		
		return '';
	}

	public function send_money($currency, $sum, $wallet, $payment_id=''){
		$currency = strtoupper(trim($currency));
		$wallet = trim($wallet);
		$payment_id = trim($payment_id);
		
		$methods = array(
			'BTC' => 12,
			'LTC' => 11,
			'ETH' => 16,
			'USDT' => 151,
			'BCH' => 21,
			'DASH' => 25,
			'XMR' => 42,
			'DOGE' => 32,
			'BCN' => 23,
			'ZEC' => 34,
			'TRX' => 63
		);
		$method_id = intval(is_isset($methods, $currency));
		
		$data = array();
		$data['error'] = 1;
		$data['trans_id'] = 0;
		
		$params = array(
			'method_id' => $method_id,
			'sum' => $sum,
			'wallet' => $wallet,
			'payment_id' => $payment_id
		);		
		
		$res = $this->request('payments/withdrawal', $params, array());
		if(isset($res['data'], $res['data']['id'])){
			$data['error'] = 0;
			$data['trans_id'] = $res['data']['id'];
		}
		
		return $data;
	}

	public function buy($execution_type, $symbol, $price, $start_volume){
		$execution_type = intval($execution_type);
		if($execution_type != 1){ $execution_type = 2; }
		if($execution_type == 2){ $price = 0; }
		
		$params = array(
			'trade_method' => 1,
			'execution_type' => $execution_type,
			'symbol' => $symbol,
			'price' => $price,
			'start_volume' => $start_volume,
		);		
		
		$res = $this->request('user/tickets', array(), $params);
		
		if(isset($res['data'])){
			
			return $res['data'];
			
			/*
			[id] => 0 - Идентификатор созданного тикета
            [symbol] => btcrub - Символ валютной пары
            [price] => 59300 - Цена фактического исполнения
            [volume] => 0.003 - Фактически исполненный объем
            [start_volume] => 0.003 - Объем, запрошенный на исполнение
            [created_timestamp] => 1549957019.2339 - Время создания
            [modify_timestamp] => 1549957019.2339 - Время изменения (если есть)
            [process_timestamp] => 0 - Время, затраченное на исполнение
            [type] => 1 - Тип торговой операции: 1-покупка, 2-продажа
            [execution_type] => 2 - Тип исполнения: 1-по лимиту 2-по рынку
            [method] => 2
			*/
		}
		
		return '';
	}	
	
	public function get_transfer_info($id){
		
		$res = $this->request('payments?id='.urlencode($id), array(), array());
		if(isset($res['data'])){
			return $res['data'];
			
			/*
			[data] => Array
				(
					[0] => Array
						(
							[id] => 27BE876B-9F15-48FD-90F2-37111D528F4D - Id платежа
							[wallet_id] => 9105 - Id кошелька
							[money_method_id] => 91 - Id метода пополнения/снятия
							[user_id] => 3 - Id пользователя
							[sum] => 11900 - Сумма операции
							[total] => 11900 - Итого списано/зачисленно
							[type] => 1 - Тип операции: 1 – пополнение, 2 – снятие
							[status] => 2 - Статус заявки: 1 – заявка создана, 2 – зачислено (при пополнении) / проверяется (при снятии), 4 – выплачено, 5 – отклонено
							[created_timestamp] => 1543247174 - Время создания заявки
							[payed_timestamp] => 1543247174 - Время выплаты/зачисления
							[user_wallet] => Gdax6jQegXb2LgYSMQMx63kEB816CtPbjY - Кошелек, на который был осуществлен вывод
							[method_data] =>  - Дополнительные данные, при их наличии
							[comment] =>  - Комментарий к операции, при наличии
							[hash] => 3cb9173dc65f01d7f3572a34315b6d288c78cdc058c0fd89794bb258d82a5b4a - Хэш криптовалютной операци
						)

				)
			*/
		}
			return '';
	}
	
	public function request($api_name, $posts_data = array(), $puts_data = array()){ 
		
		$curl = curl_init();		
		
		$url = $this->api_url . '/api/v2/'. $api_name;
		
		$headers = array(
			"Content-Type: application/json",
			"X-Auth-Token: " . $this->token
		);
		
		$c_options = array(
			CURLOPT_HTTPHEADER => $headers,
			CURLINFO_HEADER_OUT => true,
		);		
		
		if(is_array($posts_data) and count($posts_data) > 0){
			$c_options[CURLOPT_POST] = true;
			$c_options[CURLOPT_POSTFIELDS] = json_encode($posts_data, JSON_NUMERIC_CHECK);
		}
		
		if(is_array($puts_data) and count($puts_data) > 0){
			$c_options[CURLOPT_CUSTOMREQUEST] = 'PUT';
			$c_options[CURLOPT_POSTFIELDS] = json_encode($puts_data, JSON_NUMERIC_CHECK);
		}		
		
		$result = get_curl_parser($url, $c_options, 'merchant', 'bitexbook');
		
		$err  = $result['err'];
		$out = $result['output'];
		
		do_action('save_paymerchant_error', 'bitexbook', 'post: ' . print_r($posts_data, true) . 'puts: ' . print_r($puts_data, true) . 'result: ' . print_r($result, true));	
		
		if(!$err){
			$http_code = $result['code'];
			
			$result = $out;
			
			if($http_code == 200 AND $result = json_decode($result, true) AND json_last_error() == JSON_ERROR_NONE AND isset($result['status'])){
				return $result;
			}
		} 	
		return '';
	}
}    
}