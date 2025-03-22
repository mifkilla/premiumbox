<?php
if( !defined( 'ABSPATH')){ exit(); }

add_filter('exchange_step1', 'exchange_form_captcha_sci');
function exchange_form_captcha_sci($line){
global $wpdb;

	$plugin = get_plugin_class();
	if($plugin->get_option('captcha','exchangeform') == 1){
		$line .= get_captcha_sci_temp();
	}
	
	return $line;	
}	