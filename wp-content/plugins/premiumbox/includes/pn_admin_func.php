<?php 
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('get_list_user_menu')){
	function get_list_user_menu(){
		$account_list_pages = array(
			'account' => array(
				'title' => '',
				'url' => '',
				'type' => 'page',
				'class' => '',
				'id' => '',
			),
			'security' => array(
				'title' => '',
				'url' => '',
				'type' => 'page',
				'class' => '',
				'id' => '',			
			),										
		);
		$account_list_pages = apply_filters('account_list_pages',$account_list_pages);
		$pages = get_option('the_pages');
		
		$list = array();
		if(is_array($account_list_pages)){
			foreach($account_list_pages as $key => $data){
				$type = trim(is_isset($data,'type'));
				$url = trim(is_isset($data,'url'));
				$title = trim(is_isset($data,'title'));
				$target = intval(is_isset($data,'target'));
				$class = trim(is_isset($data,'class'));
				$id = trim(is_isset($data,'id'));
				
				if($type == 'page'){
					if(isset($pages[$key])){
						$page_url = get_permalink($pages[$key]);
						$current = '';
						if(is_page($pages[$key])){
							$current = 'current';
						}
						$list[] = array(
							'url' => $page_url,
							'title' => get_the_title($pages[$key]),
							'target' => '',
							'class' => is_isset($data,'class'),
							'id' => is_isset($data,'id'),
							'current' => $current,
						);
					}
				} elseif($type == 'target_link'){
					$list[] = array(
						'url' => $url,
						'title' => $title,
						'target' => 1,
						'class' => $class,
						'id' => $id,	
						'current' => is_place_url($url),
					);				
				} else {
					$list[] = array(
						'url' => $url,
						'title' => $title,
						'target' => $target,
						'class' => $class,
						'id' => $id,	
						'current' => is_place_url($url),
					);
				}
			}
		}
		
		return $list;
	}
}

add_filter('premium_js_login', 'def_premium_js_login');
function def_premium_js_login($ind){
	if($ind == 1){
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		if($user_id > 0){
			return 0;
		}
	}
	return $ind;
}

add_filter('placed_form', 'def_placed_form', 0);
function def_placed_form(){
	$placed = array(
		'exchangeform' => __('Exchange type','pn'),
	);	
	return $placed;
}

add_filter('lists_pn_stats_widget','premium_lists_pn_stats_widget', 10);
function premium_lists_pn_stats_widget($lists){
	
	$array = array(
		'count_exchanges' => __('Number of exchanges today','pn'),
		'amount_exchanges' => __('Amount of exchanges today','pn'),
		'total_reserv' => __('Total amount of reserves','pn'),
	);
	$lists = pn_array_insert($lists, '', $array);
	
	return $lists;
}

add_filter('show_pn_stats_widget', 'premium_show_pn_stats_widget', 10, 3);
function premium_show_pn_stats_widget($temp, $list_k, $list_v){

	$time = current_time('timestamp');
	$date = date('Y-m-d 00:00:00', $time);

	if($list_k == 'count_exchanges'){
		$temp .= '<div class="widget_stats_line"><span>'. $list_v .':</span> '. is_out_sum(get_count_exchanges($date), 12, 'all') .'</div>';
	} elseif($list_k == 'amount_exchanges'){
		$temp .= '<div class="widget_stats_line"><span>'. $list_v .':</span> '. is_out_sum(get_sum_exchanges($date, cur_type()), 12, 'all') .' '. cur_type() .'</div>';
	} elseif($list_k == 'total_reserv'){	
		$temp .= '<div class="widget_stats_line"><span>'. $list_v .':</span> '. is_out_sum(get_general_reserv(cur_type()), 12, 'reserv') .' '. cur_type() .'</div>';	
	}						
	
	return $temp;
}

add_filter('set_exchange_cat_filters', 'def_set_exchange_cat_filters', 0);
function def_set_exchange_cat_filters(){
	$cats = array(
		'home' => __('Homepage exchange table','pn'),
		'exchange' => __('Exchange type','pn'),
	);
	return $cats;
}

add_action('set_exchange_filters', 'dirstatus_set_exchange_filters', 0);
function dirstatus_set_exchange_filters($lists){
	$lists[] = array(
		'title' => __('Frozen exchange direction','pn'),
		'name' => 'holdstatus',
	);
	return $lists;
}

add_filter('bid_status_list','def_bid_status_list',0);
function def_bid_status_list($status){
	
	$status = array(
		'coldnew' => __('pending order','pn'),
		'new' => __('new order','pn'),
		'cancel' => __('cancelled order by user','pn'),
		'delete' => __('deleted order','pn'),
		'techpay' => __('when user entered payment section','pn'),
		'payed' => __('user marked order as paid','pn'),
		'coldpay' => __('waiting for merchant confirmation','pn'),
		'realpay' => __('paid order','pn'),
		'verify' => __('order is on checking','pn'),
		'error' => __('error order','pn'),
		'payouterror' => __('automatic payout error','pn'), 
		'scrpayerror' => __('automatic payout error (payment system API)','pn'),
		'coldsuccess' => __('waiting for automatic payment module confirmation','pn'),
		'success' => __('successful order','pn'),
	);
	
	return $status;
}

add_filter('list_directions_temp','def_list_directions_temp',0);
function def_list_directions_temp($list_directions_temp){
	
	$list_directions_temp = array(
		'description_txt' => __('Exchange description','pn'),
		'timeline_txt' => __('Deadline','pn'),
		'window_txt' => __('Popup text before order creation','pn'),
		'status_auto' => sprintf(__('Status of order is "%s"', 'pn'), __('uncreated order','pn')),
	);
	$bid_status_list = apply_filters('bid_status_list',array());
	foreach($bid_status_list as $key => $title){
		$list_directions_temp['status_'.$key] = sprintf(__('Status of order is "%s"', 'pn'), $title);
	}	
							
	return $list_directions_temp;
}

function get_bid_status($status){
	$bid_status_list = apply_filters('bid_status_list',array());
	$status_title = is_isset($bid_status_list, $status);
	if(!$status_title){ $status_title = __('Not known','pn'); }
	return $status_title;
}

function get_payuot_status($status){
	$statused = array(
		'0' => __('Waiting order','pn'),
		'1' => __('Completed order','pn'),
		'2' => __('Cancelled order','pn'),
		'3' => __('Cancelled order by user','pn'),
	);	
	return is_isset($statused, $status);
}

function pn_exchanges_output($place=''){
global $premiumbox;	
	$show_data = array(
		'mode' => 1,
		'text' => '',
	);
	if($premiumbox->get_option('up_mode') == 1){
		$show_data = array(
			'mode' => 0,
			'text' => __('Maintenance','pn'),
		);		
	}
	$show_data = apply_filters('pn_exchanges_output', $show_data, $place);
	return $show_data;
}

function get_exchange_title(){
global $direction_data;	
	if(isset($direction_data->item_give) and isset($direction_data->item_get)){
		$item_title1 = pn_strip_input($direction_data->item_give);
		$item_title2 = pn_strip_input($direction_data->item_get);	
		$title = sprintf(__('Exchange %1$s to %2$s','pn'), $item_title1, $item_title2);	
		return apply_filters('get_exchange_title', $title, $direction_data->direction_id, $item_title1, $item_title2, $direction_data);
	} else {
		return __('Error 404','pn');
	}
}

function get_exchangestep_title(){
global $wpdb, $bids_data;	
	if(isset($bids_data->id)){
		if($bids_data->status == 'auto'){
			$item_title1 = pn_strip_input(ctv_ml($bids_data->psys_give)).' '.pn_strip_input($bids_data->currency_code_give);
			$item_title2 = pn_strip_input(ctv_ml($bids_data->psys_get)).' '.pn_strip_input($bids_data->currency_code_get);
		    $title = sprintf(__('Exchange %1$s to %2$s','pn'), $item_title1, $item_title2);
			return apply_filters('get_exchangestep_auto_title', $title, $bids_data->direction_id, $item_title1, $item_title2);
		} else {
			$title = __('Order ID','pn') . ' '. $bids_data->id;
			return apply_filters('get_exchangestep_title', $title, $bids_data->id);
		}
	} else {
		return __('Error 404','pn');
	}	
}

function get_comis_text($com_ps, $dop_com, $psys, $curr_code, $vid, $gt){
	$comis_text = '';
	
	if($com_ps > 0 or $dop_com > 0){
		$comis_text = __('Including','pn').' ';
	}		

	if($com_ps > 0 and $dop_com > 0){
		$comis_text .= __('add. service fee','pn');
		$comis_text .= ' (<span class="dop_com">'. $dop_com .'</span> <span class="vtype curr_code">'. $curr_code .'</span>)';
		$comis_text .= __(' and','pn');
		$comis_text .= ' ';		
		$comis_text .= __('payment system fees','pn');
		$comis_text .= ' <span class="psys">'. $psys . '</span> (<span class="com_ps">'. $com_ps .'</span> <span class="vtype curr_code">'. $curr_code .'</span>) ';
	} elseif($com_ps > 0){
		$comis_text .= __('payment system fees','pn');
		$comis_text .= ' <span class="psys">'. $psys . '</span> (<span class="com_ps">'. $com_ps .'</span> <span class="vtype curr_code">'. $curr_code .'</span>) ';	
	} elseif($dop_com > 0){
		$comis_text .= __('add. service fee','pn');
		$comis_text .= ' (<span class="dop_com">'. $dop_com .'</span> <span class="vtype curr_code">'. $curr_code .'</span>)';
	}	
	
	if($gt == 1){
		if($com_ps > 0 or $dop_com > 0){
			$comis_text .= ', ';
			if($vid == 1){
				$comis_text .= __('you send','pn');
			} else {
				$comis_text .= __('you receive','pn');
			}
		}
	}
	
	return pn_strip_input($comis_text);
}