<?php
if( !defined( 'ABSPATH')){ exit(); }

add_filter('get_account_wline', 'wchecks_get_account_wline', 10, 5);
function wchecks_get_account_wline($temp, $vd, $direction, $side_id, $place){
	$check_purse = 0;
	if($direction->check_purse == $side_id or $direction->check_purse == 3){
		$check_purse = 1;
	} 
	$check_purse_text = pn_strip_input(ctv_ml($vd->check_text));
	if(!$check_purse_text){ $check_purse_text = __('e-wallet has valid status','pn'); }		
			
	if($check_purse > 0){
		$temp .= '
		<div class="check_purse_line">
			<label><input type="checkbox" class="js_check_purse" name="check_purse'. $side_id .'" value="1" /> '. $check_purse_text .'</label>
		</div>
		';
	}	
	return $temp;
}

add_action('exchange_action_jquery', 'wchecks_exchange_action_jquery');
function wchecks_exchange_action_jquery(){ 
?>
function get_wcheck(ind){
	var check = 0;
	if($('input[name=check_purse'+ ind +']').length > 0){
		if($('input[name=check_purse'+ ind +']').prop('checked')){
			var check = 1;
		}
	}
	return check;
}
$(document).on('change','.js_check_purse',function(){
	go_calc($('.js_sum1'),1);
});
<?php	
}

add_filter("go_exchange_calc_js", "wchecks_go_exchange_calc_js");
function wchecks_go_exchange_calc_js($param){
	return $param . "+'&check1='+ get_wcheck(1)+'&check2='+get_wcheck(2)";
}

add_filter('get_calc_data_params', 'wchecks_get_calc_data_params', 0, 3);
function wchecks_get_calc_data_params($calc_data, $place='', $bid=''){

	if($place == 'calculator'){
		$calc_data['check1'] = intval(is_param_post('check1'));
		$calc_data['check2'] = intval(is_param_post('check2'));
	}
	if($place == 'recalc'){
		$calc_data['check1'] = intval(is_isset($bid,'check_purse1'));
		$calc_data['check2'] = intval(is_isset($bid,'check_purse2'));
	}	
	if($place == 'action'){
		$check_purse1 = 0;
		$check_purse2 = 0;
		
		$direction = $calc_data['direction'];
		$vd1 = $calc_data['vd1'];
		$vd2 = $calc_data['vd2'];
		$check_enable = intval($direction->check_purse);
		
		$account1 = trim(is_isset($calc_data, 'account1'));
		$account2 = trim(is_isset($calc_data, 'account2'));
		
		if($account1){
			if($check_enable == 1 or $check_enable == 3){
				$check_purse1 = apply_filters('set_check_account_give', 0, $account1, $vd1->check_purse);
			}
		}
		if($account2){
			if($check_enable == 2 or $check_enable == 3){
				$check_purse2 = apply_filters('set_check_account_get', 0, $account2, $vd2->check_purse);
			}
		}		
			
		$calc_data['check1'] = $check_purse1;
		$calc_data['check2'] = $check_purse2;
	}
	
	return $calc_data;
}

add_filter('error_bids','wchecks_error_bids',10,9);
function wchecks_error_bids($error_bids, $account1, $account2, $direction, $vd1, $vd2, $auto_data, $unmetas, $cdata){
global $wpdb, $premiumbox;

	$check_purse1 = $cdata['check1'];
	$check_purse2 = $cdata['check2'];

	$req_check_purse = intval($direction->req_check_purse);
	if($req_check_purse == 1 or $req_check_purse == 3){
		if($check_purse1 != 1 and !isset($error_bids['error_fields']['account1'])){
			$error_bids['error_fields']['account1'] = apply_filters('check_purse_text_give', __('account has invalid status','pn'), $vd1->check_purse);		
		}
	}
	if($req_check_purse == 2 or $req_check_purse == 3){
		if($check_purse2 != 1 and !isset($error_bids['error_fields']['account2'])){
			$error_bids['error_fields']['account2'] = apply_filters('check_purse_text_get', __('account has invalid status','pn'), $vd2->check_purse);			
		}
	}		
	
	return $error_bids;
}

add_filter('array_data_create_bids', 'wchecks_array_data_create_bids', 10, 5);
function wchecks_array_data_create_bids($array, $direction, $vd1, $vd2, $cdata){
	$array['check_purse1'] = $cdata['check1'];
	$array['check_purse2'] = $cdata['check2'];	
	return $array;
}

add_filter('get_calc_data', 'wchecks_get_calc_data', 0, 2); 
function wchecks_get_calc_data($cdata, $calc_data){
	$direction = $calc_data['direction'];

	$cdata['check1'] = $check1 = intval(is_isset($calc_data,'check1'));
	$cdata['check2'] = $check2 = intval(is_isset($calc_data,'check2'));
	
	if($check1 == 1){
		$cdata['com_sum1'] = $direction->com_sum1_check;
		$cdata['com_pers1'] = $direction->com_pers1_check;							
	}
	if($check2 == 1){
		$cdata['com_sum2'] = $direction->com_sum2_check;
		$cdata['com_pers2'] = $direction->com_pers2_check;							
	}		
	
	return $cdata;
}

if(!function_exists('list_tabs_currency_verify')){
	add_filter('list_tabs_currency', 'list_tabs_currency_verify');
	function list_tabs_currency_verify($list_tabs){
		$list_tabs['verify'] = __('Verification','pn');
		return $list_tabs;
	}
}

add_action('tab_currency_verify', 'wchecks_tab_currency_verify', 30, 2);
function wchecks_tab_currency_verify($data, $data_id){
	$form = new PremiumForm();
	
	$wchecks = array();
	$wchecks[0] = '--'. __('No item','pn') .'--';
	$list_wchecks = apply_filters('list_wchecks', array());
	$list_wchecks = (array)$list_wchecks;
	foreach($list_wchecks as $val){
		$wchecks[is_isset($val,'id')] = is_isset($val,'title');
	}
?>	

	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Checking account for verification in PS','pn'); ?></span></div>
			<?php	
			$form->select_search('check_purse', $wchecks, is_isset($data, 'check_purse')); 
			?>	
		</div>
		<div class="add_tabs_single">
		</div>
	</div>
	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Text indicating the verified wallet','pn'); ?></span></div>
			<?php 
			$atts = array('class' => 'big_input');
			$form->input('check_text', is_isset($data, 'check_text'), $atts); 
			?>			
		</div>
	</div>
<?php		
}

add_filter('pn_currency_addform_post', 'wchecks_currency_addform_post');
function wchecks_currency_addform_post($array){
	$array['check_text'] = pn_strip_input(is_param_post_ml('check_text'));
	$array['check_purse'] = is_extension_name(is_param_post('check_purse'));		
	return $array;
}

if(!function_exists('list_tabs_direction_verify')){
	add_filter('list_tabs_direction', 'list_tabs_direction_verify');
	function list_tabs_direction_verify($list_tabs){
		$list_tabs['verify'] = __('Verification','pn');
		return $list_tabs;
	}
}

add_action('tab_direction_tab4', 'wchecks_tab_direction_tab4', 11, 2);
function wchecks_tab_direction_tab4($data, $data_id){
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Payment systems fees (for verified accounts)','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="com_sum1_check" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_sum1_check')); ?>" /> S</div>
				<div><input type="text" name="com_pers1_check" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_pers1_check')); ?>" /> %</div>
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="com_sum2_check" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_sum2_check')); ?>" /> S</div>
				<div><input type="text" name="com_pers2_check" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_pers2_check')); ?>" /> %</div>	
			</div>		
		</div>
	</div>
<?php
}

add_action('tab_direction_verify', 'wchecks_tab_direction_verify', 100, 2);
function wchecks_tab_direction_verify($data, $data_id){		
?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Checking account for verification in PS','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="check_purse" id="check_purse" autocomplete="off">
					<option value="0" <?php selected(is_isset($data, 'check_purse'), 0); ?>><?php _e('No','pn'); ?></option>
					<option value="1" <?php selected(is_isset($data, 'check_purse'), 1); ?>><?php _e('Account Send','pn'); ?></option>
					<option value="2" <?php selected(is_isset($data, 'check_purse'), 2); ?>><?php _e('Account Receive','pn'); ?></option>
					<option value="3" <?php selected(is_isset($data, 'check_purse'), 3); ?>><?php _e('Account Send and Receive','pn'); ?></option>
				</select>
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Require account verification in PS','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="req_check_purse" id="req_check_purse" autocomplete="off">
					<option value="0" <?php selected(is_isset($data, 'req_check_purse'), 0); ?>><?php _e('No','pn'); ?></option>
					<option value="1" <?php selected(is_isset($data, 'req_check_purse'), 1); ?>><?php _e('Account Send','pn'); ?></option>
					<option value="2" <?php selected(is_isset($data, 'req_check_purse'), 2); ?>><?php _e('Account Receive','pn'); ?></option>
					<option value="3" <?php selected(is_isset($data, 'req_check_purse'), 3); ?>><?php _e('Account Send and Receive','pn'); ?></option>
				</select>
			</div>		
		</div>
	</div>
<?php 
}

add_filter('pn_direction_addform_post', 'wchecks_direction_addform_post');
function wchecks_direction_addform_post($array){
	$array['com_sum1_check'] = is_sum(is_param_post('com_sum1_check'));	
	$array['com_pers1_check'] = is_sum(is_param_post('com_pers1_check'));
	$array['com_sum2_check'] = is_sum(is_param_post('com_sum2_check'));	
	$array['com_pers2_check'] = is_sum(is_param_post('com_pers2_check'));			
	$array['check_purse'] = intval(is_param_post('check_purse'));
	$array['req_check_purse'] = intval(is_param_post('req_check_purse'));	
	return $array;
}

add_filter('list_export_directions', 'wchecks_list_export_directions');
function wchecks_list_export_directions($array){
	$array['com_sum1_check'] = __('Fee Send for verfified account','pn');
	$array['com_pers1_check'] = __('Fee (%) Send for verfified account','pn');
	$array['com_sum2_check'] = __('Fee Receive for verfified account','pn');
	$array['com_pers2_check'] = __('Fee (%) Receive for verfified account','pn');
	return $array;
}

add_filter('export_directions_filter', 'wchecks_export_directions_filter');
function wchecks_export_directions_filter($export_currency_filter){
	$export_currency_filter['sum_arr'][] = 'com_sum1_check';
	$export_currency_filter['sum_arr'][] = 'com_pers1_check';
	$export_currency_filter['sum_arr'][] = 'com_sum2_check';
	$export_currency_filter['sum_arr'][] = 'com_pers2_check';
	return $export_currency_filter;
}