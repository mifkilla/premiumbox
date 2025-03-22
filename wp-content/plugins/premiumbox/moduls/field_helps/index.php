<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Tips for fields[:en_US][ru_RU:]Подсказки для полей[:ru_RU]
description: [en_US:]Tips for fields[:en_US][ru_RU:]Подсказки для полей[:ru_RU]
version: 2.2
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
dependent: -
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_fieldhelps');
add_action('all_bd_activated', 'bd_all_moduls_active_fieldhelps');
function bd_all_moduls_active_fieldhelps(){
global $wpdb;
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE 'txt_give'");
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `txt_give` longtext NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE 'txt_get'");
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `txt_get` longtext NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE 'helps_give'");
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `helps_give` longtext NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE 'helps_get'");
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `helps_get` longtext NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency_custom_fields LIKE 'helps_give'");
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency_custom_fields ADD `helps_give` longtext NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency_custom_fields LIKE 'helps_get'");
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency_custom_fields ADD `helps_get` longtext NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."direction_custom_fields LIKE 'helps'");
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."direction_custom_fields ADD `helps` longtext NOT NULL");
	}	
	
}

if(is_admin()){
	
 	add_action('tab_currency_tab3', 'fieldhelps_tab_currency_tab3', 30, 2);
	function fieldhelps_tab_currency_tab3($data, $data_id){
		$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Field title "From Account"','pn'); ?></span></div>
			<?php 
			$atts = array();
			$atts['class'] = 'big_input';
			$form->input('txt_give', pn_strip_input(is_isset($data,'txt_give')), $atts, 1); 
			?>	
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Field title "Onto Account"','pn'); ?></span></div>
			<?php 
			$atts = array();
			$atts['class'] = 'big_input';
			$form->input('txt_get', pn_strip_input(is_isset($data,'txt_get')), $atts, 1); 
			?>			
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Tip for field "From Account"','pn'); ?></span></div>
			<?php 
			$form->textarea('helps_give', pn_strip_input(is_isset($data, 'helps_give')), 8, array(), 1, 0); 
			?>	
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Tip for field "Onto Account"','pn'); ?></span></div>
			<?php 
			$form->textarea('helps_get', pn_strip_input(is_isset($data, 'helps_get')), 8, array(), 1, 0); 
			?>	
		</div>		
	</div>		
<?php		
	} 	

	add_filter('pn_currency_addform_post', 'fieldhelps_currency_addform_post');
	function fieldhelps_currency_addform_post($array){
		
		$array['helps_give'] = pn_strip_input(is_param_post_ml('helps_give'));
		$array['helps_get'] = pn_strip_input(is_param_post_ml('helps_get'));	
		$array['txt_give'] = pn_strip_input(is_param_post_ml('txt_give'));
		$array['txt_get'] = pn_strip_input(is_param_post_ml('txt_get'));	
		
		return $array;
	}

	add_filter('pn_cfc_addform', 'fieldhelps_pn_cfc_addform', 10, 2);
	function fieldhelps_pn_cfc_addform($options, $bd_data){
		
		$vid = intval(is_isset($bd_data, 'vid'));
		if($vid == 0){
			$cl1 = '';
			$cl2 = 'pn_hide';
		} else {
			$cl1 = 'pn_hide';
			$cl2 = '';			
		}	
		
		$n_options = array();

		$n_options['helps_give'] = array(
			'view' => 'textarea',
			'title' => __('Tip for field "From Account"','pn'),
			'default' => is_isset($bd_data, 'helps_give'),
			'name' => 'helps_give',
			'rows' => '8',
			'ml' => 1,
			'class' => 'thevib thevib0 '.$cl1
		);	
		$n_options['helps_get'] = array(
			'view' => 'textarea',
			'title' => __('Tip for field "Onto Account"','pn'),
			'default' => is_isset($bd_data, 'helps_get'),
			'name' => 'helps_get',
			'rows' => '8',
			'ml' => 1,
			'class' => 'thevib thevib0 '.$cl1
		);

		$options = pn_array_insert($options, 'cf_req', $n_options);	
		
		return $options;
	}

	add_filter('pn_cfc_addform_post', 'fieldhelps_cfc_addform_post');
	function fieldhelps_cfc_addform_post($array){
		
		$array['helps_give'] = pn_strip_input(is_param_post_ml('helps_give'));
		$array['helps_get'] = pn_strip_input(is_param_post_ml('helps_get'));		
		
		return $array;
	}

	add_filter('pn_cf_addform', 'fieldhelps_pn_cf_addform', 10, 2);
	function fieldhelps_pn_cf_addform($options, $bd_data){
		
		$vid = intval(is_isset($bd_data, 'vid'));
		if($vid == 0){
			$cl1 = '';
			$cl2 = 'pn_hide';
		} else {
			$cl1 = 'pn_hide';
			$cl2 = '';			
		}
		
		$n_options = array();

		$n_options['helps'] = array(
			'view' => 'textarea',
			'title' => __('Fill-in tips','pn'),
			'default' => is_isset($bd_data, 'helps'),
			'name' => 'helps',
			'rows' => '8',
			'ml' => 1,
			'class' => 'thevib thevib0 '.$cl1
		);

		$options = pn_array_insert($options, 'cf_req', $n_options);	
		
		return $options;
	}

	add_filter('pn_cf_addform_post', 'fieldhelps_cf_addform_post');
	function fieldhelps_cf_addform_post($array){
		
		$array['helps'] = pn_strip_input(is_param_post_ml('helps'));		
		
		return $array;
	}

	add_filter('list_export_currency', 'fieldhelps_list_export_currency');
	function fieldhelps_list_export_currency($array){
		$array['txt_give'] = __('Field title "From Account"','pn');
		$array['txt_get'] = __('Field title "Onto Account"','pn');
		return $array;
	}
}	

add_filter('direction_custom_fields_tooltip', 'fieldhelps_direction_custom_fields_tooltip', 10, 2);
function fieldhelps_direction_custom_fields_tooltip($txt, $data){
	$txt .= pn_strip_text(ctv_ml(is_isset($data,'helps')));
	return $txt;
}

add_filter('account_tooltip', 'fieldhelps_account_tooltip', 10, 3);
function fieldhelps_account_tooltip($txt, $vd, $side_id){
	if($side_id == 1){
		$txt .= pn_strip_text(ctv_ml(is_isset($vd,'helps_give')));
	} else {
		$txt .= pn_strip_text(ctv_ml(is_isset($vd,'helps_get')));
	}
	return $txt;
}

add_filter('currency_custom_fields_tooltip', 'fieldhelps_currency_custom_fields_tooltip', 10, 3);
function fieldhelps_currency_custom_fields_tooltip($txt, $vd, $side_id){
	if($side_id == 1){
		$txt .= pn_strip_text(ctv_ml(is_isset($vd,'helps_give')));
	} else {
		$txt .= pn_strip_text(ctv_ml(is_isset($vd,'helps_get')));
	}
	return $txt;
}