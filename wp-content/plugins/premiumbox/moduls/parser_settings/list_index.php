<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_parser_index', 'pn_admin_title_pn_parser_index');
	function pn_admin_title_pn_parser_index(){
		_e('Custom coefficients','pn');
	}

	add_action('pn_adminpage_content_pn_parser_index','def_pn_admin_content_pn_parser_index');
	function def_pn_admin_content_pn_parser_index(){
		premium_table_list();		
	} 

	add_filter('csl_get_pindex', 'def_csl_get_pindex', 10, 2);
	function def_csl_get_pindex($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_directions') or current_user_can('pn_parser')){
			$comment = '';
			$last = '';
			
			$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."comment_system WHERE item_id='$id' AND itemtype='pindex' ORDER BY comment_date DESC");
			foreach($items as $item){ 
				$last .= '
				<div class="one_comment">
					<div class="one_comment_author"><span class="one_comment_del js_csl_del" data-bd="pindex" data-id="'. $item->id .'"></span><a href="'. pn_edit_user_link($item->user_id) .'" target="_blank">'. pn_strip_input($item->user_login) .'</a>, <span class="one_comment_date">'. get_pn_time($item->comment_date,'d.m.Y, H:i:s') .'</span></div>
					<div class="one_comment_text">
						'. pn_strip_input($item->text_comment) .'
					</div>
				</div>
				';
			}
			
			$log['status'] = 'success';
			$log['comment'] = '';
			$log['count'] = count($items);
			$log['last'] = $last;
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}
		
		return $log;
	}
	
	add_filter('csl_add_pindex', 'def_csl_add_pindex', 10, 2);
	function def_csl_add_pindex($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_directions') or current_user_can('pn_parser')){
			$ui = wp_get_current_user();
			$text = pn_strip_input(is_param_post('comment'));
			$log['status'] = 'success';
			if($text){
				$arr = array();
				$arr['comment_date'] = current_time('mysql');
				$arr['user_id'] = $ui->ID;
				$arr['user_login'] = pn_strip_input($ui->user_login);
				$arr['text_comment'] = $text;
				$arr['itemtype'] = 'pindex';
				$arr['item_id'] = $id;
				$wpdb->insert($wpdb->prefix.'comment_system', $arr);
			} 
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}		
		
		return $log;
	}	

	add_filter('csl_del_pindex', 'def_csl_del_pindex', 10, 2);
	function def_csl_del_pindex($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_directions') or current_user_can('pn_parser')){
			$log['status'] = 'success';
			$wpdb->query("DELETE FROM ".$wpdb->prefix."comment_system WHERE itemtype = 'pindex' AND id = '$id'");
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}		
		
		return $log;
	}

	add_action('premium_action_pn_parser_index','def_premium_action_pn_parser_index');
	function def_premium_action_pn_parser_index(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_directions','pn_parser'));	

		$reply = '';
		$action = get_admin_action();
			
		if(isset($_POST['save'])){
			
			$pindexes = get_option('parser_indexes');
			if(!is_array($pindexes)){ $pindexes = array(); }
			
			if(isset($_POST['sum']) and is_array($_POST['sum'])){
				foreach($_POST['sum'] as $id => $sum){
					$name = is_extension_name($id);
					if(isset($pindexes[$name])){
						$pindexes[$name] = is_sum($sum);
					}
				}						
			}			
			
			update_option('parser_indexes', $pindexes);
			
			do_action('pntable_parser_index_save');
			$reply = '&reply=true';
			
		} else {		
			if(isset($_POST['id']) and is_array($_POST['id'])){					
				if($action == 'delete'){
					$pindexes = get_option('parser_indexes');
					if(!is_array($pindexes)){ $pindexes = array(); }
					
					foreach($_POST['id'] as $id){
						$name = is_extension_name($id);
						if(isset($pindexes[$name])){
							unset($pindexes[$name]);
						}					
					}
					
					update_option('parser_indexes', $pindexes);
				}	
				do_action('pntable_parser_index_action', $action, $_POST['id']);
				$reply = '&reply=true';			
			} 
		}
		
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	}

	class pn_parser_index_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
			
			$this->primary_column = 'name';
			$this->save_button = 1;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'name'){
				return '<strong class="clpb_item" data-clipboard-text="[index_' . is_isset($item, 'name').']">[index_' . is_isset($item, 'name').']</strong>';		
			} elseif($column_name == 'sum'){
				return '<input type="text" style="width: 200px;" name="sum['. is_isset($item, 'name') .']" value="'. is_sum(is_isset($item, 'sum')) .'" />';
			} elseif($column_name == 'comment'){
				global $wpdb;
				$id = is_isset($item, 'name');
				$has_comment = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."comment_system WHERE itemtype='pindex' AND item_id = '$id'");
				return get_comment_label('pindex', $id, $has_comment);
			}		
			return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. is_isset($item, 'name') .'" />';              
		}	

		function get_row_actions($item){
			$actions = array(
				'edit' => '<a href="'. admin_url('admin.php?page=pn_add_parser_index&item_key='. is_isset($item, 'name')) .'">'. __('Edit','pn') .'</a>',
			);
			return $actions;
		}	
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'name'     => __('Coefficient name','pn'),
				'sum' => __('Amount','pn'),
				'comment'     => __('Comment','pn'),
			);
			return $columns;
		}	

		function get_bulk_actions(){
			$actions = array(
				'delete'    => __('Delete','pn'),
			);
			return $actions;
		}
		
 		function prepare_items() {
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			
			$start_items = array();
			$pindexes = get_option('parser_indexes');
			if(!is_array($pindexes)){ $pindexes = array(); }
			
			foreach($pindexes as $pi_key => $pi_value){
				$start_items[] = array(
					'sum' => $pi_value,
					'name' => $pi_key,
				);
			}
			
			$this->items = array_slice($start_items, $offset, $per_page);
			if($this->navi == 1){
				$this->total_items = count($start_items); 
			}
		} 		
		
	 	function extra_tablenav($which){		  	
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_parser_index');?>"><?php _e('Add new','pn'); ?></a>
		<?php 
		} 	  
	} 
}	