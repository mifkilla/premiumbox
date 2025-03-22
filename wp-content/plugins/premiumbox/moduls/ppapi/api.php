<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('pn_plugin_api', 'ppapi_pn_plugin_api');
function ppapi_pn_plugin_api(){
global $wpdb, $premiumbox;	
	$api_action = trim(pn_string(is_param_get('api_action')));
	$method = pn_strip_input(is_param_get('method'));
	$methods = array('get_info','get_links','get_payouts','get_exchanges','add_payout');
	$api_key = pn_strip_input(is_param_get('api_key'));
	if($api_action == 'pp' and strlen($api_key) > 0 and in_array($method,$methods) and function_exists('create_user_payout')){

		header('Content-Type: application/json; charset=utf-8');
		status_header(200);
		
		$workapikey = intval($premiumbox->get_option('partners','workppapikey'));
		if($workapikey == 1 or $premiumbox->is_up_mode()){
			$json = array(
				'error' => 1,
				'error_text' => 'Api disabled',
			);	
			echo json_encode($json);
			exit;
		}
		
		$where = '';
		if($workapikey == 2){
			$where = " AND workppapikey = '1'";
		}
		
		$ui = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."users WHERE ppapikey = '$api_key' AND user_bann = '0' $where");
		if(isset($ui->ID)){
			$user_id = $ui->ID;
			
			$json = array(
				'error' => 0,
				'error_text' => '',
			);	
			
			if($method == 'get_info'){
				$balance = get_partner_money($user_id, array('0','1'));
				$min_payout = is_sum($premiumbox->get_option('partners','minpay'),2);
				
				$json['data']['balance'] = $balance;
				$json['data']['min_payout'] = $min_payout;
				
				$cur_type = cur_type();
				
				$currencies = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."currency WHERE auto_status='1' AND currency_status='1' AND p_payout='1' ORDER BY reserv_order ASC");	
				foreach($currencies as $item){ 
					$reserv = $item->currency_reserv; 
					$payout_com = $item->payout_com;
					$paysum = is_sum(convert_sum($balance, $cur_type, $item->currency_code_title));
					if($reserv >= $paysum){	
						$json['data']['items'][$item->id] = array(
							'id' => $item->id,
							'title' => get_currency_title($item),
							'comission' => $payout_com . '%',
							'amount' => sum_after_comis($paysum, $payout_com),
						);			
					}
				}	
			}
			
			$start_time = intval(is_param_post('start_time'));
			$end_time = intval(is_param_post('end_time'));
			$search_ip = pn_sfilter(pn_strip_input(is_param_post('ip')));

			if($method == 'get_links'){ 
				$where_time = '';
				if($start_time > 0){
					$start_date = date('Y-m-d H:i:s', $start_time);
					$where_time .= " AND pdate >= '$start_date'";
				}
				if($end_time > 0){
					$end_date = date('Y-m-d H:i:s', $end_time);
					$where_time .= " AND pdate <= '$end_date'";
				}
				if($search_ip){
					$where_time .= " AND ip = '$search_ip'";
				}
				
				$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."plinks WHERE user_id = '$user_id' $where_time ORDER BY pdate DESC");
				foreach($items as $item){ 
					$json['data']['items'][$item->id] = array(
						'id' => $item->id,
						'time' => strtotime($item->pdate), 
						'date' => $item->pdate, 
						'browser' => pn_strip_input($item->pbrowser),
						'ip' => pn_strip_input($item->pip),
						'referrer' => pn_strip_input($item->prefer),
						'user_hash' => pn_strip_input($item->user_hash),
						'query_string' => pn_strip_input($item->query_string),
					);
				}
			}

			if($method == 'get_exchanges'){ 
				$where_time = '';
				if($start_time > 0){
					$start_date = date('Y-m-d H:i:s', $start_time);
					$where_time .= " AND create_date >= '$start_date'";
				}
				if($end_time > 0){
					$end_date = date('Y-m-d H:i:s', $end_time);
					$where_time .= " AND create_date <= '$end_date'";
				}
				if($search_ip){
					$where_time .= " AND user_ip = '$search_ip'";
				}				
				
				$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE ref_id = '$user_id' AND status != 'auto' $where_time ORDER BY create_date DESC");
				foreach($items as $item){ 
					$exchange_success = 0;
					if($item->status == 'success'){
						$exchange_success = 1;
					}
					$json['data']['items'][$item->id] = array(
						'id' => $item->id,
						'time' => strtotime($item->create_date), 
						'date' => $item->create_date, 
						'currency_code_give' => is_site_value($item->currency_code_give),
						'currency_code_get' => is_site_value($item->currency_code_get),
						'course_give' => is_sum($item->course_give),
						'course_get' => is_sum($item->course_get),
						'amount_give' => is_sum($item->sum1dc),
						'amount_get' => is_sum($item->sum2c),
						'exchange_success' => $exchange_success,
						'accrued' => intval($item->pcalc),
						'partner_reward' => is_sum($item->partner_sum),
						'user_hash' => pn_strip_input($item->user_hash),
						'user_ip' => pn_strip_input($item->user_ip),
					);
				}				
			}			
			
			if($method == 'get_payouts'){ 
				$where_time = '';
				if($start_time > 0){
					$start_date = date('Y-m-d H:i:s', $start_time);
					$where_time .= " AND pay_date >= '$start_date'";
				}
				if($end_time > 0){
					$end_date = date('Y-m-d H:i:s', $end_time);
					$where_time .= " AND pay_date <= '$end_date'";
				}				
				
				$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."user_payouts WHERE auto_status = '1' AND user_id = '$user_id' $where_time ORDER BY pay_date DESC");
				foreach($items as $item){ 
					$json['data']['items'][$item->id] = array(
						'id' => $item->id,
						'time' => strtotime($item->pay_date), 
						'date' => $item->pay_date, 
						'method_id' => intval($item->currency_id),
						'account' => pn_strip_input($item->pay_account),
						'pay_amount' => pn_strip_input($item->pay_sum),
						'pay_currency_code' => is_site_value($item->currency_code_title),
						'original_amount' => pn_strip_input($item->pay_sum_or),
						'original_currency_code' => cur_type(),
						'status' => pn_strip_input($item->status),
					);
				}				
			}			
			
			if($method == 'add_payout'){ 
				$currency_id = intval(is_param_post('method_id')); if($currency_id < 1){ $currency_id = 0; }	
				$account = pn_strip_input(is_param_post('account'));

				$log = create_user_payout($currency_id, $account, $ui);
				if($log['status'] == 'success'){
					$json['error'] = 0; 
					$json['error_text'] = $log['status_text'];
					$item = is_isset($log, 'item');
					$json['data']['payout_id'] = is_isset($item, 'id');
				} else {
					$json['error'] = 1; 
					$json['error_text'] = $log['status_text'];
				}										
			}	
		
		} else {
			$json = array(
				'error' => 1,
				'error_text' => __('REST API key error', 'pn'),
			);			
		}
		
		echo json_encode($json);
		exit;
	}
}