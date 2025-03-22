<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('pn_adminpage_content_all_cron','newparser_pn_adminpage_content_pn_cron',9); 
add_action('pn_adminpage_content_pn_new_parser','newparser_pn_adminpage_content_pn_cron',9);
add_action('pn_adminpage_content_pn_settings_new_parser','newparser_pn_adminpage_content_pn_cron',9);
function newparser_pn_adminpage_content_pn_cron(){
?>
	<div class="premium_substrate">
		<?php _e('Cron URL for updating rates of CB and cryptocurrencies','pn'); ?><br /> 
		<a href="<?php echo get_cron_link('new_parser_upload_data'); ?>" target="_blank"><?php echo get_cron_link('new_parser_upload_data'); ?></a>
	</div>	
<?php
}

/* currency codes */
add_filter('standart_course_cc', 'newparser_standart_course_cc', 10, 2); 
function newparser_standart_course_cc($ind, $item){
	if(is_isset($item,'new_parser') > 0){
		return 1;
	}
	return $ind;
}

add_filter('pn_currency_code_addform', 'newparser_pn_currency_code_addform', 10, 2);
function newparser_pn_currency_code_addform($options, $data){
global $wpdb;
	
	$options[] = array(
		'view' => 'line',
	);	
	$options[] = array(
		'view' => 'h3',
		'title' => '',
		'submit' => __('Save','pn'),
	);
	
	$show_parser_course = apply_filters('show_parser_course', 1);
	
	$parsers = array();
	$parsers[0] = '-- '. __('No item','pn') .' --';
	$en_parsers = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."parser_pairs ORDER BY menu_order ASC");
	foreach($en_parsers as $item){
		$string = pn_strip_input($item->title_pair_give).'-'.pn_strip_input($item->title_pair_get).' ('. pn_strip_input($item->title_birg) .')';
		if($show_parser_course == 1){
			$string .= '['. is_rate(get_parser_course($item->pair_give), get_parser_course($item->pair_get)) .']';
		}
		$parsers[$item->id] = $string;
	}
	$options['new_parser'] = array(
		'view' => 'select',
		'title' => __('Automatic change of rate','pn'),
		'options' => $parsers,
		'default' => is_isset($data, 'new_parser'),
		'name' => 'new_parser',
		'work' => 'input',
	);	
	$options['new_parser_actions'] = array(
		'view' => 'inputbig',
		'title' => __('Add to rate','pn'),
		'default' => is_isset($data, 'new_parser_actions'),
		'name' => 'new_parser_actions',
	);
	
	return $options;
}

add_filter('pn_currency_code_addform_post', 'newparser_pn_currency_code_addform_post');
function newparser_pn_currency_code_addform_post($array){
	$array['new_parser'] = intval(is_param_post('new_parser'));
	$array['new_parser_actions'] = pn_parser_num(is_param_post('new_parser_actions'));
	return $array;
}

add_filter('pntable_columns_pn_currency_codes', 'newparser_pntable_columns_pn_currency_codes');
function newparser_pntable_columns_pn_currency_codes($columns){
	$columns['new_parser'] = __('Rate automatic adjustment','pn');
	return $columns;
}

add_filter('pntable_column_pn_currency_codes', 'newparser_pntable_column_pn_currency_codes', 10, 3);
function newparser_pntable_column_pn_currency_codes($html, $column_name, $item){
	
	if($column_name == 'new_parser'){	
		$parser_pairs = get_parser_pairs_course();
		$show_parser_course = apply_filters('show_parser_course', 1);
		
		$html = '
		<div style="width: 200px;">
		';
			$html = '
			<select name="new_parser['. $item->id .']" autocomplete="off" id="currency_code_new_parser_'. $item->id .'" class="currency_code_new_parser" style="width: 200px; display: block; margin: 0 0 10px;"> 
			';
			$enable = 0;
				$html .= '<option value="0" '. selected($item->new_parser,0,false) .'>-- '. __('No item','pn') .' --</option>';
				if(is_array($parser_pairs)){
					foreach($parser_pairs as $parser){
						if($item->new_parser == $parser->id){
							$enable = 1;
						}
							
						$html .= '<option value="'. $parser->id .'" '. selected($item->new_parser, $parser->id,false) .'>'. pn_strip_input($parser->title_pair_give).'-'.pn_strip_input($parser->title_pair_get).' ('. pn_strip_input(ctv_ml($parser->title_birg)) .')';
						if($show_parser_course == 1){
							$html .= ' ['. is_rate(get_parser_course($parser->pair_give), get_parser_course($parser->pair_get)) .']';
						}
						$html .= '</option>';
					}
				}
			$style = 'style="display: none;"';	
			if($enable == 1){
				$style = '';
			}
			$html .= '
			</select>
			<div id="the_currency_code_new_parser_'. $item->id .'" '. $style .'>
				<input type="text" name="new_parser_actions['. $item->id .']" value="'. pn_strip_input($item->new_parser_actions) .'" />
			</div>		
			';
		$html .= '</div>';	
	}
	
	return $html;
}

add_action('pntable_currency_codes_save','new_parser_pntable_currency_codes_save');
function new_parser_pntable_currency_codes_save(){
global $wpdb;
	
	if(isset($_POST['new_parser'], $_POST['new_parser_actions']) and is_array($_POST['new_parser'])){	
		foreach($_POST['new_parser'] as $id => $parser_id){		
			$id = intval($id);
			$new_parser = intval($parser_id);
			$new_parser_actions = pn_parser_num($_POST['new_parser_actions'][$id]);							
					
			$array = array();
			if($new_parser > 0){
				$array['new_parser'] = $new_parser;
				$array['new_parser_actions'] = $new_parser_actions;			
			} else {
				$array['new_parser'] = 0;
				$array['new_parser_actions'] = 0;										
			}	
			$wpdb->update($wpdb->prefix.'currency_codes', $array, array('id'=>$id));		
		}		
	}	
}

add_action('pn_adminpage_content_pn_currency_codes','new_parser_pn_adminpage_content_pn_currency_codes');
function new_parser_pn_adminpage_content_pn_currency_codes(){
?>
	<style>
	.not_adaptive th.pntable-column-new_parser{ width: 200px; }
	</style>	
	<script type="text/javascript">
	jQuery(function($){
		$('.currency_code_new_parser').on('change', function(){
			var id = $(this).attr('id').replace('currency_code_new_parser_','');
			var vale = $(this).val();
			if(vale > 0){
				$('#the_currency_code_new_parser_'+id).show();
			} else {
				$('#the_currency_code_new_parser_'+id).hide();
			}
		});		
	});
	</script>
<?php	
} 

add_filter('is_cc_rate', 'new_parser_is_cc_rate', 50, 2);
function new_parser_is_cc_rate($course, $item){	
	if($item->new_parser > 0){
		$pairs = get_parser_pairs();
		$pairs_course = get_parser_pairs_course();
		if(isset($pairs_course[$item->new_parser])){
			$curs_data = $pairs_course[$item->new_parser];
			$curs = is_rate(get_parser_course($curs_data->pair_give, $pairs), get_parser_course($curs_data->pair_get, $pairs));
			$new_curs = rate_plus_interest($curs, $item->new_parser_actions);	
			return is_sum($new_curs);
		}
			return 0;
	}	
	return $course;
}
/* end currency codes */

/* directions */
add_filter('standart_course_direction', 'new_parser_standart_course_direction', 10, 2);
function new_parser_standart_course_direction($ind, $item){
	if($item->new_parser > 0){
		$ind = 1;
	}
	return $ind;
}

add_action('pn_adminpage_content_pn_directions', 'new_parser_pn_adminpage_content_pn_directions');
function new_parser_pn_adminpage_content_pn_directions(){
?>	
<style>
.not_adaptive th.pntable-column-new_parser{ width: 230px; }
</style>
<script type="text/javascript">
jQuery(function($){
	$('.directions_new_parser').change(function(){
		var id = $(this).attr('id').replace('directions_new_parser_','');
		var vale = $(this).val();
		if(vale > 0){
			$('#the_directions_new_parser_'+id).show();
		} else {
			$('#the_directions_new_parser_'+id).hide();
		}
	});			
});
</script>
<?php
}

add_filter('pntable_columns_pn_directions', 'new_parser_pntable_columns_pn_directions');
function new_parser_pntable_columns_pn_directions($columns){
	$new_columns = array();
	$new_columns['new_parser'] = __('Auto adjust rate','pn');
	$columns = pn_array_insert($columns, 'course_get', $new_columns);
	return $columns;
}

add_action('pntable_directions_save', 'new_parser_pn_directions_save');
function new_parser_pn_directions_save(){
global $wpdb;	

	if(isset($_POST['new_parser'], $_POST['new_parser_actions_give'], $_POST['new_parser_actions_get']) and is_array($_POST['new_parser'])){ 	
		foreach($_POST['new_parser'] as $id => $parser_id){			
			$id = intval($id);
			$parser = intval($parser_id);
			$nums1 = pn_parser_num($_POST['new_parser_actions_give'][$id]);			
			$nums2 = pn_parser_num($_POST['new_parser_actions_get'][$id]);			
			$array = array();
			if($parser > 0){
				$array['new_parser'] = $parser;
				$array['new_parser_actions_give'] = $nums1;			
				$array['new_parser_actions_get'] = $nums2;
			} else {
				$array['new_parser'] = 0;
				$array['new_parser_actions_give'] = 0;			
				$array['new_parser_actions_get'] = 0;							
			}				
			$wpdb->update($wpdb->prefix.'directions', $array, array('id'=>$id));			
		}			
	}	
}

add_filter('pntable_column_pn_directions', 'new_parser_pntable_column_pn_directions', 10, 3);
function new_parser_pntable_column_pn_directions($show, $column_name, $item){
	if($column_name == 'new_parser'){
		
		$parser_pairs = get_parser_pairs_course();
		$show_parser_course = apply_filters('show_parser_course', 1);
		
		$html = '
		<div style="width: 230px;">
		';
			
		$html .= '
		<select name="new_parser['. $item->id .']" autocomplete="off" id="directions_new_parser_'. $item->id .'" class="directions_new_parser" style="width: 230px; display: block; margin: 0 0 10px;"> 
		';
			$enable = 0;
			
			$html .= '<option value="0" '. selected($item->new_parser,0,false) .'>-- '. __('No item','pn') .' --</option>';
			
			if(is_array($parser_pairs)){
				foreach($parser_pairs as $parser){
					if($item->new_parser == $parser->id){
						$enable = 1;
					}
						
					$html .= '<option value="'. $parser->id .'" '. selected($item->new_parser,$parser->id,false) .'>'. pn_strip_input($parser->title_pair_give).'-'.pn_strip_input($parser->title_pair_get).' ('. pn_strip_input(ctv_ml($parser->title_birg)) .')';
					if($show_parser_course == 1){
						$html .= '['. get_parser_course($parser->pair_give) .' => '. get_parser_course($parser->pair_get) .']';
					}
					$html .= '</option>';
				}
			}
			
		$style = 'style="display: none;"';	
		if($enable == 1){
			$style = '';
		}
				
		$html .= '
		</select>
			
		<div id="the_directions_new_parser_'. $item->id .'" '. $style .'>
			<input type="text" name="new_parser_actions_give['. $item->id .']" style="width: 95px; float: left; margin: 0px 0px 0 0;" value="'. pn_strip_input($item->new_parser_actions_give) .'" />
			<div style="float: left; margin: 3px 2px 0 2px;">=></div>
			<input type="text" name="new_parser_actions_get['. $item->id .']" style="width: 95px; float: left; margin: 0px 0px 0 0;" value="'. pn_strip_input($item->new_parser_actions_get) .'" />				
				<div class="premium_clear"></div>
		</div>		
		';
			
		$html .= '</div>';
			return $html;
	}
	return $show;
}

if(!function_exists('autoadjust_list_tabs_direction')){
	add_filter('list_tabs_direction', 'autoadjust_list_tabs_direction');
	function autoadjust_list_tabs_direction($list_tabs){
		$new_list_tabs = array();
		$new_list_tabs['tab3'] = __('Auto adjust rate','pn');
		$list_tabs = pn_array_insert($list_tabs, 'tab2',$new_list_tabs); 	
		return $list_tabs;
	}
}

add_action('tab_direction_tab3', 'new_parser_tab_direction_tab3', 1, 2);
function new_parser_tab_direction_tab3($data, $data_id){

	global $wpdb;
	
	$show_parser_course = apply_filters('show_parser_course', 1);
	
	$parsers = array();
	$parsers[0] = '-- '. __('No item','pn') .' --';
	$en_parsers = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."parser_pairs ORDER BY menu_order ASC");
	if(is_array($en_parsers)){
		foreach($en_parsers as $item){
			$string = pn_strip_input($item->title_pair_give).'-'.pn_strip_input($item->title_pair_get).' ('. pn_strip_input(ctv_ml($item->title_birg)) .')';
			if($show_parser_course == 1){
				$string .= ' ['. get_parser_course($item->pair_give) .' => '. get_parser_course($item->pair_get) .']';
			}
			$parsers[$item->id] = $string;
		}
	}
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_title"><?php _e('Parsers 2.0','pn'); ?></div>
		<div class="add_tabs_submit">
			<input type="submit" name="" class="button" value="<?php _e('Save'); ?>" />
		</div>
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Auto adjust rate','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="new_parser" id="the_new_parser_select" autocomplete="off"> 
					<?php foreach($parsers as $parser_key => $parser_title){ ?>
						<option value="<?php echo $parser_key; ?>" <?php selected(is_isset($data, 'new_parser'),$parser_key,true); ?>><?php echo $parser_title; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Add to rate','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Send','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="new_parser_actions_give" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($data, 'new_parser_actions_give'));?>" />
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Receive','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="new_parser_actions_get" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($data, 'new_parser_actions_get'));?>" />
			</div>	
		</div>
	</div>		
<?php	
}  

add_filter('pn_direction_addform_post', 'new_parser_pn_direction_addform_post');
function new_parser_pn_direction_addform_post($array){
	$array['new_parser'] = $parser = intval(is_param_post('new_parser'));
	if($parser > 0){
		$array['new_parser_actions_give'] = pn_parser_num(is_param_post('new_parser_actions_give'));			
		$array['new_parser_actions_get'] = pn_parser_num(is_param_post('new_parser_actions_get'));
	} else {
		$array['new_parser_actions_give'] = 0;			
		$array['new_parser_actions_get'] = 0;				
	}	
	return $array;
}

add_filter('get_calc_data', 'get_calc_data_newparser', 50, 2);
function get_calc_data_newparser($cdata, $calc_data){ 
	$direction = $calc_data['direction'];
	$set_course = intval(is_isset($calc_data,'set_course'));
	if($direction->new_parser > 0 and $set_course != 1){
		$pairs = get_parser_pairs();
		$pairs_course = get_parser_pairs_course();
		$vd1 = $calc_data['vd1'];
		$vd2 = $calc_data['vd2'];
		if(isset($pairs_course[$direction->new_parser])){
			$curs_data = $pairs_course[$direction->new_parser];
			$curs1 = get_parser_course($curs_data->pair_give, $pairs);
			$curs2 = get_parser_course($curs_data->pair_get, $pairs);
			$ncurs1 = rate_plus_interest($curs1, $direction->new_parser_actions_give);
			$ncurs2 = rate_plus_interest($curs2, $direction->new_parser_actions_get);
			$cdata['course_give'] = is_sum($ncurs1, $vd1->currency_decimal);
			$cdata['course_get'] = is_sum($ncurs2, $vd2->currency_decimal);
			return $cdata;
		}
		$cdata['course_give'] = 0;
		$cdata['course_get'] = 0;
	}
	return $cdata;
}

add_filter('is_course_direction', 'newparser_is_course_direction', 50, 5); 
function newparser_is_course_direction($arr, $direction, $vd1, $vd2, $place){
	if($direction->new_parser > 0){
		$pairs = get_parser_pairs();
		$pairs_course = get_parser_pairs_course();
		if(isset($pairs_course[$direction->new_parser])){
			$curs_data = $pairs_course[$direction->new_parser];
			$curs1 = get_parser_course($curs_data->pair_give, $pairs);
			$ncurs1 = rate_plus_interest($curs1, $direction->new_parser_actions_give);
			if(isset($vd1->currency_decimal)){
				$arr['give'] = is_sum($ncurs1, $vd1->currency_decimal);
			} else {
				$arr['give'] = is_sum($ncurs1);
			}
			$curs2 = get_parser_course($curs_data->pair_get, $pairs);
			$ncurs2 = rate_plus_interest($curs2, $direction->new_parser_actions_get);
			if(isset($vd2->currency_decimal)){
				$arr['get'] = is_sum($ncurs2, $vd2->currency_decimal);
			} else {
				$arr['get'] = is_sum($ncurs2);
			}	
			return $arr;
		}
		$arr['give'] = 0;
		$arr['get'] = 0;
	}
	return $arr;
}
/* end directions */

/* best */ 
add_action('pn_adminpage_content_pn_bc_adjs','new_parser_pn_admin_content_pn_bc_adjs');
add_action('pn_adminpage_content_pn_bc_corrs','new_parser_pn_admin_content_pn_bc_adjs');
function new_parser_pn_admin_content_pn_bc_adjs(){
?>	
<style>
.not_adaptive th.pntable-column-new_parser{ width: 230px; }
</style>
<script type="text/javascript">
jQuery(function($){
	$('.bcadjs_new_parser').change(function(){
		var id = $(this).attr('id').replace('bcadjs_new_parser_','');
		var vale = $(this).val();
		if(vale > 0){
			$('#the_bcadjs_new_parser_'+id).show();
		} else {
			$('#the_bcadjs_new_parser_'+id).hide();
		}
	});			
});
</script>
<?php
} 

add_filter('pntable_columns_pn_bc_adjs', 'new_parser_pntable_columns_pn_bc_adjs');
add_filter('pntable_columns_pn_bc_corrs', 'new_parser_pntable_columns_pn_bc_adjs');
function new_parser_pntable_columns_pn_bc_adjs($columns){
	$new_columns = array();
	$new_columns['new_parser'] = __('Auto adjust rate','pn');
	$columns = pn_array_insert($columns, 'standart', $new_columns);
	return $columns;
}

add_action('pntable_bcadjs_save', 'new_parser_pn_bcadjs_save');
function new_parser_pn_bcadjs_save(){
global $wpdb;		
	if(isset($_POST['standart_new_parser']) and is_array($_POST['standart_new_parser'])){ 		
		foreach($_POST['standart_new_parser'] as $id => $parser_id){						
			$id = intval($id);
			$parser = intval($parser_id);
			$standart_parser_actions_give = pn_parser_num($_POST['standart_new_parser_actions_give'][$id]);			
			$standart_parser_actions_get = pn_parser_num($_POST['standart_new_parser_actions_get'][$id]);						
			$array = array();
			if($parser > 0){
				$array['standart_new_parser'] = $parser;
				$array['standart_new_parser_actions_give'] = $standart_parser_actions_give;			
				$array['standart_new_parser_actions_get'] = $standart_parser_actions_get;
			} else {
				$array['standart_new_parser'] = 0;
				$array['standart_new_parser_actions_give'] = 0;			
				$array['standart_new_parser_actions_get'] = 0;							
			}								
			$wpdb->update($wpdb->prefix.'bcbroker_directions', $array, array('id'=>$id));		
		}			
	}	
}

add_action('pntable_bccorrs_save', 'new_parser_pn_bccorrs_save');
function new_parser_pn_bccorrs_save(){
global $wpdb;		
	if(isset($_POST['standart_new_parser']) and is_array($_POST['standart_new_parser'])){ 		
		foreach($_POST['standart_new_parser'] as $id => $parser_id){						
			$id = intval($id);
			$parser = intval($parser_id);
			$standart_parser_actions_give = pn_parser_num($_POST['standart_new_parser_actions_give'][$id]);			
			$standart_parser_actions_get = pn_parser_num($_POST['standart_new_parser_actions_get'][$id]);						
			$array = array();
			if($parser > 0){
				$array['standart_new_parser'] = $parser;
				$array['standart_new_parser_actions_give'] = $standart_parser_actions_give;			
				$array['standart_new_parser_actions_get'] = $standart_parser_actions_get;
			} else {
				$array['standart_new_parser'] = 0;
				$array['standart_new_parser_actions_give'] = 0;			
				$array['standart_new_parser_actions_get'] = 0;							
			}								
			$wpdb->update($wpdb->prefix.'bestchange_directions', $array, array('id'=>$id));		
		}			
	}	
}

add_filter('pntable_column_pn_bc_adjs', 'new_parser_pntable_column_pn_bc_adjs', 10, 3);
add_filter('pntable_column_pn_bc_corrs', 'new_parser_pntable_column_pn_bc_adjs', 10, 3);
function new_parser_pntable_column_pn_bc_adjs($show, $column_name, $item){
	if($column_name == 'new_parser'){
		
		$parsers = get_parser_pairs_course();
		$show_parser_course = apply_filters('show_parser_course', 1);
			
		$html = '
		<div style="width: 230px;">
		';
			
		$html .= '
		<select name="standart_new_parser['. $item->id .']" autocomplete="off" id="bcadjs_new_parser_'. $item->id .'" class="bcadjs_new_parser" style="width: 230px; display: block; margin: 0 0 10px;"> 
		';
			$enable = 0;
			$html .= '<option value="0" '. selected($item->standart_new_parser,0,false) .'>-- '. __('No item','pn') .' --</option>';
			if(is_array($parsers)){
				foreach($parsers as $parser){
					if($item->standart_new_parser == $parser->id){
						$enable = 1;
					}
						
					$html .= '<option value="'. $parser->id .'" '. selected($item->standart_new_parser,$parser->id,false) .'>'. pn_strip_input($parser->title_pair_give).'-'.pn_strip_input($parser->title_pair_get).' ('. pn_strip_input(ctv_ml($parser->title_birg)) .')';
					if($show_parser_course == 1){
						$html .= ' ['. get_parser_course($parser->pair_give) .' => '. get_parser_course($parser->pair_get) .']';
					}
					$html .= '</option>';
				}
			}	
		$style = 'style="display: none;"';	
		if($enable == 1){
			$style = '';
		}
				
		$html .= '
		</select>
			
		<div id="the_bcadjs_new_parser_'. $item->id .'" '. $style .'>
			<input type="text" name="standart_new_parser_actions_give['. $item->id .']" style="width: 95px; float: left; margin: 0px 0px 0 0;" value="'. pn_strip_input($item->standart_new_parser_actions_give) .'" />
			<div style="float: left; margin: 3px 2px 0 2px;">=></div>
			<input type="text" name="standart_new_parser_actions_get['. $item->id .']" style="width: 95px; float: left; margin: 0px 0px 0 0;" value="'. pn_strip_input($item->standart_new_parser_actions_get) .'" />				
				<div class="premium_clear"></div>
		</div>		
		';
			
		$html .= '</div>';
			return $html;
	}
	return $show;
}

add_filter('pn_bcadjs_addform', 'new_parser_pn_bcadjs_addform', 10, 2);
add_filter('pn_bccorrs_addform', 'new_parser_pn_bcadjs_addform', 10, 2);
function new_parser_pn_bcadjs_addform($options, $data){
global $wpdb;

	$en_parsers = get_parser_pairs_course();
	$show_parser_course = apply_filters('show_parser_course', 1);

	$parsers = $ind_parsers = array();
	$parsers[0] = $ind_parsers[0] = '-- '. __('No item','pn') .' --';
	if(is_array($en_parsers)){
		foreach($en_parsers as $item){
			if($show_parser_course == 1){
				$c_give = get_parser_course($item->pair_give);
				$c_get = get_parser_course($item->pair_get);
			}
			
			$string = $string2 = pn_strip_input($item->title_pair_give).'-'.pn_strip_input($item->title_pair_get).' ('. pn_strip_input(ctv_ml($item->title_birg)) .')';
			if($show_parser_course == 1){
				$string .= ' ['. $c_give .' => '. $c_get .']';
				$string2 .= ' ['. is_best_rate($c_give, $c_get) .']';
			}
			$parsers[$item->id] = $string;
			$ind_parsers[$item->id] = $string2;
		}
	}	
		
	$add_options = array();
	$new_options = array();
	$add_options['minsum_new_parser_actions'] = array(
		'view' => 'inputbig',
		'title' => __('Add to min rate','pn'),
		'default' => is_isset($data, 'minsum_new_parser_actions'),
		'name' => 'minsum_new_parser_actions',
	);	
	$new_options['minsum_new_parser'] = array(
		'view' => 'select',
		'title' => __('Auto adjust for min rate','pn'),
		'options' => $ind_parsers,
		'default' => is_isset($data, 'minsum_new_parser'),
		'name' => 'minsum_new_parser',
		'work' => 'input',
		'add_options' => $add_options,
	);		
	$options = pn_array_insert($options, 'min_sum', $new_options);		
		
	$add_options = array();
	$new_options = array();
	$add_options['maxsum_new_parser_actions'] = array(
		'view' => 'inputbig',
		'title' => __('Add to max rate','pn'),
		'default' => is_isset($data, 'maxsum_new_parser_actions'),
		'name' => 'maxsum_new_parser_actions',
	);
	$new_options['maxsum_new_parser'] = array(
		'view' => 'select',
		'title' => __('Auto adjust for max rate','pn'),
		'options' => $ind_parsers,
		'default' => is_isset($data, 'maxsum_new_parser'),
		'name' => 'maxsum_new_parser',
		'work' => 'input',
		'add_options' => $add_options,
	);			
	$options = pn_array_insert($options, 'max_sum', $new_options);		
		
	$new_options = array();
	$add_options = array();
	$add_options['standart_new_parser_actions_give'] = array(
		'view' => 'inputbig',
		'title' => __('Add to Send rate','pn'),
		'default' => is_isset($data, 'standart_new_parser_actions_give'),
		'name' => 'standart_new_parser_actions_give',
	);
	$add_options['standart_new_parser_actions_get'] = array(
		'view' => 'inputbig',
		'title' => __('Add to Receive rate','pn'),
		'default' => is_isset($data, 'standart_new_parser_actions_get'),
		'name' => 'standart_new_parser_actions_get',
	);	
	$new_options['standart_new_parser'] = array(
		'view' => 'select',
		'title' => __('Automatic change of rate','pn'),
		'options' => $parsers,
		'default' => is_isset($data, 'standart_new_parser'),
		'name' => 'standart_new_parser',
		'work' => 'input',
		'add_options' => $add_options,
	);				
	$options = pn_array_insert($options, 'standart_course_get', $new_options);
	
	return $options;
}

add_filter('pn_bccorrs_addform_post', 'new_parser_pn_bcadjs_addform_post');
add_filter('pn_bcadjs_addform_post', 'new_parser_pn_bcadjs_addform_post');
function new_parser_pn_bcadjs_addform_post($array){
	$array['standart_new_parser'] = intval(is_param_post('standart_new_parser'));
	$array['standart_new_parser_actions_give'] = pn_parser_num(is_param_post('standart_new_parser_actions_give'));
	$array['standart_new_parser_actions_get'] = pn_parser_num(is_param_post('standart_new_parser_actions_get'));
	$array['minsum_new_parser'] = intval(is_param_post('minsum_new_parser'));
	$array['minsum_new_parser_actions'] = pn_parser_num(is_param_post('minsum_new_parser_actions'));
	$array['maxsum_new_parser'] = intval(is_param_post('maxsum_new_parser'));
	$array['maxsum_new_parser_actions'] = pn_parser_num(is_param_post('maxsum_new_parser_actions'));
	return $array;
}

add_action('tab_bcbroker_min_sum', 'new_parser_tab_bcbroker_min_sum', 10, 2);
function new_parser_tab_bcbroker_min_sum($data, $broker){
	global $wpdb;
	
	$parsers = array();
	$parsers[0] = '-- '. __('No item','pn') .' --';
	
	$en_parsers = get_parser_pairs_course();
	$show_parser_course = apply_filters('show_parser_course', 1);
	if(is_array($en_parsers)){
		foreach($en_parsers as $item){
			$string = pn_strip_input($item->title_pair_give).'-'.pn_strip_input($item->title_pair_get).' ('. pn_strip_input(ctv_ml($item->title_birg)) .')';
			if($show_parser_course == 1){
				$string .= ' ['. is_best_rate(get_parser_course($item->pair_give), get_parser_course($item->pair_get)) .']';
			}
			$parsers[$item->id] = $string;
		}
	}
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Auto adjust for min rate','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="bcadjs_minsum_new_parser" autocomplete="off"> 
					<?php foreach($parsers as $parser_key => $parser_title){ ?>
						<option value="<?php echo $parser_key; ?>" <?php selected(is_isset($broker, 'minsum_new_parser'),$parser_key,true); ?>><?php echo $parser_title; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Add to rate','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="bcadjs_minsum_new_parser_actions" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'minsum_new_parser_actions'));?>" />
			</div>
		</div>
	</div>				
<?php	
} 

add_action('tab_bestchange_min_sum', 'new_parser_tab_bestchange_min_sum', 10, 2);
function new_parser_tab_bestchange_min_sum($data, $broker){
	$parsers = array();
	$parsers[0] = '-- '. __('No item','pn') .' --';
	
	$en_parsers = get_parser_pairs_course();
	$show_parser_course = apply_filters('show_parser_course', 1);
	if(is_array($en_parsers)){
		foreach($en_parsers as $item){
			$string = pn_strip_input($item->title_pair_give).'-'.pn_strip_input($item->title_pair_get).' ('. pn_strip_input(ctv_ml($item->title_birg)) .')';
			if($show_parser_course == 1){
				$string .= ' ['. is_best_rate(get_parser_course($item->pair_give), get_parser_course($item->pair_get)) .']';
			}
			$parsers[$item->id] = $string;
		}
	}	
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Auto adjust for min rate','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="bccorrs_minsum_new_parser" autocomplete="off"> 
					<?php foreach($parsers as $parser_key => $parser_title){ ?>
						<option value="<?php echo $parser_key; ?>" <?php selected(is_isset($broker, 'minsum_new_parser'),$parser_key,true); ?>><?php echo $parser_title; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Add to rate','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="bccorrs_minsum_new_parser_actions" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'minsum_new_parser_actions'));?>" />
			</div>
		</div>
	</div>				
<?php	
} 

add_action('tab_bcbroker_max_sum', 'new_parser_tab_bcbroker_max_sum', 10, 2);
function new_parser_tab_bcbroker_max_sum($data, $broker){
	$parsers = array();
	$parsers[0] = '-- '. __('No item','pn') .' --';
	$en_parsers = get_parser_pairs_course();
	$show_parser_course = apply_filters('show_parser_course', 1);
	if(is_array($en_parsers)){
		foreach($en_parsers as $item){
			$string = pn_strip_input($item->title_pair_give).'-'.pn_strip_input($item->title_pair_get).' ('. pn_strip_input(ctv_ml($item->title_birg)) .')';
			if($show_parser_course == 1){
				$string .= ' ['. is_best_rate(get_parser_course($item->pair_give), get_parser_course($item->pair_get)) .']';
			}
			$parsers[$item->id] = $string;
		}
	}
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Auto adjust for max rate','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="bcadjs_maxsum_new_parser" autocomplete="off"> 
					<?php foreach($parsers as $parser_key => $parser_title){ ?>
						<option value="<?php echo $parser_key; ?>" <?php selected(is_isset($broker, 'maxsum_new_parser'),$parser_key,true); ?>><?php echo $parser_title; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Add to rate','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="bcadjs_maxsum_new_parser_actions" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'maxsum_new_parser_actions'));?>" />
			</div>
		</div>
	</div>					
<?php	
}  

add_action('tab_bestchange_max_sum', 'new_parser_tab_bestchange_max_sum', 10, 2);
function new_parser_tab_bestchange_max_sum($data, $broker){
	$parsers = array();
	$parsers[0] = '-- '. __('No item','pn') .' --';
	$en_parsers = get_parser_pairs_course();
	$show_parser_course = apply_filters('show_parser_course', 1);
	if(is_array($en_parsers)){
		foreach($en_parsers as $item){
			$string = pn_strip_input($item->title_pair_give).'-'.pn_strip_input($item->title_pair_get).' ('. pn_strip_input(ctv_ml($item->title_birg)) .')';
			if($show_parser_course == 1){
				$string .= ' ['. is_best_rate(get_parser_course($item->pair_give), get_parser_course($item->pair_get)) .']';
			}
			$parsers[$item->id] = $string;
		}
	}
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Auto adjust for max rate','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="bccorrs_maxsum_new_parser" autocomplete="off"> 
					<?php foreach($parsers as $parser_key => $parser_title){ ?>
						<option value="<?php echo $parser_key; ?>" <?php selected(is_isset($broker, 'maxsum_new_parser'),$parser_key,true); ?>><?php echo $parser_title; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Add to rate','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="bccorrs_maxsum_new_parser_actions" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'maxsum_new_parser_actions'));?>" />
			</div>
		</div>
	</div>					
<?php	
} 

add_action('tab_bcbroker_standart_course', 'new_parser_tab_bcbroker_standart_course', 10, 2);
function new_parser_tab_bcbroker_standart_course($data, $broker){
	$parsers = array();
	$parsers[0] = '-- '. __('No item','pn') .' --';
	$en_parsers = get_parser_pairs_course();
	$show_parser_course = apply_filters('show_parser_course', 1);
	if(is_array($en_parsers)){
		foreach($en_parsers as $item){
			$string = pn_strip_input($item->title_pair_give).'-'.pn_strip_input($item->title_pair_get).' ('. pn_strip_input(ctv_ml($item->title_birg)) .')';
			if($show_parser_course == 1){
				$string .= ' ['. get_parser_course($item->pair_give) .' => '. get_parser_course($item->pair_get) .']';
			}
			$parsers[$item->id] = $string;
		}
	}
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Auto adjust rate','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="bcadjs_standart_new_parser" autocomplete="off"> 
					<?php foreach($parsers as $parser_key => $parser_title){ ?>
						<option value="<?php echo $parser_key; ?>" <?php selected(is_isset($broker, 'standart_new_parser'),$parser_key,true); ?>><?php echo $parser_title; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Add to rate','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Send','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="bcadjs_standart_new_parser_actions_give" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'standart_new_parser_actions_give'));?>" />
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Receive','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="bcadjs_standart_new_parser_actions_get" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'standart_new_parser_actions_get'));?>" />	
			</div>
		</div>
	</div>		
<?php	
}  

add_action('tab_bestchange_standart_course', 'new_parser_tab_bestchange_standart_course', 10, 2);
function new_parser_tab_bestchange_standart_course($data, $broker){
	$parsers = array();
	$parsers[0] = '-- '. __('No item','pn') .' --';
	$en_parsers = get_parser_pairs_course();
	$show_parser_course = apply_filters('show_parser_course', 1);
	if(is_array($en_parsers)){
		foreach($en_parsers as $item){
			$string = pn_strip_input($item->title_pair_give).'-'.pn_strip_input($item->title_pair_get).' ('. pn_strip_input(ctv_ml($item->title_birg)) .')';
			if($show_parser_course == 1){
				$string .= ' ['. get_parser_course($item->pair_give) .' => '. get_parser_course($item->pair_get) .']';
			}
			$parsers[$item->id] = $string;
		}
	}
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Auto adjust rate','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="bccorrs_standart_new_parser" autocomplete="off"> 
					<?php foreach($parsers as $parser_key => $parser_title){ ?>
						<option value="<?php echo $parser_key; ?>" <?php selected(is_isset($broker, 'standart_new_parser'),$parser_key,true); ?>><?php echo $parser_title; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Add to rate','pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Send','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="bccorrs_standart_new_parser_actions_give" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'standart_new_parser_actions_give'));?>" />
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Receive','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="bccorrs_standart_new_parser_actions_get" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'standart_new_parser_actions_get'));?>" />	
			</div>
		</div>
	</div>		
<?php	
} 

add_filter('pn_bccorrs_tab_addform_post', 'new_parser_pn_bccorrs_tab_addform_post');
function new_parser_pn_bccorrs_tab_addform_post($array){
	$array['standart_new_parser'] = intval(is_param_post('bccorrs_standart_new_parser'));
	$array['standart_new_parser_actions_give'] = pn_parser_num(is_param_post('bccorrs_standart_new_parser_actions_give'));
	$array['standart_new_parser_actions_get'] = pn_parser_num(is_param_post('bccorrs_standart_new_parser_actions_get'));
	$array['minsum_new_parser'] = intval(is_param_post('bccorrs_minsum_new_parser'));
	$array['minsum_new_parser_actions'] = pn_parser_num(is_param_post('bccorrs_minsum_new_parser_actions'));
	$array['maxsum_new_parser'] = intval(is_param_post('bccorrs_maxsum_new_parser'));
	$array['maxsum_new_parser_actions'] = pn_parser_num(is_param_post('bccorrs_maxsum_new_parser_actions'));
	return $array;
}

add_filter('pn_bcadjs_tab_addform_post', 'new_parser_pn_bcadjs_tab_addform_post');
function new_parser_pn_bcadjs_tab_addform_post($array){
	$array['standart_new_parser'] = intval(is_param_post('bcadjs_standart_new_parser'));
	$array['standart_new_parser_actions_give'] = pn_parser_num(is_param_post('bcadjs_standart_new_parser_actions_give'));
	$array['standart_new_parser_actions_get'] = pn_parser_num(is_param_post('bcadjs_standart_new_parser_actions_get'));
	$array['minsum_new_parser'] = intval(is_param_post('bcadjs_minsum_new_parser'));
	$array['minsum_new_parser_actions'] = pn_parser_num(is_param_post('bcadjs_minsum_new_parser_actions'));
	$array['maxsum_new_parser'] = intval(is_param_post('bcadjs_maxsum_new_parser'));
	$array['maxsum_new_parser_actions'] = pn_parser_num(is_param_post('bcadjs_maxsum_new_parser_actions'));
	return $array;
}

add_filter('bestchange_def_course', 'new_parser_bestchange_def_course', 10, 6);
function new_parser_bestchange_def_course($darr, $item, $options, $direction, $vd1, $vd2){
global $wpdb;
	
	$pairs = get_parser_pairs();
	$pairs_course = get_parser_pairs_course();
	
	$minsum_parser = intval($item->minsum_new_parser);
	$minsum_parser_actions = pn_strip_input($item->minsum_new_parser_actions);
	if($minsum_parser > 0 and isset($pairs_course[$minsum_parser])){
		$curs_data = $pairs_course[$minsum_parser];
		$curs1 = get_parser_course($curs_data->pair_give, $pairs);
		$curs2 = get_parser_course($curs_data->pair_get, $pairs);
		$curs = is_best_rate($curs1, $curs2);
		$ncurs = rate_plus_interest($curs, $minsum_parser_actions);				
		if($ncurs > 0){
			$darr['min_sum'] = $ncurs;
		}
	}

	$maxsum_parser = intval($item->maxsum_new_parser);
	$maxsum_parser_actions = pn_strip_input($item->maxsum_new_parser_actions);
	if($maxsum_parser > 0 and isset($pairs_course[$maxsum_parser])){
		$curs_data = $pairs_course[$maxsum_parser];
		$curs1 = get_parser_course($curs_data->pair_give, $pairs);
		$curs2 = get_parser_course($curs_data->pair_get, $pairs);		
		$curs = is_best_rate($curs1, $curs2);
		$ncurs = rate_plus_interest($curs, $maxsum_parser_actions);				
		if($ncurs > 0){
			$darr['max_sum'] = $ncurs;
		}
	}	
	
	$standart_parser = intval($item->standart_new_parser);
	$standart_parser_actions_give = pn_strip_input($item->standart_new_parser_actions_give);
	$standart_parser_actions_get = pn_strip_input($item->standart_new_parser_actions_get);
	if($standart_parser > 0 and isset($pairs_course[$standart_parser])){
		$curs_data = $pairs_course[$standart_parser];
		$curs1 = get_parser_course($curs_data->pair_give, $pairs);
		$curs2 = get_parser_course($curs_data->pair_get, $pairs);	
		$n_course_give = is_sum(rate_plus_interest($curs1, $standart_parser_actions_give), is_isset($vd1,'currency_decimal'));
		$n_course_get = is_sum(rate_plus_interest($curs2, $standart_parser_actions_get), is_isset($vd2,'currency_decimal'));				
		if($n_course_give > 0 and $n_course_get > 0){
			$darr['standart_course_give'] = $n_course_give;
			$darr['standart_course_get'] = $n_course_get;
		}
	}				
	
	return $darr;
}

add_filter('bcparser_def_course', 'new_parser_bcparser_def_course', 10, 6);
function new_parser_bcparser_def_course($darr, $item, $options, $direction, $vd1, $vd2){
global $wpdb;
	
	$pairs = get_parser_pairs();
	$pairs_course = get_parser_pairs_course();
	
	$name_column = intval($item->name_column);
	$partofrate = intval(is_isset($options,'partofrate'));
	$conversion = intval(is_isset($options,'conversion'));
	
	$minsum_parser = intval($item->minsum_new_parser);
	$minsum_parser_actions = pn_strip_input($item->minsum_new_parser_actions);
	if($minsum_parser > 0 and isset($pairs_course[$minsum_parser])){
		$curs_data = $pairs_course[$minsum_parser];
		$curs1 = get_parser_course($curs_data->pair_give, $pairs);
		$curs2 = get_parser_course($curs_data->pair_get, $pairs);
		$curs = 0;
		if($curs1 and $curs2){
			if($name_column == 0 and $partofrate == 1){
				if($conversion == 0){
					$curs = is_sum($curs1/$curs2);
				} else {
					$curs = is_sum($curs1);
				}				
			} else {
				if($conversion == 0){
					$curs = is_sum($curs2/$curs1);
				} else {
					$curs = is_sum($curs2);
				}
			}
		}
		
		$ncurs = rate_plus_interest($curs, $minsum_parser_actions);				
		if($ncurs > 0){
			$darr['min_sum'] = $ncurs;
		}
	}

	$maxsum_parser = intval($item->maxsum_new_parser);
	$maxsum_parser_actions = pn_strip_input($item->maxsum_new_parser_actions);
	if($maxsum_parser > 0 and isset($pairs_course[$maxsum_parser])){
		$curs_data = $pairs_course[$maxsum_parser];
		$curs1 = get_parser_course($curs_data->pair_give, $pairs);
		$curs2 = get_parser_course($curs_data->pair_get, $pairs);		
		$curs = 0;
		if($curs1 and $curs2){
			if($name_column == 0 and $partofrate == 1){
				if($conversion == 0){
					$curs = is_sum($curs1/$curs2);
				} else {
					$curs = is_sum($curs1);
				}
			} else {
				if($conversion == 0){
					$curs = is_sum($curs2/$curs1);
				} else {
					$curs = is_sum($curs2);
				}
			}
		}
		
		$ncurs = rate_plus_interest($curs, $maxsum_parser_actions);				
		if($ncurs > 0){
			$darr['max_sum'] = $ncurs;
		}
	}	
	
	$standart_parser = intval($item->standart_new_parser);
	$standart_parser_actions_give = pn_strip_input($item->standart_new_parser_actions_give);
	$standart_parser_actions_get = pn_strip_input($item->standart_new_parser_actions_get);
	if($standart_parser > 0 and isset($pairs_course[$standart_parser])){
		$curs_data = $pairs_course[$standart_parser];
		$curs1 = get_parser_course($curs_data->pair_give, $pairs);
		$curs2 = get_parser_course($curs_data->pair_get, $pairs);	
		$n_course_give = is_sum(rate_plus_interest($curs1, $standart_parser_actions_give), is_isset($vd1,'currency_decimal'));
		$n_course_get = is_sum(rate_plus_interest($curs2, $standart_parser_actions_get), is_isset($vd2,'currency_decimal'));				
		if($n_course_give > 0 and $n_course_get > 0){
			$darr['standart_course_give'] = $n_course_give;
			$darr['standart_course_get'] = $n_course_get;
		}
	}				
	
	return $darr;
}
/* end best */ 

/* calculator */
add_filter('get_formula_code', 'parser_formula_code', 10, 3); 
function parser_formula_code($n, $code, $id){
	if(strstr($code, 'parser_')){
		$p = get_parser_pairs();
		$code = str_replace('parser_','',$code);
		if(isset($p[$code], $p[$code]['course'])){
			return $p[$code]['course'];
		} 
	}
	return $n;
}
/* end calculator */