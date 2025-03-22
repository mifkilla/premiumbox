<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('admin_menu', 'admin_menu_reserv');
function admin_menu_reserv(){
global $premiumbox;
	if(current_user_can('administrator') or current_user_can('pn_currency_reserv')){
		add_menu_page(__('Reserve adjustment','pn'), __('Reserve adjustment','pn'), 'read', "pn_currency_reserv", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('reserv'), 626);	
		add_submenu_page("pn_currency_reserv", __('Add reserve transaction','pn'), __('Add reserve transaction','pn'), 'read', "pn_add_currency_reserv", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_currency_reserv", __('Reserve adjustment (group)','pn'), __('Reserve adjustment (group)','pn'), 'read', "pn_mass_reserv", array($premiumbox, 'admin_temp'));
	}
}

add_filter('pn_caps','currency_reserv_pn_caps'); 
function currency_reserv_pn_caps($pn_caps){
	$pn_caps['pn_currency_reserv'] = __('Use adjustment reserve','pn');
	return $pn_caps;
}

add_filter('change_bidstatus', 'reserv_change_bidstatus', 1000, 4);   
function reserv_change_bidstatus($item, $set_status, $place, $user_or_system){
global $wpdb, $premiumbox;
	$item_id = $item->id;
	$virtual_status = array('archived','realdelete');
	if($item->status == $set_status or in_array($set_status, $virtual_status)){ 
		update_currency_reserv($item->currency_id_give, '', $set_status);
		update_currency_reserv($item->currency_id_get, '', $set_status);
	}
	return $item;
}	

add_action('item_currency_edit','reserv_item_currency_edit',1,2);
function reserv_item_currency_edit($data_id, $array){
	$object = (object)$array;
	update_currency_reserv($data_id, $object);
} 

add_action('item_currency_delete','reserv_item_currency_delete', 10, 2);
function reserv_item_currency_delete($id, $item){
global $wpdb;
	$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."currency_reserv WHERE currency_id = '$id'");
	foreach($items as $item){
		$item_id = $item->id;
		$res = apply_filters('item_currency_reserv_delete_before', pn_ind(), $item_id, $item);
		if($res['ind'] == 1){
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "currency_reserv WHERE id = '$item_id'");
			do_action('item_currency_reserv_delete', $item_id, $item, $result);
		}
	}
}

add_action('item_currency_code_edit','reserv_item_currency_code_edit',1,2);
function reserv_item_currency_code_edit($data_id, $array){
global $wpdb;
	$currency_code_title = is_isset($array,'currency_code_title');
	$wpdb->update($wpdb->prefix . 'currency_reserv', array('currency_code_title' => $currency_code_title), array('currency_code_id' => $data_id));
}

add_action('item_currency_reserv_delete','reserv_item_currency_reserv_delete', 10, 2);
add_action('item_currency_reserv_basket','reserv_item_currency_reserv_delete', 10, 2);
add_action('item_currency_reserv_unbasket','reserv_item_currency_reserv_delete', 10, 2);
function reserv_item_currency_reserv_delete($id, $item){
global $wpdb;
	update_currency_reserv($item->currency_id);
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'list');
$premiumbox->include_patch(__FILE__, 'add');
$premiumbox->include_patch(__FILE__, 'mass_add');
$premiumbox->include_patch(__FILE__, 'settings');