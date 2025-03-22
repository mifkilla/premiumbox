<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_paymerchantlogs', 'def_adminpage_title_pn_paymerchantlogs');
	function def_adminpage_title_pn_paymerchantlogs(){
		_e('Automatic payouts log','pn');
	} 

	add_action('pn_adminpage_content_pn_paymerchantlogs','def_pn_adminpage_content_pn_paymerchantlogs');
	function def_pn_adminpage_content_pn_paymerchantlogs(){
		premium_table_list();
	}

	add_action('premium_action_pn_paymerchantlogs','def_premium_action_pn_paymerchantlogs');
	function def_premium_action_pn_paymerchantlogs(){
	global $wpdb;
	
		only_post();
		pn_only_caps(array('administrator'));
		
		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){
				
			do_action('pntable_paymerchantlogs_save');	
			$reply = '&reply=true';

		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){
				
				if($action == 'delete'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
								
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."paymerchant_logs WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_paymerchantlogs_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."paymerchant_logs WHERE id = '$id'");
								do_action('item_paymerchantlogs_delete', $id, $item, $result);
							}
						}
					}		
				}				
				
				do_action('pntable_paymerchantlogs_action', $action, $_POST['id']);
				$reply = '&reply=true';		
			} 
		}

		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;					
	} 

	class pn_paymerchantlogs_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'title'){
				return get_pn_time($item->createdate, 'd.m.Y H:i:s');
			} elseif($column_name == 'data'){
				return pn_strip_input($item->mdata);
			} elseif($column_name == 'merchant'){
				return is_extension_name($item->merchant);
			} elseif($column_name == 'bid_id'){
				$bid_id = intval($item->bid_id);
				if($bid_id > 0){
					return $bid_id;
				} else {
					return '-';
				}					
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
				'data'    => __('Data','pn'),
				'merchant'    => __('Automatic payout','pn'),
				'bid_id'    => __('Order ID','pn'),
			);
			return $columns;
		}
		
		function get_search(){
			global $wpdb;
			$options = array();
			$options[0] = '--'. __('All','pn') .'--';
			$items = $wpdb->get_results("SELECT DISTINCT(merchant) FROM ". $wpdb->prefix ."paymerchant_logs ORDER BY merchant ASC");  		
			foreach($items as $item){
				$options[$item->merchant] = is_extension_name($item->merchant);
			}
			
			$search = array();
			$search[] = array(
				'view' => 'select',
				'title' => __('Automatic payout','pn'),
				'default' => is_extension_name(is_param_get('merchant')),
				'options' => $options,
				'name' => 'merchant',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Order ID','pn'),
				'default' => pn_strip_input(is_param_get('bid_id')),
				'name' => 'bid_id',
			);	
			$search[] = array(
				'view' => 'datetime',
				'title' => __('Start date','pn'),
				'default' => pn_strip_input(is_param_get('date1')),
				'name' => 'date1',
			);
			$search[] = array(
				'view' => 'datetime',
				'title' => __('End date','pn'),
				'default' => pn_strip_input(is_param_get('date2')),
				'name' => 'date2',
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
			
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('DESC');
			
			$where = '';

			$merchant = is_extension_name(is_param_get('merchant'));
			if($merchant){ 
				$where .= " AND merchant = '$merchant'";
			}  	
			
			$bid_id = intval(is_param_get('bid_id'));
			if($bid_id){ 
				$where .= " AND bid_id = '$bid_id'";
			}

			$date1 = pn_strip_input(is_param_get('date1'));
			if($date1){
				$date = get_pn_date($date1, 'Y-m-d H:i:s');
				$where .= " AND createdate >= '$date'";
			}
			
			$date2 = pn_strip_input(is_param_get('date2'));
			if($date2){
				$date = get_pn_date($date2, 'Y-m-d H:i:s');
				$where .= " AND createdate <= '$date'";
			}			
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."paymerchant_logs WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."paymerchant_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}	  
	}
}