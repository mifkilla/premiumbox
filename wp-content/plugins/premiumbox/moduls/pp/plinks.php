<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_plinks', 'def_adminpage_title_pn_plinks');
	function def_adminpage_title_pn_plinks(){
		_e('Transitions','pn');
	}

	add_action('pn_adminpage_content_pn_plinks','def_pn_adminpage_content_pn_plinks');
	function def_pn_adminpage_content_pn_plinks(){
		premium_table_list();
	}	

	add_action('premium_action_pn_plinks','def_premium_action_pn_plinks');
	function def_premium_action_pn_plinks(){
	global $wpdb;

		only_post();
		pn_only_caps(array('administrator','pn_pp'));	
		
		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){
						
			do_action('pntable_plinks_save');
			$reply = '&reply=true';

		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){
				if($action == 'delete'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."plinks WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_plinks_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."plinks WHERE id = '$id'");
								do_action('item_plinks_delete', $id, $item, $result);
							}
						}
					}
							
					do_action('pntable_plinks_action', $action, $_POST['id']);
					$reply = '&reply=true';						
				}
			} 
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	} 

	class pn_plinks_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'date';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			
			if($column_name == 'user'){
				$user_id = $item->user_id;
				$us = '<a href="'. pn_edit_user_link($user_id) .'">' . is_user($item->user_login) . '</a>';
				return $us;	
			} elseif($column_name == 'date'){
				return pn_strip_input($item->pdate);
			} elseif($column_name == 'browser'){
				return get_browser_name($item->pbrowser);
			} elseif($column_name == 'qstring'){
				return pn_strip_input($item->query_string);	
			} elseif($column_name == 'ip'){	
				return pn_strip_input($item->pip);
			} elseif($column_name == 'ref'){
				return pn_strip_input($item->prefer);	
			}
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}	
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',          
				'date'     => __('Date','pn'),
				'user'    => __('User','pn'),
				'browser'    => __('Browser','pn'),
				'ip'  => __('IP','pn'),
				'ref'  => __('Referral website','pn'),
				'qstring' => __('Query string','pn'),
			);
			return $columns;
		}	
		
		function get_bulk_actions() {
			$actions = array(
				'delete'    => __('Delete','pn'),
			);
			return $actions;
		}
		
		function get_search(){
			$search = array();
			$search[] = array(
				'view' => 'input',
				'title' => __('User','pn'),
				'default' => pn_strip_input(is_param_get('user_login')),
				'name' => 'user_login',
			);	
				return $search;
		}	
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('DESC');

			$where = '';
			$user_login = pn_sfilter(pn_strip_input(is_param_get('user_login')));
			if($user_login){
				$where .= " AND user_login LIKE '%$user_login%'";
			}		
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."plinks WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."plinks WHERE id > 0 $where ORDER BY id DESC LIMIT $offset , $per_page");  		
		}	  
	}
}