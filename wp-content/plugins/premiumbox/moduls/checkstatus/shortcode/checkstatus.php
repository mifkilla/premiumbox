<?php
if( !defined( 'ABSPATH')){ exit(); }
 
add_action('pn_adminpage_quicktags_page','pn_adminpage_quicktags_page_checkstatus');
function pn_adminpage_quicktags_page_checkstatus(){
?>
edButtons[edButtons.length] = 
new edButton('premium_checkstatus', '<?php _e('Check order status','pn'); ?>','[checkstatus_form]');
<?php	
}

add_filter('replace_array_checkstatusform', 'def_replace_array_checkstatusform', 10, 3);
function def_replace_array_checkstatusform($array, $prefix, $place=''){
global $wpdb, $premiumbox;
	
	$fields = get_form_fields('checkstatusform', $place);
	
	$filter_name = '';
	if($place == 'widget'){
		$prefix = 'widget_'. $prefix;
		$filter_name = 'widget_';
	}
	$html = prepare_form_fileds($fields, $filter_name . 'checkstatus_form_line', $prefix);	
	
	$array = array(
		'[form]' => '<form method="post" class="ajax_post_form" action="'. get_pn_action('checkstatusform') .'">',
		'[/form]' => '</form>',
		'[result]' => '<div class="resultgo"></div>',
		'[html]' => $html,
		'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="'. $prefix .'_submit" value="'. __('Send a message', 'pn') .'" />',
	);	
	
	return $array;
}

function pn_checkstatus_form_shortcode($atts) {
	$temp = '';
	
 	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	$array = get_form_replace_array('checkstatusform', 'checkstatus');	
	
	$temp = '
	<div class="checkstatus_div_wrap">
	[form]

		<div class="checkstatus_div_title">
			<div class="checkstatus_div_title_ins">
				'. __('Check order status','pn') .'
			</div>
		</div>
	
		<div class="checkstatus_div">
			<div class="checkstatus_div_ins">
				
				[html]
				
				<div class="checkstatus_line has_submit">
					[submit]
				</div>
				
				[result]
				
			</div>
		</div>
	
	[/form]
	</div>
	';
	
	$temp = apply_filters('checkstatus_form_temp',$temp);
	$temp = get_replace_arrays($array, $temp);
	
	return $temp;
}
add_shortcode('checkstatus_form', 'pn_checkstatus_form_shortcode');

add_action('premium_siteaction_checkstatusform', 'def_premium_siteaction_checkstatusform');
function def_premium_siteaction_checkstatusform(){
global $or_site_url, $premiumbox, $wpdb;	
	
	only_post();
	
	header('Content-Type: application/json; charset=utf-8');
	
	$log = array();
	$log['response'] = '';
	$log['status'] = '';
	$log['status_code'] = 0;
	$log['status_text'] = '';
	
	$premiumbox->up_mode('post');
	
	$log = apply_filters('before_ajax_form_field', $log, 'checkstatusform');
	$log = apply_filters('before_ajax_checkstatusform', $log);
	
	$email = is_email(is_param_post('email'));
	$exchange_id = intval(is_param_post('exchange_id'));
	
	if($exchange_id > 0){
		$bid = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE status != 'auto' AND id = '$exchange_id'");		
		if(isset($bid->id)){
			$user_email = $bid->user_email;
			if($email == $user_email){
				$log['status'] = 'success';	
				$log['clear'] = 1;
				$log['status_text'] = __('You will be redirected to the order page now','pn');
				$log['url'] = get_bids_url($bid->hashed);
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! You have entered an invalid e-mail address','pn');				
			}
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! Order does not exist','pn');			
		}
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! You have not entered the order ID','pn');
	}
	
	echo json_encode($log);
	exit;
}