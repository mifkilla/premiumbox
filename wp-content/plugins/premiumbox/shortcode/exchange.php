<?php
if( !defined( 'ABSPATH')){ exit(); } 

add_action('template_redirect','direction_initialization', 0);
function direction_initialization(){
global $wpdb, $premiumbox, $wp_query, $direction_data;
	if(is_pn_page('exchange')){
		$is_404 = 1;
		$pnhash = is_direction_name(get_query_var('pnhash'));
		if($pnhash){
			set_directions_data('exchange', 1, 0, $pnhash);
			if(isset($direction_data->direction_id)){
				$is_404 = 0;
			}
		}
		if($is_404 == 1){
			status_header(404);
			$wp_query->set_404();	
		}
	} 
}
 
add_action('wp_before_admin_bar_render', 'wp_before_admin_bar_render_direction', 1);
function wp_before_admin_bar_render_direction(){
global $wp_admin_bar, $direction_data;
    if(current_user_can('administrator') or current_user_can('pn_directions')){
		if(is_pn_page('exchange')){
			if(isset($direction_data->direction_id)){
				$wp_admin_bar->add_menu( array(
					'id'     => 'edit_directions',
					'href' => admin_url('admin.php?page=pn_add_directions&item_id='. $direction_data->direction_id),
					'title'  => __('Edit direction exchange','pn'),	
				));	
				$wp_admin_bar->add_menu( array(
					'id'     => 'edit_currency_give',
					'parent' => 'edit_directions',
					'href' => admin_url('admin.php?page=pn_add_currency&item_id='. $direction_data->currency_id_give),
					'title'  => sprintf(__('Edit "%s"','pn'), $direction_data->item_give),	
				));
				$wp_admin_bar->add_menu( array(
					'id'     => 'edit_currency_get',
					'parent' => 'edit_directions',
					'href' => admin_url('admin.php?page=pn_add_currency&item_id='. $direction_data->currency_id_get),
					'title'  => sprintf(__('Edit "%s"','pn'), $direction_data->item_get),	
				));				
			}
		}
	}
}

add_action('premium_js','premium_js_exchange_stepselect');
function premium_js_exchange_stepselect(){
?>	
jQuery(function($){ 

 	function get_exchange_step1(id){
		
		var id1 = $('#select_give').val();
		var id2 = $('#select_get').val();
		
		$('.exch_ajax_wrap_abs').show();
			
		var param='id='+id+'&id1=' + id1 + '&id2=' + id2;
		$.ajax({
			type: "POST",
			url: "<?php echo get_pn_action('exchange_stepselect');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{	
				$('.exch_ajax_wrap_abs').hide();
				
				if(res['status'] == 'success'){
					$('#exch_html').html(res['html']);	

					if($('#the_title_page').length > 0){
						$('#the_title_page, .direction_title').html(res['titlepage']);
					}	
					
					$('title').html(res['title']);
					
					if($('meta[name=keywords]').length > 0){
						$('meta[name=keywords]').attr('content', res['keywords']);
					}
					if($('meta[name=description]').length > 0){
						$('meta[name=description]').attr('content', res['description']);
					}
					
					if(res['url']){
						window.history.replaceState(null, null, res['url']);
					}				
					
					<?php do_action('live_change_html'); ?>
				} else {
					<?php do_action('pn_js_alert_response'); ?>
				}	
			}
		});		
		
	}
	$(document).on('change', '#select_give', function(){
		get_exchange_step1(1);
	});
	$(document).on('change', '#select_get', function(){
		get_exchange_step1(2);
	});	
	
});	
<?php	
} 

add_action('premium_siteaction_exchange_stepselect', 'def_premium_siteaction_exchange_stepselect');
function def_premium_siteaction_exchange_stepselect(){
global $wpdb, $premiumbox, $direction_data;	
	
	header('Content-Type: application/json; charset=utf-8'); 
	
	$log = array();
	$log['status'] = '';
	$log['response'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');		
	
	$premiumbox->up_mode('post');
	
	$show_data = pn_exchanges_output('exchange');
	
	if($show_data['mode'] == 1){
	
		$id = intval(is_param_post('id'));
		$id1 = intval(is_param_post('id1')); if($id1 < 0){ $id1 = 0; }
		$id2 = intval(is_param_post('id2')); if($id2 < 0){ $id2 = 0; }

		set_directions_data('exchange', 0, 0, '', $id1, $id2, $id);
		
		if(isset($direction_data->direction_id) and $direction_data->direction_id > 0){				
							
			$log['status'] = 'success';
			$log['url'] = get_exchange_link($direction_data->direction->direction_name);
			$log['html'] = get_exchange_html();

			$sitename = pn_site_name();								
			$titlepage = get_exchange_title();	
										
			$log['title'] = $sitename . '- '. $titlepage;
			$log['titlepage'] = $titlepage;
			$log['keywords'] = '';
			$log['description'] = '';
			$log = apply_filters('exchange_step_meta', $log);
				
		} else {			
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! The direction do not exist','pn');
		}
		
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = $show_data['text'];
	}
	
	echo json_encode($log);
	exit;
}

function get_exchange_html($id='', $side_id=''){
global $wpdb, $premiumbox, $direction_data;
	
	$temp = ' ';	
	$id = intval($id);
	
	$side_id = intval($side_id);
	if($side_id != 2){ $side_id = 1; }
	
	if(!isset($direction_data->direction_id)){
		set_directions_data('exchange', 0, $id);
	}	
	
	if(isset($direction_data->direction_id) and $direction_data->direction_id > 0){
		$temp .= apply_filters('before_exchange_table','');
		$temp .= '
		<input type="hidden" name="direction_id" class="js_direction_id" value="'. $direction_data->direction_id .'" />
		';
			
		$array = set_exchange_shortcode('exchange_html_list', $side_id);		

		$html = '
		[window]
		[timeline]
		[other_filter]
		
		<div class="xchange_div">
			<div class="xchange_div_ins">
				<div class="xchange_data_title give">
					<div class="xchange_data_title_ins">
						<span>'. __('Send','pn') .'</span>
					</div>	
				</div>
				<div class="xchange_data_div">
					<div class="xchange_data_ins">
						<div class="xchange_data_left">
							[meta1d]
						</div>	
						<div class="xchange_data_right">
							[meta1]
						</div>
							<div class="clear"></div>
							
						<div class="xchange_data_left">
							<div class="xchange_select">
								[select_give]						
							</div>
						</div>	
						<div class="xchange_data_right">
							<div class="xchange_sum_line">
								<div class="xchange_sum_label">
									'. __('Amount','pn') .'<span class="req">*</span>:
								</div>
								[input_give]
									<div class="clear"></div>
							</div>	
						</div>
							<div class="clear"></div>
						<div class="xchange_data_left js_viv_com1" [com_class_give]>
							[com_give_text]
						</div>	
						<div class="xchange_data_right js_viv_com1" [com_class_give]>
							<div class="xchange_sum_line">
								<div class="xchange_sum_label">
									'. __('With fees','pn') .'<span class="req">*</span>:
								</div>
								[com_give]
									<div class="clear"></div>
							</div>
						</div>
							<div class="clear"></div>									
						<div class="xchange_data_left">
						
							[account_give]
							
							[give_field]
							
						</div>
							<div class="clear"></div>
						
					</div>
				</div>
				<div class="xchange_data_title get">
					<div class="xchange_data_title_ins">
						<span>'. __('Receive','pn') .'</span>
					</div>	
				</div>
				<div class="xchange_data_div">
					<div class="xchange_data_ins">
						<div class="xchange_data_left">
							[meta2d]
						</div>
						<div class="xchange_data_right">
							[meta2]
						</div>
							<div class="clear"></div>
							
						<div class="xchange_data_left">
							<div class="xchange_select">
								[select_get]						
							</div>									
						</div>		
						<div class="xchange_data_right">
							<div class="xchange_sum_line">
								<div class="xchange_sum_label">
									'. __('Amount','pn') .'<span class="req">*</span>:
								</div>
								[input_get]
									<div class="clear"></div>
							</div>	
						</div>
							<div class="clear"></div>
						<div class="xchange_data_left js_viv_com2" [com_class_get]>
							[com_get_text]
						</div>
						<div class="xchange_data_right js_viv_com2" [com_class_get]>
							<div class="xchange_sum_line">
								<div class="xchange_sum_label">
									'. __('With fees','pn') .'<span class="req">*</span>:
								</div>
								[com_get]
									<div class="clear"></div>
							</div>									
						</div>
							<div class="clear"></div>						
						<div class="xchange_data_left">	
							[account_get]
							
							[get_field]
						</div>
							<div class="clear"></div>
					</div>
				</div>	
				[direction_field]		
					<div class="clear"></div>		
				[filters]
				[submit]
				[check]
				[remember]
				[result]		
			</div>
		</div>
		
		[description]
		';

		$html = apply_filters('exchange_html', $html);			
		$temp .= get_replace_arrays($array, $html);	
	
		$temp .= apply_filters('after_exchange_table','');
	} else {
		$temp .= '<div class="exch_error"><div class="exch_error_ins">'. __('Error! The direction do not exist','pn') .'</div></div>';
	}
	
	return $temp;
}

function exchange_page_shortcode($atts='', $content='') {
global $wpdb, $premiumbox;
	
	$temp = '';
	
	$show_data = pn_exchanges_output('exchange');
	
	if($show_data['text']){
		$temp .= '<div class="exch_error"><div class="exch_error_ins">'. $show_data['text'] .'</div></div>';
	}	
	
	if($show_data['mode'] == 1){
	
		$temp .= apply_filters('before_exchange_page','');
		$temp .= '
		<form method="post" class="ajax_post_bids" action="'. get_pn_action('bidsform') .'">
			<div class="exch_ajax_wrap">
				<div class="exch_ajax_wrap_abs"></div>
				<div id="exch_html">'. get_exchange_html(is_isset($atts, 'direction_id')) .'</div>
			</div>
		</form>
		';
		$temp .= apply_filters('after_exchange_page','');
	
	}
	
	return $temp;
}
add_shortcode('exchange', 'exchange_page_shortcode');