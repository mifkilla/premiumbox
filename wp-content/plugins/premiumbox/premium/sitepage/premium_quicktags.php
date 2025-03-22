<?php
include_once('../../../../../wp-load.php');
header('Content-Type: application/x-javascript; charset=utf-8');

if(!defined('PREMIUM_PHYSICAL_FILES')){  
	status_header(404);
	exit;
} else {
	status_header(200);
}	

if(current_user_can('read')){			
	$place = pn_maxf(pn_strip_input(is_param_get('place')),500);
	if(has_filter('pn_adminpage_quicktags_' . $place) or has_filter('pn_adminpage_quicktags')){
		do_action('pn_adminpage_quicktags_' . $place);
		do_action('pn_adminpage_quicktags');
	}
}	

exit;