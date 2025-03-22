<?php
if( !defined( 'ABSPATH')){ exit(); }

add_filter('autopayment_filter', 'autopayment_filter_touap', 100, 6);  
function autopayment_filter_touap($au_filter, $m_id, $item, $place, $direction_data, $paymerch_data){ 
global $wpdb;

	if(isset($item->id) and count($au_filter['error']) == 0 and $place == 'site'){ 
		$user_id = intval($item->user_id);
		$timeout = is_sum(is_isset($direction_data,'m_out_timeout'));
		$timeout_user = is_sum(is_isset($direction_data,'m_out_timeout_user'));
		if($timeout < 1){
			$timeout = is_sum(is_isset($paymerch_data,'timeout'));
			$timeout_user = is_sum(is_isset($paymerch_data,'timeout_user'));
		}
		if($timeout > 0 and isset($item->touap_date)){
			if(
				$timeout_user == 0 or 
				$timeout_user == 1 and isset($item->new_user) and $item->new_user == 1 or 
				$timeout_user == 2 and $user_id < 1 or
				$timeout_user == 3 and isset($item->user_verify) and $item->user_verify == 0 
			){
				$now_time = current_time('timestamp');
				if($item->touap_date == '0000-00-00 00:00:00'){
					$array = array();
					$go_time = $now_time + ($timeout * 60 * 60);
					$array['touap_date'] = date('Y-m-d H:i:s', $go_time);
					$wpdb->update($wpdb->prefix."exchange_bids", $array, array('id'=>$item->id));
				} else {
					$go_time = strtotime($item->touap_date);
				}
					
				if($now_time > $go_time){
					//$au_filter['enable'] = 1;	
				} else {
					$au_filter['enable'] = 0;	
					$au_filter['error'][] = __('Timeout auto payout error','pn');
				}
			}
		}
	}
	
	return $au_filter;
}

add_filter('onebid_col1', 'onebid_col1_touap', 10, 2); 
function onebid_col1_touap($cols, $item){
	if(isset($item->touap_date) and $item->touap_date != '0000-00-00 00:00:00'){
		$cols['frozen_date'] = array(
			'type' => 'text',
			'title' => __('Est. payment date if the payment is delayed','pn'),
			'label' => '[frozen_date]',
		);	
	}
	return $cols;
}

add_filter('get_bids_replace_text','get_bids_replace_text_touap',99,3);
function get_bids_replace_text_touap($text, $item, $data_fs){
	if(strstr($text, '[frozen_date]')){
		$date = get_pn_time($item->touap_date, 'd.m.Y H:i:s');
		$text = str_replace('[frozen_date]', '<span class="onebid_item item_bcc bid_clpb_item bred_dash" data-clipboard-text="'. $date .'">' . $date . '</span>',$text);
	}
	return $text;
}

add_filter('change_bids_filter_list', 'touap_change_bids_filter_list'); 
function touap_change_bids_filter_list($lists){
global $wpdb;

	$options = array(
		'0' => '--'. __('All','pn').'--',
		'1' => __('Yes','pn'),
		'2' => __('No','pn'),
	);		
	$lists['other']['touap_date'] = array(
		'title' => __('Suspended automatic payouts','pn'),
		'name' => 'touap_date',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);
	
	return $lists;
}

add_filter('where_request_sql_bids', 'touap_where_request_sql_bids', 10,2); 
function touap_where_request_sql_bids($where, $pars_data){
global $wpdb;
	
	$pr = $wpdb->prefix;
	$sql_operator = is_sql_operator($pars_data);
	$touap_date = intval(is_isset($pars_data,'touap_date'));
	if($touap_date == 1){
		$where .= " {$sql_operator} {$pr}exchange_bids.touap_date != '0000-00-00 00:00:00'"; 
	} elseif($touap_date == 2){	
		$where .= " {$sql_operator} {$pr}exchange_bids.touap_date = '0000-00-00 00:00:00'";
	}
	
	return $where;
} 

add_action('pn_adminpage_content_pn_merchants','touap_adminpage_content_pn_cron',9);
add_action('pn_adminpage_content_all_cron','touap_adminpage_content_pn_cron',9);
add_action('pn_adminpage_content_pn_paymerchants','touap_adminpage_content_pn_cron',9);
function touap_adminpage_content_pn_cron(){
?>
	<div class="premium_substrate">
		<?php _e('Cron URL for verification and payout of frozen payments','pn'); ?><br /> 
		<a href="<?php echo get_cron_link('touap_cron'); ?>" target="_blank"><?php echo get_cron_link('touap_cron'); ?></a>
	</div>	
<?php
}
 
function touap_cron(){
global $wpdb;
	$av_status_timeout = get_option('av_status_timeout');
	if(!is_array($av_status_timeout)){ $av_status_timeout = array(); }	
	
	$st = apply_filters('status_for_autopay_admin', $av_status_timeout);
	if(is_array($st) and count($st) > 0){
		$st_in_join = create_data_for_bd($st, 'status');
		$bids = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE touap_date != '0000-00-00 00:00:00' AND status IN($st_in_join) ORDER BY touap_date ASC LIMIT 5"); 
		foreach($bids as $item){
			$direction_id = intval(is_isset($item, 'direction_id'));
			$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE auto_status='1' AND id='$direction_id'");
			$m_id = apply_filters('get_paymerchant_id', '', $direction, $item);
			if($m_id){
				$direction_data = get_direction_meta($direction_id, 'paymerch_data');
				$paymerch_data = get_paymerch_data($m_id);
				
				do_action('paymerchant_action_bid', $m_id, $item, 'site', $direction_data, 'touap', $direction, $paymerch_data);				
			}
		}
	}
} 

add_filter('list_cron_func', 'touap_list_cron_func');
function touap_list_cron_func($filters){
	$filters['touap_cron'] = array(
		'title' => __('Cron URL for verification and payout of frozen payments','pn'),
		'file' => '10min',
	);
	return $filters;
}