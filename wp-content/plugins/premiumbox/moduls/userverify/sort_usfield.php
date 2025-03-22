<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_sort_usfield')){
		add_action('pn_adminpage_title_all_sort_usfield', 'def_adminpage_title_all_sort_usfield');
		function def_adminpage_title_all_sort_usfield(){
			_e('Sort verification fields','pn');
		}
	}

	if(!function_exists('def_admin_content_all_sort_usfield')){
		add_action('pn_adminpage_content_all_sort_usfield','def_admin_content_all_sort_usfield');
		function def_admin_content_all_sort_usfield(){
		global $wpdb;	

			$form = new PremiumForm();

			$datas = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."uv_field WHERE status='1' AND fieldvid IN('0','2') ORDER BY uv_order ASC");
			$sort_list = array();
			foreach($datas as $item){
				$sort_list[0][] = array(
					'title' => pn_strip_input(ctv_ml($item->title)),
					'id' => $item->id,
					'number' => $item->id,
				);		
			}
			$form->sort_one_screen($sort_list);	

			$datas = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."uv_field WHERE status='1' AND fieldvid = '1' ORDER BY uv_order ASC");
			$sort_list = array();
			foreach($datas as $item){
				$sort_list[0][] = array(
					'title' => pn_strip_input(ctv_ml($item->title)),
					'id' => $item->id,
					'number' => $item->id,
				);		
			}
			$form->sort_one_screen($sort_list);	
			$form->sort_js('.thesort ul', pn_link('','post'));
		}
	}

	if(!function_exists('def_premium_action_all_sort_usfield')){
		add_action('premium_action_all_sort_usfield','def_premium_action_all_sort_usfield');
		function def_premium_action_all_sort_usfield(){
		global $wpdb;
			if(current_user_can('administrator') or current_user_can('pn_userverify')){
				only_post();
				$number = is_param_post('number');
				$y = 0;
				if(is_array($number)){	
					foreach($number as $theid) { $y++;
						$theid = intval($theid);
						$wpdb->query("UPDATE ".$wpdb->prefix."uv_field SET uv_order='$y' WHERE id = '$theid'");	
					}	
				}
			}
		}
	}
}	