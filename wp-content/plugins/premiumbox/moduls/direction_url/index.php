<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Dash in URL of exchange direction[:en_US][ru_RU:]Тире в URL направления обмена[:ru_RU]
description: [en_US:]Rеplacing underscore with dash in URL of exchange direction[:en_US][ru_RU:]Замена нижнего подчеркивания на тире в URL направления обмена[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

add_filter('general_tech_pages', 'dirurl_general_tech_pages');
function dirurl_general_tech_pages($g_pages){
	$g_pages['exchange'] = 'exchange-';
	return $g_pages;
}

add_filter('direction_permalink_temp', 'dirurl_direction_permalink_temp');
function dirurl_direction_permalink_temp($temp){
	$temp = '[xmlv1]-to-[xmlv2]';
	return $temp;
}

add_filter('is_direction_permalink', 'dirurl_is_direction_permalink', 10, 2);
function dirurl_is_direction_permalink($new_name, $name){
	$new_name = '';
	$new_name = replace_cyr($name);
	$new_name = preg_replace("/[^A-Za-z0-9-]/", '_', $new_name);	
	return $new_name;
}

add_filter('is_direction_name', 'dirurl_is_direction_name', 10, 2);
function dirurl_is_direction_name($new_name, $name){
	$new_name = '';
	if (preg_match("/^[-a-zA-z0-9_]{1,500}$/", $name, $matches )) {
		$new_name = $name;
	} else {
		$new_name = '';
	}	
	return $new_name;
}