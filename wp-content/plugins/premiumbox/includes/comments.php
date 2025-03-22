<?php 
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('pn_close_comment_section_all')){
	add_action('init', 'pn_close_comment_section_all', 0);
	function pn_close_comment_section_all(){	
		$data = premium_rewrite_data();
		if($data['super_base'] == 'wp-comments-post.php'){
			pn_display_mess('Page does not exist');
		}
	}
}

if(!function_exists('premium_comment_reply_link')){
	add_filter('comment_reply_link', 'premium_comment_reply_link', 10, 2);
	function premium_comment_reply_link($link, $args){
		if(strstr($link,'comment-reply-login')){
			$link = $args['before'] . '<span class="comment-reply-login">' . strip_tags($link) . '</span>' . $args['after'];
		}
		return $link;
	}
}

if(!function_exists('comment_placed_form')){
	add_filter('placed_form', 'comment_placed_form', 0);
	function comment_placed_form($placed){
		if(!defined('PN_COMMENT_STATUS') or PN_COMMENT_STATUS == 'true'){
			$placed['commentform'] = __('Comment form','pn');
		}
		return $placed;
	}
}

if(!function_exists('comment_all_settings_option')){
	add_filter('all_settings_option', 'comment_all_settings_option', 100);
	function comment_all_settings_option($options){
		if(!defined('PN_COMMENT_STATUS') or PN_COMMENT_STATUS == 'true'){
			
			$options['comment_line'] = array(
				'view' => 'line',
			);
			
			$plugin = get_plugin_class();
			
			$args = array('public' => 1);
			$post_types = get_post_types($args, 'objects');
			foreach($post_types as $post_data){
				$post_type = is_isset($post_data, 'name');
				if($post_type != 'attachment'){
					$post_label = is_isset($post_data, 'label');
					$hierarchical = intval(is_isset($post_data, 'hierarchical'));
					if($hierarchical == 0){
						$options[$post_type . '_comment'] = array(
							'view' => 'select',
							'title' => sprintf(__('Comments from "%s"','pn'), $post_label),
							'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
							'default' => $plugin->get_option('comment', $post_type. '_comment'),
							'name' => $post_type . '_comment',
						);
					}
				}
			}	
		}
		return $options;
	}	
}

if(!function_exists('comment_all_settings_option_post')){
	add_action('all_settings_option_post', 'comment_all_settings_option_post', 100);
	function comment_all_settings_option_post($data){
		if(!defined('PN_COMMENT_STATUS') or PN_COMMENT_STATUS == 'true'){
			$plugin = get_plugin_class();
			
			$args = array('public' => 1);
			$post_types = get_post_types($args, 'objects');
			foreach($post_types as $post_data){
				$post_type = is_isset($post_data, 'name');
				if($post_type != 'attachment'){
					$hierarchical = intval(is_isset($post_data, 'hierarchical'));
					if($hierarchical == 0){
						$plugin->update_option('comment', $post_type. '_comment', intval(is_param_post($post_type. '_comment')));
					}
				}
			}	
		}
	}
}

if(!function_exists('def_post_type_opencomment')){
	add_filter('post_type_opencomment', 'def_post_type_opencomment', 10, 2);
	function def_post_type_opencomment($status, $post_type){
		$post_type = pn_string($post_type);
		if(defined('PN_COMMENT_STATUS') and PN_COMMENT_STATUS == 'false'){
			return 'close';
		}
		$plugin = get_plugin_class();
		if($plugin->get_option('comment', $post_type.'_comment') != 1){
			return 'close';
		}
		return $status;
	}
}

if(!function_exists('def_hide_commentsdiv')){
	add_action('admin_menu', 'def_hide_commentsdiv', 1000);
	function def_hide_commentsdiv(){
		$status = apply_filters('post_type_opencomment', 'open', 'post');
		if($status == 'close'){
			remove_meta_box('commentsdiv', 'post', 'normal');
		}
		$status = apply_filters('post_type_opencomment', 'open', 'page');
		if($status == 'close'){
			remove_meta_box('commentsdiv', 'page', 'normal');
		}			
	}
}

if(!function_exists('def_premium_siteaction_commentform')){
	add_action('premium_siteaction_commentform', 'def_premium_siteaction_commentform');
	function def_premium_siteaction_commentform(){
		global $or_site_url, $wpdb;	
		
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
		
		$log = apply_filters('before_ajax_form_field', $log, 'commentform');
		$log = apply_filters('before_ajax_commentform', $log);
		
		$parallel_error_output = get_parallel_error_output();
		
		$author = pn_maxf_mb(pn_strip_input(is_param_post('author')), 250);
		$email = is_email(is_param_post('email'));
		$url = pn_maxf_mb(pn_strip_input(is_param_post('url')), 250);
		$comment = pn_maxf_mb(pn_strip_input(is_param_post('comment')), 2000);
		$comment_post_ID = intval(is_param_post('comment_post_ID'));
		
		$field_errors = array();
		
		if($user_id < 0){
			if(mb_strlen($author) < 2){
				$field_errors[] = __('Error! You must enter your name','pn');	
			}
			if(count($field_errors) == 0 or $parallel_error_output == 1){
				if(!$email){
					$field_errors[] = __('Error! You must enter your e-mail','pn');
				}	
			}
		}		
		
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			if(mb_strlen($comment) < 3){
				$field_errors[] = __('Error! You must enter a message','pn');
			}
		}
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			if($comment_post_ID < 1){
				$field_errors[] = __('Error! Post not found','pn');
			}
		}
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			if($comment_post_ID > 0){
				$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."posts WHERE ID = '$comment_post_ID'");
				$post_type_opencomment = apply_filters('post_type_opencomment', $item->comment_status, $item->post_type);
				if($post_type_opencomment != 'open'){
					$field_errors[] = __('Error! Comments closed','pn');
				}	
			}
		}		
		
		if(count($field_errors) == 0){
			
			$comment_data = array();
			$comment_data['comment_post_ID'] = $comment_post_ID;
			$comment_data['comment_parent'] = intval(is_param_post('comment_parent'));
			$comment_data['author'] = $author;
			$comment_data['email'] = $email;
			$comment_data['url'] = $url;
			$comment_data['comment'] = $comment;

			$comment = wp_handle_comment_submission($comment_data);
			if(is_wp_error($comment)){
				$data = intval($comment->get_error_data());
				if(!empty($data)){
					$log['status'] = 'error';
					$log['status_code'] = 1;
					$log['status_text'] = $comment->get_error_message();
					echo json_encode($log);
					exit;
				} else {
					$log['status'] = 'error';
					$log['status_code'] = 1;
					$log['status_text'] = __('Error! Comments bd error','pn');
					echo json_encode($log);
					exit;
				}
			}
			
			$user = $ui;
			$cookies_consent = 1;
			
			do_action('set_comment_cookies', $comment, $user, $cookies_consent);
			
			$location = empty($_POST['redirect_to']) ? get_comment_link( $comment ) : $_POST['redirect_to'] . '#comment-' . $comment->comment_ID;
			
			$location = add_query_arg(
				array(
					'comment_time' => current_time('timestamp'),
				),
				$location
			);			
			
			if ( 'unapproved' === wp_get_comment_status( $comment ) && ! empty( $comment->comment_author_email ) ) {
				$location = add_query_arg(
					array(
						'unapproved'      => $comment->comment_ID,
						'moderation-hash' => wp_hash($comment->comment_date_gmt),
					),
					$location
				);
			}
			
			$location = apply_filters( 'comment_post_redirect', $location, $comment );
			
			$log['status'] = 'success';	
			$log['url'] = get_safe_url($location);
			$log['clear'] = 1;
			$log['status_text'] = apply_filters('commentform_success_message',__('Your comment has been successfully add','pn'));		
			
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = join("<br />", $field_errors);
		}
		
		echo json_encode($log);
		exit;
	}
}