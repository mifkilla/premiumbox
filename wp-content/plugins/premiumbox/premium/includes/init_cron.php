<?php 
if( !defined( 'ABSPATH')){ exit(); }

//* * * */02 *  wget --spider http://site.ru/cron.html > /dev/null

if(!function_exists('get_cron_link')){
	function get_cron_link($action=''){
		$action = trim($action);
		$site_url = get_site_url_or();
		
		$cron_link = $site_url .'/cron';
		if($action){
			$cron_link .= '-' . $action;
		}
		if(defined('PREMIUM_PHYSICAL_FILES')){
			$cron_link .= '.php';
		} else {
			$cron_link .= '.html';
		}
		$cron_link .= get_hash_cron('?');
		
		return $cron_link;
	}
}

if(!function_exists('check_hash_cron')){
	function check_hash_cron(){
		$errors = array();
		if(defined('PN_HASH_CRON')){
			$hash_cron = m_defined('PN_HASH_CRON');
			if($hash_cron){
				$hash = is_param_get('hcron');
				if($hash != $hash_cron){
					$errors[] = 1;
				}
			}
		}		
			if(count($errors) > 0){
				return 0;
			} else {
				return 1;
			}
	}
}

if(!function_exists('get_hash_cron')){
	function get_hash_cron($zn){
		$atts_arr = array();
		if(defined('PN_HASH_CRON')){
			$hash_cron = m_defined('PN_HASH_CRON');
			if($hash_cron){
				$atts_arr[] = 'hcron=' . $hash_cron;
			}
		}		
			if(count($atts_arr) > 0){
				$atts = $zn . join('&', $atts_arr);
			} else {
				$atts =  '';
			}
				return $atts;
	}
}

if(!function_exists('pn_cron_times')){
	function pn_cron_times(){
		$cron_times = array();
		$cron_times['none'] = array(
			'time' => '-1',
			'title' => __('Never','premium'),
		);		
		$cron_times['now'] = array(
			'time' => 0,
			'title' => __('When handling','premium'),
		);
		$cron_times['1min'] = array(
			'time' => (1*60),
			'title' => __('Interval 1 minutes','premium'),
		);		
		$cron_times['2min'] = array(
			'time' => (2*60),
			'title' => __('Interval 2 minutes','premium'),
		);
		$cron_times['5min'] = array(
			'time' => (5*60),
			'title' => __('Interval 5 minutes','premium'),
		);
		$cron_times['10min'] = array(
			'time' => (11*60),
			'title' => __('Interval 10 minutes','premium'),
		);
		$cron_times['15min'] = array(
			'time' => (15*60),
			'title' => __('Interval 15 minutes','premium'),
		);		
		$cron_times['30min'] = array(
			'time' => (31*60),
			'title' => __('Interval 30 minutes','premium'),
		);
		$cron_times['1hour'] = array(
			'time' => (61*60),
			'title' => __('Interval 1 hour','premium'),
		);
		$cron_times['3hour'] = array(
			'time' => (3*60*60),
			'title' => __('Interval 3 hours','premium'),
		);
		$cron_times['05day'] = array(
			'time' => (12*60*60),
			'title' => __('Interval 12 hours','premium'),
		);
		$cron_times['1day'] = array(
			'time' => (24*60*60),
			'title' => __('Interval 24 hours','premium'),
		);		
		$cron_times = apply_filters('cron_times', $cron_times);
		return $cron_times;
	}
}

if(!function_exists('pn_cron_init')){
	function pn_cron_init($place=''){
		$now_time = current_time('timestamp');
		
		$pn_cron = get_option('pn_cron');
		if(!is_array($pn_cron)){ $pn_cron = array(); }
		
		$times = pn_cron_times();
		
		$update_times_all = is_isset($pn_cron, 'update_times');
		$update_times = is_isset($update_times_all, $place);
		
		$go_times = array();
		
		foreach($times as $time_key => $time_data){
			if($time_key != 'none'){
				$timer_plus = intval(is_isset($time_data, 'time'));
				$last_time = intval(is_isset($update_times, $time_key));
				$action_time = $last_time + $timer_plus;
				if($action_time < $now_time){
					$go_times[] = $time_key;
				}
			}	
		}
		
		$actions = array();
		
		$cron_func = apply_filters('list_cron_func', array());
		$cron_func = (array)$cron_func;		
		
		foreach($go_times as $time_key){
			foreach($cron_func as $func_name => $func_data){
				$work_time = trim(is_isset($func_data, $place));
				$allways = intval(is_isset($func_data, 'allways'));
				if(isset($pn_cron[$place][$func_name]['work_time']) and $allways != 1){
					$work_time = trim($pn_cron[$place][$func_name]['work_time']);
				}
				if($work_time == $time_key){
					$actions[] = $func_name;
					$pn_cron[$place][$func_name]['last_update'] = $now_time;
				}
			}
			$pn_cron['update_times'][$place][$time_key] = $now_time;		
		}			
		
		update_option('pn_cron', $pn_cron);

		foreach($actions as $action){
			go_pn_cron_func($action, $place, 0, $cron_func);
		}		
	}
}

if(!function_exists('go_pn_cron_func')){
	function go_pn_cron_func($action='', $place='', $update_time=0, $cron_func=''){
		if($action){
			$funcs = array();
			if(!is_array($cron_func)){
				$cron_func = apply_filters('list_cron_func', array());
				$cron_func = (array)$cron_func;
			}
			foreach($cron_func as $func => $name){
				$funcs[] = $func;
			}
			if(in_array($action,$funcs)){ 
			
				if($update_time == 1){
					$pn_cron = get_option('pn_cron');
					if(!is_array($pn_cron)){ $pn_cron = array(); }
					$pn_cron[$place][$action]['last_update'] = current_time('timestamp');
					update_option('pn_cron', $pn_cron);
				}
			
				call_user_func($action);
				
			} else {
				pn_display_mess(__('Error! Invalid command for task scheduler (cron)','premium'), __('Error! Invalid command for task scheduler (cron)','premium'));
			}
		}
	}
}

if(!function_exists('pn_cron_action')){
	function pn_cron_action($action=''){
		if($action){
			go_pn_cron_func($action, 'file', 1);
		} else {
			pn_cron_init('file');
		}
					
		_e('Done','premium');
		exit;
	}
}

if(!function_exists('pn_cron_init_all')){
	add_action('init', 'pn_cron_init_all', 3);
	function pn_cron_init_all(){
		$data = premium_rewrite_data();
		$super_base = $data['super_base'];	
		$matches = '';	
		
		$cron_file = apply_filters('pn_cron_filename', 'mycron.php');
		if(preg_match("/^cron-([a-zA-Z0-9\_]+).html$/", $super_base, $matches ) or $super_base == 'cron.html'){	
			if(check_hash_cron()){				
				header('Content-Type: text/html; charset=utf-8');
				status_header(200);
				
				$action = trim(is_isset($matches,1));
				if(function_exists('pn_cron_action')){
					pn_cron_action($action);
				} else {
					_e('Cron function does not exist','premium');
				}	
			}
		} elseif(!preg_match("/^cron-([a-zA-Z0-9\_]+).php$/", $super_base, $matches ) and $super_base != 'cron.php' and $super_base != $cron_file) {
			pn_cron_init('site');
		}
	}	
}	