<?php
if( !defined( 'ABSPATH')){ exit(); }
 
function list_paymerchants(){
	return list_extandeds('paymerchants');
}

function get_pscript($m_id){
	$now_script = '';
	$list = get_option('extlist_paymerchants');
	if(!is_array($list)){ $list = array(); }
	
	$data = is_isset($list, $m_id);
	$script = trim(is_isset($data, 'script'));

	if(strlen($script) > 0){
		$now_script = $script;
	}		
	return $now_script;
}

function get_paymerch_data($m_id){
global $pn_paymerch_data;
	if(!is_array($pn_paymerch_data)){
		$pn_paymerch_data = (array)get_option('paymerchants_data');
	}
	return is_isset($pn_paymerch_data,$m_id);
}
 
add_action('instruction_paymerchant','def_instruction_paymerchant',1,2);
function def_instruction_paymerchant($instruction,$m_id){
global $premiumbox;
	
	if($m_id){
		$data = get_paymerch_data($m_id); 
		$text = trim(ctv_ml(is_isset($data,'text')));
		if($text){
			return $text;
		} else {
			$show = intval($premiumbox->get_option('exchange','mp_ins'));
			if($show == 0){
				return $text;
			} else {
				return $instruction;
			}
		}
	}
	
	return $instruction;
}

function get_text_paymerch($m_id, $item, $pay_sum=0){
	$text = '';
	if($m_id and isset($item->id)){
		$data = get_paymerch_data($m_id);
		$text = trim(ctv_ml(is_isset($data,'note')));
		
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
		
		$text = apply_filters('get_text_paymerch', $text, $m_id, $item);
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
		$text = str_replace('[coupon_code]', pn_strip_input(is_isset($item,'btc_code')), $text);
		$text = str_replace('[coupon_info]', pn_strip_input(is_isset($item,'btc_code_info')), $text);
		if(strstr($text,'[bid_delete_time]')){
			$bid_delete_time = apply_filters('bid_delete_time', '', $item);
			$text = str_replace('[bid_delete_time]', $bid_delete_time, $text);
		}		
	}
	return esc_attr(trim($text));
}
 
add_filter('list_admin_notify','list_admin_notify_paymerchant');
function list_admin_notify_paymerchant($places_admin){
	$places_admin['paymerchant_error'] = __('Automatic payout error','pn');
	return $places_admin;
}

add_filter('list_notify_tags_paymerchant_error','def_mailtemp_tags_paymerchant_error');
function def_mailtemp_tags_paymerchant_error($tags){
	$tags['bid_id'] = array(
		'title' => __('Order ID','pn'),
		'start' => '[bid_id]',
	);
	$tags['error_txt'] = array(
		'title' => __('Error','pn'),
		'start' => '[error_txt]',
	);
	return $tags;
} 

function send_paymerchant_error($bid_id, $error_txt){
	$notify_tags = array();
	$notify_tags['[sitename]'] = pn_site_name();
	$notify_tags['[bid_id]'] = $bid_id;
	$notify_tags['[error_txt]'] = $error_txt;
	$notify_tags = apply_filters('notify_tags_paymerchant_error', $notify_tags);		

	$user_send_data = array();
	$result_mail = apply_filters('premium_send_message', 0, 'paymerchant_error', $notify_tags, $user_send_data); 	
}				

add_filter('change_bidstatus','paymerch_change_bidstatus', 2500, 6);   
function paymerch_change_bidstatus($item, $set_status, $place, $system, $old_status, $direction=''){
global $wpdb;	
	if($place != 'admin_panel'){
		if($set_status == 'realpay' or $set_status == 'verify'){
			$direction_id = intval(is_isset($item, 'direction_id'));
			if(!isset($direction->id)){
				$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE auto_status='1' AND id='$direction_id'");
			}
			$m_id = apply_filters('get_paymerchant_id', '', $direction, $item);
			if($m_id){
				$direction_data = get_direction_meta($direction_id, 'paymerch_data');
				$go_autopay = intval(is_isset($direction_data, 'm_out_' . $set_status));
				$paymerch_data = get_paymerch_data($m_id);
				$paymerch_autopay = intval(is_isset($paymerch_data, $set_status));
				if($paymerch_autopay and $go_autopay == 0 or $go_autopay == 2){ 
					do_action('paymerchant_action_bid', $m_id, $item, 'site', $direction_data, $place, $direction, $paymerch_data);
				}
			}
		}
	}	
	return $item;
}

add_filter('onebid_actions','onebid_actions_paymerch',99,3);
function onebid_actions_paymerch($actions, $item, $data_fs){
global $wpdb;

	$status = $item->status;
	
	$av_status_button = get_option('av_status_button');
	if(!is_array($av_status_button)){ $av_status_button = array(); }
	 
	$st = apply_filters('status_for_autopay_admin', $av_status_button);
	$st = (array)$st;
	if(in_array($status, $st)){
		if(current_user_can('administrator') or current_user_can('pn_bids_payouts')){
			$m_id = apply_filters('get_paymerchant_id', '', '', $item);
			if($m_id){
				if(is_paymerch_button($m_id)){
					$actions['pay_merch'] = array(
						'type' => 'link',
						'title' => __('Transfer','pn'),
						'label' => __('Transfer','pn'),
						'link' => pn_link('paymerchant_bid_action') .'&id=[id]',
						'link_target' => '_blank',
						'link_class' => 'pay_merch',
					);	
					$test_mode = apply_filters('autopay_test', 0);
					if($test_mode == 1){
						$actions['pay_merch_test'] = array(
							'type' => 'link',
							'title' => __('Transfer','pn') . '(test)',
							'label' => __('Transfer','pn') . '(test)',
							'link' => pn_link('paymerchant_bid_action') .'&id=[id]&test=1',
							'link_target' => '_blank',
							'link_class' => 'pay_merch',
						);							
					}
				}
			}
		}
	}
	return $actions;
}

add_action('premium_action_paymerchant_bid_action','def_paymerchant_bid_action');
function def_paymerchant_bid_action(){
global $wpdb;

	if(current_user_can('administrator') or current_user_can('pn_bids_payouts')){
		admin_pass_protected(__('Enter security code','pn'), __('Enter','pn'));	
		
		$bid_id = intval(is_param_get('id'));
		$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE id='$bid_id'");
		if(isset($item->id)){
			$status = $item->status;
			
			$av_status_button = get_option('av_status_button');
			if(!is_array($av_status_button)){ $av_status_button = array(); }
			
			$st = apply_filters('status_for_autopay_admin', $av_status_button);
			$st = (array)$st;
			if(in_array($status, $st)){
				$direction_id = intval(is_isset($item, 'direction_id'));
				$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE auto_status='1' AND id='$direction_id'");
				$m_id = apply_filters('get_paymerchant_id', '', $direction, $item);
				if($m_id){
					if(is_paymerch_button($m_id)){
						$direction_data = get_direction_meta($direction_id, 'paymerch_data');
						$paymerch_data = get_paymerch_data($m_id);
						do_action('paymerchant_action_bid', $m_id, $item, 'admin', $direction_data, 'admin_panel', $direction, $paymerch_data);
					} else {
						pn_display_mess(__('Error! Automatic payout is disabled','pn'));
					}
				} else {
					pn_display_mess(__('Error! Automatic payout is disabled','pn'));
				}
			} else {
				pn_display_mess(__('Error! Incorrect status of the order','pn'));
			}
		} else {
			pn_display_mess(__('Error! Order does not exist','pn'));
		}
	} else {
		pn_display_mess(__('Error! Insufficient privileges','pn'));
	}
}

/* стандартная проверка всех ав */
add_filter('autopayment_filter', 'def_autopayment_filter', 1, 6);
function def_autopayment_filter($au_filter, $m_id, $item, $place, $direction_data, $paymerch_data){
global $premiumbox;	
	
	$c_data = is_substitution($item);
	if(count($c_data) > 0){
		$au_filter['error'][] = __('Data from the order were compromised', 'pn') . ' ' . join(',',$c_data);
	}	
	
	$autopay_status = intval(get_bids_meta($item->id, 'ap_status'));
	if($autopay_status == 1){
		$au_filter['error'][] = __('Automatic payout has already been made', 'pn');		
	}	
	
	return $au_filter;
}

add_filter('autopayment_filter', 'avsumbig_autopayment_filter', 5, 8);
function avsumbig_autopayment_filter($au_filter, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $direction){
global $premiumbox;	

	$avsumbig = intval($premiumbox->get_option('exchange','avsumbig'));
	$exceed_pay = intval(is_isset($item, 'exceed_pay'));
	if($exceed_pay == 1 and $avsumbig == 0){ 
		$au_filter['error'][] = __('The amount of payment is less than the amount required in the order', 'pn');  
	}	

	return $au_filter;
}

function is_paymerch_sum($bids_data, $data){
	$where_sum = intval(is_isset($data, 'where_sum'));
	$sum = 0;
	if($where_sum == 0){
		$sum = $bids_data->sum2c;
	} elseif($where_sum == 1){
		$sum = $bids_data->sum2dc;
	} elseif($where_sum == 2){
		$sum = $bids_data->sum2r;
	} elseif($where_sum == 3){
		$sum = $bids_data->sum2;
	}
	return $sum;
}

function is_substitution($item){
	$hask_keys = bid_hashkey();
	$comprome_date = array();
	
	$hashdata = @unserialize($item->hashdata);
	if(!is_array($hashdata)){ $hashdata = array(); }
	
	foreach($hask_keys as $key){
		$value = trim(is_isset($item, $key));
		if($value){
			$hash = trim(is_isset($hashdata, $key));
			if(!is_pn_crypt($hash, $value)){
				$comprome_date[] = $key;
			}	
		}
	}	

	return $comprome_date;
}

function is_paymerch_checkpay($m_id){
	$data = get_paymerch_data($m_id);  
	return intval(is_isset($data,'checkpay'));
}

function is_paymerch_button($m_id){
	$data = get_paymerch_data($m_id);  
	return intval(is_isset($data,'button'));
}

add_filter('get_paymerchant_id', 'set_get_paymerchant_id', 2, 3);
function set_get_paymerchant_id($m_out, $direction, $bids_data){
global $wpdb;
	
	$m_out = trim($m_out);
	if(!$m_out){
		$list = get_option('extlist_paymerchants');
		if(!is_array($list)){ $list = array(); }
		
		$time = current_time('timestamp');
		
		$direction_id = intval(is_isset($bids_data, 'direction_id'));
		if(!isset($direction->id)){
			$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE auto_status='1' AND id='$direction_id'");
		}
		
		$dir_data = get_direction_meta(is_isset($direction,'id'), 'paymerch_data');
		$m_outs = @unserialize(is_isset($direction,'m_out')); if(!is_array($m_outs)){ $m_outs = array(); }
		
		$merch = $bids_data->m_out;
		
		$new_arrs = array();
		if(strlen($merch) > 1 and in_array($merch, $m_outs)){
			$new_arrs[] = $merch;
		}
		
		foreach($m_outs as $m){
			if($m != $merch){
				$new_arrs[] = $m;
			}
		}
		
		$now_merch = '';
		$st = array('realpay','verify','success','coldsuccess');
		foreach($new_arrs as $mer){
			if(isset($list[$mer], $list[$mer]['status'])){
				$status = intval($list[$mer]['status']);
				if($status == 1){
					
					$data = get_paymerch_data($mer);
					$sum = is_sum(is_paymerch_sum($bids_data, $data));
					$go = 1;
					$script = $list[$mer]['script'];
					
					$min_sum = is_sum(is_isset($dir_data, 'm_out_min_sum')); 
					if($min_sum <= 0){
						$min_sum = is_sum(is_isset($data, 'min_sum'));
					} 	
					if($min_sum > 0 and $sum < $min_sum){
						$go = 0;
						do_action('save_paymerchant_error', $script, sprintf(__('Payment amount (%1s) is less than specified in the settings (%2s)','pn'), $sum ,$min_sum), $bids_data->id);
					}
					
					$max_sum = is_sum(is_isset($dir_data, 'm_out_max_sum')); 
					if($max_sum <= 0){
						$max_sum = is_sum(is_isset($data, 'max_sum'));
					}
					if($max_sum > 0 and $go == 1){
						$go = 0;
						if($max_sum >= $sum){
							$go = 1;
						} else {
							do_action('save_paymerchant_error', $script, __('The amount exceeds the limit for automatic payouts for order', 'pn'), $bids_data->id);
						}
					}
					
					$max_day = is_sum(is_isset($dir_data, 'm_out_max'));
					if($max_day <= 0){
						$max_day = is_sum(is_isset($data, 'max'));
					}	

					if($max_day > 0 and $go == 1){
						$go = 0;
						$date = date('Y-m-d 00:00:00', $time);
						$sum_in = get_sum_for_autopay($mer, $date, $st, $bids_data->id);
						$sum_day = $sum_in + $sum;
						if($max_day >= $sum_day){
							$go = 1;
						} else {
							do_action('save_paymerchant_error', $script, __('The amount exceeds the daily limit for automatic payouts', 'pn'), $bids_data->id);
						}
					}

					$max_month = is_sum(is_isset($dir_data, 'm_out_max_month'));
					if($max_month <= 0){
						$max_month = is_sum(is_isset($data, 'max_month'));
					}	
					if($max_month > 0 and $go == 1){
						$go = 0;
						$date = date('Y-m-01 00:00:00', $time);
						$sum_in = get_sum_for_autopay($mer, $date, $st, $bids_data->id);
						$sum_month = $sum_in + $sum;
						if($max_month >= $sum_month){
							$go = 1;
						} else {
							do_action('save_paymerchant_error', $script, __('The amount exceeds the month limit for automatic payouts', 'pn'), $bids_data->id);
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
			$wpdb->update($wpdb->prefix.'exchange_bids', array('m_out' => $now_merch), array('id'=> $bids_data->id));
		}		

		return $now_merch;
	}
	return $m_out;
}

add_filter('list_icon_indicators', 'scrpayerror_icon_indicators');
function scrpayerror_icon_indicators($lists){
global $premiumbox;
	$lists['scrpayerror'] = array(
		'title' => __('Orders with payout error (payment system API)','pn'),
		'img' => $premiumbox->plugin_url .'images/payouterror.png',
		'link' => admin_url('admin.php?page=pn_bids&idspage=1&bidstatus[]=scrpayerror')
	);
	return $lists;
}

add_filter('count_icon_indicator_scrpayerror', 'def_icon_indicator_scrpayerror');
function def_icon_indicator_scrpayerror($count){
	global $wpdb;
	if(current_user_can('administrator') or current_user_can('pn_bids')){
		$count = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."exchange_bids WHERE status = 'scrpayerror'");
	}	
	return $count;
}

add_filter('paymerchant_admin_tags', 'remove_paymerchant_admin_tags');
function remove_paymerchant_admin_tags($tags){
	$rems = array('spbbonus','spbbonus_sum','verification_status','verify_amount','verification_link','create_acc_give','create_acc_get','bid_recalc','frozen_date','num_schet','confirm_count','confirm_count_time');
	foreach($rems as $rem){
		if(isset($tags[$rem])){
			unset($tags[$rem]);
		}
	}
	return $tags;
}

if(!class_exists('AutoPayut_Premiumbox')){
	class AutoPayut_Premiumbox extends Ext_Premium {
		function __construct($file, $title, $cron=0)
		{
			if(is_array($title)){
				return; /*deprecated */
			}			
			
			global $premiumbox;
			parent::__construct($file, $title, 'paymerchants', $premiumbox);

			if($cron == 1){
				$ids = $this->get_ids('paymerchants', $this->name);
				foreach($ids as $id){
					add_action('premium_merchant_ap_'. $id .'_cron' . hash_url($id, 'ap'), array($this,'paymerchant_cron'));
				}
			}
			
			add_action('get_paymerchants_options', array($this, 'get_paymerchants_options'), 10, 5);
			add_filter('paymerchants_security_' . $this->name, array($this, 'security_errors'), 10, 2);
			add_filter('reserv_place_list',array($this,'reserv_place_list'));
			add_filter('get_formula_code', array($this,'formula_code'), 10, 4);
			add_filter('calc_currency_reserv', array($this,'calc_currency_reserv'), 10, 4); 
			add_action('ext_paymerchants_delete', array($this, 'del_directions'), 10, 2);
			add_action('paymerchant_action_bid', array($this,'paymerchant_action_bid'), 99, 7);	
			add_filter('autopayment_filter', array($this,'check_history'), 200, 10);
		}	

		function replace_constant($m_defin, $name){
			global $premiumbox;
			$file_some = trim(is_deffin($m_defin, $name));
			$file_arr = explode('/', $file_some);
			$file = end($file_arr);
			if($file){
				return $premiumbox->plugin_dir . 'paymerchants/' . $this->name . '/dostup/' . $file;
			}
				return '';
		}

		function paymerchant_cron(){
			$m_id = key_for_url('_cron','ap_');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_paymerch_data($m_id);
			
			$this->cron($m_id, $m_defin, $m_data);
			
			_e('Done','pn');
		}
		
		function cron($m_id, $m_defin, $m_data){
			
		}		

		function calc_currency_reserv($reserv_calc, $reserv_place, $id, $item){
			if(strstr($reserv_calc, '[' . $this->name) and !strstr($reserv_calc, '[excursum_auto]')){
				$reserv_calc .= ' - [excursum_auto]';
			}
			return $reserv_calc;
		}

		function formula_code($n, $code, $id, $place=''){
			
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach($ids as $m_id){
				if (preg_match("/^". $m_id . '_' ."[a-zA-Z0-9_]{0,300}$/", $code, $matches )) {
					$m_defin = $this->get_file_data($m_id);
					$n = $this->update_reserve($code, $m_id, $m_defin);
				}
			}
			
				return $n;
		}		
		
		function update_reserve($code, $m_id, $m_defin){
			return 0;
		}
		
		function get_paymerchants_options($options, $name, $data, $id, $place){
			if($name == $this->name){
				$options = $this->options($options, $data, $id, $place);
				$m_defin = $this->get_file_data($id);
				$purses = $this->get_res($id);
				$text = '';
				foreach($purses as $k => $v){
					$text .= '<div><input type="text" name="" value="['. $k .']" /> ' . $v . '</div>';
				}
				if($text){
					$options['codes_for_reserv'] = array(
						'view' => 'help',
						'title' => __('Currency reserve shortcode','pn'),
						'default' => $text,
					);
				}
			}
			return $options;
		}
		
		function options($options, $data, $id, $place){
			return $options;
		}

		function reserv_place_list($list){	
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach($ids as $id){
				$purses = $this->get_res($id);
				foreach($purses as $k => $v){
					$list[$k] = $v;
				}
			}
			return $list;
		}

		public function get_res($m_id){
			$m_defin = $this->get_file_data($m_id);
			$res = $this->get_reserve_lists($m_id, $m_defin);
			$list = array();
			foreach($res as $res_key => $res_value){
				if(strlen($res_value) > 0){
					$list[$res_key] = $this->title . ' - '. $res_value;
				}
			}
			return $list;
		}

		public function get_reserve_lists($m_id, $m_defin){
			return array();
		}		
		
		public function paymerchant_action_bid($m_id, $item, $place, $direction_data, $modul_place, $direction, $paymerch_data){
			$script = get_pscript($m_id);
			if($script and $script == $this->name){
				$test = 0;
				$test_mode = apply_filters('autopay_test', 0);
				if($test_mode == 1 and $modul_place == 'admin_panel'){
					$test = intval(is_param_get('test'));
				}	
				$item_id = intval(is_isset($item,'id'));
				if($item_id){
					$unmetas = @unserialize($item->unmetas);
					$au_filter = array(
						'error' => array(),
						'pay_error' => 0,
						'enable' => 1,
					);
					$m_defin = $this->get_file_data($m_id);
					$au_filter = apply_filters('autopayment_filter', $au_filter, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $direction, $test, $m_defin);	
					if($au_filter['enable'] == 1){
						$error = (array)$au_filter['error'];
						$pay_error = intval($au_filter['pay_error']);
						$this->do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin);	
					} else {
						$error = (array)$au_filter['error'];
						$this->logs($error, $item->id);
						if($place == 'admin'){
							pn_display_mess(__('Error','pn'), join('<br />', $error));
						}	
					}
				}	
			}
		}
		
		function check_history($au_filter, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $direction, $test, $m_defin){
			if(isset($item->id) and count($au_filter['error']) == 0 and $au_filter['enable'] == 1){
				$check_history = intval(is_isset($paymerch_data, 'checkpay'));
				if($check_history == 1 and $m_id and $m_id == $this->name){
					$search_text = $this->search_in_history($item->id, $m_defin);
					if($search_text){	
						$au_filter['enable'] = 0;	
						$au_filter['error'][] = $search_text;
					}
				}
			}
			return $au_filter;
		}		
		
		function search_in_history($item_id, $m_defin){
			$search_text = '';
			return $search_text;
		}

		public function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){
			$this->logs('No action payouts', $item->id);
		}		
		
		public function set_ap_status($item, $test){
			if($test == 1){
				return 1;
			} else {
				$result = update_bids_meta($item->id, 'ap_status', 1);
				update_bids_meta($item->id, 'ap_status_date', current_time('timestamp'));
				return $result;
			}
		}
		
		public function reset_ap_status($error, $pay_error, $item, $place, $test){
			if($pay_error == 1){
				$params = array(
					'm_place' => 'system scrpayerror',
					'system' => 'system',
				);
				set_bid_status('scrpayerror', $item->id, $params);
			} 
							
			$error_text = join('<br />', $error);
						
			do_action('paymerchant_error', $this->name, $error, $item->id, $place, $pay_error);
						
			if($place == 'admin'){
				pn_display_mess(__('Error!','pn') . $error_text);
			} else {
				send_paymerchant_error($item->id, $error_text);
			}	
		}
		
		function reset_cron_status($item, $error_status, $m_id){
		global $wpdb;
		
			$error_status = is_status_name($error_status);
			if(!$error_status){ $error_status = 'payouterror'; }
		
			update_bids_meta($item->id, 'ap_status', 0);
			update_bids_meta($item->id, 'ap_status_date', current_time('timestamp'));
			
			$arr = array(
				'status'=> $error_status,
				'edit_date'=> current_time('mysql'),
			);									
			$wpdb->update($wpdb->prefix.'exchange_bids', $arr, array('id'=>$item->id));
										
			do_action('ap_cron_set_status', $item, $error_status, $m_id);
			send_paymerchant_error($item->id, __('Your payment is declined','pn'));
			
		}	
		
		function logs($error_text, $bid_id=''){
			do_action('save_paymerchant_error', $this->name, $error_text, $bid_id);
		}		
		
		function del_directions($script, $id){
			global $wpdb;
			$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions WHERE m_out LIKE '%\"{$id}\"%'");
			foreach($items as $item){
				$m_out = @unserialize($item->m_out);
				if(!is_array($m_out)){ $m_out = array(); }
				foreach($m_out as $m_out_k => $m_out_v){
					if($m_out_v == $id){
						unset($m_out[$m_out_k]);
					}
				}
				$arr = array();
				$arr['m_out'] = @serialize($m_out);
				$wpdb->update($wpdb->prefix ."directions", $arr, array('id' => $item->id));
			}
			$wpdb->query("UPDATE ".$wpdb->prefix."exchange_bids SET m_out = '' WHERE m_out = '$id'");
		}		
		
		public function security_errors($text, $id){
			$security_list = paymerchant_setting_list($this->name, '', $id, 0);
			$data = get_paymerch_data($id);
			
			$errors = array();
			foreach($security_list as $sec_k => $sec_val){
				$sec_k = (string)$sec_k;
				if($sec_k == 'resulturl'){
					if(!is_isset($data, $sec_k)){
						$errors[] = '<span class="bred">-' . __('Hash for Status/Result URL not set','pn') . '</span>';
					}
				}
				if($sec_k == 'checkpay'){
					if(intval(is_isset($data, $sec_k)) != 1){
						$errors[] = '<span class="bred">-' . __('Payment history verification through API interface disabled','pn') . '</span>';
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
	}
}