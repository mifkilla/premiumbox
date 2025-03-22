<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Logging changes in currency code rates[:en_US][ru_RU:]Логирование изменения курсов кодов валют[:ru_RU]
description: [en_US:]Logging changes in currency code rates[:en_US][ru_RU:]Логирование изменения курсов кодов валют[:ru_RU]
version: 2.2
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_bd_activated', 'bd_pn_moduls_active_cccourselogs');
add_action('all_moduls_active_'.$name, 'bd_pn_moduls_active_cccourselogs');
function bd_pn_moduls_active_cccourselogs(){
global $wpdb;	
	
	$table_name= $wpdb->prefix ."currency_codes_courselogs";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`user_id` bigint(20) NOT NULL default '0',
		`user_login` varchar(150) NOT NULL,
		`currency_code_id` bigint(20) default '0',
		`currency_code_title` longtext NOT NULL,
		`last_internal_rate` varchar(150) NOT NULL default '0',
		`internal_rate` varchar(150) NOT NULL default '0',		
		`who` varchar(50) NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`user_id`),
		INDEX (`currency_code_id`),
		INDEX (`who`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);
	
}
 
add_action('admin_menu', 'admin_menu_cccourselogs', 100);
function admin_menu_cccourselogs(){
global $premiumbox;	
	if(current_user_can('administrator') or current_user_can('pn_change_ir')){
		add_submenu_page("pn_currency", __('Logging changes in currency code rates','pn'), __('Logging changes in currency code rates','pn'), 'read', "pn_cccourselogs", array($premiumbox, 'admin_temp'));
	}
}

add_action('pntable_currency_codes_save', 'cdl_pntable_currency_codes_save', 100);  function cdl_pntable_currency_codes_save(){ set_currency_code_log('pntable_currency_codes_save'); }
add_action('item_currency_code_edit', 'cdl_item_currency_code_edit', 100); function cdl_item_currency_code_edit(){ set_currency_code_log('item_currency_code_edit'); }
add_action('item_currency_code_add', 'cdl_item_currency_code_add', 100); function cdl_item_currency_code_add(){ set_currency_code_log('item_currency_code_add'); }

add_action('load_new_parser_courses', 'cdl_load_new_parser_courses', 100); function cdl_load_new_parser_courses(){ set_currency_code_log('load_new_parser_courses'); }
add_action('item_parser_pairs_edit', 'cdl_item_parser_pairs_edit', 100); function cdl_item_parser_pairs_edit(){ set_currency_code_log('item_parser_pairs_edit'); }
add_action('parser_index_edit_end', 'cdl_parser_index_edit_end', 100); function cdl_parser_index_edit_end(){ set_currency_code_log('parser_index_edit_end'); }
add_action('pntable_parser_index_save', 'cdl_pntable_parser_index_save', 100); function cdl_pntable_parser_index_save(){ set_currency_code_log('pntable_parser_index_save'); }
add_action('pntable_parser_index_action', 'cdl_pntable_parser_index_action', 100); function cdl_pntable_parser_index_action(){ set_currency_code_log('pntable_parser_index_action'); }
add_action('pntable_parser_pairs_save', 'cdl_pntable_parser_pairs_save', 100); function cdl_pntable_parser_pairs_save(){ set_currency_code_log('pntable_parser_pairs_save'); }
add_action('pntable_parser_pairs_action', 'cdl_pntable_parser_pairs_action', 100); function cdl_pntable_parser_pairs_action(){ set_currency_code_log('pntable_parser_pairs_action'); }
add_action('pntable_parsercourses_deleteall', 'cdl_pntable_parsercourses_deleteall', 100); function cdl_pntable_parsercourses_deleteall(){ set_currency_code_log('pntable_parsercourses_deleteall'); }

function set_currency_code_log($who=''){
global $wpdb;	
 
	$items = $wpdb->get_results("
	SELECT *, 
	(SELECT ". $wpdb->prefix ."currency_codes_courselogs.internal_rate FROM ". $wpdb->prefix ."currency_codes_courselogs WHERE ". $wpdb->prefix ."currency_codes_courselogs.currency_code_id = ". $wpdb->prefix ."currency_codes.id ORDER BY ". $wpdb->prefix ."currency_codes_courselogs.id DESC LIMIT 1) AS last_internal_rate 
	FROM ". $wpdb->prefix ."currency_codes ORDER BY ". $wpdb->prefix ."currency_codes.id DESC
	");
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	$sqls = array();
	$date = current_time('mysql');
	foreach($items as $item){		
		$internal_rate = is_cc_rate($item->id, $item);
		$last_internal_rate = is_sum($item->last_internal_rate);
 		if($last_internal_rate != $internal_rate){	
			$sqls[] = array(
				'create_date' => $date,
				'currency_code_id' => intval($item->id),
				'currency_code_title' => pn_strip_input($item->currency_code_title),
				'user_id' => intval($user_id),
				'user_login' => is_isset($ui,'user_login'),
				'last_internal_rate' => is_sum($last_internal_rate),
				'internal_rate' => is_sum($internal_rate),
				'who' => pn_strip_input($who),
			);
		}
	}	
	pn_db_insert($wpdb->prefix ."currency_codes_courselogs", $sqls);
}

function del_cccourselogs(){
global $wpdb, $premiumbox;
	if(!$premiumbox->is_up_mode()){
		$plugin = get_plugin_class();
		$count_day = intval($plugin->get_option('logssettings', 'delete_cccourselogs_day'));
		if(!$count_day){ $count_day = 30; }
		if($count_day > 0){
			$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
			$ldate = date('Y-m-d H:i:s', $time);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."currency_codes_courselogs WHERE create_date < '$ldate'");
		}
	}
} 

add_filter('list_cron_func', 'del_cccourselogs_list_cron_func');
function del_cccourselogs_list_cron_func($filters){	
	$filters['del_cccourselogs'] = array(
		'title' => __('Deleting logs about changes in rates in currency codes','pn'),
		'site' => '1day',
	);
	return $filters;
}

add_filter('list_logs_settings', 'cccourselogs_list_logs_settings');
function cccourselogs_list_logs_settings($filters){		
	$filters['delete_cccourselogs_day'] = array(
		'title' => __('Deleting logs about changes in rates in currency codes','pn') .' ('. __('days','pn') .')',
		'count' => 30,
		'minimum' => 1,
	);
	return $filters;
} 

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'list');