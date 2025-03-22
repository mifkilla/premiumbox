<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_reviews')){
		add_action('pn_adminpage_title_all_reviews', 'def_adminpage_title_all_reviews');
		function def_adminpage_title_all_reviews(){
			_e('Reviews','pn');
		}
	}

	if(!function_exists('def_pn_adminpage_content_all_reviews')){
		add_action('pn_adminpage_content_all_reviews','def_pn_adminpage_content_all_reviews');
		function def_pn_adminpage_content_all_reviews(){
			premium_table_list();
		}
	}

	if(!function_exists('def_premium_action_all_reviews')){
		add_action('premium_action_all_reviews','def_premium_action_all_reviews');
		function def_premium_action_all_reviews(){
		global $wpdb;	

			only_post();
			pn_only_caps(array('administrator','pn_reviews'));
			
			$reply = '';
			$action = get_admin_action();
			if(isset($_POST['save'])){				
				do_action('pntable_reviews_save');
				$reply = '&reply=true';
			} else {	
				if(isset($_POST['id']) and is_array($_POST['id'])){

					if($action == 'basket'){	
						foreach($_POST['id'] as $id){
							$id = intval($id);	
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."reviews WHERE id='$id' AND auto_status != '0'");
							if(isset($item->id)){
								$res = apply_filters('item_reviews_basket_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("UPDATE ".$wpdb->prefix."reviews SET auto_status = '0' WHERE id = '$id'");
									do_action('item_reviews_basket', $id, $item, $result);
								}
							}		
						}	
					}
					
					if($action == 'unbasket'){	
						foreach($_POST['id'] as $id){
							$id = intval($id);	
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."reviews WHERE id='$id' AND auto_status != '1'");
							if(isset($item->id)){
								$res = apply_filters('item_reviews_unbasket_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("UPDATE ".$wpdb->prefix."reviews SET auto_status = '1' WHERE id = '$id'");
									do_action('item_reviews_unbasket', $id, $item, $result);
								}
							}		
						}	
					}

					if($action == 'approve'){	
						foreach($_POST['id'] as $id){
							$id = intval($id);		
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."reviews WHERE id='$id' AND review_status != 'publish'");
							if(isset($item->id)){
								$res = apply_filters('item_reviews_approve_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("UPDATE ".$wpdb->prefix."reviews SET review_status = 'publish' WHERE id = '$id'");
									do_action('item_reviews_approve', $id, $item, $result);
								}
							}		
						}			
					}

					if($action == 'unapprove'){		
						foreach($_POST['id'] as $id){
							$id = intval($id);		
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."reviews WHERE id='$id' AND review_status != 'moderation'");
							if(isset($item->id)){	
								$res = apply_filters('item_reviews_unapprove_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){	
									$result = $wpdb->query("UPDATE ".$wpdb->prefix."reviews SET review_status = 'moderation' WHERE id = '$id'");
									do_action('item_reviews_unapprove', $id, $item, $result);
								}
							}
						}			
					}				
						
					if($action == 'delete'){		
						foreach($_POST['id'] as $id){
							$id = intval($id);		
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."reviews WHERE id='$id'");
							if(isset($item->id)){
								$res = apply_filters('item_reviews_delete_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."reviews WHERE id = '$id'");
									do_action('item_reviews_delete', $id, $item, $result);
								}
							}
						}	
					}
					
					do_action('pntable_reviews_action', $action, $_POST['id']);
					$reply = '&reply=true';
				} 
			}
					
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
		} 
	}

	if(!class_exists('all_reviews_Table_List')){
	class all_reviews_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'date';
			$this->save_button = 0;
		}
		
		function get_thwidth(){
			$array = array();
			$array['date'] = '160px';
			return $array;
		}	

		function column_default($item, $column_name){
			
			if($column_name == 'user'){
				$user_id = $item->user_id;
				$us = '';
				if($user_id > 0){
					$ui = get_userdata($user_id);
					$us .='<a href="'. pn_edit_user_link($user_id) .'">';
					if(isset($ui->user_login)){
						$us .= is_user($ui->user_login); 
					}
					$us .='</a>';
				} else {
					return pn_strip_input($item->user_name);
				}
				return $us;	
			} elseif($column_name == 'email'){
				return '<a href="mailto:'. is_email($item->user_email) .'">'. is_email($item->user_email) .'</a>';
			} elseif($column_name == 'site'){
				return '<a href="'. pn_strip_input($item->user_site) .'" target="_blank" rel="noreferrer noopener">'. pn_strip_input($item->user_site) .'</a>';
			} elseif($column_name == 'lang'){	
				return get_title_forkey($item->review_locale);
			} elseif($column_name == 'date'){
				return get_pn_time($item->review_date,'d.m.Y, H:i');
			} elseif($column_name == 'status'){
				if($item->review_status == 'moderation'){ 
					return '<span class="bred">'. __('review is moderating','pn') .'</span>'; 
				} else { 
					return '<span class="bgreen">'. __('published review','pn') .'</span>'; 
				}	
			}
			
			return '';
		}		
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=all_add_reviews&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);	
			if($item->review_status == 'publish' and $item->auto_status == 1){
				$actions['view'] = '<a href="'. get_review_link($item->id, $item) .'" target="_blank" rel="noreferrer noopener">'. __('View','pn') .'</a>';
			}		
			return $actions;
		}	
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',          
				'date'     => __('Publication date','pn'),
				'user'    => __('User','pn'),
				'email'    => __('User e-mail','pn'),
				'site'  => __('Website','pn'),
				'lang'  => __('Language','pn'),
				'status'  => __('Status','pn'),
			);
			if(!is_ml() and isset($columns['lang'])){
				unset($columns['lang']);
			}
			return $columns;
		}

		function get_submenu(){
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('published','pn'),
					'2' => __('moderating','pn'),
					'9' => __('in basket','pn'),
				),
			);	
			return $options;
		}	
		
		function tr_class($tr_class, $item) {
			if($item->review_status != 'publish'){
				$tr_class[] = 'tr_red';
			}
			return $tr_class;
		}			
		
		function get_bulk_actions() {
			$actions = array(
				'approve'    => __('Approve','pn'),
				'unapprove'    => __('Decline','pn'),
				'basket'    => __('In basket','pn'),
			);
			$filter = intval(is_param_get('filter'));
			if($filter == 9){
				$actions = array(
					'unbasket' => __('Restore','pn'),
					'delete' => __('Delete','pn'),
				);
			}			
			return $actions;
		}
		
		function get_sortable_columns() {
			$sortable_columns = array( 
				'date'     => array('review_date', 'desc'),
			);
			return $sortable_columns;
		}	
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('DESC');		
			
			$where = '';

			$filter = intval(is_param_get('filter'));
			if($filter == 1){
				$where = " AND review_status = 'publish'";
			} elseif($filter == 2){
				$where = " AND review_status = 'moderation'";
			}
			
			if($filter == 9){	
				$where .= " AND auto_status = '0'";
			} else {
				$where .= " AND auto_status = '1'";
			}			

			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."reviews WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."reviews WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {
		?>
			<a href="<?php echo admin_url('admin.php?page=all_add_reviews');?>"><?php _e('Add new','pn'); ?></a>
		<?php
		}	  
	}
	}
}