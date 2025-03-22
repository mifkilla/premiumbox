<?php
if( !defined( 'ABSPATH')){ exit(); } 

if(!function_exists('get_usvedoc_temp')){
	function get_usvedoc_temp($id, $field_id){
	global $wpdb;
		$temp = '';

		$id = intval($id);
		if($id < 1){ $id = 0; }
		$field_id = intval($field_id);
		
		$userverify = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."uv_field_user WHERE uv_id='$id' AND uv_field='$field_id' AND fieldvid = '1'");
		if(isset($userverify->id)){
			$file = pn_strip_input($userverify->uv_data);
			if($file){
				$temp .= '
				<div class="usvefilelock">
					<div class="usvefilelock_delete" data-id="'. $userverify->id .'"></div>
						<a href="'. get_usve_doc($userverify->id) .'" target="_blank" rel="noreferrer noopener">'. $file .'</a>
				';
					
					if(is_admin()){
						$temp .= ' | <a href="'. get_usve_doc_view($userverify->id) .'" target="_blank">'. __('View','pn') .'</a>';
					}
					
				$temp .= '
				</div>	
				';
			} 
		}
		
		return $temp;
	}
}

if(!function_exists('get_usve_doc')){
	function get_usve_doc($uv_field_user_id){
		return get_pn_action('usvedoc').'&id='. $uv_field_user_id;
	}
}

if(!function_exists('def_premium_siteaction_usvedoc')){
	add_action('premium_siteaction_usvedoc', 'def_premium_siteaction_usvedoc');
	function def_premium_siteaction_usvedoc(){
		global $wpdb; 

		$plugin = get_plugin_class();

		$plugin->up_mode();

		$id = intval(is_param_get('id'));
		if($id < 1){
			pn_display_mess(__('Error!','pn'));
		}
		
		$dostup = 0;
		$where = " AND fieldvid='1'";
		if(current_user_can('administrator') or current_user_can('pn_userverify')){
			$dostup = 1;
			$where = "";
		}	

		$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."uv_field_user WHERE id='$id' $where");

		$file_id = intval(is_isset($data,'id'));

		if($file_id < 1){
			pn_display_mess(__('Error!','pn'));
		}

		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if($data->user_id == $user_id and $user_id > 0){
			$dostup = 1;
		}

		if($dostup != 1){
			pn_display_mess(__('Error! Access denied','pn'));
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
				if (ob_get_level()) {
					ob_end_clean();
				}
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename=' . basename($newfile));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($newfile));
				@readfile($newfile);
				
				@unlink($newfile);
				
				exit;
			} else {
				pn_display_mess(__('Error! File does not exist','pn'));
			}
		} else {
			pn_display_mess(__('Error! File does not exist','pn'));
		}
	}
}