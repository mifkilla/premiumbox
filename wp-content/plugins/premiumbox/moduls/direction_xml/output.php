<?php
if( !defined( 'ABSPATH')){ exit(); }

/* add_action('premium_request_exportjson','def_premium_request_exportjson');
function def_premium_request_exportjson(){
global $wpdb, $premiumbox;

	header('Content-Type: application/json; charset=utf-8');

	if($premiumbox->get_option('up_mode') == 1){
		txtxml_create_error(__('Maintenance','pn'), 'json');	
	}
	if(!check_hash_cron() and $premiumbox->get_option('txtxml','hash') == 1){	
		txtxml_create_error(__('Maintenance','pn'), 'json');		
	}	
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml','json'), 'json');
	if($show_files != 1){
		txtxml_create_error(__('Maintenance','pn'), 'json');
	}

	$show_data = pn_exchanges_output('files');
	if($show_data['mode'] != 1){
		txtxml_create_error(__('Maintenance','pn'), 'json');
	}

	if($premiumbox->get_option('txtxml','create') != 1){
		txtxml_create_bd();
	}	



	exit;
} */

add_action('premium_request_exporttxt','def_premium_request_exporttxt');
function def_premium_request_exporttxt(){
global $wpdb, $premiumbox;	
	
	header("Content-type: text/txt; charset=utf-8");
	
	if($premiumbox->get_option('up_mode') == 1){
		txtxml_create_error(__('Maintenance','pn'), 'txt');	
	}
	if(!check_hash_cron() and $premiumbox->get_option('txtxml','hash') == 1){	
		txtxml_create_error(__('Maintenance','pn'), 'txt');		
	}
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml','txt'), 'txt');
	if($show_files != 1){
		txtxml_create_error(__('Maintenance','pn'), 'txt');
	}	

	$show_data = pn_exchanges_output('files');
	if($show_data['mode'] != 1){
		txtxml_create_error(__('Maintenance','pn'), 'txt');
	}

	if($premiumbox->get_option('txtxml','create') != 1){
		txtxml_create_bd();
	}	
	
	$create_time = get_option('txtxml_create_time');
	pn_header_lastmodifier($create_time);
	
	$directions = get_array_option($premiumbox, 'pn_directions_filedata');
	if(!is_array($directions)){ $directions = array(); }

	foreach($directions as $direction){
		echo is_isset($direction, 'from') .';'. is_isset($direction, 'to') .';'. is_isset($direction, 'in') .';'. is_isset($direction, 'out') .';'. is_isset($direction, 'amount') .";\n"; 
	}
	
	exit;
}

add_action('premium_request_exportxml','def_premium_request_exportxml');
function def_premium_request_exportxml(){
global $wpdb, $premiumbox;

	header("Content-Type: text/xml; charset=utf-8"); 
	
	if($premiumbox->get_option('up_mode') == 1){
		txtxml_create_error(__('Maintenance','pn'), 'xml');	
	}
	
	if(!check_hash_cron() and $premiumbox->get_option('txtxml','hash') == 1){	
		txtxml_create_error(__('Maintenance','pn'), 'xml');		
	}	
	
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml','xml'), 'xml');
	if($show_files != 1){
		txtxml_create_error(__('Maintenance','pn'), 'xml');
	}
	
	$show_data = pn_exchanges_output('files');
	if($show_data['mode'] != 1){
		txtxml_create_error(__('Maintenance','pn'), 'xml');
	}	
	
	if($premiumbox->get_option('txtxml','create') != 1){
		txtxml_create_bd();
	}	
	
	$create_time = get_option('txtxml_create_time');
	pn_header_lastmodifier($create_time);
	
	echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
	
	$directions = get_array_option($premiumbox, 'pn_directions_filedata');
	if(!is_array($directions)){ $directions = array(); }
	
	?>
	<rates>
	<?php	
	foreach($directions as $direction){
		$cities = explode(',', is_isset($direction,'cities'));
		if(isset($direction['cities'])){
			unset($direction['cities']);
		}
		foreach($cities as $city){
			$city = trim($city);
			if($city){
				$direction['city'] = $city;
			}			
		?>
		<item>
		<?php
		foreach($direction as $line_key => $line_value){ 				
			?>
			<<?php echo $line_key; ?>><?php echo $line_value; ?></<?php echo $line_key; ?>>
			<?php		
		} 
		?>
		</item>
		<?php
		}				
	}
	?>
	</rates>
	<?php		
	exit;
}

function txtxml_create_error($text, $type){
	echo get_txtxml_create_error($text, $type);
	exit;
}

function get_txtxml_create_error($text, $type){
	$error = '';
	if($type == 'xml'){
		$error .= '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		$error .= '<error>'. $text .'</error>';
	} elseif($type == 'json'){	
		
	} else {
		$error .= $text;
	}
	return $error;	
}