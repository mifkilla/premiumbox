<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_sort_directions', 'pn_admin_title_pn_sort_directions');
	function pn_admin_title_pn_sort_directions(){
		_e('Sort exchange directions','pn');
	}

	add_action('pn_adminpage_content_pn_sort_directions','def_pn_admin_content_pn_sort_directions');
	function def_pn_admin_content_pn_sort_directions(){
	global $wpdb;

		$form = new PremiumForm();

		$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions ORDER BY site_order1 ASC");
		$sort_list = array();
		foreach($items as $item){
			$sort_list[0][] = array(
				'title' => pn_strip_input($item->tech_name) . pn_item_status($item, 'direction_status') . pn_item_basket($item),
				'id' => $item->id,
				'number' => $item->id,
			);		
		}
		$form->sort_one_screen($sort_list);
		$form->sort_js('.thesort ul', pn_link('pn_sort_directions','post'));
	}


	add_action('premium_action_pn_sort_directions','def_premium_action_pn_sort_directions');
	function def_premium_action_pn_sort_directions(){
	global $wpdb;	
		if(current_user_can('administrator') or current_user_can('pn_directions')){
			only_post();
				$number = is_param_post('number');
				$y = 0;
				if(is_array($number)){
					foreach($number as $theid) { $y++;
						$theid = intval($theid);
						$wpdb->query("UPDATE ".$wpdb->prefix."directions SET site_order1='$y' WHERE id = '$theid'");
					}
				}
		}
	}
}