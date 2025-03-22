<?php
if( !defined( 'ABSPATH')){ exit(); }

function the_exchange_home_mobile($def_cur_from='', $def_cur_to='') {
	echo get_exchange_table_mobile($def_cur_from, $def_cur_to);
}
	
function get_exchange_table_mobile($def_cur_from='', $def_cur_to=''){
global $wpdb;	
	
	$temp = '';
	
	$arr = array(
		'from' => $def_cur_from,
		'to' => $def_cur_to,
		'direction_id' => '',
	);
	$arr = apply_filters('get_exchange_table_vtypes', $arr, 'mobile');
	
	$type_table = get_mobile_type_table();
	if($type_table == 100){
		$show_data = pn_exchanges_output('exchange');
	} else {
		$show_data = pn_exchanges_output('home');
	}
	
	if($show_data['text']){
		$temp .= '<div class="home_resultfalse"><div class="home_resultfalse_close"></div>'. $show_data['text'] .'</div>';
	}	
	
	if($show_data['mode'] == 1){
		$html = apply_filters('exchange_mobile_table_type', '', $type_table ,$arr['from'] ,$arr['to'], $arr['direction_id']);
		$temp .= apply_filters('exchange_mobile_table_type' . $type_table, $html ,$arr['from'] ,$arr['to'], $arr['direction_id']);
	} 	
	
	return $temp;
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'table1');
$premiumbox->include_patch(__FILE__, 'table3');