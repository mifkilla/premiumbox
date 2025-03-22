<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('register_placed_form')){
	add_filter('placed_form', 'register_placed_form');
	function register_placed_form($placed){
		$placed['registerform'] = __('Registration form','pn');	
		return $placed;
	}
}

if(!function_exists('adminpage_quicktags_page_register')){
	add_action('pn_adminpage_quicktags_page','adminpage_quicktags_page_register');
	function adminpage_quicktags_page_register(){
	?>
	edButtons[edButtons.length] = 
	new edButton('premium_register', '<?php _e('Sign up','pn'); ?>','[register_form]');
	<?php	
	} 
}

if(!function_exists('list_user_notify_registerform')){
	add_filter('list_user_notify','list_user_notify_registerform');
	function list_user_notify_registerform($places_admin){
		$places_admin['registerform'] = __('Registration form','pn');
		return $places_admin;
	}
}

if(!function_exists('def_list_notify_tags_registerform')){
	add_filter('list_notify_tags_registerform','def_list_notify_tags_registerform');
	function def_list_notify_tags_registerform($tags){
		$tags['login'] = array(
			'title' => __('Login','pn'),
			'start' => '[login]'
		);
		$tags['pass'] = array(
			'title' => __('Password','pn'),
			'start' => '[pass]'
		);
		$tags['email'] = array(
			'title' => __('E-mail','pn'),
			'start' => '[email]'
		);	
		return $tags;
	}
}

if(!function_exists('def_registerform_filelds')){
	add_filter('registerform_filelds', 'def_registerform_filelds');
	function def_registerform_filelds($items){
		$ui = wp_get_current_user();
		$items['login'] = array(
			'name' => 'login',
			'title' => __('Login', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'input',
		);
		$items['email'] = array(
			'name' => 'email',
			'title' => __('E-mail', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'input',
		);	
		$items['pass'] = array(
			'name' => 'pass',
			'title' => __('Password', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'password',
		);
		$items['pass2'] = array(
			'name' => 'pass2',
			'title' => __('Password again', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'password',
		);	
		return $items;
	}
}

if(!function_exists('def_replace_array_registerform')){
	add_filter('replace_array_registerform', 'def_replace_array_registerform', 10, 3);
	function def_replace_array_registerform($array, $prefix, $place=''){
		$plugin = get_plugin_class();
		
		$fields = get_form_fields('registerform', $place);
		
		$filter_name = '';
		if($place == 'widget'){
			$prefix = 'widget_'. $prefix;
			$filter_name = 'widget_';
		}
		$html = prepare_form_fileds($fields, $filter_name . 'register_form_line', $prefix);	
		
		$return_url = pn_strip_input(is_param_get('return_url'));
		
		$array = array(
			'[form]' => '<form method="post" class="ajax_post_form" action="'. get_pn_action('registerform') .'">',
			'[/form]' => '</form>',
			'[result]' => '<div class="resultgo"></div>',
			'[html]' => $html,
			'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="'. $prefix .'_submit" value="'. __('Sign up', 'pn') .'" />',
			'[toslink]' => $plugin->get_page('tos'),
			'[loginlink]' => $plugin->get_page('login'),
			'[lostpasslink]' => $plugin->get_page('lostpass'),
			'[return_link]' => '<input type="hidden" name="return_url" value="'. $return_url .'" />',
			'[agreement]' => '<label><input type="checkbox" name="check_rule" value="1" /> '. sprintf(__('I read and agree with <a href="%s" target="_blank" rel="noreferrer noopener">the terms and conditions</a>','pn'), $plugin->get_page('tos') ) .'</label>',
		);	
		
		return $array;
	}
}

if(!function_exists('get_register_formed')){
	function get_register_formed(){
		$temp = '';
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if(!$user_id){	
			$array = get_form_replace_array('registerform', 'reg');
		
			$temp_form = '
			<div class="reg_div_wrap">
			[form]
				[return_link]
				
				<div class="reg_div_title">
					<div class="reg_div_title_ins">
						'. __('Sign up','pn') .'
					</div>
				</div>
			
				<div class="reg_div">
					<div class="reg_div_ins">
						
						[html]
						
						<div class="reg_line">
							[agreement]
						</div>
						
						<div class="reg_line">
							<div class="reg_line_subm_left">
								[submit]
							</div>
							<div class="reg_line_subm_right">
								<a href="[loginlink]">'. __('Authorization','pn') .'</a>
							</div>
							
							<div class="clear"></div>
						</div>

						[result]
	 
					</div>
				</div>

			[/form]
			</div>
			';
		
			$temp_form = apply_filters('register_form_temp',$temp_form);
			$temp .= get_replace_arrays($array, $temp_form);		

		} else {
			$temp .= '<div class="resultfalse">'. __('Error! This form is available for unauthorized users only','pn') .'</div>';
		}
		
		return $temp;
	}
}

if(!function_exists('register_form_shortcode')){
	function register_form_shortcode($atts, $content) {
		$temp = get_register_formed();	
		return $temp;
	}
	add_shortcode('register_form', 'register_form_shortcode');
}

if(!function_exists('register_page_shortcode')){
	function register_page_shortcode($atts, $content) {
		$temp = apply_filters('before_register_page','');
		$temp .= get_register_formed();
		$temp .= apply_filters('after_register_page','');	
		return $temp;
	}
	add_shortcode('register_page', 'register_page_shortcode');
}

if(!function_exists('def_premium_siteaction_registerform')){
	add_action('premium_siteaction_registerform', 'def_premium_siteaction_registerform');
	function def_premium_siteaction_registerform(){
	global $or_site_url, $wpdb, $pn_regiter_site;	
		
		only_post();
		
		header('Content-Type: application/json; charset=utf-8'); 
		
		nocache_headers();
		
		$pn_regiter_site = 1;
		
		$plugin = get_plugin_class();
		
		global $myerrors;
		$myerrors = new WP_Error();	
		$secure_cookie = is_ssl();
		
		$log = array();		
		$log['response'] = '';
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		
		$plugin->up_mode('post');
		
		$log = apply_filters('before_ajax_form_field', $log, 'registerform');
		$log = apply_filters('before_ajax_registerform', $log);
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if($user_id){
			$log['status'] = 'error'; 
			$log['status_code'] = 1;
			$log['status_text']= __('Error! This form is available for unauthorized users only','pn');
			echo json_encode($log);
			exit;		
		}
		
		$parallel_error_output = get_parallel_error_output();
			
		$user_login = is_user(is_param_post('login'));
		$email = is_email(is_param_post('email'));
		$pass = is_password(is_param_post('pass'));
		$pass2 = is_password(is_param_post('pass2'));
		$check_rule = intval(is_param_post('check_rule'));
		
		$field_errors = array();
		
		if(!$check_rule){
			$field_errors[] = __('Error! You did not agree with our terms and conditions','pn');
		}
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			if(!$user_login){
				$field_errors[] = __('Error! You have entered an incorrect username. The username must consist of digits or latin letters and contain from 3 up to 30 characters.','pn');
			}	
		}	
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			if(!$email){
				$field_errors[] = __('Error! You have entered an incorrect e-mail','pn');
			}	
		}	
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			if(!$pass or $pass != $pass2){
				$field_errors[] = __('Error! Password is incorrect or does not match with the previously entered password','pn');
			}	
		}
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			if($user_login and username_exists($user_login)){
				$field_errors[] = __('Error! This login is already in use','pn');
			}	
		}
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			if($email and email_exists($email)){
				$field_errors[] = __('Error! This e-mail is already in use','pn');
			}	
		}
		
		if(count($field_errors) == 0){
			
			$user_id = wp_insert_user( array ('user_login' => $user_login, 'user_email' => $email, 'user_pass' => $pass) ) ;
			if($user_id){
									
				do_action('pn_user_register', $user_id);
									
				$notify_tags = array();
				$notify_tags['[sitename]'] = pn_site_name();
				$notify_tags['[login]'] = $user_login;
				$notify_tags['[pass]'] = $pass;
				$notify_tags['[email]'] = $email;
				$notify_tags = apply_filters('notify_tags_registerform', $notify_tags, $user_id);		

				$user_send_data = array(
					'user_email' => $email,
				);	
				$user_send_data = apply_filters('user_send_data', $user_send_data, 'registerform');
				$result_mail = apply_filters('premium_send_message', 0, 'registerform', $notify_tags, $user_send_data); 																
									
				$creds = array();
				$creds['user_login'] = $user_login;
				$creds['user_password'] = $pass;
				$creds['remember'] = true;
				$user = wp_signon($creds, $secure_cookie);	
		
				$return_url = pn_strip_input(is_param_post('return_url'));
				if(!$return_url){
					$return_url = apply_filters('login_auth_redirect', $plugin->get_page('account'));
				}			
		
				if ( $user && !is_wp_error($user) ) {
					$log['status'] = 'success';
					$log['url'] = get_safe_url($return_url); 
					$log['clear'] = 1;
					$log['status_text'] = apply_filters('register_success_message', __('You have successfully registered','pn'));
				} else {
					$log['status'] = 'success';
					$log['clear'] = 1;
					$log['status_text'] = apply_filters('register2_success_message', __('You have successfully registered. You can now log into your account','pn'));
				}								
									
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! Contact with website admin','pn');							
			}		
			
		} else {
			
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = join("<br />", $field_errors);
			
		}				
		
		echo json_encode($log);
		exit;
	}
}

if(!function_exists('premium_js_registerform')){
	add_action('premium_js','premium_js_registerform');
	function premium_js_registerform(){	
		$js_login = apply_filters('premium_js_login', 1);
		if($js_login == 1){
	?>	
	jQuery(function($){ 
		$(document).on('click', '.js_window_join', function(){
			$(document).JsWindow('show', {
				window_class: 'update_window',
				title: '<?php _e('Sign up','pn'); ?>',
				content: $('.registerform_box_html').html(),
				insert_div: '.registerform_box',
				shadow: 1
			});		
			
			var new_url = window.location.href;
			$('input[name=return_url]').val(new_url);
			
			return false;
		});	
	});	
	<?php	
		}
	}
}

if(!function_exists('wp_footer_registerform')){
	add_action('wp_footer','wp_footer_registerform');
	function wp_footer_registerform(){
		$js_login = apply_filters('premium_js_login', 1);
		if($js_login == 1){
			$array = get_form_replace_array('registerform', 'rb');
			
			$temp = '
			<div class="registerform_box_html" style="display: none;">		
				[html]	
				<div class="rb_line">[agreement]</div>
				<div class="rb_line">[submit]</div>
				[result]
			</div>';	
			
			$temp .= '
			[form]
				[return_link]
				<div class="registerform_box"></div>
			[/form]
			';
			
			echo get_replace_arrays($array, $temp);	
		} 
	}
}