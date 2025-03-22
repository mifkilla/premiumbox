<?php 
if (session_id() === ''){
	session_start();
}

if( !defined('ABSPATH')){ exit(); }

if(!function_exists('pn_php_vers')){
	function pn_php_vers(){
		$php_vers_arr = explode('.',phpversion());
		$vers = is_isset($php_vers_arr, 0).'.'.is_isset($php_vers_arr, 1);
		if($vers == '7.0'){ $vers = '5.6'; }
		return $vers;
	}
} 

if(!function_exists('get_premium_script')){
	function get_premium_script(){
		$url = plugin_basename(__FILE__);
		$parts = explode('/', $url);
		$plugin_folder = $parts[0];
		
		return $plugin_folder;
	}
}

if(!function_exists('get_premium_url')){
	function get_premium_url(){
		return str_replace('includes/','', plugin_dir_url( __FILE__ ));
	}
}

if(!function_exists('get_premium_dir')){
	function get_premium_dir(){
		return str_replace('includes/','',plugin_dir_path( __FILE__ ));
	}
}

if(!function_exists('pn_create_nonce')){
	function pn_create_nonce($nonce=''){
		$key1 = pn_define('AUTH_SALT');
		$key2 = pn_define('NONCE_SALT');
		$nonce = intval($nonce);
		if($nonce == 1){
			return mb_substr(md5($key1 . $key2 . session_id()), 0, 10);
		} else {
			return mb_substr(md5($key1 . session_id() . $key2), 0, 12);
		}
	}
}

if(!function_exists('pn_verify_nonce')){
	function pn_verify_nonce($word, $nonce=''){
		$word = pn_string($word);
		if(pn_create_nonce($nonce) == $word){
			return 1;
		} else {
			return 0;
		}
	}
}

if(!function_exists('is_ssl_url')){
	function is_ssl_url($url){
		if(is_ssl()){
			$url = str_replace('http://','https://',$url);
		} else {
			$url = str_replace('https://','http://',$url);
		}
		return $url;
	}
}

if(!function_exists('get_pn_action')){
	function get_pn_action($action, $method='post'){ 
	global $or_site_url;
		
		$link = $or_site_url .'/premium_action-' . pn_strip_input($action);
		
		if(defined('PREMIUM_PHYSICAL_FILES')){
			$link .= '.php';
		} else {
			$link .= '.html';
		}
		
		$link .= '?meth='. $method .'&yid='. pn_create_nonce(0) . '&ynd=0';
		
		if(function_exists('is_ml') and is_ml()){
			$link .= '&lang='. get_lang_key(get_locale());
		}		
		
		return $link;
	}
}

if(!function_exists('pn_quicktags_script')){
	function pn_quicktags_script($screen_id){
		global $or_site_url;
		
		$link = $or_site_url .'/premium_quicktags';
	
		if(defined('PREMIUM_PHYSICAL_FILES')){
			$link .= '.php';
		} else {
			$link .= '.js';
		}
		
		$link .= '?place='. $screen_id;
		return $link;
	}
}

if(!function_exists('get_safe_url')){
	function get_safe_url($url){
		global $or_site_url;
		
		$sdata = parse_url($or_site_url);
		$list_safe_url = apply_filters('list_safe_url', array(is_isset($sdata,'host')));
	
		$data = parse_url($url);
		$link_url = trim(is_isset($data,'host')); 
		
		$new_url = $or_site_url;
		if(strlen($link_url) < 1 or in_array($link_url, $list_safe_url)){
			$new_url = $url;
		}
		
		$new_url = str_replace('return_url=','rtn_url=', $new_url);
		return $new_url;
	}
}

if(!function_exists('premium_table_list')){
	function premium_table_list(){
		$page = pn_strip_input(is_param_get('page'));
		$class_name = $page . '_Table_List';
		if(class_exists($class_name)){
			$table = new $class_name();
			$table->display();
		} else {
			echo 'Class not found';
		}
	}	
}

if(!function_exists('get_admin_action')){
	function get_admin_action(){
		$action = false;
		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ){
			$action = $_REQUEST['action'];
		}
		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] ){
			$action = $_REQUEST['action2'];
		}	
		return $action;
	}
}

if(!function_exists('only_post')){	
	function only_post(){
		if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
			header('Allow: POST');
			header('HTTP/1.1 405 Method Not Allowed');
			header('Content-Type: text/plain');
			exit;
		}		
	}
}

if(!function_exists('pn_only_caps')){
	function pn_only_caps($caps, $method=''){
		$caps = (array)$caps;
		$method = trim(is_param_post('form_method'));
		if($method != 'ajax'){ $method = 'display'; }
		$dopusk = 0;
		foreach($caps as $cap){
			if(current_user_can($cap)){
				$dopusk = 1;
				break;
			}
		}
		if(!$dopusk){
			if($method == 'ajax'){
				$log = array();
				$log['status'] = 'error';
				$log['status_code'] = '1'; 
				$log['status_text']= __('Error! Insufficient privileges','premium');
				echo json_encode($log);
				exit;				
			} else {
				pn_display_mess(__('Error! Insufficient privileges','premium'));				
			}
		}
	}
}

if(!function_exists('premium_rewrite_data')){
	function premium_rewrite_data(){
		global $or_site_url;
		
		$site_url = trailingslashit($or_site_url);
		$schema = 'http://';
		if(is_ssl()){
			$schema = 'https://';
		}
		$current_url = $schema . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];	
		$request_url = str_replace( $site_url, '', $current_url );
		$request_url = str_replace('index.php/', '', $request_url);	
		$url_parts = explode( '?', $request_url);
		$base = $url_parts[0];
		$base = rtrim($base,"/");
		$exp = explode( '/', $base);
		$super_base = end($exp);		
		$data = array(
			'site_url' => $site_url,
			'current_url' => $current_url,
			'base' => $base,
			'super_base' => $super_base,
		);
		return $data;
	}
}

if(!function_exists('pn_set_wp_admin')){
	function pn_set_wp_admin(){
		if(!defined('WP_ADMIN')){
			define('WP_ADMIN', true);
		} 		
	}
}

if(!function_exists('set_admin_pointer')){
	function set_admin_pointer(){
		$data = premium_rewrite_data();
		$super_base = $data['super_base'];
		$admin_pages = array('premium_post.html','premium_post.php','premium_quicktags.js','premium_quicktags.php');
		if(in_array($super_base, $admin_pages)){
			pn_set_wp_admin();			
		}	
	}
	set_admin_pointer();
}

if(!function_exists('is_isset')){ 
	function is_isset($where, $look){
		if(is_array($where)){
			if(isset($where[$look])){
				return $where[$look];
			} 
		} elseif(is_object($where)) {
			if(isset($where->$look)){
				return $where->$look;
			} 		
		}
			return '';
	}
}

if(!function_exists('is_param_get')){
	function is_param_get($arg){
		if(isset($_GET[$arg])){
			return $_GET[$arg];
		} else {
			return '';
		}
	}
}

if(!function_exists('is_param_post')){
	function is_param_post($arg){
		if(isset($_POST[$arg])){
			return $_POST[$arg];
		} else {
			return '';
		}
	}
}	

if(!function_exists('is_param_req')){
	function is_param_req($arg){
		if(isset($_REQUEST[$arg])){
			return $_REQUEST[$arg];
		} else {
			return '';
		}
	}
}

if(!function_exists('pn_string')){
	function pn_string($text){
		$text = (string)$text;
		$text = trim($text);
		return $text;
	}
}

if(!function_exists('pn_define')){
	function pn_define($name){
		$string = '';
		if(defined($name)){
			$string = constant($name);
		}
		return $string;
	}	
}

if(!function_exists('m_defined')){
	function m_defined($arg){
		if(defined($arg) and !strstr(constant($arg), 'сюда')){
			return trim(constant($arg));
		}
			return '';
	}
}

if(!function_exists('replace_cyr')){
	function replace_cyr($item){
		$iso9_table = array(
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Ѓ' => 'G',
			'Ґ' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Є' => 'YE',
			'Ж' => 'ZH', 'З' => 'Z', 'Ѕ' => 'Z', 'И' => 'I', 'Й' => 'Y',
			'Ј' => 'J', 'І' => 'I', 'Ї' => 'YI', 'К' => 'K', 'Ќ' => 'K',
			'Л' => 'L', 'Љ' => 'L', 'М' => 'M', 'Н' => 'N', 'Њ' => 'N',
			'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
			'У' => 'U', 'Ў' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS',
			'Ч' => 'CH', 'Џ' => 'DH', 'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => 'UU',
			'Ы' => 'YI', 'Ь' => 'UY', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA',
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ѓ' => 'g',
			'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'є' => 'ye',
			'ж' => 'zh', 'з' => 'z', 'ѕ' => 'z', 'и' => 'i', 'й' => 'y',
			'ј' => 'j', 'і' => 'i', 'ї' => 'yi', 'к' => 'k', 'ќ' => 'k',
			'л' => 'l', 'љ' => 'l', 'м' => 'm', 'н' => 'n', 'њ' => 'n',
			'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
			'у' => 'u', 'ў' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
			'ч' => 'ch', 'џ' => 'dh', 'ш' => 'sh', 'щ' => 'shh', 'ь' => '',
			'ы' => 'yi', 'ъ' => "uu", 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
		);
		$new_item = strtr($item, $iso9_table);
		return apply_filters('replace_cyr', $new_item, $item, $iso9_table);
	}
}

if(!function_exists('pn_strip_input')){
	function pn_strip_input($item){	
		if(is_array($item) or is_object($item)){ return ''; }
		
		$item = trim(esc_html(strip_tags(stripslashes($item))));
		
		$pn_strip_input = array(
			'select' => 'sеlect',
			'insert' => 'insеrt',
			'union' => 'uniоn',
			'loadfile' => 'lоadfile',
			'load_file' => 'lоad_file',
			'outfile' => 'оutfile',
			'cookie' => 'coоkie',
			'concat' => 'cоncat',
			'update' => 'updаte',
			'eval' => 'еval',
			'base64' => 'bаse64',
			'delete' => 'dеlete',
			'truncate' => 'truncаte',
			'replace' => 'rеplace',
			'infile' => 'infilе',
			'handler' => 'hаndler',
			'include' => 'inсlude',
			'script' => 'sсript',
		);
		
		$pn_strip_input = apply_filters('pn_strip_input', $pn_strip_input);
		$pn_strip_input = (array)$pn_strip_input;
		foreach($pn_strip_input as $key => $value){
			$item = preg_replace("/\b({$key})\b/iu", $value, $item);
		}
		
		return $item;
	}
}

if(!function_exists('pn_strip_text')){
	function pn_strip_text($item){
		if(is_array($item) or is_object($item)){ return ''; }
		
		$item = trim(stripslashes($item));
		$allow_tag = apply_filters('pn_allow_tag','<strong>,<em>,<a>,<del>,<ins>,<code>,<img>,<h1>,<h2>,<h3>,<h4>,<h5>,<b>,<i>,<table>,<tbody>,<thead>,<tr>,<th>,<td>,<span>,<p>,<div>,<ul>,<li>,<ol>,<center>,<br>,<blockquote>,<meta>');
		$allow_tag = trim($allow_tag);
		if($allow_tag){
			$item = strip_tags($item, $allow_tag);
		} else {
			$item = strip_tags($item);
		}
		
		$pn_strip_text = array(
			'select' => 'sеlect',
			'insert' => 'insеrt',
			'union' => 'uniоn',
			'loadfile' => 'lоadfile',
			'load_file' => 'lоad_file',
			'outfile' => 'оutfile',
			'cookie' => 'coоkie',
			'concat' => 'cоncat',
			'update' => 'updаte',
			'eval' => 'еval',
			'base64' => 'bаse64',
			'delete' => 'dеlete',
			'truncate' => 'truncаte',
			'replace' => 'rеplace',
			'infile' => 'infilе',
			'handler' => 'hаndler',
			'include' => 'inсlude',
			'script' => 'sсript',
		);
		
		$pn_strip_text = apply_filters('pn_strip_text', $pn_strip_text);
		$pn_strip_text = (array)$pn_strip_text;
		foreach($pn_strip_text as $key => $value){
			$item = preg_replace("/\b({$key})\b/iu", $value ,$item);
		}
		
		return $item;
	}
}

if(!function_exists('pn_strip_input_array')){
	function pn_strip_input_array($array){
		$new_array = array();
		if(is_array($array)){
			foreach($array as $key => $val){
				if(is_array($val)){
					$new_array[$key] = pn_strip_input_array($val);
				} else {
					$new_array[$key] = pn_strip_input($val);
				}
			}
		}
			return $new_array;
	}
}

if(!function_exists('pn_strip_text_array')){
	function pn_strip_text_array($array){
		$new_array = array();
		if(is_array($array)){
			foreach($array as $key => $val){
				if(is_array($val)){
					$new_array[$key] = pn_strip_text_array($val);
				} else {
					$new_array[$key] = pn_strip_text($val);
				}
			}
		}
			return $new_array;
	}
}

if(!function_exists('pn_display_mess')){
	function pn_display_mess($title, $text='', $species='error'){
		header('Content-Type: text/html; charset=utf-8');
		
		$title = trim($title);
		$text = trim($text);
		if(!$text){ $text = $title; }
		
		$html = '<html '. get_language_attributes() .'><head><title>'. $title .'</title>'. apply_filters('premium_other_head', '', 'error_message') .'</head><body class="' . join( ' ', get_body_class() ) . '">';
		
		if($species == 'error'){
			$text_html = '<p style="text-align: center; color: #ff0000; padding: 20px 0;">'. $text .'</p>';
		} else {
			$text_html = '<p style="text-align: center; color: green; padding: 20px 0;">'. $text .'</p>';
		}
		
		$html .= apply_filters('premium_display_mess', $text_html, $title, $text, $species);
		$html .= '</body></html>';
		
		echo $html;
		exit;
	}
}

if(!function_exists('get_pn_cookie')){
	function get_pn_cookie($key){
		$key = pn_strip_input($key);
		if(isset($_COOKIE[$key])){
			return pn_strip_input($_COOKIE[$key]);
		} else {
			return '';
		}
	}
}

if(!function_exists('add_pn_cookie')){
	function add_pn_cookie($key, $arg, $time=''){
		$time = intval($time);
		if($time < 1){
			$time = current_time('timestamp') + (365*24*60*60);
		}	
		$key = pn_strip_input($key);
		$arg = pn_strip_input($arg);
		setcookie($key, $arg, $time, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
		$_COOKIE[$key] = $arg;
	}
}

if(!function_exists('get_time_cookie')){
	function get_time_cookie($key){
		$key = pn_strip_input($key);
		if(isset($_COOKIE[$key])){
			$arg = pn_strip_input($_COOKIE[$key]);
			$c_arr = explode('|time:', $arg); 
			$value = is_isset($c_arr, 0);
			$end_time = is_isset($c_arr, 1);
			$now_time = current_time('timestamp');
			if($end_time >= $now_time){
				return $value;
			}				
		} 
			return false;
	}
}

if(!function_exists('add_time_cookie')){
	function add_time_cookie($key, $arg, $time=''){
		$time = intval($time);
		if($time == 0){
			$time = current_time('timestamp') + (365*24*60*60);
		}	
		$cookie_time = current_time('timestamp') + (365*24*60*60);
		$key = pn_strip_input($key);
		$arg = pn_strip_input($arg);
		setcookie($key, $arg . '|time:' . $time, $cookie_time, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
		$_COOKIE[$key] = $arg . '|time:' . $time;
	}
}

if(!function_exists('pn_link')){
	function pn_link($action='', $method='', $nonce=1){
		global $or_site_url;
		
		$nonce = intval($nonce);
		
		$action = trim($action);
		if(!$action){
			$action = pn_strip_input(is_param_get('page'));
		}
		$method = trim($method);
		if($method != 'post'){ $method = 'get'; }
			
		$link = $or_site_url .'/premium_post';
	
		if(defined('PREMIUM_PHYSICAL_FILES')){
			$link .= '.php';
		} else {
			$link .= '.html';
		}
		
		$link .= '?meth='. $method .'&yid='. pn_create_nonce($nonce) . '&ynd=' . $nonce;
		
		if($action){
			$link .= '&pn_action='.$action;	
		}
			
		return $link;
	}
}
	
if(!function_exists('the_pn_link')){	
	function the_pn_link($action='', $method='', $nonce=1){
		$nonce = intval($nonce);
		echo pn_link($action, $method, $nonce);
	}
}

if(!function_exists('get_api_link')){
	function get_api_link($action, $format=''){
		global $or_site_url;
		$format = pn_string($format); if(!$format){ $format = 'html'; }
		
		$link = $or_site_url .'/';
		if(defined('PREMIUM_PHYSICAL_FILES')){
			$link .= 'api.';
		} else {
			$link .= 'api.';
		}
		if(defined('PREMIUM_PHYSICAL_FILES')){
			$link .= 'php';
		} else {
			$link .= $format;
		}			
		$link .= '?api_action=' . $action;
		
		return $link;
	}
}

if(!function_exists('get_request_link')){
	function get_request_link($action, $format=''){
		global $or_site_url;
		$format = pn_string($format); if(!$format){ $format = 'html'; }
		
		$link = $or_site_url .'/';
		if(defined('PREMIUM_PHYSICAL_FILES')){
			$link .= 'premium_request-';
		} else {
			$link .= 'request-';
		}
		$link .= pn_strip_input($action) . '.';
		if(defined('PREMIUM_PHYSICAL_FILES')){
			$link .= 'php';
		} else {
			$link .= $format;
		}			
		
		return $link;
	}
}

if(!function_exists('get_mlink')){
	function get_mlink($action){
		global $or_site_url;
		
		$link = $or_site_url .'/merchant-'. pn_strip_input($action);
		
		if(defined('PREMIUM_PHYSICAL_FILES')){
			$link .= '.php';
		} else {
			$link .= '.html';
		}		
		
		return $link;
	}
}

if(!function_exists('pn_maxf_mb')){
	function pn_maxf_mb($text, $length){
		$text = pn_string($text);
		$length = intval($length);
		if(mb_strlen($text) > $length){
			return mb_substr($text, 0, $length);
		}
			return $text;
	}
}

if(!function_exists('pn_maxf')){
	function pn_maxf($text, $length){
		$text = pn_string($text);
		$length = intval($length);
		if(strlen($text) > $length){
			return substr($text,0,$length);
		}
			return $text;
	}
}

if(!function_exists('jserror_js_error_response')){
	add_action('pn_js_error_response', 'jserror_js_error_response');
	function jserror_js_error_response($type){ 
	?>
		console.log('<?php _e('Error text','premium'); ?>, text1: ' + res2 + ',text2:' + res3);
		for (key in res) {
			console.log(key + ' = ' + res[key]);
		}
	<?php
	}
} 

if(!function_exists('jserror_js_alert_response')){
	add_action('pn_js_alert_response', 'jserror_js_alert_response');
	function jserror_js_alert_response(){
	?>
		if(res['status_text']){
			alert(res['status_text']);
		}
	<?php
	}
}

if(!function_exists('get_copy_date')){	
	function get_copy_date($year){
		$time = current_time('timestamp');
		$y = date('Y', $time);
		if($year != $y and $year < $y){
			return $year.'-'.$y;
		} else {
			return $y;
		}
	}
}

if(!function_exists('get_replace_arrays')){
	function get_replace_arrays($array, $content, $show=0){
		$arr_key = $arr_value = array();
		if(is_array($array)){
			foreach($array as $key => $value){
				$arr_key[] = $key;
				$arr_value[] = $value;
			}
		}
		$content = str_replace($arr_key, $arr_value, $content);
		
		if($show != 1){
			$content = preg_replace("!\[(.*?)\]!si", '', $content);
		}
		return $content;
	}
}

if(!function_exists('pn_strip_symbols')){
	function pn_strip_symbols($txt, $symbols=''){	
		if(is_array($txt) or is_object($txt)){ return ''; }
		$symbols = preg_quote($symbols);
		$txt = preg_replace("/[^A-Za-z0-9$symbols]/", '', $txt);
		return $txt;
	}
}

if(!function_exists('strstr_array')){
	function strstr_array($string, $arr_word){
		$string = trim($string);
		if(is_array($arr_word)){
			foreach($arr_word as $word){
				if(strstr($string, $word)){
					return 1;
				}	
			}
		}
		return 0;
	}		
}

if(!function_exists('is_sum')){ 
	function is_sum($sum, $cs=12, $mode='standart'){
		$sum = pn_string($sum);
		$sum = str_replace(',','.',$sum);
		$sum = preg_replace( '/[^0-9-.E]/', '', $sum);
		$cs = apply_filters('is_sum_cs', $cs);
		$cs = intval($cs); if($cs < 0){ $cs = 0; }	
		if($sum){
			
			if(strstr($sum, 'E')){
				$sum = sprintf("%0.20F",$sum);
				$sum = rtrim($sum,'0');
			}
			
			$s_arr = explode('.', $sum);
			$s_ceil = trim(is_isset($s_arr, 0));
			$s_double = trim(is_isset($s_arr, 1));
			$cs_now = mb_strlen($s_double);
			
			if($cs > $cs_now){
				$cs = $cs_now;
			}
			
			if($mode == 'standart'){
				$new_sum = sprintf("%0.{$cs}F",$sum);
			} elseif($mode == 'up'){
				$new_sum = $s_ceil.'.'.mb_substr($s_double,0,$cs);
				$new_sum = rtrim($new_sum,'.');
				$f_num = intval(ltrim(mb_substr($s_double,$cs,$cs_now), '0'));
				if($f_num > 0){
					if($cs < 1){
						$s = 1;
					} else {	
						$s = '0.';
						$nr = 0;
						$ncs = $cs - 1;
						while($nr++<$ncs){
							$s .= '0';
						}
						$s .= '1';
					}
					$new_sum = $new_sum + $s;
				}
			} elseif($mode == 'down'){	
				$new_sum = $s_ceil.'.'.mb_substr($s_double,0,$cs);
			} elseif($mode == 'ceil'){
				$f_num = intval($s_double);
				if($f_num > 0){
					$new_sum = $s_ceil + 1;
				} else {
					$new_sum = $s_ceil;	
				}
			}
			
			if(strstr($new_sum,'.')){
				$new_sum = rtrim($new_sum,'0');
				$new_sum = rtrim($new_sum,'.');
			}
			
			return apply_filters('is_sum', $new_sum, $sum, $cs, $mode);
		} else {
			return '0';
		}
		
		return $sum;
	}
}

if(!function_exists('is_admin_newurl')){
	function is_admin_newurl($item){
		$item = pn_string($item);
		$new_item = pn_strip_symbols(replace_cyr($item));
		if (preg_match("/^[a-zA-z0-9]{3,250}$/", $new_item, $matches)) {
			$new_item = strtolower($new_item);
		} else {
			$new_item = '';
		}
		return apply_filters('is_admin_newurl', $new_item, $item);
	}
}

if(!function_exists('is_user')){
	function is_user($item){
		$item = pn_string($item);
		if (preg_match("/^[a-zA-z0-9]{3,30}$/", $item, $matches )) {
			$new_item = strtolower($item);
		} else {
			$new_item = '';
		}
		return apply_filters('is_user', $new_item, $item);
	}	
}

if(!function_exists('is_password')){
	function is_password($item){
		$item = pn_string($item);
		if (strlen($item) > 3 and strlen($item) < 50) {
			$new_item = $item;
		} else {
			$new_item = '';
		}
		return apply_filters('is_password', $new_item, $item);
	}
}

if(!function_exists('pn_array_unset')){
	function pn_array_unset($array, $key){
		if(is_array($key)){
			foreach($key as $key_k){
				if(isset($array[$key_k])){
					unset($array[$key_k]);
				}
			}
		} else {	
			if(isset($array[$key])){
				unset($array[$key]);
			}
		}	
		return $array;
	}
}

if(!function_exists('list_checks_top')){
	function list_checks_top($lists, $m_arr){
		$new_lists = array();
		foreach($m_arr as $m){
			if(isset($lists[$m])){
				$new_lists[$m] = $lists[$m];
			}
		}
		foreach($lists as $list_k => $list_v){
			if(!in_array($list_k, $m_arr)){
				$new_lists[$list_k] = $list_v;
			}
		}
		return $new_lists;
	}
}

if(!function_exists('pn_array_sort')){ 
	function pn_array_sort($array, $key='', $order='asc', $type='text'){
		$key = pn_string($key);
		$order = pn_string($order);
		$type = pn_string($type);
		$order = strtolower($order);
		if($order != 'asc'){ $order = 'desc'; }
		if($key){
			$d_array = array();
			foreach($array as $array_key => $array_value){
				$d_array[$array_key] = is_isset($array_value, $key);
			}
			if($order == 'asc'){
				if($type == 'num'){
					asort($d_array, SORT_NUMERIC);
				} else {	
					asort($d_array);
				}
			} else {
				if($type == 'num'){
					arsort($d_array, SORT_NUMERIC);
				} else {	
					asort($d_array);
				}					
			}
			$new_array = array();
			foreach($d_array as $d_array_key => $d_array_value){
				$new_array[$d_array_key] = $array[$d_array_key];
			}
			return $new_array;
		}
		return $array;
	}
}

if(!function_exists('uniq_data_key')){
	function uniq_data_key($script, $item, $r=1){
		$r = intval($r);
		if(isset($item[$script])){
			$r++;
			return uniq_data_key($script.$r, $item, $r);
		} else {
			return $script;
		}
	}
}

if(!function_exists('pn_header_lastmodifier')){
	function pn_header_lastmodifier($time=''){
		$lastmodified_unix = intval($time);
		if(!$lastmodified_unix){ $lastmodified_unix = current_time('timestamp'); }
		$lastmodified = gmdate("D, d M Y H:i:s \G\M\T", $lastmodified_unix);
		
		$IfModifiedSince = 0;
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
			$IfModifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
		}
		if($IfModifiedSince >= $lastmodified_unix){
			header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
			exit;
		}
		header('Last-Modified: '. $lastmodified);		
	}
}

if(!function_exists('file_safe_include')){
	function file_safe_include($path){
		$page_include = $path . ".php";
		if(file_exists($page_include)){
			include_once($page_include);
		}
	}
}

if(!function_exists('check_array_map')){
	function check_array_map($array, $map){
		if(is_array($array) and is_array($map)){
			$new_array = array();
			foreach($map as $map_key){
				$new_array[$map_key] = is_isset($array, $map_key);
			}
			return $new_array;
		}
		return $array;
	}
}

if(!function_exists('set_extension_data')){
	function set_extension_data($path, $map=''){
		$new_data = array();
		if(is_file($path .'.php')){
			include($path .'.php');
			if(isset($marr) and is_array($marr)){
				if(is_array($map)){
					foreach($map as $val){
						if(isset($marr[$val])){
							$new_data[$val] = trim($marr[$val]); 
						}
					}	
				} else {
					return $marr;
				}
			} 
		}
		
		return $new_data;
	}
}

if(!function_exists('add_phpf_data')){	
	function add_phpf_data($string){
		$file_data = '<?php /*';
		$file_data .= $string;
		$file_data .= '*/ ?>';
		return $file_data;
	}
}

if(!function_exists('get_phpf_data')){	
	function get_phpf_data($data){
		$data = str_replace(array('<?php /*','*/ ?>'),'', $data);
		return $data;
	}
}

if(!function_exists('premium_encrypt')){
	function premium_encrypt($txt, $hash=''){
		$hash = trim($hash);
		if(strlen($hash) < 1){
			$hash = m_defined('PN_HASH_KEY');
		}
		if(function_exists('openssl_encrypt') and $hash){
			$cipher="AES-128-CBC";
			$ivlen = openssl_cipher_iv_length($cipher);
			$iv = openssl_random_pseudo_bytes($ivlen);
			$ciphertext_raw = openssl_encrypt($txt, $cipher, $hash, OPENSSL_RAW_DATA, $iv);
			$hmac = hash_hmac('sha256', $ciphertext_raw, $hash, true);
			$ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
			return 'pnhash:' . $ciphertext;
		}
		return $txt;
	}
}

if(!function_exists('premium_decrypt')){
	function premium_decrypt($txt, $hash=''){
		$hash = trim($hash);
		if(strlen($hash) < 1){
			$hash = m_defined('PN_HASH_KEY');
		}
		if(strstr($txt, 'pnhash:') and function_exists('openssl_decrypt') and $hash){
			$ciphertext = str_replace('pnhash:','', $txt);
			$c = base64_decode($ciphertext);
			$cipher="AES-128-CBC";
			$ivlen = openssl_cipher_iv_length($cipher);
			$iv = substr($c, 0, $ivlen);
			$sha2len=32;
			$hmac = substr($c, $ivlen, $sha2len);
			$ciphertext_raw = substr($c, $ivlen+$sha2len);
			$plaintext = openssl_decrypt($ciphertext_raw, $cipher, $hash, OPENSSL_RAW_DATA, $iv);
			$calcmac = hash_hmac('sha256', $ciphertext_raw, $hash, true);
			if (hash_equals($hmac, $calcmac))
			{
				return $plaintext;
			}
		}
		return $txt;
	}
}

if(!function_exists('get_extension_num')){
	function get_extension_num($name){
		$num = preg_replace( '/[^0-9]/', '',$name);
		return $num;	
	}
}

if(!function_exists('set_extandeds')){
	function set_extandeds($plugin, $folder){
		$items = get_option('extlist_' . $folder);
		if(is_array($items)){
			$exts = $items;
			asort($exts);
			foreach($exts as $item){
				$name_for_base = is_extension_name(is_isset($item,'script'));
				if($name_for_base){
					include_extanded($plugin, $folder, $name_for_base);
				}
			}
		}	
	}
}

if(!function_exists('is_valid_credit_card')){
	function is_valid_credit_card($s){
		$s = strrev(preg_replace('/[^\d]/','',$s));

		$sum = 0;
		for ($i = 0, $j = strlen($s); $i < $j; $i++) {
			if (($i % 2) == 0) {
				$val = $s[$i];
			} else {
				$val = $s[$i] * 2;
				if ($val > 9)  $val -= 9;
			}
			$sum += $val;
		}

		return (($sum % 10) == 0);
	}
}

if(!function_exists('card_scheme_detected')){
	function card_scheme_detected($card=''){
		$card = trim($card);
		$scheme = '';
		
		$f = mb_substr($card, 0, 1);
		$t = mb_substr($card, 0, 2);
		if($f == '4'){
			$scheme = 'Visa';
		} elseif($f == '5'){
			$mc_arr = array('51','52','53','54','55');
			if(in_array($t, $mc_arr)){
				$scheme = 'MasterCard';
			} else {
				$scheme = 'Maestro';
			}
		} elseif($f == '2'){
			$scheme = 'Mir';
		} elseif($f == '6'){	
			if($t == '60'){
				$scheme = 'Discover';
			} elseif($t == '62'){
				$scheme = 'China UnionPay';
			} elseif($t == '63' or $t == '67'){
				$scheme = 'Maestro';
			}
		}	
		
		return apply_filters('card_scheme_detected', $scheme, $card, $f, $t);
	}
}

if(!function_exists('pn_admin_prepare_lost')){
	function pn_admin_prepare_lost($lost){
		$losted = array();
		if(is_array($lost)){
			$losted = $lost;
		} elseif(is_string($lost)) {
			$l = explode(',',$lost);
			foreach($l as $lk => $lv){
				$lv = trim($lv);
				if($lv){
					$losted[$lk] = $lv;
				}
			}		
		}
		return $losted;
	}
} 
 
if(!function_exists('pn_admin_filter_data')){
	function pn_admin_filter_data($url='', $lost=''){
		$url = trim($url);
		if(!$url){ $url = is_param_post('_wp_http_referer'); }
		$losted = pn_admin_prepare_lost($lost);
		$n = parse_url($url);
		$data_url = array();
		if(isset($n['query'])){
			parse_str($n['query'], $data_url);
		}
		foreach($losted as $v){
			if(isset($data_url[$v])){
				unset($data_url[$v]);
			}		
		}
		$sign = array();
		$link = is_isset($n, 'path');
		if(is_array($data_url)){
			foreach($data_url as $k => $v){  
				$sign[] = $k .'='. esc_html($v);
			}
		}	
		if(count($sign) > 0){
			$link .= '?' . join('&', $sign);
		}
		return $link;
	}
}

if(!function_exists('pn_ind')){
	function pn_ind(){
		$arr = array();
		$arr['ind'] = 1;
		$arr['error'] = '';
		return $arr;
	}
}

if(!function_exists('is_older_browser')){
	function is_older_browser(){
		$older_browser = false;
		
		if(isset($_SERVER['HTTP_USER_AGENT'])){
			if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0') ){
				$older_browser = true;
			} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0') ){
				$older_browser = true;
			} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0') ){
				$older_browser = true;
			} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0') ){
				$older_browser = true;
			}
		}
					
		$older_browser = apply_filters('is_older_browser',$older_browser);
		return $older_browser;
	}
}

if(!function_exists('get_browser_name')){
	function get_browser_name($user_agent, $unknown='Unknown'){
		
		$user_agent = (string)$user_agent;
		if (strpos($user_agent, "Firefox") !== false){
			$browser = 'Firefox';
		} elseif (strpos($user_agent, "OPR") !== false){
			$browser = 'Opera';
		} elseif (strpos($user_agent, "Chrome") !== false){
			$browser = 'Chrome';
		} elseif (strpos($user_agent, "MSIE") !== false){
			$browser = 'Internet Explorer';
		} elseif (strpos($user_agent, "Safari") !== false){
			$browser = 'Safari';
		} else { 
			$browser = $unknown; 
		}
		
		$browser = apply_filters('get_browser_name', $browser, $user_agent);
		return $browser;
	}
}

if(!function_exists('pn_site_name')){
	function pn_site_name(){
		return pn_strip_input(get_bloginfo('sitename'));
	}
}

if(!function_exists('get_pn_date')){
	function get_pn_date($date, $format='d.m.Y'){
		$date = pn_strip_input($date);
		if($date and $date != '0000-00-00'){
			$time = strtotime($date);
			return date($format, $time);
		}
	}
}	

if(!function_exists('get_pn_time')){
	function get_pn_time($date, $format='d.m.Y H:i'){
		$date = pn_strip_input($date);
		if($date and $date != '0000-00-00 00:00:00'){
			$time = strtotime($date);
			return date($format, $time);
		}
	}
}

if(!function_exists('is_pn_date')){
	function is_pn_date($date, $zn='d.m.Y'){
		$date = pn_string($date);
		$zn = preg_quote($zn);
		if (preg_match("/^[0-9]{1,2}[$zn]{1}[0-9]{1,2}[$zn]{1}[0-9]{4}$/", $date, $matches )) {
			return $date;
		} 
			return '';	
	}
}

if(!function_exists('pn_sfilter')){
	function pn_sfilter($arg){
		$arg = trim((string)$arg);
		$arg = str_replace('%','',$arg);
		return $arg;
	}
}

if(!function_exists('pn_real_ip')){ 
	function pn_real_ip(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ips = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ips = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ips = $_SERVER['REMOTE_ADDR'];
		}
		
		$ips_arr = explode(',',$ips);
		$ip = trim($ips_arr[0]);
		$ip = preg_replace( '/[^0-9a-fA-F:.]/', '',$ip);
		$ip = pn_maxf($ip, 140);
		
		return apply_filters('pn_real_ip', $ip, $ips_arr);	
	}
}

if(!function_exists('pn_has_ip')){
	function pn_has_ip($list_ip, $ip=''){
		$ip = pn_string($ip);
		if(!$ip){
			$ip = pn_real_ip();
		}	
		$tip = explode('.',$ip);
		$list_ip = pn_string($list_ip);
		if($ip and $list_ip){
			$items = array();
			if(strstr($list_ip, '[d]')){
				if(preg_match_all('/\[d](.*?)\[\/d]/s',$list_ip, $match, PREG_PATTERN_ORDER)){
					$items = $match[1];
				}				
			} else {
				$items = explode("\n",$list_ip);
			}
			foreach($items as $item_ip){
				$item_ip = trim($item_ip);
				if($item_ip){
					$item_ip_arr = explode('.',$item_ip);
					if(count($item_ip_arr) > 0){
						$yes = 1;
						foreach($item_ip_arr as $k => $v){
							if(strlen($v) > 0){
								if($v != is_isset($tip, $k)){
									$yes = 0;
								}
							}
						}
						if($yes == 1){
							return 1;
						}
					}
				}
			}
		}
			return 0;
	}	
}

if(!function_exists('pn_array_insert')){
	function pn_array_insert($array, $key, $new_array='', $method=''){
		$key = pn_string($key);
		$method = pn_string($method); if($method != 'before'){ $method = 'after'; }
		if(is_array($array) and is_array($new_array)){
			$set_array = array();
			if($key and isset($array[$key])){
				foreach($array as $array_key => $array_value){
					$array_key = pn_string($array_key);
					if($array_key == $key and $method == 'before'){
						foreach($new_array as $new_array_key => $new_array_value){
							$set_array[$new_array_key] = $new_array_value;
						}
					}				
					$set_array[$array_key] = $array_value;
					if($array_key == $key and $method == 'after'){
						foreach($new_array as $new_array_key => $new_array_value){
							$set_array[$new_array_key] = $new_array_value;
						}
					}
				}
			} else {
				$set_array = $array;
				foreach($new_array as $new_array_key => $new_array_value){
					$set_array[$new_array_key] = $new_array_value;
				}	
			}
				return $set_array;
		}
		
		return $array;
	}
}

if(!function_exists('get_rand_word')){
	function get_rand_word($count=4, $vid=1){
		$count = intval($count);
		if($count < 1){ $count = 4; }
		
		$vid = intval($vid);
		if($vid == 1){
			$arr = 'q,w,e,r,t,y,u,i,o,p,a,s,d,f,g,h,j,k,l,z,x,c,v,b,n,m';
		} else {
			$arr = '1,2,3,4,5,6,7,8,9,0';
		}
		$array = explode(',',$arr);
		
		$r=0;
		$word = '';
		while($r++<$count){
			shuffle($array);
			$word .= mb_strtoupper($array[0]);
		}
		
		return $word;
	}
}

if(!function_exists('pn_allow_uv')){
	function pn_allow_uv($key){
		$plugin = get_plugin_class();
		$uf = $plugin->get_option('user_fields');
		return intval(is_isset($uf, $key));
	}
}

if(!function_exists('pn_change_uv')){
	function pn_change_uv($key){
		$plugin = get_plugin_class();
		$uf = $plugin->get_option('user_fields_change');
		return intval(is_isset($uf, $key));
	}
}

if(!function_exists('strip_uf')){
	function strip_uf($value, $filter){
		$value = trim($value);
		$new_value = '';
		if($filter == 'user_phone'){
			$new_value = is_phone($value);
		} elseif($filter == 'user_email'){
			$new_value = is_email($value);
		} elseif($filter == 'user_website'){
			$new_value = esc_url($value);	
		} else {
			$new_value = pn_strip_input($value);
		}		
		$new_value = pn_maxf_mb($new_value, 500);
		$new_value = apply_filters('strip_uf', $new_value, $value, $filter);
		return $new_value;
	}
}

if(!function_exists('is_phone')){
	function is_phone($phone){
		$phone = pn_string($phone);
		$new_phone = preg_replace( '/[^(+)0-9]/', '',$phone);
		$new_phone = apply_filters('is_phone', $new_phone, $phone);
		return $new_phone;
	}
}

if(!function_exists('get_parallel_error_output')){
	function get_parallel_error_output(){
		return apply_filters('parallel_error_output', 0);
	}
}

if(!function_exists('get_curl_parser')){
	function get_curl_parser($url, $options=array(), $place='', $pointer='', $pointer2=''){
		$options = (array)$options;
		$arg = array(
			'output' => '',
			'err' => 1,
			'info' => '',
			'code' => '',
		);
		if($ch = curl_init()){
			$curl_options = array(
				CURLOPT_URL => $url,
				//CURLOPT_HEADER => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_REFERER => '',
				CURLOPT_TIMEOUT => 20,
				CURLOPT_CONNECTTIMEOUT => 20,
				CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36 OPR/60.0.3255.27",
			);
			foreach($options as $k => $v){
				$curl_options[$k] = $v;
			}
			$curl_options = apply_filters('get_curl_parser', $curl_options, $place, $pointer, $pointer2);
			curl_setopt_array($ch, $curl_options);
			
			$arg['output'] = curl_exec($ch);
			$arg['err'] = curl_errno($ch);
			$arg['info'] = curl_getinfo($ch);
			$arg['code'] = is_isset($arg['info'], 'http_code');

			curl_close($ch);
		} else {
			$arg['err'] = '901';
		}
		
		return $arg;
	}
}

if(!function_exists('is_extension_active')){
	function is_extension_active($name, $folder, $extension_name){
		$active = 0;
		
		$extended = get_option($name);
		if(!is_array($extended)){ $extended = array(); }
		
		if(isset($extended[$folder])){
			if(isset($extended[$folder][$extension_name])){
				$active = 1;
			}
		}
		
		return $active;
	}
}

if(!function_exists('is_extension_name')){
	function is_extension_name($name){
		$name = pn_string($name);
		if (preg_match("/^[a-zA-z0-9_]{1,250}$/", $name, $matches )) {
			return $name;
		} 
			return '';
	}
}

if(!function_exists('get_extension_file')){
	function get_extension_file($file){
		return wp_normalize_path(dirname($file));
	}
}

if(!function_exists('get_extension_name')){
	function get_extension_name($path){
		$name = explode('/',$path);
		$name = end($name);
		$name = is_extension_name($name);
		if(strstr($path,'/themes/')){
			$name .= '_theme';
		}
		return $name;
	}
}

if(!function_exists('include_extanded')){
	function include_extanded($plugin, $folder, $name){
		global $pnexts;
		
		if(!isset($pnexts[$folder][$name])){
			$pnexts[$folder][$name] = $name;
		
			if(strpos($name, '_theme')){
				$name = str_replace('_theme', '', $name);
				$file = get_template_directory() . '/'. $folder .'/'. $name .'/index.php';
			} else {
				$file = $plugin->plugin_dir . $folder .'/'. $name .'/index.php';	
			}

			if(file_exists($file)){
				include_once($file);
			}	
		}
	}
}

if(!function_exists('accept_extended_data')){
	function accept_extended_data($file){
		$data = array(
			'version' => '0.1',
			'description' => '',
			'category' => '',
			'cat' => '',
			'dependent' => '',
			'old_names' => '',
			'new' => 0,
		);
		
		$content = @file_get_contents($file, false, null, 0, 1500);
		$content = trim($content);
		if($content){
			$content = explode("/*", $content);
			if(isset($content[1])){
				$content = explode("*/", $content[1]);
				$content = explode("\n", $content[0]);
				foreach($content as $con){
					$con = trim($con);
					if($con){
						$item = explode(":", $con);
						$val_name = '';
						$val = array();
						$r=0;
						foreach($item as $arg){ $r++;
							if($r==1){
								$val_name = trim(strtolower($arg));
							} else {
								$val[] = $arg;
							}
						}
						$val = trim(join(':',$val));
						
						if($val){
							$val_arr = array('title','version','description','cat', 'category', 'dependent','old_names', 'new');
							if(in_array($val_name, $val_arr)){
								$data[$val_name] = $val;
							} 
						}
					}
				}
			}
		}

		return $data;
	}
}

if(!function_exists('extended_time_deactive')){
	function extended_time_deactive($extended_last, $name, $old_names=''){
		$times = array();
		if(isset($extended_last[$name])){
			$times[] = trim($extended_last[$name]);
		}	
		$old_names = explode(',', $old_names);
		foreach($old_names as $oname){
			$oname = trim($oname);
			if($oname){
				if(isset($extended_last[$oname])){
					$times[] = trim($extended_last[$oname]);
				}
			}
		}
		$time_deactive = '';
		if(count($times) > 0){
			$time_deactive = max($times);
		}
		return $time_deactive;
	}
}

if(!function_exists('get_theme_option')){
	function get_theme_option($option_name, $array=''){
		if(!is_array($array)){ $array = array(); }
		$option_name = pn_string($option_name);
	
		$change = get_option($option_name);
		$now_change = array();
		foreach($array as $opt){
			$now_change[$opt] = ctv_ml(is_isset($change,$opt));	 
		}	
	
		return $now_change;
	}
}

if(!function_exists('files_del_dir')){
	function files_del_dir($directory, $type='.png'){
		$type = pn_string($type);
		if(is_dir($directory)){
			foreach(glob($directory . "*" . $type) as $file) {
				@unlink($file);
			}			
		}
	}
}

if(!function_exists('full_del_dir')){
	function full_del_dir($directory){
		if(is_dir($directory)){
			$dir = @opendir($directory);
			while(($file = @readdir($dir))){
				if ( is_file($directory."/".$file)){
					@unlink($directory."/".$file);
				} elseif ( is_dir ($directory."/".$file) && ($file != ".") && ($file != "..")){
					full_del_dir($directory."/".$file);  
				}
			}
			@closedir ($dir);
			@rmdir ($directory);
		}
	}
}

if(!function_exists('get_session_id')){ 
	function get_session_id(){ 
		$session_key = pn_strip_input(get_pn_cookie('premium_session_id'));
		if(!$session_key){
			$session_key = session_id();
		}
		$data = pn_real_ip() . pn_maxf(pn_strip_input(is_isset($_SERVER,'HTTP_USER_AGENT')),300) . $session_key;
		return pn_strip_input(hash_hmac('sha256', $data, mb_substr(AUTH_SALT,0, 10) . mb_substr(NONCE_SALT, 10, 18)));
	}
}

if(!function_exists('is_text')){
	function is_text($arg){
		$arg = pn_string($arg);
		$arg = preg_replace("/[^A-Za-z0-9АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧЩШЭЮЯЪЬфбвгдеёщзийклмнопрстуфхцчшщэюяъъ\n\r\-.,!?$%:;()@ ]/iu", '', $arg);

		return $arg;
	}
}

if(!function_exists('get_check_list')){
	function get_check_list($lists, $name, $class=array(), $max_height='', $search=0){
		$search = intval($search);
		$max_height = intval($max_height);
		if($max_height < 1){ $max_height = 200; }		
		$html = '<div class="checkbox_all_div">';
		$all_ch = 'checked="checked"';
		if(is_array($lists)){
			foreach($lists as $list){
				if(is_isset($list,'checked') == 0){
					$all_ch = '';
				}
			}
		}
		
		if($search == 1){
			$html .= '<div class="checkbox_all_searchdiv"><input type="search" name="" placeholder="'. __('Search...','premium') .'" class="checkbox_all_search" value="" /></div>';
		}
		
		$html .= '<div class="checkbox_all_ins" style="max-height: '. $max_height .'px;">';
		
		$html .= '<div><label style="font-weight: 500;"><input class="checkbox_all" type="checkbox" '. $all_ch .' name="" autocomplete="off" value="0"> <span class="'. is_isset($class, '0') .'">'. __('Check all/Uncheck all','premium') .'</span></label></div>';
		
		if(is_array($lists)){
			foreach($lists as $list){
				$ch = '';
				if(is_isset($list,'checked') == 1){
					$ch = 'checked="checked"';
				}
				$now_name = $name;
				if(isset($list['name'])){
					$now_name = trim($list['name']);
				}
				$search = is_isset($list,'title');
				if(isset($list['search'])){
					$search = $list['search'];
				}	
				$html .= '<div style="padding: 0; margin: 0;" class="checkbox_once_div"><label><input type="checkbox" class="checkbox_once" name="'. $now_name .'" '. $ch .' '. is_isset($list,'atts') .' autocomplete="off" value="'. is_isset($list,'value') .'"> <span class="'. is_isset($class, is_isset($list,'value')) .'" data-s="'. esc_attr($search) .'">'. is_isset($list,'title') .'</span></label></div>';
			}
		}

		$html .= '<div class="premium_clear"></div></div></div>';		
		
		return $html;
	}
}

if(!function_exists('get_caps_name')){
	function get_caps_name($name){
		$name = pn_strip_input($name);
		if($name){
			$newname = mb_strtoupper(mb_substr($name,0,1)).mb_strtolower(mb_substr($name,1,mb_strlen($name)));
			return $newname;
		}
			return $name;
	}
}

if(!function_exists('get_contact')){
	function get_contact($value, $key=''){
		if($key == 'telegram'){
			$value = '<a href="https://t.me/'. pn_strip_input(str_replace('@','', $value)) .'">@'. pn_strip_input(str_replace('@','',$value)) .'</a>';
		} elseif($key == 'viber'){
			$value = '<a href="viber://chat?number='. pn_strip_input($value) .'">'. pn_strip_input($value) .'</a>';
		} elseif($key == 'whatsapp'){
			$value = '<a href="https://api.whatsapp.com/send?phone='. pn_strip_input($value) .'">'. pn_strip_input($value) .'</a>';			
		} elseif($key == 'jabber'){
			$value = '<a href="xmpp:'. pn_strip_input($value) .'">'. pn_strip_input($value) .'</a>';
		} elseif($key == 'skype'){
			$value = '<a href="skype:'. pn_strip_input($value) .'?add" title="'. __('Add to skype','premium') .'">'. pn_strip_input($value) .'</a>';
		} elseif($key == 'email'){
			$value = '<a href="mailto:'. antispambot($value) .'">'. antispambot($value) .'</a>';			
		} else {
			$value = pn_strip_input($value);
		}
		
		return $value;
	}
}

if(!function_exists('get_blog_url')){
	function get_blog_url(){
		$sof = get_option('show_on_front');	
		if($sof == 'page'){
			$blog_url = get_permalink(get_option('page_for_posts'));
		} else {
			$blog_url = get_site_url_ml();
		}		
		return $blog_url;
	}
}

if(!function_exists('get_pn_excerpt')){	
	function get_pn_excerpt($item, $count=15){
		if(function_exists('ctv_ml')){
			$excerpt = pn_strip_text(ctv_ml($item->post_excerpt));
			if($excerpt){
				return $excerpt;
			} else {
				return wp_trim_words(pn_strip_text(ctv_ml($item->post_content)),$count);
			}
		} else {
			$excerpt = pn_strip_text($item->post_excerpt);
			if($excerpt){
				return $excerpt;
			} else {
				return wp_trim_words(pn_strip_text($item->post_content),$count);
			}			
		}		
	}
}

if(!function_exists('get_form_fields')){
	function get_form_fields($form_name='', $place='shortcode'){
		$ui = wp_get_current_user();
		
		$items = array();
		$items = apply_filters($form_name . '_filelds', $items, $ui, $place);
		$items = apply_filters('get_form_filelds', $items, $form_name, $ui, $place);
		
		return $items;
	}
}

if(!function_exists('get_form_replace_array')){
	function get_form_replace_array($form_name='', $prefix='', $place='shortcode'){
		$array = array();
		$array = apply_filters('replace_array_' . $form_name, $array, $prefix, $place);
		return $array;
	}
}

if(!function_exists('prepare_form_fileds')){
	function prepare_form_fileds($items, $filter, $prefix){
		global $form_field_num;
		$form_field_num = intval($form_field_num);
		$form_field_num++;
		
		$ui = wp_get_current_user();
		$html = '';
		if(is_array($items)){
			foreach($items as $name => $data){
				$type = trim(is_isset($data, 'type'));
				$name = trim(is_isset($data, 'name'));
				$title = trim(is_isset($data, 'title'));
				$req = intval(is_isset($data, 'req'));
				$atts = is_isset($data, 'atts');
				if(!is_array($atts)){ $atts = array(); }
				$value = is_isset($data, 'value');
				$tooltip = pn_strip_input(ctv_ml(is_isset($data, 'tooltip')));
				$hidden = intval(is_isset($data, 'hidden'));
				
				$div_class = array(
					'form_field_line' => 'form_field_line',
					$prefix .'_line' => $prefix .'_line',
					'type_'. $type => 'type_'. $type,
					'field_name_'. $name => 'field_name_'. $name,
				);
				if($hidden){
					$div_class['hidden_line'] = 'hidden_line';
				}
				
				$req_html = '';
				if($req){
					$req_html = ' <span class="req">*</span>';
				}
				
				$tooltip_div = '';
				$tooltip_span = '';
				$tooltip_class = '';
				if($tooltip){
					$tooltip_span = '<span class="field_tooltip_label"></span>';
					$div_class['has_tooltip'] = 'has_tooltip';
					$tooltip_div = '<div class="field_tooltip_div"><div class="field_tooltip_abs"></div><div class="field_tooltip">'. $tooltip .'</div></div>';
				}
				
				if($title){
					$div_class['has_title'] = 'has_title';
				}	
				
				if(isset($atts['class'])){
					$atts['class'] .= ' ' . $prefix .'_'. $type;
				} else {
					$atts['class'] = $prefix .'_' . $type;
				}
				
				if(!isset($atts['autocomplete'])){
					$atts['autocomplete'] = 'off';
				} 

				if(isset($atts['id'])){
					unset($atts['id']);
				}
				if(isset($atts['name'])){
					unset($atts['name']);
				}
				if(isset($atts['value'])){
					unset($atts['value']);
				}		

				$input_atts = '';
				foreach($atts as $atts_k => $atts_v){
					$input_atts .= ' ' . esc_attr($atts_k) . '="'. esc_attr($atts_v) .'"';
				}

				$field_id = 'id="form_field_id-'. $form_field_num .'-'. $name .'"';
				
				$line = '
				<div class="'. join(' ', $div_class)  .'">';
					if($title){
						$line .= '<div class="form_field_label '. $prefix .'_label"><label for="form_field_id-'. $form_field_num .'-'. $name .'"><span class="form_field_label_ins">'. $title .''. $req_html .':'. $tooltip_span .'</span></label></div>';
					}
					$line .= '
					<div class="form_field_ins '. $prefix .'_line_ins">
				';
				
				if($type == 'text'){
					$line .= '
					<textarea '. $field_id .' '. $input_atts .' name="'. $name .'">'. $value .'</textarea>							
					';	
				} elseif($type == 'input'){
					$line .= '
					<input type="text" '. $field_id .' '. $input_atts .' name="'. $name .'" value="'. $value .'" />						
					';
				} elseif($type == 'password'){
					$line .= '
					<input type="password" '. $field_id .' '. $input_atts .' name="'. $name .'" value="'. $value .'" />						
					';				
				} elseif($type == 'select'){
					$options = (array)is_isset($data, 'options');
					$line .= '
					<select '. $field_id .' '. $input_atts .' name="'. $name .'">';
						foreach($options as $key => $title){
							$line .= '<option value="'. $key .'" '. selected($value, $key, false) .'>'. $title .'</option>';
						}
					$line .= '		
					</select>												
					';
				}
				
				$line .= '
						'. $tooltip_div .'
						<div class="form_field_errors"><div class="form_field_errors_ins"></div></div>
					</div>';
					
				$line .= '	
					<div class="form_field_clear '. $prefix .'_line_clear"></div>
				</div>
				';
			
				$line = apply_filters('form_field_line', $line, $filter, $data, $prefix, $ui);
				$html .= apply_filters($filter, $line, $data, $prefix, $ui);
			}
		}	
		return $html;
	}
}

if(!function_exists('is_place_url')){
	function is_place_url($url, $class='current'){
		$http = 'http://'; if(is_ssl()){ $http = 'https://'; }
		$url_site = $http . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		if($url == $url_site){
			return $class;
		}
	}
}		

if(!function_exists('get_userpage_pn')){
	function get_userpage_pn($page_id, $class='act'){
		if(is_page($page_id)){
			return $class;
		} else {
			return false;
		}
	}
}	

if(!function_exists('is_pn_page')){ 
	function is_pn_page($page_name){
	global $is_pn_page;
		if(isset($is_pn_page[$page_name])){
			return $is_pn_page[$page_name];
		} else {
			$pages = get_option('the_pages');
			if(isset($pages[$page_name]) and is_page($pages[$page_name])){
				$zn = 1;
			} else {
				$zn = 0;
			}
			$is_pn_page[$page_name] = $zn;
			return $zn;
		}
	}
}

if(!function_exists('get_sklon')){
	function get_sklon($num, $text1, $text2, $text3){

		$num = abs($num);
		$nums = $num % 100;
			 
		if (($nums > 4) && ($nums < 21)) {
			return str_replace('%',$num,$text3);
		}
			
		$nums = $num % 10;
		if (($nums == 0) || ($nums > 4)) {
			return str_replace('%',$num,$text3);
		}	
			
		if ($nums == 1) {
			return str_replace('%',$num,$text1);
		}
			 
		return str_replace('%',$num,$text2);	
	}
}

if(!function_exists('get_month_title')){
	function get_month_title($arg, $months=array()){
		$arg = intval($arg);

		if(!is_array($months) or is_array($months) and count($months) < 7){
			$months = array('',
				'Jan.',
				'Feb.',
				'Mar.',
				'Apr.',
				'May',
				'June',
				'July',
				'Aug.',
				'Sep.',
				'Oct.',
				'Nov.',
				'Dec.'
			);
		}
		
		return is_isset($months,$arg);
	}
}

if(!function_exists('is_status_name')){
	function is_status_name($item){
		$item = pn_string($item);
		$new_item = '';
		if (preg_match("/^[a-zA-z0-9]{3,35}$/", $item, $matches)){
			$new_item = $item;
		} 
		return $new_item;
	}
}

if(!function_exists('get_sum_color')){
	function get_sum_color($sum, $max='bgreen',$min='bred', $zero=''){
		if($sum == 0){
			return '<span class="'. $zero .'">'. $sum .'</span>';
		} elseif($sum > 0){
			return '<span class="'. $max .'">'. $sum .'</span>';
		} else {
			return '<span class="'. $min .'">'. $sum .'</span>';
		}
	}
} 

if(!function_exists('is_out_sum')){
	function is_out_sum($sum, $decimal=12, $place='all'){
		return apply_filters('is_out_sum', $sum, $decimal, $place);
	}
}

if(!function_exists('array_key_first')){
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return '';
    }
}

if(!function_exists('update_pn_meta')){
	function update_pn_meta($table, $id, $key, $value){ 
	global $wpdb;
		
		$id = intval($id);
		if(is_array($value)){
			$value = serialize($value);
		}
		$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix . $table ." WHERE item_id='$id' AND meta_key='$key'");
		if($cc == 0){
			$result = $wpdb->insert($wpdb->prefix . $table, array('meta_value'=>$value, 'item_id'=>$id, 'meta_key'=>$key));	
		} else {
			$result = $wpdb->update($wpdb->prefix . $table, array('meta_value'=>$value), array('item_id'=>$id, 'meta_key'=>$key));
		}
		return $result;
	}
}

if(!function_exists('get_pn_meta')){
	function get_pn_meta($table, $id, $key){
	global $wpdb;
		$id = intval($id);
		$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix . $table ." WHERE item_id='$id' AND meta_key='$key'");
		if(isset($data->meta_value)){
			return maybe_unserialize($data->meta_value);
		} else {
			return false;
		}
	}
}

if(!function_exists('delete_pn_meta')){
	function delete_pn_meta($table, $id, $key){
	global $wpdb;   	
		$id = intval($id);			
		return $wpdb->query("DELETE FROM ".$wpdb->prefix . $table ." WHERE item_id='$id' AND meta_key='$key'");
	}
}

if(!function_exists('create_data_for_bd')){
	function create_data_for_bd($status_arr, $strip=''){
		$join_arr = array();
		if(is_array($status_arr)){
			foreach($status_arr as $st){
				if($strip == 'status'){
					$st = is_status_name($st);
				} elseif($strip == 'int'){
					$st = intval($st);
				} 
				if(strlen($st) > 0){
					$join_arr[] = "'". $st ."'";
				}
			}
		}
		$join_arr = array_unique($join_arr);
		if(count($join_arr) > 0){
			return join(',',$join_arr);
		} else {
			return 0;
		}
	}
}

if(!function_exists('pn_item_status')){
	function pn_item_status($item='', $tb='status'){
		$tb_status = intval(is_isset($item, $tb));
		if($tb_status == 0){
			return ' ('. __('deactive','premium') . ')';
		} elseif($tb_status == 2){
			return ' ('. __('hold','premium') . ')';
		}
		return '';
	}
} 

if(!function_exists('pn_item_basket')){
	function pn_item_basket($item=''){
		$auto_status = intval(is_isset($item, 'auto_status'));
		if($auto_status == 0){
			return ' ('. __('in basket','premium') . ')';
		}
		return '';
	}
} 

if(!function_exists('pn_object_replace')){
	function pn_object_replace($object = '', $array = ''){
		$object_array = (array)$object;
		if(is_array($object_array) and is_array($array)){
			foreach($object_array as $oa_key => $oa_val){
				$object_array[$oa_key] = $oa_val;
			}
			foreach($array as $arr_k => $arr_v){
				$object_array[$arr_k] = $arr_v;
			}
			$object = (object)$object_array;
		}
		return $object;
	}
}

if(!function_exists('pn_db_insert')){
	function pn_db_insert($tbl_name, $sqls_or, $chunk=0){
	global $wpdb;
	
		$chunk = intval($chunk);
		if($chunk < 1){ $chunk = apply_filters('chunk_db_part', 200); }
		
		if(is_array($sqls_or)){
			$sqls_or = array_chunk($sqls_or, $chunk);
			foreach($sqls_or as $sqls){
		
				$query = array();
				$arr = array();
				$keys = array();
				
				foreach($sqls as $sql_data){
					if(is_array($sql_data)){
						foreach($sql_data as $sql_key => $sql_value){
							$keys[$sql_key] = $sql_key;
						}
					}
				} 
				
				$s = -1;
				foreach($sqls as $sql_data){ $s++;
					if(is_array($sql_data)){
						$arr[$s] = array();
						foreach($keys as $key_name){
							$arr[$s][] = "'" . is_isset($sql_data, $key_name) . "'";
						}
						$query[] = "(" . join(',', $arr[$s]) . ")";
					}
				} 
						
				if(count($query) > 0 and count($keys) > 0){			
					$wpdb_query = "INSERT INTO $tbl_name (". join(',',$keys) .") VALUES" . join(', ',$query);
					$wpdb->query($wpdb_query);
				}
				
			}
			
			return 1;
		}
			return 0;
	}
}

if(!function_exists('delete_txtmeta')){
	function delete_txtmeta($folder, $data_id, $plugin=''){
		if($folder){
			$dir = is_isset($plugin,'upload_dir') . $folder . '/';
			
			$my_dir = wp_upload_dir();
			$old_dir = $my_dir['basedir'].'/'. $folder .'/';
			
			$new_file = $dir . $data_id .'.php';
			if(file_exists($new_file)){
				@unlink($new_file);
			} 
			$old_file = $old_dir . $data_id .'.txt';
			if(file_exists($old_file)){
				@unlink($old_file);
			}			
		}
	}
}

if(!function_exists('copy_txtmeta')){
	function copy_txtmeta($folder, $data_id, $new_id, $plugin=''){
		if($folder){
			$dir = is_isset($plugin,'upload_dir') . $folder . '/';
			if(!is_dir($dir)){
				@mkdir($dir, 0777);
			}
			$file = $dir . $data_id .'.php';
			$newfile = $dir . $new_id .'.php';
			if(file_exists($file)){
				@copy($file, $newfile);
			} 				
		}
	}
}

if(!function_exists('get_txtmeta')){
	function get_txtmeta($folder, $data_id, $key, $plugin=''){
		if($folder){
			$dir = is_isset($plugin,'upload_dir') . $folder . '/';
			
			$my_dir = wp_upload_dir();
			$old_dir = $my_dir['basedir'].'/'. $folder .'/';
			
			$file = $dir . $data_id .'.php';
			$old_file = $old_dir . $data_id .'.txt';			
			
			$data = '';
			
			if(file_exists($file)){
				$data = @file_get_contents($file);
			} elseif(file_exists($old_file)){
				$data = @file_get_contents($old_file);
			}
			$data = get_phpf_data($data);
			$data = trim($data);
			
			$array = @unserialize($data);
			$string = trim(stripslashes(str_replace('&star;','*',is_isset($array, $key))));
			
			return $string;
		}
	}
}

if(!function_exists('update_txtmeta')){
	function update_txtmeta($folder, $data_id, $key, $value, $plugin=''){
		if($folder){
			$dir = is_isset($plugin,'upload_dir') . $folder . '/';
			
			$my_dir = wp_upload_dir();
			$old_dir = $my_dir['basedir'].'/'. $folder .'/';
			
			if(!is_dir($dir)){
				@mkdir($dir, 0777);
			}

			$file = $dir . $data_id .'.php';
			$old_file = $old_dir . $data_id .'.txt';
			
			$data = '';
			
			if(file_exists($file)){
				$data = @file_get_contents($file);
			} elseif(file_exists($old_file)){
				$data = @file_get_contents($old_file);
			}
			$data = get_phpf_data($data);
			$data = trim($data);
			
			$array = @unserialize($data); 
			if(!is_array($array)){
				$array = array();
			}
			
			$value = str_replace('*','&star;', $value);
			$array[$key] = addslashes($value);
			
			$apd = @serialize($array);
			$file_data = add_phpf_data($apd);
			
			$file_open = @fopen($file, 'w');
			@fwrite($file_open, $file_data);
			@fclose($file_open);	
			
			if(is_file($file)){
				return 1;
			} 
		} 
		return 0;
	}
}

if(!function_exists('get_cptgn')){
	function get_cptgn($text){
		$text =  pn_string($text);
		$txt = iconv('UTF-8','CP1251',$text);
		return $txt;
	}
}

if(!function_exists('get_tgncp')){
	function get_tgncp($text){
		$text =  pn_string($text);
		$txt = iconv('CP1251','UTF-8',$text);
		return $txt;
	}
}

if(!function_exists('rez_exp')){
	function rez_exp($text){
		$text = trim($text);
		$text = str_replace(array(';','"'),'',$text);
		return $text;
	}
}

if(!function_exists('rep_dot')){
	function rep_dot($text){
		$text = str_replace('.',',',$text);
		return $text;
	}
}

if(!function_exists('get_exvar')){
	function get_exvar($zn, $arr){
		return is_isset($arr,$zn);
	}
}

if(!function_exists('pn_max_upload')){
	function pn_max_upload(){
		$max_upload_size = wp_max_upload_size();
		if ( ! $max_upload_size ) {
			$max_upload_size = 0;
		}	
		$max_mb = 0;
		if($max_upload_size > 0){
			$max_mb = ($max_upload_size / 1024 / 1024);	
		}	
		
		$max_mb = apply_filters('pn_max_upload', $max_mb);
		return $max_mb;
	}
}	

if(!function_exists('pn_enable_filetype')){
	function pn_enable_filetype(){
		$filetype = array('.gif','.jpg','.jpeg','.jpe','.png');
		$filetype = apply_filters('pn_enable_filetype', $filetype);
		
		return $filetype;
	}
}

if(!function_exists('pn_mime_filetype')){
	function pn_mime_filetype($file){
		$filetype = '';
		if(function_exists('mime_content_type')){
			$filetype = mime_content_type($file['tmp_name']);
			if($filetype == 'image/png'){
				$filetype = '.png';
			} elseif($filetype == 'image/jpeg'){
				$filetype = '.jpg';
			} elseif($filetype == 'image/gif'){	
				$filetype = '.gif';
			}
		} 
		if(!$filetype){
			$filetype = strtolower(strrchr($file['name'],"."));
		}
		return apply_filters('pn_mime_filetype', $filetype, $file);
	}
}

if(!function_exists('get_array_option')){ 
	function get_array_option($plugin, $option_name){
		$dir = is_isset($plugin,'upload_dir');
		$file = $dir . $option_name .'.php';
			
		$data = '';
		if(file_exists($file)){
			$data = @file_get_contents($file);
		} 
		$data = get_phpf_data($data);
		$data = trim($data);
			
		$array = @unserialize($data); 
		if(!is_array($array)){
			$array = array();
		}
			
		$new_array = array();
		foreach($array as $array_k => $array_v){
			$new_array[str_replace('&star;', '*', $array_k)] = str_replace('&star;', '*', $array_v);
		}
			
		return $new_array;
	}
}

if(!function_exists('update_array_option')){ 
	function update_array_option($plugin, $option_name, $array){
		$dir = is_isset($plugin,'upload_dir');
		$file = $dir . $option_name .'.php';
			
		$new_array = array();
		foreach($array as $array_k => $array_v){
			$new_array[str_replace('*','&star;', $array_k)] = str_replace('*','&star;', $array_v);
		}
			
		$apd = @serialize($array);
		$file_data = add_phpf_data($apd);
			
		$file_open = @fopen($file, 'w');
		@fwrite($file_open, $file_data);
		@fclose($file_open);	 			
	}
}

if(!function_exists('delete_array_option')){ 
	function delete_array_option($plugin, $option_name){
		update_array_option($plugin, $option_name, array());
	}
}

if(!function_exists('unset_array_option')){ 
	function unset_array_option($plugin, $option_name, $key){
		$data = get_array_option($plugin, $option_name);
		if(isset($data[$key])){
			unset($data[$key]);
			update_array_option($plugin, $option_name, $data);
		}
	}
} 

if(!function_exists('get_sounds_premium')){
	function get_sounds_premium(){
		$sounds = array();
		$foldervn = get_premium_dir() ."audio/";
		$url = get_premium_url() ."audio/";
		if(is_dir($foldervn)){
			$dir = @opendir($foldervn);
			$abc_folders = array();
			while(($file = @readdir($dir))){
				if (!strstr($file,'.')){
					$abc_folders[$file] = $file;
				}
			}
			asort($abc_folders);
			$new_sounds = array();
			foreach($abc_folders as $folder){
				$nf = $foldervn . $folder .'/';
				$ndir = @opendir($nf);
				while(($nfile = @readdir($ndir))){
					if ( substr($nfile, -4) == '.mp3' ){
						$new_sounds[$folder]['mp3'] = $url . $folder .'/'.$nfile;
					}
					if ( substr($nfile, -4) == '.ogg' ){
						$new_sounds[$folder]['ogg'] = $url . $folder .'/'.$nfile;
					}				
				}
			}
			$r=0;
			foreach($new_sounds as $key => $ns){
				if(isset($ns['mp3']) and isset($ns['ogg'])){ $r++;
					$sounds[] = array(
						'id' => $r,
						'title' => $key,
						'mp3' => $ns['mp3'],
						'ogg' => $ns['ogg'],
					);
				}
			}
		}	
		return $sounds;
	}
}

if(!function_exists('is_country_attr')){
	function is_country_attr($item){
		$item = pn_string($item);
		if($item == 'NaN'){
			return $item;
		}
		if (preg_match("/^[a-zA-z]{2,3}$/", $item, $matches )) {
			$new_item = mb_strtoupper($item);
		} else {
			$new_item = 0;
		}
		return $new_item;
	}
}

if(!function_exists('get_country_title')){
	function get_country_title($attr){
	global $wpdb;
		$attr = is_country_attr($attr);
		if($attr and $attr != 'NaN'){
			$country = get_countries();
			if(isset($country[$attr])){
				return pn_strip_input(ctv_ml($country[$attr]));
			} else {
				return __('is not determined','premium');
			}
		} else {
			return __('is not determined','premium');
		}
	}
}

if(!function_exists('get_user_country')){ 
	function get_user_country(){ 
	global $user_now_country;
		$country = is_country_attr($user_now_country);
		if(!$country){ $country = 'NaN'; }
		return $country;
	}
}

if(!function_exists('get_countries')){
function get_countries(){
	
$country = "
[en_US:]Australia[:en_US][ru_RU:]Австралия[:ru_RU];AU
[en_US:]Austria[:en_US][ru_RU:]Австрия[:ru_RU];AT
[en_US:]Azerbaijan[:en_US][ru_RU:]Азербайджан[:ru_RU];AZ
[en_US:]Aland Islands[:en_US][ru_RU:]Аландские острова[:ru_RU];AX
[en_US:]Albania[:en_US][ru_RU:]Албания[:ru_RU];AL
[en_US:]Algeria[:en_US][ru_RU:]Алжир[:ru_RU];DZ
[en_US:]Minor outlying Islands (USA)[:en_US][ru_RU:]Внешние малые острова (США)[:ru_RU];UM
[en_US:]U.S. virgin Islands[:en_US][ru_RU:]Американские Виргинские острова[:ru_RU];VI
[en_US:]American Samoa[:en_US][ru_RU:]Американское Самоа[:ru_RU];AS
[en_US:]Anguilla[:en_US][ru_RU:]Ангилья[:ru_RU];AI
[en_US:]Angola[:en_US][ru_RU:]Ангола[:ru_RU];AO
[en_US:]Andorra[:en_US][ru_RU:]Андорра[:ru_RU];AD
[en_US:]Antarctica[:en_US][ru_RU:]Антарктида[:ru_RU];AQ
[en_US:]Antigua and Barbuda[:en_US][ru_RU:]Антигуа и Барбуда[:ru_RU];AG
[en_US:]Argentina[:en_US][ru_RU:]Аргентина[:ru_RU];AR
[en_US:]Armenia[:en_US][ru_RU:]Армения[:ru_RU];AM
[en_US:]Aruba[:en_US][ru_RU:]Аруба[:ru_RU];AW
[en_US:]Afghanistan[:en_US][ru_RU:]Афганистан[:ru_RU];AF
[en_US:]Bahamas[:en_US][ru_RU:]Багамы[:ru_RU];BS
[en_US:]Bangladesh[:en_US][ru_RU:]Бангладеш[:ru_RU];BD
[en_US:]Barbados[:en_US][ru_RU:]Барбадос[:ru_RU];BB
[en_US:]Bahrain[:en_US][ru_RU:]Бахрейн[:ru_RU];BH
[en_US:]Belize[:en_US][ru_RU:]Белиз[:ru_RU];BZ
[en_US:]Belarus[:en_US][ru_RU:]Белоруссия[:ru_RU];BY
[en_US:]Belgium[:en_US][ru_RU:]Бельгия[:ru_RU];BE
[en_US:]Benin[:en_US][ru_RU:]Бенин[:ru_RU];BJ
[en_US:]Bermuda[:en_US][ru_RU:]Бермуды[:ru_RU];BM
[en_US:]Bulgaria[:en_US][ru_RU:]Болгария[:ru_RU];BG
[en_US:]Bolivia[:en_US][ru_RU:]Боливия[:ru_RU];BO
[en_US:]Bosnia and Herzegovina[:en_US][ru_RU:]Босния и Герцеговина[:ru_RU];BA
[en_US:]Botswana[:en_US][ru_RU:]Ботсвана[:ru_RU];BW
[en_US:]Brazil[:en_US][ru_RU:]Бразилия[:ru_RU];BR
[en_US:]British Indian ocean territory[:en_US][ru_RU:]Британская территория в Индийском океане[:ru_RU];IO
[en_US:]British virgin Islands[:en_US][ru_RU:]Британские Виргинские острова[:ru_RU];VG
[en_US:]Brunei[:en_US][ru_RU:]Бруней[:ru_RU];BN
[en_US:]Burkina Faso[:en_US][ru_RU:]Буркина Фасо[:ru_RU];BF
[en_US:]Burundi[:en_US][ru_RU:]Бурунди[:ru_RU];BI
[en_US:]Bhutan[:en_US][ru_RU:]Бутан[:ru_RU];BT
[en_US:]Vanuatu[:en_US][ru_RU:]Вануату[:ru_RU];VU
[en_US:]The Vatican[:en_US][ru_RU:]Ватикан[:ru_RU];VA
[en_US:]UK[:en_US][ru_RU:]Великобритания[:ru_RU];GB
[en_US:]Hungary[:en_US][ru_RU:]Венгрия[:ru_RU];HU
[en_US:]Venezuela[:en_US][ru_RU:]Венесуэла[:ru_RU];VE
[en_US:]East Timor[:en_US][ru_RU:]Восточный Тимор[:ru_RU];TL
[en_US:]Vietnam[:en_US][ru_RU:]Вьетнам[:ru_RU];VN
[en_US:]Gabon[:en_US][ru_RU:]Габон[:ru_RU];GA
[en_US:]Haiti[:en_US][ru_RU:]Гаити[:ru_RU];HT
[en_US:]Guyana[:en_US][ru_RU:]Гайана[:ru_RU];GY
[en_US:]Gambia[:en_US][ru_RU:]Гамбия[:ru_RU];GM
[en_US:]Ghana[:en_US][ru_RU:]Гана[:ru_RU];GH
[en_US:]Guadeloupe[:en_US][ru_RU:]Гваделупа[:ru_RU];GP
[en_US:]Guatemala[:en_US][ru_RU:]Гватемала[:ru_RU];GT
[en_US:]Guinea[:en_US][ru_RU:]Гвинея[:ru_RU];GN
[en_US:]Guinea-Bissau[:en_US][ru_RU:]Гвинея-Бисау[:ru_RU];GW
[en_US:]Germany[:en_US][ru_RU:]Германия[:ru_RU];DE
[en_US:]Gibraltar[:en_US][ru_RU:]Гибралтар[:ru_RU];GI
[en_US:]Honduras[:en_US][ru_RU:]Гондурас[:ru_RU];HN
[en_US:]Hong Kong[:en_US][ru_RU:]Гонконг[:ru_RU];HK
[en_US:]Grenada[:en_US][ru_RU:]Гренада[:ru_RU];GD
[en_US:]Greenland[:en_US][ru_RU:]Гренландия[:ru_RU];GL
[en_US:]Greece[:en_US][ru_RU:]Греция[:ru_RU];GR
[en_US:]Georgia[:en_US][ru_RU:]Грузия[:ru_RU];GE
[en_US:]GUAM[:en_US][ru_RU:]Гуам[:ru_RU];GU
[en_US:]Denmark[:en_US][ru_RU:]Дания[:ru_RU];DK
[en_US:]DR Congo[:en_US][ru_RU:]ДР Конго[:ru_RU];CD
[en_US:]Djibouti[:en_US][ru_RU:]Джибути[:ru_RU];DJ
[en_US:]Dominica[:en_US][ru_RU:]Доминика[:ru_RU];DM
[en_US:]Dominican Republic[:en_US][ru_RU:]Доминиканская Республика[:ru_RU];DO
[en_US:]The European Union[:en_US][ru_RU:]Европейский союз[:ru_RU];EU
[en_US:]Egypt[:en_US][ru_RU:]Египет[:ru_RU];EG
[en_US:]Zambia[:en_US][ru_RU:]Замбия[:ru_RU];ZM
[en_US:]Western Sahara[:en_US][ru_RU:]Западная Сахара[:ru_RU];EH
[en_US:]Zimbabwe[:en_US][ru_RU:]Зимбабве[:ru_RU];ZW
[en_US:]Israel[:en_US][ru_RU:]Израиль[:ru_RU];IL
[en_US:]India[:en_US][ru_RU:]Индия[:ru_RU];IN
[en_US:]Indonesia[:en_US][ru_RU:]Индонезия[:ru_RU];ID
[en_US:]Jordan[:en_US][ru_RU:]Иордания[:ru_RU];JO
[en_US:]Iraq[:en_US][ru_RU:]Ирак[:ru_RU];IQ
[en_US:]Iran[:en_US][ru_RU:]Иран[:ru_RU];IR
[en_US:]Ireland[:en_US][ru_RU:]Ирландия[:ru_RU];IE
[en_US:]Iceland[:en_US][ru_RU:]Исландия[:ru_RU];IS
[en_US:]Spain[:en_US][ru_RU:]Испания[:ru_RU];ES
[en_US:]Italy[:en_US][ru_RU:]Италия[:ru_RU];IT
[en_US:]Yemen[:en_US][ru_RU:]Йемен[:ru_RU];YE
[en_US:]The DPRK[:en_US][ru_RU:]КНДР[:ru_RU];KP
[en_US:]Cape Verde[:en_US][ru_RU:]Кабо-Верде[:ru_RU];CV
[en_US:]Kazakhstan[:en_US][ru_RU:]Казахстан[:ru_RU];KZ
[en_US:]Cayman Islands[:en_US][ru_RU:]Каймановы острова[:ru_RU];KY
[en_US:]Cambodia[:en_US][ru_RU:]Камбоджа[:ru_RU];KH
[en_US:]Cameroon[:en_US][ru_RU:]Камерун[:ru_RU];CM
[en_US:]Canada[:en_US][ru_RU:]Канада[:ru_RU];CA
[en_US:]Qatar[:en_US][ru_RU:]Катар[:ru_RU];QA
[en_US:]Kenya[:en_US][ru_RU:]Кения[:ru_RU];KE
[en_US:]Cyprus[:en_US][ru_RU:]Кипр[:ru_RU];CY
[en_US:]Kyrgyzstan[:en_US][ru_RU:]Киргизия[:ru_RU];KG
[en_US:]Kiribati[:en_US][ru_RU:]Кирибати[:ru_RU];KI
[en_US:]China[:en_US][ru_RU:]КНР[:ru_RU];CN
[en_US:]Cocos Islands[:en_US][ru_RU:]Кокосовые острова[:ru_RU];CC
[en_US:]Colombia[:en_US][ru_RU:]Колумбия[:ru_RU];CO
[en_US:]Comoros[:en_US][ru_RU:]Коморы[:ru_RU];KM
[en_US:]Costa Rica[:en_US][ru_RU:]Коста-Рика[:ru_RU];CR
[en_US:]Côte d'ivoire[:en_US][ru_RU:]Кот-д’Ивуар[:ru_RU];CI
[en_US:]Cuba[:en_US][ru_RU:]Куба[:ru_RU];CU
[en_US:]Kuwait[:en_US][ru_RU:]Кувейт[:ru_RU];KW
[en_US:]Laos[:en_US][ru_RU:]Лаос[:ru_RU];LA
[en_US:]Latvia[:en_US][ru_RU:]Латвия[:ru_RU];LV
[en_US:]Lesotho[:en_US][ru_RU:]Лесото[:ru_RU];LS
[en_US:]Liberia[:en_US][ru_RU:]Либерия[:ru_RU];LR
[en_US:]Lebanon[:en_US][ru_RU:]Ливан[:ru_RU];LB
[en_US:]Libya[:en_US][ru_RU:]Ливия[:ru_RU];LY
[en_US:]Lithuania[:en_US][ru_RU:]Литва[:ru_RU];LT
[en_US:]Liechtenstein[:en_US][ru_RU:]Лихтенштейн[:ru_RU];LI
[en_US:]Luxembourg[:en_US][ru_RU:]Люксембург[:ru_RU];LU
[en_US:]Mauritius[:en_US][ru_RU:]Маврикий[:ru_RU];MU
[en_US:]Mauritania[:en_US][ru_RU:]Мавритания[:ru_RU];MR
[en_US:]Madagascar[:en_US][ru_RU:]Мадагаскар[:ru_RU];MG
[en_US:]Mayotte[:en_US][ru_RU:]Майотта[:ru_RU];YT
[en_US:]Macau[:en_US][ru_RU:]Аомынь[:ru_RU];MO
[en_US:]Macedonia[:en_US][ru_RU:]Македония[:ru_RU];MK
[en_US:]Malawi[:en_US][ru_RU:]Малави[:ru_RU];MW
[en_US:]Malaysia[:en_US][ru_RU:]Малайзия[:ru_RU];MY
[en_US:]Mali[:en_US][ru_RU:]Мали[:ru_RU];ML
[en_US:]The Maldives[:en_US][ru_RU:]Мальдивы[:ru_RU];MV
[en_US:]Malta[:en_US][ru_RU:]Мальта[:ru_RU];MT
[en_US:]Morocco[:en_US][ru_RU:]Марокко[:ru_RU];MA
[en_US:]Martinique[:en_US][ru_RU:]Мартиника[:ru_RU];MQ
[en_US:]Marshall Islands[:en_US][ru_RU:]Маршалловы Острова[:ru_RU];MH
[en_US:]Mexico[:en_US][ru_RU:]Мексика[:ru_RU];MX
[en_US:]Mozambique[:en_US][ru_RU:]Мозамбик[:ru_RU];MZ
[en_US:]Moldova[:en_US][ru_RU:]Молдавия[:ru_RU];MD
[en_US:]Monaco[:en_US][ru_RU:]Монако[:ru_RU];MC
[en_US:]Mongolia[:en_US][ru_RU:]Монголия[:ru_RU];MN
[en_US:]Montserrat[:en_US][ru_RU:]Монтсеррат[:ru_RU];MS
[en_US:]Myanmar[:en_US][ru_RU:]Мьянма[:ru_RU];MM
[en_US:]Namibia[:en_US][ru_RU:]Намибия[:ru_RU];NA
[en_US:]Nauru[:en_US][ru_RU:]Науру[:ru_RU];NR
[en_US:]Nepal[:en_US][ru_RU:]Непал[:ru_RU];NP
[en_US:]Niger[:en_US][ru_RU:]Нигер[:ru_RU];NE
[en_US:]Nigeria[:en_US][ru_RU:]Нигерия[:ru_RU];NG
[en_US:]Netherlands Antilles[:en_US][ru_RU:]Нидерландские Антильские острова[:ru_RU];AN
[en_US:]The Netherlands[:en_US][ru_RU:]Нидерланды[:ru_RU];NL
[en_US:]Nicaragua[:en_US][ru_RU:]Никарагуа[:ru_RU];NI
[en_US:]Niue[:en_US][ru_RU:]Ниуэ[:ru_RU];NU
[en_US:]New Caledonia[:en_US][ru_RU:]Новая Каледония[:ru_RU];NC
[en_US:]New Zealand[:en_US][ru_RU:]Новая Зеландия[:ru_RU];NZ
[en_US:]Norway[:en_US][ru_RU:]Норвегия[:ru_RU];NO
[en_US:]UAE[:en_US][ru_RU:]ОАЭ[:ru_RU];AE
[en_US:]Oman[:en_US][ru_RU:]Оман[:ru_RU];OM
[en_US:]Christmas Island[:en_US][ru_RU:]Остров Рождества[:ru_RU];CX
[en_US:]Cook Islands[:en_US][ru_RU:]Острова Кука[:ru_RU];CK
[en_US:]Heard and McDonald[:en_US][ru_RU:]Херд и Макдональд[:ru_RU];HM
[en_US:]Pakistan[:en_US][ru_RU:]Пакистан[:ru_RU];PK
[en_US:]Palau[:en_US][ru_RU:]Палау[:ru_RU];PW
[en_US:]Palestine[:en_US][ru_RU:]Палестина[:ru_RU];PS
[en_US:]Panama[:en_US][ru_RU:]Панама[:ru_RU];PA
[en_US:]Papua New Guinea[:en_US][ru_RU:]Папуа — Новая Гвинея[:ru_RU];PG
[en_US:]Paraguay[:en_US][ru_RU:]Парагвай[:ru_RU];PY
[en_US:]Peru[:en_US][ru_RU:]Перу[:ru_RU];PE
[en_US:]Pitcairn Islands[:en_US][ru_RU:]Острова Питкэрн[:ru_RU];PN
[en_US:]Poland[:en_US][ru_RU:]Польша[:ru_RU];PL
[en_US:]Portugal[:en_US][ru_RU:]Португалия[:ru_RU];PT
[en_US:]Puerto Rico[:en_US][ru_RU:]Пуэрто-Рико[:ru_RU];PR
[en_US:]Republic Of The Congo[:en_US][ru_RU:]Республика Конго[:ru_RU];CG
[en_US:]Reunion[:en_US][ru_RU:]Реюньон[:ru_RU];RE
[en_US:]Russia[:en_US][ru_RU:]Россия[:ru_RU];RU
[en_US:]Rwanda[:en_US][ru_RU:]Руанда[:ru_RU];RW
[en_US:]Romania[:en_US][ru_RU:]Румыния[:ru_RU];RO
[en_US:]USA[:en_US][ru_RU:]США[:ru_RU];US
[en_US:]Salvador[:en_US][ru_RU:]Сальвадор[:ru_RU];SV
[en_US:]Samoa[:en_US][ru_RU:]Самоа[:ru_RU];WS
[en_US:]San Marino[:en_US][ru_RU:]Сан-Марино[:ru_RU];SM
[en_US:]Sao Tome and Principe[:en_US][ru_RU:]Сан-Томе и Принсипи[:ru_RU];ST
[en_US:]Saudi Arabia[:en_US][ru_RU:]Саудовская Аравия[:ru_RU];SA
[en_US:]Swaziland[:en_US][ru_RU:]Свазиленд[:ru_RU];SZ
[en_US:]Svalbard and Jan Mayen[:en_US][ru_RU:]Шпицберген и Ян-Майен[:ru_RU];SJ
[en_US:]Northern Mariana Islands[:en_US][ru_RU:]Северные Марианские острова[:ru_RU];MP
[en_US:]Seychelles[:en_US][ru_RU:]Сейшельские Острова[:ru_RU];SC
[en_US:]Senegal[:en_US][ru_RU:]Сенегал[:ru_RU];SN
[en_US:]Saint Vincent and the Grenadines[:en_US][ru_RU:]Сент-Винсент и Гренадины[:ru_RU];VC
[en_US:]Saint Kitts and Nevis[:en_US][ru_RU:]Сент-Китс и Невис[:ru_RU];KN
[en_US:]Saint Lucia[:en_US][ru_RU:]Сент-Люсия[:ru_RU];LC
[en_US:]Saint Pierre and Miquelon[:en_US][ru_RU:]Сен-Пьер и Микелон[:ru_RU];PM
[en_US:]Serbia[:en_US][ru_RU:]Сербия[:ru_RU];RS
[en_US:]Serbia and Montenegro (operated until September 2006)[:en_US][ru_RU:]Сербия и Черногория (действовал до сентября 2006 года)[:ru_RU];CS
[en_US:]Singapore[:en_US][ru_RU:]Сингапур[:ru_RU];SG
[en_US:]Syria[:en_US][ru_RU:]Сирия[:ru_RU];SY
[en_US:]Slovakia[:en_US][ru_RU:]Словакия[:ru_RU];SK
[en_US:]Slovenia[:en_US][ru_RU:]Словения[:ru_RU];SI
[en_US:]Solomon Islands[:en_US][ru_RU:]Соломоновы Острова[:ru_RU];SB
[en_US:]Somalia[:en_US][ru_RU:]Сомали[:ru_RU];SO
[en_US:]Sudan[:en_US][ru_RU:]Судан[:ru_RU];SD
[en_US:]Suriname[:en_US][ru_RU:]Суринам[:ru_RU];SR
[en_US:]Sierra Leone[:en_US][ru_RU:]Сьерра-Леоне[:ru_RU];SL
[en_US:]The USSR was valid until September 1992)[:en_US][ru_RU:]СССР (действовал до сентября 1992 года)[:ru_RU];SU
[en_US:]Tajikistan[:en_US][ru_RU:]Таджикистан[:ru_RU];TJ
[en_US:]Thailand[:en_US][ru_RU:]Таиланд[:ru_RU];TH
[en_US:]The Republic Of China[:en_US][ru_RU:]Китайская Республика[:ru_RU];TW
[en_US:]Tanzania[:en_US][ru_RU:]Танзания[:ru_RU];TZ
[en_US:]In[:en_US][ru_RU:]Того[:ru_RU];TG
[en_US:]Tokelau[:en_US][ru_RU:]Токелау[:ru_RU];TK
[en_US:]Tonga[:en_US][ru_RU:]Тонга[:ru_RU];TO
[en_US:]Trinidad and Tobago[:en_US][ru_RU:]Тринидад и Тобаго[:ru_RU];TT
[en_US:]Tuvalu[:en_US][ru_RU:]Тувалу[:ru_RU];TV
[en_US:]Tunisia[:en_US][ru_RU:]Тунис[:ru_RU];TN
[en_US:]Turkmenistan[:en_US][ru_RU:]Туркмения[:ru_RU];TM
[en_US:]Turkey[:en_US][ru_RU:]Турция[:ru_RU];TR
[en_US:]Uganda[:en_US][ru_RU:]Уганда[:ru_RU];UG
[en_US:]Uzbekistan[:en_US][ru_RU:]Узбекистан[:ru_RU];UZ
[en_US:]Ukraine[:en_US][ru_RU:]Украина[:ru_RU];UA
[en_US:]Uruguay[:en_US][ru_RU:]Уругвай[:ru_RU];UY
[en_US:]Faroe Islands[:en_US][ru_RU:]Фарерские острова[:ru_RU];FO
[en_US:]Micronesia[:en_US][ru_RU:]Микронезия[:ru_RU];FM
[en_US:]Fiji[:en_US][ru_RU:]Фиджи[:ru_RU];FJ
[en_US:]Philippines[:en_US][ru_RU:]Филиппины[:ru_RU];PH
[en_US:]Finland[:en_US][ru_RU:]Финляндия[:ru_RU];FI
[en_US:]Falkland Islands[:en_US][ru_RU:]Фолклендские острова[:ru_RU];FK
[en_US:]France[:en_US][ru_RU:]Франция[:ru_RU];FR
[en_US:]French Guiana[:en_US][ru_RU:]Французская Гвиана[:ru_RU];GF
[en_US:]French Polynesia[:en_US][ru_RU:]Французская Полинезия[:ru_RU];PF
[en_US:]French Southern and Antarctic lands[:en_US][ru_RU:]Французские Южные и Антарктические Территории[:ru_RU];TF
[en_US:]Croatia[:en_US][ru_RU:]Хорватия[:ru_RU];HR
[en_US:]CAR[:en_US][ru_RU:]ЦАР[:ru_RU];CF
[en_US:]Chad[:en_US][ru_RU:]Чад[:ru_RU];TD
[en_US:]Montenegro[:en_US][ru_RU:]Черногория[:ru_RU];ME
[en_US:]Czech Republic[:en_US][ru_RU:]Чехия[:ru_RU];CZ
[en_US:]Chile[:en_US][ru_RU:]Чили[:ru_RU];CL
[en_US:]Switzerland[:en_US][ru_RU:]Швейцария[:ru_RU];CH
[en_US:]Sweden[:en_US][ru_RU:]Швеция[:ru_RU];SE
[en_US:]Sri Lanka[:en_US][ru_RU:]Шри-Ланка[:ru_RU];LK
[en_US:]Ecuador[:en_US][ru_RU:]Эквадор[:ru_RU];EC
[en_US:]Equatorial Guinea[:en_US][ru_RU:]Экваториальная Гвинея[:ru_RU];GQ
[en_US:]Eritrea[:en_US][ru_RU:]Эритрея[:ru_RU];ER
[en_US:]Estonia[:en_US][ru_RU:]Эстония[:ru_RU];EE
[en_US:]Ethiopia[:en_US][ru_RU:]Эфиопия[:ru_RU];ET
[en_US:]South Africa[:en_US][ru_RU:]ЮАР[:ru_RU];ZA
[en_US:]The Republic Of Korea[:en_US][ru_RU:]Республика Корея[:ru_RU];KR
[en_US:]South Georgia and the South sandwich Islands[:en_US][ru_RU:]Южная Георгия и Южные Сандвичевы острова[:ru_RU];GS
[en_US:]Jamaica[:en_US][ru_RU:]Ямайка[:ru_RU];JM
[en_US:]Japan[:en_US][ru_RU:]Япония[:ru_RU];JP
[en_US:]Bouvet Island[:en_US][ru_RU:]Остров Буве[:ru_RU];BV
[en_US:]Norfolk Island[:en_US][ru_RU:]Остров Норфолк[:ru_RU];NF
[en_US:]St. Helena Island[:en_US][ru_RU:]Остров Святой Елены[:ru_RU];SH
[en_US:]Turks and Caicos Islands[:en_US][ru_RU:]Тёркс и Кайкос[:ru_RU];TC
[en_US:]Wallis and Futuna[:en_US][ru_RU:]Уоллис и Футуна[:ru_RU];WF
";	

	$array = array();	
	$country = explode("\n",$country);
	foreach($country as $cou){
		$data = explode(';',$cou);
		$title = trim(is_isset($data,0));
		$attr = trim(is_isset($data,1));
		if($title and $attr){	
			$array[$attr] = $title;
		}
	}	
	asort($array);
	return $array;
}
}