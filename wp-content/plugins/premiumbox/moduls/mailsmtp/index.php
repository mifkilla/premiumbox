<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]SMTP[:en_US][ru_RU:]SMTP[:ru_RU]
description: [en_US:]Sending e-mail via SMTP[:en_US][ru_RU:]Отправление электронной почты с помощью SMTP[:ru_RU]
version: 2.2
category: [en_US:]E-mail[:en_US][ru_RU:]E-mail[:ru_RU]
cat: email
*/

if(!function_exists('admin_menu_mailsmtp')){
	add_action('admin_menu', 'admin_menu_mailsmtp', 17);
	function admin_menu_mailsmtp(){
		$plugin = get_plugin_class();
		add_submenu_page("all_mail_temps", __('SMTP settings','pn'), __('SMTP settings','pn'), 'administrator', "all_mailsmtp", array($plugin, 'admin_temp'));
	}
}

if(!function_exists('def_adminpage_title_all_mailsmtp')){
	add_action('pn_adminpage_title_all_mailsmtp', 'def_adminpage_title_all_mailsmtp');
	function def_adminpage_title_all_mailsmtp($page){
		_e('SMTP settings','pn');
	} 
}

if(!function_exists('def_all_mailsmtp_option')){
	add_filter('all_mailsmtp_option', 'def_all_mailsmtp_option', 1);
	function def_all_mailsmtp_option($options){
		$plugin = get_plugin_class();
	
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('SMTP settings','pn'),
			'submit' => __('Save','pn'),
		);
		$options['enable'] = array(
			'view' => 'select',
			'title' => __('Enable SMTP','pn'),
			'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
			'default' => $plugin->get_option('smtp','enable'),
			'name' => 'enable',
			'work' => 'int',
		);	
		$options['secure'] = array(
			'view' => 'select',
			'title' => __('SMTP connection type','pn'),
			'options' => array('0'=>__('SSL','pn'),'1'=> __('TLS','pn'), '2' => __('No','pn')),
			'default' => $plugin->get_option('smtp','secure'),
			'name' => 'secure',
			'work' => 'int',
		);	
		$options['host'] = array(
			'view' => 'inputbig',
			'title' => __('SMTP Host','pn'),
			'default' => $plugin->get_option('smtp','host'),
			'name' => 'host',
			'work' => 'input',
		);
		$options['port'] = array(
			'view' => 'inputbig',
			'title' => __('SMTP Port','pn'),
			'default' => $plugin->get_option('smtp','port'),
			'name' => 'port',
			'work' => 'input',
		);
		$options['username'] = array(
			'view' => 'inputbig',
			'title' => __('SMTP Username','pn'),
			'default' => $plugin->get_option('smtp','username'),
			'name' => 'username',
			'work' => 'input',
		);
		$options['password'] = array(
			'view' => 'inputbig',
			'title' => __('SMTP Password','pn'),
			'default' => $plugin->get_option('smtp','password'),
			'name' => 'password',
			'work' => 'input',
		);
		$options['from'] = array(
			'view' => 'inputbig',
			'title' => __('SMTP Under name','pn'),
			'default' => $plugin->get_option('smtp','from'),
			'name' => 'from',
			'work' => 'input',
		);
		$options['debug'] = array(
			'view' => 'select',
			'title' => __('Debug mode','pn'),
			'options' => array('0'=>__('No','pn'),'1'=> __('Yes','pn')),
			'default' => $plugin->get_option('smtp','debug'),
			'name' => 'debug',
			'work' => 'int',
		);		
				
		$help = '
		<p>
			<strong>'. __('SMTP Host','pn').'</strong>: smtp.yandex.ru<br />
			<strong>'. __('SMTP Port','pn').'</strong>: 465
		</p>
		';
		$options['yahelp'] = array(
			'view' => 'help',
			'title' => __('Info for yandex','pn'),
			'default' => $help,
		);		
			
		return $options;
	}
}

if(!function_exists('def_adminpage_content_all_mailsmtp')){
	add_action('pn_adminpage_content_all_mailsmtp','def_adminpage_content_all_mailsmtp');
	function def_adminpage_content_all_mailsmtp(){
	
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_mailsmtp_option',
			'method' => 'ajax',
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form);			
	}  
}

if(!function_exists('def_premium_action_all_mailsmtp')){
	add_action('premium_action_all_mailsmtp','def_premium_action_all_mailsmtp');
	function def_premium_action_all_mailsmtp(){
		$plugin = get_plugin_class();	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$data = $form->strip_options('all_mailsmtp_option', 'post');
		foreach($data as $key => $val){
			$plugin->update_option('smtp', $key, $val);
		}				

		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
				
		$form->answer_form($back_url);	
	} 
}

if(!function_exists('pn_send_smtp_email')){
	add_action('phpmailer_init','pn_send_smtp_email');
	function pn_send_smtp_email( $phpmailer ) {
		$plugin = get_plugin_class();
		if($plugin->get_option('smtp','enable') == 1){
			$phpmailer->isSMTP();
			$phpmailer->Host = $plugin->get_option('smtp','host');
			$username = trim($plugin->get_option('smtp','username'));
			$password = trim($plugin->get_option('smtp','password'));
			if($username and $password){
				$phpmailer->SMTPAuth = true;
				$phpmailer->Username = $username;
				$phpmailer->Password = $password;
			}
			$phpmailer->Port = $plugin->get_option('smtp','port');
			$phpmailer->From = $username; 
			$phpmailer->FromName = $plugin->get_option('smtp','from');
			$secure_types = array('0'=> 'ssl','1'=> 'tls','2'=> '');
			$secure = intval($plugin->get_option('smtp','secure'));
			if(isset($secure_types[$secure])){
				$phpmailer->SMTPSecure = $secure_types[$secure];
			}
			$debug = intval($plugin->get_option('smtp','debug'));
			if($debug == 1){
				$rd = premium_rewrite_data();
				$sb = is_isset($rd, 'super_base');
				$pn_action = is_param_get('pn_action');
				if($pn_action == 'all_email_send_test' and in_array($sb, array('premium_post.html','premium_post.php'))){
					$phpmailer->SMTPDebug = 2;
					$phpmailer->Debugoutput = function($str, $level){ 
						$form = new PremiumForm();
						$form->error_form("debug level $level; message: $str");
					};
				}
			}
		}
	} 
}