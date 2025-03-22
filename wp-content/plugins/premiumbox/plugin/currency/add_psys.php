<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_add_psys', 'def_adminpage_title_pn_add_psys');
	function def_adminpage_title_pn_add_psys(){
	global $bd_data, $wpdb;	
		
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$bd_data = '';
		
		if($item_id){
			$bd_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."psys WHERE id='$item_id'");
			if(isset($bd_data->id)){
				$data_id = $bd_data->id;
			}	
		}		
		
		if($data_id){
			_e('Edit payment system','pn');
		} else {
			_e('Add payment system','pn');
		}	
	}

	add_action('pn_adminpage_content_pn_add_psys','def_adminpage_content_pn_add_psys');
	function def_adminpage_content_pn_add_psys(){
	global $bd_data, $wpdb;

		$form = new PremiumForm();

		$data_id = intval(is_isset($bd_data,'id'));
		if($data_id){
			$title = __('Edit payment system','pn');
		} else {
			$title = __('Add payment system','pn');
		}	

		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_psys'),
			'title' => __('Back to list','pn')
		);
		if($data_id){
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_psys'),
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
		$options['psys_title'] = array(
			'view' => 'inputbig',
			'title' => __('PS title','pn'),
			'default' => is_isset($bd_data, 'psys_title'),
			'name' => 'psys_title',
			'work' => 'input',
			'ml' => 1,
		);		
		
		$psys_logo = @unserialize(is_isset($bd_data, 'psys_logo'));
		
		$options['psys_logo'] = array(
			'view' => 'uploader',
			'title' => __('Main logo','pn'),
			'default' => is_isset($psys_logo, 'logo1'),
			'name' => 'psys_logo',
			'work' => 'input',
			'ml' => 1,
		);
		
		if(get_settings_second_logo() == 1){
			$options['psys_logo_second'] = array(
				'view' => 'uploader',
				'title' => __('Additional logo','pn'),
				'default' => is_isset($psys_logo, 'logo2'),
				'name' => 'psys_logo_second',
				'work' => 'input',
				'ml' => 1,
			);		
		}
		
		$params_form = array(
			'filter' => 'pn_psys_addform',
			'method' => 'ajax',
			'data' => $bd_data,
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);	
	}

	add_action('premium_action_pn_add_psys','def_premium_action_pn_add_psys');
	function def_premium_action_pn_add_psys(){
	global $wpdb;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_currency'));
		
		$data_id = intval(is_param_post('data_id')); 
		
		$last_data = '';
		if($data_id > 0){
			$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "psys WHERE id='$data_id'");
			if(!isset($last_data->id)){
				$data_id = 0;
			}
		}		
		
		$array = array();
		$array['psys_title'] = $psys_title = pn_strip_input(is_param_post_ml('psys_title'));
				
		if(!$psys_title){ 
			$form->error_form(__('Error! You did not enter the name','pn'));
		}
		
		$logo1 = pn_strip_input(is_param_post_ml('psys_logo'));
		$logo2 = pn_strip_input(is_param_post_ml('psys_logo_second'));
		if(!$logo2){
			$logo2 = $logo1;
		}
		$psys_logo = array(
			'logo1' => $logo1,
			'logo2' => $logo2,
		);
		$array['psys_logo'] = @serialize($psys_logo);
			
		$ui = wp_get_current_user();
		$user_id = intval(is_isset($ui, 'ID'));

		$array['edit_date'] = current_time('mysql');
		$array['edit_user_id'] = $user_id;
		$array['auto_status'] = 1;	
		$array = apply_filters('pn_psys_addform_post',$array, $last_data);
				
		$count_psys = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."psys WHERE psys_title='$psys_title' AND id != '$data_id'");
		if($count_psys > 0){
			$form->error_form(__('Error! This currency code already exists','pn'));
		}
		
		if($data_id){
			$res = apply_filters('item_psys_edit_before', pn_ind(), $data_id, $array, $last_data);
			if($res['ind'] == 1){
				$result = $wpdb->update($wpdb->prefix.'psys', $array, array('id' => $data_id));
				do_action('item_psys_edit', $data_id, $array, $last_data, $result);
			} else { $form->error_form(is_isset($res,'error')); }
		} else {
			$res = apply_filters('item_psys_add_before', pn_ind(), $array);
			if($res['ind'] == 1){
				$array['create_date'] = current_time('mysql');
				$result = $wpdb->insert($wpdb->prefix.'psys', $array);
				$data_id = $wpdb->insert_id;
				if($result){
					do_action('item_psys_add', $data_id, $array);
				}
			} else { $form->error_form(is_isset($res,'error')); }
		}

		$url = admin_url('admin.php?page=pn_add_psys&item_id='. $data_id .'&reply=true');
		$form->answer_form($url);
	}
}	