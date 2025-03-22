<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('pn_admin_title_all_add_blacklist') and is_admin()){
	add_action('pn_adminpage_title_all_add_blacklist', 'pn_admin_title_all_add_blacklist');
	function pn_admin_title_all_add_blacklist(){
		$id = intval(is_param_get('item_id'));
		if($id){
			_e('Edit item','pn');
		} else {
			_e('Add item','pn');
		}
	}

	add_action('pn_adminpage_content_all_add_blacklist','def_pn_admin_content_all_add_blacklist');
	function def_pn_admin_content_all_add_blacklist(){
	global $wpdb;

		$form = new PremiumForm();

		$id = intval(is_param_get('item_id'));
		$data_id = 0;
		$data = '';
		
		if($id){
			$data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."blacklist WHERE id='$id'");
			if(isset($data->id)){
				$data_id = $data->id;
			}	
		}

		if($data_id){
			$title = __('Edit item','pn');
		} else {
			$title = __('Add item','pn');
		}
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=all_blacklist'),
			'title' => __('Back to list','pn')
		);
		if($data_id){
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=all_add_blacklist'),
				'title' => __('Add new','pn')
			);	
		}
		$form->back_menu($back_menu, $data);	

		$options = array();
		
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'data_id',
			'default' => $data_id,
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save','pn'),
		);	
		$options['meta_value'] = array(
			'view' => 'inputbig',
			'title' => __('Value','pn'),
			'default' => is_isset($data, 'meta_value'),
			'name' => 'meta_value',
		);				
		$options['meta_key'] = array(
			'view' => 'select',
			'title' => __('Type','pn'),
			'options' => array('0'=>__('account','pn'),'1'=>__('e-mail','pn'),'2'=>__('mobile phone no.','pn'),'3'=>__('skype','pn'),'4'=>__('ip','pn')),
			'default' => is_isset($data, 'meta_key'),
			'name' => 'meta_key',
		);
		$options['comment_text'] = array(
			'view' => 'textarea',
			'title' => __('Comment','pn'),
			'default' => is_isset($data, 'comment_text'),
			'name' => 'comment_text',
			'rows' => '10',
		);	
		
		$params_form = array(
			'filter' => 'all_blacklist_addform',
			'method' => 'ajax',
			'data' => $data,
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);	
	}

	add_action('premium_action_all_add_blacklist','def_premium_action_all_add_blacklist');
	function def_premium_action_all_add_blacklist(){
	global $wpdb;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_blacklist'));
		
		$data_id = intval(is_param_post('data_id')); 
		$last_data = '';
		if($data_id > 0){
			$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE id='$data_id'");
			if(!isset($last_data->id)){
				$data_id = 0;
			}
		}			
				
		$array = array();
		$array['meta_value'] = pn_strip_input(str_replace('+','',is_param_post('meta_value')));
		if(!$array['meta_value']){ $form->error_form(__('Value is not entered','pn')); }		
		$array['meta_key'] = intval(is_param_post('meta_key'));	
		$array['comment_text'] = pn_strip_input(is_param_post('comment_text'));	
		
		$array = apply_filters('all_blacklist_addform_post',$array,$last_data);
				
		if($data_id){
			$wpdb->update($wpdb->prefix.'blacklist', $array, array('id'=>$data_id));
		} else {
			$wpdb->insert($wpdb->prefix.'blacklist', $array);
			$data_id = $wpdb->insert_id;	
		}

		$url = admin_url('admin.php?page=all_add_blacklist&item_id='. $data_id .'&reply=true');
		$form->answer_form($url);
	}
}	