<?php
if( !defined( 'ABSPATH')){ exit(); }

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'pmbx_bd_all_moduls_active_geoip');
add_action('all_bd_activated', 'pmbx_bd_all_moduls_active_geoip');
function pmbx_bd_all_moduls_active_geoip(){
global $wpdb;		
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'not_country'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `not_country` longtext NOT NULL");
    }	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'only_country'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `only_country` longtext NOT NULL");
    }	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."exchange_bids LIKE 'user_country'");
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."exchange_bids ADD `user_country` varchar(10) NOT NULL");
    }	
}

add_action('tab_direction_tab8', 'geoip_tab_direction_tab8', 30, 2);
function geoip_tab_direction_tab8($data, $data_id){
global $wpdb;	

$en_country = get_option('geoip_country');
if(!is_array($en_country)){ $en_country = array(); }
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Prohibited countries','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				
				<?php
				$scroll_lists = array();
					
				$string = pn_strip_input(is_isset($data,'not_country'));
				$def = array();
				if(preg_match_all('/\[d](.*?)\[\/d]/s',$string, $match, PREG_PATTERN_ORDER)){
					$def = $match[1];
				}	
					
				$checked = 0;
				if(in_array('NaN',$def)){
					$checked = 1;
				}	
				$scroll_lists[] = array(
					'title' => __('is not determined','pn').' (NaN)',
					'checked' => $checked,
					'value' => 'NaN',
				);					
				foreach($en_country as $attr){
					$checked = 0;
					if(in_array($attr,$def)){
						$checked = 1;
					}	
					$scroll_lists[] = array(
						'title' => get_country_title($attr) . ' ('. $attr .')',
						'checked' => $checked,
						'value' => $attr,
					);
				}
				echo get_check_list($scroll_lists, 'not_country[]', array('NaN' => 'bred'), '300', 1);
				?>				
				
					<div class="premium_clear"></div>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Allowed countries','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				
				<?php
				$scroll_lists = array();
					
				$string = pn_strip_input(is_isset($data,'only_country'));
				$def = array();
				if(preg_match_all('/\[d](.*?)\[\/d]/s',$string, $match, PREG_PATTERN_ORDER)){
					$def = $match[1];
				}	
					
				$checked = 0;
				if(in_array('NaN',$def)){
					$checked = 1;
				}	
				$scroll_lists[] = array(
					'title' => __('is not determined','pn').' (NaN)',
					'checked' => $checked,
					'value' => 'NaN',
				);					
				foreach($en_country as $attr){
					$checked = 0;
					if(in_array($attr,$def)){
						$checked = 1;
					}	
					$scroll_lists[] = array(
						'title' => get_country_title($attr) . ' ('. $attr .')',
						'checked' => $checked,
						'value' => $attr,
					);
				}
				echo get_check_list($scroll_lists, 'only_country[]', array('NaN' => 'bred'), '300', 1);
				?>				
				
				<div class="premium_clear"></div>
			</div>
		</div>		
	</div>			
	<?php 		
}

add_filter('pn_direction_addform_post', 'geoip_pn_direction_addform_post');
function geoip_pn_direction_addform_post($array){

	$not_country = is_param_post('not_country');
	$item = '';
	if(is_array($not_country)){
		foreach($not_country as $v){
			$v = is_country_attr($v);
			if($v){
				$item .= '[d]'. $v .'[/d]';
			}
		}
	}
	$array['not_country'] = $item;
	
	$only_country = is_param_post('only_country');
	$item = '';
	if(is_array($only_country)){
		foreach($only_country as $v){
			$v = is_country_attr($v);
			if($v){
				$item .= '[d]'. $v .'[/d]';
			}
		}
	}
	$array['only_country'] = $item;	
	
	return $array;
}

add_action('set_exchange_filters', 'geoip_set_exchange_filters');
function geoip_set_exchange_filters($lists){
	$lists[] = array(
		'title' => __('Filter by country of user','pn'),
		'name' => 'napsgeoip',
	);
	return $lists;
}

add_filter('get_directions_where', 'geoip_get_directions_where',1, 2);
function geoip_get_directions_where($where, $place){
global $premiumbox;
	
	$ind = $premiumbox->get_option('exf_'. $place .'_napsgeoip');
	$user_country = get_user_country();
	if($ind == 1){
		$where .= "AND not_country NOT LIKE '%[d]{$user_country}[/d]%' ";
	}
	
	return $where;
}

add_filter('error_bids', 'error_bids_geoip', 99 ,6);
function error_bids_geoip($error_bids, $account1, $account2, $direction, $vd1, $vd2){

	$user_country = get_user_country();
	
	$string = pn_strip_input(is_isset($direction,'not_country'));
	$not_country = array();
	if(preg_match_all('/\[d](.*?)\[\/d]/s',$string, $match, PREG_PATTERN_ORDER)){
		$not_country = $match[1];
	}	
	if(in_array($user_country,$not_country)){
		$error_bids['error_text'][] = __('Error! For your country exchange is denied','pn');			
	}	
	
	$string = pn_strip_input(is_isset($direction,'only_country'));
	$yes_country = array();
	if(preg_match_all('/\[d](.*?)\[\/d]/s',$string, $match, PREG_PATTERN_ORDER)){
		$yes_country = $match[1];
	}	
	if(count($yes_country) > 0 and !in_array($user_country,$yes_country)){
		$error_bids['error_text'][] = __('Error! For your country exchange is denied','pn');			
	}	
		
	return $error_bids;
}

add_filter('array_data_create_bids', 'geoip_data_create_bids');
function geoip_data_create_bids($array){	
	$array['user_country'] = get_user_country();
	return $array;
}

add_filter('change_bids_filter_list', 'geoip_change_bids_filter_list'); 
function geoip_change_bids_filter_list($lists){
global $wpdb;
	
	$options = array(
		'0' => '--'. __('All','pn').'--',
		'NaN' => __('is not determined','pn'),
	);
	
	$countries = get_option('geoip_country');
	if(!is_array($countries)){ $countries = array(); }

	foreach($countries as $attr){
		$options[$attr] = get_country_title($attr);
	}
		
	$lists['other']['country'] = array(
		'title' => __('User country','pn'),
		'name' => 'country',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);	
	
	return $lists;
}

add_filter('where_request_sql_bids', 'napsgeoip_where_request_sql_bids', 10,2);
function napsgeoip_where_request_sql_bids($where, $pars_data){
global $wpdb;

	$pr = $wpdb->prefix;
	$sql_operator = is_sql_operator($pars_data);
	$country = is_country_attr(is_isset($pars_data,'country'));
	if($country){ 
		$where .= " {$sql_operator} {$pr}exchange_bids.user_country = '$country'";
	}	
	
	return $where;
}

add_filter('onebid_icons','onebid_icons_geoip',99,3);
function onebid_icons_geoip($onebid_icon, $item, $data_fs){
global $wpdb;
	 
	if(isset($item->user_country)){
		$onebid_icon['napsgeoip'] = array(
			'type' => 'text',
			'title' => __('User country','pn') .': [country]',
			'label' => '[country_attr]',
		);	
	}
	
	return $onebid_icon; 
}

add_filter('get_bids_replace_text','get_bids_replace_text_napsgeoip',99,3);
function get_bids_replace_text_napsgeoip($text, $item, $data_fs){
global $wpdb;
	
	if(strstr($text, '[country]')){
		$title = get_country_title($item->user_country);		
		$country = '<span class="item_country">' . $title . '</span>';
		$text = str_replace('[country]', $country ,$text);
	}

	if(strstr($text, '[country_attr]')){
		$user_cou = $item->user_country;	
		if($user_cou == 'NaN' or !$user_cou){
			$user_cou = __('N/A','pn');
		}
		$country_attr = '<span class="item_country_attr">' . $user_cou . '</span>';
		$text = str_replace('[country_attr]', $country_attr ,$text);
	}	
	
	return $text;
} 