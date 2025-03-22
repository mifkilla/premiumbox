<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Reserve settings for exchange directions[:en_US][ru_RU:]Настройки резерва для направлений обмена[:ru_RU]
description: [en_US:]Reserve settings for exchange directions[:en_US][ru_RU:]Настройки резерва для направлений обмена[:ru_RU]
version: 2.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_'.$name, 'bd_all_moduls_active_napsreserv');
add_action('all_bd_activated', 'bd_all_moduls_active_napsreserv');
function bd_all_moduls_active_napsreserv(){
global $wpdb;	
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'direction_reserv'");
    if($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `direction_reserv` varchar(250) NOT NULL default '0'");
    }
	$query = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix ."directions LIKE 'reserv_place'");
    if($query == 0){
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `reserv_place` varchar(250) NOT NULL default '0'");
    }	
}

add_filter('list_tabs_direction','list_tabs_direction_napsreserv');
function list_tabs_direction_napsreserv($list_tabs_naps){
	$list_tabs_naps['tab300'] = __('Reserve','pn');
	return $list_tabs_naps;
}

add_action('tab_direction_tab300','tab_direction_tab_napsreserv',10, 2);
function tab_direction_tab_napsreserv($data, $data_id){
	$form = new PremiumForm();
	
	$rplaced = array();
	$rplaced[0] = '--'. __('Default','pn') .'--';
	$rplaced[1] = '--'. __('From field for reserve','pn') .'--';
	$rplaced = apply_filters('reserv_place_list', $rplaced, 'direction');
	$rplaced = (array)$rplaced;

	$reserv_place = is_isset($data, 'reserv_place');
	$clr = ' pn_hide';
	if($reserv_place == '1'){
		$clr = '';
	}	
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Reserve','pn'); ?></span></div>
			<?php $form->select_search('reserv_place', $rplaced, $reserv_place);  ?>
		</div>
	</div>
	<div class="add_tabs_line line_currency_reserv	<?php echo $clr; ?>">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Field for reserve','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="direction_reserv" style="width: 100px;" value="<?php echo is_sum(is_isset($data,'direction_reserv')); ?>" />
			</div>
		</div>
	</div>

<script type="text/javascript">
jQuery(function($){ 
	$('#pn_reserv_place').on('change', function(){
		var id = $(this).val();
		if(id == '1') {
			$('.line_reserv_calc').hide();
			$('.line_currency_reserv').show();
		} else if(id == '2') {	
			$('.line_reserv_calc').show();
			$('.line_currency_reserv').hide();			
		} else {	
			$('.line_currency_reserv, .line_reserv_calc').hide();
		}			
		
		$('.premium_body').trigger('resize');
	});	
});
</script>	
<?php
}
 
add_filter('pn_direction_addform_post', 'napsreserv_pn_direction_addform_post');
function napsreserv_pn_direction_addform_post($array){
	$array['reserv_place'] = is_extension_name(is_param_post('reserv_place'));
	$array['direction_reserv'] = is_sum(is_param_post('direction_reserv'));
	return $array;
}

function update_direction_reserv($direction_id, $item='', $place='all'){
global $wpdb;
	$direction_id = intval($direction_id); 
	if($direction_id){ 
		if(!isset($item->id)){
			$item = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE id='$direction_id'");
		}
		if(isset($item->id)){
			$direction_reserv = is_sum($item->direction_reserv);
			$direction_reserv = apply_filters('update_direction_reserv', $direction_reserv, is_extension_name($item->reserv_place), $direction_id, $item, $place);
			do_action('after_update_direction_reserv', $direction_reserv, $direction_id, $item, $place);
		}
	}	
}

add_filter('change_bidstatus', 'napsreserv_change_bidstatus', 1000, 6);  
function napsreserv_change_bidstatus($item, $set_status, $place, $user_or_system, $old_status, $direction=''){ 
global $wpdb, $premiumbox;
	$item_id = $item->id;
	$virtual_status = array('archived','realdelete');
	if($item->status == $set_status or in_array($set_status, $virtual_status)){ 
		update_direction_reserv($item->direction_id, $direction, $set_status);
	}
	return $item;
}

add_action('item_direction_edit','napsreserv_item_direction_edit', 1000, 2); 
add_action('item_direction_add','napsreserv_item_direction_edit', 1000, 2);
function napsreserv_item_direction_edit($data_id, $array){
	update_direction_reserv($data_id);
}

add_filter('get_direction_reserv', 'napsreserv_get_direction_reserv', 9000, 4);
function napsreserv_get_direction_reserv($reserv, $vd1, $vd2, $direction){
	if($direction->reserv_place != '0'){
		return $direction->direction_reserv;
	}
	return $reserv;
}