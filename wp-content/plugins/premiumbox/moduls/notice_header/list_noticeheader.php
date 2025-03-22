<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('pn_adminpage_title_all_noticeheader')){
		add_action('pn_adminpage_title_all_noticeheader', 'pn_adminpage_title_all_noticeheader');
		function pn_adminpage_title_all_noticeheader(){
			_e('Warning messages','pn');
		}
	}	

	if(!function_exists('def_pn_admin_content_all_noticeheader')){
		add_action('pn_adminpage_content_all_noticeheader','def_pn_admin_content_all_noticeheader');
		function def_pn_admin_content_all_noticeheader(){
			premium_table_list();	
		}
	}		

	if(!function_exists('def_premium_action_all_noticeheader')){
		add_action('premium_action_all_noticeheader','def_premium_action_all_noticeheader');
		function def_premium_action_all_noticeheader(){
		global $wpdb;	

			only_post();
			pn_only_caps(array('administrator','pn_noticeheader'));
			
			$reply = '';
			$action = get_admin_action();
			
			if(isset($_POST['save'])){
				do_action('pntable_noticeheader_save');				
				$reply = '&reply=true';
			} else {	
				if(isset($_POST['id']) and is_array($_POST['id'])){

					if($action == 'basket'){	
						foreach($_POST['id'] as $id){
							$id = intval($id);	
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."notice_head WHERE id='$id' AND auto_status != '0'");
							if(isset($item->id)){
								$res = apply_filters('item_noticehead_basket_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("UPDATE ".$wpdb->prefix."notice_head SET auto_status = '0' WHERE id = '$id'");
									do_action('item_noticehead_basket', $id, $item, $result);
								}
							}		
						}	
					}
					
					if($action == 'unbasket'){	
						foreach($_POST['id'] as $id){
							$id = intval($id);	
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."notice_head WHERE id='$id' AND auto_status != '1'");
							if(isset($item->id)){
								$res = apply_filters('item_noticehead_unbasket_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("UPDATE ".$wpdb->prefix."notice_head SET auto_status = '1' WHERE id = '$id'");
									do_action('item_noticehead_unbasket', $id, $item, $result);
								}
							}		
						}	
					}					

					if($action == 'approve'){	
						foreach($_POST['id'] as $id){
							$id = intval($id);	
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."notice_head WHERE id='$id' AND status != '1'");
							if(isset($item->id)){
								$res = apply_filters('item_noticehead_approve_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("UPDATE ".$wpdb->prefix."notice_head SET status = '1' WHERE id = '$id'");
									do_action('item_noticehead_approve', $id, $item, $result);
								}
							}		
						}	
					}

					if($action == 'unapprove'){	
						foreach($_POST['id'] as $id){
							$id = intval($id);	
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."notice_head WHERE id='$id' AND status != '0'");
							if(isset($item->id)){	
								$res = apply_filters('item_noticehead_unapprove_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("UPDATE ".$wpdb->prefix."notice_head SET status = '0' WHERE id = '$id'");
									do_action('item_noticehead_unapprove', $id, $item, $result);	
								}
							}
						}		
					}				
						
					if($action == 'delete'){	
						foreach($_POST['id'] as $id){
							$id = intval($id);
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."notice_head WHERE id='$id'");
							if(isset($item->id)){		
								$res = apply_filters('item_noticehead_delete_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."notice_head WHERE id = '$id'");
									do_action('item_noticehead_delete', $id, $item, $result);
								}
							}
						}	
					}
					
					do_action('pntable_noticeheader_action', $action, $_POST['id']);
					$reply = '&reply=true';
				} 
			}
					
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;				
		} 
	}		 

	if(!class_exists('all_noticeheader_Table_List')){
		class all_noticeheader_Table_List extends PremiumTable {

			function __construct(){    
				parent::__construct();
					
				$this->primary_column = 'text';
				$this->save_button = 0;
			}

			function column_default($item, $column_name){
				$notice_type = $item->notice_type;
				if($column_name == 'bydate'){
					if($notice_type == 0){
						return get_pn_time($item->datestart,'d.m.Y H:i').'-'.get_pn_time($item->dateend,'d.m.Y H:i');
					}
				} elseif($column_name == 'class'){
					return pn_strip_input($item->theclass);
				} elseif($column_name == 'display'){
					if($item->notice_display == '0'){ 
						return '<strong>'. __('header','pn') .'</strong>'; 
					} elseif($item->notice_display == '1') { 
						return '<strong>'. __('pop-up window','pn') .'</strong>'; 
					} elseif($item->notice_display == '2') {
						return '<strong>'. __('notification window','pn') .'</strong>';
					}			
				} elseif($column_name == 'ctime'){
					if($notice_type == 1){
						return $item->h1 .':'. $item->m1 .'-'. $item->h2 .':'. $item->m2;
					}
				} elseif($column_name == 'cdays'){
					if($notice_type == 1){
						$days = array(
							'd1' => __('monday','pn'),
							'd2' => __('tuesday','pn'),
							'd3' => __('wednesday','pn'),
							'd4' => __('thursday','pn'),
							'd5' => __('friday','pn'),
							'd6' => '<span class="bred">'. __('saturday','pn') .'</span>',
							'd7' => '<span class="bred">'. __('sunday','pn') .'</span>',
						);
						$ndays = array();
						foreach($days as $k => $v){
							if(is_isset($item, $k) == 1){
								$ndays[] = $v;
							}
						}
					
						echo join(', ',$ndays);
					}
				} elseif($column_name == 'text'){	
					return pn_strip_text(ctv_ml($item->text));
				} elseif($column_name == 'status'){
					if($item->status == '0'){ 
						return '<span class="bred">'. __('moderating','pn') .'</span>'; 
					} else { 
						return '<span class="bgreen">'. __('published','pn') .'</span>'; 
					}	
				}
					return '';
			}	

			function column_cb($item){
				return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
			}	
			
			function get_row_actions($item){
				$actions = array(
					'edit'      => '<a href="'. admin_url('admin.php?page=all_add_noticeheader&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
				);			
				return $actions;
			}	
			
			function get_columns(){
				$columns = array(
					'cb'        => '<input type="checkbox" />',          
					'text'     => __('Text','pn'),
					'bydate'    => __('Date','pn'),
					'ctime'     => __('Period for display (hours)','pn'),
					'cdays'     => __('Period for display (days)','pn'),
					'class'    => __('CSS class','pn'),
					'display'    => __('Location','pn'),
					'status'  => __('Status','pn'),
				);
				return $columns;
			}
			
			function tr_class($tr_class, $item) {
				if($item->status == 0){
					$tr_class[] = 'tr_red';
				}
				return $tr_class;
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
				$options['filter2'] = array(
					'options' => array(
						'1' => __('on period of time','pn'),
						'2' => __('on schedule','pn'),
					),
				);	
				$options['filter3'] = array(
					'options' => array(
						'1' => __('header','pn'),
						'2' => __('pop-up window','pn'),
						'3' => __('notification window','pn'),
					),
				);	
				return $options;
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

			function prepare_items() {
				global $wpdb; 
				
				$per_page = $this->count_items();
				$current_page = $this->get_pagenum();
				$offset = $this->get_offset();
				
				$orderby = $this->db_orderby('site_order');
				$order = $this->db_order('ASC');		
				
				$where = '';

				$filter = intval(is_param_get('filter'));
				if($filter == 1){
					$where .= " AND status = '1'";
				} elseif($filter == 2){
					$where .= " AND status = '0'";
				} 
					
				if($filter == 9){	
					$where .= " AND auto_status = '0'";
				} else {
					$where .= " AND auto_status = '1'";
				}

				$filter2 = intval(is_param_get('filter2'));
				if($filter2 == 1){
					$where .= " AND notice_type = '0'";
				} elseif($filter2 == 2){
					$where .= " AND notice_type = '1'";
				}

				$filter3 = intval(is_param_get('filter3'));
				if($filter3 == 1){
					$where .= " AND notice_display = '0'";
				} elseif($filter3 == 2){
					$where .= " AND notice_display = '1'";
				} elseif($filter3 == 3){
					$where .= " AND notice_display = '2'";	
				}		
				
				$where = $this->search_where($where);
				$select_sql = $this->select_sql('');
				if($this->navi == 1){
					$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."notice_head WHERE id > 0 $where");
				}
				$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."notice_head WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
			}

			function extra_tablenav($which){
			?>
				<a href="<?php echo admin_url('admin.php?page=all_add_noticeheader');?>"><?php _e('Add new','pn'); ?></a>
				<?php
			}	
		} 
	}
}	