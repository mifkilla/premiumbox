<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	
	add_action('pn_adminpage_title_pn_moduls', 'def_adminpage_title_pn_moduls');
	function def_adminpage_title_pn_moduls($page){
		_e('Modules','pn');
	}

	add_action('pn_adminpage_content_pn_moduls','def_pn_admin_content_pn_moduls');
	function def_pn_admin_content_pn_moduls(){
		premium_table_list();
	} 

	add_action('premium_action_pn_moduls','def_premium_action_pn_moduls');
	function def_premium_action_pn_moduls(){
	global $wpdb, $premiumbox;	

		only_post();
		pn_only_caps(array('administrator'));

		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['id']) and is_array($_POST['id'])){
			if($action == 'active'){
							
				$extended = get_option('pn_extended');
				if(!is_array($extended)){ $extended = array(); }
							
				foreach($_POST['id'] as $id){
					$id = is_extension_name($id);
					if($id){
						if(!isset($extended['moduls'][$id])){
							$extended['moduls'][$id] = $id;
							
							include_extanded($premiumbox, 'moduls', $id);
							
							do_action('all_moduls_active_'.$id);
							do_action('all_moduls_active', $id);
						}
					}	
				}
				update_option('pn_extended', $extended);
				$premiumbox->plugin_create_pages();
							
				$reply = '&reply=true';		
			}
				
			if($action == 'deactive'){
							
				$extended = get_option('pn_extended');
				if(!is_array($extended)){ $extended = array(); }
								
				foreach($_POST['id'] as $id){
					$id = is_extension_name($id);
					if($id){
						if(isset($extended['moduls'][$id])){
							unset($extended['moduls'][$id]);
							
							do_action('all_moduls_deactive_'.$id);
							do_action('all_moduls_deactive', $id);
						}
					}	
				}
				update_option('pn_extended', $extended);
							
				$reply = '&reply=true';		
			}				
		}
				
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
	} 

	add_action('premium_action_pn_moduls_activate','def_premium_action_pn_moduls_activate');
	function def_premium_action_pn_moduls_activate(){
	global $wpdb, $premiumbox;

		pn_only_caps(array('administrator'));	
		
		$id = is_extension_name(is_param_get('key'));	
		if($id){
			
			$extended = get_option('pn_extended');
			if(!is_array($extended)){ $extended = array(); }
			
			if(!isset($extended['moduls'][$id])){
				$extended['moduls'][$id] = $id;
					
				include_extanded($premiumbox, 'moduls', $id);
				
				do_action('all_moduls_active_'. $id);
				do_action('all_moduls_active', $id);
			}	

			update_option('pn_extended', $extended);
			
			$premiumbox->plugin_create_pages();
		}
		
		$url = pn_admin_filter_data(is_param_get('_wp_http_referer'), 'reply') . '&reply=true';
		wp_redirect($url);
		exit;		
	}

	add_action('premium_action_pn_moduls_deactivate','def_premium_action_pn_moduls_deactivate');
	function def_premium_action_pn_moduls_deactivate(){
	global $wpdb;	

		pn_only_caps(array('administrator'));	
		
		$id = is_extension_name(is_param_get('key'));	
		if($id){
			
			$extended = get_option('pn_extended');
			if(!is_array($extended)){ $extended = array(); }
			
			if(isset($extended['moduls'][$id])){
				unset($extended['moduls'][$id]);
				
				do_action('all_moduls_deactive_'. $id);
				do_action('all_moduls_deactive', $id);
			}	

			update_option('pn_extended', $extended);
			
		}
		
		$url = pn_admin_filter_data(is_param_get('_wp_http_referer'), 'reply') . '&reply=true';
		wp_redirect($url);
		exit;
	}	

	class pn_moduls_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
			$this->count_items = 50;
		}
		
		function get_thwidth(){
			$array = array();
			$array['title'] = '200px';
			return $array;
		}	
		
		function column_default($item, $column_name){
			
			if($column_name == 'descr'){
				$html = '
					<div>'. pn_strip_input(ctv_ml($item['description'])) .'</div>
					<div class="modul_vers">'. __('Version','pn') .': '. pn_strip_input($item['version']) .'</div>
				';
				return $html;
			} elseif($column_name == 'title'){	
				return '<strong>'. pn_strip_input(ctv_ml($item['title'])) .'</strong>';			
			} elseif($column_name == 'category'){	
				return '<a href="'. admin_url('admin.php?page=pn_moduls&cat='. is_isset($item, 'cat')) .'&place='. is_extension_name(is_param_get('place')) .'&filter='. intval(is_param_get('filter')) .'">'. pn_strip_input(ctv_ml($item['category'])) . '</a>';
			} elseif($column_name == 'place'){	
				$place = __('Plugin','pn');
				if(is_isset($item, 'place') == 'theme'){
					$place = __('Theme','pn');
				}
				return '<a href="'. admin_url('admin.php?page=pn_moduls&place='. is_isset($item, 'place')) .'&cat='. is_extension_name(is_param_get('cat')) .'&filter='. intval(is_param_get('filter')) .'">'. $place . '</a>';		
			} elseif($column_name == 'dependent'){	
				return pn_strip_input($item['dependent']);
			} elseif($column_name == 'name'){	
				$name = is_extension_name($item['name']);
				$name = str_replace('_theme','', $name);
				return $name;
			}
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item['name'] .'" />';              
		}		

		function get_row_actions($item){
			$actions = array();
			if($item['status'] == 'active'){
				$actions['deactive']  = '<a href="'. pn_link('pn_moduls_deactivate', 'post') . '&key=' . $item['name'] . '&_wp_http_referer=' . urlencode($_SERVER['REQUEST_URI']) .'">'. __('Deactivate','pn') .'</a>';
			} else {
				$actions['active']  = '<a href="'. pn_link('pn_moduls_activate', 'post') . '&key=' . $item['name'] . '&_wp_http_referer=' . urlencode($_SERVER['REQUEST_URI']) .'">'. __('Activate','pn') .'</a>';
			}
			return $actions;
		}	
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __('Title','pn'),
				'descr'     => __('Description','pn'),
				'category'     => __('Category','pn'),
				'place'     => __('Location','pn'),
				'name'     => __('Folder name','pn'),
				'dependent'     => __('Dependent modules','pn'),
			);
			return $columns;
		}	
		
		function get_search(){
			$search = array();
			
			$list = pn_list_extended('moduls');
			
			$cats = array('0'=>'--'. __('All categories','pn') .'--');
			foreach($list as $data){
				$c = is_extension_name($data['cat']);
				$n = pn_strip_input(ctv_ml($data['category']));
				if($c and $n){
					$cats[$c] = $n;
				}
			}
			asort($cats);
			
			$search[] = array(
				'view' => 'select',
				'options' => $cats,
				'title' => __('Module categories','pn'),
				'default' => is_extension_name(is_param_get('cat')),
				'name' => 'cat',
			);
			
			$placed = array(
				'0' => '--'. __('All locations','pn') .'--',
				'plugin' => __('Plugin','pn'),
				'theme' => __('Theme','pn'),
			);
			$search[] = array(
				'view' => 'select',
				'options' => $placed,
				'title' => __('Module locations','pn'),
				'default' => is_extension_name(is_param_get('place')),
				'name' => 'place',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Title','pn'),
				'default' => pn_strip_input(is_param_get('title')),
				'name' => 'title',
			);			
				
				return $search;
		}
			
		function get_submenu(){
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('active modules','pn'),
					'2' => __('inactive modules','pn'),
					'3' => __('recently active modules','pn'),
					'4' => __('new modules','pn'),
				),
				'title' => '',
			);
			return $options;
		}

		function tr_class($tr_class, $item) {
			if($item['new'] == 1){
				$tr_class[] = 'tr_green';
			} 			
			if($item['status'] != 'active'){
				$tr_class[] = 'tr_red';
			}			
				return $tr_class;
		}			

		function get_bulk_actions() {
			$actions = array(
				'active'    => __('Activate','pn'),
				'deactive'    => __('Deactivate','pn'),
			);
			return $actions;
		}
		
		function prepare_items() {
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();

			$pn_extended_last = get_option('pn_extended_last');
			$extended_last = is_isset($pn_extended_last, 'moduls');

			$now_time = current_time('timestamp');
			$now_time_check = $now_time - (24*60*60);

			$list = pn_list_extended('moduls');
			
			$items = array();
			$filter = intval(is_param_get('filter'));
			$cat = is_extension_name(is_param_get('cat'));
			$place = is_extension_name(is_param_get('place'));
			$title = mb_strtolower(pn_strip_input(is_param_get('title')));
			
			foreach($list as $list_key => $list_value){
				$module_status = $list_value['status'];
				$module_category = is_extension_name($list_value['cat']);
				$module_place = $list_value['place'];
				$module_new = intval($list_value['new']);
				
				$time_deactive = extended_time_deactive($extended_last, $list_key, $list_value['old_names']);
				
				$show = 0;
				
				if($filter == 1){
					if($module_status == 'active'){
						$show = 1;
					}
				} elseif($filter == 2){
					if($module_status == 'deactive'){
						$show = 1;
					}	
				} elseif($filter == 3){
					if($module_status == 'deactive' and $time_deactive > $now_time_check){
						$show = 1;
					}
				} elseif($filter == 4){
					if($module_new == 1){
						$show = 1;
					}	
				} else {
					$show = 1;
				}
				
				if(strlen($title) > 0){
					$module_title = mb_strtolower(ctv_ml(is_isset($list_value,'title')));
					if(!strstr($module_title, $title)){
						$show = 0;
					}
				}
				
				if($cat){
					if($module_category != $cat){
						$show = 0;
					}
				}

				if($place){
					if($module_place != $place){
						$show = 0;
					}
				}			
				
				if($show == 1){
					$items[] = $list_value;
				}
			}	
			if($this->navi == 1){
				$this->total_items = count($items);
			}
			$this->items = array_slice($items, $offset, $per_page);
		}	
	} 
}	