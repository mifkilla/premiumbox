<?php 
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]AML bot[:en_US][ru_RU:]AML bot[:ru_RU]
description: [en_US:]AML bot[:en_US][ru_RU:]AML bot[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_bd_activated', 'bd_all_moduls_active_amlbot');
add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_amlbot');
function bd_all_moduls_active_amlbot(){
global $wpdb;
			
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'aml'");
    if ($query == 0) { 
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `aml` longtext NOT NULL");
    }	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'aml_give'");
    if ($query == 0) { 
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `aml_give` longtext NOT NULL");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'aml_get'");
    if ($query == 0) { 
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `aml_get` longtext NOT NULL");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'aml_merch'");
    if ($query == 0) { 
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `aml_merch` longtext NOT NULL");
    }	
	
}

function get_aml_assets($ind=0){
	$ind = intval($ind);
	if($ind == 1){
		$assets = array(
			'BTC' => 'BTC',
			'ETH' => 'ETH',
			'LTC' => 'LTC',
			'BCH' => 'BCH',
			'XRP' => 'XRP',
			'USDT' => 'USDT'
		);		
	} else {
		$assets = array(
			'BTC' => 'BTC',
			'ETH' => 'ETH',
			'LTC' => 'LTC',
			'BCH' => 'BCH',
			'XRP' => 'XRP',
			'TetherOMNI' => 'TetherOMNI',
			'TetherERC20' => 'TetherERC20'
		);
	}
	
	return $assets;
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'class');
$premiumbox->include_patch(__FILE__, 'settings');
$premiumbox->include_patch(__FILE__, 'filters');