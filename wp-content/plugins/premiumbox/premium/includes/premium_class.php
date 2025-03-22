<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('Premium')){
	class Premium {
		
		public $plugin_version = "0";
		public $plugin_prefix = "premium";
		public $plugin_name = "Premium";
		public $plugin_path = "";
		public $plugin_dir = "";
		public $plugin_url = "";
		public $plugin_usersess_day = "";
		public $debug_mode = 0;
		public $upload_dir = '';
		public $upload_url = '';
		public $theme_name = '';
		public $blog_page = '';
		public $physical_files = '';
		public $page_name = 'the_pages';
		public $extended_name = 'pn_extended';

		function __construct($settings)
		{
			$file = trim(is_isset($settings, 'file'));
			
			$this->physical_files = intval(is_isset($settings, 'physical_files'));
			if($this->physical_files){
				if(!defined('PREMIUM_PHYSICAL_FILES')){
					define('PREMIUM_PHYSICAL_FILES', true);
				}	
			}
			
			if(!defined('CURL_SSLVERSION_TLSv1_2')){
				define('CURL_SSLVERSION_TLSv1_2', 6);
			}
			
			$this->plugin_path = plugin_basename($file);
			$this->plugin_dir = wp_normalize_path(dirname($file)).'/';
			$this->plugin_url = plugin_dir_url($file);
			
			if(WP_DEBUG){
				$this->debug_mode = 1;
			} else {
				$this->debug_mode = intval(is_isset($settings, 'debug_mode'));
			}
			
			$this->debug_mode();
			
			$this->upload_dir = rtrim(wp_normalize_path(WP_CONTENT_DIR), '/').'/pn_uploads/';
			$this->upload_url = rtrim(wp_normalize_path(WP_CONTENT_URL), '/').'/pn_uploads/';
			
			$disallow_file_mode = intval(is_isset($settings, 'disallow_file_mode'));
			if($disallow_file_mode){
				if(!defined('DISALLOW_FILE_MODS')){
					define('DISALLOW_FILE_MODS', true);
				}	
			}
			
			if(!defined('CONCATENATE_SCRIPTS')){
				define('CONCATENATE_SCRIPTS', false);
			}
			
			add_filter('all_plugins', array($this, 'title_this_plugin'));
			
			add_action('plugins_loaded', array($this, 'plugin_langs_loaded'));
			
			add_action('activate_'. $this->plugin_path, array($this, 'plugin_activate'));
			add_action('deactivate_'. $this->plugin_path, array($this, 'plugin_deactivate'));
			
			add_action('admin_menu', array($this, 'admin_menu'), 1);
			
			add_filter('pn_tech_pages', array($this, 'list_tech_pages'));

			$this->include_standart_page();
			
			add_action('widgets_init', array($this, 'widgets_init'));
			
			$this->premium_once();
		}
		
		function premium_once(){
			global $premium_once;
			$premium_once = intval($premium_once); $premium_once++;
			if($premium_once == 1){
				$this->init_options();
				add_action('init', array($this,'init_session'), 0);
				add_action('init', array($this,'rewrites_pages'), 0);
				add_filter('gettext', array($this,'strip_gettext'), 1000, 3);
				add_filter('auth_cookie_expiration', array($this, 'auth_cookie_expiration'), 10, 3 );
				add_filter('use_block_editor_for_post', array($this, 'use_block_editor_for_post'));
				add_action("admin_menu", array($this, 'tech_pages_select'));
				add_action("edit_post", array($this, 'tech_pages_edit_post'));
				add_action('admin_head', array($this, 'admin_head'));
				add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
				add_action('wp_enqueue_scripts', array($this, 'theme_init'),0);
				add_action('wp_enqueue_scripts', array($this, 'theme_init_last'),99);
				add_filter('sanitize_title', array($this,'sanitize'), 9);
				add_filter('sanitize_file_name', array($this,'sanitize'));
				add_filter('wp_title' , array($this,'premium_wp_title'), 1);
				add_action('premium_siteaction_logout', array($this, 'premium_siteaction_logout')); 
				add_filter('logout_url', array($this, 'logout_url'));
				if($this->is_debug_mode()){
					add_action('wp_footer', array($this, 'wp_footer_text'), 1000);
				}	
				add_filter('admin_footer_text', array($this, 'admin_footer_text'), 1000);
			}
		}
		
		function strip_gettext($translation, $text, $domain){
			if(is_string($translation)){
				return str_replace(array("'"), array('&#039;'), $translation);
			} else {
				return $translation;
			}
		}
		
		function init_session(){
			$session_key = pn_strip_input(get_pn_cookie('premium_session_id'));
			if(!$session_key){
				$session_key = wp_generate_password(64, false, false);
				add_pn_cookie('premium_session_id', $session_key);
			}
		}
		
		function rewrites_pages(){
			
			$plugin = basename($this->plugin_path, '.php');
			$wp_content = ltrim(str_replace(ABSPATH,'',WP_CONTENT_DIR),'/');
			
			$list_rewrites_pages = array(
				'premium_post.php' => 'premium/sitepage/premium_post.php',
				'premium_quicktags.php' => 'premium/sitepage/premium_quicktags.php',
				'premium_action-([a-zA-Z0-9\_]+).php' => 'premium/sitepage/premium_action.php?pn_action=$1',
				'premium_request-([a-zA-Z0-9\_]+).php' => 'premium/sitepage/premium_request.php?pn_action=$1',
				'premium_script.php' => 'premium/sitepage/premium_script.php',
				'api.php' => 'premium/sitepage/premium_api.php',
				'merchant-([a-zA-Z0-9\_]+).php' => 'premium/sitepage/premium_merchant.php?pn_action=$1',
				'cron.php' => 'premium/sitepage/premium_cron.php',
				'cron-([a-zA-Z0-9\_]+).php' => 'premium/sitepage/premium_cron.php?pn_action=$1',
			);
			$list_rewrites_pages = apply_filters('list_pn_rewrites_pages', $list_rewrites_pages);
			
			if(is_array($list_rewrites_pages)){
				foreach($list_rewrites_pages as $html_name => $script_url){
					add_rewrite_rule($html_name . '$', $wp_content . '/plugins/'. $plugin .'/'. $script_url, 'top');
				}
			}
		}		
		
		function include_standart_page(){
			$this->file_include('includes/pn_func');
			$this->file_include('includes/pn_bd_func'); 
			$this->file_include('includes/pn_admin_func');
			$this->file_include('includes/deprecated');
			$this->file_include('includes/lang_filters');
			$this->file_include('includes/post_types');
			$this->file_include('includes/comments');

			$this->auto_include('shortcode');
		}
		
		function widgets_init(){
			$this->auto_include('widget');
		}
		
		function sanitize($title){
			$title = replace_cyr($title);
			$title = preg_replace("/[^A-Za-z0-9\-\.]/", '-', $title);
			return $title;
		}
		
		function premium_wp_title($title){
			$temp = apply_filters('premium_wp_title', '[title] - [description]');
			if($temp){
				$site_name = pn_strip_input(get_bloginfo('sitename'));
				if(is_front_page()){
					$site_description = pn_strip_input(get_bloginfo('description'));
				} else {
					$site_description = str_replace('&raquo;','',$title);
				}
				$new_title = str_replace('[title]', $site_name, $temp);
				$title = str_replace('[description]', $site_description, $new_title);
			}	
			return $title;			
		}		
		
		function use_block_editor_for_post(){
			return 0;
		}

		function set_plugin_title(){
			return array();
		}
		
		function title_this_plugin($plugins){
			global $locale;	
			
			$plugin_path = $this->plugin_path;	
			
			$title_arr = $this->set_plugin_title();
				
			if(isset($title_arr[$locale])){
				$plugins[$plugin_path]['Name'] = is_isset($title_arr[$locale], 'name');
				$plugins[$plugin_path]['Description'] = is_isset($title_arr[$locale], 'description');	
			} 	
				
			return $plugins;
		}		
		
		function create_upload_folder(){
			$dir = $this->upload_dir;
			if(!is_dir($dir)){
				@mkdir($dir, 0777);
			}			
		}		
		
		function debug_mode(){
			if($this->debug_mode){	
				if(function_exists('ini_set')){
					ini_set('display_errors', 1);
				}
				if(function_exists('error_reporting')){
					error_reporting(E_ALL); 
				}
			}
		}
		
		function is_debug_mode(){
			if($this->debug_mode){
				return 1;
			}
				return 0;
		}
		
		function _deprecated_function( $function, $version, $replacement=''){
			if($this->debug_mode) {
				if ($replacement) {
					trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> in plugin <strong>%2$s</strong> since version %3$s! Use %4$s instead.', 'premium'), $function, $this->plugin_name, $version, $replacement ) );
				} else {
					trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> in plugin <strong>%2$s</strong> since version %3$s with no alternative available.', 'premium'), $function, $this->plugin_name, $version ) );
				}
			}
		}		

		function admin_menu(){
			
		}
		
		function list_tech_pages($pages){
			return $pages;
		}	
		
		function is_up_mode(){
			$up_mode = 0;
			if($this->get_option('up_mode') == 1){
				$up_mode = 1;
			}
				return $up_mode;
		}		
		
		function up_mode($method=''){
			if(!$method){ $method = trim(is_param_get('meth')); }
			if($method != 'post'){ $method = 'get'; }
			if($this->is_up_mode()){
				if($method == 'get'){
					pn_display_mess(__('Maintenance','premium')); 
				} else {
					$log = array();
					$log['status'] = 'error';
					$log['status_code'] = '-1'; 
					$log['status_text'] = __('Maintenance','premium');
					echo json_encode($log);
					exit;	
				}
			}
		}		
		
		function auth_cookie_expiration($expiration, $user_id, $remember){
			$session_day = intval($this->plugin_usersess_day);
			if($session_day > 0){
				$expiration = $session_day * DAY_IN_SECONDS;
			}
			return $expiration;
		} 		
		
		function plugin_langs_loaded(){
			$plugin_path = dirname($this->plugin_path);
			if($plugin_path){
				load_plugin_textdomain( $this->plugin_prefix, false, $plugin_path . '/langs' );
			}
		}	

		function init_options(){
			global $premium_options, $wpdb;
			
			$query = $wpdb->query("CHECK TABLE ".$wpdb->prefix . 'pn_options');
			if($query == 1){
				$query_options = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix . 'pn_options');
				foreach($query_options as $qc){
					$key1 = trim($qc->meta_key);
					$key2 = trim($qc->meta_key2);
					$premium_options[$key1][$key2] = maybe_unserialize($qc->meta_value);
				}
			}	
		}

		function get_option($option='', $option2=''){
			global $premium_options;
			
			if(isset($premium_options[$option][$option2])){
				return $premium_options[$option][$option2];
			} 

				return '';
		}

		function update_option($key1='', $key2='', $value=''){
			global $wpdb, $premium_options; 
			
			if(is_object($value) or is_array($value)){ $value = @serialize($value); }
			
			$key1 = pn_strip_input($key1);
			$key2 = pn_strip_input($key2);
			
			$result = 0;
			
			$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix . "pn_options WHERE meta_key = '$key1' AND meta_key2 = '$key2'");
			if(isset($item->id)){
				$result = $wpdb->update($wpdb->prefix . 'pn_options', array('meta_value'=> $value), array('id'=>$item->id));
			} else {
				$wpdb->insert($wpdb->prefix . 'pn_options', array('meta_key'=>$key1,'meta_key2'=>$key2,'meta_value'=> $value));
				$result = $wpdb->insert_id;
			}
			
			$premium_options[$key1][$key2] = $value;

			return $result;
		}

		function delete_option($key1='', $key2=''){
			global $wpdb, $premium_options; 
			
			$key1 = pn_strip_input($key1);
			$key2 = pn_strip_input($key2);		
			$result = $wpdb->query("DELETE FROM ". $wpdb->prefix . "pn_options WHERE meta_key = '$key1' AND meta_key2 = '$key2'");
			
			if(isset($premium_options[$key1][$key2])){
				unset($premium_options[$key1][$key2]);
			}
			
			return $result;
		}		
		
		function file_include($page){
			$plugin_patch = $this->plugin_dir;
			$page = wp_normalize_path($page);
			$page = str_replace($plugin_patch,'',$page);
			$page_include = $plugin_patch . $page . ".php";
			if(file_exists($page_include)){
				include_once($page_include);
			}
		}

		function include_patch($path, $file){
			$plugin_patch = wp_normalize_path($this->plugin_dir);
			$path = wp_normalize_path(dirname($path));
			$page = str_replace($plugin_patch,'',$path);
			$this->file_include($page . '/' . $file);	
		}
		
		function auto_include($folder, $filename=''){
			$folder = wp_normalize_path($folder);
			$folder = str_replace($this->plugin_dir, '', $folder);
			$foldervn = $this->plugin_dir . $folder."/";			
			if(is_dir($foldervn)){
				$dir = @opendir($foldervn);
				$abc_files = array();
				while(($file = @readdir($dir))){
					$abc_files[] = $file;
				}
				asort($abc_files);
				foreach($abc_files as $file){
					if($filename){
						if ( substr($file, -4) != '.php' and substr($file, 0, 1) != '.'){
							$this->file_include($folder. '/'. $file .'/'. $filename);
						}
					} else {
						if ( substr($file, -4) == '.php' ){
							include($foldervn. '/'. $file);
						}
					}			
				}
			}
		}			

		function plugin_create_pages(){
			$pages = apply_filters('pn_tech_pages', array());
			$this->create_pages($pages);
		}
		
		function tech_pages_select(){
			$pages = apply_filters('pn_tech_pages', array());
			if(is_array($pages) and count($pages) > 0){
				add_meta_box("pn_techpage_id", __('Technical page', 'premium'), array($this, "tech_pages_select_box"), 'page', "normal");
			}
		}
		
		function tech_pages_select_box($post){
			$select = '';
			if(isset($post->ID)){
				$post_id = $post->ID; 
				$page_name = $this->page_name;
				$premium_pages = (array)get_option($page_name);
				$default = 0;
				foreach($premium_pages as $post_key => $id){
					if($id == $post_id){
						$default = $post_key;
					}
				}
				
				$sel_options = array();
				$sel_options[0] = '--'. __('No', 'premium') .'--';
				$pages = apply_filters('pn_tech_pages', array());
				foreach($pages as $data){
					$post_key = trim(is_isset($data, 'post_key')); if(!$post_key){ $post_key = trim(is_isset($data, 'post_name')); }
					if($post_key){
						if(function_exists('ctv_ml')){
							$sel_options[$post_key] = ctv_ml(is_isset($data, 'post_title'));
						} else {
							$sel_options[$post_key] = is_isset($data, 'post_title');
						}
					}
				}
			
				$select = '
				<select name="pn_techpage_key" autocomplete="off">';
					foreach($sel_options as $sel_name => $sel_value){
						$select .= '<option value="'. $sel_name .'" '. selected($sel_name, $default, false) .'>'. $sel_value .'</option>';
					}
				$select .= '
				</select>';
			}
				echo $select;
		}
		
		function tech_pages_edit_post($post_id){
			if(!current_user_can('edit_post', $post_id )){
				return $post_id;
			}
			
			$page_name = $this->page_name;	
			if(isset($_POST['pn_techpage_key'])){
				$premium_pages = get_option($page_name);
				if(!is_array($premium_pages)){ $premium_pages = array(); }
				$new_premium_pages = array();
				foreach($premium_pages as $post_key => $id){
					if($id != $post_id){
						$new_premium_pages[$post_key] = $id;
					}	
				}	
				$techpage_key = trim(is_param_post('pn_techpage_key'));
				$new_premium_pages[$techpage_key] = $post_id;
				update_option($page_name, $new_premium_pages);
			}
		}
		
		function create_pages($pages=''){
			$created = array();
			if(!is_array($pages)){ $pages = array(); }
			
			$page_name = $this->page_name;
			$blog_page = trim($this->blog_page);
			if($page_name){
				
				$premium_pages = get_option($page_name);
				if(!is_array($premium_pages)){ $premium_pages = array(); }
				
				$pages_content = array();
				foreach($pages as $page){
					if(isset($page['post_name'])){
						$post_key = trim(is_isset($page, 'post_key'));
						if(!$post_key){ $post_key = $page['post_name']; }
						
						$pages_content[$post_key] = array(
							'comment_status' => 'closed',
							'ping_status'    => 'closed',
							'post_name'      => 'test',
							'post_status'    => 'publish',
							'post_title'     => 'Test',
							'post_type'      => 'page',
							'post_content'   => '',
							'post_template'   => 'pn-pluginpage.php',
						);
								
						foreach($page as $key => $val){
							if($key == 'post_title'){
								if(function_exists('is_ml') and !is_ml()){
									$pages_content[$post_key][$key] = ctv_ml($val);
								} else {
									$pages_content[$post_key][$key] = $val;
								}
							} elseif($key != 'post_key'){
								$pages_content[$post_key][$key] = $val;
							}
						}
					}
				}
						
				foreach($pages_content as $post_key => $data){
					if(isset($premium_pages[$post_key])){
						$id = intval($premium_pages[$post_key]);
						if($id > 0){
							$status = get_post_status($id);	
							if($status == 'publish'){
								wp_update_post(array('ID' => $id, 'post_status' => 'publish'));
							} else {
								if(isset($pages_content[$post_key])){
									$created[$post_key] = $data['post_template'];
									$premium_pages[$post_key] = -1;
								}								
							}
						} else {
							$created[$post_key] = $data['post_template'];
							$premium_pages[$post_key] = -1;				
						}
					} else {
						$created[$post_key] = $data['post_template'];
						$premium_pages[$post_key] = -1;			
					}
				}		
				
				foreach($created as $post_key => $temp){
					$page_id = wp_insert_post($pages_content[$post_key]);
					if($page_id){
						$premium_pages[$post_key] = $page_id;
						$temp = trim($temp);
						if($temp){
							update_post_meta($page_id, '_wp_page_template', $temp) or add_post_meta($page_id, '_wp_page_template', $temp, true);
						}
					}
				}
				
				update_option($page_name, $premium_pages);	

				$homepage = intval(is_isset($premium_pages, 'home'));
				$blogpage = intval(is_isset($premium_pages, $blog_page));
				if($homepage and $blogpage){
					update_option('show_on_front', 'page');
					update_option('page_on_front', $homepage);
					update_option('page_for_posts', $blogpage);
				}
			}
		}

		function get_page($attr){
			global $premium_pages;

			if(!is_array($premium_pages)){
				$premium_pages = get_option($this->page_name);
			}
			if(isset($premium_pages[$attr])){
				return get_permalink($premium_pages[$attr]);
			} 
				return '#not_page';
		}

		function get_icon_link($icon=''){
			$icon = trim($icon);
			if(!$icon){ 
				return $icon = get_premium_url() . 'images/icon.png'; 
			}
			return $this->plugin_url .'images/icon/'. $icon .'.png';
		}		
		
		function rewrite_htaccess(){
			$file = ABSPATH . '/.htaccess';
			if(file_exists($file)){
				$content = @file_get_contents($file);
				$nfile = '';
				$up1 = $up2 = 1;
				$content_lines = explode("\n", $content);
				
				foreach($content_lines as $con){
					$line_text = trim($con);
					if(strstr($line_text, 'Options All -Indexes')){
						$up1 = 0;
					}
					if(strstr($line_text, '<files wp-config.php>')){
						$up2 = 0;
					}
					$nfile .= $line_text . "\n";	
				}
				
				$upfile = '';
				if($up1){
					$upfile .= "Options All -Indexes \n\r";
				}
				if($up2){
					$upfile .= "<files wp-config.php> \n order allow,deny \n deny from all \n</files> \n\r";
				}			
				if($up1 or $up2){
					$upfile .= $nfile;
					$fs = @fopen($file, 'w+');
					@fwrite($fs, $upfile);
					@fclose($fs);
				}
			} else {
				$content = "Options All -Indexes \n\r";
				$content .="<files wp-config.php> \n order allow,deny \n deny from all \n</files> \n\r";
				$fs = @fopen($file, 'w+');
				@fwrite($fs, $rsec_content);
				@fclose($fs);
			}			
		}
		
		function first_settings(){
			global $wpdb;
			$prefix = $wpdb->prefix;

			$first_pn = intval(get_option('first_pn'));
			if(!$first_pn){	
				remove_role('editor');
				remove_role('author');
				remove_role('contributor');
				remove_role('subscriber');
				
				add_role('users', 'users', array());

				$wpdb->update( $prefix.'options' , array('option_value' => 'users'), array('option_name' => 'default_role'));
			 
				update_option('template', $this->theme_name);	
				update_option('stylesheet', $this->theme_name);	

				wp_delete_post( 1, true );
				wp_delete_post( 2, true );

				update_option('use_smilies' , '');
				update_option('posts_per_rss',5);
				update_option('rss_use_excerpt' , '1');
				update_option('default_pingback_flag', 0);
				update_option('default_ping_status', 0);
				update_option('comments_notify', 0);
				update_option('moderation_notify', 0);
				update_option('comment_moderation', '1');
				update_option('show_avatars', '0');
				update_option('uploads_use_yearmonth_folders' , '');
				update_option('permalink_structure' , '/%postname%/');

				$wpdb->update($wpdb->prefix.'terms', array('slug'=>'nocategory'), array('term_id'=>1));

				update_option('first_pn', 1);
			}			
		}
		
		function create_default_bd(){
			global $wpdb;
			$prefix = $wpdb->prefix;
			
			$table_name = $wpdb->prefix . "pn_options";
			$sql = "CREATE TABLE IF NOT EXISTS $table_name(
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`meta_key` varchar(250) NOT NULL,
				`meta_key2` varchar(250) NOT NULL,
				`meta_value` longtext NOT NULL,
				PRIMARY KEY ( `id` ),
				INDEX (`meta_key`),
				INDEX (`meta_key2`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$wpdb->query($sql);

			$table_name = $wpdb->prefix ."auth_logs";
			$sql = "CREATE TABLE IF NOT EXISTS $table_name(
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`auth_date` datetime NOT NULL,
				`user_id` bigint(20) NOT NULL,
				`user_login` varchar(250) NOT NULL,
				`old_user_ip` varchar(250) NOT NULL,
				`old_user_browser` varchar(250) NOT NULL,
				`now_user_ip` varchar(250) NOT NULL,
				`now_user_browser` varchar(250) NOT NULL,
				`auth_status` int(1) NOT NULL default '0',
				`auth_status_text` longtext NOT NULL,
				PRIMARY KEY ( `id` ),
				INDEX (`user_id`),
				INDEX (`auth_date`),
				INDEX (`auth_status`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$wpdb->query($sql);

			$table_name= $wpdb->prefix ."archive_data";
			$sql = "CREATE TABLE IF NOT EXISTS $table_name(
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`meta_key` varchar(250) NOT NULL,
				`meta_key2` varchar(250) NOT NULL,
				`meta_key3` varchar(250) NOT NULL,
				`item_id` bigint(20) NOT NULL default '0',
				`meta_value` varchar(20) NOT NULL default '0',
				PRIMARY KEY ( `id` ),
				INDEX (`meta_key`),
				INDEX (`meta_key2`),
				INDEX (`meta_key3`),
				INDEX (`meta_value`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$wpdb->query($sql);

			$table_name = $wpdb->prefix ."comment_system";
			$sql = "CREATE TABLE IF NOT EXISTS $table_name(
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`comment_date` datetime NOT NULL,
				`user_id` bigint(20) NOT NULL default '0',
				`user_login` varchar(250) NOT NULL,
				`text_comment` longtext NOT NULL,
				`itemtype` varchar(50) NOT NULL,
				`item_id` varchar(50) NOT NULL default '0',
				PRIMARY KEY ( `id` ),
				INDEX (`item_id`),
				INDEX (`itemtype`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$wpdb->query($sql);			
			
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'created_data'");
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `created_data` longtext NOT NULL");
			}			
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'sec_lostpass'");
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `sec_lostpass` int(1) NOT NULL default '1'");
			}
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'alogs_email'");
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `alogs_email` int(1) NOT NULL default '0'");
			}
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'alogs_sms'");
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `alogs_sms` int(1) NOT NULL default '0'");
			}
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'alogs_telegram'");
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `alogs_telegram` int(1) NOT NULL default '0'");
			}			
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'email_login'"); 
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `email_login` int(1) NOT NULL default '0'");
			}
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'sms_login'"); 
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `sms_login` int(1) NOT NULL default '0'");
			}
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'telegram_login'"); 
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `telegram_login` int(1) NOT NULL default '0'");
			}
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'enable_ips'");
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `enable_ips` longtext NOT NULL");
			}		
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'user_pin'");
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `user_pin` varchar(250) NOT NULL");
			}	
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'user_browser'");
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `user_browser` varchar(250) NOT NULL");
			}
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'user_ip'");
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `user_ip` varchar(250) NOT NULL");
			}
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'user_bann'");
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `user_bann` int(1) NOT NULL default '0'");
			}	
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'last_adminpanel'");
			if ($query == 0){
				$wpdb->query("ALTER TABLE ".$wpdb->prefix ."users ADD `last_adminpanel` varchar(50) NOT NULL");
			}			

			$wpdb->update($wpdb->prefix.'usermeta', array('meta_key' => ''), array('meta_key' => 'locale'));
		}
		
		function plugin_activate(){	
			$this->rewrite_htaccess();
			$this->first_settings();
			$this->create_default_bd();
			$this->file_include('activation/bd');
			$this->file_include('activation/migrate');
			$this->update_option('up_mode','', 1);
			do_action('all_bd_activated');
			$this->create_upload_folder();
			$this->active_last_extended();
			$this->plugin_create_pages();
		}

		function active_last_extended(){
			$extended_last = get_option($this->extended_name . '_last');
			if(!is_array($extended_last)){ $extended_last = array(); }
		
			$list = array();
			foreach($extended_last as $folder => $data){
				if(is_array($data)){
					foreach($data as $name => $time){
						$list[$folder][$name] = $name;
						include_extanded($this, $folder, $name);
						do_action('all_'. $folder .'_active_'. $name);
						do_action('all_'. $folder .'_active', $name);
					}
				}
			}
			update_option($this->extended_name, $list);			
		}

		function plugin_deactivate(){	
			update_option($this->extended_name, array());
		}		
		
		function logout_url($link){
			return get_pn_action('logout', 'get');
		}
		
		function premium_siteaction_logout(){
		global $or_site_url;	

			wp_logout();
			
			$url = trim(is_param_get('return_url'));
			if(!$url){
				if(function_exists('get_site_url_ml')){
					$url = get_site_url_ml();
				} else {
					$url = $or_site_url;
				}
			}
			
			wp_redirect(get_safe_url($url));
			exit();				
		}		

		function get_current_screen(){
			$screen = get_current_screen();
			$screen_id = is_isset($screen,'id');
			if(strstr($screen_id, 'page_')){
				$screen_arr = explode('page_',$screen_id);
				$screen_id = trim(is_isset($screen_arr,1));
			}
			return $screen_id;
		}		
		
 		function admin_head(){
			$screen_id = $this->get_current_screen();
			
			if(has_filter('pn_adminpage_style_' . $screen_id) or has_filter('pn_adminpage_style')){
				?>
				<style>
					<?php
						do_action('pn_adminpage_style_' . $screen_id);
						do_action('pn_adminpage_style');
					?>
				</style>
				<?php
			}
			
			if(has_filter('pn_adminpage_js_' . $screen_id) or has_filter('pn_adminpage_js')){
				?>
				<script type="text/javascript">
					jQuery(function($){
					<?php 
						do_action('pn_adminpage_js_' . $screen_id); 
						do_action('pn_adminpage_js'); 
					?>
					});	
				</script>	
				<?php
			}			
		} 		
		
		function vers($vers=''){
			$vers = trim($vers);
			if(!$vers){ $vers = $this->plugin_version; }
			if($this->is_debug_mode() or $this->is_up_mode()){
				return current_time('timestamp');
			}
				return $vers;
		}
		
		function admin_enqueue_scripts(){
			$pn_version = get_premium_version();			
			$plugin_url = get_premium_url();
			$plugin_dir = get_premium_dir();
			
			wp_enqueue_style('roboto-sans', is_ssl_url("https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap"), false, "1.0");
			wp_enqueue_style('premium-ui-style', $plugin_url . "js/jquery-ui/style.css", false, "1.12.1");
			wp_enqueue_style('premium timepicker-style', $plugin_url . "js/jquery-timepicker/style.css", false, "1.3.4");
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_style('premium-style', $plugin_url . "style.css", false, $this->vers($pn_version));
			wp_enqueue_style('premium-all-style', $plugin_url . "all_style.css", false, $this->vers($pn_version));
			
			wp_deregister_script('jquery');
			wp_register_script('jquery', $plugin_url . 'js/jquery/script.min.js', false, '3.5.1');
			wp_enqueue_script('jquery');				
			wp_enqueue_script('jquery-ui', $plugin_url . 'js/jquery-ui/script.min.js', false, '1.12.1');
			wp_enqueue_script("jquery-timepicker", $plugin_url . "js/jquery-timepicker/script.min.js", false, "1.3.4");
			wp_enqueue_script("jquery-forms", $plugin_url . "js/jquery-forms/script.min.js", false, "3.51");
			wp_enqueue_script("jquery-cookie", $plugin_url ."js/jquery-cook/script.min.js", false, "3.0.0");
			wp_enqueue_script("jquery-clipboard", $plugin_url ."js/jquery-clipboard/script.min.js", false, "2.0.6");
			wp_enqueue_script('jquery-colorpicker', $plugin_url ."js/jquery-colorpicker/script.js", array('wp-color-picker'), false, '1.0');
				
			if(function_exists('get_lang_key')){	
				$locale = get_locale();
				$lang_key = get_lang_key($locale);
				if($lang_key != 'en'){
					if(file_exists($plugin_dir . "js/jquery-ui/datepicker-". $lang_key .".js")){
						wp_enqueue_script("jquery-datepicker", $plugin_url . "js/jquery-ui/datepicker-". $lang_key .".js", false, $pn_version);
					}	
				}				
			}
			wp_enqueue_script("jquery-editor", $plugin_url ."js/jquery-editor/script.min.js", false, '0.2');
			wp_enqueue_script("jquery-table", $plugin_url ."js/jquery-table/script.min.js", false, '0.3');
			wp_enqueue_script("jquery-window", $plugin_url ."js/jquery-window/script.min.js", false, $this->vers('0.5'));
			wp_enqueue_script("jquery-prbar", $plugin_url ."js/jquery-prbar/script.min.js", false, '0.2');
			wp_enqueue_script("jquery-changeinput", $plugin_url ."js/jquery-changeinput/script.min.js", false, '0.1');
				
			wp_enqueue_script('premium jquery', $plugin_url . 'js/premium.js', false, $this->vers($pn_version));
				
			$screen_id = $this->get_current_screen();	
			if(has_filter('pn_adminpage_quicktags_' . $screen_id) or has_filter('pn_adminpage_quicktags')){
				wp_enqueue_script('premium other quicktags', pn_quicktags_script($screen_id), array('quicktags'), $pn_version);
			}	
			
			$get_page = is_param_get('page');
			if(preg_match('/^'. preg_quote($this->plugin_prefix) .'_/i', $get_page)){
				$plugin_name = str_replace(' ','-',strtolower($this->plugin_name));
				wp_enqueue_style($plugin_name . '-style', $this->plugin_url . "style.css", false, $this->vers($this->plugin_version));
			}
			
			if(preg_match('/^'. preg_quote($this->plugin_prefix) .'_/i', $get_page) or preg_match('/^all_/i', $get_page)){
				wp_enqueue_media();
				wp_register_script( 'tgm-nmp-media', $plugin_url . 'js/media.js' , array( 'jquery' ), $this->vers($this->plugin_version), true );
				wp_localize_script( 'tgm-nmp-media', 'tgm_nmp_media',
					array(
						'title'     => __('Choose or upload file', 'premium'), 
						'button'    => __('Insert file into the field', 'premium'),
						'library'   => 'image', 
					)
				);
				wp_enqueue_script( 'tgm-nmp-media' ); 
			}				
		}	

		function theme_init(){	
		
			$pn_version = get_premium_version();
			$plugin_url = get_premium_url();

			wp_deregister_script('jquery');
			wp_register_script('jquery', $plugin_url . 'js/jquery/script.min.js', false, '3.5.1');
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui', $plugin_url . 'js/jquery-ui/script.min.js', false, '1.12.1');
			wp_enqueue_script("jquery-forms", $plugin_url . "js/jquery-forms/script.min.js", false, "3.51");
			wp_enqueue_script("jquery-cookie", $plugin_url ."js/jquery-cook/script.min.js", false, "3.0.0");
			wp_enqueue_script("jquery-clipboard", $plugin_url ."js/jquery-clipboard/script.min.js", false, "2.0.6");
			wp_enqueue_script("jquery-window", $plugin_url ."js/jquery-window/script.min.js", false, $this->vers('0.5'));
			wp_enqueue_script("jquery-changeinput", $plugin_url ."js/jquery-changeinput/script.min.js", false, '0.1');
			
			if(defined('PN_COMMENT_STATUS') and constant('PN_COMMENT_STATUS') == 'true'){
				if (is_singular() && comments_open() && get_option('thread_comments')){
					wp_enqueue_script("jquery-commentreply", $plugin_url . "js/jquery-commentreply/script.js", false, "0.1");
				}
			}
			
			if(current_user_can('read')){
				wp_enqueue_style('premium-all-style', $plugin_url . "all_style.css", false, $this->vers($pn_version));
			}
		}

		function theme_init_last(){
			global $or_site_url;
		
			$pn_version = current_time('timestamp');
			$lang = '';
			if(function_exists('get_lang_key')){
				$lang = get_lang_key(get_locale());
			}
			if($this->physical_files){
				$file_url = $or_site_url . '/premium_script.php?lang='. $lang;
			} else {
				$file_url = $or_site_url . '/premium_script.js?lang='. $lang;
			}			
				wp_enqueue_script('jquery-premium-js', $file_url, false, $pn_version);
		}	
		
     	function admin_temp(){
			$version = $this->plugin_version;
			$prefix = $this->plugin_prefix;

			$page = pn_strip_input(is_param_get('page'));
			$reply = is_param_get('reply');
			$code = is_param_get('rcode');
			
			$image = $this->plugin_url . 'images/big-icon.png';
			?>
			<div class="wrap">
				<?php
				$class_name = $page . '_Table_List';
				if(class_exists($class_name)){
					$table = new $class_name();
					$table->head_action();
				}
				?>
				
				<div class="premium_wrap">
					<div class="premium_wrap_ins">
					
						<?php do_action('pn_adminpage_head', $page, $prefix); ?>
					
						<div class="premium_header">
							<?php if($version){ ?>
								<div class="premium_version">
									<span class="pn_version js_<?php echo $prefix; ?>_version"><?php _e('version','premium'); ?>: <?php echo $version; ?></span>
									<div class="premium_version_menu">
										<?php		
										$ioncube_version = '-'; 
										if(function_exists('ioncube_loader_version')){
											$ioncube_version = ioncube_loader_version();
										}	
										?>
										<div class="premium_version_line"><span>PHP:</span> <?php echo phpversion(); ?></div>
										<div class="premium_version_line"><span>IonCube:</span> <?php echo $ioncube_version; ?></div>
									</div>
								</div>
							<?php }  ?>						
							
							<?php if($image){ ?>
								<div class="premium_title_logo">
									<div class="premium_title_logo_ins">
										<img src="<?php echo $image; ?>" alt="" />
									</div>	
								</div>
							<?php } ?>
							
							<div class="premium_title"><?php echo $this->plugin_name; ?></div> 
							<div class="premium_title_page">
								- <?php do_action('pn_adminpage_title_' . $page); ?>
							</div>
							<div id="premium_ajax"></div>
								
							<div class="premium_clear"></div>
						</div>									
					
						<?php do_action('after_pn_adminpage_title', $page, $prefix); ?>
					
						<div id="premium_reply_wrap">		
							<?php if($reply == 'true'){ 
								$reply_text = apply_filters('premium_admin_reply_true', __('Action completed successfully','premium'), $page, $code); 
							?>
								<div class="premium_reply pn_success js_reply_wrap"><div class="premium_reply_close js_reply_close"></div><?php echo $reply_text; ?></div>
							<?php } ?>
							<?php if($reply == 'false'){ 
								$reply_text = apply_filters('premium_admin_reply_false', __('Error! Action not completed','premium'), $page, $code); 
							?>
								<div class="premium_reply pn_error js_reply_wrap"><div class="premium_reply_close js_reply_close"></div><?php echo $reply_text; ?></div>
							<?php } ?>								
						</div>	
	
						<div class="premium_content">
							<?php do_action('pn_adminpage_content_'. $page); ?>
						</div>					
					
						<?php do_action('pn_adminpage_footer', $page, $prefix); ?>
					
							<div class="premium_clear"></div>
					</div>
				</div>
			</div>
			<?php
		}     
		
		function wp_footer_text($text){
			$tech_text = '<div>'. get_num_queries() .' queries in '. timer_stop(0, 10).' seconds.</div>';

			if(function_exists('is_mobile')){
				if(is_mobile()){
					$tech_text .= '<div><a href="'. web_vers_link() .'">Web version only</a></div>';
				} else {
					$tech_text .= '<div><a href="'. mobile_vers_link() .'">Mobile version only</a></div>';
				}
			}	
			
			$text .= ' <div style="position: fixed; z-index: 9999; float: none; font-size: 10px; color: #000; top: 160px; right: 0; padding: 5px 0px; background: #fff; opacity: 0.6; width: 120px; overflow: hidden; text-align: center;">'. $tech_text .'</div>';
			$text .= $this->footer_sound();
			
			echo $text;
		}	
		
		function admin_footer_text($text){
			$text .= $this->footer_sound();
			$text .= ' <div class="alignleft">';
			$text .= get_num_queries() .' queries in '. timer_stop(0, 10) .' seconds. ';
			$text .= '</div>';
			return $text;
		}
		
		function footer_sound(){
			$text = '';
			if(current_user_can('read')){
				$sounds = get_sounds_premium();
				$text .= '<div class="audio_list">';
				foreach($sounds as $sound){ 
					$mp3 = is_isset($sound,'mp3');
					$ogg = is_isset($sound,'ogg');
					$sound_id = is_isset($sound,'id');
					$sound_title = is_isset($sound,'title');
					$text .= '<audio id="sound_id_'. $sound_id .'" data-title="'. $sound_title .'">';
						$text .= '<source src="'. $mp3 .'"></source>';
						$text .= '<source src="'. $ogg .'"></source>';
					$text .= '</audio>';
				}				
				$text .= '</div>';
			}	
			return $text;
		}
	}
}