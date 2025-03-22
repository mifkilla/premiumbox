<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('Ext_Premium')){
	class Ext_Premium {
		public $name = "";
		public $title = "";
		public $m_data = "";
		public $map = "";
		public $place = '';
		public $plugin = '';
		
		function __construct($file, $title, $place, $plugin)
		{
			$path = get_extension_file($file);
			$name = get_extension_name($path);

			file_safe_include($path . '/class');	

			$map = array();
			$maps = $this->get_map();
			foreach($maps as $map_key => $map_value){
				$map[] = $map_key;
			}
			
			$this->name = trim($name);
			$this->title = $title . ' ('. $this->name .')';
			$this->map = $map;
			$this->m_data = set_extension_data($path . '/dostup/index', $map);
			$this->place = trim($place);
			$this->plugin = $plugin;
			
			add_filter('ext_'. $this->place .'_data', array($this, 'ext_data'), 10, 3);
			add_filter('ext_'. $this->place .'_data_post', array($this, 'ext_data_post'), 10, 3);
			add_action('ext_'. $this->place .'_delete', array($this, 'delete_ext'), 10, 2);
			add_filter($this->place .'_settingtext_' . $this->name, array($this, 'settingtext'), 10, 2);
		}

		function settingtext($text, $id){
			$data = $this->get_file_data($id);
		
			$error = 1;
			$arrs = $this->settings_list();
			if(count($arrs) > 0){
				foreach($arrs as $arr){
					$arr_now = (array)$arr;
					$n_error = 0;
					foreach($arr_now as $arr_key){
						$d = is_isset($data, $arr_key);
						if(strlen($d) < 1 or strstr($d, 'сюда')){
							$n_error = 1;
						}
					}
					if($n_error != 1){
						$error = 0;
					}
				}
			} else {
				$error = 0;
			}				
				
			if($error == 1){	
				$text = '<span class="bred">'. __('Config file is not configured','premium') .'</span>';
			}
			
			return $text;
		}

		public function get_map(){
			return array();
		}

		function settings_list(){
			$arrs = array();
			return $arrs;
		}

		function get_file_data($id){
			$m_data = array();
			$data = array();
			$file = $this->plugin->upload_dir . $this->place . '/' . $id . '.php';
			if(is_file($file)){
				$fdata = get_fdata($file);
				$data = check_array_map($fdata, $this->map);
			} else {
				$data = $this->m_data;
			}
			foreach($data as $data_key => $data_value){
				$m_data[$data_key] = premium_decrypt($data_value);
			}
			
			return $m_data;
		}

		function ext_data($options, $script, $id){
			if($script == $this->name){
				$file_data = $this->get_file_data($id);
				
				$maps = $this->get_map();
				foreach($maps as $map_key => $map_value){
					$view = trim(is_isset($map_value, 'view'));
					$opts = is_isset($map_value, 'options');
					$title = trim(ctv_ml(is_isset($map_value, 'title')));
					$default = '';
					if(isset($file_data[$map_key])){
						if(strlen($file_data[$map_key]) > 0 and !strstr($file_data[$map_key], 'сюда')){
							$default = $file_data[$map_key];
						}
					}
					if($view == 'input'){
						$placeholder = '';
						if(strlen($default) > 0){
							$placeholder = '***' . __('parameter already set','premium') . '***';
						}
						$options['map_' . $map_key] = array(
							'view' => 'inputbig',
							'title' => $title,
							'default' => apply_filters('show_secret_files', '', $default),
							'name' => 'map_' . $map_key,
							'atts' => array('placeholder' => $placeholder, 'autocomplete' => 'off'),
							'work' => 'none',
						);					
					} elseif($view == 'textarea'){
						$placeholder = '';
						if(strlen($default) > 0){
							$placeholder = '***' . __('parameter already set','premium') . '***';
						}
						$options['map_' . $map_key] = array(
							'view' => 'textarea',
							'title' => $title,
							'default' => apply_filters('show_secret_files', '', $default),
							'name' => 'map_' . $map_key,
							'atts' => array('placeholder' => $placeholder, 'autocomplete' => 'off'),
							'rows' => '10',
							'work' => 'none',
						);	
					} else {
						$options['map_' . $map_key] = array(
							'view' => 'select',
							'title' => $title,
							'options' => $opts,
							'default' => $default,
							'name' => 'map_' . $map_key,
							'work' => 'none',
						);					
					}
				}	
			}
			
			return $options;
		}

		function ext_data_post($ind, $script, $id){	
			if($ind != 1 and $script == $this->name){
				$posts = array();

				$file_data = $this->get_file_data($id);
					
				$maps = $this->get_map();
				foreach($maps as $map_key => $map_value){
					$value = is_param_post('map_' . $map_key);
					if(strlen($value) < 1){
						$value = is_isset($file_data, $map_key); 
						if(strstr($value, 'сюда')){
							$value = '';
						}
					}
					$posts[$map_key] = premium_encrypt($value);
				}		
					
				return update_fdata($this->place, $id, $posts);
			}
				return $ind;
		}

		function get_ids($name='', $script=''){
			$ids = array();
			$script = trim($script);
			$name = trim($name);
			if($script){
				$list = get_option('extlist_' . $name);
				if(!is_array($list)){ $list = array(); }
				foreach($list as $list_k => $list_v){
					$mscr = trim(is_isset($list_v, 'script'));
					$status = intval(is_isset($list_v, 'status'));
					if($mscr and $mscr == $script and $status == 1){
						$ids[] = $list_k;
					}
				}
			} 	
			return $ids;
		}

		function delete_ext($script, $id){
			$file = $this->plugin->upload_dir . $this->place . '/' . $id . '.php';
			if(is_file($file)){
				@unlink($file);
			}	
			$merch_data = get_option($this->place . '_data');
			if(!is_array($merch_data)){ $merch_data = array(); }
			if(isset($merch_data[$id])){
				unset($merch_data[$id]);
			}
			update_option($this->place .'_data', $merch_data);
		}		
	}
}