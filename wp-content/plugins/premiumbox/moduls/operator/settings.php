<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_operator', 'pn_adminpage_title_pn_operator');
	function pn_adminpage_title_pn_operator($page){
		_e('Work status','pn');
	} 

	add_action('pn_adminpage_content_pn_operator','def_pn_adminpage_content_pn_operator');
	function def_pn_adminpage_content_pn_operator(){
	global $wpdb, $premiumbox;

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save','pn'),
		);
		
		$status_operator = apply_filters('status_operator', array());

		$options['operator_type'] = array(
			'view' => 'select',
			'title' => __('Principle of determining work status','pn'),
			'options' => array('0'=>__('Manually','pn'), '1'=>__('Automatically','pn'), '2'=> __('Schedule','pn')),
			'default' => $premiumbox->get_option('operator_type'),
			'name' => 'operator_type',
			'work' => 'int',
		);	
		
		$options['line1'] = array(
			'view' => 'line',
		);	
		
		if(is_array($status_operator)){
			foreach($status_operator as $key => $title){
				$options['text'.$key] = array(
					'view' => 'inputbig',
					'title' => __('Text','pn').' "'. $title .'"',
					'default' => $premiumbox->get_option('statuswork','text'.$key),
					'name' => 'text'.$key,
					'work' => 'input',
					'ml' => 1,
				);
				$options['link'.$key] = array(
					'view' => 'inputbig',
					'title' => __('Link','pn').' "'. $title .'"',
					'default' => $premiumbox->get_option('statuswork','link'.$key),
					'name' => 'link'.$key,
					'work' => 'input',
					'ml' => 1,
				);			
			}
		}	
		
		$options['line2'] = array(
			'view' => 'line',
		);	
		
		$options['show_button'] = array(
			'view' => 'select',
			'title' => __('Operator button','pn'),
			'options' => array('0'=>__('Hide button','pn'), '1'=>__('Show button','pn')),
			'default' => $premiumbox->get_option('statuswork','show_button'),
			'name' => 'show_button',
			'work' => 'int',
		);
		$options['location'] = array(
			'view' => 'select',
			'title' => __('Button position','pn'),
			'options' => array('0'=>__('Left','pn'), '1'=>__('Right','pn')),
			'default' => $premiumbox->get_option('statuswork','location'),
			'name' => 'location',
			'work' => 'int',
		);	
		
		$options['center_title'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save','pn'),
		);	
		$options['clear1'] = array(
			'view' => 'clear_table',
		);	
		$options['second_title'] = array(
			'view' => 'h3',
			'title' => __('Manual mode settings','pn'),
			'submit' => __('Save','pn'),
		);
		$options['operator'] = array(
			'view' => 'select',
			'title' => __('Status','pn'),
			'options' => $status_operator,
			'default' => $premiumbox->get_option('operator'),
			'name' => 'operator',
			'work' => 'int',
		);	
		$options['bottom_title2'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save','pn'),
		);	
		$options['clear2'] = array(
			'view' => 'clear_table',
		);	
		$options['third_title'] = array(
			'view' => 'h3',
			'title' => __('Auto mode settings','pn'),
			'submit' => __('Save','pn'),
		);	
		$options['op_in'] = array(
			'view' => 'select',
			'title' => __('Operator logged in','pn'),
			'options' => $status_operator,
			'default' => $premiumbox->get_option('statuswork','op_in'),
			'name' => 'op_in',
			'work' => 'int',
		);	
		$options['op_out'] = array(
			'view' => 'select',
			'title' => __('Operator logged out','pn'),
			'options' => $status_operator,
			'default' => $premiumbox->get_option('statuswork','op_out'),
			'name' => 'op_out',
			'work' => 'int',
		);		
		$options['bottom_title3'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save','pn'),
		);	
		$options['clear3'] = array(
			'view' => 'clear_table',
		);	
		$options['four_title'] = array(
			'view' => 'h3',
			'title' => __('Schedule settings','pn'),
			'submit' => __('Save','pn'),
		);	
		
		$options['sh_def'] = array(
			'view' => 'select',
			'title' => __('Default status','pn'),
			'options' => $status_operator,
			'default' => $premiumbox->get_option('statuswork','sh_def'),
			'name' => 'sh_def',
			'work' => 'int',
		);	
		
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'pn_operator_option',
			'method' => 'ajax',
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);	
		
	}  

	add_action('premium_action_pn_operator','def_premium_action_pn_operator');
	function def_premium_action_pn_operator(){
	global $wpdb, $premiumbox;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$status_operator = apply_filters('status_operator', array());			
		
		$options = array('operator','operator_type');					
		foreach($options as $key){
			$premiumbox->update_option($key,'', intval(is_param_post($key)));
		}
			
		$options = array('location','sh_def','op_in','op_out','show_button');					
		foreach($options as $key){
			$premiumbox->update_option('statuswork', $key, intval(is_param_post($key)));				
		}			
				
		if(is_array($status_operator)){
			foreach($status_operator as $key => $title){
				$val = pn_strip_input(is_param_post_ml('text'.$key));
				$premiumbox->update_option('statuswork','text'.$key,$val);
					
				$val = pn_strip_input(is_param_post_ml('link'.$key));
				$premiumbox->update_option('statuswork','link'.$key,$val);				
			}
		}		
		
		$url = admin_url('admin.php?page=pn_operator&reply=true');
		$form->answer_form($url);
	} 
}