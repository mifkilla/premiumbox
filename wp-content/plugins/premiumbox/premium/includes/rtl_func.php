<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('get_pn_rtl')){
	
	$data = premium_rewrite_data();
	$super_base = $data['super_base'];
	
	global $pn_rtl;
	$pn_rtl = 'ltr';	
	
	if(!is_admin() and $super_base != 'wp-login.php'){
		global $pn_lang;
		
		$rtl_options = is_isset($pn_lang, 'rtl');
		if(!is_array($rtl_options)){ $rtl_options = array(); }
		
		$now_lang = get_locale();
		$now_rtl = trim(is_isset($rtl_options, $now_lang));
		$get_rtl = trim(is_param_get('get_rtl'));
		if($now_rtl == 'rtl' or $get_rtl == 'rtl'){
			$pn_rtl = 'rtl';
		}
	}	
	
	function get_pn_rtl(){
		global $pn_rtl;
		return $pn_rtl;
	}	
	
	function is_pn_rtl(){
		$true = 0;
		if(get_pn_rtl() == 'rtl'){
			$true = 1;
		}
		return $true;
	}	
}

if(!function_exists('pn_rtl_language_attributes')){
	add_filter('language_attributes','pn_rtl_language_attributes');
	function pn_rtl_language_attributes($output){
		$output .= ' dir="'. get_pn_rtl() .'"';
		return $output;
	}	
}	

if(!function_exists('pn_rtl_body_class')){
	add_filter('body_class', 'pn_rtl_body_class');
	function pn_rtl_body_class($classes){
		if(is_pn_rtl()){
			$classes[] = 'rtl_body';
		} 
		return $classes;
	}
}