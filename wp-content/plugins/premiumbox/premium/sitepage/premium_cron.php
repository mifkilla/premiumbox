<?php
include_once('../../../../../wp-load.php');
header('Content-Type: text/html; charset=utf-8');

if(!defined('PREMIUM_PHYSICAL_FILES')){  
	status_header(404);
	exit;
} else {
	status_header(501);
}

	
if(function_exists('check_hash_cron') and check_hash_cron()){
	status_header(200);
	
	$action = trim(is_param_get('pn_action'));
	if(function_exists('pn_cron_action')){
		pn_cron_action($action);
	} else {
		_e('Cron function does not exist','premium');
	}	
}
	
exit;