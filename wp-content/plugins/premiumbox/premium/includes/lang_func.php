<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('get_site_langs')){
	function get_site_langs(){
		return apply_filters('pn_site_langs', array());
	}
}

if(!function_exists('def_pn_site_langs')){
	add_filter('pn_site_langs','def_pn_site_langs');
	function def_pn_site_langs($langs){
		$langs['ru_RU'] = 'Русский';
		$langs['en_US'] = 'English';
		return $langs;
	}
}

if(!function_exists('get_title_forkey')){
	function get_title_forkey($key){
		$key = pn_string($key);
		$langs = get_site_langs();
		return is_isset($langs,$key);
	}
}

global $pn_lang, $pn_current_lang;

$pn_lang = get_option('pn_lang');
if(!is_array($pn_lang)){ $pn_lang = array(); }

$pn_current_lang = get_locale();

if(!function_exists('is_ml')){
	function is_ml(){
		return apply_filters('is_ml', 1);
	}
}

if(!function_exists('get_lang_icon')){
	function get_lang_icon($key){
		$key = pn_string($key); 
		$url = plugin_basename(__FILE__);
		$parts = explode('/', $url);
		$plugin_folder = apply_filters('ml_flag_url', $parts[0]);
		$new_url = WP_PLUGIN_URL . '/' . $plugin_folder . '/flags/'. $key .'.png';
		return $new_url;
	}
}

if(!function_exists('get_site_lang')){
	function get_site_lang(){
	global $pn_lang;
		$lang = get_locale();
		if(isset($pn_lang['site_lang']) and $pn_lang['site_lang']){
			$lang = $pn_lang['site_lang'];
		} 		
		return $lang;
	}
}

if(!function_exists('get_admin_lang')){
	function get_admin_lang(){
	global $pn_lang;	
		$lang = get_locale();
		if(isset($pn_lang['admin_lang']) and $pn_lang['admin_lang']){
			$lang = $pn_lang['admin_lang'];
		} 	
		return $lang;
	}
}

if(!function_exists('get_lang_key')){
	function get_lang_key($arg){
		$arg = pn_string($arg);
		$keyname = explode('_',$arg);
		$keyname = $keyname[0];
		return $keyname;
	}
}

if(!function_exists('get_site_url_or')){
	function get_site_url_or(){
	global $or_site_url;
		return rtrim($or_site_url,'/');
	}
}

if(!function_exists('get_site_url_ml')){
	function get_site_url_ml(){

		$now_lang = get_locale();
		$def_lang = get_site_lang();
		
		$url = get_site_url_or();
		if($now_lang != $def_lang){
			$key = get_lang_key($now_lang);
			return $url .'/'. $key . '/';
		}
		
		return rtrim($url,'/');
	}
}

if(!function_exists('get_langs_ml')){
	function get_langs_ml(){
	global $pn_lang;
		$lang = get_locale();	
		$ml_array = array();
		$ml_array[] = $lang;
		if(isset($pn_lang['multisite_lang'])){
			$array = $pn_lang['multisite_lang'];
			if(is_array($array)){
				foreach($array as $key){
					if($key != $lang){
						$ml_array[] = pn_strip_input($key);
					}
				}
			}		
		}
		return $ml_array;
	}
}

if(!function_exists('is_lang_prefix')){
	function is_lang_prefix($arg){
		$arg = pn_string($arg);
		if (preg_match("/^[A-Za-z]{2}$/", $arg, $matches )) {
			return strtolower($arg);
		}
		return '';
	}
}

if(!function_exists('is_lang_attr')){
	function is_lang_attr($arg){
		$arg = pn_string($arg);
		if (preg_match("/^[A-Za-z]{2}[_]{1}[A-Za-z]{2}$/", $arg, $matches )) {
			return strtolower($arg);
		} 
		return '';
	}
}

if(!function_exists('lang_self_link')){
	function lang_self_link($lang=''){
		if(!$lang){
			$lang = get_locale();
		}
		$def_lang = get_site_lang();
		if($def_lang == $lang){
			$link = esc_url(get_site_url_or() . $_SERVER['REQUEST_URI']);
		} else {
			$key = get_lang_key($lang);
			$link = esc_url(get_site_url_or() .'/'. $key . $_SERVER['REQUEST_URI']);
		}
		return $link;
	}
}

if(!function_exists('convert_to_ml')){
	function convert_to_ml($string){
		$site_lang = get_site_lang();
		
		if(is_string($string)){
			$string = trim($string);
			if($string){
				if ( false === strpos( $string, '['. $site_lang .':]' ) ) {
					$string = '['. $site_lang .':]'. $string .'[:'. $site_lang .']';
				}
			}
		}
		
			return $string;
	}
}

if(!function_exists('get_value_ml')){
	function get_value_ml($string){ 
		$array = array();
		if(is_string($string)){
			$now_lang = get_locale();
			if ( false === strpos( $string, '[' ) ) {
				$array[$now_lang] = $string;
				return $array;
			}		
			if(preg_match_all('/\[(.*?):\](.*?)\[:(.*?)\]/s',$string, $match, PREG_PATTERN_ORDER)){
				foreach($match[1] as $key => $lang){
					$array[$lang] = $match[2][$key];
				}
			} else {
				$array[$now_lang] = $string;
			}
		}	
		return $array;
	}
}

if(!function_exists('replace_value_ml')){
	function replace_value_ml($string, $newtext='', $lang=''){
		if(!$lang){ $lang = get_locale(); }
		if(is_string($string)){
			if ( false === strpos( $string, '[' ) ) {
				return $string;	
			}
			if(preg_match_all('/\[(.*?):\](.*?)\[:(.*?)\]/s',$string, $match, PREG_PATTERN_ORDER)){
				$key = array_search($lang, $match[1]);
				if(is_numeric($key)){
					$string = preg_replace('/\['. $lang .':\](.*?)\[:'. $lang .'\]/s', '['. $lang .':]'.$newtext.'[:'. $lang .']', $string);
				} else {
					$string .= '['. $lang .':]'. $newtext .'[:'. $lang .']';
				}		
			}	
		}
		return $string;
	}
}

if(!function_exists('ctv_ml')){
	function ctv_ml($string, $now_lang=''){
		if(!trim($now_lang)){
			$now_lang = get_locale();
		}
		if(is_string($string)){
			if ( false === strpos( $string, '[' ) ) {
				return $string;	
			}
			if(preg_match_all('/\[(.*?):\](.*?)\[:(.*?)\]/s',$string, $match, PREG_PATTERN_ORDER)){
				$key = array_search($now_lang, $match[1]);
				if(is_numeric($key)){
					$newtext = trim($match[2][$key]);
				} else {
					$newtext = trim($match[2][0]);
					$newtext = apply_filters('ctv_ml_default', $newtext, $match);
				}
				return $newtext;			
			}	
		} elseif(is_object($string)){
			$new_object = array();
			foreach($string as $key => $val){
				$new_object[$key] = ctv_ml($val, $now_lang);
			}
			return (object)$new_object;
		} elseif(is_array($string)){ 
			$new_object = array();
			foreach($string as $key => $val){
				$new_object[$key] = ctv_ml($val, $now_lang);
			}
			return $new_object;	
		}
		
			return $string;
	}
}

if(!function_exists('is_param_post_ml')){
	function is_param_post_ml($name){
		if(isset($_POST[$name])){
			return $_POST[$name];
		} else {
			$arg = '';
			$langs = get_langs_ml();
			foreach($langs as $key){
				$val = is_param_post($name.'_'.$key);
				if($val){
					$arg .= '['. $key .':]'. $val .'[:'. $key .']';
				}	
			}
			return $arg;
		}
	} 
}

if(!function_exists('is_param_get_ml')){
	function is_param_get_ml($name){
		if(isset($_GET[$name])){
			return $_GET[$name];
		} else {
			$arg = '';
			$langs = get_langs_ml();
			foreach($langs as $key){
				$val = is_param_get($name.'_'.$key);
				if($val){
					$arg .= '['. $key .':]'. $val .'[:'. $key .']';
				}	
			}
			return $arg;
		}
	} 
}

if(!function_exists('lang_locale')){
	$data = premium_rewrite_data();
	$super_base = $data['super_base'];
	
	if(is_admin() or $super_base == 'wp-login.php'){
		if(isset($pn_lang['admin_lang']) and $pn_lang['admin_lang']){
			$pn_current_lang = $pn_lang['admin_lang'];
		} 
	} else {	
		if(isset($pn_lang['site_lang']) and $pn_lang['site_lang']){
			$pn_current_lang = $pn_lang['site_lang'];
		} 

		if(is_ml()){ 		
			$http = 'http://'; if(is_ssl()){ $http = 'https://'; }
			$url_site = $http . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];				
			$lprefix = '';
			$delprefix = '';
			$getlang = is_lang_prefix(is_param_get('lang'));
			if($getlang){
				$lprefix = $getlang;
				$rew_uri = 0;
			} else {
				$rew_uri = 1;
				$uri = str_replace(get_site_url_or(), '', $url_site);
				$exuri = explode('/',$uri);
				if(isset($exuri[1]) and is_lang_prefix($exuri[1])){
					$lprefix = $exuri[1];
					$delprefix = '/'.$exuri[1];
				}
				if(isset($exuri[2])){
					$delprefix .= '/';
				}			
			}
			if($lprefix){
				$langs = get_langs_ml();	
				foreach($langs as $lang){
					$key_lang = get_lang_key($lang);
					if($key_lang == $lprefix){	
						$pn_current_lang = $lang;				
						if($rew_uri){
							$replace_uri = str_replace($delprefix,'',$_SERVER['REQUEST_URI']);
							$_SERVER['REQUEST_URI'] = '/'.$replace_uri;
						}
						break;
					}
				}
			} 
		} 
	} 

	add_filter('locale','lang_locale',1);
	function lang_locale($locale){
	global $pn_current_lang;	
		
		if($pn_current_lang){
			return $pn_current_lang;
		}

		return pn_strip_input($locale);
	} 
}