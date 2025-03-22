<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('geoip_pntable_columns_all_usfield') and is_admin()){
	add_filter("pntable_columns_all_usfield", 'geoip_pntable_columns_all_usfield', 100);
	function geoip_pntable_columns_all_usfield($columns){
		$n_columns = array();
		$n_columns['country'] = __('Country','pn');
		$columns = pn_array_insert($columns, 'lang', $n_columns);
		return $columns;
	}
}

if(!function_exists('geoip_pntable_column_all_usfield') and is_admin()){
	add_filter("pntable_column_all_usfield", 'geoip_pntable_column_all_usfield', 10, 3);
	function geoip_pntable_column_all_usfield($return, $column_name,$item){
		if($column_name == 'country'){
			$country = @unserialize(is_isset($item,'country'));
			if(!is_array($country)){ $country = array(); }
			
			$countrs = array();
			foreach($country as $cou){
				$countrs[] = get_country_title($cou);
			}
			if(count($countrs) == 0){
				return __('All','pn');
			} else {
				return join(', ',$countrs);
			}
		}
		return $return;
	}
}

if(!function_exists('geoip_all_usfield_addform')){
	add_filter("all_usfield_addform", 'geoip_all_usfield_addform', 10, 2);
	function geoip_all_usfield_addform($options, $data){
		$n_options = array();
		$n_options['country'] = array(
			'view' => 'user_func',
			'name' => 'country',
			'func_data' => $data,
			'func' => 'all_usfield_addform_country',
			'work' => 'input_array',
		);		
		$options = pn_array_insert($options, 'locale', $n_options);
		return $options;
	}
}

if(!function_exists('all_usfield_addform_country')){
	function all_usfield_addform_country($data){
	?>
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Display field based on user location (IP address detection)','pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
					
					$def = @unserialize(is_isset($data,'country'));
					if(!is_array($def)){ $def = array(); }
					
					$en_country = get_option('geoip_country');
					if(!is_array($en_country)){ $en_country = array(); }
					
					$checked = 0;
					if(in_array('NaN',$def) or count($def) == 0){
						$checked = 1;
					}	
					$scroll_lists[] = array(
						'title' => __('is not determined','pn').' (NaN)',
						'checked' => $checked,
						'value' => 'NaN',
					);					
					foreach($en_country as $attr){
						$checked = 0;
						if(in_array($attr,$def) or count($def) == 0){
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => get_country_title($attr),
							'checked' => $checked,
							'value' => $attr,
						);
					}
					echo get_check_list($scroll_lists, 'country[]', array('NaN' => 'bred'), '', 1);
					?>
					<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>	
		<?php			
	}
}

if(!function_exists('geoip_all_usfield_addform_post') and is_admin()){
	add_filter("all_usfield_addform_post", 'geoip_all_usfield_addform_post');
	function geoip_all_usfield_addform_post($array){

		$country = is_param_post('country');
		$item = array();
		if(is_array($country)){
			foreach($country as $v){
				$v = is_country_attr($v);
				if($v){
					$item[] = $v;
				}
			}
		}
		if(count($item)){
			$array['country'] = @serialize($item);
		} else {
			$array['country'] = '';
		}

		return $array;
	}
}

if(!function_exists('geoip_detected_init')){
	add_action('init', 'geoip_detected_init', 0);
	function geoip_detected_init(){ 
	global $wpdb, $user_now_country;

		$user_now_country = '';
		
		if(!is_admin()){
			$plugin = get_plugin_class();
			
			$ip = pn_real_ip();
			
			$memory = intval($plugin->get_option('geoip','memory'));
			$type = intval($plugin->get_option('geoip','type'));
			$api_key = pn_strip_input($plugin->get_option('geoip','api_key'));
			$timeout = intval($plugin->get_option('geoip','timeout'));
			if($timeout < 1){ $timeout = 30; }
			
			$in_memory = 0;
			$user_country = '';
			
			if($memory == 1){
				$data_memory = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."geoip_memory WHERE ip='$ip'"); 
				if(isset($data_memory->country_attr)){
					$user_country = $data_memory->country_attr;
					$in_memory = 1;
				}
			}
			
			if(!$user_country){
				
				$curl_options = array(
					CURLOPT_TIMEOUT => $timeout,
					CURLOPT_CONNECTTIMEOUT => $timeout,
				);				
				
				if($type == 1){
					$url = 'http://ip-api.com/php/'. $ip;
					$curl = get_curl_parser($url, $curl_options, 'geoip');
					if(!$curl['err']){
						$output = $curl['output'];
						$out = @unserialize($output);
						if(isset($out['countryCode'])){
							$user_country = $out['countryCode'];
						}
					}	
				} elseif($type == 2){
					$url = 'https://api.2ip.ua/geo.json?ip='. $ip;
					$curl = get_curl_parser($url, $curl_options, 'geoip');
					if(!$curl['err']){
						$output = $curl['output'];
						$out = @json_decode($output, true);
						if(isset($out['country_code'])){
							$user_country = $out['country_code'];
						}
					}				
				} elseif($type == 3){  
					$url = 'http://api.sypexgeo.net/json/'. $ip;
					if($api_key){
						$url = 'http://api.sypexgeo.net/'. $api_key .'/json/'. $ip;
					}
					$curl = get_curl_parser($url, $curl_options, 'geoip');
					if(!$curl['err']){
						$output = $curl['output'];
						$out = @json_decode($output, true);
						if(isset($out['country'], $out['country']['iso'])){
							$user_country = $out['country']['iso'];
						}
					}				
				}
				
			}
			
			$user_country = is_country_attr($user_country);

			if($memory == 1 and $user_country and $in_memory != 1){
				$arr = array();
				$arr['ip'] = $ip;
				$arr['country_attr'] = $user_country;
				$wpdb->insert($wpdb->prefix ."geoip_memory", $arr);
			}		
			
			$countries = get_countries();
			
			$en_country = get_option('geoip_country');
			if(!is_array($en_country)){ $en_country = array(); }
			
			if(isset($countries[$user_country]) and isset($en_country[$user_country])){
				$user_now_country = $user_country;
			}
		}	
	}
}

if(!function_exists('geoip_init')){
	add_action('init', 'geoip_init', 1);
	function geoip_init(){ 
		global $wpdb;

		if(!is_admin()){
			$plugin = get_plugin_class();
			
			$spider = 0;
			$agent = is_isset($_SERVER,'HTTP_USER_AGENT');
			
			if(preg_match("~(Google|Yahoo|Rambler|Bot|Yandex|Spider|Snoopy|Crawler|Finder|Mail|curl)~i", $agent)){
				$spider = 1;
			} 
			
			$ip = pn_real_ip();
			$ip_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."geoip_ips WHERE theip='$ip'");
			$thetype = 2;
			if(isset($ip_data->thetype)){
				$thetype = intval($ip_data->thetype);
			}	
			
			if($thetype == 0){
				header('Content-Type: text/html; charset=utf-8');
				
				$temp = '
				<html '. get_language_attributes() .'>
				<head profile="http://gmpg.org/xfn/11">
					<meta charset="'. get_bloginfo( 'charset' ) .'">
					<title>'. __('Your ip is blocked','pn') .'</title>
					<link rel="stylesheet" href="'. $plugin->plugin_url .'moduls/geoip/sitestyle.css" type="text/css" media="screen" />
					'. apply_filters('premium_other_head', '', 'geoip') .'
				</head>
				<body class="' . join( ' ', get_body_class() ) . '">';
					
					$temp_content = '
					<div id="container">
						<div class="title">'. __('Your ip is blocked','pn') .'</div>
						<div class="content">
							<div class="text">
								'. __('Access to the website is prohibited','pn') .'
							</div>	
						</div>
					</div>';
					$temp .= apply_filters('geoip_blockip_temp', $temp_content, $ip);
				
				$temp .= '
				</body>
				</html>
				';
				
				echo $temp;
				exit;
			}	
			
			if($thetype != 1 and $spider != 1){
				$blocked = $plugin->get_option('geoip','blocked');
				if(!is_array($blocked)){ $blocked = array(); }
				
				$user_now_country = get_user_country();
				if($user_now_country and in_array($user_now_country, $blocked)){
					$title = pn_strip_input(ctv_ml($plugin->get_option('geoip','title')));
					$text = pn_strip_text(ctv_ml($plugin->get_option('geoip','text')));

					header('Content-Type: text/html; charset=utf-8');
						
					$temp = '
					<html '. get_language_attributes() .'>
					<head profile="http://gmpg.org/xfn/11">
						<meta charset="'. get_bloginfo( 'charset' ) .'">
						<title>'. $title .'</title>
						<link rel="stylesheet" href="'. $plugin->plugin_url .'moduls/geoip/sitestyle.css" type="text/css" media="screen" />
						'. apply_filters('premium_other_head', '', 'geoip') .'
					</head>
					<body class="' . join( ' ', get_body_class() ) . '">';
					
						$temp_content = '
						<div id="container">
							<div class="title">'. $title .'</div>
							<div class="content">
								<div class="text">
									'. apply_filters('comment_text', $text) .'
								</div>	
							</div>
						</div>
						';
						$temp .= apply_filters('geoip_bloccountry_temp', $temp_content, $title, $text);
						
					$temp .= '	
					</body>
					</html>
					';
					
					echo $temp;
					exit;
				}		
			}
		}	
	}
}