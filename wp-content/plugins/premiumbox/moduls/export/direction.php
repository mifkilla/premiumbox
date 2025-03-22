<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_export_direction', 'pn_admin_title_pn_export_direction');
	function pn_admin_title_pn_export_direction($page){
		_e('Exchange directions Export/Import','pn');
	} 

	add_action('pn_adminpage_content_pn_export_direction','def_pn_admin_content_pn_export_direction');
	function def_pn_admin_content_pn_export_direction(){
	global $wpdb;
	if(current_user_can('administrator') or current_user_can('pn_export_exchange_direcions')){
	?>
	<div class="premium_body">	
		<form method="post" target="_blank" action="<?php the_pn_link('export_direction','post'); ?>">
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Select data','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<?php
						$scroll_lists = array();
						
						$array = array(
							'currency_give' => __('Currency name Sending','pn'),
							'currency_code_give' => __('Currency code Send','pn'),
							'currency_get' => __('Currency name Receiving','pn'),
							'currency_code_get' => __('Currency code Receive','pn'),
							'course_give' => __('Rate Send','pn'),
							'course_get' => __('Rate Receive','pn'),
							'min_sum1' => __('Min. amount Send','pn'),
							'max_sum1' => __('Max. amount Send','pn'),
							'min_sum2' => __('Min. amount Receive','pn'),
							'max_sum2' => __('Max. amount Receive','pn'),					
							'com_box_sum1' => __('Add. Sender fee','pn'),
							'com_box_pers1' => __('Add. Sender fee (%)','pn'),
							'com_box_min1' => __('Minimum fee from sender','pn'),
							'com_box_sum2' => __('Add. Recipient fee','pn'),
							'com_box_pers2' => __('Add. Recipient fee (%)','pn'),
							'com_box_min2' => __('Minimum fee from recipient','pn'),					
							'com_sum1' => __('Fee Send','pn'),
							'com_pers1' => __('Fee (%) Send','pn'),
							'com_sum2' => __('Fee Receive','pn'),
							'com_pers2' => __('Fee (%) Receive','pn'),				
							'pay_com1' => __('Exchange pays fee Send','pn'),
							'pay_com2' => __('Exchange pays fee Receive','pn'),
							'nscom1' => __('Non standard fee Send','pn'),
							'nscom2' => __('Non standard fee Receive','pn'),					
							'minsum1com' => __('Min. amount of fee Send','pn'),
							'minsum2com' => __('Min. amount of fee Receive','pn'),
							'maxsum1com' => __('Max. amount of fee Send','pn'),
							'maxsum2com' => __('Max. amount of fee Receive','pn'),
							'direction_status' => __('Activity','pn'),
							'enable_user_discount' => __('User discount','pn'),
							'max_user_discount' => __('Max. user discount','pn'),
							'profit_sum1' => __('Profit amount Send','pn'),
							'profit_pers1' => __('Profit percent Send','pn'),
							'profit_sum2' => __('Profit amount Receive','pn'),
							'profit_pers2' => __('Profit percent Receive','pn'),
						);
						$array = apply_filters('list_export_directions', $array);
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
	<?php } ?>

	<?php
	if(current_user_can('administrator') or current_user_can('pn_import_exchange_direcions')){
	?>
	<div class="premium_body">	
		<form method="post" target="_blank" action="<?php the_pn_link('import_direction','post'); ?>" enctype="multipart/form-data">
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

	add_action('premium_action_export_direction','def_premium_action_export_direction');
	function def_premium_action_export_direction(){
	global $wpdb;	

		pn_only_caps(array('administrator','pn_export_exchange_direcions'));

		$my_dir = wp_upload_dir();
		$path = $my_dir['basedir'].'/';		
			
		$file = $path.'directionexport-'. date('Y-m-d-H-i') .'.csv';           
		$fs=@fopen($file, 'w');
		
		$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions WHERE auto_status='1' ORDER BY id DESC");
		
		$data = is_param_post('data');
			
		$content = '';
			
		$array = array(
			'id' => __('Identifier','pn'),
			'currency_give' => __('Currency name Sending','pn'),
			'currency_code_give' => __('Currency code Send','pn'),
			'currency_get' => __('Currency name Receiving','pn'),
			'currency_code_get' => __('Currency code Receive','pn'),
			'course_give' => __('Rate Send','pn'),
			'course_get' => __('Rate Receive','pn'),
			'min_sum1' => __('Min. amount Send','pn'),
			'max_sum1' => __('Max. amount Send','pn'),
			'min_sum2' => __('Min. amount Receive','pn'),
			'max_sum2' => __('Max. amount Receive','pn'),					
			'com_box_sum1' => __('Add. Sender fee','pn'),
			'com_box_pers1' => __('Add. Sender fee (%)','pn'),
			'com_box_min1' => __('Minimum fee from sender','pn'),
			'com_box_sum2' => __('Add. Recipient fee','pn'),
			'com_box_pers2' => __('Add. Recipient fee (%)','pn'),
			'com_box_min2' => __('Minimum fee from recipient','pn'),					
			'com_sum1' => __('Fee Send','pn'),
			'com_pers1' => __('Fee (%) Send','pn'),
			'com_sum2' => __('Fee Receive','pn'),
			'com_pers2' => __('Fee (%) Receive','pn'),						
			'pay_com1' => __('Exchange pays fee Send','pn'),
			'pay_com2' => __('Exchange pays fee Receive','pn'),
			'nscom1' => __('Non standard fee Send','pn'),
			'nscom2' => __('Non standard fee Receive','pn'),
			'minsum1com' => __('Min. amount of fee Send','pn'),
			'minsum2com' => __('Min. amount of fee Receive','pn'),
			'maxsum1com' => __('Max. amount of fee Send','pn'),
			'maxsum2com' => __('Max. amount of fee Receive','pn'),
			'direction_status' => __('Activity','pn'),
			'enable_user_discount' => __('User discount','pn'),
			'max_user_discount' => __('Max. user discount','pn'),
			'profit_sum1' => __('Profit amount Send','pn'),
			'profit_pers1' => __('Profit percent Send','pn'),
			'profit_sum2' => __('Profit amount Receive','pn'),
			'profit_pers2' => __('Profit percent Receive','pn'),
		);
		$array = apply_filters('list_export_directions', $array);
			
		$psys_id = array();
		$vtype_id = array();
		$valutsn = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."currency");
		foreach($valutsn as $valut){
			$psys_id[$valut->id] = $valut->psys_title;
			$vtype_id[$valut->id] = $valut->currency_code_title;
		}
			
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
				'int_arr' => array('id'),
				'qw_arr' => array('direction_status', 'enable_user_discount', 'pay_com1', 'pay_com2', 'nscom1', 'nscom2'),
				'sum_arr' => array(
					'course_give','course_get','max_user_discount',
					'min_sum1','min_sum2','max_sum1','max_sum2',
					'com_sum1','com_pers1','com_sum2', 'com_pers2',
					'com_box_sum1','com_box_pers1','com_box_min1','com_box_sum2','com_box_pers2','com_box_min2',
					'profit_sum1', 'profit_pers1', 'profit_sum2', 'profit_pers2',
				),
			);
					
			$export_filter = apply_filters('export_directions_filter', $export_filter);		
			
			$qw_arr = (array)is_isset($export_filter,'qw_arr');
			$sum_arr = (array)is_isset($export_filter,'sum_arr');
			$int_arr = (array)is_isset($export_filter,'int_arr');
			
			if(count($en) > 0){
				foreach($items as $item){
					$line = '';
						
					$data_id = $item->id;
					foreach($en as $key){
						$line .= '"';
							
						if(in_array($key,$qw_arr)){
							$line .= get_cptgn(get_exvar(is_isset($item,$key),array(__('no','pn'),__('yes','pn'))));
						} elseif(in_array($key,$sum_arr)){
							$line .= rep_dot(is_isset($item,$key));
						} elseif(in_array($key,$int_arr)){
							$line .= intval(is_isset($item,$key));						
						} elseif($key == 'currency_give'){
							$line .= get_cptgn(ctv_ml(is_isset($psys_id,is_isset($item,'currency_id_give'))));
						} elseif($key == 'currency_get'){
							$line .= get_cptgn(ctv_ml(is_isset($psys_id,is_isset($item,'currency_id_get'))));
						} elseif($key == 'currency_code_give'){
							$line .= get_cptgn(is_isset($vtype_id,is_isset($item,'currency_id_give')));
						} elseif($key == 'currency_code_get'){
							$line .= get_cptgn(is_isset($vtype_id,is_isset($item,'currency_id_get')));							
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

	add_action('premium_action_import_direction','def_premium_action_import_direction');
	function def_premium_action_import_direction(){
	global $wpdb, $premiumbox;	

		pn_only_caps(array('administrator','pn_import_exchange_direcions'));
		
		$premit_ext = array(".csv");
		if(isset($_FILES['importfile']['name'])){
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
							'currency_give' => __('Currency name Sending','pn'),
							'currency_code_give' => __('Currency code Send','pn'),
							'currency_get' => __('Currency name Receiving','pn'),
							'currency_code_get' => __('Currency code Receive','pn'),
							'course_give' => __('Rate Send','pn'),
							'course_get' => __('Rate Receive','pn'),
							'min_sum1' => __('Min. amount Send','pn'),
							'max_sum1' => __('Max. amount Send','pn'),
							'min_sum2' => __('Min. amount Receive','pn'),
							'max_sum2' => __('Max. amount Receive','pn'),					
							'com_box_sum1' => __('Add. Sender fee','pn'),
							'com_box_pers1' => __('Add. Sender fee (%)','pn'),
							'com_box_min1' => __('Minimum fee from sender','pn'),
							'com_box_sum2' => __('Add. Recipient fee','pn'),
							'com_box_pers2' => __('Add. Recipient fee (%)','pn'),
							'com_box_min2' => __('Minimum fee from recipient','pn'),					
							'com_sum1' => __('Fee Send','pn'),
							'com_pers1' => __('Fee (%) Send','pn'),
							'com_sum2' => __('Fee Receive','pn'),
							'com_pers2' => __('Fee (%) Receive','pn'),						
							'pay_com1' => __('Exchange pays fee Send','pn'),
							'pay_com2' => __('Exchange pays fee Receive','pn'),
							'nscom1' => __('Non standard fee Send','pn'),
							'nscom2' => __('Non standard fee Receive','pn'),
							'minsum1com' => __('Min. amount of fee Send','pn'),
							'minsum2com' => __('Min. amount of fee Receive','pn'),
							'maxsum1com' => __('Max. amount of fee Send','pn'),
							'maxsum2com' => __('Max. amount of fee Receive','pn'),
							'direction_status' => __('Activity','pn'),
							'enable_user_discount' => __('User discount','pn'),
							'max_user_discount' => __('Max. user discount','pn'),
							'profit_sum1' => __('Profit amount Send','pn'),
							'profit_pers1' => __('Profit percent Send','pn'),
							'profit_sum2' => __('Profit amount Receive','pn'),
							'profit_pers2' => __('Profit percent Receive','pn'),
						);
						$array = apply_filters('list_export_directions', $array);	
						
						$allow_key = array();
						$nochecked_key = array(
							'currency_give','currency_get','currency_code_give','currency_code_get'
						);
						$nochecked_key = apply_filters('nochecked_export_directions', $nochecked_key);
						foreach($array as $k => $v){
							if(in_array($k, $nochecked_key)){
								$allow_key[] = $k;
							} else {
								$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE '{$k}'");
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
								'int_arr' => array('id'),
								'qw_arr' => array('direction_status', 'enable_user_discount', 'pay_com1', 'pay_com2', 'nscom1', 'nscom2'),
								'sum_arr' => array(
									'course_give','course_get','max_user_discount',
									'min_sum1','min_sum2','max_sum1','max_sum2',
									'com_sum1','com_pers1','com_sum2', 'com_pers2',
									'com_box_sum1','com_box_pers1','com_box_min1','com_box_sum2','com_box_pers2','com_box_min2',
									'profit_sum1', 'profit_pers1', 'profit_sum2', 'profit_pers2',
								),
							);						
							$export_filter = apply_filters('export_directions_filter', $export_filter);		
							
							$qw_arr = (array)is_isset($export_filter,'qw_arr');
							$sum_arr = (array)is_isset($export_filter,'sum_arr');
							$int_arr = (array)is_isset($export_filter,'int_arr');							
								
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

											$xml_value1 = $xml_value2 = '';
												
											$locale = get_locale();

											$tech_name = is_isset($bd_array,'currency_give').' '.is_isset($bd_array,'currency_code_give').' &rarr; '.is_isset($bd_array,'currency_get').' '.is_isset($bd_array,'currency_code_get');
											
											if(isset($bd_array['currency_give']) and isset($bd_array['currency_code_give']) and $bd_array['currency_get'] and $bd_array['currency_code_get']){
													
												$currency_give = $bd_array['currency_give'];
												if(is_ml()){
													$currency_give_ml = '['. $locale .':]'. $currency_give .'[:'. $locale .']';
												} else {
													$currency_give_ml = $currency_give;
												}
													
												$psys_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."psys WHERE psys_title LIKE '%". $currency_give_ml ."%' OR psys_title = '$currency_give'");
												if(isset($psys_data->id)){
													$bd_array['psys_id_give'] = $psys_data->id;
												} else {	
													$up_arr = array(
														'psys_title' => $bd_array['currency_give'],
													);
													$wpdb->insert($wpdb->prefix.'psys', $up_arr);
													$bd_array['psys_id_give'] = $wpdb->insert_id;
												}												
													
												if(isset($bd_array['currency_code_give'])){
													$now = $bd_array['currency_code_give'];
													$currency_code_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency_codes WHERE currency_code_title = '$now'");
													if(isset($currency_code_data->id)){
														$bd_array['currency_code_id_give'] = $currency_code_data->id;
													} else {	
														$up_arr = array(
															'currency_code_title' => $bd_array['currency_code_give'],
															'internal_rate' => '1',
														);
														$wpdb->insert($wpdb->prefix.'currency_codes', $up_arr);
														$bd_array['currency_code_id_give'] = $wpdb->insert_id;
													}
												}												
													
												if(isset($bd_array['psys_id_give']) and isset($bd_array['currency_code_id_give'])){
													if($bd_array['psys_id_give'] and $bd_array['currency_code_id_give']){
														$vals = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE psys_id='{$bd_array['psys_id_give']}' AND currency_code_id='{$bd_array['currency_code_id_give']}'");
														if(isset($vals->id)){
															
															$bd_array['currency_id_give'] = $vals->id;
															$xml_value1 = $vals->xml_value;
															
														} else {
														
															$uniq = pn_strip_symbols(replace_cyr($bd_array['currency_give']));
															$uniq = unique_xml_value($uniq, 0);
																
															$up_arr = array(
																'psys_title' => $bd_array['currency_give'],
																'psys_id' => $bd_array['psys_id_give'],
																'currency_code_title' => $bd_array['currency_code_give'],
																'currency_code_id' => $bd_array['currency_code_id_give'],															
																'xml_value' => $uniq,
															);
															$wpdb->insert($wpdb->prefix.'currency', $up_arr);													
															$bd_array['currency_id_give'] = $wpdb->insert_id;
															$xml_value1 = $uniq;
														
														}													
													}
												}
																									
											}
											
											if(isset($bd_array['currency_give'])){
												unset($bd_array['currency_give']);
											}
											if(isset($bd_array['currency_code_give'])){
												unset($bd_array['currency_code_give']);											
											}
											if(isset($bd_array['currency_code_id_give'])){
												unset($bd_array['currency_code_id_give']);											
											}											
													
											if(isset($bd_array['currency_get']) and isset($bd_array['currency_code_get']) and $bd_array['currency_get'] and $bd_array['currency_code_get']){
													
												$currency_get = $bd_array['currency_get'];
												if(is_ml()){
													$currency_get_ml = '['. $locale .':]'. $currency_get .'[:'. $locale .']';
												} else {
													$currency_get_ml = $currency_get;
												}
													
												$psys_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."psys WHERE psys_title LIKE '%". $currency_get_ml ."%' OR psys_title = '$currency_get'");
												if(isset($psys_data->id)){
													$bd_array['psys_id_get'] = $psys_data->id;
												} else {	
													$up_arr = array(
														'psys_title' => $bd_array['currency_get'],
													);
													$wpdb->insert($wpdb->prefix.'psys', $up_arr);
													$bd_array['psys_id_get'] = $wpdb->insert_id;
												}												
																						
												if(isset($bd_array['currency_code_get'])){
													$now = $bd_array['currency_code_get'];
													$currency_code_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency_codes WHERE currency_code_title = '$now'");
													if(isset($currency_code_data->id)){
														$bd_array['currency_code_id_get'] = $currency_code_data->id;
													} else {	
														$up_arr = array(
															'currency_code_title' => $bd_array['currency_code_get'],
															'internal_rate' => '1',
														);
														$wpdb->insert($wpdb->prefix.'currency_codes', $up_arr);
														$bd_array['currency_code_id_get'] = $wpdb->insert_id;
													}
												}												
													
												if(isset($bd_array['psys_id_get']) and isset($bd_array['currency_code_id_get'])){
													if($bd_array['psys_id_get'] and $bd_array['currency_code_id_get']){
														$vals = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE psys_id='{$bd_array['psys_id_get']}' AND currency_code_id='{$bd_array['currency_code_id_get']}'");
														if(isset($vals->id)){
															
															$bd_array['currency_id_get'] = $vals->id;
															$xml_value2 = $vals->xml_value;
															
														} else {
														
															$uniq = pn_strip_symbols(replace_cyr($bd_array['currency_get']));
															$uniq = unique_xml_value($uniq, 0);
																
															$up_arr = array(
																'psys_title' => $bd_array['currency_get'],
																'psys_id' => $bd_array['psys_id_get'],
																'currency_code_title' => $bd_array['currency_code_get'],
																'currency_code_id' => $bd_array['currency_code_id_get'],															
																'xml_value' => $uniq,
															);
															$wpdb->insert($wpdb->prefix.'currency', $up_arr);													
															$bd_array['currency_id_get'] = $wpdb->insert_id;
															$xml_value2 = $uniq;
														
														}
													}
												}													
											}
												
											if(isset($bd_array['currency_get'])){
												unset($bd_array['currency_get']);
											}
											if(isset($bd_array['currency_code_get'])){
												unset($bd_array['currency_code_get']);											
											}
											if(isset($bd_array['currency_code_id_get'])){
												unset($bd_array['currency_code_id_get']);											
											}
											
											$install = 1;
												
											if($data_id){
												$cc = $wpdb->query("SELECT id FROM ". $wpdb->prefix ."directions WHERE auto_status = '1' AND id='$data_id'");
												if($cc > 0){
													$install = 0;
													$wpdb->update($wpdb->prefix.'directions', $bd_array, array('id'=>$data_id));
												} 											
											} 
														
											if($install == 1){
												if(isset($bd_array['psys_id_give']) and isset($bd_array['psys_id_get']) and isset($bd_array['currency_id_give']) and isset($bd_array['currency_id_get'])){
													if($bd_array['psys_id_give'] and $bd_array['psys_id_get'] and $bd_array['currency_id_give'] and $bd_array['currency_id_get']){	
														if($xml_value1 and $xml_value2){
															$direction_permalink_temp = apply_filters('direction_permalink_temp','[xmlv1]_to_[xmlv2]');
															$direction_permalink_temp = str_replace('[xmlv1]',$xml_value1, $direction_permalink_temp);
															$direction_permalink_temp = str_replace('[xmlv2]',$xml_value2, $direction_permalink_temp);
															$direction_name = is_direction_permalink($direction_permalink_temp);
															$bd_array['direction_name'] = unique_direction_name($direction_name, 0);
														} 
														$bd_array['tech_name'] = $tech_name;	
														$wpdb->insert($wpdb->prefix.'directions', $bd_array);
														$data_id = $wpdb->insert_id;
													}
												}
											}
											
											do_action('export_direction_end');
										}
									}
								}
							}							
						} 
							
						if($error == 0){
							if(is_file($targetFile)){
								@unlink($targetFile);
							}	
							$url = admin_url('admin.php?page=pn_export_direction&reply=true');
							wp_redirect($url);
							exit;	
						}	
					} else {
						pn_display_mess(__('Error! Error loading file','usve'));
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