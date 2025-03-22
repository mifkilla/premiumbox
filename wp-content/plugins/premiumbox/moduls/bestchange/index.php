<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]BestChange parser[:en_US][ru_RU:]BestChange парсер[:ru_RU]
description: [en_US:]BestChange parser[:en_US][ru_RU:]BestChange парсер[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_bestchange');
add_action('all_bd_activated', 'bd_all_moduls_active_bestchange');
function bd_all_moduls_active_bestchange(){
global $wpdb;	
	 	
	$table_name = $wpdb->prefix ."bestchange_currency_codes";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`currency_code_id` bigint(20) NOT NULL default '0',
		`currency_code_title` longtext NOT NULL,
		PRIMARY KEY ( `id` ),
		INDEX (`currency_code_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql);	 
	
	$table_name = $wpdb->prefix ."bestchange_directions";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`direction_id` bigint(20) NOT NULL default '0',
		`currency_id_give` bigint(20) NOT NULL default '0',
		`currency_id_get` bigint(20) NOT NULL default '0',
		`v1` bigint(20) NOT NULL default '0',
		`v2` bigint(20) NOT NULL default '0',
		`pars_position` varchar(250) NOT NULL default '0',
		`min_res` varchar(250) NOT NULL default '0',
		`step` varchar(250) NOT NULL default '0',
		`reset_course` int(1) NOT NULL default '0',
		`standart_course_give` varchar(250) NOT NULL default '0',
		`standart_course_get` varchar(250) NOT NULL default '0',
		`min_sum` varchar(250) NOT NULL default '0',
		`max_sum` varchar(250) NOT NULL default '0',		
		`standart_new_parser` bigint(20) NOT NULL default '0',
		`standart_new_parser_actions_give` varchar(150) NOT NULL default '0',
		`standart_new_parser_actions_get` varchar(150) NOT NULL default '0',
		`minsum_new_parser` bigint(20) NOT NULL default '0',
		`minsum_new_parser_actions` varchar(150) NOT NULL default '0',
		`maxsum_new_parser` bigint(20) NOT NULL default '0',
		`maxsum_new_parser_actions` varchar(150) NOT NULL default '0',
		`black_ids` longtext NOT NULL,
		`white_ids` longtext NOT NULL,
		`status` int(1) NOT NULL default '0',
		PRIMARY KEY ( `id` ),
		INDEX (`direction_id`),
		INDEX (`currency_id_give`),
		INDEX (`currency_id_get`),
		INDEX (`status`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'bestchange_id'"); 
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `bestchange_id` bigint(20) NOT NULL default '0'");
	}		
	
	$wpdb->query("ALTER TABLE ". $wpdb->prefix ."bestchange_directions CHANGE `pars_position` `pars_position` varchar(250) NOT NULL"); /* 2.2 */
}	

if(!function_exists('bestchange_pn_caps')){
	add_filter('pn_caps','bestchange_pn_caps');
	function bestchange_pn_caps($pn_caps){
		$pn_caps['pn_bestchange'] = __('Bestchange parser','pn');
		return $pn_caps;
	}
}

add_action('admin_menu', 'admin_menu_bestchange');
function admin_menu_bestchange(){
global $premiumbox;
	if(current_user_can('administrator') or current_user_can('pn_bestchange')){
		add_menu_page(__('BestChange parser','pn'), __('BestChange parser','pn'), 'read', "pn_bestchange", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('parser'));
		add_submenu_page("pn_bestchange", __('Settings','pn'), __('Settings','pn'), 'read', "pn_bestchange", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_bestchange", __('Adjustments','pn'), __('Adjustments','pn'), 'read', "pn_bc_corrs", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_bestchange", __('Add adjustment','pn'), __('Add adjustment','pn'), 'read', "pn_bc_add_corrs", array($premiumbox, 'admin_temp'));
	}
}

add_action('item_bccorrs_deactive', 'item_bccorrs_deactive_bestchange', 10, 2);
add_action('item_bccorrs_delete', 'item_bccorrs_deactive_bestchange', 10, 2);
function item_bccorrs_deactive_bestchange($item_id, $item){
global $wpdb;	
	$wpdb->update($wpdb->prefix."directions", array('bestchange_id' => 0), array('id'=> $item->direction_id));
}

add_action('item_bccorrs_active', 'item_bccorrs_active_bestchange', 10, 2);
function item_bccorrs_active_bestchange($item_id, $item){
global $wpdb;	
	$wpdb->update($wpdb->prefix."directions", array('bestchange_id' => 1), array('id'=> $item->direction_id));
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'settings');
$premiumbox->include_patch(__FILE__, 'api');
$premiumbox->include_patch(__FILE__, 'filters');
$premiumbox->include_patch(__FILE__, 'list');
$premiumbox->include_patch(__FILE__, 'add');