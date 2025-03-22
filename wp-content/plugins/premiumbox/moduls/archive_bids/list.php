<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_archive_bids', 'def_adminpage_title_pn_archive_bids');
	function def_adminpage_title_pn_archive_bids(){
		_e('Archived orders','pn');
	}

	add_action('pn_adminpage_content_pn_archive_bids','def_pn_adminpage_content_pn_archive_bids');
	function def_pn_adminpage_content_pn_archive_bids(){
		$form = new PremiumForm();
		$text = '<a href="'. get_request_link('archivebids', 'html') .'?all=1" target="_blank" rel="noreferrer noopener">'. __('Download operations archive','pn') .'</a>';
		$form->substrate($text);
	
		premium_table_list();
	}

	add_action('premium_action_pn_archive_bids','def_premium_action_pn_archive_bids');
	function def_premium_action_pn_archive_bids(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_archive'));
		
		$reply = '';
		$action = get_admin_action();
		
		if(isset($_POST['save'])){	
			do_action('pntable_archive_save');	
			$reply = '&reply=true';
		} else {	
			if(isset($_POST['id']) and is_array($_POST['id'])){
				do_action('pntable_archive_action', $action, $_POST['id']);
				$reply = '&reply=true';		
			} 
		}

		$url = pn_admin_filter_data('', 'reply, paged') . '&paged=' . is_param_post('paged') . $reply;
		wp_redirect($url);
		exit;			
	}
 
	class pn_archive_bids_Table_List extends PremiumTable {

		function __construct(){    
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}
		
		function column_default($item, $column_name){
			if($column_name == 'status'){
				return get_bid_status($item->status);
			} elseif($column_name == 'valut1'){	
				return pn_strip_input(ctv_ml($item->psys_give) .' '. ctv_ml($item->currency_code_give));
			} elseif($column_name == 'valut2'){	
				return pn_strip_input(ctv_ml($item->psys_get) .' '. ctv_ml($item->currency_code_get));
			} elseif($column_name == 'title'){
				return __('Order','pn') . ' ' . $item->bid_id;
			} else {
				return pn_strip_input(ctv_ml(is_isset($item,$column_name)));
			} 
				return '';
		}	
		
		function column_cb($item){
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" value="'. $item->id .'" />';              
		}	
		
		function get_row_actions($item){
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=pn_archive_bid&item_id='. $item->id . '&paged=' . is_param_get('paged')) .'">'. __('View','pn') .'</a>',
			);			
			return $actions;
		}		
		
		function get_columns(){
			$columns = array(       
				'title'     => __('ID','pn'),
				'archive_date'     => __('Date of archiving','pn'),
				'create_date'     => __('Date of creation','pn'),
				'valut1' => __('Currency Send','pn'),
				'valut2' => __('Currency Receive','pn'),
				'user_id' => __('User ID','pn'),
				'ref_id' => __('Referral ID','pn'),
				'account_give' => __('Account To send','pn'),
				'account_get' => __('Account To receive','pn'),
				'user_phone' => __('Mobile phone no.','pn'),
				'user_skype' => __('User skype','pn'),
				'user_email' => __('User e-mail','pn'),
				'user_passport' => __('User passport number','pn'),
				'status'  => __('Status','pn'),			
			);
			return $columns;
		}	
		
		function get_search(){
			$currencies = list_currency(__('All currency','pn'));
			$search = array();			
			$search[] = array(
				'view' => 'input',
				'title' => __('User ID','pn'),
				'default' => pn_strip_input(is_param_get('user_id')),
				'name' => 'user_id',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Referral ID','pn'),
				'default' => pn_strip_input(is_param_get('ref_id')),
				'name' => 'ref_id',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Order ID','pn'),
				'default' => pn_strip_input(is_param_get('bid_id')),
				'name' => 'bid_id',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Account Send','pn'),
				'default' => pn_strip_input(is_param_get('account_give')),
				'name' => 'account_give',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Account Receive','pn'),
				'default' => pn_strip_input(is_param_get('account_get')),
				'name' => 'account_get',
			);	
			$search[] = array(
				'view' => 'line',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('First name','pn'),
				'default' => pn_strip_input(is_param_get('first_name')),
				'name' => 'first_name',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Last name','pn'),
				'default' => pn_strip_input(is_param_get('last_name')),
				'name' => 'last_name',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Second name','pn'),
				'default' => pn_strip_input(is_param_get('second_name')),
				'name' => 'second_name',
			);	
			$search[] = array(
				'view' => 'line',
			);			
			$search[] = array(
				'view' => 'input',
				'title' => __('Mobile phone no.','pn'),
				'default' => pn_strip_input(is_param_get('user_phone')),
				'name' => 'user_phone',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Skype','pn'),
				'default' => pn_strip_input(is_param_get('user_skype')),
				'name' => 'user_skype',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Telegram','pn'),
				'default' => pn_strip_input(is_param_get('user_telegram')),
				'name' => 'user_telegram',
			);			
			$search[] = array(
				'view' => 'input',
				'title' => __('E-mail','pn'),
				'default' => pn_strip_input(is_param_get('user_email')),
				'name' => 'user_email',
			);
			$search[] = array(
				'view' => 'input',
				'title' => __('Passport number','pn'),
				'default' => pn_strip_input(is_param_get('user_passport')),
				'name' => 'user_passport',
			);
			$search[] = array(
				'view' => 'line',
			);
			$search[] = array(
				'view' => 'date',
				'title' => __('Start date','pn'),
				'default' => is_pn_date(is_param_get('date1')),
				'name' => 'date1',
			);
			$search[] = array(
				'view' => 'date',
				'title' => __('End date','pn'),
				'default' => is_pn_date(is_param_get('date2')),
				'name' => 'date2',
			);
			$search[] = array(
				'view' => 'select',
				'title' => __('Currency Send','pn'),
				'default' => pn_strip_input(is_param_get('curr1')),
				'options' => $currencies,
				'name' => 'curr1',
			);	
			$search[] = array(
				'view' => 'select',
				'title' => __('Currency Receive','pn'),
				'default' => pn_strip_input(is_param_get('curr2')),
				'options' => $currencies,
				'name' => 'curr2',
			);			
			$search[] = array(
				'view' => 'line',
			);
			$search[] = array(
				'view' => 'date',
				'title' => __('Start date (archiving)','pn'),
				'default' => is_pn_date(is_param_get('adate1')),
				'name' => 'adate1',
			);
			$search[] = array(
				'view' => 'date',
				'title' => __('End date (archiving)','pn'),
				'default' => is_pn_date(is_param_get('adate2')),
				'name' => 'adate2',
			);	
			return $search;
		}
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$orderby = $this->db_orderby('id');
			$order = $this->db_order('DESC');
			
			$where = '';

			$user_id = intval(is_param_get('user_id'));
			if($user_id){
				$where .= " AND user_id = '$user_id'";
			}
			$ref_id = intval(is_param_get('ref_id'));
			if($ref_id){
				$where .= " AND ref_id = '$ref_id'";
			}		
			$bid_id = intval(is_param_get('bid_id'));
			if($bid_id){
				$where .= " AND bid_id = '$bid_id'";
			}		
			$account1 = pn_sfilter(pn_strip_input(is_param_get('account_give')));
			if($account1){
				$where .= " AND account_give LIKE '%$account1%'";
			}		
			$account2 = pn_sfilter(pn_strip_input(is_param_get('account_get')));
			if($account2){
				$where .= " AND account_get LIKE '%$account2%'";
			}
			$first_name = pn_sfilter(pn_strip_input(is_param_get('first_name')));
			if($first_name){
				$where .= " AND first_name LIKE '%$first_name%'";
			}
			$last_name = pn_sfilter(pn_strip_input(is_param_get('last_name')));
			if($last_name){
				$where .= " AND last_name LIKE '%$last_name%'";
			}
			$second_name = pn_sfilter(pn_strip_input(is_param_get('second_name')));
			if($second_name){
				$where .= " AND second_name LIKE '%$second_name%'";
			}
			$user_phone = pn_sfilter(pn_strip_input(is_param_get('user_phone')));
			if($user_phone){
				$where .= " AND user_phone LIKE '%$user_phone%'";
			}
			$user_skype = pn_sfilter(pn_strip_input(is_param_get('user_skype')));
			if($user_skype){
				$where .= " AND user_skype LIKE '%$user_skype%'";
			}
			$user_telegram = pn_sfilter(pn_strip_input(is_param_get('user_telegram')));
			if($user_telegram){
				$where .= " AND user_telegram LIKE '%$user_telegram%'";
			}			
			$user_email = pn_sfilter(pn_strip_input(is_param_get('user_email')));
			if($user_email){
				$where .= " AND user_email LIKE '%$user_email%'";
			}
			$user_passport = pn_sfilter(pn_strip_input(is_param_get('user_passport')));
			if($user_passport){
				$where .= " AND user_passport LIKE '%$user_passport%'";
			}
			$curr1 = pn_sfilter(intval(is_param_get('curr1')));
			if($curr1){
				$where .= " AND currency_id_give = '$curr1'";
			}
			$curr2 = pn_sfilter(intval(is_param_get('curr2')));
			if($curr2){
				$where .= " AND currency_id_get = '$curr2'";
			}	
			$date1 = is_pn_date(is_param_get('date1'));
			if($date1){
				$date = get_pn_date($date1, 'Y-m-d');
				$where .= " AND create_date >= '$date'";
			}	
			$date2 = is_pn_date(is_param_get('date2'));
			if($date2){
				$date = get_pn_date($date2, 'Y-m-d');
				$where .= " AND create_date < '$date'";
			}
			$adate1 = is_pn_date(is_param_get('adate1'));
			if($adate1){
				$date = get_pn_date($adate1, 'Y-m-d');
				$where .= " AND archive_date >= '$date'";
			}	
			$adate2 = is_pn_date(is_param_get('adate2'));
			if($adate2){
				$date = get_pn_date($adate2, 'Y-m-d');
				$where .= " AND archive_date < '$date'";
			}		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if($this->navi == 1){
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."archive_exchange_bids WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."archive_exchange_bids WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}	  
	} 
}	