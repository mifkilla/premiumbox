<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_vaccounts', 'def_adminpage_title_pn_vaccounts');
	function def_adminpage_title_pn_vaccounts(){
		_e('Currency accounts','pn');
	}

	add_action('pn_adminpage_content_pn_vaccounts','def_pn_adminpage_content_pn_vaccounts');
	function def_pn_adminpage_content_pn_vaccounts(){
		$form = new PremiumForm();
		?>
		<div style="margin: 0 0 20px 0;">
		<?php
		$form->help(__('On shortcodes','pn'), 
			__('display = "0" - show once randomly','pn') . '<br />' .
			__('display = "1" - show always randomly','pn') . '<br />' .
			__('display = "2" - show consistently within each order','pn') . '<br />' .
			__('hide = "0" - visible account number','pn') . '<br />' .
			__('hide = "1" - invisible (hide) account number','pn') . '<br />' .
			__('copy = "0" - remove copy account function','pn') . '<br />' .
			__('copy = "1" - copy account entirely','pn') . '<br />' .
			__('copy = "2"  - copy each space-separated account','pn') . '<br />'		
		);
		?>
		</div>
		<?php
		premium_table_list();
	}

	add_filter('csl_get_curracc', 'def_csl_get_curracc', 10, 2);
	function def_csl_get_curracc($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_vaccounts')){
			$comment = '';
			$last = '';
			
			$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."comment_system WHERE item_id='$id' AND itemtype='curracc' ORDER BY comment_date DESC");
			foreach($items as $item){ 
				$last .= '
				<div class="one_comment">
					<div class="one_comment_author"><span class="one_comment_del js_csl_del" data-bd="curracc" data-id="'. $item->id .'"></span><a href="'. pn_edit_user_link($item->user_id) .'" target="_blank">'. pn_strip_input($item->user_login) .'</a>, <span class="one_comment_date">'. get_pn_time($item->comment_date,'d.m.Y, H:i:s') .'</span></div>
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
	
	add_filter('csl_add_curracc', 'def_csl_add_curracc', 10, 2);
	function def_csl_add_curracc($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_vaccounts')){
			$ui = wp_get_current_user();
			$text = pn_strip_input(is_param_post('comment'));
			$log['status'] = 'success';
			if($text){
				$arr = array();
				$arr['comment_date'] = current_time('mysql');
				$arr['user_id'] = $ui->ID;
				$arr['user_login'] = pn_strip_input($ui->user_login);
				$arr['text_comment'] = $text;
				$arr['itemtype'] = 'curracc';
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

	add_filter('csl_del_curracc', 'def_csl_del_curracc', 10, 2);
	function def_csl_del_curracc($log, $id){
	global $wpdb;
	
		if(current_user_can('administrator') or current_user_can('pn_vaccounts')){
			$log['status'] = 'success';
			$wpdb->query("DELETE FROM ".$wpdb->prefix."comment_system WHERE itemtype = 'curracc' AND id = '$id'");
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Authorisation Error','pn');
		}		
		
		return $log;
	}	

	add_action('premium_action_pn_vaccounts','def_premium_action_pn_vaccounts');
	function def_premium_action_pn_vaccounts(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_vaccounts'));

		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){
							
			do_action('pntable_vaccounts_save');
			$reply = '&reply=true';

		} else {
			if(isset($_POST['id']) and is_array($_POST['id'])){				
						
				if($action=='active'){
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_accounts WHERE id='$id' AND status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_vaccounts_active_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->update($wpdb->prefix.'currency_accounts', array('status'=>'1'), array('id'=>$id));
								do_action('item_vaccounts_active', $id, $item, $result);
							}
						}
					}
				}	

				if($action=='notactive'){
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_accounts WHERE id='$id' AND status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_vaccounts_notactive_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->update($wpdb->prefix.'currency_accounts', array('status'=>'0'), array('id'=>$id));
								do_action('item_vaccounts_notactive', $id, $item, $result);
							}
						}	
					}
				}	

				if($action=='clearpr'){
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_accounts WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_vaccounts_clearpr_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->update($wpdb->prefix.'currency_accounts', array('count_visit'=>'0'), array('id'=>$id));
								do_action('item_vaccounts_clearpr', $id, $item, $result);
							}
						}
					}
				}	

				if($action=='delete'){
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_accounts WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_vaccounts_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."currency_accounts WHERE id = '$id'");
								do_action('item_vaccounts_delete', $id, $item, $result);
								delete_vaccs_txtmeta($id);
							}
						}
					}
				}
				
				do_action('pntable_vaccounts_action', $action, $_POST['id']);
				$reply = '&reply=true';			
			} 
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	}  

	class pn_vaccounts_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'cid';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'idsnew'){
				$code = "[num_schet currency_id='". $item->currency_id ."' display='2' hide='0' copy='1']";
				return '<input type="text" style="width: 100%;" class="clpb_item" name="" data-clipboard-text="['. $code .']" value="'. $code .'" />';
			} elseif($column_name == 'currency'){
				return get_currency_title_by_id($item->currency_id);
			} elseif($column_name == 'title'){
				$accountnum_or = pn_strip_input(get_vaccs_txtmeta($item->id, 'accountnum'));
				$accountnum = $item->accountnum;
				if($accountnum != $accountnum_or and $accountnum_or){
					return '<span class="bred_dash">'. $accountnum .'</span> <span class="bgreen">'. $accountnum_or .'</span>';
				} else {
					return $accountnum;
				}
			} elseif($column_name == 'cv'){
				return intval($item->count_visit);	
			} elseif($column_name == 'mcv'){
				return intval($item->max_visit);			
			} elseif($column_name == 'inday'){
				return is_sum($item->inday);
			} elseif($column_name == 'inmonth'){
				return is_sum($item->inmonth);
			} elseif($column_name == 'sinday'){
				$time = current_time('timestamp');
				$date = date('Y-m-d 00:00:00',$time);
				return get_vaccount_sum($item->accountnum, 'in', $date);
			} elseif($column_name == 'sinmonth'){
				$time = current_time('timestamp');
				$date = date('Y-m-01 00:00:00',$time);			
				return get_vaccount_sum($item->accountnum, 'in', $date);	
			} elseif($column_name == 'comment'){
				$has_comment = intval(is_isset($item, 'has_comment'));
				return get_comment_label('curracc', $item->id, $has_comment);
			} elseif($column_name == 'cid'){
				return $item->currency_id;
			} elseif($column_name == 'status'){
				$st = $item->status;
				if($st == 0){
					return '<span class="bred">'. __('inactive account','pn') .'</span>';
				} else { 
					return '<span class="bgreen">'. __('active account','pn') .'</span>';
				}		
			} 	
			return '';
		}

		function tr_class($tr_class, $item) {
			if($item->status == 0){
				$tr_class[] = 'tr_red';
			}
			return $tr_class;
		}		
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_vaccounts&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}		
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'cid'    => __('Shortcode ID','pn'),
				'idsnew'    => __('Shortcode','pn'),
				'currency'    => __('Currency name','pn'),
				'title'    => __('Account','pn'),
				'status'    => __('Status','pn'),
				'mcv'    => __('Hits limit','pn'),	
				'cv'    => __('Hits','pn'),	
				'inday' => __('Daily limit','pn'),
				'inmonth' => __('Monthly limit','pn'),
				'sinday' => __('Amount of exchanges (today)','pn'),
				'sinmonth' => __('Amount of exchanges (month)','pn'),
				'comment'     => __('Comment','pn'),
			);
			return $columns;
		}	
		
		function get_search(){
			$search = array();
			$search[] = array(
				'view' => 'input',
				'title' => __('Account','pn'),
				'default' => pn_strip_input(is_param_get('item')),
				'name' => 'item',
			);	
			$currencies = list_currency(__('All currency','pn'));
			$search[] = array(
				'view' => 'select',
				'title' => __('Currency','pn'),
				'default' => pn_strip_input(is_param_get('currency_id')),
				'name' => 'currency_id',
				'options' => $currencies,
			);				
			return $search;		
		}	
			
		function get_submenu(){	
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('active accounts','pn'),
					'2' => __('inactive accounts','pn'),
				),
				'title' => '',
			);		
			return $options;
		}		

		function get_bulk_actions() {
			$actions = array(
				'active'    => __('Activated','pn'),
				'notactive'    => __('Deactivate','pn'),
				'clearpr'    => __('Reset counter','pn'),
				'delete'    => __('Delete','pn'),
			);
			return $actions;
		}
		 
		function get_sortable_columns() {
			$sortable_columns = array( 
				'cid'     => array('currency_id', 'DESC'),
				'cv'     => array('(count_visit -0.0)',false),
				'mcv'     => array('(max_visit -0.0)',false),
				'inday'     => array('(inday -0.0)',false),
				'inmonth'     => array('(inmonth -0.0)',false),
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
				$where .= " AND status='1'"; 
			} elseif($filter == 2){
				$where .= " AND status='0'";
			}		
			
			$currency_id = intval(is_param_get('currency_id'));
			if($currency_id > 0){ 
				$where .= " AND currency_id='$currency_id'"; 
			}

			$accountnum = pn_sfilter(pn_strip_input(is_param_get('item')));
			if($accountnum){ 
				$where .= " AND accountnum LIKE '%$accountnum%'"; 
			}		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."currency_accounts WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT *, (SELECT COUNT(". $wpdb->prefix ."comment_system.id) FROM ". $wpdb->prefix ."comment_system WHERE itemtype='curracc' AND item_id = ". $wpdb->prefix ."currency_accounts.id) AS has_comment $select_sql FROM ". $wpdb->prefix ."currency_accounts WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_vaccounts');?>"><?php _e('Add new','pn'); ?></a>
			<a href="<?php echo admin_url('admin.php?page=pn_add_vaccounts_many');?>"><?php _e('Add list','pn'); ?></a>		
		<?php 
		}	  
	}
}