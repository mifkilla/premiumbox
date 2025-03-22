<?php
if( !defined( 'ABSPATH')){ exit(); }

remove_filter('wp_mail', 'premium_html_wp_mail');

add_filter('wp_mail', 'theme_html_wp_mail');
function theme_html_wp_mail($data){
global $or_template_directory;

	$image_directory = $or_template_directory . '/images/';
	$h_change = get_theme_option('h_change', array('phone','icq','skype','email','telegram','viber','whatsapp','jabber'));
	$f_change = get_theme_option('f_change', array('ctext','timetable','phone','vk','fb','tw','ins','tm'));
	$copyright = pn_strip_text($f_change['ctext']);
	if(!$copyright){ $copyright = '&copy; '. get_copy_date('2015') .' PremiumExchanger.com — '. __('electronic currency exchange service.','pntheme'); }

	$textlogo = get_textlogo();
	if(!$textlogo){ $textlogo = get_caps_name(str_replace(array('http://','https://','www.'),'',get_site_url_or())); }	
	
	
	$html = '<html> 
			<head> 
				<title>'. $data['subject'] .'</title> 
			</head> 
			<body>
			<div style="width: 100%; padding: 20px 0; margin: 0; background: #f3f7fc;">
				<div style="margin: 0 auto; padding: 0 20px; max-width: 800px;">
					<table style="border: none; border-collapse:collapse; border-spacing:0; vertical-align: middle; background: #fff; width: 100%; padding: 0; margin: 0;">
						<tr>
							<td style="border: none; border-bottom: 1px solid #eaeef4; background: #fff; padding: 30px 20px; text-align: center; margin: 0;">
								<a href="'. get_site_url_ml() .'" style="font: bold 26px Arial; text-decoration: none; color: #000;">'. $textlogo .'</a>
							</td>
						</tr>
						<tr>
							<td style="border: none; border-bottom: 1px solid #eaeef4; background: #fff; padding: 30px 20px; text-align: left; margin: 0; font: 14px Arial;">
								<div style="padding: 0; margin: 0 0 30px 0;">
									'. $data['message'] .'
								</div>
								<a href="'. get_site_url_ml() .'" style="display: block; width: 220px; margin: 0; text-decoration: none; text-align: center; border: 1px solid #0c72d8; background: #0c72d8; border-radius: 4px; color: #fff; height: 50px; padding: 0 25px; font: 600 18px/50px Arial;">'. __('Go to website','pntheme') .'</a>
							</td>
						</tr>
						<tr>
							<td style="border: none; background: #fff; padding: 25px 20px 10px; text-align: center; margin: 0; font: 12px Arial; color: #6b8199;">';
								
								$ins = array('phone','icq','skype','email','telegram','viber','whatsapp','jabber');
								$r=0;
								foreach($ins as $key){
									$text = trim(is_isset($h_change, $key)); 
									if($text){ $r++;
										$html .= '<span style="padding: 0 0 0 20px; background: url('. $image_directory . $key .'.png) no-repeat left center; margin: 0 25px;">'. $text .'</span>';
										if($r == 3){
											$r = 0;
											$html .= '<div style="height: 15px;"></div>';
										}
									}
								}
								if($r != 0){
									$html .= '<div style="height: 15px;"></div>';
								}
								
							$html .= '	
							</td>
						</tr>			
						<tr>
							<td style="border: none; background: #ecf0f6; padding: 30px 20px; text-align: center; color: #6b8199; margin: 0; font: 13px Arial; ">
								'. apply_filters('comment_text', $copyright) .'
							</td>
						</tr>
					</table>
				</div>
			</div>					
			</body> 
		</html>';
		
	$data['message'] = $html;	
		
	return $data;
}