<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('def_accountform_filelds')){
	add_filter('accountform_filelds', 'def_accountform_filelds');
	function def_accountform_filelds($items){
		$ui = wp_get_current_user();
		
		$items['login'] = array(
			'name' => 'login',
			'title' => __('Login', 'pn'),
			'req' => 0,
			'value' => is_user(is_isset($ui,'user_login')),
			'type' => 'input',
			'atts' => array('disabled' => 'disabled'),
		);
		
		$fields = apply_filters('user_fields_in_website', array());
		foreach($fields as $field_key => $field_data){
			
			if(pn_allow_uv($field_key) or $field_key == 'user_email'){
				$items[$field_key] = array(
					'name' => $field_key,
					'title' => is_isset($field_data,'title'),
					'req' => 0,
					'value' => strip_uf(is_isset($ui,$field_key), $field_key),
					'type' => 'input',
				);
				$dis = apply_filters('disabled_account_form_line', 0, $field_key, $ui);
				if($dis == 1){
					$items[$field_key]['atts']['disabled'] = 'disabled';
				}
			}
		
		}	
		
		return $items;
	}
}	

if(!function_exists('def_replace_array_accountform')){
	add_filter('replace_array_accountform', 'def_replace_array_accountform', 10, 3);
	function def_replace_array_accountform($array, $prefix, $place=''){
		global $wpdb;
		
		$fields = get_form_fields('accountform', $place);
		
		$filter_name = '';
		if($place == 'widget'){
			$prefix = 'widget_'. $prefix;
			$filter_name = 'widget_';
		}
		$html = prepare_form_fileds($fields, $filter_name . 'account_form_line', $prefix);	
		
		$array = array(
			'[form]' => '<form method="post" class="ajax_post_form" action="'. get_pn_action('accountform') .'">',
			'[/form]' => '</form>',
			'[result]' => '<div class="resultgo"></div>',
			'[html]' => $html,
			'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="'. $prefix .'_submit" value="'. __('Save', 'pn') .'" />',
		);	
		
		return $array;
	}
}

if(!function_exists('account_page_shortcode')){
	function account_page_shortcode($atts, $content) {
	global $wpdb;

		$temp = '';
		
		$temp .= apply_filters('before_account_page','');
				
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);		
				
		if($user_id){
				
			$array = get_form_replace_array('accountform', 'acf');	
				
			$temp_form = '
			<div class="acf_div_wrap">
			[form]
				
				<div class="acf_div_title">
					<div class="acf_div_title_ins">
						'. __('Personal data','pn') .'
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
		
		$temp .= apply_filters('after_account_page','');	
		
		return $temp;
	}
	add_shortcode('account_page', 'account_page_shortcode');
}

if(!function_exists('def_premium_siteaction_accountform')){
	add_action('premium_siteaction_accountform', 'def_premium_siteaction_accountform');
	function def_premium_siteaction_accountform(){
	global $or_site_url, $wpdb;	
		
		only_post();
		
		header('Content-Type: application/json; charset=utf-8'); 
		
		$log = array();
		$log['response'] = '';
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		
		$plugin = get_plugin_class();
		
		$plugin->up_mode('post');
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if(!$user_id){
			$log['status'] = 'error'; 
			$log['status_code'] = 1;
			$log['status_text']= __('Error! You must authorize','pn');
			echo json_encode($log);
			exit;		
		}
		
		$log = apply_filters('before_ajax_form_field', $log, 'accountform');
		$log = apply_filters('before_ajax_accountform', $log, $ui);
			
		$email = is_email(is_param_post('user_email'));				
		$old_email = is_email($ui->user_email);		
	
		$fields = apply_filters('user_fields_in_website', array());
		foreach($fields as $field_key => $field_data){
			if($field_key != 'user_email'){
				if(pn_allow_uv($field_key)){
					$disabled = apply_filters('disabled_account_form_line', 0, $field_key, $ui);
					if($disabled != 1){		
						$val = strip_uf(is_param_post($field_key), $field_key);
						update_user_meta($user_id, $field_key, $val) or add_user_meta($user_id, $field_key, $val, true);
					}
				}
			}
		}
		
		$errors = array();
		
		$disabled = apply_filters('disabled_account_form_line', 0, 'user_email', $ui);
		if($disabled != 1){
			if($email){	
				if($email != $old_email){
					if (is_email($email)){
						if (!email_exists($email)) {	
							$wpdb->update($wpdb->prefix.'users', array('user_email' => $email), array('ID'=>$user_id));
						} else {
							$errors[] = __('This email is already in use','pn');
						}
					} else {
						$errors[] = __('You have entered an incorrect e-mail','pn');
					}
				}
			}
		}

		do_action('user_account_post', $user_id, $ui);
		
		if(count($errors) > 0){
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = join('<br />',$errors);		
		} else {
			$log['status'] = 'success';
			$log['status_text'] = apply_filters('account_success_message', __('Data successfully saved','pn'));		
		}		
		
		echo json_encode($log);
		exit;
	}
}

if(!function_exists('disabled_account_form_line_standart')){
	add_filter('disabled_account_form_line', 'disabled_account_form_line_standart', 9, 3);
	function disabled_account_form_line_standart($ind, $name, $ui){
		if($ind != 1){
			$value = strip_uf(is_isset($ui,$name), $name);
			if(pn_change_uv($name) == 0 and $value){
				return 1;
			}
		}
			return $ind;
	}
}