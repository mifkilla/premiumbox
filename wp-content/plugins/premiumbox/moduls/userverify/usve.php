<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_usve')){
		add_action('pn_adminpage_title_all_usve', 'def_adminpage_title_all_usve');
		function def_adminpage_title_all_usve(){
			_e('Identity verification','pn');
		}
	}

	if(!function_exists('def_pn_adminpage_content_all_usve')){
		add_action('pn_adminpage_content_all_usve','def_pn_adminpage_content_all_usve');
		function def_pn_adminpage_content_all_usve(){
			premium_table_list();
		}
	}

	if(!function_exists('def_csl_get_verify')){
		add_action('csl_get_verify', 'def_csl_get_verify', 10, 2);
		function def_csl_get_verify($log, $id){
		global $wpdb;
		
			if(current_user_can('administrator') or current_user_can('pn_userverify')){
				$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."verify_bids WHERE id='$id'");
				$comment = pn_strip_text(is_isset($item,'comment'));
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
	}

	if(!function_exists('def_csl_add_verify')){
		add_action('csl_add_verify', 'def_csl_add_verify', 10, 2);
		function def_csl_add_verify($log, $id){
		global $wpdb;
		
			if(current_user_can('administrator') or current_user_can('pn_userverify')){
				$text = pn_strip_input(is_param_post('comment'));
				$wpdb->update($wpdb->prefix.'verify_bids', array('comment'=>$text), array('id'=>$id));
				$log['status'] = 'success';
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1; 
				$log['status_text'] = __('Authorisation Error','pn');
			}	
			
			return $log;
		}		
	}

	if(!function_exists('def_premium_action_enable_userverify')){
		add_action('premium_action_enable_userverify','def_premium_action_enable_userverify');
		function def_premium_action_enable_userverify(){
		global $wpdb;	
		
			pn_only_caps(array('administrator','pn_userverify'));
					
			$id = intval(is_param_get('id'));
			$place = trim(is_param_get('place'));
			$data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."verify_bids WHERE id='$id' AND auto_status = '1' AND status != '2'");	
			if(isset($data->id)){
				$data_id = $data->id;
					
				$array = array();
				$array['status'] = 2;
				$array['comment'] = '';
				$wpdb->update($wpdb->prefix.'verify_bids', $array, array('id'=>$id));
				
				$user_id = $data->user_id;
				$user_data = get_userdata($user_id);
				if($user_data->user_verify != 1){
				
					$arr = array();
					$arr['user_verify'] = 1;
					$wpdb->update($wpdb->prefix.'users', $arr, array('ID'=>$user_id));
					do_action('item_users_verify', $user_id, $user_data);
				
				}
					
				$uv_auto = array();			

				$fields = $wpdb->get_results("
				SELECT * FROM ".$wpdb->prefix."uv_field 
				LEFT OUTER JOIN ". $wpdb->prefix ."uv_field_user 
				ON(".$wpdb->prefix."uv_field.id = ". $wpdb->prefix ."uv_field_user.uv_field) 
				WHERE ".$wpdb->prefix."uv_field.fieldvid = '0' AND uv_id='$data_id' 
				");
				
				foreach($fields as $field){
					if($field->uv_auto and pn_verify_uv($field->uv_auto)){
						$uv_auto[$field->uv_auto] = strip_uf($field->uv_data, $field->uv_auto);
					}
				}
					
				foreach($uv_auto as $uv_k => $uv_v){
					if($uv_k == 'user_email'){
						if (!email_exists($uv_v)){
							$wpdb->update($wpdb->prefix.'users', array('user_email' => $uv_v), array('ID'=>$user_id));
						}
					} else {
						update_user_meta($user_id, $uv_k, $uv_v) or add_user_meta($user_id, $uv_k, $uv_v, true);
					}
				}

				$user_locale = pn_strip_input($data->locale);
				$user_email = is_email($data->user_email);
				
				$notify_tags = array();
				$notify_tags['[sitename]'] = pn_site_name();
				$notify_tags = apply_filters('notify_tags_userverify1_u', $notify_tags);		

				$user_send_data = array(
					'user_email' => $user_email,
				);	
				$user_send_data = apply_filters('user_send_data', $user_send_data, 'userverify1_u', $user_data);
				$result_mail = apply_filters('premium_send_message', 0, 'userverify1_u', $notify_tags, $user_send_data);							
			}
			
			if($place == 'all'){
				$url = admin_url('admin.php?page=all_usve&reply=true');
				$paged = intval(is_param_post('paged'));
				if($paged > 1){ $url .= '&paged='.$paged; }			
			} else {	
				$url = admin_url('admin.php?page=all_add_usve&item_id='. $id .'&reply=true');
			}	
			wp_redirect($url);
			exit;	
		}
	}	

	if(!function_exists('def_premium_action_disable_userverify')){
		add_action('premium_action_disable_userverify','def_premium_action_disable_userverify');
		function def_premium_action_disable_userverify(){
		global $wpdb;	

			pn_only_caps(array('administrator','pn_userverify'));
			
			$plugin = get_plugin_class();
			
			$delete_files = intval(is_param_post('delete_files'));
			
			$id = intval(is_param_get('id'));
			$place = trim(is_param_get('place'));
			$data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."verify_bids WHERE id='$id' AND auto_status = '1' AND status != '3'");	
			if(isset($data->id)){
				$data_id = $data->id;
					
				$array = array();
				$array['status'] = 3;
				if(isset($_POST['textstatus'])){
					$array['comment'] = $textstatus = pn_strip_text(is_param_post('textstatus'));
				} else {
					$textstatus = pn_strip_input($data->comment);
				}
				if(strlen($textstatus) < 1){
					$textstatus = pn_strip_text(ctv_ml($plugin->get_option('usve','canceltext')));
				}
				$wpdb->update($wpdb->prefix.'verify_bids', $array, array('id'=>$id));
				
				$user_id = $data->user_id;
				$user_data = get_userdata($user_id);
				$cc = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."verify_bids WHERE user_id = '$user_id' AND status = '2' AND id != '$data_id'");
				if($cc == 0){
					if($user_data->user_verify != 0){
						$arr = array();
						$arr['user_verify'] = 0;
						$wpdb->update($wpdb->prefix.'users', $arr, array('ID'=>$user_id));
						do_action('item_users_unverify', $user_id, $user_data);
					}
				}
					
				$user_locale = pn_strip_input($data->locale);
				$user_email = is_email($data->user_email);
						
				$notify_tags = array();
				$notify_tags['[sitename]'] = pn_site_name();
				$notify_tags['[text]'] = $textstatus;
				$notify_tags = apply_filters('notify_tags_userverify2_u', $notify_tags);		

				$user_send_data = array(
					'user_email' => $user_email,
				);	
				$user_send_data = apply_filters('user_send_data', $user_send_data, 'userverify2_u', $user_data);
				$result_mail = apply_filters('premium_send_message', 0, 'userverify2_u', $notify_tags, $user_send_data, $user_locale);
				
				if($delete_files){
					$path = $plugin->upload_dir . 'userverify/' . $data_id . '/';
					full_del_dir($path);
					
					$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."uv_field_user WHERE uv_id = '$data_id'");
					foreach($items as $item){
						$item_id = $item->id;
						$res = apply_filters('item_usfielduser_delete_before', pn_ind(), $item_id, $item);
						if($res['ind'] == 1){
							$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."uv_field_user WHERE id = '$item_id'");
							do_action('item_usfielduser_delete', $item_id, $item, $result); 
						}
					}					
				}
			}
			
			if($place == 'all'){
				$url = admin_url('admin.php?page=all_usve&reply=true');
				$paged = intval(is_param_post('paged'));
				if($paged > 1){ $url .= '&paged='.$paged; }			
			} else {	
				$url = admin_url('admin.php?page=all_add_usve&item_id='. $id .'&reply=true');
			}
			wp_redirect($url);
			exit;
		}	
	}

	if(!function_exists('def_premium_action_all_usve')){
		add_action('premium_action_all_usve','def_premium_action_all_usve');
		function def_premium_action_all_usve(){ 
		global $wpdb;
			
			only_post();
			pn_only_caps(array('administrator','pn_userverify'));	

			$reply = '';
			$action = get_admin_action();
			
			if(isset($_POST['save'])){
							
				do_action('pntable_usve_save');
				$reply = '&reply=true';

			} else {	
				if(isset($_POST['id']) and is_array($_POST['id'])){	

					if($action == 'basket'){	
						foreach($_POST['id'] as $id){
							$id = intval($id);	
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."verify_bids WHERE id='$id' AND auto_status != '0'");
							if(isset($item->id)){
								$res = apply_filters('item_usve_basket_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("UPDATE ".$wpdb->prefix."verify_bids SET auto_status = '0' WHERE id = '$id'");
									do_action('item_usve_basket', $id, $item, $result);
								}
							}		
						}	
					}
						
					if($action == 'unbasket'){	
						foreach($_POST['id'] as $id){
							$id = intval($id);	
							$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."verify_bids WHERE id='$id' AND auto_status != '1'");
							if(isset($item->id)){
								$res = apply_filters('item_usve_unbasket_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){
									$result = $wpdb->query("UPDATE ".$wpdb->prefix."verify_bids SET auto_status = '1' WHERE id = '$id'");
									do_action('item_usve_unbasket', $id, $item, $result);
								}
							}		
						}	
					}
				
					if($action == 'delete'){		
						foreach($_POST['id'] as $id){
							$id = intval($id);
							$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."verify_bids WHERE id = '$id'");
							if(isset($item->id)){		
								$res = apply_filters('item_usve_delete_before', pn_ind(), $id, $item);
								if($res['ind'] == 1){			
									$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."verify_bids WHERE id = '$id'");
									do_action('item_usve_delete', $id, $item, $result);
								}
							}		
						}	
					}
					do_action('pntable_usve_action', $action, $_POST['id']);
					$reply = '&reply=true';				
				} 
			}
					
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
		}
	} 

	if(!class_exists('all_usve_Table_List')){
		class all_usve_Table_List extends PremiumTable {

			function __construct(){    
				parent::__construct();
					
				$this->primary_column = 'create_date';
				$this->save_button = 0;
			}
			
			function get_thwidth(){
				$array = array();
				$array['create_date'] = '140px';
				return $array;
			}		
		
			function column_default($item, $column_name){
				
				if($column_name == 'id'){
					return $item->id;
				} elseif($column_name == 'create_date'){	
					return get_pn_time($item->create_date,'d.m.Y, H:i');			
				} elseif($column_name == 'ip'){
					return pn_strip_input($item->user_ip);
				} elseif($column_name == 'user'){
					return '<a href="'. pn_edit_user_link($item->user_id) .'">'. is_user($item->user_login) .'</a>';
				} elseif($column_name == 'status'){
					if($item->status == 1){
						$status='<strong>'. __('Pending request','pn') .'</strong>';
					} elseif($item->status == 2){
						$status='<span class="bgreen">'. __('Confirmed request','pn') .'</span>';
					} elseif($item->status == 3){
						$status='<span class="bred">'. __('Request is declined','pn') .'</span>';
					} else {
						$status='<strong>'. __('automatic','pn') .'</strong>';
					}
					return $status;
				} elseif($column_name == 'reason'){
					$comment_text = trim($item->comment);
					return get_comment_label('verify', $item->id, $comment_text);	
				} 		
					return '';
			}	
		
			function column_cb($item){
				return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
			}

			function get_row_actions($item){
				$paged = intval(is_param_get('paged'));
				$actions = array(
					'edit'      => '<a href="'. admin_url('admin.php?page=all_add_usve&item_id='. $item->id) .'">'. __('Edit data','pn') .'</a>',
					'disable'      => '<a href="'. pn_link('disable_userverify') .'&id='. $item->id .'&paged='. $paged .'&place=all" class="bred">'. __('Decline verification','pn') .'</a>',
				);			
				return $actions;
			}			
			
			function get_columns(){
				$columns = array(
					'cb'        => '<input type="checkbox" />', 
					'create_date'     => __('Creation date','pn'),	
					'user'     => __('User','pn'),
					'ip' => __('IP','pn'),
					'status'  => __('Status','pn'),
					'reason' => __('Failure reason','pn'),
				);
				return $columns;
			}

			function tr_class($tr_class, $item) {
				if($item->status == 2){
					$tr_class[] = 'tr_green';
				}
				if($item->status == 3){
					$tr_class[] = 'tr_red';
				}			
				return $tr_class;
			}		
		
			function get_bulk_actions() {
				$actions = array(
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
		
			function get_search(){
				$search = array();
				$search[] = array(
					'view' => 'input',
					'title' => __('User login','pn'),
					'default' => pn_strip_input(is_param_get('user_login')),
					'name' => 'user_login',
				);
				$search[] = array(
					'view' => 'input',
					'title' => __('IP','pn'),
					'default' => pn_strip_input(is_param_get('user_ip')),
					'name' => 'user_ip',
				);	
					return $search;
			}	
				
			function get_submenu(){	
				$options = array();
				$options['filter'] = array(
					'options' => array(
						'1' => __('pending request','pn'),
						'2' => __('confirmed request','pn'),
						'3' => __('cancelled request','pn'),
						'9' => __('in basket','pn'),
					),
				);
					return $options;
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
				if($filter == 1){ //на модерации
					$where .= " AND status = '1'";
				} elseif($filter == 2){ //активен
					$where .= " AND status = '2'";
				} elseif($filter == 3){ //завершен
					$where .= " AND status = '3'";
				} else { //все, кроме автозаявок
					$where .= " AND status != '0'";
				}

				if($filter == 9){	
					$where .= " AND auto_status = '0'";
				} else {
					$where .= " AND auto_status = '1'";
				}				

				$user_login = pn_sfilter(pn_strip_input(is_param_get('user_login')));
				if($user_login){
					$where .= " AND user_login LIKE '%$user_login%'";
				}

				$user_ip = pn_sfilter(pn_strip_input(is_param_get('user_ip')));
				if($user_ip){
					$where .= " AND user_ip LIKE '%$user_ip%'";
				}		
				
				$where = $this->search_where($where);
				$select_sql = $this->select_sql('');
				if($this->navi == 1){
					$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."verify_bids WHERE id > 0 $where");
				}
				$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."verify_bids WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
			}	

			function extra_tablenav( $which ) {
			?>
				<a href="<?php echo admin_url('admin.php?page=all_add_usve');?>"><?php _e('Add new','pn'); ?></a>
			<?php
			} 	
		}
	} 
}