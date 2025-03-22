<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	
	add_action('pn_adminpage_title_pn_add_merchants', 'pn_admin_title_pn_add_merchants');
	function pn_admin_title_pn_add_merchants(){
		$id = is_extension_name(is_param_get('item_key'));
		
		$item = get_option('extlist_merchants');
		if(!is_array($item)){ $item = array(); }
		
		if(isset($item[$id])){
			_e('Edit merchant','pn');
		} else {
			_e('Add merchant','pn'); 
		}
	}

	add_action('pn_adminpage_content_pn_add_merchants','def_pn_admin_content_pn_add_merchants');
	function def_pn_admin_content_pn_add_merchants(){
	global $wpdb, $premiumbox;

		$id = is_extension_name(is_param_get('item_key'));
		$data_id = '';	
		
		$data = array();
		
		$item = get_option('extlist_merchants');
		if(!is_array($item)){ $item = array(); }
		
		if(isset($item[$id])){
			$data_id = $id;
			$data = $item[$id];
			$title = __('Edit merchant','pn') . ' "' . is_isset($data, 'title') . '"';
		} else {
			$title = __('Add merchant','pn');
		}
		
		$form = new PremiumForm();
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_merchants'),
			'title' => __('Back to list','pn')
		);
		if(strlen($data_id) > 0){
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_merchants'),
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
			'title' => __('Name','pn'),
			'default' => is_isset($data, 'title'),
			'name' => 'title',
		);
		
		$scripts = array();
		$scripts[0] = '--' . __('Select','pn') . '--';
		$scripts_list = list_extended($premiumbox, 'merchants');
		foreach($scripts_list as $sc_key => $sc_val){
			$place = is_isset($sc_val,'place');
			$theme = '';
			if($place == 'theme'){
				$theme = ' (' . __('Theme','pn') . ')';
			}
			$scripts[$sc_key] = ctv_ml(is_isset($sc_val,'title')).' ('. $sc_key .' '. is_isset($sc_val,'vers') .')'. $theme;
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
			'options' => array('1'=>__('active merchant','pn'),'0'=>__('inactive merchant','pn')),
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
			$options = apply_filters('ext_merchants_data', $options, $now_script, $data_id);
			
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
					'form_link' => pn_link('pn_merchants_data', 'post'),
					'button_title' => __('Save','pn'),
				);
				$form->init_form($params_form, $options);
			}
			
			$merch_data = get_option('merchants_data');
			if(!is_array($merch_data)){ $merch_data = array(); }
			
			$data = '';
			if(isset($merch_data[$data_id])){
				$data = $merch_data[$data_id]; 
			}	
			
			$options = merchant_setting_list($now_script, $data, $data_id, 1);
			
			if(count($options) > 3){
				do_action('before_merchant_admin', $now_script, $data, $data_id);

				$params_form = array(
					'method' => 'ajax',
					'data' => '',
					'form_link' => pn_link('pn_merchants_settings', 'post'),
					'button_title' => __('Save','pn'),
				);
				$form->init_form($params_form, $options);			
			}
		}
	} 

	function merchant_setting_list($now_script, $data, $data_id, $place){
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
		$options['instruction'] = array(
			'view' => 'editor',
			'title' => __('Payment instruction for user','pn'),
			'default' => is_isset($data, 'text'),
			'name' => 'text',
			'tags' => apply_filters('direction_instruction_tags', array(), 'merchant_instruction'),
			'rows' => '12',
			'standart_tags' => 1,
			'ml' => 1,
			'work' => 'text',
		);
		$tags = array(
			'paysum' => array(
				'title' => __('Payment amount','pn'),
				'start' => '[paysum]',
			),
			'sum1' => array(
				'title' => __('Amount To send','pn'),
				'start' => '[sum1]',
			),
			'currency_give' => array(
				'title' => __('Currency name Giving','pn'),
				'start' => '[currency_give]',
			),
			'sum2' => array(
				'title' => __('Amount Receive','pn'),
				'start' => '[sum2]',
			),
			'currency_get' => array(
				'title' => __('Currency name Receiving','pn'),
				'start' => '[currency_get]',
			),
			'fio' => array(
				'title' => __('User name','pn'),
				'start' => '[fio]',
			),
			'ip' => array(
				'title' => __('User IP','pn'),
				'start' => '[ip]',
			),
		);
		$tags = apply_filters('direction_instruction_tags', $tags, 'merchant_note');
		$tags = apply_filters('merchant_admin_tags', $tags, $now_script);
		$options['note'] = array(
			'view' => 'editor',
			'title' => __('Note for payment','pn'),
			'default' => is_isset($data, 'note'),
			'tags' => $tags,
			'rows' => '12',
			'name' => 'note',
			'work' => 'text',
			'ml' => 1,
		);
		$options['pagenote'] = array(
			'view' => 'editor',
			'title' => __('Message on payment page','pn'),
			'default' => is_isset($data, 'pagenote'),
			'tags' => $tags,
			'rows' => '12',
			'name' => 'pagenote',
			'work' => 'text',
			'standart_tags' => 1,
			'ml' => 1,
		);		
		$options['corr'] = array(
			'view' => 'input',
			'title' => __('Payment amount error','pn'),
			'default' => is_isset($data, 'corr'),
			'name' => 'corr',
			'work' => 'percent',
		);
		$options['max'] = array(
			'view' => 'input',
			'title' => __('Daily limit for merchant','pn'),
			'default' => is_isset($data, 'max'),
			'name' => 'max',
			'work' => 'sum',
		);
		$options['max_month'] = array(
			'view' => 'input',
			'title' => __('Monthly limit for merchant','pn'),
			'default' => is_isset($data, 'max_month'),
			'name' => 'max_month',
			'work' => 'sum',
		);			
		$options['max_sum'] = array(
			'view' => 'input',
			'title' => __('Max. payment amount for single order','pn'),
			'default' => is_isset($data, 'max_sum'),
			'name' => 'max_sum',
			'work' => 'sum',
		);
		$options['maxc_day'] = array(
			'view' => 'input',
			'title' => __('Daily limit of orders (quantities) for merchant','pn'),
			'default' => is_isset($data, 'maxc_day'),
			'name' => 'maxc_day',
			'work' => 'int',
		);
		$options['maxc_month'] = array(
			'view' => 'input',
			'title' => __('Monthly limit of orders (quantities) for merchant','pn'),
			'default' => is_isset($data, 'maxc_month'),
			'name' => 'maxc_month',
			'work' => 'int',
		);		
		$options['discancel'] = array(
			'view' => 'select',
			'title' => __('Hide  button "Cancel order"','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => is_isset($data, 'discancel'),
			'name' => 'discancel',
			'work' => 'int',
		);		
		$options['check_api'] = array(
			'view' => 'select',
			'title' => __('Check payment history by API','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => is_isset($data, 'check_api'),
			'name' => 'check_api',
			'work' => 'int',
		);
		$options['type'] = array(
			'view' => 'select',
			'title' => __('Type','pn'),
			'options' => array('0'=>__('Standart merchant fee','pn'), '1'=>__('Non-standart merchant fee','pn')),
			'default' => is_isset($data, 'type'),
			'name' => 'type',
			'work' => 'int',
		);		
		$options['help_type'] = array(
			'view' => 'help',
			'title' => __('More info','pn'),
			'default' => __('Choose "Non-standart merchant fee" if a payment system takes a fee for incoming payment. In other case you need to set "Standart merchant fee".','pn'),
		);
		$options['enableip'] = array(
			'view' => 'textarea',
			'title' => __('Authorized IP (at the beginning of a new line)','pn'),
			'default' => is_isset($data, 'enableip'),
			'name' => 'enableip',
			'rows' => '8',
			'work' => 'text',
		);		
		$options['resulturl'] = array(
			'view' => 'inputbig',
			'title' => __('Status/Result URL hash','pn'),
			'default' => is_isset($data, 'resulturl'),
			'name' => 'resulturl',
			'work' => 'symbols',
		);
		$options['help_resulturl'] = array(
			'view' => 'help',
			'title' => __('More info','pn'),
			'default' => __('We recommend to use unique hashes at least 50 characters long, and containing Latin characters and numbers in random order. Create or generate a hash. For example ImYkwGjhuWyNasq2fdQJzVvCpis8umbx. When setting up the merchant on the side of the payment system as the status address (typically, this is the Status URL or Return URL), specify the URL with already specified hash. You can find the Status/Result URL with the specified hash below.','pn'),
		);	
		$options['show_error'] = array(
			'view' => 'select',
			'title' => __('Debug mode','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => is_isset($data, 'show_error'),
			'name' => 'show_error',
			'work' => 'int',
		);
		$options['center_title'] = array(
			'view' => 'h3',
			'title' => __('Actions with orders status in cases','pn'),
			'submit' => __('Save','pn'),
		);		
		$options['check'] = array(
			'view' => 'select',
			'title' => __('Number of account, from which payment was made does not match one specified in order','pn'),
			'options' => array('0'=>__('Save order status as New','pn'), '1'=>__('Change order status to On checking','pn'), '2'=>__('Change order status to Paid','pn')),
			'default' => is_isset($data, 'check'),
			'name' => 'check',
			'work' => 'int',
		);	
		$options['invalid_ctype'] = array(
			'view' => 'select',
			'title' => __('Incorrect currency code','pn'),
			'options' => array('0'=>__('Save order status as New','pn'), '1'=>__('Change order status to On checking','pn'), '2'=>__('Change order status to Paid','pn')),
			'default' => is_isset($data, 'invalid_ctype'),
			'name' => 'invalid_ctype',
			'work' => 'int',
		);
		$options['invalid_minsum'] = array(
			'view' => 'select',
			'title' => __('Payment amount is less than required','pn'),
			'options' => array('0'=>__('Save order status as New','pn'), '1'=>__('Change order status to On checking','pn'), '2'=>__('Change order status to Paid','pn')),
			'default' => is_isset($data, 'invalid_minsum'),
			'name' => 'invalid_minsum',
			'work' => 'int',
		);
		$options['invalid_maxsum'] = array(
			'view' => 'select',
			'title' => __('Payment amount is more required','pn'),
			'options' => array('0'=>__('Save order status as New','pn'), '1'=>__('Change order status to On checking','pn'), '2'=>__('Change order status to Paid','pn')),
			'default' => is_isset($data, 'invalid_maxsum'),
			'name' => 'invalid_maxsum',
			'work' => 'int',
		);
		$options = apply_filters('get_merchants_options', $options, $now_script, $data, $data_id, $place);	
		return $options;
	}

	add_action('premium_action_pn_merchants_settings','def_premium_action_pn_merchants_settings');
	function def_premium_action_pn_merchants_settings(){	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_merchants'));
		
		$item_key = is_extension_name(is_param_post('item_key'));
		$script = is_extension_name(is_param_post('script'));
		
		$options = merchant_setting_list($script, '', $item_key, 0);
		$data = $form->strip_options('', 'post', $options);
		
		$merch_data = get_option('merchants_data');
		if(!is_array($merch_data)){ $merch_data = array(); }
						
		$auto_create_hash = apply_filters('auto_create_hash', 0);				

		foreach($data as $key => $val){
			if($key == 'resulturl' and strlen($val) < 1 and $auto_create_hash == 1){	
				$val = mb_strtolower(get_rand_word(16, 1));
			} 
			$merch_data[$item_key][$key] = $val;
		}			

		update_option('merchants_data', $merch_data);

		do_action('merchants_admin_options_post');		

		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
		$form->answer_form($back_url);
	}

	add_action('premium_action_pn_merchants_data','def_premium_action_pn_merchants_data');
	function def_premium_action_pn_merchants_data(){	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_merchants'));
		
		$error = save_pass_protected(is_param_post('pass'));
		if($error){
			$form->error_form(__('Error! You have entered an incorrect security password','pn'));
		}	
		
		$item_key = is_extension_name(is_param_post('item_key'));
		$script = is_extension_name(is_param_post('script'));
			
		$up = apply_filters('ext_merchants_data_post', 0, $script, $item_key);
		if($up != 1){
			$form->error_form(__('Settings cannot be written','pn'));
		}

		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
		$form->answer_form($back_url);
	}

	add_action('premium_action_pn_add_merchants','def_premium_action_pn_add_merchants');
	function def_premium_action_pn_add_merchants(){
	global $wpdb, $premiumbox;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator', 'pn_merchants'));	

		$data_key = is_extension_name(is_param_post('item_key'));
		
		$script = is_extension_name(is_param_post('script'));
		if(!$script){ $form->error_form(__('Module not chosen','pn')); }
		
		$status = intval(is_param_post('status'));
		
		$title = pn_strip_input(is_param_post('title'));
		if(!$title){ 
			$scripts = list_extended($premiumbox, 'merchants');
			$scr_data = is_isset($scripts, $script);
			$title = ctv_ml(is_isset($scr_data, 'title')) . ' ('. $script . ')'; 
		}
		
		$item = get_option('extlist_merchants');
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
			include_extanded($premiumbox, 'merchants', $script);
			if($status == 1){
				do_action('ext_merchants_active_'. $script, $data_key);
				do_action('ext_merchants_active', $script, $data_key);	
			} else {
				do_action('ext_merchants_deactive_'. $script, $data_key);
				do_action('ext_merchants_deactive', $script, $data_key);	
			}

			$auto_create_hash = apply_filters('auto_create_hash', 0);
			if($auto_create_hash == 1){
				$merch_data = get_option('merchants_data');
				if(!is_array($merch_data)){ $merch_data = array(); }
				$merch_data['resulturl'] = mb_strtolower(get_rand_word(16, 1));			
				update_option('merchants_data', $merch_data);
			}			
		}
		
		update_option('extlist_merchants', $item);

		$url = admin_url('admin.php?page=pn_add_merchants&item_key='. $data_key .'&reply=true');
		$form->answer_form($url);
	}
}	