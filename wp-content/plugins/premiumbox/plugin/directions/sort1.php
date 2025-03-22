<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_sort_table1', 'pn_admin_title_pn_sort_table1');
	function pn_admin_title_pn_sort_table1(){
		printf(__('Sort exchange direction for exchange table %s','pn'),'1,4,5');
	}

	add_action('pn_adminpage_content_pn_sort_table1','def_pn_admin_content_pn_sort_table1');
	function def_pn_admin_content_pn_sort_table1(){
	global $wpdb;

		$form = new PremiumForm();

		$place = is_param_get('place');
		
		$selects = array();
		$selects[] = array(
			'link' => admin_url("admin.php?page=pn_sort_table1"),
			'title' => '--' . __('Left column','pn') . '--',
			'background' => '',
			'default' => '',
		);		
		$selects[] = array(
			'link' => admin_url("admin.php?page=pn_sort_table1&place=right"),
			'title' => '--' . __('Right column','pn') . '--',
			'background' => '',
			'default' => 'right',
		);			
		$form->select_box($place, $selects, __('Setting up','pn'));	
		
		$sort_list = array();

		if($place == 'right'){
			$datas = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."currency ORDER BY t1_2 ASC");
			foreach($datas as $val){
				$sort_list[0][] = array(
					'title' => get_currency_title($val) . pn_item_status($val, 'currency_status') . pn_item_basket($val),
					'id' => $val->id,
					'number' => $val->id,
				);			
			}
			$sort_link = pn_link('sort_table1_right', 'post');
		} else {
			$datas = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."currency ORDER BY t1_1 ASC");
			foreach($datas as $val){
				$sort_list[0][] = array(
					'title' => get_currency_title($val) . pn_item_status($val, 'currency_status') . pn_item_basket($val),
					'id' => $val->id,
					'number' => $val->id,
				);			
			}
			$sort_link = pn_link('sort_table1_left','post');
		}
		
		$form->sort_one_screen($sort_list);
		$form->sort_js('.thesort ul', $sort_link);
	}

	add_action('premium_action_sort_table1_left','def_premium_action_sort_table1_left');
	function def_premium_action_sort_table1_left(){
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

	add_action('premium_action_sort_table1_right','def_premium_action_sort_table1_right');
	function def_premium_action_sort_table1_right(){
	global $wpdb;	
		if(current_user_can('administrator') or current_user_can('pn_directions')){
			only_post();
				
			$number = is_param_post('number');
			$y = 0;
			if(is_array($number)){
				foreach($number as $theid) { $y++;
					$theid = intval($theid);
					$wpdb->query("UPDATE ".$wpdb->prefix."currency SET t1_2='$y' WHERE id = '$theid'");	
				}	
			}
		}
	}
}