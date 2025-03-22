<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Custom indicator for order notifications in topbar[:en_US][ru_RU:]Пользовательский индикатор уведомлений о заявках в топбаре[:ru_RU]
description: [en_US:]Custom indicator for order notifications in topbar[:en_US][ru_RU:]Пользовательский индикатор уведомлений о заявках в топбаре[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
new: 1
*/

if(is_admin()){
	add_filter('pn_config_option', 'persdilink_pn_config_option', 11);
	function persdilink_pn_config_option($options){
		$options['persdilink'] = array(
			'view' => 'user_func',
			'func_data' => array(),
			'func' => 'persdilink_option',
		);			
		return $options;
	}
	
	function persdilink_option(){
		$plugin = get_plugin_class();
		$bid_status_list = apply_filters('bid_status_list',array());
		
		$persdislink = $plugin->get_option('persdislink');
		if(!is_array($persdislink)){ $persdislink = array(); }
		
		?>
		
			<div class="premium_standart_line">
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Select order statuses for notification in topar','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<?php 
						$scroll_lists = array();
						if(is_array($bid_status_list)){
							foreach($bid_status_list as $key => $val){
								$checked = 0;
								if(in_array($key, $persdislink)){
									$checked = 1;
								}
								$scroll_lists[] = array(
									'title' => $val,
									'checked' => $checked,
									'value' => $key,
								);
							}	
						}	
						echo get_check_list($scroll_lists, 'persdislink[]');				
						?>
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>	
			
		<?php				
	}	
	
	add_action('pn_config_option_post', 'persdilink_pn_config_option_post');
	function persdilink_pn_config_option_post($data){
		$plugin = get_plugin_class();
			
		$button = array();
		$array = is_param_post('persdislink');
		if(is_array($array)){
			foreach($array as $v){
				$v = is_status_name($v);
				if($v){
					$button[] = $v;
				}
			}
		}
		$plugin->update_option('persdislink', '', $button);
		
	}	
	
} 

add_filter('list_icon_indicators', 'persdilink_icon_indicators', 0);
function persdilink_icon_indicators($lists){
	$plugin = get_plugin_class();
	$persdislink = $plugin->get_option('persdislink');
	if(!is_array($persdislink)){ $persdislink = array(); }
	
	$data = '';
	foreach($persdislink as $st){
		$st = is_status_name($st);
		if($st){
			$data .= '&bidstatus[]=' . $st;
		}
	}
	
	$lists['persdilink'] = array(
		'title' => __('Custom order statuses','pn'),
		'img' => $plugin->plugin_url .'images/money.gif',
		'link' => admin_url('admin.php?page=pn_bids' . $data)
	);
	return $lists;
}

add_filter('count_icon_indicator_persdilink', 'def_icon_indicator_persdilink');
function def_icon_indicator_persdilink($count){
	global $wpdb;
	if(current_user_can('administrator') or current_user_can('pn_bids')){
		$page = is_param_get('page');
		if($page != 'pn_bids'){
			$plugin = get_plugin_class();
			$persdislink = $plugin->get_option('persdislink');
			if(!is_array($persdislink)){ $persdislink = array(); }
			
			$status = create_data_for_bd($persdislink,'status');
			if($status){
				$count = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."exchange_bids WHERE status IN($status)");
			}
		}
	}	
	return $count;
}