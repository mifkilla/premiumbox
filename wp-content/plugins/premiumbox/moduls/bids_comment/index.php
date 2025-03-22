<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Comments for orders[:en_US][ru_RU:]Комментарии к заявкам[:ru_RU]
description: [en_US:]Comments for orders for administrator and users[:en_US][ru_RU:]Комментарии к заявкам для администратора и клиентов[:ru_RU]
version: 2.2
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if(is_admin()){
	add_filter('pntable_select_sql_pn_bids', 'pntable_select_sql_pn_bids_bidscommen'); 
	function pntable_select_sql_pn_bids_bidscommen($select){
	global $wpdb;
	
		$select .= "
		, (SELECT COUNT(". $wpdb->prefix ."comment_system.id) FROM ". $wpdb->prefix ."comment_system WHERE itemtype='user_bid' AND item_id = ". $wpdb->prefix ."exchange_bids.id) AS has_user_comment
		, (SELECT COUNT(". $wpdb->prefix ."comment_system.id) FROM ". $wpdb->prefix ."comment_system WHERE itemtype='admin_bid' AND item_id = ". $wpdb->prefix ."exchange_bids.id) AS has_admin_comment
		";
		
		return $select;
	}
	
	add_filter('onebid_icons','onebid_icons_bidscomment',199,3);
	function onebid_icons_bidscomment($onebid_icon, $item, $data_fs){
		
		$comment_user = intval(is_isset($item,'has_user_comment'));
		$c_u = '';
		if($comment_user){
			$c_u = 'has_comment';
		}	
		
		$comment_admin = intval(is_isset($item,'has_admin_comment'));
		$c_a = '';
		if($comment_admin){
			$c_a = 'has_comment';
		}	
		
		$user_comm = '<div class="bs_comus js_csl user_bid_comment '. $c_u .' user_bid_comment-'. $item->id .'" data-bd="user_bid_comment" data-id="'. $item->id .'" data-title="'. __('user comm.','pn') .'">'. __('user comm.','pn') .'</div>';
		$onebid_icon['user_com'] = array(
			'type' => 'html',
			'html' => $user_comm,
		);

		$admin_comm = '<div class="bs_comad js_csl admin_bid_comment '. $c_a .' admin_bid_comment-'. $item->id .'" data-bd="admin_bid_comment" data-id="'. $item->id .'" data-title="'. __('admin comm.','pn') .'">'. __('admin comm.','pn') .'</div>';
		$onebid_icon['admin_com'] = array(
			'type' => 'html',
			'html' => $admin_comm,
		);	
		
		return $onebid_icon;
	} 

	add_filter('csl_get_user_bid_comment', 'def_csl_get_user_bid_comment', 10, 2);
	function def_csl_get_user_bid_comment($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_bids')){
			$comment = '';
			$last = '';
			
			$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."comment_system WHERE item_id='$id' AND itemtype='user_bid' ORDER BY comment_date DESC");
			foreach($items as $item){ 
				$last .= '
				<div class="one_comment">
					<div class="one_comment_author"><span class="one_comment_del js_csl_del" data-bd="user_bid_comment" data-id="'. $item->id .'"></span><a href="'. pn_edit_user_link($item->user_id) .'" target="_blank">'. pn_strip_input($item->user_login) .'</a>, <span class="one_comment_date">'. get_pn_time($item->comment_date,'d.m.Y, H:i:s') .'</span></div>
					<div class="one_comment_text">
						'. pn_strip_input($item->text_comment) .'
					</div>
				</div>
				';
			}
			
			$log['status'] = 'success';
			$log['comment'] = '';
			$log['count'] = count($items);
			$log['last'] = $last;
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}
		
		return $log;
	}	

	add_filter('csl_get_admin_bid_comment', 'def_csl_get_admin_bid_comment', 10, 2);
	function def_csl_get_admin_bid_comment($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_bids')){
			$comment = '';
			$last = '';
			
			$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."comment_system WHERE item_id='$id' AND itemtype='admin_bid' ORDER BY comment_date DESC");
			foreach($items as $item){ 
				$last .= '
				<div class="one_comment">
					<div class="one_comment_author"><span class="one_comment_del js_csl_del" data-bd="admin_bid_comment" data-id="'. $item->id .'"></span><a href="'. pn_edit_user_link($item->user_id) .'" target="_blank">'. pn_strip_input($item->user_login) .'</a>, <span class="one_comment_date">'. get_pn_time($item->comment_date,'d.m.Y, H:i:s') .'</span></div>
					<div class="one_comment_text">
						'. pn_strip_input($item->text_comment) .'
					</div>
				</div>
				';
			}
			
			$log['status'] = 'success';
			$log['comment'] = '';
			$log['count'] = count($items);
			$log['last'] = $last;
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}
		
		return $log;
	}

	add_filter('csl_add_user_bid_comment', 'def_csl_add_user_bid_comment', 10, 2);
	function def_csl_add_user_bid_comment($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_bids')){
			$ui = wp_get_current_user();
			$text = pn_strip_input(is_param_post('comment'));
			$log['status'] = 'success';
			if($text){
				$arr = array();
				$arr['comment_date'] = current_time('mysql');
				$arr['user_id'] = $ui->ID;
				$arr['user_login'] = pn_strip_input($ui->user_login);
				$arr['text_comment'] = $text;
				$arr['itemtype'] = 'user_bid';
				$arr['item_id'] = $id;
				$wpdb->insert($wpdb->prefix.'comment_system', $arr);
			} 
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}		
		
		return $log;
	}
	
	add_filter('csl_add_admin_bid_comment', 'def_csl_add_admin_bid_comment', 10, 2);
	function def_csl_add_admin_bid_comment($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_bids')){
			$ui = wp_get_current_user();
			$text = pn_strip_input(is_param_post('comment'));
			$log['status'] = 'success';
			if($text){
				$arr = array();
				$arr['comment_date'] = current_time('mysql');
				$arr['user_id'] = $ui->ID;
				$arr['user_login'] = pn_strip_input($ui->user_login);
				$arr['text_comment'] = $text;
				$arr['itemtype'] = 'admin_bid';
				$arr['item_id'] = $id;
				$wpdb->insert($wpdb->prefix.'comment_system', $arr);
			} 
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}		
		
		return $log;
	}

	add_filter('csl_del_user_bid_comment', 'def_csl_del_user_bid_comment', 10, 2);
	function def_csl_del_user_bid_comment($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_bids')){
			$log['status'] = 'success';
			$wpdb->query("DELETE FROM ".$wpdb->prefix."comment_system WHERE itemtype = 'user_bid' AND id = '$id'");
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}		
		
		return $log;
	}

	add_filter('csl_del_admin_bid_comment', 'def_csl_del_admin_bid_comment', 10, 2);
	function def_csl_del_admin_bid_comment($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_bids')){
			$log['status'] = 'success';
			$wpdb->query("DELETE FROM ".$wpdb->prefix."comment_system WHERE itemtype = 'admin_bid' AND id = '$id'");
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}		
		
		return $log;
	}	
}

add_filter('direction_instruction', 'bidscomment_direction_instruction', 100000, 3);
function bidscomment_direction_instruction($instruction, $txt_name, $direction){
global $wpdb, $premiumbox, $bids_data;	
	
	$not_status = array('timeline_txt', 'description_txt');
	if(!in_array($txt_name, $not_status) and isset($bids_data->id)){
		$bid_id = $bids_data->id;
		$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."comment_system WHERE item_id='$bid_id' AND itemtype='user_bid' ORDER BY comment_date DESC");
		$comment_user = pn_strip_input(is_isset($item,'text_comment'));
		if($comment_user){
			$instruction .= '<div class="comment_user">'. $comment_user .'</div>';
		}	
	}
	
	return $instruction;
}

add_filter('notify_tags_bids', 'bidscomment_notify_tags_bids', 99, 2);
function bidscomment_notify_tags_bids($notify_tags, $obmen){
global $wpdb;
	
	$bid_id = $obmen->id;
	$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."comment_system WHERE item_id='$bid_id' AND itemtype='user_bid' ORDER BY comment_date DESC");
	$comment_user = pn_strip_input(is_isset($item,'text_comment'));
	
	$notify_tags['[comment_user]'] = $comment_user;
	
	return $notify_tags; 
}

add_filter('shortcode_notify_tags_bids','bidscomment_shortcode_notify_tags_bids');
function bidscomment_shortcode_notify_tags_bids($tags){
	$tags['comment_user'] = array(
		'title' => __('Comment to user','pn'),
		'start' => '[comment_user]',
	);
	return $tags;
}