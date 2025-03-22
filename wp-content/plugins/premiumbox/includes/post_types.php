<?php 
if( !defined( 'ABSPATH')){ exit(); }

add_filter('manage_edit-page_columns', 'premiumbox_page_columns');
function premiumbox_page_columns($columns){
	$columns = pn_array_unset($columns, 'comments');		
	return $columns;
}

add_filter('manage_edit-post_columns', 'premiumbox_post_columns');
function premiumbox_post_columns($columns){
	$plugin = get_plugin_class();
	if(defined('PN_COMMENT_STATUS') and PN_COMMENT_STATUS == 'false' or $plugin->get_option('comment', 'post_comment') != 1){
		$columns = pn_array_unset($columns, 'comments');
	} 		
	return $columns;
}