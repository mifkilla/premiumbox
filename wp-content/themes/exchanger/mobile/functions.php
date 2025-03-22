<?php
if( !defined( 'ABSPATH')){ exit(); }

if ( is_plugin_inactive( 'premiumbox/premiumbox.php' )) {
	return;
}

mobile_theme_include('includes/sites_func');
mobile_theme_include('includes/api');

mobile_theme_include('change/color_scheme'); 
mobile_theme_include('change/all');
mobile_theme_include('change/home');