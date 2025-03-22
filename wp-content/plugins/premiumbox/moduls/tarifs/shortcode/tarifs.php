<?php
if( !defined( 'ABSPATH')){ exit(); } 

function tarifs_shortcode($atts, $content) {
global $wpdb, $post;
        
	$temp = '';
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
		
	$temp .= apply_filters('before_tarifs_page','');		
		
	$show_data = pn_exchanges_output('tar'); 
	if($show_data['text']){
		$temp .= '<div class="resultfalse"><div class="resultclose"></div>'. $show_data['text'] .'</div>';
	}			
	
	if($show_data['mode'] == 1){

		$v = array();
		$currencies = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."currency WHERE auto_status='1' AND currency_status = '1' ORDER BY t1_1 ASC");			
		foreach($currencies as $currency){
			$v[$currency->id] = $currency;
		}		
				
		$where = get_directions_where('tar');		
		$directions = apply_filters('get_directions_table1', array(), 'tar', $where, $v); 		
		
		$temp .='
		<div class="tarif_div">
			<div class="tarif_div_ins">
		';	
			foreach($currencies as $currency){
				$currency_id = $currency->id;
				if(isset($directions[$currency_id])){
					$tarif_title = get_currency_title($currency);
					$tarif_logo = get_currency_logo($currency);
						
					$temp .= '
					<div class="tarif_block">
						<div class="tarif_block_ins">';
						
						$one_tarifs_title = '
						<div class="tarif_title">
							<div class="tarif_title_ins">
								<div class="tarif_title_abs"></div>
								'. $tarif_title .'
							</div>
								<div class="clear"></div>
						</div>
							<div class="clear"></div>';
						$temp .= apply_filters('one_tarifs_title', $one_tarifs_title, $tarif_title, $tarif_logo, $currency);

						$temp .= '
						<div class="tarif_table_wrap">
							<div class="tarif_table_title">
								<div class="tarif_table_title_part out">
									'. __('You send','pn') .'
								</div>
								<div class="tarif_table_title_arr"></div>
								<div class="tarif_table_title_part in">
									'. __('You receive','pn') .'
								</div>					
									<div class="clear"></div>
							</div>
						';
							$dirs = $directions[$currency_id];
							foreach($dirs as $direction){
								
								$currency_id_give = $direction->currency_id_give;
								$currency_id_get = $direction->currency_id_get;

								$vd1 = $v[$currency_id_give];
								$vd2 = $v[$currency_id_get];
								
								$dir_c = is_course_direction($direction, $vd1, $vd2, 'tar');
								
								$course_give = is_isset($dir_c, 'give'); 
								$course_get = is_isset($dir_c, 'get');
									
								$course_give = is_out_sum($course_give, $vd1->currency_decimal, 'course');
								$course_get = is_out_sum($course_get, $vd2->currency_decimal, 'course');
								
								$reserv = get_direction_reserv($vd1, $vd2, $direction);
								$reserv = is_out_sum($reserv, $vd2->currency_decimal, 'reserv');
								
								$one_tarifs_line = '
								<a href="'. get_exchange_link($direction->direction_name) .'" class="tarif_line">
									<div class="tarif_line_ins">
										<div class="tarif_line_top">
											<div class="tarif_curs_line out">
												<div class="tarif_curs_line_ins">
													<div class="tarif_curs_title">
														<div class="tarif_logo"><div class="tarif_logo_ins currency_logo" style="background-image: url('. get_currency_logo($vd1) .');"></div></div>	
														<div class="tarif_curs_title_ins">
															<span>'. get_currency_title($vd1) .'</span>
														</div>
													</div>	
													<div class="tarif_curs">
														<div class="tarif_curs_ins">
															<span>'. $course_give .'&nbsp;'. is_site_value($vd1->currency_code_title) .'</span>
														</div>
													</div>
														<div class="clear"></div>
												</div>		
											</div>
											<div class="tarif_curs_arr">
												<div class="tarif_curs_arr_ins"></div>
											</div>
											<div class="tarif_curs_line in">
												<div class="tarif_curs_line_ins">
													<div class="tarif_curs_title">
														<div class="tarif_logo"><div class="tarif_logo_ins currency_logo" style="background-image: url('. get_currency_logo($vd2) .');"></div></div>
														<div class="tarif_curs_title_ins">
															<span>'. get_currency_title($vd2) .'</span>
														</div>
													</div>	
													<div class="tarif_curs">
														<div class="tarif_curs_ins">
															<span>'. $course_get .'&nbsp;'. is_site_value($vd2->currency_code_title) .'</span>
														</div>
													</div>
														<div class="clear"></div>
												</div>		
											</div>										
												<div class="clear"></div>
										</div>	
										<div class="tarif_curs_reserv">
											<div class="tarif_curs_reserv_ins"><span>'. __('Reserve','pn') .'</span>: '. $reserv .' '. is_site_value($vd2->currency_code_title) .'</div>
										</div>
									</div>	
								</a>
								';
								$temp .= apply_filters('one_tarifs_line',$one_tarifs_line, $direction, $course_give, $course_get, $reserv, $vd1, $vd2);
							}
						
						$temp .= '
						</div>
						';
						
					$temp .= '
						</div>
					</div>
					';					
				}
			}		
	
		$temp .='
			</div>
		</div>';	
	} 
	
	$temp .= apply_filters('after_tarifs_page','');
	return $temp;
}
add_shortcode('tarifs', 'tarifs_shortcode');