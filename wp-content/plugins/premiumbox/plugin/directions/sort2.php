<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_sort_table2', 'pn_admin_title_pn_sort_table2');
	function pn_admin_title_pn_sort_table2(){
		printf(__('Sort exchange direction for exchange table %s','pn'),'2');
	}

	add_action('pn_adminpage_content_pn_sort_table2','def_pn_admin_content_pn_sort_table2');
	function def_pn_admin_content_pn_sort_table2(){
	global $wpdb;

		$form = new PremiumForm();

		$place = is_param_get('place');
		
		$selects = array();
		$selects[] = array(
			'link' => admin_url("admin.php?page=pn_sort_table2"),
			'title' => '--' . __('Left column','pn') . '--',
			'background' => '',
			'default' => '',
		);		
		$selects[] = array(
			'link' => admin_url("admin.php?page=pn_sort_table2&place=right"),
			'title' => '--' . __('Right column','pn') . '--',
			'background' => '',
			'default' => 'right',
		);			
		$form->select_box($place, $selects, __('Setting up','pn'));	
		
		$sort_list = array();

		if($place == 'right'){
			$datas = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."psys ORDER BY t2_2 ASC");
			foreach($datas as $val){
				$sort_list[0][] = array(
					'title' => get_pstitle($val->id) . pn_item_basket($val),
					'id' => $val->id,
					'number' => $val->id,
				);			
			}
			$sort_link = pn_link('sort_table2_right', 'post');
		} else {
			$datas = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."psys ORDER BY t2_1 ASC");
			foreach($datas as $val){
				$sort_list[0][] = array(
					'title' => get_pstitle($val->id) . pn_item_basket($val),
					'id' => $val->id,
					'number' => $val->id,
				);			
			}
			$sort_link = pn_link('sort_table2_left','post');
		}
		
		$form->sort_one_screen($sort_list);	
		$form->sort_js('.thesort ul', $sort_link);
	}


	add_action('premium_action_sort_table2_left','def_premium_action_sort_table2_left');
	function def_premium_action_sort_table2_left(){
	global $wpdb;	
		if(current_user_can('administrator') or current_user_can('pn_directions')){
			only_post();
		
			$number = is_param_post('number');
			$y = 0;
			if(is_array($number)){
				foreach($number as $theid) { $y++;
					$theid = intval($theid);
					$wpdb->query("UPDATE ".$wpdb->prefix."psys SET t2_1='$y' WHERE id = '$theid'");	
				}	
			}
		}
	}

	add_action('premium_action_sort_table2_right','def_premium_action_sort_table2_right');
	function def_premium_action_sort_table2_right(){
	global $wpdb;	
		if(current_user_can('administrator') or current_user_can('pn_directions')){
			only_post();
				
			$number = is_param_post('number');
			$y = 0;
			if(is_array($number)){
				foreach($number as $theid) { $y++;
					$theid = intval($theid);
					$wpdb->query("UPDATE ".$wpdb->prefix."psys SET t2_2='$y' WHERE id = '$theid'");	
				}	
			}
		}
	}
}