<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('pn_admin_panel_url')){
	function pn_admin_panel_url(){
		global $or_site_url;
		
		$plugin = get_plugin_class();
		$admin_panel_url = is_admin_newurl($plugin->get_option('admin_panel_url'));
		if(strlen($admin_panel_url) < 1){ 
			$admin_panel_url = 'wp-login.php'; 
		} 
		$url = $or_site_url . '/' . $admin_panel_url;
		
		return $url;
	}
}

if(!function_exists('pn_register_url')){
	add_filter('register_url','pn_register_url');
	function pn_register_url($url){
		$plugin = get_plugin_class();	
		return $plugin->get_page('register');
	}
}

if(!function_exists('pn_lostpassword_url')){
	add_filter('lostpassword_url','pn_lostpassword_url');
	function pn_lostpassword_url($url){
		$plugin = get_plugin_class();		
		return $plugin->get_page('lostpass');
	}
}

if(!function_exists('login_form_notfound')){

	global $admin_panel_url, $wpdb;
	
	$plugin = get_plugin_class();
	$admin_panel_url = is_admin_newurl($plugin->get_option('admin_panel_url'));

	add_action('login_form_register','login_form_notfound');
	add_action('login_form_retrievepassword','login_form_notfound');
	add_action('login_form_resetpass','login_form_notfound');
	add_action('login_form_rp','login_form_notfound');
	add_action('login_form_lostpassword','login_form_notfound');
	if(defined('PN_ADMIN_GOWP') and constant('PN_ADMIN_GOWP') != 'true' and $admin_panel_url){
		remove_action('admin_enqueue_scripts','wp_auth_check_load');
		add_action('login_form_login','login_form_notfound');
		
		add_filter('wp_redirect', 'pn_filter_wp_login');
		add_filter('network_site_url', 'pn_filter_wp_login');
		add_filter('site_url', 'pn_filter_wp_login');
		
		add_action('init', 'set_login_page');
		
		add_action('premium_action_pn_admin_login','def_premium_action_pn_admin_login');
	}

	function login_form_notfound(){
		pn_display_mess(__('Page does not exist','pn'));
	}
	
	function pn_filter_wp_login($str){
	global $admin_panel_url, $or_site_url, $wpdb;	
		if(preg_match("/reauth/i", $str)){
			wp_redirect($or_site_url);
			exit;
		} 	
		return str_replace('wp-login.php', $admin_panel_url, $str);
	}	
	
 	function set_login_page(){
		global $admin_panel_url, $wpdb, $or_site_url, $wp_query;
		
		$plugin = get_plugin_class();
		
		$data = premium_rewrite_data();
		$super_base = $data['super_base'];		
		
		if($super_base == $admin_panel_url){			
			
			$ui = wp_get_current_user();
			$user_id = intval($ui->ID);	
			
			if($user_id){
				if(current_user_can('read')){
					$url = admin_url('index.php');
					wp_redirect($url);
					exit;
				} else {
					return;
				}
			}			

			header('Content-Type: text/html; charset=utf-8');
			
			$wp_query->set_404();
			status_header(404);
			
			do_action('premium_login_init');
			
			$premium_url = get_premium_url();
			$plugin_url = $plugin->plugin_url;
			$plugin_version = current_time('timestamp');
			?>
<!DOCTYPE html>
<html <?php echo get_language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<meta name="HandheldFriendly" content="True" />
	<meta name="MobileOptimized" content="320" />
	<meta name="format-detection" content="telephone=no" />
	<meta name="PalmComputingPlatform" content="true" />
	<meta name="apple-touch-fullscreen" content="yes"/>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php _e('Authorization','pn'); ?></title>
	<link rel='stylesheet' href='<?php echo $plugin_url; ?>default/newadminpanel/style.css?ver=<?php echo $plugin->vers(); ?>' type='text/css' media='all' />
	<script type='text/javascript' src='<?php echo $premium_url; ?>js/jquery/script.min.js?ver=3.5.1'></script>
	<script type='text/javascript' src='<?php echo $premium_url; ?>js/jquery-forms/script.min.js?ver=3.5.1'></script>
	<script type="text/javascript">
		<?php set_premium_default_js('admin'); ?>
		jQuery(function(){
			$('.visible_password').on('click', function(){
				var parent_div = $(this).parents('.input_password_wrap');
				if(parent_div.find('input').hasClass('vis')){
					parent_div.find('input').prop('type', 'password');
				} else {
					parent_div.find('input').prop('type', 'text');
				}
				parent_div.find('input').toggleClass('vis');
			});
		});
	</script>	
	<?php do_action('newadminpanel_form_head'); ?>
</head>
<body <?php body_class(); ?>>
<div id="container">
	<div class="wrap">
		<form method="post" class="ajax_post_form" action="<?php the_pn_link('pn_admin_login','ajax', 0); ?>">
		<input type="hidden" name="super_base" value="<?php echo $super_base; ?>" />
		
		<div class="resultgo"></div>
		<div class="form">
			<div class="form_title"><?php _e('Authorization','pn'); ?></div>
			
			<div class="form_line">
				<div class="form_label"><?php _e('Login or email', 'pn'); ?></div>
				<input type="text" name="logmail" class="notclear" value="<?php echo pn_strip_input(is_param_get('set_logmail')); ?>" />
			</div>

			<div class="form_line">
				<div class="form_label"><?php _e('Password', 'pn'); ?></div>
				<div class="input_password_wrap">
					<div class="visible_password"></div>
					<input type="password" name="pass" class="notclear" value="<?php echo pn_strip_input(is_param_get('set_pass')); ?>" />
				</div>
			</div>
			
			<?php do_action('newadminpanel_form'); ?>
			
			<div class="form_line centered"><input type="submit" formtarget="_top" name="submit" value="<?php _e('Sign in', 'pn'); ?>" /></div>
			
			<div class="form_links"><a href="<?php echo $plugin->get_page('register'); ?>"><?php _e('Sign up','pn'); ?></a> | <a href="<?php echo $plugin->get_page('lostpass'); ?>"><?php _e('Forgot password?','pn'); ?></a></div>
		</div>
		</form>
	</div>
	<?php do_action('newadminpanel_form_footer'); ?>
</div>	
</body>
</html>		
			<?php
			exit;
		}
	} 	 
	
	function def_premium_action_pn_admin_login(){
	global $wpdb, $or_site_url, $admin_panel_url;	
		
		only_post();
		nocache_headers();
			
		header('Content-Type: application/json; charset=utf-8');	
			
		$secure_cookie = is_ssl();
		
		$log = array();	
		$log['response'] = '';
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		$log['errors'] = array();
		
		$log = apply_filters('before_ajax_form_field', $log, 'newadminpanel');
		$log = apply_filters('newadminpanel_ajax_form', $log);
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);			
		
		if($user_id){
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! This form is available for unauthorized users only','pn');
			if(current_user_can('read')){
				$url = admin_url('index.php');
			} else {
				$url = $or_site_url;
			}
			$log['url'] = get_safe_url($url);
			echo json_encode($log);
			exit;		
		}
			
		$logmail = is_param_post('logmail');
		if(strstr($logmail,'@')){
			$logmail = is_email($logmail);
		} else {
			$logmail = is_user($logmail);
		}

		$pass = is_password(is_param_post('pass'));
		
		if($logmail){
			if($pass){
				$super_base = trim(is_param_post('super_base'));
				if($admin_panel_url == $super_base){
					if(strstr($logmail,'@')){
						$ui = get_user_by('email', $logmail);
					} else {
						$ui = get_user_by('login', $logmail);
					}
					if(isset($ui->ID)){
						$creds = array();
						$creds['user_login'] = is_user($ui->user_login);
						$creds['user_password'] = $pass;
						$creds['remember'] = true;
						$user = wp_signon($creds, $secure_cookie);	
					
						$log = apply_filters('premium_auth', $log, $user, 'admin');
					
						if ($user && !is_wp_error($user)) {
							$log['status'] = 'success';
							$log['url'] = get_safe_url(admin_url('index.php'));		
						} elseif($user and isset($user->errors['pn_error'])){
							$log['status'] = 'error';	
							$log['status_text'] = $user->errors['pn_error'][0];
						} elseif($user and isset($user->errors['pn_success'])){	
							$log['status'] = 'success';	
							$log['clear'] = 1;
							$log['status_text'] = $user->errors['pn_success'][0];
						} elseif($user and isset($user->errors['pn_pin'])){	
							$log['status'] = 'success';	
							$log['show_hidden'] = 1;
							$log['status_text'] = $user->errors['pn_pin'][0];							
						} else {
							$log['status'] = 'error';
							$log['status_code'] = 1;
							$log['status_text'] = __('Error! Wrong pair of username/password entered','pn');
						}
					} else {
						$log['status'] = 'error';
						$log['status_code'] = 1;
						$log['status_text'] = __('Error! Wrong pair of username/password entered','pn');				
					}
				} else {
					$log['status'] = 'error';
					$log['status_code'] = 1;
					$log['status_text'] = __('Error! Wrong pair of username/password entered','pn');				
				}			
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! Incorrect password','pn');
			}
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! Incorrect login or e-mail','pn');
		}			
		
		echo json_encode($log);	
		exit;
	}	
	
	add_filter('all_settings_option', 'newadminpanel_settings_option');
	function newadminpanel_settings_option($options){
		
		$plugin = get_plugin_class();
		
		$options['line_newpanel'] = array(
			'view' => 'line',
		);	
		$options['newpanel'] = array(
			'view' => 'inputbig',
			'title' => __('Admin panel URL', 'pn'),
			'default' => $plugin->get_option('admin_panel_url'),
			'name' => 'admin_panel_url',
			'work' => 'input',
		);
		$options['newpanel_help'] = array(
			'view' => 'help',
			'title' => __('More info','pn'),
			'default' => __('Enter new URL to enter the admin panel. Use only lowercase letters and numbers. Be sure to remember the entered address!','pn'),
		);	
		
		return $options;
	}

	add_action('all_settings_option_post', 'newadminpanel_settings_option_post');
	function newadminpanel_settings_option_post($data){
		$plugin = get_plugin_class();
		$admin_panel_url = is_admin_newurl($data['admin_panel_url']);
		$plugin->update_option('admin_panel_url','',$admin_panel_url);
	}	
}