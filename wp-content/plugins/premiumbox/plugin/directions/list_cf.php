<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_cf', 'def_adminpage_title_pn_cf');
	function def_adminpage_title_pn_cf(){
		_e('Custom fields','pn');
	}

	add_action('pn_adminpage_content_pn_cf','def_pn_adminpage_content_pn_cf');
	function def_pn_adminpage_content_pn_cf(){
		premium_table_list();
	}

	add_action('premium_action_pn_cf','def_premium_action_pn_cf');
	function def_premium_action_pn_cf(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_directions'));
		
		
		$reply = '';
		$action = get_admin_action();
			
		if(isset($_POST['save'])){				
			do_action('pntable_cf_save');
			$reply = '&reply=true';
		} else {
			if(isset($_POST['id']) and is_array($_POST['id'])){				
					
				if($action == 'basket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."direction_custom_fields WHERE id='$id' AND auto_status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_cf_basket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."direction_custom_fields SET auto_status = '0' WHERE id = '$id'");
								do_action('item_cf_basket', $id, $item, $result);
							}
						}		
					}	
				}
					
				if($action == 'unbasket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."direction_custom_fields WHERE id='$id' AND auto_status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_cf_unbasket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."direction_custom_fields SET auto_status = '1' WHERE id = '$id'");
								do_action('item_cf_unbasket', $id, $item, $result);
							}
						}		
					}	
				}
					
				if($action == 'active'){			
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."direction_custom_fields WHERE id='$id' AND status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_cf_active_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."direction_custom_fields SET status = '1' WHERE id = '$id'");
								do_action('item_cf_activate', $id, $item, $result);
							}
						}	
					}						
				}

				if($action == 'deactive'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."direction_custom_fields WHERE id='$id' AND status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_cf_deactive_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."direction_custom_fields SET status = '0' WHERE id = '$id'");
								do_action('item_cf_deactivate', $id, $item, $result);
							}
						}
					}				
				}					
						
				if($action == 'delete'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."direction_custom_fields WHERE id='$id'");
						if(isset($item->id)){	
							$res = apply_filters('item_cf_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."direction_custom_fields WHERE id = '$id'");
								do_action('item_cf_delete', $id, $item, $result);
							}
						}
					}				
				}
					
				do_action('pntable_cf_action', $action, $_POST['id']);
				$reply = '&reply=true';	
			} 
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	} 

	class pn_cf_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			
			if($column_name == 'autofield'){
				$uf = apply_filters('user_fields_in_website', array());
				$now = is_isset($uf, $item->cf_auto);
				return is_isset($now, 'title');
			} elseif($column_name == 'title'){	
				return pn_strip_input(ctv_ml($item->tech_name));	
			} elseif($column_name == 'uniqueid'){
				return pn_strip_input($item->uniqueid);			
			} elseif($column_name == 'site_title'){
				return pn_strip_input(ctv_ml($item->cf_name));
			} elseif($column_name == 'status'){	
				if($item->status == 0){ 
					return '<span class="bred">'. __('inactive field','pn') .'</span>'; 
				} else { 
					return '<span class="bgreen">'. __('active field','pn') .'</span>'; 
				}			
			} 
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_cf&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}	

		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __('Custom field name (technical)','pn'),
				'site_title'     => __('Custom field name','pn'),
				'autofield' => __('Autofill','pn'),
				'uniqueid' => __('Unique ID','pn'),
				'status'    => __('Status','pn'),
			);
			return $columns;
		}	
		
		function tr_class($tr_class, $item) {
			if($item->status != 1){
				$tr_class[] = 'tr_red';
			}
			return $tr_class;
		}
		
		function get_submenu(){
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('active field','pn'),
					'2' => __('inactive field','pn'),
					'9' => __('in basket','pn'),
				),
			);		
			return $options;	
		}
		
		function get_bulk_actions() {
			$actions = array(
				'active'    => __('Activate','pn'),
				'deactive'    => __('Deactivate','pn'),
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
		
		function prepare_items(){
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
				
			$orderby = $this->db_orderby('cf_order');
			$order = $this->db_order('asc');
			
			$where = '';
			
			$filter = intval(is_param_get('filter'));
			if($filter == 1){ 
				$where .= " AND status='1'"; 
			} elseif($filter == 2){
				$where .= " AND status='0'";
			}		

			if($filter == 9){	
				$where .= " AND auto_status = '0'";
			} else {
				$where .= " AND auto_status = '1'";
			}				
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."direction_custom_fields WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."direction_custom_fields WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ){		  	
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_cf');?>"><?php _e('Add new','pn'); ?></a>		
		<?php 
		}	 
	}
}	