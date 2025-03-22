<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Operator status and exchange directions[:en_US][ru_RU:]Статус оператора и направления обмена[:ru_RU]
description: [en_US:]Disabling exchange direction depending on operator status[:en_US][ru_RU:]Отключение направление обмена в зависимости от статуса оператора[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
dependent: direction_xml
new: 1
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path); 

add_filter('file_xml_lines', 'statuswork_file_xml_lines', 10, 4); 
function statuswork_file_xml_lines($lines, $ob, $vd1, $vd2){
	$operator = get_operator_status();
	if($operator != 1 and isset($lines['param']) and strstr($lines['param'], 'manual')){
		return array();
	}
	return $lines;
}