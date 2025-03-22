<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Account number validator[:en_US][ru_RU:]Валидатор номера счета[:ru_RU]
description: [en_US:]Account number validator[:en_US][ru_RU:]Валидатор номера счета[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

/* BD */
add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_cpattern');
add_action('all_bd_activated', 'bd_all_moduls_active_cpattern');
function bd_all_moduls_active_cpattern(){
global $wpdb;	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE 'cpattern'");
    if($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `cpattern` varchar(150) NOT NULL default '0'");
    }	
}
/* end BD */

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'filters');