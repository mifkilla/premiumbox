<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('pn_adminpage_title_pn_migrate', 'pn_adminpage_title_pn_migrate');
function pn_adminpage_title_pn_migrate($page){
	_e('Migration','pn');
} 

add_action('pn_adminpage_content_pn_migrate','def_pn_adminpage_content_pn_migrate');
function def_pn_adminpage_content_pn_migrate(){
	
	$form = new PremiumForm();
	
	$up_list = array(
		'2.2' => array(
			'step' => 3,
			'step_key' => 3,
		),	
		'2.1' => array(
			'step' => 6,
			'step_key' => 2,
		),
		'2.0' => array(
			'step' => 30,
			'step_key' => 1,
		),	
		'1.6' => array(
			'step' => 13,
			'step_key' => 6,
		),		
		'1.5' => array(
			'step' => 3,
			'step_key' => 5,
		),
		'speacial' => array(
			'step' => 1,
			'step_key' => 9,
		),		
	);
	$up_list = apply_filters('migration_uplist', $up_list);
	
	foreach($up_list as $vers => $vers_data){
?>
<div class="premium_body">
	<div class="premium_standart_div">
		<div style="padding: 0 0 10px 0;">
		<?php
		$title = sprintf(__('Migration (if version is lesser than %s)','pn'), $vers);
		if($vers == 'speacial'){
			$title = __('Special migration steps (do not use without instructions)','pn');
		}
		$form->h3($title, '');
		?>
		</div>
		<?php
 		$r=0;
		$step = intval(is_isset($vers_data, 'step'));
		$step_key = intval(is_isset($vers_data, 'step_key'));
		while($r++<$step){
		?>		
		<div class="premium_standart_line">		 
			<input name="submit" type="submit" class="button pn_prbar" data-count-url="<?php the_pn_link('migrate_step_count', 'post'); ?>&step=<?php echo $step_key; ?>_<?php echo $r; ?>" data-title="<?php printf(__('Step %s','pn'),$r); ?>" value="<?php printf(__('Step %s','pn'),$r); ?>" />	
			&nbsp;
			<input name="submit" type="submit" class="button pn_prbar" data-count-url="<?php the_pn_link('migrate_step_count', 'post'); ?>&step=<?php echo $step_key; ?>_<?php echo $r; ?>&tech=1" data-title="<?php printf(__('Step %s','pn'),$r); ?>" value="<?php printf(__('Technical step %s','pn'),$r); ?>" />		
		</div>
		<?php 
		}
		?>
	</div>
</div>
	<?php } ?>

<script type="text/javascript">
jQuery(function($){
	$(document).PrBar({ 
		trigger: '.pn_prbar',
		start_title: '<?php _e('determining the number of requests','pn'); ?>...',
		end_title: '<?php _e('number of requests defined','pn'); ?>',
		found_title: '<?php _e('Found: %count% requests','pn'); ?>',
		perform_title: '<?php _e('Perform','pn'); ?>:',
		step_title: '<?php _e('Step','pn'); ?>:',
		run_title: '<?php _e('Run','pn'); ?>',
		line_text: '<?php _e('completed %now% of %max% steps','pn'); ?>',
		line_success: '<?php _e('step %now% is successful','pn'); ?>',
		end_progress: '<?php _e('action is completed','pn'); ?>',
		success: function(res){
			res.prop('disabled', true);
		}
	});
});
</script>
<?php
}

add_action('premium_action_migrate_step_count','def_premium_action_migrate_step_count');
function def_premium_action_migrate_step_count(){
global $wpdb;	

	header('Content-Type: application/json; charset=utf-8');

	only_post();

	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = '';
	$log['count'] = 0;
	$log['link'] = '';
	
	$step = is_param_get('step');
	$tech = intval(is_param_get('tech'));
	if(current_user_can('administrator')){
		$count = 0;
		
		if(!$tech){
			
			if($step == '9_1'){
				$count = 1;
			}			
			
			if($step == '5_2'){
				$count = 1;
			}

			if($step == '5_3'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."autodel_bids_time");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."autodel_bids_time");
				}
			}

			if($step == '6_1'){
				$count = 1;
			}
			
			if($step == '6_2'){
				$count = 1;
			}

			if($step == '6_3'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."currency_custom_fields");
				if($query == 1){
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."currency_custom_fields WHERE auto_status = '1'");
				}				
			}

			if($step == '6_4'){
				$arr = array(
					array(
						'tbl' => 'currency_custom_fields',
						'row' => 'firstzn',
					),
					array(
						'tbl' => 'currency',
						'row' => 'firstzn',					
					),
					array(
						'tbl' => 'direction_custom_fields',
						'row' => 'firstzn',
					),					
				);
				$count = count($arr);			
			}

			if($step == '6_7'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."valuts_account");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."valuts_account");
				}
			}

			if($step == '6_8'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."directions");
				}
			}

			if($step == '6_9'){				
				$count = 1;
			}			

			if($step == '6_10'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."uv_field");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."uv_field");
				}
			}			
			
			if($step == '6_11'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."uv_field_user");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."uv_field_user");
				}
			}

			if($step == '6_12'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."user_wallets");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."exchange_bids");
				}
			}

			if($step == '6_13'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."uv_wallets_files");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."uv_wallets_files");
				}
			}

			if($step == '1_1'){
				$count = 1;
			}

			if($step == '1_2'){
				$count = 1;
			}
			
			if($step == '1_3'){
				$count = 1;
			}	

			if($step == '1_4'){
				$count = $wpdb->get_var("SELECT COUNT(ID) FROM ". $wpdb->prefix ."users");		
			}

			if($step == '1_5'){
				$count = 1;		
			}

			if($step == '1_6'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."uv_field");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."uv_field");
				}
			}

			if($step == '1_7'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."geoip_blackip");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."geoip_blackip");
				}
			}

			if($step == '1_8'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."geoip_whiteip");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."geoip_whiteip");
				}
			}

			if($step == '1_9'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."geoip_country");
				if($query == 1){				
					$count = 1;
				}
			}

			if($step == '1_10'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."directions");
				}
			}

			if($step == '1_11'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."exchange_bids");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."exchange_bids WHERE status = 'success'");
				}
			}

			if($step == '1_12'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."archive_exchange_bids");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."archive_exchange_bids WHERE status = 'success'");
				}
			}			

			if($step == '1_14'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."parser_pairs");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."parser_pairs");
				}
			}

			if($step == '1_15'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."bcbroker_directions");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."bcbroker_directions");
				}
			}

			if($step == '1_16'){
				$arr = array(
					array(
						'tbl' => 'directions',
						'row' => 'm_in',
					),
					array(
						'tbl' => 'directions',
						'row' => 'm_out',					
					),					
				);
				$count = count($arr);			
			}

			if($step == '1_17'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."directions");
				}
			}

			if($step == '1_18'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."directions");
				}
			}

			if($step == '1_19'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."directions");
				}
			}

			if($step == '1_21'){				
				$count = 1;
			}

			if($step == '1_22'){				
				$count = 1;
			}

			if($step == '1_23'){				
				$count = 1;
			}

			if($step == '1_24'){				
				$count = 1;
			}
			
			if($step == '1_25'){				
				$count = 1;
			}
			
			if($step == '1_26'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."directions");
				}
			}

			if($step == '1_27'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."recalc_bids");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."recalc_bids");
				}
			}

			if($step == '1_28'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."currency");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."currency");
				}
			}			

			if($step == '1_30'){
				$count = 1;
			}

			if($step == '2_1'){
				$count = $wpdb->get_var("SELECT COUNT(ID) FROM ". $wpdb->prefix ."users");		
			}

			if($step == '2_2'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."currency");
				if($query == 1){
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."currency");
				}				
			}

			if($step == '2_3'){
				$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."exchange_bids");		
			}

			if($step == '2_4'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."currency_accounts");
				if($query == 1){
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."currency_accounts");	
				}	
			}

			if($step == '2_5'){
				$count = 1;
			}

			if($step == '2_6'){
				$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."exchange_bids");		
			}

			if($step == '3_1'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."bidstatus");
				if($query == 1){
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."bidstatus");	
				}	
			}

			if($step == '3_2'){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."recalcs");
				if($query == 1){				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."recalcs");
				}
			}

			if($step == '3_3'){
				$count = 1;		
			}

			if($step == '3_4'){
				$count = 1;		
			}	

			if($step == '3_5'){
				$count = 1;		
			}

			if($step == '3_6'){
				$count = 1;		
			}

			if($step == '3_7'){
				$count = 1;		
			}

			if($step == '3_8'){
				$count = 1;		
			}			

			$count = apply_filters('migration_count', $count, $step);
		}
		
		$log['status'] = 'success';
		$log['count'] = $count;
		$log['link'] = pn_link('migrate_step_request','post').'&step='.$step;
		$log['status_text'] = __('Ok!','pn');

	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! Insufficient privileges','pn');
	}
	
	echo json_encode($log);
	exit;	
}

add_action('premium_action_migrate_step_request','def_premium_action_migrate_step_request');
function def_premium_action_migrate_step_request(){
global $wpdb, $premiumbox;	

	header('Content-Type: application/json; charset=utf-8');

	only_post();

	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = '';
	$log['count'] = 0;
	$log['link'] = '';
	
	$step = is_param_get('step');
	$num_page = intval(is_param_post('num_page'));
	$limit = intval(is_param_post('limit')); if($limit < 1){ $limit = 1; }
	$offset = ($num_page - 1) * $limit;
	if(current_user_can('administrator')){										
		
		if($step == '5_2'){	 /*****************/
			$premiumbox->update_option('archivebids', 'limit_archive', 5);
		} 
		
		if($step == '5_3'){	 /*****************/
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."autodel_bids_time");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."autodel_bids_time LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$id = $data->id;
					
					$array = array();
					$array['direction_id'] = is_isset($data, 'naps_id');
					$array['enable_autodel'] = is_isset($data, 'enable_autodel');
					$array['cou_hour'] = is_isset($data, 'cou_hour');
					$array['cou_minute'] = is_isset($data, 'cou_minute');
					$array['statused'] = is_isset($data, 'statused');
					
					$cc_count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."auto_removal_bids WHERE id='$id'");
					if($cc_count == 0){
						$array['id'] = $id;
						$wpdb->insert($wpdb->prefix ."auto_removal_bids", $array);	
					} else {
						$wpdb->update($wpdb->prefix ."auto_removal_bids", $array, array('id'=> $id));	
					}										
				}
			}
		}

		if($step == '6_1'){	 /*****************/
			
			$premiumbox->update_option('checkpersdata', 'contactform', 1);
			$premiumbox->update_option('checkpersdata', 'reviewsform', 1);
			
			$reserv_out = get_option('reserv_out');
			if(is_array($reserv_out)){ 
				$premiumbox->update_option('reserv', 'out', $reserv_out);
				delete_option('reserv_out');
			}

			$reserv_in = get_option('reserv_in');
			if(is_array($reserv_in)){ 
				$premiumbox->update_option('reserv', 'in', $reserv_in);
				delete_option('reserv_in');
			}
			
			$reserv_auto = get_option('reserv_auto');
			if(is_array($reserv_auto)){ 
				$premiumbox->update_option('reserv', 'auto', $reserv_auto);
				delete_option('reserv_auto');
			}
			
			$premiumbox->delete_option('wchecks', '');
			
			$wp_upload_dir = wp_upload_dir();
			$path = $wp_upload_dir['basedir'];
			$dir = trailingslashit( $path . '/captcha/' );
			full_del_dir($dir);
			
		}

		if($step == '6_2'){	 /*****************/
			$tables = array(
				'warning_mess','head_mess', 'operator_schedules','change','term_meta','vtypes','login_check',
				'admin_captcha', 'admin_captcha_plus','standart_captcha','standart_captcha_plus','valuts_meta','valuts',
				'custom_fields_valut','custom_fields','cf_naps','masschange','user_accounts','uv_accounts_files',
				'naps_meta','naps_order','userverify','geoip_template','naps','autodel_bids_time','reserve_requests',
				'trans_reserv','archive_bids','payoutuser','valuts_fstats','vtypes_fstats','bids_fstats',
				'bcbroker_vtypes','bcbroker_naps',
			);
			foreach($tables as $tbl){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix . $tbl);
				if($query == 1){
					$wpdb->query("DROP TABLE ". $wpdb->prefix . $tbl);
				}	
			}
		}

		if($step == '6_3'){	 /*****************/
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."currency_custom_fields");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."currency_custom_fields WHERE auto_status = '1' LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$id = $data->id;
					$array = array();
					
					$helps = pn_strip_input(is_isset($data,'helps'));
					$array['helps_give'] = $helps;
					$array['helps_get'] = $helps;
					
					$wpdb->update($wpdb->prefix ."currency_custom_fields", $array, array('id'=> $id));

					$currency_id = intval(is_isset($data, 'currency_id'));
					$place_id = intval(is_isset($data, 'place_id'));
					
					$cc_count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."cf_currency WHERE currency_id='$currency_id' AND cf_id='$id'");
					if($cc_count == 0){
						$arr = array();
						$arr['currency_id'] = $currency_id;
						$arr['cf_id'] = $id;
						if($place_id == 0){
							$arr['place_id'] = 1;
							$wpdb->insert($wpdb->prefix ."cf_currency", $arr);
							$arr['place_id'] = 2;
							$wpdb->insert($wpdb->prefix ."cf_currency", $arr);
						} else {
							$arr['place_id'] = $place_id;
							$wpdb->insert($wpdb->prefix ."cf_currency", $arr);
						}
					}
				}
			}
		}

		if($step == '6_4'){	 /*****************/

			$arr = array(
				array(
					'tbl' => 'currency_custom_fields',
					'row' => 'firstzn',
				),
				array(
					'tbl' => 'currency',
					'row' => 'firstzn',					
				),	
				array(
					'tbl' => 'direction_custom_fields',
					'row' => 'firstzn',
				),
			);
			$arr = array_slice($arr, $offset, $limit);
			
			foreach($arr as $data){
				$table = $wpdb->prefix. $data['tbl'];
				$query = $wpdb->query("CHECK TABLE {$table}");
				if($query == 1){
					$row = $data['row'];
					$que = $wpdb->query("SHOW COLUMNS FROM {$table} LIKE '{$row}'");
					if ($que) {
						$wpdb->query("ALTER TABLE {$table} CHANGE `{$row}` `{$row}` varchar(150) NOT NULL");
					}	
				}
			}	

			$in = intval($premiumbox->get_option('txtxml','txt'));
			if($in){
				$premiumbox->update_option('txtxml','site_txt', 1);
			}
			$in = intval($premiumbox->get_option('txtxml','xml'));
			if($in){
				$premiumbox->update_option('txtxml','site_xml', 1);
			}
		}

		if($step == '6_7'){	 /*****************/
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."valuts_account");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."valuts_account LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$id = $data->id;
					
					$array = array();
					$array['currency_id'] = is_isset($data,'valut_id');
					$array['count_visit'] = is_isset($data,'count_visit');
					$array['max_visit'] = is_isset($data,'max_visit');
					$array['text_comment'] = is_isset($data,'text_comment');
					$array['inday'] = is_isset($data,'inday');
					$array['inmonth'] = is_isset($data,'inmonth');
					$array['accountnum'] = is_isset($data,'accountnum');
					$array['status'] = is_isset($data,'status');
					
					$cc_count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."currency_accounts WHERE id='$id'");
					if($cc_count == 0){
						$array['id'] = $id;
						$wpdb->insert($wpdb->prefix ."currency_accounts", $array);	
					} else {
						$wpdb->update($wpdb->prefix ."currency_accounts", $array, array('id'=> $id));	
					}										
				}
			}
		}

		if($step == '6_8'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$array_pp_data = get_direction_meta($data_id, 'pp_data');
					if(!is_array($array_pp_data)){
						$pp_data = array();
						$pp_data['enable'] = intval(get_direction_meta($data_id, 'p_enable'));
						$pp_data['ind_sum'] = get_direction_meta($data_id, 'p_ind_sum');
						$pp_data['min_sum'] = get_direction_meta($data_id, 'p_min_sum');
						$pp_data['max_sum'] = get_direction_meta($data_id, 'p_max_sum');
						$pp_data['pers'] = get_direction_meta($data_id, 'p_pers');
						$pp_data['max'] = get_direction_meta($data_id, 'p_max');
						update_direction_meta($data_id, 'pp_data', $pp_data);
					}
					
					delete_direction_meta($data_id, 'p_enable');
					delete_direction_meta($data_id, 'p_ind_sum');
					delete_direction_meta($data_id, 'p_min_sum');
					delete_direction_meta($data_id, 'p_max_sum');
					delete_direction_meta($data_id, 'p_pers');
					delete_direction_meta($data_id, 'p_max');
					
					$verify = intval(get_direction_meta($data_id, 'verify'));
					if($verify){
						$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'verify'");
						if ($query == 1){
							$array = array();
							$array['verify'] = $verify;
							$wpdb->update($wpdb->prefix ."directions", $array, array('id' => $data_id));
						}
					}
					delete_direction_meta($data_id, 'verify');
				}				
			}
		}	

		if($step == '6_9'){
			$text = pn_strip_text($premiumbox->get_option('usve','text_notverify'));
			if($text){
				$premiumbox->update_option('naps_temp', 'notverify_text', $text);
				$premiumbox->update_option('naps_nodescr', 'notverify_text', 1);
				$premiumbox->delete_option('usve','text_notverify');
			}
			
			$text = pn_strip_text($premiumbox->get_option('usve','text_notverifysum'));
			if($text){
				$premiumbox->update_option('naps_temp', 'notverify_bysum', $text);
				$premiumbox->update_option('naps_nodescr', 'notverify_bysum', 1);
				$premiumbox->delete_option('usve','text_notverifysum');
			}			
		}
		
		if($step == '6_10'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."uv_field");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."uv_field LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$fieldvid = $data->fieldvid;
					
					$wpdb->query("UPDATE ".$wpdb->prefix."uv_field_user SET fieldvid = '$fieldvid' WHERE uv_field = '$data_id'");
				}				
			}
		}

		if($step == '6_11'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."uv_field_user");
			if($query == 1){
				$my_dir = wp_upload_dir();
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."uv_field_user LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$old_file = $my_dir['basedir'].'/userverify/'. $data->uv_id .'/'. $data->uv_data;
					if(is_file($old_file)){
						$path = $premiumbox->upload_dir;
						$path2 = $path . 'userverify/';
						$path3 = $path . 'userverify/' . $data->uv_id . '/';
						if(!is_dir($path)){ 
							@mkdir($path , 0777);
						}
						if(!is_dir($path2)){ 
							@mkdir($path2 , 0777);
						}	
						if(!is_dir($path3)){ 
							@mkdir($path3 , 0777);
						}
						
						$fdata = @file_get_contents($old_file);
						$fdata = str_replace('*', '%star%', $fdata);
						
						$file = $path3 . $data->id . '.php';
						
						$apd = $fdata;
						$file_text = add_phpf_data($apd);
						
						$file_open = @fopen($file, 'w');
						@fwrite($file_open, $file_text);
						@fclose($file_open);
						
						if(is_file($file)){
							@unlink($old_file);
						}
					}
				}				
			}
		}

		if($step == '6_12'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."user_wallets");
			if($query == 1){				
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."exchange_bids LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$array = array();
					$user_id = $data->user_id;
					if($user_id){
						$account = $data->account_give;
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."user_wallets WHERE user_id = '$user_id' AND verify='1' AND accountnum='$account'");
						if($cc > 0){	
							$array['accv_give'] = 1;
						}
						$account = $data->account_get;
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."user_wallets WHERE user_id = '$user_id' AND verify='1' AND accountnum='$account'");
						if($cc > 0){	
							$array['accv_get'] = 1;
						}						
						if(count($array) > 0){
							$wpdb->update($wpdb->prefix ."exchange_bids", $array, array('id'=> $data_id)); 	
						}
					}
				}	
			}
		}

		if($step == '6_13'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."uv_wallets_files");
			if($query == 1){
				$my_dir = wp_upload_dir();
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."uv_wallets_files LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$old_file = $my_dir['basedir'].'/accountverify/'. $data->uv_wallet_id .'/'. $data->uv_data;
					if(is_file($old_file)){
						$path = $premiumbox->upload_dir;
						$path2 = $path . 'accountverify/';
						$path3 = $path . 'accountverify/' . $data->uv_wallet_id . '/';
						if(!is_dir($path)){ 
							@mkdir($path , 0777);
						}
						if(!is_dir($path2)){ 
							@mkdir($path2 , 0777);
						}	
						if(!is_dir($path3)){ 
							@mkdir($path3 , 0777);
						}
						
						$fdata = @file_get_contents($old_file);
						$fdata = str_replace('*', '%star%', $fdata);
						
						$file = $path3 . $data->id . '.php';
						
						$apd = $fdata;
						$file_text = add_phpf_data($apd);
						
						$file_open = @fopen($file, 'w');
						@fwrite($file_open, $file_text);
						@fclose($file_open);
						if(is_file($file)){
							@unlink($old_file);
						}
					}
				}				
			}
		}

		if($step == '1_1'){
			$lang = get_option('pn_lang');
			if(!is_array($lang)){ $lang = array(); } 
			
			if(!isset($lang['lang_redir'])){
				$lr = $premiumbox->get_option('lang_redir');
				$lang['lang_redir'] = intval($lr);
				update_option('pn_lang', $lang);
				
				$premiumbox->delete_option('lang_redir');
			}
		}

		if($step == '1_2'){
			$pn_notify = get_option('pn_notify');
			if(is_array($pn_notify)){
				$email_notify = is_isset($pn_notify, 'email');
				update_option('pn_notify_email', $email_notify);
				$sms_notify = is_isset($pn_notify, 'sms');
				update_option('pn_notify_sms', $sms_notify);
				delete_option('pn_notify', $pn_notify);
			}
		}

		if($step == '1_3'){
			$mail_data = get_option('pn_mailtemp_modul');
			if(is_array($mail_data)){
				$premiumbox->update_option('email', 'mail', is_isset($mail_data,'mail'));
				$premiumbox->update_option('email', 'name', is_isset($mail_data,'name'));
				delete_option('pn_mailtemp_modul');
			}
		}	

		if($step == '1_4'){	 /*****************/
			$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."users LIMIT {$offset},{$limit}");
			foreach($datas as $data){
				$id = $data->ID;
				
				$array = array();
				$array['alogs_email'] = intval(is_isset($data, 'sec_login'));
				$wpdb->update($wpdb->prefix ."users", $array, array('ID'=> $id));	

				$um_value = is_isset($data, 'user_url');
				update_user_meta($id, 'user_website', $um_value) or add_user_meta($id, 'user_website', $um_value, true);
			} 
		}

		if($step == '1_5'){	 /*****************/
			$arr = array('news_key','news_descr','ogp_news_img','ogp_news_title','ogp_news_descr');
			foreach($arr as $k){
				$nk = str_replace('news', 'post', $k);
				$wpdb->query("UPDATE ".$wpdb->prefix."pn_options SET meta_key2 = '$nk' WHERE meta_key = 'seo' AND meta_key2='$k'");
			}
		}

		if($step == '1_6'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."uv_field");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."uv_field LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$id = $data->id;
					$country = is_isset($data,'country');
					$uns = @unserialize($country);
					if(!is_array($uns)){
						$countries = array();
						if(preg_match_all('/\[d](.*?)\[\/d]/s', $country, $match, PREG_PATTERN_ORDER)){
							$countries = $match[1];
						}	
						$arr = array();
						if(count($countries) > 0){
							$arr['country'] = serialize($countries);
						} else {
							$arr['country'] = '';
						}
						$wpdb->update($wpdb->prefix ."uv_field", $arr, array('id' => $id));
					}
				}				
			}
		}

		if($step == '1_7'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."geoip_blackip");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."geoip_blackip LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$arr = array();
					$arr['theip'] = $data->theip;
					$arr['thetype'] = 0;
					$wpdb->insert($wpdb->prefix ."geoip_ips", $arr);
				}				
			}
		}

		if($step == '1_8'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."geoip_whiteip");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."geoip_whiteip LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$arr = array();
					$arr['theip'] = $data->theip;
					$arr['thetype'] = 1;
					$wpdb->insert($wpdb->prefix ."geoip_ips", $arr);					
				}				
			}
		}

		if($step == '1_9'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."geoip_country");
			if($query == 1){
				$array = get_option('geoip_country');
				if(!is_array($array)){
					$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."geoip_country");
					$arr = array();
					foreach($datas as $data){
						$arr[$data->attr] = $data->attr;
					}
					update_option('geoip_country', $arr);
				}
			}
		}		

		if($step == '1_10'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$seo = get_direction_meta($data_id, 'seo');
					if(!is_array($seo)){
						$seo = array();
						$seo['seo_exch_title'] = get_direction_meta($data_id, 'seo_exch_title');
						$seo['seo_title'] = get_direction_meta($data_id, 'seo_title');
						$seo['seo_key'] = get_direction_meta($data_id, 'seo_key'); 
						$seo['seo_descr'] = get_direction_meta($data_id, 'seo_descr');
						$seo['ogp_title'] = get_direction_meta($data_id, 'ogp_title'); 
						$seo['ogp_descr'] = get_direction_meta($data_id, 'ogp_descr');
						update_direction_meta($data_id, 'seo', $seo);
					}
				}
			}
		}
		
		if($step == '1_11'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."users_old_data");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE status = 'success' LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."users_old_data WHERE bid_id='$data_id'");
					if($cc == 0){
						$arr = array();
						$arr['bid_id'] = $data_id;
						$arr['account_give'] = $data->account_give;
						$arr['account_get'] = $data->account_get;
						$arr['user_phone'] = str_replace('+','',$data->user_phone);
						$arr['user_skype'] = $data->user_skype;
						$arr['user_email'] = $data->user_email;
						$wpdb->insert($wpdb->prefix ."users_old_data", $arr);
					}
				}
			}
		}

		if($step == '1_12'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."archive_exchange_bids");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."archive_exchange_bids WHERE status = 'success' LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."users_old_data WHERE bid_id='$data_id'");
					if($cc == 0){
						$arr = array();
						$arr['bid_id'] = $data_id;
						$arr['account_give'] = $data->account_give;
						$arr['account_get'] = $data->account_get;
						$arr['user_phone'] = str_replace('+','',$data->user_phone);
						$arr['user_skype'] = $data->user_skype;
						$arr['user_email'] = $data->user_email;
						$wpdb->insert($wpdb->prefix ."users_old_data", $arr);
					}
				}
			}
		}		
		
		if($step == '1_14'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."parser_pairs");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."parser_pairs LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$arr = array();
					$arr['title_birg'] = ctv_ml($data->title_birg);
					$wpdb->update($wpdb->prefix ."parser_pairs", $arr, array('id'=> $data_id));
				}
			}
		}

		if($step == '1_15'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."bcbroker_directions");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."bcbroker_directions LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$d_arr = array();
					$d_arr['bcbroker_id'] = 1;
					$wpdb->update($wpdb->prefix ."directions", $d_arr, array('id'=> $data->direction_id));
				}
			}
		}	

		if($step == '1_16'){	 /*****************/

			$arr = array(
				array(
					'tbl' => 'directions',
					'row' => 'm_in',
				),
				array(
					'tbl' => 'directions',
					'row' => 'm_out',					
				),	
			);
			$arr = array_slice($arr, $offset, $limit);
			foreach($arr as $data){
				$table = $wpdb->prefix. $data['tbl'];
				$query = $wpdb->query("CHECK TABLE {$table}");
				if($query == 1){
					$row = $data['row'];
					$que = $wpdb->query("SHOW COLUMNS FROM {$table} LIKE '{$row}'");
					if($que){
						$wpdb->query("ALTER TABLE {$table} CHANGE `{$row}` `{$row}` longtext NOT NULL");
					}	
				}
			}	
			
		}

		if($step == '1_17'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$array = array();
					$m_in = is_isset($data,'m_in');
					$m_in_arr = @unserialize($m_in);
					if(!is_array($m_in_arr)){
						if($m_in){
							$array['m_in'] = @serialize(array($m_in));
						} else {	
							$array['m_in'] = @serialize(array());
						}
					}
					$m_out = is_isset($data,'m_out');
					$m_out_arr = @unserialize($m_out);
					if(!is_array($m_out_arr)){
						if($m_out){
							$array['m_out'] = @serialize(array($m_out));
						} else {	
							$array['m_out'] = @serialize(array());
						}
					}
					if(count($array) > 0){
						$wpdb->update($wpdb->prefix ."directions", $array, array('id' => $data_id));
					}
				}				
			}
		}

		if($step == '1_18'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$pp_data = get_direction_meta($data_id, 'pp_data');
					if(is_array($pp_data)){
						$p_enable = intval(is_isset($pp_data, 'enable'));
						$p_disable = 0;
						if($p_enable == 0){
							$p_disable = 1;
						}
						$pp_data['disable'] = $p_disable;
						update_direction_meta($data_id, 'pp_data', $pp_data);
					}
				}				
			}
		}		

		if($step == '1_19'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$verify_account = get_direction_meta($data_id, 'verify_account');
					if($verify_account == 1){
						update_direction_meta($data_id, 'verify_acc1', 1);
					} elseif($verify_account == 2){
						update_direction_meta($data_id, 'verify_acc2', 1);
					} elseif($verify_account == 3){
						update_direction_meta($data_id, 'verify_acc1', 1);
						update_direction_meta($data_id, 'verify_acc2', 1);
					}
					delete_direction_meta($data_id, 'verify_account');
				}				
			}
		}	

		if($step == '1_21'){
			$extended = get_option('pn_extended');
			if(!is_array($extended)){ $extended = array(); }

			$merchants = get_option('smsgate');
			if(!is_array($merchants)){ $merchants = array(); }
			
			if(isset($extended['sms']) and is_array($extended['sms'])){
				$scripts = list_extended($premiumbox, 'sms');
				$item = array();
				foreach($extended['sms'] as $k => $v){ 
					$scr_data = is_isset($scripts, $k);
					$item[$k] = array(
						'title' => ctv_ml(is_isset($scr_data, 'title')) . ' ('. $v . ')',
						'script' => $k,
						'status' => intval(is_isset($merchants, $k)),
					);
				}
				unset($extended['sms']);
				update_option('extlist_sms', $item);
			}
			update_option('pn_extended', $extended);
		}
		
		if($step == '1_22'){
			$extended = get_option('pn_extended');
			if(!is_array($extended)){ $extended = array(); }

			$merchants = get_option('merchants');
			if(!is_array($merchants)){ $merchants = array(); }
			
			if(isset($extended['merchants']) and is_array($extended['merchants'])){
				$scripts = list_extended($premiumbox, 'merchants');
				$item = array();
				foreach($extended['merchants'] as $k => $v){
					$scr_data = is_isset($scripts, $k);
					$item[$k] = array(
						'title' => ctv_ml(is_isset($scr_data, 'title')) . ' ('. $v . ')',
						'script' => $k,
						'status' => intval(is_isset($merchants, $k)),
					);
				}
				unset($extended['merchants']);
				update_option('extlist_merchants', $item);
			}
			update_option('pn_extended', $extended);
		}

		if($step == '1_23'){
			$extended = get_option('pn_extended');
			if(!is_array($extended)){ $extended = array(); }

			$merchants = get_option('paymerchants');
			if(!is_array($merchants)){ $merchants = array(); }
			
			if(isset($extended['paymerchants']) and is_array($extended['paymerchants'])){
				$scripts = list_extended($premiumbox, 'paymerchants');
				$item = array();
				foreach($extended['paymerchants'] as $k => $v){
					$scr_data = is_isset($scripts, $k);
					$item[$k] = array(
						'title' => ctv_ml(is_isset($scr_data, 'title')) . ' ('. $v . ')',
						'script' => $k,
						'status' => intval(is_isset($merchants, $k)),
					);
				}
				unset($extended['paymerchants']);
				update_option('extlist_paymerchants', $item);
			}
			update_option('pn_extended', $extended);
		}

		if($step == '1_24'){
			$list = get_option('extlist_merchants');
			if(!is_array($list)){ $list = array(); }
			$ms = array();
			$ids = array();
			foreach($list as $list_k => $list_v){
				$ms[is_isset($list_v, 'script')] = $list_k;
				$ids[$list_k] = $list_k;
			}

			$m = get_option('merchants_data');
			if(!is_array($m)){
				$merch_data = get_option('merch_data');
				if(!is_array($merch_data)){ $merch_data = array(); }

				foreach($merch_data as $k => $v){
					if(isset($ms[$k])){
						$merch_data[$ms[$k]] = $v;
					} 
					if(!isset($ids[$k])){
						unset($merch_data[$k]);
					}	
				}	
				update_option('merchants_data', $merch_data);
			}
		}
		
		if($step == '1_25'){
			$list = get_option('extlist_paymerchants');
			if(!is_array($list)){ $list = array(); }
			$ms = array();
			$ids = array();
			foreach($list as $list_k => $list_v){
				$ms[is_isset($list_v, 'script')] = $list_k;
				$ids[$list_k] = $list_k;
			}

			$m = get_option('paymerchants_data');
			if(!is_array($m)){
				$merch_data = get_option('paymerch_data');
				if(!is_array($merch_data)){ $merch_data = array(); }

				foreach($merch_data as $k => $v){
					if(isset($ms[$k])){
						$merch_data[$ms[$k]] = $v;
					} 
					if(!isset($ids[$k])){
						unset($merch_data[$k]);
					}	
				}	
				update_option('paymerchants_data', $merch_data);
			}
		}

		if($step == '1_26'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."directions");
			if($query == 1){
				
				$list = get_option('extlist_merchants');
				if(!is_array($list)){ $list = array(); }
				$m_ins = array();
				foreach($list as $list_k => $list_v){
					$m_ins[is_isset($list_v, 'script')] = $list_k;
				}
				
				$list = get_option('extlist_paymerchants');
				if(!is_array($list)){ $list = array(); }
				$m_outs = array();
				foreach($list as $list_k => $list_v){
					$m_outs[is_isset($list_v, 'script')] = $list_k;
				}				
				
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					$array = array();
					$m_in = is_isset($data,'m_in');
					$m_in_arr = @unserialize($m_in);
					if(is_array($m_in_arr)){
						$nm = array();
						foreach($m_in_arr as $m){
							if(isset($m_ins[$m])){
								$nm[] = $m_ins[$m];
							}
						}
						$array['m_in'] = @serialize($nm);
					}
					$m_out = is_isset($data,'m_out');
					$m_out_arr = @unserialize($m_out);
					if(is_array($m_out_arr)){
						$nm = array();
						foreach($m_out_arr as $m){
							if(isset($m_outs[$m])){
								$nm[] = $m_outs[$m];
							}
						}
						$array['m_out'] = @serialize($nm);
					}
					
					$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'reserv_place'");
					if($query == 1){
						$array['reserv_place'] = str_replace('fres', 'dfilereserve', $data->reserv_place);
					}	
					
					if(count($array) > 0){
						$wpdb->update($wpdb->prefix ."directions", $array, array('id' => $data_id));
					}
				}				
			}
		}

		if($step == '1_27'){ /*****************/
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."recalc_bids");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."recalc_bids LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$id = $data->id;
					$array = array();

					$array['direction_id'] = is_isset($data, 'naps_id');
					$array['change_course'] = is_isset($data, 'enable_recalc');
					$array['course_minute'] = is_isset($data, 'cou_minute') + (is_isset($data, 'cou_hour') * 60);
					$array['sum_minute'] = is_isset($data, 'cou_minute') + (is_isset($data, 'cou_hour') * 60);
					$array['course_status'] = is_isset($data, 'statused');
					$array['sum_status'] = @serialize(array('techpay','coldpay','realpay','verify'));

					$cc_count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."recalculations WHERE id='$id'");
					if($cc_count == 0){
						$array['id'] = $id;
						$wpdb->insert($wpdb->prefix ."recalculations", $array);	
					} else {
						$wpdb->update($wpdb->prefix ."recalculations", $array, array('id'=> $id));	
					}			
				}
			}						
		}

		if($step == '1_28'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."currency");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."currency LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$data_id = $data->id;
					
					$array = array();
					$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."currency LIKE 'reserv_place'");
					if($query == 1){
						$array['reserv_place'] = str_replace('fres', 'cfilereserve', $data->reserv_place);
					}	
	
					if(count($array) > 0){
						$wpdb->update($wpdb->prefix ."currency", $array, array('id' => $data_id));
					}
				}				
			}
		}		
		
		if($step == '1_30'){	 /*****************/
			$tables = array(
				'adminpanelcaptcha','captcha','uv_accounts','bids','geoip_blackip','geoip_whiteip','geoip_country','geoip_iplist',
				'course_logs','blackbrokers_naps','recalc_bids','valuts_account',
			);
			foreach($tables as $tbl){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix . $tbl);
				if($query == 1){
					$wpdb->query("DROP TABLE ". $wpdb->prefix . $tbl);
				}	
			}
		}

		if($step == '2_1'){	 /*****************/
			$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."users LIKE 'admin_comment'");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."users LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$id = $data->ID;
					
					$admin_comment = pn_strip_input(is_isset($data, 'admin_comment'));
					if(strlen($admin_comment) > 0){
						$arr = array();
						$arr['comment_date'] = current_time('mysql');
						$arr['user_id'] = $data->ID;
						$arr['user_login'] = pn_strip_input($data->user_login);
						$arr['text_comment'] = pn_strip_input($data->admin_comment);
						$arr['itemtype'] = 'user';
						$arr['item_id'] = $data->ID;
						$wpdb->insert($wpdb->prefix.'comment_system', $arr);
						
						$array = array();
						$array['admin_comment'] = '';
						$wpdb->update($wpdb->prefix ."users", $array, array('ID'=> $id));	
					}
				} 
			}
		}
		
		if($step == '2_2'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."currency");
			if($query == 1){
				$currencies = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."currency LIMIT {$offset},{$limit}");
				$directions = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions");
				foreach($currencies as $currency){
					$currency_id = $currency->id;
					foreach($directions as $direction){
						$direction_id = $direction->id;
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."directions_order WHERE direction_id='$direction_id' AND c_id='$currency_id'");
						if($cc == 0){
							$arr = array(
								'direction_id' => $direction_id,
								'c_id' => $currency_id,
							);
							$wpdb->insert($wpdb->prefix.'directions_order', $arr);
						}
					}			
				}				
			}			
		}

		if($step == '2_3'){	 /*****************/
			$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."exchange_bids LIMIT {$offset},{$limit}");
			foreach($datas as $data){
				$id = $data->id;
					
				$comment_user = pn_strip_text(get_bids_meta($id,'comment_user'));
				if(strlen($comment_user) > 0){
					$arr = array();
					$arr['comment_date'] = current_time('mysql');
					$arr['user_id'] = $data->user_id;
					$arr['user_login'] = pn_strip_input($data->user_login);
					$arr['text_comment'] = $comment_user;
					$arr['itemtype'] = 'user_bid';
					$arr['item_id'] = $data->id;
					$wpdb->insert($wpdb->prefix.'comment_system', $arr);
					delete_bids_meta($id,'comment_user');
				}	
				$comment_admin = pn_strip_text(get_bids_meta($id,'comment_admin'));	
				if(strlen($comment_admin) > 0){
					$arr = array();
					$arr['comment_date'] = current_time('mysql');
					$arr['user_id'] = $data->user_id;
					$arr['user_login'] = pn_strip_input($data->user_login);
					$arr['text_comment'] = $comment_admin;
					$arr['itemtype'] = 'admin_bid';
					$arr['item_id'] = $data->id;
					$wpdb->insert($wpdb->prefix.'comment_system', $arr);
					delete_bids_meta($id,'comment_admin');
				}		
			} 
		}

		if($step == '2_4'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."currency_accounts");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."currency_accounts LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$id = $data->id;	
					$text_comment = pn_strip_input(is_isset($data, 'text_comment'));
					if(strlen($text_comment) > 0){
						$arr = array();
						$arr['comment_date'] = current_time('mysql');
						$arr['text_comment'] = $text_comment;
						$arr['itemtype'] = 'curracc';
						$arr['item_id'] = $data->id;
						$wpdb->insert($wpdb->prefix.'comment_system', $arr);	
					}
				} 
			}
		}	
		
		if($step == '2_5'){
			$opts = array('pn_bcparser_courses','pn_bestchange_courses','pn_parser_pairs','pn_curs_parser','blackbrokers_courses','pn_directions_filedata','pn_fcourse_courses');
			foreach($opts as $option_name){
				$parts = get_option($option_name.'_parts');
				$parts = intval($parts);
				$s = 0;
				while($s++<$parts){
					delete_option($option_name.'_p'. $s);
				}
				delete_option($option_name.'_parts');
			}
		}

		if($step == '2_6'){ /*****************/
			$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."exchange_bids LIMIT {$offset},{$limit}");
			foreach($datas as $data){
				$id = $data->id;
				bid_hashdata($id, $data, '');
			}						
		}		
		
		if($step == '3_1'){
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."bidstatus");
			if($query == 1){
				$colors = array('#ff3c00','#fc6d41','#dbdd0a','#31dd0a','#0adddb','#810add');
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."bidstatus LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$id = $data->id;	
					$bg_color = $data->bg_color;
					if(!strstr($bg_color,'#')){
						$arr = array();
						$arr['bg_color'] = is_isset($colors, $bg_color);
						$wpdb->update($wpdb->prefix ."bidstatus", $arr, array('id'=> $id));
					}
				} 
			}
		}

		if($step == '3_2'){ /*****************/
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."recalcs");
			if($query == 1){
				$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."recalcs LIMIT {$offset},{$limit}");
				foreach($datas as $data){
					$id = $data->id;
					$array = array();

					$array['direction_id'] = is_isset($data, 'direction_id');
					$array['change_course'] = is_isset($data, 'change_course');
					$array['change_sum'] = is_isset($data, 'change_sum');
					$array['course_minute'] = is_isset($data, 'cou_minute') + (is_isset($data, 'cou_hour') * 60);
					$array['sum_minute'] = is_isset($data, 'cou_minute') + (is_isset($data, 'cou_hour') * 60);
					$array['course_status'] = is_isset($data, 'statused');
					$array['sum_status'] = @serialize(array('techpay','coldpay','realpay','verify'));

					$cc_count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."recalculations WHERE id='$id'");
					if($cc_count == 0){
						$array['id'] = $id;
						$wpdb->insert($wpdb->prefix ."recalculations", $array);	
					} else {
						$wpdb->update($wpdb->prefix ."recalculations", $array, array('id'=> $id));	
					}			
				}
			}						
		}		
		
		if($step == '3_3'){	 /*****************/
			$tables = array(
				'currency_codes_course_logs','direction_course_logs','blackbrokers','direction_blackbroker',
			);
			foreach($tables as $tbl){
				$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix . $tbl);
				if($query == 1){
					$wpdb->query("DROP TABLE ". $wpdb->prefix . $tbl);
				}	
			}
		}
		
		if($step == '3_4'){	 /*****************/
			$arrs = array(
				'psys' => array('auto_status','create_date','edit_date','edit_user_id'),
				'currency_codes' => array('auto_status','create_date','edit_date','edit_user_id'),
				'currency' => array('auto_status','create_date','edit_date','edit_user_id','xml_value','currency_status','psys_id','currency_code_id','reserv_order','t1_1','t1_2'),
				'currency_meta' => array('item_id'),
				'currency_custom_fields' => array('auto_status','create_date','edit_date','edit_user_id','status','vid','currency_id','cf_order_give','cf_order_get'),
				'cf_currency' => array('currency_id','cf_id'),
				'direction_custom_fields' => array('auto_status','create_date','edit_date','edit_user_id','status','cf_order'),
			); 

			foreach($arrs as $bd => $index){
				$query = $wpdb->query("CHECK TABLE {$wpdb->prefix}{$bd}");
				if($query == 1){
					$indexes = $wpdb->get_results("SHOW INDEX FROM {$wpdb->prefix}{$bd}");
					$hased = array();
					foreach($indexes as $in){
						$hased[] = is_isset($in,'Column_name');
					}
					foreach($index as $index_key){
						$query = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}{$bd} LIKE '{$index_key}'");
						if ($query) {
							if(!in_array($index_key, $hased)){
								$wpdb->query("CREATE INDEX {$index_key} ON {$wpdb->prefix}{$bd}({$index_key});");
							}
						}	
					}
				}
			}
		}

		if($step == '3_5'){	 /*****************/
			$arrs = array(
				'cf_directions' => array('direction_id','cf_id'),
				'currency_reserv' => array('auto_status','create_date','edit_date','edit_user_id','currency_id','currency_code_id'),
				'directions' => array('auto_status','create_date','edit_date','edit_user_id','currency_id_give','currency_id_get','psys_id_give','psys_id_get','direction_name','site_order1','direction_status','to3_1'),
				'directions_meta' => array('item_id'),
				'exchange_bids' =>  array('direction_id','create_date','edit_date','status','currency_id_give','currency_id_get','currency_code_id_give','currency_code_id_get','psys_id_give','psys_id_get','hashed','m_in','m_out','ref_id','pcalc','user_id'),
				'bids_meta' => array('item_id'), 
				'advantages' => array('auto_status','create_date','edit_date','edit_user_id','site_order','status'), 
				'db_admin_logs' => array('item_id','tbl_name','trans_type','trans_date','user_id'), 
				'archive_exchange_bids' => array('archive_date','create_date','edit_date','bid_id','user_id','ref_id','currency_id_give','currency_id_get','status','direction_id','currency_code_id_give','currency_code_id_get','psys_id_give','psys_id_get'), 
				'auto_removal_bids' => array('direction_id','enable_autodel'),  
				'bcbroker_currency_codes' => array('currency_code_id'),
				'bcbroker_directions' => array('direction_id','currency_id_give','currency_id_get','status'),
			); 

			foreach($arrs as $bd => $index){
				$query = $wpdb->query("CHECK TABLE {$wpdb->prefix}{$bd}");
				if($query == 1){
					$indexes = $wpdb->get_results("SHOW INDEX FROM {$wpdb->prefix}{$bd}");
					$hased = array();
					foreach($indexes as $in){
						$hased[] = is_isset($in,'Column_name');
					}
					foreach($index as $index_key){
						$query = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}{$bd} LIKE '{$index_key}'");
						if ($query) {
							if(!in_array($index_key, $hased)){
								$wpdb->query("CREATE INDEX {$index_key} ON {$wpdb->prefix}{$bd}({$index_key});");
							}
						}	
					}
				}
			}
		}

		if($step == '3_6'){	 /*****************/
			$arrs = array(
				'bcc_logs' => array('bid_id'),
				'bestchange_currency_codes' => array('currency_code_id'),
				'bestchange_directions' => array('direction_id','currency_id_give','currency_id_get','status'),	
				'bid_logs' => array('createdate','bid_id','user_id','place','who'),	
				'bidstatus' => array('status_order'),	
				'blacklist' => array('meta_key'),
				'captch_site' => array('createdate','sess_hash'),
				'captch_ap' => array('createdate','sess_hash'),
				'currency_codes_courselogs' => array('create_date','user_id','currency_code_id','who'),
				'direction_courselogs' => array('create_date','user_id','direction_id','currency_id_give','currency_id_get','who'),
				'directions_order' => array('direction_id','c_id'),
				'user_discounts' => array('sumec'),
				'naps_dopsumcomis' => array('naps_id','sum_val'),
				'email_logs' => array('create_date','sum_val'),
				'user_fav' => array('user_id','menu_order'),
			); 

			foreach($arrs as $bd => $index){
				$query = $wpdb->query("CHECK TABLE {$wpdb->prefix}{$bd}");
				if($query == 1){
					$indexes = $wpdb->get_results("SHOW INDEX FROM {$wpdb->prefix}{$bd}");
					$hased = array();
					foreach($indexes as $in){
						$hased[] = is_isset($in,'Column_name');
					}
					foreach($index as $index_key){
						$query = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}{$bd} LIKE '{$index_key}'");
						if ($query) {
							if(!in_array($index_key, $hased)){
								$wpdb->query("CREATE INDEX {$index_key} ON {$wpdb->prefix}{$bd}({$index_key});");
							}
						}	
					}
				}
			}
		}

		if($step == '3_7'){	 /*****************/
			$arrs = array(
				'geoip_memory' => array('ip'),
				'maintrance' => array('operator_status'),
				'bids_operators' => array('createdate','user_id','bid_id'),
				'merchant_logs' => array('createdate','merchant'),
				'notice_head' => array('auto_status','create_date','edit_date','edit_user_id','notice_type','notice_display','datestart','dateend','op_status','status','site_order'),
				'schedule_operators' => array('auto_status','create_date','edit_date','edit_user_id','status','save_order'),
				'parser_pairs' => array('menu_order'),
				'parser_logs' => array('work_date','log_code','key_birg'),
				'partners' => array('auto_status','create_date','edit_date','edit_user_id', 'site_order','status'),
				'paymerchant_logs' => array('createdate','bid_id','merchant'),
				'partner_pers' => array('sumec'),
				'plinks' => array('user_id','pdate','user_hash'),
				'user_payouts' => array('auto_status','create_date','edit_date','edit_user_id', 'pay_date','user_id','currency_id','currency_code_id','status'),
				'recalculations' => array('direction_id'),
				'naps_reservcurs' => array('naps_id','sum_val'),
			); 

			foreach($arrs as $bd => $index){
				$query = $wpdb->query("CHECK TABLE {$wpdb->prefix}{$bd}");
				if($query == 1){
					$indexes = $wpdb->get_results("SHOW INDEX FROM {$wpdb->prefix}{$bd}");
					$hased = array();
					foreach($indexes as $in){
						$hased[] = is_isset($in,'Column_name');
					}
					foreach($index as $index_key){
						$query = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}{$bd} LIKE '{$index_key}'");
						if ($query) {
							if(!in_array($index_key, $hased)){
								$wpdb->query("CREATE INDEX {$index_key} ON {$wpdb->prefix}{$bd}({$index_key});");
							}
						}	
					}
				}
			}
		}

		if($step == '3_8'){	 /*****************/
			$arrs = array(
				'reviews' => array('auto_status','create_date','edit_date','edit_user_id','user_id','review_date','review_status','review_locale'),
				'reviews_meta' => array('item_id'),
				'naps_sumcurs' => array('naps_id','sum_val'),
				'telegram' => array('create_date','site_user_id','telegram_chat_id'),
				'telegram_logs' => array('create_date','type','place'),
				'uv_field' => array('fieldvid','locale','status','uv_order'),
				'uv_field_user' => array('user_id','uv_id','uv_field','fieldvid'),
				'verify_bids' => array('auto_status','create_date','edit_date','edit_user_id','user_id','locale','status'),
				'user_wallets' => array('auto_status','create_date','edit_date','edit_user_id','user_id','currency_id','verify'),
				'currency_accounts' => array('currency_id','status'),
				'uv_wallets' => array('create_date','user_id','currency_id','user_wallet_id','locale','status'),
				'uv_wallets_files' => array('user_id','uv_wallet_id'),
				'direction_reserve_requests' => array('request_date','direction_id','request_locale'),
				'pn_options' => array('meta_key','meta_key2'),
				'auth_logs' => array('auth_date','user_id','auth_status'),
				'archive_data' => array('auth_date','user_id','auth_status'),
			); 

			foreach($arrs as $bd => $index){
				$query = $wpdb->query("CHECK TABLE {$wpdb->prefix}{$bd}");
				if($query == 1){
					$indexes = $wpdb->get_results("SHOW INDEX FROM {$wpdb->prefix}{$bd}");
					$hased = array();
					foreach($indexes as $in){
						$hased[] = is_isset($in,'Column_name');
					}
					foreach($index as $index_key){
						$query = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}{$bd} LIKE '{$index_key}'");
						if ($query) {
							if(!in_array($index_key, $hased)){
								$wpdb->query("CREATE INDEX {$index_key} ON {$wpdb->prefix}{$bd}({$index_key});");
							}
						}	
					}
				}
			}
		}		

		if($step == '9_1'){	 /*****************/
			$result = get_curl_parser('https://premiumexchanger.com/migrate/step35.xml', array(), 'migration');
			if(!$result['err']){
				$out = $result['output'];
				if(is_string($out)){
					if(strstr($out, '<?xml')){
						$res = @simplexml_load_string($out);
						if(is_object($res)){
							foreach($res->item as $item){
								$arr = (array)$item;
								if(isset($arr['id'])){
									unset($arr['id']);
								}
								if(isset($arr['title_birg'])){
									$arr['title_birg'] = ctv_ml($arr['title_birg']);
								}	
								$wpdb->insert($wpdb->prefix . 'parser_pairs', $arr);
							}
						}
					}
				}
			}			
		}		
		
		do_action('migration_action', $step);
		
		$log['status'] = 'success';	
		$log['status_text'] = __('Ok!','pn');		
		
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! Insufficient privileges','pn');
	}
	
	echo json_encode($log);
	exit;	
}