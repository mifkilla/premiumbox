/* version: 0.1 */
jQuery(function($){ 

	var default_params = {  
		id: '',
		tabId: '',
		expires: 7
	};
    $.fn.JTabs = function(params){
        var options = $.extend({}, default_params, params);
		var thet = $(this);
		
		var id = options['id'];
		var tabId = options['tabId'];
		var expires = parseInt(options['expires']);
		
		$('div[data-id='+ id +']').find('.toggle_links a').removeClass('active');
		$('div[data-id='+ id +']').find('.toggle_tab').removeClass('active');
		$('div[data-id='+ id +']').find('.toggle_links a[data-id='+ tabId +']').addClass('active');
		$('div[data-id='+ id +']').find('.toggle_tab[data-id='+ tabId +']').addClass('active');
		$('.toggle_select[data-id='+ id +']').val(tabId);
		
		Cookies.set(id, tabId, { expires: expires, path: '/' });

		$('body').trigger('resize');		
 
        return this;
    };
});