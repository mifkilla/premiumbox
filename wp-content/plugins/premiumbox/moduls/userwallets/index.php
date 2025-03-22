<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]User accounts[:en_US][ru_RU:]Счета пользователей[:ru_RU]
description: [en_US:]User accounts[:en_US][ru_RU:]Счета пользователей[:ru_RU]
version: 2.2
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_userwallets');
add_action('all_bd_activated', 'bd_all_moduls_active_userwallets');
function bd_all_moduls_active_userwallets(){
global $wpdb;

	$table_name= $wpdb->prefix ."user_wallets";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',	
		`user_id` bigint(20) NOT NULL default '0',	
		`user_login` varchar(250) NOT NULL,
		`currency_id` bigint(20) NOT NULL default '0',
		`accountnum` longtext NOT NULL,
		`verify` int(1) NOT NULL default '0',
		`vidzn` int(5) NOT NULL default '0',
		PRIMARY KEY ( `id` ),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`auto_status`),
		INDEX (`edit_user_id`),
		INDEX (`user_id`),
		INDEX (`currency_id`),
		INDEX (`verify`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."user_wallets LIKE 'create_date'"); /* 2.0 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."user_wallets ADD `create_date` datetime NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."user_wallets LIKE 'edit_date'"); /* 2.0  */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."user_wallets ADD `edit_date` datetime NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."user_wallets LIKE 'auto_status'"); /* 2.0  */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."user_wallets ADD `auto_status` int(1) NOT NULL default '1'");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."user_wallets LIKE 'edit_user_id'"); /* 2.0  */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."user_wallets ADD `edit_user_id` bigint(20) NOT NULL default '0'");
	}	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE 'user_wallets'");
    if ($query == 0){ 
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `user_wallets` int(2) NOT NULL default '1'");
    }		 
}

add_filter('pn_tech_pages', 'list_tech_pages_userwallets');
function list_tech_pages_userwallets($pages){
	$pages[] = array(
		'post_name'      => 'userwallets',
		'post_title'     => '[en_US:]Your accounts[:en_US][ru_RU:]Ваши счета[:ru_RU]',
		'post_content'   => '[userwallets]',
		'post_template'   => 'pn-pluginpage.php',
	);		
	return $pages;
}

add_action('admin_menu', 'admin_menu_userwallets');
function admin_menu_userwallets(){
global $premiumbox;
	if(current_user_can('administrator') or current_user_can('pn_userwallets')){
		add_menu_page(__('User accounts','pn'), __('User accounts','pn'), 'read', "pn_userwallets", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('currency_codes'));	
		add_submenu_page("pn_userwallets", __('Add user account','pn'), __('Add user account','pn'), 'read', "pn_add_userwallets", array($premiumbox, 'admin_temp'));
	}
}

add_filter('pn_caps','userwallets_pn_caps');
function userwallets_pn_caps($pn_caps){
	$pn_caps['pn_userwallets'] = __('Work with user accounts','pn');
	return $pn_caps;
}

add_action('item_currency_delete', 'item_currency_delete_userwallets');
function item_currency_delete_userwallets($id){
global $wpdb;	
	$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."user_wallets WHERE currency_id = '$id'");
	foreach($items as $item){
		$item_id = $item->id;
		$res = apply_filters('item_userwallets_delete_before', pn_ind(), $item_id, $item);
		if($res['ind'] == 1){
			$result = $wpdb->query("DELETE FROM ". $wpdb->prefix ."user_wallets WHERE id = '$item_id'");
			do_action('item_userwallets_delete', $item_id, $item, $result);
		}
	}
}

add_action('delete_user', 'delete_user_userwallets');
function delete_user_userwallets($user_id){
global $wpdb;
	$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."user_wallets WHERE user_id = '$user_id'");
	foreach($items as $item){
		$item_id = $item->id;
		$res = apply_filters('item_userwallets_delete_before', pn_ind(), $item_id, $item);
		if($res['ind'] == 1){
			$result = $wpdb->query("DELETE FROM ". $wpdb->prefix ."user_wallets WHERE id = '$item_id'");
			do_action('item_userwallets_delete', $item_id, $item, $result);
		}
	}	
}

add_action('tab_currency_tab3', 'status_tab_currency_tab3', 9, 2);
function status_tab_currency_tab3($data, $data_id){
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Allow users to add new wallet in Account section','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="user_wallets" autocomplete="off">
					<?php 
						$user_wallets = is_isset($data, 'user_wallets'); 
					?>	
					<option value="0" <?php selected($user_wallets,0); ?>><?php _e('No','pn'); ?></option>
					<option value="1" <?php selected($user_wallets,1); ?>><?php _e('Yes','pn'); ?></option>
				</select>
			</div>			
		</div>
		<div class="add_tabs_single">
			
		</div>
	</div>
<?php		
}

add_filter('pn_currency_addform_post', 'pn_currency_addform_post_userwallets');
function pn_currency_addform_post_userwallets($array){
	$array['user_wallets'] = intval(is_param_post('user_wallets'));
	return $array;
}

add_filter('account_list_pages','account_list_pages_userwallets', 0);
function account_list_pages_userwallets($account_list_pages){
	$account_list_pages['userwallets'] = array(
		'type' => 'page',			
	);
	return $account_list_pages;
}

add_filter('userwallets_one', 'def_userwallets_one', 10, 3);
function def_userwallets_one($html, $key, $data){
	if($key == 'title'){
		$html .= '
		<div class="usersbill_one_title userwallets_one_title">
			'. get_currency_title($data) .'
		</div>';
	} elseif($key == 'account'){
		$html .= '
		<div class="usersbill_one_account userwallets_one_account">
			'. pn_strip_input($data->accountnum) .'
		</div>';		
	} elseif($key == 'close'){
		$html .= '
		<div class="close_usersbill close_userwallets js_close_user_wallet"></div>
		';			
	}
	return $html;
}

/* auto create account */
function create_userwallets($user_id, $user_login, $currency_id, $account){
global $wpdb, $premiumbox;	
	if($user_id > 0 and $account){
		$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE auto_status = '1' AND currency_status = '1' AND user_wallets = '1' AND id='$currency_id'");
		if(isset($item->id)){	
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."user_wallets WHERE user_id='$user_id' AND currency_id='$currency_id' AND accountnum='$account' AND auto_status = '1'");
			if($cc == 0){		
				$array = array();
				$array['user_id'] = $user_id;
				$array['user_login'] = is_user($user_login);
				$array['currency_id'] = $currency_id;
				$array['accountnum'] = $account;
				$res = apply_filters('item_userwallets_add_before', pn_ind(), $array); 
				if($res['ind'] == 1){
					$array['edit_date'] = current_time('mysql');
					$array['auto_status'] = 1;
					$array['create_date'] = current_time('mysql');
					$result = $wpdb->insert($wpdb->prefix.'user_wallets', $array);
					$data_id = $wpdb->insert_id;	
					do_action('item_userwallets_add', $data_id, $array, $result);
				}
			} 
		}	
	}
}

add_filter('change_bidstatus', 'userwallets_change_bidstatus', 50, 4);   
function userwallets_change_bidstatus($item, $set_status, $place, $user_or_system){
global $wpdb, $premiumbox;
	$item_id = $item->id;
	if($set_status == 'new' and $place == 'exchange_button'){
		create_userwallets($item->user_id, $item->user_login, $item->currency_id_give, $item->account_give);
		create_userwallets($item->user_id, $item->user_login, $item->currency_id_get, $item->account_get);
	}
	return $item;
}
/* end auto create account */

add_filter('array_list_payouts', 'array_list_payouts_userwallets');
function array_list_payouts_userwallets($array){
global $wpdb;
	
	if(isset($array['[account_input]'])){
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
	
		$new_input ='
		<div class="pay_purse_link">
			<div class="pay_purse_link_ins">
				<div class="pay_purse_ul">
					<div class="pay_purse_line ppl_0" data-purse="">'. __('No wallet','pn') .'</div>';
					
					$purses = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."user_wallets WHERE auto_status = '1' AND user_id = '$user_id'");
					foreach($purses as $purse){
						$new_input .= '<div class="pay_purse_line ppl_'. $purse->currency_id .'" data-purse="'. pn_strip_input($purse->accountnum) .'">'. pn_strip_input($purse->accountnum) .'</div>';
					}
					
					$new_input .= '
				</div>
			</div>
		</div>';
		
		$array['[account_input]'] = str_replace('name="','class="pay_input_purse" name="', $array['[account_input]']) . $new_input;	
	}
	
	return $array;
} 
 
add_action('premium_js','premium_js_exchange_purse_userwallets');
function premium_js_exchange_purse_userwallets(){
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);		
	if($user_id){	
?>
jQuery(function($){ 
    $(document).on('click', function(event) {
        if ($(event.target).closest(".js_purse_link").length) return;
        $('.js_purse_ul').hide();		
		
        event.stopPropagation();
    });	
	$(document).on('click', '.js_purse_link', function(){
		$(this).parents('.js_window_wrap').find('.js_purse_ul').show();
		
		return false;
	});
	$(document).on('click', '.js_purse_line', function(){
		var account = $(this).attr('data-purse');
		$(this).parents('.js_window_wrap').find('input').val(account).trigger( "change" );
		$('.js_purse_ul').hide();
		return false;
	});
	
	$(document).on('click', '.pay_purse_link', function(){
		$('.pay_purse_ul').show();
		var id = $('#pay_currency_id').val();
		$('.pay_purse_line').hide();
		var cc = $('.ppl_'+id).length;
		if(cc > 0){
			$('.ppl_'+id).show();
		} else {
			$('.ppl_0').show();
		}
		return false;
	});
	
	$(document).on('click', '.pay_purse_line', function(){
		var account = $(this).attr('data-purse');
		$('.pay_input_purse').val(account);
		$('.pay_purse_ul').hide();
		return false;
	});	
	
    $(document).click(function(event) {
        if ($(event.target).closest(".pay_purse_link").length) return;
        $('.pay_purse_ul').hide();
        event.stopPropagation();
    });	
});	
<?php	
	}
} 

add_filter('form_bids_account_input', 'form_bids_account_input_userwallets', 10, 6);
function form_bids_account_input_userwallets($input, $id, $vd, $purse, $placeholder, $h_class){
global $wpdb;
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	
	$purse_div = '';
	if($user_id){
		$purses = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."user_wallets WHERE auto_status = '1' AND user_id = '$user_id' AND currency_id='{$vd->id}'");
		$cp = count($purses);
		if($cp > 0){
			$purse_div = '
			<div class="js_purse_link">
				<div class="js_purse_link_ins">
					<div class="js_purse_ul">
						<div class="js_purse_line" data-purse="">'. __('No wallet','pn') .'</div>';												
							foreach($purses as $ps){
								$purse_div .= '<div class="js_purse_line" data-purse="'. pn_strip_input($ps->accountnum) .'">'. pn_strip_input($ps->accountnum) .'</div>';
							}	
						$purse_div .= '
						</div>
				</div>
			</div>';
			$input = str_replace('class="','class="js_purse_input ',$input);
		}												
	}	
	
	return $purse_div . $input;
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'add');
$premiumbox->include_patch(__FILE__, 'list');

$premiumbox->auto_include($path.'/shortcode');