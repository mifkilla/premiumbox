<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_bcc', 'pn_admin_title_pn_bcc');
	function pn_admin_title_pn_bcc(){
		_e('Confirmation log','pn');
	}

	add_action('pn_adminpage_content_pn_bcc','def_pn_adminpage_content_pn_bcc');
	function def_pn_adminpage_content_pn_bcc(){
		premium_table_list();	
	}

	add_action('premium_action_pn_bcc','def_premium_action_pn_bcc');
	function def_premium_action_pn_bcc(){
	global $wpdb;
	
		only_post();
		pn_only_caps(array('administrator','pn_bids'));
		
		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){
				
			do_action('pntable_bcc_save');	
			$reply = '&reply=true';

		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){
				
				if($action == 'delete'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
								
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."bcc_logs WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_bcc_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."bcc_logs WHERE id = '$id'");
								do_action('item_bcc_delete', $id, $item, $result);
							}
						}
					}		
				}				
				
				do_action('pntable_bcc_action', $action, $_POST['id']);
				$reply = '&reply=true';		
			} 
		}

		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;					
	}

	class pn_bcc_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'bid'){
				return '<a href="'. admin_url('admin.php?page=pn_bids&bidid='.$item->bid_id) .'" target="_blank" rel="noreferrer noopener">'. $item->bid_id .'</a>';
			} elseif($column_name == 'cc'){	
				return intval($item->counter);
			} elseif($column_name == 'title'){	
				return get_pn_time($item->createdate, 'd.m.Y H:i:s');
			}
			return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}		
		
		function get_columns(){
			$columns = array(       
				'cb'        => '<input type="checkbox" />', 
				'title'     => __('Date','pn'),
				'bid'    => __('Order ID','pn'),
				'cc'    => __('Confirmation order number','pn'),
			);
			return $columns;
		}

		function get_search(){
			$search = array();	
			$search[] = array(
				'view' => 'input',
				'title' => __('Order ID','pn'),
				'default' => pn_strip_input(is_param_get('bid_id')),
				'name' => 'bid_id',
			);		
			return $search;
		}
		
		function get_bulk_actions() {
			$actions = array(		
				'delete'    => __('Delete','pn')
			);
			return $actions;
		}		

		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('createdate');
			$order = $this->db_order('DESC');
			
			$where = '';

			$bid_id = intval(is_param_get('bid_id'));	
			if($bid_id){ 
				$where .= " AND bid_id='$bid_id'";
			}		 		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."bcc_logs WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."bcc_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}	  
	}
}