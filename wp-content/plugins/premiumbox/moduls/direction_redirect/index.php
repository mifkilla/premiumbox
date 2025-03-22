<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Redirection to exchange directions[:en_US][ru_RU:]Редирект на направления обмена[:ru_RU]
description: [en_US:]Redirection to exchange directions[:en_US][ru_RU:]Редирект на направления обмена[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('template_redirect', 'dirredirect_redirect', 11);
function dirredirect_redirect(){
global $wpdb, $premiumbox;
			
	if(isset($_GET['cur_from']) and isset($_GET['cur_to'])){ 
		$cur_from = is_xml_value(is_param_get('cur_from'));
		$cur_to = is_xml_value(is_param_get('cur_to'));
		if($cur_from and $cur_to and $cur_to != $cur_from){
			$vd1 = $wpdb->get_row("SELECT id FROM ". $wpdb->prefix ."currency WHERE auto_status='1' AND currency_status = '1' AND xml_value='$cur_from'");
			$vd2 = $wpdb->get_row("SELECT id FROM ". $wpdb->prefix ."currency WHERE auto_status='1' AND currency_status = '1' AND xml_value='$cur_to'");
			if(isset($vd1->id) and isset($vd2->id)){
				$val1 = $vd1->id;
				$val2 = $vd2->id;
				$where = get_directions_where('exchange');
				$directions = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions WHERE $where AND currency_id_give='$val1' AND currency_id_get='$val2'");
				foreach($directions as $dir){
					$output = apply_filters('get_direction_output', 1, $dir, 'exchange');
					if($output){
						wp_redirect(get_exchange_link($dir->direction_name));
						exit;
					}
				}
			}
		}
	}	
}