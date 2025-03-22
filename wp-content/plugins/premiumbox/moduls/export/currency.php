<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_export_currency', 'pn_admin_title_pn_export_currency');
	function pn_admin_title_pn_export_currency($page){
		_e('Currency Export/Import','pn');
	} 

	add_action('pn_adminpage_content_pn_export_currency','def_pn_admin_content_pn_export_currency');
	function def_pn_admin_content_pn_export_currency(){
	global $wpdb;
	if(current_user_can('administrator') or current_user_can('pn_export_currency')){ ?>
	<div class="premium_body">	
		<form method="post" target="_blank" action="<?php the_pn_link('export_currency','post'); ?>">
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Select data','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<?php
						$scroll_lists = array();
						
						$array = array(
							'psys_title' => __('PS title','pn'),
							'currency_code_title' => __('Currency code','pn'),
							'xml_value' => __('XML name','pn'),
							'currency_decimal' => __('Amount of Decimal places','pn'),					
							'show_give' => __('Show field "From Account"','pn'),
							'show_get' => __('Show filed "Onto Account"','pn'),
							'currency_reserv' => __('Reserve','pn'),
							'currency_status' => __('Status','pn'),
						);
						$array = apply_filters('list_export_currency', $array);
						foreach($array as $key => $val){
							$checked = 0;
							$scroll_lists[] = array(
								'title' => $val,
								'checked' => $checked,
								'value' => $key,
							);
						}
						echo get_check_list($scroll_lists, 'data[]','','',1);
						?>
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<input type="submit" name="" class="button" value="<?php _e('Download','pn'); ?>" />
						
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>	
		</form>	
	</div>
	<?php }  ?>

	<?php if(current_user_can('administrator') or current_user_can('pn_import_currency')){ ?>
	<div class="premium_body">	
		<form method="post" target="_blank" action="<?php the_pn_link('import_currency','post'); ?>" enctype="multipart/form-data">
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Import','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<input type="file" name="importfile" value="" />
						
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>	
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<input type="submit" name="" class="button" value="<?php _e('Upload','pn'); ?>" />
						
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>	
		</form>	
	</div>
	<?php } ?>	
	<?php
	} 

	add_action('premium_action_export_currency','def_premium_action_export_currency');
	function def_premium_action_export_currency(){
	global $wpdb;	

		pn_only_caps(array('administrator','pn_export_currency'));			
		
		$my_dir = wp_upload_dir();
		$path = $my_dir['basedir'].'/';		
			
		$file = $path.'currencyexport-'. date('Y-m-d-H-i') .'.csv';           
		$fs=@fopen($file, 'w');
		
		$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."currency WHERE auto_status = '1' ORDER BY id DESC");
		
		$data = is_param_post('data');
			
		$content = '';
			
		$array = array(
			'id' => __('Identifier','pn'), 
			'psys_title' => __('PS title','pn'),
			'currency_code_title' => __('Currency code','pn'),
			'xml_value' => __('XML name','pn'),
			'currency_decimal' => __('Amount of Decimal places','pn'),					
			'show_give' => __('Show field "From Account"','pn'),
			'show_get' => __('Show filed "Onto Account"','pn'),
			'currency_reserv' => __('Reserve','pn'),
			'currency_status' => __('Status','pn'),
		);
		$array = apply_filters('list_export_currency', $array);
				
		if(is_array($data)){
				
			$en = array();
			$csv_title = '';
			$csv_key = '';
			foreach($array as $k => $v){
				if(in_array($k, $data) or $k == 'id'){
					$en[] = $k;
					$csv_title .= '"'.get_cptgn($v).'";';
					$csv_key .= '"'.get_cptgn($k).'";';
				} 
			}	
				
			$content .= $csv_title."\n";
			$content .= $csv_key."\n";

			$export_filter = array(
				'int_arr' => array('id', 'currency_decimal'),
				'qw_arr' => array('show_give','show_get','currency_status'),
				'sum_arr' => array('currency_reserv'),
			);
			$export_filter = apply_filters('export_currency_filter', $export_filter);
			
			$qw_arr = (array)is_isset($export_filter,'qw_arr');
			$sum_arr = (array)is_isset($export_filter,'sum_arr');
			$int_arr = (array)is_isset($export_filter,'int_arr');
				
			if(count($en) > 0){
				foreach($items as $item){
					$line = '';
						
					foreach($en as $key){
						$line .= '"';
						if(in_array($key,$qw_arr)){
							$line .= get_cptgn(get_exvar(is_isset($item,$key),array(__('no','pn'),__('yes','pn'))));
						} elseif(in_array($key,$sum_arr)){
							$line .= rep_dot(is_isset($item,$key));
						} elseif(in_array($key,$int_arr)){
							$line .= intval(is_isset($item,$key));	
						} else {
							$line .= get_cptgn(rez_exp(ctv_ml(is_isset($item,$key))));
						}
						$line .= '";';
					}
						
					$line .= "\n";
					$content .= $line;
				}
			}
		}
			
		@fwrite($fs, $content);
		@fclose($fs);	
		
		if(is_file($file)) {
			if (ob_get_level()) {
				ob_end_clean();
			}
			$lang = get_locale();
			if($lang == 'ru_RU'){
				header('Content-Type: text/html; charset=CP1251');
			} else {
				header('Content-Type: text/html; charset=UTF8');
			}
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);
			@unlink($file);
			exit;
		} else {
			pn_display_mess(__('Error! Unable to create file!','pn'));
		}	
	}

	add_action('premium_action_import_currency','def_premium_action_import_currency');
	function def_premium_action_import_currency(){
	global $wpdb;	

		pn_only_caps(array('administrator','pn_import_currency'));
		
		$premit_ext = array(".csv");
		if(isset($_FILES['importfile'], $_FILES['importfile']['name'])){
			$ext = strtolower(strrchr($_FILES['importfile']['name'],"."));
			if(in_array($ext,$premit_ext)){
					
				$max_upload_size = wp_max_upload_size();
				if ( ! $max_upload_size ) {
					$max_upload_size = 0;
				}	
				$max_mb = ($max_upload_size / 1024 / 1024);
					
				if($_FILES["importfile"]["size"] > 0 and $_FILES["importfile"]["size"] < $max_upload_size){
					$tempFile = $_FILES['importfile']['tmp_name'];
					$filename = pn_strip_symbols(time() . $_FILES['importfile']['name']);

					$my_dir = wp_upload_dir();
					$path = $my_dir['basedir'].'/';
					$path2 = $my_dir['basedir'].'/import/';
					if(!is_dir($path)){ 
						@mkdir($path , 0777);
					}
					if(!is_dir($path2)){ 
						@mkdir($path2 , 0777);
					}
						
					$targetFile =  str_replace('//','/',$path2) . $filename;
						
					if(move_uploaded_file($tempFile,$targetFile)){
						
						$error = 0;
							
						$array = array(
							'id' => __('Identifier','pn'),
							'psys_title' => __('PS title','pn'),
							'currency_code_title' => __('Currency code','pn'),
							'xml_value' => __('XML name','pn'),
							'currency_decimal' => __('Amount of Decimal places','pn'),							
							'show_give' => __('Show field "From Account"','pn'),
							'show_get' => __('Show filed "Onto Account"','pn'),
							'currency_reserv' => __('Reserve','pn'),
							'currency_status' => __('Status','pn'),
						);
						$array = apply_filters('list_export_currency', $array);					
						
						$allow_key = array();
						$nochecked_key = apply_filters('nochecked_export_currency', array());
						foreach($array as $k => $v){
							if(in_array($k, $nochecked_key)){
								$allow_key[] = $k;
							} else {
								$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE '{$k}'");
								if($query == 1){
									$allow_key[] = $k;
								}
							}
						}					
							
						$result = file_get_contents($targetFile);
						$lines = explode("\n",$result);
						if(count($lines) > 2){
								
							$file_map = array();
							$csv_keys = explode(';',is_isset($lines,1));
							foreach($csv_keys as $csv_k => $csv_v){
								$file_map[$csv_k] = rez_exp($csv_v);
							}
								
							$r = -1;
							
							$export_filter = array(
								'int_arr' => array('id', 'currency_decimal'),
								'qw_arr' => array('show_give','show_get','currency_status'),
								'sum_arr' => array('currency_reserv'),
							);
							$export_filter = apply_filters('export_currency_filter', $export_filter);						
							
							$int_arr = (array)$export_filter['int_arr'];
							$sum_arr = (array)$export_filter['sum_arr'];
							$qw_arr = (array)$export_filter['qw_arr'];						
							
							foreach($lines as $line){ $r++;
								if($r > 1){	
									$line = get_tgncp(trim($line));
									if($line){
										$bd_array = array();
											
										$items = explode(';',$line);
										foreach($items as $item_key => $item){
											$item = rez_exp($item);	
											$db_key = $file_map[$item_key];
											if(in_array($db_key, $allow_key)){	
												if(in_array($db_key, $int_arr)){
													$bd_array[$db_key] = intval($item);
												} elseif(in_array($db_key, $sum_arr)){	
													$bd_array[$db_key] = is_sum($item);
												} elseif(in_array($db_key, $qw_arr)){
													$bd_array[$db_key] = intval(get_exvar(mb_strtolower($item), array(__('no','pn')=>'0',__('yes','pn')=>'1')));
												} else {
													$bd_array[$db_key] = pn_maxf_mb(pn_strip_input($item),500);
												}
											}
										}	
												
										if(count($bd_array) > 0){
											$data_id = intval(is_isset($bd_array,'id'));
											if(isset($bd_array['id'])){
												unset($bd_array['id']);
											}											
												
											$locale = get_locale();
												
											if(isset($bd_array['psys_title']) and $bd_array['psys_title']){	
												$now = $bd_array['psys_title'];
												if(is_ml()){
													$now_ml = '['. $locale .':]'. $now .'[:'. $locale .']';
												} else {
													$now_ml = $now;
												}	
												$psys_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."psys WHERE psys_title LIKE '%". $now_ml ."%' OR psys_title='$now'");
												if(isset($psys_data->id)){
													$bd_array['psys_id'] = $psys_data->id;
												} else {	
													$up_arr = array(
														'psys_title' => $bd_array['psys_title'],
													);
													$wpdb->insert($wpdb->prefix.'psys', $up_arr);
													$bd_array['psys_id'] = $wpdb->insert_id;
												}
											}
											
											if(isset($bd_array['currency_code_title']) and $bd_array['currency_code_title']){
												$now = $bd_array['currency_code_title'];
												$currency_code_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency_codes WHERE currency_code_title = '$now'");
												if(isset($currency_code_data->id)){
													$bd_array['currency_code_id'] = $currency_code_data->id;
												} else {	
													$up_arr = array(
														'currency_code_title' => $bd_array['currency_code_title'],
														'internal_rate' => '1',
													);
													$wpdb->insert($wpdb->prefix.'currency_codes', $up_arr);
													$bd_array['currency_code_id'] = $wpdb->insert_id;
												}
											}

											$install = 1;
											if($data_id){
												$vd = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE id='$data_id'");
												if(isset($vd->id) and count($bd_array) > 0){
													$install = 0;
														
													if(isset($bd_array['psys_title'])){
														$bd_array['psys_title'] = replace_value_ml($vd->psys_title,$bd_array['psys_title'],$locale);
													}
													if(isset($bd_array['currency_code_title'])){
														$bd_array['currency_code_title'] = replace_value_ml($vd->currency_code_title,$bd_array['currency_code_title'],$locale);
													}
													
													$wpdb->update($wpdb->prefix.'currency', $bd_array, array('id'=>$data_id));
												}
											}																							
												
											if($install == 1){
												if(isset($bd_array['psys_id']) and isset($bd_array['currency_code_id'])){
													$wpdb->insert($wpdb->prefix.'currency', $bd_array);
												}
											}
											
											do_action('export_currency_end');
										}
									}	
								}
							}								
						} 
							
						if($error == 0){
							if(is_file($targetFile)){
								@unlink($targetFile);
							}
								
							$url = admin_url('admin.php?page=pn_export_currency&reply=true');
							wp_redirect($url);
							exit;	
						}	
					} else {
						pn_display_mess(__('Error! Error loading file','pn'));
					}
				} else {
					pn_display_mess(__('Error! Incorrect file size!','pn'));
				}		
			} else {
				pn_display_mess(__('Error! Incorrect file format!','pn'));
			}
		} else {
			pn_display_mess(__('Error! File is not selected!','pn'));
		}		
	}
}	