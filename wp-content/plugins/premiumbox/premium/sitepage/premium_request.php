<?php
include_once('../../../../../wp-load.php');
header('Content-Type: text/html; charset=utf-8');

if(!defined('PREMIUM_PHYSICAL_FILES')){  
	status_header(404);
	exit;
} else {
	status_header(200);
}

$pn_action = pn_maxf(pn_strip_input(is_param_get('pn_action')),250);

if($pn_action and has_filter('premium_request_'. $pn_action)){
	do_action('premium_request_'. $pn_action);
}
		
exit;