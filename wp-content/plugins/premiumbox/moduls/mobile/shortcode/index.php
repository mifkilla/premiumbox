<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('pn_adminpage_quicktags_mobile')){
	add_action('pn_adminpage_quicktags','pn_adminpage_quicktags_mobile', 1);
	function pn_adminpage_quicktags_mobile(){
	?>
	edButtons[edButtons.length] = 
	new edButton('premium_from_web', '<?php _e('Original verison only','pn'); ?>','[from_web]','[/from_web]');

	edButtons[edButtons.length] = 
	new edButton('premium_from_mobile', '<?php _e('Mobile version only','pn'); ?>','[from_mobile]','[/from_mobile]');
	<?php	
	}
}

if(!function_exists('default_pn_tags_mobile')){
	add_filter('pn_tags','default_pn_tags_mobile', 1);
	function default_pn_tags_mobile($tags){
		
		$tags['premium_from_web'] = array(
			'title' => __('Original verison only','pn'),
			'start' => '[from_web]',
			'end' => '[/from_web]',
		);
		$tags['premium_from_mobile'] = array(
			'title' => __('Mobile version only','pn'),
			'start' => '[from_mobile]',
			'end' => '[/from_mobile]',
		);		
		
		return $tags;
	}
}

if(!function_exists('shortcode_from_web')){
	function shortcode_from_web($atts,$content=""){ 
		if(!is_mobile()){
			return do_shortcode($content);
		} 
	}
	add_shortcode('from_web', 'shortcode_from_web');
}

if(!function_exists('shortcode_from_mobile')){
	function shortcode_from_mobile($atts,$content=""){ 
		if(is_mobile()){
			return do_shortcode($content);
		} 
	}
	add_shortcode('from_mobile', 'shortcode_from_mobile');
}	