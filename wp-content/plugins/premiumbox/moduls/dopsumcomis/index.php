<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Additional fee that depends on exchanged amount[:en_US][ru_RU:]Дополнительная комиссия зависящая от суммы обмена[:ru_RU]
description: [en_US:]Additional fee that depends on exchanged amount[:en_US][ru_RU:]Дополнительная комиссия зависящая от суммы обмена[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_bd_activated', 'bd_all_moduls_active_dopsumcomis');
add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_dopsumcomis');
function bd_all_moduls_active_dopsumcomis(){
global $wpdb;	
	$table_name = $wpdb->prefix ."naps_dopsumcomis"; 
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`naps_id` bigint(20) NOT NULL default '0',
		`sum_val` varchar(150) NOT NULL default '0',
		`com_box_summ1` varchar(150) NOT NULL default '0',
		`com_box_pers1` varchar(150) NOT NULL default '0',
		`com_box_summ2` varchar(150) NOT NULL default '0',
		`com_box_pers2` varchar(150) NOT NULL default '0',		
		PRIMARY KEY ( `id` ),
		INDEX (`sum_val`),
		INDEX (`naps_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	$wpdb->query($sql);
}

add_action('item_direction_delete', 'item_direction_delete_dopsumcomis');
function item_direction_delete_dopsumcomis($item_id){
global $wpdb;	
	$wpdb->query("DELETE FROM ".$wpdb->prefix."naps_dopsumcomis WHERE naps_id = '$item_id'");
}

add_action('item_direction_edit', 'item_direction_dopsumcomis');
add_action('item_direction_add', 'item_direction_dopsumcomis');
function item_direction_dopsumcomis($item_id){ 
global $wpdb;	
	$wpdb->query("UPDATE ".$wpdb->prefix."naps_dopsumcomis SET naps_id = '$item_id' WHERE naps_id = '0'");
}

add_action('item_direction_copy', 'item_direction_copy_dopsumcomis', 1, 2);
function item_direction_copy_dopsumcomis($last_id, $new_id){
global $wpdb;
	$naps_meta = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."naps_dopsumcomis WHERE naps_id='$last_id'"); 
	foreach($naps_meta as $nap){
		$arr = array();
		foreach($nap as $k => $v){
			if($k == 'naps_id'){
				$arr[$k] = $new_id;
			} else {
				$arr[$k] = is_sum($v);
			}
		}
		$wpdb->insert($wpdb->prefix.'naps_dopsumcomis', $arr);
	}
}

function get_dopsumcomis_html_line($item){
	
	$temp = '
	<div class="construct_line js_dopsumcomis_line" data-id="'. is_isset($item, 'id') .'">';
		
		$temp .= '
		<div class="construct_item">
			<div class="construct_title">
				'. __('Amount','pn') .'
			</div>
			<div class="construct_input">
				<input type="text" name="" style="width: 100px;" class="rate_sum" value="'. is_sum(is_isset($item,'sum_val')) .'" />
			</div>
		</div>';
		
		if(isset($item->id)){
			$temp .= '<div class="construct_add js_dopsumcomis_add">'. __('Save','pn') .'</div>';
			$temp .= '<div class="construct_del js_dopsumcomis_del">'. __('Delete','pn') .'</div>';
		} else {
			$temp .= '<div class="construct_add js_dopsumcomis_add">'. __('Add new','pn') .'</div>';
		}
		
		$temp .= '
			<div class="premium_clear"></div>	

		<div class="construct_line_title">
			'. __('Additional sender fee','pn') .'
		</div>';
		
		$temp .= '
			<div class="construct_item">
				<div class="construct_input">
					<input type="text" name="" style="width: 100px;" class="rate_j1" value="'. is_sum(is_isset($item,'com_box_summ1')) .'" /> S
				</div>
			</div>
			<div class="construct_item">
				<div class="construct_input">
					<input type="text" name="" style="width: 100px;" class="rate_j2" value="'. is_sum(is_isset($item,'com_box_pers1')) .'" /> %
				</div>
			</div>
		';
			
		$temp .= '	
			<div class="premium_clear"></div>
		<div class="construct_line_title">
			'. __('Additional recipient fee','pn') .'
		</div>';	
		
		$temp .= '
			<div class="construct_item">
				<div class="construct_input">
					<input type="text" name="" style="width: 100px;" class="rate_j3" value="'. is_sum(is_isset($item,'com_box_summ2')) .'" /> S
				</div>
			</div>
			<div class="construct_item">
				<div class="construct_input">
					<input type="text" name="" style="width: 100px;" class="rate_j4" value="'. is_sum(is_isset($item,'com_box_pers2')) .'" /> %
				</div>
			</div>
		';
			
	$temp .= '		
			<div class="premium_clear"></div>
	</div>	
	';
	
	return $temp;
}

function get_dopsumcomis_html($data_id){
global $wpdb;	
	
	$temp = '';
	
	$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."naps_dopsumcomis WHERE naps_id='$data_id' ORDER BY (sum_val -0.0) ASC"); 
	foreach($items as $item){
		$temp .= get_dopsumcomis_html_line($item);	
	}
	
	$temp .= get_dopsumcomis_html_line('');
	
	return $temp;
}

add_action('premium_action_dopsumcomis_del', 'pn_premium_action_dopsumcomis_del');
function pn_premium_action_dopsumcomis_del(){
global $wpdb;

	only_post();
	
	header('Content-Type: application/json; charset=utf-8');
	
	$log = array();
	$log['status'] = 'success';	
	
	if(current_user_can('administrator') or current_user_can('pn_directions')){
		
		$data_id = intval(is_param_post('data_id'));
		$item_id = intval(is_param_post('item_id'));		
		$wpdb->query("DELETE FROM ".$wpdb->prefix."naps_dopsumcomis WHERE id='$item_id' AND naps_id='$data_id'");

		$log['html'] = get_dopsumcomis_html($data_id);
	}  		

	echo json_encode($log);	
	exit;
}

add_action('premium_action_dopsumcomis_add', 'pn_premium_action_dopsumcomis_add');
function pn_premium_action_dopsumcomis_add(){
global $wpdb;

	only_post();
	
	header('Content-Type: application/json; charset=utf-8');
	
	$log = array();
	$log['status'] = 'success';	
	
	if(current_user_can('administrator') or current_user_can('pn_directions')){
		
		$data_id = intval(is_param_post('data_id'));
		$item_id = intval(is_param_post('item_id'));
		
		$sum = is_sum(is_param_post('sum'));
		$j1 = is_sum(is_param_post('j1'));
		$j2 = is_sum(is_param_post('j2'));
		$j3 = is_sum(is_param_post('j3'));
		$j4 = is_sum(is_param_post('j4'));
		
		$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."naps_dopsumcomis WHERE id='$item_id' AND naps_id='$data_id'");
				
		$array = array();
		$array['naps_id'] = $data_id;
		$array['sum_val'] = $sum;
		$array['com_box_summ1'] = $j1;
		$array['com_box_pers1'] = $j2;
		$array['com_box_summ2'] = $j3;
		$array['com_box_pers2'] = $j4;
				
		if(isset($item->id)){
			$wpdb->update($wpdb->prefix.'naps_dopsumcomis', $array, array('id'=>$item->id));
		} else {
			$wpdb->insert($wpdb->prefix.'naps_dopsumcomis', $array);
		}
				
		$log['html'] = get_dopsumcomis_html($data_id);
	}  		
	
	echo json_encode($log);
	exit;
}

add_action('tab_direction_tab5', 'tab_direction_tab5_dopsumcomis', 20);
function tab_direction_tab5_dopsumcomis($data){	
	$data_id = intval(is_isset($data,'id'));
	$form = new PremiumForm();
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Additional fee that depends on exchanged amount','pn'); ?></span></div>
			<div id="dopsumcomis_html" data-id="<?php echo $data_id; ?>">
				<?php echo get_dopsumcomis_html($data_id); ?>
			</div>
		</div>
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<?php $form->help(__('More info','pn'), __('Specify the value of the additional fee that depends on the "Send" amount.','pn')); ?>
		</div>
	</div>

<script type="text/javascript">
$(function(){
	
	$(document).on('click', '.js_dopsumcomis_add', function(){ 
		var data_id = parseInt($('#dopsumcomis_html').attr('data-id'));
		var par = $(this).parents('.js_dopsumcomis_line');
		var item_id = parseInt(par.attr('data-id'));
		var sum = par.find('.rate_sum').val();
		var j1 = par.find('.rate_j1').val();
		var j2 = par.find('.rate_j2').val();
		var j3 = par.find('.rate_j3').val();
		var j4 = par.find('.rate_j4').val();
		
		$('#dopsumcomis_html').find('input').attr('disabled',true);
		$('#dopsumcomis_html').find('.js_dopsumcomis_add, .js_dopsumcomis_del').addClass('active');
		
		var param = 'data_id='+data_id+'&item_id='+item_id+'&sum='+sum+'&j1='+j1+'&j2='+j2+'&j3='+j3+'&j4='+j4;	
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('dopsumcomis_add', 'post');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				if(res['html']){
					$('#dopsumcomis_html').html(res['html']);
				} 
			}
		});		
		
		return false;
	});

	$(document).on('click', '.js_dopsumcomis_del', function(){
		var data_id = parseInt($('#dopsumcomis_html').attr('data-id'));
		var par = $(this).parents('.js_dopsumcomis_line');
		var item_id = parseInt(par.attr('data-id'));
		
		$('#dopsumcomis_html').find('input').attr('disabled',true);
		$('#dopsumcomis_html').find('.js_dopsumcomis_add, .js_dopsumcomis_del').addClass('active');
		
		var param = 'data_id='+data_id+'&item_id='+item_id;	
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('dopsumcomis_del','post');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				if(res['html']){
					$('#dopsumcomis_html').html(res['html']);
				} 
			}
		});		
		
		return false;
	});		

});
</script>		
	<?php 
}

add_filter('get_calc_data', 'get_calc_data_dopsumcomis', 100, 2);
function get_calc_data_dopsumcomis($cdata, $calc_data){
global $wpdb;
	
	$direction = $calc_data['direction'];
	$post_sum = is_sum(is_isset($calc_data,'post_sum'), 20);
	$dej = intval(is_isset($calc_data,'dej'));
	$direction_id = $direction->id;
	
	$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."naps_dopsumcomis WHERE naps_id='$direction_id'");
	if($cc > 0){
		$cdata['dis1c'] = 1;
		$cdata['dis2'] = 1;	
		$cdata['dis2c'] = 1;
	
		$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."naps_dopsumcomis WHERE naps_id='$direction_id' AND ('$post_sum' -0.0) >= sum_val ORDER BY (sum_val -0.0) DESC");
		if(isset($data->id)){
			$cdata['com_box_sum1'] = $data->com_box_summ1;
			$cdata['com_box_pers1'] = $data->com_box_pers1;		
			$cdata['com_box_sum2'] = $data->com_box_summ2;
			$cdata['com_box_pers2'] = $data->com_box_pers2;				
		}
	}
	
	return $cdata;
}	