jQuery(function($){  
	
	$(document).on('click','.js_reply_close',function(){ 
	    $(this).parents('.js_reply_wrap').fadeOut(500);
		
	    return false;
	});	
	
    $(document).on('click','.premium_helptitle span',function(){
	    $(this).parents('.premium_wrap_help').toggleClass('act');
	    return false;
	});

	var userLang = navigator.language || navigator.userLanguage; 
	userLang = userLang.substr(0, 2).toLowerCase();

	$.datetimepicker.setLocale(userLang);

    $('.pn_datepicker').datetimepicker({ 
		timepicker:false,
		format:'d.m.Y'
    });	
	
    $('.pn_datetimepicker').datetimepicker({ 
		step: 15,
		format:'d.m.Y H:i'
    });

    $('.pn_timepicker').datetimepicker({
		datepicker:false,
		step: 1,
		format:'H:i'
    });	
	
	/* list table */
	$(document).AdaptiveTable({ trigger: '.premium_table table'});
	
	$(document).on('click', '.premium_tf_button', function(){
		$('.premium_tf').toggleClass('show_div');
		$('.premium_tf_ins').toggle();
		return false;
	});
	
	$(document).on('change', '.premium_tf_checkbox', function(){
		var parent_label = $(this).parents('label');
		if($(this).prop('checked')){
			parent_label.find('.premium_tf_checkbox_hidden').prop('checked', false);
		} else {
			parent_label.find('.premium_tf_checkbox_hidden').prop('checked', true);
		}		
		return false;
	});
	
	function action_pntable_row(item){
		var parent_tr = item.parents('.pntable_tr');
		if(item.prop('checked')){
			parent_tr.addClass('tr_active');
		} else {
			parent_tr.removeClass('tr_active');
		}		
	}
	 
	$(document).on('change', '.pntable-checkbox', function(event){ 
		var parent_table = $(this).parents('.premium_table_wrap');
		if($(this).prop('checked')){
			parent_table.find('.pntable-checkbox-single, .pntable-checkbox').each(function(){
				$(this).prop('checked', true);
				action_pntable_row($(this));
			});
		} else {
			parent_table.find('.pntable-checkbox-single, .pntable-checkbox').each(function(){
				$(this).prop('checked', false);
				action_pntable_row($(this));
			});
		}		
	});

	/* shift */
	$(".premium_table tr").mouseleave(function(e){
		if(e.shiftKey){
			$(this).find('.pntable-checkbox-single').addClass('shift_tr');
		}
	});	
	/* end shift */
	
	$(document).on('change', '.pntable-checkbox-single', function(e){
		action_pntable_row($(this));	

		/* shift */
		if($(this).prop('checked')){
			$('.shift_tr').each(function(){
				$(this).prop('checked', true);
				action_pntable_row($(this));
			});
		} else {
			$('.shift_tr').each(function(){
				$(this).prop('checked', false);
				action_pntable_row($(this));
			});			
		}
		
		$('.pntable-checkbox-single').removeClass('shift_tr');
		/* end shift */
		
		var parent_table = $(this).parents('.premium_table_wrap');
		if(parent_table.find('.pntable-checkbox-single:not(:checked)').length < 1){
			parent_table.find('.pntable-checkbox').prop('checked', true);
		} else {
			parent_table.find('.pntable-checkbox').prop('checked', false);
		}		
	});	
	
	$(document).on('keydown', '.premium_table', function( e ){
		if(e.which == 13){
			$(this).parents('form').find('input[name=save]').click();
			return false;
		}
	});	
	
	$(document).on('keydown','.standart_window',function(e){
		if(e.shiftKey && e.which == 13){
			$(this).find('form').submit();
			return false;
		}
	});	
	/* end list table */	
	
	$('.js_line_label').on('click', function(){
		var value_key = $(this).attr('data-for');
		$('#pnline_'+value_key).find('input:visible, select:visible, textarea:visible').each(function(){
			$(this).focus();
			return false;
		});
	});

	function str_rand(num_lenth) {
		var result       = '';
		var words        = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
		var max_position = words.length - 1;
		for( i = 0; i < num_lenth; ++i ) {
			var position = Math.floor ( Math.random() * max_position );
			result = result + words.substring(position, position + 1);
		}
		return result;
	}	
	
	$('.input_password_generate').on('click', function(){
		var parent_div = $(this).parents('.input_password_wrap');
		parent_div.find('input').val(str_rand(16));
	});	
	
	function set_aonce(){
		if($('.premium_stline_left').length > 0){
			var wid = window.innerWidth;
			if(wid >= 700){
				$('.premium_stline_left:visible').each(function(){
					if($(this).find('.premium_stline_left_ins').length > 0){
						var t_hei = $(this).parents('.premium_standart_line').height();
						var now_hei = $(this).height();
						$(this).find('.premium_stline_left_ins').css({'height': t_hei}).fadeIn();
					}
				});
			} else {
				$('.premium_stline_left_ins').css({'height': 'auto'});
			}
		}
	}
	
	$(window).on('resize', function(){
		set_aonce();
	});
	$('.premium_body').on('resize', function(){
		set_aonce();
	});	
	set_aonce();	
	
	$(document).on('change', '.checkbox_once', function(event){
		var parent_div = $(this).parents('.checkbox_all_div');		
		if(parent_div.find('.checkbox_once:not(:checked)').length < 1){
			parent_div.find('.checkbox_all').prop('checked', true);
		} else {
			parent_div.find('.checkbox_all').prop('checked', false);
		}		
	});

	$(document).on('change', '.checkbox_all', function(event){
		var parent_div = $(this).parents('.checkbox_all_div');
		if($(this).prop('checked')){
			parent_div.find('.checkbox_once, .checkbox_all').prop('checked', true);
		} else {
			parent_div.find('.checkbox_once, .checkbox_all').prop('checked', false);
		}
	});
	
	function search_check_text(thet){
		var par = thet.parents('.checkbox_all_div');
		var txt = $.trim(thet.val()).toLowerCase();
		par.find('.checkbox_once_div').hide();
		if(txt.length > 0){
			par.find('.checkbox_once_div span').each(function(){
				var option_html = $(this).attr('data-s');
				if(option_html.toLowerCase().indexOf(txt) + 1) {
					$(this).parents('.checkbox_once_div').show();
				} 
			});	
		} else {
			par.find('.checkbox_once_div').show();
		}
		$('.premium_body').trigger('resize');
	}	
	
	$(document).on('keydown', '.checkbox_all_search', function(e){
		if(e.which == '13'){
			return false;
		}
	});	
	$(document).ChangeInput({ 
		trigger: '.checkbox_all_search',
		success: function(obj){
			search_check_text(obj);
		}
	});	

	var ssa = 1;
	function search_select_action(thet, ind){
		var par = thet.parents('.js_select_search_wrap');
		var txt = $.trim(thet.val()).toLowerCase();
		var now_select = par.find('select');
		if(txt.length > 0){
			var s = 0;
			now_select.find('option').removeClass('ns');
			now_select.find('option').each(function(){
				var option_html = $(this).html();
				if(option_html.toLowerCase().indexOf(txt) + 1) {
					s++;
					$(this).addClass('ns');	
				} 
			});
			if(s > 0){
				if(ind > s){ ind = 1; ssa = 1; }
				var ssa_index = ind - 1;
				now_select.find('option.ns').eq(ssa_index).prop('selected', true).trigger("change");

				return false;
			}	
		} 
		now_select.find('option:first').prop('selected', true).trigger("change");
	}		 
	$(document).on('keyup', '.js_select_search', function(e){
		if(e.which == '13'){
			ssa = ssa + 1;
			search_select_action($(this), ssa);
			return false;
		}
	});	
	$(document).on('keydown', '.js_select_search', function(e){
		if(e.which == '13'){
			return false;
		}
	});
	$(document).ChangeInput({ 
		trigger: '.js_select_search',
		success: function(obj){
			search_select_action(obj, 1);
		}
	});	
	
	$(document).on('click', '.save_admin_ajax_form', function(){
		$('.admin_ajax_form').submit();
	});
	
	if($('.update-nag').length > 0 && $('.premium_tf').length > 0){
		var nag_text = $('.update-nag').html();
		$('.update-nag').remove();
		$('.premium_tf').after('<div class="update-nag notice notice-warning inline">'+ nag_text +'</div>');
	}
	
	var clipboard = new ClipboardJS('.clpb_item');
	
});