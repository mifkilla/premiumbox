<?php 
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Webmoney x19[:en_US][ru_RU:]Webmoney x19[:ru_RU]
description: [en_US:]Webmoney x19[:en_US][ru_RU:]Webmoney x19[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'webmoney/index');
$premiumbox->include_patch(__FILE__, 'classed/wmxicore.class');
$premiumbox->include_patch(__FILE__, 'classed/wmxi.class');
$premiumbox->include_patch(__FILE__, 'classed/wmxiresult.class');
$premiumbox->include_patch(__FILE__, 'classed/wmsigner.class');
	
if(!function_exists('WMXI_X19')){
	function WMXI_X19() {
		global $premiumbox;
		
		$object = array();
		if(defined('WMX19_KEEPER_TYPE')){
			$object = new WMXI( $premiumbox->plugin_dir .'moduls/x19/classed/wmxi.crt', 'UTF-8' );
			if(WMX19_KEEPER_TYPE == 'CLASSIC'){
				if(defined('WMX19_ID') and defined('WMX19_CLASSIC_KEYPASS') and defined('WMX19_CLASSIC_KEYPATH')){
					$object->Classic( WMX19_ID, array( 'pass' => str_replace('{DIR_PATH}', $premiumbox->plugin_dir, WMX19_CLASSIC_KEYPASS), 'file' => str_replace('{DIR_PATH}', $premiumbox->plugin_dir, WMX19_CLASSIC_KEYPATH) ) );
				}
			} else {
				if(defined('WMX19_LIGHT_KEYPATH') and defined('WMX19_LIGHT_CERTPATH') and defined('WMX19_LIGHT_KEYPASS')){
					$object->Light(array( 'key' => str_replace('{DIR_PATH}', $premiumbox->plugin_dir, WMX19_LIGHT_KEYPATH), 'cer' => str_replace('{DIR_PATH}', $premiumbox->plugin_dir, WMX19_LIGHT_CERTPATH), 'pass' => WMX19_LIGHT_KEYPASS ));
				}
			}
		}

		return $object;
	}
}

add_action('admin_menu', 'admin_menu_x19');
function admin_menu_x19(){
global $premiumbox;
	add_submenu_page("pn_moduls", __('X19','pn'), __('X19','pn'), 'administrator', "pn_x19_config", array($premiumbox, 'admin_temp'));
}

$premiumbox->include_patch(__FILE__, 'function');
$premiumbox->include_patch(__FILE__, 'x19');
$premiumbox->include_patch(__FILE__, 'filters');