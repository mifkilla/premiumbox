<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){ 
	add_action('pn_adminpage_content_pn_sort_table1','hidesort_pn_adminpage_content_pn_sort_table1');
	function hidesort_pn_adminpage_content_pn_sort_table1(){
	global $wpdb;

		$form = new PremiumForm();

		$places = $places_t = array();
		$place = is_param_get('place');
		$currencies = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."currency WHERE auto_status='1' ORDER BY t1_1 ASC");
		foreach($currencies as $currency){
			$places[$currency->id] = get_currency_title($currency);
			$places_t[] = $currency->id;
		}
		$selects = array();
		$selects[] = array(
			'link' => admin_url("admin.php?page=pn_sort_table1"),
			'title' => '--' . __('Left column','pn') . '--',
			'background' => '',
			'default' => '',
		);		
		if(is_array($places)){ 
			foreach($places as $key => $val){ 
				$selects[] = array(
					'link' => admin_url("admin.php?page=pn_sort_table1&place=".$key),
					'title' => $val,
					'background' => '',
					'default' => $key,
				);		
			}
		}		
		$form->select_box($place, $selects, __('Setting up','pn'));

		if(in_array($place, $places_t)){
			$sort_link = pn_link('pn_hidesort_directions_sort','post');
			$place = intval($place);
			$items = $wpdb->get_results("SELECT *, dirorder.id AS item_id FROM ".$wpdb->prefix."directions dir LEFT OUTER JOIN ".$wpdb->prefix."directions_order dirorder ON(dir.id = dirorder.direction_id) WHERE dir.auto_status='1' AND dir.direction_status IN('1','2') AND dir.currency_id_give='$place' AND dirorder.c_id='$place' ORDER BY dirorder.order1 ASC");	

			$sort_list = array();
			foreach($items as $item){
				$sort_list[0][] = array(
					'title' => get_currency_title_by_id($item->currency_id_get),
					'id' => $item->item_id,
					'number' => $item->item_id,
				);		
			}
			$form->sort_one_screen($sort_list);	 
		} else {
			$sort_link = pn_link('pn_hidesort_directions_left','post');
			
			$sort_list = array();
			foreach($currencies as $item){
				$sort_list[0][] = array(
					'title' => get_currency_title($item),
					'id' => $item->id,
					'number' => $item->id,
				);		
			}
			$form->sort_one_screen($sort_list);
		} 
		
		$form->sort_js('.thesort ul', $sort_link);
	}
	
	add_action('premium_action_pn_hidesort_directions_left','def_premium_action_pn_hidesort_directions_left');
	function def_premium_action_pn_hidesort_directions_left(){
	global $wpdb;	
		if(current_user_can('administrator') or current_user_can('pn_directions')){
			only_post();
		
			$number = is_param_post('number');
			$y = 0;
			if(is_array($number)){
				foreach($number as $theid) { $y++;
					$theid = intval($theid);
					$wpdb->query("UPDATE ".$wpdb->prefix."currency SET t1_1='$y' WHERE id = '$theid'");
				}
			}
		}
	}

	add_action('premium_action_pn_hidesort_directions_sort','def_premium_action_pn_hidesort_directions_sort');
	function def_premium_action_pn_hidesort_directions_sort(){
	global $wpdb;	
		if(current_user_can('administrator') or current_user_can('pn_directions')){
			only_post();
		
			$number = is_param_post('number');
			$y = 0;
			if(is_array($number)){	
				foreach($number as $theid) { $y++;
					$theid = intval($theid);
					$wpdb->query("UPDATE ".$wpdb->prefix."directions_order SET order1='$y' WHERE id = '$theid'");	
				}	
			}
		}
	}
}