<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Transaction confirmations counter (crypto)[:en_US][ru_RU:]Счетчик подтверждений транзакции (крипто)[:ru_RU]
description: [en_US:]Transaction confirmations counter (crypto)[:en_US][ru_RU:]Счетчик подтверждений транзакции (крипто)[:ru_RU]
version: 2.2
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

/* BD */
add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_bcc');
function bd_all_moduls_active_bcc(){
global $wpdb;	
	
	$table_name= $wpdb->prefix ."bcc_logs";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`createdate` datetime NOT NULL,
		`bid_id` bigint(20) NOT NULL default '0',
		`counter` bigint(20) NOT NULL default '0',
		PRIMARY KEY ( `id` ),
		INDEX (`bid_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql); 
	
}
/* end BD */

add_action('merchant_confirm_count', 'merchant_confirm_count_bcc', 10, 5);
function merchant_confirm_count_bcc($id, $counter, $bids_data, $direction_data, $conf_count=0){
global $wpdb, $premiumbox;

	$before = intval($premiumbox->get_option('bcc','before'));
	$after = intval($premiumbox->get_option('bcc','after'));

	$id = intval($id);
	$counter = intval($counter);
	$conf_count = intval($conf_count);
	$count_after = $conf_count + $after;
	$write = 0;
	if($counter >= $before or $before == 0){
		if($after == 0 or $counter <= $count_after){
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."bcc_logs WHERE bid_id='$id' AND counter='$counter'");
			if($cc == 0){
				$arr = array();
				$arr['createdate'] = current_time('mysql');
				$arr['bid_id'] = $id;
				$arr['counter'] = $counter;
				$wpdb->insert($wpdb->prefix.'bcc_logs', $arr);
			}
		}	
	}
}

add_filter('change_bidstatus', 'bcc_change_bidstatus', 200, 4);   
function bcc_change_bidstatus($item, $set_status, $place, $user_or_system){
global $wpdb;
	if($set_status == 'realdelete' or $set_status == 'archived'){
		$id = $item->id;
		$wpdb->query("DELETE FROM ".$wpdb->prefix."bcc_logs WHERE id='$id'");
	}
	return $item;
}

add_filter('onebid_icons','onebid_icons_bcc',99,3);
function onebid_icons_bcc($onebid_icon, $item, $data_fs){
	
	$key = $item->m_in;
	$bcc_keys = apply_filters('bcc_keys', array());
	$show = 0;
	foreach($bcc_keys as $bcc_key){
		if(strstr($key, $bcc_key)){
			$show = 1;
			break;
		}
	}
	if($show == 1){
		$onebid_icon['bcc'] = array(
			'type' => 'text',
			'title' => __('Number of confirmations','pn') . ': [bcc]',
			'label' => __('Confirmations','pn') . ': [bcc]',
		);		
	}
	
	return $onebid_icon;
}

add_filter('get_bids_replace_text','get_bids_replace_text_bcc',99,3);
function get_bids_replace_text_bcc($text, $item, $data_fs){
global $wpdb;	
	
	if(strstr($text, '[bcc]')){
		$item_id = $item->id;
		$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."bcc_logs WHERE bid_id='$item_id' ORDER BY (counter -0.0) DESC");
		$confirm_count = intval(is_isset($data,'counter'));
		$text = str_replace('[bcc]', '<span class="item_bcc">' . $confirm_count . '</span>',$text);
	}
	
	return $text;
}

add_filter('direction_instruction_tags', 'bcc_directions_tags', 10, 2); 
function bcc_directions_tags($tags, $key){
	
	$in_page = array('description_txt','timeline_txt','window_txt');
	if(!in_array($key, $in_page)){	
		$tags['confirm_count'] = array(
			'title' => __('Number of confirmations','pn'),
			'start' => '[confirm_count]',
		);
		$tags['confirm_count_time'] = array(
			'title' => __('Time of confirmation receiving','pn'),
			'start' => '[confirm_count_time]',
		);		
	}
	
	return $tags;
}

add_filter('direction_instruction','bcc_direction_instruction', 10, 6);
function bcc_direction_instruction($instruction, $txt_name, $direction, $vd1, $vd2, $m_in=''){
global $wpdb, $bids_data;
	
	if(isset($bids_data->id)){
		$data = '';
		if(strstr($instruction, '[confirm_count]') or strstr($instruction, '[confirm_count_time]')){
			$item_id = $bids_data->id;
			$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."bcc_logs WHERE bid_id='$item_id' ORDER BY (counter -0.0) DESC");
		}	
		if(strstr($instruction, '[confirm_count]')){
			$confirm_count = intval(is_isset($data,'counter'));
			$instruction = str_replace('[confirm_count]', $confirm_count ,$instruction);
		}
		if(strstr($instruction, '[confirm_count_time]')){
			$createdate = trim(is_isset($data,'createdate'));
			$date = '---';
			if($createdate != '0000-00-00 00:00:00'){
				$createtime = strtotime($createdate);
				$date = date('d.m.Y, H:i', $createtime);
			}
			$instruction = str_replace('[confirm_count_time]', $date ,$instruction);
		}
	}
	
	return $instruction;
} 

add_action('admin_menu', 'admin_menu_bcc', 500);
function admin_menu_bcc(){
global $premiumbox;		
	add_submenu_page("pn_moduls", __('Number of confirmations','pn'), __('Number of confirmations','pn'), 'administrator', "pn_bcc_settings", array($premiumbox, 'admin_temp'));
	if(current_user_can('administrator') or current_user_can('pn_bids')){
		add_submenu_page("pn_bids", __('Confirmation log','pn'), __('Confirmation log','pn'), 'read', "pn_bcc", array($premiumbox, 'admin_temp'));		
	}	
} 

add_action('pn_adminpage_title_pn_bcc_settings', 'pn_admin_title_pn_bcc_settings');
function pn_admin_title_pn_bcc_settings($page){
	_e('Number of confirmations','pn');
}

add_action('pn_adminpage_content_pn_bcc_settings','pn_admin_content_pn_bcc_settings');
function pn_admin_content_pn_bcc_settings(){
global $wpdb, $premiumbox;

	$form = new PremiumForm();

	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => '',
		'submit' => __('Save','pn'),
	);
	$options['before'] = array(
		'view' => 'input',
		'title' => __('Sequence number of confirmation, from which logging of confirmations will start','pn'),
		'default' => $premiumbox->get_option('bcc','before'),
		'name' => 'before',
		'work' => 'int',
	);
	$options['after'] = array(
		'view' => 'input',
		'title' => __('Number of confirmations that will be written into log','pn'),
		'default' => $premiumbox->get_option('bcc','after'),
		'name' => 'after',
		'work' => 'int',
	);
	$options['displayuser'] = array(
		'view' => 'select',
		'title' => __('Display number of confirmations in order and personal area','pn'),
		'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
		'default' => $premiumbox->get_option('bcc','displayuser'),
		'name' => 'displayuser',
	);
	$params_form = array(
		'filter' => 'pn_bcc_settings_options',
		'method' => 'ajax',
		'button_title' => __('Save','pn'),
	);
	$form->init_form($params_form, $options); 
}  

add_action('premium_action_pn_bcc_settings','def_premium_action_pn_bcc_settings');
function def_premium_action_pn_bcc_settings(){
global $wpdb, $premiumbox;	

	only_post();
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$before = intval(is_param_post('before'));
	$premiumbox->update_option('bcc', 'before', $before);
	
	$after = intval(is_param_post('after'));
	$premiumbox->update_option('bcc', 'after', $after);	

	$displayuser = intval(is_param_post('displayuser'));
	$premiumbox->update_option('bcc', 'displayuser', $displayuser);	

	$back_url = is_param_post('_wp_http_referer');
	$back_url .= '&reply=true';
			
	$form->answer_form($back_url);
} 

add_filter('lists_table_userxch', 'bcc_lists_userxch');
function bcc_lists_userxch($lists){
global $premiumbox;	

	$displayuser = intval($premiumbox->get_option('bcc','displayuser'));
	if($displayuser){
		$lists['bcc'] = __('Number of confirmations','pn');
	}	
	
	return $lists;
} 

add_filter('body_list_userxch', 'bcc_body_list_userxch', 10, 6);
function bcc_body_list_userxch($data_item, $item, $key, $title, $date_format, $time_format){		
global $wpdb;
				
	if($key == 'bcc'){
		$item_id = $item->id;
		$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."bcc_logs WHERE bid_id='$item_id' ORDER BY (counter -0.0) DESC");
		return '<span class="bcc">'. intval(is_isset($data,'counter')) .'</span>';
	}	
	
	return $data_item;
}

/* cron */
function del_bcclogs(){
global $wpdb, $premiumbox;
	if(!$premiumbox->is_up_mode()){
		$count_day = intval($premiumbox->get_option('logssettings', 'delete_bcclogs_day'));
		if(!$count_day){ $count_day = 30; }

		$count_day = apply_filters('delete_bcclogs_day', $count_day);
		if($count_day > 0){
			$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
			$ldate = date('Y-m-d H:i:s', $time);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."bcc_logs WHERE createdate < '$ldate'");
		}
	}
} 

add_filter('list_cron_func', 'del_bcclogs_list_cron_func');
function del_bcclogs_list_cron_func($filters){
	$filters['del_bcclogs'] = array(
		'title' => __('Deleting confirmations log','pn'),
		'site' => '1day',
	);
	return $filters;
}

add_filter('list_logs_settings', 'bcclogs_list_logs_settings');
function bcclogs_list_logs_settings($filters){
			
	$filters['delete_bcclogs_day'] = array(
		'title' => __('Deleting confirmations log','pn') .' ('. __('days','pn') .')',
		'count' => 30,
		'minimum' => 1,
	);
		
	return $filters;
} 
/* end cron */

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'list');