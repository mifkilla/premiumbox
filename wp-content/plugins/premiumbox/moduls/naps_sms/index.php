<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Verification by SMS code[:en_US][ru_RU:]SMS верификация[:ru_RU]
description: [en_US:]SMS verification for From account, To account, Phone number fields[:en_US][ru_RU:]SMS верификация для полей Со счета, На счет, Номер телефона[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if(!function_exists('list_tabs_direction_verify')){
	add_filter('list_tabs_direction', 'list_tabs_direction_verify');
	function list_tabs_direction_verify($list_tabs){
		$list_tabs['verify'] = __('Verification','pn');
		return $list_tabs;
	}
}

add_action('tab_direction_verify', 'napssms_tab_direction_verify', 51, 2);
function napssms_tab_direction_verify($data, $data_id){
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Verify by SMS code','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<select name="sms_button" autocomplete="off">
					<?php 
					$sms_button = intval(get_direction_meta($data_id, 'sms_button')); 
					?>						
					<option value="0" <?php selected($sms_button,0); ?>><?php _e('No','pn');?></option>
					<option value="1" <?php selected($sms_button,1); ?>><?php _e('Yes','pn');?></option>						
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<select name="sms_button_verify" autocomplete="off">
					<?php 
					$sms_button_verify = intval(get_direction_meta($data_id, 'sms_button_verify')); 
					?>						
					<option value="0" <?php selected($sms_button_verify,0); ?>><?php _e('Default','pn');?></option>
					<option value="1" <?php selected($sms_button_verify,1); ?>><?php _e('Account Send','pn');?></option>
					<option value="2" <?php selected($sms_button_verify,2); ?>><?php _e('Account Receive','pn');?></option>
					<option value="3" <?php selected($sms_button_verify,3); ?>><?php _e('Phone number','pn');?></option>					
				</select>
			</div>
		</div>		
	</div>	
<?php	
} 

add_action('item_direction_edit','item_direction_edit_napssms'); 
add_action('item_direction_add','item_direction_edit_napssms');
function item_direction_edit_napssms($data_id){
	$sms_button = intval(is_param_post('sms_button'));
	update_direction_meta($data_id, 'sms_button', $sms_button);
	$sms_button_verify = intval(is_param_post('sms_button_verify'));
	update_direction_meta($data_id, 'sms_button_verify', $sms_button_verify);	
}

add_action('admin_menu', 'admin_menu_napssms');
function admin_menu_napssms(){
global $premiumbox;		
	add_submenu_page("pn_moduls", __('Verify by SMS code settings','pn'), __('Verify by SMS code settings','pn'), 'administrator', "pn_napssms", array($premiumbox, 'admin_temp'));
}

add_action('pn_adminpage_title_pn_napssms', 'def_adminpage_title_pn_napssms');
function def_adminpage_title_pn_napssms($page){
	_e('Verification by SMS code','pn');
}

add_action('pn_adminpage_content_pn_napssms','pn_admin_content_pn_napssms');
function pn_admin_content_pn_napssms(){
global $wpdb, $premiumbox;

	$form = new PremiumForm();

	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Verify by SMS code settings','pn'),
		'submit' => __('Save','pn'),
	);
	$options['vid'] = array(
		'view' => 'select',
		'title' => __('Code type','pn'),
		'options' => array('0'=>__('Digits','pn'),'1'=>__('Letters','pn')),
		'default' => $premiumbox->get_option('napssms','vid'),
		'name' => 'vid',
		'work' => 'int',
	);	
	$options['sendto'] = array(
		'view' => 'select',
		'title' => __('Send SMS for','pn'),
		'options' => array('0'=>__('All users','pn'),'1'=>__('Newcomer','pn')),
		'default' => $premiumbox->get_option('napssms','sendto'),
		'name' => 'sendto',
		'work' => 'int',
	);
	$options['time_check'] = array(
		'view' => 'input',
		'title' => __('Timeout (seconds)','pn'),
		'default' => $premiumbox->get_option('napssms','time_check'),
		'name' => 'time_check',
		'work' => 'int',
	);	
	$options['max_check'] = array(
		'view' => 'input',
		'title' => __('Max amount of resended SMS','pn'),
		'default' => $premiumbox->get_option('napssms','max_check'),
		'name' => 'max_check',
		'work' => 'int',
	);	
	$options['field'] = array(
		'view' => 'select',
		'title' => __('Verification option','pn'),
		'options' => array('0'=>__('Account Send','pn'),'1'=>__('Account Receive','pn'),'2'=>__('Mobile phone no.','pn')),
		'default' => $premiumbox->get_option('napssms','field'),
		'name' => 'field',
		'work' => 'int',
	);	
	$tags = array(
		'code' => array(
			'title' => __('Code','pn'),
			'start' => '[code]',
		),
	);
	$options['text'] = array(
		'view' => 'editor',
		'title' => __('Text','pn'),
		'default' => $premiumbox->get_option('napssms','text'),
		'tags' => $tags,
		'rows' => '5',
		'name' => 'text',
		'work' => 'text',
		'word_count' => 1,
		'ml' => 1,
	);	
	$params_form = array(
		'filter' => 'pn_napssms_options',
		'method' => 'ajax',
		'button_title' => __('Save','pn'),
	);
	$form->init_form($params_form, $options);  
}  			

add_action('premium_action_pn_napssms','def_premium_action_pn_napssms');
function def_premium_action_pn_napssms(){
global $wpdb, $premiumbox;	

	only_post();
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$vid = intval(is_param_post('vid'));
	$premiumbox->update_option('napssms', 'vid', $vid);
	
	$field = intval(is_param_post('field'));
	$premiumbox->update_option('napssms', 'field', $field);	
	
	$sendto = intval(is_param_post('sendto'));
	$premiumbox->update_option('napssms', 'sendto', $sendto);

	$max_check = intval(is_param_post('max_check'));
	$premiumbox->update_option('napssms', 'max_check', $max_check);	
	
	$time_check = intval(is_param_post('time_check'));
	$premiumbox->update_option('napssms', 'time_check', $time_check);	

	$text = pn_strip_text(is_param_post_ml('text'));
	$premiumbox->update_option('napssms', 'text', $text);					

	$back_url = is_param_post('_wp_http_referer');
	$back_url .= '&reply=true';
			
	$form->answer_form($back_url);
} 

function get_napssms($bid_data='', $bid_id='', $repeat=''){
global $wpdb, $premiumbox;

	$info = array(
		'status' => 'success',
		'status_code' => 0,
		'status_text' => __('SMS was resent','pn'),
		'next' => '-1',
	);

	$repeat = intval($repeat);
	
	if(!isset($bid_data->id)){
		$bid_id = intval($bid_id);
		$bid_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE id='$bid_id'");
	}	
	if(isset($bid_data->id)){	
		$direction_id = $bid_data->direction_id;
	
		$checker_count = intval(get_bids_meta($bid_id, 'sms_checker_count')); /* кол-во отправленных смс */
		$max_check = intval($premiumbox->get_option('napssms','max_check'));
		if($max_check < 1){ $max_check = 1; }

		$word = pn_strip_input(get_bids_meta($bid_id, 'sms_word'));
		if(!$word){ /* если нет слова */
			$word = get_rand_word(6, $premiumbox->get_option('napssms','vid'));
			update_bids_meta($bid_id, 'sms_word', $word);
		}		
		
		$user_phone = '';
		$field_now = intval(get_direction_meta($direction_id, 'sms_button_verify'));
		if($field_now == 0){
			$field = intval($premiumbox->get_option('napssms','field'));
			if($field == 0){
				$user_phone = pn_strip_input($bid_data->account_give);
			} elseif($field == 1){
				$user_phone = pn_strip_input($bid_data->account_get);
			} elseif($field == 2){	
				 $user_phone = pn_strip_input($bid_data->user_phone);
			}
		} elseif($field_now == 1){
			$user_phone = pn_strip_input($bid_data->account_give);
		} elseif($field_now == 2){
			$user_phone = pn_strip_input($bid_data->account_get);
		} elseif($field_now == 3){
			$user_phone = pn_strip_input($bid_data->user_phone);
		}		
		
		if($user_phone){
			if($checker_count < $max_check){
				$time = current_time('timestamp');
				$check_second = intval($premiumbox->get_option('napssms','time_check'));
				if($check_second < 1){ $check_second = 60; }
				
				$checker_time = intval(get_bids_meta($bid_id, 'sms_checker_time'));
				
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

					update_bids_meta($bid_id, 'sms_checker_time', $time);
					$checker_count = intval(get_bids_meta($bid_id, 'sms_checker_count')) + 1;
					update_bids_meta($bid_id, 'sms_checker_count', $checker_count);
							
					$text_sms = pn_strip_input(ctv_ml($premiumbox->get_option('napssms','text')));
					$text_sms = str_replace('[code]',$word,$text_sms);
					if(!$text_sms){ $text_sms = $word; }
					
					$res = apply_filters('pn_sms_send', 0, $text_sms, $user_phone);				
				
				} else {
					$info['status'] = 'error';
					$info['status_code'] = 1;
					$info['status_text'] = sprintf(__('Sending is possible not earlier than %1s seconds','pn'), $next);
				}												
			} else {
				$info['status'] = 'error';
				$info['status_code'] = 1;
				$info['status_text'] = sprintf(__('You have been sent the maximum number of SMS (%1s of %2s)','pn'), $checker_count, $max_check);				
			}						
		} else {
			$info['status'] = 'error';
			$info['status_code'] = 1;
			$info['status_text'] = __('Mobile phone no. not specified','pn');			
		}		
	} else {
		$info['status'] = 'error';
		$info['status_code'] = 1;
		$info['status_text'] = __('Order does not exist','pn');		
	}
		return $info;
}

function is_napssms_check($bid_data, $bid_id=''){
global $premiumbox, $wpdb;

	$check = 0;
	
	if(!isset($bid_data->id)){
		$bid_id = intval($bid_id);
		$bid_data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE id='$bid_id'");
	}	
	if(isset($bid_data->id)){
		$in_status = array('new','techpay');
		if(in_array($bid_data->status, $in_status)){	
			$new_user_option = intval($premiumbox->get_option('napssms','sendto'));
			if($new_user_option == 0 or isset($bid_data->new_user) and $bid_data->new_user == 1 and $new_user_option == 1){
				$direction_id = $bid_data->direction_id;
				$button = intval(get_direction_meta($direction_id, 'sms_button'));
				if($button == 1){
					$bid_id = $bid_data->id;
					$checker = intval(get_bids_meta($bid_id, 'sms_checker'));
					if($checker != 1){
						$check = 1;
					}
				}
			}	
		}
	}
	
	return $check;
}

add_filter('status_instruction', 'napssms_status_instruction', 10, 7);
function napssms_status_instruction($ind, $name, $direction, $vd1, $vd2, $m_in='', $m_out=''){
	if($m_in and $m_in == 'napssms'){
		return 0;
	}
	return $ind;
}

add_filter('merchant_pay_button_visible','napssms_merchant_pay_button_visible', 2, 4);
function napssms_merchant_pay_button_visible($ind, $m_in, $item, $direction){
	if($ind == 1){
		if($m_in and $m_in == 'napssms'){ 
			return 0;
		}
	}
	return $ind;
}

add_filter('get_merchant_id','napssms_get_merchant_id', 0, 3);
function napssms_get_merchant_id($m_in, $direction, $bids_data){
	if(!$m_in){
		if(is_napssms_check($bids_data)){
			return 'napssms';
		}
	}
	return $m_in;
}

add_action('before_bidaction_payedbids', 'napssms_before_bidaction_payedbids', 1);
add_action('before_bidaction_payedmerchant', 'napssms_before_bidaction_payedbids', 1);
function napssms_before_bidaction_payedbids($bids_data){
global $premiumbox;	
	if(is_napssms_check($bids_data)){
		$url = get_bids_url($bids_data->hashed);
		wp_redirect($url);
		exit;					
	}
}

add_filter('merchant_formstep_after','napssms_merchant_formstep_after', 10, 5);
function napssms_merchant_formstep_after($html, $m_in, $direction, $vd1, $vd2){
global $premiumbox, $bids_data;	
	if($m_in and $m_in == 'napssms'){
		$send_info = get_napssms($bids_data, $bids_data->id, 1);
		$next = intval(is_isset($send_info, 'next'));
		
		$html = '
		<div class="block_smsbutton napssms_block">
			<div class="block_smsbutton_ins">
				<div class="block_smsbutton_label">
					<div class="block_smsbutton_label_ins">
						'. __('Enter code specified in SMS:','pn') .'
					</div>
				</div>
				<div class="block_smsbutton_action">
					<input type="text" name="" maxlength="10" placeholder="'. __('Enter code','pn') .'" id="napssms_text" value="" />
					<input type="submit" name="" data-id="'. $bids_data->id .'" id="napssms_send" value="'. __('Confirm code','pn') .'" />';
					if($next != '-1'){
						$dis = 'disabled="disabled"';
						if($next == 0){
							$dis = '';
						}
						$html .= '<input type="submit" name="" data-id="'. $bids_data->id .'" data-next="'. $next .'" '. $dis .' id="napssms_reload" value="'. __('Resend','pn') .'" />';
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

add_action('premium_js','premium_js_napssms');
function premium_js_napssms(){
global $premiumbox;	
?>	
jQuery(function($){ 
	if($('.napssms_block').length > 0){
	
		function interval_napssms(){
			var ch_sec = parseInt($('#napssms_reload').attr('data-next'));
			var now = ch_sec - 1;
			if(now > 1){
				$('#napssms_reload').attr('data-next', now);
				$('#napssms_reload').val('<?php _e('Resend','pn'); ?> ('+ now +')');
				$('#napssms_reload').prop('disabled', true);
			} else {
				$('#napssms_reload').val('<?php _e('Resend','pn'); ?>');
				$('#napssms_reload').attr('data-next', 0);
				$('#napssms_reload').prop('disabled', false);
			}
		}
		setInterval(interval_napssms, 1000);
		
		$(document).on('click', '#napssms_reload', function(){
			if(!$(this).prop('disabled')){
				
				var id = $(this).attr('data-id');
				var thet = $(this);
				thet.prop('disabled', true);
				var param='id=' + id;
				$.ajax({
					type: "POST",
					url: "<?php echo get_pn_action('resend_napssms_bids');?>",
					dataType: 'json',
					data: param,
					error: function(res, res2, res3){
						<?php do_action('pn_js_error_response', 'ajax'); ?>
					},			
					success: function(res)
					{
						if(res['status'] == 'success'){
							thet.attr('data-next', res['next']);
							if(res['next'] == '-1'){
								thet.remove();
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
		
		$(document).on('click', '#napssms_send', function(){
			if(!$(this).prop('disabled')){
				
				var id = $(this).attr('data-id');
				var txt = $('#napssms_text').val();
				var thet = $(this);
				thet.prop('disabled', true);

				var param='id=' + id + '&txt=' + txt;
				$.ajax({
					type: "POST",
					url: "<?php echo get_pn_action('save_napssms_bids');?>",
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

add_action('premium_siteaction_resend_napssms_bids', 'def_premium_siteaction_resend_napssms_bids');
function def_premium_siteaction_resend_napssms_bids(){
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
		if(is_napssms_check('', $bid_id)){
			$log = get_napssms('', $bid_id, 0);
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('E-mail sending error','pn');			
		}
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('SMS sending error','pn');		
	}
	
	echo json_encode($log);
	exit;
}

add_action('premium_siteaction_save_napssms_bids', 'def_premium_siteaction_save_napssms_bids');
function def_premium_siteaction_save_napssms_bids(){
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
		if(is_napssms_check('', $bid_id)){
			$word = pn_strip_input(get_bids_meta($bid_id, 'sms_word'));
			if($word and $word == $txt){
				update_bids_meta($bid_id, 'sms_checker', 1);
				
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