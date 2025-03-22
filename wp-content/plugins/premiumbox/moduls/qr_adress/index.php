<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]QR code generator[:en_US][ru_RU:]QR код генератор[:ru_RU]
description: [en_US:]QR code generator[:en_US][ru_RU:]QR код генератор[:ru_RU]
version: 2.2
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

add_action('pn_adminpage_quicktags','pn_adminpage_quicktags_qr_adress', 0); 
function pn_adminpage_quicktags_qr_adress(){
?>
edButtons[edButtons.length] = 
new edButton('premium_qr_code', '<?php _e('QR code','pn'); ?>','[qr_code size="200"]','[/qr_code]');
<?php	
}

add_filter('pn_tags','qr_adress_pn_tags', 0);
function qr_adress_pn_tags($tags){

	$tags['qr_code'] = array(
		'title' => __('QR code','pn'),
		'start' => '[qr_code size="200"]',
		'end' => '[/qr_code]',
	);

	return $tags;
}

add_filter('merchant_temps_script', 'qr_code_merchant_temps_script');
function qr_code_merchant_temps_script($lists){
global $premiumbox, $wpdb;
	$lists['qrcode'] = '<script type="text/javascript" src="'. get_premium_url() . 'js/jquery-qrcode/script.min.js"></script>';
	return $lists;
}

add_action('wp_enqueue_scripts', 'pn_themeinit_qr_code');
function pn_themeinit_qr_code(){
global $premiumbox;
	wp_enqueue_script('jquery js qr', get_premium_url() . "js/jquery-qrcode/script.min.js", false, time());
}

function shortcode_qr_code($atts,$content=""){ 
	$size = intval(is_isset($atts, 'size')); if($size < 1){ $size = 200; }
    return '<div class="js_qr_code_wrap"><span class="js_qr_code" data-size="'. $size .'" data-code="'. esc_attr(strip_tags(do_shortcode($content))) .'"></span></div>';
}
add_shortcode('qr_code', 'shortcode_qr_code'); 

add_action('get_merchants_options', 'qr_code_get_merchants_options', 10, 5);
function qr_code_get_merchants_options($options, $name, $data, $id, $place){
	$in_array = apply_filters('qr_keys', array());
	$show = 0;
	foreach($in_array as $in){
		if(strstr($name, $in)){
			$show = 1;
		}
	}
	if($show == 1){
		$options['qrcode'] = array(
			'view' => 'select',
			'title' => __('Show QR code on payment page','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => is_isset($data, 'qrcode'),
			'name' => 'qrcode',
			'work' => 'int',
		);	
	}
	
	return $options;
}	
	
add_filter('merchant_footer', 'qr_code_merchant_footer', 900);
function qr_code_merchant_footer($html){
global $premiumbox, $wpdb, $bids_data;	

	$item_id = $bids_data->id;
	$m_id = $bids_data->m_in;
	$m_data = get_merch_data($m_id);
	$qrcode = intval(is_isset($m_data, 'qrcode'));
	$new_html = '';
	if($qrcode == 1){
		$to_account = pn_strip_input($bids_data->to_account);
		
		$new_html .= '
		<div style="padding: 20px 0; width: 260px; margin: 0 auto;">
			<div id="qr_adress"></div>
		</div>
			
		<script type="text/javascript">
		jQuery(function($){
			$("#qr_adress").qrcode({
				size: 260,
				text: "'. $to_account .'"
			});
		});
		</script>
		';
	}
	$new_html .= '
	<script type="text/javascript">
	jQuery(function($){
		$(".js_qr_code").each(function(){
			var thet = $(this);
			$(thet).qrcode({
				size: parseInt(thet.attr("data-size")),
				text: thet.attr("data-code")
			});	
		});	
	});
	</script>	
	';
	
	return $new_html . $html;
}	

add_action('premium_js','qr_code_live');
add_action('live_change_html','qr_code_live');
function qr_code_live(){
?>	
jQuery(function($){
	$('.js_qr_code').each(function(){
		var thet = $(this);
		$(thet).qrcode({
			size: parseInt(thet.attr('data-size')),
			text: thet.attr('data-code')
		});	
	});
});
<?php	
}	