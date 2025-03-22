<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]User login in order[:en_US][ru_RU:]Логин юзера в заявке[:ru_RU]
description: [en_US:]User login in order[:en_US][ru_RU:]Логин юзера в заявке[:ru_RU]
version: 2.2
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

add_filter('onebid_icons','onebid_icons_uslogin',1000,3);
function onebid_icons_uslogin($onebid_icon, $item, $data_fs){
	
	$user_id = $item->user_id;
	if($user_id > 0){
		$user_login = is_user(is_isset($item,'user_login'));
		if(!$user_login){
			$ui = get_userdata($user_id);
			$user_login = is_user(is_isset($ui,'user_login'));			
		}
		$onebid_icon['uslogin'] = array(
			'type' => 'text',
			'title' => __('User login','pn'),
			'label' => $user_login .': [last_name] [first_name] [second_name]', 
			'link' => pn_edit_user_link($user_id),
			'link_target' => '_blank',
		);			
	}
	
	return $onebid_icon;
}