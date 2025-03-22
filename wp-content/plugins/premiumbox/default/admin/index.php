<?php
if( !defined( 'ABSPATH')){ exit(); }
 
$plugin = get_plugin_class();
 
if(!function_exists('admin_menu_admin')){
	$plugin->include_patch(__FILE__, 'settings');
	$plugin->include_patch(__FILE__, 'filters');
	$plugin->include_patch(__FILE__, 'jivochat');
}