<?php
if( !defined( 'ABSPATH')){ exit(); }

add_filter('exchange_step1', 'exchange_form_captcha');
function exchange_form_captcha($line){ 
	global $wpdb, $pn_captcha;
	
	if(!isset($pn_captcha->id)){
		$pn_captcha = captcha_reload(0);
	}
	
	$plugin = get_plugin_class();

	if($plugin->get_option('captcha','exchangeform') == 1){
		$data = $pn_captcha;
		$sumbols = array('+','-','x');
		if(isset($data->id)){
			$img1 = captcha_generate($data->num1, $data->num1h);
			$img2 = captcha_generate($data->num2, $data->num2h);
			$symb = is_isset($sumbols, $data->symbol);		
		} else {
			$img1 = captcha_generate(0, 0);
			$img2 = captcha_generate(0, 0);
			$symb = '+';		
		}	

		$line .= get_captcha_temp($img1,$img2, $symb);
	}
	
	return $line;	
}