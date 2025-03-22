<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('admin_menu', 'admin_menu_theme_color_scheme');
function admin_menu_theme_color_scheme(){
	$plugin = get_plugin_class();
	add_submenu_page("themes.php", __('Color scheme','pntheme'), __('Color scheme','pntheme'), 'administrator', "pn_theme_color_scheme", array($plugin, 'admin_temp'));
}

add_action('pn_adminpage_title_pn_theme_color_scheme', 'def_adminpage_title_pn_theme_color_scheme');
function def_adminpage_title_pn_theme_color_scheme($page){
	_e('Color scheme','pntheme');
} 

add_action('pn_adminpage_content_pn_theme_color_scheme','def_pn_adminpage_content_pn_theme_color_scheme');
function def_pn_adminpage_content_pn_theme_color_scheme(){
	$plugin = get_plugin_class();
	
	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Color scheme','pntheme'),
		'submit' => __('Save','pntheme'),
		'colspan' => 2,
	);
	$options['color_scheme'] = array(
		'view' => 'select',
		'title' => __('Color scheme','pntheme'),
		'options' => array('color_g'=>__('green','pntheme'), 'color_y'=>__('yellow','pntheme'), 'color_b'=>__('blue','pntheme'), 'color_r'=>__('red','pntheme')),
		'default' => $plugin->get_option('color_scheme'),
		'name' => 'color_scheme',
		'work' => 'input',
	);
	
	$options['color_scheme_help'] = array(
		'view' => 'help',
		'title' => __('More info','pntheme'),
		'default' => sprintf(__('Find out more information about website appearance settings by <a href="%1$s" target="_blank">link</a>.','pntheme'), 'https://premiumexchanger.com/wiki/appearance-settings/'),
	);	

	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'pn_theme_color_scheme_option',
		'method' => 'ajax',
	);
	$form->init_form($params_form, $options);
	
} 

add_action('premium_action_pn_theme_color_scheme','def_premium_action_pn_theme_color_scheme');
function def_premium_action_pn_theme_color_scheme(){
global $wpdb;	

	$plugin = get_plugin_class();

	only_post();
	pn_only_caps(array('administrator'));

	$form = new PremiumForm();
	$color_scheme = pn_strip_input(is_param_post('color_scheme'));
	$plugin->update_option('color_scheme','', $color_scheme);			
		
	$back_url = is_param_post('_wp_http_referer');
	$back_url .= '&reply=true';
				
	$form->answer_form($back_url);	
	
}

add_filter('body_class', 'color_scheme_body_class');
function color_scheme_body_class($classes){
	$plugin = get_plugin_class();
	$classes[] = $plugin->get_option('color_scheme');
	return $classes;
}