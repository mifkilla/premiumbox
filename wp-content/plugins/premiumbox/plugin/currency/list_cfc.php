<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_cfc', 'def_adminpage_title_pn_cfc');
	function def_adminpage_title_pn_cfc(){
		_e('Custom fields','pn');
	}

	add_action('pn_adminpage_content_pn_cfc','def_pn_adminpage_content_pn_cfc');
	function def_pn_adminpage_content_pn_cfc(){
		premium_table_list();
	}

	add_action('premium_action_pn_cfc','def_premium_action_pn_cfc');
	function def_premium_action_pn_cfc(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_cfc'));	

		$reply = '';
		$action = get_admin_action();
						
		if(isset($_POST['save'])){	
			do_action('pntable_cfc_save');
			$reply = '&reply=true';
		} else {					
						
			if(isset($_POST['id']) and is_array($_POST['id'])){				
					
				if($action == 'basket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_custom_fields WHERE id='$id' AND auto_status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_cfc_basket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."currency_custom_fields SET auto_status = '0' WHERE id = '$id'");
								do_action('item_cfc_basket', $id, $item, $result);
							}
						}		
					}	
				}
					
				if($action == 'unbasket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_custom_fields WHERE id='$id' AND auto_status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_cfc_unbasket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."currency_custom_fields SET auto_status = '1' WHERE id = '$id'");
								do_action('item_cfc_unbasket', $id, $item, $result);
							}
						}		
					}	
				}					
					
				if($action=='active'){
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_custom_fields WHERE id='$id' AND status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_cfc_active_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->update($wpdb->prefix.'currency_custom_fields', array('status'=>'1'), array('id'=>$id));
								do_action('item_cfc_activate', $id, $result);
							}
						}
					}
				}	

				if($action=='deactive'){
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_custom_fields WHERE id='$id' AND status != '0'");
						if(isset($item->id)){		
							$res = apply_filters('item_cfc_deactive_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->update($wpdb->prefix.'currency_custom_fields', array('status'=>'0'), array('id'=>$id));
								do_action('item_cfc_deactivate', $id, $item, $result);
							}
						}
					}
				}	

				if($action=='delete'){
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_custom_fields WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_cfc_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."currency_custom_fields WHERE id = '$id'");
								do_action('item_cfc_delete', $id, $item, $result);
							}
						}
					}
				}
				
				do_action('pntable_cfc_action', $action, $_POST['id']);
				$reply = '&reply=true';		
			}
		}	
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	} 

	class pn_cfc_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){

			if($column_name == 'site_title'){
				return pn_strip_input(ctv_ml($item->cf_name));
			} elseif($column_name == 'uniqueid'){
				return pn_strip_input($item->uniqueid);
			} elseif($column_name == 'title'){	
				return pn_strip_input(ctv_ml($item->tech_name));
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
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_cfc&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}	
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __('Custom field name (technical)','pn'),
				'site_title'     => __('Custom field name','pn'),
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
					'1' => __('active custom fields','pn'),
					'2' => __('not active custom fields','pn'),
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
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
				
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('desc');		

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
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."currency_custom_fields WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."currency_custom_fields WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {		  	
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_cfc');?>"><?php _e('Add new','pn'); ?></a>		
		<?php 
		} 	  
	}
}	