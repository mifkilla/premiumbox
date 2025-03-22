<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_geoip_blacklist')){
		add_action('pn_adminpage_title_all_geoip_blacklist', 'def_adminpage_title_all_geoip_blacklist');
		function def_adminpage_title_all_geoip_blacklist(){
			_e('Blacklist','pn');
		}
	}

	if(!function_exists('def_pn_adminpage_content_all_geoip_blacklist')){
		add_action('pn_adminpage_content_all_geoip_blacklist','def_pn_adminpage_content_all_geoip_blacklist');
		function def_pn_adminpage_content_all_geoip_blacklist(){
			premium_table_list();	
		}
	}

	if(!function_exists('def_premium_action_all_geoip_blacklist')){
		add_action('premium_action_all_geoip_blacklist','def_premium_action_all_geoip_blacklist');
		function def_premium_action_all_geoip_blacklist(){
		global $wpdb;	

			only_post();
			pn_only_caps(array('administrator','pn_geoip'));

			$reply = '';
			$action = get_admin_action();
			
			if(isset($_POST['save'])){
			
				do_action('pntable_geoip_blackip_save');
				$reply = '&reply=true';
			
			} else {
				if(isset($_POST['id']) and is_array($_POST['id'])){			
						
					if($action == 'delete'){	
						foreach($_POST['id'] as $id){
							$id = intval($id);
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."geoip_ips WHERE id='$id'");
							if(isset($item->id)){
								$res = apply_filters('item_geoip_blackip_delete_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."geoip_ips WHERE id = '$id'");
									do_action('item_geoip_blackip_delete', $id, $item, $result);
								}
							}				
						}		
					}

					do_action('pntable_geoip_blackip_action', $action, $_POST['id']);
					$reply = '&reply=true';
				} 
			}
					
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
		} 
	}

	if(!class_exists('all_geoip_blacklist_Table_List')){
		class all_geoip_blacklist_Table_List extends PremiumTable {

			function __construct(){    
				parent::__construct();
					
				$this->primary_column = 'theip';
				$this->save_button = 0;
			}
		
			function column_default($item, $column_name){
				if($column_name == 'theip'){
					return pn_strip_input($item->theip);			
				}  
					return '';
			}	
		
			function column_cb($item){
				return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
			}		
		
			function get_columns(){
				$columns = array(
					'cb'        => '<input type="checkbox" />',          
					'theip'     => __('IP','pn'),
				);
				return $columns;
			}	
		
			function get_bulk_actions() {
				$actions = array(
					'delete'    => __('Delete','pn'),
				);
				return $actions;
			}
		
			function get_search(){
				$search = array();
				$search[] = array(
					'view' => 'input',
					'title' => __('IP','pn'),
					'default' => pn_strip_input(is_param_get('item')),
					'name' => 'item',
				);	
				return $search;
			}		
		
			function prepare_items() {
				global $wpdb; 
				
				$per_page = $this->count_items();
				$current_page = $this->get_pagenum();
				$offset = $this->get_offset();
				
				$orderby = $this->db_orderby('id');
				$order = $this->db_order('DESC');
			
				$where = '';

				$item = pn_sfilter(pn_strip_input(is_param_get('item')));
				if($item){ 
					$where .= " AND theip LIKE '%$item%'";
				}	
				
				$where = $this->search_where($where);
				$select_sql = $this->select_sql('');
				if($this->navi == 1){
					$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."geoip_ips WHERE thetype = '0' $where");
				}
				$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."geoip_ips WHERE thetype = '0' $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
			}
		
			function extra_tablenav( $which ) {
			?>
				<a href="<?php echo admin_url('admin.php?page=all_geoip_addblacklist');?>"><?php _e('Add new','pn'); ?></a>
			<?php
			} 	  
		}
	} 
}	