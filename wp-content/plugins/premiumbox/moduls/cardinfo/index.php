<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Information about bank card[:en_US][ru_RU:]Информация о банковской карте[:ru_RU]
description: [en_US:]Information about bank card[:en_US][ru_RU:]Информация о банковской карте[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_cardinfo');
function bd_all_moduls_active_cardinfo(){
global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'card_scheme_give'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `card_scheme_give` varchar(500) NOT NULL");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'card_issuer_give'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `card_issuer_give` varchar(500) NOT NULL");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'card_country_give'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `card_country_give` varchar(250) NOT NULL");
    }	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'card_scheme'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `card_scheme` varchar(500) NOT NULL");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'card_issuer'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `card_issuer` varchar(500) NOT NULL");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'card_country'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `card_country` varchar(250) NOT NULL");
    }	
	
	$table_name = $wpdb->prefix ."card_detected_memory";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`card` varchar(250) NOT NULL,
		`card_info` longtext NOT NULL,
		PRIMARY KEY ( `id` ),
		INDEX (`card`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);	
		
	$wpdb->query("DELETE FROM ". $wpdb->prefix ."card_detected_memory");	
	
}

add_filter('array_data_create_bids', 'cardinfo_array_data_create_bids', 10, 4);
function cardinfo_array_data_create_bids($array, $direction, $vd1, $vd2){
global $premiumbox;	
	
	$cardinfo = $premiumbox->get_option('cardinfo', 'currency');
	if(!is_array($cardinfo)){ $cardinfo = array(); }
	
	$account_give = trim(is_isset($array, 'account_give'));
	$currency_id_give = intval(is_isset($array, 'currency_id_give'));
	if($account_give and in_array($currency_id_give, $cardinfo)){
		$info = check_data_for_card($account_give);
		if($info['scheme']){
			$array['card_scheme_give'] = pn_strip_input($info['scheme']);
		}
		if($info['issuer']){
			$array['card_issuer_give'] = pn_strip_input($info['issuer']);
		}
		if($info['country']){
			$array['card_country_give'] = pn_strip_input($info['country']);
		}		
	}	
	
	$account_get = trim(is_isset($array, 'account_get'));
	$currency_id_get = intval(is_isset($array, 'currency_id_get'));
	if($account_get and in_array($currency_id_get, $cardinfo)){
		$info = check_data_for_card($account_get);
		if($info['scheme']){
			$array['card_scheme'] = pn_strip_input($info['scheme']);
		}
		if($info['issuer']){
			$array['card_issuer'] = pn_strip_input($info['issuer']);
		}
		if($info['country']){
			$array['card_country'] = pn_strip_input($info['country']);
		}		
	}
	
	return $array;
}

add_filter('onebid_col2', 'onebid_col2_cardinfo', 10, 4);
function onebid_col2_cardinfo($actions, $item, $data_fs, $v){
	
	$actions['card_scheme_give'] = array(
		'type' => 'text',
		'title' => __('Card type','pn'),
		'label' => pn_strip_input($item->card_scheme_give),
	);
	$actions['card_issuer_give'] = array(
		'type' => 'text',
		'title' => __('Issuer','pn'),
		'label' => pn_strip_input($item->card_issuer_give),
	);
	$actions['card_country_give'] = array(
		'type' => 'text',
		'title' => __('Country','pn'),
		'label' => pn_strip_input($item->card_country_give),
	);	
	
	return $actions;
}

add_filter('onebid_col3', 'onebid_col3_cardinfo', 10, 4);
function onebid_col3_cardinfo($actions, $item, $data_fs, $v){
	
	$actions['card_scheme'] = array(
		'type' => 'text',
		'title' => __('Card type','pn'),
		'label' => pn_strip_input($item->card_scheme),
	);
	$actions['card_issuer'] = array(
		'type' => 'text',
		'title' => __('Issuer','pn'),
		'label' => pn_strip_input($item->card_issuer),
	);
	$actions['card_country'] = array(
		'type' => 'text',
		'title' => __('Country','pn'),
		'label' => pn_strip_input($item->card_country),
	);	
	
	return $actions;
}

function check_data_for_card($card){
global $wpdb, $premiumbox;
	
	$info = array(
		'scheme' => '',
		'issuer' => '',
		'country' => '',
	);
	
	$server = intval($premiumbox->get_option('cardinfo','server'));
	$memory = intval($premiumbox->get_option('cardinfo','memory'));
	
	$key = pn_strip_input($premiumbox->get_option('cardinfo','key'));
	$timeout = intval($premiumbox->get_option('cardinfo','timeout'));
	if($timeout < 1){ $timeout = 10; }
		
	$curl_options = array(
		CURLOPT_TIMEOUT => $timeout,
		CURLOPT_CONNECTTIMEOUT => $timeout,
	);	
	
	$card = preg_replace("/\s/", '', $card);
	$bin = mb_substr($card, 0, 6);
	
	$save_memory = 0;
	if($memory){
		$card_memory = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."card_detected_memory WHERE card = '$card'");
		if(isset($card_memory->id)){
			$card_info = @unserialize($card_memory->card_info);
			$info = array(
				'scheme' => mb_strtolower(trim(is_isset($card_info, 'scheme'))),
				'issuer' => trim(is_isset($card_info, 'issuer')),
				'country' => trim(is_isset($card_info, 'country')),
			);
		} else {
			$save_memory = 1;
		}	
	}
	
	if(strlen($info['scheme']) < 1){
		$info = apply_filters('check_data_for_card', $info, $server);
	}	
		
	if(strlen($info['scheme']) < 1){	
		if($server == 1){
			$curl = get_curl_parser('https://api.bincodes.com/bin/?format=json&api_key='. $key .'&bin='. $bin, $curl_options, 'moduls', 'cardinfo');
			$string = $curl['output'];
			if(!$curl['err']){
				$res = @json_decode($string, true);
				if(is_array($res)){
					$info['scheme'] = mb_strtolower(is_isset($res,'card'));
					$info['issuer'] = is_isset($res,'bank');
					$info['country'] = is_isset($res,'country');			
				}
			}		
		} elseif($server == 2){
			$curl = get_curl_parser('https://lookup.binlist.net/'. $bin, $curl_options, 'moduls', 'cardinfo');
			$string = $curl['output'];
			if(!$curl['err']){
				$res = @json_decode($string, true);
				if(is_array($res)){
					$info['scheme'] = mb_strtolower(is_isset($res,'scheme'));
					if(isset($res['bank']['name'])){
						$info['issuer'] = $res['bank']['name'];
					}
					if(isset($res['country']['name'])){
						$info['country'] = $res['country']['name'];
					}	
				}
			}		
		} elseif($server == 0) {
			$info['scheme'] = mb_strtolower(card_scheme_detected($card));				
		}
	}
	
	if(strlen($info['scheme']) > 0 and strlen($info['country']) > 0 and $save_memory){
		$arr = array();
		$arr['card'] = $card;
		$arr['card_info'] = @serialize($info);
		$wpdb->insert($wpdb->prefix ."card_detected_memory", $arr);
	}
	
	return $info;
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'config');