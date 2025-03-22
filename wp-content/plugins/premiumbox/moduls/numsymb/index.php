<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Max number of decimal places allowed in DB[:en_US][ru_RU:]Макс. кол-во знаков после запятой в БД[:ru_RU]
description: [en_US:]Max number of decimal places in calculations allowed in database[:en_US][ru_RU:]Макс. кол-во знаков после запятой в БД[:ru_RU]
version: 2.2
category: [en_US:]Settings[:en_US][ru_RU:]Настройки[:ru_RU]
cat: sett
dependent: -
*/

if(!function_exists('numsybm_settings_option')){
	
	add_filter('all_settings_option', 'numsybm_settings_option');
	function numsybm_settings_option($options){
		$plugin = get_plugin_class();

		$options['line_numsybm'] = array(
			'view' => 'line',
		);
		$options['numsybm_count'] = array(
			'view' => 'input',
			'title' => __('Max number of decimal places in calculations allowed in DB','pn'),
			'default' => $plugin->get_option('numsybm_count'),
			'name' => 'numsybm_count',
			'work' => 'input',
		);		
		
		return $options;	
	}

	add_action('all_settings_option_post', 'numsybm_settings_option_post');
	function numsybm_settings_option_post(){
		$plugin = get_plugin_class();
		
		$numsybm_count = intval(is_param_post('numsybm_count'));
		$plugin->update_option('numsybm_count', '', $numsybm_count);
	}

	add_filter('is_sum_cs', 'numsybm_is_sum_cs', 10);
	function numsybm_is_sum_cs($cs){
		$plugin = get_plugin_class();
		
		$numsybm_count = intval($plugin->get_option('numsybm_count'));
		if($numsybm_count > 0){
			if($cs > $numsybm_count){
				$cs = $numsybm_count;	
			}
		}	
		
		return $cs;
	}		

}	