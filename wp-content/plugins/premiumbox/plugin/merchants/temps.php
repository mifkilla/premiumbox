<?php
if( !defined( 'ABSPATH')){ exit(); }

function merchant_temps_script(){
global $premiumbox;

	$time = current_time('timestamp');

	$temp = '';
	
	$array = array(
		'jquery' => '<script type="text/javascript" src="'. get_premium_url() .'js/jquery/script.min.js?ver='. $time .'"></script>',
		'jquery-ui' => '<script type="text/javascript" src="'. get_premium_url() .'js/jquery-ui/script.min.js"></script>',
		'clipboard' => '<script type="text/javascript" src="'. get_premium_url() .'js/jquery-clipboard/script.min.js"></script>',
		'form' => '<script type="text/javascript" src="'. get_premium_url() .'js/jquery-forms/script.min.js"></script>',
		'cookie' => '<script type="text/javascript" src="'. get_premium_url() .'js/jquery-cook/script.min.js"></script>',
		'style' => '<link rel="stylesheet" href="'. $premiumbox->plugin_url .'merchant_style.css?ver='. $time .'" type="text/css" media="all" />',
	);
	
	$array = apply_filters('merchant_temps_script', $array);
	foreach($array as $name => $link){
		$temp .= $link . "\n";
	}
	
	return $temp;
}

function merchant_temps_body_class(){
global $premiumbox;
	$body_class = "";
	if($premiumbox->get_option('exchange','mhead_style') == 1){
		$body_class = "body_black";
	}
	return join(' ',get_body_class($body_class));
}

add_filter('merchant_header', 'def_merchant_header', 0);
function def_merchant_header($html){
global $premiumbox, $bids_data;
	
	$item_title1 = pn_strip_input(ctv_ml($bids_data->psys_give)).' '.is_site_value($bids_data->currency_code_give);
	$item_title2 = pn_strip_input(ctv_ml($bids_data->psys_get)).' '.is_site_value($bids_data->currency_code_get);
	$title = sprintf(__('Exchange %1$s to %2$s','pn'), $item_title1, $item_title2);
	$title_order = apply_filters('merchant_order_title', __('Order ID','pn') . ' <strong>'. $bids_data->id . '</strong>');
	
	$html .= '
	<!DOCTYPE html>
	<html '. get_language_attributes( 'html' ) .'>
	<head>

		'. apply_filters('merchant_header_head', '', $title, $title_order) .'

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<meta name="HandheldFriendly" content="True" />
		<meta name="MobileOptimized" content="320" />
		<meta name="format-detection" content="telephone=no" />
		<meta name="PalmComputingPlatform" content="true" />
		<meta name="apple-touch-fullscreen" content="yes"/>
		<meta charset="'. get_bloginfo( 'charset' ) .'">
		<title>'. $title .'</title>
			
		'. merchant_temps_script() .'
		
	</head>
	<body class="' . merchant_temps_body_class() . '">';
		
	return $html;
}

add_filter('merchant_header', 'defhead_merchant_header', 5);
function defhead_merchant_header($html){
global $premiumbox, $bids_data;
	$logo = get_logotype();
	$textlogo = get_textlogo();
	
	$item_title1 = pn_strip_input(ctv_ml($bids_data->psys_give)).' '.is_site_value($bids_data->currency_code_give);
	$item_title2 = pn_strip_input(ctv_ml($bids_data->psys_get)).' '.is_site_value($bids_data->currency_code_get);
	$title = sprintf(__('Exchange %1$s to %2$s','pn'), $item_title1, $item_title2);
	$title_order = apply_filters('merchant_order_title', __('Order ID','pn') . ' <strong>'. $bids_data->id . '</strong>');
	
	$html .= '
	<div class="header">
		<div class="logo">
			<div class="logo_ins">
				<a href="'. get_site_url_ml() .'">';
								
					if($logo){
						$html .= '<img src="'. $logo .'" alt="" />';
					} elseif($textlogo){
						$html .= $textlogo; 
					} else { 
						$textlogo = str_replace(array('http://','https://','www.'),'',get_site_url_or());
						$html .= get_caps_name($textlogo);	
					} 
									
				$html .= '				
				</a>	
			</div>
		</div>
			<div class="clear"></div>
	</div>
	<div class="exchange_title">
		<div class="exchange_title_ins">
			'. $title .'
		</div>
	</div>
	<div class="order_title">
		<div class="order_title_ins">
			'. $title_order .'
		</div>
	</div>	
	<div class="back_div"><a href="'. get_bids_url($bids_data->hashed) .'" id="back_link">'. __('Back to order page','pn') .'</a></div>
	<div class="content">
	';

	return $html;
}

add_filter('merchant_footer', 'deffoot_merchant_footer', 990);
function deffoot_merchant_footer($html){
global $premiumbox, $bids_data;

	$html .= '</div>';

	return $html;
}

add_filter('merchant_footer', 'def_merchant_footer', 1000);
function def_merchant_footer($html){
	
	$html .= "
		<script type='text/javascript'>
			jQuery(function($){			
				$(document).on('keyup', function( e ){
					if(e.which == 27){
						var nurl = $('a#back_link').attr('href');
						window.location.href = nurl;
					}
				});								
			});
		</script>	
	</body>
	</html>
	";
	
	return $html;
}