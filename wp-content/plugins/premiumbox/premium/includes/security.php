<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('premium_verify_csrf')){ 
	add_action('premium_post', 'premium_verify_csrf', 0);
	function premium_verify_csrf($m) {
		if($m == 'post' or $m == 'action'){
			$method = trim(is_param_get('meth'));
			if ( !pn_verify_nonce( is_param_get('yid'), is_param_get('ynd') ) ) {
				if($method == 'get'){
					pn_display_mess(__('System error (code: anticsfr)','premium'));
				} else {
					header('Content-Type: application/json; charset=utf-8'); 
					
					$log = array();
					$log['status'] = 'error';
					$log['status_code'] = '1'; 
					$log['status_text']= __('System error (code: anticsfr)','premium');
					echo json_encode($log);
					exit;
				}
			}			
		}
	}
}

if(!function_exists('premium_verify_csrf_byorigin')){
	add_action('premium_post', 'premium_verify_csrf_byorigin', 0);
	function premium_verify_csrf_byorigin($m) {
		if($m == 'post' or $m == 'action'){
			$method = trim(is_param_get('meth'));
			$origin = '';
			if(isset($_SERVER['HTTP_ORIGIN'])){
				$origin = rtrim(str_replace(array('http://','https://'),'',$_SERVER['HTTP_ORIGIN']),'/');
			}
			$origin_arr = explode(':', $origin);
			$origin = is_isset($origin_arr, 0);
			$site = rtrim(str_replace(array('http://','https://'),'',get_site_url_or()),'/');
			if($origin and $origin != $site){
				if($method == 'get'){
					pn_display_mess(__('System error (code: validreq)','premium'));
				} else {
					header('Content-Type: application/json; charset=utf-8'); 
					
					$log = array();
					$log['status'] = 'error';
					$log['status_code'] = '1'; 
					$log['status_text']= __('System error (code: validreq)','premium');
					echo json_encode($log);
					exit;
				}
			} 		
		}
	}
}

if(!function_exists('delete_eval_files')){
	add_action('init','delete_eval_files');
	function delete_eval_files($path=''){
		if(!$path){
			$my_dir = wp_upload_dir();
			$path = $my_dir['basedir'].'/';
		} 
		$true = array('.gif','.jpg','.jpeg','.jpe','.png','.csv','.htaccess','.txt','.xml','.dat','.svg');
		$true = apply_filters('delete_eval_files_ext', $true);
		if(is_dir($path)){
			$dir = @opendir($path);
			while(($file = @readdir($dir))){
				if (is_file($path.$file)){	
					$ext = strtolower(strrchr($file,"."));	
					if(!in_array($ext, $true) or strstr($file,'.php')){
						@unlink($path.$file);			
					}
				} elseif(is_dir($path.$file)){
					if ( substr($file, 0, 1) != '.'){
						delete_eval_files($path.$file.'/');
					}
				}
			}
		}
	}
}

if(!function_exists('pn_remove_pingback_method')){
	add_filter('xmlrpc_enabled', '__return_false');
	add_filter('wp_xmlrpc_server_class', 'disable_wp_xmlrpc_server_class');
	function disable_wp_xmlrpc_server_class() {
		return 'disable_wp_xmlrpc_server_class';
	}
	class disable_wp_xmlrpc_server_class {
		function serve_request() {
			echo 'XMLRPC disabled';
			exit;
		}
	}
	add_filter('xmlrpc_methods', 'pn_remove_pingback_method');
	function pn_remove_pingback_method( $methods ) {
		if(isset($methods['pingback.ping'])){
			unset($methods['pingback.ping']);
		}	
		if(isset($methods['pingback.extensions.getPingbacks'])){	
			unset($methods['pingback.extensions.getPingbacks']);
		}
		return $methods;
	}

	add_filter('wp_headers', 'pn_remove_x_pingback_header');
	function pn_remove_x_pingback_header($headers){
		if(isset($headers['X-Pingback'])){
			unset( $headers['X-Pingback'] );
		}
		return $headers;
	}
}

if(!function_exists('security_comment_text')){
	add_filter('comment_text', 'security_comment_text',0);
	add_filter('the_content', 'security_comment_text',0);
	add_filter('the_excerpt', 'security_comment_text',0);
	function security_comment_text($content){
		return pn_strip_text($content);
	}

	add_filter('the_title', 'security_the_title',0);
	function security_the_title($content){
		return pn_strip_input($content);
	}

	add_filter('is_email', 'security_is_email',0);
	function security_is_email($content){
		return pn_strip_input($content);
	}
}

if(!function_exists('security_preprocess_comment')){
	add_filter('preprocess_comment', 'security_preprocess_comment',10);
	function security_preprocess_comment($commentdata){
		
		if(is_array($commentdata)){
			$new_comment = array();
			foreach($commentdata as $k => $v){
				$new_comment[$k] = pn_maxf_mb(pn_strip_text($v), 2000);
			}
			return $new_comment;
		}
		
		return $commentdata;
	}
}

if(!function_exists('security_query_vars')){
	add_filter( 'query_vars', 'security_query_vars' );
	function security_query_vars($data){
		if(!is_admin()){
			$key = array_search('author', $data);
			if($key){
				if(isset($data[$key])){
					unset($data[$key]);
				}
			}
			$key = array_search('author_name', $data);
			if($key){
				if(isset($data[$key])){
					unset($data[$key]);
				}
			}			
		}
		return $data;
	}
}

if(!function_exists('security_wp_dashboard_setup')){
	add_action('wp_dashboard_setup', 'security_wp_dashboard_setup' );
	function security_wp_dashboard_setup(){
		if(current_user_can('administrator')){
			wp_add_dashboard_widget('standart_security_dashboard_widget', __('Security check','premium'), 'dashboard_security_in_admin_panel');
		}
	}

	function dashboard_security_in_admin_panel(){
		$errors = apply_filters('premium_security_errors', array());
		$errors = (array)$errors;
		
		$r=0;
		foreach($errors as $error){ $r++;
			?>
			<div class="dashboard_security_line">-<?php echo $error; ?></div>
			<?php
		}
		
		if($r == 0){
			echo '<div class="bgreen">' . __('Security status - OK','premium') . '</div>';
		}
	}
}

if(!function_exists('premium_admin_bar_security')){
	add_action('wp_before_admin_bar_render', 'premium_admin_bar_security', 2);
	function premium_admin_bar_security(){
	global $wp_admin_bar, $wpdb;

		if(current_user_can('administrator')){
			$errors = apply_filters('premium_security_errors', array());
			$errors = (array)$errors;
			$count_errors = count($errors);
			
			if($count_errors > 0){
				$wp_admin_bar->add_node( array(
					'id'     => 'security_alert',
					'href' => admin_url('admin.php?page=all_security_alert'),
					'title'  => '<div style="height: 32px; width: 32px; background: url('. get_premium_url() .'/images/alert.gif) no-repeat center center; background-size: contain;"></div>',
					'meta' => array( 
						'title' => sprintf(__('Security errors (%s)','premium'), $count_errors),
						'class' => 'premium_ab_icon',
					)		
				));	
			}	
		}
	}
}

if(!function_exists('premium_security_admin_menu')){
	add_action('admin_menu', 'premium_security_admin_menu');
	function premium_security_admin_menu(){
		if(function_exists('get_plugin_class')){
			$plugin = get_plugin_class();	
			add_submenu_page("pn_none_menu", __('Security errors','premium'), __('Security errors','premium'), 'administrator', "all_security_alert", array($plugin, 'admin_temp'));
		}
	}
}

if(!function_exists('def_adminpage_title_all_security_alert')){
	add_action('pn_adminpage_title_all_security_alert', 'def_adminpage_title_all_security_alert');
	function def_adminpage_title_all_security_alert(){
		_e('Security errors','premium');
	}
}	

if(!function_exists('def_adminpage_content_all_security_alert')){
	add_action('pn_adminpage_content_all_security_alert','def_adminpage_content_all_security_alert');
	function def_adminpage_content_all_security_alert(){
		$text = sprintf(__('Specify security settings or follow <a href="%s" target="_blank">the link</a> to see instructions for disabling security settings notifications.','premium'), 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/biblioteka-hukov/');
		?>
		<div style="margin: 0 0 10px 0;">
			<?php
			$form = new PremiumForm();
			$form->help(__('Instructions for disabling notifications','premium'), $text);
			?>
		</div>
		<?php
		
		$errors = apply_filters('premium_security_errors', array());
		$errors = (array)$errors;
		
		foreach($errors as $error){
		?>
		<div class="security_line"><?php echo $error; ?></div>
		<?php
		}
	}	
} 

if(!function_exists('def_premium_security_errors')){
	add_filter('premium_security_errors', 'def_premium_security_errors');
	function def_premium_security_errors($errors){
	global $wpdb;
	
		$plugin = '';
		if(function_exists('get_plugin_class')){
			$plugin = get_plugin_class();
		}
		
		$updater = ABSPATH . 'updater.php';
		if(is_file($updater)){ 
			$errors[] = __('There is a dangerous script updater.php in root directory. Delete it','premium');
		}	
		
		$sql_file = ABSPATH . 'damp_db.sql';
		if(is_file($sql_file)){ 
			$errors[] = __('There is a dangerous file damp_db.sql in root directory. Delete it','premium');
		}		
		
		$installer = ABSPATH . 'installer/'; 
		if(is_dir($installer)){ 
			$errors[] = __('There is a dangerous folder installer in root directory. Delete it','premium'); 
		}	

		if(!defined('DISALLOW_FILE_MODS') or defined('DISALLOW_FILE_MODS') and !constant('DISALLOW_FILE_MODS')){
			$errors[] = __('Edit mode enabled. Disable it','premium');
		}	
		
		if(defined('PN_ADMIN_GOWP') and constant('PN_ADMIN_GOWP') == 'true'){
			$errors[] = __('Disable editing mode','premium');
		}
		
		if(is_object($plugin)){
			$admin_panel_url = is_admin_newurl($plugin->get_option('admin_panel_url'));
			if(!$admin_panel_url){
				$errors[] = __('Set new address of website control panel','premium');
			}	
		}

		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		if(isset($ui->user_login) and $ui->user_login == 'admin' or isset($ui->user_login) and $ui->user_login == 'administrator'){ 
			$errors[] = __('Admin login is standard. Change it','premium');		
		}	
		
		if(
			isset($ui->email_login) and $ui->email_login != 1 and
			isset($ui->sms_login) and $ui->sms_login != 1 and
			isset($ui->telegram_login) and $ui->telegram_login != 1
		){ 
			$errors[] = sprintf(__('Two-factor authentication is disabled. <a href="%s">Instructions</a> how to enable it','premium'), 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/dvuhfaktornaya-avtorizatsiya-v-paneli-upravleniya-saytom/'); 	
		}
		
		if(isset($ui->user_pass) and $ui->user_pass == '$P$BASwWSemU6D3fp2iRd2M7pX0SH.g2a/'){ 
			$errors[] = __('Admin password is standard. Change it','premium');		
		}	

		if(!defined('PN_SECRET_KEY') or defined('PN_SECRET_KEY') and strlen(PN_SECRET_KEY) < 1){
			$errors[] = sprintf(__('Security password is disabled. <a href="%s">Instructions</a> how to enable it','premium'), 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/kod-bezopasnosti-dlya-podtverzhdeniya-platezhey/');	
		}

		/*if(!defined('PN_HASH_KEY') or defined('PN_HASH_KEY') and strlen(PN_HASH_KEY) < 1){
			$errors[] = sprintf(__('Security hash key is disabled. <a href="%s">Instructions</a> how to enable it','premium'), 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/kod-bezopasnosti-dlya-podtverzhdeniya-platezhey/');	
		}*/		

		return $errors;
	}
} 