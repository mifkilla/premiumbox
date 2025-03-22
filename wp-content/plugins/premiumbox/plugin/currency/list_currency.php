<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_currency', 'def_adminpage_title_pn_currency');
	function def_adminpage_title_pn_currency(){
		_e('Currency','pn');
	}

	add_action('pn_adminpage_content_pn_currency','def_pn_adminpage_content_pn_currency');
	function def_pn_adminpage_content_pn_currency(){
		premium_table_list();
		?>
	<script type="text/javascript">
	jQuery(function($){
		$(document).on('click', '.js_button_small', function(){
			var id = $(this).attr('data-id');
			var thet = $(this);
			thet.addClass('active');
				
			$('.js_reserve_'+id).html('***');
				
			$('#premium_ajax').show();
			var param ='id=' + id;
				
			$.ajax({
				type: "POST",
				url: "<?php the_pn_link('pn_currency_updatereserv'); ?>",
				dataType: 'json',
				data: param,
				error: function(res, res2, res3){
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},			
				success: function(res)
				{
					$('#premium_ajax').hide();	
					thet.removeClass('active');
						
					if(res['status'] == 'success'){
						$('.js_reserve_'+id).html(res['reserv']);
					}
				}
			});
			
			return false;
		});		
	});
	</script>		
		<?php	
	}

	add_action('premium_action_pn_currency_updatereserv', 'pn_premium_action_pn_currency_updatereserv');
	function pn_premium_action_pn_currency_updatereserv(){
	global $wpdb;

		only_post();
		
		header('Content-Type: application/json; charset=utf-8');
		
		$log = array();
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = '';
		
		if(current_user_can('administrator') or current_user_can('pn_currency')){
			$data_id = intval(is_param_post('id'));
			if($data_id){
				if(function_exists('update_currency_reserv')){ 
					update_currency_reserv($data_id);
				}
				$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency WHERE id='$data_id'");
				if(isset($item->id)){
					$log['status'] = 'success';
					$log['reserv'] = get_sum_color(is_sum($item->currency_reserv, $item->currency_decimal));
				}
			}	
		}  		
			
		echo json_encode($log);
		exit;	
	}

	add_action('premium_action_pn_currency','def_action_pn_currency');
	function def_action_pn_currency(){
	global $wpdb;	

		only_post();
		
		pn_only_caps(array('administrator','pn_currency'));

		$reply = '';
		$action = get_admin_action();
				
		if(isset($_POST['save'])){
					
			if(isset($_POST['currency_decimal']) and is_array($_POST['currency_decimal'])){
				foreach($_POST['currency_decimal'] as $id => $currency_decimal){
					$id = intval($id);
					$currency_decimal = intval($currency_decimal);
					if($currency_decimal < 0){ $currency_decimal = 4; }
								
					$wpdb->query("UPDATE ".$wpdb->prefix."currency SET currency_decimal = '$currency_decimal' WHERE id = '$id'");
				}
			}										
					
			do_action('pntable_currency_save');
			$reply = '&reply=true';

		} else {
					
			if(isset($_POST['id']) and is_array($_POST['id'])){				
				
				if($action == 'basket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency WHERE id='$id' AND auto_status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_currency_basket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."currency SET auto_status = '0' WHERE id = '$id'");
								do_action('item_currency_basket', $id, $item, $result);
							}
						}		
					}	
				}
					
				if($action == 'unbasket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency WHERE id='$id' AND auto_status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_currency_unbasket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."currency SET auto_status = '1' WHERE id = '$id'");
								do_action('item_currency_unbasket', $id, $item, $result);
							}
						}		
					}	
				}
				
				if($action == 'active'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency WHERE id='$id' AND currency_status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_currency_active_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."currency SET currency_status = '1' WHERE id = '$id'");
								do_action('item_currency_active', $id, $item, $result);
							}
						}
					}	
				}

				if($action == 'deactive'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency WHERE id='$id' AND currency_status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_currency_deactive_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."currency SET currency_status = '0' WHERE id = '$id'");
								do_action('item_currency_deactive', $id, $item, $result);
							}
						}
					}
				}					
					
				if($action == 'delete'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_currency_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."currency WHERE id = '$id'");
								do_action('item_currency_delete', $id, $item, $result);
							}
						}
					}		
				}
				
				do_action('pntable_currency_action', $action, $_POST['id']);
				$reply = '&reply=true';
			} 
					
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	} 

	class pn_currency_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 1;
		}
		
		function get_thwidth(){
			$array = array();
			$array['id'] = '30px';
			return $array;
		}	

		function column_default($item, $column_name){
			
			if($column_name == 'id'){
				return $item->id;
			} elseif($column_name == 'code'){
				return is_site_value($item->currency_code_title);		
			} elseif($column_name == 'xml_value'){
				return is_xml_value($item->xml_value);		
			} elseif($column_name == 'reserve'){
				$html = '
				<div class="js_reserve_'. $item->id .'">'. get_sum_color(is_sum($item->currency_reserv, $item->currency_decimal)) .'</div>
				<a href="#" data-id="'. $item->id .'" class="js_button_small">'. __('Update','pn') .'</a>
				';	
				return $html;
			} elseif($column_name == 'received'){
				return is_sum(get_currency_in($item->id), $item->currency_decimal);
			} elseif($column_name == 'issued'){
				return is_sum(get_currency_out($item->id), $item->currency_decimal);		
			} elseif($column_name == 'decimal'){		
				return '<input type="text" style="width: 50px;" name="currency_decimal['. $item->id .']" value="'. intval($item->currency_decimal) .'" />';				
			} elseif($column_name == 'status'){	
				if($item->currency_status == 0){ 
					return '<span class="bred">'. __('inactive currency','pn') .'</span>'; 
				} else { 
					return '<span class="bgreen">'. __('active currency','pn') .'</span>'; 
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
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_currency&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}	

		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'id'     => __('ID','pn'),
				'title'     => __('Currency name','pn'),
				'code' => __('Currency code','pn'),
				'reserve' => __('Reserve','pn'),
				'received' => __('Received','pn').' &larr;',
				'issued' => __('Sent','pn').' &rarr;',
				'decimal' => __('Amount of Decimal places','pn'),
				'xml_value' => __('XML name','pn'),
				'status'    => __('Status','pn'),
			);
			return $columns;
		}

		function tr_class($tr_class, $item) {
			if($item->currency_status != 1){
				$tr_class[] = 'tr_red';
			}
			return $tr_class;
		}	
		
		function get_search(){
			$search = array();
			
			$currency_codes = list_currency_codes(__('All codes','pn'));
			$search[] = array(
				'view' => 'select',
				'title' => __('Code','pn'),
				'default' => pn_strip_input(is_param_get('currency_code_id')),
				'options' => $currency_codes,
				'name' => 'currency_code_id',
			);	
			$psys = list_psys(__('All payment systems','pn'));	
			$search[] = array(
				'view' => 'select',
				'title' => __('Payment system','pn'),
				'default' => pn_strip_input(is_param_get('psys_id')),
				'options' => $psys,
				'name' => 'psys_id',
			);		
			
			return $search;
		}	
			
		function get_submenu(){	
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('active currency','pn'),
					'2' => __('inactive currency','pn'),
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
		
		function get_sortable_columns() {
			$sortable_columns = array( 
				'id'     => array('id', false),
				'title'     => array('psys_title', 'asc'),
				'code' => array('currency_code_title', false),
			);
			return $sortable_columns;
		}	
		
		function prepare_items(){
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
				
			$orderby = $this->db_orderby('psys_title');
			$order = $this->db_order('asc');
			
			$where = '';
			
			$filter = intval(is_param_get('filter'));
			if($filter == 1){ 
				$where .= " AND currency_status='1'"; 
			} elseif($filter == 2){
				$where .= " AND currency_status='0'";
			}

			if($filter == 9){	
				$where .= " AND auto_status = '0'";
			} else {
				$where .= " AND auto_status = '1'";
			}			
			
			$currency_code_id = intval(is_param_get('currency_code_id'));
			if($currency_code_id > 0){ 
				$where .= " AND currency_code_id='$currency_code_id'"; 
			}
			
			$psys_id = intval(is_param_get('psys_id'));
			if($psys_id > 0){ 
				$where .= " AND psys_id='$psys_id'"; 
			}		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."currency WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."currency WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_currency');?>"><?php _e('Add new','pn'); ?></a>		
		<?php 
		}	  
	}
}	