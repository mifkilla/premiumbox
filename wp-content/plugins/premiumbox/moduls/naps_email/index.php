<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]E-mail verification[:en_US][ru_RU:]E-mail верификация[:ru_RU]
description: [en_US:]Verification through e-mail for From account, To account, Phone number fields[:en_US][ru_RU:]E-mail верификация для полей Со счета, На счет, Номер телефона[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_filter('list_user_notify','list_user_notify_napsemail', 10, 2);
function list_user_notify_napsemail($places, $place){
	if($place == 'email'){
		$places['napsemail'] = __('Verification through e-mail','pn');
	}
	return $places;
}

add_filter('list_notify_tags_napsemail','def_mailtemp_tags_napsemail');
function def_mailtemp_tags_napsemail($tags){
	$tags['code'] = array(
		'title' => __('Code','pn'),
		'start' => '[code]',
	);
	return $tags;
}

if(!function_exists('list_tabs_direction_verify')){
	add_filter('list_tabs_direction', 'list_tabs_direction_verify');
	function list_tabs_direction_verify($list_tabs){
		$list_tabs['verify'] = __('Verification','pn');
		return $list_tabs;
	}
}

add_action('tab_direction_verify', 'napsemail_tab_direction_verify', 50, 2);
function napsemail_tab_direction_verify($data, $data_id){
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Verification through e-mail','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<select name="email_button" autocomplete="off">
					<?php 
					$sms_button = intval(get_direction_meta($data_id, 'email_button')); 
					?>						
					<option value="0" <?php selected($sms_button,0); ?>><?php _e('No','pn');?></option>
					<option value="1" <?php selected($sms_button,1); ?>><?php _e('Yes','pn');?></option>						
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<select name="email_button_verify" autocomplete="off">
					<?php 
					$sms_button_verify = intval(get_direction_meta($data_id, 'email_button_verify')); 
					?>						
					<option value="0" <?php selected($sms_button_verify,0); ?>><?php _e('Default','pn');?></option>
					<option value="1" <?php selected($sms_button_verify,1); ?>><?php _e('Account Send','pn');?></option>
					<option value="2" <?php selected($sms_button_verify,2); ?>><?php _e('Account Receive','pn');?></option>
					<option value="3" <?php selected($sms_button_verify,3); ?>><?php _e('E-mail','pn');?></option>					
				</select>
			</div>
		</div>		
	</div>	
<?php	
}  

add_action('item_direction_edit','item_direction_edit_napsemail'); 
add_action('item_direction_add','item_direction_edit_napsemail');
function item_direction_edit_napsemail($data_id){
	$button = intval(is_param_post('email_button'));
	update_direction_meta($data_id, 'email_button', $button);
	$button_verify = intval(is_param_post('email_button_verify'));
	update_direction_meta($data_id, 'email_button_verify', $button_verify);
} 

add_action('admin_menu', 'admin_menu_napsemail');
function admin_menu_napsemail(){
global $premiumbox;		
	add_submenu_page("pn_moduls", __('Verification through e-mail settings','pn'), __('Verification through e-mail settings','pn'), 'administrator', "pn_napsemail", array($premiumbox, 'admin_temp'));
}

add_action('pn_adminpage_title_pn_napsemail', 'def_adminpage_title_pn_napsemail');
function def_adminpage_title_pn_napsemail($page){
	_e('Verification through e-mail','pn');
}

add_action('pn_adminpage_content_pn_napsemail','pn_admin_content_pn_napsemail');
function pn_admin_content_pn_napsemail(){
global $wpdb, $premiumbox;

	$form = new PremiumForm();

	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Verification through e-mail settings','pn'),
		'submit' => __('Save','pn'),
	);
	$options['vid'] = array(
		'view' => 'select',
		'title' => __('Code type','pn'),
		'options' => array('0'=>__('Digits','pn'),'1'=>__('Letters','pn')),
		'default' => $premiumbox->get_option('napsemail','vid'),
		'name' => 'vid',
		'work' => 'int',
	);	
	$options['sendto'] = array(
		'view' => 'select',
		'title' => __('Send e-mail to','pn'),
		'options' => array('0'=>__('All users','pn'),'1'=>__('Newcomer','pn')),
		'default' => $premiumbox->get_option('napsemail','sendto'),
		'name' => 'sendto',
		'work' => 'int',
	);
	$options['time_check'] = array(
		'view' => 'input',
		'title' => __('Timeout (seconds)','pn'),
		'default' => $premiumbox->get_option('napsemail','time_check'),
		'name' => 'time_check',
		'work' => 'int',
	);	
	$options['max_check'] = array(
		'view' => 'input',
		'title' => __('Max amount of resended e-mail','pn'),
		'default' => $premiumbox->get_option('napsemail','max_check'),
		'name' => 'max_check',
		'work' => 'int',
	);	
	$options['field'] = array(
		'view' => 'select',
		'title' => __('Verification option','pn'),
		'options' => array('0'=>__('Account Send','pn'),'1'=>__('Account Receive','pn'),'2'=>__('E-mail','pn')),
		'default' => $premiumbox->get_option('napsemail','field'),
		'name' => 'field',
		'work' => 'int',
	);	
	$params_form = array(
		'filter' => 'pn_napsemail_options',
		'method' => 'ajax',
		'button_title' => __('Save','pn'),
	);
	$form->init_form($params_form, $options);	  
}  

add_action('premium_action_pn_napsemail','def_premium_action_pn_napsemail');
function def_premium_action_pn_napsemail(){
global $wpdb, $premiumbox;	

	only_post();
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$vid = intval(is_param_post('vid'));
	$premiumbox->update_option('napsemail', 'vid', $vid);
	
	$field = intval(is_param_post('field'));
	$premiumbox->update_option('napsemail', 'field', $field);	
	
	$sendto = intval(is_param_post('sendto'));
	$premiumbox->update_option('napsemail', 'sendto', $sendto);

	$max_check = intval(is_param_post('max_check'));
	$premiumbox->update_option('napsemail', 'max_check', $max_check);	
	
	$time_check = intval(is_param_post('time_check'));
	$premiumbox->update_option('napsemail', 'time_check', $time_check);	

	$back_url = is_param_post('_wp_http_referer');
	$back_url .= '&reply=true';
			
	$form->answer_form($back_url);
}  

function get_napsemail($bid_data='', $bid_id='', $repeat=''){
global $wpdb, $premiumbox;

	$info = array(
		'status' => 'success',
		'status_code' => 0,
		'status_text' => __('Resent e-mail','pn'),
		'next' => '-1',
	);

	$repeat = intval($repeat);
	
	if(!isset($bid_data->id)){
		$bid_id = intval($bid_id);
		$bid_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE id='$bid_id'");
	}	
	if(isset($bid_data->id)){	
		$direction_id = $bid_data->direction_id;
	
		$checker_count = intval(get_bids_meta($bid_id, 'email_checker_count')); /* кол-во отправленных смс */
		$max_check = intval($premiumbox->get_option('napsemail','max_check'));
		if($max_check < 1){ $max_check = 1; }

		$word = pn_strip_input(get_bids_meta($bid_id, 'email_word'));
		if(!$word){ /* если нет слова */
			$word = get_rand_word(10, $premiumbox->get_option('napsemail','vid'));
			update_bids_meta($bid_id, 'email_word', $word);
		}		
		
		$user_email = '';
		$field_now = intval(get_direction_meta($direction_id, 'email_button_verify'));
		if($field_now == 0){
			$field = intval($premiumbox->get_option('napsemail','field'));
			if($field == 0){
				$user_email = pn_strip_input($bid_data->account_give);
			} elseif($field == 1){
				$user_email = pn_strip_input($bid_data->account_get);
			} elseif($field == 2){	
				$user_email = pn_strip_input($bid_data->user_email);
			}
		} elseif($field_now == 1){
			$user_email = pn_strip_input($bid_data->account_give);
		} elseif($field_now == 2){
			$user_email = pn_strip_input($bid_data->account_get);
		} elseif($field_now == 3){
			$user_email = pn_strip_input($bid_data->user_email);
		}		
		
		if(is_email($user_email)){
			if($checker_count < $max_check){
				$time = current_time('timestamp');
				$check_second = intval($premiumbox->get_option('napsemail','time_check'));
				if($check_second < 1){ $check_second = 60; }
				
				$checker_time = intval(get_bids_meta($bid_id, 'email_checker_time'));
				
				$send = 0;
				
				if(!$checker_time){
					$send = 1;
					$next_time = $time + $check_second;
				} else {
					$next_time = $checker_time + $check_second;
					if($time > $next_time and $repeat == 0){
						$send = 1;
						$next_time = $time + $check_second;
					}
				}
				
				$next = $next_time - $time;
				if($next < 0){ $next = 0; }
				$info['next'] = $next; 
				if(($checker_count+1) == $max_check and $repeat == 0){
					$info['next'] = '-1';
				}
				
				if($send == 1){

					update_bids_meta($bid_id, 'email_checker_time', $time);
					$checker_count = intval(get_bids_meta($bid_id, 'email_checker_count')) + 1;
					update_bids_meta($bid_id, 'email_checker_count', $checker_count);
							
					$notify_tags = array();
					$notify_tags['[sitename]'] = pn_site_name();
					$notify_tags['[code]'] = $word;
					$notify_tags = apply_filters('notify_tags_napsemail', $notify_tags);		

					$user_send_data = array(
						'user_email' => $user_email,
					);	
					$user_send_data = apply_filters('user_send_data', $user_send_data, 'napsemail', $bid_data);
					$result_mail = apply_filters('premium_send_message', 0, 'napsemail', $notify_tags, $user_send_data, $bid_data->bid_locale);				
				
				} else {
					$info['status'] = 'error';
					$info['status_code'] = 1;
					$info['status_text'] = sprintf(__('Sending is possible not earlier than %1s seconds','pn'), $next);
				}												
			} else {
				$info['status'] = 'error';
				$info['status_code'] = 1;
				$info['status_text'] = sprintf(__('You have been sent the maximum number of e-mail (%1s of %2s)','pn'), $checker_count, $max_check);				
			}						
		} else {
			$info['status'] = 'error';
			$info['status_code'] = 1;
			$info['status_text'] = __('E-mail not specified','pn');			
		}		
	} else {
		$info['status'] = 'error';
		$info['status_code'] = 1;
		$info['status_text'] = __('Order does not exist','pn');		
	}
		return $info;
}

function is_napsemail_check($bid_data='', $bid_id=''){
global $premiumbox, $wpdb;
	
	$check = 0;
	
	if(!isset($bid_data->id)){
		$bid_id = intval($bid_id);
		$bid_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE id='$bid_id'");
	}	
	if(isset($bid_data->id)){
		$in_status = array('new','techpay');
		if(in_array($bid_data->status, $in_status)){
			$new_user_option = intval($premiumbox->get_option('napsemail','sendto'));
			if($new_user_option == 0 or isset($bid_data->new_user) and $bid_data->new_user == 1 and $new_user_option == 1){
				$direction_id = $bid_data->direction_id;
				$button = intval(get_direction_meta($direction_id, 'email_button'));
				if($button == 1){
					$bid_id = $bid_data->id;
					$checker = intval(get_bids_meta($bid_id, 'email_checker'));
					if($checker != 1){
						$check = 1;
					}
				}
			}
		}
	}

	return $check;
}

add_filter('status_instruction', 'napsemail_status_instruction', 10, 7);
function napsemail_status_instruction($ind, $name, $direction, $vd1, $vd2, $m_in='', $m_out=''){
	if($m_in and $m_in == 'napsemail'){
		return 0;
	}
	return $ind;
}

add_filter('merchant_pay_button_visible','napsemail_merchant_pay_button_visible', 2, 4);
function napsemail_merchant_pay_button_visible($ind, $m_in, $item, $direction){
	if($ind == 1){
		if($m_in and $m_in == 'napsemail'){ 
			return 0;
		}
	}
	return $ind;
}

add_filter('get_merchant_id','napsemail_get_merchant_id', 0, 3);
function napsemail_get_merchant_id($m_in, $direction, $bids_data){
	if(!$m_in){
		if(is_napsemail_check($bids_data)){
			return 'napsemail';
		}
	}
	return $m_in;
}

add_action('before_bidaction_payedbids', 'napsemail_before_bidaction_payedbids', 0);
add_action('before_bidaction_payedmerchant', 'napsemail_before_bidaction_payedbids', 0);
function napsemail_before_bidaction_payedbids($bids_data){
	if(is_napsemail_check($bids_data)){
		$url = get_bids_url($bids_data->hashed);
		wp_redirect($url);
		exit;					
	}	
}

add_filter('merchant_formstep_after','napsemail_merchant_formstep_after', 10, 5);
function napsemail_merchant_formstep_after($html, $m_in, $direction, $vd1, $vd2){
global $premiumbox, $bids_data;	
	if($m_in and $m_in == 'napsemail'){
		$send_info = get_napsemail($bids_data, $bids_data->id, 1);
		$next = intval(is_isset($send_info, 'next'));
		
		$html = '
		<div class="block_smsbutton napsemail_block">
			<div class="block_smsbutton_ins">
				<div class="block_smsbutton_label">
					<div class="block_smsbutton_label_ins">
						'. __('Enter code specified in e-mail:','pn') .'
					</div>
				</div>
				<div class="block_smsbutton_action">
					<input type="text" name="" maxlength="10" placeholder="'. __('Enter code','pn') .'" id="napsemail_text" value="" />
					<input type="submit" name="" data-id="'. $bids_data->id .'" id="napsemail_send" value="'. __('Confirm code','pn') .'" />';
					if($next != '-1'){
						$dis = 'disabled="disabled"';
						if($next == 0){
							$dis = '';
						}
						$html .= '<input type="submit" name="" data-id="'. $bids_data->id .'" data-next="'. $next .'" '. $dis .' id="napsemail_reload" value="'. __('Resend','pn') .'" />';
					}
					$html .= '
						<div class="clear"></div>
				</div>
			</div>
		</div>
		';			
	}
	return $html;
} 

add_action('premium_js','premium_js_napsemail');
function premium_js_napsemail(){
global $premiumbox;	
?>	
jQuery(function($){ 
	if($('.napsemail_block').length > 0){
	
		function interval_napsemail(){
			var ch_sec = parseInt($('#napsemail_reload').attr('data-next'));
			var now = ch_sec - 1;
			if(now > 1){
				$('#napsemail_reload').attr('data-next', now);
				$('#napsemail_reload').val('<?php _e('Resend','pn'); ?> ('+ now +')');
				$('#napsemail_reload').prop('disabled', true);
			} else {
				$('#napsemail_reload').val('<?php _e('Resend','pn'); ?>');
				$('#napsemail_reload').attr('data-next', 0);
				$('#napsemail_reload').prop('disabled', false);
			}
		}
		setInterval(interval_napsemail, 1000);
		
		$(document).on('click', '#napsemail_reload', function(){
			if(!$(this).prop('disabled')){
				
				var id = $(this).attr('data-id');
				var thet = $(this);
				thet.prop('disabled', true);
				var param='id=' + id;
				$.ajax({
					type: "POST",
					url: "<?php echo get_pn_action('resend_napsemail_bids');?>",
					dataType: 'json',
					data: param,
					error: function(res, res2, res3){
						<?php do_action('pn_js_error_response', 'ajax'); ?>
					},			
					success: function(res)
					{
						if(res['status'] == 'success'){
							$('#napsemail_reload').attr('data-next', res['next']);
							if(res['next'] == '-1'){
								$('#napsemail_reload').remove();
							}
						} 

						<?php do_action('pn_js_alert_response'); ?>
						
						if(res['status'] == 'error'){
							thet.prop('disabled', false);
						}
					}
				});
			}
		
			return false;
		});
		
		$(document).on('click', '#napsemail_send', function(){
			if(!$(this).prop('disabled')){
				
				var id = $(this).attr('data-id');
				var txt = $('#napsemail_text').val();
				var thet = $(this);
				thet.prop('disabled', true);

				var param='id=' + id + '&txt=' + txt;
				$.ajax({
					type: "POST",
					url: "<?php echo get_pn_action('save_napsemail_bids');?>",
					dataType: 'json',
					data: param,
					error: function(res, res2, res3){
						<?php do_action('pn_js_error_response', 'ajax'); ?>
					},			
					success: function(res)
					{
						if(res['status'] == 'success'){
							window.location.href = '';
						} 
						if(res['status'] == 'error'){
							<?php do_action('pn_js_alert_response'); ?>
							thet.prop('disabled', false);
						}
					}
				});
		
			}
		
			return false;
		});		
	}
});		
<?php	
}

add_action('premium_siteaction_resend_napsemail_bids', 'def_premium_siteaction_resend_napsemail_bids');
function def_premium_siteaction_resend_napsemail_bids(){
global $or_site_url, $wpdb, $premiumbox;	
	
	only_post();
	
	header('Content-Type: application/json; charset=utf-8');
	
	$log = array();
	$log['response'] = '';
	$log['status'] = '';
	$log['status_text'] = '';
	$log['status_code'] = 0;
	
	$premiumbox->up_mode('post');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	$bid_id = intval(is_param_post('id'));
	if($bid_id){
		if(is_napsemail_check('', $bid_id)){
			$log = get_napsemail('', $bid_id, 0);
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('E-mail sending error','pn');			
		}
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('E-mail sending error','pn');		
	}
	
	echo json_encode($log);
	exit;
}

add_action('premium_siteaction_save_napsemail_bids', 'def_premium_siteaction_save_napsemail_bids');
function def_premium_siteaction_save_napsemail_bids(){
global $or_site_url, $wpdb, $premiumbox;	
	
	only_post();
	
	header('Content-Type: application/json; charset=utf-8');
	
	$log = array();
	$log['response'] = '';
	$log['status'] = '';
	$log['status_text'] = '';
	$log['status_code'] = 0;
	
	$premiumbox->up_mode('post');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	$bid_id = intval(is_param_post('id'));
	$txt = strtoupper(is_param_post('txt'));
	if($bid_id and $txt){
		if(is_napsemail_check('', $bid_id)){
			$word = pn_strip_input(get_bids_meta($bid_id, 'email_word'));
			if($word and $word == $txt){
				update_bids_meta($bid_id, 'email_checker', 1);
				
				$log['status'] = 'success';
				$log['status_code'] = 0;		
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('You have entered the wrong code','pn');
			}
		} else {
			$log['status'] = 'success';
			$log['status_code'] = 0;			
		}
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('You have not entered the code','pn');		
	}
	
	echo json_encode($log);
	exit;
}