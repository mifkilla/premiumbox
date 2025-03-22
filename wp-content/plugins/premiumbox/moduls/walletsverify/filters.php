<?php 
if( !defined( 'ABSPATH')){ exit(); } 

if(!function_exists('clear_visible_usac')){
	function clear_visible_usac(){
		$plugin = get_plugin_class();
		if(!$plugin->is_up_mode()){
			$path = $plugin->upload_dir .'usacshow/';
			full_del_dir($path);
		}
	}
}

if(!function_exists('usac_list_cron_func')){
	add_filter('list_cron_func', 'usac_list_cron_func');
	function usac_list_cron_func($filters){
		$filters['clear_visible_usac'] = array(
			'title' => __('Delete temporary account verification files','pn'),
			'site' => '1hour',
		);		
		return $filters;
	}
}

add_filter("pntable_trclass_pn_userwallets", 'uv_wallets_pntable_trclass_pn_userwallets', 10, 2);
function uv_wallets_pntable_trclass_pn_userwallets($tr_class, $item){
	if(is_isset($item, 'verify') == 1){
		$tr_class[] = 'tr_green';
	}
		
	return $tr_class;
}	

add_filter("pntable_bulkactions_pn_userwallets", 'uv_wallets_pntable_bulkactions_pn_userwallets');
function uv_wallets_pntable_bulkactions_pn_userwallets($actions){
	$new_actions = array(
		'verify'    => __('Verified','pn'),
		'unverify'    => __('Unverified','pn'),	
	);
	$actions = pn_array_insert($actions, 'basket', $new_actions, 'before');
	return $actions;
}

add_filter("pntable_columns_pn_userwallets", 'uv_wallets_pntable_columns_pn_userwallets', 100);
function uv_wallets_pntable_columns_pn_userwallets($columns){
	$columns['status'] = __('Status','pn');
	return $columns;
}

add_filter("pntable_column_pn_userwallets", 'uv_wallets_pntable_column_pn_userwallets', 10, 3);
function uv_wallets_pntable_column_pn_userwallets($return, $column_name,$item){
	if($column_name == 'status'){
		if($item->verify == 1){
			$status ='<span class="bgreen">'. __('Verified account nubmer','pn') .'</span>';
		} else {
			$status ='<span class="bred">'. __('Unverified account nubmer','pn') .'</span>';
		} 	
		return $status;	
	}
	return $return;
}

add_filter("pntable_submenu_pn_userwallets", 'pntable_submenu_pn_userwallets_uv_wallets', 10, 3);
function pntable_submenu_pn_userwallets_uv_wallets($options){
	$options['filter2'] = array(
		'options' => array(
			'1' => __('verified account number','pn'),
			'2' => __('unverified account number','pn'),
		),
		'title' => '',
	);
	return $options;
}

add_filter("pntable_searchwhere_pn_userwallets", 'pntable_searchwhere_pn_userwallets_uv_wallets');
function pntable_searchwhere_pn_userwallets_uv_wallets($where){
	$filter2 = intval(is_param_get('filter2'));
	if($filter2 == 1){
		$where .= " AND verify = '1'";
	} elseif($filter2 == 2){
		$where .= " AND verify = '0'";
	}
	return $where;
}

add_action('pntable_userwallets_action', 'pntable_userwallets_action_uv_wallets', 10, 2);
function pntable_userwallets_action_uv_wallets($action, $post_ids){
global $wpdb;
	if($action == 'verify'){		
		foreach($post_ids as $id){
			$id = intval($id);
			$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."user_wallets WHERE id='$id' AND verify != '1'");
			if(isset($item->id)){
				$result = $wpdb->query("UPDATE ".$wpdb->prefix."user_wallets SET verify = '1' WHERE id = '$id'");
				do_action('item_userwallets_verify', $id, $item, $result);
			}
		}									
	}
	if($action == 'unverify'){		
		foreach($post_ids as $id){
			$id = intval($id);
			$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."user_wallets WHERE id='$id' AND verify != '0'");
			if(isset($item->id)){				
				$result = $wpdb->query("UPDATE ".$wpdb->prefix."user_wallets SET verify = '0' WHERE id = '$id'");
				do_action('item_userwallets_unverify', $id, $item, $result);
			}
		}								
	}	
}

add_action('item_userwallets_edit', 'uv_wallets_item_userwallets_edit', 10, 3);
function uv_wallets_item_userwallets_edit($id, $array, $last_data){ 
	$array['id'] = $id;
	$item = (object)$array;
	if(is_isset($last_data,'verify') == 0 and $item->verify == 1){
		do_action('item_userwallets_verify', $id, $item);
	}
	if(is_isset($last_data,'verify') == 1 and $item->verify == 0){
		do_action('item_userwallets_unverify', $id, $item);		
	}	
}

add_filter('pn_userwallets_addform', 'uv_wallets_pn_userwallets_addform', 10, 2);
function uv_wallets_pn_userwallets_addform($options, $data){	
	$options['verify_line'] = array(
		'view' => 'line',
	);	
	$options['verify'] = array(
		'view' => 'select',
		'title' => __('Status','pn'),
		'options' => array('0'=>__('Unverified account nubmer','pn'), '1'=>__('Verified account nubmer','pn')),
		'default' => is_isset($data, 'verify'),
		'name' => 'verify',
	);		
	
	return $options;
}

add_filter('pn_userwallets_addform_post', 'uv_wallets_pn_userwallets_addform_post');
function uv_wallets_pn_userwallets_addform_post($array){
	$array['verify'] = intval(is_param_post('verify'));	
	return $array;
}

if(!function_exists('list_tabs_currency_verify')){
	add_filter('list_tabs_currency', 'list_tabs_currency_verify');
	function list_tabs_currency_verify($list_tabs){
		$list_tabs['verify'] = __('Verification','pn');
		return $list_tabs;
	}
}

add_action('tab_currency_verify', 'fieldhelps_tab_currency_verify', 10, 2);
function fieldhelps_tab_currency_verify($data, $data_id){
	$form = new PremiumForm();
	
	$has_verify = intval(get_currency_meta(is_isset($data, 'id'), 'has_verify'));
	$verify_files = intval(get_currency_meta(is_isset($data, 'id'), 'verify_files'));
	$help_verify = pn_strip_input(get_currency_meta(is_isset($data, 'id'), 'help_verify'));
?>	

	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Ability for account verification','pn'); ?></span></div>
			<?php $form->select('has_verify', array('0'=>__('No','pn'), '1'=>__('Yes','pn')), $has_verify);  ?>
		</div>
		<div class="add_tabs_single">
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Nubmer of images for upload','pn'); ?></span></div>
			<?php 
			$form->input('verify_files', $verify_files, array()); 
			?>			
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Instruction for account verification','pn'); ?></span></div>
			<?php 
			$form->textarea('help_verify', $help_verify, 8, array(), 1, 0); 
			?>	
		</div>
	</div>	
<?php		
}	 

add_action('item_currency_edit','item_currency_edit_uv_wallets');
add_action('item_currency_add','item_currency_edit_uv_wallets');
function item_currency_edit_uv_wallets($data_id){
	if($data_id){
		$has_verify = intval(is_param_post('has_verify'));
		update_currency_meta($data_id, 'has_verify', $has_verify);

		$verify_files = intval(is_param_post('verify_files'));
		update_currency_meta($data_id, 'verify_files', $verify_files);		
		
		$help_verify = pn_strip_input(is_param_post_ml('help_verify'));
		update_currency_meta($data_id, 'help_verify', $help_verify);
	}
}

add_filter('list_icon_indicators', 'uv_wallets_icon_indicators');
function uv_wallets_icon_indicators($lists){
	$plugin = get_plugin_class();
	$lists['uv_wallets'] = array(
		'title' => __('Account verification requests','pn'),
		'img' => $plugin->plugin_url .'images/verify.png',
		'link' => admin_url('admin.php?page=pn_userwallets_verify&filter=1')
	);
	return $lists;
}

add_filter('count_icon_indicator_uv_wallets', 'def_icon_indicator_uv_wallets');
function def_icon_indicator_uv_wallets($count){
	global $wpdb;
	if(current_user_can('administrator') or current_user_can('pn_userwallets')){
		$count = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."uv_wallets WHERE status = '0'");
	}	
	return $count;
}

add_filter('item_userwallets_delete_before', 'uv_wallets_item_userwallets_delete_before', 10, 3);
function uv_wallets_item_userwallets_delete_before($arr, $id, $item){
global $premiumbox;

	if($arr['ind'] == 1){
		if($premiumbox->get_option('usve','disabledelete') == 1 and $item->verify == 1 and !is_admin()){
			$arr['ind'] = 0;
			$arr['error'] = __('forbidden to delete','pn');
		}
	}
		return $arr;
}

add_filter('item_userwallets_add_before', 'uv_wallets_item_userwallets_add_before', 10, 2);
function uv_wallets_item_userwallets_add_before($arr, $array){
global $premiumbox, $wpdb;	
	
	if($arr['ind'] == 1){
		$account = $array['accountnum'];
		$currency_id = $array['currency_id'];
		if($premiumbox->get_option('usve','uniq') == 1 and !is_admin()){
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."user_wallets WHERE currency_id='$currency_id' AND accountnum='$account'");
			if($cc > 0){	
				$arr['ind'] = 0;
				$arr['error'] = __('forbidden to add','pn');
			}
		}
	}
	
	return $arr;
}

if(!function_exists('list_tabs_direction_verify')){
	add_filter('list_tabs_direction', 'list_tabs_direction_verify');
	function list_tabs_direction_verify($list_tabs){
		$list_tabs['verify'] = __('Verification','pn');
		return $list_tabs;
	}
}

add_action('tab_direction_verify','tab_direction_verify_uv_wallets',20,2);
function tab_direction_verify_uv_wallets($data, $data_id){
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Account verification Send','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Verified accounts only','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$verify = get_direction_meta($data_id, 'verify_acc1');
				?>									
				<select name="verify_acc1" autocomplete="off"> 
					<option value="0" <?php selected($verify,0); ?>><?php _e('No','pn'); ?></option>
					<option value="1" <?php selected($verify,1); ?>><?php _e('Yes','pn'); ?></option>
					<option value="2" <?php selected($verify,2); ?>><?php _e('If exchange amount is more than','pn'); ?></option>
				</select>		
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange amount for Send','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$verify_sum = get_direction_meta($data_id, 'verify_sum_acc1');
				?>									
				<input type="text" name="verify_sum_acc1" style="width: 100%;" value="<?php echo is_sum($verify_sum); ?>" />				
			</div>		
		</div>
	</div>

	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Account verification Receive','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Verified accounts only','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$verify = get_direction_meta($data_id, 'verify_acc2');
				?>									
				<select name="verify_acc2" autocomplete="off"> 
					<option value="0" <?php selected($verify,0); ?>><?php _e('No','pn'); ?></option>
					<option value="1" <?php selected($verify,1); ?>><?php _e('Yes','pn'); ?></option>
					<option value="2" <?php selected($verify,2); ?>><?php _e('If exchange amount is more than','pn'); ?></option>
				</select>		
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange amount for Receive','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$verify_sum = get_direction_meta($data_id, 'verify_sum_acc2');
				?>									
				<input type="text" name="verify_sum_acc2" style="width: 100%;" value="<?php echo is_sum($verify_sum); ?>" />				
			</div>		
		</div>
	</div>	
<?php
}  

add_action('item_direction_edit','item_direction_edit_uv_wallets');
add_action('item_direction_add','item_direction_edit_uv_wallets');
function item_direction_edit_uv_wallets($data_id){
	update_direction_meta($data_id, 'verify_acc1', intval(is_param_post('verify_acc1')));	
	update_direction_meta($data_id, 'verify_acc2', intval(is_param_post('verify_acc2')));
	update_direction_meta($data_id, 'verify_sum_acc1', is_sum(is_param_post('verify_sum_acc1')));
	update_direction_meta($data_id, 'verify_sum_acc2', is_sum(is_param_post('verify_sum_acc2')));
} 

add_filter('direction_instruction_tags', 'userwalletsverify_directions_tags', 10, 2); 
function userwalletsverify_directions_tags($tags, $key){
	
	$tags['create_acc_give'] = array(
		'title' => __('Link to verification of Send account','pn'),
		'start' => '[create_acc_give]',
	);
	$tags['create_acc_get'] = array(
		'title' => __('Link to verification of Receive account','pn'),
		'start' => '[create_acc_get]',
	);		
	
	return $tags;
}

add_filter('direction_instruction','userwalletsverify_direction_instruction', 10, 5);
function userwalletsverify_direction_instruction($instruction, $txt_name, $direction, $vd1, $vd2){
global $bids_data, $direction_data;	
	if(isset($bids_data->id)){
		$instruction = str_replace('[create_acc_give]', link_createandverify_wallet($bids_data->account_give, $bids_data->currency_id_give), $instruction);
		$instruction = str_replace('[create_acc_get]', link_createandverify_wallet($bids_data->account_get, $bids_data->currency_id_get), $instruction);
	}
	return $instruction;
}

function link_createandverify_wallet($account, $currency_id){
	return '<a href="'. get_pn_action('create_userwallet', 'get') .'&acc='. $account .'&currency_id='. $currency_id .'&return_place=verify" target="_blank">'. __('Account verification link','pn') .'</a>';
}

add_filter('change_bidstatus', 'userwalletsverify_change_bidstatus', 100, 5);  
function userwalletsverify_change_bidstatus($item, $set_status, $place, $user_or_system, $old_status){
global $wpdb, $premiumbox;
	$item_id = $item->id;

	if($item->status == 'new'){
		$show_error = intval($premiumbox->get_option('usve','create_notacc'));
		if($place == 'exchange_button' and $show_error == 1){
			$direction_id = $item->direction_id;
			$cold = 0;
			if($item->accv_give != 1){
				$verify = intval(get_direction_meta($direction_id, 'verify_acc1'));
				$sum = is_sum($item->sum1);
				$verify_sum = is_sum(get_direction_meta($direction_id, 'verify_sum_acc1'));
				if($verify == 1 or $verify == 2 and $sum >= $verify_sum){
					$cold = 1;	
				}
			}
			if($item->accv_get != 1){
				$verify = intval(get_direction_meta($direction_id, 'verify_acc2'));
				$sum = is_sum($item->sum2c);
				$verify_sum = is_sum(get_direction_meta($direction_id, 'verify_sum_acc2'));
				if($verify == 1 or $verify == 2 and $sum >= $verify_sum){
					$cold = 1;	
				}
			}
			if($cold == 1){
				$array = array();
				$array['edit_date'] = current_time('mysql');
				$array['status'] = 'coldnew';
				$wpdb->update($wpdb->prefix.'exchange_bids', $array, array('id'=>$item->id));
					
				$old_status = $item->status;
				$item = pn_object_replace($item, $array);
				$new_item = apply_filters('change_bidstatus', $item, 'coldnew', 'walletsverify_module', 'user', $old_status);		
			}			
		}
	}
	return $item;
}

add_filter('coldnew_to_new', 'userwalletsverify_coldnew_to_new', 10,2);
function userwalletsverify_coldnew_to_new($ind, $item){
global $premiumbox, $wpdb;
	if($ind == 1){
		$direction_id = $item->direction_id;
		$cold = 0;
		if($item->accv_give != 1){
			$verify = intval(get_direction_meta($direction_id, 'verify_acc1'));
			$sum = is_sum($item->sum1);
			$verify_sum = is_sum(get_direction_meta($direction_id, 'verify_sum_acc1'));
			if($verify == 1 or $verify == 2 and $sum >= $verify_sum){
				$cold = 1;	
			}
		}
		if($item->accv_get != 1){
			$verify = intval(get_direction_meta($direction_id, 'verify_acc2'));
			$sum = is_sum($item->sum2c);
			$verify_sum = is_sum(get_direction_meta($direction_id, 'verify_sum_acc2'));
			if($verify == 1 or $verify == 2 and $sum >= $verify_sum){
				$cold = 1;	
			}
		}
		if($cold == 1){
			return 0;	
		}			
	}
	return $ind;
}

add_action('item_userwallets_verify', 'coldnew_item_userwallets_verify', 10, 2);
function coldnew_item_userwallets_verify($uw_id, $uw=''){
global $premiumbox, $wpdb;

	if(!isset($uw->id)){
		$uw = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."user_wallets WHERE id = '$uw_id'");
	}

	$user_id = intval(is_isset($uw, 'user_id'));
	$currency_id = intval(is_isset($uw, 'currency_id'));
	$account = trim(is_isset($uw, 'accountnum'));
	$wpdb->query("UPDATE ".$wpdb->prefix."exchange_bids SET accv_give = '1' WHERE user_id = '$user_id' AND currency_id_give='$currency_id' AND account_give='$account' AND status IN('new','coldnew','techpay')");
	$wpdb->query("UPDATE ".$wpdb->prefix."exchange_bids SET accv_get = '1' WHERE user_id = '$user_id' AND currency_id_get='$currency_id' AND account_get='$account' AND status IN('new','coldnew','techpay')");
	
	$show_error = intval($premiumbox->get_option('usve','create_notacc'));
	if($show_error == 1){
		coldnew_to_new();
	}
}

add_action('item_userwallets_unverify', 'coldnew_item_userwallets_unverify', 10, 2);
function coldnew_item_userwallets_unverify($uw_id, $uw=''){
global $premiumbox, $wpdb;

	if(!isset($uw->id)){
		$uw = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."user_wallets WHERE id = '$uw_id'");
	}

	$user_id = intval(is_isset($uw, 'user_id'));
	$currency_id = intval(is_isset($uw, 'currency_id'));
	$account = trim(is_isset($uw, 'accountnum'));
	$wpdb->query("UPDATE ".$wpdb->prefix."exchange_bids SET accv_give = '0' WHERE user_id = '$user_id' AND currency_id_give='$currency_id' AND account_give='$account' AND status IN('new','coldnew','techpay')");
	$wpdb->query("UPDATE ".$wpdb->prefix."exchange_bids SET accv_get = '0' WHERE user_id = '$user_id' AND currency_id_get='$currency_id' AND account_get='$account' AND status IN('new','coldnew','techpay')");

}

if(!function_exists('coldnew_to_new')){
	function coldnew_to_new(){
		global $wpdb;
		$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE status = 'coldnew'");
		foreach($items as $item){
			$ind = apply_filters('coldnew_to_new', 1, $item);
			if($ind == 1){
				$array = array();
				$array['edit_date'] = current_time('mysql');
				$array['status'] = 'new';
				$array = apply_filters('array_data_bids_new', $array, $item);
				$wpdb->update($wpdb->prefix.'exchange_bids', $array, array('id'=>$item->id));
			}
		}		
	}
}

add_filter('array_data_create_bids', 'uv_wallets_array_data_create_bids', 99, 5);
function uv_wallets_array_data_create_bids($array, $direction, $vd1, $vd2, $cdata){
global $wpdb;
	
	if(isset($direction->id)){
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		if($user_id){
			
			$verify_account = intval(is_isset($direction,'verify_acc1'));
			$sum = $cdata['sum1'];
			$sum_min = is_sum(is_isset($direction,'verify_sum_acc1'));
			if($verify_account == 1 or $verify_account == 2 and $sum >= $sum_min){
				$account = pn_strip_input(is_isset($array, 'account_give'));
				$currency_id = $vd1->id;
				$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."user_wallets WHERE user_id = '$user_id' AND verify='1' AND accountnum='$account' AND currency_id='$currency_id'");
				$cc = intval($cc); if($cc > 1){ $cc = 1; }
				$array['accv_give'] = $cc;
			}
			
			$verify_account = intval(is_isset($direction,'verify_acc2'));
			$sum = $cdata['sum2c'];
			$sum_min = is_sum(is_isset($direction,'verify_sum_acc2'));
			if($verify_account == 1 or $verify_account == 2 and $sum >= $sum_min){			
				$account = pn_strip_input(is_isset($array, 'account_get'));
				$currency_id = $vd2->id;
				$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."user_wallets WHERE user_id = '$user_id' AND verify='1' AND accountnum='$account' AND currency_id='$currency_id'");
				$cc = intval($cc); if($cc > 1){ $cc = 1; }
				$array['accv_get'] = $cc;				
			}
			
		}	
	}	
	
	return $array;
}

add_filter('error_bids', 'uv_wallets_error_bids', 99, 9);
function uv_wallets_error_bids($error_bids, $account1, $account2, $direction, $vd1, $vd2, $auto_data, $unmetas, $cdata){
global $wpdb, $premiumbox;
	
	$show_error = intval($premiumbox->get_option('usve','create_notacc'));
	if($show_error != 1){
		if(count($error_bids['error_fields']) == 0){
			$error_temp = pn_strip_input(ctv_ml($premiumbox->get_option('usve','accounterror')));
			if(!$error_temp){ $error_temp = 'Error!'; }
			
			if(isset($direction->id)){
				$verify_account = intval(is_isset($direction,'verify_acc1'));
				$sum = $cdata['sum1'];
				$sum_min = is_sum(is_isset($direction,'verify_sum_acc1'));
				
				$currency_id = $vd1->id;
				if($verify_account == 1 or $verify_account == 2 and $sum >= $sum_min){
					$ui = wp_get_current_user();
					$user_id = intval($ui->ID);
					$err = 0;
					if($user_id and $account1){
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."user_wallets WHERE user_id = '$user_id' AND verify='1' AND accountnum='$account1' AND currency_id='$currency_id'");
						$cc = intval($cc);
						if($cc == 0){
							$err = 1;
						}	
					} else {
						$err = 1;
					}
					if($err == 1){
						$error_now = $error_temp;
						$error_now = str_replace('[verifylink]', link_createandverify_wallet($account1, $currency_id), $error_now);
						$error_now = str_replace('[accountnum]', $account1, $error_now);
						$error_bids['error_text'][] = $error_now;
						$error_bids['error_fields']['account1'] = __('account is not verified','pn');	
					}
				}
				
				$verify_account = intval(is_isset($direction,'verify_acc2'));
				$sum = $cdata['sum2c'];
				$sum_min = is_sum(is_isset($direction,'verify_sum_acc2'));
				
				$currency_id = $vd2->id;
				if($verify_account == 1 or $verify_account == 2 and $sum >= $sum_min){
					$ui = wp_get_current_user();
					$user_id = intval($ui->ID);
					$err = 0;
					if($user_id and $account2){
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."user_wallets WHERE user_id = '$user_id' AND verify='1' AND accountnum='$account2' AND currency_id='$currency_id'");
						$cc = intval($cc);
						if($cc == 0){
							$err = 1;
						}	
					} else {
						$err = 1;
					}
					if($err == 1){
						$error_now = $error_temp;
						$error_now = str_replace('[verifylink]', link_createandverify_wallet($account2, $currency_id), $error_now);
						$error_now = str_replace('[accountnum]', $account2, $error_now);
						$error_bids['error_text'][] = $error_now;
						$error_bids['error_fields']['account2'] = __('account is not verified','pn');	
					}				
				}			
			}
		}
	}
	
	return $error_bids;
}

add_filter('onebid_col2', 'onebid_col2_uv_wallets', 10, 4);
function onebid_col2_uv_wallets($actions, $item, $data_fs, $v){
	if(isset($actions['account_give'])){
		$accv = $item->accv_give;
		if($accv == 1){
			$actions['account_give']['label'] .= '<br /><span class="bgreen">'. __('Verified account nubmer','pn') .'</span>';
		}
	}
	return $actions;
}

add_filter('onebid_col3', 'onebid_col3_uv_wallets', 10, 4);
function onebid_col3_uv_wallets($actions, $item, $data_fs, $v){
	if(isset($actions['account_get'])){
		$accv = $item->accv_get;
		if($accv == 1){
			$actions['account_get']['label'] .= '<br /><span class="bgreen">'. __('Verified account nubmer','pn') .'</span>';
		}
	}
	return $actions;
}

add_filter('userwallets_redirect', 'userwallets_redirect_uv_wallets', 10,2);
function userwallets_redirect_uv_wallets($url, $wallet_id){
global $premiumbox;	
	$return_place = is_param_get('return_place');
	if($return_place == 'verify'){
		return $premiumbox->get_page('walletsverify') .'?item_id='. $wallet_id;
	}
	return $url;
}