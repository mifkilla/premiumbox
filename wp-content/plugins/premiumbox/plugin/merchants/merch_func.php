<?php
if( !defined( 'ABSPATH')){ exit(); }

function list_merchants(){
	return list_extandeds('merchants');
}

function get_mscript($m_id){
	$now_script = '';
	$list = get_option('extlist_merchants');
	if(!is_array($list)){ $list = array(); }
	
	$data = is_isset($list, $m_id);
	$script = trim(is_isset($data, 'script'));

	if(strlen($script) > 0){
		$now_script = $script;
	}		
	return $now_script;
}

function get_merch_data($m_id){
global $pn_merch_data;
	if(!is_array($pn_merch_data)){
		$pn_merch_data = (array)get_option('merchants_data');
	}
	return is_isset($pn_merch_data, $m_id);
}

add_action('direction_instruction','merchant_direction_instruction',1,5);
function merchant_direction_instruction($instruction, $status, $direction, $vd1, $vd2){
global $premiumbox, $bids_data;
	
	if(isset($bids_data->m_in)){
		$m_id = $bids_data->m_in;
		$st = array('status_new', 'status_techpay');
		if($m_id and in_array($status, $st) and !in_array($m_id, array('napsidenty','napssms','napsemail'))){	
			$data = get_merch_data($m_id); 
			$text = trim(ctv_ml(is_isset($data,'text')));
			if($text){
				return $text;
			} else {
				$show = intval($premiumbox->get_option('exchange','m_ins'));
				if($show == 0){
					return $text;
				} else {
					return $instruction;
				}
			}
		}
	}
	
	return $instruction;
}

add_action('merchant_cancel_button','def_merchant_cancel_button');
function def_merchant_cancel_button($button){
global $premiumbox, $bids_data;
	
	$m_id = $bids_data->m_in;	
	$data = get_merch_data($m_id); 
	$discancel = intval(is_isset($data, 'discancel'));
	if($discancel){
		return '';
	}
	
	return $button;
}

add_action('before_bidaction_canceledbids', 'merchant_before_bidaction_canceledbids');
function merchant_before_bidaction_canceledbids($bids_data){
	$m_id = $bids_data->m_in;
	$data = get_merch_data($m_id); 
	$discancel = intval(is_isset($data, 'discancel'));
	if($discancel){
		$hashed = is_bid_hash($bids_data->hashed);
		$url = get_bids_url($hashed);
		wp_redirect($url);
		exit;		
	}	
}

function get_merch_text($m_id, $item, $pay_sum=0, $tb){
	$text = '';
	if($m_id and isset($item->id)){
		$data = get_merch_data($m_id);
		$text = trim(ctv_ml(is_isset($data, $tb)));
		
		$fio_arr = array();
		if($item->last_name){
			$fio_arr[] = $item->last_name;
		}
		if($item->first_name){
			$fio_arr[] = $item->first_name;
		}
		if($item->second_name){
			$fio_arr[] = $item->second_name;
		}		
		$fio = pn_strip_input(join(' ',$fio_arr));
		
		$text = apply_filters('get_text_pay', $text, $m_id, $item);
		$text = str_replace(array('[id]','[exchange_id]'),$item->id, $text);
		$text = str_replace('[create_date]', $item->create_date, $text);
		$text = str_replace('[edit_date]', $item->edit_date, $text);
		$text = str_replace('[course_give]', pn_strip_input($item->course_give), $text);
		$text = str_replace('[course_get]', pn_strip_input($item->course_get), $text);
		$text = str_replace('[psys_give]', pn_strip_input(ctv_ml($item->psys_give)), $text);
		$text = str_replace('[psys_get]', pn_strip_input(ctv_ml($item->psys_get)), $text);
		$text = str_replace('[currency_code_give]', pn_strip_input($item->currency_code_give), $text);
		$text = str_replace('[currency_code_get]', pn_strip_input($item->currency_code_get), $text);	
		$text = str_replace('[first_name]', pn_strip_input($item->first_name), $text);
		$text = str_replace('[last_name]', pn_strip_input($item->last_name), $text);
		$text = str_replace('[second_name]', pn_strip_input($item->second_name), $text);
		$text = str_replace(array('[user_phone]','[phone]'), pn_strip_input($item->user_phone), $text);
		$text = str_replace(array('[user_skype]','[skype]'), pn_strip_input($item->user_skype), $text);
		$text = str_replace(array('[user_email]','[email]'), pn_strip_input($item->user_email), $text);
		$text = str_replace('[user_telegram]', pn_strip_input($item->user_telegram), $text);
		$text = str_replace(array('[user_passport]','[passport]'), pn_strip_input($item->user_passport), $text);
		$text = str_replace('[to_account]', pn_strip_input($item->to_account), $text);
		$text = str_replace('[bidurl]', get_bids_url($item->hashed), $text);		
		$text = str_replace('[paysum]', $pay_sum, $text);
		$text = str_replace(array('[sum1]','[sum_dc]'), is_sum($item->sum1dc), $text); 
		$text = str_replace(array('[valut1]','[currency_give]'), pn_strip_input(ctv_ml($item->psys_give)) .' '. pn_strip_input($item->currency_code_give),$text);	
		$text = str_replace('[sum2]', is_sum($item->sum2c),$text);
		$text = str_replace(array('[valut2]','[currency_get]'), pn_strip_input(ctv_ml($item->psys_get)) .' '. pn_strip_input($item->currency_code_get),$text);
		$text = str_replace('[account_give]', pn_strip_input($item->account_give),$text);
		$text = str_replace('[account_get]', pn_strip_input($item->account_get),$text);
		$text = str_replace('[fio]',$fio,$text);		
		$text = str_replace('[ip]', pn_strip_input($item->user_ip),$text);	
		$text = str_replace('[bid_trans_in]', pn_strip_input($item->trans_in), $text);
		$text = str_replace('[bid_trans_out]', pn_strip_input($item->trans_out), $text);
		if(strstr($text,'[bid_delete_time]')){
			$bid_delete_time = apply_filters('bid_delete_time', '', $item);
			$text = str_replace('[bid_delete_time]', $bid_delete_time, $text);
		}
	}
	if($tb == 'note'){
		return esc_attr(trim($text));
	} else {
		return pn_strip_text(trim($text));
	}		
}

function get_text_pay($m_id, $item, $pay_sum=0){
	return get_merch_text($m_id, $item, $pay_sum, 'note');
}

function get_pagenote($m_id, $item, $pay_sum=0){
	return get_merch_text($m_id, $item, $pay_sum, 'pagenote');
} 

function key_for_url($word, $replace=''){
	$req_arr = explode($word, is_isset($_SERVER, 'REQUEST_URI'));
	$arrs = explode('-', $req_arr[0]);
	$w = trim(is_isset($arrs, 1));
	$replace = trim($replace);
	if($replace){
		$w = str_replace($replace, '', $w);
	}
	return $w;
}

add_action('merchant_logs','def_merchant_secure',1, 5);
function def_merchant_secure($m_name, $req, $m_id, $m_defin, $m_data){
global $premiumbox;
	if($m_id){	
		$req_arr = explode('?', is_isset($_SERVER, 'REQUEST_URI'));
		$req_arrs = explode('_', $req_arr[0]);
		$now = end($req_arrs);
		$now = str_replace(array('.html','.php'), '', $now);
		
		$resulturl = trim(is_isset($m_data, 'resulturl'));
		if($resulturl and $resulturl != $now){
			do_action('save_merchant_error', $m_name, 'Error URL hash');
			die('Error URL hash');
			exit;
		}
	
		$yes_ip = trim(is_isset($m_data, 'enableip'));
		$user_ip = pn_real_ip();
		if($yes_ip and !pn_has_ip($yes_ip)){ 
			do_action('save_merchant_error', $m_name, sprintf(__('IP adress (%s) is blocked','pn'), $user_ip));
			die(sprintf(__('IP adress (%s) is blocked','pn'), $user_ip));
			exit;
		}
	}
} 

function get_corr_sum($m_id){
	$data = get_merch_data($m_id); 
	return is_isset($data,'corr');
}

add_filter('merchant_bid_sum', 'def_merchant_bid_sum', 10, 2);
function def_merchant_bid_sum($sum, $m_id){
	$corr = get_corr_sum($m_id);
	if(strstr($corr, '%')){
		$corr = str_replace('%','',$corr);
		$corr = is_sum($corr);
		$one_pers = 0;
		if($sum > 0){
			$one_pers = $sum / 100;
		}
		$new_sum = $sum - ($corr * $one_pers);
	} else {
		$corr = is_sum($corr);
		$new_sum = $sum - $corr;
	}
	return $new_sum;
}

function is_type_merchant($m_id){
	$data = get_merch_data($m_id); 
	return intval(is_isset($data,'type'));
} 

function is_pay_purse($payer, $m_data, $m_id){
	return apply_filters('pay_purse_merchant', $payer, $m_data, $m_id);
}

add_filter('pay_purse_merchant', 'def_pay_purse_merchant', 10);
function def_pay_purse_merchant($purse){
	$purse = str_replace('+', '', $purse);
	$purse = preg_replace("/\s/", '', $purse);	
	return $purse;
}

function get_payment_id($arg){
	$id = intval(is_param_post($arg));
	if(!$id){ $id = intval(is_param_get($arg)); }
	return $id;
}

function redirect_merchant_action($id, $script='', $place=''){
global $wpdb;
	$id = intval($id);	
	$script = trim($script);
	$place = intval($place);
	
	$text = __('You have successfully paid','pn');
	$res = 'true';
	if($place == 0){
		$text = __('You refused a payment','pn');
		$res = 'error';
	}

	if($id > 0){
		$scr = array();
		$where = '';
		if($script){
			$list = get_option('extlist_merchants');
			if(!is_array($list)){ $list = array(); }
			foreach($list as $list_k => $list_v){
				$mscr = trim(is_isset($list_v, 'script'));
				$status = intval(is_isset($list_v, 'status'));
				if($mscr and $mscr == $script){
					$scr[] = "'". $list_k ."'";
				}
			}
		} 
		if(count($scr) > 0){
			$join = join(',',$scr);
			$where .= " AND m_in IN(". $join .")";
		} else {
			$where .= " AND m_in != ''";
		}
		
		$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE id='$id' AND status != 'auto' $where");
		if(isset($item->id)){
			
			do_action('redirect_merchant_action', $script, $id);
			
			$hashed = is_bid_hash($item->hashed);
			$url = get_bids_url($hashed);
			wp_redirect($url);
			exit;		

		} else {
			pn_display_mess($text, $text, $res);
		}
	} else {
		pn_display_mess($text, $text, $res);
	}	
}

function check_trans_in($m_in, $trans_id, $order_id){
global $wpdb;
	$trans_id = pn_maxf_mb(pn_strip_input($trans_id),500);	
	$where = '';
	$order_id = intval($order_id);
	if($order_id){
		$where .= " AND id != '$order_id'";
	}
	return $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."exchange_bids WHERE trans_in = '$trans_id' AND m_in = '$m_in' $where");
}

function set_bid_status($status, $id, $params=array(), $direction=''){ 
global $wpdb;	

	$sum = is_sum(is_isset($params, 'sum'), 12);
	$bid_sum = is_sum(is_isset($params, 'bid_sum'), 12);
	$bid_corr_sum = is_sum(is_isset($params, 'bid_corr_sum'), 12);
	$pay_purse = pn_maxf_mb(pn_strip_input(is_isset($params, 'pay_purse')), 500);
	$to_account = pn_maxf_mb(pn_strip_input(is_isset($params, 'to_account')),500); 
	$from_account = pn_maxf_mb(pn_strip_input(is_isset($params, 'from_account')),500); 
	$trans_in = pn_maxf_mb(pn_strip_input(is_isset($params, 'trans_in')),500); 
	$trans_out = pn_maxf_mb(pn_strip_input(is_isset($params, 'trans_out')),500);
	$currency = strtoupper(trim(is_isset($params, 'currency')));
	$bid_currency = strtoupper(trim(is_isset($params, 'bid_currency')));
	$invalid_ctype = intval(is_isset($params, 'invalid_ctype'));
	$invalid_minsum = intval(is_isset($params, 'invalid_minsum'));
	$invalid_maxsum = intval(is_isset($params, 'invalid_maxsum'));
	$invalid_check = intval(is_isset($params, 'invalid_check'));
	$m_status = is_isset($params, 'bid_status');

	$id = intval($id);
	
	$m_place = trim(is_isset($params, 'm_place'));
	if(!$m_place){ 	
		$m_place = 'undefined';	
	}

	$system = trim(is_isset($params, 'system'));
	if($system != 'user'){ $system = 'system'; }
	
	$status = is_status_name($status);
	if($id and $status){
		$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE id='$id' AND status != 'auto'");
		if(isset($item->id)){
			$bid_status = $item->status;
			if(!is_array($m_status) or in_array($bid_status, $m_status)){
				if($bid_status != $status){
					if($bid_sum <= 0){
						$bid_sum = is_sum($item->sum1r, 12);
					}	
					if($bid_corr_sum <= 0){
						$bid_corr_sum = $bid_sum;
					}
					
					$account = apply_filters('pay_purse_merchant', $item->account_give);
					
					$arr = array(
						'edit_date'=> current_time('mysql') 
					);	
						
					if($to_account){
						$arr['to_account'] = $to_account;
					}	
					if($from_account){
						$arr['from_account'] = $from_account;
					}					
					if($trans_in){
						$arr['trans_in'] = $trans_in;			
					}
					if($trans_out){
						$arr['trans_out'] = $trans_out;				
					}					
						
					if($sum > $bid_sum){
						$arr['exceed_pay'] = 1;
					}			
						
					if($sum > 0){
						$arr['pay_sum'] = $sum;					
					}
					if($pay_purse){
						$arr['pay_ac'] = $pay_purse;					
					}	

					$arr['status'] = $status;
						
					$st = array('realpay');	
					$st = apply_filters('set_bid_status_for_verify', $st);
					
					if(in_array($arr['status'], $st)){
						if($invalid_check > 0){
							if($pay_purse and $account){
								if($pay_purse != $account){
									if($invalid_check == 1){
										$arr['status'] = 'verify';	
									}
								}
							}
						}
					}	
					
					if(in_array($arr['status'], $st)){
						if($invalid_ctype > 0){
							if($currency and $bid_currency){
								if($currency != $bid_currency){
									if($invalid_ctype == 1){
										$arr['status'] = 'verify';
									}
								}	
							}
						}
					}
					
					if(in_array($arr['status'], $st)){
						if($invalid_minsum > 0){
							if($sum < $bid_corr_sum){
								if($invalid_minsum == 1){
									$arr['status'] = 'verify';
								}
							}
						}
					}

					if(in_array($arr['status'], $st)){
						if($invalid_maxsum > 0){
							if($sum > $bid_sum){
								if($invalid_maxsum == 1){
									$arr['status'] = 'verify';
								}
							}						
						}
					}	
					
					$arr = apply_filters('set_bid_status_array', $arr, $params, $item, $direction);
						
					$result = $wpdb->update($wpdb->prefix.'exchange_bids', $arr, array('id'=>$item->id));
					if($result == 1){
						$old_status = $item->status;
						$item = pn_object_replace($item, $arr);
						$item = apply_filters('change_bidstatus', $item, $arr['status'], $m_place, $system, $old_status, $direction);
					}				
				}
			}
		}	
	}
}	

add_filter('sum_to_pay','def_sum_to_pay',1,4);
function def_sum_to_pay($sum, $m_id, $direction, $item){
	if($m_id){
		if(isset($direction->id) and isset($item->id)){
			$vid = is_type_merchant($m_id);
			if($direction->pay_com1 == 0 and $vid == 1){
				return $item->sum1c;
			} 
		} 
	}	
	return $sum;
}

function get_data_merchant_for_id($id, $item=''){ 
global $wpdb;	

    $id = intval($id);
	$array = array();
	$array['err'] = 0;
	$array['status'] = $array['currency'] = $array['hashed'] = $array['m_id'] = $array['m_script'] = '';
	$array['sum'] = $array['pay_sum'] = 0;
	$array['bids_data'] = $array['direction_data'] = array();
	
	if($id > 0){
		if(!is_object($item)){
			$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE id='$id'");
		}
		if(isset($item->id)){
			$array['err'] = 0;
			$array['status'] = is_status_name($item->status);
			$array['sum'] = is_sum($item->sum1dc);
			$array['currency'] = is_site_value($item->currency_code_give);
			$array['hashed'] = is_bid_hash($item->hashed);
			$array['pay_sum'] = is_sum($item->sum1dc);
			$array['bids_data'] = $item;
			
			$direction_id = intval($item->direction_id);
			$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE auto_status='1' AND id='$direction_id'");
			if(isset($direction->id)){
				$array['direction_data'] = $direction;
				$m_id = apply_filters('get_merchant_id','', $direction, $item);
				$array['m_script'] = get_mscript($m_id);
				$array['m_id'] = $m_id;
				if($direction->pay_com1 == 1){	
					$array['pay_sum'] = is_sum($item->sum1r);	 
				} 				
			}
		} else {
			$array['err'] = 2;	
		}
	} else {
		$array['err'] = 1;
	}
	
	return $array;
}

add_filter('get_merchant_id', 'set_get_merchant_id', 2, 3);
function set_get_merchant_id($m_in, $direction, $bids_data){
global $wpdb;
	
	$m_in = trim($m_in);
	if(!$m_in){
		
		$list = get_option('extlist_merchants');
		if(!is_array($list)){ $list = array(); }
		
		$time = current_time('timestamp');
		
		$direction_id = intval(is_isset($bids_data, 'direction_id'));
		if(!isset($direction->id)){
			$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE auto_status='1' AND id='$direction_id'");
		}
		
		$dir_data = get_direction_meta(is_isset($direction,'id'), 'paymerch_data');
		$m_ins = @unserialize(is_isset($direction,'m_in')); if(!is_array($m_ins)){ $m_ins = array(); }
		
		$sum = is_sum($bids_data->sum1dc);
		
		$merch = $bids_data->m_in;
		
		$new_arrs = array();
		if(strlen($merch) > 1 and in_array($merch, $m_ins)){
			$new_arrs[] = $merch;
		}
		
		foreach($m_ins as $m){
			if($m != $merch){
				$new_arrs[] = $m;
			}
		}
		
		$now_merch = '';
		$st = array('new','techpay','payed','coldpay','realpay','verify','scrpayerror','payouterror','success','coldsuccess');
		$st = apply_filters('status_for_calc_merch_limits', $st, $direction, $bids_data);
		foreach($new_arrs as $mer){
			if(isset($list[$mer], $list[$mer]['status'])){
				$status = intval($list[$mer]['status']);
				if($status == 1){
					$data = get_merch_data($mer);
					$go = 1;
					
					$max_sum = is_sum(is_isset($dir_data, 'm_in_max_sum')); 
					if($max_sum <= 0){
						$max_sum = is_sum(is_isset($data, 'max_sum'));
					}
					if($max_sum > 0){
						$go = 0;
						if($max_sum >= $sum){
							$go = 1;
						}
					}
					
					$max_day = is_sum(is_isset($dir_data, 'm_in_max'));
					if($max_day <= 0){
						$max_day = is_sum(is_isset($data, 'max'));
					}	
					if($max_day > 0 and $go == 1){
						$go = 0;
						$date = date('Y-m-d 00:00:00', $time);
						$sum_in = get_sum_for_merchpay($mer, $date, $st, $bids_data->id);
						$sum_day = $sum_in + $sum;
						if($max_day >= $sum_day){
							$go = 1;
						}
					}

					$max_month = is_sum(is_isset($dir_data, 'm_in_max_month'));
					if($max_month <= 0){
						$max_month = is_sum(is_isset($data, 'max_month'));
					}	
					if($max_month > 0 and $go == 1){
						$go = 0;
						$date = date('Y-m-01 00:00:00', $time);
						$sum_in = get_sum_for_merchpay($mer, $date, $st, $bids_data->id);
						$sum_month = $sum_in + $sum;
						if($max_month >= $sum_month){
							$go = 1;
						}
					}

					$maxc_day = intval(is_isset($dir_data, 'm_in_maxc_day'));
					if($maxc_day <= 0){
						$maxc_day = intval(is_isset($data, 'maxc_day'));
					}	
					if($maxc_day > 0 and $go == 1){
						$go = 0;
						$date = date('Y-m-d 00:00:00', $time);
						$count_in = get_count_for_merchpay($mer, $date, $st, $bids_data->id);
						$count_day = $count_in + 1;
						if($maxc_day >= $count_day){
							$go = 1;
						}
					}

					$maxc_month = intval(is_isset($dir_data, 'm_in_maxc_month'));
					if($maxc_month <= 0){
						$maxc_month = intval(is_isset($data, 'maxc_month'));
					}	
					if($maxc_month > 0 and $go == 1){
						$go = 0;
						$date = date('Y-m-01 00:00:00', $time);
						$count_in = get_count_for_merchpay($mer, $date, $st, $bids_data->id);
						$count_month = $count_in + 1;
						if($maxc_month >= $count_month){
							$go = 1;
						}
					}					
					
					if($go == 1){
						$now_merch = $mer;
						break;
					}
				}
			}
		}
		
		if($merch != $now_merch){
			$wpdb->update($wpdb->prefix.'exchange_bids', array('m_in' => $now_merch), array('id'=> $bids_data->id));
		}		
		return $now_merch;
	}
	
	return $m_in;
}

add_filter('merchant_admin_tags', 'remove_merchant_admin_tags');
function remove_merchant_admin_tags($tags){
	$rems = array('spbbonus','spbbonus_sum','verification_status','verify_amount','verification_link','create_acc_give','create_acc_get','bid_recalc','frozen_date','num_schet','confirm_count','confirm_count_time');
	foreach($rems as $rem){
		if(isset($tags[$rem])){
			unset($tags[$rem]);
		}
	}
	return $tags;
}

if(!class_exists('Merchant_Premiumbox')){
	class Merchant_Premiumbox extends Ext_Premium {
		function __construct($file, $title, $cron=0)
		{
			if(is_array($title)){
				return; /*deprecated */
			}
			
			global $premiumbox;
			parent::__construct($file, $title, 'merchants', $premiumbox);
			
			if($cron == 1){
				$ids = $this->get_ids('merchants', $this->name);
				foreach($ids as $id){
					add_action('premium_merchant_'. $id .'_cron' . hash_url($id), array($this,'merchant_cron'));
				}
			}
			
			add_action('get_merchants_options', array($this, 'get_merchants_options'), 10, 5);
			add_filter('merchants_security_' . $this->name, array($this, 'security_errors'), 10, 2);
			add_filter('merchant_bidform', array($this,'bidform'),99,5);
			add_filter('merchant_bidaction', array($this,'bidaction'),99,5);
			add_action('ext_merchants_delete', array($this, 'del_directions'), 10, 2);
		}

		function replace_constant($m_defin, $name){
			global $premiumbox;
			$file_some = trim(is_deffin($m_defin, $name));
			$file_arr = explode('/', $file_some);
			$file = end($file_arr);
			if($file){
				return $premiumbox->plugin_dir . 'merchants/' . $this->name . '/dostup/' . $file;
			}
				return '';
		}

		function merchant_cron(){
			$m_id = key_for_url('_cron');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			$this->cron($m_id, $m_defin, $m_data);
			
			_e('Done','pn');
		}
		
		function cron($m_id, $m_defin, $m_data){
			
		}

		function bidform($temp, $m_id, $pay_sum, $item, $direction){
			return $temp;
		}	
		
		function bidaction($temp, $m_id, $pay_sum, $item, $direction){
			return $temp;
		}

		function get_merchants_options($options, $name, $data, $id, $place){
			if($name == $this->name){
				$options = $this->options($options, $data, $id, $place);
			}
			return $options;
		}
		
		function options($options, $data, $id, $place){
			return $options;
		}
 
		public function security_errors($text, $id){
			$security_list = merchant_setting_list($this->name, '', $id, 0);
			$data = get_merch_data($id);
			
			$errors = array();
			foreach($security_list as $sec_k => $sec_val){
				$sec_k = (string)$sec_k;
				
				if($sec_k == 'resulturl'){
					if(!is_isset($data, $sec_k)){
						$errors[] = '<span class="bred">-' . __('Hash for Status/Result URL not set','pn') . '</span>';
					}
				}
				if($sec_k == 'check_api'){
					if(intval(is_isset($data, $sec_k)) != 1){
						$errors[] = '<span class="bred">-' . __('Payment history verification through API interface disabled','pn') . '</span>';
					}
				}				
				if($sec_k == 'enableip'){
					if(!trim(is_isset($data, $sec_k))){
						$errors[] = '<span class="bred">-' . __('No restriction by IP address set','pn') . '</span>';
					}
				}				
				if($sec_k == 'show_error'){
					$sh = intval(is_isset($data, $sec_k));
					if($sh == 1){
						$errors[] = '<span class="bred">-' . __('Debug mode enabled','pn') . '</span>';
					}
				}
			}
			
			if(count($errors) > 0){
				return join('<br />', $errors);
			}
			return $text;
		}	

		function set_keys($keys){
			$keys[] = $this->name;
			return $keys;
		}

		function logs($error_text){
			do_action('save_merchant_error', $this->name, $error_text);
		}
		
		function zone_form($pagenote, $list_data, $descr='', $link, $hash){
			$temp = '	
			<div class="zone_center">  
				<div class="zone_center_ins">';
					if($pagenote){
						$temp .= '<div class="zone_description">'. apply_filters('comment_text', $pagenote) .'</div>';
					}							
					$temp .= '		
					<div class="zone_form">
						<form action="'. $link .'" method="post">
							<input type="hidden" name="hash" value="'. $hash .'" />';
							
							if(is_array($list_data)){
								foreach($list_data as $key => $item){

									$temp .= '
									<div class="zone_form_line">
										<div class="zone_form_label">'. is_isset($item, 'title') .'</div>
										<input type="text" required name="'. is_isset($item, 'name') .'" value="" />
									</div>								
									';
								}
							}	
						
						$temp .= '	
							<div class="zone_form_line">
								<input type="submit" class="submit_form" formtarget="_top" value="'. __('Submit code','pn').'" />
							</div>
						</form>
					</div>				
					';
			$temp .= '
				</div>
			</div>
			';	
			if($descr){
				$temp .= '<div class="zone_descr">' . $descr . '</div>';
			}
			return apply_filters('zone_form', $temp, $pagenote, $list_data, $descr, $link, $hash);
		}		
		
		function zone_table($pagenote, $list_data, $descr=''){
			$temp = '	
			<div class="zone_center">  
				<div class="zone_center_ins">';
					if($pagenote){
						$temp .= '<div class="zone_description">'. apply_filters('comment_text', $pagenote) .'</div>';
					}							
					$temp .= '		
					<div class="zone_table">';
						if(is_array($list_data)){
							foreach($list_data as $key => $item){
								$text = trim(is_isset($item, 'text'));
								if($text){
									$temp .= '
									<div class="zone_div">
										<div class="zone_title"><div class="zone_copy" data-clipboard-text="'. is_isset($item, 'copy') .'"><div class="zone_copy_abs">'. __('copied to clipboard','pn') .'</div>'. is_isset($item, 'title') .'</div></div>
										<div class="zone_text">'. $text .'</div>					
									</div>								
									';
								}
							}
						}	
						$temp .= '						
					</div>				
					';
			$temp .= '
				</div>
			</div>
			';	
			if($descr){
				$temp .= '<div class="zone_descr">' . $descr . '</div>';
			}
			return apply_filters('zone_table', $temp, $pagenote, $list_data, $descr);
		}
		
		function zone_error($error_text){
			$temp = '<div class="error_div"><div class="error_div_ins">'. $error_text .'</div></div>';
			return $temp;
		}
		
		function del_directions($script, $id){
			global $wpdb;
			$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions WHERE m_in LIKE '%\"{$id}\"%'");
			foreach($items as $item){
				$m_in = @unserialize($item->m_in);
				if(!is_array($m_in)){ $m_in = array(); }
				foreach($m_in as $m_in_k => $m_in_v){
					if($m_in_v == $id){
						unset($m_in[$m_in_k]);
					}
				}
				$arr = array();
				$arr['m_in'] = @serialize($m_in);
				$wpdb->update($wpdb->prefix ."directions", $arr, array('id' => $item->id));
			}
			$wpdb->query("UPDATE ".$wpdb->prefix."exchange_bids SET m_in = '' WHERE m_in = '$id'");
		}
	}
}