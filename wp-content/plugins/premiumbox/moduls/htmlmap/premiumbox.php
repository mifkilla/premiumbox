<?php
if( !defined( 'ABSPATH')){ exit(); }

add_filter('all_htmlmap_option','pn_all_htmlmap_option');
function pn_all_htmlmap_option($options){
	$plugin = get_plugin_class();

	$options['exchanges'] = array(
		'view' => 'select',
		'title' => __('Show exchange directions','pn'),
		'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
		'default' => $plugin->get_option('htmlmap','exchanges'),
		'name' => 'exchanges',
	);
	$options['line_exchanges'] = array(
		'view' => 'line',
	);	
	
	return $options;
}

add_action('all_htmlmap_option_post','def_all_htmlmap_option_post');
function def_all_htmlmap_option_post(){
	$plugin = get_plugin_class();
	$options = array('exchanges');					
	foreach($options as $key){
		$plugin->update_option('htmlmap',$key, intval(is_param_post($key)));
	}				
} 

add_filter('insert_sitemap_page','pn_insert_sitemap_page');
function pn_insert_sitemap_page($temp){
	global $wpdb;
	
	$plugin = get_plugin_class();

	if($plugin->get_option('htmlmap','exchanges') == 1){
				
		$show_data = pn_exchanges_output('sm');		
		if($show_data['text']){
			$temp .= '<div class="resultfalse">'. $show_data['text'] .'</div>';
		}
				
		if($show_data['mode'] == 1){
					
			$v = get_currency_data();				
			$where = get_directions_where('sm'); 
			$directions = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions WHERE $where ORDER BY site_order1 ASC");
					
			$temp .= '
			<div class="sitemap_block">
				<div class="sitemap_block_ins">';	
				
					$sitemap_block_title = '
					<div class="sitemap_title">
						<div class="sitemap_title_ins">
							<div class="sitemap_title_abs"></div>
							'. __('Exchange directions','pn') .'
						</div>
					</div>
						<div class="clear"></div>
					';
					$temp .= apply_filters('sitemap_block_title', $sitemap_block_title, 'exchanges');
							 
					$temp .= '
					<div class="sitemap_once">
						<div class="sitemap_once_ins">
							<ul class="sitemap_ul_exchanges">';
							
							foreach($directions as $direction){
								$output = apply_filters('get_direction_output', 1, $direction, 'sm');
								if($output){
									$currency_id_give = $direction->currency_id_give;
									$currency_id_get = $direction->currency_id_get;
									
									if(isset($v[$currency_id_give]) and isset($v[$currency_id_get])){
										$vd1 = $v[$currency_id_give];
										$vd2 = $v[$currency_id_get];
										
										$title1 = get_currency_title($vd1);
										$title2 = get_currency_title($vd2);
										$link = get_exchange_link($direction->direction_name);
										$line = '<li><a href="'. $link .'">'. $title1 .' &rarr; '. $title2 .'</a></li>';
										$temp .= apply_filters('sitemap_exchange_title', $line, $vd1, $vd2, $link, $direction);
									}
								}
							}	
						
							$temp .= '
							</ul>
								<div class="clear"></div>
						</div>
					</div>	
				</div>
			</div>	
			';	
					
		}		
	}	
	
	return $temp;
}

add_filter('set_exchange_cat_filters','set_exchange_cat_filters_htmlmap');
function set_exchange_cat_filters_htmlmap($cats){
	$cats['sm'] = __('Sitemap HTML','pn');
	return $cats;
}