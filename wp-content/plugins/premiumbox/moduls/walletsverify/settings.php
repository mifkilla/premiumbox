<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_userwallets_verify_settings', 'def_adminpage_title_pn_userwallets_verify_settings');
	function def_adminpage_title_pn_userwallets_verify_settings(){
		_e('Settings','pn');
	} 

	add_action('pn_adminpage_content_pn_userwallets_verify_settings','def_adminpage_content_pn_userwallets_verify_settings');
	function def_adminpage_content_pn_userwallets_verify_settings(){
	global $premiumbox;	
		
		$form = new PremiumForm();
		
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Settings','pn'),
			'submit' => __('Save','pn'),
		);		
		$options['acc_status'] = array(
			'view' => 'select',
			'title' => __('Allow send request','pn'),
			'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
			'default' => $premiumbox->get_option('usve','acc_status'),
			'name' => 'acc_status',
		);
		$options['uniq'] = array(
			'view' => 'select',
			'title' => __('Prohibit adding account number if it has already been added','pn'),
			'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
			'default' => $premiumbox->get_option('usve','uniq'),
			'name' => 'uniq',
		);
		$options['disabledelete'] = array(
			'view' => 'select',
			'title' => __('Prevent user from deleting verified accounts','pn'),
			'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
			'default' => $premiumbox->get_option('usve','disabledelete'),
			'name' => 'disabledelete',
		);
		$options['create_notacc'] = array(
			'view' => 'select',
			'title' => __('Allow creating orders if account not verified','pn'),
			'default' => $premiumbox->get_option('usve','create_notacc'),
			'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
			'name' => 'create_notacc',
		);		
		$tags = array(
			'verifylink' => array(
				'title' => __('Link to account verification','pn'),
				'start' => '[verifylink]',
			),
			'accountnum' => array(
				'title' => __('Account number','pn'),
				'start' => '[accountnum]',		
			),
		);
		$options['accounterror'] = array(
			'view' => 'editor',
			'title' => __('Message if the account number is not verified and orders cannot be created for unverified users', 'pn'),
			'default' => $premiumbox->get_option('usve','accounterror'),
			'rows' => '12',
			'tags' => $tags, 
			'name' => 'accounterror',
			'work' => 'text',
			'ml' => 1,
		);
		
		$params_form = array(
			'filter' => 'pn_userwallets_verify_settings_adminform',
			'method' => 'ajax',
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);		
	} 

	add_action('premium_action_pn_userwallets_verify_settings','def_premium_action_pn_userwallets_verify_settings');
	function def_premium_action_pn_userwallets_verify_settings(){
	global $wpdb, $premiumbox;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_userwallets'));

		$options = array('acc_status', 'uniq', 'disabledelete','create_notacc');
		foreach($options as $key){
			$val = is_sum(is_param_post($key));
			$premiumbox->update_option('usve',$key,$val);
		}
		$val = pn_strip_input(is_param_post_ml('accounterror'));
		$premiumbox->update_option('usve', 'accounterror', $val);
				
		do_action('pn_userwallets_verify_settings_adminform_post');
				
		$url = admin_url('admin.php?page=pn_userwallets_verify_settings&reply=true');
		$form->answer_form($url);
	}
}	