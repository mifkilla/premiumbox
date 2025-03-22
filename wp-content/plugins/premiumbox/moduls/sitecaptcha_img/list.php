<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_sci_variants')){
		add_action('pn_adminpage_title_all_sci_variants', 'def_adminpage_title_all_sci_variants');
		function def_adminpage_title_all_sci_variants(){
			_e('Captcha options','pn');
		}
	}

	if(!function_exists('def_adminpage_content_all_sci_variants')){
		add_action('pn_adminpage_content_all_sci_variants','def_adminpage_content_all_sci_variants');
		function def_adminpage_content_all_sci_variants(){
			premium_table_list();
		}
	}

	if(!function_exists('def_premium_action_all_sci_variants')){
		add_action('premium_action_all_sci_variants','def_premium_action_all_sci_variants');
		function def_premium_action_all_sci_variants(){
		global $wpdb;	

			only_post();
			pn_only_caps(array('administrator'));
			
			$reply = '';
			$action = get_admin_action();
			
			if(isset($_POST['save'])){
								
				do_action('pntable_sci_save');
				$reply = '&reply=true';

			} else {	
				if(isset($_POST['id']) and is_array($_POST['id'])){

					if($action=='delete'){		
						foreach($_POST['id'] as $id){
							$id = intval($id);					
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."sitecaptcha_images WHERE id='$id'");
							if(isset($item->id)){
								$res = apply_filters('item_sci_delete_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."sitecaptcha_images WHERE id = '$id'");
									do_action('item_sci_delete', $id, $item, $result);
								}
							}
						}
					}
					
					do_action('pntable_sci_action', $action, $_POST['id']);
					$reply = '&reply=true';
				} 
			}

			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
		}
	}

	if(!class_exists('all_sci_variants_Table_List')){
	class all_sci_variants_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}

		function column_default($item, $column_name){
			
			if($column_name == 'image1'){
				$img = pn_strip_input($item->img1);
				if($img){
					return '<img src="'. $img .'" width="50px" alt="" />';	
				}	
			} elseif($column_name == 'image2'){
				$img = pn_strip_input($item->img2);
				if($img){
					return '<img src="'. $img .'" width="50px" alt="" />';	
				}			
			} elseif($column_name == 'image3'){
				$img = pn_strip_input($item->img3);
				if($img){
					return '<img src="'. $img .'" width="50px" alt="" />';	
				}
			} elseif($column_name == 'variant'){
				return $item->variant;
			} elseif($column_name == 'title'){
				return pn_strip_input(ctv_ml($item->uslov));
			} 
			
			return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=all_sci_add_variants&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}		
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',          
				'title'     => __('Title','pn'),
				'image1'    => sprintf('%1s %2s', __('Image','pn'), '1'),
				'image2'    => sprintf('%1s %2s', __('Image','pn'), '2'),
				'image3'    => sprintf('%1s %2s', __('Image','pn'), '3'),
				'variant'     => __('Right choice','pn'),
			);
			return $columns;
		}	

		function get_bulk_actions() {
			$actions = array(
				'delete'    => __('Delete','pn')
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
			
			$where = $this->search_where('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."sitecaptcha_images WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."sitecaptcha_images WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset, $per_page");  		
		}
		
		function extra_tablenav($which){
		?>
			<a href="<?php echo admin_url('admin.php?page=all_sci_add_variants');?>"><?php _e('Add new','pn'); ?></a>
		<?php
		}	  
	}
	}
}