<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('premium_js_clipboard')){
	add_action('premium_js','premium_js_clipboard');
	function premium_js_clipboard(){
	?>
	jQuery(function($){
		var clipboard = new ClipboardJS('.pn_copy');
		clipboard.on('success', function(e) {
			$('.pn_copy').removeClass('copied');
			$(e.trigger).addClass('copied');	
		});	
	});
	<?php	
	}
}

if(!function_exists('premium_js_tooltip_field')){
	add_action('premium_js','premium_js_tooltip_field');
	function premium_js_tooltip_field(){
	?>
	jQuery(function($){		
		$(document).on('focusin', '.has_tooltip input, .has_tooltip textarea',function(){
			$(this).parents('.has_tooltip').addClass('showed');
		});
		$(document).on('click', '.field_tooltip_label',function(){
			$(this).parents('.has_tooltip').addClass('showed');
		});
		$(document).on('focusout', '.has_tooltip input, .has_tooltip textarea',function(){
			$(this).parents('.has_tooltip').removeClass('showed');
		});	
	});
	<?php	
	}
}

if(!function_exists('pn_adminpage_quicktags_standart')){
	add_action('pn_adminpage_quicktags','pn_adminpage_quicktags_standart');
	function pn_adminpage_quicktags_standart(){
	?>
	edButtons[edButtons.length] = 
	new edButton('premium_h2', 'H2','<h2>','</h2>');

	edButtons[edButtons.length] = 
	new edButton('premium_h3', 'H3','<h3>','</h3>');

	edButtons[edButtons.length] = 
	new edButton('premium_copyright_year', '<?php _e('Year','pn'); ?>','[copyright year=""]');

	edButtons[edButtons.length] = 
	new edButton('premium_antispam', '<?php _e('Anti spam','pn'); ?>','[antispam]','[/antispam]');

	edButtons[edButtons.length] = 
	new edButton('premium_from_user', '<?php _e('Users only','pn'); ?>','[from_user]','[/from_user]');

	edButtons[edButtons.length] = 
	new edButton('premium_from_guest', '<?php _e('Guests only','pn'); ?>','[from_guest]','[/from_guest]');

	edButtons[edButtons.length] = 
	new edButton('premium_classblock', '<?php _e('CSS class','pn'); ?>','[infobl class=""]','[/infobl]');

	edButtons[edButtons.length] = 
	new edButton('premium_textcolor', '<?php _e('Text color','pn'); ?>','[textcolor color=""]','[/textcolor]');

	edButtons[edButtons.length] = 
	new edButton('premium_copytext', '<?php _e('Copy text','pn'); ?>','[copytext]','[/copytext]');

	edButtons[edButtons.length] = 
	new edButton('premium_copytextbywords', '<?php _e('Copy text by words','pn'); ?>','[copytext copy="1"]','[/copytext]');

	edButtons[edButtons.length] = 
	new edButton('premium_breakword', '<?php _e('Line breaks','pn'); ?>','[breakword]','[/breakword]');

	edButtons[edButtons.length] = 
	new edButton('premium_joinlink', '<?php _e('Registration link','pn'); ?>','[joinlink title=""]');

	edButtons[edButtons.length] = 
	new edButton('premium_loginlink', '<?php _e('Login link','pn'); ?>','[loginlink title=""]');
	
	edButtons[edButtons.length] = 
	new edButton('premium_textblock', '<?php _e('Text block','pn'); ?>','[textblock]', '[/textblock]');
	
	edButtons[edButtons.length] = 
	new edButton('premium_image', '<?php _e('Image','pn'); ?>','[image title="" description=""]', '[/image]');	
	<?php	
	} 
}

if(!function_exists('default_pn_tags')){
	add_filter('pn_tags','default_pn_tags', 0);
	function default_pn_tags($tags){
		
		$tags['a'] = array(
			'title' => 'a',
			'start' => '<a href="">',
			'end' => '</a>',
		);
		$tags['img'] = array(
			'title' => 'img',
			'start' => '<img src="" alt="" />',
		);	
		$tags['b'] = array(
			'title' => 'b',
			'start' => '<strong>',
			'end' => '</strong>',
		);	
		$tags['i'] = array(
			'title' => 'i',
			'start' => '<em>',
			'end' => '</em>',
		);
		$tags['u'] = array(
			'title' => 'u',
			'start' => '<u>',
			'end' => '</u>',
		);
		$tags['ul'] = array(
			'title' => 'ul',
			'start' => '<ul>',
			'end' => '</ul>',
		);	
		$tags['li'] = array(
			'title' => 'li',
			'start' => '<li>',
			'end' => '</li>',
		);	
		$tags['h2'] = array(
			'title' => 'H2',
			'start' => '<h2>',
			'end' => '</h2>',
		);	
		$tags['h3'] = array(
			'title' => 'H3',
			'start' => '<h3>',
			'end' => '</h3>',
		);
		$tags['antispam'] = array(
			'title' => __('Anti spam','pn'),
			'start' => '[antispam]',
			'end' => '[/antispam]',
		);		
		$tags['textcolor'] = array(
			'title' => __('Text color','pn'),
			'start' => '[textcolor color=""]',
			'end' => '[/textcolor]',
		);		
		$tags['copyright_year'] = array(
			'title' => __('Year','pn'),
			'start' => '[copyright year=""]',
		);
		$tags['from_user'] = array(
			'title' => __('Users only','pn'),
			'start' => '[from_user]',
			'end' => '[/from_user]',
		);
		$tags['from_guest'] = array(
			'title' => __('Guests only','pn'),
			'start' => '[from_guest]',
			'end' => '[/from_guest]',
		);
		$tags['classblock'] = array(
			'title' => __('CSS class','pn'),
			'start' => '[infobl class=""]',
			'end' => '[/infobl]',
		);
		$tags['copytext'] = array(
			'title' => __('Copy text','pn'),
			'start' => '[copytext]',
			'end' => '[/copytext]',
		);
		$tags['copytextbywords'] = array(
			'title' => __('Copy text by words','pn'),
			'start' => '[copytext copy="1"]',
			'end' => '[/copytext]',
		);		
		$tags['textblock'] = array(
			'title' => __('Text block','pn'),
			'start' => '[textblock]',
			'end' => '[/textblock]',
		);		
		$tags['breakword'] = array(
			'title' => __('Line breaks','pn'),
			'start' => '[breakword]',
			'end' => '[/breakword]',
		);
		$tags['joinlink'] = array(
			'title' => __('Registration link','pn'),
			'start' => '[joinlink title=""]',
		);
		$tags['loginlink'] = array(
			'title' => __('Login link','pn'),
			'start' => '[loginlink title=""]',
		);
		$tags['image'] = array(
			'title' => __('Image','pn'),
			'start' => '[image title="" description=""]',
			'end' => '[/image]',
		);		
		
		return $tags;
	}
}

if(!function_exists('shortcode_antispam')){
	function shortcode_antispam($atts,$content=""){ 
		return antispambot($content);
	}
	add_shortcode('antispam', 'shortcode_antispam');
}

if(!function_exists('shortcode_loginlink')){
	function shortcode_loginlink($atts,$content=""){ 
		$plugin = get_plugin_class();
		$title = pn_strip_input(is_isset($atts, 'title'));
		$title = str_replace('&quot;','',$title);
		if(!$title){ $title = __('Authorization','pn'); }
		return '<a href="'. $plugin->get_page('login') .'" target="_blank" rel="noreferrer noopener" class="js_window_login">'. $title .'</a>';
	}
	add_shortcode('loginlink', 'shortcode_loginlink');
}

if(!function_exists('shortcode_joinlink')){
	function shortcode_joinlink($atts,$content=""){ 
		$plugin = get_plugin_class();
		$title = pn_strip_input(is_isset($atts, 'title'));
		$title = str_replace('&quot;','',$title);
		if(!$title){ $title = __('Registration','pn'); }
		return '<a href="'. $plugin->get_page('register') .'" target="_blank" rel="noreferrer noopener" class="js_window_join">'. $title .'</a>';
	}
	add_shortcode('joinlink', 'shortcode_joinlink');
}

if(!function_exists('shortcode_breakword')){
	function shortcode_breakword($atts,$content=""){ 
		return '<span class="break_words">'. do_shortcode($content) .'</span>';
	}
	add_shortcode('breakword', 'shortcode_breakword');
}

if(!function_exists('shortcode_copytext')){
	function shortcode_copytext($atts, $content=""){ 
		$copy = intval(is_isset($atts, 'copy'));
		$content = do_shortcode($content);
		if($copy == 1){
			$trim_words = array();
			$arr_words = explode(' ', $content);
			foreach($arr_words as $arr_word){
				$arr_word = trim($arr_word);
				if(strlen($arr_word) > 0){
					$trim_words[] = $arr_word;
				}
			}
			$wd = '';
			foreach($trim_words as $tr){
				$wd .= '<span class="pn_copy" data-clipboard-text="'. esc_attr($tr) .'">' . $tr . '</span> ';
			}
			return $wd;
		} else {
			return '<span class="pn_copy" data-clipboard-text="'. esc_attr($content) .'">'. $content .'</span>';
		}
	}
	add_shortcode('copytext', 'shortcode_copytext');
}

if(!function_exists('shortcode_copyright')){
	function shortcode_copyright($atts,$content=""){ 
		$year = is_isset($atts, 'year');
		if(!$year){ $year = date('Y'); }
		return get_copy_date($year);
	}
	add_shortcode('copyright', 'shortcode_copyright');
}

if(!function_exists('shortcode_textblock')){
	function shortcode_textblock($atts,$content=""){ 
		return '<div class="textblock"><div class="text">' . apply_filters('comment_text', $content) . '</div></div>'; 
	}
	add_shortcode('textblock', 'shortcode_textblock');
}

if(!function_exists('shortcode_from_user')){
	function shortcode_from_user($atts,$content=""){ 
	global $user_ID;
		if($user_ID){
			return do_shortcode($content);
		} 
	}
	add_shortcode('from_user', 'shortcode_from_user');
}

if(!function_exists('shortcode_from_guest')){
	function shortcode_from_guest($atts,$content=""){ 
	global $user_ID;
		if(!$user_ID){
			return do_shortcode($content);
		} 
	}
	add_shortcode('from_guest', 'shortcode_from_guest');
} 

if(!function_exists('shortcode_infobl')){
	function shortcode_infobl($atts,$content=""){ 

		$class = is_isset($atts, 'class');
		return '<div class="infobl '. esc_attr($class) .'">'. do_shortcode($content) . '</div>';
	}
	add_shortcode('infobl', 'shortcode_infobl');
}

if(!function_exists('shortcode_textcolor')){
	function shortcode_textcolor($atts,$content=""){ 
		$color = trim(is_isset($atts, 'color'));
		if(!$color){ $color = '#e46066'; }
		return '<div style="color: '. $color .';">'. do_shortcode($content) . '</div>';
	}
	add_shortcode('textcolor', 'shortcode_textcolor');
}

if(!function_exists('shortcode_image')){
	function shortcode_image($atts, $content=""){ 
	global $post;
		$n_atts = array();
		if(is_array($atts)){
			foreach($atts as $k => $v){
				$n_atts[$k] = str_replace(array('&quot;','&#039;'),'',$v);
			}
		}	
		$content = strip_tags($content, '<img>');
		$image_url = $content;
		if(strstr($content, '<img')){
			if(preg_match('/src=\"(.*?)\"/is', $content, $item)){
				$image_url = is_isset($item, 1);
			}
		}
		$image_url = trim($image_url);
		if($image_url){
			$title = trim(is_isset($n_atts,'title'));
			if(!$title){ $title = ctv_ml(is_isset($post, 'post_title')); }
			$description = trim(is_isset($n_atts,'description'));
			if(!$description){ $description = ctv_ml(is_isset($post, 'post_title')); }
			
			$im = '
			<a href="'. $image_url .'" class="text_image fancyimg" itemscope itemtype="https://schema.org/ImageObject">
				<meta itemprop="name" content="'. esc_attr($title) .'" />
				<img src="'. $image_url .'" alt="'. esc_attr($title) .'" itemprop="contentUrl" />
				<meta itemprop="description" content="'. esc_attr($description) .'" />
			</a>
			';
			return $im;
		}
	}
	add_shortcode('image', 'shortcode_image');
}  