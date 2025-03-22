<?php
if( !defined( 'ABSPATH')){ exit(); } 

function userxch_page_shortcode($atts, $content) {
global $wpdb;
	
	$temp = apply_filters('before_userxch_page','');

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	if($user_id){
		
		$user_discount = get_user_discount($user_id);
		$user_exchange = get_user_count_exchanges($user_id);
		$user_exchange_sum = get_user_sum_exchanges($user_id);		
		
		$list_stat_userxch = array(
			'discount' => array(
				'title' => __('Personal discount','pn'),
				'content' => is_out_sum($user_discount, 12, 'all') .'%',
			),
			'exchanges' => array(
				'title' => __('Exchanges','pn'),
				'content' => is_out_sum($user_exchange, 12, 'all'),
			),	
			'exchange_sum' => array(
				'title' => __('Amount of exchanges','pn'),
				'content' => is_out_sum($user_exchange_sum, 12, 'all') .' '. cur_type(),				
			),
		);
		$list_stat_userxch = apply_filters('list_stat_userxch', $list_stat_userxch);
		
		$stat = '
		<table>';	
			foreach($list_stat_userxch as $list_key => $list_value){
				$stat .= '
				<tr>
					<th>'. is_isset($list_value, 'title') .'</th>
					<td>'. is_isset($list_value, 'content') .'</td>
				</tr>					
				';
			}
		$stat .= '	
		</table>
		';
		
		$lists = array(
			'id' => __('ID','pn'),
			'date' => __('Date','pn'),
			'rate' => __('Rate','pn'),
			'give' => __('Send','pn'),
			'get' => __('Receive','pn'),
			'status' => __('Status','pn'),
		);
		$lists = apply_filters('lists_table_userxch', $lists);
		$lists = (array)$lists;	

		$limit = apply_filters('limit_list_userxch', 15);
		$count = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."exchange_bids WHERE user_id = '$user_id' AND status != 'auto'");
		$pagenavi = get_pagenavi_calc($limit,get_query_var('paged'),$count);
		
		$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE user_id = '$user_id' AND status != 'auto' ORDER BY edit_date DESC LIMIT ". $pagenavi['offset'] .",".$pagenavi['limit']);		

		$date_format = get_option('date_format');
		$time_format = get_option('time_format');				
			
		$v = get_currency_data();
			
		$table_list = '<table>';
		$table_list .= '<thead><tr>';
			foreach($lists as $list_key => $list_val) {
				$table_list .= '<th class="th_'. $list_key .'">'. $list_val .'</th>';
			}
		$table_list .= '</tr></thead><tbody>';
			
		$s=0;
        foreach ($datas as $item) { $s++;
			if($s%2==0){ $odd_even = 'even'; } else { $odd_even = 'odd'; }
			
			$currency_id_give = $item->currency_id_give;
			$currency_id_get = $item->currency_id_get;
						
			if(isset($v[$currency_id_give]) and isset($v[$currency_id_get])){
				$vd1 = $v[$currency_id_give];
				$vd2 = $v[$currency_id_get];
				$decimal1 = $vd1->currency_decimal;
				$decimal2 = $vd2->currency_decimal;	
			} else {
				$decimal1 = 12;
				$decimal2 = 12;
			}			
				
			$table_list .= '<tr>';
			foreach($lists as $key => $title){
				$table_list .= '<td>';
					
				$one_line = '';
				if($key == 'id'){
					$one_line = $item->id;
				}							
				if($key == 'date'){
					$one_line = get_pn_time($item->create_date, "{$date_format}, {$time_format}");
				}
				if($key == 'rate'){
					$one_line = '<span class="exch_course1"><span class="exch_sum">'. is_out_sum(is_sum($item->course_give), $decimal1, 'course') .'</span> '. is_site_value($item->currency_code_give) .'</span> <span class="exch_course2"><span class="exch_sum">'. is_out_sum(is_sum($item->course_get), $decimal2, 'course') .'</span> '. is_site_value($item->currency_code_get) .'</span>';
				}	
				if($key == 'give'){
					$one_line = '<span class="exch_sum">'. is_out_sum(is_sum(ex_sum_give($item)), $decimal1, 'all') .'</span> '. pn_strip_input(ctv_ml($item->psys_give)) .' '. is_site_value($item->currency_code_give);
				}	
				if($key == 'get'){
					$one_line = '<span class="exch_sum">'. is_out_sum(is_sum(ex_sum_get($item)), 12 , 'all') .'</span> '. pn_strip_input(ctv_ml($item->psys_get)) .' '. is_site_value($item->currency_code_get);
				}
				if($key == 'status'){
					$status = get_bid_status($item->status);
					$link = get_bids_url($item->hashed);
					$one_line = '<a href="'. $link .'" target="_blank" rel="noreferrer noopener" class="exch_status_link st_'. is_status_name($item->status) .'">'. $status .'</a>';
				}
					
				$table_list .= apply_filters('body_list_userxch', $one_line, $item, $key, $title, $date_format, $time_format, $v);
				$table_list .= '</td>';	
			}
			$table_list .= '</tr>';
	    }	

		if($count == 0){
			$table_list .= '<tr><td colspan="'. count($lists) .'"><div class="no_items"><div class="no_items_ins">'. __('No items','pn') .'</div></div></td></tr>';
		}	

		$table_list .= '</tbody></table>';			
		
		$array = array(
			'[stat]' => $stat,
			'[table_list]' => $table_list,
			'[pagenavi]' => get_pagenavi($pagenavi),
		);	
		
		$temp_form = '
		<div class="userxch_tablediv statstablediv">
			<div class="userxch_tablediv_ins statstablediv_ins">
				[stat]
			</div>
		</div>
		<div class="userxchtable pntable_wrap">
			<div class="userxchtable_ins pntable_wrap_ins">
				<div class="userxchtable_title pntable_wrap_title">
					<div class="userxchtable_title_ins pntable_wrap_title_ins">
						'. __('Your transactions','pn') .'
					</div>
				</div>	
				<div class="userxch_table pntable">
					<div class="userxch_table_ins pntable_ins">
						[table_list]
					</div>	
				</div>
						
				[pagenavi]
			</div>
		</div>						
		';
		
		$temp_form = apply_filters('userxch_form_temp',$temp_form);
		$temp .= get_replace_arrays($array, $temp_form);		
	
	} else {
		$temp .= '<div class="resultfalse">'. __('Error! Page is available for authorized users only','pn') .'</div>';
	}

	$temp .= apply_filters('after_userxch_page','');

	return $temp;
}
add_shortcode('userxch', 'userxch_page_shortcode');