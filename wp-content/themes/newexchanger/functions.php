<?php
if( !defined( 'ABSPATH')){ exit(); }

load_theme_textdomain( 'pntheme', get_template_directory() . '/lang' );

if(!function_exists('theme_include')){
	function theme_include($page){
	$pager = TEMPLATEPATH . "/".$page.".php";
		if(file_exists($pager)){
			include($pager);
		}
	}
}

function init_premium_theme($plugin_name=''){
	$plugin_name = trim($plugin_name);
	if($plugin_name){
			
		$script_name = '';
		if(isset($_SERVER['SCRIPT_NAME'])){
			$script_name = $_SERVER['SCRIPT_NAME'];
		}
		$allow_script_name = array('/wp-admin/index.php','/wp-login.php');

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if (is_plugin_inactive($plugin_name . '/'. $plugin_name .'.php') and !is_admin() and !in_array($script_name, $allow_script_name)) {
			header('Content-Type: text/html; charset=utf-8');
			$text = trim(get_option('pn_update_plugin_text'));
			if(!$text){ $text = __('Dear users, right now our website is updating. Please come back later.','pntheme'); }
			$text = apply_filters('comment_text', $text);
			$output_html = '<div style="border: 1px solid #ff0000; padding: 10px 15px; font: 13px Arial; width: 500px; border-radius: 3px; margin: 0 auto; text-align: center;">'. $text .'</div>';
			echo apply_filters('update_mode_plugin', $output_html, $text);
			exit;
		}	
	}
}
init_premium_theme('premiumbox');

if ( is_plugin_inactive( 'premiumbox/premiumbox.php' )) {
	return;
}

theme_include('includes/sites_func');
theme_include('includes/breadcrumb');
theme_include('includes/api');
theme_include('includes/comment_func');
theme_include('temps/error');
theme_include('temps/mail');

theme_include('change/color_scheme'); 
theme_include('change/header');
theme_include('change/home');
theme_include('change/footer');