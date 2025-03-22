<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Exchange directions Language settings[:en_US][ru_RU:]Настройка языков для направлений обмена[:ru_RU]
description: [en_US:]Exchange directions Language settings[:en_US][ru_RU:]Настройка языков для направлений обмена[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_napslangs');
add_action('all_bd_activated', 'bd_all_moduls_active_napslangs');
function bd_all_moduls_active_napslangs(){
global $wpdb;		
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'naps_lang'"); /* 1.6 */
    if ($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `naps_lang` longtext NOT NULL");
    } else {
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions CHANGE `naps_lang` `naps_lang` longtext NOT NULL");
	}
}

add_action('tab_direction_tab8', 'napslangs_tab_direction_tab8', 1, 2);
function napslangs_tab_direction_tab8($data, $data_id){
	if(is_ml()){ 
		$langs = get_langs_ml();
		?>
		<div class="add_tabs_line">
			<div class="add_tabs_single long">
				<div class="add_tabs_sublabel"><span><?php _e('Language','pn'); ?></span></div>
				<div class="premium_wrap_standart">
					
					<?php
					$scroll_lists = array();
						
					$string = pn_strip_input(is_isset($data,'naps_lang'));
					$def = array();
					if(preg_match_all('/\[d](.*?)\[\/d]/s',$string, $match, PREG_PATTERN_ORDER)){
						$def = $match[1];
					}	
											
					foreach($langs as $lang){
						$checked = 0;
						if(in_array($lang,$def) or count($def) == 0){
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => get_title_forkey($lang),
							'checked' => $checked,
							'value' => $lang,
						);
					}
					echo get_check_list($scroll_lists, 'naps_lang[]');
					?>				
					
						<div class="premium_clear"></div>
				</div>
			</div>
		</div>		
	<?php }		
}

add_filter('pn_direction_addform_post', 'napslangs_pn_direction_addform_post');
function napslangs_pn_direction_addform_post($array){
	$naps_lang = is_param_post('naps_lang');
	$langs = '';
	if(is_array($naps_lang)){
		foreach($naps_lang as $lang){
			$lang = pn_strip_input($lang);
			if($lang){
				$langs .= '[d]'. $lang .'[/d]';
			}
		}
	}
	$array['naps_lang'] = $langs;
	return $array;
}

add_action('set_exchange_filters', 'napslangs_set_exchange_filters');
function napslangs_set_exchange_filters($lists){
	$lists[] = array(
		'title' => __('Filter by user language','pn'),
		'name' => 'napslangs',
	);
	return $lists;
}

add_filter('get_direction_output', 'napslangs_get_direction_output', 10, 3);
function napslangs_get_direction_output($show, $dir, $place){
global $premiumbox;	
	if($show == 1){
		$lang = get_locale();
		$ind = $premiumbox->get_option('exf_'. $place .'_napslangs');
		if($ind == 1){
			$string = pn_strip_input(is_isset($dir, 'naps_lang'));
			$def = array();
			if(preg_match_all('/\[d](.*?)\[\/d]/s', $string, $match, PREG_PATTERN_ORDER)){
				$def = $match[1];
			}
			if(count($def) > 0 and !in_array($lang,$def)){
				$show = 0;
			}
		}				
	}
	return $show;
}

add_filter('error_bids', 'error_bids_napslangs', 99 ,6);
function error_bids_napslangs($error_bids, $account1, $account2, $direction, $vd1, $vd2){

	$user_locale = get_locale();
	$string = pn_strip_input(is_isset($direction,'naps_lang'));
	$naps_lang = array();
	if(preg_match_all('/\[d](.*?)\[\/d]/s',$string, $match, PREG_PATTERN_ORDER)){
		$naps_lang = $match[1];
	}	
	if(!in_array($user_locale,$naps_lang) and count($naps_lang) > 0){
		$error_bids['error_text'][] = __('Error! Exchange direction is prohibited for your language','pn');			
	}	
	
	return $error_bids;
}