<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Mobile version[:en_US][ru_RU:]Мобильная версия[:ru_RU]
description: [en_US:]Mobile version[:en_US][ru_RU:]Мобильная версия[:ru_RU]
version: 2.2
category: [en_US:]Mobile[:en_US][ru_RU:]Мобильное приложение[:ru_RU]
cat: mobile
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_mobile');
add_action('all_bd_activated', 'bd_all_moduls_active_mobile');
function bd_all_moduls_active_mobile(){ 
global $wpdb;	
		
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'mobile'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `mobile` int(1) NOT NULL default '0'");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'device'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `device` int(1) NOT NULL default '0'");
    } 	
}

$plugin = get_plugin_class();
$plugin->include_patch(__FILE__, 'includes/functions');
$plugin->include_patch(__FILE__, 'exchange/settings');
$plugin->include_patch(__FILE__, 'exchange/filters');
$plugin->include_patch(__FILE__, 'exchange/index');
$plugin->auto_include($path.'/shortcode');