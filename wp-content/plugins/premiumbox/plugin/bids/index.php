<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('admin_menu', 'admin_menu_bids');
function admin_menu_bids(){
global $premiumbox;
	if(current_user_can('administrator') or current_user_can('pn_bids')){	
		add_menu_page(__('Orders','pn'), __('Orders','pn'), 'read', "pn_bids", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('icon'), 3);
	}
}

add_filter('pn_caps','bids_pn_caps');
function bids_pn_caps($pn_caps){
	$pn_caps['pn_bids'] = __('To process exchange orders','pn');
	$pn_caps['pn_bids_change'] = __('Changing order status','pn');
	$pn_caps['pn_bids_delete'] = __('Complete removal of orders','pn');
	$pn_caps['pn_bids_payouts'] = __('Making payouts by button','pn');
	return $pn_caps;
}


add_filter('list_icon_indicators', 'bids_icon_indicators', 0);
function bids_icon_indicators($lists){
	$plugin = get_plugin_class();
	$lists['bids'] = array(
		'title' => __('Orders on hold are to be processed','pn'),
		'img' => $plugin->plugin_url .'images/money.gif',
		'link' => admin_url('admin.php?page=pn_bids&paystatus=2')
	);
	return $lists;
}

add_filter('count_icon_indicator_bids', 'def_icon_indicator_bids');
function def_icon_indicator_bids($count){
	global $wpdb;
	if(current_user_can('administrator') or current_user_can('pn_bids')){
		$page = is_param_get('page');
		if($page != 'pn_bids'){
			$count = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."exchange_bids WHERE status IN('payed','realpay','verify')");
		}
	}	
	return $count;
}

add_filter('get_statusbids_for_admin', 'get_statusbids_for_admin_remove', 1000);
function get_statusbids_for_admin_remove($st){
	if(current_user_can('administrator') or current_user_can('pn_bids_delete')){
		$st['realdelete'] = array(
			'name' => 'realdelete',
			'title' => __('complete removal','pn'),
			'color' => '#ffffff',
			'background' => '#ff0000',
		);		
	}
	return $st;
}

add_filter('change_bidstatus', 'delfile_change_bidstatus', 500, 4);   
function delfile_change_bidstatus($item, $set_status, $place, $user_or_system){
global $premiumbox;	
	if($set_status == 'realdelete'){
		$bids_dir = $premiumbox->upload_dir . 'bids/';
		$my_dir = wp_upload_dir();
		$bids_dir_old = $my_dir['basedir'].'/bids/';
		
		$old_file = $bids_dir_old . $item->id .'.txt';
		if(is_file($old_file)){
			@unlink($old_file);
		}
		$file = $bids_dir . $item->id .'.php';
		if(is_file($file)){
			@unlink($file);
		}	
	}
	return $item;
}

add_filter('change_bidstatus', 'sethashdata_change_bidstatus', 900, 4); //2400  
function sethashdata_change_bidstatus($item, $set_status, $place, $user_or_system){
	if($set_status == 'new' and $place == 'exchange_button'){
		$tables = bid_hashkey();
		$hashdata = bid_hashdata($item->id, '', $tables); 
		$hashdata = @serialize($hashdata);
		$item = pn_object_replace($item, array('hashdata'=>$hashdata));
	}
	return $item;
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'bids');
$premiumbox->include_patch(__FILE__, 'ajax');