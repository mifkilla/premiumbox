<?php 
/*
Plugin Name: Premium Exchanger hooks
Plugin URI: http://best-curs.info
Description: Actions and filters
Version: 0.1
Author: Best-Curs.info
Author URI: http://best-curs.info
*/

if( !defined( 'ABSPATH')){ exit(); }

add_action('wp_footer','my_wp_footer'); 
function my_wp_footer(){
?>

<!-- Put online chat code or another code here / Razmestite kod onlajn chata ili drugoi kod vmesto jetogo teksta !-->

<?php
}