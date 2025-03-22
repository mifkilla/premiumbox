<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('pn_adminpage_content_pn_bestchange','bcbroker_adminpage_content_pn_bestchange', 0);
function bcbroker_adminpage_content_pn_bestchange(){
	$form = new PremiumForm();
	$text = __('Cron URL for updating rates in BestChange parser module','pn') . '<br /><a href="'. get_cron_link('bestchange_upload_data') .'" target="_blank">'. get_cron_link('bestchange_upload_data')  .'</a>';
	$form->substrate($text);
}

function bestchange_upload_data(){
global $wpdb, $premiumbox;
	if(function_exists('download_data_bestchange')){
		download_data_bestchange($premiumbox->get_option('bcbroker','server'), $premiumbox->get_option('bcbroker','timeout'));
	}
	if(function_exists('set_directions_bestchange')){
		set_directions_bestchange();
	}
}

add_filter('list_cron_func', 'bestchange_list_cron_func');
function bestchange_list_cron_func($filters){	
	$filters['bestchange_upload_data'] = array(
		'title' => __('BestChange parser','pn'),
		'file' => 'now',
	);
	return $filters;
}