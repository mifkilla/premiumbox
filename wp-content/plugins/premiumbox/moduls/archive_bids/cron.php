<?php
if( !defined( 'ABSPATH')){ exit(); }

function pn_archives_bids(){
global $wpdb, $premiumbox;

	if(!$premiumbox->is_up_mode()){
		$del_file = intval($premiumbox->get_option('archivebids','txt'));
		$limit = intval($premiumbox->get_option('archivebids','limit_archive'));
		if($limit < 1){ $limit = 5; }
		
		$count_day = intval($premiumbox->get_option('logssettings', 'archive_bids_day'));
		if(!$count_day){ $count_day = 60; }
		
		$count_day = apply_filters('archive_bids_day', $count_day);
		if($count_day > 0){
			$second = $count_day * DAY_IN_SECONDS;
			$date = current_time('mysql');
			$time = current_time('timestamp') - $second;
			$ldate = date('Y-m-d H:i:s', $time);
			
			$my_dir = wp_upload_dir();
			$dir_old = $my_dir['basedir'].'/bids/';
			$dir = $premiumbox->upload_dir . 'bids/';
			
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE edit_date < '$ldate' LIMIT $limit");
			foreach($items as $item){
				$id = $item->id;
				
				$result = $wpdb->query("DELETE FROM ".$wpdb->prefix."exchange_bids WHERE id = '$id'");
				if($result == 1){
							
					$status = $item->status;			
					$user_id = $item->user_id;
					$ref_id = is_isset($item,'ref_id');
					$pcalc = intval(is_isset($item, 'pcalc'));
					$currency_code_id_give = $item->currency_code_id_give;
					$currency_code_id_get = $item->currency_code_id_get;
					$sum1c = $item->sum1c;
					$sum2c = $item->sum2c;		
					$partner_sum = is_isset($item,'partner_sum');
					$currency_id_give = $item->currency_id_give;
					$currency_id_get = $item->currency_id_get;
					$domacc1 = intval(is_isset($item, 'domacc1'));
					$domacc2 = intval(is_isset($item, 'domacc2'));					
						
					if($status == 'success'){
						if($user_id > 0){
							set_archive_data($user_id, 'user_exsum', '', '', $item->exsum);	
						}
						if($pcalc == 1){
							set_archive_data($ref_id, 'pbids', '', '', 1);
							set_archive_data($ref_id, 'pbids_sum', '', '', $partner_sum);
							set_archive_data($ref_id, 'pbids_exsum', '', '', $item->exsum);
						}
					}
						
					set_archive_data($currency_code_id_give, 'currency_code_give', $status, '', $item->sum1r);
					set_archive_data($currency_code_id_get, 'currency_code_get', $status, '', $item->sum2r);
					set_archive_data($currency_id_give, 'currency_give', $status, '', $item->sum1r);
					set_archive_data($currency_id_get, 'currency_get', $status, '', $item->sum2r);
					set_archive_data($item->direction_id, 'direction_give', $status, '', $item->sum1r);
					set_archive_data($item->direction_id, 'direction_get', $status, '', $item->sum2r);
						
					if($user_id > 0){
						set_archive_data($user_id, 'user_bids', $status, '', 1);
						
						if($domacc1 == 1){
							set_archive_data($user_id, 'domacc1_currency_code', $status, $currency_code_id_give, $sum1c);
						}
						if($domacc2 == 1){
							set_archive_data($user_id, 'domacc2_currency_code', $status, $currency_code_id_get, $sum2c);
						}				
					}
					
					do_action('archive_bids', $item->id, $item);
						
					$archive_content = array();
					foreach($item as $k => $v){
						$archive_content[$k] = $v;
					}
					
					$archive_content['comment_user'] = get_bids_meta($item->id, 'comment_user');
					$archive_content['comment_admin'] = get_bids_meta($item->id, 'comment_admin');
						
					$arr = array();
					$arr['archive_date'] = $date;
					$arr['create_date'] = is_isset($item,'create_date');
					$arr['edit_date'] = is_isset($item,'edit_date');
					$arr['archive_content'] = serialize($archive_content);
					$arr['bid_id'] = $id;
					$arr['user_id'] = is_isset($item,'user_id');
					$arr['ref_id'] = $ref_id;
					$arr['account_give'] = is_isset($item,'account_give');
					$arr['account_get'] = is_isset($item,'account_get');
					$arr['first_name'] = is_isset($item,'first_name');
					$arr['last_name'] = is_isset($item,'last_name');
					$arr['second_name'] = is_isset($item,'second_name');
					$arr['user_phone'] = is_isset($item,'user_phone');
					$arr['user_skype'] = is_isset($item,'user_skype');
					$arr['user_email'] = is_isset($item,'user_email');
					$arr['user_telegram'] = is_isset($item,'user_telegram');
					$arr['user_passport'] = is_isset($item,'user_passport');
					$arr['currency_id_give'] = is_isset($item,'currency_id_give');
					$arr['currency_id_get'] = is_isset($item,'currency_id_get');
					$arr['status'] = is_isset($item,'status');
					$arr['direction_id'] = is_isset($item,'direction_id');
					$arr['currency_code_id_give'] = is_isset($item,'currency_code_id_give');
					$arr['currency_code_id_get'] = is_isset($item,'currency_code_id_get');
					$arr['psys_id_give'] = is_isset($item,'psys_id_give');
					$arr['psys_id_get'] = is_isset($item,'psys_id_get');
					$arr['exsum'] = is_isset($item,'exsum');
					$arr['profit'] = is_isset($item,'profit');
					$arr['trans_in'] = is_isset($item,'trans_in');
					$arr['trans_out'] = is_isset($item,'trans_out');
					$arr['to_account'] = is_isset($item,'to_account');
					$arr['from_account'] = is_isset($item,'from_account');
					$wpdb->insert($wpdb->prefix . "archive_exchange_bids", $arr);
						
					$item = apply_filters('change_bidstatus', $item, 'archived', 'archive', 'system', $item->status); 	 
						
					$wpdb->query("DELETE FROM ".$wpdb->prefix."bids_meta WHERE item_id = '$id'");	
						
					if($del_file == 1){
						$file_old = $dir_old . $id .'.txt';
						if(is_file($file_old)){
							@unlink($file_old);
						}
						$file = $dir . $id .'.php';
						if(is_file($file)){
							@unlink($file);
						}					
					}
				}
			}
		}
	}
} 

add_filter('list_cron_func', 'pn_archives_bids_list_cron_func');
function pn_archives_bids_list_cron_func($filters){
	$filters['pn_archives_bids'] = array(
		'title' => __('Archiving orders older','pn'),
		'site' => '10min',
	);
	return $filters;
}

add_filter('list_logs_settings', 'pn_archives_bids_list_logs_settings');
function pn_archives_bids_list_logs_settings($filters){	
	$filters['archive_bids_day'] = array(
		'title' => __('Archiving orders older','pn') .' ('. __('days','pn') .')',
		'count' => 60,
		'minimum' => 5,
	);
	return $filters;
} 