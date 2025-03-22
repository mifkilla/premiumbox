<?php
if( !defined( 'ABSPATH')){ exit(); }

function list_bid_limit(){
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	
	$limit = get_user_meta($user_id, 'list_bid_limit', true);
	$limit = intval($limit);
	if($limit < 1){ $limit = apply_filters('list_bid_limit_default', 10, $user_id); }
	$limit = intval($limit);
	
	return $limit;
}		

add_action('premium_action_bids_filter_count', 'pn_premium_action_bids_filter_count');
function pn_premium_action_bids_filter_count(){
	only_post();
	
	header('Content-Type: application/json; charset=utf-8'); 
	
	$log = array();
	$log['status'] = '';
	$log['response'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if(current_user_can('administrator') or current_user_can('pn_bids')){
		$list_bid_limit = intval(is_param_post('count'));
		if($list_bid_limit < 1){ $limit = apply_filters('list_bid_limit_default', 10, $user_id); }
		update_user_meta( $user_id, 'list_bid_limit', $list_bid_limit) or add_user_meta($user_id, 'list_bid_limit', $list_bid_limit, true);
		$log['status'] = 'success';
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Authorisation Error','pn');
	}
	
	echo json_encode($log);	
	exit;
}

add_action('premium_action_bids_filter_change', 'pn_premium_action_bids_filter_change');
function pn_premium_action_bids_filter_change(){
	only_post();
	
	header('Content-Type: application/json; charset=utf-8'); 
	
	$log = array();
	$log['status'] = '';
	$log['response'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if(current_user_can('administrator') or current_user_can('pn_bids')){
	
		$lists = apply_filters('change_bids_filter_list', array());
		$lists = (array)$lists;
		$user_filter_change = array();
		foreach($lists as $vn_list){
			foreach($vn_list as $key => $val){
				$name = trim(is_isset($val,'name'));
				$options = is_isset($val,'options');
				$work = trim(is_isset($val,'work'));
				if($name){
					$save_val = '';
					if($work == 'input'){
						$urlen_val = urlencode(is_param_post($name));
						$save_val = pn_maxf_mb(pn_strip_input($urlen_val), 1000);
					} elseif($work == 'int'){
						$urlen_val = urlencode(is_param_post($name));
						$urlen_val = trim($urlen_val);
						$save_val = intval($urlen_val);
					} elseif($work == 'sum'){
						$urlen_val = urlencode(is_param_post($name));
						$urlen_val = trim($urlen_val);
						$save_val = is_sum($urlen_val);					
					} elseif($work == 'options'){
						$urlen_val = is_param_post($name);
						$en_options = array();
						if(is_array($options)){
							foreach($options as $k => $v){
								$en_options[] = $k;
							}
						}
						if(is_array($urlen_val)){
							$save_val = array();
							foreach($urlen_val as $va){
								$va = urlencode($va);
								if(in_array($va, $en_options)){
									$save_val[] = $va;
								}
							}
						} else {
							$save_val = '';
							$urlen_val = urlencode($urlen_val);
							if(in_array($urlen_val, $en_options)){
								$save_val = $urlen_val;
							}
						}
					}
					$user_filter_change[$name] = $save_val;
				}
			}
		}
	
		update_user_meta( $user_id, 'user_filter_change', $user_filter_change) or add_user_meta($user_id, 'user_filter_change', $user_filter_change, true);
	
		$log['status'] = 'success';
	
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Authorisation Error','pn');
	}
	
	echo json_encode($log);	
	exit;
}

add_action('premium_action_bids_filter_restore', 'pn_premium_action_bids_filter_restore');
function pn_premium_action_bids_filter_restore(){
	only_post();
	
	header('Content-Type: application/json; charset=utf-8'); 
	
	$log = array();
	$log['status'] = '';
	$log['response'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if(current_user_can('administrator') or current_user_can('pn_bids')){
	
		$data = array();
		$user_filter_change = get_user_meta($user_id, 'user_filter_change', true);
		$lists = apply_filters('change_bids_filter_list', array());
		$lists = (array)$lists;
		foreach($lists as $vn_list){
			foreach($vn_list as $key => $val){
				$name = trim(is_isset($val,'name'));
				$options = is_isset($val,'options');
				$work = trim(is_isset($val,'work'));
				if($name){
					$urlen_val = is_isset($user_filter_change,$name);
					$urlen_val = urldecode($urlen_val);
					$save_val = '';
					if($work == 'input'){
						$save_val = pn_maxf_mb(pn_strip_input($urlen_val), 1000);
					} elseif($work == 'int'){
						$save_val = intval($urlen_val);
					} elseif($work == 'sum'){
						$save_val = is_sum($urlen_val);					
					} elseif($work == 'options'){
						$en_options = array();
						if(is_array($options)){
							foreach($options as $k => $v){
								$en_options[] = $k;
							}
						}
						if(is_array($urlen_val)){
							$save_val = array();
							foreach($urlen_val as $va){
								if(in_array($va, $en_options)){
									$save_val[] = $va;
								}
							}
						} else {
							$save_val = '';
							if(in_array($urlen_val, $en_options)){
								$save_val = $urlen_val;
							}
						}
					}
					if($save_val){
						$data[$name] = $save_val;
					}					
				}
			}
		}		
		
		$log['values'] = $data;
		$log['status'] = 'success';
		
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Authorisation Error','pn');
	}
	
	echo json_encode($log);	
	exit;
}	

add_action('premium_action_bids_filter_html', 'pn_premium_action_bids_filter_html');
function pn_premium_action_bids_filter_html(){

	only_post();
	
	header('Content-Type: application/json; charset=utf-8'); 
	
	$log = array();
	$log['status'] = '';
	$log['response'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');	
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if(current_user_can('administrator') or current_user_can('pn_bids')){
	
		$url = array();
		$url['page'] = 'pn_bids';
		
		$lists = apply_filters('change_bids_filter_list', array());
		$lists = (array)$lists;
		foreach($lists as $vn_list){
			foreach($vn_list as $key => $val){
				$name = trim(is_isset($val,'name'));
				$options = is_isset($val,'options');
				$work = trim(is_isset($val,'work'));
				if($name){
					$urlen_val = is_param_post($name);
					$save_val = '';
					if($work == 'input'){
						$save_val = pn_maxf_mb(pn_strip_input($urlen_val), 1000);
					} elseif($work == 'int'){
						$urlen_val = trim($urlen_val);
						$save_val = intval($urlen_val);
					} elseif($work == 'sum'){
						$urlen_val = trim($urlen_val);
						$save_val = is_sum($urlen_val);					
					} elseif($work == 'options'){
						$en_options = array();
						if(is_array($options)){
							foreach($options as $k => $v){
								$en_options[] = $k;
							}
						}
						if(is_array($urlen_val)){
							$save_val = array();
							foreach($urlen_val as $va){
								if(in_array($va, $en_options)){
									$save_val[] = $va;
								}
							}
						} else {
							$save_val = '';
							if(in_array($urlen_val, $en_options)){
								$save_val = $urlen_val;
							}
						}
					}
					if($save_val){
						$url[$name] = $save_val;
					}
				}
			}
		}	
		$page_num = intval(is_param_post('page_num'));
		if($page_num > 1){
			$url['page_num'] = $page_num;
		}

		$log['html'] = get_bids_html(http_build_query($url));
		$log['status'] = 'success';
		
	} else {
		$log['status'] = 'error';
		$log['status_text'] = 1;
		$log['status_text'] = __('Authorisation Error','pn');
	}
	
	echo json_encode($log);	
	exit;
}

function bids_actions(){
global $wpdb, $premiumbox;

	$action = get_admin_action();
	if(isset($_POST['id']) and is_array($_POST['id'])){
		$edit_date = current_time('mysql');
	
		/* удаляем полностью */
		if(current_user_can('administrator') or current_user_can('pn_bids_delete')){
			if($action == 'realdelete'){ 
				foreach($_POST['id'] as $id){
					$id = intval($id); 
					$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE id='$id'");
					if(isset($item->id)){
						$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."exchange_bids WHERE id = '$id'");
						if($result == 1){
							$wpdb->query("DELETE FROM ".$wpdb->prefix."bids_meta WHERE item_id = '$id'");						
							$old_status = $item->status; 	
							$item = apply_filters('change_bidstatus', $item, 'realdelete', 'admin_panel', 'user', $old_status);	 						
						}
					}
				}	 
			}
		}
		/* end удаляем полностью */	

		if(current_user_can('administrator') or current_user_can('pn_bids_change')){
			/* other */
			$sts = array();
			$bid_status_list = apply_filters('bid_status_list',array());
			if(is_array($bid_status_list)){
				foreach($bid_status_list as $bsl_key => $bsl_val){
					$sts[] = $bsl_key;
				}
			}
			if(in_array($action, $sts)){
				foreach($_POST['id'] as $id){
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE id='$id' AND status != '$action'");
					if(isset($item->id)){
						$arr = array('status'=>$action, 'edit_date'=> $edit_date);
						$result = $wpdb->update($wpdb->prefix.'exchange_bids', $arr, array('id'=>$id));
						if($result == 1){
							$old_status = $item->status;
							$item = pn_object_replace($item, $arr);
							$item = apply_filters('change_bidstatus', $item, $action, 'admin_panel', 'user', $old_status);
						}
					}
				}
			}	
			/* end other */				
			
			do_action('bidstatus_admin_action', $_POST['id'], $action);
		}
	}
}

add_action('premium_action_bids_action_ajax', 'pn_premium_action_bids_action_ajax');
function pn_premium_action_bids_action_ajax(){
	only_post();
	
	header('Content-Type: application/json; charset=utf-8'); 
	
	$log = array();
	$log['status'] = '';
	$log['response'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');
	 
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if(current_user_can('administrator') or current_user_can('pn_bids')){
	
		$param = trim(is_param_post('_wp_param'));
		bids_actions();
		$log['html'] = get_bids_html($param);
		$log['status'] = 'success';
		
	} else {
		$log['status'] = 'error';
		$log['status_text'] = 1;
		$log['status_text'] = __('Authorisation Error','pn');
	}	
	
	echo json_encode($log);
	exit;
}	

add_filter('change_bids_filter_list', 'def_change_bids_filter_list', 0);
function def_change_bids_filter_list($lists){
global $wpdb;
	
	/*********/
	$options = array(
		'0' => '--'. __('All','pn').'--',
		'1' => __('Except paid orders','pn'),
		'2' => __('Paid orders','pn'),
	);
	$lists['status']['paystatus'] = array(
		'title' => __('Payment status','pn'),
		'name' => 'paystatus',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);
	/*********/
		
	/*********/
	$options = array(
		'0' => '--'. __('All','pn').'--',
		'1' => __('Exact amount','pn'),
		'2' => __('Overpayment','pn'),
	);		
	$lists['status']['exceed_pay'] = array(
		'title' => __('Amount of payment via merchant','pn'),
		'name' => 'exceed_pay',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);

	$statused = apply_filters('bid_status_list',array());
	if(!is_array($statused)){ $statused = array(); }
	
	$lists['status']['bidstatus'] = array(
		'title' => __('Status of order','pn'),
		'name' => 'bidstatus',
		'options' => $statused,
		'view' => 'multi',
		'work' => 'options',
	);
	$lists['status']['status_clear1'] = array(
		'view' => 'clear',
	);	
	/*********/		
		
	/*********/
	$lists['sum']['bidid'] = array(
		'title' => __('Order ID','pn'),
		'name' => 'bidid',
		'view' => 'input',
		'work' => 'int',
	);
	/*********/		
		
	/*********/
	$lists['sum']['startdate'] = array(
		'title' => __('Start date','pn'),
		'name' => 'startdate',
		'view' => 'date',
		'work' => 'input',
	);
	/*********/			

	/*********/
	$lists['sum']['enddate'] = array(
		'title' => __('End date','pn'),
		'name' => 'enddate',
		'view' => 'date',
		'work' => 'input',
	);
	/*********/	
		
	/*********/
	$lists['sum']['min_sum1'] = array(
		'title' => __('Min. amount Giving','pn'),
		'name' => 'min_sum1',
		'view' => 'input',
		'work' => 'sum',
	);
	/*********/		

	/*********/
	$lists['sum']['min_sum2'] = array(
		'title' => __('Min. amount Receiving','pn'),
		'name' => 'min_sum2',
		'view' => 'input',
		'work' => 'sum',
	);
	/*********/

	/*********/
	$options = array(
		'0' => '--'. __('All','pn').'--',
	);
	$directions = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions ORDER BY site_order1 ASC");
	foreach($directions as $nap){ 
		$options[$nap->id]= pn_strip_input($nap->tech_name) . pn_item_status($nap, 'direction_status') . pn_item_basket($nap);
	}
				
	$lists['currency']['direction_id'] = array(
		'title' => __('Exchange direction','pn'),
		'name' => 'direction_id',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);
	/*********/		

	/*********/
	$currencies = list_currency(__('All','pn'));
	
	$options = array();
	foreach($currencies as $key => $curr){ 
		$options[$key] = $curr;
	}
			
	$lists['currency']['v1'] = array(
		'title' => __('Currency name Giving','pn'),
		'name' => 'v1',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);

	$lists['currency']['v2'] = array(
		'title' => __('Currency name Receiving','pn'),
		'name' => 'v2',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);
	/*********/

	/*********/
	$psys = list_psys(__('All','pn'));
	$options = array();
	foreach($psys as $ps_key => $ps_title){ 
		$options[$ps_key] = $ps_title;
	}
		
	$lists['currency']['psys1'] = array(
		'title' => __('PS name Giving','pn'),
		'name' => 'psys1',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);

	$lists['currency']['psys2'] = array(
		'title' => __('PS name Receiving','pn'),
		'name' => 'psys2',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',	
	);
	/*********/

	/*********/
	$vtype = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."currency_codes ORDER BY currency_code_title ASC");
	$options = array(
		'0' => '--'. __('All','pn').'--',
	);
	foreach($vtype as $item){ 
		$options[$item->id] = is_site_value($item->currency_code_title) . pn_item_basket($item);
	}
			
	$lists['currency']['vtype1'] = array(
		'title' => __('Currency code Giving','pn'),
		'name' => 'vtype1',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);

	$lists['currency']['vtype2'] = array(
		'title' => __('Currency code Receiving','pn'),
		'name' => 'vtype2',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);
	/*********/				
		
	/*********/	
	$lists['user']['iduser'] = array(
		'title' => __('User ID','pn'),
		'name' => 'iduser',
		'view' => 'input',
		'work' => 'int',
	);
	/*********/		
		
	/*********/		
	$lists['user']['user_login'] = array(
		'title' => __('User login','pn'),
		'name' => 'user_login',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/		
		
	/*********/
	$lists['user']['user_email'] = array(
		'title' => __('E-mail','pn'),
		'name' => 'user_email',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/
			
	/*********/	
	$lists['user']['user_skype'] = array(
		'title' => __('User skype','pn'),
		'name' => 'user_skype',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/
	
	/*********/	
	$lists['user']['user_telegram'] = array(
		'title' => __('User telegram','pn'),
		'name' => 'user_telegram',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/	

	/*********/	
	$lists['user']['user_phone'] = array(
		'title' => __('Mobile phone no.','pn'),
		'name' => 'user_phone',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/
			
	/*********/	
	$lists['user']['user_passport'] = array(
		'title' => __('User passport number','pn'),
		'name' => 'user_passport',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/

	/*********/	
	$lists['user']['user_ip'] = array(
		'title' => __('User IP','pn'),
		'name' => 'user_ip',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/	

	$lists['user']['first_name'] = array(
		'title' => __('First name','pn'),
		'name' => 'first_name',
		'view' => 'input',
		'work' => 'input',
	); 
	
	$lists['user']['last_name'] = array(
		'title' => __('Last name','pn'),
		'name' => 'last_name',
		'view' => 'input',
		'work' => 'input',
	); 
	
	$lists['user']['second_name'] = array(
		'title' => __('Second name','pn'),
		'name' => 'second_name',
		'view' => 'input',
		'work' => 'input',
	); 

	/*********/		
	$lists['user']['ac1'] = array(
		'title' => __('Account To send','pn'),
		'name' => 'ac1',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/

	/*********/		
	$lists['user']['ac2'] = array(
		'title' => __('Account To receive','pn'),
		'name' => 'ac2',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/	
		
	/*********/		
	$lists['other']['to_account'] = array(
		'title' => __('Merchant account','pn'),
		'name' => 'to_account',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/		
		
	/*********/			
	$lists['other']['from_account'] = array(
		'title' => __('Automatic payout account','pn'),
		'name' => 'from_account',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/

	/*********/		
	$lists['other']['trans_in'] = array(
		'title' => __('Merchant transaction ID','pn'),
		'name' => 'trans_in',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/

	/*********/		
	$lists['other']['trans_out'] = array(
		'title' => __('Auto payout transaction ID','pn'),
		'name' => 'trans_out',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/	

	$lists['other']['pay_ac'] = array(
		'title' => __('Real account','pn'),
		'name' => 'pay_ac',
		'view' => 'input',
		'work' => 'input',
	);	

	/*********/
	if(is_ml()){
		$options = array(
			'0' => '--'. __('All','pn').'--',
		);
		$langs = get_langs_ml();
		foreach($langs as $key){
			$options[$key] = get_title_forkey($key);
		}
				
		$lists['other']['lan'] = array(
			'title' => __('Language','pn'),
			'name' => 'lan',
			'options' => $options,
			'view' => 'select',
			'work' => 'options',
		);
	}
	/*********/	
	
	$lists['system']['onematch'] = array(
		'title' => __('Filter orders if at least one match is found','pn'),
		'name' => 'onematch',
		'options' => array('0'=>__('No','pn'), '1'=> __('Yes','pn')),
		'view' => 'select',
		'work' => 'options',
	);	
	
	return $lists;
}

function get_bids_html($url){
global $wpdb, $premiumbox;	

 	$url = str_replace('/wp-admin/admin.php?','',$url);

	$temp = '';
	
	if(current_user_can('administrator') or current_user_can('pn_bids')){

		$pr = $wpdb->prefix;
		
		parse_str($url, $encode_pars_data);
		
		$pars_data = array();
		if(is_array($encode_pars_data)){
			foreach($encode_pars_data as $key => $val){
				$pars_data[$key] = $val;
			}
		}

		$where = '';
		$sql_operator = is_sql_operator($pars_data);
		
		$paystatus = intval(is_isset($pars_data,'paystatus'));
		if($paystatus == 1){ 
			$where = " {$sql_operator} {$pr}exchange_bids.status IN('new','coldnew','cancel','delete','techpay','error','scrpayerror','payouterror','my','coldpay','coldsuccess','success')";
		} elseif($paystatus == 2){
			$where = " {$sql_operator} {$pr}exchange_bids.status IN('payed','realpay','verify')";
		}
		
		$bidstatus = is_isset($pars_data,'bidstatus');
		if(is_array($bidstatus)){ 
			$where = " {$sql_operator} {$pr}exchange_bids.status = '1'";
			if(count($bidstatus) > 0){
				$in_bs = array();
				foreach($bidstatus as $bs){
					$bs = is_status_name($bs);
					if($bs){
						$in_bs[] = "'". $bs ."'";
					}
				}
				if(count($in_bs) > 0){
					$in_bs_join = join(',', $in_bs);
					$where = " {$sql_operator} {$pr}exchange_bids.status IN($in_bs_join)";
				}
			}
		} else {
			$bidstatus = is_status_name($bidstatus);
			if($bidstatus){
				$where = " {$sql_operator} {$pr}exchange_bids.status = '$bidstatus'";
			}
		}
		
		$startdate = is_pn_date(is_isset($pars_data,'startdate'));
		if($startdate){
			$startdate = get_pn_date($startdate,'Y-m-d 00:00');
			$where .= " {$sql_operator} {$pr}exchange_bids.edit_date >= '$startdate'";
		}
		$enddate = is_pn_date(is_isset($pars_data,'enddate'));
		if($enddate){
			$enddate = get_pn_date($enddate,'Y-m-d 00:00');
			$where .= " {$sql_operator} {$pr}exchange_bids.edit_date <= '$enddate'";
		}
		$min_sum1 = is_sum(is_isset($pars_data,'min_sum1'));
		if($min_sum1 > 0){
			$where .= " {$sql_operator} {$pr}exchange_bids.sum1dc >= ('$min_sum1' -0.0)";
		}
		$min_sum2 = is_sum(is_isset($pars_data,'min_sum2'));
		if($min_sum2 > 0){
			$where .= " {$sql_operator} {$pr}exchange_bids.sum2c >= ('$min_sum2' -0.0)";
		}
		$direction_id = intval(is_isset($pars_data,'direction_id'));
		if($direction_id > 0){
			$where .= " {$sql_operator} {$pr}exchange_bids.direction_id = '$direction_id'";
		}		
		$v1 = intval(is_isset($pars_data,'v1'));
		if($v1 > 0){
			$where .= " {$sql_operator} {$pr}exchange_bids.currency_id_give = '$v1'";
		}
		$v2 = intval(is_isset($pars_data,'v2'));
		if($v2 > 0){
			$where .= " {$sql_operator} {$pr}exchange_bids.currency_id_get = '$v2'";
		}
		$psys1 = intval(is_isset($pars_data,'psys1'));
		if($psys1 > 0){
			$where .= " {$sql_operator} {$pr}exchange_bids.psys_id_give = '$psys1'";
		}
		$psys2 = intval(is_isset($pars_data,'psys2'));
		if($psys2 > 0){
			$where .= " {$sql_operator} {$pr}exchange_bids.psys_id_get = '$psys2'";
		}
		$vtype1 = intval(is_isset($pars_data,'vtype1'));
		if($vtype1 > 0){
			$where .= " {$sql_operator} {$pr}exchange_bids.currency_code_id_give = '$vtype1'";
		}		
		$vtype2 = intval(is_isset($pars_data,'vtype2'));
		if($vtype2 > 0){
			$where .= " {$sql_operator} {$pr}exchange_bids.currency_code_id_get = '$vtype2'";
		}
		
		$iduser = intval(is_isset($pars_data,'iduser'));
		if($iduser > 0){
			$where .= " {$sql_operator} {$pr}exchange_bids.user_id='$iduser'";
		} else {	
			$user_login = is_user(is_isset($pars_data,'user_login'));
			if($user_login){
				$user_id = username_exists($user_login);
				if($user_id){
					$where .= " {$sql_operator} {$pr}exchange_bids.user_id='$user_id'";
				}
			}
		}
		
		$user_email = is_email(is_isset($pars_data,'user_email'));
		if($user_email){
			$where .= " {$sql_operator} {$pr}exchange_bids.user_email LIKE '%$user_email%'";
		}		
		$user_skype = pn_strip_input(pn_sfilter(is_isset($pars_data,'user_skype')));
		if($user_skype){
			$where .= " {$sql_operator} {$pr}exchange_bids.user_skype LIKE '%$user_skype%'";
		}
		$first_name = pn_strip_input(pn_sfilter(is_isset($pars_data,'first_name')));
		if($first_name){
			$where .= " {$sql_operator} {$pr}exchange_bids.first_name LIKE '%$first_name%'";
		}
		$last_name = pn_strip_input(pn_sfilter(is_isset($pars_data,'last_name')));
		if($last_name){
			$where .= " {$sql_operator} {$pr}exchange_bids.last_name LIKE '%$last_name%'";
		}
		$second_name = pn_strip_input(pn_sfilter(is_isset($pars_data,'second_name')));
		if($second_name){
			$where .= " {$sql_operator} {$pr}exchange_bids.second_name LIKE '%$second_name%'";
		}
		$user_telegram = pn_strip_input(pn_sfilter(is_isset($pars_data,'user_telegram')));
		if($user_telegram){
			$where .= " {$sql_operator} {$pr}exchange_bids.user_telegram LIKE '%$user_telegram%'";
		}		
		$user_phone = pn_strip_input(pn_sfilter(is_isset($pars_data,'user_phone')));
		if($user_phone){
			$where .= " {$sql_operator} {$pr}exchange_bids.user_phone LIKE '%$user_phone%'";
		}
		$user_passport = pn_strip_input(pn_sfilter(is_isset($pars_data,'user_passport')));
		if($user_passport){
			$where .= " {$sql_operator} {$pr}exchange_bids.user_passport LIKE '%$user_passport%'";
		}		
		$user_ip = pn_strip_input(pn_sfilter(is_isset($pars_data,'user_ip')));
		if($user_ip){
			$where .= " {$sql_operator} {$pr}exchange_bids.user_ip LIKE '%$user_ip%'";
		}		
		$lan = is_lang_attr(is_isset($pars_data,'lan'));
		if($lan){
			$where .= " {$sql_operator} {$pr}exchange_bids.bid_locale = '$lan'";
		}		
		$ac1 = pn_strip_input(pn_sfilter(is_isset($pars_data,'ac1')));
		if($ac1){
			$where .= " {$sql_operator} {$pr}exchange_bids.account_give LIKE '%$ac1%'";
		}		
		$ac2 = pn_strip_input(pn_sfilter(is_isset($pars_data,'ac2')));
		if($ac2){
			$where .= " {$sql_operator} {$pr}exchange_bids.account_get LIKE '%$ac2%'";
		}
		$to_account = pn_strip_input(pn_sfilter(is_isset($pars_data,'to_account')));
		if($to_account){
			$where .= " {$sql_operator} {$pr}exchange_bids.to_account LIKE '%$to_account%'";
		}
		$from_account = pn_strip_input(pn_sfilter(is_isset($pars_data,'from_account')));
		if($from_account){
			$where .= " {$sql_operator} {$pr}exchange_bids.from_account LIKE '%$from_account%'";
		}
		$trans_in = pn_strip_input(pn_sfilter(is_isset($pars_data,'trans_in')));
		if($trans_in){
			$where .= " {$sql_operator} {$pr}exchange_bids.trans_in = '$trans_in'";
		}
		$trans_out = pn_strip_input(pn_sfilter(is_isset($pars_data,'trans_out')));
		if($trans_out){
			$where .= " {$sql_operator} {$pr}exchange_bids.trans_out = '$trans_out'";
		}
		$pay_ac = pn_strip_input(pn_sfilter(is_isset($pars_data,'pay_ac')));
		if($pay_ac){
			$where .= " {$sql_operator} {$pr}exchange_bids.pay_ac = '$pay_ac'";
		}		
		
		$exceed_pay = intval(is_isset($pars_data,'exceed_pay'));  
		if($exceed_pay == 1){
			$where .= " {$sql_operator} {$pr}exchange_bids.exceed_pay = '0'";
		} elseif($exceed_pay == 2){	
			$where .= " {$sql_operator} {$pr}exchange_bids.exceed_pay = '1'";
		}		
		
		$bidid = intval(is_isset($pars_data,'bidid'));
		if($bidid > 0){
			$where = " {$sql_operator} {$pr}exchange_bids.id='$bidid'";
		}
		
		$where = apply_filters('where_request_sql_bids', $where, $pars_data);
		
		$paged = intval(is_isset($pars_data,'page_num'));
	
		$limit = list_bid_limit();
		
		$url_new = admin_url('admin.php?') . $url;

		$ui = wp_get_current_user();
		$mini_navi = intval(is_isset($ui, 'mini_navi'));
		if($mini_navi == 1){
			$count_bids = 0;
			$pagenavi = get_pagenavi_mini_calc($limit,$paged);
		} else {
			$count_bids = $wpdb->get_var("SELECT COUNT({$pr}exchange_bids.id) FROM {$pr}exchange_bids WHERE status != 'auto' $where");
			$pagenavi = get_pagenavi_calc($limit,$paged,$count_bids);
		}
		
		$select = apply_filters('pntable_select_sql_pn_bids', '');
			
		$sql = "SELECT *, {$pr}exchange_bids.id AS bid_id {$select} FROM {$pr}exchange_bids WHERE status != 'auto' $where ORDER BY id DESC LIMIT {$pagenavi['offset']}, {$pagenavi['limit']}";
			
		$statused = get_statusbids_for_admin();

		$datablock = '
		<div class="bids_datablock">
		';
			
			$data_blocks = array();
				
			if(current_user_can('administrator') or current_user_can('pn_bids_change')){
				$data_blocks['check'] = '
				<div class="bids_action_check">
					<input type="checkbox" name="" class="check_all" value="1" />
				</div>				
				';					
					
				$data_blocks['actions'] = '
				<div class="bids_action_select">
					<select name="action" class="sel_action" autocomplete="off">
						<option value="0">'. __('Actions','pn') .'</option>';
						foreach($statused as $key => $data){
							$style = '';
							$title = $data['title'];
							$background = trim($data['background']);
							$color = trim($data['color']);
							if($background){
								$style .= 'background: '.$background.';';
							}
							if($color){
								$style .= 'color: '.$color.';';
							}								
									
							$data_blocks['actions'] .= '<option value="'. $key .'" style="'. $style .'">'. $title .'</option>';
						}
					$data_blocks['actions'] .= '
					</select>
				</div>				
				';						
				$data_blocks['apply'] = '
				<input type="submit" name="submit" formtarget="_top" class="bids_action_apply js_bids_action" value="'. __('Apply','pn') .'" />
				';
			}
				
			$data_blocks['loader'] = '
			<div class="apply_loader"></div>
			';				
			$data_blocks['pagenavi'] = '
			<div class="bids_pagenavi">';
				if($mini_navi == 1){
					$data_blocks['pagenavi'] .= get_pagenavi_mini($pagenavi, $url_new);
				} else {
					$data_blocks['pagenavi'] .= get_pagenavi($pagenavi,'notstandart', $url_new);
				}
			$data_blocks['pagenavi'] .='
				<div class="premium_clear"></div>
			</div>
			';				
			if($mini_navi != 1){
				$data_blocks['total'] = '
				<div class="bids_datablock_count">
					<strong>'. __('Total orders','pn') .'</strong>: '. $count_bids .'
				</div>
				';					
			}
			$data_blocks = apply_filters('bids_datablock', $data_blocks);
			if(is_array($data_blocks)){
				foreach($data_blocks as $db){
					$datablock .= $db;
				}
			}
				

		$datablock .='	
			<div class="premium_clear"></div>
		</div>';
			
		$temp .= $datablock;
			
		$cl = '';
			
		if($count_bids > 0 or $mini_navi == 1){
			$v = get_currency_data();
			$cl = 'style="display: none;"';
			$items = $wpdb->get_results($sql);
			foreach($items as $item){ 
				$temp .= apply_filters('get_bid_item','',$item, $v);
			}
		}
			
		$temp .= '<div class="nobids" id="nobids" '. $cl .'>'. __('No orders','pn') .'</div>';			
			
		$temp .= $datablock; 
		
		if($premiumbox->is_debug_mode()){ 
			$temp .= '<div class="tech_url">'. $url_new .'<hr />'. $sql .'</div>';
		}
	}
	
	return $temp;
} 

add_filter('get_bid_item','def_get_bid_item',0,3);
function def_get_bid_item($temp, $item, $v){
global $wpdb, $premiumbox;

	if(!is_object($item)){ return __('No object','pn'); }

	$temp = '';
	
	$bid_id = $item->bid_id;
	
	$data_fs = get_bidfile_data($bid_id);
	
	$podmena = check_podmena($item, $data_fs);		
	
	$locale = pn_strip_input($item->bid_locale);		
	
	$dmetas = @unserialize($item->dmetas);
	$metas = @unserialize($item->metas);
	
	$temp = '
	<div class="one_bids" id="bidid_'. $bid_id .'">
		<div class="one_bids_wrap">';
		
			$temp .= '
			<div class="one_bids_abs">';
				
				$onebid_icon = array(
					'checkbox' => array(
						'type' => 'checkbox',
						'checked' => '',
						'disabled' => '',
					),
				);
				if($podmena == 1){
					$onebid_icon['substitution'] = array(
						'type' => 'label',
						'title' => __('Attention! User details were spoofed','pn'),
						'image' => $premiumbox->plugin_url.'images/podmena.gif',
					);
				}	
				$onebid_icon['bid_id'] = array(
					'type' => 'text',
					'title' => __('ID','pn'),
					'label' => __('ID','pn') .': [id]',
					'link' => '[bid_site_url]',
					'link_target' => '_blank',
				);				
				if(is_ml()){
					$onebid_icon['language'] = array(
						'type' => 'label',
						'title' => __('Language','pn') .': '. get_title_forkey($locale),
						'image' => get_lang_icon($locale),
					);	
				}
				$onebid_icon = apply_filters('onebid_icons', $onebid_icon, $item, $data_fs, $v);
				$onebid_icon = (array)$onebid_icon;
				
				$temp .= get_onebid_thead_temp($onebid_icon, $item, $data_fs, $v);
			
				$temp .= '
				<div class="premium_clear"></div>
			</div>
				<div class="premium_clear"></div>
			';	 			
			
			$temp .= '
			<div class="one_bids_ins">
				<div class="abs_line al1"></div>
				<div class="abs_line al2"></div>
				<div class="abs_line al3"></div>			
			';
			
				$temp .= '
				<div class="bids_col">';
				
					$cols = array();
					$cols['status'] = array(
						'type' => 'text',
						'title' => '',
						'label' => '[visible_status]',
					);
					$cols['rate'] = array(
						'type' => 'text',
						'title' => __('Rate','pn'),
						'label' => '[course_give] [currency_code_give] = [course_get] [currency_code_get]',
						'link' => admin_url('admin.php?page=pn_add_directions&item_id=[direction_id]'),
						'link_target' => '_blank',
					);	
					$cols['createdate'] = array(
						'type' => 'text',
						'title' => __('Creation date','pn'),
						'label' => '[createdate]',
					);
					$cols['editdate'] = array(
						'type' => 'text',
						'title' => __('Modification date','pn'),
						'label' => '[editdate]',
					);	
					$cols['to_account'] = array(
						'type' => 'text',
						'title' => __('Merchant account','pn'),
						'label' => '[to_account]',
					);	
					$cols['from_account'] = array(
						'type' => 'text',
						'title' => __('Automatic payout account','pn'),
						'label' => '[from_account]',
					);						
					$cols['trans_in'] = array(
						'type' => 'text',
						'title' => __('Merchant transaction ID','pn'),
						'label' => '[trans_in]',
					);						
					$cols['trans_out'] = array(
						'type' => 'text',
						'title' => __('Auto payout transaction ID','pn'),
						'label' => '[trans_out]',
					);						
					$cols['pay_sum'] = array(
						'type' => 'text',
						'title' => __('Real amount to pay','pn'),
						'label' => '[pay_sum]',
					);
					$cols['pay_ac'] = array(
						'type' => 'text',
						'title' => __('Real account','pn'),
						'label' => '[pay_ac]',
					);						
					$temp .= get_onebid_col_temp($cols, 'onebid_col1', $item, $data_fs, $v);										
					
					$temp .='
					<div class="premium_clear"></div>
				</div>';

				$temp .='
				<div class="bids_col">';
				
					$cols = array();
					$cols['currency1'] = array(
						'type' => 'text',
						'title' => __('Send','pn'),
						'label' => '[currency_give] [currency_code_give]',
					);	
					$cols['sum1dc'] = array(
						'type' => 'text',
						'title' => __('Amount (with add. fees)','pn'),
						'label' => '[sum1dc] [currency_code_give]',
						'class' => 'btbg_green',
					);
					$cols['sum1c'] = array(
						'type' => 'text',
						'title' => __('Amount (with add. fees and PS fees)','pn'),
						'label' => '[sum1c] [currency_code_give]',
					);	
					$cols['account_give'] = array(
						'type' => 'text',
						'title' => __('From account','pn'),
						'label' => '[account_give_visible]',
						'class' => 'btbg_fiol',
					);
					$meta_data = '';
					if(isset($dmetas[1]) and is_array($dmetas[1])){
						foreach($dmetas[1] as $value){			
							$title = pn_strip_input(ctv_ml(is_isset($value,'title')));
							$data = pn_strip_input(is_isset($value,'data'));
							if($data){
								$meta_data .= '<div class="bids_text"><span class="bt_fix"><span class="bt">'. $title .':</span></span> <span class="onebid_item clpb_item" data-clipboard-text="'. $data .'">'. $data .'</span></div>';
							}
						}
					}		
					$cols['meta_data'] = array(
						'type' => 'html',
						'html' => $meta_data,
					);					
					$temp .= get_onebid_col_temp($cols, 'onebid_col2', $item, $data_fs, $v);						
						
					$temp .='
					<div class="premium_clear"></div>
				</div>';
				
				$temp .='
				<div class="bids_col">';
				
					$cols = array();
					$cols['currency2'] = array(
						'type' => 'text',
						'title' => __('Receive','pn'),
						'label' => '[currency_get] [currency_code_get]',
					);	
					$cols['sum2dc'] = array(
						'type' => 'text',
						'title' => __('Amount (with add. fees)','pn'),
						'label' => '[sum2dc] [currency_code_get]',
						'class' => 'btbg_green',
					);
					$cols['sum2c'] = array(
						'type' => 'text',
						'title' => __('Amount (with add. fees and PS fees)','pn'),
						'label' => '[sum2c] [currency_code_get]',
					);
					$cols['account_get'] = array(
						'type' => 'text',
						'title' => __('Into account','pn'),
						'label' => '[account_get_visible]',
						'class' => 'btbg_fiol',
					);
					$meta_data = '';
					if(isset($dmetas[2]) and is_array($dmetas[2])){
						foreach($dmetas[2] as $value){			
							$title = pn_strip_input(ctv_ml(is_isset($value,'title')));
							$data = pn_strip_input(is_isset($value,'data'));
							if($data){
								$meta_data .= '<div class="bids_text"><span class="bt_fix"><span class="bt">'. $title .':</span></span> <span class="onebid_item clpb_item" data-clipboard-text="'. $data .'">'. $data .'</span></div>';
							}
						}
					}		
					$cols['meta_data'] = array(
						'type' => 'html',
						'html' => $meta_data,
					);
					$temp .= get_onebid_col_temp($cols, 'onebid_col3', $item, $data_fs, $v);				
						
					$temp .='
					<div class="premium_clear"></div>
				</div>';
				
				$temp .='
				<div class="bids_col_bg four"></div>
				<div class="bids_col four">
				';				
					$cols = array();
					
					$dir_fields = array(
						'last_name' => __('Last name','pn'),
						'first_name' => __('First name','pn'),
						'second_name' => __('Second name','pn'),
						'user_phone' => __('Mobile phone no.','pn'),
						'user_skype' => __('Skype','pn'),
						'user_email' => __('E-mail','pn'),
						'user_passport' => __('Passport number','pn'),
						'user_telegram' => __('Telegram','pn'),
					);
					foreach($dir_fields as $dir_field_key => $dir_field_title){
						$cols[$dir_field_key] = array(
							'type' => 'text',
							'title' => $dir_field_title,
							'label' => '['. $dir_field_key .']',
						);						
					}																
					
					$meta_data = '';
					if(is_array($metas)){
						foreach($metas as $value){		
							$title = pn_strip_input(ctv_ml(is_isset($value,'title')));
							$data = pn_strip_input(is_isset($value,'data'));
							if($data and !isset($value['auto'])){
								$meta_data .= '<div class="bids_text"><span class="bt_fix"><span class="bt">'. $title .':</span></span> <span class="onebid_item clpb_item" data-clipboard-text="'. $data .'">'. $data .'</span></div>';		
							}	
						}						
					}	
					$cols['meta_data'] = array(
						'type' => 'html',
						'html' => $meta_data,
					);					
					$cols['user_ip'] = array(
						'type' => 'text',
						'title' => __('User IP','pn'),
						'label' => '[user_ip]',
					);	
					$temp .= get_onebid_col_temp($cols, 'onebid_col4', $item, $data_fs, $v);
			
				$temp .='	
					<div class="premium_clear"></div>
				</div>
					<div class="premium_clear"></div>
				';		

				$temp .= '
				<div class="one_bids_info js_info_block">
					<div class="bi_block">
				';
				
						$cols = array();
						$cols['title'] = array(
							'type' => 'html',
							'html' => '<div class="bi_bigtitle">'. __('Information','pn') .'</div>',
						);					
						$cols['user_or_guest'] = array(
							'type' => 'text',
							'title' => __('User ID','pn'),
							'label' => '[user_or_guest]',
						);
						$cols['user_ip'] = array(
							'type' => 'text',
							'title' => __('User IP','pn'),
							'label' => '[user_ip]',
						);
						$cols['user_discount'] = array(
							'type' => 'text',
							'title' => __('User discount (%)','pn'),
							'label' => '[user_discount]%',
						);
						$cols['user_discount_sum'] = array(
							'type' => 'text',
							'title' => __('User discount (amount)','pn'),
							'label' => '[user_discount_sum] [currency_code_get]',
						);
						$cols['profit'] = array(
							'type' => 'text',
							'title' => __('Profit','pn'),
							'label' => '[profit] [cur_vtype]',
						);
						$cols['exsum'] = array(
							'type' => 'text',
							'title' => __('Amount in internal currency','pn'),
							'label' => '[exsum] [cur_vtype]',
						);					
						$temp .= get_onebid_hidecol_temp($cols, 'onebid_hidecol1', $item, $data_fs, $v);	
					
					$temp .= '
					</div>	
					<div class="bi_block">';
					
						$cols = array();
						$cols['title'] = array(
							'type' => 'html',
							'html' => '<div class="bi_bigtitle">'. __('Information "Sent"','pn') .'</div>',
						);					
						$cols['sum1'] = array(
							'type' => 'text',
							'title' => __('Amount To send','pn'),
							'label' => '[sum1] [currency_code_give]',
						);
						$cols['dop_com1'] = array(
							'type' => 'text',
							'title' => __('Add. fees amount','pn'),
							'label' => '[dop_com1] [currency_code_give]',
						);
						$cols['sum1dc'] = array(
							'type' => 'text',
							'title' => __('Amount To send (add. fees)','pn'),
							'label' => '[sum1dc] [currency_code_give]',
						);
						$cols['com_ps1'] = array(
							'type' => 'text',
							'title' => __('PS fees amount','pn'),
							'label' => '[com_ps1] [currency_code_give]',
						);
						$cols['sum1c'] = array(
							'type' => 'text',
							'title' => __('Amount To send (add.fee and PS fee)','pn'),
							'label' => '[sum1c] [currency_code_give]',
						);
						$cols['sum1r'] = array(
							'type' => 'text',
							'title' => __('Amount for reserve','pn'),
							'label' => '[sum1r] [currency_code_give]',
						);						
						$temp .= get_onebid_hidecol_temp($cols, 'onebid_hidecol2', $item, $data_fs, $v);						
							
					$temp .= '
					</div>	
					<div class="bi_block">';
					
						$cols = array();
						$cols['title'] = array(
							'type' => 'html',
							'html' => '<div class="bi_bigtitle">'. __('Information "Received"','pn') .'</div>',
						);					
						$cols['sum2t'] = array(
							'type' => 'text',
							'title' => __('Amount at the Exchange Rate','pn'),
							'label' => '[sum2t] [currency_code_get]',
						);
						$cols['sum2'] = array(
							'type' => 'text',
							'title' => __('Amount (discount included)','pn'),
							'label' => '[sum2] [currency_code_get]',
						);	
						$cols['dop_com2'] = array(
							'type' => 'text',
							'title' => __('Add. fees amount','pn'),
							'label' => '[dop_com2] [currency_code_get]',
						);
						$cols['sum2dc'] = array(
							'type' => 'text',
							'title' => __('Amount To receive (add. fees)','pn'),
							'label' => '[sum2dc] [currency_code_get]',
						);
						$cols['com_ps2'] = array(
							'type' => 'text',
							'title' => __('PS fees amount','pn'),
							'label' => '[com_ps2] [currency_code_get]',
						);
						$cols['sum2c'] = array(
							'type' => 'text',
							'title' => __('Amount To receive (add.fees and PS fees)','pn'),
							'label' => '[sum2c] [currency_code_get]',
						);
						$cols['sum2r'] = array(
							'type' => 'text',
							'title' => __('Amount for reserve','pn'),
							'label' => '[sum2r] [currency_code_get]',
						);						
						$temp .= get_onebid_hidecol_temp($cols, 'onebid_hidecol3', $item, $data_fs, $v);					
								
					$temp .= '
					</div>	
					<div class="bi_block">';

					$temp .= get_onebid_hidecol_temp(array(), 'onebid_hidecol4', $item, $data_fs, $v);
					
				$temp .='
					</div>
						<div class="premium_clear"></div>
				</div>';
				
			$temp .= '
			</div>
				<div class="premium_clear"></div>
			';
			
			$temp .= '
			<div class="action_bids_abs">';
			
				$onebid_actions = array();
				$onebid_actions['info'] = array(
					'type' => 'link',
					'title' => __('Information','pn'),
					'label' => __('info','pn'),
					'link' => '#',
					'link_target' => '',
					'link_class' => 'js_info',
				);
				
				if($item->user_id){
					$relat_link = admin_url('admin.php?page=pn_bids&iduser='.$item->user_id);
				} else {
					$relat_link = admin_url('admin.php?page=pn_bids&user_email='. pn_strip_input($item->user_email) .'&user_skype='. pn_strip_input($item->user_skype) .'&user_phone='. pn_strip_input($item->user_phone));
				}				
				
				$onebid_actions['relative'] = array(
					'type' => 'link',
					'title' => __('Similar exchanges','pn'),
					'label' => __('Similar','pn'),
					'link' => $relat_link,
					'link_target' => '_blank',
					'link_class' => '',
				);				
				
				$onebid_actions = apply_filters('onebid_actions', $onebid_actions, $item, $data_fs, $v);
				$onebid_actions = (array)$onebid_actions;
	
				$temp .= get_onebid_thead_temp($onebid_actions, $item, $data_fs, $v);			
				
				$temp .= '
					<div class="premium_clear"></div>
			</div>';
			
	$temp .= '
		</div>
	</div>';
	
	return $temp;
}

function get_onebid_thead_temp($actions, $item, $data_fs, $v){
	$temp = '';
	
	foreach($actions as $data){
		$type = trim(is_isset($data,'type'));
		if($type == 'checkbox'){
			$checked = trim(is_isset($data,'checked'));
			$ch = '';
			if($checked == 'true'){ $ch = 'checked="checked"'; }
			$disabled = trim(is_isset($data,'disabled'));
			$di = '';
			if($disabled == 'true'){ $di = 'disabled="disabled"'; }
			$temp .= '
			<div class="bids_checkbox">
				<input type="checkbox" name="id[]" '. $ch .' '. $di .' class="check_one" value="'. $item->id .'" />
			</div>
			';
		} elseif($type == 'label'){
			$temp .= '
			<div class="bids_label" title="'. is_isset($data,'title') .'">
				<div class="bids_label_img">
					<img src="'. is_isset($data,'image') .'" alt="" />
				</div>
			</div>
			';		
		} elseif($type == 'text'){
			$link_target = trim(is_isset($data,'link_target')); if($link_target != '_blank'){ $link_target = '_self'; }
			$link = strip_tags(get_bids_replace_text(is_isset($data,'link'), $item, $data_fs, $v));
			$label = get_bids_replace_text(is_isset($data,'label'), $item, $data_fs, $v);
			$title = strip_tags(get_bids_replace_text(is_isset($data,'title'), $item, $data_fs, $v));
			$temp .= '<div class="bids_label_txt" title="'. $title .'">';
				if($link){
					$temp .= '<a href="'. $link .'" target="'. $link_target .'" rel="noreferrer noopener">';
				}
				$temp .= $label;
				if($link){
					$temp .= '</a>';
				}						
			$temp .= '</div>';
		} elseif($type == 'link'){	
			$link_class = trim(is_isset($data,'link_class'));
			$link_target = trim(is_isset($data,'link_target')); if($link_target != '_blank'){ $link_target = '_self'; }
			$link = strip_tags(get_bids_replace_text(is_isset($data,'link'), $item, $data_fs, $v));
			if($link){
				$label = get_bids_replace_text(is_isset($data,'label'), $item, $data_fs, $v);
				$title = strip_tags(get_bids_replace_text(is_isset($data,'title'), $item, $data_fs, $v));			
				$temp .= '<a href="'. $link .'" target="'. $link_target .'" rel="noreferrer noopener" title="'. $title .'" class="one_action_bid_link '. $link_class .'">';		
				$temp .= $label;
				$temp .= '</a>';		
			}			
		} elseif($type == 'html'){
			$temp .= is_isset($data,'html');
		}
	}	
	return $temp;
}

function get_onebid_col_temp($actions, $filter, $item, $data_fs, $v){
	$temp = '';
	
	$actions = apply_filters($filter, $actions, $item, $data_fs, $v);
	$actions = (array)$actions;	
	
	foreach($actions as $data){
		$type = trim(is_isset($data,'type'));
		if($type == 'text'){
			$class = trim(is_isset($data,'class'));
			$link_target = trim(is_isset($data,'link_target')); if($link_target != '_blank'){ $link_target = '_self'; }
			$link = strip_tags(get_bids_replace_text(is_isset($data,'link'), $item, $data_fs, $v));
			$label = get_bids_replace_text(is_isset($data,'label'), $item, $data_fs, $v);
			if(strip_tags($label)){
				$title = strip_tags(get_bids_replace_text(is_isset($data,'title'), $item, $data_fs, $v));
				$temp .= '<div class="bids_text '. $class .'">';
					if($title){
						$temp .= '<span class="bt_fix"><span class="bt">'. $title .':</span></span> ';
					}
					if($link){
						$temp .= '<a href="'. $link .'" target="'. $link_target .'" rel="noreferrer noopener">';
					}
						$temp .= $label;
					if($link){
						$temp .= '</a>';
					}						
				$temp .= '</div>';
			}
		} elseif($type == 'html'){
			$temp .= is_isset($data,'html');
		}
	}		
	
	return $temp;
}

function get_onebid_hidecol_temp($actions, $filter, $item, $data_fs, $v){
	$temp = '';
	
	$actions = apply_filters($filter, $actions, $item, $data_fs, $v);
	$actions = (array)$actions;	
	
	foreach($actions as $data){
		$type = trim(is_isset($data,'type'));
		if($type == 'text'){
			$link_target = trim(is_isset($data,'link_target')); if($link_target != '_blank'){ $link_target = '_self'; }
			$link = strip_tags(get_bids_replace_text(is_isset($data,'link'), $item, $data_fs, $v));
			$label = get_bids_replace_text(is_isset($data,'label'), $item, $data_fs, $v);
			$title = strip_tags(get_bids_replace_text(is_isset($data,'title'), $item, $data_fs, $v));
			$temp .= '<div class="bi_title">'. $title .'</div><div class="bi_div">';
				if($link){
					$temp .= '<a href="'. $link .'" target="'. $link_target .'" rel="noreferrer noopener">';
				}
					$temp .= $label;
				if($link){
					$temp .= '</a>';
				}						
			$temp .= '</div><div class="premium_clear"></div>';
		} elseif($type == 'html'){
			$temp .= is_isset($data,'html');
		}
	}	
	
	return $temp;
}

function get_bids_replace_text($text, $item, $data_fs, $v){
	
	if(strstr($text, '[id]')){
		$text = str_replace('[id]', '<span class="onebid_item item_id bid_clpb_item" data-clipboard-text="' . $item->id . '">' . $item->id . '</span>', $text);
	}
	if(strstr($text, '[bid_site_url]')){
		$text = str_replace('[bid_site_url]', '<span class="onebid_item item_bid_site_url bid_clpb_item" data-clipboard-text="' . get_bids_url($item->hashed) . '">' . get_bids_url($item->hashed) . '</span>',$text);
	}	
	if(strstr($text, '[bid_admin_url]')){
		$text = str_replace('[bid_admin_url]', '<span class="onebid_item item_bid_admin_url bid_clpb_item" data-clipboard-text="' . admin_url('admin.php?page=pn_bids&bidid='. $item->id) . '">' . admin_url('admin.php?page=pn_bids&bidid='. $item->id) . '</span>',$text);
	}	
	if(strstr($text, '[bid_hash]')){
		$text = str_replace('[bid_hash]', '<span class="onebid_item item_bid_hash bid_clpb_item" data-clipboard-text="' . $item->hashed . '">' . $item->hashed . '</span>',$text);
	}
	if(strstr($text, '[user_id]')){
		$text = str_replace('[user_id]', '<span class="onebid_item item_user_id bid_clpb_item" data-clipboard-text="' . $item->user_id . '">' . $item->user_id . '</span>',$text);
	}
	if(strstr($text, '[user_login]')){
		$text = str_replace('[user_login]', '<span class="onebid_item item_user_login bid_clpb_item" data-clipboard-text="' . is_user($item->user_login) . '">' . is_user($item->user_login) . '</span>',$text);
	}	
	if(strstr($text, '[user_or_guest]')){
		$user_id = $item->user_id;
		if($user_id){ $user = $user_id; } else { $user = __('Guest','pn'); }
		$text = str_replace('[user_or_guest]', '<span class="onebid_item item_user_or_guest bid_clpb_item" data-clipboard-text="' . $user . '">' . $user . '</span>',$text);
	}	
	if(strstr($text, '[user_discount]')){
		$text = str_replace('[user_discount]', '<span class="onebid_item item_user_discount bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->user_discount) . '">' . check_podmena_db('user_discount', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[user_discount_sum]')){
		$text = str_replace('[user_discount_sum]', '<span class="onebid_item item_user_discount_sum bid_clpb_item" data-clipboard-text="' . is_sum($item->user_discount_sum) . '">' . check_podmena_db('user_discount_sum', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[profit]')){
		$text = str_replace('[profit]', '<span class="onebid_item item_profit bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->profit) . '">' . check_podmena_db('profit', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[exsum]')){
		$text = str_replace('[exsum]', '<span class="onebid_item item_exsum bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->exsum) . '">' . check_podmena_db('exsum', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[cur_vtype]')){
		$text = str_replace('[cur_vtype]', '<span class="onebid_item item_cur_vtype bid_clpb_item" data-clipboard-text="' . cur_type() . '">' . cur_type() . '</span>',$text);
	}		
	if(strstr($text, '[currency_code_give]')){
		$text = str_replace('[currency_code_give]', '<span class="onebid_item item_currency_code_give bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->currency_code_give) . '">' . pn_strip_input($item->currency_code_give) . '</span>',$text);
	}
	if(strstr($text, '[currency_code_get]')){
		$text = str_replace('[currency_code_get]', '<span class="onebid_item item_currency_code_get bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->currency_code_get) . '">' . pn_strip_input($item->currency_code_get) . '</span>',$text);
	}	
	if(strstr($text, '[sum1]')){
		$text = str_replace('[sum1]', '<span class="onebid_item item_sum1 bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->sum1) . '">' . check_podmena_db('sum1', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[dop_com1]')){
		$text = str_replace('[dop_com1]', '<span class="onebid_item item_dop_com1 bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->dop_com1) . '">' . check_podmena_db('dop_com1', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[sum1dc]')){
		$text = str_replace('[sum1dc]', '<span class="onebid_item item_sum1dc bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->sum1dc) . '">' . check_podmena_db('sum1dc', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[com_ps1]')){
		$text = str_replace('[com_ps1]', '<span class="onebid_item item_com_ps1 bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->com_ps1) . '">' . check_podmena_db('com_ps1', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[sum1c]')){
		$text = str_replace('[sum1c]', '<span class="onebid_item item_sum1c bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->sum1c) . '">' . check_podmena_db('sum1c', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[sum1r]')){
		$text = str_replace('[sum1r]', '<span class="onebid_item item_sum1r bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->sum1r) . '">' . check_podmena_db('sum1r', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[sum2t]')){
		$text = str_replace('[sum2t]', '<span class="onebid_item item_sum2t bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->sum2t) . '">' . check_podmena_db('sum2t', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[sum2]')){
		$text = str_replace('[sum2]', '<span class="onebid_item item_sum2 bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->sum2) . '">' . check_podmena_db('sum2', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[dop_com2]')){
		$text = str_replace('[dop_com2]', '<span class="onebid_item item_dop_com2 bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->dop_com2) . '">' . check_podmena_db('dop_com2', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[sum2dc]')){
		$text = str_replace('[sum2dc]', '<span class="onebid_item item_sum2dc bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->sum2dc) . '">' . check_podmena_db('sum2dc', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[com_ps2]')){
		$text = str_replace('[com_ps2]', '<span class="onebid_item item_com_ps2 bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->com_ps2) . '">' . check_podmena_db('com_ps2', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[sum2c]')){
		$text = str_replace('[sum2c]', '<span class="onebid_item item_sum2c bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->sum2c) . '">' . check_podmena_db('sum2c', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[sum2r]')){
		$text = str_replace('[sum2r]', '<span class="onebid_item item_sum2r bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->sum2r) . '">' . check_podmena_db('sum2r', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[user_email]')){
		$text = str_replace('[user_email]', '<span class="onebid_item item_user_email bid_clpb_item" data-clipboard-text="' . is_email($item->user_email) . '">' . check_podmena_db('user_email', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[user_skype]')){
		$text = str_replace('[user_skype]', '<span class="onebid_item item_user_skype bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->user_skype) . '">' . check_podmena_db('user_skype', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[user_telegram]')){
		$text = str_replace('[user_telegram]', '<span class="onebid_item item_user_telegram bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->user_telegram) . '">' . check_podmena_db('user_telegram', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[user_phone]')){
		$text = str_replace('[user_phone]', '<span class="onebid_item item_user_phone bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->user_phone) . '">' . check_podmena_db('user_phone', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[user_ip]')){
		$text = str_replace('[user_ip]', '<span class="onebid_item item_user_ip bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->user_ip) . '">' . pn_strip_input($item->user_ip) . '</span>',$text);
	}
	if(strstr($text, '[user_passport]')){
		$text = str_replace('[user_passport]', '<span class="onebid_item item_user_passport bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->user_passport) . '">' . check_podmena_db('user_passport', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[last_name]')){
		$text = str_replace('[last_name]', '<span class="onebid_item item_last_name bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->last_name) . '">' . check_podmena_db('last_name', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[first_name]')){
		$text = str_replace('[first_name]', '<span class="onebid_item item_first_name bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->first_name) . '">' . check_podmena_db('first_name', $item, $data_fs) . '</span>',$text);
	}						
	if(strstr($text, '[second_name]')){
		$text = str_replace('[second_name]', '<span class="onebid_item item_second_name bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->second_name) . '">' . check_podmena_db('second_name', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[account_give]')){
		$text = str_replace('[account_give]', '<span class="onebid_item item_account_give bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->account_give) . '">' . check_podmena_db('account_give', $item, $data_fs, $v) . '</span>',$text);
	}
	if(strstr($text, '[account_get]')){
		$text = str_replace('[account_get]', '<span class="onebid_item item_account_get bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->account_get) . '">' . check_podmena_db('account_get', $item, $data_fs, $v) . '</span>',$text);
	}	
	if(strstr($text, '[status]')){
		$text = str_replace('[status]', '<span class="onebid_item item_status bid_clpb_item" data-clipboard-text="' . get_bid_status($item->status) . '">' . get_bid_status($item->status) . '</span><div class="premium_clear"></div>',$text);
	}
	if(strstr($text, '[visible_status]')){
		$text = str_replace('[visible_status]', '<span class="stname st_'. is_status_name($item->status) .'">'. get_bid_status($item->status) .'</span><div class="premium_clear"></div>',$text);
	}	
	if(strstr($text, '[course_give]')){
		$text = str_replace('[course_give]', '<span class="onebid_item item_curs1 bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->course_give) . '">' . pn_strip_input($item->course_give) . '</span>',$text);
	}	
	if(strstr($text, '[course_get]')){
		$text = str_replace('[course_get]', '<span class="onebid_item item_curs2 bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->course_get) . '">' . pn_strip_input($item->course_get) . '</span>',$text);
	}
	if(strstr($text, '[direction_id]')){
		$text = str_replace('[direction_id]', '<span class="onebid_item item_naps_id bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->direction_id) . '">' . pn_strip_input($item->direction_id) . '</span>',$text);
	}	
	if(strstr($text, '[createdate]')){
		$text = str_replace('[createdate]', '<span class="onebid_item item_createdate bid_clpb_item" data-clipboard-text="' . get_pn_time($item->create_date,'d.m.Y H:i:s') . '">' . get_pn_time($item->create_date,'d.m.Y H:i:s') . '</span>',$text);
	}
	if(strstr($text, '[editdate]')){
		$text = str_replace('[editdate]', '<span class="onebid_item item_editdate bid_clpb_item" data-clipboard-text="' . get_pn_time($item->edit_date,'d.m.Y H:i:s') . '">' . get_pn_time($item->edit_date,'d.m.Y H:i:s') . '</span>',$text);
	}	
	if(strstr($text, '[from_account]')){
		$text = str_replace('[from_account]', '<span class="onebid_item item_soschet bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->from_account) . '">' . check_podmena_db('from_account', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[to_account]')){
		$text = str_replace('[to_account]', '<span class="onebid_item item_naschet bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->to_account) . '">' . check_podmena_db('to_account', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[trans_in]')){
		$text = str_replace('[trans_in]', '<span class="onebid_item item_trans_in bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->trans_in) . '">' . check_podmena_db('trans_in', $item, $data_fs) . '</span>',$text);
	}	
	if(strstr($text, '[trans_out]')){		
		$text = str_replace('[trans_out]', '<span class="onebid_item item_trans_out bid_clpb_item" data-clipboard-text="' . pn_strip_input($item->trans_out) . '">' . check_podmena_db('trans_out', $item, $data_fs) . '</span>',$text);
	}
	if(strstr($text, '[pay_ac]')){
		$pay_ac = pn_strip_input($item->pay_ac);	
		$text = str_replace('[pay_ac]', '<span class="onebid_item item_pay_ac bid_clpb_item" data-clipboard-text="' . $pay_ac . '">' . $pay_ac . '</span>',$text);
	}
	if(strstr($text, '[pay_sum]')){
		$pay_sum = pn_strip_input($item->pay_sum);
		$cl = '';
		$title = '';
		if($item->exceed_pay){
			$cl = 'bpur_dash';
			$title = __('Overpayment','pn');
		}
		$text = str_replace('[pay_sum]', '<span class="onebid_item item_pay_sum bid_clpb_item '. $cl .'" data-clipboard-text="' . $pay_sum . '" title="'. $title .'">' . $pay_sum . '</span>',$text);
	}		
	if(strstr($text, '[currency_give]')){
		$text = str_replace('[currency_give]', '<span class="onebid_item item_currency_give bid_clpb_item" data-clipboard-text="' . pn_strip_input(ctv_ml($item->psys_give)) . '">' . pn_strip_input(ctv_ml($item->psys_give)) . '</span>',$text);
	}
	if(strstr($text, '[currency_get]')){
		$text = str_replace('[currency_get]', '<span class="onebid_item item_currency_get bid_clpb_item" data-clipboard-text="' . pn_strip_input(ctv_ml($item->psys_get)) . '">' . pn_strip_input(ctv_ml($item->psys_get)) . '</span>',$text);
	}	
	if(strstr($text, '[account_give_visible]')){
		$text = str_replace('[account_give_visible]', '<span class="onebid_item item_account_give_visible bid_clpb_item" data-clipboard-text="'. pn_strip_input($item->account_give) .'">' . check_podmena_db('account_give', $item, $data_fs, $v) . '</span>',$text);
	}
	if(strstr($text, '[account_get_visible]')){
		$text = str_replace('[account_get_visible]', '<span class="onebid_item item_account_get_visible bid_clpb_item" data-clipboard-text="'. pn_strip_input($item->account_get) .'">' . check_podmena_db('account_get', $item, $data_fs, $v) . '</span>',$text);
	}	
	
	$text = apply_filters('get_bids_replace_text', $text, $item, $data_fs, $v);
	return $text;
}

function check_podmena_db($key, $item, $data_fs, $v=''){
	
	$value = $value_or = pn_strip_input(is_isset($item, $key));
	$value_fs = pn_strip_input(is_isset($data_fs, $key));
	$bid_hashkey = bid_hashkey();
	
	$hashdata = @unserialize($item->hashdata);
	if(!is_array($hashdata)){ $hashdata = array(); }

	if(in_array($key, $bid_hashkey)){
		$hash = pn_strip_input(is_isset($hashdata, $key));
		if(!is_pn_crypt($hash, $value)){
			if(strlen($value_fs) < 1){ $value_fs = __('missing','pn'); }
				
			return '<span class="bred_dash" title="'. __('Data do not match','pn') .'">'. $value .'</span> (<span class="bgreen">'. __('Original','pn') .': '. $value_fs .'</span>)';
		}	
	}			
		
	return $value;
}