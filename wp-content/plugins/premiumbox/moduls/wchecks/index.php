<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Accounts verification checker[:en_US][ru_RU:]Чекер верификации кошельков[:ru_RU]
description: [en_US:]Accounts verification checker[:en_US][ru_RU:]Чекер верификации кошельков[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

/* BD */
add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_wchecks');
add_action('all_bd_activated', 'bd_all_moduls_active_wchecks');
function bd_all_moduls_active_wchecks(){
global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE 'check_text'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `check_text` longtext NOT NULL");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE 'check_purse'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `check_purse` varchar(150) NOT NULL default '0'");
    }	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'com_sum1_check'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `com_sum1_check` varchar(50) NOT NULL default '0'");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'com_sum2_check'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `com_sum2_check` varchar(50) NOT NULL default '0'");
    }	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'com_pers1_check'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `com_pers1_check` varchar(20) NOT NULL default '0'");
    }	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'com_pers2_check'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `com_pers2_check` varchar(20) NOT NULL default '0'");
    }	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'check_purse'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `check_purse` int(1) NOT NULL default '0'");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'req_check_purse'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `req_check_purse` int(1) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'check_purse1'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `check_purse1` varchar(20) NOT NULL default '0'");
    }	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'check_purse2'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `check_purse2` varchar(20) NOT NULL default '0'");
    }
}
/* end BD */

add_action('admin_menu', 'admin_menu_wchecks', 11);
function admin_menu_wchecks(){
global $premiumbox;	
	if(current_user_can('administrator') or current_user_can('pn_merchants')){
		add_submenu_page("pn_merchants", __('Accounts verification checker','pn'), __('Accounts verification checker','pn'), 'read', "pn_wchecks", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_merchants", __('Accounts verification checker settings','pn'), __('Accounts verification checker settings','pn'), 'read', "pn_wchecks_settings", array($premiumbox, 'admin_temp'));			
	}
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'data');
$premiumbox->include_patch(__FILE__, 'functions');
$premiumbox->include_patch(__FILE__, 'list'); 
$premiumbox->include_patch(__FILE__, 'filters');