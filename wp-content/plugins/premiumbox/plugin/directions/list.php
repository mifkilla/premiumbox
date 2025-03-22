<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_directions', 'pn_adminpage_title_pn_directions');
	function pn_adminpage_title_pn_directions(){
		_e('Exchange directions','pn');
	}

	add_action('pn_adminpage_content_pn_directions','def_pn_adminpage_content_pn_directions');
	function def_pn_adminpage_content_pn_directions(){
		premium_table_list();
		?>
<script type="text/javascript">
jQuery(function($){	
	
	$(document).on('change', '.merch_once', function(){
		var parent_div = $(this).parents('.merch_div');	
		parent_div.find('input, select').prop('disabled', true);
		var m = parent_div.attr('data-m');
		var id = parent_div.attr('data-id');
		
		var arrs = [];
		var k = -1;
		parent_div.find('input:checked, select').each(function(){ k++;
			arrs[k] = $(this).val();
		});
		
		$('#premium_ajax').show();
		var param = 'id=' + id + '&m=' + m + '&arrs=' + arrs;
		
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('merchant_direction_save', 'post'); ?>",
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{
				$('#premium_ajax').hide();	
				parent_div.find('input, select').prop('disabled', false);
			}
		});
		
		return false;
	});
		
});
</script>		
		<?php
	}

	add_action('premium_action_merchant_direction_save', 'pn_premium_action_merchant_direction_save');
	function pn_premium_action_merchant_direction_save(){
	global $wpdb;
		only_post();
		if(current_user_can('administrator') or current_user_can('pn_directions_merchant')){
			$type = trim(is_param_post('m'));
			if($type != 'paymerchants'){ $type = 'merchants'; }
			$data_id = intval(is_param_post('id'));
			if($data_id > 0){
				$arrs = explode(',', is_param_post('arrs'));
				$n_arrs = array();
				foreach($arrs as $arr){
					$arr = is_extension_name($arr);
					if($arr){
						$n_arrs[] = $arr;
					}
				}
				$array = array();
				if($type == 'merchants'){
					$array['m_in'] = @serialize($n_arrs);
				} else {
					$array['m_out'] = @serialize($n_arrs);
				}
				$wpdb->update($wpdb->prefix.'directions', $array, array('id'=>$data_id));
			}	
		}  			
	}

	add_action('premium_action_pn_directions','def_premium_action_pn_directions');
	function def_premium_action_pn_directions(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_directions'));

		$reply = '';
		$action = get_admin_action();
				
		if(isset($_POST['filter'])){
					
			$ref = is_param_post('_wp_http_referer');
			$url = pn_admin_filter_data($ref, 'reply, curr_give, curr_get, paged');			
				
			$curr_give = intval(is_param_post('curr_give'));
			if($curr_give){
				$url .= '&curr_give='.$curr_give;
			}
					
			$curr_get = intval(is_param_post('curr_get'));
			if($curr_get){
				$url .= '&curr_get='.$curr_get;
			}				
					
			wp_redirect($url);
			exit;
					
		} elseif(isset($_POST['back_filter'])){	
					
			$ref = is_param_post('_wp_http_referer');
			$url = pn_admin_filter_data($ref, 'reply, curr_give, curr_get, paged');		
				
			$curr_give = intval(is_param_post('curr_give'));
			if($curr_give){
				$url .= '&curr_get=' . $curr_give;
			}
					
			$curr_get = intval(is_param_post('curr_get'));
			if($curr_get){
				$url .= '&curr_give=' . $curr_get;
			}				
					
			wp_redirect($url);
			exit;				
				
		} elseif(isset($_POST['save'])){
					
			$v = get_currency_data();		
							
			if(isset($_POST['course_give'], $_POST['course_get']) and is_array($_POST['course_give']) and is_array($_POST['course_get'])){	
				$now_date = current_time('mysql');	
				foreach($_POST['course_give'] as $id => $course_give){
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."directions WHERE id='$id'");
					if(isset($item->id)){
						if(isset($v[$item->currency_id_give], $v[$item->currency_id_get])){
							$vd1 = $v[$item->currency_id_give];
							$vd2 = $v[$item->currency_id_get];
							
							$course_give = is_sum($course_give, $vd1->currency_decimal);
							$course_get = is_sum($_POST['course_get'][$id], $vd2->currency_decimal);
									
							$arr = array();				
							if($course_give != $item->course_give or $course_get != $item->course_get){
								$arr['course_give'] = $course_give;
								$arr['course_get'] = $course_get;
							}
							if(count($arr) > 0){
								$arr['edit_date'] = $now_date;
								$wpdb->update($wpdb->prefix.'directions', $arr, array('id'=>$id));
							}
						}
					}	
				}
			}

			if(isset($_POST['com_box_sum1'], $_POST['com_box_sum2'], $_POST['com_box_pers1'], $_POST['com_box_pers2']) and is_array($_POST['com_box_sum1'])){
				foreach($_POST['com_box_sum1'] as $id => $com_box_sum1){
					$id = intval($id);
					$com_box_sum1 = is_sum($com_box_sum1);
					$com_box_sum2 = is_sum($_POST['com_box_sum2'][$id]);			
					$com_box_pers1 = is_sum($_POST['com_box_pers1'][$id]);	
					$com_box_pers2 = is_sum($_POST['com_box_pers2'][$id]);				
								
					$array = array();
					$array['com_box_sum1'] = $com_box_sum1;
					$array['com_box_sum2'] = $com_box_sum2;
					$array['com_box_pers1'] = $com_box_pers1;
					$array['com_box_pers2'] = $com_box_pers2;					
					$wpdb->update($wpdb->prefix.'directions', $array, array('id'=>$id));			
				}
			}		 		
					
			do_action('pntable_directions_save', $v);
			$reply = '&reply=true';

		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){				
					
				if($action == 'basket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."directions WHERE id='$id' AND auto_status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_direction_basket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."directions SET auto_status = '0' WHERE id = '$id'");
								do_action('item_direction_basket', $id, $item, $result);
							}
						}		
					}	
				}
					
				if($action == 'unbasket'){	
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."directions WHERE id='$id' AND auto_status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_direction_unbasket_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."directions SET auto_status = '1' WHERE id = '$id'");
								do_action('item_direction_unbasket', $id, $item, $result);
							}
						}		
					}	
				}					
					
				if($action == 'active'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."directions WHERE id='$id' AND direction_status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_direction_active_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."directions SET direction_status = '1' WHERE id = '$id'");
								do_action('item_direction_active', $id, $item, $result);
							}
						}
					}			
				}

				if($action == 'hold'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."directions WHERE id='$id' AND direction_status != '2'");
						if(isset($item->id)){
							$res = apply_filters('item_direction_hold_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."directions SET direction_status = '2' WHERE id = '$id'");
								do_action('item_direction_hold', $id, $item, $result);
							}
						}
					}		
				}

				if($action == 'deactive'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."directions WHERE id='$id' AND direction_status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_direction_deactive_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."directions SET direction_status = '0' WHERE id = '$id'");
								do_action('item_direction_deactive', $id, $item, $result);
							}
						}
					}		
				}					
					
				if($action == 'delete'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."directions WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_direction_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."directions WHERE id = '$id'");
								do_action('item_direction_delete', $id, $item, $result);
							}
						}
					}			
				}
				
				do_action('pntable_directions_action', $action, $_POST['id']);
				$reply = '&reply=true';		
			} 	
		}
				
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
	} 

	class pn_directions_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
					
			$this->primary_column = 'title';
			$this->save_button = 1;
		}
		
		function get_thwidth(){
			$array = array();
			$array['id'] = '30px';
			$array['course_give'] = '120px';
			$array['course_get'] = '120px';
			$array['comboxlist_give'] = '80px';
			$array['comboxlist_get'] = '80px';
			return $array;
		}	
		
		function column_default($item, $column_name){
			
			$standart_course_direction = apply_filters('standart_course_direction', 0, $item);
			$standart_course_direction = intval($standart_course_direction);
			if($column_name == 'course_give'){
				$dir_c = is_course_direction($item, '', '', 'admin');
				if($standart_course_direction == 0){	
					return '<input type="text" style="width: 100%;" name="course_give['. $item->id .']" value="'. is_isset($dir_c,'give') .'" />'; 
				} else {
					return '<strong>'. is_isset($dir_c,'give') .'</strong>';
				}
			} elseif($column_name == 'course_get'){	
				$dir_c = is_course_direction($item, '', '', 'admin');
				if($standart_course_direction == 0){	
					return '<input type="text" style="width: 100%;" name="course_get['. $item->id .']" value="'. is_isset($dir_c,'get') .'" />';
				} else {
					return '<strong>'. is_isset($dir_c,'get') .'</strong>';
				}
			} elseif($column_name == 'comboxlist_give'){	
				$show = '
				<div><input type="text" style="width: 100%; max-width: 80px;" name="com_box_sum1['. $item->id .']" value="'. is_sum($item->com_box_sum1) .'" /> S</div>
				<div><input type="text" style="width: 100%; max-width: 80px;" name="com_box_pers1['. $item->id .']" value="'. is_sum($item->com_box_pers1) .'" /> %</div>
				';
				return $show;
			} elseif($column_name == 'comboxlist_get'){	
				$show = '
				<div><input type="text" style="width: 100%; max-width: 80px;" name="com_box_sum2['. $item->id .']" value="'. is_sum($item->com_box_sum2) .'" /> S</div>
				<div><input type="text" style="width: 100%; max-width: 80px;" name="com_box_pers2['. $item->id .']" value="'. is_sum($item->com_box_pers2) .'" /> %</div>
				';
				return $show;				
			} elseif($column_name == 'merchant'){	

				$lists = list_extandeds('merchants');
				$m_arr = @unserialize(is_isset($item, 'm_in'));
				$m_arr = (array)$m_arr;
				
				$lists = list_checks_top($lists, $m_arr);
				
				if(count($lists) > 0){
					$html = '<div style="width: 100%; background: #fff; padding: 5px; max-height: 120px; overflow-y: scroll;" class="merch_div" data-m="merchants" data-id="'. $item->id .'">';
						foreach($lists as $m_key => $m_title){
							$checked = '';
							if(in_array($m_key, $m_arr)){ $checked = 'checked="checked"'; }
							
							$link_title = $m_title;
							if(current_user_can('administrator') or current_user_can('pn_merchants')){
								$link_title = '<a href="'. admin_url('admin.php?page=pn_add_merchants&item_key='.$m_key) .'" target="_blank">'. $m_title .'</a>';
							}
							$html .='<div><label><input type="checkbox" class="merch_once" name="" '. $checked .' autocomplete="off" value="'. $m_key .'" /> '. $link_title .'</label></div>';
						}			
					$html .='</div>';
				} else {
					$html = __('No merchants available','pn');
				}
				
				return $html;

			} elseif($column_name == 'paymerchant'){	

				$lists = list_extandeds('paymerchants');
				$m_arr = @unserialize(is_isset($item, 'm_out')); 
				$m_arr = (array)$m_arr;
				
				$lists = list_checks_top($lists, $m_arr);
				
				if(count($lists) > 0){
					$html = '<div style="width: 100%; background: #fff; padding: 5px; max-height: 120px; overflow-y: scroll;" class="merch_div" data-m="paymerchants" data-id="'. $item->id .'">';
						foreach($lists as $m_key => $m_title){
							$checked = '';
							if(in_array($m_key, $m_arr)){ $checked = 'checked="checked"'; }
							
							$link_title = $m_title;
							if(current_user_can('administrator') or current_user_can('pn_merchants')){
								$link_title = '<a href="'. admin_url('admin.php?page=pn_add_paymerchants&item_key='.$m_key) .'" target="_blank">'. $m_title .'</a>';
							}							
							$html .='<div><label><input type="checkbox" class="merch_once" name="" '. $checked .' autocomplete="off" value="'. $m_key .'" /> '. $link_title .'</label></div>';
						}			
					$html .='</div>';
				} else {
					$html = __('No payouts available','pn');
				}	
				
				return $html;			
				
			} elseif($column_name == 'status'){
				if($item->direction_status == 0){ 
					return '<span class="bred">'. __('inactive direction','pn') .'</span>'; 
				} elseif($item->direction_status == 1) { 
					return '<span class="bgreen">'. __('active direction','pn') .'</span>'; 
				} elseif($item->direction_status == 2) { 
					return '<strong>'. __('hold direction','pn') .'</strong>'; 	
				}	
			} elseif($column_name == 'title'){
				return pn_strip_input($item->tech_name);
			} elseif($column_name == 'id'){
				return '<strong>'. $item->id .'</strong>';	
			} 
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_directions&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);
			if($item->direction_status != 0 and $item->auto_status == 1){
				$actions['view'] = '<a href="'. get_exchange_link($item->direction_name) .'" target="_blank">'. __('View','pn') .'</a>';
			}	
			return $actions;
		}		
		
		function tr_class($tr_class, $item) {
			if($item->direction_status == 0){
				$tr_class[] = 'tr_red';
			}			
			return $tr_class;
		}		
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'id'     => __('ID','pn'),
				'title'     => __('Direction','pn'),
				'course_give' => __('Exchange rate 1','pn'),
				'course_get' => __('Exchange rate 2','pn'),
				'comboxlist_give' => __('Additional sender fee','pn'),
				'comboxlist_get' => __('Additional recipient fee','pn'),
			);
			if(current_user_can('administrator') or current_user_can('pn_directions_merchant')){
				$columns['merchant'] = __('Merchant','pn');
				$columns['paymerchant'] = __('Automatic payouts','pn');
			}	
			$columns['status'] = __('Status','pn');
			return $columns;
		}	
		
		function get_sortable_columns() {
			$sortable_columns = array( 
				'id' => array('id', 'desc'),
			);
			return $sortable_columns;
		}
		
		function get_bulk_actions() {
			$actions = array(
				'active'    => __('Activate','pn'),
				'hold'    => __('Freeze','pn'),
				'deactive'    => __('Deactivate','pn'),
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
			
		function get_submenu(){
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'2' => __('active direction','pn'),
					'1' => __('inactive direction','pn'),
					'3' => __('frozen direction','pn'),
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
			$in_filter = array('1','2','3');
			if(in_array($filter,$in_filter)){
				$filter = $filter - 1;
				$where .= " AND direction_status='$filter'"; 	
			}
			
			if($filter == 9){	
				$where .= " AND auto_status = '0'";
			} else {
				$where .= " AND auto_status = '1'";
			}			
			
			$curr_give = intval(is_param_get('curr_give'));
			if($curr_give > 0){ 
				$where .= " AND currency_id_give = '$curr_give'"; 
			}
			$curr_get = intval(is_param_get('curr_get'));
			if($curr_get > 0){ 
				$where .= " AND currency_id_get = '$curr_get'"; 
			}		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."directions WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."directions WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {
			global $wpdb;

			if ( 'top' == $which ) {
				$currencies = list_currency(__('All currency','pn'));
				$curr_give = intval(is_param_get('curr_give'));
				$curr_get = intval(is_param_get('curr_get'));
				?>
				<select name="curr_give" autocomplete="off">
					<?php
					foreach($currencies as $currency_key => $currency_title){
					?>
						<option value="<?php echo $currency_key; ?>" <?php selected($currency_key, $curr_give); ?>><?php echo $currency_title; ?></option>
					<?php
					}
					?>
				</select>
				
				<input type="submit" name="back_filter" class="back_filter" value="">
				
				<select name="curr_get" autocomplete="off">
					<?php
					foreach($currencies as $currency_key => $currency_title){
					?>
						<option value="<?php echo $currency_key; ?>" <?php selected($currency_key, $curr_get); ?>><?php echo $currency_title; ?></option>
					<?php
					}
					?>			
				</select>
				
				<input type="submit" name="filter" value="<?php _e('Filter','pn'); ?>">
				<?php
			}
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_directions');?>"><?php _e('Add new','pn'); ?></a>		
		<?php  
		}
	}
}