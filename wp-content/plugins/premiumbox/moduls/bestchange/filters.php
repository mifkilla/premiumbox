<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('item_direction_copy', 'item_direction_copy_bestchange', 1, 2); 
function item_direction_copy_bestchange($last_id, $new_id){
global $wpdb;
	$broker = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."bestchange_directions WHERE direction_id='$last_id'"); 
	if(isset($broker->id)){
		$arr = array();
		foreach($broker as $k => $v){
			$arr[$k] = $v;
		}
		$arr['direction_id'] = $new_id;		
		$wpdb->insert($wpdb->prefix.'bestchange_directions', $arr);
	}
}

add_filter('standart_course_direction', 'bestchange_standart_course_direction', 10, 2);
function bestchange_standart_course_direction($ind, $item){
	if($item->bestchange_id > 0){
		$ind = 1;
	}
	return $ind;
}

add_filter('list_tabs_direction', 'bestchange_list_tabs_direction', 10);
function bestchange_list_tabs_direction($list_tabs){
	if(current_user_can('administrator') or current_user_can('pn_bestchange')){
		$new_list_tabs = array();
		$new_list_tabs['bestchange'] = __('BestChange parser','pn');
		$list_tabs = pn_array_insert($list_tabs, 'tab2', $new_list_tabs);
	}	
	return $list_tabs;
}
 
add_action('tab_direction_bestchange', 'def_tab_direction_bestchange');
function def_tab_direction_bestchange($data){	
global $wpdb;
 
	$data_id = is_isset($data,'id');
		
	$broker = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."bestchange_directions WHERE direction_id='$data_id'"); 
		
	$v1 = intval(is_isset($broker,'v1'));
	$v2 = intval(is_isset($broker,'v2'));
	$reset_course = intval(is_isset($broker,'reset_course'));
	$status = intval(is_isset($broker,'status'));
		
	$alls = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."bestchange_currency_codes ORDER BY currency_code_title ASC");  
 	?>	
		<div class="add_tabs_line">
			<div class="add_tabs_title"><?php _e('BestChange parser','pn'); ?></div>
			<div class="add_tabs_submit">
				<input type="submit" name="" class="button" value="<?php _e('Save'); ?>" />
			</div>
		</div>	
		
		<div class="add_tabs_line">
			<div class="add_tabs_single long">
				<div class="add_tabs_sublabel"><span><?php _e('Enable parser','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="bestchange_status" autocomplete="off">
						<option value="0" <?php selected($status,0); ?>><?php _e('No','pn'); ?></option>
						<option value="1" <?php selected($status,1); ?>><?php _e('Yes','pn'); ?></option>
					</select>
				</div>			
			</div>
		</div>

		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Black list of exchangers ID (separate coma)','pn') ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="bestchange_black_ids" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'black_ids')); ?>" />
				</div>
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('White list of exchangers ID (separate coma)','pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="bestchange_white_ids" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'white_ids')); ?>" />
				</div>
			</div>
		</div>		
	
		<div class="add_tabs_line">
			<div class="add_tabs_label"><span><?php _e('Currencies','pn'); ?></span></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Send','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="bestchange_v1" autocomplete="off">
						<option value="0">--<?php _e('Send','pn'); ?>--</option>
						<?php foreach($alls as $all){ ?>
							<option value="<?php echo $all->currency_code_id; ?>" <?php if($all->currency_code_id == $v1){ ?>selected="selected"<?php } ?>><?php echo pn_strip_input($all->currency_code_title); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Receive','pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<select name="bestchange_v2" autocomplete="off">
						<option value="0">--<?php _e('Receive','pn'); ?>--</option>
						<?php foreach($alls as $all){ ?>
							<option value="<?php echo $all->currency_code_id; ?>" <?php if($all->currency_code_id == $v2){ ?>selected="selected"<?php } ?>><?php echo pn_strip_input($all->currency_code_title); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
		</div>
		
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Position','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="bestchange_pars_position" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'pars_position')); ?>" />
				</div>
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Step','pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="bestchange_step" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($broker, 'step')); ?>" />
				</div>
			</div>
		</div>		
		
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Min reserve for position','pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="bestchange_min_res" style="width: 100%;" value="<?php echo is_sum(is_isset($broker, 'min_res')); ?>" />
				</div>
			</div>
		</div>				
	
		<div class="add_tabs_line">
			<div class="add_tabs_label"><span></span></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Min rate','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="bestchange_min_sum" style="width: 100%;" value="<?php echo is_sum(is_isset($broker, 'min_sum')); ?>" />
				</div>
			</div>
		</div>		
		
		<?php do_action('tab_bestchange_min_sum', $data, $broker); ?>
		
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Max rate','pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="bestchange_max_sum" style="width: 100%;" value="<?php echo is_sum(is_isset($broker, 'max_sum')); ?>" />
				</div>
			</div>
		</div>		
		
		<?php do_action('tab_bestchange_max_sum', $data, $broker); ?>
	
		<div class="add_tabs_line">
			<div class="add_tabs_single long">
				<div class="add_tabs_sublabel"><span><?php _e('Reset to standard rate','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="bestchange_reset_course" autocomplete="off">
						<option value="0" <?php selected($reset_course,0); ?>><?php _e('No','pn'); ?></option>
						<option value="1" <?php selected($reset_course,1); ?>><?php _e('Yes','pn'); ?></option>
					</select>
				</div>
			</div>
		</div>	
	
		<div class="add_tabs_line">
			<div class="add_tabs_label"><span><?php _e('Standard rate','pn'); ?></span></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Send','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="bestchange_standart_course_give" style="width: 100%;" value="<?php echo is_sum(is_isset($broker, 'standart_course_give')); ?>" />
				</div>
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Receive','pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="bestchange_standart_course_get" style="width: 100%;" value="<?php echo is_sum(is_isset($broker, 'standart_course_get')); ?>" />
				</div>
			</div>
		</div>
		
		<?php do_action('tab_bestchange_standart_course', $data, $broker); ?>
	<?php   
} 

add_filter('pn_direction_addform_post', 'bestchange_pn_direction_addform_post');
function bestchange_pn_direction_addform_post($array){
	if(current_user_can('administrator') or current_user_can('pn_bestchange')){
		$array['bestchange_id'] = intval(is_param_post('bestchange_status'));
	}
	return $array;
}

add_action('item_direction_edit', 'item_direction_edit_bestchange', 10, 2);
add_action('item_direction_add', 'item_direction_edit_bestchange', 10, 2);
function item_direction_edit_bestchange($direction_id, $direction_array){
global $wpdb, $premiumbox;
	if(current_user_can('administrator') or current_user_can('pn_bestchange')){
		if($direction_id){
			$up = 0;
			$vid1 = intval(is_param_post('bestchange_v1'));
			$vid2 = intval(is_param_post('bestchange_v2'));
			if($vid1 > 0 and $vid2 > 0){
				$v1 = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."bestchange_currency_codes WHERE currency_code_id='$vid1'");
				$v2 = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."bestchange_currency_codes WHERE currency_code_id='$vid2'");
				if(isset($v1->id) and isset($v2->id)){
					
					$arr = array();
					$arr['direction_id'] = $direction_id;
					$arr['v1'] = intval($v1->currency_code_id);
					$arr['v2'] = intval($v2->currency_code_id);
					$arr['currency_id_give'] = $direction_array['currency_id_give'];
					$arr['currency_id_get'] = $direction_array['currency_id_get'];
					$arr['pars_position'] = pn_strip_input(is_param_post('bestchange_pars_position'));
					$arr['step'] = pn_parser_num(is_param_post('bestchange_step'));
					$arr['min_res'] = is_sum(is_param_post('bestchange_min_res'));
					$arr['min_sum'] = is_sum(is_param_post('bestchange_min_sum'));
					$arr['max_sum'] = is_sum(is_param_post('bestchange_max_sum'));
					$arr['standart_course_give'] = is_sum(is_param_post('bestchange_standart_course_give'));
					$arr['standart_course_get'] = is_sum(is_param_post('bestchange_standart_course_get'));
					$arr['reset_course'] = intval(is_param_post('bestchange_reset_course'));
					$arr['status'] = intval(is_param_post('bestchange_status'));
					$arr['black_ids'] = pn_strip_input(is_param_post('bestchange_black_ids'));
					$arr['white_ids'] = pn_strip_input(is_param_post('bestchange_white_ids'));
					
					$broker = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."bestchange_directions WHERE direction_id='$direction_id'"); 
					$arr = apply_filters('pn_bccorrs_tab_addform_post', $arr, $broker, $direction_id, $direction_array);
					
					if(isset($broker->id)){
						$wpdb->update($wpdb->prefix."bestchange_directions", $arr, array('id'=>$broker->id));
						do_action('item_bccorrs_tab_edit', $broker->id, $arr, $broker, $direction_id, $direction_array);
					} else {
						$wpdb->insert($wpdb->prefix."bestchange_directions", $arr);
						$broker_id = $wpdb->insert_id;	
						do_action('item_bccorrs_tab_add', $broker_id, $arr, $direction_id, $direction_array);
					}
				} else {
					$up = 1;			
				}
			} else {
				$up = 1;
			}
			if($up == 1){
				$wpdb->query("DELETE FROM ".$wpdb->prefix."bestchange_directions WHERE direction_id = '$direction_id'");
				unset_array_option($premiumbox, 'pn_bestchange_courses', $direction_id);				
			}
		}
	}
}

add_action('item_direction_delete', 'item_direction_delete_bestchange', 10, 2);
function item_direction_delete_bestchange($item_id, $item){
global $wpdb, $premiumbox;	
	$wpdb->query("DELETE FROM ".$wpdb->prefix."bestchange_directions WHERE direction_id = '$item_id'");
	unset_array_option($premiumbox, 'pn_bestchange_courses', $item_id);
}

add_filter('get_calc_data', 'get_calc_data_bestchange', 50, 2);
function get_calc_data_bestchange($cdata, $calc_data){
global $bestchange_courses, $premiumbox;
	if(!is_array($bestchange_courses)){
		$bestchange_courses = get_array_option($premiumbox, 'pn_bestchange_courses');
	}
	$direction = $calc_data['direction'];
	$vd1 = $calc_data['vd1'];
	$vd2 = $calc_data['vd2'];
	$set_course = intval(is_isset($calc_data,'set_course'));
	if($set_course != 1){
		if($direction->bestchange_id > 0){
			if(isset($bestchange_courses[$direction->id]) and isset($bestchange_courses[$direction->id]['give'], $bestchange_courses[$direction->id]['get'])){
				$course_give = is_sum($bestchange_courses[$direction->id]['give'], $vd1->currency_decimal);
				$course_get = is_sum($bestchange_courses[$direction->id]['get'], $vd2->currency_decimal);
				$cdata['course_give'] = $course_give;
				$cdata['course_get'] = $course_get;
			} else {
				$cdata['course_give'] = 0;
				$cdata['course_get'] = 0;
			}
		}	
	}
	return $cdata;
}

add_filter('is_course_direction', 'bestchange_is_course_direction', 50, 5); 
function bestchange_is_course_direction($arr, $direction, $vd1, $vd2, $place){
global $bestchange_courses, $premiumbox;	
	if(!is_array($bestchange_courses)){
		$bestchange_courses = get_array_option($premiumbox, 'pn_bestchange_courses');
	}
	if($direction->bestchange_id > 0){
		if(isset($bestchange_courses[$direction->id]) and isset($bestchange_courses[$direction->id]['give'], $bestchange_courses[$direction->id]['get'])){
			if(isset($vd1->currency_decimal)){
				$arr['give'] = is_sum($bestchange_courses[$direction->id]['give'], $vd1->currency_decimal);
			} else {
				$arr['give'] = is_sum($bestchange_courses[$direction->id]['give']);
			}
			if(isset($vd2->currency_decimal)){
				$arr['get'] = is_sum($bestchange_courses[$direction->id]['get'], $vd2->currency_decimal);
			} else {
				$arr['get'] = is_sum($bestchange_courses[$direction->id]['get']);
			}
				return $arr;
		} else {
			$arr = array(
				'give' => 0,
				'get' => 0,
			);
		}
	}	
	return $arr;
}