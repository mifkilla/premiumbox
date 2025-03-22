<?php
if( !defined( 'ABSPATH')){ exit(); }
 
add_filter('placed_form', 'placed_form_zreserv');
function placed_form_zreserv($placed){
	$placed['reservform'] = __('Reserve request','pn');
	return $placed;
}

add_filter('reservform_filelds', 'def_reservform_filelds');
function def_reservform_filelds($items){
	$ui = wp_get_current_user();

	$items['sum'] = array(
		'name' => 'sum',
		'title' => '',
		'req' => 1,
		'value' => '',
		'type' => 'input',
		'atts' => array('placeholder' => __('Required amount', 'pn')),
	);
	$items['email'] = array(
		'name' => 'email',
		'title' => '',
		'req' => 1,
		'value' => is_email(is_isset($ui,'user_email')),
		'type' => 'input',
		'atts' => array('class'=> 'notclea', 'placeholder' => __('E-mail', 'pn')),
	);		
	$items['comment'] = array(
		'name' => 'comment',
		'title' => '',
		'req' => 0,
		'value' => '', 
		'type' => 'text',
		'atts' => array('class'=> 'notclea', 'placeholder' => __('Comment', 'pn')),
	);		
	
	return $items;
}

add_filter('replace_array_reservform', 'def_replace_array_reservform', 10, 3);
function def_replace_array_reservform($array, $prefix, $place=''){
global $wpdb, $premiumbox;
	
	$fields = get_form_fields('reservform', $place);
	
	$filter_name = '';
	if($place == 'widget'){
		$prefix = 'widget_'. $prefix;
		$filter_name = 'widget_';
	}
	$html = prepare_form_fileds($fields, $filter_name . 'reserv_form_line', $prefix);	
	
	$array = array(
		'[form]' => '<form method="post" class="ajax_post_form" action="'. get_pn_action('reservform') .'">',
		'[/form]' => '</form>',
		'[result]' => '<div class="resultgo"></div>',
		'[html]' => $html,
		'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="'. $prefix .'_submit" value="'. __('Send a request', 'pn') .'" />',
	);	
	
	return $array;
}
 
add_action('premium_js','premium_js_zreserv');
function premium_js_zreserv(){	
	if(is_enable_zreserve()){
?>	
jQuery(function($){ 

	$(document).on('click', '.js_reserv', function(){
		$(document).JsWindow('show', {
			window_class: 'update_window',
			title: '<?php _e('Request to reserve','pn'); ?> <?php echo '"<span id="reserv_box_title"></span>"'; ?>',
			content: $('.reserv_box_html').html(),
			insert_div: '.reserv_box',
			shadow: 1
		});		
		
		var title = $(this).attr('data-title');
        var id = $(this).attr('data-id');		
		$('#reserv_box_title').html(title);	
		$('#reserv_box_id').attr('value',id);				
		
	    return false;
	});	
	
});	
<?php	
	}
}

add_action('wp_footer','wp_footer_zreserv');
function wp_footer_zreserv(){
    if(is_enable_zreserve()){
		
		$array = get_form_replace_array('reservform', 'rb');
		
		$temp = '
		<div class="reserv_box_html" style="display: none;">
			[result]		
			[html]	
			<div class="rb_line">[submit]</div>
		</div>';	
		
		$temp .= '
		[form]
			<input type="hidden" name="id" id="reserv_box_id" value="0" />
			
			<div class="reserv_box"></div>
		[/form]
		';
		
		$temp = apply_filters('zreserv_form_temp', $temp);
		echo get_replace_arrays($array, $temp);	

    } 
}

add_action('premium_siteaction_reservform', 'def_premium_siteaction_reservform');
function def_premium_siteaction_reservform(){
global $wpdb, $premiumbox;	
	
	only_post();
	
	header('Content-Type: application/json; charset=utf-8'); 
	
	$log = array();
	$log['response'] = '';
	$log['status'] = '';
	$log['status_code'] = 0;
	$log['status_text'] = '';
	$log['errors'] = array();
	
	$premiumbox->up_mode('post');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	$parallel_error_output = get_parallel_error_output();
	
	if(is_enable_zreserve()){
	
		$log = apply_filters('before_ajax_form_field', $log, 'reservform');
		$log = apply_filters('before_ajax_reservform', $log);
		
		$field_errors = array();
		
		$id = intval(is_param_post('id'));
		$sum = is_sum(is_param_post('sum'),8);
		$email = is_email(is_param_post('email'));
		$comment = pn_maxf_mb(pn_strip_input(is_param_post('comment')),500);
		
		if($sum <= 0){
			$field_errors[] = __('Error! Requested amount is lesser than zero','pn');	
		}
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			if(!$email){
				$field_errors[] = __('Error! You have not entered e-mail','pn');
			}	
		}		
		if(count($field_errors) == 0 or $parallel_error_output == 1){
			$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE id='$id' AND direction_status IN('1','2') AND auto_status='1'");
			if(!isset($direction->id)){
				$field_errors[] = __('Error! Direction does not exist','pn');
			}	
		}		

		if(count($field_errors) == 0){

			$last = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."direction_reserve_requests WHERE user_email = '$email' AND direction_id='$id'");
					
			$array = array();
			$array['request_date'] = current_time('mysql');
			$array['direction_id'] = $id;
			$array['direction_title'] = pn_strip_input($direction->tech_name);
			$array['user_email'] = $email;
			$array['request_comment'] = $comment;
			$array['request_amount'] = $sum;
			$array['request_locale'] = get_locale();
					
			if(isset($last->id)){
				$wpdb->update($wpdb->prefix ."direction_reserve_requests", $array, array('id'=>$last->id));
			} else {
				$wpdb->insert($wpdb->prefix ."direction_reserve_requests", $array);
			}
				
			$notify_tags = array();
			$notify_tags['[sitename]'] = pn_site_name();
			$notify_tags['[sum]'] = $array['request_amount'];
			$notify_tags['[direction]'] = $array['direction_title'];
			$notify_tags['[email]'] = $array['user_email'];
			$notify_tags['[comment]'] = $comment;
			$notify_tags['[ip]'] = pn_real_ip();
			$notify_tags = apply_filters('notify_tags_zreserv_admin', $notify_tags, $ui);
			
			$user_send_data = array();	
			$result_mail = apply_filters('premium_send_message', 0, 'zreserv_admin', $notify_tags, $user_send_data); 										
					
			$log['status'] = 'success';
			$log['clear'] = 1;
			$log['status_text'] = __('Request has been successfully created','pn');
			
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = join("<br />", $field_errors);
		}
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! You have not entered e-mail','pn');		
	}
	
	echo json_encode($log);
	exit;
}