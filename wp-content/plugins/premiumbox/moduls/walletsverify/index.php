<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Accounts verification[:en_US][ru_RU:]Верификация счетов пользователей[:ru_RU]
description: [en_US:]Accounts verification[:en_US][ru_RU:]Верификация счетов пользователей[:ru_RU]
version: 2.2
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
dependent: userwallets
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_walletsverify');
add_action('all_bd_activated', 'bd_all_moduls_active_walletsverify');
function bd_all_moduls_active_walletsverify(){
global $wpdb;	
	
	$table_name = $wpdb->prefix ."uv_wallets";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`user_id` bigint(20) NOT NULL default '0',
		`user_login` varchar(250) NOT NULL,
		`user_email` varchar(250) NOT NULL,
		`user_ip` varchar(250) NOT NULL,
		`currency_id` bigint(20) NOT NULL default '0',
		`user_wallet_id` bigint(20) NOT NULL default '0',
		`wallet_num` longtext NOT NULL,
		`comment` longtext NOT NULL,
		`locale` varchar(20) NOT NULL,
		`status` int(1) NOT NULL default '0',
		PRIMARY KEY ( `id` ),
		INDEX (`create_date`),
		INDEX (`user_id`),
		INDEX (`currency_id`),
		INDEX (`user_wallet_id`),
		INDEX (`locale`),
		INDEX (`status`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);
	
	$table_name = $wpdb->prefix ."uv_wallets_files";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) NOT NULL default '0',
		`uv_data` longtext NOT NULL,
		`uv_wallet_id` bigint(20) NOT NULL default '0',
		PRIMARY KEY ( `id` ),
		INDEX (`user_id`),
		INDEX (`uv_wallet_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'accv_give'");
    if ($query == 0) { 
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `accv_give` int(2) NOT NULL default '0'");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'accv_get'");
    if ($query == 0) { 
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `accv_get` int(2) NOT NULL default '0'");
    }	
	
}

add_action('admin_menu', 'admin_menu_walletsverify');
function admin_menu_walletsverify(){
global $premiumbox;
	if(current_user_can('administrator') or current_user_can('pn_userwallets')){ 	
		add_submenu_page("pn_userwallets", __('Accounts verification','pn'), __('Accounts verification','pn'), 'read', "pn_userwallets_verify", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_userwallets", __('Verification settings','pn'), __('Verification settings','pn'), 'read', "pn_userwallets_verify_settings", array($premiumbox, 'admin_temp'));		
	}
}

add_filter('pn_tech_pages', 'list_tech_pages_walletsverify');
function list_tech_pages_walletsverify($pages){
	$pages[] = array(
		'post_name'      => 'walletsverify',
		'post_title'     => '[en_US:]Your accounts verification[:en_US][ru_RU:]Верификация счетов[:ru_RU]',
		'post_content'   => '[walletsverify]',
		'post_template'   => 'pn-pluginpage.php',
	);		
	return $pages;
}

add_filter('list_admin_notify','list_admin_notify_walletsverify');
function list_admin_notify_walletsverify($places_admin){
	$places_admin['userverify2'] = __('Request for account verification','pn');
	return $places_admin;
}

add_filter('list_user_notify','list_user_notify_walletsverify');
function list_user_notify_walletsverify($places_admin){
	$places_admin['userverify3_u'] = __('Successful account verification','pn');
	$places_admin['userverify4_u'] = __('Account verification declined','pn');
	$places_admin['userverify5_u'] = __('Account verification delete','pn');
	return $places_admin;
}

add_filter('list_notify_tags_userverify2','def_list_notify_tags_walletsverify');
add_filter('list_notify_tags_userverify3_u','def_list_notify_tags_walletsverify');
add_filter('list_notify_tags_userverify4_u','def_list_notify_tags_walletsverify');
add_filter('list_notify_tags_userverify5_u','def_list_notify_tags_walletsverify');
function def_list_notify_tags_walletsverify($tags){
	$tags['user_login'] = array(
		'title' => __('User login','pn'),
		'start' => '[user_login]',
	);
	$tags['purse'] = array(
		'title' => __('Account number','pn'),
		'start' => '[purse]',
	);
	$tags['comment'] = array(
		'title' => __('Failure reason','pn'),
		'start' => '[comment]',
	);
	return $tags;
}

function delete_userwallets_files($user_wallet_id){
global $wpdb, $premiumbox;

	$user_wallet_id = intval($user_wallet_id);
	$wpdb->query("DELETE FROM ".$wpdb->prefix."uv_wallets_files WHERE uv_wallet_id = '$user_wallet_id'");
	$path = $premiumbox->upload_dir . 'accountverify/'. $user_wallet_id .'/';
	full_del_dir($path);
	
}

add_action('item_uv_wallets_delete', 'item_uv_wallets_delete_walletsverify', 10, 2);
function item_uv_wallets_delete_walletsverify($item_id, $item){
	$user_wallet_id = $item->user_wallet_id;
	delete_userwallets_files($user_wallet_id);
}

add_action('item_userwallets_delete', 'item_userwallets_delete_walletsverify');
function item_userwallets_delete_walletsverify($id){
global $wpdb;

	$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."uv_wallets WHERE user_wallet_id = '$id'");
	foreach($items as $item){
		$item_id = $item->id;
		$res = apply_filters('item_uv_wallets_delete_before', pn_ind(), $item_id, $item);
		if($res['ind'] == 1){
			$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."uv_wallets WHERE id = '$item_id'");
			do_action('item_uv_wallets_delete', $item_id, $item, $result);
		}
	}
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'settings');
$premiumbox->include_patch(__FILE__, 'list');
$premiumbox->include_patch(__FILE__, 'file');
$premiumbox->include_patch(__FILE__, 'shortcode');
$premiumbox->include_patch(__FILE__, 'filters');