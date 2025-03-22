<?php
if( !defined( 'ABSPATH')){ exit(); }
	
/*
title: [en_US:]Rates parser 2.0[:en_US][ru_RU:]Парсер курсов 2.0[:ru_RU]
description: [en_US:]Rates parser 2.0[:en_US][ru_RU:]Парсер курсов 2.0[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/	
	
$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_newparser');
add_action('all_bd_activated', 'bd_all_moduls_active_newparser');
function bd_all_moduls_active_newparser(){
global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'new_parser'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `new_parser` bigint(20) NOT NULL default '0'");
    }		
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'new_parser_actions_give'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `new_parser_actions_give` varchar(150) NOT NULL default '0'");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'new_parser_actions_get'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `new_parser_actions_get` varchar(150) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency_codes LIKE 'new_parser'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency_codes ADD `new_parser` bigint(20) NOT NULL default '0'");
    }		
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency_codes LIKE 'new_parser_actions'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency_codes ADD `new_parser_actions` varchar(150) NOT NULL default '0'");
    }
	
	$table_name = $wpdb->prefix ."parser_pairs";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`title_pair_give` varchar(150) NOT NULL,
		`title_pair_get` varchar(150) NOT NULL,
		`title_birg` longtext NOT NULL,
		`pair_give` longtext NOT NULL,
		`pair_get` longtext NOT NULL,
		`menu_order` bigint(20) NOT NULL default '0', 
		PRIMARY KEY ( `id` ),
		INDEX (`menu_order`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql);
	
	$table_name = $wpdb->prefix ."parser_logs"; 
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`work_date` datetime NOT NULL,
		`log_comment` varchar(150) NOT NULL,
		`log_code` int(1) NOT NULL default '0', 
		`title_birg` varchar(250) NOT NULL,
		`key_birg` varchar(250) NOT NULL,
		PRIMARY KEY ( `id` ),
		INDEX (`work_date`),
		INDEX (`log_code`),
		INDEX (`key_birg`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
}	

if(!function_exists('parser_pn_caps')){
	add_filter('pn_caps','parser_pn_caps');
	function parser_pn_caps($pn_caps){
		$pn_caps['pn_parser'] = __('Parsers 2.0','pn');
		return $pn_caps;
	}
}

add_action('admin_menu', 'pn_adminpage_newparser');
function pn_adminpage_newparser(){
global $premiumbox;
	if(current_user_can('administrator') or current_user_can('pn_directions') or current_user_can('pn_parser')){
		add_menu_page(__('Parsers 2.0','pn'), __('Parsers 2.0','pn'), 'read', 'pn_new_parser', array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('parser'));  
		add_submenu_page("pn_new_parser", __('Source rates','pn'), __('Source rates','pn'), 'read', "pn_new_parser", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_new_parser", __('Rates','pn'), __('Rates','pn'), 'read', "pn_parser_pairs", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_new_parser", __('Add rate','pn'), __('Add rate','pn'), 'read', "pn_add_parser_pairs", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_new_parser", __('Sorting rates','pn'), __('Sorting rates','pn'), 'read', "pn_sort_parser_pairs", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_new_parser", __('Custom coefficients','pn'), __('Custom coefficients','pn'), 'read', "pn_parser_index", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_new_parser", __('Add coefficient','pn'), __('Add coefficient','pn'), 'read', "pn_add_parser_index", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_new_parser", __('Logs','pn'), __('Logs','pn'), 'read', "pn_parser_logs", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_new_parser", __('Settings','pn'), __('Settings','pn'), 'read', "pn_settings_new_parser", array($premiumbox, 'admin_temp'));
	}	
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'birg_filters');
$premiumbox->include_patch(__FILE__, 'list_courses');
$premiumbox->include_patch(__FILE__, 'list_logs');
$premiumbox->include_patch(__FILE__, 'list');
$premiumbox->include_patch(__FILE__, 'add');
$premiumbox->include_patch(__FILE__, 'sort');
$premiumbox->include_patch(__FILE__, 'list_index');
$premiumbox->include_patch(__FILE__, 'add_index');
$premiumbox->include_patch(__FILE__, 'filters');
$premiumbox->include_patch(__FILE__, 'settings');
$premiumbox->include_patch(__FILE__, 'cron');
$premiumbox->include_patch(__FILE__, 'email');

$premiumbox->auto_include($path.'/widget');