<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Captcha for website (sеlect image)[:en_US][ru_RU:]Капча для сайта (выбор картинки)[:ru_RU]
description: [en_US:]Captcha for website with a correct image selection[:en_US][ru_RU:]Капча для сайта с выбором верной картинки[:ru_RU]
version: 2.2
category: [en_US:]Security[:en_US][ru_RU:]Безопасность[:ru_RU]
cat: secur
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if(!function_exists('bd_all_moduls_active_sci')){
	add_action('all_bd_activated', 'bd_all_moduls_active_sci');
	add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_sci');
	function bd_all_moduls_active_sci(){
	global $wpdb;	
		
		$table_name = $wpdb->prefix ."sitecaptcha_user";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
			`createdate` datetime NOT NULL,
			`sess_hash` varchar(150) NOT NULL,
			`img1` varchar(150) NOT NULL,
			`img2` varchar(150) NOT NULL,
			`img3` varchar(150) NOT NULL,		
			`num1` varchar(150) NOT NULL,
			`num2` varchar(150) NOT NULL,
			`num3` varchar(150) NOT NULL,
			`uslov` longtext NOT NULL,
			`variant` varchar(150) NOT NULL,
			PRIMARY KEY ( `id` ),
			INDEX (`createdate`),
			INDEX (`sess_hash`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
		$wpdb->query($sql);
		
		$table_name = $wpdb->prefix ."sitecaptcha_images";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
			`uslov` longtext NOT NULL,
			`img1` varchar(250) NOT NULL,
			`img2` varchar(250) NOT NULL,
			`img3` varchar(250) NOT NULL,
			`variant` int(1) NOT NULL default '1',
			PRIMARY KEY ( `id` )	
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
		$wpdb->query($sql);
	}
}

if(!function_exists('admin_menu_sci')){
	add_action('admin_menu', 'admin_menu_sci');
	function admin_menu_sci(){
		$plugin = get_plugin_class();
		
		add_menu_page(__('Choosing picture captcha','pn'), __('Choosing picture captcha','pn'), 'administrator', 'all_sci_variants', array($plugin, 'admin_temp'));  
		add_submenu_page("all_sci_variants", __('Captcha options','pn'), __('Captcha options','pn'), 'administrator', 'all_sci_variants', array($plugin, 'admin_temp'));  
		add_submenu_page("all_sci_variants", __('Add captcha options','pn'), __('Add captcha options','pn'), 'administrator', 'all_sci_add_variants', array($plugin, 'admin_temp'));	
	}
}

$plugin = get_plugin_class();
$plugin->include_patch(__FILE__, 'list');
$plugin->include_patch(__FILE__, 'add');	 
$plugin->include_patch(__FILE__, 'function');
$plugin->include_patch(__FILE__, 'premiumbox');