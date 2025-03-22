<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	
	add_action('pn_adminpage_title_pn_courselogs', 'pn_admin_title_pn_courselogs');
	function pn_admin_title_pn_courselogs(){ 
		_e('Log of rates','pn');
	}
	
	add_action('pn_adminpage_content_pn_courselogs','def_pn_adminpage_content_pn_courselogs');
	function def_pn_adminpage_content_pn_courselogs(){
		premium_table_list();
	}	

	add_action('premium_action_pn_courselogs','def_premium_action_pn_courselogs');
	function def_premium_action_pn_courselogs(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator'));
		
		$reply = '';
		$action = get_admin_action();

		if(isset($_POST['save'])){
							
			do_action('pntable_courselogs_save');
			$reply = '&reply=true';

		} else {
			if(isset($_POST['id']) and is_array($_POST['id'])){
				
				do_action('pntable_courselogs_action', $action, $_POST['id']);
				$reply = '&reply=true';
				
			}
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	}

	class pn_courselogs_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}
		
		function get_thwidth(){
			$array = array();
			$array['title'] = '160px';
			return $array;
		}			
		
		function column_default($item, $column_name){
			
			if($column_name == 'user'){
				$user_id = $item->user_id;
				if($user_id){
					$us = '<a href="'. pn_edit_user_link($user_id) .'">'. is_user($item->user_login) . '</a>';
					return $us;
				}
			} elseif($column_name == 'direction'){
				return '<a href="'. admin_url('admin.php?page=pn_add_directions&item_id='.$item->direction_id) .'" target="_blank">'. $item->direction_id .'</a>';
			} elseif($column_name == 'old_course'){	
				return is_sum($item->lcourse_give) . '&rarr;' . is_sum($item->lcourse_get);
			} elseif($column_name == 'new_course'){	
				return is_sum($item->course_give) . '&rarr;' . is_sum($item->course_get);			
			} elseif($column_name == 'title'){
				return get_pn_time($item->create_date, 'd.m.Y H:i:s');
			} elseif($column_name == 'who'){		
				return pn_strip_input($item->who);			
			}
				return '';
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_courselogs&direction_id='.$item->direction_id) .'">'. __('Go to exchange direction','pn') .'</a>',
			);			
			return $actions;
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
				'title' => __('Exchange direction ID','pn'),
				'default' => pn_strip_input(is_param_get('direction_id')),
				'name' => 'direction_id',
			);		
			return $search;
		}
		
		function get_columns(){
			$columns = array(         
				'title'     => __('Date','pn'),
				'user'    => __('User','pn'),
				'direction'    => __('Exchange direction ID','pn'),
				'old_course'  => __('Old rate','pn'),
				'new_course'  => __('New rate','pn'),
				'who'    => __('Changed by','pn'),
			);
			return $columns;
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
			
			$direction_id = intval(is_param_get('direction_id'));	
			if($direction_id){ 
				$where .= " AND direction_id='$direction_id'";
			}		
			
			$user = is_user(is_param_get('user'));
			if($user){
				$where .= " AND user_login LIKE '%$user%'";
			} 		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."direction_courselogs WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."direction_courselogs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}		
	}
}