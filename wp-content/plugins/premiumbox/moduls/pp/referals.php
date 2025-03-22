<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_preferals', 'def_adminpage_title_pn_preferals');
	function def_adminpage_title_pn_preferals(){
		_e('Referrals','pn');
	}

	add_action('pn_adminpage_content_pn_preferals','def_pn_adminpage_content_pn_preferals');
	function def_pn_adminpage_content_pn_preferals(){
		premium_table_list();
	}
	 
	add_action('premium_action_pn_preferals','def_premium_action_pn_preferals');
	function def_premium_action_pn_preferals(){
	global $wpdb;
		
		only_post();
		
		$reply = '';
		$action = get_admin_action();	
		
		if(isset($_POST['save'])){			
			do_action('pntable_preferals_save');
			$reply = '&reply=true';
		} else {
			if(isset($_POST['id']) and is_array($_POST['id'])){
				do_action('pntable_preferals_action', $action, $_POST['id']);
				$reply = '&reply=true';
			}	
		}
		
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	}  
	 
	class pn_preferals_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'user';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'user'){
				
				$user_id = $item->ID;
				$us = '';
				if($user_id > 0){
					$ui = get_userdata($user_id);
					$us .='<a href="'. pn_edit_user_link($user_id) .'">';
					if(isset($ui->user_login)){
						$us .= is_user($ui->user_login); 
					}
					$us .='</a>';
				}			
				
				return $us;	
				
			} elseif($column_name == 'ref'){
		
				$user_id = $item->ref_id;
				$us = '';
				if($user_id > 0){
					$ui = get_userdata($user_id);
					$us .='<a href="'. pn_edit_user_link($user_id) .'">';
					if(isset($ui->user_login)){
						$us .= is_user($ui->user_login); 
					}
					$us .='</a>';
				}	
				
				return $us;		
			
			}
				return '';
		}	
		
		function get_columns(){
			$columns = array(          
				'user'    => __('User','pn'),
				'ref'    => __('Referral','pn'),
			);
			return $columns;
		}	
		
		function get_search(){
			$search = array();
			$search[] = array(
				'view' => 'input',
				'title' => __('User','pn'),
				'default' => pn_strip_input(is_param_get('user_login')),
				'name' => 'user_login',
			);		
			$search[] = array(
				'view' => 'input',
				'title' => __('Referral','pn'),
				'default' => pn_strip_input(is_param_get('ref_login')),
				'name' => 'ref_login',
			);	
			return $search;
		}

		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('ID');
			$order = $this->db_order('DESC');

			$where = '';
			$ref_login = is_user(is_param_get('ref_login'));
			if($ref_login){
				$suser_id = username_exists($ref_login);
				$where .= " AND ref_id='$suser_id'";
			}
			$user_login = is_user(is_param_get('user_login'));
			if($user_login){
				$user_id = username_exists($user_login);
				$where .= " AND ID='$user_id'";
			}		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(ID) FROM ". $wpdb->prefix ."users WHERE ref_id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."users WHERE ref_id > 0 $where ORDER BY ID DESC LIMIT $offset , $per_page");  		
		}	  
	}
}	