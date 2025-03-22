<?php
include_once('../../../../../wp-load.php');
header("Content-type: text/xml; charset=utf-8");

if(!class_exists('PremiumBox')){ exit; }

if(has_filter('premium_request_exportxml')){
	do_action('premium_request_exportxml');
}