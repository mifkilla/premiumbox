<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Don't make currency reserve negative[:en_US][ru_RU:]Не делать резерв валюты отрицательным[:ru_RU]
description: [en_US:]Don't make currency reserve negative[:en_US][ru_RU:]Не делать резерв валюты отрицательным[:ru_RU]
version: 2.2
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
new: 1
*/

add_filter('get_currency_reserv', 'get_currency_reserv_currminreserv', 10000, 3);
function get_currency_reserv_currminreserv($reserv, $data, $decimal){
	if($reserv < 0){
		$reserv = 0;
	}		
	return $reserv;
}

add_filter('get_direction_reserv', 'get_direction_reserv_currminreserv', 10000, 4);
function get_direction_reserv_currminreserv($reserv, $vd1, $vd2, $direction){
	if($reserv < 0){
		$reserv = 0;
	}			
	return $reserv;
}										