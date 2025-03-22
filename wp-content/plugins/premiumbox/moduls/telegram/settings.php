<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('pn_adminpage_title_all_telegram_temps', 'def_pn_adminpage_title_all_telegram_temps');
function def_pn_adminpage_title_all_telegram_temps(){
	_e('Telegram templates','pn');
}

add_action('pn_adminpage_content_all_telegram_temps','def_pn_adminpage_content_all_telegram_temps');
function def_pn_adminpage_content_all_telegram_temps(){
			
	$place = pn_strip_input(is_param_get('place'));	
			
	$form = new PremiumForm();	
			
	$selects = array();
	$selects[] = array(
		'link' => admin_url("admin.php?page=all_telegram_temps"),
		'title' => '--' . __('Make a choice','pn') . '--',
		'background' => '',
		'default' => '',
	);			
	 
	$places_admin = apply_filters('list_admin_notify',array(),'telegram');
	if(!is_array($places_admin)){ $places_admin = array(); }
				
	if(count($places_admin) > 0){
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_telegram_temps&place=admin_notify"),
			'title' => '---' . __('Admin notification','pn'),
			'opt_data' => 'style="background: #faf9c4"',
			'default' => 'admin_notify',
		);				
	}
				
	foreach($places_admin as $key => $val){	
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_telegram_temps&place=".$key),
			'title' => $val,
			'default' => $key,
		);				
	}		
				
	$places_user = apply_filters('list_user_notify',array(),'telegram');
	if(!is_array($places_user)){ $places_user = array(); }
				
	if(count($places_user) > 0){
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_telegram_temps&place=user_notify"),
			'title' => '---' . __('Users notification','pn'),
			'opt_data' => 'style="background: #faf9c4"',
			'default' => 'user_notify',
		);					
	}			
				
	foreach($places_user as $key => $val){
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_telegram_temps&place=".$key),
			'title' => $val,
			'default' => $key,
		);				
	}
				
	$form->select_box($place, $selects, __('Setting up','pn'));

	if(isset($places_admin[$place]) or isset($places_user[$place])){
			
		$pn_notify = get_option('pn_notify_telegram');
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
					'title' => __('Administrator telegram username (without @)','pn'),
					'default' => is_isset($data, 'to'),
					'name' => 'to',
					'work' => 'input',
				);					
				$options['tohelp'] = array(
					'view' => 'help',
					'title' => __('More info','pn'),
					'default' => __('If the recipient has several telegram logins, telegram logins should be comma-separated','pn'),
				);					
			}
					
			$tags = array(
				'sitename' => array(
					'title' => __('Website name','pn'),
					'start' => '[sitename]',
				),
				'b' => array(
					'title' => 'b',
					'start' => '<b>',
					'end' => '</b>',
				),
				'strong' => array(
					'title' => 'strong',
					'start' => '<strong>',
					'end' => '</strong>',
				),
				'i' => array(
					'title' => 'i',
					'start' => '<i>',
					'end' => '</i>',
				),
				'em' => array(
					'title' => 'em',
					'start' => '<em>',
					'end' => '</em>',
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
				'ml' => 1,
			);				
					
			$params_form = array(
				'filter' => 'all_telegram_temps_option',
				'method' => 'ajax',
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options);		 		
			
	} 
}

add_action('premium_action_all_telegram_temps','def_premium_action_all_telegram_temps');
function def_premium_action_all_telegram_temps(){
global $wpdb;

	only_post();
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator','pn_change_notify'));
		
	$block = pn_strip_input(is_param_post('block'));	
						
	if($block){
		$pn_notify = get_option('pn_notify_telegram');
		if(!is_array($pn_notify)){ $pn_notify = array(); }

		$pn_notify[$block]['send'] = intval(is_param_post('send'));
		$pn_notify[$block]['to'] = pn_strip_input(is_param_post('to'));
		$pn_notify[$block]['text'] = pn_strip_text(addslashes(is_param_post_ml('text')));

		update_option('pn_notify_telegram', $pn_notify);
	}			

	do_action('all_telegram_temps_option_post');

	$back_url = is_param_post('_wp_http_referer');
	$back_url .= '&reply=true';
				
	$form->answer_form($back_url);  
}

add_filter('premium_send_message', 'telegram_premium_send_message', 12, 5);
function telegram_premium_send_message($result, $method, $notify_tags='', $user_send_data='', $lang=''){
global $wpdb;
	
	if(!is_array($notify_tags)){ $notify_tags = array(); }
	if(!$lang){ $lang = get_locale(); }
		
	$pn_notify = get_option('pn_notify_telegram');
			
	if(isset($pn_notify[$method])){
		$data = $pn_notify[$method];
		if($data['send'] == 1){
			$html = pn_strip_text(ctv_ml($data['text'], $lang));
			$html = get_replace_arrays($notify_tags, $html);
			$to = trim(is_isset($user_send_data, 'user_telegram'));
			if(!$to){ $to = is_isset($data,'to'); }
					
			$tdata = get_option('telegram_settings');
			if(!is_array($tdata)){ $tdata = array(); }
			$token = pn_strip_input(is_isset($tdata, 'token'));		
			$class = new TelegramBot($token, is_isset($tdata, 'bot_logs'), is_isset($tdata, 'answer_logs'));	
				
			$logins = explode(',', $to);
			foreach($logins as $login){
				$login = str_replace('@', '', pn_strip_input($login));
				if($login){
					$chat = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."telegram WHERE telegram_login = '$login'");
					if(isset($chat->id)){
						$class->send('text', $chat->telegram_chat_id, $html);
					}
				}
			}
			
			return 1;
		}
	}		
		
	return $result;
}		

add_filter('user_send_data', 'telegram_user_send_data', 10, 3);
function telegram_user_send_data($user_send_data, $place, $ui=''){
	
	if(isset($ui->user_telegram)){
		if($place == 'alogs'){
			if(isset($ui->alogs_telegram) and $ui->alogs_telegram == 1){
				$user_send_data['user_telegram'] = is_isset($ui, 'user_telegram');
			}
		} elseif($place == 'letterauth'){
			if(isset($ui->telegram_login) and $ui->telegram_login == 1){
				$user_send_data['user_telegram'] = is_isset($ui, 'user_telegram');
			}			
		} else {
			$user_send_data['user_telegram'] = is_isset($ui, 'user_telegram');
		}
	}
	return $user_send_data;
}

add_filter('all_user_editform', 'telegram_all_user_editform', 12, 2);
function telegram_all_user_editform($options, $bd_data){
	
	$n_options = array();
	$n_options['alogs_telegram'] = array(
		'view' => 'select',
		'title' => __('Notification upon authentication','pn') .' ('. __('Telegram','pn') .')',
		'options' => array('0'=> __('No','pn'),'1'=> __('Yes','pn')),
		'default' => intval($bd_data->alogs_telegram),
		'name' => 'alogs_telegram',
	);
	$n_options['telegram_login'] = array(
		'view' => 'select',
		'title' => __('Two-factor authentication by pin-code','pn').' ('. __('Telegram','pn') .')',
		'options' => array('0'=> __('No','pn'),'1'=> __('Yes','pn')),
		'default' => intval($bd_data->telegram_login),
		'name' => 'telegram_login',
	);
	
	$options = pn_array_insert($options, 'email_login', $n_options, 'after');
	return $options;
}

add_filter('all_user_editform_post', 'telegram_all_user_editform_post'); 
function telegram_all_user_editform_post($new_user_data){
	
	$new_user_data['alogs_telegram'] = intval(is_param_post('alogs_telegram'));
	$new_user_data['telegram_login'] = intval(is_param_post('telegram_login'));
	
	return $new_user_data;
}

add_filter('securityform_filelds', 'telegram_securityform_filelds', 12);
function telegram_securityform_filelds($items){
	$ui = wp_get_current_user();
	$n_items = array();
	$n_items['alogs_telegram'] = array(
		'name' => 'alogs_telegram',
		'title' => __('Notification upon authentication','pn') .' ('. __('Telegram','pn') .')',
		'req' => 0,
		'value' => is_isset($ui,'alogs_telegram'),
		'type' => 'select',
		'options' => array(__('No','pn'), __('Yes','pn')),
	);
	$items = pn_array_insert($items, 'alogs_email', $n_items, 'before');
	return $items;
}

add_filter('data_securityform', 'telegram_data_securityform');
function telegram_data_securityform($new_user_data){
	
	$new_user_data['alogs_telegram'] = intval(is_param_post('alogs_telegram'));
	if(isset($_POST['telegram_login'])){
		$new_user_data['telegram_login'] = intval(is_param_post('telegram_login'));
	}
	return $new_user_data;
}