<?php
if( !defined( 'ABSPATH')){ exit(); }

add_filter('pn_pp_adminform', 'blacklist_pn_pp_adminform');
function blacklist_pn_pp_adminform($options){
global $premiumbox;	
	
	$options['payoutblcheck'] = array(
		'view' => 'select',
		'title' => __('Check user details in blacklists when requesting affiliate rewards','pn'),
		'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
		'default' => $premiumbox->get_option('partners','payoutblcheck'),
		'name' => 'payoutblcheck',
	);	
	
	return $options;
}

add_action('pn_pp_adminform_post', 'blacklist_pn_pp_adminform_post');
function blacklist_pn_pp_adminform_post(){
global $premiumbox;
	$premiumbox->update_option('partners','payoutblcheck',intval(is_param_post('payoutblcheck')));
}

add_filter('item_user_payouts_add_before', 'blacklist_item_user_payouts_add_before', 10, 2);
function blacklist_item_user_payouts_add_before($res, $arr){
global $wpdb, $premiumbox;	
	if($res['ind'] == 1 and !is_admin()){
		$check = intval($premiumbox->get_option('partners','payoutblcheck'));
		if($check){
			$account = pn_strip_input($arr['pay_account']);
			$blacklist = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."blacklist WHERE meta_value='$account' AND meta_key='0'");
			if($blacklist > 0){
				$res['ind'] = 0;
				$res['error'] = __('Error! Your account in blacklist. Contact us','pn');
			}	
		}
	}
		return $res;
}

add_filter('cf_auto_form_value','blacklist_cf_auto_form_value',1,4);
function blacklist_cf_auto_form_value($cauv,$value,$item,$direction){
global $wpdb, $premiumbox, $bl_error;

	if($bl_error != 1){ $bl_error = 0; }
	$cf_auto = $item->cf_auto;
	if($value){
		
		$checks = $premiumbox->get_option('blacklist','check');
		if(!is_array($checks)){ $checks = array(); }
		
		if($cf_auto == 'user_email' and in_array(4, $checks) and $bl_error == 0){
			$value_arr = explode('@',$value);
			$domen = '@' . trim(is_isset($value_arr, 1));
			$blacklist = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."blacklist WHERE meta_value='$value' AND meta_key='1' OR meta_value='$domen' AND meta_key='1'");
			if($blacklist > 0){
				$cauv = array(
					'error' => 1,
					'error_text' => __('In blacklist','pn')
				);
				$bl_error = 1;
			}
		} 
		if($cf_auto == 'user_phone' and in_array(2, $checks) and $bl_error == 0){
			$value = str_replace('+','',$value);
			$blacklist = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."blacklist WHERE meta_value='$value' AND meta_key='2'");
			if($blacklist > 0){
				$cauv = array(
					'error' => 1,
					'error_text' => __('In blacklist','pn')
				);									
			}
		} 
		if($cf_auto == 'user_skype' and in_array(3, $checks) and $bl_error == 0){	
			$blacklist = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."blacklist WHERE meta_value='$value' AND meta_key='3'");
			if($blacklist > 0){
				$cauv = array(
					'error' => 1,
					'error_text' => __('In blacklist','pn')
				);					
			}							
		}
	}	

	return $cauv;
}

add_filter('error_bids','blacklist_error_bids',1,3);
function blacklist_error_bids($error_bids, $account1, $account2){
global $wpdb, $premiumbox, $bl_error;

	if($bl_error != 1){ $bl_error = 0; }

	$user_ip = pn_real_ip();
	
	$checks = $premiumbox->get_option('blacklist','check');
	if(!is_array($checks)){ $checks = array(); }
	
	if(in_array(0, $checks) and $account1 and !isset($error_bids['error_fields']['account1']) and $bl_error == 0){
		$blacklist = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."blacklist WHERE meta_value='$account1' AND meta_key='0'");
		if($blacklist > 0){
			$error_bids['error_fields']['account1'] = __('In blacklist','pn');
			$bl_error = 1;
		}	
	}
	
	if(in_array(1, $checks) and $account2 and !isset($error_bids['error_fields']['account2']) and $bl_error == 0){
		$blacklist = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."blacklist WHERE meta_value='$account2' AND meta_key='0'");
		if($blacklist > 0){
			$error_bids['error_fields']['account2'] = __('In blacklist','pn');
			$bl_error = 1;
		}	
	}	
	
	if(in_array(5, $checks) and $user_ip and $bl_error == 0){
		$blacklist = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."blacklist WHERE meta_value='$user_ip' AND meta_key='4'");
		if($blacklist > 0){
			$error_bids['error_text'][] = __('Error! Your IP address in black list','pn');
			$bl_error = 1;
		}
	}
	
	return $error_bids;
}

add_filter('get_statusbids_for_admin', 'get_statusbids_for_admin_blacklist');
function get_statusbids_for_admin_blacklist($st){
	if(current_user_can('administrator') or current_user_can('pn_blacklist')){
		$st['blacklist'] = array(
			'name' => 'blacklist',
			'title' => __('add to blacklist','pn'),
			'color' => '#ffffff',
			'background' => '#000000',
		);
		$st['delblacklist'] = array(
			'name' => 'delblacklist',
			'title' => __('remove from blacklist','pn'),
			'color' => '#ffffff',
			'background' => '#028e19',
		);		
	}
		return $st;
}

add_action('bidstatus_admin_action', 'bidstatus_admin_action_blacklist', 10, 2);
function bidstatus_admin_action_blacklist($ids, $action){
global $wpdb;

	/* ЧС */
	if($action == 'blacklist'){
		foreach($ids as $id){
			$id = intval($id);
			$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE id='$id'");
			if(isset($item->id)){
						
				$account1 = pn_strip_input($item->account_give);
				if($account1){
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."blacklist WHERE meta_value='$account1' AND meta_key='0'");
					if($cc == 0){
						$wpdb->insert($wpdb->prefix.'blacklist', array('meta_value'=>$account1,'meta_key'=>0, 'comment_text'=> sprintf(__('Bid id %s','pn'), $id)));
					}
				}

				$account2 = pn_strip_input($item->account_get);
				if($account2){
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."blacklist WHERE meta_value='$account2' AND meta_key='0'");
					if($cc == 0){
						$wpdb->insert($wpdb->prefix.'blacklist', array('meta_value'=>$account2,'meta_key'=>0, 'comment_text'=> sprintf(__('Bid id %s','pn'), $id)));
					}	
				}						
						
				$user_email = is_email($item->user_email);
				if($user_email){
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."blacklist WHERE meta_value='$user_email' AND meta_key='1'");
					if($cc == 0){
						$wpdb->insert($wpdb->prefix.'blacklist', array('meta_value'=>$user_email,'meta_key'=>1, 'comment_text'=> sprintf(__('Bid id %s','pn'), $id)));
					}
				}						
						
				$user_phone = str_replace('+','',pn_strip_input($item->user_phone));
				if($user_phone){
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."blacklist WHERE meta_value='$user_phone' AND meta_key='2'");
					if($cc == 0){
						$wpdb->insert($wpdb->prefix.'blacklist', array('meta_value'=>$user_phone,'meta_key'=>2, 'comment_text'=> sprintf(__('Bid id %s','pn'), $id)));
					}	
				}
						
				$user_skype = pn_strip_input($item->user_skype);
				if($user_skype){
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."blacklist WHERE meta_value='$user_skype' AND meta_key='3'");
					if($cc == 0){
						$wpdb->insert($wpdb->prefix.'blacklist', array('meta_value'=>$user_skype,'meta_key'=>3, 'comment_text'=> sprintf(__('Bid id %s','pn'), $id)));
					}
				}

				$user_ip = pn_strip_input($item->user_ip);
				if($user_ip){
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."blacklist WHERE meta_value='$user_ip' AND meta_key='4'");
					if($cc == 0){
						$wpdb->insert($wpdb->prefix.'blacklist', array('meta_value'=>$user_ip,'meta_key'=>4, 'comment_text'=> sprintf(__('Bid id %s','pn'), $id)));
					}
				}	
				
				if($user_email){
				
					$notify_tags = array();
					$notify_tags['[sitename]'] = pn_site_name();
					$notify_tags['[bid_id]'] = $item->id;
					$notify_tags = apply_filters('notify_tags_inblacklist', $notify_tags, $item);		

					$user_send_data = array(
						'user_email' => $user_email,
					);
					$user_send_data = apply_filters('user_send_data', $user_send_data, 'inblacklist', $item);
					$result_mail = apply_filters('premium_send_message', 0, 'inblacklist', $notify_tags, $user_send_data);
					
				}	
			}
		}
	}
	if($action == 'delblacklist'){
		foreach($ids as $id){
			$id = intval($id);
			$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE id='$id'");
			if(isset($item->id)){
						
				$account1 = pn_strip_input($item->account_give);
				if($account1){
					$wpdb->query("DELETE FROM ".$wpdb->prefix."blacklist WHERE meta_value='$account1' AND meta_key='0'");
				}

				$account2 = pn_strip_input($item->account_get);
				if($account2){
					$wpdb->query("DELETE FROM ".$wpdb->prefix."blacklist WHERE meta_value='$account2' AND meta_key='0'");	
				}						
						
				$user_email = is_email($item->user_email);
				if($user_email){
					$wpdb->query("DELETE FROM ".$wpdb->prefix."blacklist WHERE meta_value='$user_email' AND meta_key='1'");
				}						
						
				$user_phone = str_replace('+','',pn_strip_input($item->user_phone));
				if($user_phone){
					$wpdb->query("DELETE FROM ".$wpdb->prefix."blacklist WHERE meta_value='$user_phone' AND meta_key='2'");	
				}
						
				$user_skype = pn_strip_input($item->user_skype);
				if($user_skype){
					$wpdb->query("DELETE FROM ".$wpdb->prefix."blacklist WHERE meta_value='$user_skype' AND meta_key='3'");
				}

				$user_ip = pn_strip_input($item->user_ip);
				if($user_ip){
					$wpdb->query("DELETE FROM ".$wpdb->prefix."blacklist WHERE meta_value='$user_ip' AND meta_key='4'");
				}	
						
			}
		}
	}	
	/* end ЧС */	
}