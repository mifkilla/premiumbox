<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Proxy for merchants[:en_US][ru_RU:]Прокси для мерчантов[:ru_RU]
description: [en_US:]Proxy for merchants[:en_US][ru_RU:]Прокси для мерчантов[:ru_RU]
version: 2.2
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
dependent: -
*/

add_action('get_paymerchants_options', 'proxy_get_merchants_options', 100, 5);
add_action('get_merchants_options', 'proxy_get_merchants_options', 100, 5);
function proxy_get_merchants_options($options, $name, $data, $id, $place){
	$in = array('qiwinew','privat','privatbank');
	if(in_array($name, $in)){
		$options['proxy_title'] = array(
			'view' => 'h3',
			'title' => __('Proxy settings','pn'),
			'submit' => __('Save','pn'),
		);		
		$options['proxy_ip'] = array(
			'view' => 'inputbig',
			'title' => __('IP address','pn'),
			'default' => is_isset($data, 'proxy_ip'),
			'name' => 'proxy_ip',
			'work' => 'input',
		);	
		$options['proxy_port'] = array(
			'view' => 'inputbig',
			'title' => __('Port','pn'),
			'default' => is_isset($data, 'proxy_port'),
			'name' => 'proxy_port',
			'work' => 'input',
		);
		$options['proxy_login'] = array(
			'view' => 'inputbig',
			'title' => __('Login','pn'),
			'default' => is_isset($data, 'proxy_login'),
			'name' => 'proxy_login',
			'work' => 'input',
		);
		$options['proxy_password'] = array(
			'view' => 'inputbig',
			'title' => __('Password','pn'),
			'default' => is_isset($data, 'proxy_password'),
			'name' => 'proxy_password',
			'work' => 'input',
		);
		$options['proxy_tunnel'] = array(
			'view' => 'select',
			'title' => __('Disable proxy tunnel','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => is_isset($data, 'proxy_tunnel'),
			'name' => 'proxy_tunnel',
			'work' => 'int',
		);		
	}
	return $options;
}

add_filter('get_curl_parser', 'proxy_get_curl_parser', 10, 4);
function proxy_get_curl_parser($c_options, $place, $pointer='', $id=''){
	$pointer = trim($pointer);
	$id = trim($id);
	$in = array('qiwinew','privat','privatbank');
	if($pointer and $id and in_array($pointer, $in)){
		if($place == 'autopay'){
			$m_data = get_paymerch_data($id);
		} else {
			$m_data = get_merch_data($id);
		}
		
		$ip = trim(is_isset($m_data,'proxy_ip'));
		$port = trim(is_isset($m_data,'proxy_port'));
		$login = trim(is_isset($m_data,'proxy_login'));
		$password = trim(is_isset($m_data,'proxy_password'));	
		$tunnel = intval(is_isset($m_data,'proxy_tunnel'));
		
		if($ip and $port){
			if($tunnel){
				$c_options[CURLOPT_HTTPPROXYTUNNEL] = 0; 
			}
			
			$c_options[CURLOPT_PROXY] = $ip;
			$c_options[CURLOPT_PROXYPORT] = $port;
			
			if($password and $login){
				$c_options[CURLOPT_PROXYUSERPWD] = $login.':'.$password;
			} elseif($password){
				$c_options[CURLOPT_PROXYAUTH] = $password;
			}
		}			
	}
	return $c_options;
}