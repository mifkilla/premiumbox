<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('def_adminpage_title_all_emlogs') and is_admin()){
	add_action('pn_adminpage_title_all_emlogs', 'def_adminpage_title_all_emlogs');
	function def_adminpage_title_all_emlogs(){
		_e('E-mail logs','pn');
	} 

	add_action('pn_adminpage_content_all_emlogs','def_pn_adminpage_content_all_emlogs');
	function def_pn_adminpage_content_all_emlogs(){
		premium_table_list();
	}

	add_action('premium_action_all_emlogs','def_premium_action_all_emlogs');
	function def_premium_action_all_emlogs(){
	global $wpdb;
		
		only_post();
		pn_only_caps(array('administrator'));
		
		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){	
			do_action('pntable_emlogs_save');	
			$reply = '&reply=true';
		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){
				do_action('pntable_emlogs_action', $action, $_POST['id']);
				$reply = '&reply=true';		
			} 
		}

		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	} 

	class all_emlogs_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'date';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'date'){
				return get_pn_time($item->create_date, 'd.m.Y H:i:s');
			} else {
				return pn_strip_input(is_isset($item, $column_name));					
			}
			return '';
		}	
		
		function get_columns(){
			$columns = array(         
				'date'     => __('Date','pn'),
				'subject'     => __('Subject of e-mail','pn'),
				'ot_name'     => __('Header e-mail','pn'),
				'to_mail'     => __('Recipient e-mail','pn'),
				'html'     => __('Text','pn'),
			);
			return $columns;
		}	

		function prepare_items(){
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('create_date');
			$order = $this->db_order('DESC');
			
			$where = '';
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."email_logs WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."email_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}	  
	}
}