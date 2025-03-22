<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	
add_action('pn_adminpage_title_pn_add_directions', 'pn_admin_title_pn_add_directions');
function pn_admin_title_pn_add_directions(){
global $bd_data, $wpdb;	
	
	$data_id = 0;
	$item_id = intval(is_param_get('item_id'));
	$bd_data = '';
	
	if($item_id){
		$bd_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."directions WHERE id='$item_id'");
		if(isset($bd_data->id)){
			$data_id = $bd_data->id;
		}	
	}		
	
	if($data_id){
		_e('Edit exchange direction','pn');
	} else {
		_e('Add exchange direction','pn');
	}	
}

add_action('pn_adminpage_content_pn_add_directions','def_pn_admin_content_pn_add_directions');
function def_pn_admin_content_pn_add_directions(){
global $bd_data, $wpdb, $premiumbox;

	$form = new PremiumForm();

	$data_id = intval(is_isset($bd_data,'id'));
	if($data_id){
		$title = __('Edit exchange direction','pn');
	} else {
		$title = __('Add exchange direction','pn');
	}
	
	$title .= ' "<span id="title1"></span>-<span id="title2"></span>"';
	
	$back_menu = array();
	$back_menu['back'] = array(
		'link' => admin_url('admin.php?page=pn_directions'),
		'title' => __('Back to list','pn')
	);
	$back_menu['save'] = array(
		'link' => '#',
		'title' => __('Save','pn'),
		'atts' => 'class="savelink save_admin_ajax_form"',
	);	
	if($data_id){
		$back_menu['add'] = array(
			'link' => admin_url('admin.php?page=pn_add_directions'),
			'title' => __('Add new','pn')
		);			
		if(is_isset($bd_data,'direction_status') != 0 and is_isset($bd_data,'auto_status') == 1){
			$back_menu['direction_link'] = array(
				'link' => get_exchange_link($bd_data->direction_name),
				'title' => __('View','pn'),
				'atts' => 'target="_blank"',
			);			
		}
	}
	$form->back_menu($back_menu, $bd_data);
	
	$dir_c = is_course_direction($bd_data, '', '', 'admin');
	
	$list_tabs_direction = array(
		'tab1' => __('General settings','pn'), 
		'tab2' => __('Rate','pn') . ' <span class="one_tabs_submenu">[<span id="rate1">'. is_isset($dir_c,'give') .'</span> => <span id="rate2">'. is_isset($dir_c,'get') .'</span>]</span>',
		'tab4' => __('Payment systems fees','pn'),
		'tab5' => __('Exchange office fees','pn'),
		'tab6' => __('Exchange amount','pn'),
		'tab7' => __('Customer information','pn'),
		'tab8' => __('Limitations and checking','pn'),
		'tab9' => __('Custom fields','pn'),
	);
	if(current_user_can('administrator') or current_user_can('pn_directions_merchant')){
		$list_tabs_direction['tab11'] = __('Merchants and payouts','pn');
	}	
	
	$params_form = array(
		'key' => 'tab_direction',
		'method' => 'ajax',
		'hidden_data' => array('data_id' => $data_id),
		'page_title' => $title,
		'tabs' => apply_filters('list_tabs_direction', $list_tabs_direction),
		'button_title' => __('Save','pn'),
		'data' => $bd_data,
		'data_id' => $data_id,
	);
	$form->init_tab_form($params_form);	
?>
	
<script type="text/javascript">
jQuery(function($){
	
	function set_visible_title(){
		var direction_status = $('#direction_status').val();
		if(direction_status == 1){
			$('.add_tabs_pagetitle').removeClass('notactive');
		} else {
			$('.add_tabs_pagetitle').addClass('notactive');
		}
		
		var title1 = $('#currency_id_give option:selected').html().replace(new RegExp("-",'g'),'');
		var title2 = $('#currency_id_get option:selected').html().replace(new RegExp("-",'g'),'');
		$('#title1').html(title1);
		$('#title2').html(title2);
	}
	$('#direction_status, #currency_id_give, #currency_id_get').change(function(){
		set_visible_title();
	});
	set_visible_title();	
	
	function set_tech_title(){
		var title = $.trim($('.tech_name').val());
		if(title.length > 0){
			$('title').html(title);
		}
	}
	$('.tech_name').change(function(){
		set_tech_title();
	});
	set_tech_title();	
	
	function set_now_decimal(obj, dec){
		if(obj.length > 0){
			var sum = obj.val().replace(new RegExp(",",'g'),'.');
			var len_arr = sum.split('.');
			var len_data = len_arr[1];
			if(typeof len_data !== typeof undefined){
				var len = len_data.length;
				if(len > dec){
					var new_data = len_arr[0]+'.'+len_data.substr(0, dec);
					obj.val(new_data);
				}
			} else {
				var new_data = sum;
			}
		}
	}
	
	function set_valut_decimal(){
		var decimal1 = $('#currency_id_give option:selected').attr('data-decimal');
		var decimal2 = $('#currency_id_get option:selected').attr('data-decimal');
		set_now_decimal($('#course_give'), decimal1);
		set_now_decimal($('#course_get'), decimal2);
	}
	$('#direction_status, #currency_id_give, #currency_id_get').change(function(){
		set_valut_decimal();
	});
	$('#course_give, #course_get').change(function(){
		set_valut_decimal();
	});
	$('#course_give, #course_get').keyup(function(){
		set_valut_decimal();
	});	
	set_valut_decimal();		

});
</script>	
<?php
}  

add_action('premium_action_pn_add_directions','def_premium_action_pn_add_directions');
function def_premium_action_pn_add_directions(){
global $wpdb;

	only_post();
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator', 'pn_directions'));
	
	$data_id = intval(is_param_post('data_id'));
	
	$last_data = '';
	if($data_id > 0){
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id='$data_id'");
		if(!isset($last_data->id)){
			$data_id = 0;
		}
	}	
		
	$array = array();
	
	/* tab1 */
	$array['currency_id_give'] = $currency_id_give = intval(is_param_post('currency_id_give'));
	$array['currency_id_get'] = $currency_id_get = intval(is_param_post('currency_id_get'));
				
	if($currency_id_give == $currency_id_get){
		$form->error_form(__('Error! Send and Receive currency cannot be the same','pn'));
	}
			
	$xml_value1 = $xml_value2 = '';
	$title_value1 = $title_value2 = '';
	$status_currency1 = $status_currency2 = 0;
			
	$currency_data1 = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE auto_status = '1' AND id='$currency_id_give'");
	if(isset($currency_data1->id)){
		$array['psys_id_give'] = $currency_data1->psys_id;
		$xml_value1 = is_xml_value($currency_data1->xml_value);
		$title_value1 = get_currency_title($currency_data1);
		$status_currency1 = intval($currency_data1->currency_status);
	} else {
		$form->error_form(__('Error! Send currency does not exist','pn'));
	}
			
	$currency_data2 = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE auto_status = '1' AND id='$currency_id_get'");
	if(isset($currency_data2->id)){
		$array['psys_id_get'] = $currency_data2->psys_id;
		$xml_value2 = is_xml_value($currency_data2->xml_value);
		$title_value2 = get_currency_title($currency_data2);
		$status_currency2 = intval($currency_data2->currency_status);
	} else {
		$form->error_form(__('Error! Receive currency does not exist','pn'));
	}
	
	if($status_currency1 != 1){
		$form->error_form(__('Error! Send currency deactivated','pn'));
	}
	
	if($status_currency2 != 1){
		$form->error_form(__('Error! Receive currency deactivated','pn'));
	}	

	$array['direction_status'] = intval(is_param_post('direction_status'));
			
	$tech_name = pn_strip_input(is_param_post('tech_name'));
	if(!$tech_name){
		$tech_name = $title_value1 .' &rarr; '. $title_value2;
	}
	$array['tech_name'] = $tech_name;
			
	$direction_name = trim(is_param_post('direction_name'));
	if($direction_name){
		$direction_name = is_direction_permalink($direction_name);
	} 
	if(!$direction_name){
		$direction_permalink_temp = apply_filters('direction_permalink_temp','[xmlv1]_to_[xmlv2]');
		$direction_permalink_temp = str_replace('[xmlv1]',$xml_value1,$direction_permalink_temp);
		$direction_permalink_temp = str_replace('[xmlv2]',$xml_value2,$direction_permalink_temp);
		$direction_name = strtolower(is_direction_permalink($direction_permalink_temp));
	}		
			
	$array['direction_name'] = unique_direction_name($direction_name, $data_id);
	/* end tab1 */
			
	/* tab2 */			
	$course_give = is_sum(is_param_post('course_give'), intval($currency_data1->currency_decimal));
	$course_get = is_sum(is_param_post('course_get'), intval($currency_data2->currency_decimal));
			
	if($course_give <= 0){
		$course_give = 1;
	}
	if($course_get <= 0){
		$course_get = 1;
	}	
	$array['course_give'] = $course_give;
	$array['course_get'] = $course_get;
	$array['profit_sum1'] = is_sum(is_param_post('profit_sum1'));	
	$array['profit_pers1'] = is_sum(is_param_post('profit_pers1'));
	$array['profit_sum2'] = is_sum(is_param_post('profit_sum2'));	
	$array['profit_pers2'] = is_sum(is_param_post('profit_pers2'));			
	/* end tab2 */
			
	/* tab4 */
	$array['pay_com1'] = intval(is_param_post('pay_com1'));
	$array['pay_com2'] = intval(is_param_post('pay_com2'));
	$array['nscom1'] = intval(is_param_post('nscom1'));
	$array['nscom2'] = intval(is_param_post('nscom2'));
	$array['dcom1'] = intval(is_param_post('dcom1'));
	$array['dcom2'] = intval(is_param_post('dcom2'));	
	$array['com_sum1'] = is_sum(is_param_post('com_sum1'));	
	$array['com_pers1'] = is_sum(is_param_post('com_pers1'));
	$array['com_sum2'] = is_sum(is_param_post('com_sum2'));	
	$array['com_pers2'] = is_sum(is_param_post('com_pers2'));	
	$array['maxsum1com'] = is_sum(is_param_post('maxsum1com'));
	$array['maxsum2com'] = is_sum(is_param_post('maxsum2com'));
	$array['minsum1com'] = is_sum(is_param_post('minsum1com'));
	$array['minsum2com'] = is_sum(is_param_post('minsum2com'));			
	/* end tab4 */
			
	/* tab5 */
	$array['com_box_sum1'] = is_sum(is_param_post('com_box_sum1'));	
	$array['com_box_pers1'] = is_sum(is_param_post('com_box_pers1'));
	$array['com_box_min1'] = is_sum(is_param_post('com_box_min1'));				
	$array['com_box_sum2'] = is_sum(is_param_post('com_box_sum2'));	
	$array['com_box_pers2'] = is_sum(is_param_post('com_box_pers2'));
	$array['com_box_min2'] = is_sum(is_param_post('com_box_min2'));			
	/* end tab5 */
			
	/* tab6 */
	$array['min_sum1'] = is_sum(is_param_post('min_sum1'));
	$array['max_sum1'] = is_sum(is_param_post('max_sum1'));
	$array['min_sum2'] = is_sum(is_param_post('min_sum2'));
	$array['max_sum2'] = is_sum(is_param_post('max_sum2'));			
	/* end tab6 */
			
	/* tab11 */
	if(current_user_can('administrator') or current_user_can('pn_directions_merchant')){
		$m_arrs = is_param_post('m_in');
		if(!is_array($m_arrs)){ $m_arrs = array(); }
		$m_in = array();
		
			foreach($m_arrs as $m_arr){
				$m_arr = is_extension_name($m_arr);
				if($m_arr){
					$m_in[] = $m_arr;
				}
			}
		
		$array['m_in'] = @serialize($m_in);
		
		$m_arrs = is_param_post('m_out');
		if(!is_array($m_arrs)){ $m_arrs = array(); }
		$m_out = array();
		
			foreach($m_arrs as $m_arr){
				$m_arr = is_extension_name($m_arr);
				if($m_arr){
					$m_out[] = $m_arr;
				}
			}
			
		$array['m_out'] = @serialize($m_out);
	}
	/* end tab11 */
			
	/* tab8 */
	$array['enable_user_discount'] = intval(is_param_post('enable_user_discount'));
	$array['max_user_discount'] = is_sum(is_param_post('max_user_discount'));
	/* end tab8 */

	$ui = wp_get_current_user();
	$user_id = intval(is_isset($ui, 'ID'));

	$array['edit_date'] = current_time('mysql');
	$array['edit_user_id'] = $user_id;
	$array['auto_status'] = 1;	
	$array = apply_filters('pn_direction_addform_post', $array, $last_data);

	if($data_id){
		$res = apply_filters('item_direction_edit_before', pn_ind(), $data_id, $array, $last_data);
		if($res['ind'] == 1){
			$result = $wpdb->update($wpdb->prefix.'directions', $array, array('id' => $data_id));
			do_action('item_direction_edit', $data_id, $array, $last_data, $result);
		} else { $form->error_form(is_isset($res,'error')); }
	} else {
		$res = apply_filters('item_direction_add_before', pn_ind(), $array);
		if($res['ind'] == 1){
			$array['create_date'] = current_time('mysql');
			$result = $wpdb->insert($wpdb->prefix.'directions', $array);
			$data_id = $wpdb->insert_id;
			if($result){
				do_action('item_direction_add', $data_id, $array);
			}
		} else { $form->error_form(is_isset($res,'error')); }
	}	
			
	if($data_id){
		if(current_user_can('administrator') or current_user_can('pn_directions_merchant')){	
			$paymerch_data = array();
			$paymerch_data['m_in_max'] = is_sum(is_param_post('m_in_max'));
			$paymerch_data['m_in_max_month'] = is_sum(is_param_post('m_in_max_month'));
			$paymerch_data['m_in_max_sum'] = is_sum(is_param_post('m_in_max_sum'));
			$paymerch_data['m_out_realpay'] = intval(is_param_post('m_out_realpay'));
			$paymerch_data['m_out_verify'] = intval(is_param_post('m_out_verify')); 
			$paymerch_data['m_in_maxc_day'] = intval(is_param_post('m_in_maxc_day'));
			$paymerch_data['m_in_maxc_month'] = intval(is_param_post('m_in_maxc_month'));
			$paymerch_data['m_out_max'] = is_sum(is_param_post('m_out_max'));
			$paymerch_data['m_out_max_month'] = is_sum(is_param_post('m_out_max_month'));
			$paymerch_data['m_out_min_sum'] = is_sum(is_param_post('m_out_min_sum'));
			$paymerch_data['m_out_max_sum'] = is_sum(is_param_post('m_out_max_sum'));
			$paymerch_data['m_out_timeout'] = is_sum(is_param_post('m_out_timeout'));
			$paymerch_data['m_out_timeout_user'] = intval(is_param_post('m_out_timeout_user'));
			update_direction_meta($data_id, 'paymerch_data', $paymerch_data);
		}
		
		/* custom fields */
		$cfs_del = array();
		$cf_directions = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."cf_directions WHERE direction_id='$data_id'");
		foreach($cf_directions as $cf_item){
			$cfs_del[$cf_item->cf_id] = $cf_item->cf_id;
		}	
		if(isset($_POST['cf']) and is_array($_POST['cf'])){
			$cf = $_POST['cf'];	
			foreach($cf as $cfid){
				$cfid = intval($cfid);
				if(!in_array($cfid,$cfs_del)){		
					$arr = array();
					$arr['direction_id'] = $data_id;
					$arr['cf_id'] = $cfid;
					$wpdb->insert($wpdb->prefix.'cf_directions', $arr);	
				} else {
					unset($cfs_del[$cfid]);
				}
			}
		}		
		foreach($cfs_del as $tod){
			$wpdb->query("DELETE FROM ".$wpdb->prefix."cf_directions WHERE cf_id = '$tod' AND direction_id='$data_id'");			
		}					
		/* end custom fields */
					
		/* template */
		$list_directions_temp = apply_filters('list_directions_temp',array());
		if(is_array($list_directions_temp)){
			foreach($list_directions_temp as $key => $title){						
				$value = pn_strip_text(is_param_post_ml($key));
				$res = update_direction_txtmeta($data_id, $key, $value);
				if($res != 1){
					$form->error_form(sprintf(__('Error! Directory <b>%s</b> do not exist or cannot be written! Create this directory or get permission 777.','pn'),'/wp-content/pn_uploads/napsmeta/'));
				}
			}
		}
		/* end template */
					
	}

	$url = admin_url('admin.php?page=pn_add_directions&item_id='. $data_id .'&reply=true');
	$form->answer_form($url);
}	

add_action('tab_direction_tab1', 'direction_tab_direction_tab1', 10, 2);
function direction_tab_direction_tab1($data, $data_id){
	$currencies = list_currency(__('No item','pn'), 1);
	$form = new PremiumForm();
?>							
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Send','pn'); ?></span></div>
			
			<?php 
			$atts = array();
			$atts['id'] = 'currency_id_give';
			$option_data = array();
			$opts = array();
			foreach($currencies as $key => $val){
				$option_data[$key] = 'data-decimal="'. $val['decimal'] .'"';
				$opts[$key] = $val['title'];
			}	
			$form->select_search('currency_id_give', $opts, is_isset($data, 'currency_id_give'), $atts, $option_data); 
			?>
		
			<?php do_action('tab_dir_direction', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Receive','pn'); ?></span></div>
			
			<?php 
			$atts = array();
			$atts['id'] = 'currency_id_get';
			$option_data = array();
			$opts = array();
			foreach($currencies as $key => $val){
				$option_data[$key] = 'data-decimal="'. $val['decimal'] .'"';
				$opts[$key] = $val['title'];
			}	
			$form->select_search('currency_id_get', $opts, is_isset($data, 'currency_id_get'), $atts, $option_data); 
			?>			
				
			<?php do_action('tab_dir_direction', 2, $data, $data_id); ?>
		</div>
	</div>
<?php	
}

add_action('tab_direction_tab1', 'techname_tab_direction_tab1', 20, 2);
function techname_tab_direction_tab1($data, $data_id){
?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Technical name','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="tech_name" class="tech_name" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($data, 'tech_name')); ?>" />
			</div>
			<?php do_action('tab_dir_techname', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<?php do_action('tab_dir_techname', 2, $data, $data_id); ?>
		</div>
	</div>
<?php	
} 

add_action('tab_direction_tab1', 'permalink_tab_direction_tab1', 30, 2);
function permalink_tab_direction_tab1($data, $data_id){
global $premiumbox;	

	$form = new PremiumForm();
	$gp = $premiumbox->general_tech_pages();
	$permalink = rtrim(get_site_url_ml(),'/').'/' . is_isset($gp, 'exchange');
?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Permalink','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php echo $permalink; ?><br />
				<input type="text" name="direction_name" style="width: 100%;" value="<?php echo is_direction_permalink(is_isset($data, 'direction_name')); ?>" />
			</div>
			<?php $form->help(__('More info','pn'), sprintf(__('Permanent link for exchange direction: %sPERMANENTLINK','pn'), $permalink)); ?>
			<?php do_action('tab_dir_permalink', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<?php do_action('tab_dir_permalink', 2, $data, $data_id); ?>
		</div>
	</div>
<?php	
} 

add_action('tab_direction_tab1', 'status_tab_direction_tab1', 40, 2);
function status_tab_direction_tab1($data, $data_id){
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Status','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="direction_status" id="direction_status" autocomplete="off">
					<?php 
						$direction_status = is_isset($data, 'direction_status'); 
						if(!is_numeric($direction_status)){ $direction_status = 1; }
					?>						
					<option value="1" <?php selected($direction_status,1); ?>><?php _e('active direction','pn');?></option>
					<option value="0" <?php selected($direction_status,0); ?>><?php _e('inactive direction','pn');?></option>
					<option value="2" <?php selected($direction_status,2); ?>><?php _e('hold direction','pn');?></option>
				</select>
			</div>			
			<?php do_action('tab_dir_status', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<?php do_action('tab_dir_status', 2, $data, $data_id); ?>
		</div>
	</div>
<?php								
}

add_action('tab_direction_tab2', 'rate_tab_direction_tab2', 10, 2);
function rate_tab_direction_tab2($data, $data_id){	
	$dir_c = is_course_direction($data, '', '', 'admin');
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Exchange rate','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="course_give" id="course_give" style="width: 100%;" value="<?php echo is_isset($dir_c, 'give'); ?>" />
			</div>			
			<?php do_action('tab_dir_rate', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="course_get" id="course_get" style="width: 100%;" value="<?php echo is_isset($dir_c, 'get'); ?>" />	
			</div>		
			<?php do_action('tab_dir_rate', 2, $data, $data_id); ?>
		</div>
	</div>
<?php
}

add_action('tab_direction_tab2', 'profit_tab_direction_tab2', 20, 2);
function profit_tab_direction_tab2($data, $data_id){
	$form = new PremiumForm();
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Profit','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="profit_sum1" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'profit_sum1')); ?>" /> S</div>
				<div><input type="text" name="profit_pers1" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'profit_pers1')); ?>" /> %</div>
			</div>							
			<?php do_action('tab_dir_profit', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="profit_sum2" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'profit_sum2')); ?>" /> S</div>
				<div><input type="text" name="profit_pers2" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'profit_pers2')); ?>" /> %</div>	
			</div>		
			<?php do_action('tab_dir_profit', 2, $data, $data_id); ?>
		</div>
	</div>
	<div class="add_tabs_line">
		<?php $form->help(__('More info','pn'), __('Enter profit amount for this direction. Profit may be set in numbers (S) or in percent (%). This value is used for the affiliate program.','pn')); ?>
	</div>
<?php
}

add_action('tab_direction_tab4', 'fees_tab_direction_tab4', 10, 2);
function fees_tab_direction_tab4($data, $data_id){
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Payment systems fees','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="com_sum1" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_sum1')); ?>" /> S</div>
				<div><input type="text" name="com_pers1" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_pers1')); ?>" /> %</div>
			</div>							
			<?php do_action('tab_dir_fees', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="com_sum2" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_sum2')); ?>" /> S</div>
				<div><input type="text" name="com_pers2" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_pers2')); ?>" /> %</div>	
			</div>		
			<?php do_action('tab_dir_fees', 2, $data, $data_id); ?>
		</div>
	</div>
<?php
}

add_action('tab_direction_tab4', 'payfees_tab_direction_tab4', 20, 2);
function payfees_tab_direction_tab4($data, $data_id){
	$form = new PremiumForm();
?>		
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="pay_com1" <?php checked(is_isset($data, 'pay_com1'),1); ?> value="1" /> <?php _e('exchange pays fee','pn'); ?></label>
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="pay_com2" <?php checked(is_isset($data, 'pay_com2'),1); ?> value="1" /> <?php _e('exchange pays fee','pn'); ?></label>
			</div>		
		</div>
	</div>
	<div class="add_tabs_line">
		<?php $form->help(__('More info','pn'), __('Check this box if you are to pay a payment system fee instead of client','pn')); ?>
	</div>
					
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="nscom1" <?php checked(is_isset($data, 'nscom1'),1); ?> value="1" /> <?php _e('non standard fees','pn'); ?></label>
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="nscom2" <?php checked(is_isset($data, 'nscom2'),1); ?> value="1" /> <?php _e('non standard fees','pn'); ?></label>
			</div>		
		</div>
	</div>							
	<div class="add_tabs_line">
		<?php $form->help(__('More info','pn'), __('Check this box if a payment system takes a fee for incoming payment.','pn')); ?>
	</div>	
<?php
} 

add_action('tab_direction_tab4', 'maxfees_tab_direction_tab4', 30, 2);
function maxfees_tab_direction_tab4($data, $data_id){
?>		
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Min. amount of fees','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="minsum1com" value="<?php echo is_sum(is_isset($data, 'minsum1com')); ?>" />		
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="minsum2com" value="<?php echo is_sum(is_isset($data, 'minsum2com')); ?>" />				
			</div>		
		</div>
	</div>
						
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Max. amount of fees','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="maxsum1com" value="<?php echo is_sum(is_isset($data, 'maxsum1com')); ?>" />		
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="maxsum2com" value="<?php echo is_sum(is_isset($data, 'maxsum2com')); ?>" />				
			</div>		
		</div>
	</div>
<?php
}  
 
add_action('tab_direction_tab5', 'combox_tab_direction_tab5', 10, 2);
function combox_tab_direction_tab5($data, $data_id){		
	$com_box_sum1 = is_sum(is_isset($data, 'com_box_sum1'));
	$com_box_pers1 = is_sum(is_isset($data, 'com_box_pers1'));
	$com_box_sum2 = is_sum(is_isset($data, 'com_box_sum2'));
	$com_box_pers2 = is_sum(is_isset($data, 'com_box_pers2'));
	?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Additional sender fee','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_sum1" style="width: 80%;" id="com_box_sum1" value="<?php echo $com_box_sum1; ?>" /> S			
			</div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_pers1" style="width: 80%;" id="com_box_pers1" value="<?php echo $com_box_pers1; ?>" /> %		
			</div>	
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Additional recipient fee','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_sum2" style="width: 80%;" id="com_box_sum2" value="<?php echo $com_box_sum2; ?>" /> S		
			</div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_pers2" style="width: 80%;" id="com_box_pers2" value="<?php echo $com_box_pers2; ?>" /> %		
			</div>
		</div>
	</div>	
<?php
}   

add_action('tab_direction_tab5', 'comboxmin_tab_direction_tab5', 20, 2);
function comboxmin_tab_direction_tab5($data, $data_id){		
	?>	
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">	
			<div class="add_tabs_sublabel"><span><?php _e('Minimum sender fee','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_min1" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_box_min1')); ?>" /> S				
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Minimum recipient fee','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_min2" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_box_min2')); ?>" /> S				
			</div>			
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Additional sender fee','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="dcom1" <?php checked(is_isset($data, 'dcom1'),1); ?> value="1" /> <?php _e('subtract fee from payment amount','pn'); ?></label>
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Additional recipient fee','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="dcom2" <?php checked(is_isset($data, 'dcom2'),1); ?> value="1" /> <?php _e('add fee to payout amount','pn'); ?></label>
			</div>		
		</div>
	</div>	
<?php
}

add_action('tab_direction_tab6', 'minamount_tab_direction_tab6', 10, 2);
function minamount_tab_direction_tab6($data, $data_id){		
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Minimum amount','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="min_sum1" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'min_sum1')); ?>" />
			</div>
			<?php do_action('tab_dir_minamount', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="min_sum2" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'min_sum2')); ?>" />
			</div>		
			<?php do_action('tab_dir_minamount', 2, $data, $data_id); ?>
		</div>
	</div>												
<?php
}  

add_action('tab_direction_tab6', 'maxamount_tab_direction_tab6', 20, 2);
function maxamount_tab_direction_tab6($data, $data_id){		
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Maximum amount','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="max_sum1" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'max_sum1')); ?>" />
			</div>
			<?php do_action('tab_dir_maxamount', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="max_sum2" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'max_sum2')); ?>" />
			</div>		
			<?php do_action('tab_dir_maxamount', 2, $data, $data_id); ?>
		</div>
	</div>
<?php
} 

add_action('tab_direction_tab7', 'directions_temp_tab_direction_tab7', 10, 2);
function directions_temp_tab_direction_tab7($data, $data_id){
global $premiumbox;	

	$form = new PremiumForm();
	$list_directions_temp = apply_filters('list_directions_temp',array());
	if(is_array($list_directions_temp)){
		foreach($list_directions_temp as $key => $title){ 
			$text = pn_strip_text(get_direction_txtmeta($data_id, $key));									
			if(!$text){ 
				$text = $premiumbox->get_option('naps_temp',$key); 
			} 
			?>
			<div class="add_tabs_line">
				<div class="add_tabs_submit">
					<input type="submit" name="" class="button" value="<?php _e('Save'); ?>" />
				</div>
			</div>
			<div class="add_tabs_line">
				<div class="add_tabs_label"><span><?php echo $title; ?></span></div>
				<div class="add_tabs_single long">
					<?php $form->editor($key, $text, '12', '', apply_filters('direction_instruction_tags', array(), $key), 1, 0, 1); ?>
				</div>
			</div>			
			<?php 
		}
	}													
}  

add_action('tab_direction_tab8', 'udiscount_tab_direction_tab8', 10, 2);
function udiscount_tab_direction_tab8($data, $data_id){		
?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User discount','pn'); ?></span></div>
			
			<div class="premium_wrap_standart">
				<?php 
				$enable_user_discount = is_isset($data, 'enable_user_discount'); 
				if(!is_numeric($enable_user_discount)){ $enable_user_discount = 1; }
				?>														
				<select name="enable_user_discount" autocomplete="off">
					<option value="1" <?php selected($enable_user_discount,1); ?>><?php _e('Yes','pn'); ?></option>
					<option value="0" <?php selected($enable_user_discount,0); ?>><?php _e('No','pn'); ?></option>
				</select>
			</div>			

			<?php do_action('tab_dir_udiscount', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. user discount','pn'); ?></span></div>
			
			<div class="premium_wrap_standart">
				<input type="text" name="max_user_discount" style="width: 100px;" value="<?php echo is_sum(is_isset($data, 'max_user_discount')); ?>" />%
			</div>	
		
			<?php do_action('tab_dir_udiscount', 2, $data, $data_id); ?>
		</div>
	</div>
<?php
} 

add_action('tab_direction_tab9', 'directions_cf_tab_direction_tab9', 10, 2);
function directions_cf_tab_direction_tab9($data, $data_id){
global $wpdb;	

	$form = new PremiumForm();
	
	$cfs_in = array();
	$cf_directions = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."cf_directions WHERE direction_id > 0 AND direction_id='$data_id'");
	foreach($cf_directions as $cf){
		$cfs_in[] = $cf->cf_id;
	}						
	$custom_fields = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."direction_custom_fields ORDER BY cf_order ASC");
	?>	
	<div class="add_tabs_line">
		<div class="premium_wrap_standart">
			<?php
			$cfs_in_count = count($cfs_in);
			$scroll_lists = array();
			$class = array();
			foreach($custom_fields as $cf_data){
				$checked = 0;
				if(in_array($cf_data->id,$cfs_in) or $cfs_in_count == 0 and $cf_data->cf_auto == 'user_email'){
					$checked = 1;
				}

				if($cf_data->cf_auto == 'user_email'){
					$class[$cf_data->id] = 'bred';
				}
					
				$uniqueid = '';
				if($cf_data->uniqueid){
					$uniqueid = ' ('. $cf_data->uniqueid . ')';
				}
				$tech_title = pn_strip_input(ctv_ml($cf_data->tech_name));
				if(!$tech_title){ $tech_title = pn_strip_input(ctv_ml($cf_data->cf_name)); }
					
				$scroll_lists[] = array(
					'title' => $tech_title . pn_item_status($cf_data) . pn_item_basket($cf_data),
					'checked' => $checked,
					'value' => $cf_data->id,
				);	
			}	
			echo get_check_list($scroll_lists, 'cf[]', $class, '', 1);
			?>			
			<div class="premium_clear"></div>
		</div>
		<?php $form->warning(__('Check E-mail field. It is necessary in order to notify users via e-mail','pn')); ?>
	</div>							
	<?php						
}

add_action('tab_direction_tab11', 'directions_cf_tab_direction_tab11', 10, 2);
function directions_cf_tab_direction_tab11($data, $data_id){
	if(current_user_can('administrator') or current_user_can('pn_directions_merchant')){
		
		$paymerch_data = get_direction_meta($data_id, 'paymerch_data');

		$lists = list_extandeds('merchants');
		$m_arr = @unserialize(is_isset($data, 'm_in'));
		$m_arr = (array)$m_arr;
	
		$lists = list_checks_top($lists, $m_arr);
	?>	
	
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Merchant','pn'); ?></span></div>
		<div class="add_tabs_single long">
			<div class="premium_wrap_standart">
				<?php
				$scroll_lists = array();				
				foreach($lists as $m_key => $m_title){
					$checked = 0;
					if(in_array($m_key, $m_arr)){
						$checked = 1;
					}	
					$link_title = $m_title;
					if(current_user_can('administrator') or current_user_can('pn_merchants')){
						$link_title = '<a href="'. admin_url('admin.php?page=pn_add_merchants&item_key='.$m_key) .'" target="_blank">'. $m_title .'</a>';
					}
					$scroll_lists[] = array(
						'title' => $link_title,
						'search' => $m_title,
						'checked' => $checked,
						'value' => $m_key,
					);
				}
				echo get_check_list($scroll_lists, 'm_in[]','','', 1);
				?>			
			</div>			
		</div>
	</div>
	
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Daily limit for merchant','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_in_max = is_sum(is_isset($paymerch_data, 'm_in_max')); 
				?>			
				<input type="text" name="m_in_max" style="width: 100%;" value="<?php echo $m_in_max; ?>" />
			</div>			
		</div>	
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Monthly limit for merchant','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_in_max_month = is_sum(is_isset($paymerch_data, 'm_in_max_month'));  
				?>			
				<input type="text" name="m_in_max_month" style="width: 100%;" value="<?php echo $m_in_max_month; ?>" />
			</div>			
		</div>		
	</div>
	
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>	
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. payment amount for single order','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_in_max_sum = is_sum(is_isset($paymerch_data, 'm_in_max_sum'));  
				?>			
				<input type="text" name="m_in_max_sum" style="width: 100%;" value="<?php echo $m_in_max_sum; ?>" />
			</div>			
		</div>		
	</div>

	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Daily limit of orders (quantities) for merchant','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_in_maxc_day = intval(is_isset($paymerch_data, 'm_in_maxc_day')); 
				?>			
				<input type="text" name="m_in_maxc_day" style="width: 100%;" value="<?php echo $m_in_maxc_day; ?>" />
			</div>			
		</div>	
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Monthly limit of orders (quantities) for merchant','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_in_maxc_month = intval(is_isset($paymerch_data, 'm_in_maxc_month'));  
				?>			
				<input type="text" name="m_in_maxc_month" style="width: 100%;" value="<?php echo $m_in_maxc_month; ?>" />
			</div>			
		</div>		
	</div>	

	<?php
		$lists = list_extandeds('paymerchants');
		$m_arr = @unserialize(is_isset($data, 'm_out'));
		$m_arr = (array)$m_arr;	
	
		$lists = list_checks_top($lists, $m_arr);
	?>
	
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Automatic payout','pn'); ?></span></div>
		<div class="add_tabs_single long">
			<div class="premium_wrap_standart">
				<?php
				$scroll_lists = array();
								
				foreach($lists as $m_key => $m_title){
					$checked = 0;
					if(in_array($m_key, $m_arr)){
						$checked = 1;
					}
					$link_title = $m_title;
					if(current_user_can('administrator') or current_user_can('pn_merchants')){
						$link_title = '<a href="'. admin_url('admin.php?page=pn_add_paymerchants&item_key='.$m_key) .'" target="_blank">'. $m_title .'</a>';
					}					
					$scroll_lists[] = array(
						'title' => $link_title,
						'search' => $m_title,
						'checked' => $checked,
						'value' => $m_key,
					);
				}
				echo get_check_list($scroll_lists, 'm_out[]','','', 1);
				?>
			</div>			
		</div>	
	</div>

	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Automatic payout when order has status "Paid order"','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_out_realpay = intval(is_isset($paymerch_data, 'm_out_realpay')); 
				?>									
				<select name="m_out_realpay" autocomplete="off"> 
					<option value="0" <?php selected($m_out_realpay,0); ?>>--<?php _e('Default','pn'); ?>--</option>
					<option value="1" <?php selected($m_out_realpay,1); ?>><?php _e('No','pn'); ?></option>
					<option value="2" <?php selected($m_out_realpay,2); ?>><?php _e('Yes','pn'); ?></option>
				</select>
			</div>			
		</div>	
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Automatic payout when order has status "Order is on checking"','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_out_verify = intval(is_isset($paymerch_data, 'm_out_verify')); 
				?>									
				<select name="m_out_verify" autocomplete="off"> 
					<option value="0" <?php selected($m_out_verify,0); ?>>--<?php _e('Default','pn'); ?>--</option>
					<option value="1" <?php selected($m_out_verify,1); ?>><?php _e('No','pn'); ?></option>
					<option value="2" <?php selected($m_out_verify,2); ?>><?php _e('Yes','pn'); ?></option>
				</select>
			</div>			
		</div>		
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Daily automatic payout limit','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_out_max = is_sum(is_isset($paymerch_data, 'm_out_max')); 
				?>			
				<input type="text" name="m_out_max" style="width: 100%;" value="<?php echo $m_out_max; ?>" />
			</div>			
		</div>	
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Monthly automatic payout limit','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_out_max_month = is_sum(is_isset($paymerch_data, 'm_out_max_month'));  
				?>			
				<input type="text" name="m_out_max_month" style="width: 100%;" value="<?php echo $m_out_max_month; ?>" />
			</div>			
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>	
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Min. amount of automatic payouts due to order','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_out_min_sum = is_sum(is_isset($paymerch_data, 'm_out_min_sum'));  
				?>			
				<input type="text" name="m_out_min_sum" style="width: 100%;" value="<?php echo $m_out_min_sum; ?>" />
			</div>			
		</div>	
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. amount of automatic payouts due to order','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_out_max_sum = is_sum(is_isset($paymerch_data, 'm_out_max_sum'));  
				?>			
				<input type="text" name="m_out_max_sum" style="width: 100%;" value="<?php echo $m_out_max_sum; ?>" />
			</div>			
		</div>		
	</div>		
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Automatic payout delay (hrs or min)','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_out_timeout = is_sum(is_isset($paymerch_data, 'm_out_timeout')); 
				?>			
				<input type="text" name="m_out_timeout" style="width: 100%;" value="<?php echo $m_out_timeout; ?>" />
			</div>			
		</div>	
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Whom the delay is for','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$m_out_timeout_user = intval(is_isset($paymerch_data, 'm_out_timeout_user')); 
				?>
				<select name="m_out_timeout_user" autocomplete="off"> 
					<option value="0" <?php selected($m_out_timeout_user,0); ?>><?php _e('everyone','pn'); ?></option>
					<option value="1" <?php selected($m_out_timeout_user,1); ?>><?php _e('newcomers','pn'); ?></option>
					<option value="2" <?php selected($m_out_timeout_user,2); ?>><?php _e('not registered users','pn'); ?></option>
					<option value="3" <?php selected($m_out_timeout_user,3); ?>><?php _e('not verified users','pn'); ?></option>
				</select>
			</div>			
		</div>		
	</div>

	<?php
	}
} 	
}  