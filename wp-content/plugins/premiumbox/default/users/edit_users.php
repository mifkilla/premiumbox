<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('pn_adminpage_title_pn_edit_user') and is_admin()){
	
	add_action('pn_adminpage_title_all_edit_user', 'pn_adminpage_title_all_edit_user');
	function pn_adminpage_title_all_edit_user(){
		_e('Edit user','pn');
	}

	add_action('pn_adminpage_content_all_edit_user','def_pn_adminpage_content_all_edit_user');
	function def_pn_adminpage_content_all_edit_user(){
	global $wpdb;

		$ui = wp_get_current_user();
		
		$data_id = 0;
		$user_id = intval(is_isset($ui, 'ID'));
		$item_id = $user_id;
		if(current_user_can('edit_users') or current_user_can('administrator')){
			$item_id = intval(is_param_get('item_id'));
		}	
		$bd_data = '';
		
		if($item_id){
			$bd_data = get_userdata($item_id);
			if(isset($bd_data->ID)){
				$data_id = $bd_data->ID;
			}	
		}

		if($data_id){

			$form = new PremiumForm();

			$title = __('Edit user','pn').' "'. is_user($bd_data->user_login) .'"';

			$back_menu = array();
			$back_menu['back'] = array(
				'link' => admin_url('admin.php?page=all_users'),
				'title' => __('Back to list','pn')
			);
			if(current_user_can('administrator') or current_user_can('add_users')){
				$back_menu['add'] = array(
					'link' => admin_url('admin.php?page=all_add_user'),
					'title' => __('Add new','pn')
				);		
			}
			$form->back_menu($back_menu, '');	
			
			$options = array();	
			$options['hidden_block'] = array(
				'view' => 'hidden_input',
				'name' => 'data_id',
				'default' => $data_id,
			);		
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => $title,
				'submit' => __('Save','pn'),
			);
			$options['user_login'] = array(
				'view' => 'textfield',
				'title' => __('Login','pn'),
				'default' => is_user($bd_data->user_login),
			);	
			
			if(current_user_can('administrator') or current_user_can('promote_users')){
				if($user_id != $item_id){
					
					$roles = array();
					global $wp_roles;
					if (!isset($wp_roles)){
						$wp_roles = new WP_Roles();
					}
					if(isset($wp_roles)){ 
						foreach($wp_roles->role_names as $role => $name){
							$roles[$role] = $name;	
						}
					}			
					
					$options['user_role'] = array(
						'view' => 'select',
						'title' => __('Role','pn'),
						'options' => $roles,
						'default' => $bd_data->roles[0],
						'name' => 'user_role',
					);
					
				}
			}
			
			$options['line0'] = array(
				'view' => 'line',
			);		
			
			$options['rich_editing'] = array(
				'view' => 'checkbox',
				'title' => '',
				'second_title' => __('Disable visual editor','pn'),
				'value' => 'false',
				'default' => $bd_data->rich_editing,
				'name' => 'rich_editing',
				'work' => 'int',
			);
			$options['confirm_deletion'] = array(
				'view' => 'checkbox',
				'title' => '',
				'second_title' => __('Disable confirmation of deletion','pn'),
				'value' => '1',
				'default' => is_isset($bd_data,'confirm_deletion'),
				'name' => 'confirm_deletion',
				'work' => 'int',
			);
			$options['mini_navi'] = array(
				'view' => 'checkbox',
				'title' => '',
				'second_title' => __('Disable page count in tables in control panel','pn'),
				'value' => '1',
				'default' => is_isset($bd_data,'mini_navi'),
				'name' => 'mini_navi',
				'work' => 'int',
			);								
			
			$options['line1'] = array(
				'view' => 'line',
			);		
			
			$options['user_email'] = array(
				'view' => 'inputbig',
				'title' => __('E-mail','pn'),
				'default' => is_email($bd_data->user_email),
				'name' => 'user_email',
				'work' => 'input',
			);		
			
			$contact_methods = wp_get_user_contact_methods();
			foreach($contact_methods  as $cm_key => $cm_title){
				$options[$cm_key] = array(
					'view' => 'inputbig',
					'title' => $cm_title,
					'default' => pn_strip_input($bd_data->$cm_key),
					'name' => $cm_key,
					'work' => 'input',
				);			
			}		
			
			$options[] = array(
				'view' => 'h3',
				'title' => __('Security settings','pn'),
				'submit' => __('Save','pn'),
			);
			
			$options['user_pass'] = array(
				'view' => 'input_password',
				'title' => __('New password','pn'),
				'default' => '',
				'name' => 'user_pass',
				'work' => 'input',
			);			
			if($data_id == $user_id or current_user_can('administrator')){
				$options['delete_your_session'] = array(
					'view' => 'textfield',
					'title' => '',
					'default' => '<a href="'. pn_link('delete_your_session') .'&user_id='. $data_id .'" class="button">'. __('exit all devices','pn') .'</a>',
				);				
			}	
			$options['sec_lostpass'] = array(
				'view' => 'select',
				'title' => __('Password recovery','pn'),
				'options' => array('0'=> __('No','pn'),'1'=> __('Yes','pn')),
				'default' => intval($bd_data->sec_lostpass),
				'name' => 'sec_lostpass',
			);
			$options['alogs_email'] = array(
				'view' => 'select',
				'title' => __('Notification upon authentication','pn') .' ('. __('E-mail','pn') .')',
				'options' => array('0'=> __('No','pn'),'1'=> __('Yes','pn')),
				'default' => intval($bd_data->alogs_email),
				'name' => 'alogs_email',
			);
			$options['email_login'] = array(
				'view' => 'select',
				'title' => __('Two-factor authentication by pin-code','pn').' ('. __('E-mail','pn') .')',
				'options' => array('0'=> __('No','pn'),'1'=> __('Yes','pn')),
				'default' => intval($bd_data->email_login),
				'name' => 'email_login',
			);		
			if(current_user_can('disableip_users') or current_user_can('administrator')){
				$options['enable_ips'] = array(
					'view' => 'textarea',
					'title' => __('Allowed IP address (in new line)','pn'),
					'default' => pn_strip_input($bd_data->enable_ips),
					'name' => 'enable_ips',
					'rows' => '5',
					'work' => 'text',
				);			
			}
			
			if(current_user_can('edit_users') or current_user_can('administrator')){
				
				$options[] = array(
					'view' => 'h3',
					'title' => __('User information','pn'),
					'submit' => __('Save','pn'),
				);	
				
				$comment_count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."comment_system WHERE itemtype='user' AND item_id = '$data_id'");
				$options['system_comment'] = array(
					'view' => 'textfield',
					'title' => __('Comment','pn'),
					'default' => get_comment_label('user_comment', $data_id, $comment_count),
				);
				
				$options['user_ip'] = array(
					'view' => 'inputbig',
					'title' => __('IP','pn'),
					'default' => pn_strip_input($bd_data->user_ip),
					'name' => 'user_ip',
					'work' => 'input',
				);
				$options['user_browser'] = array(
					'view' => 'inputbig',
					'title' => __('Browser','pn'),
					'default' => pn_strip_input($bd_data->user_browser),
					'name' => 'user_browser',
					'work' => 'input',
				);			
				$options['user_bann'] = array(
					'view' => 'select',
					'title' => __('Block','pn'),
					'options' => array('0'=> __('not blocked','pn'),'1'=> __('blocked','pn')),
					'default' => intval($bd_data->user_bann),
					'name' => 'user_bann',
				);	
				
			}	
				
			$params_form = array(
				'filter' => 'all_user_editform',
				'method' => 'ajax',
				'data' => $bd_data,
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options);		
		
		} else {
			_e('Error! User is not found','pn');
		}
	}

	add_action('premium_action_delete_your_session','def_premium_action_delete_your_session');
	function def_premium_action_delete_your_session(){
		global $wpdb, $change_ld_account;
		$change_ld_account = 1;
		
		$ui = wp_get_current_user();
		$user_id = intval(is_isset($ui, 'ID'));
		$edit_user = intval(is_param_get('user_id'));

		if($user_id > 0 and $user_id == $edit_user){
			wp_destroy_all_sessions();
			$secure_cookie = is_ssl();
			wp_set_auth_cookie($user_id, true, $secure_cookie);
			wp_set_current_user($user_id);
		} elseif(current_user_can('administrator')){
			$manager = WP_Session_Tokens::get_instance($edit_user);
			$manager->destroy_all();
		}
			$url = admin_url('admin.php?page=all_edit_user&item_id='. $edit_user .'&reply=true');
			wp_redirect(get_safe_url($url));		
	}

	add_action('premium_action_all_edit_user','def_premium_action_all_edit_user');
	function def_premium_action_all_edit_user(){
	global $wpdb, $change_ld_account;	

		$change_ld_account = 1;

		only_post();

		$form = new PremiumForm();
		$form->send_header();

		pn_only_caps(array('administrator','read','edit_users'));
		
		$ui = wp_get_current_user();
		$user_id = intval(is_isset($ui, 'ID'));
		
		$data_id = $user_id;
		if(current_user_can('edit_users') or current_user_can('administrator')){
			$data_id = intval(is_param_post('data_id'));
		}	
		
		$user_data = get_userdata($data_id);
		
		if(!isset($user_data->ID)){
			$form->error_form(__('Error! User is not found','pn'));
		}
		
		$created_data = @unserialize($user_data->created_data);
		
		$new_user_data = array();
		
		$user_role = is_user_role_name(is_param_post('user_role'));
		
		if(current_user_can('administrator') or current_user_can('promote_users')){
			if($user_id != $data_id){	
			
				$roles = array();
				global $wp_roles;
				if (!isset($wp_roles)){
					$wp_roles = new WP_Roles();
				}
				if(isset($wp_roles)){ 
					foreach($wp_roles->role_names as $role => $name){
						$roles[$role] = $name;	
					}
				}
					
				if(isset($roles[$user_role])){
					$role = $user_data->roles[0];
					
					$enable = 1;

					if($role == 'administrator' and $user_role != 'administrator'){
						$enable = 0;
						$search_role = '"administrator"';
						$count_admin = $wpdb->get_var("SELECT COUNT(ID) FROM ". $wpdb->prefix ."users tbl_users LEFT OUTER JOIN ". $wpdb->prefix ."usermeta tbl_usermeta ON(tbl_users.ID = tbl_usermeta.user_id) WHERE tbl_users.ID != '$data_id' AND tbl_usermeta.meta_key = '". $wpdb->prefix ."capabilities' AND tbl_usermeta.meta_value LIKE '%{$search_role}%'");
						if($count_admin > 0){
							$enable = 1;
						}	
					}
					
					if($enable == 1){
						$u = new WP_User($data_id);
						$u->remove_role($role);
						$u->add_role($user_role);
						if($user_role == 'administrator'){
							$created_data['admin_id'] = $user_id;
							$created_data['admin_date'] = current_time('mysql');
							$created_data['admin_place'] = 'single';
						}	
					}		
				}
			}
		}	
		
		$rich_editing = trim(is_param_post('rich_editing'));
		update_user_meta($data_id, 'rich_editing', $rich_editing) or add_user_meta($data_id, 'rich_editing', $rich_editing, true);
		
		$confirm_deletion = intval(is_param_post('confirm_deletion'));
		update_user_meta($data_id, 'confirm_deletion', $confirm_deletion) or add_user_meta($data_id, 'confirm_deletion', $confirm_deletion, true);
		
		$mini_navi = intval(is_param_post('mini_navi'));
		update_user_meta($data_id, 'mini_navi', $mini_navi) or add_user_meta($data_id, 'mini_navi', $mini_navi, true);		
		
		$user_email = is_email(is_param_post('user_email'));	
		if(!$user_email){
			$form->error_form(__('Error! You have entered an incorrect e-mail','pn'));	
		}	
		
		$old_email = is_email($user_data->user_email);
		if($old_email != $user_email){
			if($user_email and email_exists($user_email)){
				$form->error_form(__('Error! This e-mail is already in use','pn'));
			}	
			$new_user_data['user_email'] = $user_email;
		}
				
		$contact_methods = wp_get_user_contact_methods();
		foreach($contact_methods  as $cm_key => $cm_title){
			$um_value = strip_uf(is_param_post($cm_key), $cm_key);
			update_user_meta($data_id, $cm_key, $um_value) or add_user_meta($data_id, $cm_key, $um_value, true);		
		}		
		
		if(current_user_can('edit_users') or current_user_can('administrator')){
			$new_user_data['user_bann'] = intval(is_param_post('user_bann'));
			$new_user_data['user_ip'] = pn_maxf_mb(pn_strip_input(is_param_post('user_ip')),500);
			$new_user_data['user_browser'] = pn_maxf_mb(pn_strip_input(is_param_post('user_browser')),500);
		}	
		
		$new_user_data['sec_lostpass'] = intval(is_param_post('sec_lostpass'));
		$new_user_data['alogs_email'] = intval(is_param_post('alogs_email'));
		$new_user_data['email_login'] = intval(is_param_post('email_login'));
		if(current_user_can('disableip_users') or current_user_can('administrator')){
			$new_user_data['enable_ips'] = pn_maxf(pn_strip_input(is_param_post('enable_ips')),1500);
		}
		$new_user_data = apply_filters('all_user_editform_post', $new_user_data, $data_id, $user_data); 
		
		if(count($new_user_data) > 0){
			$new_user_data['created_data'] = @serialize($created_data);
			$wpdb->update($wpdb->prefix.'users', $new_user_data, array('ID'=>$data_id));
		}	
		
		do_action('all_user_editform_edit', $data_id, $new_user_data, $user_data);

		$user_pass = is_password(is_param_post('user_pass'));
		if($user_pass){
			wp_set_password($user_pass, $data_id);
			if($user_id == $data_id){
				$secure_cookie = is_ssl();
				wp_set_auth_cookie($user_id, true, $secure_cookie);
				wp_set_current_user($user_id);
			}
		}	
		
		$url = admin_url('admin.php?page=all_edit_user&item_id='. $data_id .'&reply=true');
		$form->answer_form($url);
	}	
}