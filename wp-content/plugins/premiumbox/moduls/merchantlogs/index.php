<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]!Do not activate without any reason! Merchants log[:en_US][ru_RU:]!Не активируйте без необходимости! Лог мерчантов[:ru_RU]
description: [en_US:]!Do not activate without any reason! Logging requests of those merchants who send payment systems right after making a payment.[:en_US][ru_RU:]!Не активируйте без необходимости! Логирование обращений мерчантов, которые присылают платежные системы после оплаты.[:ru_RU]
version: 2.2
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_merchantlogs');
add_action('all_bd_activated', 'bd_all_moduls_active_merchantlogs');
function bd_all_moduls_active_merchantlogs(){
global $wpdb;	
	
	$table_name= $wpdb->prefix ."merchant_logs"; 
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`createdate` datetime NOT NULL,
		`mdata` longtext NOT NULL,
		`merchant` varchar(150) NOT NULL,
		`ip` varchar(250) NOT NULL,
		`variant` int(1) NOT NULL default '0',
		PRIMARY KEY ( `id` ),
		INDEX (`createdate`),
		INDEX (`merchant`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."merchant_logs LIKE 'ip'"); /* 1.6 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."merchant_logs ADD `ip` varchar(250) NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."merchant_logs LIKE 'variant'"); /* 1.6 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."merchant_logs ADD `variant` int(1) NOT NULL default '0'");
	}	
}
 
add_action('admin_menu', 'admin_menu_merchantlogs', 1000);
function admin_menu_merchantlogs(){
global $premiumbox;	
	if(current_user_can('administrator') or current_user_can('pn_merchants')){
		add_submenu_page("pn_merchants", __('Merchants log','pn'), __('Merchants log','pn'), 'read', "pn_merchantlogs", array($premiumbox, 'admin_temp'));		
	}
}

add_action('merchant_logs','merchantlogs_merchant_logs', 10, 2); 
function merchantlogs_merchant_logs($merchant='', $data=''){
global $wpdb;
	if(is_array($data)){
		$bd_data = $data;
	} else {
		$bd_data = $_REQUEST;
	}	
	
	$arr = array();
	$arr['createdate'] = current_time('mysql');
	$arr['mdata'] = pn_strip_input(print_r($bd_data, true));
	$arr['merchant'] = is_extension_name($merchant);
	$arr['ip'] = pn_strip_input(pn_real_ip());
	$wpdb->insert($wpdb->prefix.'merchant_logs', $arr);
}

add_action('save_merchant_error','merchantlogs_save_merchant_error', 10, 2); 
function merchantlogs_save_merchant_error($merchant='', $data=''){
global $wpdb;

	$arr = array();
	$arr['createdate'] = current_time('mysql');
	$arr['mdata'] = pn_strip_input(print_r($data, true));
	$arr['merchant'] = is_extension_name($merchant);
	$arr['ip'] = pn_strip_input(pn_real_ip());
	$wpdb->insert($wpdb->prefix.'merchant_logs', $arr);
}

function del_merchantlogs(){
global $wpdb, $premiumbox;
	if(!$premiumbox->is_up_mode()){
		$count_day = intval($premiumbox->get_option('logssettings', 'delete_merchantlogs_day'));
		if(!$count_day){ $count_day = 20; }

		$count_day = apply_filters('delete_merchantlogs_day', $count_day);
		if($count_day > 0){
			$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
			$ldate = date('Y-m-d H:i:s', $time);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."merchant_logs WHERE createdate < '$ldate'");
		}
	}
} 

add_filter('list_cron_func', 'del_merchantlogs_list_cron_func');
function del_merchantlogs_list_cron_func($filters){
global $premiumbox;	
	$filters['del_merchantlogs'] = array(
		'title' => __('Delete merchants log','pn'),
		'site' => '1day',
	);
	return $filters;
}

add_filter('list_logs_settings', 'merchantlogs_list_logs_settings');
function merchantlogs_list_logs_settings($filters){		
	$filters['delete_merchantlogs_day'] = array(
		'title' => __('Delete merchants log','pn') .' ('. __('days','pn') .')',
		'count' => 20,
		'minimum' => 1,
	);
	return $filters;
} 

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'list');