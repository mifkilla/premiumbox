/*
version: 0.5
*/
jQuery(function($){	
	
    var defaults = { 
		id: '',
		window_class: '',
		insert_div: '',
		close_class: '',
		title: '',
		content: '',
		scrollContent: '',
		shadow: 1,
		draggable: 1,
		enable_button: 0,
		button_title: '',
		button_class: '',
		before: function(){ },
		after: function(){ },
		close_after: function(){ }
	};
	
    $.fn.JsWindow = function(method, params){
        var options = $.extend({}, defaults, options, params);
        var now_obj = $(this);
 
		var id = $.trim(options['id']);
		if(id.length < 1){ id = Math.floor(Math.random() * (100000 - 1 + 1)) + 1; }
		var window_class = $.trim(options['window_class']);
		var insert_div = $.trim(options['insert_div']);
		if(insert_div.length > 0){
			insert_obj = $(insert_div);
		} else {
			insert_obj = $('body');
		}
		var close_class = $.trim(options['close_class']);
		var shadow = parseInt($.trim(options['shadow']));
		var draggable = parseInt($.trim(options['draggable']));
		var enable_button = parseInt($.trim(options['enable_button']));
		var button_title = $.trim(options['button_title']);
		var button_class = $.trim(options['button_class']);
		var title = $.trim(options['title']);
		var content = $.trim(options['content']);
		var scrollContent = $.trim(options['scrollContent']);
		
		var before_func = options['before'];
		var after_func = options['after'];
		var close_after_func = options['close_after'];
 
 		if(method == 'show'){
			
			before_func.apply(null, [now_obj]);
			
			$('.js_techwindow').remove();
			
			if(shadow == 1){
				insert_obj.append('<div class="standart_shadow js_techwindow"></div>');
				$('.standart_shadow').show();
			}	
			
			var scroll_html = '';
			if(scrollContent.length > 0){
				scroll_html = ''+
				'<div class="standart_window_scrollcontent">' +
					scrollContent +
				'</div>';	
			}
			
			var button_html = '';
			if(enable_button == 1){
				button_html = '<div class="standart_window_submit"><input type="submit" class="button '+ button_class +'" name="" value="'+ button_title +'" /></div>';
			}
			
			var creating_window = '' +
			'<div class="standart_window '+ window_class +' js_techwindow" id="techwindow_'+ id +'">' +
				'<div class="standart_windowins">' +
					'<div class="standart_window_title">' +
						'<div class="standart_window_close js_window_close '+ close_class +'"></div>' +
						'<div class="standart_window_title_ins">' +
						title +
						'</div>' +
					'</div>' +
					'<div class="standart_window_content">' +
						'<div class="standart_window_content_ins">' +
							content + 
							'<div class="premium_clear"></div>' +
						'</div>' +
					'</div>' +
					scroll_html +
					button_html +
				'</div>' +
			'</div>';
			
			insert_obj.append(creating_window);
			
			$('#techwindow_'+ id).show();
			
			create_position_window();
			
			if(draggable == 1){
				$('#techwindow_'+ id).draggable({
					handle: ".standart_window_title",
					start: function() {
						$('#techwindow_'+ id).addClass('dragged');
					}
				});
			}
				
			after_func.apply(null,[now_obj]);	
				
		} else if(method == 'hide'){

			before_func.apply(null,[now_obj]);
			
			$('.js_techwindow').remove();
			
			after_func.apply(null,[now_obj]);
			
		}
		
		function create_position_window(){
			var window_hei = $(window).height();
			var hei = Math.ceil(($(window).height() - $('.standart_window').height()) / 2);
			var left = Math.ceil(($(window).width() - $('.standart_window').width()) / 2);
			$('.standart_window:not(.dragged)').css({'top': hei, 'left': left});
			$('.standart_window').css({'max-height': window_hei, 'overflow-y': 'auto'});			
		}
		
		$(window).on('scroll', function(){
			create_position_window();
		});
		$(window).on('resize', function(){
			create_position_window();
		});		
		
		$('.js_window_close').on('click', function(){
			$('.js_techwindow').remove();
			close_after_func.apply(null, [$(this)]);
		});
 
        return this;
    };
});