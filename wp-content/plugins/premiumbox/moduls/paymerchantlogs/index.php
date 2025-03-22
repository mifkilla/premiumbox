<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]!Do not activate without any reason! Automatic payouts log[:en_US][ru_RU:]!Не активируйте без необходимости! Лог автовыплат[:ru_RU]
description: [en_US:]!Do not activate without any reason! Automatic payouts log[:en_US][ru_RU:]!Не активируйте без необходимости! Лог автовыплат[:ru_RU]
version: 2.2
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

/* BD */
add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_paymerchantlogs');
add_action('all_bd_activated', 'bd_all_moduls_active_paymerchantlogs');
function bd_all_moduls_active_paymerchantlogs(){
global $wpdb;	
	
	$table_name= $wpdb->prefix ."paymerchant_logs";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`createdate` datetime NOT NULL,
		`bid_id` bigint(20) NOT NULL default '0',
		`mdata` longtext NOT NULL,
		`merchant` varchar(150) NOT NULL,
		PRIMARY KEY ( `id` ),
		INDEX (`createdate`),
		INDEX (`bid_id`),
		INDEX (`merchant`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);	
	
}
/* end BD */
 
add_action('admin_menu', 'pn_adminpage_paymerchantlogs', 1000);
function pn_adminpage_paymerchantlogs(){
global $premiumbox;	
	
	if(current_user_can('administrator') or current_user_can('pn_merchants')){
		add_submenu_page("pn_merchants", __('Automatic payouts log','pn'), __('Automatic payouts log','pn'), 'read', "pn_paymerchantlogs", array($premiumbox, 'admin_temp'));
	}
}

add_action('paymerchant_error','paymerchantlogs_save_paymerchant_error',10, 3);
add_action('save_paymerchant_error','paymerchantlogs_save_paymerchant_error',10, 3); 
function paymerchantlogs_save_paymerchant_error($merchant='', $data, $bid_id=''){
global $wpdb;
	
	$arr = array();
	$arr['createdate'] = current_time('mysql');
	$arr['mdata'] = pn_strip_input(print_r($data, true));
	$arr['merchant'] = is_extension_name($merchant);
	$arr['bid_id'] = pn_strip_input($bid_id);
	$wpdb->insert($wpdb->prefix.'paymerchant_logs', $arr);
	
}

function del_paymerchantlogs(){
global $wpdb, $premiumbox;
	if(!$premiumbox->is_up_mode()){
		$count_day = intval($premiumbox->get_option('logssettings', 'delete_paymerchantlogs_day'));
		if(!$count_day){ $count_day = 20; }

		$count_day = apply_filters('delete_paymerchantlogs_day', $count_day);
		if($count_day > 0){
			$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
			$ldate = date('Y-m-d H:i:s', $time);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."paymerchant_logs WHERE createdate < '$ldate'");
		}
	}
} 

add_filter('list_cron_func', 'del_paymerchantlogs_list_cron_func');
function del_paymerchantlogs_list_cron_func($filters){
	$filters['del_paymerchantlogs'] = array(
		'title' => __('Deleting automatic payout logs','pn'),
		'site' => '1day',
	);
	return $filters;
}

add_filter('list_logs_settings', 'paymerchantlogs_list_logs_settings');
function paymerchantlogs_list_logs_settings($filters){		
	$filters['delete_paymerchantlogs_day'] = array(
		'title' => __('Deleting automatic payout logs','pn') .' ('. __('days','pn') .')',
		'count' => 20,
		'minimum' => 1,
	);
	return $filters;
} 

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'list');