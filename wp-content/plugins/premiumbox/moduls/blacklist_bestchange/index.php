<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Blacklist Bestchange[:en_US][ru_RU:]Черный список Bestchange[:ru_RU]
description: [en_US:]Blacklist Bestchange[:en_US][ru_RU:]Черный список Bestchange[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('admin_menu', 'admin_menu_blacklistbest');
function admin_menu_blacklistbest(){
global $premiumbox;
	if(current_user_can('administrator') or current_user_can('pn_blacklistbest')){
		add_menu_page(__('Blacklist Bestchange','pn'), __('Blacklist Bestchange','pn'), 'read', 'pn_blacklistbest', array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('blacklist'));  
	}
}

add_filter('pn_caps','blacklistbest_pn_caps');
function blacklistbest_pn_caps($pn_caps){
	$pn_caps['pn_blacklistbest'] = __('Work with a blacklist Bestchange','pn');
	return $pn_caps;
}

add_filter('cf_auto_form_value','blacklistbest_cf_auto_form_value',1,4);
function blacklistbest_cf_auto_form_value($cauv,$value,$item,$naps){
global $wpdb, $premiumbox, $bb_error;

	if($bb_error != 1){ $bb_error = 0; }

	$cf_auto = $item->cf_auto;
	if($value){
		
		$checks = $premiumbox->get_option('blacklistbest','check');
		if(!is_array($checks)){ $checks = array(); }
		
		if($cf_auto == 'user_email' and in_array(4, $checks) and $bb_error == 0){
			$info = check_data_for_bestchange($value);
			if($info['error'] > 0){
				$cauv = array(
					'error' => 1,
					'error_text' => __('In blacklist','pn')
				);	
				$bb_error = 1;
			}
		} 
		if($cf_auto == 'user_phone' and in_array(2, $checks) and $bb_error == 0){
			$value = str_replace('+','',$value);
			$info = check_data_for_bestchange($value);
			if($info['error'] > 0){
				$cauv = array(
					'error' => 1,
					'error_text' => __('In blacklist','pn')
				);
				$bb_error = 1;
			}
		} 
		if($cf_auto == 'user_skype' and in_array(3, $checks) and $bb_error == 0){	
			$info = check_data_for_bestchange($value);
			if($info['error'] > 0){
				$cauv = array(
					'error' => 1,
					'error_text' => __('In blacklist','pn')
				);
				$bb_error = 1;
			}							
		}
	}	
	
	return $cauv;
}

add_filter('error_bids','blacklistbest_error_bids',1,3);
function blacklistbest_error_bids($error_bids, $account1, $account2){
global $wpdb, $premiumbox, $bb_error;

	if($bb_error != 1){ $bb_error = 0; }

	$user_ip = pn_real_ip();
	
	$checks = $premiumbox->get_option('blacklistbest','check');
	if(!is_array($checks)){ $checks = array(); }
	
	if(in_array(0, $checks) and !isset($error_bids['error_fields']['account1']) and $bb_error == 0){
		$info = check_data_for_bestchange($account1); 
		if($info['error'] > 0){
			$error_bids['error_fields']['account1'] = __('In blacklist','pn');
			$bb_error = 1;
		}	
	}		
		
	if(in_array(1, $checks) and !isset($error_bids['error_fields']['account2']) and $bb_error == 0){
		$info = check_data_for_bestchange($account2); 
		if($info['error'] > 0){
			$error_bids['error_fields']['account2'] = __('In blacklist','pn');
			$bb_error = 1;
		}	
	}	
	
	if(in_array(5, $checks) and $bb_error == 0){
		$info = check_data_for_bestchange($user_ip);
		if($info['error'] > 0){
			$error_bids['error_text'][] = __('Error! For your exchange denied','pn');
			$bb_error = 1;
		}
	}
	
	return $error_bids;
}

function check_data_for_bestchange($item){
global $wpdb, $premiumbox, $error_bccurl;
	
	if($error_bccurl != 1){ $error_bccurl = 0; }
	
	$info = array(
		'error' => 0,
		'info' => '',
	);
	
	if($error_bccurl != 1){
		$id = trim($premiumbox->get_option('blacklistbest','id'));
		$key = trim($premiumbox->get_option('blacklistbest','key'));
		$ctype = intval($premiumbox->get_option('blacklistbest','type'));
		
		$timeout = intval($premiumbox->get_option('blacklistbest','timeout'));
		if($timeout < 1){ $timeout = 20; }
		
		$curl_options = array(
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_CONNECTTIMEOUT => $timeout,
		);	
		
		$type = 'sc';
		if($ctype == 1){
			$type = 's';	
		} elseif($ctype == 2){
			$type = 'c';
		}
		
		if($id and $key and strlen($item) > 0){
			$curl = get_curl_parser('https://www.bestchange.org/member/scamapi.php?id='. $id .'&key='. $key .'&where=c&type='. $type .'&query='.$item, $curl_options, 'moduls', 'blacklistbest');
			$string = $curl['output'];
			if(!$curl['err']){
				$res = @simplexml_load_string($string);
				if(is_object($res)){
					$info = array(
						'error' => (string)$res->request->results,
						'info' => (string)$res->response->result->desc,
					);
				}
			} else {
				$error_bccurl = 1;
			}		
		}
	}
	
	return $info;
}

add_filter('pn_pp_adminform', 'bbblacklist_pn_pp_adminform');
function bbblacklist_pn_pp_adminform($options){
global $premiumbox;	
	
	$options['payoutblcheckbb'] = array(
		'view' => 'select',
		'title' => __('Check user details in blacklists when requesting affiliate rewards','pn') . ' (bestchange)',
		'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
		'default' => $premiumbox->get_option('partners','payoutblcheckbb'),
		'name' => 'payoutblcheckbb',
	);	
	
	return $options;
}

add_action('pn_pp_adminform_post', 'bbblacklist_pn_pp_adminform_post');
function bbblacklist_pn_pp_adminform_post(){
global $premiumbox;
	$premiumbox->update_option('partners','payoutblcheckbb',intval(is_param_post('payoutblcheckbb')));
}

add_filter('item_user_payouts_add_before', 'bbblacklist_item_user_payouts_add_before', 10, 2);
function bbblacklist_item_user_payouts_add_before($res, $arr){
global $wpdb, $premiumbox;	
	if($res['ind'] == 1 and !is_admin()){
		$check = intval($premiumbox->get_option('partners','payoutblcheckbb'));
		if($check){		
			$account = pn_strip_input($arr['pay_account']);
			$info = check_data_for_bestchange($account);
			if($info['error'] > 0){
				$res['ind'] = 0;
				$res['error'] = __('Error! Your account in blacklist. Contact us','pn');
			}
		}	
	}
		return $res;
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'config');