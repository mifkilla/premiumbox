<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_add_vaccounts_many', 'pn_admin_title_pn_add_vaccounts_many');
	function pn_admin_title_pn_add_vaccounts_many(){
		_e('Add list','pn');
	}

	add_action('pn_adminpage_content_pn_add_vaccounts_many','def_pn_admin_content_pn_add_vaccounts_many');
	function def_pn_admin_content_pn_add_vaccounts_many(){
	global $wpdb;

		$form = new PremiumForm();

		$title = __('Add list','pn');
		$currencies = list_currency(__('No item','pn'));		
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_vaccounts'),
			'title' => __('Back to list','pn')
		);
		$form->back_menu($back_menu, '');

		$options = array();	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save','pn'),
		);	
		$options['currency_id'] = array(
			'view' => 'select_search',
			'title' => __('Currency name','pn'),
			'options' => $currencies,
			'default' => 0,
			'name' => 'currency_id',
		);	
		$options['items'] = array(
			'view' => 'textarea',
			'title' => __('Accounts (at the beginning of a new line)','pn'),
			'default' => '',
			'name' => 'items',
			'rows' => '20',
		);
		$options['status'] = array(
			'view' => 'select',
			'title' => __('Status','pn'),
			'options' => array('0'=>__('inactive account','pn'),'1'=>__('active account','pn')),
			'default' => 1,
			'name' => 'status',
		);	
		$params_form = array(
			'filter' => 'pn_add_vaccounts_many_addform',
			'method' => 'ajax',
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);					
	}

	add_action('premium_action_pn_add_vaccounts_many','def_premium_action_pn_add_vaccounts_many');
	function def_premium_action_pn_add_vaccounts_many(){
	global $wpdb;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_vaccounts'));
		
		$items = explode("\n",is_param_post('items'));
		if(is_array($items)){
					
			$currency_id = intval(is_param_post('currency_id'));
			$status = intval(is_param_post('status'));	
			
			foreach($items as $item){
				$accountnum = pn_strip_input($item);
				if($accountnum){
					$array = array(
						'currency_id' => $currency_id,
						'accountnum' => $accountnum,
						'status' => $status,
					);
					$wpdb->insert($wpdb->prefix.'currency_accounts', $array);
					$data_id = $wpdb->insert_id;
					
					if($data_id){
						$otv = update_vaccs_txtmeta($data_id, 'accountnum', $accountnum);
						if($otv != 1){
							$form->error_form(sprintf(__('Error! Directory <b>%s</b> do not exist or cannot be written! Create this directory or get permission 777.','pn'),'/wp-content/pn_uploads/vaccsmeta/'));
						}							
					}
							
				}
			}
		}

		$url = admin_url('admin.php?page=pn_vaccounts&reply=true');
		$form->answer_form($url);
	}
}	