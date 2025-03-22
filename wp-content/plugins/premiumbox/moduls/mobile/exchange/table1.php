<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('premium_js','premium_js_mobile_exchange_table1');
function premium_js_mobile_exchange_table1(){
	if(get_mobile_type_table() == 1){
		$ajax = get_ajax_table();
?>	
jQuery(function($){
	
function go_active_left_col(){
	
	if($('.js_item_left.active').length == 0){
		$('.js_item_left').removeClass('active');
		$('.js_item_left:first').addClass('active');
	} 	
	
	var valid = $('.js_item_left.active').attr('data-id');
	var cur_to = $('#js_cur_to').val();
	<?php if($ajax == 0){ ?>
		$('.js_line_tab').removeClass('active');
		$('#js_tabnaps_'+valid).addClass('active');
	<?php } else { ?>
		$('.xtt_html_abs').show();
		var param ='id=' + valid + '&cur_to='+cur_to;
		$.ajax({
			type: "POST",
			url: "<?php echo get_pn_action('mobile_table1_change');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},       
			success: function(res)
			{
				$('.xtt_html_abs').hide();
				if(res['status'] == 'success'){
					$('#xtt_right_col_html').html(res['html']);
				} 	
			}
		});	
	<?php } ?>
}

	go_active_left_col();
	
    $(document).on('click',".js_item_left",function () {
        if(!$(this).hasClass('active')){
		    
			$(".js_item_left").removeClass('active');
			$(this).addClass('active');

			go_active_left_col();
        }
        return false;
    });	
});			
<?php	
	}
} 

add_filter('exchange_mobile_table_type1','get_exchange_mobile_table_type1', 10, 3);
function get_exchange_mobile_table_type1($temp, $def_cur_from='', $def_cur_to=''){
global $wpdb, $premiumbox;	

	$temp = '';
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);

	$cur_from = $def_cur_from;
	$cur_to = $def_cur_to;
	
	$v = get_currency_data();

	$where = get_directions_where('home');
	
	$directions = array();
	$dirs = array();
	$dirs_data = array();
	$directions_arr = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions WHERE $where");
	foreach($directions_arr as $dir){
		if(isset($v[$dir->currency_id_give], $v[$dir->currency_id_get])){
			$output = apply_filters('get_direction_output', 1, $dir, 'home');
			if($output == 1){
				$dirs_data[$dir->id] = $dir;
				$dirs[$dir->id] = intval($v[$dir->currency_id_give]->t1_1);
			}
		}
	}	
	asort($dirs);
	foreach($dirs as $dir_id => $order){
		$dir = $dirs_data[$dir_id];
		$directions[$dir->currency_id_give] = $dir;
	}	
	
	$directions2 = array();
	$ajax = get_ajax_table();
	if($ajax == 0){
		$directions2 = apply_filters('get_directions_table1', array(), 'home', $where, $v);	
	}
	
	$tableicon = get_mobile_icon_for_table();
	
	$temp .= '
	<input type="hidden" name="" id="js_cur_from" value="'. $cur_from .'" />
	<input type="hidden" name="" id="js_cur_to" value="'. $cur_to .'" />
	<div class="xchange_type_table">
		<div class="xchange_type_table_ins">';
			
			$temp .= '
			<div class="xtt_table_wrap">';				
				
				$exchange_headname = '
				<div class="xtt_table_title_wrap">
					<div class="xtt_left_col_title">
						<div class="xtt_table_title1">
							<span>'. __('You send','pn') .'</span>
						</div>
					</div>
					<div class="xtt_right_col_title">
						<div class="xtt_table_title2">
							<span>'. __('You receive','pn') .'</span>
						</div>
					</div>
						<div class="clear"></div>
				</div>';
				$temp .= apply_filters('mobile_tbl1_exchange_headname', $exchange_headname);				
				
				$temp .= '
				<div class="xtt_table_body_wrap">
					<div class="xtt_html_abs"></div>';						
							
					$temp .= '
					<div class="xtt_left_col_table">';
					
						$temp .= apply_filters('mobile_tbl1_exchange_leftcol', '');
							
						$temp .= get_table1_leftcol($directions, $v, 'table1', $tableicon, $cur_from, 'mobile_tbl1_leftcol_data');
											
						$temp .= '
					</div>		
					<div class="xtt_right_col_table">';	
						$temp .= apply_filters('mobile_tbl1_exchange_rightcol', '');
							
						if($ajax == 0){
							
							if(is_array($directions)){	
								foreach($directions as $direction_data){
											
									$currency_id_give = $direction_data->currency_id_give;
												
									$temp .= '
									<!-- tab currency -->
									<div class="xtt_line_tab js_line_tab" id="js_tabnaps_'. $currency_id_give .'">';										
													
										$temp .= get_table1_rightcol($directions2, $currency_id_give, $v, 'table1', $tableicon, 'mobile_tbl1_rightcol_data', $cur_to);			
													
									$temp .= '
									</div>
									<!-- end tab currency -->										
									';		
								}
							}	

						} else {
							$temp .= '<div id="xtt_right_col_html"></div>';
						}

					$temp .= '
					</div>';
					
					$temp .= '
					<div class="clear"></div>
				</div>';
				
			$temp .= '	
				<div class="clear"></div>
			</div>';
			
		$temp .= '	
		</div>
	</div>';	
	
	return $temp;
}

add_action('premium_siteaction_mobile_table1_change', 'def_premium_siteaction_mobile_table1_change');
function def_premium_siteaction_mobile_table1_change(){
global $wpdb, $premiumbox;	
	
	header('Content-Type: application/json; charset=utf-8');
	
	$log = array();
	$log['status'] = '';
	$log['response'] = '';
	$log['status_code'] = '0'; 
	$log['status_text']= '';
	
	$premiumbox->up_mode('post');
	
	if(get_mobile_type_table() == 1){	
	
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		
		$cur_to = is_xml_value(is_param_post('cur_to'));
		$id = intval(is_param_post('id'));
		if($id > 0){
			
			$v = get_currency_data();
	
			$tableicon = get_mobile_icon_for_table();
			$where = get_directions_where('home');
			$html = '';
			
			$directions = apply_filters('get_directions_table1', array(), 'home', $where, $v, $id);
			
			$html .= get_table1_rightcol($directions, $id, $v, 'table1', $tableicon, 'mobile_tbl1_rightcol_data', $cur_to);				

			$log['status'] = 'success';
			$log['html'] = $html;			
		}	
	}
	
	echo json_encode($log);
	exit;
}