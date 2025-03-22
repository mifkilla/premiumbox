<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_currency_codes', 'def_adminpage_title_pn_currency_codes');
	function def_adminpage_title_pn_currency_codes(){
		_e('Currency codes','pn');
	}

	add_action('pn_adminpage_content_pn_currency_codes','def_pn_adminpage_content_pn_currency_codes');
	function def_pn_adminpage_content_pn_currency_codes(){
		premium_table_list();
	}

	add_action('premium_action_pn_currency_codes','def_premium_action_pn_currency_codes');
	function def_premium_action_pn_currency_codes(){
	global $wpdb;
		
		only_post();
		
		pn_only_caps(array('administrator','pn_currency'));
		
		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){
			
			if(current_user_can('administrator') or current_user_can('pn_change_ir')){
				if(isset($_POST['internal_rate']) and is_array($_POST['internal_rate'])){
					foreach($_POST['internal_rate'] as $id => $internal_rate){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_codes WHERE id='$id'");
						if(isset($item->id)){
							$internal_rate = is_sum($internal_rate);
							if($internal_rate <= 0){ $internal_rate = 1; }
									
							$arr = array();				
							$arr['internal_rate'] = $internal_rate;
							$arr['edit_date'] = current_time('mysql');
							$wpdb->update($wpdb->prefix.'currency_codes', $arr, array('id'=>$id));					
						}
					}
				}
			}
					
			do_action('pntable_currency_codes_save');

			$reply = '&reply=true';
			
		} else {
			if(isset($_POST['id']) and is_array($_POST['id'])){	
			
				if($action == 'basket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_codes WHERE id='$id' AND auto_status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_currency_code_basket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."currency_codes SET auto_status = '0' WHERE id = '$id'");
								do_action('item_currency_code_basket', $id, $item, $result);
							}
						}		
					}	
				}
					
				if($action == 'unbasket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_codes WHERE id='$id' AND auto_status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_currency_code_unbasket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."currency_codes SET auto_status = '1' WHERE id = '$id'");
								do_action('item_currency_code_unbasket', $id, $item, $result);
							}
						}		
					}	
				}			
			
				if($action == 'delete'){
					foreach($_POST['id'] as $id){
						$id = intval($id);		
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_codes WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_currency_code_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."currency_codes WHERE id = '$id'");
								do_action('item_currency_code_delete', $id, $item, $result);
							}   
						}
					}		
				}
				
				do_action('pntable_currency_codes_action', $action, $_POST['id']);
				$reply = '&reply=true';
			} 
		}
						
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	} 

	class pn_currency_codes_Table_List extends PremiumTable {
		
		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$save_button = 0;
			if(current_user_can('administrator') or current_user_can('pn_change_ir')){
				$save_button = 1;
			}
			$this->save_button = $save_button;
		}
		
		function get_thwidth(){
			$arr = array();
			$arr['id'] = '50px';
			return $arr;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'id'){
				return $item->id;
			} elseif($column_name == 'reserve'){
				return get_sum_color(get_reserv_currency_code($item->id));
			} elseif($column_name == 'od'){	
				$standart_course_cc = apply_filters('standart_course_cc', 0, $item);
				$standart_course_cc = intval($standart_course_cc);
				if(current_user_can('administrator') or current_user_can('pn_change_ir')){
					if($standart_course_cc == 0){
						return '<input type="text" style="width: 100px;" name="internal_rate['. $item->id .']" value="'. is_cc_rate($item->id, $item) .'" />';
					}	
				} 
				return is_cc_rate($item->id, $item);
			} elseif($column_name == 'title'){
				return is_site_value($item->currency_code_title);
			} 
			return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_currency_codes&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}		
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'id' => __('ID','pn'),
				'title'     => __('Currency code','pn'),
				'reserve'     => __('Reserve','pn'),
				'od'    => __('Internal rate per','pn'). ' 1 '. cur_type() .'',
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
		
		function get_sortable_columns() {
			$sortable_columns = array( 
				'id'     => array('id', 'desc'),
				'title'     => array('currency_code_title', false),
				'od' => array('(internal_rate -0.0)', false),
			);
			return $sortable_columns;
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
			if($filter == 9){	
				$where .= " AND auto_status = '0'";
			} else {
				$where .= " AND auto_status = '1'";
			}			
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."currency_codes WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."currency_codes WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_currency_codes');?>"><?php _e('Add new','pn'); ?></a>
		<?php
		}	 
	}
}	