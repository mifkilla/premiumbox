<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('get_comment_label')){
	function get_comment_label($identifier, $id, $comment='', $title=''){
		$comment = pn_strip_input($comment);
		$identifier = pn_string($identifier);
		$id = pn_strip_input($id);
		
		$title = pn_strip_input($title);
		if(!$title){ $title = __('Comment','premium'); }
		
		$class = '';
		if($comment){ 
			$class = 'has_comment'; 
		}
		$temp = '<div class="column_comment_label js_csl '. $identifier .' '. $identifier .'-'. $id .' '. $class .'" data-bd="'. $identifier .'" data-id="'. $id .'" data-title="'. $title .'"></div>';
			
		return $temp;
	}
}

if(!function_exists('comment_system_admin_footer')){
	add_action('pn_adminpage_js', 'comment_system_admin_footer');
	function comment_system_admin_footer(){
		?>
		$(document).on('click','.js_csl',function(){	
			$(document).JsWindow('show', { 
				window_class: 'comment_window',
				title: 'loading...',
				content: '<form action="<?php the_pn_link('csl_add');?>" class="csl_form_action" method="post"><p><textarea id="csl_the_text" name="comment"></textarea></p><p><input type="submit" name="submit" class="button-primary" value="<?php _e('Save','premium'); ?>" /></p><input type="hidden" id="csl_the_id" name="id" value="" /><input type="hidden" id="csl_the_bd" name="bd" value="" /></form>',
				scrollContent: '<div id="csl_the_scroll" style="height: 80px;"></div>',
				shadow: 0,
				after: after_csl_form
			});		
			
			var id = $(this).attr('data-id');
			var bd = $(this).attr('data-bd');
			var title = $(this).attr('data-title');
			$('.standart_window_title_ins').html(title);
			$('#csl_the_id').val(id);
			$('#csl_the_bd').val(bd);
			
			csl_load_comments();
			
			return false;
		});
		
		$(document).on('click','.js_csl_del',function(){
			var id = $(this).attr('data-id');
			var bd = $(this).attr('data-bd');
			var param = 'id=' + id + '&bd=' + bd;
			$('.js_csl_del').hide();
			
			$.ajax({
				type: "POST",
				url: "<?php the_pn_link('csl_del');?>",
				dataType: 'json',
				data: param,
				error: function(res, res2, res3){
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},			
				success: function(res)
				{		
					if(res['status'] == 'error'){
						<?php do_action('pn_js_alert_response'); ?>
					} else {
						csl_load_comments();
					}	
					$('.js_csl_del').show();
					<?php do_action('csl_del_jsresult'); ?>
				}
			});
		});	
		
		function csl_load_comments(){
			$('.comment_window input[type=submit]').prop('disabled',true);
			$('#csl_the_text').val('').prop('disabled',true);
			$('#csl_the_scroll').html('<center>loading...</center>');
			var id = $('#csl_the_id').val();
			var bd = $('#csl_the_bd').val();
			var js_but = $('.js_csl[data-id='+ id +'][data-bd='+ bd +']');

			var param = 'id=' + id + '&bd=' + bd;
			$.ajax({
				type: "POST",
				url: "<?php the_pn_link('csl_get');?>",
				dataType: 'json',
				data: param,
				error: function(res, res2, res3){
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},			
				success: function(res)
				{		
					$('.comment_window input[type=submit]').prop('disabled',false);
					
					if(res['status'] == 'error'){
						<?php do_action('pn_js_alert_response'); ?>
					} else {
						$('#csl_the_text').val(res['comment']);	
						$('#csl_the_scroll').html(res['last']);
						if(res['count'] > 0){
							js_but.addClass('has_comment');
						} else {
							js_but.removeClass('has_comment');
						}
					}	
					
					$('#csl_the_text').prop('disabled', false);
					<?php do_action('csl_get_jsresult'); ?>
				}
			});			
		}
		
		function after_csl_form(){
			$('.csl_form_action').ajaxForm({
				dataType:  'json',
				beforeSubmit: function(a,f,o) {
					$('.comment_window input[type=submit]').prop('disabled',true);
				},
				error: function(res, res2, res3) {
					<?php do_action('pn_js_error_response', 'form'); ?>
				},		
				success: function(res) {
					$('.comment_window input[type=submit]').prop('disabled',false);
					if(res['status'] && res['status'] == 'error'){
						<?php do_action('pn_js_alert_response'); ?>
					} 
					if(res['status'] && res['status'] == 'success'){
						csl_load_comments();
					}
					<?php do_action('csl_add_jsresult'); ?>
				}
			});	
		}
		<?php
	}
}

/* comments */
if(!function_exists('pn_premium_action_csl_get')){
	add_action('premium_action_csl_get', 'pn_premium_action_csl_get');
	function pn_premium_action_csl_get(){
		only_post();

		header('Content-Type: application/json; charset=utf-8');

		$log = array();
		$log['status'] = '';
		$log['response'] = '';
		$log['status_code'] = 0; 
		$log['status_text'] = __('Error','pn');	
		$log['count'] = 0;

		$id = pn_strip_input(is_param_post('id'));
		$bd = pn_strip_input(is_param_post('bd'));
		if(current_user_can('read')){
			$log = apply_filters('csl_get_' . $bd, $log, $id);	
		}
		echo json_encode($log);
		exit;
	}
}

if(!function_exists('pn_premium_action_csl_add')){
	add_action('premium_action_csl_add', 'pn_premium_action_csl_add');
	function pn_premium_action_csl_add(){
		only_post();

		header('Content-Type: application/json; charset=utf-8');

		$log = array();
		$log['status'] = '';
		$log['response'] = '';
		$log['status_code'] = 0; 
		$log['status_text'] = __('Error','pn');
		
		$id = pn_strip_input(is_param_post('id'));
		$bd = pn_strip_input(is_param_post('bd'));
		if(current_user_can('read')){
			$log = apply_filters('csl_add_' . $bd, $log, $id);	
		}
		echo json_encode($log);	
		exit;
	}
}

if(!function_exists('pn_premium_action_csl_del')){
	add_action('premium_action_csl_del', 'pn_premium_action_csl_del');
	function pn_premium_action_csl_del(){
		only_post();

		header('Content-Type: application/json; charset=utf-8');

		$log = array();
		$log['status'] = '';
		$log['response'] = '';
		$log['status_code'] = 0; 
		$log['status_text'] = __('Error','pn');
		
		$id = pn_strip_input(is_param_post('id'));
		$bd = pn_strip_input(is_param_post('bd'));
		if(current_user_can('read')){
			$log = apply_filters('csl_del_' . $bd, $log, $id);	
		}
		echo json_encode($log);	
		exit;
	}
}
/* end comments */	