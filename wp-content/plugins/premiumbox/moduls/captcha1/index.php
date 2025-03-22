<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Captcha for website[:en_US][ru_RU:]Капча для сайта[:ru_RU]
description: [en_US:]Captcha for website[:en_US][ru_RU:]Капча для сайта[:ru_RU]
version: 2.2
category: [en_US:]Security[:en_US][ru_RU:]Безопасность[:ru_RU]
cat: secur
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if(!function_exists('all_moduls_active_captcha')){
	add_action('all_bd_activated', 'all_moduls_active_captcha');
	add_action('all_moduls_active_'.$name, 'all_moduls_active_captcha');
	function all_moduls_active_captcha(){
	global $wpdb;	

		$table_name = $wpdb->prefix ."captch_site";
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

if(!function_exists('captcha_generate')){
	function captcha_generate($word, $title, $size=50){
		$plugin = get_plugin_class();

		$word = pn_strip_input($word);
		$title = pn_strip_input($title);
		$size = intval($size);
		if($size < 1){ $size = 50; }

		$font = $plugin->plugin_dir . 'moduls/captcha/fonts/font.ttf';

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
		
		$bgs_dir = $plugin->plugin_dir . 'moduls/captcha/bg/';
		$bgs_arr = glob("$bgs_dir*.png");
		if(is_array($bgs_arr)){ 
			shuffle($bgs_arr);
		}
		$bg_to = trim(is_isset($bgs_arr, 0));		
		
		if($im = imagecreatetruecolor($size, $size)){
			
			$bg_color = apply_filters('pn_sc_bgcolor', array('255','255','255'));
			$bg_color = imagecolorallocate( $im, $bg_color[0], $bg_color[1], $bg_color[2]);
			
			$f_color = apply_filters('pn_sc_color', array('0','0','0'));
			$f_color = imagecolorallocate($im, $f_color[0], $f_color[1], $f_color[2]);
			
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

if(!function_exists('captcha_del_img')){
	function captcha_del_img($sess_hash=''){	
	global $wpdb;
		$plugin = get_plugin_class();
		if(!$plugin->is_up_mode()){
			
			$del_ims = array();
 			$time = current_time('timestamp') - (24*60*60);
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."captch_site WHERE createdate < $time OR sess_hash = '$sess_hash'");
			foreach($items as $item){
				$del_ims[] = $item->num1h;
				$del_ims[] = $item->num2h;
			}
			
			$wpdb->query("DELETE FROM ".$wpdb->prefix."captch_site WHERE createdate < $time OR sess_hash = '$sess_hash'");
			
			$dir = $plugin->upload_dir . 'captcha/';
			
			foreach($del_ims as $im_title){
				$file = $dir . $im_title . '.png';
				if(file_exists($file)){
					@unlink($file);
				}
			} 
			
		}
	}
}

if(!function_exists('captcha_list_cron_func')){
	add_filter('list_cron_func', 'captcha_list_cron_func');
	function captcha_list_cron_func($filters){
	global $wpdb;	
		$filters['captcha_del_img'] = array(
			'title' => __('Removing captcha sessions','pn'),
			'site' => '10min',
		);
		return $filters;
	}
}

if(!function_exists('ajax_post_form_jsresult_captcha')){
	add_action('ajax_post_form_jsresult','ajax_post_form_jsresult_captcha');
	function ajax_post_form_jsresult_captcha($place=''){
		$place = trim($place);
		if(!$place){ $place = 'site'; }
		if($place == 'site'){
	?>
		if(res['ncapt1']){
			$('.captcha1').attr('src',res['ncapt1']);
		}
		if(res['ncapt2']){
			$('.captcha2').attr('src',res['ncapt2']);
		}
		if(res['nsymb']){
			$('.captcha_sym').html(res['nsymb']);
		}	
	<?php	
		}
	}
}

if(!function_exists('premium_js_captcha')){
	add_action('premium_js','premium_js_captcha');
	function premium_js_captcha(){
	?>
	jQuery(function($){ 
		$(document).on('click', '.captcha_reload', function(){
			var thet = $(this);
			thet.addClass('act');
			var param ='have=reload';
			$.ajax({
				type: "POST",
				url: "<?php echo get_pn_action('captcha_reload'); ?>",
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
					if(res['nsymb']){
						$('.captcha_sym').html(res['nsymb']);
					}			
					
					thet.removeClass('act');
				}
			});
			
			return false;
		});
	});	
	<?php	
	} 
} 

if(!function_exists('captcha_reload')){
	function captcha_reload($replace=0){
		global $wpdb;
		
		$replace = intval($replace);
		$sess_hash = get_session_id();
		$plugin = get_plugin_class();
		$site_captcha = intval($plugin->get_option('site_captcha'));
		
		if($replace == 1){
			$data = '';
			captcha_del_img($sess_hash);
		} else {
			$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."captch_site WHERE sess_hash = '$sess_hash'");
			
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
			$wpdb->insert($wpdb->prefix ."captch_site", $array);
			$array['id'] = $wpdb->insert_id;
			return (object)$array;
		}	
		
		return $data;	
	}
}

if(!function_exists('captcha_init')){
	add_action('init','captcha_init', 9);
	function captcha_init(){
		if(!is_admin()){
			global $pn_captcha;
			$pn_captcha = captcha_reload(0); 			
		}
	}
}	

if(!function_exists('def_premium_siteaction_captcha_reload')){
	add_action('premium_siteaction_captcha_reload', 'def_premium_siteaction_captcha_reload');
	function def_premium_siteaction_captcha_reload(){	
		$plugin = get_plugin_class();

		only_post();
		
		header('Content-Type: application/json; charset=utf-8');
		
		$log = array();
		$log['status'] = 'success';
		$log['status_text'] = '';
		$log['status_code'] = 0;
		
		$plugin->up_mode('post');
		
		$data = captcha_reload(1);
		$sumbols = array('+','-','x');
		if(isset($data->id)){
			$img1 = captcha_generate($data->num1, $data->num1h);
			$img2 = captcha_generate($data->num2, $data->num2h);
			$symb = is_isset($sumbols, $data->symbol);		
		} else {
			$img1 = captcha_generate(0, 0);
			$img2 = captcha_generate(0, 0);
			$symb = '+';		
		}
		
		$log['ncapt1'] = $img1;
		$log['ncapt2'] = $img2;
		$log['nsymb'] = $symb;	

		echo json_encode($log);
		exit;
	}
}

if(!function_exists('captcha_premium_auth')){
	add_filter('premium_auth', 'captcha_premium_auth', 10, 3);
	function captcha_premium_auth($log, $user, $place='site'){
		if($place == 'site'){
			$data = captcha_reload(1);
			$sumbols = array('+','-','x');
			if(isset($data->id)){
				$img1 = captcha_generate($data->num1, $data->num1h);
				$img2 = captcha_generate($data->num2, $data->num2h);
				$symb = is_isset($sumbols, $data->symbol);
			} else {		
				$img1 = captcha_generate(0, 0);
				$img2 = captcha_generate(0, 0);
				$symb = '+';
			}
			$log['ncapt1'] = $img1;
			$log['ncapt2'] = $img2;
			$log['nsymb'] = $symb;
		}
		return $log;
	}
}

if(!function_exists('get_form_filelds_captcha')){
	add_filter('get_form_filelds','get_form_filelds_captcha', 1000, 2);
	function get_form_filelds_captcha($items, $name){
		$plugin = get_plugin_class();	
		if($plugin->get_option('captcha',$name) == 1){
			$items['captcha'] = array(
				'type' => 'captcha',
			);
		}
		return $items;
	}
}

if(!function_exists('form_field_line_captcha')){
	add_filter('form_field_line','form_field_line_captcha', 10, 3);
	function form_field_line_captcha($line, $filter, $data){
	global $wpdb, $pn_captcha;	
		
		$type = trim(is_isset($data, 'type'));
		if($type == 'captcha'){
			$data = $pn_captcha;
			$sumbols = array('+','-','x');
			if(isset($data->id)){
				$img1 = captcha_generate($data->num1, $data->num1h);
				$img2 = captcha_generate($data->num2, $data->num2h);
				$symb = is_isset($sumbols, $data->symbol);		
			} else {
				$img1 = captcha_generate(0, 0);
				$img2 = captcha_generate(0, 0);
				$symb = '+';		
			}		
		
			$line = get_captcha_temp($img1,$img2, $symb);	
		}
		
		return $line;
	}
}

if(!function_exists('comment_form_captcha')){
	add_action('comment_form', 'comment_form_captcha', 1000);
	function comment_form_captcha(){
	global $pn_captcha;	
		$plugin = get_plugin_class();
		if($plugin->get_option('captcha', 'commentform') == 1){
			$data = $pn_captcha;
			$sumbols = array('+','-','x');
			if(isset($data->id)){
				$img1 = captcha_generate($data->num1, $data->num1h);
				$img2 = captcha_generate($data->num2, $data->num2h);
				$symb = is_isset($sumbols, $data->symbol);		
			} else {
				$img1 = captcha_generate(0, 0);
				$img2 = captcha_generate(0, 0);
				$symb = '+';		
			}		
		
			$line = get_captcha_temp($img1,$img2, $symb);
			echo $line;
		}	
	}
}

if(!function_exists('get_captcha_temp')){
	function get_captcha_temp($img1,$img2,$symbol=''){
		$symbol = trim($symbol);
		if(!$symbol){ $symbol = '+'; }
		
		$temp = '
		<div class="captcha_div">
			<div class="captcha_title">
				'. __('Type your answer','pn') .'
			</div>
			<div class="captcha_body">
				<div class="captcha_divimg">
					<img src="'. $img1 .'" class="captcha1" alt="" />
				</div>
				<div class="captcha_divznak">
					<span class="captcha_sym">'. $symbol .'</span>
				</div>	
				<div class="captcha_divimg">
					<img src="'. $img2 .'" class="captcha2" alt="" />
				</div>
				<div class="captcha_divznak">
					=
				</div>
				<input type="text" class="captcha_divpole" name="number" maxlength="4" autocomplete="off" value="" />
				<a href="#" class="captcha_reload" title="'. __('replace task','pn') .'"></a>
					<div class="clear"></div>
			</div>
		</div>		
		';
		
		$temp = apply_filters('get_captcha_temp', $temp, $img1, $img2, $symbol);
		return $temp;
	}
}

if(!function_exists('before_ajax_form_field_captcha')){
	add_filter('before_ajax_form_field','before_ajax_form_field_captcha', 95, 2);
	function before_ajax_form_field_captcha($logs, $name){
	global $wpdb;
	
		$plugin = get_plugin_class();

		if($plugin->get_option('captcha',$name) == 1){
			
			$number = trim(is_param_post('number'));	
			$data = captcha_reload(0);
			
			$error = 0;
			if(!isset($data->id) or $data->value != $number){		
				$error = 1;				
			}
			
			$new_data = captcha_reload(1);
			$sumbols = array('+','-','x');
			if(isset($new_data->id)){
				$img1 = captcha_generate($new_data->num1, $new_data->num1h);
				$img2 = captcha_generate($new_data->num2, $new_data->num2h);
				$symb = is_isset($sumbols, $new_data->symbol);
			} else {		
				$img1 = captcha_generate(0, 0);
				$img2 = captcha_generate(0, 0);
				$symb = '+';
			}
			
			$logs['ncapt1'] = $img1;
			$logs['ncapt2'] = $img2;
			$logs['nsymb'] = $symb;			
			
			if($error == 1){
				$logs['status'] = 'error';
				$logs['status_code'] = '-3';
				$logs['status_text'] = __('Error! Incorrect verification number entered','pn');
				echo json_encode($logs);
				exit;					
			} 	
		}
		
		return $logs;
	}
}

if(!function_exists('captcha_all_settings_option')){
	add_filter('all_settings_option', 'captcha_all_settings_option');
	function captcha_all_settings_option($options){
		$plugin = get_plugin_class();
			
		$options[] = array(
			'view' => 'line',
		);	
		$options['site_captcha'] = array(
			'view' => 'select',
			'title' => __('Website captcha', 'pn'),
			'options' => array('0'=> __('only numbers addition','pn'), '1'=> __('all mathematical actions with numbers','pn')),
			'default' => $plugin->get_option('site_captcha'),
			'name' => 'site_captcha',
			'work' => 'input',
		);	
			
		return $options;
	}
}

if(!function_exists('captcha_all_settings_option_post')){
	add_action('all_settings_option_post', 'captcha_all_settings_option_post');
	function captcha_all_settings_option_post($data){
		$plugin = get_plugin_class();
		$site_captcha = intval($data['site_captcha']);
		$plugin->update_option('site_captcha','', $site_captcha);
	}
}

$plugin = get_plugin_class();
$plugin->include_patch(__FILE__, 'premiumbox');