<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]User automatic registration[:en_US][ru_RU:]Автоматическая регистрация пользователя[:ru_RU]
description: [en_US:]User automatic registration during exchange[:en_US][ru_RU:]Автоматическая регистрация пользователя при обмене[:ru_RU]
version: 2.2
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_filter('pn_exchange_settings_option', 'autoreg_exchange_settings_option');
function autoreg_exchange_settings_option($options){
global $premiumbox;
	$options[] = array(
		'view' => 'select',
		'title' => __('Automatic user registration','pn'),
		'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
		'default' => $premiumbox->get_option('exchange','auto_reg'),
		'name' => 'auto_reg',
	);		
	$options[] = array(
		'view' => 'line',
	);
	return $options;	
}

add_action('pn_exchange_settings_option_post', 'autoreg_exchange_settings_option_post');
function autoreg_exchange_settings_option_post(){
global $premiumbox;
	$options = array('auto_reg');
	foreach($options as $key){
		$val = pn_strip_input(is_param_post($key));
		$premiumbox->update_option('exchange',$key,$val);
	}
}

add_filter('list_user_notify','list_user_notify_autoregisterform');
function list_user_notify_autoregisterform($places_admin){
	$places_admin['autoregisterform'] = __('Automatic user registration','pn');
	return $places_admin;
}

add_filter('list_notify_tags_autoregisterform','def_list_notify_tags_autoregisterform');
function def_list_notify_tags_autoregisterform($tags){
	$tags['login'] = array(
		'title' => __('Login','pn'),
		'start' => '[login]',
	);
	$tags['pass'] = array(
		'title' => __('Password','pn'),
		'start' => '[pass]',
	);
	$tags['email'] = array(
		'title' => __('E-mail','pn'),
		'start' => '[email]',
	);	
	return $tags;
}
	
add_filter('change_bidstatus', 'autoreg_change_bidstatus', 40, 4);   
function autoreg_change_bidstatus($item, $set_status, $place, $user_or_system){
global $wpdb, $premiumbox, $pn_regiter_site;
	if($set_status == 'new' and $place == 'exchange_button' and $premiumbox->get_option('exchange','auto_reg') == 1){
		$pn_regiter_site = 1;
		$locale = pn_strip_input($item->bid_locale);
		$user_id = $item->user_id;
		$user_email = is_email($item->user_email);
		if(!$user_id and $user_email){
			if(!email_exists($user_email)){
				$user_login = is_user(selection_email_login($user_email));
				if($user_login){
					$pass = wp_generate_password( 20 , false, false);
					$user_id = wp_insert_user( array ('user_login' => $user_login, 'user_email' => $user_email, 'user_pass' => $pass) ) ;
					if($user_id){
								
						do_action( 'pn_user_register', $user_id);

						$arr = array('user_id'=> $user_id, 'user_login' => $user_login);

						$item = pn_object_replace($item, $arr);
								
						$wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id'=>$item->id));
								
						$fields = apply_filters('user_fields_in_website', array());	
						foreach($fields as $field_key => $field_value){
							if($field_key != 'user_email'){
								$value = strip_uf(is_isset($item, $field_key), $field_key);
								if($value){
									update_user_meta($user_id, $field_key, $value) or add_user_meta($user_id, $field_key, $value, true);
								}
							}	
						}
								
						$notify_tags = array();
						$notify_tags['[sitename]'] = pn_site_name();
						$notify_tags['[login]'] = $user_login;
						$notify_tags['[pass]'] = $pass;
						$notify_tags['[email]'] = $user_email;
						$notify_tags = apply_filters('notify_tags_autoregisterform', $notify_tags, $user_id, $item);		

						$user_send_data = array(
							'user_email' => $user_email,
						);	
						$user_send_data = apply_filters('user_send_data', $user_send_data, 'autoregisterform', $item);
						$result_mail = apply_filters('premium_send_message', 0, 'autoregisterform', $notify_tags, $user_send_data, $locale);
							
						$secure_cookie = is_ssl();
						$creds = array();
						$creds['user_login'] = $user_login;
						$creds['user_password'] = $pass;
						$creds['remember'] = true;
						$user = wp_signon($creds, $secure_cookie);
						
					}	
				}
			}
		}		
	}	
	
	return $item;
}	