<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('admin_menu', 'admin_menu_txtxml', 500);
	function admin_menu_txtxml(){
	global $premiumbox;	
		add_submenu_page("pn_config", __('TXT and XML export settings','pn'), __('TXT and XML export settings','pn'), 'administrator', "pn_txtxml", array($premiumbox, 'admin_temp'));
	}
	
	add_action('pn_adminpage_title_pn_txtxml', 'def_adminpage_title_pn_txtxml');
	function def_adminpage_title_pn_txtxml($page){
		_e('TXT and XML export settings','pn');
	} 	
	 
	add_action('pn_adminpage_content_pn_txtxml','pn_adminpage_content_pn_txtxml');
	function pn_adminpage_content_pn_txtxml(){
	global $wpdb, $premiumbox;

		$form = new PremiumForm();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('TXT and XML export settings','pn'),
			'submit' => __('Save','pn'),
		);
		$options['txt'] = array(
			'view' => 'select',
			'title' => __('TXT file','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => $premiumbox->get_option('txtxml','txt'),
			'name' => 'txt',
		);
		$options['site_txt'] = array(
			'view' => 'select',
			'title' => __('Show link to TXT file on site','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => $premiumbox->get_option('txtxml', 'site_txt'),
			'name' => 'site_txt',
		);	
		$options['line_txt'] = array(
			'view' => 'line',
		);
		$options['xml'] = array(
			'view' => 'select',
			'title' => __('XML file','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => $premiumbox->get_option('txtxml','xml'),
			'name' => 'xml',
		);	
		$options['site_xml'] = array(
			'view' => 'select',
			'title' => __('Show link to XML file on site','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => $premiumbox->get_option('txtxml','site_xml'),
			'name' => 'site_xml',
		);
		$options['line_xml'] = array(
			'view' => 'line',
		);	
		// $options['json'] = array(
			// 'view' => 'select',
			// 'title' => __('JSON file','pn'),
			// 'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			// 'default' => $premiumbox->get_option('txtxml','json'),
			// 'name' => 'json',
		// );	
		// $options['site_json'] = array(
			// 'view' => 'select',
			// 'title' => __('Show link to JSON file on site','pn'),
			// 'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			// 'default' => $premiumbox->get_option('txtxml','site_json'),
			// 'name' => 'site_json',
		// );	
		// $options['line_json'] = array(
			// 'view' => 'line',
		// );	
		$options['hash'] = array(
			'view' => 'select',
			'title' => __('Add personal hash to URL of files with exchange rates','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => $premiumbox->get_option('txtxml','hash'),
			'name' => 'hash',
		);	
		$options['hash_help'] = array(
			'view' => 'help',
			'title' => __('More info','pn'),
			'default' => __('Personal hash is set in file /wp-content/plugins/premiumbox/userdata.php','pn'),
		);	
		$options['create'] = array(
			'view' => 'select',
			'title' => __('Static file with exchange rates','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => $premiumbox->get_option('txtxml','create'),
			'name' => 'create',
		);	
		$options['live_help'] = array(
			'view' => 'help',
			'title' => __('More info','pn'),
			'default' => __('File contains static data. File will be updated only when changing the characteristics of the exchange direction. For example, when changing the exchange rate or reserve and etc.','pn'),
		);	
		$options['line2'] = array(
			'view' => 'line',
		);	
		$options['fromfee'] = array(
			'view' => 'select',
			'title' => __('For fromfee parameter, pass value','pn'),
			'options' => array('0'=>__('additional fee of exchange office charged from sender','pn'), '1'=>__('payment system fee for wallet','pn'), '2'=>__('payment system fee for a verified wallet','pn')),
			'default' => $premiumbox->get_option('txtxml','fromfee'),
			'name' => 'fromfee',
		);
		$options['tofee'] = array(
			'view' => 'select',
			'title' => __('For tofee parameter, pass value','pn'),
			'options' => array('0'=>__('additional fee of exchange office charged from recipient','pn'), '1'=>__('payment system fee for wallet','pn'), '2'=>__('payment system fee for a verified wallet','pn')),
			'default' => $premiumbox->get_option('txtxml','tofee'),
			'name' => 'tofee',
		);
		$options['line3'] = array(
			'view' => 'line',
		);		
		$options['decimal_with'] = array(
			'view' => 'select',
			'title' => __('Number of simbols after comma','pn'),
			'options' => array('0'=>__('depending on currency settings','pn'), '1'=>__('depending on setting below','pn')),
			'default' => $premiumbox->get_option('txtxml','decimal_with'),
			'name' => 'decimal_with',
		);
		$options['decimal'] = array(
			'view' => 'input',
			'title' => __('Number of simbols after comma (forcibly)','pn'),
			'default' => $premiumbox->get_option('txtxml','decimal'),
			'name' => 'decimal',
		);	
		$params_form = array(
			'filter' => 'pn_txtxml_option',
			'method' => 'ajax',
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);
	} 
	
	add_action('premium_action_pn_txtxml','premium_action_pn_txtxml');
	function premium_action_pn_txtxml(){
	global $wpdb, $premiumbox;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$options = array('hash','create','decimal_with','decimal','txt','xml','json','site_txt','site_xml','site_json','fromfee','tofee');		
		foreach($options as $key){
			$val = intval(is_param_post($key));
			$premiumbox->update_option('txtxml',$key, $val);
		}				

		do_action('pn_txtxml_option_post');
		
		$url = admin_url('admin.php?page=pn_txtxml&reply=true');
		$form->answer_form($url);
	}

	add_action('pn_adminpage_content_pn_directions','txtxml_pn_admin_content_pn_directions', 0);
	add_action('pn_adminpage_content_pn_txtxml','txtxml_pn_admin_content_pn_directions', 0);
	function txtxml_pn_admin_content_pn_directions(){
	global $premiumbox;
		
		$form = new PremiumForm();
		$hash = '';
		if($premiumbox->get_option('txtxml','hash') == 1){
			$hash = get_hash_cron('?');
		}
		$links = array();
		$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml','txt'), 'txt');
		if($show_files == 1){
			$links['txt'] = array(
				'url' => get_request_link('exporttxt','txt') . $hash,
				'title' => 'TXT',
			);
		}
		$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml','xml'), 'xml');
		if($show_files == 1){	
			$links['xml'] = array(
				'url' => get_request_link('exportxml','xml') . $hash,
				'title' => 'XML',
			);
		}
		/* 		
		$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml','json'), 'json');
		if($show_files == 1){	
			$links['json'] = array(
				'url' => get_request_link('exportjson','html') . $hash,
				'title' => 'JSON',		
			);
		} 
		*/
		if(count($links) > 0){
			$text = __('Links to files containing rates','pn').':';
			foreach($links as $link){
				$text .= ' <a href="'. is_isset($link,'url') .'" target="_blank" rel="noreferrer noopener">'. is_isset($link,'url') .'</a>';
			}
			$form->substrate($text);
		}
	}
}

add_filter('account_list_pages','pn_account_list_pages');
function pn_account_list_pages($account_list_pages){	
global $wpdb, $premiumbox;	
		
	$link_data = '';
	if($premiumbox->get_option('txtxml','hash') == 1){
		$link_data = get_hash_cron('?');
	}
	if(is_ml()){
		$lang = get_locale();
		$lang_key = get_lang_key($lang);
		$link_data = '?lang=' . $lang_key;
		if($premiumbox->get_option('txtxml','hash') == 1){
			$link_data .= get_hash_cron('&');
		}	
	}
		
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml','txt'), 'txt');
	if($show_files == 1){
		if($premiumbox->get_option('txtxml','site_txt') == 1){
			$account_list_pages['exporttxt'] = array(
				'title' => __('TXT file containing rates','pn'),
				'url' => get_request_link('exporttxt','txt').$link_data,
				'type' => 'target_link',
			);
		}		
	}
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml','xml'), 'xml');
	if($show_files == 1){
		if($premiumbox->get_option('txtxml','site_xml') == 1){
			$account_list_pages['exportxml'] = array(
				'title' => __('XML file containing rates','pn'),
				'url' => get_request_link('exportxml','xml').$link_data,
				'type' => 'target_link',
			);
		}		
	}
/* 	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml','json'), 'json');
	if($show_files == 1){
		if($premiumbox->get_option('txtxml','site_json') == 1){
			$account_list_pages['exportjson'] = array(
				'title' => __('JSON file containing rates','pn'),
				'url' => get_request_link('exportjson','html').$link_data,
				'type' => 'target_link',
			);
		}		
	} */		
		
	return $account_list_pages;
}	