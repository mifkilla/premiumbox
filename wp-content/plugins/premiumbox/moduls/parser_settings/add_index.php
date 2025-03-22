<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_add_parser_index', 'pn_admin_title_pn_add_parser_index');
	function pn_admin_title_pn_add_parser_index(){
		$id = is_extension_name(is_param_get('item_key'));
		if(strlen($id) > 0){
			_e('Edit coefficient','pn');
		} else {
			_e('Add coefficient','pn'); 
		}
	}

	add_action('pn_adminpage_content_pn_add_parser_index','def_pn_admin_content_pn_add_parser_index');
	function def_pn_admin_content_pn_add_parser_index(){
	global $wpdb;

		$id = is_extension_name(is_param_get('item_key'));
		$data_id = '';
		
		$pindexes = get_option('parser_indexes');
		if(!is_array($pindexes)){ $pindexes = array(); }	
		
		$data = array();
		
		if(isset($pindexes[$id])){
			$data_id = $id;
			$data = array(
				'name' => $id,
				'sum' => $pindexes[$id],
			);
		}	
		
		if(strlen($data_id) > 0){
			$title = __('Edit coefficient','pn') . ' "' . $data_id . '"';
		} else {
			$title = __('Add coefficient','pn');
		}
		
		$form = new PremiumForm();
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_parser_index'),
			'title' => __('Back to list','pn')
		);
		if(strlen($data_id) > 0){
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_parser_index'),
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
		$options['sum'] = array(
			'view' => 'inputbig',
			'title' => __('Amount','pn'),
			'default' => is_isset($data, 'sum'),
			'name' => 'sum',
		);	
		if(strlen($data_id) == 0){
			$options['name'] = array(
				'view' => 'inputbig',
				'title' => __('Coefficient name','pn'),
				'default' => is_isset($data, 'name'),
				'name' => 'name',
			);	
		}
		
		if(strlen($data_id) > 0){
			$comment_count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."comment_system WHERE itemtype='pindex' AND item_id = '$data_id'");
			$options['system_comment'] = array(
				'view' => 'textfield',
				'title' => __('Comment','pn'),
				'default' => get_comment_label('pindex', $data_id, $comment_count),
			);	
		}		
		
		$params_form = array(
			'filter' => '',
			'method' => 'ajax',
		);
		$form->init_form($params_form, $options);
	} 

	add_action('premium_action_pn_add_parser_index','def_premium_action_pn_add_parser_index');
	function def_premium_action_pn_add_parser_index(){
	global $wpdb;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_directions','pn_parser'));	

		$data_key = is_extension_name(is_param_post('item_key'));
		
		$name = is_extension_name(str_replace('index_','',is_param_post('name')));
		$sum = is_sum(is_param_post('sum'));
		
		$pindexes = get_option('parser_indexes');
		if(!is_array($pindexes)){ $pindexes = array(); }
		
		if(strlen($data_key) > 0 and isset($pindexes[$data_key])){
			$pindexes[$data_key] = $sum;
		} else {
			if(!$name){ $form->error_form(__('Coefficient not specified','pn')); }
			$pindexes[$name] = $sum;
			$data_key = $name;
		}	
		
		update_option('parser_indexes', $pindexes);
		
		do_action('parser_index_edit_end');

		$url = admin_url('admin.php?page=pn_add_parser_index&item_key='. $data_key .'&reply=true');
		$form->answer_form($url);
	}
}	