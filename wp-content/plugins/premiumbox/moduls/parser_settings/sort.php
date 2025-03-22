<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_sort_parser_pairs', 'pn_admin_title_pn_sort_parser_pairs');
	function pn_admin_title_pn_sort_parser_pairs(){
		_e('Sorting rates','pn');
	}

	add_action('pn_adminpage_content_pn_sort_parser_pairs','def_pn_admin_content_pn_sort_parser_pairs');
	function def_pn_admin_content_pn_sort_parser_pairs(){
	global $wpdb;

		$form = new PremiumForm();

		$datas = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."parser_pairs ORDER BY menu_order ASC");
		$sort_list = array();
		foreach($datas as $item){
			$sort_list[0][] = array(
				'title' => pn_strip_input(ctv_ml($item->title_pair_give)).'-'.pn_strip_input(ctv_ml($item->title_pair_get)).' ('.pn_strip_input($item->title_birg).')',
				'id' => $item->id,
				'number' => $item->id,
			);		
		}
		
		$form->sort_one_screen($sort_list);	
		$form->sort_js('.thesort ul', pn_link('','post'));
	}

	add_action('premium_action_pn_sort_parser_pairs','def_premium_action_pn_sort_parser_pairs');
	function def_premium_action_pn_sort_parser_pairs(){
	global $wpdb;	
		only_post();
		if(current_user_can('administrator') or current_user_can('pn_directions') or current_user_can('pn_parser')){
			$number = is_param_post('number');
			$y = 0;
			if(is_array($number)){	
				foreach($number as $theid) { $y++;
					$theid = intval($theid);
					$wpdb->query("UPDATE ".$wpdb->prefix."parser_pairs SET menu_order='$y' WHERE id = '$theid'");
				}
			}
		}
	}
}	