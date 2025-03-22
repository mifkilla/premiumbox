<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_parser_logs', 'pn_adminpage_title_pn_parser_logs');
	function pn_adminpage_title_pn_parser_logs(){
		_e('Logs','pn');
	}
	
	add_action('pn_adminpage_content_pn_parser_logs','def_pn_admin_content_pn_parser_logs');
	function def_pn_admin_content_pn_parser_logs(){
		premium_table_list();	
	} 

	add_action('premium_action_pn_parser_logs','def_premium_action_pn_parser_logs');
	function def_premium_action_pn_parser_logs(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator'));	

		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){
			do_action('pntable_parserlogs_save');
			$reply = '&reply=true';
		} elseif(isset($_POST['delete_all'])){
			$wpdb->query("DELETE FROM ".$wpdb->prefix."parser_logs");
			do_action('pntable_parserlogs_deleteall');
			$reply = '&reply=true';		
		} else {
			if(isset($_POST['id']) and is_array($_POST['id'])){									
				do_action('pntable_parserlogs_action', $action, $_POST['id']);
				$reply = '&reply=true';		
			} 			
		}		
								
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	}

	class pn_parser_logs_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
			
			$this->primary_column = 'date';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'title_birg'){ 
				return pn_strip_input($item->title_birg);	
			} elseif($column_name == 'key_birg'){
				return pn_strip_input($item->key_birg);
			} elseif($column_name == 'comment'){	
				return pn_strip_input($item->log_comment);
			} elseif($column_name == 'status'){	
				if($item->log_code == 1){ 
					return '<span class="bred">'. __('error','pn') .'</span>'; 
				} else { 
					return '<span class="bgreen">'. __('success','pn') .'</span>'; 
				}	
			} elseif($column_name == 'date'){
				return get_pn_time($item->work_date, "d.m.Y, H:i:s");	
			}
				return '';
		}		
		
		function get_columns(){
			$columns = array(         
				'date'     => __('Date','pn'),
				'title_birg'    => __('Source key','pn'),
				'key_birg'    => __('Source name','pn'),
				'comment'  => __('Comment','pn'),	
				'status'  => __('Status','pn'),
			);
			return $columns;
		}		
		
		function tr_class($tr_class, $item) {
			if($item->log_code == 1){
				$tr_class[] = 'tr_red';
			}
			return $tr_class;
		}	
		
		function get_search(){
			$search = array();
			$search[] = array(
				'view' => 'input',
				'title' => __('Source','pn'),
				'default' => pn_strip_input(is_param_get('birg')),
				'name' => 'birg',
			);		
			$search[] = array(
				'view' => 'date',
				'title' => __('Start date','pn'),
				'default' => is_pn_date(is_param_get('date1')),
				'name' => 'date1',
			);
			$search[] = array(
				'view' => 'date',
				'title' => __('End date','pn'),
				'default' => is_pn_date(is_param_get('date2')),
				'name' => 'date2',
			);	
			return $search;
		}
		
		function get_submenu(){
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('success','pn'),
					'2' => __('error','pn'),
				),
			);	
			return $options;
		}
		
		function get_sortable_columns() {
			$sortable_columns = array( 
				'date'     => array('id', 'desc'),
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
			
			$birg = pn_sfilter(pn_strip_input(is_param_get('birg')));
			if($birg){
				$where .= " AND key_birg LIKE '%$birg%'";
			}
			
			$date1 = is_pn_date(is_param_get('date1'));
			if($date1){
				$date = get_pn_date($date1, 'Y-m-d');
				$where .= " AND work_date >= '$date'";
			}
			
			$date2 = is_pn_date(is_param_get('date2'));
			if($date2){
				$date = get_pn_date($date2, 'Y-m-d');
				$where .= " AND work_date < '$date'";
			}

			$filter = intval(is_param_get('filter'));
			if($filter == 1){
				$where .= " AND log_code = '0'";
			} elseif($filter == 2){
				$where .= " AND log_code = '1'";
			}		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."parser_logs WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."parser_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {		  	
		?>
			<input type="submit" name="delete_all" style="background: #f4eaee;" value="<?php _e('Delete logs','pn'); ?>">	
		<?php 
		}		
	}
}