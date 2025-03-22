<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Status "Automatic payout error"[:en_US][ru_RU:]Статус "Ошибка автовыплаты"[:ru_RU]
description: [en_US:]Status "Automatic payout error"[:en_US][ru_RU:]Статус "Ошибка автовыплаты"[:ru_RU]
version: 2.2
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('paymerchant_error', 'payouterror_paymerchant_error', 10, 5);
function payouterror_paymerchant_error($m_id, $error, $item_id, $place, $pay_error){
	if($pay_error < 1){
		$params = array(
			'm_place' => 'modul payouterror',
			'system' => 'system',
		);
		set_bid_status('payouterror', $item_id, $params); 
	}
}

add_filter('list_icon_indicators', 'payouterror_icon_indicators');
function payouterror_icon_indicators($lists){
global $premiumbox;
	$lists['payouterror'] = array(
		'title' => __('Orders with payout error','pn'),
		'img' => $premiumbox->plugin_url .'images/payouterror.png',
		'link' => admin_url('admin.php?page=pn_bids&idspage=1&bidstatus[]=payouterror')
	);
	return $lists;
}

add_filter('count_icon_indicator_payouterror', 'def_icon_indicator_payouterror');
function def_icon_indicator_payouterror($count){
	global $wpdb;
	if(current_user_can('administrator') or current_user_can('pn_bids')){
		$count = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."exchange_bids WHERE status = 'payouterror'");
	}	
	return $count;
}