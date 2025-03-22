<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_discount', 'pn_adminpage_title_pn_discount');
	function pn_adminpage_title_pn_discount(){
		_e('User discounts','pn');
	}

	add_action('pn_adminpage_content_pn_discount','def_pn_adminpage_content_pn_discount');
	function def_pn_adminpage_content_pn_discount(){
		premium_table_list();
	}

	add_action('premium_action_pn_discount','def_premium_action_pn_discount');
	function def_premium_action_pn_discount(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_discount'));
		
		$reply = '';
		$action = get_admin_action();
		if(isset($_POST['save'])){				
			do_action('pntable_discount_save');
			$reply = '&reply=true';
		} else {
			if(isset($_POST['id']) and is_array($_POST['id'])){				
				if($action == 'delete'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);		
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."user_discounts WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_discount_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."user_discounts WHERE id = '$id'");
								do_action('item_discount_delete', $id, $item, $result);
							}
						}	
					}			
				}	
				do_action('pntable_discount_action', $action, $_POST['id']);
				$reply = '&reply=true';
			}
		}	
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	} 

	class pn_discount_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'discount'){
				return is_sum($item->discount) . '%';	
			} elseif($column_name == 'title'){
				return is_sum($item->sumec);
			} 
			
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_discount&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}	

		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',          
				'title'     => __('Total amount of exchanges (in USD)','pn'),
				'discount'    => __('Discount (%)','pn'),
			);
			return $columns;
		}	
		
		function get_bulk_actions() {
			$actions = array(
				'delete'    => __('Delete','pn'),
			);
			return $actions;
		}
		
		function get_sortable_columns() {
			$sortable_columns = array( 
				'discount'     => array('(discount -0.0)', 'desc'),
				'title'     => array('(sumec -0.0)',false),
			);
			return $sortable_columns;
		}	
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('(discount -0.0)');
			$order = $this->db_order('DESC');		

			$where = $this->search_where('');
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."user_discounts WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."user_discounts WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ){
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_discount');?>"><?php _e('Add new','pn'); ?></a>
		<?php
		}	  
	}
}