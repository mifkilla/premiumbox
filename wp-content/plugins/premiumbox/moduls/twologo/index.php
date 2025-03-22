<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Second currency logo[:en_US][ru_RU:]Второй логотип валюты[:ru_RU]
description: [en_US:]Second currency logo[:en_US][ru_RU:]Второй логотип валюты[:ru_RU]
version: 2.2
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
dependent: -
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path); 

add_filter('pn_second_logo', 'twologo_pn_second_logo');
function twologo_pn_second_logo(){
	return 1;
}