<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_geoip_settings_detected')){
		add_action('pn_adminpage_title_all_geoip_settings_detected', 'def_adminpage_title_all_geoip_settings_detected');
		function def_adminpage_title_all_geoip_settings_detected(){
			_e('IP determination settings','pn');
		}
	}

	if(!function_exists('def_adminpage_content_all_geoip_settings_detected')){
		add_action('pn_adminpage_content_all_geoip_settings_detected','def_adminpage_content_all_geoip_settings_detected');
		function def_adminpage_content_all_geoip_settings_detected(){
			$plugin = get_plugin_class();
			
			$form = new PremiumForm();
			
			$options = array();
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => __('Settings','pn'),
				'submit' => __('Save','pn'),
			);
			$options['type'] = array(
				'view' => 'select',
				'title' => __('IP address determination source','pn'),
				'options' => array('0'=> '--' . __('no','pn') . '--','1'=> 'ip-api.com', '2' => '2ip.ua', '3' => 'sypexgeo.net'),
				'default' => $plugin->get_option('geoip','type'),
				'name' => 'type',
			);
			$options['api_key'] = array(
				'view' => 'inputbig',
				'title' => __('API key','pn'),
				'default' => $plugin->get_option('geoip','api_key'),
				'name' => 'api_key',
			);
			$options['api_key_help'] = array(
				'view' => 'help',
				'title' => __('More info','pn'),
				'default' => __('For some IP address determining services, the API key may be specified, if a paid tariff is used in these services.','pn'),
			);			
			$options['memory'] = array(
				'view' => 'select',
				'title' => __('Remember previously defined IP address','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => $plugin->get_option('geoip','memory'),
				'name' => 'memory',
			);
			$options['timeout'] = array(
				'view' => 'inputbig',
				'title' => __('Timeout (sec.)','pn'),
				'default' => $plugin->get_option('geoip','timeout'),
				'name' => 'timeout',
			);
			$options['timeout_help'] = array(
				'view' => 'help',
				'title' => __('More info','pn'),
				'default' => __('Timeout is the period when the website awaits a response from a third-party service. If no response is received in the preset period, the website will continue running without response. If the duration is not specified or is 0, the standard 20-second timeout is applied. There is no universal value for the timeout, since it depends on the operation speed of a specific service.','pn'),
			);			
			
			$form = new PremiumForm();
			$params_form = array(
				'filter' => 'all_geoip_settings_detected_form',
				'method' => 'ajax',
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options);	
		}
	}

	if(!function_exists('def_premium_action_all_geoip_settings_detected')){
		add_action('premium_action_all_geoip_settings_detected','def_premium_action_all_geoip_settings_detected');
		function def_premium_action_all_geoip_settings_detected(){
			$plugin = get_plugin_class();	

			only_post();
			
			$form = new PremiumForm();
			$form->send_header();
			
			pn_only_caps(array('administrator','pn_geoip'));
				
			$options = array('type','memory','timeout');	
			foreach($options as $key){
				$val = intval(is_param_post($key));
				$plugin->update_option('geoip', $key, $val);
			}
			
			$options = array('api_key');	
			foreach($options as $key){
				$val = pn_strip_input(is_param_post($key));
				$plugin->update_option('geoip', $key, $val);
			}		
					
			do_action('all_geoip_settings_detected_form_post');
					
			$url = admin_url('admin.php?page=all_geoip_settings_detected&reply=true');
			$form->answer_form($url);
		}	
	}
}	