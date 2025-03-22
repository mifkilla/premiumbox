<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Email notification logs [:en_US][ru_RU:]Лог e-mail уведомлений[:ru_RU]
description: [en_US:]Email notification logs [:en_US][ru_RU:]Лог e-mail уведомлений[:ru_RU]
version: 2.2
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if(!function_exists('bd_all_moduls_active_emlogs')){
	add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_emlogs');
	add_action('all_bd_activated', 'bd_all_moduls_active_emlogs');
	function bd_all_moduls_active_emlogs(){
	global $wpdb;	
		$table_name= $wpdb->prefix ."email_logs"; 
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`create_date` datetime NOT NULL,
			`to_mail` longtext NOT NULL,
			`subject` longtext NOT NULL,
			`html` longtext NOT NULL,
			`ot_name` longtext NOT NULL,
			`ot_mail` longtext NOT NULL,
			PRIMARY KEY ( `id` ),
			INDEX (`create_date`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
		$wpdb->query($sql);	
	}
}
 
if(!function_exists('admin_menu_emlogs')){ 
	add_action('admin_menu', 'admin_menu_emlogs', 49);
	function admin_menu_emlogs(){
		$plugin = get_plugin_class();
		if(current_user_can('administrator')){
			add_submenu_page("all_mail_temps", __('E-mail logs','pn'), __('E-mail logs','pn'), 'read', "all_emlogs", array($plugin, 'admin_temp'));		
		}
	}
}

if(!function_exists('emlogs_email_send')){ 
	add_filter('wp_mail','emlogs_email_send', 100); 
	function emlogs_email_send($atts){
	global $wpdb;
		
		$arr = array();
		$arr['create_date'] = current_time('mysql');
		$to_mail = explode(',', is_isset($atts,'to'));
		$arr['to_mail'] = pn_strip_input(join(',',$to_mail));
		$arr['subject'] = pn_strip_input(is_isset($atts,'subject'));
		$arr['html'] = pn_strip_input(is_isset($atts,'message'));
		$arr['ot_name'] = pn_strip_input(str_replace(array('<','>'), array('(',')'), is_isset($atts,'headers')));
		$arr['ot_mail'] = pn_strip_input(str_replace(array('<','>'), array('(',')'), is_isset($atts,'headers')));
		$wpdb->insert($wpdb->prefix.'email_logs', $arr);
		
		return $atts;
	}
}

if(!function_exists('del_emlogs')){
	function del_emlogs(){
	global $wpdb;
		$plugin = get_plugin_class();
		if(!$plugin->is_up_mode()){
			$count_day = intval($plugin->get_option('logssettings', 'delete_merchantlogs_day'));
			if(!$count_day){ $count_day = 10; }
			if($count_day > 0){
				$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
				$ldate = date('Y-m-d H:i:s', $time);
				$wpdb->query("DELETE FROM ".$wpdb->prefix."email_logs WHERE create_date < '$ldate'");
			}
		}
	}	
} 

if(!function_exists('del_emlogs_list_cron_func')){
	add_filter('list_cron_func', 'del_emlogs_list_cron_func');
	function del_emlogs_list_cron_func($filters){
		$filters['del_emlogs'] = array(
			'title' => __('Delete e-mail log','pn'),
			'site' => '1day',
		);
		return $filters;
	}
}

if(!function_exists('emlogs_list_logs_settings')){
	add_filter('list_logs_settings', 'emlogs_list_logs_settings');
	function emlogs_list_logs_settings($filters){		
		$filters['delete_merchantlogs_day'] = array(
			'title' => __('Delete e-mail log','pn') .' ('. __('days','pn') .')',
			'count' => 10,
			'minimum' => 1,
		);
		return $filters;
	} 
}

$plugin = get_plugin_class();
$plugin->include_patch(__FILE__, 'list');