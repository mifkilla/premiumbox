<?php
if( !defined( 'ABSPATH')){ exit(); }

add_filter('change_bidstatus', 'setuserebids_change_bidstatus', 45, 3);    
function setuserebids_change_bidstatus($item, $set_status, $place){
global $wpdb, $premiumbox;
	if($set_status == 'new' and $place == 'exchange_button'){
		$user_id = intval($item->user_id);
		$user_login = is_user($item->user_login);
		$user_hash = pn_strip_input($item->user_hash);
		if($user_id > 0){
			$wpdb->query("UPDATE ".$wpdb->prefix."exchange_bids SET user_id = '$user_id', user_login = '$user_login' WHERE user_hash = '$user_hash' AND user_id < 1");
		}
	}
	return $item;
}

add_action('pn_user_register', 'setuserebids_pn_user_register', 45);
function setuserebids_pn_user_register($user_id){
global $wpdb;
	$ui = get_userdata($user_id);
	$user_login = is_user(is_isset($ui,'user_login'));
	$user_hash = get_user_hash();
	if($user_id > 0 and isset($ui->ID)){
		$wpdb->query("UPDATE ".$wpdb->prefix."exchange_bids SET user_id = '$user_id', user_login = '$user_login' WHERE user_hash = '$user_hash' AND user_id < 1");
	}	
}

global $premiumbox; 
$premiumbox->include_patch(__FILE__, 'calculator'); 
$premiumbox->include_patch(__FILE__, 'action');
$premiumbox->include_patch(__FILE__, 'cron'); 
$premiumbox->include_patch(__FILE__, 'mails');