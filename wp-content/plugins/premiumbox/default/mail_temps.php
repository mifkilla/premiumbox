<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('def_pn_adminpage_title_all_mail_temps') and is_admin()){
	add_action('pn_adminpage_title_all_mail_temps', 'def_pn_adminpage_title_all_mail_temps');
	function def_pn_adminpage_title_all_mail_temps(){
		_e('E-mail templates','pn');
	}
}

if(!function_exists('def_pn_adminpage_content_all_mail_temps') and is_admin()){
	add_action('pn_adminpage_content_all_mail_temps','def_pn_adminpage_content_all_mail_temps');
	function def_pn_adminpage_content_all_mail_temps(){
			
		$place = pn_strip_input(is_param_get('place'));	
			
		$form = new PremiumForm();	
			
		$selects = array();
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_mail_temps"),
			'title' => '--' . __('Make a choice','pn') . '--',
			'default' => '',
		);			
	 
		$places_admin = apply_filters('list_admin_notify',array(), 'email');
		if(!is_array($places_admin)){ $places_admin = array(); }
				
		if(count($places_admin) > 0){
			$selects[] = array(
				'link' => admin_url("admin.php?page=all_mail_temps&place=admin_notify"),
				'title' => '---' . __('Admin notification','pn'),
				'opt_data' => 'style="background: #faf9c4"',
				'default' => 'admin_notify',
			);				
		}
				
		foreach($places_admin as $key => $val){	
			$selects[] = array(
				'link' => admin_url("admin.php?page=all_mail_temps&place=".$key),
				'title' => $val,
				'default' => $key,
			);				
		}		
				
		$places_user = apply_filters('list_user_notify',array(), 'email');
		if(!is_array($places_user)){ $places_user = array(); }
				
		if(count($places_user) > 0){
			$selects[] = array(
				'link' => admin_url("admin.php?page=all_mail_temps&place=user_notify"),
				'title' => '---' . __('Users notification','pn'),
				'opt_data' => 'style="background: #faf9c4"',
				'default' => 'user_notify',
			);					
		}			
				
		foreach($places_user as $key => $val){	
			$selects[] = array(
				'link' => admin_url("admin.php?page=all_mail_temps&place=".$key),
				'title' => $val,
				'default' => $key,
			);				
		}
				
		$form->select_box($place, $selects, __('Setting up','pn'));

		if(isset($places_admin[$place]) or isset($places_user[$place])){
			
			$pn_notify = get_option('pn_notify_email');
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
			$options['title'] = array(
				'view' => 'inputbig',
				'title' => __('Subject of e-mail','pn'),
				'default' => is_isset($data, 'title'),
				'name' => 'title',
				'work' => 'input',
				'ml' => 1,
			);
			$options['mail'] = array(
				'view' => 'inputbig',
				'title' => __('Sender e-mail','pn'),
				'default' => is_isset($data, 'mail'),
				'name' => 'mail',
				'work' => 'input',
			);	
			$options['mail_warning'] = array(
				'view' => 'warning',
				'default' => __('Use only existing e-mail address (for example - info@site.ru)','pn'),
			);			
			$options['name'] = array(
				'view' => 'inputbig',
				'title' => __('Sender name','pn'),
				'default' => is_isset($data, 'name'),
				'name' => 'name',
				'work' => 'input',
			);	

			if(isset($places_admin[$place])){
				$options['tomail'] = array(
					'view' => 'inputbig',
					'title' => __('Administrator e-mail','pn'),
					'default' => is_isset($data, 'tomail'),
					'name' => 'tomail',
					'work' => 'input',
				);					
				$options['tomailhelp'] = array(
					'view' => 'help',
					'title' => __('More info','pn'),
					'default' => __('If the recipient has several e-mail address, e-mail address should be comma-separated','pn'),
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
				'rows' => '20',
				'name' => 'text',
				'work' => 'text',
				'ml' => 1,
			);				
				
			$params_form = array(
				'filter' => 'all_mail_temps_option',
				'method' => 'ajax',
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options); 		
			
		} else {

			$options = array();
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => __('Send test e-mail','pn'),
				'submit' => __('Send a message','pn'),
			);
			$options['to'] = array(
				'view' => 'inputbig',
				'title' => __('Your e-mail','pn'),
				'default' => '',
				'name' => 'to',
				'work' => 'email',
			);		
			
			$params_form = array(
				'filter' => '',
				'method' => 'ajax',
				'data' => '',
				'form_link' => pn_link('all_email_send_test'),
				'button_title' => __('Send a message','pn'),
			);
			$form->init_form($params_form, $options);				
		
		}
	}
}

if(!function_exists('def_premium_action_all_email_send_test') and is_admin()){
	add_action('premium_action_all_email_send_test','def_premium_action_all_email_send_test');
	function def_premium_action_all_email_send_test(){	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_change_notify'));
		
			$to = is_email(is_param_post('to'));
			if(!$to){
				$form->error_form(__('Error! You have not entered an e-mail!','pn'));
			} else {
				$result = apply_filters('pn_email_send', 0, $to, 'Test MAIL send', 'Test MAIL send content');		
			}

		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
		
		$form->answer_form($back_url);
		
	}
}

if(!function_exists('def_premium_action_all_mail_temps') and is_admin()){
	add_action('premium_action_all_mail_temps','def_premium_action_all_mail_temps');
	function def_premium_action_all_mail_temps(){
	global $wpdb;

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_change_notify'));
		
		$block = pn_strip_input(is_param_post('block'));
		
		if($block){
			$pn_notify = get_option('pn_notify_email');
			if(!is_array($pn_notify)){ $pn_notify = array(); }

			$pn_notify[$block]['send'] = intval(is_param_post('send'));
			$pn_notify[$block]['title'] = pn_strip_input(is_param_post_ml('title'));
			$pn_notify[$block]['mail'] = trim(is_param_post('mail'));
			$pn_notify[$block]['tomail'] = trim(is_param_post('tomail'));
			$pn_notify[$block]['name'] = pn_strip_input(is_param_post('name'));
			$pn_notify[$block]['text'] = pn_strip_text(is_param_post_ml('text'));

			update_option('pn_notify_email', $pn_notify);
		}			

		do_action('all_mail_temps_option_post');

		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
				
		$form->answer_form($back_url); 
	}
}	

if(!function_exists('email_premium_send_message')){
	add_filter('premium_send_message', 'email_premium_send_message', 10, 5);
	function email_premium_send_message($result, $method, $notify_tags='', $user_send_data='', $lang=''){
		if(!is_array($notify_tags)){ $notify_tags = array(); }
		if(!$lang){ $lang = get_locale(); }
		
		$pn_notify = get_option('pn_notify_email');
			
		if(isset($pn_notify[$method])){
			$data = $pn_notify[$method];
			if($data['send'] == 1){
				$ot_mail = is_email($data['mail']);
				$ot_mail = get_replace_arrays($notify_tags, $ot_mail, 1);
				$ot_name = pn_strip_input($data['name']);
				$ot_name = get_replace_arrays($notify_tags, $ot_name, 1);
				$subject = pn_strip_input(ctv_ml($data['title'], $lang));
				$subject = get_replace_arrays($notify_tags, $subject);
				$html = pn_strip_text(ctv_ml($data['text'], $lang));
				$html = get_replace_arrays($notify_tags, $html, 1);
				$html = str_replace('[subject]', $subject, $html);
				$html = apply_filters('comment_text',$html);
				$to_mail = is_email(is_isset($user_send_data, 'user_email'));
				if(!$to_mail){ $to_mail = is_isset($data,'tomail'); }
					
				$nresult = apply_filters('pn_email_send', 0, $to_mail, $subject, $html, $ot_name, $ot_mail);
				if($nresult == 1){ return 1; }
			}
		}		
		
		return $result;
	}
}