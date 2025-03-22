<?php 
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('set_premium_page')){
	
	add_action('init', 'set_premium_page', 10);
	function set_premium_page(){
		
		$data = premium_rewrite_data();
		$super_base = $data['super_base'];
		$matches = '';
		
		if($super_base == 'premium_post.html'){
				
			header('Content-Type: text/html; charset=utf-8');
			
			status_header(501);
			
			do_action('premium_post', 'post');			

			$pn_action = pn_maxf(pn_strip_input(is_param_get('pn_action')), 250);
			if($pn_action and has_filter('premium_action_'.$pn_action)){
				status_header(200);
				
				do_action('premium_action_'.$pn_action);
			}
			
			exit;

		} elseif($super_base == 'premium_quicktags.js'){
				
			header('Content-Type: application/x-javascript; charset=utf-8');
			
			if(current_user_can('read')){
				$place = pn_maxf(pn_strip_input(is_param_get('place')),500);
				if(has_filter('pn_adminpage_quicktags_' . $place) or has_filter('pn_adminpage_quicktags')){
					do_action('pn_adminpage_quicktags_' . $place);
					do_action('pn_adminpage_quicktags');
				}			
			}
			exit;

		} elseif($super_base == 'premium_script.js'){	
			
			header('Content-Type: application/x-javascript; charset=utf-8');
	
			do_action('premium_post', 'js');
	
			set_premium_default_js();
	
			do_action('premium_js');
			
			exit;
			
		} elseif(preg_match("/^request-([a-zA-Z0-9\_]+).(txt|html|xml|js|json)?$/", $super_base, $matches )){	
			
			header('Content-Type: text/html; charset=utf-8');
								
			$pn_action = pn_maxf(pn_strip_input(is_isset($matches,1)), 250);
			if($pn_action and has_filter('premium_request_'.$pn_action)){
				do_action('premium_request_'.$pn_action);
			}
			
			exit;
			
		} elseif($super_base == 'api.html'){		
			
			status_header(501);
			
			do_action('pn_plugin_api');
			exit;			
	
		} elseif(preg_match("/^premium_action-([a-zA-Z0-9\_]+).html?$/", $super_base, $matches )){	
				
			header('Content-Type: text/html; charset=utf-8');
			
			status_header(501);	
				
			do_action('premium_post', 'action');	
							
			$pn_action = pn_maxf(pn_strip_input(is_isset($matches,1)), 250);
			
			if($pn_action and has_filter('premium_siteaction_'. $pn_action)){
				status_header(200);
				
				do_action('premium_siteaction_'. $pn_action);
			}

			exit;	
		}
			
	}
	
	add_action('init', 'set_premium_page_merchant', 2);
	function set_premium_page_merchant(){
		
		$data = premium_rewrite_data();
		$super_base = $data['super_base'];
		$matches = '';
		
		if(preg_match("/^merchant-([a-zA-Z0-9\_]+).html?$/", $super_base, $matches )){	
			header('Content-Type: text/html; charset=utf-8');
				
			status_header(501);	
				
			do_action('premium_merchants');			
						
			$pn_action = pn_maxf(pn_strip_input(is_isset($matches,1)), 250);
			
			if($pn_action and has_filter('premium_merchant_'.$pn_action)){
				status_header(200);
				
				do_action('premium_merchant_' . $pn_action);
			}
			
			exit;			
		} 	
	}	
}

if(!function_exists('set_premium_default_js')){
	function set_premium_default_js($place=''){ 
		$place = trim($place); if(!$place){ $place = 'site'; }
?>
jQuery(function($){

 	$('.ajax_post_form').ajaxForm({
		dataType: 'json',
		beforeSubmit: function(a,f,o) {
			f.addClass('thisactive');
			$('.thisactive input[type=submit], .thisactive input[type=button]').attr('disabled',true);
			$('.thisactive').find('.ajax_submit_ind').show();
		},
		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response', 'form', $place); ?>
		},
		success: function(res) {
					
			if(res['status'] == 'error'){
				if(res['status_text']){
					$('.thisactive .resultgo').html('<div class="resultfalse"><div class="resultclose"></div>'+res['status_text']+'</div>');
				}
			}			
			if(res['status'] == 'success'){
				if(res['status_text']){
					$('.thisactive .resultgo').html('<div class="resulttrue"><div class="resultclose"></div>'+res['status_text']+'</div>');
				}
			}
			
			if(res['clear']){
				$('.thisactive input[type=text]:not(.notclear), .thisactive input[type=password]:not(.notclear), .thisactive textarea:not(.notclear)').val('');
			}

			if(res['show_hidden']){
				$('.thisactive .hidden_line').show();
			}			
					
			if(res['url']){
				window.location.href = res['url']; 
			}
						
			<?php do_action('ajax_post_form_jsresult', $place); ?>
					
			$('.thisactive input[type=submit], .thisactive input[type=button]').attr('disabled',false);
			$('.thisactive').find('.ajax_submit_ind').hide();
			$('.thisactive').removeClass('thisactive');	
		}
	});	
	
	if(self != top && window.parent.frames.length > 0){
		$('.not_frame').remove();
	}  
	
	<?php do_action('premium_js_inside', $place); ?>
});		
<?php
	}
} 

if(!function_exists('view_premium_merchant_locale')){
	add_filter('locale','view_premium_merchant_locale',100);
	function view_premium_merchant_locale($locale){
		$data = premium_rewrite_data();
		$super_base = $data['super_base'];		
		if(preg_match("/^merchant-([a-zA-Z0-9\_]+).(php|html)?$/", $super_base, $matches)){
			$new_locale = get_pn_cookie('merch_locale');
			if($new_locale){
				return $new_locale;
			}
		}
		return $locale;
	}
}

if(!function_exists('set_premium_merchant_locale')){
	add_action('template_redirect','set_premium_merchant_locale', 3);
	function set_premium_merchant_locale(){
		add_pn_cookie('merch_locale', get_locale());
	}
}