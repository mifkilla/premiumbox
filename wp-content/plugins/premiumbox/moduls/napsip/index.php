<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Restriction for users[:en_US][ru_RU:]Ограничения для пользователей[:ru_RU]
description: [en_US:]Restriction for users by IP address, account number, login, etc. when orders are created[:en_US][ru_RU:]Ограничение для пользователей по IP адресу, номеру счета, логину и т.п. при создании заявок[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_napsip');
add_action('all_bd_activated', 'bd_all_moduls_active_napsip');
function bd_all_moduls_active_napsip(){
global $wpdb;	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'not_ip'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `not_ip` longtext NOT NULL");
    }
}

add_action('tab_direction_tab8', 'napsip_tab_direction_tab8', 30, 2);
function napsip_tab_direction_tab8($data, $data_id){

	$string = pn_strip_input(is_isset($data, 'not_ip'));
	$def = array();
	if(preg_match_all('/\[d](.*?)\[\/d]/s',$string, $match, PREG_PATTERN_ORDER)){
		$def = $match[1];
	}
	
	$naps_constraints = get_direction_meta($data_id, 'naps_constraints');
	if(!is_array($naps_constraints)){ $naps_constraints = array(); }
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Prohibited IP (at the beginning of a new line)','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<textarea name="not_ip" style="width: 100%; height: 100px;"><?php echo join("\n",$def); ?></textarea>
			</div>
		</div>		
	</div>		
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of exchange orders from same IP per day','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="napsip_max_ip" style="width: 200px;" value="<?php echo intval(is_isset($naps_constraints, 'max_ip')); ?>" />
			</div>
		</div>	
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of exchange orders from same account Send per day','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="napsip_max_account1" style="width: 200px;" value="<?php echo intval(is_isset($naps_constraints, 'max_account1')); ?>" />
			</div>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of exchange orders from same account Receive per day','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="napsip_max_account2" style="width: 200px;" value="<?php echo intval(is_isset($naps_constraints, 'max_account2')); ?>" />
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of exchange orders from same user login per day','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="napsip_max_user" style="width: 200px;" value="<?php echo intval(is_isset($naps_constraints, 'max_user')); ?>" />
			</div>
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of exchange orders from same e-mail per day','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="napsip_max_email" style="width: 200px;" value="<?php echo intval(is_isset($naps_constraints, 'max_email')); ?>" />
			</div>
		</div>
	</div>	
	<?php 		
} 

add_filter('pn_direction_addform_post', 'napsip_pn_direction_addform_post');
function napsip_pn_direction_addform_post($array){
	$not_ip = explode("\n", is_param_post('not_ip'));
	$item = '';
	foreach($not_ip as $v){
		$v = pn_strip_input($v);
		if($v){
			$item .= '[d]'. $v .'[/d]';
		}
	}
	$array['not_ip'] = $item;
	return $array;
}
 
add_action('item_direction_edit', 'item_direction_edit_napsip', 10, 2);
add_action('item_direction_add', 'item_direction_edit_napsip', 10, 2);
function item_direction_edit_napsip($data_id, $array){
	$naps_constraints = array(
		'max_ip' => intval(is_param_post('napsip_max_ip')),
		'max_account1' => intval(is_param_post('napsip_max_account1')),
		'max_account2' => intval(is_param_post('napsip_max_account2')),
		'max_user' => intval(is_param_post('napsip_max_user')),
		'max_email' => intval(is_param_post('napsip_max_email')),
	);
	update_direction_meta($data_id, 'naps_constraints', $naps_constraints);	
}	

add_action('admin_menu', 'admin_init_napsip');
function admin_init_napsip(){
global $premiumbox;	
	add_submenu_page("pn_moduls", __('Restriction for users','pn'), __('Restriction for users','pn'), 'administrator', "pn_napsip", array($premiumbox, 'admin_temp'));
}

add_action('pn_adminpage_title_pn_napsip', 'def_adminpage_title_pn_napsip');
function def_adminpage_title_pn_napsip($page){
	_e('Restriction for users','pn');
} 

add_action('pn_adminpage_content_pn_napsip','def_pn_adminpage_content_pn_napsip');
function def_pn_adminpage_content_pn_napsip(){
global $wpdb;

	$bid_status_list = apply_filters('bid_status_list',array());
	
	$napsip = get_option('napsip');
	if(!is_array($napsip)){ $napsip = array(); }

	$form = new PremiumForm();
?>
<form method="post" action="<?php the_pn_link('','post'); ?>">
<div class="premium_body">
	<?php
	$form->h3(__('Status settings','pn'), __('Save','pn'));	
	?>
	<div class="premium_standart_line"> 
		<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Which orders are considered executed','pn'); ?></div></div>
		<div class="premium_stline_right"><div class="premium_stline_right_ins">
			<div class="premium_wrap_standart">
				
				<?php
				$scroll_lists = array();
							
				foreach($bid_status_list as $key => $val){
					$checked = 0;
					if(in_array($key,$napsip)){
						$checked = 1;
					}	
					$scroll_lists[] = array(
						'title' => $val,
						'checked' => $checked,
						'value' => $key,
					);
				}
				echo get_check_list($scroll_lists, 'napsip[]','','',1);
				?>			
				
				<div class="premium_clear"></div>
			</div>
		</div></div>
			<div class="premium_clear"></div>
	</div>
	<?php
	$form->h3('', __('Save','pn'));	
	?>							
</div>	
</form>
<?php
}

add_action('premium_action_pn_napsip','def_premium_action_pn_napsip');
function def_premium_action_pn_napsip(){
global $wpdb;	

	only_post();
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$new_napsip = array();
	$napsip = is_param_post('napsip');
	if(is_array($napsip)){
		foreach($napsip as $v){
			$v = is_status_name($v);
			if($v){
				$new_napsip[] = $v;
			}
		}
	}
	update_option('napsip',$new_napsip); 	

	$url = admin_url('admin.php?page=pn_napsip&reply=true');
	$form->answer_form($url);
} 

add_filter('error_bids', 'error_bids_napsip', 99 ,7);
function error_bids_napsip($error_bids, $account1, $account2, $direction, $vd1, $vd2, $auto_data){
global $wpdb;

	$user_ip = pn_real_ip();

	if(pn_has_ip($direction->not_ip, $user_ip)){ 
	
		$error_bids['error_text'][] = __('Error! For your exchange denied','pn');
		
	} else {
		
		$naps_constraints = @unserialize(is_isset($direction,'naps_constraints'));
		if(!is_array($naps_constraints)){ $naps_constraints = array(); }		
		
		$napsip = get_option('napsip');
		if(!is_array($napsip)){ $napsip = array(); }
		
		$st_join = create_data_for_bd($napsip, 'status');
		$where = '';
		if($st_join){
			$where = " AND status IN($st_join)";
		} 
			
		$time = current_time('timestamp');
		$date = date('Y-m-d 00:00:00',$time);
		$direction_id = $direction->id;		
		
		$error = 0;
		
		$max_ip = intval(is_isset($naps_constraints, 'max_ip'));
		if($max_ip > 0 and $error == 0){
			$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."exchange_bids WHERE user_ip='$user_ip' AND edit_date >= '$date' AND status != 'auto' $where AND direction_id='$direction_id'");
			if($now_cou >= $max_ip){
				$error_bids['error_text'][] = __('Error! For your exchange denied','pn');			
			}
		}
		$max_account1 = intval(is_isset($naps_constraints, 'max_account1'));
		if($max_account1 > 0 and $error == 0){
			$n_item = $account1;
			if($n_item){
				$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."exchange_bids WHERE account_give='$n_item' AND edit_date >= '$date' AND status != 'auto' $where AND direction_id='$direction_id'");
				if($now_cou >= $max_account1){
					$error_bids['error_text'][] = __('Error! For your exchange denied','pn');			
				}
			}
		}
		$max_account2 = intval(is_isset($naps_constraints, 'max_account2'));
		if($max_account2 > 0 and $error == 0){		
			$n_item = $account2;
			if($n_item){
				$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."exchange_bids WHERE account_get='$n_item' AND edit_date >= '$date' AND status != 'auto' $where AND direction_id='$direction_id'");
				if($now_cou >= $max_account2){
					$error_bids['error_text'][] = __('Error! For your exchange denied','pn');			
				}	
			}
		}	
		$max_user = intval(is_isset($naps_constraints, 'max_user'));
		if($max_user > 0 and $error == 0){	
			$ui = wp_get_current_user();
			$n_item = intval($ui->ID);		
			if($n_item){
				$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."exchange_bids WHERE user_id='$n_item' AND edit_date >= '$date' AND status != 'auto' $where AND direction_id='$direction_id'");
				if($now_cou >= $max_user){
					$error_bids['error_text'][] = __('Error! For your exchange denied','pn');			
				}		
			}
		}
		$max_email = intval(is_isset($naps_constraints, 'max_email'));
		if($max_email > 0 and $error == 0){	
			$n_item = is_isset($auto_data, 'user_email');
			if($n_item){
				$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."exchange_bids WHERE user_email='$n_item' AND edit_date >= '$date' AND status != 'auto' $where AND direction_id='$direction_id'");
				if($now_cou >= $max_email){
					$error_bids['error_text'][] = __('Error! For your exchange denied','pn');			
				}		
			}
		}		
	}
	
	return $error_bids;
}