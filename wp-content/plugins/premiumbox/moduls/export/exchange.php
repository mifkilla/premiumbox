<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_export_exchange', 'pn_admin_title_pn_export_exchange');
	function pn_admin_title_pn_export_exchange($page){
		_e('Exchanges export','pn');
	} 

	add_action('pn_adminpage_content_pn_export_exchange','def_pn_admin_content_pn_export_exchange');
	function def_pn_admin_content_pn_export_exchange(){
	global $wpdb;
	?>
	<div class="premium_body">	
		<form method="post" target="_blank" action="<?php the_pn_link('export_exchange','post'); ?>">
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Start date','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<input type="text" name="date1" class="pn_datepicker" autocomplete="off" value="" />
							<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>		
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('End date','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<input type="text" name="date2" class="pn_datepicker" autocomplete="off" value="" />
							<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>		
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Select data','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<?php
						$scroll_lists = array();
						
						$array = array(
							'id' => __('Identifier','pn'),
							'create_date' => __('Date','pn'),
							'edit_date' => __('Edit date','pn'),
							'cgive' => __('Currency Send','pn'),
							'cget' => __('Currency Receive','pn'),
							'course_give' => __('Rate Send','pn'),
							'course_get' => __('Rate Receive','pn'),
							'sum1' => __('Amount To send','pn'),
							'dop_com1' => __('Add. fees amount Send','pn'),
							'sum1dc' => __('Amount Send with add. fees','pn'),
							'com_ps1' => __('PS fees Send','pn'),
							'sum1c' => __('Amount Send with add. fees and PS fees','pn'),
							'sum1r' => __('Amount Send for reserve','pn'),
							'sum2t' => __('Amount at the Exchange Rate','pn'),
							'sum2' => __('Amount (discount included)','pn'),
							'dop_com2' => __('Add. fees amount Receive','pn'),
							'sum2dc' => __('Amount Receive with add. fees','pn'),
							'com_ps2' => __('PS fees Receive','pn'),
							'sum2c' => __('Amount Receive with add. fees and PS fees','pn'),
							'sum2r' => __('Amount Receive for reserve','pn'),
							'exsum' => __('Amount in internal currency needed for exhange','pn'),
							'profit' => __('Profit','pn'),
							'account_give' => __('Account To send','pn'),
							'account_get' => __('Account To receive','pn'),
							'to_account' => __('Merchant account','pn'),
							'from_account' => __('Automatic payout account','pn'),
							'trans_in' => __('Merchant transaction ID','pn'),
							'trans_out' => __('Auto payout transaction ID','pn'),
							'last_name' => __('Last name','pn'),
							'first_name' => __('First name','pn'),
							'second_name' => __('Second name','pn'),
							'user_email' => __('E-mail','pn'),
							'user_phone' => __('Mobile phone no.','pn'),
							'user_telegram' => __('Telegram','pn'),
							'user_skype' => __('Skype','pn'),
							'user' => __('User','pn'),
							'user_discount' => __('User discount','pn'),
							'user_discount_sum' => __('User discount amount','pn'),
							'user_ip' => __('User IP','pn'),
							'hash' => __('Hash','pn'),
							'link' => __('Link','pn'),
							'status' => __('Status','pn'),	
							'locale' => __('Language','pn'),
						);
						$array = apply_filters('list_export_bids', $array);
						foreach($array as $key => $val){
							$checked = 0;
							$scroll_lists[] = array(
								'title' => $val,
								'checked' => $checked,
								'value' => $key,
							);
						}
						echo get_check_list($scroll_lists, 'data[]','','',1);
						?>
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>		
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Bid status','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<?php
						$scroll_lists = array();
						
						$bid_status_list = apply_filters('bid_status_list',array());
						foreach($bid_status_list as $key => $val){
							$checked = 0;
							$scroll_lists[] = array(
								'title' => $val,
								'checked' => $checked,
								'value' => $key,
							);
						}
						echo get_check_list($scroll_lists, 'bs[]','','',1);
						?>
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>		
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<input type="submit" name="" class="button" value="<?php _e('Download','pn'); ?>" />
							<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>
		</form>	
	</div>	
	<?php
	} 

	add_action('premium_action_export_exchange','def_premium_action_export_exchange');
	function def_premium_action_export_exchange(){
	global $wpdb;	

		only_post();
		pn_only_caps(array('administrator','pn_export_exchange'));			
		
		$where = '';
		$datestart = is_pn_date(is_param_post('date1'));
		if($datestart){
			$dstart = get_pn_time($datestart, 'Y-m-d H:i:s');
			$where .= " AND edit_date >= '$dstart'";
		}
			
		$dateend = is_pn_date(is_param_post('date2'));
		if($dateend){
			$dend = get_pn_time($dateend, 'Y-m-d H:i:s');
			$where .= " AND edit_date <= '$dend'";
		}	
		
		$bs = is_param_post('bs');
		$in = create_data_for_bd($bs, 'status');
		if($in){
			$where .= " AND status IN($in)";
		}
		
		$my_dir = wp_upload_dir();
		$path = $my_dir['basedir'].'/';		
			
		$file = $path . 'bidsexport-'. date('Y-m-d-H-i') .'.csv';           
		$fs = @fopen($file, 'w');
		
		$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE status != 'auto' $where ORDER BY id DESC");
		 
		$data = is_param_post('data');
			
		$content = '';
			
		$array = array(
			'id' => __('Identifier','pn'),
			'create_date' => __('Date','pn'),
			'edit_date' => __('Edit date','pn'),
			'cgive' => __('Currency Send','pn'),
			'cget' => __('Currency Receive','pn'),
			'course_give' => __('Rate Send','pn'),
			'course_get' => __('Rate Receive','pn'),
			'sum1' => __('Amount To send','pn'),
			'dop_com1' => __('Add. fees amount Send','pn'),
			'sum1dc' => __('Amount Send with add. fees','pn'),
			'com_ps1' => __('PS fees Send','pn'),
			'sum1c' => __('Amount Send with add. fees and PS fees','pn'),
			'sum1r' => __('Amount Send for reserve','pn'),
			'sum2t' => __('Amount at the Exchange Rate','pn'),
			'sum2' => __('Amount (discount included)','pn'),
			'dop_com2' => __('Add. fees amount Receive','pn'),
			'sum2dc' => __('Amount Receive with add. fees','pn'),
			'com_ps2' => __('PS fees Receive','pn'),
			'sum2c' => __('Amount Receive with add. fees and PS fees','pn'),
			'sum2r' => __('Amount Receive for reserve','pn'),
			'exsum' => __('Amount in internal currency needed for exhange','pn'),
			'profit' => __('Profit','pn'),
			'account_give' => __('Account To send','pn'),
			'account_get' => __('Account To receive','pn'),
			'to_account' => __('Merchant account','pn'),
			'from_account' => __('Automatic payout account','pn'),
			'trans_in' => __('Merchant transaction ID','pn'),
			'trans_out' => __('Auto payout transaction ID','pn'),
			'last_name' => __('Last name','pn'),
			'first_name' => __('First name','pn'),
			'second_name' => __('Second name','pn'),
			'user_email' => __('E-mail','pn'),
			'user_phone' => __('Mobile phone no.','pn'),
			'user_skype' => __('Skype','pn'),
			'user_telegram' => __('Telegram','pn'),
			'user' => __('User','pn'),
			'user_discount' => __('User discount','pn'),
			'user_discount_sum' => __('User discount amount','pn'),
			'user_ip' => __('User IP','pn'),
			'hash' => __('Hash','pn'),
			'link' => __('Link','pn'),
			'status' => __('Status','pn'),	
			'locale' => __('Language','pn'),
		);
		$array = apply_filters('list_export_bids', $array);
		
		if(is_array($data)){
				
			$en = array();
			$csv_title = '';
			$csv_key = '';
			foreach($array as $k => $v){
				if(in_array($k, $data)){
					$en[] = $k;
					$csv_title .= '"'. get_cptgn($v) .'";';
				} 
			}	
				
			$content .= $csv_title."\n";

			if(count($en) > 0){
				foreach($items as $item){
					$line = '';
						
					foreach($en as $key){
						$line .= '"';
							
						if($key == 'id'){
							$line .= $item->id;
						} elseif($key == 'create_date'){
							$line .= get_pn_time($item->create_date,'d.m.Y H:i');
						} elseif($key == 'edit_date'){
							$line .= get_pn_time($item->edit_date,'d.m.Y H:i');
						} elseif($key == 'cgive'){
							$line .= get_cptgn(ctv_ml($item->psys_give) .' '. $item->currency_code_give);
						} elseif($key == 'cget'){
							$line .= get_cptgn(ctv_ml($item->psys_get) .' '. $item->currency_code_get);
						} elseif($key == 'account_give'){
							$line .= get_cptgn($item->account_give);
						} elseif($key == 'account_get'){
							$line .= get_cptgn($item->account_get);
						} elseif($key == 'to_account'){
							$line .= get_cptgn($item->to_account);	
						} elseif($key == 'from_account'){
							$line .= get_cptgn($item->from_account); 
						} elseif($key == 'trans_in'){
							$line .= get_cptgn($item->trans_in);
						} elseif($key == 'trans_out'){
							$line .= get_cptgn($item->trans_out);							
						} elseif($key == 'last_name'){
							$line .= get_cptgn($item->last_name);
						} elseif($key == 'first_name'){
							$line .= get_cptgn($item->first_name);
						} elseif($key == 'second_name'){
							$line .= get_cptgn($item->second_name);
						} elseif($key == 'user_email'){
							$line .= get_cptgn($item->user_email);							
						} elseif($key == 'user_phone'){
							$line .= get_cptgn($item->user_phone);
						} elseif($key == 'user_skype'){
							$line .= get_cptgn($item->user_skype);
						} elseif($key == 'user_telegram'){
							$line .= get_cptgn($item->user_telegram);							
						} elseif($key == 'user'){			
							$line .= get_cptgn($item->user_login);
						} elseif($key == 'user_discount'){
							$line .= $item->user_discount;
						} elseif($key == 'user_discount_sum'){
							$line .= $item->user_discount_sum;
						} elseif($key == 'user_ip'){
							$line .= get_cptgn($item->user_ip);
						} elseif($key == 'hash'){
							$line .= $item->hashed;
						} elseif($key == 'link'){
							$line .= get_bids_url($item->hashed);
						} elseif($key == 'status'){
							$line .= get_cptgn(get_bid_status($item->status));
						} elseif($key == 'locale'){	
							$line .= get_lang_key($item->bid_locale);
						} else {
							$line .= rep_dot(is_isset($item,$key));
						}						

						$line .= '";';
					}
						
					$line .= "\n";
					$content .= $line;
				}
			}
		}
			
		@fwrite($fs, $content);
		@fclose($fs);	
		
		if(is_file($file)) {
			if (ob_get_level()) {
				ob_end_clean();
			}
			$lang = get_locale();
			if($lang == 'ru_RU'){
				header('Content-Type: text/html; charset=CP1251');
			} else {
				header('Content-Type: text/html; charset=UTF8');
			}
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);
			unlink($file);
			exit;
		} else {
			pn_display_mess(__('Error! Unable to create file!','pn'));
		}	
	}
}	