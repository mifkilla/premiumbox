<?php
include_once('../../../../../wp-load.php');
header('Content-Type: text/html; charset=utf-8');

if(!defined('PREMIUM_PHYSICAL_FILES')){  
	status_header(404);
	exit;
} else {
	status_header(501);
}

do_action('premium_post', 'action');			

$pn_action = pn_maxf(pn_strip_input(is_param_get('pn_action')),250);
if($pn_action and has_filter('premium_siteaction_'. $pn_action)){
	status_header(200);
	
	do_action('premium_siteaction_'. $pn_action);
}
		
exit;