<?php
if( !defined( 'ABSPATH')){ exit(); }

if (strpos($_SERVER['REQUEST_URI'], "eval(") ||
    strpos($_SERVER['REQUEST_URI'], "CONCAT") ||
    strpos($_SERVER['REQUEST_URI'], "UNION+SELECT") ||
    strpos($_SERVER['REQUEST_URI'], "base64")){
		header("HTTP/1.1 414 Request-URI Too Long");
		header("Status: 414 Request-URI Too Long");
		header("Connection: Close");
		exit;
}

global $or_template_directory;
if(!$or_template_directory){
	$or_template_directory = get_template_directory_uri();
}

global $or_site_url;
if(!$or_site_url){
	$or_site_url = rtrim(get_option('siteurl'), '/');
}

if(!function_exists('get_premium_version')){
	function get_premium_version(){
		return '3.3';
	}		
}

require_once( __DIR__ . "/includes/functions.php"); 
require_once( __DIR__ . "/includes/comment_system.php");
require_once( __DIR__ . "/includes/mail_filters.php");
require_once( __DIR__ . "/includes/menu_filters.php"); 
require_once( __DIR__ . "/includes/lang_func.php");
require_once( __DIR__ . "/includes/rtl_func.php"); 
require_once( __DIR__ . "/includes/form_class.php"); 
require_once( __DIR__ . "/includes/table_class.php"); 
require_once( __DIR__ . "/includes/init_page.php");
require_once( __DIR__ . "/includes/init_cron.php"); 
require_once( __DIR__ . "/includes/pagenavi.php");
require_once( __DIR__ . "/includes/security.php");
require_once( __DIR__ . "/includes/premium_class.php");
require_once( __DIR__ . "/includes/merch_class.php");

if(!function_exists('premium_langs_loaded')){
	add_action('plugins_loaded', 'premium_langs_loaded');
	function premium_langs_loaded(){
		load_plugin_textdomain('premium', false, dirname( plugin_basename( __FILE__ ) ) . '/langs'); 
	}			
}