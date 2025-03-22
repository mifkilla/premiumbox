<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_seo_exchange_directions', 'pn_admin_title_seo_exchange_directions');
	function pn_admin_title_seo_exchange_directions(){
		_e('Exchange directions','pn');
	}

	add_action('pn_adminpage_content_seo_exchange_directions','def_pn_admin_content_seo_exchange_directions');
	function def_pn_admin_content_seo_exchange_directions(){
	global $wpdb;

		$form = new PremiumForm();

		$direction_id = intval(is_param_get('direction_id'));
		
		$selects = array();
		$selects[] = array(
			'link' => admin_url("admin.php?page=seo_exchange_directions"),
			'title' => '--' . __('Choice','pn') . '--',
			'background' => '',
			'default' => '',
		);		
		$directions = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions WHERE auto_status = '1' ORDER BY id DESC");
		foreach($directions as $direction){
			$selects[] = array(
				'link' => admin_url("admin.php?page=seo_exchange_directions&direction_id=" . $direction->id),
				'title' => pn_strip_input($direction->tech_name),
				'background' => '',
				'default' => $direction->id,
			);
		}
		
		$form->select_box($direction_id, $selects, __('Setting up','pn'));	

		if($direction_id > 0){

			$seo = get_direction_meta($direction_id, 'seo');	

			$options = array();
			$options['hidden_block'] = array(
				'view' => 'hidden_input',
				'name' => 'direction_id',
				'default' => $direction_id,
			);	
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => '',
				'submit' => __('Save','pn'),
			);
			$options['description_txt'] = array(
				'view' => 'editor',
				'title' => __('Exchange description','pn'),
				'default' => get_direction_txtmeta($direction_id, 'description_txt'),
				'name' => 'description_txt',
				'rows' => '12',
				'standart_tags' => 1,
				'tags' => apply_filters('direction_instruction_tags', array(), 'description_txt'),
				'ml' => 1,
			);			
			$options['seo_exch_title'] = array(
				'view' => 'inputbig',
				'title' => __('Exchange title (H1)','pn'),
				'default' => is_isset($seo, 'seo_exch_title'),
				'name' => 'seo_exch_title',
				'work' => 'input',
				'ml' => 1,
			);
			$options['seo_title'] = array(
				'view' => 'inputbig',
				'title' => __('Page title','pn'),
				'default' => is_isset($seo, 'seo_title'),
				'name' => 'seo_title',
				'work' => 'input',
				'ml' => 1,
			);			
			$options['seo_key'] = array(
				'view' => 'textarea',
				'title' => __('Page keywords','pn'),
				'default' => is_isset($seo, 'seo_key'),
				'name' => 'seo_key',
				'rows' => '6',
				'word_count' => 1,
				'ml' => 1,
			);
			$options['seo_descr'] = array(
				'view' => 'textarea',
				'title' => __('Page description','pn'),
				'default' => is_isset($seo, 'seo_descr'),
				'name' => 'seo_descr',
				'rows' => '12',
				'word_count' => 1,
				'ml' => 1,
			);
			$options['ogp_title'] = array(
				'view' => 'inputbig',
				'title' => __('OGP title','pn'),
				'default' => is_isset($seo, 'ogp_title'),
				'name' => 'ogp_title',
				'work' => 'input',
				'ml' => 1,
			);
			$options['ogp_descr'] = array(
				'view' => 'textarea',
				'title' => __('OGP description','pn'),
				'default' => is_isset($seo, 'ogp_descr'),
				'name' => 'ogp_descr',
				'rows' => '12',
				'word_count' => 1,
				'ml' => 1,
			);			
			$params_form = array(
				'filter' => 'seo_exchange_directions_form',
				'data' => $seo,
				'method' => 'ajax',
			);
			$form->init_form($params_form, $options);
		}	
	}
	
	add_action('premium_action_seo_exchange_directions','def_premium_action_seo_exchange_directions');
	function def_premium_action_seo_exchange_directions(){
	global $wpdb;	
			
		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_seo'));
			
		$direction_id = intval(is_param_post('direction_id')); 
		if($direction_id > 0){	
			$description_txt = pn_strip_text(is_param_post_ml('description_txt'));
			$res = update_direction_txtmeta($direction_id, 'description_txt', $description_txt);
				
			$seo = array();
			$seo['seo_exch_title'] = pn_strip_input(is_param_post_ml('seo_exch_title'));
			$seo['seo_title'] = pn_strip_input(is_param_post_ml('seo_title'));					
			$seo['seo_key'] = pn_strip_input(is_param_post_ml('seo_key'));
			$seo['seo_descr'] = pn_strip_input(is_param_post_ml('seo_descr'));								
			$seo['ogp_title'] = pn_strip_input(is_param_post_ml('ogp_title'));
			$seo['ogp_descr'] = pn_strip_input(is_param_post_ml('ogp_descr'));
			update_direction_meta($direction_id, 'seo', $seo);			
		}
		
		$url = admin_url('admin.php?page=seo_exchange_directions&direction_id='. $direction_id .'&reply=true');
		$form->answer_form($url);
	}		
}