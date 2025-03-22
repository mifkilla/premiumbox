<?php
if( !defined( 'ABSPATH')){ exit(); } 

/** 1.5 **/
if(!function_exists('get_mycookie')){
	function get_mycookie($key){
		global $premiumbox;
			$premiumbox->_deprecated_function('get_mycookie', '1.5', "get_pn_cookie");
			return get_pn_cookie($key);
	}
}
if(!function_exists('add_mycookie')){
	function add_mycookie($key, $arg, $time=0){
		global $premiumbox;
			$premiumbox->_deprecated_function('add_mycookie', '1.5', "add_pn_cookie");
			return add_pn_cookie($key, $arg, $time);
	}
}
if(!function_exists('pn_editor_ml')){
	function pn_editor_ml(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_editor_ml', '1.5', "");
	}
}
if(!function_exists('pn_editor')){
	function pn_editor(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_editor', '1.5', "");
	}
}
if(!function_exists('pn_select')){
	function pn_select(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_select', '1.5', "");
	}
}
if(!function_exists('pn_select_disabled')){
	function pn_select_disabled(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_select_disabled', '1.5', "");
	}
} 
if(!function_exists('pn_admin_one_screen')){
	function pn_admin_one_screen(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_admin_one_screen', '1.5', "");
	}
}
if(!function_exists('pn_the_link_ajax')){
	function pn_the_link_ajax($action=''){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_the_link_ajax', '1.5', "the_pn_link");
			the_pn_link($action, 'post');
	}
}
if(!function_exists('pn_link_ajax')){
	function pn_link_ajax($action=''){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_link_ajax', '1.5', "pn_link");
			return pn_link($action, 'post');
	}
}
if(!function_exists('pn_admin_work_options')){
	function pn_admin_work_options(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_admin_work_options', '1.5', "");
	}
}
if(!function_exists('pn_set_option_template')){
	function pn_set_option_template(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_set_option_template', '1.5', "");
	}
}
if(!function_exists('pn_h3')){
	function pn_h3(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_h3', '1.5', "");
	}
}
if(!function_exists('pn_inputbig_ml')){
	function pn_inputbig_ml(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_inputbig_ml', '1.5', "");
	}
}
if(!function_exists('pn_inputbig')){
	function pn_inputbig(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_inputbig', '1.5', "");
	}
}
if(!function_exists('pn_input')){
	function pn_input(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_input', '1.5', "");
	}
}
if(!function_exists('pn_strip_options')){
	function pn_strip_options(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_strip_options', '1.5', "");
	}
}
if(!function_exists('pn_admin_substrate')){
	function pn_admin_substrate(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_admin_substrate', '1.5', "");
	}
}
if(!function_exists('pn_help')){
	function pn_help(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_help', '1.5', "");
	}
}
if(!function_exists('pn_uploader_ml')){
	function pn_uploader_ml(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_uploader_ml', '1.5', "");
	}
}
if(!function_exists('pn_uploader')){
	function pn_uploader(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_uploader', '1.5', "");
	}
}
if(!function_exists('pn_admin_select_box')){
	function pn_admin_select_box(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_admin_select_box', '1.5', "");
	}
}
if(!function_exists('pn_hidden_input')){
	function pn_hidden_input(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_hidden_input', '1.5', "");
	}
}
if(!function_exists('pn_admin_back_menu')){
	function pn_admin_back_menu(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_admin_back_menu', '1.5', "");
	}
}
if(!function_exists('pn_textareaico_ml')){
	function pn_textareaico_ml(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_textareaico_ml', '1.5', "");
	}
}
if(!function_exists('pn_textareaico')){
	function pn_textareaico(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_textareaico', '1.5', "");
	}
}
if(!function_exists('pn_sort_one_screen')){
	function pn_sort_one_screen(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_sort_one_screen', '1.5', "");
	}
}
if(!function_exists('get_sort_ul')){
	function get_sort_ul(){
		global $premiumbox;
			$premiumbox->_deprecated_function('get_sort_ul', '1.5', "");
	}
}
if(!function_exists('pn_textarea_ml')){
	function pn_textarea_ml(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_textarea_ml', '1.5', "");
	}
}
if(!function_exists('pn_textarea')){
	function pn_textarea(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_textarea', '1.5', "");
	}
}
if(!function_exists('pn_select_search')){
	function pn_select_search(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_select_search', '1.5', "");
	}
}
if(!function_exists('pn_textfield')){
	function pn_textfield(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_textfield', '1.5', "");
	}
}
if(!function_exists('pn_date')){
	function pn_date(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_date', '1.5', "");
	}
}
if(!function_exists('update_directions_to_masschange')){
	function update_directions_to_masschange(){
		global $premiumbox;
			$premiumbox->_deprecated_function('update_directions_to_masschange', '1.5', "");
	}
}
if(!function_exists('pn_checkbox')){
	function pn_checkbox(){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_checkbox', '1.5', "");
	}
}

/** 1.6 **/

if(!function_exists('get_mytime')){
	function get_mytime($date, $format='d.m.Y H:i'){
		global $premiumbox;
			$premiumbox->_deprecated_function('get_mytime', '1.6', "get_pn_time");		
	}
}
if(!function_exists('get_mydate')){
	function get_mydate($date, $format='d.m.Y'){
		global $premiumbox;
			$premiumbox->_deprecated_function('get_mydate', '1.6', "get_pn_date");		
	}
}		
if(!function_exists('is_my_date')){
	function is_my_date($date, $zn='d.m.Y'){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_my_date', '1.6', "is_pn_date");
	}
}
if(!function_exists('get_partner_money_now')){
	function get_partner_money_now(){
		global $premiumbox;
			$premiumbox->_deprecated_function('get_partner_money_now', '1.6', 'get_partner_money($user_id, array("1","0"))');
	}
}

/** 2.0 **/

if(!function_exists('pn_the_link_post')){
	function pn_the_link_post($action=''){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_the_link_post', '2.0', "the_pn_link");
			the_pn_link($action, 'post');
	}
}

if(!function_exists('pn_link_post')){
	function pn_link_post($action='', $method='', $nonce=1){
		global $premiumbox;
			$premiumbox->_deprecated_function('pn_link_post', '2.0', "pn_link");
			pn_link($action, 'post');
	}
}

if(!function_exists('get_ajax_link')){
	function get_ajax_link($action, $method='post'){
		global $premiumbox;
			$premiumbox->_deprecated_function('get_ajax_link', '2.0', "get_pn_action");
			get_pn_action($action, $method);		
	}
}

if(!function_exists('get_pn_notify')){
	function get_pn_notify(){
		global $premiumbox;
			$premiumbox->_deprecated_function('get_pn_notify', '2.0', "");		
	}
}

if(!function_exists('is_lahash')){
	function is_lahash(){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_lahash', '2.0', "");		
	}
}

if(!function_exists('is_firstzn_value')){
	function is_firstzn_value(){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_firstzn_value', '2.0', "");		
	}
}

if(!function_exists('get_merchant_link')){
	function get_merchant_link($action){
		global $premiumbox;
			$premiumbox->_deprecated_function('get_merchant_link', '2.0', "get_mlink");
			return get_mlink($action);
	}
}

if(!function_exists('get_hash_result_url')){
	function get_hash_result_url($m_id, $place='m'){
		global $premiumbox;
			$premiumbox->_deprecated_function('get_hash_result_url', '2.0', "hash_url");
			return hash_url($m_id, $place);
	}
}

if(!function_exists('the_merchant_bid_delete')){
	function the_merchant_bid_delete($id){
		global $premiumbox;
			$premiumbox->_deprecated_function('the_merchant_bid_delete', '2.0', "redirect_merchant_action");
	}
}

if(!function_exists('the_merchant_bid_success')){
	function the_merchant_bid_success($id){
		global $premiumbox;
			$premiumbox->_deprecated_function('the_merchant_bid_success', '2.0', "");
	}
}

if(!function_exists('is_enable_merchant')){
	function is_enable_merchant($id){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_enable_merchant', '2.0', "");
	}
}

if(!function_exists('the_merchant_bid_status')){
	function the_merchant_bid_status($status, $id, $params=array(), $ap=0){
		global $premiumbox;
			$premiumbox->_deprecated_function('the_merchant_bid_status', '2.0', "set_bid_status"); 
			set_bid_status($status, $id, $params);
	}
}

if(!function_exists('is_paymerch_realpay')){
	function is_paymerch_realpay($id){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_paymerch_realpay', '2.0', "");
	}
}

if(!function_exists('is_paymerch_verify')){
	function is_paymerch_verify($id){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_paymerch_verify', '2.0', "");
	}
}

if(!function_exists('is_paymerch_check_sum')){
	function is_paymerch_check_sum($id){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_paymerch_check_sum', '2.0', "");
	}
}

if(!function_exists('is_paymerch_check_day_sum')){
	function is_paymerch_check_day_sum($id){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_paymerch_check_day_sum', '2.0', "");
	}
}

if(!function_exists('is_course_give')){
	function is_course_give($id){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_course_give', '2.0', "is_course_direction");
	}
}

if(!function_exists('is_course_get')){
	function is_course_get($id){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_course_get', '2.0', "is_course_direction");
	}
}

if(!function_exists('convert_mycurs')){
	function convert_mycurs($sum, $curs, $share){
		global $premiumbox;
			$premiumbox->_deprecated_function('convert_mycurs', '2.0', "convert_bycourse");
			return convert_bycourse($sum, $curs, $share);
	}
}

/** 2.1 **/
if(!function_exists('is_exchange_page')){
	function is_exchange_page(){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_exchange_page', '2.1', "is_pn_page('exchange')");
			return is_pn_page('exchange');
	}
}

if(!function_exists('is_exchangestep_page')){
	function is_exchangestep_page(){
		global $premiumbox;
			$premiumbox->_deprecated_function('is_exchangestep_page', '2.1', "is_pn_page('hst')");
			return is_pn_page('hst');
	}
}

/** 2.2 **/
