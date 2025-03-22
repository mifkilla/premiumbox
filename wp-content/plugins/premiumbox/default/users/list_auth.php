<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('all_alogs_Table_List') and is_admin()){

	add_action('pn_adminpage_title_all_alogs', 'pn_adminpage_title_all_alogs');
	function pn_adminpage_title_all_alogs(){
		_e('Authorization log','pn');
	}
	
	add_action('pn_adminpage_content_all_alogs','def_pn_admin_content_all_alogs');
	function def_pn_admin_content_all_alogs(){
		premium_table_list();		
	} 

	add_action('premium_action_all_alogs','def_premium_action_all_alogs');
	function def_premium_action_all_alogs(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator'));	

		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){
			do_action('pntable_alogs_save');
			$reply = '&reply=true';
		} else {
			if(isset($_POST['id']) and is_array($_POST['id'])){									
				do_action('pntable_alogs_action', $action, $_POST['id']);
				$reply = '&reply=true';		
			} 			
		}		
								
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	}

	class all_alogs_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
			
			$this->primary_column = 'date';
			$this->save_button = 0;
			
		}
		
		function column_default($item, $column_name){
			if($column_name == 'user'){
				$user_id = $item->user_id;
				$us = '';
				if($user_id > 0){
					$us ='<a href="'. pn_edit_user_link($user_id) .'">' . is_user($item->user_login) . '</a>';
				} 
				return $us;	
			} elseif($column_name == 'browser'){
				return get_browser_name($item->now_user_browser, __('Unknown','pn'));
			} elseif($column_name == 'ip'){	
				return pn_strip_input($item->now_user_ip);
			} elseif($column_name == 'old_browser'){
				return get_browser_name($item->old_user_browser, __('Unknown','pn'));
			} elseif($column_name == 'old_ip'){	
				return pn_strip_input($item->old_user_ip);
			} elseif($column_name == 'status'){	
				if($item->auth_status == 0){ 
					return '<span class="bred">'. pn_strip_input($item->auth_status_text) .'</span>'; 
				} else { 
					return '<span class="bgreen">'. __('successful authentication','pn') .'</span>'; 
				}	
			} elseif($column_name == 'date'){
				return get_pn_time($item->auth_date, "d.m.Y, H:i:s");	
			}
				return '';
		}		
		
		function get_columns(){
			$columns = array(         
				'date'     => __('Date','pn'),
				'user'    => __('User','pn'),
				'ip'    => __('Current IP address','pn'),
				'browser'  => __('Current browser','pn'),
				'old_ip'    => __('Previous IP address','pn'),
				'old_browser'  => __('Previous browser','pn'),	
				'status'  => __('Status','pn'),
			);
			return $columns;
		}		
		
		function tr_class($tr_class, $item) {
			if($item->auth_status == 0){
				$tr_class[] = 'tr_red';
			}
			return $tr_class;
		}	
		
		function get_search(){
			$search = array();
			$search[] = array(
				'view' => 'input',
				'title' => __('User','pn'),
				'default' => pn_strip_input(is_param_get('user')),
				'name' => 'user',
			);		
			$search[] = array(
				'view' => 'date',
				'title' => __('Start date','pn'),
				'default' => pn_strip_input(is_param_get('date1')),
				'name' => 'date1',
			);
			$search[] = array(
				'view' => 'date',
				'title' => __('End date','pn'),
				'default' => pn_strip_input(is_param_get('date2')),
				'name' => 'date2',
			);	
			return $search;
		}
		
		function get_submenu(){
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('successful authentication','pn'),
					'2' => __('unsuccessful authentication','pn'),
				),
				'title' => '',
			);	
			return $options;
		}
		
		function get_sortable_columns() {
			$sortable_columns = array( 
				'date'     => array('auth_date', 'desc'),
			);
			return $sortable_columns;
		}		
		
		function prepare_items(){
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();		
			
			$orderby = $this->db_orderby('auth_date');
			$order = $this->db_order('DESC');
			
			$where = '';
			
			$user = pn_sfilter(pn_strip_input(is_param_get('user')));
			if($user){
				$where .= " AND user_login LIKE '%$user%'";
			}
			
			$date1 = is_pn_date(is_param_get('date1'));
			if($date1){
				$date = get_pn_date($date1, 'Y-m-d');
				$where .= " AND auth_date >= '$date'";
			}
			
			$date2 = is_pn_date(is_param_get('date2'));
			if($date2){
				$date = get_pn_date($date2, 'Y-m-d');
				$where .= " AND auth_date < '$date'";
			}

			$filter = intval(is_param_get('filter'));
			if($filter == 1){
				$where .= " AND auth_status = '1'";
			} elseif($filter == 2){
				$where .= " AND auth_status = '0'";
			}		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."auth_logs WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."auth_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}	  
	}
}