<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_bidstatus', 'pn_admin_title_pn_bidstatus');
	function pn_admin_title_pn_bidstatus(){
		_e('Orders status','pn');
	}

	add_action('pn_adminpage_content_pn_bidstatus','def_pn_adminpage_content_pn_bidstatus');
	function def_pn_adminpage_content_pn_bidstatus(){
		premium_table_list();
	}

	add_action('premium_action_pn_bidstatus','def_premium_action_pn_bidstatus');
	function def_premium_action_pn_bidstatus(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_bidstatus'));
		
		$reply = '';
		$action = get_admin_action();
			
		if(isset($_POST['save'])){			
			do_action('pntable_bidstatus_save');
			$reply = '&reply=true';
		} else {		
			if(isset($_POST['id']) and is_array($_POST['id'])){											
				if($action == 'delete'){			
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."exchange_bids WHERE status='my{$id}'");
						if($cc == 0){
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."bidstatus WHERE id='$id'");
							if(isset($item->id)){
								$res = apply_filters('item_bidstatus_delete_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."bidstatus WHERE id = '$id'");
									do_action('item_bidstatus_delete', $id, $item, $result);
								}
							}					
						}
					}			
					$reply = '&reply=true';			
				}
				
				do_action('pntable_bidstatus_action', $action, $_POST['id']);
				$reply = '&reply=true';
			} 
		}
				
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
	} 

	class pn_bidstatus_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			global $wpdb;
			
			if($column_name == 'cap'){
				$status_id = $item->id;
				$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."exchange_bids WHERE status='my{$status_id}'");
				return $cc;
			} elseif($column_name == 'title'){
				return pn_strip_input(ctv_ml($item->title));
			} 
			return '';

		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_bidstatus&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}		
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __('Displayed name','pn'),
				'cap'     => __('Amount of orders','pn'),
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
			
			$orderby = $this->db_orderby('status_order');
			$order = $this->db_order('ASC');

			$where = $this->search_where('');
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."bidstatus WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."bidstatus WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {		  	
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_bidstatus');?>"><?php _e('Add new','pn'); ?></a>		
		<?php 
		} 	  
	}
}	