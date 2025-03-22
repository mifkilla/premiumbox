<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('PremiumTable')){
	class PremiumTable {
		
		public $version = "0.5";
		public $count_items = 20;
		public $primary_column = 'title';
		public $page = '';
		public $save_button = 0;
		public $items = array();
		public $total_items = '-1';
		public $navi = 1;
		public $confirm_buttons = array();
		public $transfer_actions = array('delete');
		public $transfer_options = array();

		function __construct()
		{
			$this->confirm_buttons = array('delete' => __('Are you sure you want to delete these items?','premium'));
			
			$this->page = pn_strip_input(is_param_get('page'));
			
			$ui = wp_get_current_user();
			$user_id = intval($ui->ID);
			$mini_navi = intval(is_isset($ui, 'mini_navi'));
			if($mini_navi == 1){
				$this->navi = 0;
			}			
			
			global $user_pntable_settings;
			if(!is_array($user_pntable_settings)){
				$option_name = $this->page . '_Table_List_options';
				$user_pntable_settings = get_user_meta($user_id, $option_name, true);
				if(!is_array($user_pntable_settings)){ $user_pntable_settings = array(); }
			}
		}
		
		function count_items(){
			global $user_pntable_settings;
			
			$count_items = intval(is_isset($user_pntable_settings, 'count_items'));
			if($count_items < 1){ $count_items = $this->count_items; }
			if($count_items < 1){ $count_items = 1; }
			
			return $count_items;
		}
		
		function set_url($not){
			if(!is_array($not)){ $not = array(); }
			
			$now_url = is_isset($_SERVER, 'REQUEST_URI');
			$now_url = str_replace('/wp-admin/','', $now_url);
			$now_url = explode('?',$now_url);
			$now_url = $now_url[0];			
			
			$hidden_items = '';
			if(isset($_GET) and is_array($_GET)){
				foreach($_GET as $key => $val){
					if(!in_array($key, $not)){
						$hidden_items .= '&'. $key . '=' . esc_html($val);
					}	  
				}		  
			}
			
			$url = admin_url($now_url . '?page=' . $this->page . $hidden_items);
			return $url;
		}		
		
		function get_pagenum(){
			$paged = intval(is_param_get('paged')); 
			if($paged < 1){ $paged = 1; }
			return $paged;
		}
		
		function get_offset(){
			$current_page = $this->get_pagenum();
			$per_page = $this->count_items();
			$offset = ($current_page-1)*$per_page;
			return $offset;
		}	
		
		function show_columns(){
			global $user_pntable_settings;
			
			$hide_columns = is_isset($user_pntable_settings, 'hide_columns');
			if(!is_array($hide_columns)){
				$hide_columns = array();
			}
			
			$show_columns = array(); 
			$columns = $this->get_columns_filter();
			if(is_array($columns)){
				foreach($columns as $column_key => $column_title){
					if(!in_array($column_key, $hide_columns)){
						$show_columns[] = $column_key;
					}	
				}	
			}
			
			return $show_columns;
		}		
		
 		function head_action(){
			$count_items = $this->count_items();
			$show_columns = $this->show_columns();
			
			$html = '
			<div class="premium_tf">
				<div class="premium_tf_button open_block"><span>'. __('Display settings','premium') .'</span></div>	
				<form action="'. pn_link('pntable_head_action') .'" method="post">
					'. wp_referer_field(false) .'
					<input type="hidden" name="old_count_items" value="'. $count_items .'" />
					<input type="hidden" name="page" value="'. $this->page .'" />
					
					<div class="premium_tf_ins">';
					
 						$columns = $this->get_columns_filter();
						if(isset($columns['cb'])){
							unset($columns['cb']);
						}
						if(is_array($columns) and count($columns) > 0){
							$html .= '
							<div class="premium_tf_line">
								<div class="premium_tf_label">'. __('Columns','premium') .'</div>
								<div class="premium_tf_items">';
									
									foreach($columns as $column_key => $column_title){ 
										if($column_key != $this->primary_column){
											
											$ch1 = '';
											$ch2 = '';
											if(in_array($column_key, $show_columns)){ 
												$ch1 = 'checked="checked"'; 
											} else {
												$ch2 = 'checked="checked"';
											}
											
											$html .= '
											<div class="premium_tf_item"><label>
												<input name="show_columns[]" type="checkbox" class="premium_tf_checkbox" value="'. $column_key .'" '. $ch1 .'>'. $column_title .'
												<input name="hide_columns[]" type="checkbox" class="premium_tf_checkbox_hidden" style="display: none;" value="'. $column_key .'" '. $ch2 .'>
											</label></div>
											';
										}
									} 
									
									$html .= '
										<div class="premium_clear"></div>
								</div>
							</div>
							';
						} 
						
						$html .= '
						<div class="premium_tf_line">
							<div class="premium_tf_label">'. __('Page navigation','premium') .'</div>
							<div class="premium_tf_items">
								<label>'. __('Number of elements on the page','premium') .': <input type="number" step="1" min="1" max="999" class="screen-per-page" name="count_items" maxlength="3" value="'. $count_items .'"></label>
							</div>							
						</div>
						
						<div class="premium_tf_submit">
							<input type="submit" name="" class="premium_button" value="'. __('Apply','premium') .'" />
						</div>						
					</div>
				</form>
			</div>';
			echo $html;
		}  
		
		function searchbox(){
			$search = $this->get_search();
			$works = pn_admin_prepare_lost('reply, paged');
			$search = apply_filters('pntable_searchbox_'. $this->page, $search);
			
			if(is_array($search) and count($search) > 0){
				$has_filter = 0;
				
				$now_url = is_isset($_SERVER, 'REQUEST_URI');
				$now_url = str_replace('/wp-admin/','', $now_url);
				$now_url = explode('?',$now_url);
				$now_url = $now_url[0];
				
				foreach($search as $item){
					$name = trim(is_isset($item, 'name'));
					if($name){
						$works[] = $name;
					}
				}			 
				?>
				<div class="premium_search">
					<form action="" method="get">
						<?php 
						$hidden_items = '';
						if(isset($_GET) and is_array($_GET)){
							foreach($_GET as $key => $val){
								if(!in_array($key, $works)){
									if($key != 'page'){ 
										$hidden_items .= '&'. $key . '=' . esc_html($val);
									}
								?>
									<input type="hidden" name="<?php echo $key; ?>" value="<?php echo esc_html($val); ?>" />
								<?php 
								}
							}
						} 
						?>
								
						<?php
						foreach($search as $option){
							$view = trim(is_isset($option,'view'));
							$title = trim(is_isset($option,'title'));
							$name = trim(is_isset($option,'name'));
							$default = trim(is_isset($option,'default'));
							if(strlen($default) > 0){
								$has_filter = 1;
							}		

							if($view == 'input'){
								?>
									<div class="premium_search_div">
										<div class="premium_search_label"><?php echo $title; ?></div>
										<input type="search" name="<?php echo $name; ?>" value="<?php echo $default; ?>" />
									</div>
								<?php
							} elseif($view == 'date'){
								?>
									<div class="premium_search_div">
										<div class="premium_search_label"><?php echo $title; ?></div>
										<input type="search" name="<?php echo $name; ?>" class="pn_datepicker" autocomplete="off" value="<?php echo $default; ?>" />
									</div>
								<?php
							} elseif($view == 'datetime'){
								?>
									<div class="premium_search_div">
										<div class="premium_search_label"><?php echo $title; ?></div>
										<input type="search" name="<?php echo $name; ?>" class="pn_datetimepicker" autocomplete="off" value="<?php echo $default; ?>" />
									</div>
								<?php	
							} elseif($view == 'select'){
								$options = is_isset($option,'options');
								?>
									<div class="premium_search_div">
										<div class="premium_search_label"><?php echo $title; ?></div>
										<select name="<?php echo $name; ?>" style="position: relative; top: -1px;" autocomplete="off">
											<?php foreach($options as $key => $title){ ?>
												<option value="<?php echo $key; ?>" <?php selected($key, $default); ?>><?php echo $title; ?></option>
											<?php } ?>
										</select>
									</div>
								<?php					
							} elseif($view == 'line'){
								?>
									<div class="premium_clear"></div>	
									<div class="premium_search_line"></div>
								<?php
							}
						}
						?>
								
					<div class="premium_search_div">
						<div class="premium_search_label"></div>
						<input type="submit" style="float: left; margin: -1px 5px 0 0;" name="" class="premium_button" value="<?php _e('Filter','premium'); ?>"  />
						<?php if($has_filter){ ?>
							<a href="<?php echo admin_url($now_url . '?page=' . $this->page . $hidden_items);?>" style="background: #fef4f4; margin: -1px 0 0 0;" class="premium_button"><?php _e('Cancel','premium'); ?></a>
						<?php } ?>
					</div>	
						<div class="premium_clear"></div>	
					</form>		
				</div>
					<div class="premium_clear"></div>	
				<?php 
			} 
		}  	 
		
		function submenu(){
			$options = $this->get_submenu();
			$options = apply_filters('pntable_submenu_'. $this->page, $options);
			if(is_array($options)){
				foreach($options as $option_name => $option){
					$title = pn_strip_input(is_isset($option, 'title'));
					$lists = is_isset($option, 'options');
					$mod = pn_strip_input(is_param_get($option_name));
					
					$link = $this->set_url(array('reply','paged','page',$option_name));
					
					$temp = '
					<div class="premium_submenu">';
						if($title){ 
							$temp .= '
							<div class="premium_submenu_title">
								'. $title .':
							</div>';
						} 
						$cl='';
						if(!$mod){ $cl = 'class="current"'; }
						
						$temp .= '
						<ul>
							<li '. $cl .'><a href="'. $link .'">'. __('All', 'premium') .'</a></li>';
							
							if(is_array($lists)){
								foreach($lists as $key => $val){
									$cl = '';
									if($mod == $key){
										$cl = 'class="current"';
									}	
									
									$temp .= '<li '. $cl .'>| <a href="'. $link . '&' . $option_name . '=' . $key .'">'. $val .'</a></li>';
								}
							} 
							
						$temp .= '	
							<div class="premium_clear"></div>
						</ul>';
						
					$temp .= '	
					</div>
						<div class="premium_clear"></div>';
						
					echo $temp;	
				}
			}			
		}
		
		function actions($which){ 		
			if($which != 'top'){ $which = 'bottom'; }
			
			$actions = $this->get_bulk_actions();
			$actions = apply_filters('pntable_bulkactions_'. $this->page, $actions);
			$select_name = 'action';
			if($which == 'bottom'){
				$select_name = 'action2';
			}
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$items = $this->items;
			$count_items = count($items);
			$total_items = $this->total_items;
			$total_pages = 0;
			if($total_items > 0){
				$total_pages = ceil($total_items/$per_page);
			}
			$prev = $current_page - 1;
			if($prev < 1){ $prev = 1; }
			
			$next = $current_page + 1;
			if($next > $total_pages and $total_items != '-1'){ $next = $total_pages; }
			
			$url = $this->set_url(array('reply','paged','page'));
			?>
			<div class="premium_table_pagenavi">
				<?php if($total_items != '-1'){ ?><div class="premium_table_pagenavi_text"><strong><?php _e('items','premium'); ?>:</strong> <?php echo $total_items; ?> -</div><?php } ?>
				<?php if($current_page != 1){ ?><a href="<?php echo $url; ?>&paged=1">«</a><?php } ?>
				<?php if($prev != $current_page){ ?><a href="<?php echo $url; ?>&paged=<?php echo $prev; ?>">&larr;</a><?php } ?>
				<?php if($which == 'top'){ ?>
					<input type="text" name="paged" value="<?php echo $current_page; ?>" autocomplete="off" /> 
				<?php } else { ?>
					<div class="premium_table_pagenavi_text"><?php echo $current_page; ?></div>
				<?php } ?>
				<?php if($total_pages){ ?><div class="premium_table_pagenavi_text"><?php _e('out of','premium'); ?> <?php echo $total_pages; ?></div><?php } ?> 
				<?php if($next and $next != $current_page){ ?><a href="<?php echo $url; ?>&paged=<?php echo $next; ?>">&rarr;</a><?php } ?>
				<?php if($total_pages and $total_pages != $current_page){ ?><a href="<?php echo $url; ?>&paged=<?php echo $total_pages; ?>">»</a><?php } ?>
					<div class="premium_clear"></div>
			</div>			
			
			<div class="premium_table_actions">
			
				<?php if(is_array($actions) and count($actions) > 0 and $count_items > 0){ ?>
					<select name="<?php echo $select_name; ?>" class="pntable-bulk-select pntable-bulk-select-<?php echo $which; ?>" autocomplete="off">
						<option value="-1">-- <?php _e('Actions', 'premium'); ?> --</option>
						<?php 
						foreach($actions as $action_key => $action_val){ 
							$bg = '';
							if($action_key == 'delete'){
								$bg = 'background: #ff0000; color: #fff;';
							}
						?>
						<option value="<?php echo $action_key; ?>" style="<?php echo $bg; ?>"><?php echo $action_val; ?></option>
						<?php } ?>
					</select>
					
					<?php
					$this->prev_tablenav($which);
					?>
					
					<?php
					$transfer_options = $this->transfer_options;
					if(is_array($transfer_options) and count($transfer_options) > 0){
					?>
					<div class="pntable-transfer-select js_select_search_wrap">
						<select name="transfer_id" autocomplete="off">
							<option value="-1">-- <?php _e('No transfer', 'premium'); ?> --</option>
							<?php 
							foreach($transfer_options as $fs_key => $fs_val){ 
							?>
							<option value="<?php echo $fs_key; ?>"><?php echo $fs_val; ?></option>
							<?php } ?>
						</select>
						<input type="search" name="" class="js_select_search" placeholder="<?php _e('Search...','premium'); ?>" value="" />
					</div>
					<?php
					}
					?>

					<input type="submit" class="pntable-bulk-action pntable-bulk-action-<?php echo $which; ?>" data-key="<?php echo $which; ?>" name="" value="<?php _e('Apply','premium'); ?>"  />	
				
				<?php } else { ?>
					<?php if($which == 'top'){ ?>
						<input type="submit" style="display: none;" name="" value="<?php _e('Apply','premium'); ?>"  />
					<?php } ?>
				<?php } ?>
				
				<?php
				$save_button = apply_filters('pntable_savebutton_'. $this->page, $this->save_button);
				if($save_button == 1 and $count_items > 0){ 
				?>
				<input type="submit" name="save" style="background: #eaf4eb;" value="<?php _e('Save','premium'); ?>"  />
				<?php 
				} 
				
				$this->extra_tablenav($which);
				
				echo apply_filters('pntable_actions_'. $this->page, '');
				?>
				
				<div class="premium_clear"></div>
			</div>	
				<div class="premium_clear"></div>
			<?php
		} 
		
 		function display(){
			$this->prepare_items();
			$this->searchbox();
			$this->submenu();
			
			$columns = $this->get_columns_filter();
			$items = $this->items;
			
			$show_columns = $this->show_columns();
			$show_columns[] = 'cb';
			if($this->primary_column){
				$show_columns[] = $this->primary_column;
			}			
			
			$sortable_columns = $this->get_sortable_columns_filter();
			$orderby = $this->orderby();
			$order = $this->order();
			
			$url = $this->set_url(array('reply','paged','page','orderby','order'));
			
			$confirm_actions = $this->confirm_buttons;
			$transfer_actions = $this->transfer_actions;
			?>
			<script type="text/javascript">
			jQuery(function($){
				<?php 
				$ui = wp_get_current_user();
				$confirm_deletion = intval(is_isset($ui, 'confirm_deletion'));
				if($confirm_deletion != 1){
				?>	
				$(document).on('click', '.pntable-bulk-action', function(){
					var select_key = $(this).attr('data-key');
					var select_action = $('.pntable-bulk-select-'+select_key).val();
					<?php foreach($confirm_actions as $c_action => $c_text){ ?>
					if(select_action == '<?php echo $c_action; ?>'){
						if (!confirm("<?php echo $c_text; ?>")) {
							return false;
						}
					}					
					<?php } ?>
				});
				<?php
				}
				?>
				
				$(document).on('change', '.pntable-bulk-select', function(){
					var now_value = $(this).val();
					$('.pntable-bulk-select').val(now_value);
					$('.pntable-transfer-select').hide();
					<?php foreach($transfer_actions as $t_action){ ?>
					if(now_value == '<?php echo $t_action; ?>'){
						$('.pntable-transfer-select').show();
					}	
					<?php } ?>
				});	
				
				$(document).on('change', '.pntable-transfer-select select', function(){
					var now_value = $(this).val();
					$('.pntable-transfer-select select').val(now_value);
				});					
			});
			</script>
			<style>
			.not_adaptive th.pntable-column-cb{ width: 10px; }
			<?php 
			$th_widths = $this->get_thwidth_filter();
			$no_class = 0;
			foreach($th_widths as $th_wkey => $th_width){
				if($th_wkey == $this->primary_column){
					$no_class = 1;
				}
			?> 
			.not_adaptive th.pntable-column-<?php echo $th_wkey; ?>{ width: <?php echo $th_width; ?>; } 
			<?php
			}
			if($no_class == 0){
			?>
			.not_adaptive th.pntable-column-<?php echo $this->primary_column; ?>{ width: 200px; }
			<?php } ?>
			</style>
			 
			<form method="post" action="<?php the_pn_link(); ?>">
				<?php wp_referer_field(); ?>
				
				<?php
				$this->actions('top');
				$count_columns = count($columns);
				?>				

				<div class="premium_table_wrap">
					
					<?php if($count_columns > 0 and isset($columns['cb'])){ ?>
						<div class="premium_table_checkbox has_adaptive_content"><label><input type="checkbox" class="pntable-checkbox" name="" value="1" /> <strong><?php _e('Check all/Uncheck all','premium'); ?></strong></label></div>
					<?php } ?>
					
					<div class="premium_wrap_table">
						<div class="premium_table">	
							<table>
								<?php 
								$thead = '
								<thead>
									<tr>';
										
										foreach($columns as $column_key => $column_title){ 
											if(in_array($column_key, $show_columns)){
												$class = '';
												if($orderby == $column_key){
													$class = 'th_' . $order;
												}	
												$n_order = 'asc';
												if($order == 'asc'){
													$n_order = 'desc';
												}
											
												$thead .= '
												<th class="pntable-column pntable-column-'. $column_key .' '. $class .'">';
													if(isset($sortable_columns[$column_key])){ 
														$thead .= '<a href="'. $url .'&orderby='. $column_key .'&order='. $n_order .'">';
													} 
														$thead .= '<span>';
														if($column_key == 'cb'){ 
															$thead .= '<input type="checkbox" class="pntable-checkbox" name="" value="1" />';
														} else { 
															$thead .= $column_title;
														} 
														$thead .= '</span>';
													if(isset($sortable_columns[$column_key])){ 
														$thead .= '</a>';
													} 											
												$thead .= '
												</th>
												';
											}
										} 
										
										$thead .= '
									</tr>
								</thead>								
								';
								
								echo $thead;
								?>
								<tbody>
									<?php 
									if(is_array($items) and count($items) > 0){
										$r=0;
										foreach($items as $item){ $r++;
											$tr_class = array();
											$tr_class[]= 'pntable_tr';
											if($r%2 == 0){ $tr_class[]= 'tr_odd'; } else { $tr_class[]= 'tr_even'; }
											$tr_class = $this->tr_class($tr_class, $item);
											$tr_class = apply_filters('pntable_trclass_'. $this->page, $tr_class, $item);
											?>
										<tr class="<?php echo join(' ', $tr_class); ?>">
											<?php 
											foreach($columns as $column_key => $column_title){ 
												if(in_array($column_key, $show_columns)){
											?>
											<td class="pntable-column pntable-column-<?php echo $column_key; ?>">
												
												<?php
												$column_name = 'column_' . $column_key;
												if(method_exists($this, $column_name)){
													echo call_user_func( array( $this, $column_name ), $item, $column_key, $column_title);
												} else {
													echo $this->column_default($item, $column_key);
												}
												
												echo apply_filters('pntable_column_'. $this->page, '', $column_key, $item);
												
												if($this->primary_column == $column_key){
													$this->show_row_filters($item);
													$this->show_row_actions($item);
												}
												?>
												
													<div class="premium_clear"></div>
											</td>			
											<?php 
												}
											} 
											?>
										</tr>
									<?php 
										} 
									} else {
									?>
										<tr>
											<td colspan="<?php echo $count_columns; ?>"><?php _e('No items','premium'); ?></td>
										</tr>
									<?php
									}
									?>
								</tbody>
								<?php 
								echo $thead;
								?>
							</table>
						</div>
					</div>
					
					<?php if($count_columns > 0 and isset($columns['cb'])){ ?>
						<div class="premium_table_checkbox has_adaptive_content"><label><input type="checkbox" class="pntable-checkbox" name="" value="1" /> <strong><?php _e('Check all/Uncheck all','premium'); ?></strong></label></div>
					<?php } ?>
					
				</div>
				
				<?php
				$this->actions('bottom');
				?>
			</form>
			<?php 
		}  
		
 		function show_row_filters($item){
			$actions = $this->get_row_filters_filter($item);
			if(is_array($actions) and count($actions) > 0){
				echo join(' ', $actions);
			}
		}
		
		function get_row_filters_filter($item){
			$actions = $this->get_row_filters($item);
			$actions = apply_filters('pntable_rowfilters_'. $this->page, $actions, $item);
			return $actions;
		}

		function get_row_filters($item){
			return array();
		}		
		
		function show_row_actions($item){
			$actions = $this->get_row_actions_filter($item);
			if(is_array($actions) and count($actions) > 0){
				?>
				<div class="pntable_actions_wrap">
					<div class="pntable_actions">
						<?php echo join(' | ', $actions); ?>
					</div>
				</div>
				<?php
			}
		} 
		
		function get_row_actions_filter($item){
			$actions = $this->get_row_actions($item);
			$actions = apply_filters('pntable_rowactions_'. $this->page, $actions, $item);
			return $actions;
		}		
		
		function get_row_actions($item){
			return array();
		}
		
		function search_where($where){ 
			$where = apply_filters('pntable_searchwhere_'. $this->page, $where);
			return $where;
		}
		
		function select_sql($select_sql){
			$select_sql = apply_filters('pntable_select_sql_'. $this->page, $select_sql);
			return $select_sql;
		}		
		
		function get_sortable_columns_filter(){
			$columns = $this->get_sortable_columns();
			$columns = apply_filters('pntable_sortable_columns_'. $this->page, $columns);
			return $columns;
		}		
		
		function get_sortable_columns(){
			return array();
		}		
		
		function db_orderby($default){
			$orderby = $this->orderby();
			$order_by = '';
			$orders = $this->get_sortable_columns_filter();
			if(is_array($orders) and count($orders) > 0){
				if($orderby and isset($orders[$orderby])){
					$order_by = $orders[$orderby][0];
				}	
			}	
			
			if(!$order_by){ $order_by = $default; }
			return $order_by;
		}
		
		function db_order($default){
			$order = $this->order();
			if(!$order){ $order = $default; }
			$order = strtoupper($order);
			if($order != 'ASC'){ $order = 'DESC'; }
			return $order;
		}
		
		function orderby(){
			$orders = $this->get_sortable_columns_filter();
			$order_data = '';
			if(is_array($orders) and count($orders) > 0){
				$get_orderby = trim(is_param_get('orderby'));
				if($get_orderby){
					if(isset($orders[$get_orderby])){
						$order_data = $get_orderby;
					} 					
				} else {
					foreach($orders as $order_key => $order){
						if(isset($order[1]) and $order[1]){
							$order_data = $order_key;
							break;
						}
					}
				}
			} 
			
			return $order_data;
		}
		
		function order(){
			$orders = $this->get_sortable_columns_filter();
			$order_data = '';
			if(is_array($orders) and count($orders) > 0){
				$order_data = trim(is_param_get('order'));
				if(!$order_data){				
					foreach($orders as $order){
						if(isset($order[1]) and $order[1]){
							$order_data = $order[1];
							break;
						}
					}
				}
			} 
			
			return $order_data;
		}
		
		function get_thwidth_filter(){
			$columns = $this->get_thwidth();
			$columns = apply_filters('pntable_thwidth_'. $this->page, $columns);
			return $columns;
		}			
		
		function get_thwidth(){
			return array();
		}		
		
		function get_columns_filter(){
			$columns = $this->get_columns();
			$columns = apply_filters('pntable_columns_'. $this->page, $columns);
			return $columns;
		}			
		
		function get_columns(){
			return array();
		}
		
		function get_search(){
			return array();
		}
		
		function get_submenu(){
			return array();
		}
		
		function get_bulk_actions(){
			return array();
		}	
		
		function prev_tablenav($which){
			
		}
		
		function extra_tablenav($which){
			
		}
		
		function prepare_items(){
			
		}
		
		function column_default($item, $column_key){
			return '';
		}
		
		function tr_class($tr_class, $item){
			return $tr_class;
		}
		
	}
}

if(!function_exists('premium_pntable_head_action')){
	add_action('premium_action_pntable_head_action', 'premium_pntable_head_action');
	function premium_pntable_head_action(){
		
		pn_only_caps(array('read'));
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);

		$lost = array('reply');
		
		if($user_id){
			$old_count_items = intval(is_param_post('old_count_items'));
			$count_items = intval(is_param_post('count_items'));
			if($old_count_items != $count_items){
				$lost[] = 'paged';
			}
			$hide_columns = is_param_post('hide_columns');
			if(!is_array($hide_columns)){ $hide_columns = array(); }
			$page = pn_strip_input(is_param_post('page'));
			if($page){
				$option_name = $page . '_Table_List_options';
				$class_name = $page . '_Table_List';
				$options = array(
					'count_items' => $count_items,
					'hide_columns' => $hide_columns,
				);
				if(class_exists($class_name)){
					update_user_meta($user_id, $option_name, $options);
				}
			}
		}
		
		$url = pn_admin_filter_data('', $lost);
		wp_redirect(get_safe_url($url));
	}
}