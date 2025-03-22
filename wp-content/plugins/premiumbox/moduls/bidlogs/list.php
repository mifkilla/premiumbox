<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_bidlogs', 'def_adminpage_title_pn_bidlogs');
	function def_adminpage_title_pn_bidlogs(){
		_e('Orders status log','pn');
	}

	add_action('pn_adminpage_content_pn_bidlogs','def_pn_adminpage_content_pn_bidlogs');
	function def_pn_adminpage_content_pn_bidlogs(){
		premium_table_list();
	}	
	
	add_action('premium_action_pn_bidlogs','def_premium_action_pn_bidlogs');
	function def_premium_action_pn_bidlogs(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_bids'));

		$reply = '';
		$action = get_admin_action();
				
		if(isset($_POST['save'])){
							
			do_action('pntable_bidlogs_save');
			$reply = '&reply=true';

		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){				
				
				do_action('pntable_bidlogs_action', $action, $_POST['id']);
				$reply = '&reply=true';
				
			} 	
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	} 

	class pn_bidlogs_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'date';
			$this->save_button = 0;
		}
		
		function get_thwidth(){
			$array = array();
			$array['date'] = '160px';
			$array['bid'] = '100px';
			$array['user'] = '160px';
			return $array;
		}
		
		function column_default($item, $column_name){
			
			if($column_name == 'user'){
				$user_id = $item->user_id;
				$us = '<a href="'. pn_edit_user_link($user_id) .'">'. is_user($item->user_login) . '</a>';
				return $us;
			} elseif($column_name == 'bid'){
				return '<a href="'. admin_url('admin.php?page=pn_bids&bidid='.$item->bid_id) .'" target="_blank">'. $item->bid_id .'</a>';
			} elseif($column_name == 'place'){	
				return '<strong>'. pn_strip_input($item->place) .'</strong>';
			} elseif($column_name == 'who'){	
				if($item->who == 'system'){
					return '<strong>'. __('System changed','pn') .'</strong>';
				} else {
					return '<strong>'. __('User changed','pn') .'</strong>';
				}			
			} elseif($column_name == 'course_give'){
				return is_sum($item->course_give);
			} elseif($column_name == 'course_get'){
				return is_sum($item->course_get);			
			} elseif($column_name == 'status'){
				return bidlogs_status($item->old_status).'<div class="premium_clear"></div>';
			} elseif($column_name == 'date'){
				return get_pn_time($item->createdate, 'd.m.Y H:i:s');
			} elseif($column_name == 'newstatus'){	
				return bidlogs_status($item->new_status).'<div class="premium_clear"></div>';
			}
			
				return '';
		}	
		
		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_bids&bidid='.$item->bid_id) .'" target="_blank" rel="noreferrer noopener">'. __('Go to order','pn') .'</a>',
			);			
			return $actions;
		}			
		
		function get_columns(){
			$columns = array(         
				'date'     => __('Date','pn'),
				'user'    => __('User','pn'),
				'bid'    => __('Order ID','pn'),
				'place'    => __('Where made','pn'),
				'who'    => __('Who made','pn'),
				'course_give'    => __('Rate Send','pn'),
				'course_get'    => __('Rate Receive','pn'),
				'status'  => __('Old status','pn'),
				'newstatus'  => __('New status','pn'),
			);
			return $columns;
		}
		
		function get_search(){
			$search = array();
			$search[] = array(
				'view' => 'input',
				'title' => __('User login','pn'),
				'default' => pn_strip_input(is_param_get('user')),
				'name' => 'user',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('User ID','pn'),
				'default' => pn_strip_input(is_param_get('user_id')),
				'name' => 'user_id',
			);		
			$search[] = array(
				'view' => 'input',
				'title' => __('Order ID','pn'),
				'default' => pn_strip_input(is_param_get('bid_id')),
				'name' => 'bid_id',
			);
				return $search;
		}		
			
		function get_submenu(){	
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('user','pn'),
					'2' => __('system','pn'),
				),
				'title' => '',
			);	
				return $options;
		}		

		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
				
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('DESC');
			
			$where = '';

			$user_id = intval(is_param_get('user_id'));	
			if($user_id){ 
				$where .= " AND user_id='$user_id'";
			}
			
			$bid_id = intval(is_param_get('bid_id'));	
			if($bid_id){ 
				$where .= " AND bid_id='$bid_id'";
			}		
			
			$user = is_user(is_param_get('user'));
			if($user){
				$where .= " AND user_login LIKE '%$user%'";
			}
			
			$filter = intval(is_param_get('filter'));
			if($filter == 1){ 
				$where .= " AND who = 'user'";
			} elseif($filter == 2) {
				$where .= " AND who = 'system'";
			} 		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."bid_logs WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."bid_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}		
	}
}