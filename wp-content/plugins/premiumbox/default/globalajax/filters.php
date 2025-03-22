<?php
if( !defined( 'ABSPATH')){ exit(); }

$plugin = get_plugin_class();

function get_ga_time($place){
	
	$plugin = get_plugin_class();
	$timer = intval($plugin->get_option('ga', $place.'_time'));
	if($timer < 1){ $timer = 10; }
	
	return $timer;
}

if($plugin->get_option('ga','ga_admin') == 1){
	add_action('admin_footer','globalajax_admin_footer');
	add_action('premium_action_globalajax_admin_check', 'def_premium_action_globalajax_admin_check');
}
function globalajax_admin_footer(){
	$plugin = get_plugin_class();
 	
	$globalajax_admin_timer = get_ga_time('admin') * 1000;
	
	$link = urlencode($_SERVER['REQUEST_URI']);
	$page = pn_strip_input(is_param_get('page'));
	
	$ga_test = 0;
	if($plugin->is_debug_mode() and is_param_get('ga_test') == 1){
		$ga_test = 1;
	}

	$params = array();
	$params['link'] = $link;
	$params['page'] = $page;
	$params = apply_filters('globalajax_admin_data_request', $params , $link, $page);
	$http_params_arr = array();	
	foreach($params as $k_param => $v_param){
		$http_params_arr[] = $k_param . '='. $v_param;
	}
	$http_params = join('&', $http_params_arr);
?>	
<script type="text/javascript">
jQuery(function($){

var auto_load = 1;
function globalajax_timer(){
	if(auto_load == 1){
		auto_load = 0;
		
		var param = '<?php echo $http_params; ?>';
		<?php if($ga_test == 1){ ?>
			console.log(param);
		<?php } ?>
		$('.globalajax_ind').addClass('active');
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('globalajax_admin_check', 'post');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},
			beforeSend: function(res, res2, res3){
				<?php do_action('globalajax_admin_data_before', $link, $page); ?>
			},			
			success: function(res)
			{		
				<?php if($ga_test == 1){ ?>
					console.log(res);
				<?php } ?>
				if(res['status'] == 'success'){
					auto_load = 1;
					<?php do_action('globalajax_admin_data_jsresult', $link, $page); ?>
				} 
				$('.globalajax_ind').removeClass('active');
			}
		});	

	}
}	
	setInterval(globalajax_timer, <?php echo $globalajax_admin_timer; ?>);
	globalajax_timer();
});	
</script>
<?php 
}

function def_premium_action_globalajax_admin_check(){
$plugin = get_plugin_class();	
	
	only_post();
	
	header('Content-Type: application/json; charset=utf-8');
	
	$log = array();
	$log['status'] = 'success';
	$log['status_code'] = 0;
	$log['status_text'] = '';
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	$plugin->up_mode('post');
	
	if(current_user_can('read')){
		$log = apply_filters('globalajax_admin_data', $log, urldecode(is_param_post('link')), is_param_post('page'));
	}
	
	echo json_encode($log);
	exit;
} 

if($plugin->get_option('ga','ga_site') == 1){
	add_action('wp_footer','globalajax_wp_footer');
	add_action('premium_siteaction_globalajax_wp_check', 'def_premium_siteaction_globalajax_wp_check');
}
function globalajax_wp_footer(){
	$plugin = get_plugin_class();

	$globalajax_wp_timer = get_ga_time('site') * 1000;	
	
	$ga_test = 0;
	if($plugin->is_debug_mode() and is_param_get('ga_test') == 1){
		$ga_test = 1;
	}	
	
	$link = urlencode($_SERVER['REQUEST_URI']);
	
	$params = array();
	$params['link'] = $link;
	$params = apply_filters('globalajax_wp_data_request', $params, $link);
	$http_params_arr = array();	
	foreach($params as $k_param => $v_param){
		$http_params_arr[] = $k_param . '='. $v_param;
	}
	$http_params = join('&', $http_params_arr);
?>	
<script type="text/javascript">
jQuery(function($){

var auto_load = 1;
function globalajax_timer(){

	if(auto_load == 1){
		auto_load = 0;
		
		var param = '<?php echo $http_params; ?>';
		<?php if($ga_test == 1){ ?>
			console.log(param);
		<?php } ?>		
		$('.globalajax_ind').addClass('active');
		$.ajax({
			type: "POST",
			url: "<?php echo get_pn_action('globalajax_wp_check');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3){
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},
			beforeSend: function(res, res2, res3){
				<?php do_action('globalajax_wp_data_before', $link); ?>
			},			
			success: function(res)
			{		
				<?php if($ga_test == 1){ ?>
					console.log(res);
				<?php } ?>			
				if(res['status'] == 'success'){
					auto_load = 1;						
					<?php do_action('globalajax_wp_data_jsresult',$link); ?>
				}	
				$('.globalajax_ind').removeClass('active');
			}
		});
	}

}	
	setInterval(globalajax_timer, <?php echo $globalajax_wp_timer; ?>);
	globalajax_timer();
});	
</script>
<?php	
}

function def_premium_siteaction_globalajax_wp_check(){
$plugin = get_plugin_class();
	
	only_post();
	
	header('Content-Type: application/json; charset=utf-8');
	
	$log = array();
	$log['status'] = 'success';
	$log['status_code'] = 0;
	$log['status_text'] = '';

	$plugin->up_mode('post');
	
	$log = apply_filters('globalajax_wp_data', $log, urldecode(is_param_post('link')));
	
	echo json_encode($log);	
	exit;
}