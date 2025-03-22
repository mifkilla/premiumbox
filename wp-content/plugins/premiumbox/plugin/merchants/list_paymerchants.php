<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_paymerchants', 'pn_admin_title_pn_paymerchants');
	function pn_admin_title_pn_paymerchants($page){
		_e('Automatic payouts','pn');
	} 

	add_action('pn_adminpage_content_pn_paymerchants','def_adminpage_content_pn_paymerchants');
	function def_adminpage_content_pn_paymerchants(){
		premium_table_list();
	}		

	add_action('premium_action_pn_paymerchants','def_premium_action_pn_paymerchants');
	function def_premium_action_pn_paymerchants(){
	global $wpdb, $premiumbox;	

		only_post();
		pn_only_caps(array('administrator','pn_merchants'));

		$reply = '';
		$action = get_admin_action();
		if(isset($_POST['id']) and is_array($_POST['id'])){

			$extended = get_option('extlist_paymerchants');
			if(!is_array($extended)){ $extended = array(); }

			if($action == 'active'){	
				foreach($_POST['id'] as $id){
					$id = is_extension_name($id);
					if($id){
						if(isset($extended[$id])){
							$extended[$id]['status'] = 1;
							$id_name = is_isset($extended[$id], 'script');
							
							include_extanded($premiumbox, 'paymerchants', $id_name);
							
							if($id_name){
								do_action('ext_paymerchants_active_'. $id_name, $id);
								do_action('ext_paymerchants_active', $id_name, $id);
							}	
						}
					}	
				}	
			}

			if($action == 'deactive'){		
				foreach($_POST['id'] as $id){
					$id = is_extension_name($id);
					if($id){
						if(isset($extended[$id])){
							$extended[$id]['status'] = 0;
							$id_name = is_isset($extended[$id], 'script');
							
							include_extanded($premiumbox, 'paymerchants', $id_name);
							
							if($id_name){
								do_action('ext_paymerchants_deactive_'. $id_name, $id);
								do_action('ext_paymerchants_deactive', $id_name, $id);	
							}	
						}		
					}	
				}
			}

			if($action == 'delete'){		
				foreach($_POST['id'] as $id){
					$id = is_extension_name($id);
					if($id){
						if(isset($extended[$id])){
							$id_name = is_isset($extended[$id], 'script');
							unset($extended[$id]);
							
							include_extanded($premiumbox, 'paymerchants', $id_name);
							
							if($id_name){
								do_action('ext_paymerchants_delete_'. $id_name, $id);
								do_action('ext_paymerchants_delete', $id_name, $id);
							}	
						}		
					}	
				}
			}	

			update_option('extlist_paymerchants', $extended);
			$reply = '&reply=true';
		} 
				
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
	} 

	add_action('premium_action_pn_paymerchants_activate','def_premium_action_pn_paymerchants_activate');
	function def_premium_action_pn_paymerchants_activate(){
	global $wpdb, $premiumbox;	

		pn_only_caps(array('administrator','pn_merchants'));
		
		$id = is_extension_name(is_param_get('key'));	
		if($id){
			
			$extended = get_option('extlist_paymerchants');
			if(!is_array($extended)){ $extended = array(); }
				
			if(isset($extended[$id])){
				$extended[$id]['status'] = 1;
				$id_name = is_isset($extended[$id], 'script');
				
				include_extanded($premiumbox, 'paymerchants', $id_name);
				
				if($id_name){
					do_action('ext_paymerchants_active_'. $id_name, $id);
					do_action('ext_paymerchants_active', $id_name, $id);
				}	
			}	

			update_option('extlist_paymerchants', $extended);
			
		}
			
		$url = pn_admin_filter_data(is_param_get('_wp_http_referer'), 'reply') . '&reply=true';
		wp_redirect($url);
		exit;		
	}

	add_action('premium_action_pn_paymerchants_deactivate','def_premium_action_pn_paymerchants_deactivate');
	function def_premium_action_pn_paymerchants_deactivate(){
	global $wpdb, $premiumbox;	

		pn_only_caps(array('administrator','pn_merchants'));
		
		$id = is_extension_name(is_param_get('key'));	
		if($id){
			
			$extended = get_option('extlist_paymerchants');
			if(!is_array($extended)){ $extended = array(); }
				
			if(isset($extended[$id])){
				$extended[$id]['status'] = 0;
				$id_name = is_isset($extended[$id], 'script');
				
				include_extanded($premiumbox, 'paymerchants', $id_name);
				
				if($id_name){
					do_action('ext_paymerchants_deactive_'. $id_name, $id);
					do_action('ext_paymerchants_deactive', $id_name, $id);
				}	
			}	

			update_option('extlist_paymerchants', $extended);
		
		}

		$url = pn_admin_filter_data(is_param_get('_wp_http_referer'), 'reply') . '&reply=true';
		wp_redirect($url);
		exit;	
	}	

	class pn_paymerchants_Table_List extends PremiumTable {

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
			
			if($column_name == 'title'){	
				return '<strong>'. pn_strip_input($item['title']) .'</strong>';
			} elseif($column_name == 'script'){	
				$script = is_isset($item, 'script');
				$theme = '';
				if(strstr($script, '_theme')){
					$theme = ' (' . __('Theme','pn') . ')';
				}
				$script = str_replace('_theme','', $script);
				return $script . $theme;
			} elseif($column_name == 'settings'){	
				return apply_filters('paymerchants_settingtext_' . is_isset($item, 'script'), '<span class="bgreen">'. __('ok','pn') .'</span>', $item['name']);
			} elseif($column_name == 'security'){	
				return apply_filters('paymerchants_security_' . is_isset($item, 'script'), '<span class="bgreen">'. __('ok','pn') .'</span>', $item['name']);	
			} elseif($column_name == 'status'){
				$status = intval(is_isset($item,'status'));
				if($status != 1){ 
					return '<span class="bred">'. __('inactive automatic payout','pn') .'</span>'; 
				} else { 
					return '<span class="bgreen">'. __('active automatic payout','pn') .'</span>'; 
				}	
			}
			return '';
		}	
		
		function get_search(){
			$search = array();
			$search[] = array(
				'view' => 'input',
				'title' => __('Name','pn'),
				'default' => pn_strip_input(is_param_get('title')),
				'name' => 'title',
			);									
			return $search;
		}	
		
		function get_submenu(){
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('active automatic payout','pn'),
					'2' => __('inactive automatic payout','pn'),
				),
				'title' => '',
			);		
			return $options;		
		}		
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item['name'] .'" />';              
		}

		function get_row_actions($item){
			$actions = array();
			$status = intval(is_isset($item,'status'));
			if($status == 1){
				$actions['deactive']  = '<a href="'. pn_link('pn_paymerchants_deactivate', 'post') . '&key=' . $item['name'] . '&_wp_http_referer=' . urlencode($_SERVER['REQUEST_URI']) .'">'. __('Deactivate','pn') .'</a>';
			} else {
				$actions['active']  = '<a href="'. pn_link('pn_paymerchants_activate', 'post') . '&key=' . $item['name'] . '&_wp_http_referer=' . urlencode($_SERVER['REQUEST_URI']) .'">'. __('Activate','pn') .'</a>';
			}
			$actions['edit'] = '<a href="'. admin_url('admin.php?page=pn_add_paymerchants&item_key='. is_isset($item, 'name')) .'">'. __('Settings','pn') .'</a>';
			return $actions;
		}			
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __('Name','pn'),
				'settings'     => __('Settings','pn'),
				'script'     => __('Folder name','pn'),
				'status'     => __('Status','pn'),
				'security'     => __('Security','pn'),
			);
			return $columns;
		}	
		
		function tr_class($tr_class, $item) {
			$status = intval(is_isset($item,'status'));
			if($status != 1){
				$tr_class[] = 'tr_red';
			} 
				return $tr_class;
		}		

		function get_bulk_actions() {
			$actions = array(
				'active'    => __('Activated','pn'),
				'deactive'    => __('Deactivated','pn'),
				'delete'    => __('Delete','pn'),
			);
			return $actions;
		}
		
		function prepare_items(){
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$list = get_option('extlist_paymerchants');
			if(!is_array($list)){ $list = array(); }

			$items = array();
			$filter = intval(is_param_get('filter'));
			$title = mb_strtolower(pn_strip_input(is_param_get('title')));
			
			foreach($list as $list_key => $list_value){
				$module_status = intval(is_isset($list_value,'status'));
				$module_title = mb_strtolower(is_isset($list_value,'title'));
				
				$show = 0;
				
				if($filter == 1){
					if($module_status == 1){
						$show = 1;
					}
				} elseif($filter == 2){
					if($module_status == 0){
						$show = 1;
					}			
				} else {
					$show = 1;
				}
				
				if(strlen($title) > 0){
					if(!strstr($module_title, $title)){
						$show = 0;
					}
				}							
				
				if($show == 1){
					$items[$list_key] = $list_value;
					$items[$list_key]['name'] = $list_key;
				}
			}
			
			$items = pn_array_sort($items, 'title');
			
			if($this->navi == 1){
				$this->total_items = count($items);
			}
			$this->items = array_slice($items, $offset, $per_page);
		}
		
 		function extra_tablenav( $which ) {
			?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_paymerchants');?>"><?php _e('Add new','pn'); ?></a>
			<?php
		} 		
	}
}