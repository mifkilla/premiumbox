<?php  
if( !defined( 'ABSPATH')){ exit(); }

add_filter('new_parser_links', 'def_new_parser_links');
function def_new_parser_links($links){	

	$time = current_time('timestamp');
	$tomorrow = $time + (24*60*60);

	$links['cbr'] = array(
		'title' => 'CBR.RU',
		'url' => 'http://www.cbr.ru/scripts/XML_daily.asp?date_req='.date('d.m.Y', $tomorrow),
		'birg_key' => 'cbr',
	);
	$links['ecb'] = array(
		'title' => 'ECB.EU',
		'url' => 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml',
		'birg_key' => 'ecb',
	);
	$links['nbu'] = array(
		'title' => 'NBU',
		'url' => 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange',
		'birg_key' => 'nbu',
	);	
	$links['privat24'] = array(
		'title' => 'Privat24 Online',
		'url' => 'https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=11',
		'birg_key' => 'privat24',
	);	
	$links['privat'] = array(
		'title' => 'PRIVATBANK.UA',
		'url' => 'https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=5',
		'birg_key' => 'privat',
	);
	$links['bankgovua'] = array(
		'title' => 'Bank.gov.ua',
		'url' => 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange',
		'birg_key' => 'bankgovua',
	);
	$links['nationalkz'] = array(
		'title' => 'NATIONALBANK.KZ',
		'url' => 'http://www.nationalbank.kz/rss/rates_all.xml',
		'birg_key' => 'nationalkz',
	);	
	$links['nbrb'] = array(
		'title' => 'NBRB.BY',
		'url' => 'http://www.nbrb.by/Services/XmlExRates.aspx?ondate='.date('m/d/Y', $time),
		'birg_key' => 'nbrb',
	);	
	$links['instaforex'] = array(
		'title' => 'Instaforex.com',
		'url' => 'https://quotes.instaforex.com/api/quotesTick?m=json&q=eurrur,usdrur',
		'birg_key' => 'instaforex',
	);
	$links['bitfinex'] = array(
		'title' => 'Bitfinex.com',
		'url' => 'https://api.bitfinex.com/v1/tickers?symbols',
		'birg_key' => 'bitfinex',
	);	
	$links['binance'] = array(
		'title' => 'Binance.com (price)',
		'url' => 'https://api.binance.com/api/v3/ticker/price',
		'birg_key' => 'binance',
	);
	$links['binanceticker'] = array(
		'title' => 'Binance.com (bookTicker)',
		'url' => 'https://api.binance.com/api/v3/ticker/bookTicker',
		'birg_key' => 'binanceticker',
	);
	$links['blockchain'] = array(
		'title' => 'Blockchain.com',
		'url' => 'https://www.blockchain.com/ticker',
		'birg_key' => 'blockchain',
	);		
	$links['exmo'] = array(
		'title' => 'Exmo.me',
		'url' => 'http://api.exmo.me/v1/ticker/',
		'birg_key' => 'exmo',
	);
	$links['poloniex'] = array(
		'title' => 'Poloniex.com',
		'url' => 'https://poloniex.com/public?command=returnTicker',
		'birg_key' => 'poloniex',
	);
	$links['utorg'] = array(
		'title' => 'Utorg.io',
		'url' => 'https://public-api.utorg.io/api/v1/market/stats',
		'birg_key' => 'utorg',
	);	
	$links['btc_alpha'] = array(
		'title' => 'Btc-alpha.com',
		'url' => 'https://btc-alpha.com/api/v1/ticker/?format=json',
		'birg_key' => 'btc_alpha',
	);	
	$links['livecoin'] = array(
		'title' => 'Livecoin.net',
		'url' => 'https://api.livecoin.net/exchange/ticker',
		'birg_key' => 'livecoin',
	);
	$links['whitebit'] = array(
		'title' => 'Whitebit.com',
		'url' => 'https://whitebit.com/api/v4/public/ticker',
		'birg_key' => 'whitebit',
	);	
	$links['garantex'] = array(
		'title' => 'Garantex.io',
		'url' => 'https://garantex.io/rates',
		'birg_key' => 'garantex',
	);
	$links['bitexbook'] = array(
		'title' => 'Bitexbook.com',
		'url' => 'https://api.bitexbook.com/api/v2/symbols/statistic',
		'birg_key' => 'bitexbook',
	);
	$links['okcoin'] = array(
		'title' => 'Okcoin',
		'url' => 'https://www.okcoin.com/api/spot/v3/instruments/ticker',
		'birg_key' => 'okcoin',
	);
	$links['zbcn'] = array(
		'title' => 'Zb.cn',
		'url' => 'http://api.zb.cn/data/v1/allTicker',
		'birg_key' => 'zbcn',
	);		

	$arrs = array('btcusd','btceur','eurusd','xrpusd','xrpeur','xrpbtc','ltcusd','ltceur','ltcbtc','ethusd','etheur','ethbtc','bchusd','bcheur','bchbtc');
	foreach($arrs as $arr_v){
		$title = mb_strtoupper($arr_v);
		$links['bitstamp_'. $arr_v] = array(
			'title' => 'Bitstamp.net '. mb_substr($title, 0, 3). '-'. mb_substr($title, 3, 7),
			'url' => 'https://www.bitstamp.net/api/v2/ticker/'. $arr_v . '/',
			'birg_key' => 'bitstamp_'. $arr_v,
		);		
	}	
	
	$arrs = array(
		'1' => 'WMZ/WMR',
		'2' => 'WMR/WMZ',
		'3' => 'WMZ/WME',
		'4' => 'WME/WMZ',
		'5' => 'WME/WMR',
		'6' => 'WMR/WME',
		'7' => 'WMZ/WMU',
		'8' => 'WMU/WMZ',
		'9' => 'WMR/WMU',
		'10' => 'WMU/WMR',
		'11' => 'WMU/WME',
		'12' => 'WME/WMU',
		'17' => 'WMB/WMZ',
		'18' => 'WMZ/WMB',
		'19' => 'WMB/WME',
		'20' => 'WME/WMB',
		'23' => 'WMR/WMB',
		'24' => 'WMB/WMR',
		'25' => 'WMZ/WMG',
		'26' => 'WMG/WMZ',
		'27' => 'WME/WMG',
		'28' => 'WMG/WME',
		'29' => 'WMR/WMG',
		'30' => 'WMG/WMR',
		'31' => 'WMU/WMG',
		'32' => 'WMG/WMU',
		'33' => 'WMZ/WMX',
		'34' => 'WMX/WMZ',
		'35' => 'WME/WMX',
		'36' => 'WMX/WME',
		'37' => 'WMR/WMX',
		'38' => 'WMX/WMR',
		'39' => 'WMU/WMX',
		'40' => 'WMX/WMU',
		'41' => 'WMK/WMZ',
		'42' => 'WMZ/WMK',
		'43' => 'WMK/WME',
		'44' => 'WME/WMK',
		'45' => 'WMR/WMK',
		'46' => 'WMK/WMR',
		'47' => 'WMB/WMU',
		'48' => 'WMU/WMB',
		'49' => 'WMB/WMX',
		'50' => 'WMX/WMB',
		'51' => 'WMK/WMX',
		'52' => 'WMX/WMK',
		'53' => 'WMB/WMG',
		'54' => 'WMG/WMB',
		'55' => 'WMB/WMK',
		'56' => 'WMK/WMB',
		'57' => 'WMG/WMK',
		'58' => 'WMK/WMG',
		'59' => 'WMG/WMX',
		'60' => 'WMX/WMG',
		'61' => 'WMU/WMK',
		'62' => 'WMK/WMU',
		'63' => 'WMV/WMZ',
		'64' => 'WMZ/WMV',
		'65' => 'WMV/WME',
		'66' => 'WME/WMV',
	);
	foreach($arrs as $arr_k => $arr_v){
		$links['wmexchanger' . $arr_k] = array(
			'title' => 'Wm.exchanger.ru '. $arr_v,
			'url' => 'https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype='. $arr_k,
			'birg_key' => 'wmexchanger' . $arr_k,
		);		
	}
	
	return $links;
}

add_filter('set_parser_pairs', 'def_set_parser_pairs', 10, 4);
function def_set_parser_pairs($parser_pairs, $output, $birg_key, $up_time){
	
	$cs = 20;
	
	if($birg_key == 'binance'){
		$res = @json_decode($output);
		if(is_array($res)){	
			foreach($res as $out){
				$title_in = is_isset($out,'symbol');
				$rate = (string)is_isset($out, 'price');
				$rate = is_sum($rate, $cs);
				if($rate){
					$parser_pair_key = $birg_key . '_'. mb_strtolower($title_in);
					$parser_pairs[$parser_pair_key] = array(
						'course' => $rate,
						'birg' => $birg_key,
						'give' => mb_substr($title_in, 0, 3),
						'get' => mb_substr($title_in, 3, 7),
						'up' => $up_time,
					);					
				}
			}
		}		
	}
	
	if($birg_key == 'instaforex'){
		$res = @json_decode($output);
		if(is_array($res)){	
			foreach($res as $out){
				$title_in = is_isset($out,'symbol');
				$arrs = array('ask','bid');
				foreach($arrs as $arr_value){
					$rate = (string)is_isset($out, $arr_value);
					$rate = is_sum($rate, $cs);
					if($rate){
						$parser_pair_key = $birg_key . '_'. mb_strtolower($title_in).'_'. mb_strtolower($arr_value);
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $arr_value,
							'give' => mb_substr($title_in, 0, 3),
							'get' => mb_substr($title_in, 3, 7),
							'up' => $up_time,
						);	
					}
				}
			}
		}		
	}

	if($birg_key == 'whitebit'){
		$res = @json_decode($output, true);
		if(is_array($res)){	
			foreach($res as $title_in => $out){
				$title_arr = explode('_', $title_in);
				$rate = (string)is_isset($out, 'last_price');
				$rate = is_sum($rate, $cs);
				if($rate){
					$parser_pair_key = $birg_key . '_'. mb_strtolower(is_isset($title_arr,0)).'_'. mb_strtolower(is_isset($title_arr,1));
					$parser_pairs[$parser_pair_key] = array(
						'course' => $rate,
						'birg' => $birg_key,
						'give' => is_isset($title_arr,0),
						'get' => is_isset($title_arr,1),
						'up' => $up_time,
					);	
				}
			}
		}		
	}

	if($birg_key == 'garantex'){
		$res = @json_decode($output, true);
		if(is_array($res)){	
			foreach($res as $title_in => $out){
				$c_title_in = mb_strlen($title_in);
				$arrs = array('sell','buy');
				foreach($arrs as $arr_value){
					$rate = (string)is_isset($out, $arr_value);
					$rate = is_sum($rate, $cs);
					if($rate){
						$parser_pair_key = $birg_key . '_'. mb_strtolower($title_in).'_'. mb_strtolower($arr_value);
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $arr_value,
							'give' => mb_substr($title_in, 0, ($c_title_in - 3)),
							'get' => mb_substr($title_in, ($c_title_in - 3), $c_title_in),
							'up' => $up_time,
						);	
					}
				}
			}
		}		
	}		
	
	if($birg_key == 'binanceticker'){
		$res = @json_decode($output);
		if(is_array($res)){	
			foreach($res as $out){
				$title_in = is_isset($out,'symbol');
				$arrs = array('bidPrice','askPrice');
				foreach($arrs as $arr_value){
					$rate = (string)is_isset($out, $arr_value);
					$rate = is_sum($rate, $cs);
					if($rate){
						$parser_pair_key = $birg_key . '_'. mb_strtolower($title_in).'_'. mb_strtolower($arr_value);
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $arr_value,
							'give' => mb_substr($title_in, 0, 3),
							'get' => mb_substr($title_in, 3, 7),
							'up' => $up_time,
						);					
					}
				}
			}
		}		
	}
	
	if($birg_key == 'bitexbook'){
		$res = @json_decode($output, true);
		if(is_array($res) and isset($res['symbols'])){
			foreach($res['symbols'] as $re){
				$give = is_isset($re,'currency_base');
				$get = is_isset($re,'currency_quoted');
				$arrs = array('price_open','price_close','price_low','price_high','price_bid','price_ask');
				$stat = is_isset($re,'statistic');
				$out = is_isset($stat, 0);
				foreach($arrs as $arr_value){
					$rate = (string)is_isset($out, $arr_value);
					$rate = is_sum($rate, $cs);
					if($rate){
						$arr_value = str_replace('price_','',$arr_value);
						$parser_pair_key = $birg_key . '_'. mb_strtolower($give . $get) . '_'. $arr_value;
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $arr_value,
							'give' => mb_strtoupper($give),
							'get' => mb_strtoupper($get),
							'up' => $up_time,
						);
					}					
				}
			}
		}	
	}
	
	if($birg_key == 'utorg'){		
		$res = @json_decode($output, true);
		if(is_array($res)){	
			foreach($res as $out){
				$arrs = array('price','high','low','open','buy','sell');
				foreach($arrs as $arr_value){
					$title = (string)is_isset($out, 'title');
					$title_arr = explode('-', $title);
					if($arr_value == 'price' or $arr_value == 'buy' or $arr_value == 'sell'){
						$rate = (string)is_isset($out, $arr_value);
					} elseif(isset($out['ohlcv'])) {
						$rate = (string)is_isset($out['ohlcv'], $arr_value);
					}
					$rate = is_sum($rate, $cs);
					if($rate){
						$parser_pair_key = $birg_key . '_'. mb_strtolower(str_replace('-','',$title)) . '_'. $arr_value;
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $arr_value,
							'give' => mb_strtoupper(is_isset($title_arr,0)),
							'get' => mb_strtoupper(is_isset($title_arr,1)),
							'up_time' => $up_time,
						);
					}				
				}
			}
		}
	}	
	
	if($birg_key == 'okcoin'){		
		$res = @json_decode($output, true);
		if(is_array($res)){	
			foreach($res as $out){
				$arrs = array('last','ask','bid');
				foreach($arrs as $arr_value){
					$title = (string)is_isset($out, 'instrument_id');
					$title_arr = explode('-', $title);
					$rate = (string)is_isset($out, $arr_value);
					$rate = is_sum($rate, $cs);
					if($rate){
						$parser_pair_key = $birg_key . '_'. mb_strtolower(str_replace('-','',$title)) . '_'. $arr_value;
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $arr_value,
							'give' => mb_strtoupper(is_isset($title_arr,0)),
							'get' => mb_strtoupper(is_isset($title_arr,1)),
							'up' => $up_time,
						);
					}				
				}
			}
		}	
	}

	if($birg_key == 'zbcn'){		
		$res = @json_decode($output, true);
		if(is_array($res)){	
			foreach($res as $k => $out){
				$title = (string)$k;
				$give = mb_strtoupper(mb_substr($title, 0, 3));
				$get = mb_strtoupper(mb_substr($title, 3, 8));
				$arrs = array('last','sell','buy','high','low');
				foreach($arrs as $arr_value){
					$rate = (string)is_isset($out, $arr_value);
					$rate = is_sum($rate, $cs);
					if($rate){
						$parser_pair_key = $birg_key . '_'. mb_strtolower($title) . '_'. $arr_value;
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $arr_value,
							'give' => $give,
							'get' => $get,
							'up' => $up_time,
						);
					}	
				}
			}
		}	
	}	
	
	if($birg_key == 'blockchain'){
		$res = @json_decode($output);
		if(is_object($res)){	
			foreach($res as $title => $out){
				$arrs = array('15m','last','buy','sell');
				foreach($arrs as $arr_value){
					$rate = (string)is_isset($out, $arr_value);
					$rate = is_sum($rate, $cs);
					if($rate){
						$parser_pair_key = $birg_key . '_btc'. mb_strtolower($title) . '_'. $arr_value;
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $arr_value,
							'give' => 'BTC',
							'get' => mb_strtoupper($title),
							'up' => $up_time,
						);
					}
				}
			}
		}			
	}
	
	if($birg_key == 'bitfinex'){		
		$res = @json_decode($output);
		if(is_array($res)){	
			foreach($res as $out){
				$title_in = is_isset($out,'pair');
				$narr = array('mid', 'bid', 'ask', 'last_price', 'low','high');
				foreach($narr as $res_key){
					$rate = (string)is_isset($out, $res_key);
					$rate = is_sum($rate, $cs);
					if($rate){
						$parser_pair_key = $birg_key . '_'. mb_strtolower($title_in) .'_'.$res_key;
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $res_key,
							'give' => mb_substr($title_in, 0, 3),
							'get' => mb_substr($title_in, 3, 7),
							'up' => $up_time,
						);					
					}
				}
			}
		}				
	}
	
	if($birg_key == 'livecoin'){		
		$res = @json_decode($output);
		if(is_array($res)){	
			foreach($res as $out){
				$title_in = is_isset($out,'symbol');
				$title_arr = explode('/', $title_in);
				$narr = array('last', 'high', 'low', 'volume', 'vwap','max_bid','min_ask','best_bid','best_ask');
				foreach($narr as $res_key){
					$rate = (string)is_isset($out, $res_key);
					$rate = is_sum($rate, $cs);
					if($rate){
						$parser_pair_key = $birg_key . '_'. mb_strtolower(str_replace('/', '', $title_in)) .'_'.$res_key;
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $res_key,
							'give' => is_isset($title_arr,0),
							'get' => is_isset($title_arr,1),
							'up' => $up_time,
						);						
					}
				}
			}
		}				
	}
	
	if($birg_key == 'poloniex'){		
		$res = @json_decode($output);
		if(is_object($res)){	
			foreach($res as $title_in => $v){
				$title_arr = explode('_', $title_in);
				$narr = array('last', 'lowestAsk', 'highestBid', 'high24hr', 'low24hr');
				foreach($narr as $res_key){
					$rate = (string)is_isset($v, $res_key);
					$rate = is_sum($rate, $cs);
					if($rate){	
						$parser_pair_key = $birg_key . '_'. mb_strtolower(str_replace('_', '', $title_in)) .'_'.$res_key;
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $res_key,
							'give' => mb_strtoupper(is_isset($title_arr, 0)),
							'get' => mb_strtoupper(is_isset($title_arr, 1)),
							'up' => $up_time,
						);					
					}
				}
			}
		}				
	}
	
	if($birg_key == 'btc_alpha'){		
		$res = @json_decode($output);
		if(is_array($res)){	
			foreach($res as $v){
				$title_in = $v->pair;
				$title_arr = explode('_', $title_in);
				
				$narr = array('last', 'diff', 'vol', 'high', 'low', 'buy', 'sell');
				foreach($narr as $res_key){
					$rate = (string)is_isset($v, $res_key);
					$rate = is_sum($rate, $cs);
					if($rate){	
						$parser_pair_key = $birg_key . '_'. mb_strtolower(str_replace('_', '', $title_in)) .'_'.$res_key;
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $res_key,
							'give' => is_isset($title_arr, 0),
							'get' => is_isset($title_arr, 1),
							'up' => $up_time,
						);					
					}
				}
			}
		}				
	}	
	
	if($birg_key == 'exmo'){		
		$res = @json_decode($output);
		if(is_object($res)){	
			foreach($res as $title_in => $v){
				$title_arr = explode('_', $title_in);
				$narr = array('buy_price', 'sell_price', 'last_trade', 'high', 'low', 'avg');
				foreach($narr as $res_key){
					$rate = (string)is_isset($v, $res_key);
					$rate = is_sum($rate, $cs);
					if($rate){
						$parser_pair_key = $birg_key . '_'. mb_strtolower(str_replace('_', '', $title_in)) .'_'.$res_key;
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'title' => $res_key,
							'give' => is_isset($title_arr, 0),
							'get' => is_isset($title_arr, 1),
							'up' => $up_time,
						);						
					}
				}
			}
		}				
	}	

	if($birg_key == 'bankgovua'){
		if(strstr($output,'<?xml')){	
			$res = @simplexml_load_string($output);
			if(is_object($res) and isset($res->currency)){
				foreach($res->currency as $data){
					$CharCode = (string)$data->cc;
					$CharCode = trim($CharCode); /* type */
					
					$course = (string)$data->rate;
					$course = is_sum($course, $cs); 
					
					if($course > 0){
						$parser_pair_key = $birg_key. '_'. strtolower($CharCode) .'uah';
						$parser_pairs[$parser_pair_key] = array(
							'course' => $course,
							'birg' => $birg_key,
							'give' => $CharCode,
							'get' => 'UAH',
							'up' => $up_time,
						);						
					}
				}
			}
		}				
	}	
	
	if(strstr($birg_key,'bitstamp_')){
		$res = @json_decode($output);
		if(is_object($res)){
			$title = mb_strtoupper(str_replace('bitstamp_','', $birg_key));
			$narr = array('high', 'last', 'bid', 'vwap', 'low', 'ask', 'open');
			foreach($narr as $res_key){
				$rate = (string)is_isset($res, $res_key);
				$rate = is_sum($rate, $cs);
				if($rate){
					$parser_pair_key = $birg_key . '_'.$res_key;
					$parser_pairs[$parser_pair_key] = array(
						'course' => $rate,
						'birg' => $birg_key,
						'title' => $res_key,
						'give' => mb_substr($title, 0, 3),
						'get' => mb_substr($title, 3, 7),
						'up' => $up_time,
					);					
				}
			}
		}	
	}	
	
	if(strstr($birg_key,'wmexchanger')){
		if(strstr($output,'<?xml')){
			$res = @simplexml_load_string($output);
			if(is_object($res) and isset($res->WMExchnagerQuerys)){	
				if(isset($res->WMExchnagerQuerys['amountin'], $res->WMExchnagerQuerys['amountout'])){
					$curr1 = (string)$res->WMExchnagerQuerys['amountin'];
					$curr2 = (string)$res->WMExchnagerQuerys['amountout'];
					
					$rate1 = (string)$res->WMExchnagerQuerys->query['inoutrate'][0]; 
					$rate1 = is_sum($rate1, $cs);
					
					$rate2 = (string)$res->WMExchnagerQuerys->query['outinrate'][0];
					$rate2 = is_sum($rate2, $cs);
					
					$parser_pair_key = $birg_key. '_'. strtolower($curr2) . strtolower($curr1). '_outrate';
					$parser_pairs[$parser_pair_key] = array(
						'course' => $rate1,
						'birg' => $birg_key,
						'title' => 'outrate',
						'give' => $curr2,
						'get' => $curr1,
						'up' => $up_time,
					);					
					$parser_pair_key = $birg_key. '_'. strtolower($curr2) . strtolower($curr1). '_inrate';
					$parser_pairs[$parser_pair_key] = array(
						'course' => $rate2,
						'birg' => $birg_key,
						'title' => 'inrate',
						'give' => $curr1,
						'get' => $curr2,
						'up' => $up_time,
					);					
				}	
			}
		}		
	}
	
	if($birg_key == 'nbrb'){
		if(strstr($output,'<?xml')){		
			$res = @simplexml_load_string($output);
			if(is_object($res) and isset($res->Currency)){
				foreach($res->Currency as $data){
					
					$CharCode = (string)$data->CharCode;
					$CharCode = trim($CharCode); /* type */
					
					$nominal = (string)$data->Scale; /* 1 USD */
					$nominal = is_sum($nominal, $cs); 
					
					$value = (string)$data->Rate; /* ? KZT */
					$value = is_sum($value, $cs);
					
					if($nominal > 0 and $value > 0){
						$course = is_sum($value / $nominal, $cs);
						
						$parser_pair_key = $birg_key. '_'. strtolower($CharCode) .'byn';
						$parser_pairs[$parser_pair_key] = array(
							'course' => $course,
							'birg' => $birg_key,
							'give' => $CharCode,
							'get' => 'BYN',
							'up' => $up_time,
						);						
					}
				}
			}
		}				
	}		
	
	if($birg_key == 'nationalkz'){
		if(strstr($output,'<?xml')){		
			$res = @simplexml_load_string($output);
			if(is_object($res) and isset($res->channel)){
				foreach($res->channel->item as $data){
					
					$CharCode = $data->title;
					$CharCode = trim($CharCode); /* type */
					
					$nominal = (string)$data->quant; /* 1 USD */
					$nominal = is_sum($nominal, $cs); 
					
					$value = (string)$data->description; /* ? KZT */
					$value = is_sum($value, $cs);
					
					if($nominal > 0 and $value > 0){
						$course = is_sum($value / $nominal, $cs);
						$parser_pair_key = $birg_key. '_'. strtolower($CharCode) .'kzt';
						$parser_pairs[$parser_pair_key] = array(
							'course' => $course,
							'birg' => $birg_key,
							'give' => $CharCode,
							'get' => 'KZT',
							'up' => $up_time,
						);						
					}
				}
			}
		}				
	}	
	
	if($birg_key == 'cbr'){
		if(strstr($output,'<?xml')){
			$res = @simplexml_load_string($output);
			if(is_object($res)){
				if(isset($res->Valute)){
					$currencies = $res->Valute;
					foreach($currencies as $c_obj){
						$CharCode = (string)$c_obj->CharCode;
						$CharCode = trim($CharCode); /* type */
						
						$nominal = (string)$c_obj->Nominal;
						$nominal = is_sum($nominal, $cs);
						
						$value = (string)$c_obj->Value;
						$value = is_sum($value, $cs);
						
						if($nominal > 0 and $value > 0){
							$course = is_sum($value / $nominal, $cs);
							$parser_pair_key = $birg_key. '_'. strtolower($CharCode) .'rub';
							$parser_pairs[$parser_pair_key] = array(
								'course' => $course,
								'birg' => $birg_key,
								'give' => $CharCode,
								'get' => 'RUB',
								'up' => $up_time,
							);						
						}
					}	
				}	
			}	
		}
	}
	
	if($birg_key == 'ecb'){
		if(strstr($output,'<?xml')){
			$res = @simplexml_load_string($output);
			if(is_object($res) and isset($res->Cube, $res->Cube->Cube)){
				foreach($res->Cube->Cube->Cube as $cube){
					$currency = (string)$cube['currency'];
					$currency = trim($currency);
					
					$rate = (string)$cube['rate'];
					$rate = is_sum($rate, $cs);
					
					if($rate > 0){
						$parser_pair_key = $birg_key. '_eur'. strtolower($currency);
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'give' => 'EUR',
							'get' => $currency,
							'up' => $up_time,						
						);
					}				
				}
			}
		}	
	}	
	
	if($birg_key == 'nbu'){
		if(strstr($output,'<?xml')){
			$res = @simplexml_load_string($output);
			if(is_object($res) and isset($res->currency)){
				foreach($res->currency as $val){
					$currency = (string)$val->cc;
					$currency = trim($currency);
					
					$rate = (string)$val->rate;
					$rate = is_sum($rate, $cs);
					
					if($rate > 0){
						$parser_pair_key = $birg_key . '_' . strtolower($currency).'uah';
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'give' => $currency,
							'get' => 'UAH',
							'up' => $up_time,						
						);					
					}				
				}
			}
		}	
	}	
	
	if($birg_key == 'privat' or $birg_key == 'privat24'){
		if(strstr($output,'<?xml')){
			$res = @simplexml_load_string($output);
			if(is_object($res) and isset($res->row)){
				foreach($res->row as $val){
					$v_data = (array)$val->exchangerate;
					$currency = (string)$v_data['@attributes']['ccy'];
					$currency = trim($currency);
					
					$rate1 = (string)$v_data['@attributes']['buy'];
					$rate1 = is_sum($rate1, $cs);
					
					if($rate1 > 0){
						$parser_pair_key = $birg_key . '_' . strtolower($currency) . '_uah_buy';
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate1,
							'birg' => $birg_key,
							'title' => 'buy',
							'give' => $currency,
							'get' => 'UAH',
							'up' => $up_time,						
						);					
					}

					$rate2 = (string)$v_data['@attributes']['sale'];
					$rate2 = is_sum($rate2, $cs);
					
					if($rate2 > 0){
						$parser_pair_key = $birg_key . '_' . strtolower($currency) . '_uah_sale';
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate2,
							'birg' => $birg_key,
							'title' => 'sale',
							'give' => $currency,
							'get' => 'UAH',
							'up' => $up_time,						
						);					
					}				
				}
			}
		}	
	}

	if(strstr($birg_key,'xmlc_')){
		$now_birg_key = str_replace('xmlc_', '', $birg_key); 
		if(strstr($output,'<?xml')){
			$res = @simplexml_load_string($output);
			if(is_object($res)){
				foreach($res->item as $res_key){
					$from = (string)is_isset($res_key,'from');
					$to = (string)is_isset($res_key,'to');
					$in = (string)is_isset($res_key,'in');
					$in = is_sum($in, $cs);
					$out = (string)is_isset($res_key,'out');
					$out = is_sum($out, $cs);					
					$rate = 0;
					if($in > 0 and $out > 0){
						$rate = $out / $in;
					}
					$rate = is_sum($rate, $cs); 
					if($rate > 0){
						$parser_pair_key = $birg_key . '_'. mb_strtolower($from . $to);
						$parser_pairs[$parser_pair_key] = array(
							'course' => $rate,
							'birg' => $birg_key,
							'give' => mb_strtoupper($from),
							'get' => mb_strtoupper($to),
							'up' => $up_time,
						);					
					}
				}
			}
		}	
	}	
	
	return $parser_pairs;
}

add_filter('work_parser_links', 'def_work_parser_links');
function def_work_parser_links($links){	
	$birgs = apply_filters('new_parser_links', array());
	
	$work_birgs = get_option('work_birgs');
	if(!is_array($work_birgs)){ $work_birgs = array(); }
	
	foreach($birgs as $birg_key => $birg_data){
		if(in_array($birg_key, $work_birgs)){
			$links[$birg_key] = $birg_data;
		}
	}	

	return $links;
}