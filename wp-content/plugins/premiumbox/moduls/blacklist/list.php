<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('def_adminpage_title_all_blacklist') and is_admin()){
	add_action('pn_adminpage_title_all_blacklist', 'def_adminpage_title_all_blacklist');
	function def_adminpage_title_all_blacklist(){
		_e('Blacklist','pn');
	}

	add_action('pn_adminpage_content_all_blacklist','def_pn_adminpage_content_all_blacklist');
	function def_pn_adminpage_content_all_blacklist(){
		premium_table_list();
	}

	add_action('csl_get_blacklist', 'def_csl_get_blacklist', 10, 2);
	function def_csl_get_blacklist($log, $id){
	global $wpdb;
		
		if(current_user_can('administrator') or current_user_can('pn_blacklist')){
			$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."blacklist WHERE id='$id'");
			$comment = pn_strip_text(is_isset($item,'comment_text'));
			$log['status'] = 'success';
			$log['comment'] = $comment;
			$log['last'] = '
			<div class="one_comment">
				<div class="one_comment_text">
					'. $comment .'
				</div>
			</div>
			';
			if($comment){
				$log['count'] = 1;
			} else {
				$log['count'] = 0;
			}
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}	
			
		return $log;
	}	

	add_action('csl_add_blacklist', 'def_csl_add_blacklist', 10, 2);
	function def_csl_add_blacklist($log, $id){
	global $wpdb;
		
		if(current_user_can('administrator') or current_user_can('pn_blacklist')){
			$text = pn_strip_input(is_param_post('comment'));
			$wpdb->update($wpdb->prefix.'blacklist', array('comment_text'=>$text), array('id'=>$id));
			$log['status'] = 'success';
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}	
			
		return $log;
	}

	add_action('premium_action_all_blacklist','def_premium_action_all_blacklist');
	function def_premium_action_all_blacklist(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_blacklist'));

		$reply = '';
		$action = get_admin_action();
		if(isset($_POST['save'])){
							
			do_action('pntable_blacklist_save');
			$reply = '&reply=true';

		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){			
				if($action == 'delete'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."blacklist WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_blacklist_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."blacklist WHERE id = '$id'");
								do_action('item_blacklist_delete', $id, $item, $result);
							}
						}
					}	
				}
				do_action('pntable_blacklist_action', $action, $_POST['id']);
				$reply = '&reply=true';
			} 
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	}

	class all_blacklist_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'cvalue';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			
			if($column_name == 'ctype'){
				$arr = array('0'=>__('invoice','pn'),'1'=>__('e-mail','pn'),'2'=>__('mobile phone no.','pn'),'3'=>__('skype','pn'),'4'=>__('ip','pn'));
				return is_isset($arr,$item->meta_key);	
			} elseif($column_name == 'comment'){
				$comment_text = pn_strip_text($item->comment_text);	
				$has = 0;
				if($comment_text){
					$has = 1;
				}
				return get_comment_label('blacklist', $item->id, $has);
			} elseif($column_name == 'cvalue') {
				return pn_strip_input($item->meta_value);
			} 
			
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=all_add_blacklist&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}		
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',          
				'cvalue'     => __('Value','pn'),
				'ctype'    => __('Type','pn'),
				'comment'     => __('Comment','pn'),
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
				'title' => '',
				'default' => pn_strip_input(is_param_get('item')),
				'name' => 'item',
			);
			$options = array(
				'0' => __('everywhere','pn'),
				'1' => __('account','pn'),
				'2' => __('e-mail','pn'),
				'3' => __('mobile phone no.','pn'),
				'4' => __('skype','pn'),
				'5' => __('ip','pn'),
			);
			$search[] = array(
				'view' => 'select',
				'title' => '',
				'options' => $options,
				'default' => pn_strip_input(is_param_get('witem')),
				'name' => 'witem',
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

			$item = pn_sfilter(pn_strip_input(is_param_get('item')));
			if($item){ 
				$where .= " AND meta_value LIKE '%$item%'";
			}		
			
			$witem = intval(is_param_get('witem'));
			if($witem > 0){ 
				$witem = $witem - 1;
				$where .= " AND meta_key = '$witem'";
			}		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."blacklist WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."blacklist WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {
		?>
			<a href="<?php echo admin_url('admin.php?page=all_add_blacklist');?>"><?php _e('Add new','pn'); ?></a>
			<a href="<?php echo admin_url('admin.php?page=all_add_blacklist_many');?>"><?php _e('Add list','pn'); ?></a>
			<?php
		}	  
	}
}