<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_all_setting_roles', 'def_pn_adminpage_title_all_setting_roles');
	function def_pn_adminpage_title_all_setting_roles(){
		_e('User roles','pn');
	}

	add_action('pn_adminpage_content_all_setting_roles','def_pn_adminpage_content_all_setting_roles');
	function def_pn_adminpage_content_all_setting_roles(){
	global $wpdb;

		$prefix = $wpdb->prefix;
		
		global $wp_roles;
		if(!isset($wp_roles)){
			$wp_roles = new WP_Roles();
		}
		
		$selects = array();
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_setting_roles"),
			'title' => '--' . __('Make a choice','pn') . '--',
			'default' => '',
		);		
		
		$places = array();
		$place = is_param_get('place');
		$role_title = '';
		if(isset($wp_roles)){ 
			foreach($wp_roles->role_names as $role => $name){
				if($role != 'administrator'){
					if($place == $role){
						$role_title = $name;
					}
					$places[] = $role;
					$selects[] = array(
						'link' => admin_url("admin.php?page=all_setting_roles&place=" . $role),
						'title' => $name,
						'default' => $role,
					);				
				}	
			}
		}	
		
		$form = new PremiumForm();
		
		$form->select_box($place, $selects, __('Setting up','pn'));

		if(in_array($place,$places)){
			$pn_caps = get_pn_capabilities();	
			$capabilities = $wp_roles->roles[$place]['capabilities'];

			$options = array();
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => $role_title,
				'submit' => __('Save','pn'),
			);
			$options[] = array(
				'view' => 'hidden_input',
				'name' => 'role',
				'default' => $place,
			);		
			if(is_array($pn_caps)){
				foreach($pn_caps as $key => $val){			
					$default = 0;
					if(isset($capabilities[$key])){
						$default = 1;	
					}	
					if($key == 'list_users'){
						$options[] = array(
							'view' => 'line',
						);							
					}				
					$options[$key] = array(
						'view' => 'select',
						'title' => $val,
						'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
						'default' => $default,
						'name' => $key,
						'work' => 'int',
					);												
				}
			}				
			$params_form = array(
				'method' => 'ajax',
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options);
		}
	} 

	add_action('premium_action_all_setting_roles','def_premium_action_all_setting_roles');
	function def_premium_action_all_setting_roles(){
	global $wpdb;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
			
		$role = is_param_post('role');
		$prefix = $wpdb->prefix;
		
		global $wp_roles;
		if(!isset($wp_roles)){
			$wp_roles = new WP_Roles();
		}
		
		$roles = array();	
		if(isset($wp_roles)){
			foreach($wp_roles->role_names as $role_key => $name){
				if($role_key != 'administrator'){
					$roles[] = $role_key;
				}	
			}
		}			
		
		if(in_array($role,$roles)){ 
			$pn_caps = get_pn_capabilities();
			$capabilities = array('level_0' => '1');

			foreach($pn_caps as $key => $val){
				$value = is_param_post($key);
				if($value == 1){	
					$capabilities[$key] = 1;
				}
			} 
					
			$roles = get_option($prefix. 'user_roles');
			$roles[$role]['capabilities'] = $capabilities;
			$roles = serialize($roles);
			$wpdb->update( $prefix.'options' , array('option_value' => $roles), array('option_name' => $prefix.'user_roles'));
					
			$back_url = is_param_post('_wp_http_referer');
			$back_url .= '&reply=true';

			$form->answer_form($back_url);
				
		} else {
			$form->error_form(__('Error! This role do not exist!','pn'));
		}
	}
}	