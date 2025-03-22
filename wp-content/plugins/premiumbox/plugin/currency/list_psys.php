<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_psys', 'def_adminpage_title_pn_psys');
	function def_adminpage_title_pn_psys(){
		_e('Payment systems','pn');
	}

	add_action('pn_adminpage_content_pn_psys','def_pn_adminpage_content_pn_psys');
	function def_pn_adminpage_content_pn_psys(){
		premium_table_list();
	}

	add_action('premium_action_pn_psys','def_premium_action_pn_psys');
	function def_premium_action_pn_psys(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_currency'));
		
		$reply = '';
		$action = get_admin_action();

		if(isset($_POST['save'])){
			do_action('pntable_psys_save');
			$reply = '&reply=true';
		} else {
			if(isset($_POST['id']) and is_array($_POST['id'])){

				if($action == 'basket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."psys WHERE id='$id' AND auto_status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_psys_basket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."psys SET auto_status = '0' WHERE id = '$id'");
								do_action('item_psys_basket', $id, $item, $result);
							}
						}		
					}	
				}
					
				if($action == 'unbasket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."psys WHERE id='$id' AND auto_status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_psys_unbasket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."psys SET auto_status = '1' WHERE id = '$id'");
								do_action('item_psys_unbasket', $id, $item, $result);
							}
						}		
					}	
				}
				
				if($action == 'delete'){			
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."psys WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_psys_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."psys WHERE id = '$id'");
								do_action('item_psys_delete', $id, $item, $result);
							}
						}
					}			
				}
				
				do_action('pntable_psys_action', $action, $_POST['id']);
				$reply = '&reply=true';
			} 
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	} 

	class pn_psys_Table_List extends PremiumTable {
		
		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}

		function get_thwidth(){
			$array = array();
			$array['id'] = '30px';
			return $array;
		}		
		
		function column_default($item, $column_name){
		
			if($column_name == 'id'){
				return $item->id;
			} elseif($column_name == 'logo'){
				$logo = get_psys_logo($item, 1); 
				if($logo){
					return '<img src="'. $logo .'" style="max-width: 40px; max-height: 40px;" alt="" />';
				}
			} elseif($column_name == 'logo2'){
				$logo = get_psys_logo($item, 2); 
				if($logo){
					return '<img src="'. $logo .'" style="max-width: 40px; max-height: 40px;" alt="" />';
				}
			} elseif($column_name == 'title'){
				return pn_strip_input(ctv_ml($item->psys_title));
			}		
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_psys&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}		
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'id'     => __('ID','pn'),
				'title'     => __('PS title','pn'),
				'logo'     => __('Main logo','pn'),
			);
			if(get_settings_second_logo() == 1){
				$columns['logo2'] = __('Additional logo','pn');
			}	
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
		
		function get_sortable_columns() {
			$sortable_columns = array( 
				'id' => array('id', false),
				'title' => array('psys_title', 'desc'),
			);
			return $sortable_columns;
		}	
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
				
			$orderby = $this->db_orderby('psys_title');
			$order = $this->db_order('desc');	

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
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."psys WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."psys WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav($which){
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_psys');?>"><?php _e('Add new','pn'); ?></a>
		<?php
		}	  
	} 
}	