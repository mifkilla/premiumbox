<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Old  URL of XML file [:en_US][ru_RU:]Старый URL XML файла[:ru_RU]
description: [en_US:]Old URL /exportxml.xml for XML file with exchange rates[:en_US][ru_RU:]Старый URL /exportxml.xml для XML файла с курсами[:ru_RU]
version: 2.2
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
dependent: direction_xml
new: 1
*/

add_filter('list_pn_rewrites_pages', 'oldxml_list_pn_rewrites_pages');
function oldxml_list_pn_rewrites_pages($list_rewrites_pages){
	$list_rewrites_pages['exportxml.xml'] = 'moduls/oldxmlfile/exportxml.php';
	return $list_rewrites_pages;
}