<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('mobile_template_include')){
	add_filter('template_include', 'mobile_template_include');
	function mobile_template_include($template){
		$template = wp_normalize_path($template);
		$temp_part = explode('/', $template);
		$replace = end($temp_part);
		$mobile_dir = str_replace($replace, 'mobile/', $template);
		$new_template = str_replace($replace, 'mobile/'.$replace, $template);
		
		if(is_dir($mobile_dir) and is_mobile()){
			if(is_file($new_template)){
				return $new_template;
			} else {
				$file = apply_filters('mobile_template_not_found', $mobile_dir.'index.php', $new_template, $mobile_dir);
				$file_default = $mobile_dir.'page.php';
				if(is_file($file)){
					return $file;
				} elseif(is_file($file_default)){
					return $file_default;
				}
			}
		}
		
		return $template;
	}
}

if(!function_exists('is_mobile')){
	function is_mobile(){
		$web_version = intval(get_pn_cookie('web_version'));
		if($web_version == 1){
			return false;
		} elseif($web_version == 2){	
			return true;
		} else {
			return wp_is_mobile();
		}
	}
}

if(!function_exists('mobile_theme_include')){
	function mobile_theme_include($page){
	$pager = TEMPLATEPATH . "/mobile/".$page.".php";
		if(file_exists($pager)){
			include($pager);
		}
	}
}

if(!function_exists('set_mobile_functions')){
	add_action('after_setup_theme', 'set_mobile_functions');
	function set_mobile_functions(){
		$file_functions = TEMPLATEPATH . "/mobile/functions.php";
		if(file_exists($file_functions)){
			include($file_functions);
		}
	}
}

if(!function_exists('pn_mobile_body_class')){
	add_filter('body_class', 'pn_mobile_body_class');
	function pn_mobile_body_class($classes){
		if(is_mobile()){
			$classes[] = 'mobile_body';		
		}	
		return $classes;
	}
}

if(!function_exists('mobile_vers_link')){
	function mobile_vers_link(){
		return get_pn_action('set_site_vers', 'get').'&set=mobile&return_url='. urlencode(esc_url($_SERVER['REQUEST_URI']));
	}
}

if(!function_exists('web_vers_link')){
	function web_vers_link(){
		return get_pn_action('set_site_vers', 'get').'&set=web&return_url='. urlencode(esc_url($_SERVER['REQUEST_URI']));
	}
}

if(!function_exists('mobile_site_set_site_vers')){
	add_action('premium_siteaction_set_site_vers', 'mobile_site_set_site_vers');
	function mobile_site_set_site_vers(){
		$return_url = trim(urldecode(is_param_get('return_url')));
		$set = trim(is_param_get('set'));
		if($set != 'mobile'){ $set = 'web'; }
		$set_indicator = 0;
		if($set == 'web'){ $set_indicator=1; } else { $set_indicator=2; }
		
		add_pn_cookie('web_version', $set_indicator);
		
		wp_redirect(get_safe_url($return_url));
		exit;
	}
}

if(!function_exists('mobile_get_pagenavi')){
	add_filter('get_pagenavi', 'mobile_get_pagenavi');
	function mobile_get_pagenavi($array){
		if(is_mobile()){
			if(isset($array['first'])){
				unset($array['first']);
			}
			if(isset($array['last'])){
				unset($array['last']);
			}		
			$array['num'] = 1;
			$array['numleft'] = 0;
			$array['numright'] = 0;
		}			
		return $array;
	}
}