<?php
if( !defined( 'ABSPATH')){ exit(); } 

if(!function_exists('get_usve_doc_view')){
	function get_usve_doc_view($uv_field_user_id){
		return pn_link('usvedoc_view').'&id='. $uv_field_user_id;
	}
}

if(!function_exists('def_premium_action_usvedoc_view')){
	add_action('premium_action_usvedoc_view', 'def_premium_action_usvedoc_view');
	function def_premium_action_usvedoc_view(){
		global $wpdb; 

		$plugin = get_plugin_class();

		pn_only_caps(array('administrator','pn_userverify'));	

		$id = intval(is_param_get('id'));
		if($id < 1){
			pn_display_mess(__('Error!','pn'));
		}

		$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."uv_field_user WHERE id='$id'");
		$file_id = intval(is_isset($data,'id'));

		if($file_id < 1){
			pn_display_mess(__('Error!','pn'));
		}

		$file = $plugin->upload_dir . 'userverify/'. $data->uv_id .'/'. $file_id .'.php';
		$newfile = $plugin->upload_dir .'usveshow/'. $data->uv_data;
		$linkfile = $plugin->upload_url .'usveshow/'. $data->uv_data;
		$path = $plugin->upload_dir . 'usveshow/';
		if(!is_dir($path)){ 
			@mkdir($path , 0777);
		}	
		
		if(is_file($file) and is_dir($path)){
			$fdata = @file_get_contents($file);
			$fdata = str_replace('%star%', '*', $fdata);
			$fdata = get_phpf_data($fdata);
			$file_open = @fopen($newfile, 'w');
			@fwrite($file_open, $fdata);
			@fclose($file_open);
				
			if(is_file($newfile)){	
				wp_redirect($linkfile);
				exit;		
			} else {
				pn_display_mess(__('Error! File does not copy','pn'));
			}
		} else {
			pn_display_mess(__('Error! File does not exist','pn'));
		}
	}
}