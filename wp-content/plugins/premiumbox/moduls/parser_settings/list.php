<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_parser_pairs', 'def_adminpage_title_pn_parser_pairs');
	function def_adminpage_title_pn_parser_pairs(){
		_e('Rates','pn');
	} 

	add_action('pn_adminpage_content_pn_parser_pairs','def_pn_adminpage_content_pn_parser_pairs');
	function def_pn_adminpage_content_pn_parser_pairs(){
		$form = new PremiumForm();
		?>
		<div style="margin: 0 0 10px 0;">
			<?php 
			$text = sprintf(__('For creating an exchange rate you can use the following mathematical operations:<br><br> 
			* multiplication<br> 
			/ division<br> 
			- subtraction<br> 
			+ addition<br><br> 
			An example of a formula where two exchange rates are multiplied: [bitfinex_btcusd_last_price] * [cbr_usdrub]<br> 
			For more detailed instructions, follow the <a href="%s" target="_blank" rel="noreferrer noopener">link</a>.','pn'), 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/parseryi-kursov-valyut/');
			$form->help(__('Example of formulas for parser','pn'), $text);
			?>
		</div>
		<?php
		premium_table_list();
	}

	add_action('premium_action_pn_parser_pairs','def_premium_action_pn_parser_pairs');
	function def_premium_action_pn_parser_pairs(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_directions','pn_parser'));

		$reply = '';
		$action = get_admin_action();
				
		if(isset($_POST['save'])){
					
			if(isset($_POST['pair_give']) and is_array($_POST['pair_give']) and isset($_POST['pair_get']) and is_array($_POST['pair_get'])){
				foreach($_POST['pair_give'] as $id => $pair_give){		
					$id = intval($id);
					$pair_give = pn_parser_actions($pair_give);
					$pair_get = pn_parser_actions($_POST['pair_get'][$id]);	
							
					$array = array();	
					$array['pair_give'] = $pair_give;
					$array['pair_get'] = $pair_get;
					$wpdb->update($wpdb->prefix."parser_pairs", $array, array('id'=>$id));
				}					
			}
			
			do_action('pntable_parser_pairs_save');
			$reply = '&reply=true';

		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){					
				if($action == 'delete'){		
					foreach($_POST['id'] as $id){
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."parser_pairs WHERE id='$id'");
						if(isset($item->id)){
							$res = apply_filters('item_parser_pairs_delete_before', pn_ind(), $id, $item);
							if($res['ind'] == 1){
								$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."parser_pairs WHERE id = '$id'");
								do_action('item_parser_pairs_delete', $id, $item, $result);
								if($result){
									$wpdb->update($wpdb->prefix."directions", array('new_parser'=>'0'), array('new_parser'=>$id));
									$wpdb->update($wpdb->prefix."currency_codes", array('new_parser'=>'0'), array('new_parser'=>$id));
								}
							}	
						}		
					}
				}
				
				do_action('pntable_parser_pairs_action', $action, $_POST['id']);
				$reply = '&reply=true';					
			} 
					
		}
				
		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	}  

	class pn_parser_pairs_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 1;
		}

		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}
		
		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_parser_pairs&item_id='. $item->id) .'">'. __('Edit','pn') .'</a>',
			);			
			return $actions;
		}		
		
		function column_default($item, $column_name){
			
			if($column_name == 'source'){
				return pn_strip_input($item->title_birg); 
			} elseif($column_name == 'calc1'){		
				return '<input type="text" style="width: 100%;" name="pair_give['. $item->id .']" value="'. pn_strip_input($item->pair_give) .'" />';	
			} elseif($column_name == 'calc2'){	
				return '<input type="text" style="width: 100%;" name="pair_get['. $item->id .']" value="'. pn_strip_input($item->pair_get) .'" />';
			} elseif($column_name == 'rate1'){
				return get_parser_course($item->pair_give);
			} elseif($column_name == 'rate2'){
				return get_parser_course($item->pair_get);
			} elseif($column_name == 'title'){	
				return pn_strip_input($item->title_pair_give).'-'.pn_strip_input($item->title_pair_get);
			} elseif($column_name == 'copy'){	
				return '<a href="'. pn_link('copy_parser_pairs','post') .'&item_id='. $item->id .'" class="button">'. __('Copy','pn') .'</a>';			
			} 
			
				return '';
		}		

		function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __('Rate name','pn'),
				'source'     => __('Source name','pn'),
				'calc1' => __('Rate formula for Send','pn'),
				'calc2' => __('Rate formula for Receive','pn'),
				'rate1' => __('Rate for Send','pn'),
				'rate2' => __('Rate for Receive','pn'),	
				'copy' => __('Copy','pn'),
			);
			return $columns;
		}	
		
		function get_bulk_actions() {
			$actions = array(
				'delete'    => __('Delete','pn'),
			);
			return $actions;
		}
		
		function get_search(){
			global $wpdb;
			$options = array();
			$options[0] = '--'. __('All','pn') .'--';
			
			$items = $wpdb->get_results("SELECT DISTINCT(title_birg) FROM ". $wpdb->prefix ."parser_pairs");  		
			foreach($items as $item){
				$options[$item->title_birg] = pn_strip_input($item->title_birg);
			}
			$search = array();
			$search[] = array(
				'view' => 'select',
				'title' => __('Source name','pn'),
				'default' => is_param_get('title_birg'),
				'options' => $options,
				'name' => 'title_birg',
			);		
			return $search;
		}	
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('menu_order');
			$order = $this->db_order('ASC');
			
			$where = '';
			
			$title_birg = pn_sfilter(trim(is_param_get('title_birg')));
			if($title_birg){
				$where .= " AND title_birg = '$title_birg'";
			}
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."parser_pairs WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."parser_pairs WHERE id > 0 $where ORDER BY menu_order ASC LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav( $which ) {		  	
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_parser_pairs');?>"><?php _e('Add new','pn'); ?></a>		
		<?php 
		}	  
	}
}