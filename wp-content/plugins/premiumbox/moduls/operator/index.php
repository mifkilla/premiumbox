<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Operator status[:en_US][ru_RU:]Статус оператора[:ru_RU]
description: [en_US:]Operator status[:en_US][ru_RU:]Статус оператора[:ru_RU]
version: 2.2
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_bd_activated', 'e_active_operator');
add_action('all_moduls_active_'.$name, 'e_active_operator');
function e_active_operator(){
global $wpdb;
	
	$table_name= $wpdb->prefix ."schedule_operators";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',		
		`status` int(5) NOT NULL default '0',
		`h1` varchar(5) NOT NULL default '0',
		`m1` varchar(5) NOT NULL default '0',
		`h2` varchar(5) NOT NULL default '0',
		`m2` varchar(5) NOT NULL default '0',		
		`d1` int(1) NOT NULL default '0',
		`d2` int(1) NOT NULL default '0',
		`d3` int(1) NOT NULL default '0',
		`d4` int(1) NOT NULL default '0',
		`d5` int(1) NOT NULL default '0',
		`d6` int(1) NOT NULL default '0',
		`d7` int(1) NOT NULL default '0',
		`save_order` bigint(20) NOT NULL default '0',
		PRIMARY KEY ( `id` ),
		INDEX (`auto_status`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`edit_user_id`),
		INDEX (`status`),
		INDEX (`save_order`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);		
        	
}

add_filter('status_operator','def_status_operator');
function def_status_operator($status_operator){
	$status_operator[0] = __('offline','pn');
	$status_operator[1] = __('online','pn');
	return $status_operator;
}

add_action('admin_menu', 'operator_admin_menu');
function operator_admin_menu(){
global $premiumbox;
	
	add_menu_page( __('Work status','pn'), __('Work status','pn'), 'administrator', "pn_operator", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('operator'), 10010);
	add_submenu_page( "pn_operator", __('Settings','pn'), __('Settings','pn'), 'administrator', "pn_operator", array($premiumbox, 'admin_temp'));
	add_submenu_page( "pn_operator", __('Operator schedule','pn'), __('Operator schedule','pn'), 'administrator', "pn_schedule_operators", array($premiumbox, 'admin_temp'));
	add_submenu_page( "pn_operator", __('Add schedule','pn'), __('Add schedule','pn'), 'administrator', "pn_add_schedule_operators", array($premiumbox, 'admin_temp'));
	
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'settings');
$premiumbox->include_patch(__FILE__, 'list_shedules');
$premiumbox->include_patch(__FILE__, 'add_shedules');
$premiumbox->include_patch(__FILE__, 'filters');