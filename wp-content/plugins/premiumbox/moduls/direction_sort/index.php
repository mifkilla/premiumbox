<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Sort directions[:en_US][ru_RU:]Сортировка направлений[:ru_RU]
description: [en_US:]Sort directions[:en_US][ru_RU:]Сортировка направлений[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
dependent: 2.1 step 2
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_directionsort');
add_action('all_bd_activated', 'bd_all_moduls_directionsort');
function bd_all_moduls_directionsort(){
global $wpdb;	
	$table_name = $wpdb->prefix ."directions_order";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`direction_id` bigint(20) NOT NULL default '0',
		`c_id` bigint(20) NOT NULL default '0',
		`order1` bigint(20) NOT NULL default '0',
		PRIMARY KEY ( `id` ),
		INDEX (`direction_id`),
		INDEX (`c_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);				
}

add_action('item_direction_delete', 'directionsort_item_direction_delete');
function directionsort_item_direction_delete($id){
global $wpdb;
	$wpdb->query("DELETE FROM ".$wpdb->prefix."directions_order WHERE direction_id = '$id'"); 
}

add_action('item_direction_copy', 'directionsort_direction_copy', 10, 2);
function directionsort_direction_copy($last_id, $new_id){
global $wpdb;	
	$cf_directions = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions_order WHERE direction_id='$last_id'");
	foreach($cf_directions as $dirs){
		$arr = array();
		foreach($dirs as $dir_k => $dir_v){
			if($dir_k != 'id'){
				$arr[$dir_k] = $dir_v;
			}						
		}
		$arr['direction_id'] = $new_id;
		$wpdb->insert($wpdb->prefix.'directions_order', $arr);
	}	
}

add_action('item_currency_edit','directionsort_pn_currency_edit', 10, 2);
add_action('item_currency_add','directionsort_pn_currency_edit', 10, 2);
function directionsort_pn_currency_edit($data_id, $array){
global $wpdb;
	if($data_id > 0){
		$directions = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions");
		foreach($directions as $direction){
			$direction_id = $direction->id;
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."directions_order WHERE direction_id='$direction_id' AND c_id='$data_id'");
			if($cc == 0){
				$arr = array(
					'direction_id' => $direction_id,
					'c_id' => $data_id,
				);
				$wpdb->insert($wpdb->prefix.'directions_order', $arr);
			}
		}		
	}
} 

add_action('item_direction_edit','directionsort_item_direction_edit', 10, 2);
add_action('item_direction_add','directionsort_item_direction_edit', 10, 2);
function directionsort_item_direction_edit($data_id, $array){
global $wpdb;
	if($data_id > 0){
		$currencies = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."currency");
		foreach($currencies as $currency){
			$currency_id = $currency->id;
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."directions_order WHERE direction_id='$data_id' AND c_id='$currency_id'");
			if($cc == 0){
				$arr = array(
					'direction_id' => $data_id,
					'c_id' => $currency_id,
				);
				$wpdb->insert($wpdb->prefix.'directions_order', $arr);
			}
		}							
	}
} 

add_action('init', 'del_stand_directions_table1', 9);
function del_stand_directions_table1(){
	remove_filter('get_directions_table1', 'def_get_directions_table1', 10, 5);
	remove_action('pn_adminpage_content_pn_sort_table1','def_pn_admin_content_pn_sort_table1');
}

add_filter('get_directions_table1', 'directionsort_get_directions_table1', 15, 5);
function directionsort_get_directions_table1($directions, $place, $where, $v, $currency_id_give=''){
global $wpdb;

	$currency_id_give = intval($currency_id_give);
	if($currency_id_give > 0){
		$where .= " AND currency_id_give = '$currency_id_give'";
	}
	$directions = array();
	
	$directions_arr = $wpdb->get_results("
	SELECT * FROM ". $wpdb->prefix ."directions_order 
	LEFT OUTER JOIN ".$wpdb->prefix."directions 
	ON (".$wpdb->prefix."directions.id = ".$wpdb->prefix."directions_order.direction_id AND ".$wpdb->prefix."directions.currency_id_give = ".$wpdb->prefix."directions_order.c_id)
	WHERE $where ORDER BY ".$wpdb->prefix."directions_order.order1 ASC
	");
	foreach($directions_arr as $dir){
		if(isset($v[$dir->currency_id_give], $v[$dir->currency_id_get])){
			$output = apply_filters('get_direction_output', 1, $dir, $place);
			if($output == 1){
				$directions[$dir->currency_id_give][] = $dir;
			}
		}
	}	
	
	return $directions;
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'sort');