<?php
if( !defined( 'ABSPATH')){ exit(); } 

function get_usac_files($user_wallet_id){
global $wpdb;
	
	$html = '<div class="verify_accline_wrap">';
		$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."uv_wallets_files WHERE uv_wallet_id='$user_wallet_id'");
		foreach($items as $item){
			$html .='
			<div class="verify_accline accline_'. $item->id .'">
			<a href="'. get_usac_doc($item->id) .'" target="_blank" rel="noreferrer noopener">'. pn_strip_input($item->uv_data) .'</a> 
			| <a href="#" data-id="'. $item->id .'" class="bred js_usac_del">'. __('Delete','pn') .'</a>';
			
			if(is_admin()){
				$html .= ' | <a href="'. get_usac_doc_view($item->id) .'" target="_blank">'. __('View','pn') .'</a>';
			}
			
			$html .='
			</div>';
		}	
	$html .= '</div>';
	
	return $html;
}

function get_usac_doc_view($id){
	return pn_link('usacdoc_view').'&id='. $id;
}

function get_usac_doc($id){
	return get_pn_action('usacdoc').'&id='. $id;
}

add_action('premium_siteaction_usacdoc', 'def_premium_siteaction_usacdoc');
function def_premium_siteaction_usacdoc(){
global $wpdb, $premiumbox; 

	$premiumbox->up_mode();

	$id = intval(is_param_get('id'));
	if(!$id){
		pn_display_mess(__('Error!','pn'));
	}

	$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."uv_wallets_files WHERE id='$id'");
	if(!isset($data->id)){
		pn_display_mess(__('Error!','pn'));
	}	
	
	$dostup = 0;

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	
	if($data->user_id == $user_id or current_user_can('administrator') or current_user_can('pn_userwallets')){
		$dostup = 1;
	}

	if($dostup != 1){
		pn_display_mess(__('Error! Access denied','pn'));
	}

	$file = $premiumbox->upload_dir . 'accountverify/'. $data->uv_wallet_id .'/'. $data->id .'.php';
	$newfile = $premiumbox->upload_dir .'usacshow/'. $data->uv_data;
	$linkfile = $premiumbox->upload_url .'usacshow/'. $data->uv_data;
	$path = $premiumbox->upload_dir . 'usacshow/';
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
		
		if(ob_get_level()){
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
}

add_action('premium_action_usacdoc_view', 'def_premium_action_usacdoc_view');
function def_premium_action_usacdoc_view(){
global $wpdb, $premiumbox; 

	$premiumbox->up_mode();

	pn_only_caps(array('administrator','pn_userwallets'));

	$id = intval(is_param_get('id'));
	if(!$id){
		pn_display_mess(__('Error!','pn'));
	}

	$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."uv_wallets_files WHERE id='$id'");
	if(!isset($data->id)){
		pn_display_mess(__('Error!','pn'));
	}	

	$file = $premiumbox->upload_dir . 'accountverify/'. $data->uv_wallet_id .'/'. $data->id .'.php';
	$newfile = $premiumbox->upload_dir .'usacshow/'. $data->uv_data;
	$linkfile = $premiumbox->upload_url .'usacshow/'. $data->uv_data;	
	$path = $premiumbox->upload_dir . 'usacshow/';
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