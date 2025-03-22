<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_wchecks_settings', 'def_adminpage_title_pn_wchecks_settings');
	function def_adminpage_title_pn_wchecks_settings(){
		_e('Accounts verification checker settings','pn');
	}

	add_action('pn_adminpage_content_pn_wchecks_settings','def_adminpage_content_pn_wchecks_settings');
	function def_adminpage_content_pn_wchecks_settings(){
	global $wpdb;
			
		$form = new PremiumForm();	
			
		$m_id = is_extension_name(is_param_get('m_id'));

		$list_wchecks = apply_filters('list_wchecks',array());
		$list_wchecks_t = array();
		foreach($list_wchecks as $data){
			$list_wchecks_t[] = is_isset($data,'id');
		}
		
		$selects = array();
		$selects[] = array(
			'link' => admin_url("admin.php?page=pn_wchecks_settings"),
			'title' => '--' . __('Make a choice','pn') . '--',
			'background' => '',
			'default' => '',
		);		
		if(is_array($list_wchecks)){  
			foreach($list_wchecks as $data){
				$id = is_isset($data,'id');
				$title = is_isset($data,'title');	
				$selects[] = array(
					'link' => admin_url("admin.php?page=pn_wchecks_settings&m_id=".$id),
					'title' => $title,
					'background' => '',
					'default' => $id,
				);			
			}
		}	
		$form->select_box($m_id, $selects, __('Setting up','pn'));

		if(in_array($m_id,$list_wchecks_t)){
			do_action('wchecks_admin', $m_id);		
		} 
	} 
}