<?php
if( !defined( 'ABSPATH')){ exit(); }

function del_apbd(){
global $wpdb, $premiumbox;
	if(!$premiumbox->is_up_mode()){
		$count_day = intval($premiumbox->get_option('logssettings', 'del_apbd_day'));
		if(!$count_day){ $count_day = 60; }
		$second = $count_day*24*60*60;
		$second = apply_filters('del_apbd_second', $second);
		$time = current_time('timestamp') - $second;
		if($second != '-1'){
			$ldate = date('Y-m-d H:i:s', $time);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."db_admin_logs WHERE trans_date < '$ldate'");
		}
	}
} 

add_filter('list_cron_func', 'del_apbd_list_cron_func');
function del_apbd_list_cron_func($filters){	
	$filters['del_apbd'] = array(
		'title' => __('Deleting logs of administrator actions','pn'),
		'site' => 'now',
	);
	return $filters;
}

add_filter('list_logs_settings', 'apbd_list_logs_settings');
function apbd_list_logs_settings($filters){		
	$filters['del_apbd_day'] = array(
		'title' => __('Deleting logs of administrator actions','pn') .' ('. __('days','pn') .')',
		'count' => 40,
		'minimum' => 1,
	);
	return $filters;
} 