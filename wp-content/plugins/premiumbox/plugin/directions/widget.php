<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('premium_js','premium_js_exchange_widget');
function premium_js_exchange_widget(){	
?>	
jQuery(function($){ 

	if($('#hexch_html').length > 0){
		$(document).on('click', '.js_exchange_link', function(){
			if(!$(this).hasClass('active')){
				
				$('.js_exchange_link').removeClass('active');
				$(this).addClass('active');
				
				var direction_id = $(this).attr('data-direction-id'); 
				
				$('.js_exchange_widget_abs').show();
				
				var tscroll = $('#hexch_html').offset().top - 100;
				$('body,html').animate({scrollTop : tscroll}, 500);
				
				var param = 'direction_id=' + direction_id;
				$.ajax({
					type: "POST",
					url: "<?php echo get_pn_action('exchange_widget');?>",
					dataType: 'json',
					data: param,
					error: function(res, res2, res3){
						<?php do_action('pn_js_error_response', 'ajax'); ?>
					},					
					success: function(res)
					{
						if(res['html']){
							$('#hexch_html').html(res['html']);
						}
						if(res['status'] == 'error'){
							$('#hexch_html').html('<div class="resultfalse"><div class="resultclose"></div>'+res['status_text']+'</div>');
						}
						<?php do_action('live_change_html'); ?>
						$('.js_exchange_widget_abs').hide();
					}
				});	

			}
			
			return false;
		});
	}	
});
<?php	
} 

function the_exchange_widget(){ 
global $premiumbox, $widget_exchange;
	$widget_exchange = intval($widget_exchange);
	$exch_method = intval($premiumbox->get_option('exchange','exch_method'));
	$exch_method = apply_filters('exch_method', $exch_method);
	if($exch_method == 1 and $widget_exchange == 0){
?>
<form method="post" class="ajax_post_bids" action="<?php echo get_pn_action('bidsform'); ?>">
	<div class="hexch_ajax_wrap">
		<div class="hexch_ajax_wrap_abs js_exchange_widget_abs"></div>
		<div id="hexch_html">
		<?php 
			$dir_id = apply_filters('table_exchange_widget', 0, 'widget');
			$dir_id = intval($dir_id);
			if($dir_id){
				echo get_exchange_widget($dir_id); 
			}
		?>
		</div>
	</div>
</form>
<?php
	}
}

function table_exchange_widget(){
global $widget_exchange, $premiumbox;

	$widget_exchange = 1;
	
	$dir_id = intval($premiumbox->get_option('exchange','currtable'));
	$dir_id = apply_filters('table_exchange_widget', $dir_id, 'table5');
	$dir_id = intval($dir_id);
	if($dir_id){
		$html = get_exchange_widget($dir_id);
	} else {
		$html = '<div class="htable_notwidget"><div class="htable_notwidget_ins">'. __('Select currency "Receive" to display exchange form','pn') .'</div></div>';
	}
	$temp = '
	<form method="post" class="ajax_post_bids" action="'. get_pn_action('bidsform') .'">
		<div class="htable_ajax_wrap">
			<div class="htable_ajax_wrap_abs js_exchange_widget_abs"></div>
			<div id="hexch_html">'. apply_filters('notexchange_widget', $html) .'</div>
		</div>
	</form>	
	';
	return $temp;
}

add_action('premium_siteaction_exchange_widget', 'def_premium_siteaction_exchange_widget');
function def_premium_siteaction_exchange_widget(){
global $premiumbox;
	
	header('Content-Type: application/json; charset=utf-8'); 
	
	$log = array();
	$log['status'] = '';
	$log['response'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');	
	
	$premiumbox->up_mode('post');
	
	$direction_id = is_param_post('direction_id');

	$exch_method = intval($premiumbox->get_option('exchange','exch_method'));
	if($exch_method == 1 or get_type_table() == 5){
		$log['status'] = 'success';
		$log['html'] = get_exchange_widget($direction_id);
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 		
	}
	
	echo json_encode($log);
	exit;
} 

function get_exchange_widget($id){
global $wpdb, $premiumbox, $direction_data;
	
	$temp =' ';	
	$id = intval($id);		
	
	$show_data = pn_exchanges_output('home');
	if($show_data['text']){
		$temp .= '<div class="exch_error"><div class="exch_error_ins">'. $show_data['text'] .'</div></div>';
	}
	if($show_data['mode'] == 1){
	
		set_directions_data('home', 1, $id);	
		
		if(isset($direction_data->direction_id) and $direction_data->direction_id > 0){
					
			$temp .= apply_filters('before_exchange_widget','');
			$temp .= '
			<input type="hidden" name="direction_id" class="js_direction_id" value="'. $direction_data->direction_id .'" />
			';
					
			$array = set_exchange_shortcode('exchange_html_list_ajax');		
		
			$html = '
			<div class="hexch_widget">
			
				[window]
				[timeline]
				[other_filter]
				
				<div class="hexch_div">
					<div class="hexch_div_ins">
						<div class="hexch_bigtitle">'. __('Data input','pn') .'</div>
						
						<div class="hexch_information">
							<div class="hexch_information_line"><span class="hexh_line_label">'. __('Exchange rate','pn') .'</span>: [course]</div>
							<div class="hexch_information_line"><span class="hexh_line_label">'. __('Reserve','pn') .'</span>: [reserve]</div>
							[user_discount_html]
						</div>
						
						<div class="hexch_left">
							<div class="hexch_title">
								<div class="hexch_title_ins">
									<div class="hexch_title_logo currency_logo" style="background-image: url([currency_logo_give]);"></div>
									<span class="hexch_psys">[psys_give] [currency_code_give]</span>
								</div>
							</div>
							[meta1]
							
							<div class="hexch_curs_line">
								<div class="hexch_curs_label">
									<div class="hexch_curs_label_ins">
										'. __('Amount','pn') .'<span class="req">*</span>:
									</div>
								</div>											
		
								[input_give]
					
								<div class="clear"></div>
							</div>

							<div class="hexch_curs_line js_viv_com1" [com_class_give]>
								<div class="hexch_curs_label">
									<div class="hexch_curs_label_ins">
										'. __('With fees','pn') .'<span class="req">*</span>:
									</div>
								</div>
								[com_give]
			
								<div class="clear"></div>
							</div>

							[com_give_text]
							
							[account_give]
							
							[give_field]

						</div>
						<div class="hexch_right">
							<div class="hexch_title">
								<div class="hexch_title_ins">
									<div class="hexch_title_logo currency_logo" style="background-image: url([currency_logo_get]);"></div>
									<span class="hexch_psys">[psys_get] [currency_code_get]</span>
								</div>
							</div>
							[meta2]
							
							<div class="hexch_curs_line">
								<div class="hexch_curs_label">
									<div class="hexch_curs_label_ins">
										'. __('Amount','pn') .'<span class="req">*</span>:
									</div>
								</div>
				
								[input_get]	
				
								<div class="clear"></div>
							</div>																
			
							<div class="hexch_curs_line js_viv_com2" [com_class_get]>
								<div class="hexch_curs_label">
									<div class="hexch_curs_label_ins">
										'. __('With fees','pn') .'<span class="req">*</span>:
									</div>
								</div>
								[com_get]
				
								<div class="clear"></div>
							</div>		

							[com_get_text]
		
							[account_get]
									
							[get_field]
	
						</div>
							<div class="clear"></div>
						[direction_field]				
							<div class="clear"></div>
							
						[filters]
						[submit]
						[check]
						[remember]
						[result]
					</div>
				</div>
			</div>
			';		
		
			$html = apply_filters('exchange_html_ajax', $html);			
			$temp .= get_replace_arrays($array, $html);
					
			$temp .= apply_filters('after_exchange_widget','');
 		}		
	}
	
	return $temp;
}