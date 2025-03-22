<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_blackparser', 'def_adminpage_title_pn_blackparser');
	function def_adminpage_title_pn_blackparser(){
		_e('Auto broker','pn');
	} 

	add_action('pn_adminpage_content_pn_blackparser','def_pn_adminpage_content_pn_blackparser');
	function def_pn_adminpage_content_pn_blackparser(){
		premium_table_list();
	}

	add_action('premium_action_pn_blackparser','def_premium_action_pn_blackparser');
	function def_premium_action_pn_blackparser(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_directions'));

		$reply = '';
		$action = get_admin_action();
				
		if(isset($_POST['save'])){
					
			if(isset($_POST['url']) and is_array($_POST['url'])){
				foreach($_POST['url'] as $id => $url){
					$id = intval($id);
					$url = pn_strip_input($url);
							
					$array = array();	
					$array['url'] = $url;
					$wpdb->update($wpdb->prefix."blackparsers", $array, array('id'=>$id));	
				}						
			}
					
			do_action('pntable_blackparsers_save');
			$reply = '&reply=true';

		} else {		
			if(isset($_POST['id']) and is_array($_POST['id'])){					
				if($action == 'delete'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);		
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."blackparsers WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_blackparsers_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."blackparsers WHERE id = '$id'");
								do_action('item_blackparsers_delete', $id, $item, $result);
							}	
						}		
					}
				}
				do_action('pntable_blackparsers_action', $action, $_POST['id']);
				$reply = '&reply=true';			
			} 		
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	}  

	class pn_blackparser_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 1;
		}
		
		function get_thwidth(){
			$arr = array();
			$arr['title'] = '140px';
			return $arr;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'url'){		
				return '<input type="text" style="width: 100%;" name="url['. $item->id .']" value="'. pn_strip_input($item->url) .'" />';				
			} elseif($column_name == 'title'){
				return pn_strip_input($item->title);
			} 
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_blackparser&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}		

		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __('Website name','pn'),
				'url' => __('XML file URL','pn'),
			);
			return $columns;
		}	
		
		function get_bulk_actions() {
			$actions = array(
				'delete'    => __('Delete','pn'),
			);
			return $actions;
		}
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('DESC');
			
			$where = '';
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."blackparsers WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."blackparsers WHERE id > 0 $where ORDER BY id DESC LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {		  	
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_blackparser');?>"><?php _e('Add new','pn'); ?></a>		
		<?php 
		}	  
	}
}