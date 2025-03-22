<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Contacts export[:en_US][ru_RU:]Экспорт контактов[:ru_RU]
description: [en_US:]Contacts export[:en_US][ru_RU:]Экспорт контактов[:ru_RU]
version: 2.2
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

if(is_admin()){
	add_action('admin_menu', 'admin_menu_cexp', 100);
	function admin_menu_cexp(){
	global $premiumbox;		
		add_submenu_page('all_users', __('Export contacts with exchange bids','pn'), __('Export contacts with exchange bids','pn'), 'administrator', 'pn_cexp', array($premiumbox, 'admin_temp'));  	
	}

	add_action('pn_adminpage_title_pn_cexp', 'def_adminpage_title_pn_cexp');
	function def_adminpage_title_pn_cexp($page){
		_e('Export contacts','pn');
	} 

	add_action('pn_adminpage_content_pn_cexp','def_adminpage_content_pn_cexp');
	function def_adminpage_content_pn_cexp(){
	global $wpdb;
	?>
	<div class="premium_body">
		<form method="post" target="_blank" action="<?php the_pn_link('cexp'); ?>">
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
							'name' => __('First name','pn'),
							'email' => __('E-mail','pn'),
							'phone' => __('Mobile phone no.','pn'),
							'skype' => __('Skype','pn'),					
							'telegram' => __('Telegram','pn'),
						);
						foreach($array as $key => $val){
							$checked = 0;
							$scroll_lists[] = array(
								'title' => $val,
								'checked' => $checked,
								'value' => $key,
							);
						}
						echo get_check_list($scroll_lists, 'data[]');
						?>
						
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>	
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Unique key','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
					
						<?php
						$scroll_lists = array();
						
						$array = array(
							'name' => __('First name','pn'),
							'email' => __('E-mail','pn'),
							'phone' => __('Mobile phone no.','pn'),
							'skype' => __('Skype','pn'),					
							'telegram' => __('Telegram','pn'),
						);
						foreach($array as $key => $val){
							$checked = 0;
							$scroll_lists[] = array(
								'title' => $val,
								'checked' => $checked,
								'value' => $key,
							);
						}
						echo get_check_list($scroll_lists, 'key[]');
						?>
						
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>
			<?php
			$query = $wpdb->query("CHECK TABLE ".$wpdb->prefix . "archive_exchange_bids");
			if($query == 1){
			?>
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Archived orders','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
					
						<div><label><input type="checkbox" name="archive" value="1" /> <?php _e('Include archived orders in list','pn'); ?></label></div>
						
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>	
			<?php } ?>
			
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

	add_action('premium_action_cexp','def_premium_action_cexp');
	function def_premium_action_cexp(){
	global $wpdb;	

		pn_only_caps(array('administrator'));

		$where = '';
		$datestart = is_pn_date(is_param_post('date1'));
		if($datestart){
			$dstart = get_pn_time($datestart, 'Y-m-d H:i:s');
			$where .= " AND create_date >= '$dstart'";
		}
			
		$dateend = is_pn_date(is_param_post('date2'));
		if($dateend){
			$dend = get_pn_time($dateend, 'Y-m-d H:i:s');
			$where .= " AND create_date <= '$dend'";
		}	
			
		$my_dir = wp_upload_dir();
		$path = $my_dir['basedir'].'/';		
			
		$file = $path.'contactexport-'. date('Y-m-d-H-i') .'.csv';           
		$fs = @fopen($file, 'w');
		
		$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE status != 'auto' $where");
		
		$query = $wpdb->query("CHECK TABLE ".$wpdb->prefix . "archive_exchange_bids");
		if($query == 1){
			$archive = intval(is_param_post('archive'));
			if($archive){
				$aitems = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."archive_exchange_bids WHERE id > 0 $where");
				$nitems = array_merge($items, $aitems);
			} else {
				$nitems = $items;
			}
		} else {
			$nitems = $items;
		}
		
		$data = is_param_post('data');
		$key = is_param_post('key');
		if(!is_array($key)){ $key = array(); }
			
		$keys1 = $keys2 = $keys3 = $keys4 = $keys5 = array();
		$k1 = $k2 = $k3 = $k4 = $k5 = 0;
		if(in_array('name', $key)){
			$k1 = 1;
		}
		if(in_array('email', $key)){
			$k2 = 1;
		}			
		if(in_array('phone', $key)){
			$k3 = 1;
		}
		if(in_array('skype', $key)){
			$k4 = 1;
		}
		if(in_array('telegram', $key)){
			$k5 = 1;
		}
		
		$content = '';
			
		if(is_array($data)){
					
			if(in_array('name', $data)){
				$content .= get_cptgn(__('First name','pn')).';';
			}
			if(in_array('email', $data)){
				$content .= get_cptgn(__('E-mail','pn')).';';
			}
			if(in_array('phone', $data)){
				$content .= get_cptgn(__('Mobile phone no.','pn')).';';
			}		
			if(in_array('skype', $data)){
				$content .= get_cptgn(__('Skype','pn')).';';
			}
			if(in_array('telegram', $data)){
				$content .= get_cptgn(__('Telegram','pn')).';';
			}	

			$content .= "\n";
				
			foreach($nitems as $item){
				$email = get_cptgn(str_replace(';','',is_isset($item,'user_email')));
				$tel = get_cptgn(str_replace(';','',is_isset($item,'user_phone')));
				$fio = str_replace(';','',is_isset($item,'last_name') .' '. is_isset($item,'first_name') .' '. is_isset($item,'second_name'));
				$fio = get_cptgn($fio);
				$skype = get_cptgn(str_replace(';','',is_isset($item,'user_skype')));
				$telegram = get_cptgn(str_replace(';','', is_isset($item,'user_telegram')));
					
				$line = '';
					
				if(!in_array($fio, $keys1) and !in_array($email, $keys2) and !in_array($tel, $keys3) and !in_array($skype, $keys4) and !in_array($telegram, $keys5)){
						
					if(in_array('name', $data)){
						$line .= $fio.';';
						if($k1 == 1){
							$keys1[] = $fio;
						}
					}
					if(in_array('email', $data)){
						$line .= $email.';';
						if($k2 == 1){
							$keys2[] = $email;
						}						
					}
					if(in_array('phone', $data)){
						$line .= $tel.';';
						if($k3 == 1){
							$keys3[] = $tel;
						}						
					}
					if(in_array('skype', $data)){
						$line .= $skype.';';
						if($k4 == 1){
							$keys4[] = $skype;
						}						
					}
					if(in_array('telegram', $data)){
						$line .= $telegram.';';
						if($k5 == 1){
							$keys5[] = $telegram;
						}						
					}				
					$line .= "\n";
					
				}
					
				$content .= $line;
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
			@unlink($file);
			exit;
		} else {
			pn_display_mess(__('Error! Unable to create file!','pn'));
		}	
	}
}	