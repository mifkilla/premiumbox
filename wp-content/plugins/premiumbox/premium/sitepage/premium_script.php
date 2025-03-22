<?php
include_once('../../../../../wp-load.php');
header('Content-Type: application/x-javascript; charset=utf-8');

if(!defined('PREMIUM_PHYSICAL_FILES')){  
	status_header(404);
	exit;
} else {
	status_header(200);
}	
	
do_action('premium_post', 'js');
	
set_premium_default_js();
	
do_action('premium_js');
			
exit;