<?php
if( !defined( 'ABSPATH')){ exit(); }

$plugin = get_plugin_class();

add_filter('admin_footer_text', '__return_false', 0);

if($plugin->get_option('admin', 'w0') == 1){
	remove_action('welcome_panel','wp_welcome_panel');
}

add_action('premium_post', 'premium_post_send_frame_options_header'); 
function premium_post_send_frame_options_header($method=''){
	if($method and $method == 'post'){
		send_frame_options_header();
	}
}

add_action('premium_login_init', 'premium_login_init_send_frame_options_header');
function premium_login_init_send_frame_options_header(){
	send_frame_options_header();
}

add_action('wp_dashboard_setup', 'pn_remove_dashboard_widgets');
function pn_remove_dashboard_widgets() {
	$plugin = get_plugin_class();

	remove_meta_box('dashboard_site_health', 'dashboard', 'normal');

	if($plugin->get_option('admin','w1') == 1){
		remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); 
	}
	if($plugin->get_option('admin','w2') == 1){
		remove_meta_box('dashboard_activity', 'dashboard', 'normal'); 
	}	
	if($plugin->get_option('admin','w3') == 1){
		remove_meta_box('dashboard_quick_press', 'dashboard', 'side'); 
	}	
	if($plugin->get_option('admin','w4') == 1 or function_exists('is_ml') and is_ml()){
		remove_meta_box('dashboard_primary', 'dashboard', 'side'); 
	}	
	if($plugin->get_option('admin','w5') == 1){
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	}
	if($plugin->get_option('admin','w6') == 1){
		remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
	}
	if($plugin->get_option('admin','w7') == 1){
		remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
	}	
	if($plugin->get_option('admin','w8') == 1){
		remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
	}
	
	remove_meta_box('dashboard_secondary', 'dashboard', 'side');
}

add_action( 'widgets_init', 'pn_remove_default_widget' );
function pn_remove_default_widget() {
    unregister_widget('WP_Widget_RSS');
	unregister_widget('WP_Widget_Calendar');
	unregister_widget('WP_Widget_Tag_Cloud');		
	unregister_widget('WP_Nav_Menu_Widget');
    unregister_widget('WP_Widget_Recent_Posts');
	unregister_widget('WP_Widget_Pages');
	unregister_widget('WP_Widget_Archives');		
	unregister_widget('WP_Widget_Meta');	
	unregister_widget('WP_Widget_Search');
	if(defined('PN_COMMENT_STATUS') and constant('PN_COMMENT_STATUS') != 'true'){
		unregister_widget('WP_Widget_Recent_Comments');
	}	
	unregister_widget('WP_Widget_Categories');		
}

add_action('admin_init', 'pn_close_admin_mail');
function pn_close_admin_mail(){
	add_filter('wp_new_user_notification_email_admin', 'pn_wp_new_user_notification_email_admin');
	remove_action('personal_options_update', 'send_confirmation_on_profile_email');		
}

function pn_wp_new_user_notification_email_admin($wp_new_user_notification_email_admin){
	if(isset($wp_new_user_notification_email_admin['to'])){
		unset($wp_new_user_notification_email_admin['to']);
	}
	return $wp_new_user_notification_email_admin;
}

add_filter('send_password_change_email', 'def_send_password_change_email', 1, 3);
function def_send_password_change_email($send, $user, $userdata){
	if(isset($userdata['ID']) and !user_can( $userdata['ID'], 'administrator' )){
		return false;
	} 		
	return $send;	
}

add_action('admin_init', 'remove_sitehealth_admin_init');
function remove_sitehealth_admin_init(){
	$data = premium_rewrite_data();
	$s_base = $data['super_base'];
	$delete_pages = array('site-health.php','site-health-info.php');
	if(in_array($s_base, $delete_pages)){
		pn_display_mess(__('Page does not exist','pn'));
	}	
}

add_action( 'admin_menu', 'sitehealth_remove_meta_boxes', 1000);
function sitehealth_remove_meta_boxes() {
global $menu, $submenu;
	$s_restricted = array();
	$s_restricted[] = 'site-health.php';
	if(is_array($submenu)){
		foreach($submenu as $smenu_key => $smenu_data){
			foreach($smenu_data as $sm_key => $sm_data){
				$menu_data_key = is_isset($sm_data, 2);
				if(in_array($menu_data_key,$s_restricted)){
					unset($submenu[$smenu_key][$sm_key]);
				}	
			}
		}
	}	
}

add_action( 'admin_menu', 'pn_remove_meta_boxes', 1000);
function pn_remove_meta_boxes() {
global $menu, $submenu;
	
	$plugin = get_plugin_class();
	
	if(function_exists('is_ml') and is_ml()){
		remove_meta_box('postexcerpt', 'post', 'normal');
	}
	remove_meta_box('trackbacksdiv', 'post', 'normal');
	remove_meta_box('postcustom', 'post', 'normal');
	remove_meta_box('trackbacksdiv', 'page', 'normal');
	remove_meta_box('postcustom', 'page', 'normal');
	
	$restricted = array();
	$s_restricted = array();
	if($plugin->get_option('admin','ws0') == 1){
		$restricted[] = 'edit.php';
	}
	if(defined('PN_COMMENT_STATUS') and constant('PN_COMMENT_STATUS') != 'true'){
		$restricted[] = 'edit-comments.php';
		$s_restricted[] = 'options-discussion.php';
	}	
	if($plugin->get_option('admin','ws2') == 1){
		$restricted[] = 'upload.php';
	}
	if($plugin->get_option('admin','ws3') == 1){
		$restricted[] = 'tools.php';
	}	
	if($plugin->get_option('admin','ws4') == 1){
		$s_restricted[] = 'options-media.php';
	}	
	if($plugin->get_option('admin','ws5') == 1){
		$s_restricted[] = 'options-privacy.php';
	}	
	if($plugin->get_option('admin','ws6') == 1){
		$s_restricted[] = 'options-writing.php';
	}	
	
	if(is_array($menu)){
		foreach($menu as $menu_key => $menu_data){
			$menu_data_key = is_isset($menu_data, 2);
			if(in_array($menu_data_key,$restricted)){
				unset($menu[$menu_key]);
			}			
		}
	}

	if(is_array($submenu)){
		foreach($submenu as $smenu_key => $smenu_data){
			foreach($smenu_data as $sm_key => $sm_data){
				$menu_data_key = is_isset($sm_data, 2);
				if(in_array($menu_data_key,$s_restricted)){
					unset($submenu[$smenu_key][$sm_key]);
				}	
			}
		}
	}	
}

remove_action( 'wp_head', 'wp_generator' );

foreach(array( 'rss2_head', 'commentsrss2_head', 'rss_head', 'rdf_header', 'atom_head', 'comments_atom_head', 'opml_head', 'app_head' ) as $action){
	remove_action( $action, 'the_generator' );
}

add_filter('rest_enabled', '__return_false');
remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
remove_action( 'wp_head', 'rest_output_link_wp_head', 10, 0 );
remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );
remove_action( 'auth_cookie_malformed', 'rest_cookie_collect_status' );
remove_action( 'auth_cookie_expired', 'rest_cookie_collect_status' );
remove_action( 'auth_cookie_bad_username', 'rest_cookie_collect_status' );
remove_action( 'auth_cookie_bad_hash', 'rest_cookie_collect_status' );
remove_action( 'auth_cookie_valid', 'rest_cookie_collect_status' );
remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
remove_action( 'init', 'rest_api_init' );
remove_action( 'rest_api_init', 'rest_api_default_filters', 10, 1 );
remove_action( 'parse_request', 'rest_api_loaded' );
remove_action( 'rest_api_init', 'wp_oembed_register_route');
remove_filter( 'rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4 );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'rsd_link');
remove_action( 'wp_head', 'wlwmanifest_link');

add_action('wp_before_admin_bar_render', 'pn_admin_bar_links');
function pn_admin_bar_links(){
global $wp_admin_bar;

	$plugin = get_plugin_class();

    $wp_admin_bar->remove_menu('wp-logo'); 
	$wp_admin_bar->remove_menu('new-media');
	$wp_admin_bar->remove_menu('new-link');
	$wp_admin_bar->remove_menu('themes');
	$wp_admin_bar->remove_menu('search');
	$wp_admin_bar->remove_menu('customize');
	
	if($plugin->get_option('admin','ws0') == 1){
		$wp_admin_bar->remove_menu('new-post');
	}
	if(defined('PN_COMMENT_STATUS') and constant('PN_COMMENT_STATUS') != 'true'){
		$wp_admin_bar->remove_menu('comments');
	}
}

add_filter('the_content', 'do_shortcode', 10);		
add_filter('comment_text', 'do_shortcode', 10);

add_action( 'parse_query', 'pn_search_turn_off' );
function pn_search_turn_off( $q, $e = true ) {
	if(is_search()) {
		$q->is_search = false;
		$q->query_vars['s'] = false;
		$q->query['s'] = false;	
		if ( $e == true ){
			$q->is_404 = true;
		}
	}
}

add_filter('get_search_form', 'def_get_search_form');
function def_get_search_form(){
	return null;
}
 
function disable_all_feeds() {
	pn_display_mess(__('RSS feed is off','pn'));
}

if($plugin->get_option('admin','wm0') == 1){
	add_action('do_feed', 'disable_all_feeds', 1);
	add_action('do_feed_rdf', 'disable_all_feeds', 1);
	add_action('do_feed_rss', 'disable_all_feeds', 1);
	add_action('do_feed_rss2', 'disable_all_feeds', 1);
	add_action('do_feed_atom', 'disable_all_feeds', 1);
}