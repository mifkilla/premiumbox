<?php
if( !defined( 'ABSPATH')){ exit(); }	

global $wpdb; 
$prefix = $wpdb->prefix; 

	/* psys */
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."psys LIKE 'create_date'"); /* 1.5 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."psys ADD `create_date` datetime NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."psys LIKE 'edit_date'"); /* 1.5 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."psys ADD `edit_date` datetime NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."psys LIKE 'auto_status'"); /* 1.5 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."psys ADD `auto_status` int(1) NOT NULL default '1'");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."psys LIKE 'edit_user_id'"); /* 1.5 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."psys ADD `edit_user_id` bigint(20) NOT NULL default '0'");
	}	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."psys LIKE 't2_1'"); /* 1.6 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."psys ADD `t2_1` bigint(20) NOT NULL default '0'");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."psys LIKE 't2_2'"); /* 1.6 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."psys ADD `t2_2` bigint(20) NOT NULL default '0'");
	}	
	/* end psys */

	/* currency */
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE 't1_1'"); /* 1.6 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `t1_1` bigint(20) NOT NULL default '0'");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE 't1_2'"); /* 1.6 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `t1_2` bigint(20) NOT NULL default '0'");
	}	
	/* end currency */
	
	/* currency_custom_fields */ 
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency_custom_fields LIKE 'cf_order_give'"); /* 1.6 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency_custom_fields ADD `cf_order_give` bigint(20) NOT NULL default '0'");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency_custom_fields LIKE 'cf_order_get'"); /* 1.6 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency_custom_fields ADD `cf_order_get` bigint(20) NOT NULL default '0'");
	}	
	/* end currency_custom_fields */

	/* directions */
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'dcom1'"); /* 2.0 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `dcom1` int(1) NOT NULL default '0'");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'dcom2'"); /* 2.0 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `dcom2` int(1) NOT NULL default '0'");
	}	
	/* end directions */

	/* bids */
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'user_login'"); /* 1.6 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `user_login` varchar(150) NOT NULL");
	}
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'user_telegram'"); /* 2.0 */
	if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `user_telegram` varchar(150) NOT NULL");
	}	
	/* end bids */	