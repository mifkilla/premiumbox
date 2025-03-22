<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_settings_reviews')){
		add_action('pn_adminpage_title_all_settings_reviews', 'def_adminpage_title_all_settings_reviews');
		function def_adminpage_title_all_settings_reviews(){
			_e('Settings','pn');
		}
	}

	if(!function_exists('def_adminpage_content_all_settings_reviews')){
		add_action('pn_adminpage_content_all_settings_reviews','def_adminpage_content_all_settings_reviews');
		function def_adminpage_content_all_settings_reviews(){
			$plugin = get_plugin_class();
			
			$form = new PremiumForm();
			
			$options = array();
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => __('Settings','pn'),
				'submit' => __('Save','pn'),
			);
			$count = intval($plugin->get_option('reviews','count'));
			if(!$count){ $count = 10; }
			$options['count'] = array(
				'view' => 'input',
				'title' => __('Amount of reviews on a page','pn'),
				'default' => $count,
				'name' => 'count',
				'work' => 'input',
			);				
			$options['deduce'] = array(
				'view' => 'select',
				'title' => __('Display reviews','pn'),
				'options' => array('0'=>__('All'),'1'=>__('by language','pn')),
				'default' => $plugin->get_option('reviews','deduce'),
				'name' => 'deduce',
				'work' => 'int',
			);
			$options['method'] = array(
				'view' => 'select',
				'title' => __('Method used for adding process','pn'),
				'options' => array('not'=>__('Forbidden to add','pn'),'verify'=>__('E-mail confirmation','pn'),'moderation'=>__('Moderation by admin','pn'),'notmoderation'=>__('Without moderation','pn')),
				'default' => $plugin->get_option('reviews','method'),
				'name' => 'method',
				'work' => 'int',
			);
			$options['by'] = array(
				'view' => 'select',
				'title' => __('For whom','pn'),
				'options' => array('0'=>__('All','pn'),'1'=>__('only users','pn'),'2'=>__('only quests','pn')),
				'default' => $plugin->get_option('reviews','by'),
				'name' => 'by',
				'work' => 'int',
			);			
			$options['website'] = array(
				'view' => 'select',
				'title' => __('Enable field "Website"','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => $plugin->get_option('reviews','website'),
				'name' => 'website',
				'work' => 'int',
			);	
			
			$form = new PremiumForm();
			$params_form = array(
				'filter' => 'all_reviews_settingsform',
				'method' => 'ajax',
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options);	
		}
	}

	if(!function_exists('def_premium_action_all_settings_reviews')){
		add_action('premium_action_all_settings_reviews','def_premium_action_all_settings_reviews');
		function def_premium_action_all_settings_reviews(){
		global $wpdb;	

			$plugin = get_plugin_class();
			
			only_post();
			
			$form = new PremiumForm();
			$form->send_header();
			
			pn_only_caps(array('administrator','pn_reviews'));
				
			$options = array('count','by','deduce','method','website');	
			foreach($options as $key){
				$val = pn_strip_input(is_param_post($key));
				$plugin->update_option('reviews', $key, $val);
			}
						
			do_action('all_reviews_settingsform_post');
					
			$url = admin_url('admin.php?page=all_settings_reviews&reply=true');
			$form->answer_form($url);
		}
	}
}	