<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Exchange rate dependent on currency reserve[:en_US][ru_RU:]Курс зависящий от резерва[:ru_RU]
description: [en_US:]Exchange rate dependent on currency reserve[:en_US][ru_RU:]Курс зависящий от резерва[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_bd_activated', 'bd_all_moduls_active_reservcurs');
add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_reservcurs');
function bd_all_moduls_active_reservcurs(){
global $wpdb;	
	$table_name= $wpdb->prefix ."naps_reservcurs"; 
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

add_action('item_direction_delete', 'item_direction_delete_reservcurs');
function item_direction_delete_reservcurs($item_id){ 
global $wpdb;	
	$wpdb->query("DELETE FROM ".$wpdb->prefix."naps_reservcurs WHERE naps_id = '$item_id'");
}

add_action('item_direction_edit', 'item_direction_reservcurs');
add_action('item_direction_add', 'item_direction_reservcurs');
function item_direction_reservcurs($item_id){ 
global $wpdb;	
	$wpdb->query("UPDATE ".$wpdb->prefix."naps_reservcurs SET naps_id = '$item_id' WHERE naps_id = '0'");
}

add_action('item_direction_copy', 'item_direction_copy_reservcurs', 1, 2);  
function item_direction_copy_reservcurs($last_id, $new_id){
global $wpdb;
	$naps_meta = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."naps_reservcurs WHERE naps_id='$last_id'"); 
	foreach($naps_meta as $nap){
		$arr = array();
		$arr['naps_id'] = $new_id;
		$arr['sum_val'] = is_sum($nap->sum_val);
		$arr['curs1'] = is_sum($nap->curs1);
		$arr['curs2'] = is_sum($nap->curs2);
		$wpdb->insert($wpdb->prefix.'naps_reservcurs', $arr);
	}
}

function get_reservcurs_form($item, $pers){
	
	$temp = '
	<div class="construct_line js_reservcurs_line" data-id="'. is_isset($item, 'id') .'">';
	
		$temp .= '
		<div class="construct_item">
			<div class="construct_title">
				'. __('Reserve','pn') .'
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
			<div class="construct_add js_reservcurs_add">'. __('Save','pn') .'</div>
			<div class="construct_del js_reservcurs_del">'. __('Delete','pn') .'</div>
			';			
		} else {
			$temp .= '
			<div class="construct_add js_reservcurs_add">'. __('Add new','pn') .'</div>
			';
		}
		
		$temp .= '	
			<div class="premium_clear"></div>
	</div>	
	';

	return $temp;
}

function get_reservcurs_html($data_id){
global $wpdb, $premiumbox;	
	$temp = '';
	$pers = '';
	$what = intval($premiumbox->get_option('reservcurs','what'));
	if($what == 1){
		$pers = ' (%)';
	}	
	
	$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."naps_reservcurs WHERE naps_id='$data_id' ORDER BY (sum_val -0.0) ASC"); 
	foreach($items as $item){
		$temp .= get_reservcurs_form($item, $pers);
	}
	$temp .= get_reservcurs_form('', $pers);
	
	return $temp;
}

add_action('premium_action_reservcurs_del', 'pn_premium_action_reservcurs_del');
function pn_premium_action_reservcurs_del(){
global $wpdb;

	only_post();
	
	header('Content-Type: application/json; charset=utf-8');
	
	$log = array();
	$log['status'] = 'success';	
	
	if(current_user_can('administrator') or current_user_can('pn_directions')){
		
		$data_id = intval(is_param_post('data_id'));
		$item_id = intval(is_param_post('item_id'));		
		$wpdb->query("DELETE FROM ".$wpdb->prefix."naps_reservcurs WHERE id='$item_id' AND naps_id='$data_id'");

		$log['html'] = get_reservcurs_html($data_id);
	}  		

	echo json_encode($log);	
	exit;
}

add_action('premium_action_reservcurs_add', 'pn_premium_action_reservcurs_add');
function pn_premium_action_reservcurs_add(){
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

		$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."naps_reservcurs WHERE id='$item_id' AND naps_id='$data_id'");
				
		$array = array();
		$array['naps_id'] = $data_id;
		$array['sum_val'] = $sum1;
		$array['curs1'] = $sum2;
		$array['curs2'] = $sum3;
				
		if(isset($item->id)){
			$wpdb->update($wpdb->prefix.'naps_reservcurs', $array, array('id'=>$item->id));
		} else {
			$wpdb->insert($wpdb->prefix.'naps_reservcurs', $array);
		}
	
		$log['html'] = get_reservcurs_html($data_id);
	}  		
	
	echo json_encode($log);
	exit;
}

add_action('tab_direction_tab2', 'tab_direction_tab2_reservcurs', 12);
function tab_direction_tab2_reservcurs($data){	 
	$data_id = intval(is_isset($data, 'id'));
	$form = new PremiumForm();
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange rate dependent on currency reserve','pn'); ?></span></div>
			<div id="reservcurs_html" data-id="<?php echo $data_id; ?>">
				<?php echo get_reservcurs_html($data_id); ?>
			</div>
		</div>
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<?php $form->help(__('More info','pn'), __('Specify the value of the exchange rate depending on the currency reserve','pn')); ?>
		</div>
	</div>	

<script type="text/javascript">
$(function(){
	
	$(document).on('click', '.js_reservcurs_add', function(){ 
		var data_id = parseInt($('#reservcurs_html').attr('data-id'));
		var par = $(this).parents('.js_reservcurs_line');
		var item_id = parseInt(par.attr('data-id'));
		var sum1 = par.find('.rate_sum0').val();
		var sum2 = par.find('.rate_sum1').val();
		var sum3 = par.find('.rate_sum2').val();
		
		$('#reservcurs_html').find('input').attr('disabled',true);
		$('#reservcurs_html').find('.js_reservcurs_add, .js_reservcurs_del').addClass('active');
		
		var param = 'data_id='+data_id+'&item_id='+item_id+'&sum1='+sum1+'&sum2='+sum2+'&sum3='+sum3;	
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('reservcurs_add', 'post');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				if(res['html']){
					$('#reservcurs_html').html(res['html']);
				} 
			}
		});		
		
		return false;
	});

	$(document).on('click', '.js_reservcurs_del', function(){
		var data_id = parseInt($('#reservcurs_html').attr('data-id'));
		var par = $(this).parents('.js_reservcurs_line');
		var item_id = parseInt(par.attr('data-id'));
		
		$('#reservcurs_html').find('input').attr('disabled',true);
		$('#reservcurs_html').find('.js_reservcurs_add, .js_reservcurs_del').addClass('active');
		
		var param = 'data_id='+data_id+'&item_id='+item_id;	
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('reservcurs_del','post');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				if(res['html']){
					$('#reservcurs_html').html(res['html']);
				} 
			}
		});		
		
		return false;
	});		

});
</script>		
	<?php 
}

add_action('after_update_currency_reserv', 'reservcurs_after_update_currency_reserv', 101, 3);
function reservcurs_after_update_currency_reserv($currency_reserv, $currency_id, $item){
global $wpdb, $premiumbox; 
		
	$what = intval($premiumbox->get_option('reservcurs','what'));
	$method = intval($premiumbox->get_option('reservcurs','method'));
	if($method == 0){
		$bd_ids = array();
		$bd_string = $currency_id.','.is_isset($item,'tieds');
		$bd_string_arr = explode(',', $bd_string);
		$not_bd = array('rc','rd','d');
		foreach($bd_string_arr as $bd_st){
			$bd_st = trim($bd_st);
			if($bd_st and !strstr_array($bd_st, $not_bd)){
				$bd_ids[] = preg_replace( '/[^0-9]/', '', $bd_st);
			}
		}
		
		$bd_id = create_data_for_bd($bd_ids, 'int');
		if($bd_id){
			$directions = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions WHERE auto_status='1' AND direction_status='1' AND currency_id_get IN($bd_id)");
			foreach($directions as $direction){
				$direction_id = $direction->id;
				$dir_c = is_course_direction($direction, '', '', 'admin');
				$course_give = is_isset($dir_c,'give');
				$course_get = is_isset($dir_c,'get');
				$reserv = get_direction_reserv('', $item, $direction); 
				
				$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."naps_reservcurs WHERE naps_id='$direction_id' AND ('$reserv' -0.0) >= sum_val ORDER BY (sum_val -0.0) DESC");
				if(isset($data->id)){
					$arr = array();
					if($what == 0){
						$n_course_give = $data->curs1;
						$n_course_get = $data->curs2;
					} else {
						$c1 = $data->curs1;
						$c2 = $data->curs2;
						$one_pers1 = $course_give / 100;
						$n_course_give = $course_give + ($one_pers1 * $c1);
						$one_pers2 = $course_get / 100;
						$n_course_get = $course_get + ($one_pers2 * $c2);
					}
					$arr['course_give'] = $n_course_give;
					$arr['course_get'] = $n_course_get;
					if($n_course_give > 0 and $n_course_get > 0){
						$wpdb->update($wpdb->prefix ."directions", $arr, array('id'=> $direction_id));
					}
				}		
			}
		}
		do_action('reservcurs_end');
	}
}

add_action('after_update_direction_reserv', 'reservcurs_after_update_direction_reserv', 101, 3);
function reservcurs_after_update_direction_reserv($reserv, $direction_id, $item){
global $wpdb, $premiumbox; 
		
	$what = intval($premiumbox->get_option('reservcurs','what'));
	$method = intval($premiumbox->get_option('reservcurs','method'));
	if($method == 0){
		
		$bd_ids = array();
		$bd_string = $direction_id.','.is_isset($item,'tieds');
		$bd_string_arr = explode(',', $bd_string);
		$not_bd = array('rc','rd','c');
		foreach($bd_string_arr as $bd_st){
			$bd_st = trim($bd_st);
			if($bd_st and !strstr_array($bd_st, $not_bd)){
				$bd_ids[] = preg_replace( '/[^0-9]/', '', $bd_st);
			}
		}		
		
		$bd_id = create_data_for_bd($bd_ids, 'int');
		if($bd_id){
			$dir_c = is_course_direction($item, '', '', 'admin');
			$course_give = is_isset($dir_c,'give');
			$course_get = is_isset($dir_c,'get'); 
				
			$datas = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."naps_reservcurs WHERE naps_id IN ($bd_id) AND ('$reserv' -0.0) >= sum_val ORDER BY (sum_val -0.0) DESC");
			foreach($datas as $data){
				$arr = array();
				if($what == 0){
					$n_course_give = $data->curs1;
					$n_course_get = $data->curs2;
				} else {
					$c1 = $data->curs1;
					$c2 = $data->curs2;
					$one_pers1 = $course_give / 100;
					$n_course_give = $course_give + ($one_pers1 * $c1);
					$one_pers2 = $course_get / 100;
					$n_course_get = $course_get + ($one_pers2 * $c2);
				}
				$arr['course_give'] = $n_course_give;
				$arr['course_get'] = $n_course_get;
				if($n_course_give > 0 and $n_course_get > 0){
					$wpdb->update($wpdb->prefix ."directions", $arr, array('id'=> $direction_id));
				}
			}
		}
		do_action('reservcurs_end');
	}
}

add_filter('get_calc_data', 'get_calc_data_reservcurs', 81, 2);
function get_calc_data_reservcurs($cdata, $calc_data){
global $wpdb, $premiumbox;
	
	$what = intval($premiumbox->get_option('reservcurs','what'));
	$method = intval($premiumbox->get_option('reservcurs','method'));
	if($method == 1){	
		$set_course = intval(is_isset($calc_data,'set_course'));
		if($set_course != 1){
			$direction = $calc_data['direction'];
			$direction_id = $direction->id;
			
			$vd1 = $calc_data['vd1'];
			$vd2 = $calc_data['vd2'];
			
			$reserv = get_direction_reserv($vd1, $vd2, $direction);
			
			$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."naps_reservcurs WHERE naps_id='$direction_id' AND ('$reserv' -0.0) >= sum_val ORDER BY (sum_val -0.0) DESC");
			if(isset($data->id)){
				$course_give = $cdata['course_give'];
				$course_get = $cdata['course_get'];
					
				$decimal_give = $cdata['decimal_give'];
				$decimal_get = $cdata['decimal_get'];
			
				if($what == 0){
					$n_course_give = $data->curs1;
					$n_course_get = $data->curs2;
				} else {
					$c1 = is_sum($data->curs1, 20);
					$c2 = is_sum($data->curs2, 20);
					$one_pers1 = $course_give / 100;
					$n_course_give = $course_give + ($one_pers1 * $c1);
					$one_pers2 = $course_get / 100;
					$n_course_get = $course_get + ($one_pers2 * $c2);
				}		
			
				$cdata['course_give'] = is_sum($n_course_give, $decimal_give);
				$cdata['course_get'] = is_sum($n_course_get, $decimal_get);
			}
		}
	}
	
	return $cdata;
}	

add_action('admin_menu', 'admin_menu_reservcurs');
function admin_menu_reservcurs(){
global $premiumbox;	
	add_submenu_page("pn_moduls", __('Exchange rate dependent on currency reserve','pn'), __('Exchange rate dependent on currency reserve','pn'), 'administrator', "pn_reservcurs", array($premiumbox, 'admin_temp'));
}

add_action('pn_adminpage_title_pn_reservcurs', 'def_adminpage_title_pn_reservcurs');
function def_adminpage_title_pn_reservcurs($page){
	_e('Exchange rate dependent on currency reserve','pn');
} 

add_action('pn_adminpage_content_pn_reservcurs','def_pn_admin_content_pn_reservcurs');
function def_pn_admin_content_pn_reservcurs(){
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
		'default' => $premiumbox->get_option('reservcurs','what'),
		'name' => 'what',
	);	
	$options['method'] = array(
		'view' => 'select',
		'title' => __('Update rates', 'pn'),
		'options' => array('0'=> __('during currency operations','pn'), '1' => __('in an instant','pn')),
		'default' => $premiumbox->get_option('reservcurs','method'),
		'name' => 'method',
	);
	$params_form = array(
		'filter' => 'pn_reservcurs_options',
		'method' => 'ajax',
		'button_title' => __('Save','pn'),
	);
	$form->init_form($params_form, $options);
	
}  

add_action('premium_action_pn_reservcurs','def_premium_action_pn_reservcurs');
function def_premium_action_pn_reservcurs(){
global $wpdb, $premiumbox;	

	only_post();
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));

	$premiumbox->update_option('reservcurs', 'what', intval(is_param_post('what')));
	$premiumbox->update_option('reservcurs', 'method', intval(is_param_post('method')));

	$url = admin_url('admin.php?page=pn_reservcurs&reply=true');
	$form->answer_form($url);
} 