<?php
if( !defined( 'ABSPATH')){ exit(); }
	
add_action('premium_js','premium_js_exchange_tooltip'); 
function premium_js_exchange_tooltip(){
?>	
jQuery(function($){	
	/* help exchange */	
	$(document).on('focusin', '.js_window_wrap',function(){
		$(this).addClass('showed');
	});	
	$(document).on('focusout', '.js_window_wrap',function(){
		$(this).removeClass('showed');
	});	
	/* end help exchange */	
});
<?php
}
	
add_action('premium_js','premium_js_exchange_action'); 
function premium_js_exchange_action(){
?>	
jQuery(function($){
	
	function checknumbr(mixed_var) {
		return ( mixed_var == '' ) ? false : !isNaN( mixed_var );
	}	
	
	$(document).on('change', 'select, input:not(.js_sum_val)', function(){
		$(this).parents('.js_wrap_error').removeClass('error');
	});
	$(document).on('click', 'input, select', function(){
		$(this).parents('.js_wrap_error').removeClass('error');
	});	
	
	$(document).on('click', '.js_amount', function(){
		var amount = $(this).attr('data-val');
		var id = $(this).attr('data-id');
		$('input.js_'+id + ':not(:disabled)').val(amount).trigger('change');
		$('.js_'+id + '_html').html(amount);
	});	
	
	function cache_exchange_data(thet){
		var ind = 1;
		if(thet.hasClass('check_cache')){
			var not_check_data = Cookies.get('not_check_data');
			if(not_check_data == 1){
				ind = 0;
			}	
		} 
		if(ind == 1){
			var id = thet.attr('cash-id');
			var v = thet.val();
			Cookies.set("cache_"+id, v, { expires: 7, path: '/' });	
		}
	}
	
	$(document).ChangeInput({ 
		trigger: '.cache_data',
		success: function(obj){
			cache_exchange_data(obj);
		}
	});	
	
	$(document).on('change', '#not_check_data', function(){
		if($(this).prop('checked')){
			Cookies.set("not_check_data", 1, { expires: 7, path: '/' });
			$('.check_cache').each(function(){
				var id = $(this).attr('cash-id');
				Cookies.remove("cache_"+id);
			});	
		} else {
			Cookies.set("not_check_data", 0, { expires: 7, path: '/' });
			$('.check_cache').each(function(){
				var id = $(this).attr('cash-id');
				var v = $(this).val();
				Cookies.set("cache_"+id, v, { expires: 7, path: '/' });
			});		
		}
	});
	
	$(document).on('click', '.ajax_post_bids input[type=submit]', function(){
		var count_window = $('.window_message').length;
		if(count_window > 0){
			
			$(document).JsWindow('show', {
				window_class: 'update_window',
				close_class: 'js_direction_window_close',
				title: '<?php _e('Attention!','pn'); ?>',
				content: $('.window_message').html(),
				shadow: 1,
				enable_button: 1,
				button_title: '<?php _e('OK','pn'); ?>',
				button_class: 'js_window_close js_direction_window_close'
			});			
			
			return false;
		} 
	});
	
    $(document).on('click', '.js_direction_window_close', function(){
		$('.ajax_post_bids').submit();
    });	
	
	function add_error_field(id, text){
		$('.js_'+ id).parents('.js_wrap_error').addClass('error');
		if(text.length > 0){
			$('.js_'+ id +'_error').html(text).show();
		}
	}	
	function remove_error_field(id){
		$('.js_'+ id).parents('.js_wrap_error').removeClass('error');
	}	
	
	var res_timer = '';
	function start_res_timer(){
		
		$('.res_timer').html('0');
		clearInterval(res_timer);
		
		res_timer = setInterval(function(){ 
			if($('.res_timer').length > 0){
				var num_t = parseInt($('.res_timer').html());
				num_t = num_t + 1;
				$('.res_timer').html(num_t);				
			}
		},1000);
	}
	
    $('.ajax_post_bids').ajaxForm({
        dataType:  'json',
		beforeSubmit: function(a,f,o) {
			f.addClass('thisactive');
			$('.thisactive input[type=submit], .thisactive input[type=button]').attr('disabled',true);
			$('.ajax_post_bids_res').html('<div class="resulttrue"><?php echo esc_attr(__('Processing. Please wait','pn')); ?> (<span class="res_timer">0</span>)</div>');
			
			start_res_timer();
			
			$('.ajax_post_bids_res').find('.js_wrap_error').removeClass('error');
			
			<?php do_action('ajax_post_form_process', 'site', 'bidsform'); ?>
        },
		error: function(res, res2, res3) {
			$('.ajax_post_bids_res').html('<div class="resultfalse"><?php echo esc_attr(__('Database error','pn')); ?></div>');
			<?php do_action('pn_js_error_response', 'form'); ?>
		},		
        success: function(res) { 
			if(res['error_fields']){
				$.each(res['error_fields'], function(index, value){
					add_error_field(index, value);
				});					
			}			
			if(res['status'] && res['status'] == 'error'){
				$('.ajax_post_bids_res').html('<div class="resultfalse"><div class="resultclose"></div>'+ res['status_text'] +'</div>');
				if($('.js_wrap_error.error').length > 0){
					var ftop = $('.js_wrap_error.error:first').offset().top - 100;
					$('body,html').animate({scrollTop: ftop},500);
				}
			}
			if(res['status'] && res['status'] == 'success'){
				$('.ajax_post_bids_res').html('<div class="resulttrue"><div class="resultclose"></div>'+ res['status_text'] +' (<span class="res_timer">0</span>)</div>');
				start_res_timer();
			}				
		
			if(res['url']){
				window.location.href = res['url']; 
			}
			
			<?php do_action('ajax_post_form_jsresult', 'site', 'bidsform'); ?>
		
		    $('.thisactive input[type=submit], .thisactive input[type=button]').attr('disabled',false);
			$('.thisactive').removeClass('thisactive');
        }
    });	
	
	function calc_set_value(the_obj, the_num){
		<?php echo apply_filters('js_calc_set_value', '$(the_obj).val(the_num);'); ?>
	}
	
	function calc_set_html(the_obj, the_num){
		<?php echo apply_filters('js_calc_set_html', '$(the_obj).html(the_num);'); ?>
	}	
	
	function go_exchange_calc(sum, dej){
		
		var id = $('.js_direction_id:first').val();
		var param = <?php echo apply_filters("go_exchange_calc_js","'id='+id+'&sum='+sum+'&dej='+dej"); ?>;
		
		$('.exch_ajax_wrap_abs, .js_exchange_widget_abs, .js_loader').show();
		
        $.ajax({
            type: "POST",
            url: "<?php echo get_pn_action('exchange_calculator');?>",
            data: param,
	        dataType: 'json',
 			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},           
			success: function(res){ 
			
				if(dej !== 1){
					calc_set_value('input.js_sum1', res['sum1']);
					calc_set_html('.js_sum1_html', res['sum1']);
					
					Cookies.set("cache_sum", res['sum1'], { expires: 7, path: '/' });
				}
				if(dej !== 2){
					calc_set_value('input.js_sum2', res['sum2']);
					calc_set_html('.js_sum2_html', res['sum2']);					
				}
				if(dej !== 3){
					calc_set_value('input.js_sum1c', res['sum1c']);
					calc_set_html('.js_sum1c_html', res['sum1c']);					
				}
				if(dej !== 4){
					calc_set_value('input.js_sum2c', res['sum2c']);
					calc_set_html('.js_sum2c_html', res['sum2c']);					
				}								
				
				$('.js_comis_text1').html(res['comis_text1']);
				$('.js_comis_text2').html(res['comis_text2']);				
				
				remove_error_field('sum1');
				remove_error_field('sum2');
				remove_error_field('sum1c');
				remove_error_field('sum2c');
				
				if(res['error_fields']){
					$.each(res['error_fields'], function(index, value){
						add_error_field(index, value);
					});					
				}				
				
				if(res['curs_html'] && res['curs_html'].length > 0){
					$('.js_curs_html').html(res['curs_html']);
					$('input.js_curs_html').val(res['curs_html']);
				}			
				if(res['reserv_html'] && res['reserv_html'].length > 0){
					$('.js_reserv_html').html(res['reserv_html']);
					$('input.js_reserv_html').val(res['reserv_html']);
				}
				if(res['user_discount'] && res['user_discount'].length > 0){
					$('.js_direction_user_discount').html(res['user_discount']);
					$('input.js_direction_user_discount').html(res['user_discount']);
				}	
				if(res['viv_com1'] && res['viv_com1'] == 1){
					$('.js_viv_com1').show();
				} else {
					$('.js_viv_com1').hide();
				}
				if(res['viv_com2'] && res['viv_com2'] == 1){
					$('.js_viv_com2').show();
				} else {
					$('.js_viv_com2').hide();
				}			

				<?php do_action('go_exchange_calc_js_response'); ?>
				
				$('.exch_ajax_wrap_abs, .js_exchange_widget_abs, .js_loader').hide();
            }
		});	
	}

	function go_calc(obj, f_id){
		var vale = obj.val().replace(/,/g,'.');
		if (checknumbr(vale)){

			if(f_id == 1){
				$('input.js_sum1:not(:focus)').val(vale);
				$('.js_sum1_html').html(vale);
			} else if(f_id == 2){
				$('input.js_sum2:not(:focus)').val(vale);
				$('.js_sum2_html').html(vale);					
			} else if(f_id == 3){
				$('input.js_sum1c:not(:focus)').val(vale);
				$('.js_sum1c_html').html(vale);
			} else if(f_id == 4){
				$('input.js_sum2c:not(:focus)').val(vale);
				$('.js_sum2c_html').html(vale);				
			}						
			
			go_exchange_calc(vale, f_id);
		} else {
			obj.parents('.js_wrap_error').addClass('error').find('.js_error').hide();
		}
	}					
	
	$(document).ChangeInput({ 
		trigger: '.js_sum1',
		success: function(obj){
			go_calc(obj, 1);
		}
	});

	$(document).ChangeInput({ 
		trigger: '.js_sum2',
		success: function(obj){
			go_calc(obj, 2);
		}
	});

	$(document).ChangeInput({ 
		trigger: '.js_sum1c',
		success: function(obj){
			go_calc(obj, 3);
		}
	});

	$(document).ChangeInput({ 
		trigger: '.js_sum2c',
		success: function(obj){
			go_calc(obj, 4);
		}
	});	
	
	function set_input_decimal(obj){
		var dec = obj.attr('data-decimal');
		var sum = obj.val().replace(new RegExp(",",'g'),'.');
		var len_arr = sum.split('.');
		var len_data = len_arr[1];
		if(len_data !== undefined){
			var len = len_data.length;
			if(len > dec){
				var new_data = len_data.substr(0, dec);
				obj.val(len_arr[0]+'.'+new_data);
			}
		}			
	}	
	
	$(document).ChangeInput({ 
		trigger: '.js_decimal',
		success: function(obj){
			set_input_decimal(obj);
		}
	});	
	 
	<?php do_action('exchange_action_jquery'); ?>
});	
<?php	
} 
 
/* bids create */
add_action('premium_siteaction_bidsform', 'def_premium_siteaction_bidsform');
function def_premium_siteaction_bidsform(){
global $wpdb, $premiumbox;	
	
	header('Content-Type: application/json; charset=utf-8'); 
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	
	$log = array();
	$log['status'] = '';
	$log['response'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');	

	$premiumbox->up_mode('post');

	$direction_id = intval(is_param_post('direction_id'));
	
	$check_rule = intval(is_param_post('check_rule'));
	$enable_step2 = intval($premiumbox->get_option('exchange','enable_step2'));
	if(!$check_rule and $enable_step2 == 0){
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! You have not accepted the terms and conditions of the User Agreement','pn');
		echo json_encode($log);
		exit;		
	}	
	
	$log = apply_filters('before_ajax_form_field', $log, 'exchangeform');
	$log = apply_filters('before_ajax_bidsform', $log, $direction_id);	
	
	if(!$direction_id){
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! The direction do not exist','pn');
		echo json_encode($log);
		exit;		
	}
	
	$direction_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."directions WHERE direction_status='1' AND auto_status='1' AND id='$direction_id'");
	if(!isset($direction_data->id)){
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! Exchange direction is disabled','pn');
		echo json_encode($log);
		exit;		
	}
	
	$direction = array();
	foreach($direction_data as $direction_key => $direction_val){
		$direction[$direction_key] = $direction_val;
	}
	$directions_meta = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions_meta WHERE item_id='$direction_id'");
	foreach($directions_meta as $direction_item){
		$direction[$direction_item->meta_key] = $direction_item->meta_value;
	}	
	$direction = (object)$direction; /* вся информация о направлении */
	
	$currency_id_give = intval($direction->currency_id_give);
	$currency_id_get = intval($direction->currency_id_get);
	
	$vd1 = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE id='$currency_id_give'");
	$vd2 = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE id='$currency_id_get'");

	if(!isset($vd1->id) or !isset($vd2->id)){
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! The direction do not exist','pn');
		echo json_encode($log);
		exit;		
	}	
	
	$error_fields = array();
	$error_text = array();	
	
	/* счета валют */
	$account1 = $account2 = '';
	$show = apply_filters('form_bids_account_give', $vd1->show_give, $direction, $vd1);
	if($show == 1){
		$account1 = is_param_post('account1');
		$account1 = get_purse($account1, $vd1);
		if(!$account1){
			$error_fields['account1'] = __('invalid account number','pn');
		}
	}
	
	$show = apply_filters('form_bids_account_get', $vd2->show_get, $direction, $vd2);
	if($show == 1){
		$account2 = is_param_post('account2');
		$account2 = get_purse($account2, $vd2);
		if(!$account2){
			$error_fields['account2'] = __('invalid account number','pn');
		}
	}
	/* end счета валют */	
	
	$post_sum = is_sum(is_param_post('sum1'));
	$calc_data = array(
		'vd1' => $vd1,
		'vd2' => $vd2,
		'direction' => $direction,
		'user_id' => $user_id,
		'ui' => $ui,
		'post_sum' => $post_sum,
		'account1' => $account1,
		'account2' => $account2,
	);
	$calc_data = apply_filters('get_calc_data_params', $calc_data, 'action');
	$cdata = get_calc_data($calc_data);
	
	$decimal_give = $cdata['decimal_give'];
	$decimal_get = $cdata['decimal_get'];
	$currency_code_give = $cdata['currency_code_give'];
	$currency_code_get = $cdata['currency_code_get'];
	$psys_give = $cdata['psys_give'];
	$psys_get = $cdata['psys_get'];	
	$course_give = $cdata['course_give'];
	$course_get = $cdata['course_get'];	
	
	$sum1 = $cdata['sum1'];
	$sum1c = $cdata['sum1c'];
	$sum2 = $cdata['sum2'];
	$sum2c = $cdata['sum2c'];	
	
	$dir_minmax = get_direction_minmax($direction, $vd1, $vd2, $course_give, $course_get);  
	$min1 = is_isset($dir_minmax, 'min_give');
	$max1 = is_isset($dir_minmax, 'max_give');
	$min2 = is_isset($dir_minmax, 'min_get');
	$max2 = is_isset($dir_minmax, 'max_get');		
		
	if($sum1 < $min1){
		$error_fields['sum1'] = '<span class="js_amount" data-id="sum1" data-val="'. $min1 .'">' . __('min','pn').'.: '. is_out_sum($min1, $cdata['decimal_give'], 'reserv') .' '.$currency_code_give . '</span>';													
	}						
	if($sum1 > $max1 and is_numeric($max1)){
		$error_fields['sum1'] = '<span class="js_amount" data-id="sum1" data-val="'. $max1 .'">' . __('max','pn').'.: '. is_out_sum($max1, $cdata['decimal_give'], 'reserv') .' '.$currency_code_give . '</span>';													
	}						
	if($sum2 < $min2){
		$error_fields['sum2'] = '<span class="js_amount" data-id="sum2" data-val="'. $min2 .'">' . __('min','pn').'.: '. is_out_sum($min2, $cdata['decimal_get'], 'reserv') .' '.$currency_code_get . '</span>';													
	}							
	if($sum2 > $max2 and is_numeric($max2)){
		$error_fields['sum2'] = '<span class="js_amount" data-id="sum2" data-val="'. $max2 .'">' . __('max','pn').'.: '. is_out_sum($max2, $cdata['decimal_get'], 'reserv') .''.$currency_code_get . '</span>';													
	}	
	
	if($sum1 <= 0){
		$error_fields['sum1'] = __('amount must be greater than 0','pn');
	}							
	if($sum2 <= 0){
		$error_fields['sum2'] = __('amount must be greater than 0','pn');
	}						
	if($sum1c <= 0){
		$error_fields['sum1c'] = __('amount must be greater than 0','pn');
	}							
	if($sum2c <= 0){
		$error_fields['sum2c'] = __('amount must be greater than 0','pn');
	}		
	/* end максимум и минимум */
	
	/* данные по валютам */
	$psys_give = pn_strip_input($vd1->psys_title);
	$psys_get = pn_strip_input($vd2->psys_title);
	$currency_id_give = $vd1->id;
	$currency_id_get = $vd2->id;
	$currency_code_give = $vd1->currency_code_title;
	$currency_code_get = $vd2->currency_code_title;
	$currency_code_id_give = $vd1->currency_code_id;
	$currency_code_id_get = $vd2->currency_code_id;	
	$psys_id_give = $vd1->psys_id;
	$psys_id_get = $vd2->psys_id;	
	/* end данные по валютам */
	
	$unmetas = array();
	$auto_data = array();
	$metas = array();
	$dmetas = array();
	
	/* основные поля */
	$dir_fields = $wpdb->get_results("
	SELECT * FROM ".$wpdb->prefix."direction_custom_fields LEFT OUTER JOIN ". $wpdb->prefix ."cf_directions ON(".$wpdb->prefix."direction_custom_fields.id = ". $wpdb->prefix ."cf_directions.cf_id) 
	WHERE ".$wpdb->prefix."direction_custom_fields.auto_status='1' AND ".$wpdb->prefix."direction_custom_fields.status='1' AND ". $wpdb->prefix ."cf_directions.direction_id = '$direction_id' ORDER BY cf_order ASC");
	foreach($dir_fields as $op_item){
		$op_id = $op_item->cf_id;
		$op_vid = $op_item->vid;
		$op_name = pn_strip_input($op_item->cf_name);
		$op_req = $op_item->cf_req;
		$op_value = is_param_post('cf'.$op_id);
		$op_uniq = '';
		if($op_vid == 0){
			$op_value = $op_uniq = get_purse($op_value,$op_item);
		} else {
			$op_value = intval($op_value);
		}
		
		if(!$op_value and $op_req == 1){
			if($op_vid == 0){
				$error_fields['cf'.$op_id] = __('field is filled with errors','pn');
			} else {
				$error_fields['cf'.$op_id] = __('value is not selected','pn');
			}
		}		
		
		$op_auto = $op_item->cf_auto;
		if(!$op_auto){ /* если не авто поле */
			if($op_vid == 0){
				
				$metas[] = array(
					'title' => $op_name,
					'data' => $op_value,
					'id' => $op_id,
				);
				
			} else { /* select */
			
				$op_datas = explode("\n",ctv_ml($op_item->datas));
				foreach($op_datas as $key => $da){
					$key = $key + 1;
					$da = pn_strip_input($da);
					if($da){
						if($key == $op_value){
							$op_uniq = $op_name;
							$metas[] = array(
								'title' => $op_name,
								'data' => $da,
								'id' => $op_id,
							);
						}		
					}
				}
				
			}
		} else {
			
			$op_value = $op_uniq = strip_uf($op_value, $op_auto);
			
			if(!$op_value and $op_req == 1){
				$error_fields['cf'.$op_id] = __('field is filled with errors','pn');	
			} 
			
			$cauv = array(
				'error' => 0,
				'error_text' => ''
			);
			$cauv = apply_filters('cf_auto_form_value',$cauv, $op_value, $op_item, $direction, $cdata);
			
			if($cauv['error'] == 1){
				$error_fields['cf'.$op_id] = $cauv['error_text'];				
			}
							
			$auto_data[$op_auto] = $op_value;
			
		}
		
		$uniqueid = pn_strip_input($op_item->uniqueid);
		if($uniqueid){
			$unmetas[$uniqueid] = $op_uniq;
		}		
	}
	/* end основные поля */		
	
	/* дополнительные поля */
	$dmetas[1] = $dmetas[2] = array();	
	
	$sql = "
	SELECT * FROM ".$wpdb->prefix."currency_custom_fields
	LEFT OUTER JOIN ". $wpdb->prefix ."cf_currency
	ON(".$wpdb->prefix."currency_custom_fields.id = ". $wpdb->prefix ."cf_currency.cf_id)
	WHERE ".$wpdb->prefix."currency_custom_fields.auto_status = '1' AND ".$wpdb->prefix."currency_custom_fields.status='1' AND ". $wpdb->prefix ."cf_currency.currency_id = '$currency_id_give' AND ". $wpdb->prefix ."cf_currency.place_id = '1'
	OR ".$wpdb->prefix."currency_custom_fields.auto_status = '1' AND ".$wpdb->prefix."currency_custom_fields.status='1' AND ". $wpdb->prefix ."cf_currency.currency_id = '$currency_id_get' AND ". $wpdb->prefix ."cf_currency.place_id = '2'
	";
	$doppoles = $wpdb->get_results($sql);
	foreach($doppoles as $dp_item){
		$place_id = $dp_item->place_id;
		$dp_id = $dp_item->cf_id;
		$cf_now = 'cfgive'.$dp_id;
		if($place_id == 2){
			$cf_now = 'cfget'.$dp_id;
		}
		$dp_vid = $dp_item->vid;
		$dp_name = pn_strip_input($dp_item->cf_name);
		$dp_req = $dp_item->cf_req;
		$dp_hidden = $dp_item->cf_hidden;
		$dp_value = is_param_post($cf_now);
		$dp_uniq = '';
		if($dp_vid == 0){
			$dp_value = $dp_uniq = get_purse($dp_value,$dp_item);
		} else {
			$dp_value = intval($dp_value);
		}		
		
		if(!$dp_value and $dp_req == 1){
			if($dp_vid == 0){
				$error_fields[$cf_now] = __('field is filled with errors','pn');
			} else {
				$error_fields[$cf_now] = __('value is not selected','pn');
			}
		}		
		
		if($dp_vid == 0){
				
			$dmetas[$place_id][] = array(
				'title' => $dp_name,
				'data' => $dp_value,
				'hidden' => $dp_hidden,
				'id' => $dp_id,
			);
				
		} else { /* select */
		
			$dp_datas = explode("\n",ctv_ml($dp_item->datas));
			foreach($dp_datas as $key => $da){
				$key = $key + 1;
				$da = pn_strip_input($da);
				if($da){
					if($key == $dp_value){
						$dp_uniq = $dp_name;
						$dmetas[$place_id][] = array(
							'title' => $dp_name,
							'data' => $da,
							'hidden' => $dp_hidden,
							'id' => $dp_id,
						);
					}		
				}
			}
				
		}
		
		$uniqueid = pn_strip_input($dp_item->uniqueid);
		if($uniqueid){
			$unmetas[$uniqueid] = $dp_uniq;
		}		
	}	
	/* end доп.поля */		
	
	$user_ip = pn_real_ip();
	
	/* проверки на обмен */
	$error_bids = array(
		'error_text' => $error_text,
		'error_fields' => $error_fields,
	);
	$error_bids = apply_filters('error_bids', $error_bids, $account1, $account2, $direction, $vd1, $vd2, $auto_data, $unmetas, $cdata);

	$error_text = $error_bids['error_text'];
	$error_fields = $error_bids['error_fields'];			
	/* end проверки */
	
	
	if(count($error_text) > 0 or count($error_fields) > 0){
		
		$log['status'] = 'error';
		$log['status_code'] = 1;
		if(is_array($error_text) and count($error_text) > 0){ 
			$error_text = join('<br />',$error_text);
		} else {
			$error_text = __('Error!','pn'); 
		}
		$log['status_text'] = $error_text;
		
	} else {
		
		$datetime = current_time('mysql');
		$hashed = unique_bid_hashed();
		
		$array = array();
		$array['create_date'] = $datetime;
		$array['edit_date'] = $datetime;
		$array['hashed'] = $hashed;
		$array['status'] = 'auto';
		$array['bid_locale'] = get_locale();
		$array['direction_id'] = $direction_id;
		$array['course_give'] = $course_give;
		$array['course_get'] = $course_get;
		$array['currency_code_give'] = $currency_code_give;
		$array['currency_code_get'] = $currency_code_get;
		$array['currency_id_give'] = $currency_id_give;
		$array['currency_id_get'] = $currency_id_get;
		$array['psys_give'] = $psys_give;
		$array['psys_get'] = $psys_get;
		$array['currency_code_id_give'] = $currency_code_id_give;
		$array['currency_code_id_get'] = $currency_code_id_get;
		$array['psys_id_give'] = $psys_id_give;
		$array['psys_id_get'] = $psys_id_get;
		$array['user_id'] = $user_id;
		$array['user_login'] = is_user(is_isset($ui, 'user_login'));
		$array['user_ip'] = $user_ip;
		$array['first_name'] = is_isset($auto_data, 'first_name');
		$array['last_name'] = is_isset($auto_data, 'last_name');
		$array['second_name'] = is_isset($auto_data, 'second_name');
		$array['user_phone'] = is_isset($auto_data, 'user_phone');
		$array['user_skype'] = is_isset($auto_data, 'user_skype');
		$array['user_email'] = is_isset($auto_data, 'user_email');
		$array['user_passport'] = is_isset($auto_data, 'user_passport');
		$array['user_telegram'] = is_isset($auto_data, 'user_telegram');
		$array['account_give'] = $account1;
		$array['account_get'] = $account2;
		$array['metas'] = serialize($metas);	
		$array['dmetas'] = serialize($dmetas);
		$array['unmetas'] = serialize($unmetas);
		$array['user_discount'] = $cdata['user_discount'];
		$array['user_discount_sum'] = $cdata['user_discount_sum'];		
		$array['exsum'] = $cdata['exsum'];
		$array['sum1'] = $sum1;
		$array['dop_com1'] = $cdata['dop_com1'];
		$array['sum1dc'] = $cdata['sum1dc'];
		$array['com_ps1'] = $cdata['com_ps1'];
		$array['sum1c'] = $sum1c;
		$array['sum1r'] = $cdata['sum1r'];
		$array['sum2t'] = $cdata['sum2t'];
		$array['sum2'] = $sum2;
		$array['dop_com2'] = $cdata['dop_com2'];
		$array['com_ps2'] = $cdata['com_ps2'];
		$array['sum2dc'] = $cdata['sum2dc'];
		$array['sum2c'] = $sum2c;
		$array['sum2r'] = $cdata['sum2r'];
		$array['profit'] = $cdata['profit'];
		$array['user_hash'] = get_user_hash();
		
		$array = apply_filters('array_data_create_bids', $array, $direction, $vd1, $vd2, $cdata, $auto_data, $unmetas);
		$wpdb->insert($wpdb->prefix.'exchange_bids', $array);
		$exchange_id = $wpdb->insert_id;
		if($exchange_id > 0){
			$obmen = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE id='$exchange_id' AND status='auto'");
			if(isset($obmen->id)){ 
				$obmen = apply_filters('change_bidstatus', $obmen, 'auto', 'exchange_button', 'user', 'auto', $direction); 
			
				set_action_bidstatus_new(0, $obmen, $direction, $vd1, $vd2);
			
				$log['url'] = get_safe_url(get_bids_url($hashed));
				$log['status'] = 'success';
				$log['status_text'] = __('Your order successfully created','pn');	
			} else {
				$log['status'] = 1;
				$log['status_text'] = __('Error! System error','pn');				
			}
		} else {
			$log['status'] = 1;
			$log['status_text'] = __('Error! Database error','pn');
		}
	}
	
	$log['error_fields'] = $error_fields;
	$log['error_text'] = $error_text;	
	
	echo json_encode($log);
	exit;
}

function set_action_bidstatus_new($place, $obmen, $direction, $vd1, $vd2){
global $wpdb, $premiumbox;

	$place = intval($place);
	$enable_step2 = intval($premiumbox->get_option('exchange','enable_step2'));
	
	if($place == 0 and $enable_step2 == 0 or $place == 1 and $enable_step2 == 1){
		if($place == 1){
	
			$dir_minmax = get_direction_minmax($direction, $vd1, $vd2, $obmen->course_give, $obmen->course_get);  
			$min1 = is_isset($dir_minmax, 'min_give');
			$max1 = is_isset($dir_minmax, 'max_give');
			$min2 = is_isset($dir_minmax, 'min_get');
			$max2 = is_isset($dir_minmax, 'max_get');
					
			$sum1 = pn_strip_input($obmen->sum1);
			$sum2 = pn_strip_input($obmen->sum2);
									
			if($sum1 > $max1 and is_numeric($max1) or $sum2 > $max2 and is_numeric($max2)){
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! Not enough reserve for the exchange','pn');
				echo json_encode($log);
				exit;													
			}
			
		}
		
		add_pn_cookie('cache_sum', 0);			
		
		$datetime = current_time('mysql');
		$array = array();
		$array['create_date'] = $datetime;
		$array['edit_date'] = $datetime;
		$array['status'] = 'new';
		
		$array = apply_filters('array_data_bids_new', $array, $obmen);
		
		$wpdb->update($wpdb->prefix.'exchange_bids', $array, array('id'=>$obmen->id));
		
		$old_status = $obmen->status;
		$obmen = pn_object_replace($obmen, $array);
		$obmen = apply_filters('change_bidstatus', $obmen, 'new', 'exchange_button', 'user', $old_status, $direction);	 
	} 
}	

/* bids add */
add_action('premium_siteaction_createbids', 'def_premium_siteaction_createbids');
function def_premium_siteaction_createbids(){
global $wpdb, $premiumbox;	
	
	header('Content-Type: application/json; charset=utf-8'); 
	
	$log = array();
	$log['status'] = '';
	$log['response'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');		

	$premiumbox->up_mode('post');
	
	$check_rule = intval(is_param_post('check_rule'));
	if(!$check_rule){
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! You have not accepted the terms and conditions of the User Agreement','pn');
		echo json_encode($log);
		exit;		
	}	
	
	$log = apply_filters('before_ajax_form_field', $log, 'createbids');
	$log = apply_filters('before_ajax_createbids', $log);
	
	$hashed = is_bid_hash(is_param_post('hash'));
	
	if(!$hashed){
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! System error','pn');
		echo json_encode($log);
		exit;		
	}
	
	$obmen = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE hashed='$hashed' AND status='auto'");
	if(!isset($obmen->id)){
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! System error','pn');
		echo json_encode($log);
		exit;		
	}
	
	if(!is_true_userhash($obmen)){
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! You cannot control this order in another browser','pn');
		echo json_encode($log);
		exit;		
	}	
	
	$currency_id_give = intval($obmen->currency_id_give);
	$currency_id_get = intval($obmen->currency_id_get);
	
	$vd1 = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE auto_status='1' AND id='$currency_id_give' AND currency_status = '1'");
	$vd2 = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."currency WHERE auto_status='1' AND id='$currency_id_get' AND currency_status = '1'");

	if(!isset($vd1->id) or !isset($vd2->id)){
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! System error','pn');
		echo json_encode($log);
		exit;		
	}

	$direction_id = intval($obmen->direction_id);
	
	$direction_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."directions WHERE direction_status='1' AND auto_status='1' AND id='$direction_id'");
	if(!isset($direction_data->id)){
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! The direction do not exist','pn');
		echo json_encode($log);
		exit;		
	}
	
	$direction = array();
	foreach($direction_data as $direction_key => $direction_val){
		$direction[$direction_key] = $direction_val;
	}
	$naps_meta = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."directions_meta WHERE item_id='$direction_id'");
	foreach($naps_meta as $naps_item){
		$direction[$naps_item->meta_key] = $naps_item->meta_value;
	}	
	$direction = (object)$direction; /* вся информация о направлении */		
	
	set_action_bidstatus_new(1, $obmen, $direction, $vd1, $vd2);
	
	$log['url'] = get_safe_url(get_bids_url($obmen->hashed));
	$log['status'] = 'success';
	$log['status_text'] = __('Your order successfully created','pn');		
	
	echo json_encode($log);
	exit;
}

/* bids cancel */
add_action('premium_siteaction_canceledbids', 'def_premium_siteaction_canceledbids');
function def_premium_siteaction_canceledbids(){
global $wpdb, $premiumbox;	
	
	$premiumbox->up_mode();

	$hashed = is_bid_hash(is_param_get('hash'));
	if($hashed){
		$bids_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE hashed='$hashed' AND status IN('new','techpay','coldnew')");
		if(isset($bids_data->id)){
			
			do_action('before_bidaction', 'canceledbids', $bids_data);
			do_action('before_bidaction_canceledbids', $bids_data);
			 
			if(is_true_userhash($bids_data)){
				$arr = array('status'=>'cancel','edit_date'=>current_time('mysql'));
				$result = $wpdb->update($wpdb->prefix.'exchange_bids', $arr, array('id'=>$bids_data->id));
				if($result == 1){
					$old_status = $bids_data->status;
					$bids_data = pn_object_replace($bids_data, $arr);
					$bids_data = apply_filters('change_bidstatus', $bids_data, 'cancel', 'exchange_button', 'user', $old_status);
				}
			}
		}
	} 
		$url = get_bids_url($hashed);
		wp_redirect($url);
		exit;
}

/* bids payed */
add_action('premium_siteaction_payedbids', 'def_premium_siteaction_payedbids');
function def_premium_siteaction_payedbids(){ 
global $wpdb, $premiumbox;
	
	$premiumbox->up_mode();
	
	$hashed = is_bid_hash(is_param_get('hash'));
	if($hashed){
		$bids_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE hashed='$hashed' AND status IN('new','techpay')");
		if(isset($bids_data->id)){
			
			do_action('before_bidaction', 'payedbids', $bids_data);
			do_action('before_bidaction_payedbids', $bids_data);
			
			if(is_true_userhash($bids_data)){					
				$direction_id = intval($bids_data->direction_id);
				$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE direction_status='1' AND auto_status='1' AND id='$direction_id'");
				if(isset($direction->id)){
					$m_in = apply_filters('get_merchant_id','', $direction, $bids_data);
					$bids_data = pn_object_replace($bids_data, array('m_in'=>$m_in));
					if(!$m_in){
						$arr = array('status'=>'payed','edit_date'=>current_time('mysql'));
						$result = $wpdb->update($wpdb->prefix.'exchange_bids', $arr, array('id'=>$bids_data->id));
						if($result == 1){
							$old_status = $bids_data->status;
							$bids_data = pn_object_replace($bids_data, $arr);
							$bids_data = apply_filters('change_bidstatus', $bids_data, 'payed', 'exchange_button', 'user', $old_status, $direction);
						}
					}
				}	
			}
		}
	} 
		$url = get_bids_url($hashed);
		wp_redirect($url);
		exit;		
}

/* merchant payed */
add_action('premium_siteaction_payedmerchant', 'def_premium_siteaction_payedmerchant'); 
function def_premium_siteaction_payedmerchant(){
global $wpdb, $premiumbox, $bids_data;	
	
	$premiumbox->up_mode();
	
	$error = 1;
	$hashed = is_bid_hash(is_param_get('hash'));
	if($hashed){
		
		$bids_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE hashed='$hashed' AND status IN('new','techpay','coldpay')");
		if(isset($bids_data->id)){
			
			do_action('before_bidaction', 'payedmerchant', $bids_data);
			do_action('before_bidaction_payedmerchant', $bids_data);
			
			if(is_true_userhash($bids_data)){
				$direction_id = intval($bids_data->direction_id);
				$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE auto_status='1' AND id='$direction_id'");
				if(isset($direction->id)){
					$m_in = apply_filters('get_merchant_id','',$direction, $bids_data);
					$bids_data = pn_object_replace($bids_data, array('m_in'=>$m_in));
					if($m_in){
						
						$error = 0;
						
						$sum_to_pay = apply_filters('sum_to_pay', is_sum($bids_data->sum1dc),$m_in ,$direction, $bids_data);
						
						echo apply_filters('merchant_header', '', $direction); 
						
						$action = apply_filters('merchant_bidaction', '', $m_in, $sum_to_pay, $bids_data, $direction);
						$form = apply_filters('merchant_bidform', '', $m_in, $sum_to_pay, $bids_data, $direction);
							
						if($action){
							echo $action;
							?>
							<script type="text/javascript">
							jQuery(function($){							
								var clipboard = new ClipboardJS('.pn_copy');
								var clipboard2 = new ClipboardJS('.zone_copy');
					
								$('.pn_copy').on('click', function(){
									$(this).addClass('copied');
								});	
								
								$('.zone_copy').on('click', function(){
									$(this).addClass('copied');
									$('.zone_div').removeClass('active');
									$(this).parents('.zone_div').addClass('active');
								});	
								
								$(document).click(function(event) {
									if ($(event.target).closest(".pn_copy").length) return;
									$('.pn_copy').removeClass('copied');
									event.stopPropagation();
								});								
								
								$(document).click(function(event) {
									if ($(event.target).closest(".zone_copy").length) return;
									$('.zone_copy').removeClass('copied').parents('.zone_div').removeClass('active');
									event.stopPropagation();
								});
							});
							</script>
							<?php
						} 

						if($form){
							echo '<div id="goedform" style="display: none;">';
							echo $form;
							echo '</div>';
							echo '<div id="redirect_text" class="success_div" style="display: none;">'. __('Redirecting. Please wait','pn') .'</div>';
							?>
							<script type="text/javascript">
							jQuery(function($){
								document.oncontextmenu=function(e){return false};
								window.history.replaceState(null, null, '<?php echo get_bids_url($hashed); ?>');
								$('#redirect_text').show();
								$('#goedform form').attr('target','_self').submit();
							});
							</script>							
							<?php
						}  	
						
						echo apply_filters('merchant_footer', '', $direction);
						
					}
				}
			}
		} 
	}  
	
	if($error == 1){
		$url = get_bids_url($hashed);
		wp_redirect($url);
		exit;	
	}
} 