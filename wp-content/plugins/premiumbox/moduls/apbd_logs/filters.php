<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('item_currency_reserv_delete','apbd_item_currency_reserv_delete');
function apbd_item_currency_reserv_delete($id){
global $wpdb;
	$wpdb->query("DELETE FROM ".$wpdb->prefix."db_admin_logs WHERE item_id = '$id' AND tbl_name='reserv'");
}

add_action('item_discount_delete','apbd_item_discount_delete');
function apbd_item_discount_delete($id){
global $wpdb;
	$wpdb->query("DELETE FROM ".$wpdb->prefix."db_admin_logs WHERE item_id = '$id' AND tbl_name='discount'");
}

add_filter('change_bidstatus', 'apbd_change_bidstatus', 200, 4);   
function apbd_change_bidstatus($item, $set_status, $place, $user_or_system){
global $wpdb;
	if($set_status == 'realdelete' or $set_status == 'archived'){
		$id = $item->id;
		$wpdb->query("DELETE FROM ".$wpdb->prefix."db_admin_logs WHERE item_id = '$id' AND tbl_name='bids'");
	}
	return $item;
}	

/************************/

add_action('item_currency_reserv_edit','apbd_item_currency_reserv', 11 , 3);
function apbd_item_currency_reserv($id, $array, $ldata=''){	

	$tbl_check = array(
		'trans_title' => __('Comment','pn'),
		'trans_sum' => __('Amount','pn'),
		'currency_id' => __('Currency ID','pn'),
	);	
	
	insert_apbd('reserv', $tbl_check, $id, $array, $ldata);
}

add_action('item_discount_edit','apbd_item_discount', 11 , 3);
function apbd_item_discount($id, $array, $ldata=''){	

	$tbl_check = array(
		'sumec' => __('Amount more than','pn'),
		'discount' => __('Discount (%)','pn'),
	);	
	
	insert_apbd('discount', $tbl_check, $id, $array, $ldata);
}
	
add_action('pn_onebid_edit','apbd_pn_onebid_edit', 11 , 4);
function apbd_pn_onebid_edit($id, $array, $ldata='', $lists){	

	$tbl_check = array();
	if(is_array($lists)){
		foreach($lists as $list_name => $list_data){
			$tbl_check[$list_name] = is_isset($list_data, 'title');
		}
	}
	
	insert_apbd('bids', $tbl_check, $id, $array, $ldata);
}	
	
/************************/

add_action('pn_adminpage_content_pn_add_currency_reserv','transreslogs_pn_admin_content_pn_add_currency_reserv', 11);
function transreslogs_pn_admin_content_pn_add_currency_reserv(){
	$tbl_check = array(
		'trans_sum' => __('Amount','pn'),
		'currency_id' => __('Currency ID','pn'),
		'trans_title' => __('Comment','pn'),
	);	
	view_apbd('reserv', $tbl_check);
}

add_action('pn_adminpage_content_pn_add_discount','transreslogs_pn_admin_content_pn_add_discount', 11);
function transreslogs_pn_admin_content_pn_add_discount(){
	$tbl_check = array(
		'sumec' => __('Amount more than','pn'),
		'discount' => __('Discount (%)','pn'),
	);	
	view_apbd('discount', $tbl_check);
}	

add_action('onebid_edit','apbd_onebid_edit', 11, 3);
function apbd_onebid_edit($id, $item, $lists){

	$tbl_check = array();
	if(is_array($lists)){
		foreach($lists as $list_name => $list_data){
			$tbl_check[$list_name] = is_isset($list_data, 'title');
		}
	}	
	view_apbd('bids', $tbl_check);

}

/************************/