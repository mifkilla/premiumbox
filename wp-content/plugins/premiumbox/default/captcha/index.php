<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('admin_menu_captcha')){
	if(is_admin()){ 
		add_action('admin_menu', 'admin_menu_captcha');
		function admin_menu_captcha(){
			$plugin = get_plugin_class();	
			add_submenu_page("options-general.php", __('Captcha','pn'), __('Captcha','pn'), 'administrator', "all_captcha", array($plugin, 'admin_temp'));
		}	
		
		add_action('pn_adminpage_title_all_captcha', 'def_adminpage_title_all_captcha');
		function def_adminpage_title_all_captcha(){
			_e('Captcha','pn');
		}	
		
		add_action('pn_adminpage_content_all_captcha','def_pn_adminpage_content_all_captcha');
		function def_pn_adminpage_content_all_captcha(){
			$plugin = get_plugin_class();
			
			$form = new PremiumForm();	
			
			$options = array();	
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => __('Captcha','pn'),
				'submit' => __('Save','pn'),
			);	
			
			$placed = apply_filters('placed_form', array());	
			if(is_array($placed)){
				foreach($placed as $key => $title){
					$options[] = array(
						'view' => 'select',
						'title' => $title,
						'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
						'default' => $plugin->get_option('captcha',$key),
						'name' => $key,
					);			
				}
			}
			
			$params_form = array(
				'filter' => 'all_captcha_option',
				'method' => 'ajax',
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options);		
		}

		add_action('premium_action_all_captcha','def_premium_action_all_captcha');
		function def_premium_action_all_captcha() {
			$plugin = get_plugin_class();	

			only_post();
			
			$form = new PremiumForm();
			$form->send_header();			
			
			pn_only_caps(array('administrator'));

			$placed = apply_filters('placed_form', array());	
			if(is_array($placed)){
				foreach($placed as $key => $title){	
					$plugin->update_option('captcha',$key ,intval(is_param_post($key)));	
				}
			}		

			$url = admin_url('options-general.php?page=all_captcha&reply=true');
			$form->answer_form($url);
		}	
	}
}