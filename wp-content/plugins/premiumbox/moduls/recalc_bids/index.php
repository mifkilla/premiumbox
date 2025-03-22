<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Recalculation of exchange amount[:en_US][ru_RU:]Пересчет суммы обмена[:ru_RU]
description: [en_US:]Recalculation of exchange amount[:en_US][ru_RU:]Пересчет суммы обмена[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/
$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_recalcbids');
add_action('all_bd_activated', 'bd_all_moduls_active_recalcbids');
function bd_all_moduls_active_recalcbids(){
global $wpdb;	

	$table_name = $wpdb->prefix ."recalculations"; 
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`direction_id` bigint(20) NOT NULL default '0',
		`change_course` int(1) NOT NULL default '0',
		`change_sum` int(1) NOT NULL default '0',
		`course_minute` varchar(20) NOT NULL default '0',
		`sum_minute` varchar(20) NOT NULL default '0',
		`course_status` longtext NOT NULL,
		`sum_status` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`direction_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'recalc_amount'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `recalc_amount` datetime NOT NULL");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'recalc_course'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `recalc_course` datetime NOT NULL");
    }	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'recalc_date'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `recalc_date` datetime NOT NULL");
    }	
}

add_filter('list_admin_notify','list_admin_notify_recalcbids');
function list_admin_notify_recalcbids($places_admin){
	$places_admin['admin_recalcbids'] = __('Order recalculation','pn');
	return $places_admin;
}

add_filter('list_user_notify','list_user_notify_recalcbids');
function list_user_notify_recalcbids($places_admin){
	$places_admin['user_recalcbids'] = __('Order recalculation','pn');
	return $places_admin;
}	

add_filter('list_notify_tags_admin_recalcbids','def_mailtemp_tags_bids');
add_filter('list_notify_tags_user_recalcbids','def_mailtemp_tags_bids');

add_action('item_direction_delete', 'item_direction_delete_recalcbids');
function item_direction_delete_recalcbids($item_id){
global $wpdb;
	$wpdb->query("DELETE FROM ".$wpdb->prefix."recalculations WHERE direction_id = '$item_id'");
}

add_filter('pn_caps','recalcbids_pn_caps');
function recalcbids_pn_caps($pn_caps){
	$pn_caps['pn_bids_recalc'] = __('Work with recalculation of order','pn');
	return $pn_caps;
}

add_filter('onebid_actions','onebid_actions_dop_recalcbids', 1000,3);
function onebid_actions_dop_recalcbids($onebid_actions, $item, $data_fs){
	if(current_user_can('administrator') or current_user_can('pn_bids_recalc')){
		$status = $item->status;
		$st = array('coldnew','new','cancel','techpay','payed','coldpay','realpay','verify','error','payouterror','scrpayerror');
		$st = apply_filters('status_for_recalculate_admin',$st);
		$st = (array)$st;
		if(in_array($status, $st)){
			$onebid_actions['recalculate_amount'] = array(
				'type' => 'link',
				'title' => __('Recalculate amount','pn'),
				'label' => __('Recalculate amount','pn'),
				'link' => pn_link('recalculate_bid') .'&item_id=[id]&recalc=0',
				'link_target' => '_blank',
				'link_class' => 'editting',
			);
			$onebid_actions['recalculate_course'] = array(
				'type' => 'link',
				'title' => __('Recalculate rate','pn'),
				'label' => __('Recalculate rate','pn'),
				'link' => pn_link('recalculate_bid') .'&item_id=[id]&recalc=1',
				'link_target' => '_blank',
				'link_class' => 'editting',
			);			
		}
	}
	return $onebid_actions;
}

add_action('premium_action_recalculate_bid','def_recalculate_one_bid');
function def_recalculate_one_bid(){
global $wpdb, $premiumbox;
	if(current_user_can('administrator') or current_user_can('pn_bids_recalc')){
		admin_pass_protected(__('Enter security code','pn'), __('Enter','pn')); 	
		
		$bid_id = intval(is_param_get('item_id'));
		$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE id='$bid_id'");
		if(isset($item->id)){
			$status = $item->status;
			$st = array('coldnew','new','cancel','techpay','payed','coldpay','realpay','verify','error','payouterror','scrpayerror');
			$st = apply_filters('status_for_recalculate_admin',$st);
			$st = (array)$st;
			if(in_array($status, $st)){
				$recalc = intval(is_param_get('recalc'));
				$ch_s = 4;
				$ch_c = 0;
				if($recalc == 1){
					$ch_s = 0;
					$ch_c = 4;
				}
				recalculation_bid($bid_id, $item, $ch_s, $ch_c, '');
				$hashdata = bid_hashdata($bid_id, '');
			}
		}	

		_e('Done','pn');
	}	
}		
	
add_filter('pn_exchange_settings_option', 'recalculate_exchange_settings_option');
function recalculate_exchange_settings_option($options){
global $premiumbox;
	$options['recalc'] = array(
		'view' => 'select',
		'title' => __('Order recalculating method','pn'),
		'options' => array('0'=>__('Upon order status change','pn'),'1'=>__('By cron','pn')),
		'default' => $premiumbox->get_option('exchange','recalc'),
		'name' => 'recalc',
	);		
	return $options;	
}

add_action('pn_exchange_settings_option_post', 'recalculate_exchange_settings_option_post');
function recalculate_exchange_settings_option_post(){
global $premiumbox;
	$recalc = intval(is_param_post('recalc'));
	$premiumbox->update_option('exchange', 'recalc', $recalc);
}
	
add_filter('list_tabs_direction','list_tabs_direction_recalcbids');
function list_tabs_direction_recalcbids($list_tabs){
	$list_tabs['recalcbids'] = __('Order recalculating','pn');
	return $list_tabs;
}	
	
add_action('tab_direction_recalcbids','direction_tab_recalcbids',10,2);
function direction_tab_recalcbids($data, $data_id){	
global $wpdb, $premiumbox;
 	$data_id = is_isset($data,'id');
	$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."recalculations WHERE direction_id='$data_id'");
	
	$change_course = intval(is_isset($item, 'change_course'));
	$change_sum = intval(is_isset($item, 'change_sum'));
	$course_minute = intval(is_isset($item, 'course_minute'));
	$sum_minute = intval(is_isset($item, 'sum_minute'));	
	
	$lists = apply_filters('bid_status_list', array());
	?>
		<div class="add_tabs_line">
			<div class="add_tabs_title"><?php _e('Recalculation according to amount of payment','pn'); ?></div>
			<div class="add_tabs_submit">
				<input type="submit" name="" class="button" value="<?php _e('Save'); ?>" />
			</div>
		</div>
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Order recalculation upon changing payment amount','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="recalc_change_sum" autocomplete="off"> 
						<option value="0" <?php selected($change_sum,0); ?>><?php _e('No','pn'); ?></option>
						<option value="4" <?php selected($change_sum,4); ?>><?php _e('Yes, always','pn'); ?></option>
						<option value="1" <?php selected($change_sum,1); ?>><?php _e('Yes, if payment amount changed','pn'); ?></option>					
						<option value="2" <?php selected($change_sum,2); ?>><?php _e('Yes, if payment amount increased','pn'); ?></option>	
						<option value="3" <?php selected($change_sum,3); ?>><?php _e('Yes, if payment amount decreased','pn'); ?></option>
					</select>
				</div>			
			</div>
			
		</div>	
		<div class="add_tabs_line">
			<div class="add_tabs_single long">
				<div class="add_tabs_sublabel"><span><?php _e('Order status for recalculation','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
			
					$def = @unserialize(is_isset($item,'sum_status'));
					if(!is_array($def)){ $def = array(); }
											
					foreach($lists as $key => $title){
						$checked = 0;
						if(in_array($key,$def)){
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => $title,
							'checked' => $checked,
							'value' => $key,
						);
					}
					echo get_check_list($scroll_lists, 'recalc_sum_status[]', '', '300');
					?>				
						<div class="premium_clear"></div>
				</div>
			</div>
		</div>			
		
		<div class="add_tabs_line">
			<div class="add_tabs_title"><?php _e('Recalculation according to exchange rate','pn'); ?></div>
			<div class="add_tabs_submit">
				<input type="submit" name="" class="button" value="<?php _e('Save'); ?>" />
			</div>
		</div>	
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Order recalculation upon changing exchange rate','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="recalc_change_course" autocomplete="off"> 
						<option value="0" <?php selected($change_course,0); ?>><?php _e('No','pn'); ?></option>
						<option value="4" <?php selected($change_course,4); ?>><?php _e('Yes, always','pn'); ?></option>
						<option value="1" <?php selected($change_course,1); ?>><?php _e('Yes, if rate changed','pn'); ?></option>					
						<option value="2" <?php selected($change_course,2); ?>><?php _e('Yes, if rate increased','pn'); ?></option>	
						<option value="3" <?php selected($change_course,3); ?>><?php _e('Yes, if rate decreased','pn'); ?></option>
					</select>
				</div>			
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Perform recalculation through','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="recalc_course_minute" value="<?php echo $course_minute; ?>" /> <?php _e('minuts', 'pn'); ?>
				</div>			
			</div>			
		</div>
		<div class="add_tabs_line">
			<div class="add_tabs_single long">
				<div class="add_tabs_sublabel"><span><?php _e('Order status for recalculation','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
			
					$def = @unserialize(is_isset($item,'course_status'));
					if(!is_array($def)){ $def = array(); }
											
					foreach($lists as $key => $title){
						$checked = 0;
						if(in_array($key,$def)){
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => $title,
							'checked' => $checked,
							'value' => $key,
						);
					}
					echo get_check_list($scroll_lists, 'recalc_course_status[]', '', '300');
					?>				
						<div class="premium_clear"></div>
				</div>
			</div>
		</div>			
	<?php 
}
 
add_action('item_direction_edit', 'item_direction_edit_recalcbids', 10, 2);
add_action('item_direction_add', 'item_direction_edit_recalcbids', 10, 2);
function item_direction_edit_recalcbids($data_id, $array){
global $wpdb;
	
	if($data_id){
		$change_course = intval(is_param_post('recalc_change_course'));	
		$change_sum = intval(is_param_post('recalc_change_sum'));
		
		$wpdb->query("DELETE FROM ".$wpdb->prefix."recalculations WHERE direction_id = '$data_id'");
		
		if($change_course > 0 or $change_sum > 0){
			$arr = array();
			$arr['change_course'] = $change_course;
			$arr['change_sum'] = $change_sum;
			$arr['direction_id'] = $data_id;
			$arr['sum_minute'] = intval(is_param_post('recalc_sum_minute'));
			$arr['course_minute'] = intval(is_param_post('recalc_course_minute'));
			
			$p_statused = is_param_post('recalc_course_status');
			$statused = array();
			if(is_array($p_statused)){
				foreach($p_statused as $st){ 
					$st = is_status_name($st);
					if($st){
						$statused[] = $st;
					}
				}
			}
			$arr['course_status'] = @serialize($statused);
			
			$p_statused = is_param_post('recalc_sum_status');
			$statused = array();
			if(is_array($p_statused)){
				foreach($p_statused as $st){
					$st = is_status_name($st);
					if($st){
						$statused[] = $st;
					}
				}
			}
			$arr['sum_status'] = @serialize($statused);			
			
			$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."recalculations WHERE direction_id='$data_id'"); 
			if(isset($item->id)){
				$wpdb->update($wpdb->prefix."recalculations", $arr, array('id'=>$item->id));
			} else {
				$wpdb->insert($wpdb->prefix."recalculations", $arr);
			}
		} 
	}
}

add_action('item_direction_copy', 'item_direction_copy_recalcbids', 10, 2);
function item_direction_copy_recalcbids($last_id, $data_id){
global $wpdb;	
	$data = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."recalculations WHERE direction_id='$last_id'");
	foreach($data as $item){
		$arr = array();
		foreach($item as $k => $v){
			$arr[$k] = $v;
		}
		if(isset($arr['id'])){
			unset($arr['id']);
		}
		$arr['direction_id'] = $data_id;
		$wpdb->insert($wpdb->prefix . 'recalculations', $arr);
	}	
}

add_filter('onebid_col1', 'onebid_col1_recalcbids', 10, 4);
function onebid_col1_recalcbids($actions, $item, $data_fs, $v){
	
	$new_actions = array();
	$new_actions['recalc_amount'] = array(
		'type' => 'text',
		'title' => __('Recalculating amount date','pn'),
		'label' => '<span class="onebid_item item_recalc_amount clpb_item bred" data-clipboard-text="' . get_pn_time($item->recalc_amount,'d.m.Y H:i:s') . '">' . get_pn_time($item->recalc_amount,'d.m.Y H:i:s') . '</span>',
	);
	$new_actions['recalc_course'] = array(
		'type' => 'text',
		'title' => __('Recalculating rate date','pn'),
		'label' => '<span class="onebid_item item_recalc_course clpb_item bred" data-clipboard-text="' . get_pn_time($item->recalc_course,'d.m.Y H:i:s') . '">' . get_pn_time($item->recalc_course,'d.m.Y H:i:s') . '</span>',
	);	
	$actions = pn_array_insert($actions, 'editdate', $new_actions, 'after');
	
	return $actions;
}

add_filter('direction_instruction_tags', 'recalcbids_directions_tags', 20, 2); 
function recalcbids_directions_tags($tags, $key){
	$in_page = array('description_txt','timeline_txt','window_txt');
	if(!in_array($key, $in_page)){
		
		$tags['recalc_course'] = array(
			'title' => __('Recalculation rate time','pn'),
			'start' => '[recalc_course]',
		);
		$tags['recalc_amount'] = array(
			'title' => __('Recalculation amount time','pn'),
			'start' => '[recalc_amount]',
		);		
		
	}
	return $tags;
}			

add_filter('direction_instruction','recalcbids_direction_instruction', 20, 5);
function recalcbids_direction_instruction($instruction, $txt_name, $direction, $vd1, $vd2){
global $wpdb, $premiumbox, $bids_data;

	if(isset($bids_data->id)){
		$recalc_data = '';
		if(strstr($instruction,'[bid_recalc]') or strstr($instruction,'[recalc_course]')){
			$bid_recalc = __('undefined','pn');

			$direction_id = $bids_data->direction_id;
			if(!isset($recalc_data->id)){
				$recalc_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."recalculations WHERE direction_id='$direction_id'");
			}
			if(isset($recalc_data->id)){
				$minute = intval(is_isset($recalc_data,'course_minute'));
				if($minute > 0){
					$cou_hour = floor($minute / 60);
					$cou_minute = $minute - ($cou_hour * 60);
					if($cou_hour > 0 and $cou_minute > 0){
						$bid_recalc = sprintf(__('Order will be recalculated through %1s hour(s), %2s minute(s) after creating','pn'), $cou_hour, $cou_minute);
					} elseif($cou_hour > 0){
						$bid_recalc = sprintf(__('Order will be recalculate through %s hour(s) after creating','pn'), $cou_hour);
					} elseif($cou_minute > 0){
						$bid_recalc = sprintf(__('Order will be recalculate through %s minute(s) after creating','pn'), $cou_minute);		
					}
				} else {
					$bid_recalc = __('Exchange amount will be recalculated upon order status change','pn');
				}	
			}

			$instruction = str_replace(array('[bid_recalc]','[recalc_course]'), $bid_recalc, $instruction); 
		}	
		if(strstr($instruction,'[recalc_amount]')){
			$bid_recalc = __('undefined','pn');
			$direction_id = $bids_data->direction_id;
			if(!isset($recalc_data->id)){
				$recalc_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."recalculations WHERE direction_id='$direction_id'");
			}
			if(isset($recalc_data->id)){
				$minute = intval(is_isset($recalc_data,'sum_minute'));
				if($minute > 0){
					$cou_hour = floor($minute / 60);
					$cou_minute = $minute - ($cou_hour * 60);
					if($cou_hour > 0 and $cou_minute > 0){
						$bid_recalc = sprintf(__('Order will be recalculate through %1s hour(s), %2s minute(s) after creating','pn'), $cou_hour, $cou_minute);
					} elseif($cou_hour > 0){
						$bid_recalc = sprintf(__('Order will be recalculate through %s hour(s) after creating','pn'), $cou_hour);
					} elseif($cou_minute > 0){
						$bid_recalc = sprintf(__('Order will be recalculate through %s minute(s) after creating','pn'), $cou_minute);		
					}
				} else {
					$bid_recalc = __('Exchange amount will be recalculated upon order status change','pn');
				}
			}
			
			$instruction = str_replace('[recalc_amount]', $bid_recalc, $instruction);
		}	
	}
	
	return $instruction;
}
 
add_filter('change_bidstatus', 'recalculation_change_bidstatus', 60, 6);    
function recalculation_change_bidstatus($item, $set_status, $place, $user_or_system, $old_status, $direction=''){
global $wpdb, $premiumbox;
	$item_id = $item->id;
	
	if(isset($item->direction_id)){
		$recalc = intval($premiumbox->get_option('exchange','recalc'));
		if($recalc != 1){
			$direction_id = $item->direction_id;
			$recalc_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."recalculations WHERE direction_id='$direction_id'");
			if(isset($recalc_data->id)){
				
				$time = current_time('timestamp');

				$create_date = $item->create_date;
				$create_time = strtotime($create_date);
				$item_status = $item->status;
				
				$ch_s = 0;
				$minute = intval($recalc_data->sum_minute);
				$change_time = $create_time + ($minute * 60);
				$in_status = @unserialize($recalc_data->sum_status);
				if(!is_array($in_status)){ $in_status = array(); }
				if($time >= $change_time and in_array($item_status, $in_status)){
					$ch_s = intval($recalc_data->change_sum);
				}	
				
				$ch_c = 0;
				$minute = intval($recalc_data->course_minute);
				$change_time = $create_time + ($minute * 60);
				$in_status = @unserialize($recalc_data->course_status);
				if(!is_array($in_status)){ $in_status = array(); }
				if($time >= $change_time and in_array($item_status, $in_status)){
					$ch_c = intval($recalc_data->change_course);
				}		
				
				if($ch_s > 0 or $ch_c > 0){	
					$item = recalculation_bid($item->id, $item, $ch_s, $ch_c, $direction);
					$hashdata = bid_hashdata($item->id, $item);
					$n_arr = array();
					$n_arr['hashdata'] = @serialize($hashdata);
					$item = pn_object_replace($item, $n_arr);
				}	
			}
		}
	}
	
	return $item;
}	

function recalculation_bids(){
global $wpdb, $premiumbox;

	if(!$premiumbox->is_up_mode()){
		$recalc = intval($premiumbox->get_option('exchange','recalc'));
		if($recalc == 1){
			$time = current_time('timestamp');
			$date = current_time('mysql');
			
			$v = get_currency_data();
			
			$recalcs = $wpdb->get_results("
			SELECT * FROM ".$wpdb->prefix."recalculations 
			LEFT OUTER JOIN ".$wpdb->prefix."directions 
			ON(".$wpdb->prefix."recalculations.direction_id = ".$wpdb->prefix."directions.id) 
			WHERE ".$wpdb->prefix."directions.auto_status = '1' AND ".$wpdb->prefix."directions.direction_status = '1'");

			$time_left_60sec = $time - 60;
			$recalc_fool_protection = apply_filters('recalc_fool_protection', 1);
			if($recalc_fool_protection != 1){
				$time_left_60sec = $time;
			}
			$last_date = date('Y-m-d H:i:s', $time_left_60sec);

			foreach($recalcs as $rec){
				$direction_id = $rec->direction_id;
				$statused = array();
				$course_status = @unserialize($rec->course_status);
				if(!is_array($course_status)){ $course_status = array(); }
				$statused = array_merge($course_status, $statused);
				
				$statused = array_unique($statused);
				
				$in_status = create_data_for_bd($statused, 'status');
				if($in_status){
					$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE status IN($in_status) AND direction_id='$direction_id' AND recalc_date < '$last_date'"); 
					foreach($items as $item){
						$create_date = $item->create_date;
						$create_time = strtotime($create_date);
						$item_status = $item->status;

						$course_minute = intval($rec->course_minute);
						$ch_c = 0;
						$change_time = $create_time + ($course_minute * 60);
						if($time >= $change_time and in_array($item_status, $course_status)){
							$ch_c = intval($rec->change_course);
						}						

						$item = recalculation_bid($item->id, $item, 0, $ch_c, $rec);
						$hashdata = bid_hashdata($item->id, $item);
					}
				}	
			}
		}
	}
}  

add_filter('list_cron_func', 'recalculation_bids_list_cron_func');
function recalculation_bids_list_cron_func($filters){
	$filters['recalculation_bids'] = array(
		'title' => __('Recalculation of exchange amount','pn'),
		'file' => 'now',
	);
	return $filters;
}

function recalculation_bid($bid_id, $item='', $change_sum='', $change_course='', $direction=''){
global $wpdb, $premiumbox;

	$change_course = intval($change_course);
	$change_sum = intval($change_sum);
	$date = current_time('mysql');
	if(!isset($item->id)){
		$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE id='$bid_id'");
	}
	if(isset($item->id)){
		$direction_id = $item->direction_id; 
		$v = get_currency_data();
		
		if(!isset($direction->id)){
			$direction = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."directions WHERE auto_status='1' AND id='$direction_id'");
		}		
		
		if(isset($direction->id)){
			if(isset($v[$direction->currency_id_give]) and isset($v[$direction->currency_id_get])){
				
				$arr = array();
				$arr['recalc_date'] = $date;		

				$sum = is_sum($item->sum1dc);
				$sum_pay = is_sum($item->pay_sum);

				$calc_data = array(
					'vd1' => $v[$direction->currency_id_give],
					'vd2' => $v[$direction->currency_id_get],
					'direction' => $direction,
					'user_id' => $item->user_id, 
					'post_sum' => $sum,
					'dej' => 5,
				);
				
				$active_s = 0;
				if($change_sum > 0 and $sum_pay > 0){
					$ch_sum = 0;
					if($sum_pay > $sum){
						$ch_sum = 1;
					} elseif($sum > $sum_pay){
						$ch_sum = 2;
					}					
					
					if($change_sum == 4){
						$active_s = 1;	
					}
					if($change_sum == 1 and $ch_sum > 0){
						$active_s = 1;
					}
					if($change_sum == 2 and $ch_sum == 1){
						$active_s = 1;
					}
					if($change_sum == 3 and $ch_sum == 2){
						$active_s = 1;
					}	

					if($active_s == 1){
						$calc_data['post_sum'] = $sum_pay;
					}
				}
				
				$active_c = 0;
				if($change_course > 0){
				
					$dir_c = is_course_direction($direction, $v[$direction->currency_id_give], $v[$direction->currency_id_get], 'table1');
					$course_give = is_sum(is_isset($dir_c,'give')); 
					$course_get = is_sum(is_isset($dir_c,'get'));
						
					$ch_course = is_course_change($item->course_give, $item->course_get, $course_give, $course_get);
						
					$active_c = 0;
					if($change_course == 4){
						$active_c = 1;	
					}
					if($change_course == 1 and $ch_course > 0){
						$active_c = 1;
					}
					if($change_course == 2 and $ch_course == 1){
						$active_c = 1;
					}
					if($change_course == 3 and $ch_course == 2){
						$active_c = 1;
					}	

				}
				
				if($active_c != 1){
					$calc_data['set_course'] = 1; 
					$calc_data['c_give'] = $item->course_give; 
					$calc_data['c_get'] = $item->course_get; 
				}
					
				if($active_s == 1 or $active_c == 1){
					
					$calc_data = apply_filters('get_calc_data_params', $calc_data, 'recalc', $item); 
					$cdata = get_calc_data($calc_data);					
					
					if($active_s == 1){
						$arr['exceed_pay'] = 0;
						$arr['recalc_amount'] = $date;
					}
					if($active_c == 1){
						$arr['recalc_course'] = $date;
					}					
					$arr['course_give'] = $cdata['course_give'];
					$arr['course_get'] = $cdata['course_get'];
					$arr['user_id'] = $item->user_id;
					$arr['user_discount'] = $cdata['user_discount'];
					$arr['user_discount_sum'] = $cdata['user_discount_sum'];
					$arr['exsum'] = $cdata['exsum'];
					$arr['sum1'] = $cdata['sum1'];
					$arr['dop_com1'] = $cdata['dop_com1'];
					$arr['sum1dc'] = $cdata['sum1dc'];
					$arr['com_ps1'] = $cdata['com_ps1'];
					$arr['sum1c'] = $cdata['sum1c'];
					$arr['sum1r'] = $cdata['sum1r'];
					$arr['sum2t'] = $cdata['sum2t'];
					$arr['sum2'] = $cdata['sum2'];
					$arr['dop_com2'] = $cdata['dop_com2'];
					$arr['com_ps2'] = $cdata['com_ps2'];
					$arr['sum2dc'] = $cdata['sum2dc'];
					$arr['sum2c'] = $cdata['sum2c'];
					$arr['sum2r'] = $cdata['sum2r'];	
					$arr['profit'] = $cdata['profit'];
					$arr = apply_filters('array_data_recalculate_bids', $arr, $direction, $v[$direction->currency_id_give], $v[$direction->currency_id_get], $cdata, $item);
					$wpdb->update($wpdb->prefix ."exchange_bids", $arr, array('id'=>$item->id)); 

					$old_item = $item;
					$item = pn_object_replace($item, $arr);  
					
					goed_mail_to_changestatus_bids($item->id, $item, 'admin_recalcbids', 'user_recalcbids');
					
					do_action('recalculation_bid', $item->id, $item, $old_item);
				}
			}			
		}
	}
	return $item;
}