<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Users' orders status[:en_US][ru_RU:]Пользовательские статусы заявок[:ru_RU]
description: [en_US:]Users' orders status[:en_US][ru_RU:]Пользовательские статусы заявок[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

/* BD */
add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_bidstatus');
add_action('all_bd_activated', 'bd_all_moduls_active_bidstatus');
function bd_all_moduls_active_bidstatus(){
global $wpdb;
	
	$table_name = $wpdb->prefix ."bidstatus";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`title` longtext NOT NULL,
		`bg_color` varchar(250) NOT NULL default '0',
		`status_order` bigint(20) NOT NULL default '0',
		PRIMARY KEY ( `id` ),
		INDEX (`status_order`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);	

	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."bidstatus LIKE 'bg_color'"); /* 1.5 */
    if ($query == 0) { 
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."bidstatus ADD `bg_color` varchar(250) NOT NULL default '0'");
    }	
	
}
/* end BD */

add_filter('pn_caps','bidstatus_pn_caps');
function bidstatus_pn_caps($pn_caps){
	$pn_caps['pn_bidstatus'] = __('Work with orders status','pn');
	return $pn_caps;
}

add_action('admin_menu', 'admin_menu_bidstatus');
function admin_menu_bidstatus(){
global $premiumbox;	
	if(current_user_can('administrator') or current_user_can('pn_bidstatus')){
		add_menu_page( __('Orders status','pn'), __('Orders status','pn'), 'read', "pn_bidstatus", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('mystatus'));	
		add_submenu_page("pn_bidstatus", __('Add','pn'), __('Add','pn'), 'read', "pn_add_bidstatus", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_bidstatus", __('Sort','pn'), __('Sort','pn'), 'read', "pn_sort_bidstatus", array($premiumbox, 'admin_temp'));
	}
}

add_filter('bid_status_list', 'bid_status_list_bidstatus');
function bid_status_list_bidstatus($list){
global $wpdb;	
	
	$bids_my_statused = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."bidstatus ORDER BY status_order ASC");
	foreach($bids_my_statused as $item){
		$list['my'.$item->id] = pn_strip_input(ctv_ml($item->title));
	}
	
	return $list;
}

add_action('pn_adminpage_style_pn_bids', 'pn_adminpage_style_bidstatus');
add_action('pn_adminpage_style_pn_live_bids', 'pn_adminpage_style_bidstatus');
add_action('pn_adminpage_style_pn_bidlogs', 'pn_adminpage_style_bidstatus');
function pn_adminpage_style_bidstatus(){
global $wpdb;	
	$bids_my_statused = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."bidstatus ORDER BY status_order ASC");
	foreach($bids_my_statused as $item){
?>
	.st_my<?php echo $item->id; ?>{ background: <?php echo pn_strip_input($item->bg_color); ?>; }
<?php
	}
?>
<?php	
}

global $premiumbox;
$premiumbox->include_patch(__FILE__, 'add');
$premiumbox->include_patch(__FILE__, 'list');
$premiumbox->include_patch(__FILE__, 'sort');