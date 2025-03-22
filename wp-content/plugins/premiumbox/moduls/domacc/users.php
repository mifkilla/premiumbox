<?php
if( !defined( 'ABSPATH')){ exit(); }

add_filter('all_user_editform', 'domacc_all_user_editform', 20, 2);
function domacc_all_user_editform($options, $bd_data){ 
global $premiumbox, $wpdb;
	
	$user_id = $bd_data->ID;
	
	$options[] = array(
		'view' => 'h3',
		'title' => __('Internal account','pn'),
		'submit' => __('Save','pn'),
	);	
	$currency_codes = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."currency_codes WHERE auto_status = '1' ORDER BY currency_code_title ASC");
	foreach($currency_codes as $currency_code){
		$curr_title = is_site_value($currency_code->currency_code_title);
		$options['domacc_'. str_replace('.','_',$curr_title)] = array(
			'view' => 'textfield',
			'title' => $curr_title,
			'default' => get_user_domacc($user_id, $currency_code->id),
		);
	}
	
	return $options;
}