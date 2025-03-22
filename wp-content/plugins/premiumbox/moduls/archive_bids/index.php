<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Archiving of old requests[:en_US][ru_RU:]Архивация старых заявок[:ru_RU]
description: [en_US:]!Do not disable the module after activation! Archiving of old requests with the creation date longer than two months[:en_US][ru_RU:]!Не отключать модуль после его активации! Архивация старых заявок со сроком создания более двух месяцев[:ru_RU]
version: 2.2
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

/* BD */
add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_archive_bids');
add_action('all_bd_activated', 'bd_all_moduls_active_archive_bids');
function bd_all_moduls_active_archive_bids(){
global $wpdb;	
	
	$table_name = $wpdb->prefix ."archive_exchange_bids";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`archive_date` datetime NOT NULL,
		`create_date` datetime NOT NULL, 
		`edit_date` datetime NOT NULL, 	
		`bid_id` bigint(20) NOT NULL default '0',
		`user_id` bigint(20) NOT NULL default '0',
		`ref_id` bigint(20) NOT NULL default '0',
		`archive_content` longtext NOT NULL,
		`account_give` varchar(250) NOT NULL, 
		`account_get` varchar(250) NOT NULL, 
		`first_name` varchar(150) NOT NULL,
		`last_name` varchar(150) NOT NULL,
		`second_name` varchar(150) NOT NULL,
		`user_phone` varchar(150) NOT NULL,
		`user_skype` varchar(150) NOT NULL,
		`user_email` varchar(150) NOT NULL,
		`user_telegram` varchar(150) NOT NULL,
		`user_passport` varchar(250) NOT NULL, 
		`currency_id_give` bigint(20) NOT NULL default '0', 
		`currency_id_get` bigint(20) NOT NULL default '0',	
		`status` varchar(35) NOT NULL,
		`direction_id` bigint(20) NOT NULL default '0',
		`currency_code_id_give` bigint(20) NOT NULL default '0', 
		`currency_code_id_get` bigint(20) NOT NULL default '0',
		`psys_id_give` bigint(20) NOT NULL default '0', 
		`psys_id_get` bigint(20) NOT NULL default '0', 
		`exsum` varchar(50) NOT NULL default '0',
		`profit` varchar(50) NOT NULL default '0',
		`trans_in` varchar(250) NOT NULL default '0',
		`trans_out` varchar(250) NOT NULL default '0',
		`to_account` varchar(250) NOT NULL, 
		`from_account` varchar(250) NOT NULL,
		`psys_give` longtext NOT NULL, 
		`psys_get` longtext NOT NULL,
		`course_give` varchar(50) NOT NULL default '0', 
		`course_get` varchar(50) NOT NULL default '0',
		`user_ip` varchar(150) NOT NULL,	
		`currency_code_give` varchar(35) NOT NULL, 
		`currency_code_get` varchar(35) NOT NULL, 
		`user_discount` varchar(10) NOT NULL default '0',
		`user_discount_sum` varchar(50) NOT NULL default '0',
		`pay_ac` varchar(250) NOT NULL,
		`pay_sum` varchar(50) NOT NULL default '0',	
		`sum1` varchar(50) NOT NULL default '0', 
		`dop_com1` varchar(50) NOT NULL default '0',
		`sum1dc` varchar(50) NOT NULL default '0',
		`com_ps1` varchar(50) NOT NULL default '0',
		`com_ps2` varchar(50) NOT NULL default '0',
		`sum1c` varchar(50) NOT NULL default '0', 
		`sum1r` varchar(50) NOT NULL default '0',
		`sum2t` varchar(50) NOT NULL default '0',
		`sum2` varchar(50) NOT NULL default '0', 
		`dop_com2` varchar(50) NOT NULL default '0',
		`sum2dc` varchar(50) NOT NULL default '0',
		`sum2r` varchar(50) NOT NULL default '0',
		`sum2c` varchar(50) NOT NULL default '0',		
		PRIMARY KEY ( `id` ),
		INDEX (`archive_date`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`bid_id`),
		INDEX (`user_id`),
		INDEX (`ref_id`),
		INDEX (`currency_id_give`),
		INDEX (`currency_id_get`),
		INDEX (`status`),
		INDEX (`direction_id`),
		INDEX (`currency_code_id_give`),
		INDEX (`currency_code_id_get`),
		INDEX (`psys_id_give`),
		INDEX (`psys_id_get`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql);	 
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."archive_exchange_bids LIKE 'user_telegram'"); /* 2.0 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."archive_exchange_bids ADD `user_telegram` varchar(150) NOT NULL");
	}	
}
/* end BD */

add_filter('pn_caps','archive_pn_caps');
function archive_pn_caps($pn_caps){
	$pn_caps['pn_archive'] = __('Work with archived orders','pn');
	return $pn_caps;
}

add_action('delete_user', 'delete_user_archive_bids');
function delete_user_archive_bids($user_id){
global $wpdb;
    $wpdb->query("DELETE FROM ". $wpdb->prefix ."archive_data WHERE item_id='$user_id' AND meta_key IN('user_exsum','user_bids','domacc1_currency_code','domacc2_currency_code')");
}

add_filter('user_sum_exchanges', 'user_sum_exchanges_archive_bids', 1, 3);
function user_sum_exchanges_archive_bids($d_sum, $sum, $user_id){ 
global $wpdb;
	$count = $wpdb->get_var("SELECT SUM(meta_value) FROM ".$wpdb->prefix."archive_data WHERE meta_key='user_exsum' AND item_id='$user_id'");
	$d_sum = $sum + $count;
	$d_sum = is_sum($d_sum);
	return $d_sum;
}

add_filter('user_count_exchanges', 'user_count_exchanges_archive_bids', 1, 3);
function user_count_exchanges_archive_bids($d_sum, $sum, $user_id){
global $wpdb;
	$count = $wpdb->get_var("SELECT SUM(meta_value) FROM ".$wpdb->prefix."archive_data WHERE meta_key='user_bids' AND meta_key2 = 'success' AND item_id='$user_id'");
	$sum = $sum + $count;
	$sum = is_sum($sum);
	return $sum;
}

add_filter('get_partner_earn_all', 'partner_money_archive_bids', 1, 3);
function partner_money_archive_bids($d_sum, $sum, $user_id){
global $wpdb;
	$count = $wpdb->get_var("SELECT SUM(meta_value) FROM ".$wpdb->prefix."archive_data WHERE meta_key='pbids_sum' AND item_id='$user_id'");
	$sum = $sum + $count;
	$sum = is_sum($sum);
	return $sum;
}
 
add_filter('user_sum_refobmen', 'user_sum_refobmen_archive_bids', 1, 3);
function user_sum_refobmen_archive_bids($d_sum, $sum, $user_id){
global $wpdb;
	$count = $wpdb->get_var("SELECT SUM(meta_value) FROM ".$wpdb->prefix."archive_data WHERE meta_key='pbids_exsum' AND item_id='$user_id'");
	$sum = $sum + $count;
	$sum = is_sum($sum);
	return $sum;
}

add_filter('user_count_refobmen', 'user_count_refobmen_archive_bids', 1, 2);
function user_count_refobmen_archive_bids($sum, $ref_id){
global $wpdb;
	$count = $wpdb->get_var("SELECT SUM(meta_value) FROM ".$wpdb->prefix."archive_data WHERE meta_key='pbids' AND item_id='$ref_id'");
	$sum = $sum + $count;
	$sum = is_sum($sum);
	return $sum;
}

/* currency codes */
add_action('item_currency_code_delete','archive_item_currency_code_delete');
function archive_item_currency_code_delete($id){
global $wpdb;
	$wpdb->query("DELETE FROM ".$wpdb->prefix."archive_data WHERE item_id = '$id' AND meta_key IN('currency_code_give','currency_code_get')");
}

add_filter('get_reserv_currency_code', 'get_reserv_currency_code_archive_bids', 1, 3);
function get_reserv_currency_code_archive_bids($d_sum, $sum, $currency_code_id){
global $wpdb;
	$count = $wpdb->get_var("SELECT SUM(meta_value) FROM ".$wpdb->prefix."archive_data WHERE meta_key='currency_code_give' AND meta_key2='success' AND item_id='$currency_code_id'");
	$count2 = $wpdb->get_var("SELECT SUM(meta_value) FROM ".$wpdb->prefix."archive_data WHERE meta_key='currency_code_get' AND meta_key2='success' AND item_id='$currency_code_id'");
	$sum = $sum + $count - $count2;
	$d_sum = is_sum($sum);
	return $d_sum;
}
/* end currency codes */

/* currency */
add_action('item_currency_delete','archive_item_currency_delete');
function archive_item_currency_delete($id){
global $wpdb;
	$wpdb->query("DELETE FROM ".$wpdb->prefix."archive_data WHERE item_id = '$id' AND meta_key IN('currency_give','currency_get')");
}

add_filter('exchange_bids_by_currency_give', 'archive_exchange_bids_by_currency_give', 1, 4);
function archive_exchange_bids_by_currency_give($sum, $currency_id, $status_where, $date=''){
global $wpdb;
	$date = trim($date);
	if(!$date){
		$where = '';
		if(is_array($currency_id)){
			$bd_id = create_data_for_bd($currency_id, 'int');
			$where = " AND item_id IN($bd_id)";
		} else {
			$bd_id = intval($currency_id);
			$where = " AND item_id = '$bd_id'";
		}
		$count = $wpdb->get_var("SELECT SUM(meta_value) FROM ".$wpdb->prefix."archive_data WHERE meta_key='currency_give' AND meta_key2 IN($status_where) $where");
		$sum = $sum + $count + 0;
	}
	return $sum;
}

add_filter('exchange_bids_by_currency_get', 'archive_exchange_bids_by_currency_get', 1, 4);
function archive_exchange_bids_by_currency_get($sum, $currency_id, $status_where, $date=''){
global $wpdb;
	$date = trim($date);
	if(!$date){
		$where = '';
		if(is_array($currency_id)){
			$bd_id = create_data_for_bd($currency_id, 'int');
			$where = " AND item_id IN($bd_id)";
		} else {
			$bd_id = intval($currency_id);
			$where = " AND item_id = '$bd_id'";
		}		
		$count = $wpdb->get_var("SELECT SUM(meta_value) FROM ".$wpdb->prefix."archive_data WHERE meta_key='currency_get' AND meta_key2 IN($status_where) $where");
		$sum = $sum + $count + 0;
	}
	return $sum;
}
/* end currency */

/* directions */
add_action('item_direction_delete','archive_item_direction_delete');
function archive_item_direction_delete($id){
global $wpdb;
	$wpdb->query("DELETE FROM ".$wpdb->prefix."archive_data WHERE meta_key IN('direction_give','direction_get') AND item_id = '$id'");
}

add_filter('get_sum_direction', 'archive_get_sum_direction', 1, 6);
function archive_get_sum_direction($d_sum, $sum, $direction_id, $method, $filter_status, $date=''){
global $wpdb;
	
	$date = trim($date);
	if(!$date){
		if($method == 'in'){
			$sum1 = $wpdb->get_var("SELECT SUM(meta_value) FROM ". $wpdb->prefix ."archive_data WHERE item_id='$direction_id' AND meta_key='direction_give' AND meta_key2 IN($filter_status)");
		} else {
			$sum1 = $wpdb->get_var("SELECT SUM(meta_value) FROM ". $wpdb->prefix ."archive_data WHERE item_id='$direction_id' AND meta_key='direction_get' AND meta_key2 IN($filter_status)");
		}		
		$d_sum = is_sum($sum + $sum1);
	}
	
	return $d_sum;
}
/* end directions */

/* dom acc */
add_filter('get_user_domacc', 'get_user_domacc_archive_bids', 1, 3);
function get_user_domacc_archive_bids($sum, $user_id, $currency_code_id){
global $wpdb;
	$sum1 = $wpdb->get_var("SELECT SUM(meta_value) FROM ".$wpdb->prefix."archive_data WHERE item_id='$user_id' AND meta_key='domacc2_currency_code' AND meta_key2 = 'success' AND meta_key3='$currency_code_id'");
	$sum2 = $wpdb->get_var("SELECT SUM(meta_value) FROM ".$wpdb->prefix."archive_data WHERE item_id='$user_id' AND meta_key='domacc1_currency_code' AND meta_key2 IN('realpay','success','verify') AND meta_key3='$currency_code_id'");
	$sum3 = is_sum($sum + $sum1 - $sum2);
	return $sum3;
}
/* end dom acc */

add_action('admin_menu', 'admin_menu_archive_bids', 500);
function admin_menu_archive_bids(){
global $premiumbox;	
	if(current_user_can('administrator') or current_user_can('pn_archive')){
		add_submenu_page('pn_bids', __('Archived orders','pn'), __('Archived orders','pn'), 'read', 'pn_archive_bids', array($premiumbox, 'admin_temp')); 
		add_submenu_page('pn_none_menu', __('Information about archived order','pn'), __('Information about archived order','pn'), 'read', 'pn_archive_bid', array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_moduls", __('Archiving settings','pn'), __('Archiving settings','pn'), 'read', "pn_settings_archive_bids", array($premiumbox, 'admin_temp'));
	}
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'cron');
$premiumbox->include_patch(__FILE__, 'list');
$premiumbox->include_patch(__FILE__, 'single');
$premiumbox->include_patch(__FILE__, 'settings');
$premiumbox->include_patch(__FILE__, 'files');