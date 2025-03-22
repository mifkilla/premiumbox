<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('admin_menu', 'admin_menu_mobile', 50);
	function admin_menu_mobile(){
	global $premiumbox;
		if(current_user_can('administrator')){
			add_submenu_page('pn_config', __('Mobile version settings','pn'), __('Mobile version settings','pn'), 'administrator', 'pn_mobile_settings', array($premiumbox, 'admin_temp'));
		}
	}

	add_action('pn_adminpage_title_pn_mobile_settings', 'def_adminpage_title_pn_mobile_settings');
	function def_adminpage_title_pn_mobile_settings(){
		_e('Mobile version settings','pn');
	}

	add_action('pn_adminpage_content_pn_mobile_settings','def_adminpage_content_pn_mobile_settings');
	function def_adminpage_content_pn_mobile_settings(){
	global $wpdb, $premiumbox;

		$form = new PremiumForm();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Exchange settings','pn'),
			'submit' => __('Save','pn'),
		);
		
		$tablevids = array(
			'0'=> sprintf(__('Table %1s','pn'),'1'),
			'2'=> sprintf(__('Table %1s','pn'),'3'),
			'99'=> __('Exchange form','pn'),
		);
		$tablevids = apply_filters('mobile_exchange_tablevids_list', $tablevids);
		
		$options['tablevid'] = array(
			'view' => 'select',
			'title' => __('Exchange pairs table type','pn'),
			'options' => $tablevids,
			'default' => $premiumbox->get_option('mobile','tablevid'),
			'name' => 'tablevid',
		);		
		if(get_settings_second_logo() == 1){
			$options['tableicon'] = array(
				'view' => 'select',
				'title' => __('Show PS logo in exchange table','pn'),
				'options' => array('0'=>__('Main logo','pn'),'1'=>__('Additional logo','pn')),
				'default' => $premiumbox->get_option('mobile','tableicon'),
				'name' => 'tableicon',
			);	
		}
		
		$params_form = array(
			'filter' => 'pn_mobile_exchange_settings_option',
			'method' => 'ajax',
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);	
		
	} 

	add_action('premium_action_pn_mobile_settings','def_premium_action_pn_mobile_settings');
	function def_premium_action_pn_mobile_settings(){
	global $wpdb, $premiumbox;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();		
		
		pn_only_caps(array('administrator'));

		$options = array('tablevid','tableicon');
		foreach($options as $key){
			$val = pn_strip_input(is_param_post($key));
			$premiumbox->update_option('mobile',$key,$val);
		}			
				
		do_action('pn_mobile_exchange_settings_option_post');
		
		$url = admin_url('admin.php?page=pn_mobile_settings&reply=true');
		$form->answer_form($url);
	}	
}	