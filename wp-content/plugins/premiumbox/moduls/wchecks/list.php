<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	
	add_action('pn_adminpage_title_pn_wchecks', 'def_adminpage_title_pn_wchecks');
	function def_adminpage_title_pn_wchecks($page){
		_e('Accounts verification checker','pn');
	} 

	add_action('pn_adminpage_content_pn_wchecks','def_pn_admin_content_pn_wchecks');
	function def_pn_admin_content_pn_wchecks(){
		premium_table_list();
		?>
<script type="text/javascript">	
jQuery(function($){
	$('select.merchant_change').on('change', function(){ 
		var id = $(this).attr('data-id');
		var wid = $(this).val();
		var thet = $(this);
		thet.prop('disabled',true);
			
		$('#premium_ajax').show();
		var param='id=' + id + '&wid=' + wid;
			
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('wchecks_settings_save','post'); ?>",
			data: param,
			error: function(res,res2,res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{
				$('#premium_ajax').hide();	
				thet.prop('disabled',false);
			}
		});
		
		return false;
	});	
});
</script>		
		<?php
	} 

	add_action('premium_action_wchecks_settings_save', 'pn_premium_action_wchecks_settings_save');
	function pn_premium_action_wchecks_settings_save(){
	global $wpdb;

		only_post();
		
		if(current_user_can('administrator') or current_user_can('pn_merchants')){
			$id = is_extension_name(is_param_post('id'));
			$wid = intval(is_param_post('wid'));
			
			$items = get_option('wchecks');
			if(!is_array($items)){ $items = array(); }
			
			$items[$id] = $wid;
			
			$items = apply_filters('wcheck_settings_save', $items, $id, $wid);
			
			update_option('wchecks', $items);
		}  		
	}
		
	add_action('premium_action_pn_wchecks','def_premium_action_pn_wchecks');
	function def_premium_action_pn_wchecks(){
	global $wpdb, $premiumbox;	

		only_post();
		pn_only_caps(array('administrator','pn_merchants'));

		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['id']) and is_array($_POST['id'])){
			if($action == 'active'){
						
				$extended = get_option('pn_extended');
				if(!is_array($extended)){ $extended = array(); }
						
				foreach($_POST['id'] as $id){
					$id = is_extension_name($id);
					if($id){
						if(!isset($extended['wchecks'][$id])){
							$extended['wchecks'][$id] = $id;
							
							include_extanded($premiumbox, 'wchecks', $id);
							
							do_action('pn_wchecks_active_'.$id);
							do_action('pn_wchecks_active', $id);
						}	
					}	
				}
				update_option('pn_extended', $extended);
						
				$reply = '&reply=true';	
			}

			if($action == 'deactive'){
						
				$extended = get_option('pn_extended');
				if(!is_array($extended)){ $extended = array(); }
						
				$items = get_option('wchecks');		
				foreach($_POST['id'] as $id){
					$id = is_extension_name($id);
					if($id){
						if(isset($extended['wchecks'][$id])){
							unset($extended['wchecks'][$id]);
							
							do_action('pn_wchecks_deactive_'.$id);
							do_action('pn_wchecks_deactive', $id);
								
							if(isset($items[$id])){
								unset($items[$id]);
							}
						}		
					}	
				}
				
				update_option('wchecks', $items);
				update_option('pn_extended', $extended);
						
				$reply = '&reply=true';	
			}				

		} 
				
			$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
			wp_redirect($url);
			exit;			
	} 

	add_action('premium_action_pn_wchecks_activate','def_premium_action_pn_wchecks_activate');
	function def_premium_action_pn_wchecks_activate(){
	global $wpdb, $premiumbox;	

		pn_only_caps(array('administrator','pn_merchants'));
		
		$id = is_extension_name(is_param_get('key'));	
		if($id){
			
			$extended = get_option('pn_extended');
			if(!is_array($extended)){ $extended = array(); }
				
			if(!isset($extended['wchecks'][$id])){
				$extended['wchecks'][$id] = $id;
					
				include_extanded($premiumbox, 'wchecks', $id);
				
				do_action('pn_wchecks_active_'.$id);
				do_action('pn_wchecks_active',$id);
			}	

			update_option('pn_extended', $extended);
			
		}
		
		$url = pn_admin_filter_data(is_param_get('_wp_http_referer'), 'reply') . '&reply=true';
		wp_redirect($url);
		exit;		
	}

	add_action('premium_action_pn_wchecks_deactivate','def_premium_action_pn_wchecks_deactivate');
	function def_premium_action_pn_wchecks_deactivate(){
	global $wpdb;

		pn_only_caps(array('administrator','pn_merchants'));
		
		$id = is_extension_name(is_param_get('key'));	
		if($id){
			
			$extended = get_option('pn_extended');
			if(!is_array($extended)){ $extended = array(); }
				
			if(isset($extended['wchecks'][$id])){
				unset($extended['wchecks'][$id]);
				
				do_action('pn_merchants_deactive_'.$id);
				do_action('pn_merchants_deactive', $id);
					
				$items = get_option('wchecks');
				if(isset($items[$id])){
					unset($items[$id]);
					update_option('wchecks', $items);
				}				
			}	

			update_option('pn_extended', $extended);
			
		}		
		
		$url = pn_admin_filter_data(is_param_get('_wp_http_referer'), 'reply') . '&reply=true';
		wp_redirect($url);
		exit;	
	}	

	class pn_wchecks_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
			$this->count_items = 50;
		}
		
		function get_thwidth(){
			$array = array();
			$array['title'] = '200px';
			return $array;
		}		
		
		function column_default($item, $column_name){
			
			if($column_name == 'descr'){
				$settings_html = '';
				if($item['status'] == 'active'){
					$settings_html .=' | <a href="'. admin_url('admin.php?page=pn_wchecks_settings&m_id='.$item['name']) .'" target="_blank">'. __('Settings','pn') .'</a> ';
				}
				
				$html = '
					<div>'. pn_strip_input(ctv_ml($item['description'])) .'</div>
					<div class="modul_vers">'. __('Version','pn') .': '. pn_strip_input($item['version']) . $settings_html . apply_filters('wchecks_settingtext_'. $item['name'],'') .'</div>
				';
				
				return $html;
			} elseif($column_name == 'title'){	
				return '<strong>'. pn_strip_input(ctv_ml($item['title'])) .'</strong>';	
			} elseif($column_name == 'name'){
				$name = is_extension_name($item['name']);
				$name = str_replace('_theme','', $name);
				return $name;
			} elseif($column_name == 'place'){	
				$place = __('Plugin','pn');
				if(is_isset($item, 'place') == 'theme'){
					$place = __('Theme','pn');
				}
				return '<a href="'. admin_url('admin.php?page=pn_wchecks&place='. is_isset($item, 'place')) .'&filter='. intval(is_param_get('filter')) .'">'. $place . '</a>';			
			} elseif($column_name == 'status'){
				if($item['status'] == 'active'){
					$default = is_enable_wchecks($item['name']);
				
					$html = '
					<select name="" data-id="'. $item['name'] .'" class="merchant_change" autocomplete="off">	
						<option value="0" '. selected($default,0,false) .'>'. __('Disable','pn') .'</option>
						<option value="1" '. selected($default,1,false) .'>'. __('Enable','pn') .'</option>
					</select>
					';
					
					return $html;
				}
			}
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item['name'] .'" />';              
		}

		function get_row_actions($item){
			$actions = array();
			if($item['status'] == 'active'){
				$actions['deactive']  = '<a href="'. pn_link('pn_wchecks_deactivate', 'post') . '&key=' . $item['name'] . '&_wp_http_referer=' . urlencode($_SERVER['REQUEST_URI']) .'">'. __('Deactivate','pn') .'</a>';
			} else {
				$actions['active']  = '<a href="'. pn_link('pn_wchecks_activate', 'post') . '&key=' . $item['name'] . '&_wp_http_referer=' . urlencode($_SERVER['REQUEST_URI']) .'">'. __('Activate','pn') .'</a>';
			}
			return $actions;
		}			
		
		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __('Title','pn'),
				'name' => __('Folder name','pn'),
				'descr'     => __('Checker description','pn'),
				'place'     => __('Location','pn'),
				'status'     => __('Status','pn'),
			);
			return $columns;
		}

		function tr_class($tr_class, $item) {
			if($item['new'] == 1){
				$tr_class[] = 'tr_green';
			}			
			if($item['status'] != 'active'){
				$tr_class[] = 'tr_red';
			}
				return $tr_class;
		}				

		function get_bulk_actions() {
			$actions = array(
				'active'    => __('Enable','pn'),
				'deactive'    => __('Disable','pn'),
			);
			return $actions;
		}
		
		function get_search(){
			$search = array();
			$placed = array(
				'0' => '--'. __('All locations','pn') .'--',
				'plugin' => __('Plugin','pn'),
				'theme' => __('Theme','pn'),
			);
			$search[] = array(
				'view' => 'select',
				'options' => $placed,
				'title' => __('Module locations','pn'),
				'default' => is_extension_name(is_param_get('place')),
				'name' => 'place',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Title','pn'),
				'default' => pn_strip_input(is_param_get('title')),
				'name' => 'title',
			);			
			return $search;
		}	

		function get_submenu(){			
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('active checkers','pn'),
					'2' => __('inactive checkers','pn'),
					'3' => __('recently active items','pn'),
					'4' => __('new items','pn'),
				),
				'title' => '',
			);		
			return $options;		
		}		
		
		function prepare_items(){
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$pn_extended_last = get_option('pn_extended_last');
			$extended_last = is_isset($pn_extended_last, 'wchecks');

			$now_time = current_time('timestamp');
			$now_time_check = $now_time - (24*60*60);
			
			$list = pn_list_extended('wchecks');
			
			$items = array();
			$filter = intval(is_param_get('filter'));
			$place = is_extension_name(is_param_get('place'));
			$title = mb_strtolower(pn_strip_input(is_param_get('title')));
			
			foreach($list as $list_key => $list_value){
				$module_status = $list_value['status'];
				$module_place = $list_value['place'];
				$module_new = intval($list_value['new']);
				
				$time_deactive = extended_time_deactive($extended_last, $list_key, $list_value['old_names']);
				
				$show = 0;
				
				if($filter == 1){
					if($module_status == 'active'){
						$show = 1;
					}
				} elseif($filter == 2){
					if($module_status == 'deactive'){
						$show = 1;
					}
				} elseif($filter == 3){
					if($module_status == 'deactive' and $time_deactive > $now_time_check){
						$show = 1;
					}
				} elseif($filter == 4){
					if($module_new == 1){
						$show = 1;
					}					
				} else {
					$show = 1;
				}
				
				if(strlen($title) > 0){
					$module_title = mb_strtolower(ctv_ml(is_isset($list_value,'title')));
					if(!strstr($module_title, $title)){
						$show = 0;
					}
				}				
				
				if($place){
					if($module_place != $place){
						$show = 0;
					}
				}			
				
				if($show == 1){
					$items[] = $list_value;
				}
			}
			
			if($this->navi == 1){
				$this->total_items = count($items);
			}
			$this->items = array_slice($items, $offset, $per_page);		
		}
	}
}