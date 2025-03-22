<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('pn_adminpage_title_pn_config', 'def_adminpage_title_pn_config');
function def_adminpage_title_pn_config($page){
	_e('General settings','pn');
} 

add_filter('pn_config_option', 'def_pn_config_option', 1);
function def_pn_config_option($options){
global $wpdb, $premiumbox;	
		
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('General settings','pn'),
		'submit' => __('Save','pn'),
	);
	$options['up_mode'] = array(
		'view' => 'select',
		'title' => __('Updating mode','pn'),
		'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
		'default' => $premiumbox->get_option('up_mode'),
		'name' => 'up_mode',
		'work' => 'int',
	);
	$options[] = array(
		'view' => 'line',
	);
	$options['adminpass'] = array(
		'view' => 'select',
		'title' => __('Remember successful entry of the security code','pn'),
		'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
		'default' => $premiumbox->get_option('adminpass'),
		'name' => 'adminpass',
		'work' => 'int',
	);
	$options['nocopydata'] = array(
		'view' => 'select',
		'title' => __('Ability to copy information on clients in one click','pn'),
		'options' => array('0'=>__('Yes','pn'), '1'=>__('No','pn')),
		'default' => $premiumbox->get_option('nocopydata'),
		'name' => 'nocopydata',
		'work' => 'int',
	);	
		
	return $options;
}
	
add_action('pn_adminpage_content_pn_config','def_adminpage_content_pn_config');
function def_adminpage_content_pn_config(){

	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'pn_config_option',
		'method' => 'ajax',
		'data' => '',
		'form_link' => '',
		'button_title' => __('Save','pn'),
	);
	$form->init_form($params_form);
		
} 

add_action('premium_action_pn_config','def_premium_action_pn_config');
function def_premium_action_pn_config(){
global $wpdb, $premiumbox;	

	only_post();
	
	$form = new PremiumForm();
	$form->send_header();	
	
	pn_only_caps(array('administrator'));
		
	$data = $form->strip_options('pn_config_option', 'post');
		
	$opts =  array('up_mode'); 
	foreach($opts as $opt){
		$premiumbox->update_option('up_mode','',$data[$opt]);
	}
	
	$opts =  array('adminpass','nocopydata'); 
	foreach($opts as $opt){
		$premiumbox->update_option($opt,'',$data[$opt]);
	}		
	
	add_pn_cookie('adminpass', '');
		
	do_action('pn_config_option_post', $data);			
		
	$back_url = is_param_post('_wp_http_referer');
	$back_url .= '&reply=true';
				
	$form->answer_form($back_url);					
}