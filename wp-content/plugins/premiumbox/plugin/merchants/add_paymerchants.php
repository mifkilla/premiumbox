<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	
	add_action('pn_adminpage_title_pn_add_paymerchants', 'pn_admin_title_pn_add_paymerchants');
	function pn_admin_title_pn_add_paymerchants(){
		$id = is_extension_name(is_param_get('item_key'));
		
		$item = get_option('extlist_paymerchants');
		if(!is_array($item)){ $item = array(); }
		
		if(isset($item[$id])){
			_e('Edit automatic payout','pn');
		} else {
			_e('Add automatic payout','pn'); 
		}
	}

	add_action('pn_adminpage_content_pn_add_paymerchants','def_pn_admin_content_pn_add_paymerchants');
	function def_pn_admin_content_pn_add_paymerchants(){
	global $wpdb, $premiumbox;

		$id = is_extension_name(is_param_get('item_key'));
		$data_id = '';	
		
		$data = array();
		
		$item = get_option('extlist_paymerchants');
		if(!is_array($item)){ $item = array(); }
		
		if(isset($item[$id])){
			$data_id = $id;
			$data = $item[$id];
			$title = __('Edit automatic payout','pn') . ' "' . is_isset($data, 'title') . '"';
		} else {
			$title = __('Add automatic payout','pn');
		}
		
		$form = new PremiumForm();
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_paymerchants'),
			'title' => __('Back to list','pn')
		);
		if(strlen($data_id) > 0){
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_paymerchants'),
				'title' => __('Add new','pn')
			);	
		}
		$form->back_menu($back_menu, $data);
		
		$warn_temp = '<div style="padding: 0 0 20px 0;">';
		$warn_temp .= $form->get_warning(sprintf(__('Do not use automatic payouts if it not urgent. Developer is not responsible for the safety of currency on your accounts. Read more here <a href="%s">here</a>.','pn'),'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/preduprezhdenie-auto/'));
		$warn_temp .= '</div>';
		
		echo $warn_temp;
		
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
		$scripts_list = list_extended($premiumbox, 'paymerchants');
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
			'options' => array('1'=>__('active automatic payout','pn'),'0'=>__('inactive automatic payout','pn')),
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
			$options = apply_filters('ext_paymerchants_data', $options, $now_script, $data_id);
			
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
					'form_link' => pn_link('pn_paymerchants_data', 'post'),
					'button_title' => __('Save','pn'),
				);
				$form->init_form($params_form, $options);			
			}
			
			$merch_data = get_option('paymerchants_data');
			if(!is_array($merch_data)){ $merch_data = array(); }
			
			$data = '';
			if(isset($merch_data[$data_id])){
				$data = $merch_data[$data_id]; 
			}	
			
			$options = paymerchant_setting_list($now_script, $data, $data_id, 1);

			if(count($options) > 4){
				do_action('before_paymerchant_admin', $now_script, $data, $data_id);

				$params_form = array(
					'method' => 'ajax',
					'data' => '',
					'form_link' => pn_link('pn_paymerchants_settings', 'post'),
					'button_title' => __('Save','pn'),
				);
				$form->init_form($params_form, $options);
			}
		}
	} 
	
	function paymerchant_setting_list($now_script, $data, $data_id, $place){
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
			'title' => __('Payout instruction for user','pn'),
			'default' => is_isset($data, 'text'),
			'name' => 'text',
			'tags' => apply_filters('direction_instruction_tags', array(), 'paymerchant_instruction'),
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
		$tags = apply_filters('direction_instruction_tags', $tags, 'paymerchant_note');
		$tags = apply_filters('paymerchant_admin_tags', $tags, $now_script);
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
		$options['realpay'] = array(
			'view' => 'select',
			'title' => __('Automatic payout when order has status "Paid order"','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => is_isset($data,'realpay'),
			'name' => 'realpay',
			'work' => 'int',
		);
		$options['verify'] = array(
			'view' => 'select',
			'title' => __('Automatic payout when order has status "Order is on checking"','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => is_isset($data,'verify'),
			'name' => 'verify',
			'work' => 'int',
		);
		$options['button'] = array(
			'view' => 'select',
			'title' => __('Button used to make payouts according to order manually','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => is_isset($data,'button'),
			'name' => 'button',
			'work' => 'int',
		);
		$options['line0'] = array(
			'view' => 'line',
		);		
		$options['max'] = array(
			'view' => 'input',
			'title' => __('Daily automatic payout limit','pn'),
			'default' => is_isset($data, 'max'),
			'name' => 'max',
			'work' => 'sum',
		);
		$options['max_month'] = array(
			'view' => 'input',
			'title' => __('Monthly automatic payout limit','pn'),
			'default' => is_isset($data, 'max_month'),
			'name' => 'max_month',
			'work' => 'sum',
		);
		$options['min_sum'] = array(
			'view' => 'input',
			'title' => __('Min. amount of automatic payouts due to order','pn'),
			'default' => is_isset($data, 'min_sum'),
			'name' => 'min_sum',
			'work' => 'sum',
		);		
		$options['max_sum'] = array(
			'view' => 'input',
			'title' => __('Max. amount of automatic payouts due to order','pn'),
			'default' => is_isset($data, 'max_sum'),
			'name' => 'max_sum',
			'work' => 'sum',
		);
		$where_sum = array(
			'0' => __('Amount To receive (add.fees and PS fees)','pn'), 
			'1' => __('Amount To receive (add. fees)','pn'), 
			'2' => __('Amount for reserve','pn'), 
			'3' => __('Amount (discount included)','pn'), 
		);
		$options['where_sum'] = array(
			'view' => 'select',
			'title' => __('Amount transfer for payout','pn'),
			'options' => $where_sum,
			'default' => is_isset($data,'where_sum'),
			'name' => 'where_sum',
			'work' => 'int',
		);	
		$options['checkpay'] = array(
			'view' => 'select',
			'title' => __('Check payment history by API','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => is_isset($data, 'checkpay'),
			'name' => 'checkpay',
			'work' => 'int',
		);	
		$options['resulturl'] = array(
			'view' => 'inputbig',
			'title' => __('Status/Result URL hash','pn'),
			'default' => is_isset($data, 'resulturl'),
			'name' => 'resulturl',
			'work' => 'symbols',
		);
		$options['show_error'] = array(
			'view' => 'select',
			'title' => __('Debug mode','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => is_isset($data, 'show_error'),
			'name' => 'show_error',
			'work' => 'int',
		);		
		$options['line1'] = array(
			'view' => 'line',
		);
		$options['timeout'] = array(
			'view' => 'input',
			'title' => __('Automatic payout delay (hrs or min)','pn'),
			'default' => is_isset($data, 'timeout'),
			'name' => 'timeout',
			'work' => 'sum',
		);
		$options['timeout_user'] = array(
			'view' => 'select',
			'title' => __('Whom the delay is for','pn'),
			'options' => array('0'=>__('everyone','pn'), '1'=>__('newcomers','pn'), '2'=>__('not registered users','pn'), '3' => __('not verified users','pn')),
			'default' => is_isset($data,'timeout_user'),
			'name' => 'timeout_user',
			'work' => 'int',
		);	
		$options['line_timeout'] = array(
			'view' => 'line',
		);
		
		$statused = apply_filters('bid_status_list', array());
		if(!is_array($statused)){ $statused = array(); }

		$error_status = trim(is_isset($data, 'error_status'));
		if(!$error_status){ $error_status = 'payouterror'; }
		
		$options['error_status'] = array(
			'view' => 'select',
			'title' => __('API status error','pn'),
			'options' => $statused,
			'default' => $error_status,
			'name' => 'error_status',
			'work' => 'input',
		);				
		

		$options = apply_filters('get_paymerchants_options', $options, $now_script, $data, $data_id, $place);	
		return $options;
	}	

	add_action('premium_action_pn_paymerchants_settings','def_premium_action_pn_paymerchants_settings');
	function def_premium_action_pn_paymerchants_settings(){	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_merchants'));
		
		$item_key = is_extension_name(is_param_post('item_key'));
		$script = is_extension_name(is_param_post('script'));
		
		$options = paymerchant_setting_list($script, '', $item_key, 0);
		$data = $form->strip_options('', 'post', $options);
		
		$merch_data = get_option('paymerchants_data');
		if(!is_array($merch_data)){ $merch_data = array(); }
						
		$auto_create_hash = apply_filters('auto_create_hash', 0);				
						
		foreach($data as $key => $val){
			if($key == 'resulturl' and strlen($val) < 1 and $auto_create_hash == 1){	
				$val = mb_strtolower(get_rand_word(16, 1));
			} 
			$merch_data[$item_key][$key] = $val;
		}			

		update_option('paymerchants_data', $merch_data);

		do_action('paymerchants_admin_options_post');		

		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
		$form->answer_form($back_url);
	}

	add_action('premium_action_pn_paymerchants_data','def_premium_action_pn_paymerchants_data');
	function def_premium_action_pn_paymerchants_data(){	

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
			
		$up = apply_filters('ext_paymerchants_data_post', 0, $script, $item_key);
		if($up != 1){
			$form->error_form(__('Settings cannot be written','pn'));
		}

		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
		$form->answer_form($back_url);
	}

	add_action('premium_action_pn_add_paymerchants','def_premium_action_pn_add_paymerchants');
	function def_premium_action_pn_add_paymerchants(){
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
			$scripts = list_extended($premiumbox, 'paymerchants');
			$scr_data = is_isset($scripts, $script);
			$title = ctv_ml(is_isset($scr_data, 'title')) . ' ('. $script . ')'; 
		}
		
		$item = get_option('extlist_paymerchants');
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
			include_extanded($premiumbox, 'paymerchants', $script);
			if($status == 1){
				do_action('ext_paymerchants_active_'. $script, $data_key);
				do_action('ext_paymerchants_active', $script, $data_key);	
			} else {
				do_action('ext_paymerchants_deactive_'. $script, $data_key);
				do_action('ext_paymerchants_deactive', $script, $data_key);	
			}	
			
			$auto_create_hash = apply_filters('auto_create_hash', 0);
			if($auto_create_hash == 1){
				$merch_data = get_option('paymerchants_data');
				if(!is_array($merch_data)){ $merch_data = array(); }
				$merch_data['resulturl'] = mb_strtolower(get_rand_word(16, 1));			
				update_option('paymerchants_data', $merch_data);
			}	
		}
		
		update_option('extlist_paymerchants', $item);

		$url = admin_url('admin.php?page=pn_add_paymerchants&item_key='. $data_key .'&reply=true');
		$form->answer_form($url);
	}
}	