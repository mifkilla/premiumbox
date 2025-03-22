<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_payouts', 'def_adminpage_title_pn_payouts');
	function def_adminpage_title_pn_payouts(){
		_e('Payouts','pn');
	} 

	add_action('pn_adminpage_content_pn_payouts','def_pn_adminpage_content_pn_payouts');
	function def_pn_adminpage_content_pn_payouts(){
		premium_table_list();	
	} 		

	add_action('premium_action_pn_payouts','def_premium_action_pn_payouts');
	function def_premium_action_pn_payouts(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_pp'));

		$reply = '';
		$action = get_admin_action();
				
		if(isset($_POST['save'])){	
			do_action('pntable_user_payouts_save');
			$reply = '&reply=true';		
		} else {
					
			if(isset($_POST['id']) and is_array($_POST['id'])){				
					
				if($action == 'basket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."user_payouts WHERE id='$id' AND auto_status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_user_payouts_basket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."user_payouts SET auto_status = '0' WHERE id = '$id'");
								do_action('item_user_payouts_basket', $id, $item, $result);
							}
						}		
					}	
				}
					
				if($action == 'unbasket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."user_payouts WHERE id='$id' AND auto_status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_user_payouts_unbasket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."user_payouts SET auto_status = '1' WHERE id = '$id'");
								do_action('item_user_payouts_unbasket', $id, $item, $result);
							}
						}		
					}	
				}					
					
				if($action=='wait'){
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."user_payouts WHERE id='$id' AND status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_user_payouts_wait_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."user_payouts SET status = '0' WHERE id = '$id'");
								do_action('item_user_payouts_wait', $id, $item, $result);
							}
						}
					}
				}
				if($action=='success'){
					foreach($_POST['id'] as $id){
						$id = intval($id);		
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."user_payouts WHERE id='$id' AND status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_user_payouts_success_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."user_payouts SET status = '1' WHERE id = '$id'");
								do_action('item_user_payouts_success', $id, $item, $result);
							}
						}
					}	
				}
				if($action=='not'){
					foreach($_POST['id'] as $id){
						$id = intval($id);		
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."user_payouts WHERE id='$id' AND status != '2'");
						if(isset($item->id)){
							$res = apply_filters('item_user_payouts_not_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."user_payouts SET status = '2' WHERE id = '$id'");
								do_action('item_user_payouts_not', $id, $item, $result);
							}
						}
					}
				}

				if($action=='delete'){
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."user_payouts WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_user_payouts_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."user_payouts WHERE id = '$id'");
								do_action('item_user_payouts_delete', $id, $item, $result);
							}
						}
					}
				}				
				
				do_action('pntable_user_payouts_action', $action, $_POST['id']);
				$reply = '&reply=true';
			} 		
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;		
	} 
	 
	class pn_payouts_Table_List extends PremiumTable { 

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'date';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'user'){
				$user_id = $item->user_id;
				$us = '<a href="'. pn_edit_user_link($user_id) .'">'. is_user($item->user_login) . '</a>';
				return $us;	
			} elseif($column_name == 'date'){
				return pn_strip_input($item->pay_date);
			} elseif($column_name == 'num'){
				return $item->id;
			} elseif($column_name == 'sum'){	
				return is_sum($item->pay_sum) .' '. is_site_value($item->currency_code_title);
			} elseif($column_name == 'sum_or'){	
				return is_sum($item->pay_sum_or) .' '. cur_type();
			} elseif($column_name == 'purse'){
				return pn_strip_input($item->pay_account);	
			} elseif($column_name == 'sys'){
				return pn_strip_input(ctv_ml($item->psys_title));
			} elseif($column_name == 'status'){
				$status = intval($item->status);
				if($status == 0){
					$st = '<span>'. __('Request in progress','pn') .'</span>';
				} elseif($status == 1){
					$st = '<span class="bgreen">'. __('Request completed','pn') .'</span>';
				} elseif($status == 2){
					$st = '<span class="bred">'. __('Request rejected','pn') .'</span>';
				} elseif($status == 3){
					$st = '<span class="bred">'. __('Request is cancelled by user','pn') .'</span>';
				}		
				return $st;
			}
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_payouts&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}	
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',  
				'num'     => __('ID','pn'),
				'date'     => __('Date','pn'),
				'user'    => __('User','pn'),
				'sum'    => __('Amount','pn'),
				'sum_or'    => __('Amount','pn').' '.cur_type(),
				'purse'  => __('Account','pn'),
				'sys'  => __('PS','pn'),
				'status'  => __('Status','pn'),
			);
			return $columns;
		}

		function tr_class($tr_class, $item) {
			if($item->status == 1){
				$tr_class[] = 'tr_blue';
			}
			return $tr_class;
		}			

		function get_search(){
			$search = array();
			$search[] = array(
				'view' => 'input',
				'title' => __('User','pn'),
				'default' => pn_strip_input(is_param_get('suser')),
				'name' => 'suser',
			);	
			return $search;
		}
			
		function get_submenu(){	
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('waiting','pn'),
					'2' => __('paid','pn'),
					'3' => __('cancelled','pn'),
					'4' => __('cancelled by user','pn'),
					'9' => __('in basket','pn'),
				),
			);
			return $options;
		}

		function get_bulk_actions() {
			$actions = array(
				'wait'    => __('Change status to In progress','pn'),
				'success'    => __('Change status to Paid','pn'),
				'not'    => __('Change status to Not paid','pn'),		
				'basket'    => __('In basket','pn'),
			);
			$filter = intval(is_param_get('filter'));
			if($filter == 9){
				$actions = array(
					'unbasket' => __('Restore','pn'),
					'delete' => __('Delete','pn'),
				);
			}			
			return $actions;
		}
		
		function get_sortable_columns() {
			$sortable_columns = array( 
				'num'     => array('id', 'desc'),
				'user'     => array('user_login',false),
				'date'     => array('pay_date',false),
				'sum'     => array('(pay_sum -0.0)',false),
				'sum_or'     => array('(pay_sum_or -0.0)',false),
				'sys'     => array('psys_title',false),
			);
			return $sortable_columns;
		}	
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('DESC');
			
			$where = '';
			$suser = pn_sfilter(pn_strip_input(is_param_get('suser')));
			if($suser){
				$where .= " AND user_login LIKE '%$suser%'";
			}
			
			$filter = intval(is_param_get('filter'));
			if($filter==1){ 
				$where .= " AND status = '0'";
			} elseif($filter==2) {
				$where .= " AND status = '1'";
			} elseif($filter==3) {
				$where .= " AND status = '2'";
			} elseif($filter==4) {
				$where .= " AND status = '3'";			
			} 		
			
			if($filter == 9){	
				$where .= " AND auto_status = '0'";
			} else {
				$where .= " AND auto_status = '1'";
			}			
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."user_payouts WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."user_payouts WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}

		function extra_tablenav( $which ) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_payouts');?>"><?php _e('Add new','pn'); ?></a>
		<?php
		}		
	} 
} 