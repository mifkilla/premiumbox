<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('admin_menu', 'admin_menu_amlbot');
	function admin_menu_amlbot(){
	global $premiumbox;
		add_submenu_page("pn_moduls", __('AML Bot','pn'), __('AML Bot','pn'), 'administrator', "pn_amlbot", array($premiumbox, 'admin_temp'));
	}	
	
	add_action('pn_adminpage_title_pn_amlbot', 'def_adminpage_title_pn_amlbot');
	function def_adminpage_title_pn_amlbot($page){
		_e('AML Bot settings','pn');
	} 

	add_action('pn_adminpage_content_pn_amlbot','def_adminpage_content_pn_amlbot');
	function def_adminpage_content_pn_amlbot(){	
	global $premiumbox;

		$form = new PremiumForm();

		$assets = get_aml_assets();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('AML Bot settings','pn'),
			'submit' => __('Save','pn'),
		);
		$options['access_id'] = array(
			'view' => 'inputbig',
			'title' => __('Access ID', 'pn'),
			'default' => $premiumbox->get_option('amlbot','access_id'),
			'name' => 'access_id',
		);
		$options['access_key'] = array(
			'view' => 'inputbig',
			'title' => __('Access key', 'pn'),
			'default' => $premiumbox->get_option('amlbot','access_key'),
			'name' => 'access_key',
		);
		$options['error_score'] = array(
			'view' => 'inputbig',
			'title' => __('Critical level of risk address', 'pn'),
			'default' => $premiumbox->get_option('amlbot','error_score'),
			'name' => 'error_score',
		);		
		$params_form = array(
			'filter' => 'pn_amlbot_settings',
			'method' => 'ajax',
			'form_link' => pn_link('pn_amlbot_settings','post'),
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);

		$options = array();
		$options['test1_title'] = array(
			'view' => 'h3',
			'title' => __('Check address','pn'),
			'submit' => __('Test','pn'),
		);
		$options['address'] = array(
			'view' => 'inputbig',
			'title' => __('Address', 'pn'),
			'default' => '',
			'name' => 'address',
		);
		$options['currency'] = array(
			'view' => 'select',
			'title' => __('Crypto currency', 'pn'),
			'options' => $assets,
			'default' => '',
			'name' => 'currency',
		);		
		$params_form = array(
			'filter' => 'pn_amlbot_test1',
			'method' => 'ajax',
			'form_link' => pn_link('pn_amlbot_test1','post'),
			'button_title' => __('Test','pn'),
		);
		$form->init_form($params_form, $options);

		$options = array();
		$options['test2_title'] = array(
			'view' => 'h3',
			'title' => __('Check transaction','pn'),
			'submit' => __('Test','pn'),
		);
		$options['address'] = array(
			'view' => 'inputbig',
			'title' => __('Address', 'pn'),
			'default' => '',
			'name' => 'address',
		);
		$options['currency'] = array(
			'view' => 'select',
			'title' => __('Crypto currency', 'pn'),
			'options' => $assets,
			'default' => '',
			'name' => 'currency',
		);
		$options['txid'] = array(
			'view' => 'inputbig',
			'title' => __('TxID', 'pn'),
			'default' => '',
			'name' => 'txid',
		);
		$types = array(
			'0' => 'deposit',
			'1' => 'withdrawal',
		);
		$options['type'] = array(
			'view' => 'select',
			'title' => __('Type', 'pn'),
			'options' => $types,
			'default' => '',
			'name' => 'type',
		);		
		$params_form = array(
			'filter' => 'pn_amlbot_test2',
			'method' => 'ajax',
			'form_link' => pn_link('pn_amlbot_test2','post'),
			'button_title' => __('Test','pn'),
		);
		$form->init_form($params_form, $options);		

	} 
	
	add_action('premium_action_pn_amlbot_settings','def_premium_action_pn_amlbot_settings');
	function def_premium_action_pn_amlbot_settings(){
	global $premiumbox;
	
		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$premiumbox->update_option('amlbot', 'access_id', pn_strip_input(is_param_post('access_id')));
		$premiumbox->update_option('amlbot', 'access_key', pn_strip_input(is_param_post('access_key')));
		$premiumbox->update_option('amlbot', 'error_score', intval(is_param_post('error_score')));
		
		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
				
		$form->answer_form($back_url);
	}	

	add_action('premium_action_pn_amlbot_test1','def_premium_action_pn_amlbot_test1');
	function def_premium_action_pn_amlbot_test1(){
	global $premiumbox;
	
		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$access_id = pn_strip_input($premiumbox->get_option('amlbot','access_id'));
		$access_key = pn_strip_input($premiumbox->get_option('amlbot','access_key'));
		
		$class = new AMLClass($access_id, $access_key);
		$res = $class->verify_address(is_param_post('address'), is_param_post('currency'));
		
		$form->error_form(print_r($res, true));
	}

	/*
	Array ( 
		[result] => 1 
		[balance] => 3 
		[discount] => 0 
		[data] => Array ( 
			[riskscore] => 0.35 
			[signals] => Array ( 
				[atm] => 0.001 
				[dark_market] => 0.019 
				[dark_service] => 0.001 
				[exchange_fraudulent] => 0 
				[exchange_mlrisk_high] => 0.288 
				[exchange_mlrisk_low] => 0.26 
				[exchange_mlrisk_moderate] => 0.153 
				[exchange_mlrisk_veryhigh] => 0.097 
				[gambling] => 0.107 
				[illegal_service] => 0 
				[marketplace] => 0 
				[miner] => 0.001 
				[mixer] => 0.006 
				[payment] => 0.026 
				[ransom] => 0 
				[scam] => 0.001 
				[stolen_coins] => 0 
				[wallet] => 0.036 
			) 
			[updated_at] => 1604558257 
			[address] => bc1qu604w20gvyrf92vxue8nd4z3dvtjfe0ey2k0ck 
			[fiat_code_effective] => usd 
			[counterparty] => Array ( [id] => 741140567 ) 
			[reportedAddressBalance] => 
			[blackListsConnections] => 
			[pdfReport] => https://extrnlapiendpoint.silencatech.com/response/userdata/3F61DAC8F243253/renderer/pdf/responsedata/4444520201105083737:23585F01EF6A305 
			[asset] => BTC 
			[timestamp] => 2020-11-05 08:37:37 
		) 
	)
	*/

	add_action('premium_action_pn_amlbot_test2','def_premium_action_pn_amlbot_test2');
	function def_premium_action_pn_amlbot_test2(){
	global $premiumbox;
	
		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$access_id = pn_strip_input($premiumbox->get_option('amlbot','access_id'));
		$access_key = pn_strip_input($premiumbox->get_option('amlbot','access_key'));
		
		$class = new AMLClass($access_id, $access_key);
		$res = $class->verify_trans(is_param_post('address'), is_param_post('currency'), is_param_post('txid'), intval(is_param_post('type')));
		
		$form->error_form(print_r($res, true));
	}	
	
	/*
	Array ( 
		[result] => 1 
		[balance] => 3 
		[discount] => 0 
		[data] => Array ( 
			[riskscore] => 0.323 
			[signals] => Array ( 
				[atm] => 0 
				[dark_market] => 0 
				[dark_service] => 0 
				[exchange_fraudulent] => 0 
				[exchange_mlrisk_high] => 0.355 
				[exchange_mlrisk_low] => 0.069 
				[exchange_mlrisk_moderate] => 0.521 
				[exchange_mlrisk_veryhigh] => 0.011 
				[gambling] => 0.005 
				[illegal_service] => 0 
				[marketplace] => 0 
				[miner] => 0.005 
				[mixer] => 0.002 
				[payment] => 0.016 
				[ransom] => 0 
				[scam] => 0 
				[stolen_coins] => 0 
				[wallet] => 0.014 
			) 
			[updated_at] => 1604557094 
			[address] => 3AyxhcLmZNq36z1FrtPTxpDC6LDDW9mJxk 
			[created_at] => 1604557094 
			[amount] => 768067 
			[risky_volume] => 5458.3441624365 
			[direction] => deposit 
			[tx] => e08e0607f8c64f45f362c3e703cfba280db31347a8456353bae9807f0a26a911 
			[risky_volume_fiat] => 78 
			[fiat_code_effective] => usd 
			[blackListsConnections] => 
			[pdfReport] => https://extrnlapiendpoint.silencatech.com/response/userdata/C9709379C0B9D87/renderer/pdf/responsedata/4444020201105081813:F07AFFEB04DFB02 
			[asset] => BTC 
			[timestamp] => 2020-11-05 08:18:13 
		) 
	)
	*/
}	