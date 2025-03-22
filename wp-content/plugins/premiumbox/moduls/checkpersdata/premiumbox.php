<?php
if( !defined( 'ABSPATH')){ exit(); }

add_filter('exchange_check_filter', 'checkpersdata_exchange_check_filter', 100);
function checkpersdata_exchange_check_filter($check){
	$plugin = get_plugin_class();
	$checkpersdata = intval($plugin->get_option('checkpersdata'));

	if($plugin->get_option('checkpersdata', 'exchangeform') == 1){
		$check .= '
		<div class="exchange_checkpersdata">
			<label><input type="checkbox" '. checked($checkpersdata, 1, false) .' name="tpd" value="1" /> '. sprintf(__('I consent to processing of my personal data and accept the terms and conditions of the <a href="%s" target="_blank" rel="noreferrer noopener">User Agreement</a>.','pn'), $plugin->get_page('terms_personal_data')) .'</label>
		</div>
		';		
	}
		
	return $check;
}