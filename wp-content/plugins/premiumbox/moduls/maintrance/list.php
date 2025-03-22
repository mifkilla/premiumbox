<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_maintrance_list', 'pn_adminpage_title_pn_maintrance_list');
	function pn_adminpage_title_pn_maintrance_list(){
		_e('Maintenance mode','pn');
	}

	add_action('pn_adminpage_content_pn_maintrance_list','def_pn_adminpage_content_pn_maintrance_list');
	function def_pn_adminpage_content_pn_maintrance_list(){
		premium_table_list();
	}

	add_action('premium_action_pn_maintrance_list','def_premium_action_pn_maintrance_list');
	function def_premium_action_pn_maintrance_list(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_maintrance'));
		
		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){
							
			do_action('pntable_maintrance_save');
			$reply = '&reply=true';

		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){						
				if($action == 'delete'){			
					foreach($_POST['id'] as $id){
						$id = intval($id);			
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."maintrance WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_maintrance_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."maintrance WHERE id = '$id'");
								do_action('item_maintrance_delete', $id, $item, $result);
							}
						}
					}	
				}
				do_action('pntable_maintrance_action', $action, $_POST['id']);
				$reply = '&reply=true';			
			} 
		}
				
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
	} 

	class pn_maintrance_list_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'forwhom'){
				$arr = array('0'=>__('For users and administrators','pn'),'1'=>__('For users','pn'));
				return is_isset($arr, $item->for_whom);
			} elseif($column_name == 'title'){
				return pn_strip_text(ctv_ml($item->the_title));
			}
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_maintrance_add&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}		
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __('Title','pn'),
				'forwhom'     => __('Apply mode','pn'),
			);
			return $columns;
		}	

		function get_bulk_actions() {
			$actions = array(
				'delete'    => __('Delete','pn'),
			);
			return $actions;
		}	
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('DESC');
			
			$where = $this->search_where('');
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."maintrance");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."maintrance ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_maintrance_add');?>"><?php _e('Add new','pn'); ?></a>
		<?php
		}	  
	}
}	