<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Logging changes in exchange rates[:en_US][ru_RU:]Логирование изменения курсов обмена[:ru_RU]
description: [en_US:]Logging changes in exchange rates. Notification window[:en_US][ru_RU:]Логирование изменения курсов обмена[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_bd_activated', 'bd_all_moduls_active_courselogs');
add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_courselogs');
function bd_all_moduls_active_courselogs(){
global $wpdb;	
	
	$table_name= $wpdb->prefix ."direction_courselogs";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`user_id` bigint(20) NOT NULL default '0',
		`user_login` varchar(150) NOT NULL,
		`direction_id` bigint(20) default '0',
		`currency_id_give` bigint(20) NOT NULL default '0',
		`currency_id_get` bigint(20) NOT NULL default '0',
		`lcourse_give` varchar(150) NOT NULL default '0',
		`lcourse_get` varchar(150) NOT NULL default '0',
		`course_give` varchar(150) NOT NULL default '0',
		`course_get` varchar(150) NOT NULL default '0',		
		`who` varchar(50) NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`user_id`),
		INDEX (`direction_id`),
		INDEX (`who`),
		INDEX (`currency_id_give`),
		INDEX (`currency_id_get`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);

}
 
add_action('admin_menu', 'pn_adminpage_courselogs', 1000);   
function pn_adminpage_courselogs(){
global $premiumbox;	
	if(current_user_can('administrator') or current_user_can('pn_directions')){
		add_submenu_page("pn_directions", __('Logging changes in exchange rates','pn'), __('Logging changes in exchange rates','pn'), 'read', "pn_courselogs", array($premiumbox, 'admin_temp'));
	}
}

add_action('item_direction_edit', 'sdl_item_direction_edit', 100); function sdl_item_direction_edit(){ set_direction_log('item_direction_edit'); }
add_action('item_direction_add', 'sdl_item_direction_add', 100); function sdl_item_direction_add(){ set_direction_log('item_direction_add'); }
add_action('pntable_directions_save', 'sdl_pntable_directions_save', 100);  function sdl_pntable_directions_save(){ set_direction_log('pntable_directions_save'); }
add_action('request_bcparser_end', 'sdl_request_bcparser_end', 100); function sdl_request_bcparser_end(){ set_direction_log('request_bcparser_end'); }
add_action('request_bestchange_end', 'sdl_request_bestchange_end', 100); function sdl_request_bestchange_end(){ set_direction_log('request_bestchange_end'); }
add_action('export_direction_end', 'sdl_export_direction_end', 100); function sdl_export_direction_end(){ set_direction_log('export_direction_end'); }
add_action('request_fcourse', 'sdl_request_fcourse', 100); function sdl_request_fcourse(){ set_direction_log('request_fcourse'); }

add_action('load_new_parser_courses', 'sdl_load_new_parser_courses', 100); function sdl_load_new_parser_courses(){ set_direction_log('load_new_parser_courses'); }
add_action('item_parser_pairs_edit', 'sdl_item_parser_pairs_edit', 100); function sdl_item_parser_pairs_edit(){ set_direction_log('item_parser_pairs_edit'); }
add_action('parser_index_edit_end', 'sdl_parser_index_edit_end', 100); function sdl_parser_index_edit_end(){ set_direction_log('parser_index_edit_end'); }
add_action('pntable_parser_index_save', 'sdl_pntable_parser_index_save', 100); function sdl_pntable_parser_index_save(){ set_direction_log('pntable_parser_index_save'); }
add_action('pntable_parser_index_action', 'sdl_pntable_parser_index_action', 100); function sdl_pntable_parser_index_action(){ set_direction_log('pntable_parser_index_action'); }
add_action('pntable_parser_pairs_save', 'sdl_pntable_parser_pairs_save', 100); function sdl_pntable_parser_pairs_save(){ set_direction_log('pntable_parser_pairs_save'); }
add_action('pntable_parser_pairs_action', 'sdl_pntable_parser_pairs_action', 100); function sdl_pntable_parser_pairs_action(){ set_direction_log('pntable_parser_pairs_action'); }
add_action('pntable_parsercourses_deleteall', 'sdl_pntable_parsercourses_deleteall', 100); function sdl_pntable_parsercourses_deleteall(){ set_direction_log('pntable_parsercourses_deleteall'); }

add_action('reservcurs_end', 'sdl_reservcurs_end', 100); function sdl_reservcurs_end(){ set_direction_log('reservcurs_end'); }
add_action('item_bccorrs_edit', 'sdl_item_bccorrs_edit', 100); function sdl_item_bccorrs_edit(){ set_direction_log('item_bccorrs_edit'); }
add_action('pntable_bccorrs_save', 'sdl_pntable_bccorrs_save', 100); function sdl_pntable_bccorrs_save(){ set_direction_log('pntable_bccorrs_save'); }
add_action('pntable_bccorrs_action', 'sdl_pntable_bccorrs_action', 100); function sdl_pntable_bccorrs_action(){ set_direction_log('pntable_bccorrs_action'); }

function set_direction_log($who=''){
global $wpdb;	
 
	$items = $wpdb->get_results("
	SELECT *, 
	(SELECT ". $wpdb->prefix ."direction_courselogs.course_give FROM ". $wpdb->prefix ."direction_courselogs WHERE ". $wpdb->prefix ."direction_courselogs.direction_id = ". $wpdb->prefix ."directions.id ORDER BY ". $wpdb->prefix ."direction_courselogs.id DESC LIMIT 1) AS last_course_give, 
	(SELECT ". $wpdb->prefix ."direction_courselogs.course_get FROM ". $wpdb->prefix ."direction_courselogs WHERE ". $wpdb->prefix ."direction_courselogs.direction_id = ". $wpdb->prefix ."directions.id ORDER BY ". $wpdb->prefix ."direction_courselogs.id DESC LIMIT 1) AS last_course_get 
	FROM ". $wpdb->prefix ."directions ORDER BY ". $wpdb->prefix ."directions.id DESC
	");
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	$sqls = array();
	foreach($items as $item){		
		$dir_c = is_course_direction($item, '', '', 'admin');
		$course_give = is_sum(is_isset($dir_c,'give')); 
		$course_get = is_sum(is_isset($dir_c,'get'));
		$lcourse_give = is_sum(is_isset($item,'last_course_give'));
		$lcourse_get = is_sum(is_isset($item,'last_course_get'));
		if($lcourse_give != $course_give or $lcourse_get != $course_get){
			$sqls[] = array(
				'create_date' => current_time('mysql'),
				'direction_id' => intval($item->id),
				'user_id' => intval($user_id),
				'user_login' => is_isset($ui,'user_login'),
				'currency_id_give' => intval($item->currency_id_give),
				'currency_id_get' => intval($item->currency_id_get),
				'lcourse_give' => $lcourse_give,
				'lcourse_get' => $lcourse_get,
				'course_give' => $course_give,
				'course_get' => $course_get,			
				'who' => pn_strip_input($who),
			);
		}
	}
	pn_db_insert($wpdb->prefix ."direction_courselogs", $sqls);
}

function del_courselogs(){
global $wpdb, $premiumbox;
	if(!$premiumbox->is_up_mode()){
		$count_day = intval($premiumbox->get_option('logssettings', 'delete_courselogs_day'));
		if(!$count_day){ $count_day = 40; }
		if($count_day > 0){
			$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
			$ldate = date('Y-m-d H:i:s', $time);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."direction_courselogs WHERE create_date < '$ldate'");
		}
	}
} 

add_filter('list_cron_func', 'del_courselogs_list_cron_func');
function del_courselogs_list_cron_func($filters){
	$filters['del_courselogs'] = array(
		'title' => __('Deleting logs about changes in rates in direction of exchange','pn'),
		'site' => '1day',
	);
	return $filters;
}

add_filter('list_logs_settings', 'courselogs_list_logs_settings');
function courselogs_list_logs_settings($filters){	
	$filters['delete_courselogs_day'] = array(
		'title' => __('Deleting logs about changes in rates in direction of exchange','pn') .' ('. __('days','pn') .')',
		'count' => 40,
		'minimum' => 1,
	);	
	return $filters;
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'list');