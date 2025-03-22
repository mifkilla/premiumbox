<?php
if( !defined( 'ABSPATH')){ exit(); } 

if(!function_exists('delete_user_userverify')){
	add_action('delete_user', 'delete_user_userverify');
	function delete_user_userverify($user_id){
	global $wpdb;
		$usves = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."verify_bids WHERE user_id = '$user_id'");
		foreach($usves as $item){
			$id = $item->id;
			$res = apply_filters('item_usve_delete_before', pn_ind(), $id, $item);
			if($res['ind'] == 1){
				$result = $wpdb->query("DELETE FROM ". $wpdb->prefix ."verify_bids WHERE id = '$id'");
				do_action('item_usve_delete', $id, $item, $result);
			}
		}
	}
}

if(!function_exists('userverify_icon_indicators')){
	add_filter('list_icon_indicators', 'userverify_icon_indicators');
	function userverify_icon_indicators($lists){
		$plugin = get_plugin_class();
		$lists['userverify'] = array(
			'title' => __('Requests for identity verification','pn'),
			'img' => $plugin->plugin_url .'images/userverify.png',
			'link' => admin_url('admin.php?page=all_usve&filter=1')
		);
		return $lists;
	}
}

if(!function_exists('def_icon_indicator_userverify')){
	add_filter('count_icon_indicator_userverify', 'def_icon_indicator_userverify');
	function def_icon_indicator_userverify($count){
		global $wpdb;
		if(current_user_can('administrator') or current_user_can('pn_userverify')){
			$count = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."verify_bids WHERE auto_status = '1' AND status = '1'");
		}	
		return $count;
	}
}

if(!function_exists('userverify_user_discount')){	
	add_filter('user_discount','userverify_user_discount',99,3);
	function userverify_user_discount($sk, $user_id, $ui){
		$plugin = get_plugin_class();	
		if($user_id){
			if(isset($ui->user_verify) and $ui->user_verify == 1){
				$verifysk = is_sum($plugin->get_option('usve','verifysk'));
				$sk = $sk + $verifysk;
			}
		}
		return $sk;
	}  
}

if(!function_exists('clear_visible_userverify')){
	function clear_visible_userverify(){
		$plugin = get_plugin_class();
		if(!$plugin->is_up_mode()){
			$path = $plugin->upload_dir .'usveshow/';
			full_del_dir($path);
		}
	}
}

if(!function_exists('delete_last_userverify_list_cron_func')){
	add_filter('list_cron_func', 'delete_last_userverify_list_cron_func');
	function delete_last_userverify_list_cron_func($filters){
		
		$filters['clear_visible_userverify'] = array(
			'title' => __('Delete temporary identity verification files','pn'),
			'site' => '1hour',
		);		
		
		return $filters;
	}
}

/* users */
if(!function_exists('userverify_pntable_trclass_all_users')){
	add_filter("pntable_trclass_all_users", 'userverify_pntable_trclass_all_users', 10, 2);
	function userverify_pntable_trclass_all_users($tr_class, $item){
		if(is_isset($item, 'user_verify') == 1){
			$tr_class[] = 'tr_green';
		}
		return $tr_class;
	}	
}

if(!function_exists('userverify_pntable_bulkactions_all_users')){
	add_filter("pntable_bulkactions_all_users", 'userverify_pntable_bulkactions_all_users');
	function userverify_pntable_bulkactions_all_users($actions){
		$new_actions = array(
			'verify'    => __('Verified','pn'),
			'unverify'    => __('Unverified','pn'),	
		);
		if(current_user_can('administrator') or current_user_can('pn_userverify')){
			$actions = pn_array_insert($actions, 'delete', $new_actions, 'before');
		}
		return $actions;
	}
}

if(!function_exists('pntable_users_action_verify')){
	add_action('pntable_users_action', 'pntable_users_action_verify', 10, 2);
	function pntable_users_action_verify($action, $post_ids){
	global $wpdb;	
		if(current_user_can('administrator') or current_user_can('pn_userverify')){
			if($action == 'verify'){		
				foreach($post_ids as $id){
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."users WHERE ID='$id' AND user_verify != '1'");
					if(isset($item->ID)){
						$wpdb->query("UPDATE ".$wpdb->prefix."users SET user_verify = '1' WHERE ID = '$id'");
						do_action('item_users_verify', $id, $item);
					}
				}									
			}
			if($action == 'unverify'){		
				foreach($post_ids as $id){
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."users WHERE ID='$id' AND user_verify != '0'");
					if(isset($item->ID)){
						$wpdb->query("UPDATE ".$wpdb->prefix."users SET user_verify = '0' WHERE ID = '$id'");
						do_action('item_users_unverify', $id, $item);
					}
				}								
			}	
		}
	}
}

if(!function_exists('userverify_pntable_submenu_all_users')){
	add_filter("pntable_submenu_all_users", 'userverify_pntable_submenu_all_users', 10, 3);
	function userverify_pntable_submenu_all_users($options){
		$options['filter'] = array(
			'options' => array(
				'1' => __('verified users','pn'),
				'2' => __('unverified users','pn'),
			),
		);
		return $options;
	}
}

if(!function_exists('userverify_pntable_searchwhere_all_users')){
	add_filter("pntable_searchwhere_all_users", 'userverify_pntable_searchwhere_all_users');
	function userverify_pntable_searchwhere_all_users($where){
		$filter = intval(is_param_get('filter'));
		if($filter == 1){
			$where .= " AND tbl_users.user_verify = '1'";
		} elseif($filter == 2){
			$where .= " AND tbl_users.user_verify = '0'";
		}
		return $where;
	}
}

if(!function_exists('userverify_pntable_columns_all_users')){
	add_filter("pntable_columns_all_users", 'userverify_pntable_columns_all_users', 100);
	function userverify_pntable_columns_all_users($columns){
		$columns['user_verify'] = __('Identity verification','pn');
		return $columns;
	}
}

if(!function_exists('userverify_pntable_column_all_users')){
	add_filter("pntable_column_all_users", 'userverify_pntable_column_all_users', 10, 3);
	function userverify_pntable_column_all_users($return, $column_name,$item){
		if($column_name == 'user_verify'){
			if(isset($item->user_verify) and $item->user_verify == 1){
				return '<span class="bgreen">'. __('verified','pn') .'</span>';
			} else {
				return '<span class="bred">'. __('not verified','pn') .'</span>';
			}		
		}
		return $return;
	}
}

if(!function_exists('verify_all_user_editform')){
	add_filter('all_user_editform', 'verify_all_user_editform', 1000, 2);
	function verify_all_user_editform($options, $bd_data){
		global $wpdb;
		
		$user_id = $bd_data->ID;
		
		if(current_user_can('administrator') or current_user_can('pn_userverify')){ 
			$options['user_verify_h3'] = array(
				'view' => 'h3',
				'title' => __('Verification','pn'),
				'submit' => __('Save','pn'),
			);	
			$options['user_verify'] = array(
				'view' => 'select',
				'title' => __('Status','pn'),
				'options' => array('0'=> __('not verified','pn'), '1'=> __('verified','pn')),
				'default' => intval($bd_data->user_verify),
				'name' => 'user_verify',
			);		
			
			$uv_files = '';
			if($bd_data->user_verify == 1){
				$fields = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."uv_field LEFT OUTER JOIN ". $wpdb->prefix ."uv_field_user ON(".$wpdb->prefix."uv_field.id = ". $wpdb->prefix ."uv_field_user.uv_field) WHERE user_id='$user_id' AND ".$wpdb->prefix."uv_field.fieldvid='1' ORDER BY uv_order ASC");
				foreach($fields as $field){
					$uv_files .= '<div><strong>'. pn_strip_input(ctv_ml($field->title)).':</strong> <a href="'. get_usve_doc_view($field->id) .'" target="_blank">'. __('View','pn') .'</a> | <a href="'. get_usve_doc($field->id) .'" target="_blank">'. __('Download','pn') .'</a></div>';
				}
			}
			if($uv_files){
				$options['verification_files'] = array(
					'view' => 'textfield',
					'title' => __('Verification files','pn'),
					'default' => $uv_files,
				);	
			}
		}
		
		return $options;
	}
}

if(!function_exists('verify_all_user_editform_post')){
	add_filter('all_user_editform_post', 'verify_all_user_editform_post', 10, 3); 
	function verify_all_user_editform_post($new_user_data, $user_id, $user_data){
		global $wpdb;
		if(current_user_can('administrator') or current_user_can('pn_userverify')){ 
			$new_user_data['user_verify'] = $user_verify = intval(is_param_post('user_verify'));
			$old_user_verify = intval(is_isset($user_data, 'user_verify'));
			if($user_verify == 1 and $old_user_verify == 0){
				do_action('item_users_verify', $user_id, $user_data);
			}
			if($user_verify == 0 and $old_user_verify == 1){
				do_action('item_users_unverify', $user_id, $user_data);
			}			
		}
		return $new_user_data;
	}
}

if(!function_exists('pn_verify_uv')){
	function pn_verify_uv($key){
		$plugin = get_plugin_class();	
		$uf = $plugin->get_option('usve','verify_fields');
		return intval(is_isset($uf, $key));
	}
}

if(!function_exists('userverify_disabled_account_form_line')){
	add_filter('disabled_account_form_line', 'userverify_disabled_account_form_line',99,3);
	function userverify_disabled_account_form_line($disabled,$name, $ui){
		if(isset($ui->user_verify) and $ui->user_verify == 1){
			if(pn_verify_uv($name)){
				return 1;
			}
		}
		return $disabled;
	}
}