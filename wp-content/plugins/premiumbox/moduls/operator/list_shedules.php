<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_schedule_operators', 'def_adminpage_title_pn_schedule_operators');
	function def_adminpage_title_pn_schedule_operators(){
		_e('Schedules','pn');
	}

	add_action('pn_adminpage_content_pn_schedule_operators','def_pn_admin_content_pn_schedule_operators');
	function def_pn_admin_content_pn_schedule_operators(){
		premium_table_list();	
	}

	add_action('premium_action_pn_schedule_operators','def_premium_action_pn_schedule_operators');
	function def_premium_action_pn_schedule_operators(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator'));
		
		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){
			do_action('pntable_schedule_operators_save');				
			$reply = '&reply=true';						
		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){	

				if($action == 'basket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."schedule_operators WHERE id='$id' AND auto_status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_schedule_operators_basket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."schedule_operators SET auto_status = '0' WHERE id = '$id'");
								do_action('item_schedule_operators_basket', $id, $item, $result);
							}
						}		
					}	
				}
					
				if($action == 'unbasket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."schedule_operators WHERE id='$id' AND auto_status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_schedule_operators_unbasket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."schedule_operators SET auto_status = '1' WHERE id = '$id'");
								do_action('item_schedule_operators_unbasket', $id, $item, $result);
							}
						}		
					}	
				}
			
				if($action == 'delete'){						
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."schedule_operators WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_schedule_operators_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."schedule_operators WHERE id = '$id'");
								do_action('item_schedule_operators_delete', $id, $item, $result);
							}
						}
					}
				}
				
				do_action('pntable_schedule_operators_action', $action, $_POST['id']);
				$reply = '&reply=true';			
			}
		}	
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	} 

	class pn_schedule_operators_Table_List extends PremiumTable {
		
		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'status';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			
			if($column_name == 'wtime'){
				return $item->h1 .':'. $item->m1 .'-'. $item->h2 .':'. $item->m2;
			} elseif($column_name == 'wdays'){
				$days = array(
					'd1' => __('monday','pn'),
					'd2' => __('tuesday','pn'),
					'd3' => __('wednesday','pn'),
					'd4' => __('thursday','pn'),
					'd5' => __('friday','pn'),
					'd6' => '<span class="bred">'. __('saturday','pn') .'</span>',
					'd7' => '<span class="bred">'. __('sunday','pn') .'</span>',
				);
				$ndays = array();
				foreach($days as $k => $v){
					if(is_isset($item, $k) == 1){
						$ndays[] = $v;
					}
				}
			
				echo join(', ',$ndays);
			} elseif($column_name == 'status'){
				global $premiumbox;
				return pn_strip_text(ctv_ml($premiumbox->get_option('statuswork','text'. $item->status)));			
			} 
				return '';
		}			
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}	
		
		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_schedule_operators&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}	
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'status'     => __('Status','pn'),
				'wtime'     => __('Work time','pn'),
				'wdays'     => __('Work days','pn'),
			);
			return $columns;
		}	
		
		function get_submenu(){
			$options = array();				
			$options['filter'] = array(
				'options' => array(
					'1' => __('published','pn'),
					'9' => __('in basket','pn'),
				),
			);	
			return $options;
		}		
		
		function get_bulk_actions() {
			$actions = array(
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
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('DESC');
			
			$where = '';

			$filter = intval(is_param_get('filter'));
			if($filter == 9){	
				$where .= " AND auto_status = '0'";
			} else {
				$where .= " AND auto_status = '1'";
			}			
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."schedule_operators WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."schedule_operators WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav($which){
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_schedule_operators');?>"><?php _e('Add new','pn'); ?></a>
			<?php
		}	  
	} 
}	