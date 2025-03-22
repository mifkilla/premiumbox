<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('lostpass_placed_form')){
	add_filter('placed_form', 'lostpass_placed_form');
	function lostpass_placed_form($placed){
		$placed['lostpass1form'] = __('Lost password form','pn');
		return $placed;
	}
}

if(!function_exists('list_user_notify_lostpass')){
	add_filter('list_user_notify','list_user_notify_lostpass');
	function list_user_notify_lostpass($places_admin){
		$places_admin['lostpassform'] = __('Lost password form','pn');
		return $places_admin;
	}
}

if(!function_exists('def_list_notify_tags_lostpassform')){
	add_filter('list_notify_tags_lostpassform','def_list_notify_tags_lostpassform');
	function def_list_notify_tags_lostpassform($tags){	
		$tags['link'] = array(
			'title' => __('Link','pn'),
			'start' => '[link]',
		);
		return $tags;
	}
}

if(!function_exists('def_lostpass2form_filelds')){
	add_filter('lostpass2form_filelds', 'def_lostpass2form_filelds');
	function def_lostpass2form_filelds($items){
		$ui = wp_get_current_user();
		$items['pass'] = array(
			'name' => 'pass',
			'title' => __('New password', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'password',
		);
		$items['pass2'] = array(
			'name' => 'pass2',
			'title' => __('New password again', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'password',
		);	
		return $items;
	}
}

if(!function_exists('def_lostpass1form_filelds')){
	add_filter('lostpass1form_filelds', 'def_lostpass1form_filelds');
	function def_lostpass1form_filelds($items){
		$ui = wp_get_current_user();
		$items['email'] = array(
			'name' => 'email',
			'title' => __('E-mail', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'input',
		);	
		return $items;
	}
}

if(!function_exists('def_replace_array_lostpass2form')){
	add_filter('replace_array_lostpass2form', 'def_replace_array_lostpass2form', 10, 3);
	function def_replace_array_lostpass2form($array, $prefix, $place=''){
		$fields = get_form_fields('lostpass2form', $place); 
		
		$filter_name = '';
		if($place == 'widget'){
			$prefix = 'widget_'. $prefix;
			$filter_name = 'widget_';
		}
		$html = prepare_form_fileds($fields, $filter_name . 'lostpass2_form_line', $prefix);	
		
		$maction = pn_strip_input(is_param_get('maction'));
		$mkey = pn_strip_input(is_param_get('mkey'));
		$mid = pn_strip_input(is_param_get('mid'));
		
		$array = array(
			'[form]' => '
			<form method="post" class="ajax_post_form" action="'. get_pn_action('lostpass2') .'">
				<input type="hidden" name="action" value="'. $maction .'" />
				<input type="hidden" name="key" value="'. $mkey .'" />
				<input type="hidden" name="id" value="'. $mid .'" />
			',
			'[/form]' => '</form>',
			'[result]' => '<div class="resultgo"></div>',
			'[html]' => $html,
			'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="'. $prefix .'_submit" value="'. __('Save', 'pn') .'" />',
		);		
		
		return $array;
	}
}

if(!function_exists('def_replace_array_lostpass1form')){
	add_filter('replace_array_lostpass1form', 'def_replace_array_lostpass1form', 10, 3);
	function def_replace_array_lostpass1form($array, $prefix, $place=''){
		$fields = get_form_fields('lostpass1form', $place); 
		
		$filter_name = '';
		if($place == 'widget'){
			$prefix = 'widget_'. $prefix;
			$filter_name = 'widget_';
		}
		$html = prepare_form_fileds($fields, $filter_name . 'lostpass1_form_line', $prefix);	
		
		$array = array(
			'[form]' => '<form method="post" class="ajax_post_form" action="'. get_pn_action('lostpass1') .'">',
			'[/form]' => '</form>',
			'[result]' => '<div class="resultgo"></div>',
			'[html]' => $html,
			'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="'. $prefix .'_submit" value="'. __('Reset password', 'pn') .'" />',
		);		
		
		return $array;
	}
}

if(!function_exists('lostpass_page_shortcode')){
	function lostpass_page_shortcode($atts, $content){
		$temp = '';
		
		$temp .= apply_filters('before_lostpass_page','');
				
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);			
				
		if(!$user_id){

			$maction = pn_strip_input(is_param_get('maction'));
			$mkey = pn_strip_input(is_param_get('mkey'));
			$mid = pn_strip_input(is_param_get('mid'));
		
			if($maction == 'rp' and $mkey and $mid) {	
				
				$array = get_form_replace_array('lostpass2form', 'lp');

				$temp_form = '
				<div class="lp_div_wrap">
				[form]
					
					<div class="lp_div_title">
						<div class="lp_div_title_ins">
							'. __('Password recovery','pn') .'
						</div>
					</div>
				
					<div class="lp_div">
						<div class="lp_div_ins">
							
							[html]
							
							<div class="lp_line has_submit">
								[submit]
							</div>
							
							[result]
							
						</div>
					</div>

				[/form]
				</div>
				';
		
				$temp_form = apply_filters('lostpass2_form_temp',$temp_form);
				$temp .= get_replace_arrays($array, $temp_form);			
				
			} else {	

				$array = get_form_replace_array('lostpass1form', 'lp');
				
				$temp_form = '
				<div class="lp_div_wrap">
				[form]
					
					<div class="lp_div_title">
						<div class="lp_div_title_ins">
							'. __('Password recovery','pn') .'
						</div>
					</div>
				
					<div class="lp_div">
						<div class="lp_div_ins">
							
							[html]
							
							<div class="lp_line has_submit">
								[submit]
							</div>
							
							[result]
							
						</div>
					</div>

				[/form]
				</div>
				';
		
				$temp_form = apply_filters('lostpass1_form_temp',$temp_form);
				$temp .= get_replace_arrays($array, $temp_form);			
				
			}		

		} else {
			$temp .= '<div class="resultfalse">'. __('Error! This form is available for unauthorized users only','pn') .'</div>';
		}	
		
		$after = apply_filters('after_lostpass_page','');
		$temp .= $after;	
		
		return $temp;
	}
	add_shortcode('lostpass_page', 'lostpass_page_shortcode');
}

if(!function_exists('def_premium_siteaction_lostpass1')){
	add_action('premium_siteaction_lostpass1', 'def_premium_siteaction_lostpass1');
	function def_premium_siteaction_lostpass1(){
	global $or_site_url, $wpdb;	
		
		$plugin = get_plugin_class();
		
		only_post();
		
		header('Content-Type: application/json; charset=utf-8'); 

		$log = array();	
		$log['response'] = '';
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		
		$plugin->up_mode('post');
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		$log = apply_filters('before_ajax_form_field', $log, 'lostpass1form');
		$log = apply_filters('before_ajax_lostpass1', $log);
		
		if($user_id){
			$log['status'] = 'error'; 
			$log['status_code'] = 1;
			$log['status_text']= __('Error! This form is available for unauthorized users only','pn');
			echo json_encode($log);
			exit;		
		}
		
		$email = is_email(is_param_post('email'));
		if($email){
			$user_id = email_exists($email);
			if ($user_id){
				$ui = get_userdata($user_id);
				$sec_lostpass = intval(is_isset($ui,'sec_lostpass'));
				if($sec_lostpass == 1){
					
					$admin_password = wp_generate_password( 20 , false, false);
					$ad_hash = pn_crypt_data($admin_password);
					
					$wpdb->query("UPDATE ".$wpdb->prefix."users SET user_activation_key = '$ad_hash' WHERE user_email = '$email'");
					
					$notify_tags = array();
					$notify_tags['[sitename]'] = pn_site_name();
					$link = $plugin->get_page('lostpass'). '?maction=rp&mid='. $user_id .'&mkey='. $admin_password;
					$link = apply_filters('lostpass_remind_link', $link, $user_id, $admin_password);
					$notify_tags['[link]'] = $link;
					$notify_tags = apply_filters('notify_tags_lostpassform', $notify_tags, $ui);		

					$user_send_data = array(
						'user_email' => $email,
					);	
					$user_send_data = apply_filters('user_send_data', $user_send_data, 'lostpassform', $ui);
					$result_mail = apply_filters('premium_send_message', 0, 'lostpassform', $notify_tags, $user_send_data); 				

					$log['status'] = 'success';
					$log['clear'] = 1;
					$log['status_text'] = apply_filters('lostpass1_success_message', __('Confirmation e-mail is sent you','pn'));					
		   
				} else {
					$log['status'] = 'error';
					$log['status_code'] = 1;
					$log['status_text'] = __('Error! Password recovery is disabled','pn');
				}	   
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! This e-mail is not registered','pn');
			}
		}  else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! You have entered an incorrect e-mail','pn');
		}	
			
		echo json_encode($log);	
		exit;
	}
}

if(!function_exists('def_premium_siteaction_lostpass2')){
	add_action('premium_siteaction_lostpass2', 'def_premium_siteaction_lostpass2');
	function def_premium_siteaction_lostpass2(){
	global $or_site_url, $wpdb;	
		
		$plugin = get_plugin_class();
		
		only_post();
		
		header('Content-Type: application/json; charset=utf-8'); 

		$log = array();	
		$log['response'] = '';
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		
		$plugin->up_mode('post');
		
		$log = apply_filters('before_ajax_form_field', $log, 'lostpass2form');
		$log = apply_filters('before_ajax_lostpass2', $log);
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if($user_id){
			$log['status'] = 'error'; 
			$log['status_code'] = 1;
			$log['status_text']= __('Error! This form is available for unauthorized users only','pn');
			echo json_encode($log);
			exit;		
		}
		
		$action = pn_strip_input(is_param_post('action'));
		$key = pn_strip_input(is_param_post('key'));
		$user_id = intval(is_param_post('id'));
		$pass = is_password(is_param_post('pass'));
		$pass2 = is_password(is_param_post('pass2'));
		
		if(preg_match("/^[a-zA-z0-9]{0,150}$/", $key) and $action == 'rp' and $user_id > 0){
			if($pass and $pass == $pass2){
				$password = wp_hash_password($pass);
				$ui = get_userdata($user_id);
				if(isset($ui->sec_lostpass) and $ui->sec_lostpass == 1){				
					$user = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."users WHERE ID='$user_id'");
					if(isset($user->ID)){
						if(is_pn_crypt($user->user_activation_key, $key)){
							$wpdb->query("UPDATE ".$wpdb->prefix."users SET user_pass = '$password', user_activation_key = '' WHERE ID = '$user_id'");
					
							$log['url'] = $link = get_safe_url(apply_filters('lostpass_login_redirect', $plugin->get_page('login')));
							$log['status'] = 'success';
							$log['clear'] = 1;
							$log['status_text'] = apply_filters('lostpass2_success_message', __('Password successfully changed','pn'));					

						} else {
							$log['status'] = 'error';
							$log['status_code'] = 1;
							$log['status_text'] = __('Error! System error 2','pn');						
						}
					} else {
						$log['status'] = 'error';
						$log['status_code'] = 1;
						$log['status_text'] = __('Error! Password is incorrect or does not match with the previously entered password','pn');					
					}
				} else {
					$log['status'] = 'error';
					$log['status_code'] = 1;
					$log['status_text'] = __('Error! Password recovery is disabled','pn');				
				}
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! Password is incorrect or does not match with the previously entered password','pn');
			}
		}  else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! System error 1','pn');
		}	
			
		echo json_encode($log);	
		exit;
	}
}