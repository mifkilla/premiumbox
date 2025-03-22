<?php
if( !defined( 'ABSPATH')){ exit(); }
 
if(!function_exists('pn_adminpage_quicktags_page_contact')){
	add_action('pn_adminpage_quicktags_page','pn_adminpage_quicktags_page_contact');
	function pn_adminpage_quicktags_page_contact(){
	?>
	edButtons[edButtons.length] = 
	new edButton('premium_contact', '<?php _e('Contact form','pn'); ?>','[contact_form]');
	<?php	
	}
}

if(!function_exists('def_replace_array_contactform')){
	add_filter('replace_array_contactform', 'def_replace_array_contactform', 10, 3);
	function def_replace_array_contactform($array, $prefix, $place=''){
		$fields = get_form_fields('contactform', $place);
		
		$filter_name = '';
		if($place == 'widget'){
			$prefix = 'widget_'. $prefix;
			$filter_name = 'widget_';
		}
		$html = prepare_form_fileds($fields, $filter_name . 'contact_form_line', $prefix);	
		
		$array = array(
			'[form]' => '<form method="post" class="ajax_post_form" action="'. get_pn_action('contactform') .'">',
			'[/form]' => '</form>',
			'[result]' => '<div class="resultgo"></div>',
			'[html]' => $html,
			'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="'. $prefix .'_submit" value="'. __('Send a message', 'pn') .'" />',
		);	
		
		return $array;
	}
}

if(!function_exists('pn_contact_form_shortcode')){
	function pn_contact_form_shortcode($atts){
		$temp = '';
		
		$array = get_form_replace_array('contactform', 'cf');	
		
		$temp = '
		<div class="cf_div_wrap">
		[form]

			<div class="cf_div_title">
				<div class="cf_div_title_ins">
					'. __('Contact form','pn') .'
				</div>
			</div>
		
			<div class="cf_div">
				<div class="cf_div_ins">
					
					[html]
					
					<div class="cf_line has_submit">
						[submit]
					</div>
					
					[result]
					
				</div>
			</div>
		
		[/form]
		</div>
		';
		
		$temp = apply_filters('contact_form_temp',$temp);
		$temp = get_replace_arrays($array, $temp);
		
		return $temp;
	}
	add_shortcode('contact_form', 'pn_contact_form_shortcode');
}

if(!function_exists('def_premium_siteaction_contactform')){
	add_action('premium_siteaction_contactform', 'def_premium_siteaction_contactform');
	function def_premium_siteaction_contactform(){
		global $or_site_url;	
		
		only_post();
		
		header('Content-Type: application/json; charset=utf-8');
		
		$plugin = get_plugin_class();
		
		$log = array();
		$log['response'] = '';
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		$log['errors'] = array();
		
		$plugin->up_mode('post');
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		$log = apply_filters('before_ajax_form_field', $log, 'contactform');
		$log = apply_filters('before_ajax_contactform', $log);
		
		$parallel_error_output = get_parallel_error_output();
		
		$name = pn_maxf_mb(pn_strip_input(is_param_post('name')), 150);
		$email = is_email(is_param_post('email'));
		$text = pn_maxf_mb(pn_strip_input(is_param_post('text')), 2000);
		
		$field_errors = array();
		
		if(mb_strlen($name) < 2){
			$field_errors[] = __('Error! You must enter your name','pn');	
		}
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			if(!$email){
				$field_errors[] = __('Error! You must enter your e-mail','pn');
			}	
		}
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			if(mb_strlen($text) < 3){
				$field_errors[] = __('Error! You must enter a message','pn');
			}
		}	
		
		if(count($field_errors) == 0){
			
			$notify_tags = array();
			$notify_tags['[sitename]'] = pn_site_name();
			$notify_tags['[name]'] = $name;
			$notify_tags['[user]'] = $name;
			$notify_tags['[email]'] = $email;
			$notify_tags['[text]'] = $text;
			$notify_tags['[ip]'] = pn_real_ip();
			$notify_tags['[link]'] = '<a href="mailto:'. $email .'?subject=[subject]">'. __('Reply','pn') .'</a>';
			$notify_tags = apply_filters('notify_tags_contactform', $notify_tags, $ui);		

			$user_send_data = array();
			$result_mail = apply_filters('premium_send_message', 0, 'contactform', $notify_tags, $user_send_data); 
			
			$notify_tags = apply_filters('notify_tags_contactform_auto', $notify_tags, $ui);
			
			$user_send_data = array(
				'user_email' => $email,
			);	
			$user_send_data = apply_filters('user_send_data', $user_send_data, 'contactform_auto', $ui);
			$result_mail = apply_filters('premium_send_message', 0, 'contactform_auto', $notify_tags, $user_send_data);
			
			$log['status'] = 'success';	
			$log['clear'] = 1;
			$log['status_text'] = apply_filters('contact_form_success_message',__('Your message has been successfully sent','pn'));		
			
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = join("<br />", $field_errors);
		}
		
		echo json_encode($log);
		exit;
	}
}