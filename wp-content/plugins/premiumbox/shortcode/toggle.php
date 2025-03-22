<?php 
if( !defined( 'ABSPATH')){ exit(); }

add_action('premium_js','premium_js_toggle');
function premium_js_toggle(){	
?>	
jQuery(function($){ 
	$('.oncetoggletitle').on('click', function(){
		$(this).parents('.oncetoggle').toggleClass('active');
		return false;
	});
});		
<?php	
} 


add_action('pn_adminpage_quicktags','pn_adminpage_quicktags_toggle');
function pn_adminpage_quicktags_toggle(){
?>
	edButtons[edButtons.length] = 
	new edButton('premium_toggle','<?php _e('Spoiler','pn'); ?>','[toggle title=""]','[/toggle]');	
	<?php	
}

add_filter('pn_tags','toggle_pn_tags', 1);
function toggle_pn_tags($tags){	

	$tags['toggle'] = array(
		'title' => __('Spoiler','pn'),
		'start' => '[toggle title="" open="0"]',
		'end' => '[/toggle]',
	);		
	
	return $tags;
}
	
function the_toggle_shortcode($atts, $content) {
	$temp = '';
	
	$open = intval(is_isset($atts,'open'));
	$title = pn_strip_input(is_isset($atts,'title'));
	$content = str_replace(array('<br />','<br>'),'',$content);
	if($title){
		$cl = '';
		if($open){
			$cl = 'active';
		}
		$temp .= '
		<div class="oncetoggle '. $cl .'" itemscope itemtype="https://schema.org/Question">
			<div class="oncetoggletitle"><div class="oncetoggletitle_ins" itemprop="name">'. $title .'</div></div>
			<div class="oncetogglebody" itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">
				<meta itemprop="upvoteCount" content="1" />
				<span itemprop="text">'. do_shortcode($content) .'</span>
			</div>
		</div>
		';
	}
	
	return $temp;
}
add_shortcode('toggle', 'the_toggle_shortcode');