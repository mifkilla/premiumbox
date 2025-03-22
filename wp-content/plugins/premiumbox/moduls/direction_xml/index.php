<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Show Exchange direction settings in XML/TXT file[:en_US][ru_RU:]Настройка вывода направлений обмена в XML/TXT файле[:ru_RU]
description: [en_US:]Show Exchange direction settings in XML/TXT file[:en_US][ru_RU:]Настройка вывода направлений обмена в XML/TXT файле[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_directionxml');
add_action('all_bd_activated', 'bd_all_moduls_active_directionxml');
function bd_all_moduls_active_directionxml(){
global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'show_file'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `show_file` int(1) NOT NULL default '1'");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'xml_city'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `xml_city` longtext NOT NULL");
    } else {
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions CHANGE `xml_city` `xml_city` longtext NOT NULL");
	}	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'xml_manual'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `xml_manual` int(1) NOT NULL default '0'");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'xml_juridical'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `xml_juridical` int(1) NOT NULL default '0'");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'xml_show1'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `xml_show1` varchar(50) NOT NULL");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'xml_show2'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `xml_show2` varchar(50) NOT NULL");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'xml_param'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `xml_param` longtext NOT NULL");
    }	
}	

global $premiumbox;
if($premiumbox->get_option('txtxml','create') == 1){
	add_action('pn_exchange_settings_option_post', 'txtxml_create_bd', 1000);
	add_action('pn_exchange_filters_option_post', 'txtxml_create_bd', 1000);
	add_action('pntable_psys_save', 'txtxml_create_bd', 1000);
	add_action('pntable_psys_action', 'txtxml_create_bd', 1000);
	add_action('item_psys_add', 'txtxml_create_bd', 1000);
	add_action('item_psys_edit', 'txtxml_create_bd', 1000);
	add_action('pntable_currency_save', 'txtxml_create_bd', 1000);
	add_action('pntable_currency_action', 'txtxml_create_bd', 1000);
	add_action('item_currency_add', 'txtxml_create_bd', 1000);
	add_action('pntable_currency_codes_save', 'txtxml_create_bd', 1000);
	add_action('pntable_currency_codes_action', 'txtxml_create_bd', 1000);
	add_action('item_currency_code_edit', 'txtxml_create_bd', 1000);
	add_action('item_currency_code_add', 'txtxml_create_bd', 1000);
	add_action('pntable_directions_save', 'txtxml_create_bd', 1000);
	add_action('pntable_directions_action', 'txtxml_create_bd', 1000);
	add_action('item_direction_edit', 'txtxml_create_bd', 1000);
	add_action('item_direction_add', 'txtxml_create_bd', 1000);
	add_action('after_update_currency_reserv', 'txtxml_create_bd', 1000);
	add_action('after_update_direction_reserv', 'txtxml_create_bd', 1000);
	add_action('export_direction_end', 'txtxml_create_bd', 1000);
	add_action('export_currency_end', 'txtxml_create_bd', 1000);
	add_action('reservcurs_end', 'txtxml_create_bd', 1000);
	add_action('item_parser_pairs_edit', 'txtxml_create_bd', 1000);
	add_action('parser_index_edit_end', 'txtxml_create_bd', 1000);
	add_action('pntable_parser_index_save', 'txtxml_create_bd', 1000);
	add_action('pntable_parser_index_action', 'txtxml_create_bd', 1000);
	add_action('pntable_parser_pairs_save', 'txtxml_create_bd', 1000);
	add_action('pntable_parser_pairs_action', 'txtxml_create_bd', 1000);
	add_action('pntable_parsercourses_deleteall', 'txtxml_create_bd', 1000);
	add_action('item_bccorrs_edit', 'txtxml_create_bd', 1000);
	add_action('pntable_bccorrs_save', 'txtxml_create_bd', 1000);
	add_action('pntable_bccorrs_action', 'txtxml_create_bd', 1000);
	add_action('request_bcparser_end', 'txtxml_create_bd', 20);
	add_action('request_bestchange_end', 'txtxml_create_bd', 20);
	add_action('load_new_parser_courses', 'txtxml_create_bd', 20);
	add_action('request_fcourse', 'txtxml_create_bd', 20);
	add_action('fres_change_reserve', 'txtxml_create_bd', 20);
	add_action('pn_txtxml_option_post', 'txtxml_create_bd', 20);
}

function txtxml_create_bd_filter($v){
	txtxml_create_bd();
	return $v;
}

function txtxml_create_bd(){
global $wpdb, $premiumbox;

	$time = current_time('timestamp');
	update_option('txtxml_create_time', $time);
	
	$fromfee_setting = intval($premiumbox->get_option('txtxml','fromfee'));
	$tofee_setting = intval($premiumbox->get_option('txtxml','tofee'));
	$decimal_with = intval($premiumbox->get_option('txtxml','decimal_with'));
	$decimal = intval($premiumbox->get_option('txtxml','decimal'));

	$directions = array();
	
	$v = get_currency_data();

	$where = get_directions_where("files");
	$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions WHERE $where ORDER BY site_order1 ASC");
	foreach($items as $ob){ 
		$output = apply_filters('get_direction_output', 1, $ob, 'txtxml');
		if($output == 1){
			$valid1 = $ob->currency_id_give;
			$valid2 = $ob->currency_id_get;
						
			if(isset($v[$valid1]) and isset($v[$valid2])){
				$vd1 = $v[$valid1];
				$vd2 = $v[$valid2];
				$decimal1 = $vd1->currency_decimal;
				$decimal2 = $vd2->currency_decimal;
				if($decimal_with == 1){
					$decimal1 = $decimal2 = $decimal;
				}
				$direction_id = $ob->id;
				
				$lines = array();
				$lines['from'] = is_xml_value($vd1->xml_value);
				$lines['to'] = is_xml_value($vd2->xml_value);
				
				$dir_c = is_course_direction($ob, $vd1, $vd2, 'txtxml');
				
				$lines['in'] = is_sum(is_isset($dir_c,'give'), $decimal1); 
				$lines['out'] = is_sum(is_isset($dir_c,'get'), $decimal2);
				$lines['amount'] = get_direction_reserv($vd1, $vd2, $ob);
				
				$currency_code_give = is_site_value($vd1->currency_code_title);
				$currency_code_get = is_site_value($vd2->currency_code_title);
				
				$min1 = is_sum($ob->com_box_min1, $decimal1);
				$min2 = is_sum($ob->com_box_min2, $decimal2);
				if($min1 > 0){ 
					$minfee = $min1;
					$vtype = $currency_code_give;
				} else {
					$minfee = $min2;
					$vtype = $currency_code_get;					
				}
				if($minfee > 0){
					$lines['minfee'] = $minfee .' '. $vtype;
				}						
						
				$fromfee = array();
				if($fromfee_setting == 1){
					if($ob->com_sum1){ 
						$fromfee[] = is_sum($ob->com_sum1, $decimal1) . ' '. $currency_code_give;
					}
					if($ob->com_pers1){
						$fromfee[] = is_sum($ob->com_pers1, $decimal1).' %';
					}							
				} elseif($fromfee_setting == 2){
					if(isset($ob->com_sum1_check) and $ob->com_sum1_check){ 
						$fromfee[] = is_sum($ob->com_sum1_check, $decimal1) . ' '. $currency_code_give;
					}
					if(isset($ob->com_pers1_check) and $ob->com_pers1_check){
						$fromfee[] = is_sum($ob->com_pers1_check, $decimal1).' %';
					}							
				} else {
					if($ob->dcom1 == 0){
						if(isset($ob->com_box_sum1) and $ob->com_box_sum1){ 
							$fromfee[] = is_sum($ob->com_box_sum1, $decimal1) . ' '. $currency_code_give;
						}
						if(isset($ob->com_box_pers1) and $ob->com_box_pers1){
							$fromfee[] = is_sum($ob->com_box_pers1, $decimal1).' %';
						}	
					}
				}
				if(count($fromfee) > 0){
					$lines['fromfee'] = join(', ',$fromfee);
				}	

				$tofee = array();
				if($tofee_setting == 1){
					if($ob->com_sum2){ 
						$tofee[] = is_sum($ob->com_sum2, $decimal2) . ' '. $currency_code_get;
					}
					if($ob->com_pers2){
						$tofee[] = is_sum($ob->com_pers2, $decimal2).' %';
					}							
				} elseif($tofee_setting == 2){
					if(isset($ob->com_sum2_check) and $ob->com_sum2_check){ 
						$tofee[] = is_sum($ob->com_sum2_check, $decimal2) . ' '. $currency_code_get;
					}
					if(isset($ob->com_pers2_check) and $ob->com_pers2_check){
						$tofee[] = is_sum($ob->com_pers2_check, $decimal2).' %';
					}							
				} else {
					if($ob->dcom1 == 0){
						if($ob->com_box_sum2){ 
							$tofee[] = is_sum($ob->com_box_sum2, $decimal2) . ' '. $currency_code_get;
						}
						if($ob->com_box_pers2){
							$tofee[] = is_sum($ob->com_box_pers2, $decimal2).' %';
						}	
					}
				}						
				if(count($tofee) > 0){
					$lines['tofee'] = join(', ',$tofee);
				}
				
				$dir_minmax = get_direction_minmax($ob, $vd1, $vd2, $lines['in'], $lines['out'], $lines['amount'], 'xml');  
				$min1 = is_isset($dir_minmax, 'min_give');
				$max1 = is_isset($dir_minmax, 'max_give');
				$min2 = is_isset($dir_minmax, 'min_get');
				$max2 = is_isset($dir_minmax, 'max_get');
				
				if(is_numeric($min1) and $min1 > 0){
					$lines['minamount'] = $min1 .' '. $currency_code_give;
				}
				if(is_numeric($max1)){
					$lines['maxamount'] = $max1 .' '. $currency_code_give;
				}								
				
				$m_in = is_isset($ob,'m_in');
				$m_in_arr = @unserialize($m_in);
				$has_m_in = 0;
				if(!is_array($m_in_arr) and strlen($m_in) > 0 or is_array($m_in_arr) and count($m_in_arr) > 0){
					$has_m_in = 1;
				}	
				$m_out = is_isset($ob,'m_out');
				$m_out_arr = @unserialize($m_out);
				$has_m_out = 0;
				if(!is_array($m_out_arr) and strlen($m_out) > 0 or is_array($m_out_arr) and count($m_out_arr) > 0){
					$has_m_out = 1;
				}				
				
				$params = array();
				$xml_param = trim(is_isset($ob,'xml_param'));
				if($xml_param){
					$params[] = $xml_param;
				}
						
				$xml_manual = intval(is_isset($ob,'xml_manual'));
				if($xml_manual == 0){
					if($has_m_in != 1 or $has_m_out != 1){
						$params[] = 'manual';
					}
				} elseif($xml_manual == 2){
					$params[] = 'manual';
				} 
				
				$xml_juridical = intval(is_isset($ob,'xml_juridical'));
				if($xml_juridical){
					$params[] = 'juridical';
				}
				if(count($params) > 0){
					$lines['param'] = join(', ',$params);
				}				
				
				$lines['cities'] = pn_strip_input(is_isset($ob,'xml_city'));
				$lines = apply_filters('file_xml_lines', $lines, $ob, $vd1, $vd2);
				if(count($lines) > 0){
					$directions[$direction_id] = $lines;
				}
			}
		}
	}
	
	update_array_option($premiumbox, 'pn_directions_filedata', $directions);
} 

$premiumbox->include_patch(__FILE__, 'settings');
$premiumbox->include_patch(__FILE__, 'output');
$premiumbox->include_patch(__FILE__, 'filters');