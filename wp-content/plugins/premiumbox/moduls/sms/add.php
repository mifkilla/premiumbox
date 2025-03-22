<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	
	add_action('pn_adminpage_title_all_sms_add', 'pn_admin_title_all_sms_add');
	function pn_admin_title_all_sms_add(){
		$id = is_extension_name(is_param_get('item_key'));
		
		$item = get_option('extlist_sms');
		if(!is_array($item)){ $item = array(); }
		
		if(isset($item[$id])){
			_e('Edit SMS gate','pn');
		} else {
			_e('Add SMS gate','pn'); 
		}
	}

	add_action('pn_adminpage_content_all_sms_add','def_pn_admin_content_all_sms_add');
	function def_pn_admin_content_all_sms_add(){
	global $wpdb, $premiumbox;

		$id = is_extension_name(is_param_get('item_key'));
		$data_id = '';	
		
		$data = array();
		
		$item = get_option('extlist_sms');
		if(!is_array($item)){ $item = array(); }
		
		if(isset($item[$id])){
			$data_id = $id;
			$data = $item[$id];
			$title = __('Edit SMS gate','pn') . ' "' . is_isset($data, 'title') . '"';
		} else {
			$title = __('Add SMS gate','pn');
		}
		
		$form = new PremiumForm();
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=all_sms_list'),
			'title' => __('Back to list','pn')
		);
		if(strlen($data_id) > 0){
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=all_sms_add'),
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
			'title' => __('Title','pn'),
			'default' => is_isset($data, 'title'),
			'name' => 'title',
		);
		
		$scripts = array();
		$scripts[0] = '--' . __('Select','pn') . '--';
		$scripts_list = list_extended($premiumbox, 'sms');
		foreach($scripts_list as $sc_key => $sc_val){
			$place = is_isset($sc_val,'place');
			$theme = '';
			if($place == 'theme'){
				$theme = ' (' . __('Theme','pn') . ')';
			}
			$scripts[$sc_key] = ctv_ml(is_isset($sc_val,'title')).' ('. $sc_key .')'. $theme;
		}
		asort($scripts);
		
		$now_script = trim(is_isset($data, 'script'));
		
		$options['script'] = array(
			'view' => 'select_search',
			'title' => __('Module','pn'),
			'options' => $scripts,
			'default' => $now_script,
			'name' => 'script',
		);		
		$options['status'] = array(
			'view' => 'select',
			'title' => __('Status','pn'),
			'options' => array('1'=>__('active SMS gate','pn'),'0'=>__('inactive SMS gate','pn')),
			'default' => is_isset($data, 'status'),
			'name' => 'status',
		);		
		$params_form = array(
			'method' => 'ajax',
			'data' => $data,
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);
		
		if($now_script){
			
			$options = array();
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => __('Module settings','pn'),
				'submit' => __('Save','pn'),
			);
			$options['hidden_block'] = array(
				'view' => 'hidden_input',
				'name' => 'item_key',
				'default' => $data_id,
			);
			$options['hidden_block_script'] = array(
				'view' => 'hidden_input',
				'name' => 'script',
				'default' => $now_script,
			);			
			$options = apply_filters('ext_smsgate_data', $options, $now_script, $data_id);
			
			if(count($options) > 3){
				if(is_has_admin_password()){
					$placeholder = '';
					if(is_pass_protected()){
						$placeholder = __('Enter security password','pn');
					}
					$options['pass_line'] = array(
						'view' => 'line',
					);				
					$options['pass'] = array(
						'view' => 'inputbig',
						'title' => '<span class="bred">'. __('Security password','pn') . '</span>',
						'default' => '',
						'name' => 'pass',
						'atts' => array('autocomplete' => 'off', 'placeholder' => $placeholder),
						'work' => 'none',
					);
					$options['warning_pass'] = array(
						'view' => 'warning',
						'title' => __('More info','pn'),
						'default' => sprintf(__('Enter your security password to save the settings. Instructions for setting the security password are available in the <a href="%s">link</a>.','pn'), 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/kod-bezopasnosti-dlya-podtverzhdeniya-platezhey/'),
					);
				}
				
				$params_form = array(
					'method' => 'ajax',
					'data' => '',
					'form_link' => pn_link('all_sms_data', 'post'),
					'button_title' => __('Save','pn'),
				);
				$form->init_form($params_form, $options);			
			}
			
			$merch_data = get_option('smsgate_data');
			if(!is_array($merch_data)){ $merch_data = array(); }
			
			$data = '';
			if(isset($merch_data[$data_id])){
				$data = $merch_data[$data_id]; 
			}	
			
			$options = smsgate_setting_list($now_script, $data, $data_id, 1);

			if(count($options) > 3){
				do_action('before_smsgate_admin', $now_script, $data, $data_id);

				$params_form = array(
					'method' => 'ajax',
					'data' => '',
					'form_link' => pn_link('pn_smsgate_settings', 'post'),
					'button_title' => __('Save','pn'),
				);
				$form->init_form($params_form, $options);
			}	
		}
	} 
	
	function smsgate_setting_list($now_script, $data, $data_id, $place){
		$options = array();	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Settings','pn'),
			'submit' => __('Save','pn'),
		);
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'item_key',
			'default' => $data_id,
		);
		$options['hidden_block_script'] = array(
			'view' => 'hidden_input',
			'name' => 'script',
			'default' => $now_script,
		);	
		$options = apply_filters('get_smsgate_options', $options, $now_script, $data, $data_id, $place);	
		return $options;
	}		

	add_action('premium_action_pn_smsgate_settings','def_premium_action_pn_smsgate_settings');
	function def_premium_action_pn_smsgate_settings(){	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_change_notify'));
		
		$item_key = is_extension_name(is_param_post('item_key'));
		$script = is_extension_name(is_param_post('script'));
		
		$options = smsgate_setting_list($script, '', $item_key, 0);
		$data = $form->strip_options('', 'post', $options);
		
		$merch_data = get_option('smsgate_data');
		if(!is_array($merch_data)){ $merch_data = array(); }
									
		foreach($data as $key => $val){
			$merch_data[$item_key][$key] = $val;
		}			

		update_option('smsgate_data', $merch_data);

		do_action('smsgate_admin_options_post');		

		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
		$form->answer_form($back_url);
	}

	add_action('premium_action_all_sms_data','def_premium_action_all_sms_data');
	function def_premium_action_all_sms_data(){	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_change_notify'));
		
		$error = save_pass_protected(is_param_post('pass'));
		if($error){
			$form->error_form(__('Error! You have entered an incorrect security password','pn'));
		}	
		
		$item_key = is_extension_name(is_param_post('item_key'));
		$script = is_extension_name(is_param_post('script'));
			
		$up = apply_filters('ext_smsgate_data_post', 0, $script, $item_key);
		if($up != 1){
			$form->error_form(__('Settings cannot be written','pn'));
		}

		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
		$form->answer_form($back_url);
	}

	add_action('premium_action_all_sms_add','def_premium_action_all_sms_add');
	function def_premium_action_all_sms_add(){
	global $wpdb, $premiumbox;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator', 'pn_change_notify'));	

		$data_key = is_extension_name(is_param_post('item_key'));
		
		$script = is_extension_name(is_param_post('script'));
		if(!$script){ $form->error_form(__('Module not chosen','pn')); }
		
		$status = intval(is_param_post('status'));
		
		$title = pn_strip_input(is_param_post('title'));
		if(!$title){ 
			$scripts = list_extended($premiumbox, 'sms');
			$scr_data = is_isset($scripts, $script);
			$title = ctv_ml(is_isset($scr_data, 'title')) . ' ('. $script . ')'; 
		}
		
		$item = get_option('extlist_sms');
		if(!is_array($item)){ $item = array(); }
		
		if(strlen($data_key) > 0 and isset($item[$data_key])){
			$item[$data_key] = array(
				'title' => $title,
				'script' => $script,
				'status' => $status,
			);			
		} else {
			$data_key = uniq_data_key($script, $item);
			$item[$data_key] = array(
				'title' => $title,
				'script' => $script,
				'status' => $status,
			);
		}

		if($script and $data_key){
			include_extanded($premiumbox, 'sms', $script);
			if($status == 1){
				do_action('ext_smsgate_active_'. $script, $data_key);
				do_action('ext_smsgate_active', $script, $data_key);	
			} else {
				do_action('ext_smsgate_deactive_'. $script, $data_key);
				do_action('ext_smsgate_deactive', $script, $data_key);	
			}	
		}
		
		update_option('extlist_sms', $item);

		$url = admin_url('admin.php?page=all_sms_add&item_key='. $data_key .'&reply=true');
		$form->answer_form($url);
	}
}	