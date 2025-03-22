<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('pn_adminpage_quicktags_extlink')){
	add_action('pn_adminpage_quicktags','pn_adminpage_quicktags_extlink');
	function pn_adminpage_quicktags_extlink(){
	?>
	edButtons[edButtons.length] = 
	new edButton('premium_extlink', '<?php _e('External link','pn'); ?>','[extlink url=""]','[/extlink]');
	<?php
	}
}

if(!function_exists('extlink_pn_tags')){
	add_filter('pn_tags','extlink_pn_tags');
	function extlink_pn_tags($tags){
		$tags['extlink'] = array(
			'title' => __('External link','pn'),
			'start' => '[extlink url=""]',
			'end' => '[/extlink]',
		);
		return $tags;
	}
}

if(!function_exists('is_extlink')){
	function is_extlink($url=''){ 
	global $or_site_url;
		return $or_site_url .'/extlink.html?url='. $url;
	}
}

if(!function_exists('shortcode_extlink')){
	function shortcode_extlink($atts,$content=""){ 
		$url = trim(is_isset($atts, 'url'));
		$url = str_replace('&quot;','',$url);
		$url = esc_url($url);
		$content = trim(do_shortcode($content));
		return '<a href="'. is_extlink($url) .'" class="external_link" target="_blank">'. $content .'</a>';
	}
	add_shortcode('extlink', 'shortcode_extlink');
}

if(!function_exists('init_extlink')){
	add_action('init', 'init_extlink', 2);
	function init_extlink(){
	global $or_site_url;	
		$data = premium_rewrite_data();
		$super_base = $data['super_base'];
		if($super_base == 'extlink.html'){	
			status_header(200);
			header('Content-Type: text/html; charset=utf-8');
			
			$url = esc_url(is_param_get('url'));
			if(!$url or strstr($url, 'extlink.html')){
				$url = $or_site_url;
			} 
			wp_redirect($url);
			?>
			<!doctype html>
			<html>
			<head>
				<title>Redirecting</title>
				<meta name="robots" content="noindex, nofollow" />
				<meta http-equiv="cache-control" content="no-cache" />
				<meta http-equiv="pragma" content="no-cache" />
			</head>
			<body>
				<h1>Attention!</h1>
				<p><b>You'll be redirected to another site.</b></p>
				<p>To confirm, follow the link: <?php echo $url; ?></p>
			</body>
			</html>
			<?php
			
			exit;
		}	
	}
}	