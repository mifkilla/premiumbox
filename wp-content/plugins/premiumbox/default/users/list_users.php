<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('all_users_Table_List') and is_admin()){

	add_action('pn_adminpage_title_all_users', 'pn_adminpage_title_all_users');
	function pn_adminpage_title_all_users(){
		_e('Users','pn');
	}

	add_action('pn_adminpage_content_all_users','def_pn_admin_content_all_users');
	function def_pn_admin_content_all_users(){
		premium_table_list();
	}	
	
	add_filter('csl_get_user_comment', 'def_csl_get_user_comment', 10, 2);
	function def_csl_get_user_comment($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('edit_users')){
			$comment = '';
			$last = '';
			
			$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."comment_system WHERE item_id='$id' AND itemtype='user' ORDER BY comment_date DESC");
			foreach($items as $item){ 
				$last .= '
				<div class="one_comment">
					<div class="one_comment_author"><span class="one_comment_del js_csl_del" data-bd="user_comment" data-id="'. $item->id .'"></span><a href="'. pn_edit_user_link($item->user_id) .'" target="_blank">'. pn_strip_input($item->user_login) .'</a>, <span class="one_comment_date">'. get_pn_time($item->comment_date,'d.m.Y, H:i:s') .'</span></div>
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
	
	add_filter('csl_add_user_comment', 'def_csl_add_user_comment', 10, 2);
	function def_csl_add_user_comment($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('edit_users')){
			$ui = wp_get_current_user();
			$text = pn_strip_input(is_param_post('comment'));
			$log['status'] = 'success';
			if($text){
				$arr = array();
				$arr['comment_date'] = current_time('mysql');
				$arr['user_id'] = $ui->ID;
				$arr['user_login'] = pn_strip_input($ui->user_login);
				$arr['text_comment'] = $text;
				$arr['itemtype'] = 'user';
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

	add_filter('csl_del_user_comment', 'def_csl_del_user_comment', 10, 2);
	function def_csl_del_user_comment($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('edit_users')){
			$log['status'] = 'success';
			$wpdb->query("DELETE FROM ".$wpdb->prefix."comment_system WHERE itemtype = 'user' AND id = '$id'");
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}		
		
		return $log;
	}	
	
	add_action('premium_action_all_users','def_premium_action_all_users');
	function def_premium_action_all_users(){
	global $wpdb;	

		only_post();
		
		$reply = '';
		$action = get_admin_action();
				
		$ui = wp_get_current_user();
		
		$search_role = '"administrator"';
				
		if(isset($_POST['changerole'])){
			
			if(current_user_can('administrator') or current_user_can('promote_users')){
				
				$roles = array();
				global $wp_roles;
				if (!isset($wp_roles)){
					$wp_roles = new WP_Roles();
				}
				if(isset($wp_roles)){ 
					foreach($wp_roles->role_names as $role => $name){
						$roles[$role] = $name;	
					}
				}
				
				$new_role = is_user_role_name(is_param_post('new_role'));
				
				if(isset($roles[$new_role])){
					if(isset($_POST['id']) and is_array($_POST['id'])){
						foreach($_POST['id'] as $id){
							$id = intval($id);
							if($id != $ui->ID){
								$user_data = get_userdata($id);
								if(isset($user_data->ID)){
									$role = $user_data->roles[0];

									$enable = 1;
									
									if($role == 'administrator' and $new_role != 'administrator'){
										$enable = 0;
										$count_admin = $wpdb->get_var("SELECT COUNT(ID) FROM ". $wpdb->prefix ."users tbl_users LEFT OUTER JOIN ". $wpdb->prefix ."usermeta tbl_usermeta ON(tbl_users.ID = tbl_usermeta.user_id) WHERE tbl_users.ID != '$id' AND tbl_usermeta.meta_key = '". $wpdb->prefix ."capabilities' AND tbl_usermeta.meta_value LIKE '%{$search_role}%'");
										if($count_admin > 0){
											$enable = 1;
										}	
									}
									
									if($enable == 1){
										$u = new WP_User($id);
										$u->remove_role($role);
										$u->add_role($new_role);
										
										if($new_role == 'administrator'){
											$created_data = @unserialize($user_data->created_data);
											$created_data['admin_id'] = $ui->ID;
											$created_data['admin_date'] = current_time('mysql');
											$created_data['admin_place'] = 'list';
											$new_user_data = array();
											$new_user_data['created_data'] = @serialize($created_data);
											$wpdb->update($wpdb->prefix.'users', $new_user_data, array('ID'=>$id));
										}
									}
								}	
							}
						}
					}				
					
					$reply = '&reply=true';
				}
			}
					
		} elseif(isset($_POST['save'])){
			
			if(current_user_can('administrator') or current_user_can('edit_users')){	
				do_action('pntable_users_save');
				$reply = '&reply=true';
			}
			
		} else {	
			if(current_user_can('administrator') or current_user_can('delete_users')){
				require_once(ABSPATH . 'wp-admin/includes/user.php' );
		
				if(isset($_POST['id']) and is_array($_POST['id'])){										
					if($action == 'delete'){		
						foreach($_POST['id'] as $id){
							$id = intval($id);
							if($id != $ui->ID){
								$user_data = get_userdata($id);
								if(isset($user_data->ID)){
									$role = is_isset($user_data->roles, 0);
									$enable = 0;
									if($role != 'administrator'){
										$enable = 1;
									} 
									if($enable != 1){
										$count_admin = $wpdb->get_var("SELECT COUNT(ID) FROM ". $wpdb->prefix ."users tbl_users LEFT OUTER JOIN ". $wpdb->prefix ."usermeta tbl_usermeta ON(tbl_users.ID = tbl_usermeta.user_id) WHERE tbl_users.ID != '$id' AND tbl_usermeta.meta_key = '". $wpdb->prefix ."capabilities' AND tbl_usermeta.meta_value LIKE '%{$search_role}%'");
										if($count_admin > 0){
											$enable = 1;
										}
									}
									if($enable == 1){
										wp_delete_user($id, $ui->ID);
									}
								}	
							}
						}			
					}
					do_action('pntable_users_action', $action, $_POST['id']);
					$reply = '&reply=true';						
				}	
			} 
		}
		
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	} 

	class all_users_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
			
			$this->primary_column = 'username';
			$this->save_button = 0;
			
		}
		
		function get_thwidth(){
			$array = array();
			$array['user_id'] = '30px';
			return $array;
		}		
	
		function column_default($item, $column_name){
			global $cd_ui, $now_roles;
			
			if(!isset($cd_ui[$item->ID])){
				$cd_ui[$item->ID] = get_userdata($item->ID);
			}
			
			if($column_name == 'user_id'){ 
				return $item->ID;	
			} elseif($column_name == 'register_date'){
				return get_pn_time($item->user_registered, 'd.m.Y, H:i');
			} elseif($column_name == 'user_email'){	
				return '<a href="mailto:'. is_email($item->user_email) .'" target="_blank">'. is_email($item->user_email) .'</a>';
			} elseif($column_name == 'last_adminpanel'){
				$admin_time_last = pn_strip_input(is_isset($item, 'last_adminpanel'));
				if($admin_time_last){
					return date("d.m.Y, H:i:s",$admin_time_last);
				}	
			} elseif($column_name == 'user_browser'){
				$user_browser = get_browser_name(is_isset($item, 'user_browser'), __('Unknown','pn'));
				return $user_browser;
			} elseif($column_name == 'user_ip'){
				$user_ip = pn_strip_input(is_isset($item, 'user_ip'));
				return $user_ip;		
			} elseif($column_name == 'role'){
				if(isset($cd_ui[$item->ID]->roles[0])){
					$role = $cd_ui[$item->ID]->roles[0];
					return is_isset($now_roles, $role);
				}
			} elseif($column_name == 'user_bann'){
				$user_bann = intval(is_isset($item, 'user_bann'));
				if($user_bann == 1){		
					return '<span class="bred">'. __('banned','pn') .'</span>';
				} else {
					return __('not banned','pn');
				}
			} elseif($column_name == 'username'){	
				$ui = wp_get_current_user();
				$user_login = '<strong>'.is_user($item->user_login).'</strong>';
				if($ui->ID == $item->ID or current_user_can('administrator') or current_user_can('edit_users')){
					$user_login = '<a href="'. pn_edit_user_link($item->ID) .'" target="_blank"><strong>'. is_user($item->user_login) .'</strong></a>';
				}	
				return $user_login;
			} elseif($column_name == 'admin_comment'){
				$has_comment = intval(is_isset($item, 'has_comment'));	
				return get_comment_label('user_comment', $item->ID, $has_comment);
			} elseif($column_name == 'user_phone'){	
				return pn_strip_input($cd_ui[$item->ID]->user_phone);
			} elseif($column_name == 'user_skype'){	
				return pn_strip_input($cd_ui[$item->ID]->user_skype);				
			} elseif($column_name == 'user_telegram'){	
				return pn_strip_input($cd_ui[$item->ID]->user_telegram);			
			}
				
				return '';
		}		
	
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->ID .'" />';              
		}		
	
		function get_row_actions($item){
			$actions = array();
			$ui = wp_get_current_user();
			if($ui->ID == $item->ID or current_user_can('administrator') or current_user_can('edit_users')){
				$actions['edit'] = '<a href="'. pn_edit_user_link($item->ID) .'">'. __('Edit','pn') .'</a>';
			}			
			return $actions;
		}	

		function get_columns(){
			$columns = array(         
				'cb'        => '<input type="checkbox" />',
				'user_id' => 'ID',
				'username' => __('User login', 'pn'),
				'register_date' => __('Registration date','pn'),
				'user_email' => __('E-mail','pn'),
				'role' => __('Role','pn'),
				'last_adminpanel' => __( 'Admin Panel','pn' ),
				'user_phone' => __('Mobile phone no.','pn'),
				'user_skype' => __('Skype','pn'),
				'user_telegram' => __('Telegram','pn'),
				'user_browser' => __('Browser','pn'),
				'user_ip' => __('IP','pn'),
				'user_bann' => __('Block','pn'),			
			);
			if(current_user_can('administrator') or current_user_can('edit_users')){
				$columns['admin_comment'] = __('Comment','pn');
			}	
			return $columns;
		}
		
		function get_search(){
			$search = array();
			$search[] = array(
				'view' => 'input',
				'title' => __('User ID','pn'),
				'default' => pn_strip_input(is_param_get('user_id')),
				'name' => 'user_id',
			);			
			$search[] = array(
				'view' => 'input',
				'title' => __('User login','pn'),
				'default' => is_user(is_param_get('user_login')),
				'name' => 'user_login',
			);		
			$search[] = array(
				'view' => 'input',
				'title' => __('User email','pn'),
				'default' => pn_strip_input(is_param_get('user_email')),
				'name' => 'user_email',
			);	
			return $search;
		}
		
		function get_submenu(){
			global $now_roles;
			$options = array();
			$options['role'] = array(
				'options' => $now_roles, 
				'title' => '',
			);	
			return $options;
		}

		function tr_class($tr_class, $item) {
			if($item->user_bann == 1){
				$tr_class[] = 'tr_red';
			}
			return $tr_class;
		}

		function get_bulk_actions(){
			$actions = array();
			
			if(current_user_can('administrator') or current_user_can('delete_users')){
				$actions = array(
					'delete'    => __('Delete','pn'),
				);
			}
			
			return $actions;
		}		

		function get_sortable_columns() {
			$sortable_columns = array( 
				'user_id'     => array('tbl_users.ID', 'desc'),
				'username'     => array('tbl_users.user_login', false),
				'register_date' => array('tbl_users.user_registered', false),
				'last_adminpanel' => array('(tbl_users.last_adminpanel -0.0)', false),
				'admin_comment' => array('(has_comment -0.0)', false),
			);
			return $sortable_columns;
		}	
	
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('tbl_users.ID');
			$order = $this->db_order('DESC');
			
			$where = '';
			
			$user_id = intval(is_param_get('user_id'));
			if($user_id > 0){
				$where .= " AND tbl_users.ID = '$user_id'";
			}			
			
			$user_login = pn_sfilter(pn_strip_input(is_param_get('user_login')));
			if($user_login){
				$where .= " AND tbl_users.user_login LIKE '%$user_login%'";
			}
			
			$user_email = pn_sfilter(pn_strip_input(is_param_get('user_email')));
			if($user_email){
				$where .= " AND tbl_users.user_email LIKE '%$user_email%'";
			}

			$role = is_user_role_name(is_param_get('role'));
			if($role){
				$search_role = '"'. $role .'"';
				$where .= " AND tbl_usermeta.meta_value LIKE '%{$search_role}%'";
			}		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(ID) FROM ". $wpdb->prefix ."users tbl_users LEFT OUTER JOIN ". $wpdb->prefix ."usermeta tbl_usermeta ON(tbl_users.ID = tbl_usermeta.user_id) WHERE tbl_usermeta.meta_key = '". $wpdb->prefix ."capabilities' $where");
			}
			$this->items = $wpdb->get_results("SELECT *, (SELECT COUNT(". $wpdb->prefix ."comment_system.id) FROM ". $wpdb->prefix ."comment_system WHERE itemtype='user' AND item_id = tbl_users.ID) AS has_comment $select_sql FROM ". $wpdb->prefix ."users tbl_users LEFT OUTER JOIN ". $wpdb->prefix ."usermeta tbl_usermeta ON(tbl_users.ID = tbl_usermeta.user_id)  WHERE tbl_usermeta.meta_key = '". $wpdb->prefix ."capabilities' $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		
			global $now_roles;
			$now_roles = array();
			
			global $wp_roles;
			if (!isset($wp_roles)){
				$wp_roles = new WP_Roles();
			}
			if(isset($wp_roles)){ 
				foreach($wp_roles->role_names as $role => $name){
					$now_roles[$role] = $name;	
				}
			}			
		}	

 		function extra_tablenav( $which ) {
			global $wpdb, $now_roles;	
		
			if(current_user_can('administrator') or current_user_can('promote_users')){
			?>
				<?php   
				if ( 'top' == $which ) {
				?>
				<select name="new_role" autocomplete="off">
					<option value="0"><?php _e('Change role to...','pn'); ?></option>
					<?php
					if(is_array($now_roles)){
						foreach($now_roles as $role_name => $role_title){
							?>
							<option value='<?php echo $role_name; ?>'><?php echo $role_title; ?></option>
							<?php
						}
					}
					?>
				</select>			
				<input type="submit" name="changerole" value="<?php _e('Change role for users','pn'); ?>">
				<?php
				}
				?>
			<?php
			}
			?>
			<?php if(current_user_can('administrator') or current_user_can('add_users')){ ?>
				<a href="<?php echo admin_url('admin.php?page=all_add_user');?>"><?php _e('Add new','pn'); ?></a>
			<?php } ?>
			<?php  
		}	
	} 
}