<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_add_usve')){
		add_action('pn_adminpage_title_all_add_usve', 'def_adminpage_title_all_add_usve');
		function def_adminpage_title_all_add_usve(){
		global $bd_data, $wpdb;	
			
			$data_id = 0;
			$item_id = intval(is_param_get('item_id'));
			$bd_data = '';
			
			if($item_id){
				$bd_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."verify_bids WHERE id='$item_id'");
				if(isset($bd_data->id)){
					$data_id = $bd_data->id;
				}	
			}		
			
			if($data_id){
				_e('Edit verification','pn');
			} else {
				_e('Add verification','pn');
			}	
		}
	}

	if(!function_exists('def_adminpage_content_all_add_usve')){
		add_action('pn_adminpage_content_all_add_usve','def_adminpage_content_all_add_usve');
		function def_adminpage_content_all_add_usve(){
		global $bd_data, $wpdb;

			$form = new PremiumForm();

			$data_id = intval(is_isset($bd_data,'id'));
			if($data_id){
				$title = __('Edit verification','pn');
			} else {
				$title = __('Add verification','pn');
			}

			$back_menu = array();
			$back_menu['back'] = array(
				'link' => admin_url('admin.php?page=all_usve'),
				'title' => __('Back to list','pn')
			);
			if($data_id){
				$back_menu['add'] = array(
					'link' => admin_url('admin.php?page=all_add_usve'),
					'title' => __('Add new','pn')
				);	
				if(is_isset($bd_data,'auto_status') == 1){
					$back_menu['approve'] = array(
						'link' => pn_link('enable_userverify') .'&id='. $data_id,
						'title' => __('Approve','pn')
					);
				}
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
		
			$users = array();
			$blogusers = $wpdb->get_results("SELECT ID, user_login FROM ". $wpdb->prefix ."users ORDER BY user_login ASC"); 
			foreach($blogusers as $us){
				$users[$us->ID] = is_user($us->user_login);
			}
			$options['user_id'] = array(
				'view' => 'select_search',
				'title' => __('User','pn'),
				'options' => $users,
				'default' => is_isset($bd_data, 'user_id'),
				'name' => 'user_id',
				'work' => 'int',
			);	
			if(isset($bd_data->id)){
				$options['user_id']['view'] = 'select';
				$options['user_id']['atts'] = array('disabled' => 'disabled');
			} 	

			$txtfields = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."uv_field WHERE fieldvid IN('0','2') AND status = '1' ORDER BY uv_order ASC");
			foreach($txtfields as $txtfield){
				$id = $txtfield->id;
				$field = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."uv_field_user WHERE uv_field='$id' AND uv_id='$data_id'");
				$fieldvid = intval($txtfield->fieldvid);
				
				$options['uv'. $id] = array(
					'view' => 'inputbig',
					'title' => pn_strip_input(ctv_ml($txtfield->title)),
					'default' => is_isset($field, 'uv_data'),
					'name' => 'uv'. $id,
				);	
			}	
		
			$status = array('1'=>__('Pending request','pn'),'2'=>__('Confirmed request','pn'),'3'=>__('Request is declined','pn'));
			if(isset($bd_data->id)){
				$options['status'] = array(
					'view' => 'textfield',
					'title' => __('Status','pn'),
					'default' => is_isset($status, is_isset($bd_data, 'status')),
				);	
			}
			$options['bottom_title'] = array(
				'view' => 'h3',
				'title' => '',
				'submit' => __('Save','pn'),
			);
		
			$params_form = array(
				'filter' => 'all_userverify_addform',
				'method' => 'ajax',
				'data' => $bd_data,
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options);	

			$max_mb = pn_max_upload();
			$max_upload_size = $max_mb * 1024 * 1024;
			$fileupform = pn_enable_filetype();
				
			echo '<div class="premium_single">';
			
			$fields = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."uv_field WHERE fieldvid = '1' AND status = '1' ORDER BY uv_order ASC");
			foreach($fields as $field){	

				$temp = '<form action="'. pn_link('userverify_upload', 'post') .'" class="usveajaxform" enctype="multipart/form-data" method="post">
					<input type="hidden" name="theid" value="'. $field->id .'" />
					<input type="hidden" name="id" value="'. $data_id .'" />
				';
											
				$thetitle = pn_strip_input(ctv_ml($field->title));
											
				$req_txt = '';
				if($field->uv_req == 1){
					$req_txt = '<span class="req">*</span>';
				}								
											
				$temp .= '
				<div class="premium_standart_div">
					<div class="premium_standart_line">
						<div class="usvelabeldown">'. $thetitle .' '. $req_txt .'</div>
						<div class="usvelabeldownsyst">('. strtoupper(join(', ',$fileupform)) .', '. __('max.','pn') .' '. $max_mb .''. __('MB','pn') .')</div>
															
						<div class="usveupfile">
							<input type="file" class="usveupfilesome" name="file" value="" />
						</div>
															
						<div class="ustbl_res">'. get_usvedoc_temp($data_id, $field->id) .'</div>
					</div>
				</div>
				';
								
				$temp .= '</form>';
				echo $temp;	
			}	

			echo '
				<div id="usveformedres"></div>
			</div>';
			
			if(isset($bd_data->id)){
				$temp = '	
				<div class="premium_body">
					<h3 style="padding: 0; margin: 0;">'. __('Failure reason','pn') .'</h3>
					<form method="post" action="'. pn_link('disable_userverify') .'&id='. $data_id .'">
						<p><textarea name="textstatus" style="width: 100%; height: 100px;">'. pn_strip_input(is_isset($bd_data, 'comment')) .'</textarea></p>
						<p><label><input type="checkbox" name="delete_files" value="1" /> '. __('Delete verification files','pn') .'</label></p>
						<input type="submit" name="submit" class="button" value="'. __('Decline verification','pn') .'" />
					</form>	
				</div>
				';
				echo $temp;
			} 
			?>
<script type="text/javascript">
jQuery(function($){ 

	$(document).on('change', '.usveupfilesome', function(){
		var thet = $(this);
		var text = thet.val();
		var par = thet.parents('form');
		var ccn = thet[0].files.length;
		if(ccn > 0){
			var fileInput = thet[0];
			var bitec = fileInput.files[0].size;		
			if(bitec > <?php echo $max_upload_size; ?>){
				alert('<?php _e('Max.','pn'); ?> <?php echo $max_mb; ?> <?php _e('MB','pn'); ?> !');
				thet.val('');
			} else {
				par.submit();
			}
		}	
	});
		
	var thet = '';
	$('.usveajaxform').ajaxForm({
		dataType:  'json',
		beforeSubmit: function(a,f,o) {
			thet = f;		
			$('#usveformedres').html(' ');
			thet.find('input').prop('disabled',true);
		},
		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response', 'form'); ?>
		},		
		success: function(res) { 
			if(res['status']== 'error'){
				$('#usveformedres').html('<div class="premium_reply theerror">'+ res['status_text'] + '</div>');
				thet.find('.usveupfilesome').attr('value','');
			}
			if(res['response']){
				thet.find('.ustbl_res').html(res['response']); 
			}			
			thet.find('input').prop('disabled', false);
		}
	});
		
	$(document).on('click', '.usvefilelock_delete', function(){
		var id = $(this).attr('data-id');
		var thet = $(this);
		if(!thet.hasClass('active')){
			if(confirm("<?php _e('Are you sure you want to delete the file?','pn'); ?>")){
				thet.addClass('active');
				var param='id=' + id;
				$.ajax({
					type: "POST",
					url: "<?php echo pn_link('delete_userverify_file', 'post');?>",
					dataType: 'json',
					data: param,
					error: function(res, res2, res3){
						<?php do_action('pn_js_error_response', 'ajax'); ?>
					},			
					success: function(res)
					{
						if(res['status'] == 'success'){
							thet.parents('.ustbl_res').html(' ');
						} 
						if(res['status'] == 'error'){
							<?php do_action('pn_js_alert_response'); ?>
						}
								
						thet.removeClass('active');
					}
				});
			}
		}
		
		return false;
	});	
});	
</script>
		<?php 
		}   
	}

	if(!function_exists('def_premium_action_delete_userverify_file')){
		add_action('premium_action_delete_userverify_file', 'def_premium_action_delete_userverify_file');
		function def_premium_action_delete_userverify_file(){
			global $or_site_url, $wpdb;

			only_post();
			
			header('Content-Type: application/json; charset=utf-8');
			
			$log = array();
			$log['response'] = '';
			$log['status'] = '';
			$log['status_code'] = 0;
			$log['status_text'] = '';

			$plugin = get_plugin_class();

			if(!current_user_can('administrator') and !current_user_can('pn_userverify')){
				$log['status'] = 'error'; 
				$log['status_code'] = 1;
				$log['status_text']= __('Error! Insufficient privileges','pn');
				echo json_encode($log);
				exit;		
			}
			
			$id = intval(is_param_post('id'));
			if($id < 1){ $id = 0; } /* id юзер поля */

			if($id > 0){
				$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."uv_field_user WHERE fieldvid = '1' AND id = '$id'");
				if(isset($item->id)){
					$uv_id = $item->uv_id;
					$item_id = $item->id;
					
					$res = apply_filters('item_usfielduser_delete_before', pn_ind(), $item_id, $item);
					if($res['ind'] == 1){
						$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."uv_field_user WHERE id = '$item_id'");
						do_action('item_usfielduser_delete', $item_id, $item);
						if($result){
							do_action('item_usfielduser_delete_after', $item_id, $item); 
						}
						$log['status'] = 'success';
					} else { $form->error_form(is_isset($res,'error')); }
				}
			}		
			
			echo json_encode($log);
			exit;
		}
	}

	if(!function_exists('def_premium_action_userverify_upload')){
		add_action('premium_action_userverify_upload','def_premium_action_userverify_upload');
		function def_premium_action_userverify_upload(){
		global $or_site_url, $wpdb;	
			
			only_post();
			
			header('Content-Type: application/json; charset=utf-8');
			
			$plugin = get_plugin_class();
			
			$log = array();
			$log['response'] = '';
			$log['status'] = '';
			$log['status_code'] = 0;
			$log['status_text'] = '';	
			
			if(!current_user_can('administrator') and !current_user_can('pn_userverify')){
				$log['status'] = 'error'; 
				$log['status_code'] = 1;
				$log['status_text']= __('Error! Insufficient privileges','pn');
				echo json_encode($log);
				exit;		
			}
					
			$id = intval(is_param_post('id'));
			if($id < 1){ $id = 0; } /* id заявки */
			
			$theid = intval(is_param_post('theid'));
			if($theid < 1){ $theid = 0; }	/* id поля */
			
			$locale = get_locale();
				
			$field_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."uv_field WHERE fieldvid = '1' AND status = '1' AND id='$theid'");
			if(!isset($field_data->id)){
				$log['status'] = 'error';
				$log['status_code'] = 1; 
				$log['status_text'] = __('Error! Error loading file','pn');			
				echo json_encode($log);
				exit;	
			}		
				
			$data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."verify_bids WHERE id='$id'");
			$user_id = intval(is_isset($data,'user_id'));
			$data_id = intval(is_isset($data,'id'));
	
			if(is_array($_FILES) and isset($_FILES['file'], $_FILES['file']['name'])){		
				$ext = pn_mime_filetype($_FILES['file']);
				$tempFile = $_FILES['file']['tmp_name'];
							
				$max_mb = pn_max_upload();
				$max_upload_size = $max_mb * 1024 * 1024;
				$fileupform = pn_enable_filetype();	
						
				$ext_old = strtolower(strrchr($_FILES['file']['name'],"."));
				if(in_array($ext_old, $fileupform)){
					$fi = @getimagesize($_FILES['file']['tmp_name']);
					$mtype = is_isset($fi, 'mime');
					$up_mtype = array('image/png','image/jpeg','image/gif');
					$up_mtype = apply_filters('pn_enable_mimetype', $up_mtype);
					if(in_array($mtype, $up_mtype)){
						if(in_array($ext, $fileupform)){
							if($_FILES["file"]["size"] > 0 and $_FILES["file"]["size"] < $max_upload_size){
											
								$filename = time().'_'.pn_strip_symbols(replace_cyr($_FILES['file']['name']),'.');				
										
								$path = $plugin->upload_dir;
								$path2 = $path . 'userverify/';
								$path3 = $path . 'userverify/' . $data_id . '/';
								if(!is_dir($path)){ 
									@mkdir($path , 0777);
								}
								if(!is_dir($path2)){ 
									@mkdir($path2 , 0777);
								}	
								if(!is_dir($path3)){ 
									@mkdir($path3 , 0777);
								}

								$htacces = $path2.'.htaccess';
								if(!is_file($htacces)){
									$nhtaccess = "Order allow,deny \n Deny from all";
									$file_open = @fopen($htacces, 'w');
									@fwrite($file_open, $nhtaccess);
									@fclose($file_open);		
								}								

								$targetFile =  str_replace('//','/',$path3) . $filename;
								$result = move_uploaded_file($tempFile,$targetFile);
								if($result){
												
									$fdata = @file_get_contents($targetFile);
									$fdata = str_replace('*', '%star%', $fdata);
									if(is_file($targetFile)){
										@unlink($targetFile);
									}									
												
									$olddata = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."uv_field_user WHERE uv_id='$data_id' AND uv_field='$theid'");
													
									$arr = array();
									$arr['user_id'] = $user_id;
									$arr['uv_data'] = $filename;
									$arr['uv_id'] = $id;
									$arr['uv_field'] = $theid;
									$arr['fieldvid'] = 1;
													
									if(isset($olddata->id)){										
										$wpdb->update($wpdb->prefix.'uv_field_user', $arr, array('id'=>$olddata->id));
										$uv_field_user_id = $olddata->id;
									} else {									
										$wpdb->insert($wpdb->prefix.'uv_field_user', $arr);
										$uv_field_user_id = $wpdb->insert_id;
									}
												
									if($uv_field_user_id){
										$file = $plugin->upload_dir . 'userverify/' . $data_id . '/' . $uv_field_user_id . '.php';
										
										$apd = $fdata;
										$file_text = add_phpf_data($apd);
										
										$file_open = @fopen($file, 'w');
										@fwrite($file_open, $file_text);
										@fclose($file_open);
													
										if(!is_file($file)){
											$wpdb->query("DELETE FROM ".$wpdb->prefix."uv_field_user WHERE id = '$uv_field_user_id'");
											$log['status'] = 'error';
											$log['status_code'] = 1;
											$log['status_text'] = __('Error! Error loading file','pn');	
											echo json_encode($log);
											exit;
										}												
									}									
												
									$wpdb->query("UPDATE ".$wpdb->prefix."uv_field_user SET user_id = '$user_id' WHERE uv_id = '$data_id'");
													
									$log['response'] = get_usvedoc_temp($id, $theid);
												
								} else {
									$log['status'] = 'error';
									$log['status_code'] = 1;
									$log['status_text'] = __('Error! Error loading file','pn');
								}
							} else {
								$log['status'] = 'error';
								$log['status_code'] = 1;
								$log['status_text'] = __('Max.','pn').' '. $max_mb .' '. __('MB','pn') .'!';			
							}
						} else {
							$log['status'] = 'error';
							$log['status_code'] = 1;
							$log['status_text'] = __('Error! Incorrect file format','pn');					
						}
					} else {
						$log['status'] = 'error';
						$log['status_code'] = 1;
						$log['status_text'] = __('Error! Incorrect file format','pn');					
					}
				} else {
					$log['status'] = 'error';
					$log['status_code'] = 1;
					$log['status_text'] = __('Error! Incorrect file format','pn');					
				}
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! Error loading file','pn'). '(n)';
			}				
			
			echo json_encode($log);
			exit;	
		}
	}
		
	if(!function_exists('def_premium_action_all_add_usve')){	
		add_action('premium_action_all_add_usve','def_premium_action_all_add_usve');
		function def_premium_action_all_add_usve(){	
		global $wpdb;	

			only_post();
			
			$form = new PremiumForm();
			$form->send_header();
			
			pn_only_caps(array('administrator','pn_userverify'));
			
			$plugin = get_plugin_class();
			
			$data_id = intval(is_param_post('data_id'));
			
			$last_data = '';
			if($data_id > 0){
				$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE id='$data_id'");
				if(!isset($last_data->id)){
					$data_id = 0;
				}
			}		
			$user_id = intval(is_isset($last_data,'user_id'));
			
			$array = array();				
			$array['user_ip'] = pn_real_ip();
			$array['auto_status'] = 1;
			$array['edit_date'] = current_time('mysql');
			
			if($data_id){
				$wpdb->update($wpdb->prefix.'verify_bids', $array, array('id'=>$data_id));	
			} else {
				$array['create_date'] = current_time('mysql');
				$user_id = intval(is_param_post('user_id'));
				$array['user_id'] = $user_id;
				$ui = get_userdata($user_id);
				if(isset($ui->ID)){
					$array['user_login'] = is_user($ui->user_login);
					$array['user_email'] = is_email($ui->user_email);
				} else {
					$form->error_form(__('Error! You did not choose the user','pn'));
				}	
				$array['status'] = 1;
				
				//$cc = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."verify_bids WHERE user_id = '$user_id' AND status IN('1','2') AND id != '$data_id'");		
				//if($cc > 0){
					//$form->error_form(__('Error! This user already has a verification order','pn'));
				//}				
				
				$wpdb->insert($wpdb->prefix.'verify_bids', $array);
				$data_id = $wpdb->insert_id;

				if($data_id){
					$path = $plugin->upload_dir;
					$path2 = $path . 'userverify/';
					$path3 = $path . 'userverify/' . $data_id . '/';
					$path4 = $path . 'userverify/0/';
					if(!is_dir($path)){ 
						@mkdir($path , 0777);
					}
					if(!is_dir($path2)){ 
						@mkdir($path2 , 0777);
					}	
					if(!is_dir($path3)){ 
						@mkdir($path3 , 0777);
					}
								
					$files = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."uv_field_user WHERE uv_id = '0' AND fieldvid = '1'");
					foreach($files as $file){
						$or_file = $path4 . $file->id . '.php';
						$new_file = $path3 . $file->id . '.php';
						@copy($or_file, $new_file);
						@unlink($or_file);
						
						$arr = array();
						$arr['user_id'] = $user_id;
						$arr['uv_id'] = $data_id;
						$wpdb->update($wpdb->prefix . 'uv_field_user', $arr, array('id'=> $file->id));
					}
				}
			}
			
			if($data_id){
				$fields = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."uv_field WHERE fieldvid IN('0','2') AND status = '1' ORDER BY uv_order ASC");
				foreach($fields as $field){
					$field_id = $field->id;

					$value = strip_uf(is_param_post( 'uv' . $field->id ), $field->uv_auto);
								
					$us_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."uv_field_user WHERE uv_id='$data_id' AND uv_field='$field_id'");
					
					$arr = array();
					$arr['user_id'] = $user_id;
					$arr['uv_data'] = $value;
					$arr['uv_id'] = $data_id;
					$arr['uv_field'] = $field_id;
					$arr['fieldvid'] = $field->fieldvid;
								
					if(isset($us_data->id)){
						$wpdb->update($wpdb->prefix.'uv_field_user', $arr, array('id'=>$us_data->id)); 
					} else {
						$wpdb->insert($wpdb->prefix.'uv_field_user', $arr);
					}
				}			
			}

			$url = admin_url('admin.php?page=all_add_usve&item_id='. $data_id .'&reply=true');
			$form->answer_form($url);
		}
	}	
}