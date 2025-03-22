<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]E-mail notifications templates[:en_US][ru_RU:]Шаблоны e-mail уведомлений[:ru_RU]
description: [en_US:]Sender E-mail and sender name used for letters template by default[:en_US][ru_RU:]E-mail отправителя и имя отправителя используемые для шаблонов писем по умолчанию[:ru_RU]
version: 2.2
category: [en_US:]E-mail[:en_US][ru_RU:]E-mail[:ru_RU]
cat: email
*/

if(!function_exists('admin_menu_mailtemps')){
	add_action('admin_menu', 'admin_menu_mailtemps', 15);
	function admin_menu_mailtemps(){
		$plugin = get_plugin_class();	
		add_submenu_page("all_mail_temps", __('E-mail settings','pn'), __('E-mail settings','pn'), 'administrator', "all_mailtemps", array($plugin, 'admin_temp'));
	}
}

if(!function_exists('def_adminpage_title_all_mailtemps')){
	add_action('pn_adminpage_title_all_mailtemps', 'def_adminpage_title_all_mailtemps');
	function def_adminpage_title_all_mailtemps(){
		_e('E-mail settings','pn');
	} 
}

if(!function_exists('def_all_mailtemps_option')){
	add_filter('all_mailtemps_option', 'def_all_mailtemps_option', 1);
	function def_all_mailtemps_option($options){
	global $wpdb;	
			
		$plugin = get_plugin_class();		
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save','pn'),
		);	
		$options['mail'] = array(
			'view' => 'inputbig',
			'title' => __('Senders e-mail','pn'),
			'default' => $plugin->get_option('email','mail'),
			'name' => 'mail',
			'work' => 'input',
		);
		$options['mail_warning'] = array(
			'view' => 'warning',
			'default' => __('Use only existing e-mail like info@site.ru','pn'),
		);	
		$options['name'] = array(
			'view' => 'inputbig',
			'title' => __('Sender name','pn'),
			'default' => $plugin->get_option('email','name'),
			'name' => 'name',
			'work' => 'input',
		);		
			
		return $options;
	}
}

if(!function_exists('def_adminpage_content_all_mailtemps')){
	add_action('pn_adminpage_content_all_mailtemps','def_adminpage_content_all_mailtemps');
	function def_adminpage_content_all_mailtemps(){

		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_mailtemps_option',
			'method' => 'ajax',
			'data' => '',
			'form_link' => '',
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form);	
	}  
}

if(!function_exists('def_premium_action_all_mailtemps')){
	add_action('premium_action_all_mailtemps','def_premium_action_all_mailtemps');
	function def_premium_action_all_mailtemps(){

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
			
		$plugin = get_plugin_class();
		
		$plugin->update_option('email', 'mail', pn_strip_input(is_param_post('mail')));
		$plugin->update_option('email', 'name', pn_strip_input(is_param_post('name')));			
			
		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
					
		$form->answer_form($back_url);
	} 
}

if(!function_exists('mailtemps_all_mail_temps_option')){
	add_filter('all_mail_temps_option', 'mailtemps_all_mail_temps_option');
	function mailtemps_all_mail_temps_option($options){
		
		if(isset($options['mail'])){
			unset($options['mail']);
		}
		if(isset($options['name'])){
			unset($options['name']);
		}
		if(isset($options['mail_warning'])){
			unset($options['mail_warning']);
		}	
		
		return $options;
	}
}

if(!function_exists('mailtemps_wp_mail')){
	add_filter('wp_mail', 'mailtemps_wp_mail');
	function mailtemps_wp_mail($data){
		$plugin = get_plugin_class();
		$mail = pn_strip_input($plugin->get_option('email', 'mail'));
		$name = pn_strip_input($plugin->get_option('email', 'name'));
		if($mail and $name){
			$data['headers'] = "From: $name <". $mail .">\r\n";
		}
		return $data;
	}
}

if(!function_exists('mailtemps_phpmailer_init')){
	add_action('phpmailer_init','mailtemps_phpmailer_init');
	function mailtemps_phpmailer_init($phpmailer){
		$plugin = get_plugin_class();
		$mail = pn_strip_input($plugin->get_option('email', 'mail'));
		if($mail){
			$phpmailer->Sender = $mail;
		}
	}
}