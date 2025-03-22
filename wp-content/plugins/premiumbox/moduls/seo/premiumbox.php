<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('admin_menu', 'pn_admin_menu_seo', 11);
function pn_admin_menu_seo(){
	$plugin = get_plugin_class();
	if(current_user_can('administrator') or current_user_can('pn_seo')){
		add_submenu_page("all_seo", __('Exchange directions','pn'), __('Exchange directions','pn'), 'read', "seo_exchange_directions", array($plugin, 'admin_temp'));
	}
}

add_filter('all_xmlmap_option','pn_all_xmlmap_option');
function pn_all_xmlmap_option($options){
	$plugin = get_plugin_class();

	$options['exchanges'] = array(
		'view' => 'select',
		'title' => __('Show exchange directions','pn'),
		'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
		'default' => $plugin->get_option('xmlmap','exchanges'),
		'name' => 'exchanges',
	);	
	
	return $options;
}

if(!function_exists('pn_all_xmlmap_option_post')){
	add_action('all_xmlmap_option_post','pn_all_xmlmap_option_post');
	function pn_all_xmlmap_option_post(){
		$plugin = get_plugin_class();
		$options = array('exchanges');					
		foreach($options as $key){
			$plugin->update_option('xmlmap',$key, intval(is_param_post($key)));
		}				
	} 
}

add_action('insert_xmlmap_page','pn_insert_xmlmap_page');
function pn_insert_xmlmap_page(){
global $wpdb;	
	$plugin = get_plugin_class();
	$now_time = current_time('timestamp');
	if($plugin->get_option('xmlmap','exchanges') == 1){
		$show_data = pn_exchanges_output('smxml');
		if($show_data['mode'] == 1){
			$where = get_directions_where("smxml");
			$directions = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions WHERE $where ORDER BY site_order1 ASC");		
			foreach($directions as $direction){
				$output = apply_filters('get_direction_output', 1, $direction, 'smxml');
				if($output){
					$link = get_exchange_link($direction->direction_name);
?>
	<url>
		<loc><?php echo $link; ?></loc>
		<changefreq>daily</changefreq>
		<priority>0.6</priority>
		<lastmod><?php echo date('Y-m-d', $now_time); ?></lastmod>
	</url>
<?php		
				}
			}	
		}
	}
}

add_filter('set_exchange_cat_filters','set_exchange_cat_filters_sitemap');
function set_exchange_cat_filters_sitemap($cats){
	$cats['smxml'] = __('Sitemap XML','pn');
	return $cats;
}

add_filter('all_metasettings_option','pn_all_metasettings_option');
function pn_all_metasettings_option($options){
	$plugin = get_plugin_class();
	
	$n_options = array();
	$n_options['ya_goal1'] = array(
		'view' => 'inputbig',
		'title' => __('Choosing exchange direction','pn'),
		'default' => $plugin->get_option('seo','ya_goal1'),
		'name' => 'ya_goal1',
		'work' => 'input',
	);			
	$n_options['ya_goal2'] = array(
		'view' => 'inputbig',
		'title' => __('Entering exchange amount in exchange form','pn'),
		'default' => $plugin->get_option('seo','ya_goal2'),
		'name' => 'ya_goal2',
		'work' => 'input',
	);
	$n_options['ya_goal3'] = array(
		'view' => 'inputbig',
		'title' => __('Entering personal data in exchange form','pn'),
		'default' => $plugin->get_option('seo','ya_goal3'),
		'name' => 'ya_goal3',
		'work' => 'input',
	);
	$n_options['ya_goal4'] = array(
		'view' => 'inputbig',
		'title' => __('Create order button','pn'),
		'default' => $plugin->get_option('seo','ya_goal4'),
		'name' => 'ya_goal4',
		'work' => 'input',
	);
	$n_options['ya_goal5'] = array(
		'view' => 'inputbig',
		'title' => __('Create order button (in step 2)','pn'),
		'default' => $plugin->get_option('seo','ya_goal5'),
		'name' => 'ya_goal5',
		'work' => 'input',
	);
	$n_options['ya_goal6'] = array(
		'view' => 'inputbig',
		'title' => __('Cancel order button','pn'),
		'default' => $plugin->get_option('seo','ya_goal6'),
		'name' => 'ya_goal6',
		'work' => 'input',
	);
	$n_options['ya_goal7'] = array(
		'view' => 'inputbig',
		'title' => __('Go to payment/I paid button','pn'),
		'default' => $plugin->get_option('seo','ya_goal7'),
		'name' => 'ya_goal7',
		'work' => 'input',
	);
	$options = pn_array_insert($options, 'ya_metrika', $n_options);
	
	return $options;
}

add_action('wp_enqueue_scripts', 'seopremiumbox_themeinit', 0);
function seopremiumbox_themeinit(){
	$plugin = get_plugin_class();
	$ya_metrika = pn_strip_input($plugin->get_option('seo','ya_metrika'));
	if($ya_metrika){
		wp_enqueue_script('jquery-yametrika-js', $plugin->plugin_url .'moduls/seo/js/yaMetrika.js', false, $plugin->vers('0.1'));
	}
}

add_action('wp_footer' , 'wp_footer_seopremiumbox', 11);
function wp_footer_seopremiumbox(){
	$plugin = get_plugin_class();
	$ya_metrika = pn_strip_input($plugin->get_option('seo','ya_metrika'));
	if($ya_metrika){
	?>
	<script type="text/javascript">
	jQuery(function($){ 
		$(document).PremiumYaMetrika({
			'id': <?php echo $ya_metrika; ?>,
<?php 
$r=0;
while($r++<7){
?>
			'goal_<?php echo $r; ?>': '<?php echo pn_strip_input($plugin->get_option("seo","ya_goal" . $r)); ?>',
<?php } ?>
			'test': <?php echo $plugin->is_debug_mode(); ?>
		}); 
	});
	</script>
	<?php
	}
} 

add_filter('selects_all_seo', 'dir_selects_all_seo');
function dir_selects_all_seo($selects){
	$selects[] = array(
		'link' => admin_url("admin.php?page=all_seo&place=exchange"),
		'title' => __('Exchange form','pn'),
		'default' => 'exchange',
	);	
	return $selects;				
}

add_filter('all_seo_option','pn_all_seo_option', 10, 2);
function pn_all_seo_option($options, $place=''){
	$plugin = get_plugin_class();

	if($place and $place == 'exchange'){

		$options = pn_array_unset($options, array('title_line','line2','exchange_title','exchange_key','exchange_descr','ogp_exchange_title','ogp_exchange_descr','ogp_exchange_img','exchange_temp'));

		$tags = array();
		$tags['sitename'] = array(
			'title' => __('Site name','pn'),
			'start' => '[sitename]',
		);	
		$tags['title1'] = array(
			'title' => sprintf(__('Currency title %s','pn'), '1'),
			'start' => '[title1]',
		);
		$tags['title2'] = array(
			'title' => sprintf(__('Currency title %s','pn'), '2'),
			'start' => '[title2]',
		);
		$tags['curr_title1'] = array(
			'title' => sprintf(__('Currency code %s','pn'), '1'),
			'start' => '[curr_title1]',
		);
		$tags['curr_title2'] = array(
			'title' => sprintf(__('Currency code %s','pn'), '2'),
			'start' => '[curr_title2]',
		);		
		$tags['xml_title1'] = array(
			'title' => sprintf(__('Currency XML name %s','pn'), '1'),
			'start' => '[xml_title1]',
		);
		$tags['xml_title2'] = array(
			'title' => sprintf(__('Currency XML name %s','pn'), '2'),
			'start' => '[xml_title2]',
		);										
		$options['exch_temp'] = array(
			'view' => 'editor',
			'title' => __('Title template','pn'),
			'default' => $plugin->get_option('seo','exch_temp'),
			'tags' => $tags,
			'rows' => '3',
			'word_count' => 1,
			'name' => 'exch_temp',
			'work' => 'input',
			'ml' => 1,
		);						
		$options['exch_temp2'] = array(
			'view' => 'editor',
			'title' => __('Exchange page title template (H1)','pn'),
			'default' => $plugin->get_option('seo','exch_temp2'),
			'tags' => $tags,
			'rows' => '3',
			'name' => 'exch_temp2',
			'work' => 'input',
			'ml' => 1,
		);		
		$options['exch_descr'] = array(
			'view' => 'editor',
			'title' => __('Description','pn'),
			'default' => $plugin->get_option('seo','exch_descr'),
			'tags' => $tags,
			'rows' => '5',
			'word_count' => 1,
			'name' => 'exch_descr',
			'work' => 'input',
			'ml' => 1,
		);
		$options['exch_key'] = array(
			'view' => 'editor',
			'title' => __('Keywords','pn'),
			'default' => $plugin->get_option('seo','exch_key'),
			'tags' => $tags,
			'rows' => '3',
			'word_count' => 1,
			'name' => 'exch_key',
			'work' => 'input',
			'ml' => 1,
		);
		$options['ogp_exch_title'] = array(
			'view' => 'editor',
			'title' => __('OGP title','pn'),
			'default' => $plugin->get_option('seo','ogp_exch_title'),
			'tags' => $tags,
			'rows' => '3',
			'word_count' => 1,
			'name' => 'ogp_exch_title',
			'work' => 'input',
			'ml' => 1,			
		);	
		$options['ogp_exch_descr'] = array( 
			'view' => 'editor',
			'title' => __('OGP description','pn'),
			'default' => $plugin->get_option('seo','ogp_exch_descr'),
			'tags' => $tags,
			'name' => 'ogp_exch_descr',
			'work' => 'input',
			'rows' => '6',
			'word_count' => 1,
			'ml' => 1,
		);	
		$options['ogp_exch_img'] = array(
			'view' => 'uploader',
			'title' => __('OGP image', 'pn'),
			'default' => $plugin->get_option('seo','ogp_exch_img'),
			'name' => 'ogp_exch_img',
			'work' => 'input',
			'ml' => 1,
		);		
	
	}
	
	return $options;
}

/* exchange directions */
add_filter('list_tabs_direction','list_tabs_direction_seo');
function list_tabs_direction_seo($lists){
	$lists['tab10'] = __('SEO','pn');
	return $lists;
}

add_action('tab_direction_tab10', 'seo_tab_direction_tab10', 99, 2);
function seo_tab_direction_tab10($data, $data_id){
				
	$form = new PremiumForm();
				
	$seo = get_direction_meta($data_id, 'seo');	

	$atts_input = array();
	$atts_input['class'] = 'big_input';
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange title (H1)','pn'); ?></span></div>
			<?php $form->input('seo_exch_title' , is_isset($seo,'seo_exch_title'), $atts_input, 1); ?>			
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Page title','pn'); ?></span></div>
			<?php $form->input('seo_title' , is_isset($seo,'seo_title'), $atts_input, 1); ?>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Page keywords','pn'); ?></span></div>
			<?php $form->textarea('seo_key', is_isset($seo,'seo_key'), '6', '', 1, 1); ?>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Page description','pn'); ?></span></div>
			<?php $form->textarea('seo_descr', is_isset($seo,'seo_descr'), '12', '', 1, 1); ?>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('OGP title','pn'); ?></span></div>
			<?php $form->input('ogp_title' , is_isset($seo,'ogp_title'), $atts_input, 1); ?>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('OGP description','pn'); ?></span></div>
			<?php $form->textarea('ogp_descr', is_isset($seo,'ogp_descr'), '12', '', 1, 1); ?>
		</div>
	</div>	
	<?php						
}
 
add_action('item_direction_edit','seo_item_direction_edit'); 
add_action('item_direction_add','seo_item_direction_edit');
function seo_item_direction_edit($data_id){
	$seo = array();
	$seo['seo_exch_title'] = pn_strip_input(is_param_post_ml('seo_exch_title'));
	$seo['seo_title'] = pn_strip_input(is_param_post_ml('seo_title'));					
	$seo['seo_key'] = pn_strip_input(is_param_post_ml('seo_key'));
	$seo['seo_descr'] = pn_strip_input(is_param_post_ml('seo_descr'));								
	$seo['ogp_title'] = pn_strip_input(is_param_post_ml('ogp_title'));
	$seo['ogp_descr'] = pn_strip_input(is_param_post_ml('ogp_descr'));
	update_direction_meta($data_id, 'seo', $seo);						
}
/* end exchange directions */

function replace_exchange_seo($text, $direction_data){
	
	$text = str_replace('[title1]', pn_strip_input($direction_data->item_give), $text);
	$text = str_replace('[title2]', pn_strip_input($direction_data->item_get), $text);
	$text = str_replace('[sitename]', pn_site_name(), $text);
	$text = str_replace('[curr_title1]', pn_strip_input($direction_data->vd1->currency_code_title), $text);
	$text = str_replace('[curr_title2]', pn_strip_input($direction_data->vd2->currency_code_title), $text);
	$text = str_replace('[xml_title1]', pn_strip_input($direction_data->vd1->xml_value), $text);
	$text = str_replace('[xml_title2]', pn_strip_input($direction_data->vd2->xml_value), $text);	
	
	return $text;
}

/* exchange title */
add_filter('get_exchange_title' , 'get_exchange_title_seo', 99, 5);
function get_exchange_title_seo($title, $direction_id, $item_title1, $item_title2, $direction_data){
global $exchange_seo;
	
	$direction_id = intval($direction_id);
	
	if(!$exchange_seo){
		$exchange_seo = get_direction_meta($direction_id, 'seo');	
	}
		
	$plugin = get_plugin_class();	
	$seo_exch_title = pn_strip_input(ctv_ml(is_isset($exchange_seo,'seo_exch_title')));
	if(!$seo_exch_title){
		$seo_exch_title = pn_strip_input(ctv_ml($plugin->get_option('seo','exch_temp2')));
	}
	$seo_exch_title = replace_exchange_seo($seo_exch_title, $direction_data);
	
	if($seo_exch_title){
		return $seo_exch_title;
	}		
	
	return $title;
}
/* end exchange title */

add_filter('exchange_step_meta', 'seo_exchange_step_meta');
function seo_exchange_step_meta($log){
global $direction_data;
	if(isset($direction_data->direction_id)){
		$direction_id = intval($direction_data->direction_id);
		$exchange_seo = get_direction_meta($direction_id, 'seo');
		$plugin = get_plugin_class();
		
		$key = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'seo_key')));
		if(!$key){
			$key = pn_strip_input(ctv_ml($plugin->get_option('seo','exch_key')));
		}	
		$key = replace_exchange_seo($key, $direction_data);
		
		$descr = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'seo_descr')));	
		if(!$descr){
			$descr = pn_strip_input(ctv_ml($plugin->get_option('seo','exch_descr')));
		}	
		$descr = replace_exchange_seo($descr, $direction_data);
		
		$log['keywords'] = $key;
		$log['description'] = $descr;
	}
		return $log;
}

add_filter('wp_title' , 'direction_wp_title_seo', 100);
function direction_wp_title_seo($title){
global $direction_data, $exchange_seo;	
	if(is_pn_page('exchange')){
		if(isset($direction_data->direction_id)){
			$direction_id = intval($direction_data->direction_id);
			if(!$exchange_seo){
				$exchange_seo = get_direction_meta($direction_id, 'seo');	
			}		
			
			$plugin = get_plugin_class();	
			$seo_title = pn_strip_input(ctv_ml(is_isset($exchange_seo,'seo_title')));
			if(!$seo_title){
				$seo_title = pn_strip_input(ctv_ml($plugin->get_option('seo','exch_temp')));
			}
			$seo_title = replace_exchange_seo($seo_title, $direction_data);
			
			if($seo_title){
				return $seo_title;
			}			
						
		} else {
			return __('Error 404','pn');
		}
	} elseif(is_pn_page('hst')){
		return get_exchangestep_title();
	}			
	return $title;			
}				

add_filter('pn_seo_name', 'direction_pn_seo_name');
function direction_pn_seo_name($pn_seo_name){
	if(is_pn_page('exchange')){
		return '';	
	}	
	return $pn_seo_name;
}		

add_action('wp_head' , 'direction_wp_head_seo');
function direction_wp_head_seo(){
global $direction_data, $exchange_seo;	

	$plugin = get_plugin_class();

	if(is_pn_page('exchange')){
		if(isset($direction_data->direction_id)){
			$direction_id = intval($direction_data->direction_id);
			if(!$exchange_seo){
				$exchange_seo = get_direction_meta($direction_id, 'seo');	
			}	


			$key = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'seo_key')));
			if(!$key){
				$key = pn_strip_input(ctv_ml($plugin->get_option('seo','exch_key')));
			}	
			$key = replace_exchange_seo($key, $direction_data);
		
			$descr = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'seo_descr')));	
			if(!$descr){
				$descr = pn_strip_input(ctv_ml($plugin->get_option('seo','exch_descr')));
			}	
			$descr = replace_exchange_seo($descr, $direction_data);

			$ogp_title = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'ogp_title')));	
			if(!$ogp_title){
				$ogp_title = pn_strip_input(ctv_ml($plugin->get_option('seo','ogp_exch_title')));
			}	
			$ogp_title = replace_exchange_seo($ogp_title, $direction_data);
			
			$ogp_descr = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'ogp_descr')));	
			if(!$ogp_descr){
				$ogp_descr = pn_strip_input(ctv_ml($plugin->get_option('seo','ogp_exch_descr')));
			}	
			$ogp_descr = replace_exchange_seo($ogp_descr, $direction_data);	

			$ogp_image = ctv_ml(is_isset($exchange_seo, 'ogp_image'));	
			if(!$ogp_image){
				$ogp_image = ctv_ml($plugin->get_option('seo','ogp_exch_img'));
			}	
			if(!$ogp_image){
				$ogp_image = ctv_ml($plugin->get_option('seo','ogp_def_img'));
			}	
			?><meta name="keywords" content="<?php echo get_seo_keys($key); ?>" />
<meta name="description" content="<?php echo $descr; ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<?php echo lang_self_link(); ?>" />
<meta property="og:site_name" content="<?php echo pn_site_name(); ?>" />
<?php if($ogp_descr){ ?>
<meta property="og:description" content="<?php echo $ogp_descr; ?>" />
<?php } ?>
<?php if($ogp_title){ ?>
<meta property="og:title" content="<?php echo $ogp_title; ?>" />
<?php } ?>
<?php if($ogp_image){ ?>
<meta property="og:image" content="<?php echo $ogp_image; ?>" />
<?php }	
		}		
	}
} 