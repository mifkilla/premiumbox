<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_bc_corrs', 'def_adminpage_title_pn_bc_corrs');
	function def_adminpage_title_pn_bc_corrs(){
		_e('Adjustments','pn');
	}

	add_action('pn_adminpage_content_pn_bc_corrs','def_pn_adminpage_content_pn_bc_corrs');
	function def_pn_adminpage_content_pn_bc_corrs(){
		premium_table_list();
	}

	add_action('premium_action_pn_bc_corrs','def_premium_action_pn_bc_corrs');
	function def_premium_action_pn_bc_corrs(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_bestchange'));

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
				
			if(isset($_POST['ids']) and is_array($_POST['ids'])){
				foreach($_POST['ids'] as $id){
					$id = intval($id);
					$arr = array();
					if(isset($_POST['v1'])){
						$arr['v1'] = intval($_POST['v1'][$id]);
					}	
					if(isset($_POST['v2'])){
						$arr['v2'] = intval($_POST['v2'][$id]);
					}
					if(isset($_POST['pars_position'])){
						$arr['pars_position'] = pn_strip_input($_POST['pars_position'][$id]);
					}
					if(isset($_POST['step'])){
						$arr['step'] = pn_parser_num($_POST['step'][$id]);
					}
					if(isset($_POST['min_res'])){
						$arr['min_res'] = is_sum($_POST['min_res'][$id]);
					}
					if(isset($_POST['min_sum'])){
						$arr['min_sum'] = is_sum($_POST['min_sum'][$id]);
					}
					if(isset($_POST['max_sum'])){
						$arr['max_sum'] = is_sum($_POST['max_sum'][$id]);
					}
					if(isset($_POST['standart_course_give'])){
						$arr['standart_course_give'] = is_sum($_POST['standart_course_give'][$id]);
					}
					if(isset($_POST['standart_course_get'])){
						$arr['standart_course_get'] = is_sum($_POST['standart_course_get'][$id]);
					}
					if(isset($_POST['reset_course'])){
						$arr['reset_course'] = intval($_POST['reset_course'][$id]);
					}
					if(count($arr) > 0){
						$wpdb->update($wpdb->prefix."bestchange_directions", $arr, array('id'=>$id));
					}	
				}
			}					
					
			do_action('pntable_bccorrs_save');
			$reply = '&reply=true';

		} else {		
			if(isset($_POST['id']) and is_array($_POST['id'])){				
					
				if($action == 'active'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."bestchange_directions WHERE id='$id' AND status != '1'");
						if(isset($item->id)){
							$res = apply_filters('item_bccorrs_active_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."bestchange_directions SET status = '1' WHERE id = '$id'");
								do_action('item_bccorrs_active', $id, $item, $result);
							}
						}
					}				
				}

				if($action == 'deactive'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."bestchange_directions WHERE id='$id' AND status != '0'");
						if(isset($item->id)){
							$res = apply_filters('item_bccorrs_deactive_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("UPDATE ".$wpdb->prefix."bestchange_directions SET status = '0' WHERE id = '$id'");
								do_action('item_bccorrs_deactive', $id, $item, $result);
							}
						}
					}	
				}					
					
				if($action == 'delete'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."bestchange_directions WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_bccorrs_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."bestchange_directions WHERE id = '$id'");
								do_action('item_bccorrs_delete', $id, $item, $result);
							}
						}
					}			
				}
				
				do_action('pntable_bccorrs_action', $action, $_POST['id']);
				$reply = '&reply=true';	
			} 		
		}
				
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
	} 

	class pn_bc_corrs_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
					
			$this->primary_column = 'title';
			$this->save_button = 1;
		}
		
		function column_default($item, $column_name){
		global $wpdb;    
			if($column_name == 'status'){	
				if($item->status == 0){ 
					return '<span class="bred">'. __('No','pn') .'</span>'; 
				} else { 
					return '<span class="bgreen">'. __('Yes','pn') .'</span>'; 
				}		
			} elseif($column_name == 'title'){	
				$direction_id = intval($item->direction_id);
				$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE id='$direction_id'");
				$direction_title = '';
				if(isset($direction->id)){
					$direction_title = pn_strip_input($direction->tech_name);
				}
				return $direction_title . '<input type="hidden" name="ids[]" value="'. $item->id .'" />';
			} elseif($column_name == 'course'){	
				$direction_id = intval($item->direction_id);
				$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE id='$direction_id'");
				if(isset($direction->id)){ 
					$dir_c = is_course_direction($direction, '', '', 'admin');
					return is_isset($dir_c,'give') . '&rarr;' . is_isset($dir_c,'get');
				}
			} elseif($column_name == 'giveget'){
				$alls = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."bestchange_currency_codes");
				$html = '
				<div>
					<select name="v1['. $item->id .']" autocomplete="off" style="max-width: 100%;">
						<option value="0">--'. __('Give','pn') .'</option>
					';
						foreach($alls as $all){
							$html .= '<option value="'. $all->currency_code_id .'" '. selected($all->currency_code_id, $item->v1, false) .'>'. pn_strip_input($all->currency_code_title) .'</option>';
						}
					$html .= '
					</select>
				</div>
				<div>
					<select name="v2['. $item->id .']" autocomplete="off" style="max-width: 100%;">
						<option value="0">--'. __('Get','pn') .'</option>
					';
						foreach($alls as $all){
							$html .= '<option value="'. $all->currency_code_id .'" '. selected($all->currency_code_id, $item->v2, false) .'>'. pn_strip_input($all->currency_code_title) .'</option>';
						}
					$html .= '
					</select>
				</div>';			
				return $html;	
			} elseif($column_name == 'standart'){
				$html = '
				<div>
					<select name="reset_course['. $item->id .']" autocomplete="off" style="max-width: 100%;">
						<option value="0" '. selected(0, $item->reset_course, false) .'>'. __('No','pn') .'</option>
						<option value="1" '. selected(1, $item->reset_course, false) .'>'. __('Yes','pn') .'</option>
					</select>
				</div>
				<div>
					<input type="text" style="width: 50px;" name="standart_course_give['. $item->id .']" value="'. is_sum($item->standart_course_give) .'" /> &rarr; <input type="text" style="width: 50px;" name="standart_course_get['. $item->id .']" value="'. is_sum($item->standart_course_get) .'" />
				</div>
				';
				return $html;
			} elseif($column_name == 'position'){
				$html = '<input type="text" name="pars_position['. $item->id .']" style="width: 70px;" value="'. pn_strip_input($item->pars_position) .'" />';
				return $html;
			} elseif($column_name == 'minres'){
				$html = '<input type="text" name="min_res['. $item->id .']" style="width: 70px;" value="'. is_sum($item->min_res) .'" />';
				return $html;
			} elseif($column_name == 'step'){
				$html = '<input type="text" name="step['. $item->id .']" style="width: 70px;" value="'. pn_parser_num($item->step) .'" />';
				return $html;
			} elseif($column_name == 'minsum'){
				$html = '<input type="text" name="min_sum['. $item->id .']" style="width: 70px;" value="'. is_sum($item->min_sum) .'" />';
				return $html;
			} elseif($column_name == 'maxsum'){
				$html = '<input type="text" name="max_sum['. $item->id .']" style="width: 70px;" value="'. is_sum($item->max_sum) .'" />';
				return $html;			
			} 
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}

		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_bc_add_corrs&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
				'view' 		=> '<a href="'. admin_url('admin.php?page=pn_add_directions&item_id='. $item->direction_id) .'" target="_blank">'. __('View','pn') .'</a>',
			);
			return $actions;
		}		
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __('Exchange direction','pn'),
				'course'     => __('Rate','pn'),
				'giveget'     => __('Send and Receive','pn'),
				'position'    => __('Position','pn'),
				'minres'    => __('Min reserve for position','pn'),
				'step'    => __('Step','pn'),
				'minsum'    => __('Min rate','pn'),
				'maxsum'    => __('Max rate','pn'),
				'standart'    => __('Standart rate','pn'),
				'status'    => __('Status','pn'),
			);
			return $columns;
		}	
		
		function tr_class($tr_class, $item) {
			if($item->status != 1){
				$tr_class[] = 'tr_red';
			}			
			return $tr_class;
		}		
		
		function get_bulk_actions() {
			$actions = array(
				'active'    => __('Activate','pn'),
				'deactive'    => __('Deactivate','pn'),
				'delete'    => __('Delete','pn'),
			);
			return $actions;
		}
		
		function get_search(){
		global $wpdb;	
			$search = array();
			$options = array(
				'0' => '--'. __('All','pn').'--',
			);
			$directions = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions ORDER BY site_order1 ASC");
			foreach($directions as $direction){ 
				$options[$direction->id]= pn_strip_input($direction->tech_name) . pn_item_status($direction, 'direction_status') . pn_item_basket($direction);
			}		
			$search[] = array(
				'view' => 'select',
				'title' => __('Exchange direction','pn'),
				'default' => pn_strip_input(is_param_get('direction_id')),
				'options' => $options,
				'name' => 'direction_id',
			);			
				return $search;
		}			
		
		function get_submenu(){
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('active parser','pn'),
					'2' => __('inactive parser','pn'),
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
			if($filter == 1){ 
				$where .= " AND status='1'"; 
			} elseif($filter == 2){
				$where .= " AND status='0'";
			}		
			
			$direction_id = intval(is_param_get('direction_id'));
			if($direction_id > 0){ 
				$where .= " AND direction_id='$direction_id'"; 
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
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."bestchange_directions WHERE id > 0 $where");
			}	
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."bestchange_directions WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ){
			$currency = list_currency(__('All currency','pn'));   
			if ( 'top' == $which ) {
				$curr_give = intval(is_param_get('curr_give'));
				$curr_get = intval(is_param_get('curr_get'));
			?>
				<select name="curr_give" autocomplete="off">
				<?php
				foreach($currency as $currency_key => $currency_title){
				?>	
					<option value='<?php echo $currency_key;?>' <?php echo selected($currency_key, $curr_give, false ); ?>><?php echo $currency_title; ?></option>
				<?php
				}
				?>
				</select>
				
				<input type="submit" name="back_filter" class="back_filter" value="">
				
				<select name="curr_get" autocomplete="off">
				<?php
				foreach($currency as $currency_key => $currency_title){
				?>	
					<option value='<?php echo $currency_key;?>' <?php echo selected($currency_key, $curr_get, false ); ?>><?php echo $currency_title; ?></option>
				<?php
				}
				?>
				</select>			
				<input type="submit" name="filter" value="<?php _e('Filter','pn'); ?>">
			<?php
			}
			?>
			<a href="<?php echo admin_url('admin.php?page=pn_bc_add_corrs');?>"><?php _e('Add new','pn'); ?></a>		
		<?php 
		}	  
	} 
}