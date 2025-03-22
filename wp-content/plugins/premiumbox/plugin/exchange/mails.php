<?php
if( !defined( 'ABSPATH')){ exit(); }
	
add_filter('list_admin_notify','list_admin_notify_bids');
function list_admin_notify_bids($places_admin){
	$bid_status_list = apply_filters('bid_status_list',array());
	$bid_status_list['realdelete'] = __('Completely deleted order','pn');	
	foreach($bid_status_list as $k => $v){
		$t = $v;
		if($k != 'realdelete'){ $t = sprintf(__('Status of order is "%s"','pn'), $v); }
		$places_admin[$k . '_bids1'] = $t;
	}
	return $places_admin;
}

add_filter('list_user_notify','list_user_notify_bids');
function list_user_notify_bids($places_admin){
	$bid_status_list = apply_filters('bid_status_list',array());
	$bid_status_list['realdelete'] = __('Completely deleted order','pn');	
	foreach($bid_status_list as $k => $v){
		$t = $v;
		if($k != 'realdelete'){ $t = sprintf(__('Status of order is "%s"','pn'), $v); }
		$places_admin[$k . '_bids2'] = $t;
	}
	return $places_admin;
}	

add_action('init', 'def_bid_mailtemp_init');
function def_bid_mailtemp_init(){
	$bid_status_list = apply_filters('bid_status_list',array());
	$bid_status_list['realdelete'] = __('Completely deleted order','pn');
	foreach($bid_status_list as $k => $v){
		add_filter('list_notify_tags_'. $k .'_bids1','def_mailtemp_tags_bids');
		add_filter('list_notify_tags_'. $k .'_bids2','def_mailtemp_tags_bids');
	}
}

function def_mailtemp_tags_bids($tags){
	$tags['exchange_id'] = array(
		'title' => __('Order ID','pn'),
		'start' => '[exchange_id]',
	);	
	$tags['create_date'] = array(
		'title' => __('Creation date','pn'),
		'start' => '[create_date]',
	);
	$tags['edit_date'] = array(
		'title' => __('Edit date','pn'),
		'start' => '[edit_date]',
	);
	$tags['course_give'] = array(
		'title' => __('Rate Send','pn'),
		'start' => '[course_give]',
	);
	$tags['course_get'] = array(
		'title' => __('Rate Receive','pn'),
		'start' => '[course_get]',
	);
	$tags['psys_give'] = array(
		'title' => __('PS name Send','pn'),
		'start' => '[psys_give]',
	);
	$tags['psys_get'] = array(
		'title' => __('PS name Receive','pn'),
		'start' => '[psys_get]',
	);	
	$tags['currency_code_give'] = array(
		'title' => __('Currency code Send','pn'),
		'start' => '[currency_code_give]',
	);
	$tags['currency_code_get'] = array(
		'title' => __('Currency code Receive','pn'),
		'start' => '[currency_code_get]',
	);	
	$tags['account_give'] = array(
		'title' => __('Account To send','pn'),
		'start' => '[account_give]',
	);
	$tags['account_get'] = array(
		'title' => __('Account To receive','pn'),
		'start' => '[account_get]',
	);
	$tags['first_name'] = array(
		'title' => __('First name','pn'),
		'start' => '[first_name]',
	);
	$tags['last_name'] = array(
		'title' => __('Last name','pn'),
		'start' => '[last_name]',
	);
	$tags['second_name'] = array(
		'title' => __('Second name','pn'),
		'start' => '[second_name]',
	);
	$tags['user_phone'] = array(
		'title' => __('Mobile phone no.','pn'),
		'start' => '[user_phone]',
	);
	$tags['user_skype'] = array(
		'title' => __('Skype','pn'),
		'start' => '[user_skype]',
	);
	$tags['user_telegram'] = array(
		'title' => __('Telegram','pn'),
		'start' => '[user_telegram]',
	);	
	$tags['user_email'] = array(
		'title' => __('E-mail','pn'),
		'start' => '[user_email]',
	);
	$tags['user_passport'] = array(
		'title' => __('Passport number','pn'),
		'start' => '[user_passport]',
	);	
	$tags['to_account'] = array(
		'title' => __('Merchant account','pn'),
		'start' => '[to_account]',
	);
	$tags['bidurl'] = array(
		'title' => __('Exchange URL','pn'),
		'start' => '[bidurl]',
	);
	$tags['bidadminurl'] = array(
		'title' => __('Link to order for administrator','pn'),
		'start' => '[bidadminurl]',
	);	
	$tags['bid_trans_in'] = array(
		'title' => __('Merchant transaction ID','pn'),
		'start' => '[bid_trans_in]',
	);
	$tags['bid_trans_out'] = array(
		'title' => __('Auto payout transaction ID','pn'),
		'start' => '[bid_trans_out]',
	);
	$tags['sum1'] = array(
		'title' => __('Amount To send','pn'),
		'start' => '[sum1]',
	);
	$tags['sum1dc'] = array(
		'title' => __('Amount To send (add. fees)','pn'),
		'start' => '[sum1dc]',
	);
	$tags['sum1c'] = array(
		'title' => __('Amount Send (PS fee)','pn'),
		'start' => '[sum1c]',
	);
	$tags['sum2'] = array(
		'title' => __('Amount Receive','pn'),
		'start' => '[sum2]',
	);	
	$tags['sum2dc'] = array(
		'title' => __('Amount To receive (add. fees)','pn'),
		'start' => '[sum2dc]',
	);	
	$tags['sum2c'] = array(
		'title' => __('Amount Receive (PS fee)','pn'),
		'start' => '[sum2c]',
	);
	$tags['uniq'] = array(
		'title' => __('Unique ID','pn'),
		'start' => '[uniq id=""]',
	);	
	
	$tags = apply_filters('shortcode_notify_tags_bids', $tags);
	return $tags;
}	

function goed_mail_to_changestatus_bids($obmen_id, $obmen, $name1='', $name2=''){
global $wpdb, $pn_lang;	
	
	if(isset($obmen->id)){
		
		$admin_lang = get_admin_lang();	
		$bid_locale = $obmen->bid_locale;
		
		$notify_tags = array();
		$notify_tags['[sitename]'] = pn_site_name();
		$notify_tags['[id]'] = $obmen->id; /* deprecated */
		$notify_tags['[exchange_id]'] = $obmen->id;
		$notify_tags['[createdate]'] = pn_strip_input($obmen->create_date); /* deprecated */
		$notify_tags['[create_date]'] = pn_strip_input($obmen->create_date);
		$notify_tags['[edit_date]'] = pn_strip_input($obmen->edit_date);
		$notify_tags['[curs1]'] = $notify_tags['[course_give]'] = pn_strip_input($obmen->course_give);
		$notify_tags['[curs2]'] = $notify_tags['[course_get]'] = pn_strip_input($obmen->course_get);
		$notify_tags['[valut1]'] = $notify_tags['[psys_give]'] = pn_strip_input(ctv_ml($obmen->psys_give,$bid_locale));
		$notify_tags['[valut2]'] = $notify_tags['[psys_get]'] = pn_strip_input(ctv_ml($obmen->psys_get,$bid_locale));
		$notify_tags['[vtype1]'] = $notify_tags['[currency_code_give]'] = pn_strip_input($obmen->currency_code_give);
		$notify_tags['[vtype2]'] = $notify_tags['[currency_code_get]'] = pn_strip_input($obmen->currency_code_get);
		$notify_tags['[account1]'] = $notify_tags['[account_give]'] = pn_strip_input($obmen->account_give);
		$notify_tags['[account2]'] = $notify_tags['[account_get]'] = pn_strip_input($obmen->account_get);
		$notify_tags['[first_name]'] = pn_strip_input($obmen->first_name);
		$notify_tags['[last_name]'] = pn_strip_input($obmen->last_name);
		$notify_tags['[second_name]'] = pn_strip_input($obmen->second_name);
		$notify_tags['[user_phone]'] = pn_strip_input($obmen->user_phone);
		$notify_tags['[user_skype]'] = pn_strip_input($obmen->user_skype);
		$notify_tags['[user_telegram]'] = pn_strip_input($obmen->user_telegram);
		$notify_tags['[user_email]'] = pn_strip_input($obmen->user_email);
		$notify_tags['[user_passport]'] = pn_strip_input($obmen->user_passport);
		$notify_tags['[to_account]'] = pn_strip_input($obmen->to_account);
		$notify_tags['[summ1]'] = $notify_tags['[sum1]'] = is_sum($obmen->sum1);
		$notify_tags['[summ1_dc]'] = $notify_tags['[sum1dc]'] = is_sum($obmen->sum1dc);
		$notify_tags['[summ1c]'] = $notify_tags['[sum1c]'] = is_sum($obmen->sum1c);
		$notify_tags['[summ2]'] = $notify_tags['[sum2]'] = is_sum($obmen->sum2);
		$notify_tags['[summ2_dc]'] = $notify_tags['[sum2dc]'] = is_sum($obmen->sum2dc);
		$notify_tags['[summ2c]'] = $notify_tags['[sum2c]'] = is_sum($obmen->sum2c);
		$notify_tags['[bidurl]'] = get_bids_url($obmen->hashed);
		$notify_tags['[bidadminurl]'] = admin_url('admin.php?page=pn_bids&bidid='. $obmen->id);
		$notify_tags['[bid_trans_in]'] = pn_strip_input($obmen->trans_in);
		$notify_tags['[bid_trans_out]'] = pn_strip_input($obmen->trans_out);
		
		$unmetas = @unserialize($obmen->unmetas);
		if(is_array($unmetas)){
			foreach($unmetas as $un_key => $un_value){
				$notify_tags['[uniq id="'. $un_key .'"]'] = pn_strip_input(ctv_ml($un_value));
			}
		}
		
		$notify_tags = apply_filters('notify_tags_bids', $notify_tags, $obmen);		

		if($name1){
			$user_send_data = array();
			$result_mail = apply_filters('premium_send_message', 0, $name1, $notify_tags, $user_send_data, $admin_lang); 
		}
		
		if($name2){
			$user_send_data = array(
				'user_email' => $obmen->user_email,
			);
			$user_send_data = apply_filters('user_send_data', $user_send_data, $name2, $obmen);
			$result_mail = apply_filters('premium_send_message', 0, $name2, $notify_tags, $user_send_data, $bid_locale);
		}	
	}		
}

add_filter('change_bidstatus', 'mail_change_bidstatus', 70, 5);     
function mail_change_bidstatus($item, $set_status, $place, $user_or_system, $old_status){
global $wpdb, $premiumbox;
	$item_id = $item->id;
	
	$action1 = '';
	if($place != 'admin_panel' or $premiumbox->get_option('exchange','admin_mail') == 1){
		$action1 = $set_status.'_bids1';
	}
	$action2 = $set_status.'_bids2';
	goed_mail_to_changestatus_bids($item_id, $item, $action1, $action2);

	return $item;
}	