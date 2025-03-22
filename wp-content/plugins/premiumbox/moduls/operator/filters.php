<?php
if( !defined( 'ABSPATH')){ exit(); } 

add_filter('all_noticehead_addform','statuswork_all_noticehead_addform', 10, 2);
function statuswork_all_noticehead_addform($options, $data){
	
	$notice_type = intval(is_isset($data, 'notice_type'));
	if($notice_type == 0){
		$class_1 = '';
		$class_2 = 'pn_hide';
	} else {
		$class_1 = 'pn_hide';
		$class_2 = '';			
	}	
	
	$statused = array();
	$statused['-1'] = '--'. __('Any status','pn') .'--';
	$status_operator = apply_filters('status_operator', array());
	if(is_array($status_operator)){
		foreach($status_operator as $key => $val){
			$statused[$key] = $val;
		}
	}
	$array = array(
		'op_status' => array( 
			'view' => 'select',
			'title' => __('Status of operator','pn'),
			'options' => $statused,
			'default' => is_isset($data, 'op_status'),
			'name' => 'op_status',
			'work' => 'int',
			'class' => 'thevib thevib1 '. $class_2,
		),
	);	
	
	$options = pn_array_insert($options, 'datetime', $array);
	
	return $options;
}
  
add_filter('all_noticeheader_addform_post','statuswork_all_noticeheader_addform_post', 10, 2);
function statuswork_all_noticeheader_addform_post($array, $last_data){
	$array['op_status'] = intval(is_param_post('op_status'));
	return $array;
}

add_action('wp_dashboard_setup', 'statuswork_wp_dashboard_setup' );
function statuswork_wp_dashboard_setup() {
global $premiumbox;	
	if(intval($premiumbox->get_option('operator_type')) == 0){
		wp_add_dashboard_widget('statuswork_dashboard_widget', __('Work status','pn'), 'statuswork_dashboard_widget_function');
	}
}
  
function statuswork_dashboard_widget_function(){
global $premiumbox;		
	$status_operator = apply_filters('status_operator',array());
	$operator = $premiumbox->get_option('operator');
?>
<select id="statuswork" name="statuswork" autocomplete="off">
	<?php 
	if(is_array($status_operator)){
		foreach($status_operator as $key => $title){ 
		?>
			<option value="<?php echo $key; ?>" <?php selected($operator,$key); ?>><?php echo $title; ?></option>
		<?php 
		}
	}
	?>
</select>
<?php
}

add_action('pn_adminpage_js_dashboard','statuswork_adminpage_js_dashboard');
function statuswork_adminpage_js_dashboard(){
global $premiumbox;	
	if(intval($premiumbox->get_option('operator_type')) == 0){
?>
    $('#statuswork').on('change', function(){ 
		var id = $(this).val();
		var param='id='+id;
		$('#statuswork').prop('disabled',true);
        $.ajax({
			type: "POST",
			url: "<?php the_pn_link('statuswork_change', 'post'); ?>",
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{
				$('#statuswork').prop('disabled',false);				
			}
        });
	
        return false;
    });
<?php	
	}
}

add_action('premium_action_statuswork_change', 'pn_premium_action_statuswork_change');
function pn_premium_action_statuswork_change(){
global $premiumbox;	

	only_post();
	
	if(current_user_can('read') and intval($premiumbox->get_option('operator_type')) == 0){	
		$id = intval(is_param_post('id'));
		$premiumbox->update_option('operator','',$id);	
	}
} 

add_filter('globalajax_admin_data','operator_globalajax_data');
add_filter('globalajax_wp_data','operator_globalajax_data');
function operator_globalajax_data($log){
global $premiumbox;		

	if(intval($premiumbox->get_option('operator_type')) == 1){
		if(current_user_can('administrator') or current_user_can('pn_bids')){
			update_option('operator_time', current_time('timestamp'));
		}
	}
	
	return $log;
}

add_action('wp_footer','statuswork_wp_footer');
function statuswork_wp_footer(){
global $premiumbox;	
	
	if(intval($premiumbox->get_option('statuswork','show_button')) == 1){
		$operator = get_operator_status();
		$status = 'status_op'.$operator;
		
		$text = pn_strip_input(ctv_ml($premiumbox->get_option('statuswork','text'.$operator)));
		$link = pn_strip_input(ctv_ml($premiumbox->get_option('statuswork','link'.$operator)));

		$style = 'toleft';
		if($premiumbox->get_option('statuswork','location') == 1){
			$style = 'toright';
		}
		
		$date_format = get_option('date_format');
		$time_format = get_option('time_format');
		$date = current_time("{$date_format}, {$time_format}");
		$date = apply_filters('statuswork_now_date', $date);
?>
<?php if($link){ ?>
	<a href="<?php echo $link; ?>" class="statuswork_div <?php echo $status; ?> <?php echo $style; ?>">
<?php } else { ?>
	<div class="statuswork_div <?php echo $status; ?> <?php echo $style; ?>">
<?php } ?>
	<div class="statuswork_div_ins">
		<div class="statuswork">
			<div class="statuswork_ins">
				<div class="statuswork_title"><span><?php echo $text; ?></span></div>
				<div class="statuswork_date"><span><?php echo $date; ?></span></div>
			</div>	
		</div>
	</div>
<?php if($link){ ?>
	</a>
<?php } else { ?>
	</div>
<?php } ?>
<?php
	}
} 