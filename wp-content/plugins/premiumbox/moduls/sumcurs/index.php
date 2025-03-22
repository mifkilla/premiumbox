<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Exchange rate dependent on amount of exchange[:en_US][ru_RU:]Курс зависящий от суммы обмена[:ru_RU]
description: [en_US:]Exchange rate dependent on amount of exchange[:en_US][ru_RU:]Курс зависящий от суммы обмена[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_bd_activated', 'bd_all_moduls_active_sumcurs');
add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_sumcurs');
function bd_all_moduls_active_sumcurs(){
global $wpdb;	
	$table_name= $wpdb->prefix ."naps_sumcurs"; 
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`naps_id` bigint(20) NOT NULL default '0',
		`sum_val` varchar(50) NOT NULL default '0',
		`curs1` varchar(50) NOT NULL default '0',
		`curs2` varchar(50) NOT NULL default '0',
		PRIMARY KEY ( `id` ),
		INDEX (`naps_id`),
		INDEX (`sum_val`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql);
}

add_action('item_direction_delete', 'item_direction_delete_sumcurs');
function item_direction_delete_sumcurs($item_id){
global $wpdb;	
	$wpdb->query("DELETE FROM ".$wpdb->prefix."naps_sumcurs WHERE naps_id = '$item_id'");
}

add_action('item_direction_edit', 'item_direction_sumcurs');
add_action('item_direction_add', 'item_direction_sumcurs');
function item_direction_sumcurs($item_id){ 
global $wpdb;	
	$wpdb->query("UPDATE ".$wpdb->prefix."naps_sumcurs SET naps_id = '$item_id' WHERE naps_id = '0'");
}

add_action('item_direction_copy', 'item_direction_copy_sumcurs', 1, 2);
function item_direction_copy_sumcurs($last_id, $new_id){
global $wpdb;
	$naps_meta = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."naps_sumcurs WHERE naps_id='$last_id'"); 
	foreach($naps_meta as $nap){
		$arr = array();
		foreach($nap as $k => $v){
			if($k == 'naps_id'){
				$arr[$k] = $new_id;
			} else {
				$arr[$k] = is_sum($v);
			}
		}
		$wpdb->insert($wpdb->prefix.'naps_sumcurs', $arr);
	}	
}

function get_napscurs_form($item, $pers){
	
	$temp = '
	<div class="construct_line js_napscurs_line" data-id="'. is_isset($item,'id') .'">';
	
		$temp .= '
		<div class="construct_item">
			<div class="construct_title">
				'. __('Amount','pn') .'
			</div>
			<div class="construct_input">
				<input type="text" name="" style="width: 100px;" class="rate_sum0" value="'. is_sum(is_isset($item,'sum_val')) .'" />
			</div>
		</div>
		<div class="construct_item">
			<div class="construct_title">
				'. __('Send','pn') .''. $pers .'
			</div>
			<div class="construct_input">
				<input type="text" name="" style="width: 100px;" class="rate_sum1" value="'. is_sum(is_isset($item,'curs1')) .'" />
			</div>
		</div>		
		<div class="construct_item">
			<div class="construct_title">
				'. __('Receive','pn') .''. $pers .'
			</div>
			<div class="construct_input">
				<input type="text" name="" style="width: 100px;" class="rate_sum2" value="'. is_sum(is_isset($item,'curs2')) .'" />
			</div>
		</div>';

		if(isset($item->id)){
			$temp .= '
				<div class="construct_add js_napscurs_add">'. __('Save','pn') .'</div>
				<div class="construct_del js_napscurs_del">'. __('Delete','pn') .'</div>			
			';
		} else {
			$temp .= '
				<div class="construct_add js_napscurs_add">'. __('Add new','pn') .'</div>
			';			
		}
		
		$temp .= '
			<div class="premium_clear"></div>
	</div>
	';	
	
	return $temp;
}

function get_napscurs_html($data_id){
global $wpdb, $premiumbox;	
	
	$temp = '';
	
	$pers = '';
	$what = intval($premiumbox->get_option('sumcurs','what'));
	if($what == 1){
		$pers = ' (%)';
	}
	
	$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."naps_sumcurs WHERE naps_id='$data_id' ORDER BY (sum_val -0.0) ASC"); 
	foreach($items as $item){
		$temp .= get_napscurs_form($item, $pers);	
	}
	
	$temp .= get_napscurs_form('', $pers);
	
	return $temp;
}

add_action('premium_action_sumcurs_del', 'pn_premium_action_sumcurs_del');
function pn_premium_action_sumcurs_del(){
global $wpdb;

	only_post();
	
	header('Content-Type: application/json; charset=utf-8');
	
	$log = array();
	$log['status'] = 'success';	
	
	if(current_user_can('administrator') or current_user_can('pn_directions')){
		$data_id = intval(is_param_post('data_id'));
		$item_id = intval(is_param_post('item_id'));		
		$wpdb->query("DELETE FROM ".$wpdb->prefix."naps_sumcurs WHERE id='$item_id' AND naps_id='$data_id'");

		$log['html'] = get_napscurs_html($data_id);
	}  		

	echo json_encode($log);	
	exit;
}

add_action('premium_action_sumcurs_add', 'pn_premium_action_sumcurs_add');
function pn_premium_action_sumcurs_add(){
global $wpdb;

	only_post();
	
	header('Content-Type: application/json; charset=utf-8');
	
	$log = array();
	$log['status'] = 'success';	
	
	if(current_user_can('administrator') or current_user_can('pn_directions')){
		$data_id = intval(is_param_post('data_id'));
		$item_id = intval(is_param_post('item_id'));
		$sum1 = is_sum(is_param_post('sum1'));
		$sum2 = is_sum(is_param_post('sum2'));
		$sum3 = is_sum(is_param_post('sum3'));

		$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."naps_sumcurs WHERE id='$item_id' AND naps_id='$data_id'");
		$array = array();
		$array['naps_id'] = $data_id;
		$array['sum_val'] = $sum1;
		$array['curs1'] = $sum2;
		$array['curs2'] = $sum3;
		if(isset($item->id)){
			$wpdb->update($wpdb->prefix.'naps_sumcurs', $array, array('id'=>$item->id));
		} else {
			$wpdb->insert($wpdb->prefix.'naps_sumcurs', $array);
		}
	
		$log['html'] = get_napscurs_html($data_id);
	}  		
	
	echo json_encode($log);
	exit;
}

add_action('tab_direction_tab2', 'tab_direction_tab2_sumcurs', 11);
function tab_direction_tab2_sumcurs($data){	 
	$data_id = intval(is_isset($data, 'id'));
	$form = new PremiumForm();
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Rate is depends on exchange amount','pn'); ?></span></div>
			<div id="napscurs_html" data-id="<?php echo $data_id; ?>">
				<?php echo get_napscurs_html($data_id); ?>
			</div>
		</div>
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<?php $form->help(__('More info','pn'), __('Set a the lower amount of exchange in field "Amount". Then set a currency rate for Giving and Receiving. If the user wants to send you the specified amount then the rate will be the same you previously set.','pn')); ?>
		</div>
	</div>	

<script type="text/javascript">
$(function(){
	
	$(document).on('click', '.js_napscurs_add', function(){ 
		var data_id = $('#napscurs_html').attr('data-id');
		var par = $(this).parents('.js_napscurs_line');
		var item_id = par.attr('data-id');
		var sum1 = par.find('.rate_sum0').val();
		var sum2 = par.find('.rate_sum1').val();
		var sum3 = par.find('.rate_sum2').val();
		
		$('#napscurs_html').find('input').attr('disabled',true);
		$('#napscurs_html').find('.js_napscurs_add, .js_napscurs_del').addClass('active');
		
		var param = 'data_id='+data_id+'&item_id='+item_id+'&sum1='+sum1+'&sum2='+sum2+'&sum3='+sum3;	
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('sumcurs_add', 'post');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				if(res['html']){
					$('#napscurs_html').html(res['html']);
				} 
			}
		});		
		
		return false;
	});

	$(document).on('click', '.js_napscurs_del', function(){
		var data_id = parseInt($('#napscurs_html').attr('data-id'));
		var par = $(this).parents('.js_napscurs_line');
		var item_id = parseInt(par.attr('data-id'));
		
		$('#napscurs_html').find('input').attr('disabled',true);
		$('#napscurs_html').find('.js_napscurs_add, .js_napscurs_del').addClass('active');
		
		var param = 'data_id='+data_id+'&item_id='+item_id;	
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('sumcurs_del', 'post');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				if(res['html']){
					$('#napscurs_html').html(res['html']);
				} 
			}
		});		
		
		return false;
	});	

});
</script>		
	<?php 
}

add_filter('get_calc_data', 'get_calc_data_sumcurs', 80, 2);
function get_calc_data_sumcurs($cdata, $calc_data){
global $wpdb, $premiumbox;
	
	$direction = $calc_data['direction'];
	$direction_id = $direction->id;
	$post_sum = is_sum(is_isset($calc_data,'post_sum'), 20);
	
	$set_course = intval(is_isset($calc_data,'set_course'));
	if($set_course != 1){
		$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."naps_sumcurs WHERE naps_id='$direction_id'");
		if($cc > 0){	
			$cdata['dis1c'] = 1;
			$cdata['dis2'] = 1;	
			$cdata['dis2c'] = 1;
			
			$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."naps_sumcurs WHERE naps_id='$direction_id' AND ('$post_sum' -0.0) >= sum_val ORDER BY (sum_val -0.0) DESC");
			if(isset($data->id)){
				$course_give = $cdata['course_give'];
				$course_get = $cdata['course_get'];
					
				$decimal_give = $cdata['decimal_give'];
				$decimal_get = $cdata['decimal_get'];
					
				$what = intval($premiumbox->get_option('sumcurs','what'));
				if($what == 0){
					$cdata['course_give'] = is_sum($data->curs1, $decimal_give);
					$cdata['course_get'] = is_sum($data->curs2, $decimal_get);
				} elseif($course_give != 0 and $course_get != 0){
					$c1 = is_sum($data->curs1, 20);
					$c2 = is_sum($data->curs2, 20);
					$one_pers1 = $course_give / 100;
					$cdata['course_give'] = is_sum($course_give + ($one_pers1 * $c1), $decimal_give);
					$one_pers2 = $course_get / 100;
					$cdata['course_get'] = is_sum($course_get + ($one_pers2 * $c2), $decimal_get);
				}
			}
		}
	}
	
	return $cdata;
}	

add_action('admin_menu', 'admin_menu_sumcurs');
function admin_menu_sumcurs(){
global $premiumbox;	
	if(current_user_can('administrator')){
		add_submenu_page("pn_moduls", __('Rate is depends on exchange amount','pn'), __('Rate is depends on exchange amount','pn'), 'read', "pn_sumcurs", array($premiumbox, 'admin_temp'));
	}
}

add_action('pn_adminpage_title_pn_sumcurs', 'def_adminpage_title_pn_sumcurs');
function def_adminpage_title_pn_sumcurs($page){
	_e('Rate is depends on exchange amount','pn');
} 

add_action('pn_adminpage_content_pn_sumcurs','def_adminpage_content_pn_sumcurs');
function def_adminpage_content_pn_sumcurs(){
global $wpdb, $premiumbox;

	$form = new PremiumForm();
	
	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => '',
		'submit' => __('Save','pn'),
	);	
	$options['what'] = array(
		'view' => 'select',
		'title' => __('Method of exchange rate formation', 'pn'),
		'options' => array('0'=> __('specify the exchange rate directly','pn'), '1' => __('add interest to the exchange rate','pn')),
		'default' => $premiumbox->get_option('sumcurs','what'),
		'name' => 'what',
	);	

	$params_form = array(
		'filter' => 'pn_sumcurs_options',
		'method' => 'ajax',
		'button_title' => __('Save','pn'),
	);
	$form->init_form($params_form, $options);	
}  

add_action('premium_action_pn_sumcurs','def_premium_action_pn_sumcurs');
function def_premium_action_pn_sumcurs(){
global $wpdb, $premiumbox;	
	only_post();
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$premiumbox->update_option('sumcurs', 'what', intval(is_param_post('what')));			

	$url = admin_url('admin.php?page=pn_sumcurs&reply=true');
	$form->answer_form($url);
} 