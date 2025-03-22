/*
version: 0.2
*/
jQuery(function($){
	
	function setSelectionRange(input, selectionStart, selectionEnd) {
		if (input.setSelectionRange) {
			input.focus();
			input.setSelectionRange(selectionStart, selectionEnd);
		} else if (input.createTextRange) {
			var range = input.createTextRange();
			range.collapse(true);
			range.moveEnd('character', selectionEnd);
			range.moveStart('character', selectionStart);
			range.select();
		}
	}	
	
	$(document).on('click', '.js_editor_tag',function(){
		var wrap_container = $(this).parents('.premium_editor');
		var now_textarea = wrap_container.find('textarea.premium_editor_textarea');
		var start_shortcode = $.trim($(this).find('.premium_editor_tag_start').val());
		var end_shortcode = $.trim($(this).find('.premium_editor_tag_end').val());
		
		var section_start = parseInt(now_textarea.prop('selectionStart'));
		var section_end = parseInt(now_textarea.prop('selectionEnd'));
		var section_value = now_textarea.val();
		var new_value = '';
		
		if(section_start == section_end){
			if(end_shortcode.length > 0){
				if($(this).hasClass('open_tag')){
					$(this).removeClass('open_tag');
					new_value = section_value.substr(0, section_start) + end_shortcode + section_value.substr(section_end, (section_value.length - section_start));
					now_textarea.val(new_value);
					setSelectionRange(now_textarea.get(0), (section_start + end_shortcode.length), (section_start + end_shortcode.length));
				} else {
					$(this).addClass('open_tag');
					new_value = section_value.substr(0, section_start) + start_shortcode + section_value.substr(section_end, (section_value.length - section_start));
					now_textarea.val(new_value);
					setSelectionRange(now_textarea.get(0), (section_start + start_shortcode.length), (section_start + start_shortcode.length));
				}
			} else {
				new_value = section_value.substr(0, section_start) + start_shortcode + section_value.substr(section_end, (section_value.length - section_start));
				now_textarea.val(new_value);
				setSelectionRange(now_textarea.get(0), (section_start + start_shortcode.length), (section_start + start_shortcode.length));
			}
		} else {
			if(end_shortcode.length > 0){
				if($(this).hasClass('open_tag')){
					$(this).removeClass('open_tag');
					new_value = section_value.substr(0, section_end) + end_shortcode + section_value.substr(section_end, (section_value.length - section_end));
					now_textarea.val(new_value);
					setSelectionRange(now_textarea.get(0), (section_end + end_shortcode.length), (section_end + end_shortcode.length));
				} else {
					new_value = section_value.substr(0, section_start) + start_shortcode + section_value.substr(section_start, (section_end - section_start)) + end_shortcode + section_value.substr(section_end, (section_value.length - section_end));
					now_textarea.val(new_value);
					setSelectionRange(now_textarea.get(0), (section_end + start_shortcode.length + end_shortcode.length), (section_end + start_shortcode.length + end_shortcode.length));
				}				
			} else {
				new_value = section_value.substr(0, section_start) + start_shortcode + section_value.substr(section_start, (section_value.length - section_start));
				now_textarea.val(new_value);
				setSelectionRange(now_textarea.get(0), (section_start + start_shortcode.length), (section_start + start_shortcode.length));
			}			
		}
		
		now_textarea.trigger('change');
			
		return false;
	});	

	function strip_content(s){
		s = $.trim(s);
		s = s.replace(/(^\s*)|(\s*$)/gi,"");
		s = s.replace(/[ ]{2,}/gi," ");
		s = s.replace(/\n /,"\n");
		return s;
	}

	function check_editor_words(object){
		var editor_content = object.find('textarea.premium_editor_textarea').val();
		editor_content = strip_content(editor_content);
		var cw = 0;
		var cs = editor_content.length;
		if(cs > 0){
			cw = editor_content.split(' ').length; 
		}
		object.find('.premium_editor_words span').html(cw);
		object.find('.premium_editor_symb span').html(cs);
	}

	$('.pn_word_count').each(function(){
		check_editor_words($(this));
	});
	$(document).on('change', '.pn_word_count textarea.premium_editor_textarea', function(){
		check_editor_words($(this).parents('.premium_editor'));	
	});
 	$(document).on('keyup', '.pn_word_count textarea.premium_editor_textarea', function(){
		check_editor_words($(this).parents('.premium_editor'));
	});
});