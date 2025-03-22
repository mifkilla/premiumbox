<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){ 
	add_action('pn_adminpage_title_pn_new_parser', 'pn_admin_title_pn_new_parser');
	function pn_admin_title_pn_new_parser(){
		_e('Source rates','pn');
	}

	add_action('pn_adminpage_content_pn_new_parser','def_pn_admin_content_pn_new_parser');
	function def_pn_admin_content_pn_new_parser(){
		premium_table_list();		
	} 

	add_action('premium_action_pn_new_parser','def_premium_action_pn_new_parser');
	function def_premium_action_pn_new_parser(){
	global $wpdb, $premiumbox;	

		only_post();
		pn_only_caps(array('administrator','pn_directions','pn_parser'));	

		$reply = '';
		$action = get_admin_action();
			
		if(isset($_POST['save'])){
			
			do_action('pntable_parsercourses_save');
			$reply = '&reply=true';
			
		} elseif(isset($_POST['delete_all'])){
			
			delete_array_option($premiumbox, 'pn_parser_pairs');
			do_action('pntable_parsercourses_deleteall');
			$reply = '&reply=true';
			
		} else {		
			if(isset($_POST['id']) and is_array($_POST['id'])){						
				do_action('pntable_parsercourses_action', $action, $_POST['id']);
				$reply = '&reply=true';			
			} 
		}
		
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	}

	class pn_new_parser_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
			$this->count_items = 50;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'title'){
				global $birgs_list;
				return is_isset($birgs_list, is_isset($item, 'birg')).' ('.  is_isset($item, 'give') .' => '. is_isset($item, 'get') .')';		
			} elseif($column_name == 'place'){
				return is_isset($item, 'title');
			} elseif($column_name == 'rate'){
				return '1 '.  is_isset($item, 'give') .' => '. is_isset($item, 'course') .' '. is_isset($item, 'get');
			} elseif($column_name == 'date'){
				$time = intval(is_isset($item, 'up'));
				if($time){
					return date('d.m.Y H:i:s', $time);
				}
			} elseif($column_name == 'code'){
				return '<input type="text" class="premium_input clpb_item" style="width: 100%;" name="" data-clipboard-text="['. is_isset($item, 'code') .']" value="['. is_isset($item, 'code') .']" />';
			}		
			return '';
		}	

		function get_columns(){
			$columns = array(
				'title'     => __('Title','pn'),
				'place' => __('Type','pn'),
				'code' => __('Code','pn'),
				'rate' => __('Rate','pn'),
				'date' => __('Parsing date','pn'),
			);
			return $columns;
		}

		function get_search(){
		global $birgs_list;
			$search = array();
			
			$lists = array();
			$lists[''] = '--' . __('All','pn') . '--';
			$birgs = apply_filters('new_parser_links', array());
			foreach($birgs as $birg){
				$lists[is_isset($birg,'birg_key')] = is_isset($birg,'title');
			}
			$birgs_list = $lists;
			
			$search[] = array(
				'view' => 'select',
				'title' => __('Source','pn'),
				'default' => pn_strip_input(is_param_get('birg')),
				'options' => $lists,
				'name' => 'birg',
			);			
			
			$search[] = array(
				'view' => 'input',
				'title' => __('Currency Send','pn'),
				'default' => pn_strip_input(is_param_get('currency_give')),
				'name' => 'currency_give',
			);	
			$search[] = array(
				'view' => 'input',
				'title' => __('Currency Receive','pn'),
				'default' => pn_strip_input(is_param_get('currency_get')),
				'name' => 'currency_get',
			);		
			
			return $search;
		}		

 		function prepare_items() {
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$parser_pairs = get_parser_pairs(1);
			
			$s_birg = pn_strip_input(is_param_get('birg'));
			$s_give = strtoupper(pn_strip_input(is_param_get('currency_give')));
			$s_get = strtoupper(pn_strip_input(is_param_get('currency_get')));
			
			$items = array();
			foreach($parser_pairs as $pi_key => $pi_value){
				$birg = trim(is_isset($pi_value, 'birg'));
				$give = strtoupper(trim(is_isset($pi_value, 'give')));
				$get = strtoupper(trim(is_isset($pi_value, 'get')));
				
				$return = 1;
				
				if($s_birg and $s_birg != $birg){
					$return = 0;
				}
				if($s_give and $s_give != $give){
					$return = 0;
				}
				if($s_get and $s_get != $get){
					$return = 0;
				}		
				
				if($return == 1){
					$items[$pi_key] = $pi_value;
					$items[$pi_key]['code'] = $pi_key;
				}
			}
			
			$this->items = array_slice($items, $offset, $per_page);
			if($this->navi == 1){
				$this->total_items = count($items); 
			}
		}

		function extra_tablenav( $which ) {		  	
		?>
			<input type="submit" name="delete_all" style="background: #f4eaee;" value="<?php _e('Delete rates','pn'); ?>">	
		<?php 
		}		
	} 
}	