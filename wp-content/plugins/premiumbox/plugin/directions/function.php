<?php
if( !defined( 'ABSPATH')){ exit(); }

function the_exchange_home($def_cur_from='', $def_cur_to='') {
	echo get_exchange_table($def_cur_from, $def_cur_to);
}
	
function get_exchange_table($def_cur_from='', $def_cur_to=''){
global $wpdb;	
	
	$temp = '';
	
	$arr = array(
		'from' => $def_cur_from,
		'to' => $def_cur_to,
		'direction_id' => '',
	);
	$arr = apply_filters('get_exchange_table_vtypes', $arr, 'web');
	
	$type_table = get_type_table();
	if($type_table == 100){
		$show_data = pn_exchanges_output('exchange');
	} else {
		$show_data = pn_exchanges_output('home');
	}
	
	if($show_data['text']){
		$temp .= '<div class="home_resultfalse"><div class="home_resultfalse_close"></div>'. $show_data['text'] .'</div>';
	}	
	
	if($show_data['mode'] == 1){
		$html = apply_filters('exchange_table_type', '', $type_table ,$arr['from'] ,$arr['to'], $arr['direction_id']);
		$temp .= apply_filters('exchange_table_type' . $type_table, $html ,$arr['from'] ,$arr['to'], $arr['direction_id']);
	} 	
	
	return $temp;
}	

add_filter('get_exchange_table_vtypes', 'def_get_exchange_table_vtypes', 100);
function def_get_exchange_table_vtypes($arr){
	$cur_from = is_xml_value(is_param_get('cur_from'));
	$cur_to = is_xml_value(is_param_get('cur_to'));
	if($cur_from or $cur_to){
		$arr['from'] = $cur_from;
		$arr['to'] = $cur_to;
	}		
	return $arr;
}

add_filter('get_directions_table1', 'def_get_directions_table1', 10, 5);
function def_get_directions_table1($directions, $place, $where, $v, $currency_id_give=''){
global $wpdb;

	$currency_id_give = intval($currency_id_give);
	if($currency_id_give > 0){
		$where .= " AND currency_id_give = '$currency_id_give'";
	}
	$directions = array();
	$dirs = array();
	$dirs_data = array();
	$directions_arr = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions WHERE $where");
	foreach($directions_arr as $dir){
		if(isset($v[$dir->currency_id_give], $v[$dir->currency_id_get])){
			$output = apply_filters('get_direction_output', 1, $dir, $place);
			if($output == 1){
				$dirs_data[$dir->id] = $dir;
				$dirs[$dir->id] = intval($v[$dir->currency_id_get]->t1_2);
			}
		}
	}	
	asort($dirs);
	foreach($dirs as $dir_id => $order){
		$dir = $dirs_data[$dir_id];
		$directions[$dir->currency_id_give][] = $dir;
	}
	
	return $directions;
}

function set_directions_data($place, $error_page=0, $id=0, $pnhash='', $cur1=0, $cur2=0, $cur_place_id=0){
global $wpdb, $direction_data, $premiumbox;

	$id = intval($id);
	$error_page = intval($error_page);
	$pnhash = is_direction_name($pnhash);
	$cur1 = intval($cur1);
	$cur2 = intval($cur2);
	$cur_place_id = intval($cur_place_id);
	
	$where = get_directions_where($place);
	$where_now = '';
	$set = 0;
	if($id > 0){
		$where_now .= " AND id='$id'";
		$set = 1;
	} elseif($pnhash){ 
		$where_now .= " AND direction_name='$pnhash'";
		$set = 1;
	} elseif($cur1 and $cur2){
		$where_now .= " AND currency_id_give='$cur1' AND currency_id_get='$cur2'";
		$set = 1;
	}
	if($set == 1){
		
		$dirs = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions WHERE $where $where_now");
		$dir = '';
		foreach($dirs as $d){
			$output = apply_filters('get_direction_output', 1, $d, $place);
			if($output){
				$dir = $d;
				break;
			}
		}
		
		if(!isset($dir->id)){
			$tablenot = intval($premiumbox->get_option('exchange','tablenot'));
			if($tablenot == 1 and $error_page != 1){
				if($cur_place_id != 0 and $cur1 and $cur2){
					if($cur_place_id == 1){
						$direction_items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions WHERE $where AND currency_id_give = '$cur1' ORDER BY site_order1 ASC");
						foreach($direction_items as $direction){
							$output = apply_filters('get_direction_output', 1, $direction, $place);
							if($output){
								$dir = $direction;
								break;
							}	
						}				
					} else {
						$direction_items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions WHERE $where AND currency_id_get='$cur2' ORDER BY site_order1 ASC");
						foreach($direction_items as $direction){
							$output = apply_filters('get_direction_output', 1, $direction, $place);
							if($output){
								$dir = $direction;
								break;
							}	
						}				
					}
				} 
				if(!isset($dir->id)){
					$direction_items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions WHERE $where ORDER BY site_order1 ASC");
					foreach($direction_items as $direction){
						$output = apply_filters('get_direction_output', 1, $direction, $place);
						if($output){
							$dir = $direction;
							break;
						}	
					}					
				}
			}
		}	
		
		if(isset($dir->id)){
			$currency_id_give = intval($dir->currency_id_give);
			$currency_id_get = intval($dir->currency_id_get);
			$vd1 = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE auto_status = '1' AND currency_status='1' AND id='$currency_id_give'");
			$vd2 = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE auto_status = '1' AND currency_status='1' AND id='$currency_id_get'");
			if(isset($vd1->id) and isset($vd2->id)){			
				$direction_data = array();
				
				$direction_data['direction_id'] = intval($dir->id);
				$direction_data['item_give'] = get_currency_title($vd1);
				$direction_data['item_get'] = get_currency_title($vd2);
				$direction_data['currency_id_give'] = $vd1->id;
				$direction_data['currency_id_get'] = $vd2->id;
				$direction_data['vd1'] = $vd1;
				$direction_data['vd2'] = $vd2;
				$direction_data['direction'] = $dir;
				
				if(!is_object($direction_data)){
					$direction_data = (object)$direction_data;
				}
			}	
		}
	}
}

function set_exchange_shortcode($place='', $side_id=''){
global $wpdb, $premiumbox, $direction_data;	
	
	$array = array();
	
	if(isset($direction_data->direction_id) and $direction_data->direction_id > 0){
	
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
	
		$direction_id = intval($direction_data->direction_id);
		$vd1 = $direction_data->vd1;
		$vd2 = $direction_data->vd2;
		$direction = $direction_data->direction;
		$cdata = array();
		
		$side_id = intval($side_id);
		if($side_id != 2){ $side_id = 1; }
		
		/* message */
		$text = get_direction_descr('timeline_txt', $direction, $vd1, $vd2);
		$text = apply_filters('direction_instruction', $text, 'timeline_txt', $direction, $vd1, $vd2);
		$text = ctv_ml($text);
					
		$message = '';
		if($text){	
			$message = '
			<div class="notice_message">
				<div class="notice_message_ins">
					<div class="notice_message_abs"></div>
					<div class="notice_message_close"></div>
					<div class="notice_message_title">
						<div class="notice_message_title_ins">
							<span>'. __('Attention!','pn') .'</span>
						</div>
					</div>
					<div class="notice_message_text">
						<div class="notice_message_text_ins">
							'. apply_filters('comment_text',$text) .'
						</div>
					</div>
				</div>
			</div>';					
		}
		/* end message */		
		
		/* window */
		$text = get_direction_descr('window_txt', $direction, $vd1, $vd2);	
		$text = apply_filters('direction_instruction', $text, 'window_txt', $direction, $vd1, $vd2);
		$text = ctv_ml($text);
					
		$window_txt = '';	
		if($text){											
			$window_txt = '
			<div class="window_message" style="display: none;">
				<div class="window_message_ins">
					'. apply_filters('comment_text',$text) .'
				</div>
			</div>';			
		}					
		/* end window */

		/* description */
		$text = get_direction_descr('description_txt', $direction, $vd1, $vd2);	
		$text = apply_filters('direction_instruction', $text, 'description_txt', $direction, $vd1, $vd2);
		$text = ctv_ml($text);
					
		$description = '';	
		if($text){			
			$title = get_exchange_title();								
			$description = '
			<div class="warning_message" itemscope itemtype="https://schema.org/Article">
				<div class="warning_message_ins">
					<div class="warning_message_abs"></div>
					<div class="warning_message_close"></div>
					<div class="warning_message_title">
						<div class="warning_message_title_ins" itemprop="name">
							<span>'. $title .'</span>
						</div>
					</div>
					<div class="warning_message_text">
						<div class="warning_message_text_ins" itemprop="articleBody">
							'. apply_filters('comment_text',$text) .'
						</div>
					</div>
				</div>
			</div>';			
		}										
		/* end description */		
		
		/* submit */
		$now_cl = 'xchange';
		if($place != 'exchange_html_list'){
			$now_cl = 'hexch';
		}		
		$submit = '
		<div class="'. $now_cl .'_submit_div">
			<input type="submit" formtarget="_top" class="'. $now_cl .'_submit" name="" value="'. __('Exchange','pn') .'" /> 
				<div class="clear"></div>
		</div>';
		/* end submit */		
		
		/* check */	
		$not_check_data = intval(get_pn_cookie('not_check_data'));
		$ch_ch = '';
		if($not_check_data == 1){
			$ch_ch = 'checked="checked"';				
		}
		
		$now_cl = 'xchange_checkdata_div';
		if($place != 'exchange_html_list'){
			$now_cl = 'hexch_checkdata_div';
		}
			
		$remember ='
		<div class="'. $now_cl .'">
			<label><input type="checkbox" id="not_check_data" name="not_check_data" '. $ch_ch .' value="1" /> '. __('Do not remember entered data','pn') .'</label>
		</div>
		';
					
		$check = '';
		$enable_step2 = intval($premiumbox->get_option('exchange','enable_step2'));
		if($enable_step2 == 0){
			$check = '	
			<div class="'. $now_cl .'">
				<label><input type="checkbox" name="check_rule" value="1" /> '. sprintf(__('I read and agree with <a href="%s" target="_blank">the terms and conditions</a>','pn'), $premiumbox->get_page('tos')) .'</label>
			</div>
			';
		}
		/* end check */

		$tableicon = get_icon_for_table();
		if(function_exists('is_mobile') and is_mobile()){
			$tableicon = get_mobile_icon_for_table();
		}

		$currency_logo_give = get_currency_logo($vd1, $tableicon);
		$currency_logo_get = get_currency_logo($vd2, $tableicon);

		/* selects */
		$directions1 = $directions2 = array();
			
		$currency_id_give = $vd1->id;
		$currency_id_get = $vd2->id;
			
		$select_give = $select_get = '';
			
		$tableselect = intval($premiumbox->get_option('exchange','tableselect'));
			
		$v = get_currency_data();	
			
		if($place == 'exchange_html_list'){	
			$pl_id = 'exchange';
		} else {
			$pl_id = 'home';
		}
		$where = get_directions_where($pl_id);
		$directions_arr = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions WHERE $where ORDER BY site_order1 ASC");
		foreach($directions_arr as $nd){
			$output = apply_filters('get_direction_output', 1, $nd, $pl_id);
			if($output){
				if($tableselect == 1){
					if($side_id == 1){ /* если выбрана левая сторона */
						$directions1[$nd->currency_id_give] = $nd;
						if($nd->currency_id_give == $currency_id_give){
							$directions2[$nd->currency_id_get] = $nd;
						}
					} else { /* если выбрана правая сторона */
						$directions2[$nd->currency_id_get] = $nd;
						if($nd->currency_id_get == $currency_id_get){
							$directions1[$nd->currency_id_give] = $nd;
						}						
					}
				} else {
					$directions1[$nd->currency_id_give] = $nd;
					$directions2[$nd->currency_id_get] = $nd;					
				}
			}
		}		
		
		$select_give = '
		<select name="" class="js_my_sel" autocomplete="off" id="select_give">'; 
			foreach($directions1 as $key => $np){
				$select_give .= '<option value="'. $key .'" '. selected($key,$currency_id_give,false) .' data-img="'. get_currency_logo(is_isset($v,$key), $tableicon) .'" data-logo="'. get_currency_logo(is_isset($v,$key), 1) .'" data-logo-next="'. get_currency_logo(is_isset($v,$key), 2) .'">'. get_currency_title(is_isset($v,$key)) .'</option>';
			}
		$select_give .= '
		</select>';	

		$select_get = '
		<select name="" class="js_my_sel" autocomplete="off" id="select_get">';
			foreach($directions2 as $key => $np){					
				$select_get .= '<option value="'. $key .'" '. selected($key,$currency_id_get,false) .' data-img="'. get_currency_logo(is_isset($v,$key), $tableicon) .'" data-logo="'. get_currency_logo(is_isset($v,$key), 1) .'" data-logo-next="'. get_currency_logo(is_isset($v,$key), 2) .'">'. get_currency_title(is_isset($v,$key)) .'</option>';					
			}
		$select_get .= '
		</select>';	
		/* end selects */		
		
		$post_sum = is_sum(is_param_get('get_sum'));
		if($post_sum <= 0){
			$post_sum = is_sum(get_pn_cookie('cache_sum'));
		}		
		$calc_data = array(
			'vd1' => $vd1,
			'vd2' => $vd2,
			'direction' => $direction,
			'user_id' => $user_id,
			'ui' => $ui,
			'post_sum' => $post_sum,
		);
		$calc_data = apply_filters('get_calc_data_params', $calc_data);
		$cdata = get_calc_data($calc_data);

		$currency_code_give = $cdata['currency_code_give'];
		$currency_code_get = $cdata['currency_code_get'];
		$psys_give = $cdata['psys_give'];
		$psys_get = $cdata['psys_get'];		
		
		$get_reserv = get_direction_reserv($vd1, $vd2, $direction);
		$reserv = is_out_sum($get_reserv, $vd2->currency_decimal, 'reserv');
		
		$viv_com1 = $cdata['viv_com1'];
		$viv_com2 = $cdata['viv_com2'];
		
		$viv_com1_style = 'style="display: none;"'; /* не выводим поле доп.комиссии */
		if($viv_com1){
			$viv_com1_style = '';
		}
													
		$viv_com2_style = 'style="display: none;"'; /* не выводим поле доп.комиссии */
		if($viv_com2){
			$viv_com2_style = '';
		}
		
		$comis_text1 = $cdata['comis_text1'];
		$comis_text2 = $cdata['comis_text2'];
		
		$sum1 = $cdata['sum1'];
		$sum1c = $cdata['sum1c'];
		$sum2 = $cdata['sum2'];
		$sum2c = $cdata['sum2c'];		
		
		$sum1_error = $sum2_error = $sum1c_error = $sum2c_error = '';
		$sum1_error_txt = $sum2_error_txt = $sum1c_error_txt = $sum2c_error_txt = ''; 
		
		$user_discount = $cdata['user_discount'];
		$user_discount_html = '';
		$user_discounttext_html = '';
		$us = '';
		if($user_discount > 0){
			$us = '<p><span class="span_skidka">'. __('Your discount','pn') .': <span class="js_direction_user_discount">'. $user_discount .'</span>%</span></p>';
			$user_discount_html = '<span class="js_direction_user_discount">'. $user_discount .'</span>%';
			$user_discounttext_html = '<div class="user_discount_div"><span class="user_discount_label">'. __('Your discount','pn') .'</span>: <span class="js_direction_user_discount">'. $user_discount .'</span>%</div>';
		}
		
		$dir_minmax = get_direction_minmax($direction, $vd1, $vd2, $cdata['course_give'], $cdata['course_get'], $get_reserv);    
		$min1 = is_isset($dir_minmax, 'min_give');
		$max1 = is_isset($dir_minmax, 'max_give');
		$min2 = is_isset($dir_minmax, 'min_get');
		$max2 = is_isset($dir_minmax, 'max_get');										
		
		if($sum1 < $min1){
			$sum1_error = 'error';
			$sum1_error_txt = '<span class="js_amount" data-id="sum1" data-val="'. $min1 .'">' . __('min','pn').'.: '. is_out_sum($min1, $vd1->currency_decimal, 'reserv') .' '. $currency_code_give . '</span>';													
		}		
		if($sum1 > $max1 and is_numeric($max1)){
			$sum1_error = 'error';
			$sum1_error_txt = '<span class="js_amount" data-id="sum1" data-val="'. $max1 .'">' . __('max','pn').'.: '. is_out_sum($max1, $vd1->currency_decimal, 'reserv') .' '. $currency_code_give . '</span>';													
		}
		if($sum2 < $min2){
			$sum2_error = 'error';
			$sum2_error_txt = '<span class="js_amount" data-id="sum2" data-val="'. $min2 .'">' . __('min','pn').'.: '. is_out_sum($min2, $vd2->currency_decimal, 'reserv') .' '. $currency_code_get . '</span>';													
		}		
		if($sum2 > $max2 and is_numeric($max2)){
			$sum2_error = 'error';
			$sum2_error_txt = '<span class="js_amount" data-id="sum2" data-val="'. $max2 .'">' . __('max','pn').'.: '. is_out_sum($max2, $vd2->currency_decimal, 'reserv') .' '. $currency_code_get . '</span>';													
		}
		
		if($sum1 <= 0){
			$sum1_error = 'error';
			$sum1_error_txt = __('amount must be greater than 0','pn');
		}
		if($sum1c <= 0){
			$sum1c_error = 'error';
			$sum1c_error_txt = __('amount must be greater than 0','pn');
		}
		if($sum2 <= 0){
			$sum2_error = 'error';
			$sum2_error_txt = __('amount must be greater than 0','pn');
		}
		if($sum2c <= 0){
			$sum2c_error = 'error';
			$sum2c_error_txt = __('amount must be greater than 0','pn');
		}		
		
		$now_cl = 'xchange_sum_input';
		if($place != 'exchange_html_list'){
			$now_cl = 'hexch_curs_input';
		}		
		
		$input_give = '
		<div class="'. $now_cl .' js_wrap_error js_wrap_error_br '. $sum1_error .'">';
			$input_give .= apply_filters('exchange_input', '', 'give', $cdata, $calc_data);
			$input_give .= '
			<div class="js_error js_sum1_error">'. $sum1_error_txt .'</div>					
		</div>				
		';
					
		$input_get = '
		<div class="'. $now_cl .' js_wrap_error js_wrap_error_br '. $sum2_error .'">';
			$input_get .= apply_filters('exchange_input', '', 'get', $cdata, $calc_data);
			$input_get .= '
			<div class="js_error js_sum2_error">'. $sum2_error_txt .'</div>					
		</div>				
		';

		if($place == 'exchange_html_list'){
			$com_give_text = '
			<div class="xchange_sumandcom js_viv_com1" '. $viv_com1_style .'>
				<span class="js_comis_text1">'. $comis_text1 .'</span>
			</div>';		
			
			$com_get_text = '
			<div class="xchange_sumandcom js_viv_com2" '. $viv_com2_style .'>
				<span class="js_comis_text2">'. $comis_text2 .'</span>
			</div>';		

			$com_give ='
			<div class="xchange_sum_input js_wrap_error js_wrap_error_br '. $sum1c_error .'">';
				$com_give .= apply_filters('exchange_input', '', 'give_com', $cdata, $calc_data);
				$com_give .= '
				<div class="js_error js_sum1c_error">'. $sum1c_error_txt .'</div>
			</div>';

			$com_get ='
			<div class="xchange_sum_input js_wrap_error js_wrap_error_br '. $sum2c_error .'">';
				$com_get .= apply_filters('exchange_input', '', 'get_com', $cdata, $calc_data);
				$com_get .= '
				<div class="js_error js_sum2c_error">'. $sum2c_error_txt .'</div>
			</div>';		
		} else {
			$com_give = '
			<div class="hexch_curs_input hexch_sum_input js_wrap_error js_wrap_error_br '. $sum1c_error .'">';
				$com_give .= apply_filters('exchange_input', '', 'give_com', $cdata, $calc_data);
				$com_give .= '
				<div class="js_error js_sum1c_error">'. $sum1c_error_txt .'</div>
			</div>				
			';
				
			$com_give_text = '
			<div class="hexch_comis_line js_viv_com1" '. $viv_com1_style .'>
				<span class="js_comis_text1">'. $comis_text1 .'</span>
			</div>				
			';
				
			$com_get = '
			<div class="hexch_curs_input hexch_sum_input js_wrap_error js_wrap_error_br '. $sum2c_error .'">';
				$com_get .= apply_filters('exchange_input', '', 'get_com', $cdata, $calc_data);
				$com_get .= '
				<div class="js_error js_sum2c_error">'. $sum2c_error_txt .'</div>
			</div>				
			';
				
			$com_get_text = '
			<div class="hexch_comis_line js_viv_com2" '. $viv_com2_style .'>
				<span class="js_comis_text2">'. $comis_text2 .'</span>
			</div>				
			';		
		}

		$vz1 = array();
		if($min1 > 0){
			$vz1[] = '<span class="js_amount" data-id="sum1" data-val="'. $min1 .'">' . __('min','pn').'.: '. is_out_sum($min1, $vd1->currency_decimal, 'reserv') .' '.$currency_code_give . '</span>';
		}
		if(is_numeric($max1)){
			$vz1[] = '<span class="js_amount" data-id="sum1" data-val="'. $max1 .'">' . __('max','pn').'.: '. is_out_sum($max1, $vd1->currency_decimal, 'reserv') .' '.$currency_code_give . '</span>';
		}
		$minmax_give_html = '';
		if(count($vz1) > 0){
			$minmax_give_html = '<p class="span_give_max">'. join(', ',$vz1) .'</p>';
		}
								
		$vz2 = array();	
		if($min2 > 0){
			$vz2[] = '<span class="js_amount" data-id="sum2" data-val="'. $min2 .'">' . __('min','pn').'.: '. is_out_sum($min2, $vd2->currency_decimal, 'reserv') .' '.$currency_code_get . '</span>';
		}
		if(is_numeric($max2)){
			$vz2[] = '<span class="js_amount" data-id="sum2" data-val="'. $max2 .'">' . __('max','pn').'.: '. is_out_sum($max2, $vd2->currency_decimal, 'reserv') .' '.$currency_code_get . '</span>';
		}
		$minmax_get_html = '';
		if(count($vz2) > 0){
			$minmax_get_html = '<p class="span_get_max">'. join(', ',$vz2) .'</p>';
		}																																											
				
		$meta1 = $meta2 = $meta1d = $meta2d = '';

		$course_html = '<span class="js_curs_html">'. is_out_sum($cdata['course_give'], $cdata['decimal_give'], 'course') .' '. $currency_code_give .' = '. is_out_sum($cdata['course_get'], $cdata['decimal_get'], 'course') .' '. $currency_code_get .'</span>';

		if($place == 'exchange_html_list'){
			
			$meta1d = '<div class="xchange_info_line">'. __('Exchange rate','pn') .': '. $course_html .'</div>';	
			if($minmax_give_html){
				$meta1 = '<div class="xchange_info_line">'. $minmax_give_html .'</div>';
			}
			if($us){
				$meta2d = '<div class="xchange_info_line">'. $us .'</div>';
			}		
			if($minmax_get_html){
				$meta2 = '<div class="xchange_info_line">'. $minmax_get_html .'</div>';
			}	
			
		} else {
			
			$meta1 = '
			<div class="hexch_txt_line">
				'. $minmax_give_html .'
			</div>';	
			
			$meta2 = '
			<div class="hexch_txt_line">
				'. $minmax_get_html .'
			</div>';
			
		}																																		
		
		$array = array(
			'[timeline]' => $message,
			'[description]' => $description,
			'[window]' => $window_txt,
			'[other_filter]' => apply_filters('exchange_other_filter', '', $direction, $vd1, $vd2, $cdata),
			'[result]' => '<div class="ajax_post_bids_res"></div>',
			'[check]' => apply_filters('exchange_check_filter', $check, $direction, $vd1, $vd2, $cdata),
			'[remember]' => $remember,
			'[submit]' => $submit,
			'[filters]' => apply_filters('exchange_step1','', $direction, $vd1, $vd2, $cdata),
			'[reserve]' => '<span class="js_reserv_html">'. $reserv .' '. $currency_code_get .'</span>',
			'[course]' => $course_html,
			'[psys_give]' => $psys_give,
			'[vtype_give]' => $currency_code_give,
			'[currency_code_give]' => $currency_code_give,
			'[psys_get]' => $psys_get,
			'[vtype_get]' => $currency_code_get,
			'[currency_code_get]' => $currency_code_get,
			'[currency_logo_give]' => $currency_logo_give,
			'[currency_logo_get]' => $currency_logo_get,
			'[user_discount]' => $user_discount_html,
			'[user_discount_html]' => $user_discounttext_html,
			'[select_give]' => $select_give,
			'[select_get]' => $select_get,
			'[minmax_give]' => $minmax_give_html,
			'[minmax_get]' => $minmax_get_html,
			'[meta1]' => $meta1,
			'[meta2]' => $meta2,
			'[meta1d]' => $meta1d,
			'[meta2d]' => $meta2d,
			'[input_give]' => $input_give,
			'[input_get]' => $input_get,
			'[com_give]' => $com_give,
			'[com_give_text]' => $com_give_text,
			'[com_get]' => $com_get,
			'[com_get_text]' => $com_get_text,	
			'[account_give]' => get_account_wline($vd1, $direction, 1, $place),	
			'[account_get]' => get_account_wline($vd2, $direction, 2, $place),
			'[give_field]' => get_doppole_wline($vd1, $direction, 1, $place),
			'[get_field]' => get_doppole_wline($vd2, $direction, 2, $place),
			'[com_class_give]' => $viv_com1_style,
			'[com_class_get]' => $viv_com2_style,
		);	
		$array['[direction_field]'] = get_direction_wline($direction, $place);				
		$array = apply_filters($place, $array, $direction, $vd1, $vd2, $cdata);
	}		

	return $array;
}

function get_account_wline($vd, $direction, $side_id, $place){
global $wpdb;	

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);

	$temp = '';
	
	$currency_id = $vd->id;
	if($side_id == 1){
		$show = $vd->show_give;
		$show = apply_filters('form_bids_account_give', $show, $direction, $vd);
		$txt = pn_strip_input(ctv_ml(is_isset($vd,'txt_give')));
		if(!$txt){ $txt = __('From account','pn'); }
	} else {
		$show = $vd->show_get;
		$show = apply_filters('form_bids_account_get', $show, $direction, $vd);
		$txt = pn_strip_input(ctv_ml(is_isset($vd,'txt_get')));
		if(!$txt){ $txt = __('Into account','pn'); }
	}
		
	$helps = apply_filters('account_tooltip', '', $vd, $side_id);	
		
	$notv = 'style="display: none"';
	if($show == 1){ $notv = ''; }
		
	$firstzn = create_placeholder($vd);				
	$placeholder = apply_filters('placeholder_field_account', $firstzn, $vd, $direction);
														
	$h_class = '';
	$h_div = '';
	$has_help_cl = '';
	$help_span = '';		
	if($helps){
		$has_help_cl = 'has_help';
		$help_span = '<span class="help_tooltip_label"></span>';			
		$h_class = 'js_help';
		$h_div = '
		<div class="info_window js_window">
			<div class="info_window_ins">
				<div class="info_window_abs"></div>
				'. apply_filters('comment_text', $helps) .'
			</div>
		</div>															
		';
	}
														
	$purse = get_purse(get_pn_cookie('cache_account'. $currency_id), $vd);
		
	if($place == 'exchange_html_list_ajax'){
		$class = 'hexch';			
	} else {
		$class = 'xchange';			
	}

	$temp .= '
	<div class="'. $class .'_curs_line" '. $notv .'>
		<div class="'. $class .'_curs_line_ins">
			<div class="'. $class .'_curs_label">
				<div class="'. $class .'_curs_label_ins">
					<label for="account'. $side_id .'"><span class="'. $class .'_label">'. $txt .'<span class="req">*</span>: '. $help_span .'</span></label>
				</div>
			</div>	
		</div>
		<div class="'. $class .'_curs_input js_wrap_error js_wrap_error_br js_window_wrap">
	';
		$account_input = '<input type="text" name="account'. $side_id .'" cash-id="account'. $currency_id .'" id="account'. $side_id .'" class="js_account'. $side_id .' '. $h_class .' cache_data check_cache" autocomplete="off" placeholder="'. $placeholder .'" value="'. $purse .'" />';
		$temp .= apply_filters('form_bids_account_input', $account_input, $side_id, $vd, $purse, $placeholder, $h_class);
	$temp .= '
		<div class="js_error js_account'. $side_id .'_error"></div>
			'. $h_div .'
		</div>
			<div class="clear"></div>
	';
			
		$temp .= apply_filters('get_account_wline', '', $vd, $direction, $side_id, $place); 			
	$temp .= '
	</div>	
	';	
	
	return $temp;
}

function get_direction_wline($direction, $place){
global $wpdb;	

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	

	$temp = '';
	
	$direction_id = $direction->id;
	$datas = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."direction_custom_fields LEFT OUTER JOIN ". $wpdb->prefix ."cf_directions ON(".$wpdb->prefix."direction_custom_fields.id = ". $wpdb->prefix ."cf_directions.cf_id) WHERE ".$wpdb->prefix."direction_custom_fields.auto_status='1' AND ".$wpdb->prefix."direction_custom_fields.status='1' AND ". $wpdb->prefix ."cf_directions.direction_id = '$direction_id' ORDER BY cf_order ASC");
	
	if($place == 'exchange_html_list_ajax'){
		$class = 'hexch';
	} else {
		$class = 'xchange';
	}	
	
	if(count($datas) > 0){
		
		$temp .= '
		<div class="'. $class .'_pers">
			<div class="'. $class .'_pers_ins">
													
				<div class="'. $class .'_pers_title">
					<div class="'. $class .'_pers_title_ins">
						<span>'. apply_filters('exchange_personaldata_title',__('Personal data','pn')) .'</span>
					</div>
				</div>
				<div class="'. $class .'_pers_div">
					<div class="'. $class .'_pers_div_ins">';

					foreach($datas as $data){
	
						$title = pn_strip_input(ctv_ml($data->cf_name));
						$data_id = $data->cf_id;
						$cf_req = $data->cf_req;
						$req = '';
						if($cf_req == 1){
							$req = '<span class="req">*</span>';
						}	
					
						$helps = apply_filters('direction_custom_fields_tooltip', '', $data);
						$has_help_cl = '';
						$help_span = '';
						if($helps){
							$has_help_cl = 'has_help';
							$help_span = '<span class="help_tooltip_label"></span>';
						}					
					
						$temp .= '
						<div class="'. $class .'_pers_line '. $has_help_cl .'">
							<div class="'. $class .'_pers_label">
								<div class="'. $class .'_pers_label_ins">
									<label for="cf'. $data_id .'"><span class="'. $class .'_label">'. $title .''. $req .': '. $help_span .'</span></label>
								</div>	
							</div>
							<div class="'. $class .'_pers_input">
								<div class="js_wrap_error js_wrap_error_br js_window_wrap">';
									
								$vid = $data->vid;
								
								$value = '';
								$cf_auto = $data->cf_auto;
								
								$value = pn_strip_input(get_pn_cookie('cache_cf'. $data_id));
								if($user_id and !$value){
									$fields = apply_filters('user_fields_in_website', array());
									if(isset($fields[$cf_auto])){
										$value = strip_uf(is_isset($ui, $cf_auto), $cf_auto);
									}
								}				
								
								if($vid == 0){
									
									$h_class = '';
									$h_div = '';
									if($helps){
										$h_class = 'js_help';
										$h_div = '
										<div class="info_window js_window">
											<div class="info_window_ins">
												<div class="info_window_abs"></div>
												'. apply_filters('comment_text', $helps) .'
											</div>
										</div>															
										';
									}		
									
									$firstzn = create_placeholder($data);										
								
									$value = get_purse($value, $data);

									$temp .= '
									<input type="text" name="cf'. $data_id .'" cash-id="cf'. $data_id .'" id="cf'. $data_id .'" class="cache_data check_cache js_cf'. $data_id .' '. $h_class .'" autocomplete="off" placeholder="'. $firstzn .'" value="'. $value .'" />
									'. $h_div .'								
									';				
									
								} else {
									
									$temp .= '
									<select name="cf'. $data_id .'" cash-id="cf'. $data_id .'" class="js_my_sel cache_data check_cache js_cf'. $data_id .'" id="cf'. $data_id .'" autocomplete="off">';
										$temp .= '<option value="0" '. selected(0, $value, false) .'>'. __('No selected','pn') .'</option>';
										$datas = explode("\n",ctv_ml($data->datas));
										foreach($datas as $key => $da){
											$key = $key + 1;
											$da = pn_strip_input($da);
											if($da){
												$temp .= '<option value="'. $key .'" '. selected($key, $value, false) .'>'. $da .'</option>';
											}
										}
									$temp .= '	
									</select>	
									';
									
								}				
									
									$temp .= '
									<div class="js_error js_cf'. $data_id .'_error"></div>
								</div>
							</div>
								<div class="clear"></div>
						</div>';
					}
	
					$temp .= '
						<div class="clear"></div>
					</div>
				</div>											
			</div>
		</div>
		';	
	
	}
	
	return $temp;	
} 

function get_doppole_wline($vd, $direction, $id, $place){
global $wpdb;	

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);

	$temp = '';
	
	$where = '';
	if($id == 1){
		$where .= " AND ". $wpdb->prefix ."cf_currency.place_id IN('0','1')";
		$orderby = 'cf_order_give';
	} else {
		$where .= " AND ". $wpdb->prefix ."cf_currency.place_id IN('0','2')";
		$orderby = 'cf_order_get';
	}
	
	$currency_id = $vd->id;
	
	$sql ="
	SELECT * FROM ".$wpdb->prefix."currency_custom_fields
	LEFT OUTER JOIN ". $wpdb->prefix ."cf_currency
	ON(".$wpdb->prefix."currency_custom_fields.id = ". $wpdb->prefix ."cf_currency.cf_id)
	WHERE ".$wpdb->prefix."currency_custom_fields.auto_status='1' AND ".$wpdb->prefix."currency_custom_fields.status='1' AND ". $wpdb->prefix ."cf_currency.currency_id = '$currency_id' $where
	ORDER BY $orderby ASC
	";
	$datas = $wpdb->get_results($sql);
	foreach($datas as $data){
		$place_id = $data->place_id;
		$data_id = $data->cf_id;
		$cf_now = 'cfgive'.$data_id;
		if($place_id == 2){
			$cf_now = 'cfget'.$data_id;
		}
		$title = pn_strip_input(ctv_ml($data->cf_name));
		$cf_req = $data->cf_req;
		$req = '';
		if($cf_req == 1){
			$req = '<span class="req">*</span>';
		}
		
		if($place == 'exchange_html_list_ajax'){
			$class = 'hexch';			
		} else {
			$class = 'xchange';			
		}		
		
		if($id == 1){
			$helps = apply_filters('currency_custom_fields_tooltip', '', $data, $id);
		} else {
			$helps = apply_filters('currency_custom_fields_tooltip', '', $data, $id);
		}
		$has_help_cl = '';
		$help_span = '';
		if($helps){
			$has_help_cl = 'has_help';
			$help_span = '<span class="help_tooltip_label"></span>';
		}
		
		$temp .= '
		<div class="'. $class .'_curs_line '. $has_help_cl .'">
			<div class="'. $class .'_curs_label">
				<div class="'. $class .'_curs_label_ins">
					<label for="'. $cf_now .'"><span class="'. $class .'_label">'. $title .''. $req .': '. $help_span .'</span></label>
				</div>	
			</div>
			<div class="'. $class .'_curs_input js_wrap_error js_wrap_error_br js_window_wrap">
			';
			
			$value = pn_strip_input(get_pn_cookie('cache_' . $cf_now)); 
			
			$vid = $data->vid;
			if($vid == 0){
				
				$h_class = '';
				$h_div = '';
				if($helps){
					$h_class = 'js_help';
					$h_div = '
					<div class="info_window js_window">
						<div class="info_window_ins">
							<div class="info_window_abs"></div>
							'. apply_filters('comment_text', $helps) .'
						</div>
					</div>															
					';
				}	

				$firstzn = create_placeholder($data);	
													
				$value = get_purse($value, $data);				

				$temp .= '
				<input type="text" name="'. $cf_now .'" id="'. $cf_now .'" cash-id="'. $cf_now .'" class="js_'. $cf_now .' cache_data check_cache '. $h_class .'" autocomplete="off" placeholder="'. $firstzn .'" value="'. $value .'" />
				'. $h_div .'								
				';				
				
			} else {
				
				$temp .= '
				<select name="'. $cf_now .'" cash-id="'. $cf_now .'" class="js_my_sel js_'. $cf_now .' cache_data check_cache" id="'. $cf_now .'" autocomplete="off">';
					$temp .= '<option value="0" '. selected(0, $value, false) .'>'. __('No selected','pn') .'</option>';
					$datas = explode("\n",ctv_ml($data->datas));
					foreach($datas as $key => $da){
						$key = $key + 1;
						$da = pn_strip_input($da);
						if($da){
							$temp .= '<option value="'. $key .'" '. selected($key, $value, false) .'>'. $da .'</option>';
						}
					}
				$temp .= '	
				</select>	
				';
				
			}
			
			$temp .= '
				<div class="js_error js_'. $cf_now .'_error"></div>
			</div>
				<div class="clear"></div>
		</div>	
		';
	}
	
	return $temp;
}

add_filter('exchange_input', 'def_exchange_input', 10, 4);
function def_exchange_input($html, $place, $cdata, $calc_data){
	
	$dis1 = $dis1c = $dis2 = $dis2c = '';
	if($cdata['dis1'] == 1){ $dis1 = 'disabled="disabled"'; }
	if($cdata['dis1c'] == 1){ $dis1c = 'disabled="disabled"'; }
	if($cdata['dis2'] == 1){ $dis2 = 'disabled="disabled"'; }
	if($cdata['dis2c'] == 1){ $dis2c = 'disabled="disabled"'; }	
	
	$sum1 = is_sum(is_isset($cdata,'sum1'));
	$sum1c = is_sum(is_isset($cdata,'sum1c'));
	$sum2 = is_sum(is_isset($cdata,'sum2'));
	$sum2c = is_sum(is_isset($cdata,'sum2c'));		
	
	if($place == 'give'){
		$html = '<input type="text" name="sum1" '. $dis1 .' autocomplete="off" cash-id="sum" data-decimal="'. $cdata['decimal_give'] .'" class="js_sum_val js_decimal js_sum1 cache_data" value="'. $sum1 .'" />';
	} elseif($place == 'give_com'){
		$html = '<input type="text" name="" '. $dis1c .' autocomplete="off" class="js_sum_val js_decimal js_sum1c" data-decimal="'. $cdata['decimal_give'] .'" value="'. $sum1c .'" />';
	} elseif($place == 'get'){
		$html = '<input type="text" name="" '. $dis2 .' autocomplete="off" class="js_sum_val js_decimal js_sum2" data-decimal="'. $cdata['decimal_get'] .'" value="'. $sum2 .'" />';
	} elseif($place == 'get_com'){	
		$html = '<input type="text" name="" '. $dis2c .' autocomplete="off" class="js_sum_val js_decimal js_sum2c" data-decimal="'. $cdata['decimal_get'] .'" value="'. $sum2c .'" /> ';
	}
	
	return $html;
}