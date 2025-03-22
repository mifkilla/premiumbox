<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_userwallets_verify', 'pn_admin_title_pn_userwallets_verify');
	function pn_admin_title_pn_userwallets_verify(){
		_e('Account verification','pn');
	}

	add_action('pn_adminpage_content_pn_userwallets_verify','def_pn_adminpage_content_pn_userwallets_verify');
	function def_pn_adminpage_content_pn_userwallets_verify(){
		premium_table_list();
		?>
	<script type="text/javascript">
	jQuery(function($){	
		$(document).on('click', '.js_usac_del', function(){
			var id = $(this).attr('data-id');
			var thet = $(this);
			if(!thet.hasClass('act')){
				if(confirm("<?php _e('Are you sure you want to delete the file?','pn'); ?>")){
					thet.addClass('act');
					var param ='id=' + id;
					$.ajax({
					type: "POST",
					url: "<?php echo get_pn_action('delete_accverify');?>",
					dataType: 'json',
					data: param,
					error: function(res, res2, res3){
						<?php do_action('pn_js_error_response', 'ajax'); ?>
					},			
					success: function(res)
					{
						if(res['status'] == 'success'){
							$('.accline_' + id).remove();
						} 
						if(res['status'] == 'error'){
							<?php do_action('pn_js_alert_response'); ?>
						}
						thet.removeClass('act');
					}
					});
				}
			}
			return false;
		});	
		
	});
	</script>		
		<?php
	}

	add_action('csl_get_walletsverify', 'def_csl_get_walletsverify', 10, 2);
	function def_csl_get_walletsverify($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_userwallets')){
			$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."uv_wallets WHERE id='$id'");
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

	add_action('csl_add_walletsverify', 'def_csl_add_walletsverify', 10, 2);
	function def_csl_add_walletsverify($log, $id){
	global $wpdb;

		if(current_user_can('administrator') or current_user_can('pn_userwallets')){
			$text = pn_strip_input(is_param_post('comment'));
			$wpdb->update($wpdb->prefix.'uv_wallets', array('comment'=>$text), array('id'=>$id));
			$log['status'] = 'success';
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}	
		
		return $log;
	}

	add_action('premium_action_pn_userwallets_verify','def_premium_action_pn_userwallets_verify');
	function def_premium_action_pn_userwallets_verify(){
	global $wpdb;

		only_post();
		pn_only_caps(array('administrator','pn_userwallets'));	

		$reply = '';
		$action = get_admin_action();
			
		if(isset($_POST['save'])){
							
			do_action('pntable_uv_wallets_save');
			$reply = '&reply=true';

		} else {
			if(isset($_POST['id']) and is_array($_POST['id'])){				
				if($action == 'true'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);		
						$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."uv_wallets WHERE id = '$id' AND status != '1'");
						if(isset($item->id)){
							$user_wallet_id = $item->user_wallet_id;
							
							$res = apply_filters('item_uv_wallets_true_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								
								do_action('item_uv_wallets_true', $id, $item);
								$arr = array();
								$arr['status'] = 1;
								$result = $wpdb->update($wpdb->prefix . 'uv_wallets', $arr, array('id'=>$item->id));
								if($result){
									do_action('item_uv_wallets_true_after', $id, $item);
								}

								$arr = array();
								$arr['verify'] = 1;
								$wpdb->update($wpdb->prefix.'user_wallets', $arr, array('id'=>$user_wallet_id));
								do_action('item_userwallets_verify', $user_wallet_id);
				
								$user_locale = pn_strip_input($item->locale);
								$purse = pn_strip_input($item->wallet_num);
								$ui = get_userdata($item->user_id);
								
								$notify_tags = array();
								$notify_tags['[sitename]'] = pn_site_name();
								$notify_tags['[user_login]'] = $item->user_login;
								$notify_tags['[purse]'] = $purse;
								$notify_tags['[comment]'] = $item->comment;
								$notify_tags = apply_filters('notify_tags_userverify3_u', $notify_tags, $item);					
							
								$user_send_data = array(
									'user_email' => is_email($item->user_email),
								);	
								$user_send_data = apply_filters('user_send_data', $user_send_data, 'userverify3_u', $ui);
								$result_mail = apply_filters('premium_send_message', 0, 'userverify3_u', $notify_tags, $user_send_data, $user_locale); 								
							}		
						}
					}			
				}

				if($action == 'false'){			
					foreach($_POST['id'] as $id){
						$id = intval($id);		
						$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."uv_wallets WHERE id = '$id' AND status != '2'");
						if(isset($item->id)){
							$user_wallet_id = $item->user_wallet_id;
								
							$res = apply_filters('item_uv_wallets_false_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){	
								
								do_action('item_uv_wallets_false', $id, $item);
								$arr = array();
								$arr['status'] = 2;
								$result = $wpdb->update($wpdb->prefix . 'uv_wallets', $arr, array('id'=>$item->id));
								if($result){
									do_action('item_uv_wallets_false_after', $id, $item);
								}

								$user_locale = pn_strip_input($item->locale);
								$purse = pn_strip_input($item->wallet_num);
								$ui = get_userdata($item->user_id);
									
								$notify_tags = array();
								$notify_tags['[sitename]'] = pn_site_name();
								$notify_tags['[user_login]'] = $item->user_login;
								$notify_tags['[purse]'] = $purse;
								$notify_tags['[comment]'] = $item->comment;
								$notify_tags = apply_filters('notify_tags_userverify4_u', $notify_tags, $item);					
							
								$user_send_data = array(
									'user_email' => is_email($item->user_email),
								);	
								$user_send_data = apply_filters('user_send_data', $user_send_data, 'userverify4_u', $ui);
								$result_mail = apply_filters('premium_send_message', 0, 'userverify4_u', $notify_tags, $user_send_data, $user_locale);

								$verify_request = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."uv_wallets WHERE user_wallet_id='$user_wallet_id' AND status='1'");
								if($verify_request == 0){			
									$arr = array();
									$arr['verify'] = 0;
									$wpdb->update($wpdb->prefix.'user_wallets', $arr, array('id'=>$user_wallet_id));	
									do_action('item_userwallets_unverify', $user_wallet_id);
								}
							}
						}
					}			
				}				
						
				if($action == 'delete'){			
					foreach($_POST['id'] as $id){
						$id = intval($id);			
						$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."uv_wallets WHERE id = '$id'");
						if(isset($item->id)){
							$user_wallet_id = $item->user_wallet_id;
							
							$res = apply_filters('item_uv_wallets_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){						
							
								do_action('item_uv_wallets_delete', $id, $item);
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."uv_wallets WHERE id = '$id'");
								if($result){
									do_action('item_uv_wallets_delete_after', $id, $item);
								}
							
								$user_locale = pn_strip_input($item->locale);
								$purse = pn_strip_input($item->wallet_num);
								$ui = get_userdata($item->user_id);
									
								$notify_tags = array();
								$notify_tags['[sitename]'] = pn_site_name();
								$notify_tags['[user_login]'] = $item->user_login;
								$notify_tags['[purse]'] = $purse;
								$notify_tags['[comment]'] = $item->comment;
								$notify_tags = apply_filters('notify_tags_userverify5_u', $notify_tags, $item);					
							
								$user_send_data = array(
									'user_email' => is_email($item->user_email),
								);	
								$user_send_data = apply_filters('user_send_data', $user_send_data, 'userverify5_u', $ui);
								$result_mail = apply_filters('premium_send_message', 0, 'userverify5_u', $notify_tags, $user_send_data, $user_locale);
							
								$verify_request = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."uv_wallets WHERE user_wallet_id='$user_wallet_id' AND status='1'");
								if($verify_request == 0){		
									$arr = array();
									$arr['verify'] = 0;
									$wpdb->update($wpdb->prefix.'user_wallets', $arr, array('id'=>$user_wallet_id));								
									do_action('item_userwallets_unverify', $user_wallet_id);
								}						
							}
						}
					}			
				}
				
				do_action('pntable_uv_wallets_action', $action, $_POST['id']);
				$reply = '&reply=true';
			} 
		}
				
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
	} 

	class pn_userwallets_verify_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'create_date';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			
			if($column_name == 'accnum'){
				return pn_strip_input($item->wallet_num);
			} elseif($column_name == 'create_date'){	
				return get_pn_time($item->create_date,'d.m.Y, H:i');
			} elseif($column_name == 'ip'){
				return pn_strip_input($item->user_ip);
			} elseif($column_name == 'user'){
				$user_id = $item->user_id;
				$us = '<a href="'. pn_edit_user_link($user_id) .'">'. is_user($item->user_login) . '</a>';
				return $us;
			} elseif($column_name == 'ps'){ 	
				return get_currency_title_by_id($item->currency_id);
			} elseif($column_name == 'files'){
				if(function_exists('get_usac_files')){
					return get_usac_files($item->user_wallet_id);
				}
			} elseif($column_name == 'status'){
				if($item->status == 1){
					$status ='<span class="bgreen">'. __('Verified','pn') .'</span>';
				} elseif($item->status == 2){
					$status ='<span class="bred">'. __('Unverified','pn') .'</span>';
				} else {
					$status = '<b>'.  __('Pending verification','pn')  .'</b>';
				}
				return $status;
			} elseif($column_name == 'comment') {
				$comment_text = trim($item->comment);		
				return get_comment_label('walletsverify', $item->id, $comment_text);				
			} 
			
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}			
		
		function tr_class($tr_class, $item) {
			if($item->status == 1){
				$tr_class[] = 'tr_green';
			}
			return $tr_class;
		}		
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'create_date'     => __('Creation date','pn'),
				'user'     => __('User','pn'),
				'ip' => __('IP','pn'),
				'ps' => __('PS','pn'),
				'accnum' => __('Account number','pn'),
				'files' => __('Files','pn'),
				'status'  => __('Status','pn'),
				'comment'     => __('Failure reason','pn'),
			);
			return $columns;
		}	

		function get_bulk_actions() {
			$actions = array(
				'true'    => __('Verify','pn'),
				'false'    => __('Unverify','pn'),
				'delete'    => __('Delete','pn'),
			);
			return $actions;
		}
		
		function get_search(){
			$search = array();
			$search['user_login'] = array(
				'view' => 'input',
				'title' => __('User login','pn'),
				'default' => is_user(is_param_get('user_login')),
				'name' => 'user_login',
			);
			$search['wallet_num'] = array(
				'view' => 'input',
				'title' => __('Account number','pn'),
				'default' => pn_strip_input(is_param_get('wallet_num')),
				'name' => 'wallet_num',
			);
			$search['user_ip'] = array(
				'view' => 'input',
				'title' => __('IP','pn'),
				'default' => pn_strip_input(is_param_get('user_ip')),
				'name' => 'user_ip',
			);		
			
			$currency = list_currency(__('All currency','pn'));
			$search[] = array(
				'view' => 'select',
				'options' => $currency,
				'title' => __('Currency','pn'),
				'default' => pn_strip_input(is_param_get('currency_id')),
				'name' => 'currency_id',
			);		
				return $search;
		}			
			
		function get_submenu(){	
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('pending request','pn'),
					'2' => __('verified request','pn'),
					'3' => __('unverified request','pn'),
				),
				'title' => '',
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
			if($filter == 1){ //в ожидании
				$where .= " AND status = '0'";
			} elseif($filter == 2) { //верифицированные
				$where .= " AND status = '1'";
			} elseif($filter == 3) { //не верифицированные
				$where .= " AND status = '2'";
			}  		

			$user_login = is_user(is_param_get('user_login'));
			if($user_login){
				$where .= " AND user_login LIKE '%$user_login%'";
			}

			$user_ip = pn_sfilter(pn_strip_input(is_param_get('user_ip')));
			if($user_ip){
				$where .= " AND user_ip LIKE '%$user_ip%'";
			}

			$wallet_num = pn_sfilter(pn_strip_input(is_param_get('wallet_num')));
			if($wallet_num){
				$where .= " AND wallet_num LIKE '%$wallet_num%'";
			}

			$currency_id = intval(is_param_get('currency_id'));
			if($currency_id){ 
				$where .= " AND currency_id = '$currency_id'";
			}		
			
			$where = $this->search_where($where);
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."uv_wallets WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."uv_wallets WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}	
	}  
}