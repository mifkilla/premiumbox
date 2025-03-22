<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_usve_settings')){
		add_action('pn_adminpage_title_all_usve_settings', 'def_adminpage_title_all_usve_settings');
		function def_adminpage_title_all_usve_settings(){
			_e('Settings','pn');
		}
	} 

	if(!function_exists('def_adminpage_content_all_usve_settings')){
		add_action('pn_adminpage_content_all_usve_settings','def_adminpage_content_all_usve_settings');
		function def_adminpage_content_all_usve_settings(){
			$plugin = get_plugin_class();
			
			$form = new PremiumForm();
			
			$options = array();
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => __('Settings','pn'),
				'submit' => __('Save','pn'),
			);					
			$options['status'] = array(
				'view' => 'select',
				'title' => __('Allow send request','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => $plugin->get_option('usve','status'),
				'name' => 'status',
			);			
			$options['verifysk'] = array(
				'view' => 'inputbig',
				'title' => __('Additional discount for verified users','pn').' (%)',
				'default' => $plugin->get_option('usve','verifysk'),
				'name' => 'verifysk',
			);
			$options['create_not'] = array(
				'view' => 'select',
				'title' => __('Allow creating orders if user not verified','pn'),
				'default' => $plugin->get_option('usve','create_not'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'name' => 'create_not',
			);			
			$options['line1'] = array(
				'view' => 'line',
			);
			$options['text'] = array(
				'view' => 'editor',
				'title' => __('Message on a verification page', 'pn'),
				'default' => $plugin->get_option('usve','text'),
				'rows' => '15',
				'standart_tags' => 1, 
				'name' => 'text',
				'work' => 'text',
				'ml' => 1,
			);	
			$options['line2'] = array(
				'view' => 'line',
			);
			$options['canceltext'] = array(
				'view' => 'editor',
				'title' => __('Verification denial response template', 'pn'),
				'default' => $plugin->get_option('usve','canceltext'),
				'rows' => '15',
				'standart_tags' => 1, 
				'name' => 'canceltext',
				'work' => 'text',
				'ml' => 1,
			);	
			$options['line3'] = array(
				'view' => 'line',
			);			
			
			$uf = $plugin->get_option('usve','verify_fields');
			
			$cf_auto = array();
			$cf_auto_list = apply_filters('user_fields_in_website', array());
			foreach($cf_auto_list as $cf_k => $cf_v){
				$cf_auto[$cf_k] = is_isset($cf_v, 'title');
			}
			
			foreach($cf_auto as $field_key => $field_val){
				$options[$field_key] = array(
					'view' => 'select',
					'title' => sprintf(__('Verify the "%s" field in user profile','pn'), $field_val),
					'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
					'default' => is_isset($uf, $field_key),
					'name' => $field_key,
				);	
			}	
			
			$params_form = array(
				'filter' => 'all_usve_settings_adminform',
				'method' => 'ajax',
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options);		
		} 
	}

	if(!function_exists('def_premium_action_all_usve_settings')){
		add_action('premium_action_all_usve_settings','def_premium_action_all_usve_settings');
		function def_premium_action_all_usve_settings(){
			$plugin = get_plugin_class();

			only_post();
			
			$form = new PremiumForm();
			$form->send_header();			
			
			pn_only_caps(array('administrator','pn_userverify'));
			
			$cf_auto = array();
			$cf_auto_list = apply_filters('user_fields_in_website', array());
			foreach($cf_auto_list as $cf_k => $cf_v){
				$cf_auto[$cf_k] = is_isset($cf_v, 'title');
			}	
			$fields_arr = array();
			foreach($cf_auto as $k => $v){
				$fields_arr[$k] = intval(is_param_post($k));
			}	
			$plugin->update_option('usve','verify_fields',$fields_arr);
			
			$options = array('status','verifysk','create_not');
			foreach($options as $key){
				$val = is_sum(is_param_post($key));
				$plugin->update_option('usve',$key,$val);
			}			
					
			$options_text = array('text','canceltext');		
			foreach($options_text as $key){		
				$val = pn_strip_text(is_param_post_ml($key));
				$plugin->update_option('usve', $key, $val);
			}

			do_action('all_usve_settings_adminform_post');
					
			$url = admin_url('admin.php?page=all_usve_settings&reply=true');
			$form->answer_form($url);
		}
	}	 
}