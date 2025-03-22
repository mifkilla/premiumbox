<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_cccourselogs', 'def_adminpage_title_pn_cccourselogs');
	function def_adminpage_title_pn_cccourselogs(){ 
		_e('Log of rates','pn');
	}

	add_action('pn_adminpage_content_pn_cccourselogs','def_pn_adminpage_content_pn_cccourselogs');
	function def_pn_adminpage_content_pn_cccourselogs(){
		premium_table_list();
	}

	add_action('premium_action_pn_cccourselogs','def_premium_action_pn_cccourselogs');
	function def_premium_action_pn_cccourselogs(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator'));
		
		$reply = '';
		$action = get_admin_action();

		if(isset($_POST['save'])){			
			do_action('pntable_cccourselogs_save');
			$reply = '&reply=true';
		} else {
			if(isset($_POST['id']) and is_array($_POST['id'])){
				do_action('pntable_cccourselogs_action', $action, $_POST['id']);
				$reply = '&reply=true';
			}
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;				
	}

	class pn_cccourselogs_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'date';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'user'){
				$user_id = $item->user_id;
				if($user_id){
					$us = '<a href="'. pn_edit_user_link($user_id) .'">'. is_user($item->user_login) . '</a>';
					return $us;
				}
			} elseif($column_name == 'cc'){
				return '<a href="'. admin_url('admin.php?page=pn_add_currency_codes&item_id='.$item->currency_code_id) .'" target="_blank">'. pn_strip_input($item->currency_code_title) .'</a>';
			} elseif($column_name == 'old_curs'){	
				return is_sum($item->last_internal_rate);
			} elseif($column_name == 'new_curs'){	
				return is_sum($item->internal_rate);			
			} elseif($column_name == 'who'){	
				return pn_strip_input($item->who);	
			} elseif($column_name == 'date'){
				return get_pn_time($item->create_date, 'd.m.Y H:i:s');
			}
				return '';
		}	
		
		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_cccourselogs&currency_code_id='. $item->currency_code_id) .'">'. __('Filter by currency code','pn') .'</a>',
			);			
			return $actions;
		}	
		
		function get_columns(){
			$columns = array(         
				'date'     => __('Date','pn'),
				'user'    => __('User','pn'),
				'cc'    => __('Currency code','pn'),
				'old_curs'  => __('Old rate','pn'),
				'new_curs'  => __('New rate','pn'),
				'who'    => __('Changed by','pn'),
			);
			
			return $columns;
		}

		function get_search(){
			
			$search = array();
			$search['user'] = array(
				'view' => 'input',
				'title' => __('User login','pn'),
				'default' => pn_strip_input(is_param_get('user')),
				'name' => 'user',
			);
			$search['user_id'] = array(
				'view' => 'input',
				'title' => __('User ID','pn'),
				'default' => pn_strip_input(is_param_get('user_id')),
				'name' => 'user_id',
			);		
			$currency_codes = list_currency_codes(__('All codes','pn'));
			$search[] = array(
				'view' => 'select',
				'title' => __('Code','pn'),
				'default' => pn_strip_input(is_param_get('currency_code_id')),
				'options' => $currency_codes,
				'name' => 'currency_code_id',
			);		
			
			return $search;
		}

		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
				
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('desc');

			$where = '';

			$user_id = intval(is_param_get('user_id'));	
			if($user_id){ 
				$where .= " AND user_id='$user_id'";
			}
			
			$currency_code_id = intval(is_param_get('currency_code_id'));	
			if($currency_code_id){ 
				$where .= " AND currency_code_id='$currency_code_id'";
			}		
			
			$user = is_user(is_param_get('user'));
			if($user){
				$where .= " AND user_login LIKE '%$user%'";
			} 		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."currency_codes_courselogs WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."currency_codes_courselogs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}	 	
	}
}	