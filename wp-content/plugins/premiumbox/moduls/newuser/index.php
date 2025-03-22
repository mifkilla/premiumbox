<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Newcomers verification during exchange[:en_US][ru_RU:]Проверка на новичка при обмене[:ru_RU]
description: [en_US:]Newcomers verification during exchange[:en_US][ru_RU:]Проверка на новичка при обмене[:ru_RU]
version: 2.2
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_newuser');
add_action('all_bd_activated', 'bd_all_moduls_active_newuser');
function bd_all_moduls_active_newuser(){
global $wpdb;	

    $query = $wpdb->query("SHOW COLUMNS FROM ". $wpdb->prefix ."exchange_bids LIKE 'new_user'");
    if ($query == 0) {
        $wpdb->query("ALTER TABLE ". $wpdb->prefix ."exchange_bids ADD `new_user` int(2) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'max_newuser'");
	if($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `max_newuser` varchar(50) NOT NULL default '0'");
	}	
	
	$table_name = $wpdb->prefix ."users_old_data";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`account_give` varchar(250) NOT NULL,
		`account_get` varchar(250) NOT NULL,
		`user_phone` varchar(150) NOT NULL,
		`user_skype` varchar(150) NOT NULL,
		`user_telegram` varchar(150) NOT NULL,
		`user_email` varchar(150) NOT NULL,
		`bid_id` bigint(20) NOT NULL default '0',
		PRIMARY KEY ( `id` ),
		INDEX (`account_give`),
		INDEX (`account_get`),
		INDEX (`user_phone`),
		INDEX (`user_skype`),
		INDEX (`user_email`),
		INDEX (`user_telegram`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
}

add_filter('change_bidstatus', 'newuser_change_bidstatus', 80, 4);   
function newuser_change_bidstatus($item, $set_status, $place, $user_or_system){
global $wpdb, $premiumbox;
	$item_id = $item->id;
	if($set_status == 'success'){
		$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."users_old_data WHERE bid_id='$item_id'");
		if($cc == 0){
			$arr = array();
			$arr['bid_id'] = $item_id;
			$arr['account_give'] = $item->account_give;
			$arr['account_get'] = $item->account_get;
			$arr['user_phone'] = $item->user_phone;
			$arr['user_skype'] = $item->user_skype;
			$arr['user_email'] = $item->user_email;
			$arr['user_telegram'] = $item->user_telegram;
			$wpdb->insert($wpdb->prefix ."users_old_data", $arr);
		}
	} elseif($set_status != 'archived'){
		$wpdb->query("DELETE FROM ".$wpdb->prefix."users_old_data WHERE bid_id = '$item_id'");
	}	
	return $item;
}

add_filter('pn_exchange_settings_option', 'newuser_exchange_settings_option');
function newuser_exchange_settings_option($options){
global $premiumbox;	
	
	$options['check_new_user_linetop'] = array(
		'view' => 'line',
	);	
	$options['check_new_user'] = array(
		'view' => 'user_func',
		'name' => 'check_new_user',
		'func_data' => array(),
		'func' => 'pn_newuser_option',
	);
	$options['who_new_user'] = array(
		'view' => 'select',
		'title' => __('Beginner identification method','pn'),
		'options' => array('0'=>__('If all user details are unique','pn'),'1'=>__('If at least one user detail is unique','pn')),
		'default' => $premiumbox->get_option('new_user','who'),
		'name' => 'who_new_user',
	);	
	$tags = array(
		'amount' => array(
			'title' => __('Amount Send','pn'),
			'start' => '[max_give]',
		),
		'currency' => array(
			'title' => __('Currency Send','pn'),
			'start' => '[currency_give]',
		),		
	);			
	$options['text_new_user'] = array(
		'view' => 'editor',
		'title' => __('Warning text before creating an order for beginner','pn'),
		'default' => $premiumbox->get_option('new_user','text'),
		'tags' => $tags,
		'rows' => '10',
		'name' => 'text_new_user',
		'work' => 'text',
		'ml' => 1,
	);
	$options['texterror_new_user'] = array(
		'view' => 'editor',
		'title' => __('Error text if amount exceeds specified limit for beginner','pn'),
		'default' => $premiumbox->get_option('new_user','texterror'),
		'tags' => $tags,
		'rows' => '10',
		'name' => 'texterror_new_user',
		'work' => 'text',
		'ml' => 1,
	);		
	
	$options['check_new_user_linebot'] = array(
		'view' => 'line',
	);	
	
	return $options;	
}

function pn_newuser_option(){
	$check_new_user = get_option('check_new_user');
	if(!is_array($check_new_user)){ $check_new_user = array(); }
				
	$fields = array(
		'0'=> __('Invoice Send','pn'),
		'1'=> __('Invoice Receive','pn'),
		'2'=> __('Mobile phone no.','pn'),
		'3'=> __('Skype','pn'),
		'4'=> __('E-mail','pn'),
		'5'=> __('Telegram','pn'),
	);
	?>
	<div class="premium_standart_line"> 
		<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Newcomer verification','pn'); ?></div></div>
		<div class="premium_stline_right"><div class="premium_stline_right_ins">
			<div class="premium_wrap_standart">
				
				<?php
				$scroll_lists = array();
							
				foreach($fields as $key => $val){
					$checked = 0;
					if(in_array($key,$check_new_user)){
						$checked = 1;
					}	
					$scroll_lists[] = array(
						'title' => $val,
						'checked' => $checked,
						'value' => $key,
					);
				}
				echo get_check_list($scroll_lists, 'check_new_user[]');
				?>			
				
				<div class="premium_clear"></div>
			</div>
		</div></div>
			<div class="premium_clear"></div>
	</div>					
	<?php				
}

add_action('pn_exchange_settings_option_post', 'newuser_exchange_settings_option_post');
function newuser_exchange_settings_option_post(){
global $premiumbox;	
	
	$check_new_user = is_param_post('check_new_user');
	update_option('check_new_user', $check_new_user);
	
	$val = intval(is_param_post('who_new_user'));
	$premiumbox->update_option('new_user', 'who', $val);
	
	$val = pn_strip_text(is_param_post_ml('text_new_user'));
	$premiumbox->update_option('new_user', 'text', $val);
	
	$val = pn_strip_text(is_param_post_ml('texterror_new_user'));
	$premiumbox->update_option('new_user', 'texterror', $val);		
	
}

add_action('tab_direction_tab8','tab_direction_tab_newuser', 200,2);
function tab_direction_tab_newuser($data, $data_id){
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. exchange amount for Send for beginner','pn'); ?></span></div>
			<div class="premium_wrap_standart">							
				<input type="text" name="max_newuser" value="<?php echo is_sum(is_isset($data,'max_newuser')); ?>" />				
			</div>		
		</div>
	</div>	
<?php
}

add_filter('pn_direction_addform_post', 'newuser_pn_direction_addform_post');
function newuser_pn_direction_addform_post($array){
	$array['max_newuser'] = is_sum(is_param_post('max_newuser'));
	return $array;
}

add_filter('exchange_other_filter', 'newuser_exchange_other_filter', 100, 5);
function newuser_exchange_other_filter($html, $direction, $vd1, $vd2, $cdata){
global $premiumbox;	
		
	$direction_id = $direction->id;
	$max_newuser = is_sum(is_isset($direction, 'max_newuser'));
	if($max_newuser > 0){
		$text = pn_strip_text(ctv_ml($premiumbox->get_option('new_user','text')));
		$text = str_replace('[max_give]', $max_newuser, $text);
		$text = str_replace('[currency_give]', get_currency_title($vd1), $text);
		if($text){
			$html .= '
			<div class="notice_message newuser_notice">
				<div class="notice_message_ins">
					<div class="notice_message_text">
						<div class="notice_message_text_ins">
							'. apply_filters('comment_text', $text) .'
						</div>
					</div>
				</div>
			</div>			
			';
		}
	}
	
	return $html;
} 

add_filter('error_bids', 'newuser_error_bids', 100, 9);
function newuser_error_bids($error_bids, $account1, $account2, $direction, $vd1, $vd2, $auto_data, $unmetas, $cdata){
global $wpdb, $premiumbox, $premiumbox_newuser_identifier;	
	
	$check_new_user = get_option('check_new_user');
	if(!is_array($check_new_user)){ $check_new_user = array(); }	
	
	$user_phone = pn_sfilter(str_replace('+','',is_phone(is_isset($auto_data,'user_phone'))));
	$user_skype = pn_sfilter(pn_strip_input(is_isset($auto_data,'user_skype')));
	$user_telegram = pn_sfilter(pn_strip_input(is_isset($auto_data,'user_telegram')));
	$user_email = pn_sfilter(is_email(is_isset($auto_data,'user_email')));
	$account1 = pn_sfilter(pn_strip_input($account1));
	$account2 = pn_sfilter(pn_strip_input($account2));
	
	$who = intval($premiumbox->get_option('new_user','who'));
	
	$new = 0;
	
	if($who == 1){
		if($account1 and in_array(0,$check_new_user) and $new != 1){
			$where = "account_give = '$account1'";
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."users_old_data WHERE $where");
			if($cc < 1){
				$new = 1;
			}
		}	
		if($account2 and in_array(1,$check_new_user) and $new != 1){
			$where = "account_get = '$account2'";
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."users_old_data WHERE $where");
			if($cc < 1){
				$new = 1;
			}
		}
		if($user_phone and in_array(2,$check_new_user) and $new != 1){
			$where = "user_phone = '$user_phone'";
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."users_old_data WHERE $where");
			if($cc < 1){
				$new = 1;
			}
		}
		if($user_skype and in_array(3,$check_new_user) and $new != 1){
			$where = "user_skype = '$user_skype'";
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."users_old_data WHERE $where");
			if($cc < 1){
				$new = 1;
			}
		}
		if($user_email and in_array(4,$check_new_user) and $new != 1){
			$where = "user_email = '$user_email'";
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."users_old_data WHERE $where");
			if($cc < 1){
				$new = 1;
			}
		}
		if($user_telegram and in_array(5,$check_new_user) and $new != 1){
			$where = "user_telegram = '$user_telegram'";
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."users_old_data WHERE $where");
			if($cc < 1){
				$new = 1;
			}
		}		
	} else {
		$where = array();
		if($account1 and in_array(0,$check_new_user)){
			$where[] = "account_give = '$account1'";
		}	
		if($account2 and in_array(1,$check_new_user)){
			$where[] = "account_get = '$account2'";
		}
		if($user_phone and in_array(2,$check_new_user)){
			$where[] = "user_phone = '$user_phone'";
		}	
		if($user_skype and in_array(3,$check_new_user)){
			$where[] = "user_skype = '$user_skype'";
		}
		if($user_email and in_array(4,$check_new_user)){
			$where[] = "user_email = '$user_email'";
		}
		if($user_telegram and in_array(5,$check_new_user)){
			$where[] = "user_telegram = '$user_telegram'";
		}	
		
		if(count($where) > 0){
			$new = 1;
			$where_join = join(' OR ', $where);
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."users_old_data WHERE $where_join");
			if($cc > 0){
				$new = 0;
			}
		}
	}
		
	$premiumbox_newuser_identifier = $new;	
		
	$max_newuser = is_sum(is_isset($direction, 'max_newuser'));
	$sum_give = is_sum(is_isset($cdata, 'sum1'));
	if($new == 1 and $max_newuser > 0 and $sum_give > $max_newuser){
		$text = pn_strip_text(ctv_ml($premiumbox->get_option('new_user','texterror')));
		if(!$text){ $text = sprintf(__('Error! Max. exchange amount for Send is %1s %2s','pn'), $max_newuser, get_currency_title($vd1)); }
		$text = str_replace('[max_give]', $max_newuser, $text);
		$text = str_replace('[currency_give]', get_currency_title($vd1), $text);
		
		$error_bids['error_text'][] = $text;
		$error_bids['error_fields']['sum1'][] = '<span class="js_amount" data-id="sum1" data-val="'. $max_newuser .'">' . __('max','pn').'.: '. is_out_sum($max_newuser, $vd1->currency_decimal, 'reserv') .' '. is_xml_value($vd1->currency_code_title) . '</span>';
	}

	return $error_bids;
}

add_filter('array_data_create_bids', 'newuser_array_data_create_bids', 10, 4);
function newuser_array_data_create_bids($array, $direction, $vd1, $vd2){
global $premiumbox_newuser_identifier;
	$array['new_user'] = intval($premiumbox_newuser_identifier);
	return $array;
}

add_filter('change_bids_filter_list', 'newuser_change_bids_filter_list'); 
function newuser_change_bids_filter_list($lists){
global $wpdb;
	
	$options = array(
		'0' => '--'. __('All','pn').'--',
		'1' => __('Yes','pn'),
		'2' => __('No','pn'),
	);
			
	$lists['other']['new'] = array(
		'title' => __('Newcomer','pn'),
		'name' => 'new',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);	
	
	return $lists;
}

add_filter('where_request_sql_bids', 'newuser_where_request_sql_bids', 10,2); 
function newuser_where_request_sql_bids($where, $pars_data){
global $wpdb;	
	
	$pr = $wpdb->prefix;
	$sql_operator = is_sql_operator($pars_data);
	$new = intval(is_isset($pars_data,'new'));
	if($new == 1){
		$where .= " {$sql_operator} {$pr}exchange_bids.new_user = '1'"; 
	} elseif($new == 2){	
		$where .= " {$sql_operator} {$pr}exchange_bids.new_user = '0'";
	}	
	
	return $where;
}

add_filter('onebid_icons','onebid_icons_newuser',10,2);
function onebid_icons_newuser($onebid_icon, $item){
global $premiumbox;
	
	if(isset($item->new_user) and $item->new_user == 1){
		$onebid_icon['newuser'] = array(
			'type' => 'label',
			'title' => __('Attention! Newcomer makes an exchange','pn'),
			'image' => $premiumbox->plugin_url . 'images/new.png',
		);	
	}
	
	return $onebid_icon;
}