<?php
include_once('../../../../../wp-load.php');
header('Content-Type: text/html; charset=utf-8');

if(!defined('PREMIUM_PHYSICAL_FILES')){  
	status_header(404);
	exit;
} else {
	status_header(501);
}

do_action('pn_plugin_api');

exit;