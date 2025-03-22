<?php
if( !defined( 'ABSPATH')){ exit(); }

function domacc_page_shortcode($atts, $content) {
global $wpdb;

	$temp = '';
	
    $temp .= apply_filters('before_domacc_page','');
			
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);		
			
	if($user_id){
			
		$currency_codes = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."currency_codes WHERE auto_status = '1' ORDER BY currency_code_title ASC");	
		$temp .= '
		<div class="domacc_div_wrap">
			<div class="domacc_wrap_ins">
			
				<div class="domacc_div_title">
					<div class="domacc_div_title_ins">
						'. __('Internal account','pn') .'
					</div>
				</div>
		
				<div class="domacc_div">
					<div class="domacc_div_ins">
						';
						
						foreach($currency_codes as $currency_code){
							
							$temp .= '
							<div class="domacc_line">
								<div class="domacc_label">
									'. is_site_value($currency_code->currency_code_title) .':
								</div>
								<div class="domacc_val">
									'. get_user_domacc($user_id, $currency_code->id) .'
								</div>
									<div class="clear"></div>
							</div>
							';
							
						}
						
						$temp .= '
					</div>
				</div>
		
			</div>
		</div>
		';		

	} else {
		$temp .= '<div class="resultfalse">'. __('Error! You must authorize','pn') .'</div>';
	}
	
    $after = apply_filters('after_domacc_page','');
    $temp .= $after;	
	
	return $temp;
}
add_shortcode('domacc_page', 'domacc_page_shortcode');