<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('def_securityform_filelds')){
	add_filter('securityform_filelds', 'def_securityform_filelds');
	function def_securityform_filelds($items){
		$ui = wp_get_current_user();

		$items['pass'] = array(
			'name' => 'pass',
			'title' => __('New password', 'pn'),
			'req' => 0,
			'value' => '',
			'type' => 'password',
		);
		$items['pass2'] = array(
			'name' => 'pass2',
			'title' => __('New password again', 'pn'),
			'req' => 0,
			'value' => '',
			'type' => 'password',
		);	
		$items['sec_lostpass'] = array(
			'name' => 'sec_lostpass',
			'title' => __('Recover password', 'pn'),
			'req' => 0,
			'value' => is_isset($ui,'sec_lostpass'),
			'type' => 'select',
			'options' => array(__('No','pn'), __('Yes','pn')),
		);
		$items['alogs_email'] = array(
			'name' => 'alogs_email',
			'title' => __('Notification upon authentication','pn') .' ('. __('E-mail','pn') .')',
			'req' => 0,
			'value' => is_isset($ui,'alogs_email'),
			'type' => 'select',
			'options' => array(__('No','pn'), __('Yes','pn')),
		);
		$items['enable_ips'] = array(
			'name' => 'enable_ips',
			'title' => __('Allowed IP address (in new line)', 'pn'),
			'placeholder' => '',
			'req' => 0,
			'value' => is_isset($ui,'enable_ips'),
			'type' => 'text',
		);	
		
		return $items;
	}
}

if(!function_exists('def_replace_array_securityform')){
	add_filter('replace_array_securityform', 'def_replace_array_securityform', 10, 3);
	function def_replace_array_securityform($array, $prefix, $place=''){
		$fields = get_form_fields('securityform', $place);
		
		$filter_name = '';
		if($place == 'widget'){
			$prefix = 'widget_'. $prefix;
			$filter_name = 'widget_';
		}
		$html = prepare_form_fileds($fields, $filter_name . 'account_form_line', $prefix);	
		
		$array = array(
			'[form]' => '<form method="post" class="ajax_post_form" action="'. get_pn_action('securityform') .'">',
			'[/form]' => '</form>',
			'[result]' => '<div class="resultgo"></div>',
			'[html]' => $html,
			'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="'. $prefix .'_submit" value="'. __('Save', 'pn') .'" />',
		);	
		
		return $array;
	}
}

if(!function_exists('security_page_shortcode')){
	function security_page_shortcode($atts, $content) {
		$temp = '';
		$temp .= apply_filters('before_security_page','');
				
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);		
				
		if($user_id){
				
			$array = get_form_replace_array('securityform', 'acf');	
		
			$temp_form = '
			<div class="acf_div_wrap">
			[form]
				
				<div class="acf_div_title">
					<div class="acf_div_title_ins">
						'. __('Security settings','pn') .'
					</div>
				</div>
			
				<div class="acf_div">
					<div class="acf_div_ins">
						
						[html]
						
						<div class="acf_line has_submit">
							[submit]
						</div>
						
						[result]
					</div>
				</div>

			[/form]
			</div>
			';
		
			$temp_form = apply_filters('account_form_temp',$temp_form);
			$temp .= get_replace_arrays($array, $temp_form);		

		} else {
			$temp .= '<div class="resultfalse">'. __('Error! You must authorize','pn') .'</div>';
		}
		
		$after = apply_filters('after_security_page','');
		$temp .= $after;	
		
		return $temp;
	}
	add_shortcode('security_page', 'security_page_shortcode');
}

if(!function_exists('def_premium_siteaction_securityform')){
	add_action('premium_siteaction_securityform', 'def_premium_siteaction_securityform');
	function def_premium_siteaction_securityform(){
	global $wpdb;	
		
		$plugin = get_plugin_class();
		
		header('Content-Type: application/json; charset=utf-8'); 
		
		only_post();
		
		$log = array();
		$log['response'] = ''; 
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		
		$plugin->up_mode('post');
		
		$log = apply_filters('before_ajax_form_field', $log, 'securityform');
		$log = apply_filters('before_ajax_securityform', $log);
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if(!$user_id){
			$log['status'] = 'error'; 
			$log['status_code'] = 1;
			$log['status_text']= __('Error! You must authorize','pn');
			echo json_encode($log);
			exit;		
		}
			
		$array = array();
		$array['sec_lostpass'] = intval(is_param_post('sec_lostpass'));
		if(isset($_POST['email_login'])){
			$array['email_login'] = intval(is_param_post('email_login'));
		}
		$array['alogs_email'] = intval(is_param_post('alogs_email'));
		$array['enable_ips'] = pn_maxf(pn_strip_input(is_param_post('enable_ips')),1500);
		$array = apply_filters('data_securityform', $array);
		$wpdb->update($wpdb->prefix ."users", $array, array('ID'=>$user_id));
		
		$pass = is_password(is_param_post('pass'));
		$pass2 = is_password(is_param_post('pass2'));	
		
		global $change_ld_account;
		$change_ld_account = 1;
		
		if($pass){
			if($pass == $pass2){
				wp_set_password($pass, $user_id);
				/* wp_clear_auth_cookie(); */
				$secure_cookie = is_ssl();
				wp_set_auth_cookie($user_id, true, $secure_cookie);
				wp_set_current_user($user_id);
			} else {
				$log['status'] = 'error'; 
				$log['status_code'] = 1;
				$log['status_text']= __('Passwords do not match','pn');
				echo json_encode($log);
				exit;			
			}
		} 	
			
		$log['status'] = 'success';
		$log['status_text'] = apply_filters('security_success_message', __('Data successfully saved','pn'));				
		
		echo json_encode($log);
		exit;
	}
}