<?php 
/*
Plugin Name: Premium Exchanger
Plugin URI: https://premiumexchanger.com
Description: Professional e-currency exchanger
Version: 2.2
Author: Best-Curs.info
Author URI: https://premiumexchanger.com
*/

if(!defined('ABSPATH')){ exit(); }

require( dirname(__FILE__) . "/includes/plugin_class.php");
if(!class_exists('PremiumBox')){
	return;
}

/* 
Если вы проводите тестирование, поставьте 1, в противном случае, оставьте 0. Также желательно в файле wp-config.php поставить WP_DEBUG true.
If you are testing the system then enter 1. In other cases enter 0. Note that it will be of much help if you add the following line in the wp-config.php file: WP_DEBUG true.
*/
$debug_mode = 0;

$settings = array(
	'file' => __FILE__,
	'debug_mode' => $debug_mode,
	'disallow_file_mode' => 1,
	'physical_files' => 0, 
);

$plugin = new PremiumBox($settings); 

$plugin->file_include('default/up_mode/index');
$plugin->file_include('default/mail_temps');
$plugin->file_include('default/themesettings');
$plugin->file_include('default/settings');
$plugin->file_include('default/newadminpanel/index');
$plugin->file_include('default/lang/index');
$plugin->file_include('default/rtl/index');
$plugin->file_include('default/admin/index');
$plugin->file_include('default/globalajax/index');
$plugin->file_include('default/cron');
$plugin->file_include('default/roles/index');
$plugin->file_include('default/users/index');
$plugin->file_include('default/captcha/index');
$plugin->file_include('default/logs_settings/index');

$plugin->file_include('plugin/migrate/index');
$plugin->file_include('plugin/admin/index');
$plugin->file_include('plugin/config');
$plugin->file_include('plugin/update/index');
$plugin->file_include('plugin/moduls');
$plugin->file_include('plugin/users/index');
$plugin->file_include('plugin/directions/index'); 
$plugin->file_include('plugin/currency/index');
$plugin->file_include('plugin/reserv/index');
$plugin->file_include('plugin/exchange/index'); 
$plugin->file_include('plugin/bids/index');
$plugin->file_include('plugin/exchange_settings');
$plugin->file_include('plugin/merchants/index');
$plugin->file_include('plugin/exchange_filters'); 

// add_filter('show_secret_files', 'test_show_secret_files', 10, 2);
// function test_show_secret_files($none, $value){
	// return $value;
// }	