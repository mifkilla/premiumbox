<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Captcha for admin panel[:en_US][ru_RU:]Капча для админ панели[:ru_RU]
description: [en_US:]Captcha for admin panel[:en_US][ru_RU:]Капча для админ панели[:ru_RU]
version: 2.2
category: [en_US:]Security[:en_US][ru_RU:]Безопасность[:ru_RU]
cat: secur
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if(!function_exists('all_moduls_active_captchaap')){
	add_action('all_bd_activated', 'all_moduls_active_captchaap');
	add_action('all_moduls_active_'.$name, 'all_moduls_active_captchaap');
	function all_moduls_active_captchaap(){
	global $wpdb;		
	
		$table_name = $wpdb->prefix ."captch_ap";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`createdate` varchar(15) NOT NULL default '0',
			`num1` varchar(10) NOT NULL default '0',
			`num1h` varchar(10) NOT NULL default '0',
			`num2` varchar(10) NOT NULL default '0',
			`num2h` varchar(10) NOT NULL default '0',
			`symbol` varchar(10) NOT NULL default '0',
			`value` varchar(10) NOT NULL default '0',
			`sess_hash` varchar(150) NOT NULL,
			PRIMARY KEY ( `id` ),
			INDEX (`createdate`),
			INDEX (`sess_hash`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
		$wpdb->query($sql);	
	}
}

if(!function_exists('captchaap_generate')){
	function captchaap_generate($word, $title, $size=50) {
		$plugin = get_plugin_class();

		$word = pn_strip_input($word);
		$title = pn_strip_input($title);
		$size = intval($size);
		if($size < 1){ $size = 50; }

		$font = $plugin->plugin_dir . 'moduls/captcha_ap/fonts/font.ttf';

		$url = $plugin->upload_url . 'captcha/';
		$dir = $plugin->upload_dir . 'captcha/';	
		if(!realpath($dir)){
			@mkdir($dir, 0777);
		}
		
		$image_dir = $dir . $title . '.png';
		$image_url = $url . $title . '.png';
		if(file_exists($image_dir)){
			return $image_url;
		}	
		
		$bgs_dir = $plugin->plugin_dir . 'moduls/captcha_ap/bg/';
		$bgs_arr = glob("$bgs_dir*.png");
		if(is_array($bgs_arr)){ 
			shuffle($bgs_arr);
		}
		$bg_to = trim(is_isset($bgs_arr, 0));		
		
		if($im = imagecreatetruecolor($size, $size)){
			$bg_color = imagecolorallocate( $im, 255, 255, 255 );
			$f_color = imagecolorallocate( $im, 0, 0, 0 );
			imagefill($im, 0, 0, $bg_color);
			
			if($bg_to){
				$bg_im = imagecreatefrompng($bg_to);
				imagecopy($im, $bg_im, 0, 0, 0, 0, $size, $size);
			} 

			imagettftext($im, 30, 0, mt_rand(0,30), mt_rand(30,40) , $f_color, $font, $word);

			imagepng($im, $image_dir);
			imagedestroy($im);	
			return $image_url;
		}

		return get_premium_url() . 'images/gd_error.png';
	}
}

if(!function_exists('acp_del_img')){
	function acp_del_img(){	
		global $wpdb;
		$plugin = get_plugin_class();
		if(!$plugin->is_up_mode()){
			$time = current_time('timestamp') - (24*60*60);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."captch_ap WHERE createdate < $time");
			$dir = $plugin->upload_dir . 'captcha/';
			files_del_dir($dir, '.png');
		}
	}
}

if(!function_exists('captchaap_list_cron_func')){
	add_filter('list_cron_func', 'captchaap_list_cron_func');
	function captchaap_list_cron_func($filters){
		$filters['acp_del_img'] = array(
			'title' => __('Removing admin captcha sessions','pn'),
			'site' => '10min',
		);

		return $filters;
	}
}

if(!function_exists('def_premium_action_captchaap_reload')){
	add_action('premium_action_captchaap_reload', 'def_premium_action_captchaap_reload');
	function def_premium_action_captchaap_reload(){
	global $wpdb;

		only_post();
		
		header('Content-Type: application/json; charset=utf-8');
		
		$log = array();
		$log['status'] = 'success';
		$log['status_text'] = '';
		$log['status_code'] = 0;

		$data = captchaap_reload(1);
		$sumbols = array('+','-','x');
		if(isset($data->id)){
			$img1 = captchaap_generate($data->num1, $data->num1h);
			$img2 = captchaap_generate($data->num2, $data->num2h);
			$symb = is_isset($sumbols, $data->symbol);		
		} else {
			$img1 = captchaap_generate(0, 0);
			$img2 = captchaap_generate(0, 0);
			$symb = '+';		
		}

		$log['ncapt1'] = $img1;
		$log['ncapt2'] = $img2;
		$log['nsym'] = $symb;

		echo json_encode($log);	
		exit;
	}
}

if(!function_exists('captchaap_login_footer')){
	add_action('newadminpanel_form_footer', 'captchaap_login_footer');
	function captchaap_login_footer(){
	?>
	<script type="text/javascript">	
	jQuery(function($){	 
		$(document).on('click', '.rlc_reload', function(){
			var param ='have=reload';
			$('.rlc_reload').addClass('active');
			$.ajax({
				type: "POST",
				url: "<?php the_pn_link('captchaap_reload'); ?>",
				dataType: 'json',
				data: param,
				error: function(res,res2,res3){
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},		
				success: function(res)
				{
					if(res['ncapt1']){
						$('.captcha1').attr('src',res['ncapt1']);
					}
					if(res['ncapt2']){
						$('.captcha2').attr('src',res['ncapt2']);
					}	
					if(res['nsym']){
						$('.captcha_sym').html(res['nsym']);
					}
					$('.rlc_reload').removeClass('active');
				}
			});
			return false;
		});	
	});	
	</script>
	<style>
	.rlc_div{
	margin: 0 0 10px 0;
	}
		.rlc_divimg{
		float: left;
		width: 40px!important;
		height: 40px!important;
		border: 1px solid #ddd;
		}
			.rlc_divimg img{
			width: 40px!important;
			height: 40px!important;	
			}
		.rlc_divznak{
		float: left;
		width: 30px;
		height: 40px;
		font: 20px/40px Arial;
		text-align: center;
		}
		input.rlc_divpole{
		float: left;
		width: 60px!important;
		height: 42px!important;
		font-size: 20px!important;
		margin: 0!important;
		text-align: center;
		}
			.rtl_body .rlc_divimg,
			.rtl_body .rlc_divznak,
			.rtl_body input.rlc_divpole{
			float: right;	
			}
		a.rlc_reload{
		float: left;
		margin: 0px 0 0 5px;
		width: 32px;
		height: 40px;
		border-radius: 3px;
		background: url(<?php echo get_premium_url(); ?>images/reload.png) no-repeat center center;
		}
			a.rlc_reload.active{
			background: url(<?php echo get_premium_url(); ?>images/ajax-loader.gif) no-repeat center center;	
			}
		
	.clear{ clear: both; }	
	</style>
		<?php
	}
}

if(!function_exists('ajax_post_form_jsresult_captchaap')){
	add_action('ajax_post_form_jsresult','ajax_post_form_jsresult_captchaap');
	function ajax_post_form_jsresult_captchaap($place=''){
		$place = trim($place); if(!$place){ $place = 'site'; }
		if($place == 'admin'){
	?>
		if(res['ncapt1']){
			$('.captcha1').attr('src',res['ncapt1']);
		}
		if(res['ncapt2']){
			$('.captcha2').attr('src',res['ncapt2']);
		}
		if(res['nsym']){
			$('.captcha_sym').html(res['nsym']);
		}	
	<?php	
		}
	}
} 

if(!function_exists('captchaap_init')){
	add_action('premium_login_init', 'captchaap_init', 1000);
	function captchaap_init(){
		$data = captchaap_reload(1);
	}
}	

if(!function_exists('captchaap_reload')){
	function captchaap_reload($replace=0){
		global $wpdb;
		
		$replace = intval($replace);
		$sess_hash = get_session_id();
		$plugin = get_plugin_class();
		$site_captcha = intval($plugin->get_option('admin_panel_captcha'));
		
		if($replace == 1){
			$data = '';
			$wpdb->query("DELETE FROM ".$wpdb->prefix."captch_ap WHERE sess_hash = '$sess_hash'");
		} else {
			$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."captch_ap WHERE sess_hash = '$sess_hash'");
		}
		
		if(!isset($data->id)){
			$array = array();
			$array['createdate'] = current_time('timestamp');
			$array['sess_hash'] = $sess_hash;
			$array['num1'] = $num1 = mt_rand(5,8);
			$array['num2'] = $num2 = mt_rand(1,9);
			if($site_captcha == 1){
				$array['symbol'] = $symbol = mt_rand(0,2);
			} else {
				$array['symbol'] = $symbol = 0;
			}	
			if($symbol == 1){
				if($num1 < $num2){
					$array['num1'] = $num2;
					$array['num2'] = $num1;
				} elseif($num1 == $num2){
					$array['num1'] = $num1 + mt_rand(1,3);
					$array['num2'] = $num2;						
				}
			}		
			$array['num1h'] = wp_generate_password(8 ,false , false);
			$array['num2h'] = wp_generate_password(8 ,false , false);
			$value = 0;
			if($symbol == 1){
				$value = $array['num1'] - $array['num2'];
			} elseif($symbol == 2){
				$value = $array['num2'] * $array['num1'];
			} else {
				$value = $array['num2'] + $array['num1'];
			}
			$array['value'] = $value;
			$wpdb->insert($wpdb->prefix ."captch_ap", $array);
			$array['id'] = $wpdb->insert_id;
			return (object)$array;
		}	
		
		return $data;	
	}
}

if(!function_exists('captchaap_login_form')){
	add_action('newadminpanel_form', 'captchaap_login_form', 1000);
	function captchaap_login_form(){ 
	global $wpdb;

		$temp = '';

		$data = captchaap_reload(0);
		
		$sumbols = array('+','-','x');
		if(isset($data->id)){
			$num1 = intval($data->num1);
			$num2 = intval($data->num2);
			$img1 = captchaap_generate($data->num1, $data->num1h);
			$img2 = captchaap_generate($data->num2, $data->num2h);
			$symb = is_isset($sumbols, $data->symbol);
		} else {
			$num1 = 0;
			$num2 = 0;		
			$img1 = captchaap_generate(0, 0);
			$img2 = captchaap_generate(0, 0);
			$symb = '+';
		}
		
		$temp = '
		<div class="rlc_div">
			<div class="rlc_divimg">
				<img src="'. $img1 .'" class="captcha1" alt="" />
			</div>
			<div class="rlc_divznak">
				<span class="captcha_sym">'. $symb .'</span>
			</div>	
			<div class="rlc_divimg">
				<img src="'. $img2 .'" class="captcha2" alt="" />
			</div>
			<div class="rlc_divznak">
				=
			</div>
			<input type="text" class="rlc_divpole" name="number" maxlength="5" autocomplete="off" value="" />
			<a href="#" class="rlc_reload" title="'. __('replace task','pn') .'" alt="'. __('replace task','pn') .'"></a>
				<div class="clear"></div>
		</div>
		';
		
		echo $temp;
	}
}	

if(!function_exists('captchaap_newadminpanel_ajax_form')){
	add_filter('newadminpanel_ajax_form', 'captchaap_newadminpanel_ajax_form');
	function captchaap_newadminpanel_ajax_form($log){
	global $wpdb;

		$error = 0;

		$number = trim(is_param_post('number'));
		$data = captchaap_reload(0);

		if(!isset($data->id) or $data->value != $number){		
			$error = 1;				
		}
		
		if($error == 1){
			$data = captchaap_reload(1);
			$sumbols = array('+','-','x');
			if(isset($data->id)){
				$img1 = captchaap_generate($data->num1, $data->num1h);
				$img2 = captchaap_generate($data->num2, $data->num2h);
				$symb = is_isset($sumbols, $data->symbol);
			} else {		
				$img1 = captchaap_generate(0, 0);
				$img2 = captchaap_generate(0, 0);
				$symb = '+';
			}
			$log['ncapt1'] = $img1;
			$log['ncapt2'] = $img2;
			$log['nsym'] = $symb;					
			
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('<strong>Error:</strong> You have not entered test number.','pn');	
			echo json_encode($log);
			exit;		
		}
		
		return $log;
	}
}

if(!function_exists('captchaap_premium_auth')){
	add_filter('premium_auth', 'captchaap_premium_auth', 10, 3);
	function captchaap_premium_auth($log, $user, $place='site'){
		if(is_wp_error($user) and $place == 'admin'){
			$data = captchaap_reload(1);
			$sumbols = array('+','-','x');
			if(isset($data->id)){
				$img1 = captchaap_generate($data->num1, $data->num1h);
				$img2 = captchaap_generate($data->num2, $data->num2h);
				$symb = is_isset($sumbols, $data->symbol);
			} else {		
				$img1 = captchaap_generate(0, 0);
				$img2 = captchaap_generate(0, 0);
				$symb = '+';
			}
			$log['ncapt1'] = $img1;
			$log['ncapt2'] = $img2;
			$log['nsym'] = $symb;		
		}
		return $log;
	}
}

if(!function_exists('captchaap_settings_option')){
	add_filter('all_settings_option', 'captchaap_settings_option');
	function captchaap_settings_option($options){
		$plugin = get_plugin_class();
			
		$options[] = array(
			'view' => 'line',
		);	
		$options['admin_panel_captcha'] = array(
			'view' => 'select',
			'title' => __('Admin panel captcha', 'pn'),
			'options' => array('0'=> __('only numbers addition','pn'), '1'=> __('all mathematical actions with numbers','pn')),
			'default' => $plugin->get_option('admin_panel_captcha'),
			'name' => 'admin_panel_captcha',
			'work' => 'int',
		);
			
		return $options;
	}
}

if(!function_exists('captchaap_all_settings_option_post')){
	add_action('all_settings_option_post', 'captchaap_all_settings_option_post');
	function captchaap_all_settings_option_post($data){
		$plugin = get_plugin_class();
		$admin_panel_captcha = intval($data['admin_panel_captcha']);
		$plugin->update_option('admin_panel_captcha','', $admin_panel_captcha);	
	}
}