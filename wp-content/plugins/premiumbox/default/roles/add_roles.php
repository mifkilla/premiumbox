<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_all_add_roles', 'pn_admin_title_all_add_roles');
	function pn_admin_title_all_add_roles(){
		$id = is_user_role_name(is_param_get('item_key'));
		if($id){
			_e('Edit user role','pn');
		} else {
			_e('Add user role','pn');
		}
	}

	add_action('pn_adminpage_content_all_add_roles','def_pn_admin_content_all_add_roles');
	function def_pn_admin_content_all_add_roles(){
	global $wpdb;

		$id = is_user_role_name(is_param_get('item_key'));
		$data_id = '';
		
		$prefix = $wpdb->prefix;
		
		global $wp_roles;
		if(!isset($wp_roles)){
			$wp_roles = new WP_Roles();
		}	
		
		$data = array();
		
		if(is_array($wp_roles->role_names)){
			foreach($wp_roles->role_names as $role_key => $role_title){
				if($id == $role_key){
					$data_id = $role_key;
					$data = array(
						'title' => $role_title,
						'key' => $role_key,
					);
				}
			}
		}	
		
		if($data_id){
			$title = __('Edit user role','pn') . ' "' . is_isset($data, 'key') . '"';
		} else {
			$title = __('Add user role','pn');
		}
		
		$form = new PremiumForm();
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=all_roles'),
			'title' => __('Back to list','pn')
		);
		if($data_id){
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=all_add_roles'),
				'title' => __('Add new','pn')
			);	
		}
		
		$form->back_menu($back_menu, $data); 

		$options = array();
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'item_key',
			'default' => $data_id,
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save','pn'),
		);	
		$options['title'] = array(
			'view' => 'inputbig',
			'title' => __('Role name','pn'),
			'default' => is_isset($data, 'title'),
			'name' => 'title',
		);	
		if(!$data_id){
			$options['key'] = array(
				'view' => 'inputbig',
				'title' => __('System role name','pn'),
				'default' => is_isset($data, 'key'),
				'name' => 'key',
			);	
		}
		$params_form = array(
			'filter' => '',
			'method' => 'ajax',
		);
		$form->init_form($params_form, $options);
	} 

	add_action('premium_action_all_add_roles','def_premium_action_all_add_roles');
	function def_premium_action_all_add_roles(){
	global $wpdb;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));	

		$prefix = $wpdb->prefix;
		
		$data_key = is_user_role_name(is_param_post('item_key'));
		
		$role_key = is_user_role_name(is_param_post('key'));
		$role_title = pn_strip_input(is_param_post('title'));
		if(!$role_title){ $role_title = $role_key; }
				
		$list_roles = array();
		
		global $wp_roles;
		if (!isset($wp_roles)){
			$wp_roles = new WP_Roles();
		}	
		
		if(is_array($wp_roles->role_names)){
			foreach($wp_roles->role_names as $key => $title){
				$list_roles[] = $key;
			}
		}
		
		if($data_key and in_array($data_key, $list_roles)){
			
			$wp_user_roles = get_option($prefix . 'user_roles');
			if(isset($wp_user_roles[$data_key])){
				$wp_user_roles[$data_key]['name'] = $role_title;
			}
			update_option($prefix.'user_roles', $wp_user_roles);							

		} else {
			if(!$role_key){ $form->error_form(__('You did not enter a system role name','pn')); }
			if(in_array($role_key, $list_roles) or $role_key == 'admin'){ $form->error_form(__('Role with this name exists','pn')); }
					
			$ncap = array();
					
			$result = add_role($role_key, $role_title, array());
			$data_key = $role_key;
		}	

		$url = admin_url('admin.php?page=all_add_roles&item_key='. $data_key .'&reply=true');
		$form->answer_form($url);
	}
}	