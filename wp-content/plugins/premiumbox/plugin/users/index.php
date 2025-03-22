<?php
if( !defined( 'ABSPATH')){ exit(); }

add_filter('all_user_editform', 'premiumbox_all_user_editform', 10, 2);
function premiumbox_all_user_editform($options, $bd_data){
	$user_id = $bd_data->ID;
	
	$options[] = array(
		'view' => 'h3',
		'title' => __('User data','pn'),
		'submit' => __('Save','pn'),
	);	
	if(current_user_can('edit_users') or current_user_can('administrator')){
		if(current_user_can('pn_bids') or current_user_can('administrator')){
			$options['link_orders'] = array(
				'view' => 'textfield',
				'title' => __('Orders','pn'),
				'default' => '<a href="'. admin_url('admin.php?page=pn_bids&iduser='. $user_id) .'" class="button" target="_blank">'. __('User orders','pn') .'</a>',
			);	
		}
		$options['user_discount'] = array(
			'view' => 'input',
			'title' => __('Personal discount','pn'),
			'default' => is_sum($bd_data->user_discount),
			'name' => 'user_discount',
			'work' => 'sum',
		);
	}
	$options['all_discount'] = array(
		'view' => 'textfield',
		'title' => __('Discount (%)','pn'),
		'default' => get_user_discount($user_id).'%',
	);	
	$options['exchange_list'] = array(
		'view' => 'textfield',
		'title' => __('User exchange list','pn'),
		'default' => get_user_count_exchanges($user_id).' ('. get_user_sum_exchanges($user_id).' '. cur_type().')',
	);			
	
	return $options;
}

add_action('all_user_editform_post', 'premiumbox_all_user_editform_post'); 
function premiumbox_all_user_editform_post($new_user_data){
global $wpdb;

	if(current_user_can('edit_users') or current_user_can('administrator')){
		$new_user_data['user_discount'] = is_sum(is_param_post('user_discount'));
	}
	
	return $new_user_data;
}

add_filter('pntable_columns_all_users', 'premiumbox_pntable_columns_all_users');
function premiumbox_pntable_columns_all_users($columns){
	
	$columns['users_discount'] = __('Discount (%)','pn');
	$columns['count_exchanges'] = __('User exchange list','pn');
	
	return $columns;
}

add_filter('pntable_column_all_users', 'premiumbox_pntable_column_all_users', 10, 3); 
function premiumbox_pntable_column_all_users($empty='', $column_name, $item){
			
	if($column_name == 'users_discount'){
		return get_user_discount($item->ID, $item).'%';
	}
	if($column_name == 'count_exchanges'){
		return get_user_count_exchanges($item->ID).'<br />(<strong>'. get_user_sum_exchanges($item->ID) .'</strong>&nbsp;'. cur_type() .')';
	}	
			
	return $empty;	
}