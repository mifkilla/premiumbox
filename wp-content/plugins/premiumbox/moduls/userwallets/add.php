<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_add_userwallets', 'pn_adminpage_title_pn_add_userwallets');
	function pn_adminpage_title_pn_add_userwallets(){
	global $bd_data, $wpdb;
	
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$bd_data = '';
			
		if($item_id){
			$bd_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."user_wallets WHERE id='$item_id'");
			if(isset($bd_data->id)){
				$data_id = $bd_data->id;
			}	
		}	
			
		if($data_id){
			_e('Edit account','pn');
		} else {
			_e('Add account','pn');
		}		
	}

	add_action('pn_adminpage_content_pn_add_userwallets','def_pn_adminpage_content_pn_add_userwallets');
	function def_pn_adminpage_content_pn_add_userwallets(){
	global $bd_data, $wpdb;

		$form = new PremiumForm();

		$data_id = intval(is_isset($bd_data,'id'));
		if($data_id){
			$title = __('Edit account','pn');
		} else {
			$title = __('Add account','pn');
		}
		
		$users = array();
		$users[0] = '-- '. __('No item','pn') .' --';
		$en_users = $wpdb->get_results("SELECT ID, user_login FROM ". $wpdb->prefix ."users ORDER BY user_login ASC"); 
		foreach($en_users as $en_user){
			$users[$en_user->ID] = is_user($en_user->user_login);
		}
		
		$currency = list_currency(__('No item','pn'));	
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_userwallets'),
			'title' => __('Back to list','pn')
		);
		if($data_id){
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_userwallets'),
				'title' => __('Add new','pn')
			);	
		}
		$form->back_menu($back_menu, $bd_data);

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
		$options['user_id'] = array(
			'view' => 'select_search',
			'title' => __('User','pn'),
			'options' => $users,
			'default' => is_isset($bd_data, 'user_id'),
			'name' => 'user_id',
		);
		$options['currency_id'] = array(
			'view' => 'select_search',
			'title' => __('Currency name','pn'),
			'options' => $currency,
			'default' => is_isset($bd_data, 'currency_id'),
			'name' => 'currency_id',
		);	
		$options['accountnum'] = array(
			'view' => 'inputbig',
			'title' => __('Account number','pn'),
			'default' => is_isset($bd_data, 'accountnum'),
			'name' => 'accountnum',
		);	
		
		$params_form = array(
			'filter' => 'pn_userwallets_addform',
			'method' => 'ajax',
			'data' => $bd_data,
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);		
	}

	add_action('premium_action_pn_add_userwallets','def_premium_action_pn_add_userwallets');
	function def_premium_action_pn_add_userwallets(){
	global $wpdb;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_userwallets'));
		
		$data_id = intval(is_param_post('data_id'));
		$last_data = '';
		if($data_id > 0){
			$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE id='$data_id'");
			if(!isset($last_data->id)){
				$data_id = 0;
			}
		}
		
		$array = array();
				
		$array['currency_id'] = $currency_id = intval(is_param_post('currency_id'));		
				
		$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE auto_status = '1' AND id='$currency_id'");
		if(!isset($item->id)){		
			$form->error_form(__('Error! Currency does not exist or disabled','pn'));	
		} else {		
			$account = pn_strip_input(is_param_post('accountnum'));	
			$array['accountnum'] = get_purse($account, $item);
		}
				
		$array['user_id'] = $user_id = intval(is_param_post('user_id'));
		$array['user_login'] = '';
		$ui = get_userdata($user_id);
		if(isset($ui->user_login)){
			$array['user_login'] = is_user($ui->user_login);
		}

		$ui = wp_get_current_user();
		$user_id = intval(is_isset($ui, 'ID'));

		$array['edit_date'] = current_time('mysql');
		$array['edit_user_id'] = $user_id;
		$array['auto_status'] = 1;
		$array = apply_filters('pn_userwallets_addform_post', $array, $last_data);
				
		if($data_id){	
			$res = apply_filters('item_userwallets_edit_before', pn_ind(), $data_id, $array, $last_data);
			if($res['ind'] == 1){
				$result = $wpdb->update($wpdb->prefix.'user_wallets', $array, array('id'=>$data_id));
				do_action('item_userwallets_edit', $data_id, $array, $last_data, $result);		
			} else { $form->error_form(is_isset($res,'error')); }
		} else {
			$res = apply_filters('item_userwallets_add_before', pn_ind(), $array); 
			if($res['ind'] == 1){
				$array['create_date'] = current_time('mysql');	
				$result = $wpdb->insert($wpdb->prefix.'user_wallets', $array);
				$data_id = $wpdb->insert_id;
				if($result){
					do_action('item_userwallets_add', $data_id, $array);
				}
			} else { $form->error_form(is_isset($res,'error')); }
		}

		$url = admin_url('admin.php?page=pn_add_userwallets&item_id='. $data_id .'&reply=true');
		$form->answer_form($url);
	}	
}