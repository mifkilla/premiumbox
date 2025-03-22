<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_sort_noticeheader')){
		add_action('pn_adminpage_title_all_sort_noticeheader', 'def_adminpage_title_all_sort_noticeheader');
		function def_adminpage_title_all_sort_noticeheader($page){
			_e('Sorting notifications','pn');
		}
	}

	if(!function_exists('def_pn_adminpage_content_all_sort_noticeheader')){
		add_action('pn_adminpage_content_all_sort_noticeheader','def_pn_adminpage_content_all_sort_noticeheader');
		function def_pn_adminpage_content_all_sort_noticeheader(){
			global $wpdb;
			
			$ui = wp_get_current_user();
			$user_id = intval($ui->ID);
			
			$place = pn_strip_input(is_param_get('place'));
			$form = new PremiumForm();
			
			$selects = array();
			$selects[] = array(
				'link' => admin_url("admin.php?page=all_sort_noticeheader"),
				'title' => '--' . __('Make a choice','pn') . '--',
				'background' => '',
				'default' => '',
			);			
			$selects[] = array(
				'link' => admin_url("admin.php?page=all_sort_noticeheader&place=text"),
				'title' => __('header','pn'),
				'background' => '',
				'default' => 'text',
			);	 
			$selects[] = array(
				'link' => admin_url("admin.php?page=all_sort_noticeheader&place=window"),
				'title' => __('pop-up window','pn'),
				'background' => '',
				'default' => 'window',
			);
			$selects[] = array(
				'link' => admin_url("admin.php?page=all_sort_noticeheader&place=nwindow"),
				'title' => __('notification window','pn'),
				'background' => '',
				'default' => 'nwindow',
			);			
			
			$places_t = array('text', 'window', 'nwindow');
										
			$form->select_box($place, $selects, __('Setting up','pn'));	
			
			if(in_array($place,$places_t)){
				$notice_display = 0;
				if($place == 'window'){ $notice_display = 1; }
				if($place == 'nwindow'){ $notice_display = 2; }
				
				$datas = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."notice_head WHERE auto_status = '1' AND status = '1' AND notice_display = '$notice_display' ORDER BY site_order ASC");	
				$sort_list = array();
				foreach($datas as $item){
					$sort_list[0][] = array(
						'title' => pn_strip_input(ctv_ml($item->text)),
						'id' => $item->id,
						'number' => $item->id,
					);		
				}
			
				$form->sort_one_screen($sort_list);	

				$form->sort_js('.thesort ul', pn_link('','post'));
			}
		}
	}

	if(!function_exists('def_premium_action_all_sort_noticeheader')){
		add_action('premium_action_all_sort_noticeheader','def_premium_action_all_sort_noticeheader');
		function def_premium_action_all_sort_noticeheader(){
		global $wpdb;	

			if(current_user_can('read')){
				$number = is_param_post('number');
				$y = 0;
				if(is_array($number)){	
					foreach($number as $theid){ $y++;
						$theid = intval($theid);
						$wpdb->query("UPDATE ".$wpdb->prefix."notice_head SET site_order='$y' WHERE id = '$theid'");	
					}	
				}
			}
		} 
	}
}	