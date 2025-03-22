<?php
if( !defined( 'ABSPATH')){ exit(); }

global $premiumbox; 
$premiumbox->include_patch(__FILE__, 'migrate'); 
$premiumbox->include_patch(__FILE__, 'cron_migrate');