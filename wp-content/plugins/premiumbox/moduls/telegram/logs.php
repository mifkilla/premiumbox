<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_all_telegram_logs', 'def_adminpage_title_all_telegram_logs');
	function def_adminpage_title_all_telegram_logs(){
		_e('Telegram logs','pn');
	} 

	add_action('pn_adminpage_content_all_telegram_logs','def_pn_adminpage_content_all_telegram_logs');
	function def_pn_adminpage_content_all_telegram_logs(){
		premium_table_list();
	}

	add_action('premium_action_all_telegram_logs','def_premium_action_all_telegram_logs');
	function def_premium_action_all_telegram_logs(){
	global $wpdb;
	
		only_post();
		pn_only_caps(array('administrator'));
		
		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){
				
			do_action('pntable_telegramlogs_save');	
			$reply = '&reply=true';

		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){
				
				if($action == 'delete'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
								
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."telegram_logs WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_telegramlogs_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."telegram_logs WHERE id = '$id'");
								do_action('item_telegramlogs_delete', $id, $item, $result);
							}
						}
					}		
				}				
				
				do_action('pntable_telegramlogs_action', $action, $_POST['id']);
				$reply = '&reply=true';		
			} 
		}

		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;					
	} 

	class all_telegram_logs_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'title'){
				return get_pn_time($item->create_date, 'd.m.Y H:i:s');
			} elseif($column_name == 'data'){
				return pn_strip_input($item->error_text);
			} elseif($column_name == 'place'){
				$place = intval($item->place);
				if($place == 0){
					return __('Bot log','pn');
				} else {
					return __('User log','pn');
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
				'place'    => __('Type','pn'),
			);
			return $columns;
		}
		
		function get_search(){
			$search = array();
			$search[] = array(
				'view' => 'select',
				'title' => __('Type','pn'),
				'default' => pn_strip_input(is_param_get('place')),
				'options' => array('0'=>__('All','pn'),'1'=>__('Bot log','pn'),'2'=>__('User log','pn')),
				'name' => 'place',
			);		
			return $search;			
		}	

		function get_bulk_actions() {
			$actions = array(		
				'delete'    => __('Delete','pn')
			);
			return $actions;
		}

		function prepare_items(){
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('DESC');
			
			$where = '';

			$place = intval(is_param_get('place'));
			if($place > 0){ 
				$place = $place - 1;
				$where .= " AND place = '$place'";
			} 		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."telegram_logs WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."telegram_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}	  
	}
}