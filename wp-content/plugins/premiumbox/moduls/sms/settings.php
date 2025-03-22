<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('pn_adminpage_title_all_sms_temps', 'def_pn_adminpage_title_all_sms_temps');
function def_pn_adminpage_title_all_sms_temps(){
	_e('SMS templates','pn');
}

add_action('pn_adminpage_content_all_sms_temps','def_pn_adminpage_content_all_sms_temps');
function def_pn_adminpage_content_all_sms_temps(){
				
	$place = pn_strip_input(is_param_get('place'));	
				
	$form = new PremiumForm();	
				
	$selects = array();
	$selects[] = array(
		'link' => admin_url("admin.php?page=all_sms_temps"),
		'title' => '--' . __('Make a choice','pn') . '--',
		'background' => '',
		'default' => '',
	);			
		 
	$places_admin = apply_filters('list_admin_notify', array(), 'sms');
	if(!is_array($places_admin)){ $places_admin = array(); }
					
	if(count($places_admin) > 0){
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_sms_temps&place=admin_notify"),
			'title' => '---' . __('Admin notification','pn'),
			'background' => '#faf9c4',
			'default' => 'admin_notify',
		);				
	}
					
	foreach($places_admin as $key => $val){	
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_sms_temps&place=".$key),
			'title' => $val,
			'background' => '',
			'default' => $key,
		);				
	}		
					
	$places_user = apply_filters('list_user_notify',array(), 'sms');
	if(!is_array($places_user)){ $places_user = array(); }
					
	if(count($places_user) > 0){
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_sms_temps&place=user_notify"),
			'title' => '---' . __('Users notification','pn'),
			'background' => '#faf9c4',
			'default' => 'user_notify',
		);					
	}			
					
	foreach($places_user as $key => $val){
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_sms_temps&place=".$key),
			'title' => $val,
			'background' => '',
			'default' => $key,
		);				
	}
					
	$form->select_box($place, $selects, __('Setting up','pn'));

	if(isset($places_admin[$place]) or isset($places_user[$place])){
				
		$pn_notify = get_option('pn_notify_sms');
		$data = is_isset($pn_notify, $place);		
				
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Templates','pn'),
			'submit' => __('Save','pn'),
		);
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'block',
			'default' => $place,
		);				
		$options['send'] = array(
			'view' => 'select',
			'title' => __('To send','pn'),
			'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
			'default' => is_isset($data, 'send'),
			'name' => 'send',
			'work' => 'int',
		);					
		if(isset($places_admin[$place])){
			$options['to'] = array(
				'view' => 'inputbig',
				'title' => __('Administrator phone number','pn'),
				'default' => is_isset($data, 'to'),
				'name' => 'to',
				'work' => 'input',
			);					
			$options['tohelp'] = array(
				'view' => 'help',
				'title' => __('More info','pn'),
				'default' => __('If the recipient has several phone numbers, phone numbers should be comma-separated','pn'),
			);					
		}
						
		$tags = array(
			'sitename' => array(
				'title' => __('Website name','pn'),
				'start' => '[sitename]',
			),
		);
		$tags = apply_filters('list_notify_tags_'.$place, $tags);
						
		$options['text'] = array(
			'view' => 'editor',
			'title' => __('Text','pn'),
			'default' => is_isset($data, 'text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'text',
			'work' => 'text',
			'word_count' => 1,
			'ml' => 1,
		);				
						
		$params_form = array(
			'filter' => 'all_sms_temps_option',
			'method' => 'ajax',
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);		 		
				
	} else {

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Send test sms','pn'),
			'submit' => __('Send a message','pn'),
		);
		$options['to'] = array(
			'view' => 'inputbig',
			'title' => __('Phone number','pn'),
			'default' => '',
			'name' => 'to',
		);		
				
		$params_form = array(
			'filter' => '',
			'method' => 'ajax',
			'data' => '',
			'form_link' => pn_link('all_sms_send_test'),
			'button_title' => __('Send a message','pn'),
		);
		$form->init_form($params_form, $options); 	
			
	}
}

add_action('premium_action_all_sms_send_test','def_premium_action_all_sms_send_test');
function def_premium_action_all_sms_send_test(){
	global $wpdb;	

	only_post();
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator','pn_change_notify'));
			
	$to = is_phone(is_param_post('to'));
	if(!$to){
		$form->error_form(__('Error! You have not entered phone number','pn'));
	} else {
		$result = apply_filters('pn_sms_send', 0, 'Test SMS', $to);			
	}

	$back_url = is_param_post('_wp_http_referer');
	$back_url .= '&reply=true';
					
	$form->answer_form($back_url);
}

add_action('premium_action_all_sms_temps','def_premium_action_all_sms_temps');
function def_premium_action_all_sms_temps(){
	global $wpdb;

	only_post();
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator','pn_change_notify'));
			
	$block = pn_strip_input(is_param_post('block'));	
							
	if($block){
		$pn_notify = get_option('pn_notify_sms');
		if(!is_array($pn_notify)){ $pn_notify = array(); }

		$pn_notify[$block]['send'] = intval(is_param_post('send'));
		$pn_notify[$block]['to'] = pn_strip_input(is_param_post('to'));
		$pn_notify[$block]['text'] = pn_strip_input(is_param_post_ml('text'));

		update_option('pn_notify_sms', $pn_notify);
	}			

	do_action('all_sms_temps_option_post');

	$back_url = is_param_post('_wp_http_referer');
	$back_url .= '&reply=true';
					
	$form->answer_form($back_url);  
}		

add_filter('premium_send_message', 'sms_premium_send_message', 11, 5);
function sms_premium_send_message($result, $method, $notify_tags='', $user_send_data='', $lang=''){
	if(!is_array($notify_tags)){ $notify_tags = array(); }
	if(!$lang){ $lang = get_locale(); }
			
	$pn_notify = get_option('pn_notify_sms');
				
	if(isset($pn_notify[$method])){
		$data = $pn_notify[$method];
		if($data['send'] == 1){
			$html = pn_strip_input(ctv_ml($data['text'], $lang));
			$html = get_replace_arrays($notify_tags, $html);
			$to = is_phone(is_isset($user_send_data, 'user_phone'));
			if(!$to){ $to = is_isset($data,'to'); }
						
			$nresult = apply_filters('pn_sms_send', 0, $html, $to);
			if($nresult == 1){ return 1; }
		}
	}		
			
	return $result;
}

add_filter('user_send_data', 'sms_user_send_data', 10, 3);
function sms_user_send_data($user_send_data, $place, $ui=''){
	
	if(isset($ui->user_phone)){
		if($place == 'alogs'){
			if(isset($ui->alogs_sms) and $ui->alogs_sms == 1){
				$user_send_data['user_phone'] = is_isset($ui, 'user_phone');
			}
		} elseif($place == 'letterauth'){
			if(isset($ui->sms_login) and $ui->sms_login == 1){
				$user_send_data['user_phone'] = is_isset($ui, 'user_phone');
			}			
		} else {
			$user_send_data['user_phone'] = is_isset($ui, 'user_phone');
		}
	}
	return $user_send_data;
}

add_filter('all_user_editform', 'sms_all_user_editform', 11, 2);
function sms_all_user_editform($options, $bd_data){
	
	$n_options = array();
	$n_options['alogs_sms'] = array(
		'view' => 'select',
		'title' => __('Notification upon authentication','pn') .' ('. __('SMS','pn') .')',
		'options' => array('0'=> __('No','pn'),'1'=> __('Yes','pn')),
		'default' => intval($bd_data->alogs_sms),
		'name' => 'alogs_sms',
	);
	$n_options['sms_login'] = array(
		'view' => 'select',
		'title' => __('Two-factor authentication by pin-code','pn').' ('. __('SMS','pn') .')',
		'options' => array('0'=> __('No','pn'),'1'=> __('Yes','pn')),
		'default' => intval($bd_data->sms_login),
		'name' => 'sms_login',
	);
	
	$options = pn_array_insert($options, 'email_login', $n_options, 'after');
	return $options;
}

add_filter('all_user_editform_post', 'sms_all_user_editform_post'); 
function sms_all_user_editform_post($new_user_data){
	
	$new_user_data['alogs_sms'] = intval(is_param_post('alogs_sms'));
	$new_user_data['sms_login'] = intval(is_param_post('sms_login'));
	
	return $new_user_data;
}

add_filter('securityform_filelds', 'sms_securityform_filelds', 11);
function sms_securityform_filelds($items){
	$ui = wp_get_current_user();
	$n_items = array();
	$n_items['alogs_sms'] = array(
		'name' => 'alogs_sms',
		'title' => __('Notification upon authentication','pn') .' ('. __('SMS','pn') .')',
		'req' => 0,
		'value' => is_isset($ui,'alogs_sms'),
		'type' => 'select',
		'options' => array(__('No','pn'), __('Yes','pn')),
	);	
	$items = pn_array_insert($items, 'alogs_email', $n_items, 'after');
	return $items;
}

add_filter('data_securityform', 'sms_data_securityform');
function sms_data_securityform($new_user_data){
	
	$new_user_data['alogs_sms'] = intval(is_param_post('alogs_sms'));
	if(isset($_POST['sms_login'])){
		$new_user_data['sms_login'] = intval(is_param_post('sms_login'));
	}
	return $new_user_data;
}