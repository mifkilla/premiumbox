<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!class_exists('PremiumForm')){
	class PremiumForm {
		
		public $version = "0.6";

		function __construct()
		{

		}
		
		function prepare_attr($atts){
			if(isset($atts['wrap_class'])){ unset($atts['wrap_class']); }
			
			$attr_arr = array();
			foreach($atts as $atts_key => $atts_value){
				$attr_arr[] = $atts_key . '="' . $atts_value . '"';
			}
			
			return join(' ', $attr_arr);
		}
		
		function ml_head($name){
			$langs = get_langs_ml();
			$admin_lang = get_admin_lang();
			
			$html = '
			<div class="premium_title_multi">';
			
				foreach($langs as $key){ 
					$cl = '';
					if($key == $admin_lang){ $cl = 'active'; }
					$html .= '
					<div name="tab_'. $name .'_'. $key .'" class="tab_multi_title '. $cl .'">
						<div class="tab_multi_flag" title="'. get_title_forkey($key) .'"><img src="'. get_lang_icon($key) .'" alt="'. get_title_forkey($key) .'" /></div>
					</div>
					';
				}
					
			$html .= '
				<div class="clear_multi_title" title="'. __('Clear field','premium') .'"></div>
					<div class="premium_clear"></div>
			</div>
			';			
			
			return $html;
		}		
		
		function substrate($text=''){
			echo $this->get_substrate($text);
		}  		
		
		function get_substrate($text=''){
			$temp = '
			<div class="premium_substrate">
				'. $text .'
			</div>';
			
			return $temp;
		}
		
		function select_search($name='', $options = array(), $default='', $atts = array(), $option_data = array()){
			echo $this->get_select_search($name, $options, $default, $atts, $option_data);
		}	

		function get_select_search($name='', $options = array(), $default='', $atts = array(), $option_data = array()){
			$temp = '';
			$name = pn_string($name);
			$options = (array)$options;
			$default = pn_string($default);
			$atts = (array)$atts;
			$option_data = (array)$option_data;
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }
			$wrap_class .= ' js_select_search_wrap';
			
			if(!isset($atts['id'])){ $atts['id'] = 'pn_'. $name; }
			if(!isset($atts['autocomplete'])){ $atts['autocomplete'] = 'off'; }
			if(!isset($atts['name'])){ $atts['name'] = $name; }
			
			$temp .= '<div class="'. $wrap_class .'">';
			$temp .= '<select '. $this->prepare_attr($atts) .'>';
				foreach($options as $option_key => $option_value){
					$opt_data = is_isset($option_data, $option_key);
					$temp .= '<option value="'. $option_key .'" '. selected($default, $option_key, false) .' '. $opt_data .'>'. pn_strip_input($option_value) .'</option>';
				}
			$temp .= '</select><input type="search" name="" class="js_select_search premium_input" placeholder="'. __('Search...','premium') .'" value="" />';	
			$temp .= '</div>';		
			
			return $temp;			
		}
		
		function select($name='', $options = array(), $default='', $atts = array(), $option_data = array()){
			echo $this->get_select($name, $options, $default, $atts, $option_data);
		}	

		function get_select($name='', $options = array(), $default='', $atts = array(), $option_data = array()){
			$temp = '';
			$name = pn_string($name);
			$options = (array)$options;
			$default = pn_string($default);
			$atts = (array)$atts;
			$option_data = (array)$option_data;
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }
			
			if(!isset($atts['id'])){ $atts['id'] = 'pn_'. $name; }
			if(!isset($atts['autocomplete'])){ $atts['autocomplete'] = 'off'; }
			if(!isset($atts['name'])){ $atts['name'] = $name; }
			
			$temp .= '<div class="'. $wrap_class .'">';
			$temp .= '<select '. $this->prepare_attr($atts) .'>';
				foreach($options as $option_key => $option_value){
					$opt_data = is_isset($option_data, $option_key);
					$temp .= '<option value="'. $option_key .'" '. selected($default, $option_key, false) .' '. $opt_data .'>'. pn_strip_input($option_value) .'</option>';
				}
			$temp .= '</select>';	
			$temp .= '</div>';		
			
			return $temp;
		}

		function colorpicker($name='', $default, $atts = array()){
			echo $this->get_colorpicker($name, $default, $atts);
		}
		
		function get_colorpicker($name='', $default='', $atts = array()){
			$temp = '';
			$name = pn_string($name);
			$default = pn_string($default);
			$atts = (array)$atts;
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }
			
			if(!isset($atts['id'])){ $atts['id'] = 'pn_'. $name; }
			if(!isset($atts['name'])){ $atts['name'] = $name; }
			if(!isset($atts['type'])){ $atts['type'] = 'text'; }
			if(!isset($atts['autocomplete'])){ $atts['autocomplete'] = 'off'; }
			
			$atts['class'] = is_isset($atts, 'class').' premium_colorpicker_input';
			
			$default = str_replace('#','', $default);
			if(!$default){ $default = 'ffffff'; }
			$default = '#'. $default;
			
			$temp .= '
			<div class="'. $wrap_class .'">
				<div class="premium_colorpicker_wrap">
					<input '. $this->prepare_attr($atts) .' data-strings="Theme Colors2,Standard Colors3,Web Colors4,Theme Colors4,Back to Palette5,History6,No history yet7." value="'. $default .'" />
				</div>
			</div>
			';
			
			return $temp;
		}		

		function uploader($name='', $default='', $atts, $ml=0){
			echo $this->get_uploader($name, $default, $atts, $ml);
		}
		
		function get_uploader($name='', $default='', $atts = array(), $ml=''){
			$temp = '';
			$name = pn_string($name);
			$default = pn_string($default);
			$ml = intval($ml);
			$atts = (array)$atts;
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }
			
			if(function_exists('is_ml') and is_ml() and $ml == 1){
				
				$langs = get_langs_ml();
				$admin_lang = get_admin_lang();
				$temp .= '
				<div class="multi_wrapper">';
				
					$temp .= $this->ml_head($name);	 		
					
					$value_ml = get_value_ml($default);
					foreach($langs as $key){ 
						$cl = '';
						if($key == $admin_lang){ $cl = 'active'; }
						
						$val = '';
						if(isset($value_ml[$key])){
							$val = $value_ml[$key];
						}

						$temp .= '				
						<div class="premium_wrap_multi '. $cl .'" id="tab_'. $name .'_'. $key .'">
							<div class="'. $wrap_class .'">
								<div class="premium_uploader">
									<div class="premium_uploader_top">
										<div class="premium_uploader_img" data-id="pn_'. $name.'_'.$key .'">';
											if($val){ $temp .= '<a href="'. $val .'" target="_blank"><img src="'. $val .'" alt="" /></a>'; } 
										$temp .= '
										</div>
										<div class="premium_uploader_show tgm-open-media" data-id="pn_'. $name.'_'.$key .'"></div>
											<div class="premium_uploader_clear ';
											if($default){ 
												$temp .= 'has_img'; 
											} 
											$temp .= '"></div>
											<div class="premium_clear"></div>
									</div>
									<div class="premium_uploader_input">
										<input type="text" name="'. $name.'_'.$key .'" id="pn_'. $name.'_'.$key .'_value" value="'. pn_strip_input($val) .'" />
									</div>
								';
							$temp .= '	
									<div class="premium_clear"></div>
								</div>
							</div>
						</div>';
					}
					
				$temp .= '</div>';
				
			} else {

				$temp .= '
				<div class="'. $wrap_class .'">
					<div class="premium_uploader">
						<div class="premium_uploader_top">
							<div class="premium_uploader_img" data-id="pn_'. $name .'">'; 
								if($default){ $temp .= '<a href="'. $default .'" target="_blank"><img src="'. $default .'" alt="" /></a>'; } 
							$temp .= '
							</div>		
							<div class="premium_uploader_show tgm-open-media" data-id="pn_'. $name .'"></div>
							<div class="premium_uploader_clear ';
								if($default){ 
									$temp .= 'has_img'; 
								} 
							$temp .= '"></div>
							<div class="premium_clear"></div>
								<div class="premium_clear"></div>
						</div>
						<div class="premium_uploader_input">
							<input type="text" name="'. $name .'" id="pn_'. $name .'_value" value="'. pn_strip_input($default) .'" />
						</div>
					';
					$temp .= '
						</div>
				</div>
				';
				
			}	
			
			return $temp;
		}
		
		function hidden_input($name='', $default='', $atts = array()){
			echo $this->get_hidden_input($name, $default, $atts);
		}
		
		function get_hidden_input($name='', $default='', $atts = array()){
			
			if(isset($atts['type'])){ unset($atts['type']); }
			$atts['type'] = 'hidden';
			if(!isset($atts['name'])){ $atts['name'] = $name; }	
			if(!isset($atts['value'])){ $atts['value'] = pn_strip_input($default); }	

			$temp = '<input '. $this->prepare_attr($atts) .' />';
			return $temp;
		}							
		
		function textarea($name='', $default='', $rows='10', $atts = array(), $ml=0, $word_count=0){
			echo $this->get_textarea($name, $default, $rows, $atts, $ml, $word_count);
		}		
		
		function get_textarea($name='', $default='', $rows='10', $atts = array(), $ml=0, $word_count=0){
			$temp = '';
			$name = pn_string($name);
			$default = pn_string($default);
			$ml = intval($ml);
			$atts = (array)$atts;
			$rows = intval($rows); if($rows < 1){ $rows = 5; }
			$height = $rows * 15;
			$word_count = intval($word_count);
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }
			
			$wr_cl = '';
			if($word_count == 1){
				$wr_cl = 'pn_word_count';
			}

			if(function_exists('is_ml') and is_ml() and $ml == 1){
				$langs = get_langs_ml();
				$admin_lang = get_admin_lang();	
				
				$temp .= '
				<div class="multi_wrapper">';
				
					$temp .= $this->ml_head($name);
				
					$value_ml = get_value_ml($default);
					foreach($langs as $key){
						$cl = '';
						if($key == $admin_lang){ $cl = 'active'; }
						
						$val = '';
						if(isset($value_ml[$key])){
							$val = $value_ml[$key];
						}	
						
						$temp .= '
						<div class="premium_wrap_multi '. $cl .'" id="tab_'. $name .'_'. $key .'">
							<div class="'. $wrap_class .'">';
								$temp .= '<div class="premium_editor '. $wr_cl .'">';
									$temp .= '<textarea name="'. $name .'_'. $key .'" class="premium_editor_textarea" style="height: '. $height .'px;">'. pn_strip_text($val) .'</textarea>';
									
								$temp .= '
									<div class="premium_editor_data">
										<div class="premium_editor_words">'. __('Count words','premium') .': <span>0</span></div>
										<div class="premium_editor_symb">'. __('Count symbols','premium') .': <span>0</span></div>
											<div class="premium_clear"></div>
									</div>								
								</div>';
							$temp .= '	
							</div>
						</div>
						';
					}
				
				$temp .= '	
				</div>';	
			} else { 
				$default = ctv_ml($default);
				$temp .= '<div class="'. $wrap_class .'">';	
					$temp .= '<div class="premium_editor '. $wr_cl .'">';
						$temp .= '<textarea name="'. $name .'" class="premium_editor_textarea" id="pn_'. $name .'" style="height: '. $height .'px;">'. pn_strip_text($default) .'</textarea>';
						
					$temp .= '
						<div class="premium_editor_data">
							<div class="premium_editor_words">'. __('Count words','premium') .': <span>0</span></div>
							<div class="premium_editor_symb">'. __('Count symbols','premium') .': <span>0</span></div>
								<div class="premium_clear"></div>
						</div>
					</div>';
				$temp .= '</div>';		
			} 			
			
			return $temp;
		}		
		
		function editor($name='', $default='', $rows='10', $atts = array(), $tags='', $standart_tags=0, $media=0, $ml=0, $word_count=0){
			echo $this->get_editor($name, $default, $rows, $atts, $tags, $standart_tags, $media, $ml, $word_count);
		}		
		
		function get_editor($name='', $default='', $rows='10', $atts = array(), $editor_tags='', $standart_tags=0, $media=0, $ml=0, $word_count=0){
			$temp = '';
			
			$name = pn_string($name);
			$default = pn_string($default);
			$ml = intval($ml);
			$atts = (array)$atts;
			$media = intval($media);
			$standart_tags = intval($standart_tags);
			$word_count = intval($word_count);
			$rows = intval($rows); if($rows < 1){ $rows = 1; }
			$height = $rows * 15;
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }

			$now_page = is_param_get('page');

			$tags = array();
			if($standart_tags){
				$tags = apply_filters('pn_tags', $tags, $now_page, $name);
			}
			if($now_page){
				$tags = apply_filters('pn_tags_page'. $now_page, $tags);
			}	
			if(is_array($editor_tags)){
				$tags = array_merge($tags, $editor_tags);
			}
			
			$wr_cl = '';
			if($word_count == 1){
				$wr_cl = 'pn_word_count';
			}			
			
			if(function_exists('is_ml') and is_ml() and $ml == 1){
				
				$langs = get_langs_ml();
				$admin_lang = get_admin_lang();	
				
				$temp .= '
				<div class="multi_wrapper">';
				
					$temp .= $this->ml_head($name);
				
					$value_ml = get_value_ml($default);
					foreach($langs as $key){
						$cl = '';
						if($key == $admin_lang){ $cl = 'active'; }
						
						$val = '';
						if(isset($value_ml[$key])){
							$val = $value_ml[$key];
						}	
						
						$temp .= '
						<div class="premium_wrap_multi '. $cl .'" id="tab_'. $name .'_'. $key .'">
							<div class="'. $wrap_class .'">';
							
								$temp .= '<div class="premium_editor '. $wr_cl .'">';

								if(is_array($tags) and count($tags) > 0){
									$temp .= '
									<div class="premium_editor_tags">';
										foreach($tags as $tag){
											$title = is_isset($tag, 'title');
											$start = trim(is_isset($tag, 'start'));
											$end = trim(is_isset($tag, 'end'));
											$temp .= '<div class="premium_editor_tag js_editor_tag"><textarea class="premium_editor_tag_start">'. $start .'</textarea><textarea class="premium_editor_tag_end">'. $end .'</textarea><span class="premium_editor_opentag">/</span>'. $title .'</div>';
										}
									$temp .= '	
										<div class="premium_clear"></div>
									</div>
									';
								}
								
								$temp .= '<textarea name="'. $name .'_'. $key .'" class="premium_editor_textarea" style="height: '. $height .'px;">'. pn_strip_text($val) .'</textarea>';
								
								$temp .= '
									<div class="premium_editor_data">
										<div class="premium_editor_words">'. __('Count words','premium') .': <span>0</span></div>
										<div class="premium_editor_symb">'. __('Count symbols','premium') .': <span>0</span></div>
											<div class="premium_clear"></div>
									</div>
								</div>';
								
							$temp .= '	
							</div>
						</div>
						';
					}
				
				$temp .= '	
				</div>';
				
			} else { 
				$default = ctv_ml($default);
				$temp .= '<div class="'. $wrap_class .'">';
				$temp .= '<div class="premium_editor '. $wr_cl .'">';
				
					if(is_array($tags) and count($tags) > 0){
						$temp .= '
						<div class="premium_editor_tags">';
							$temp .= '
							<div class="premium_editor_tags">';
								foreach($tags as $tag){
									$title = is_isset($tag, 'title');
									$start = trim(is_isset($tag, 'start'));
									$end = trim(is_isset($tag, 'end'));
									$temp .= '<div class="premium_editor_tag js_editor_tag"><textarea class="premium_editor_tag_start">'. $start .'</textarea><textarea class="premium_editor_tag_end">'. $end .'</textarea><span class="premium_editor_opentag">/</span>'. $title .'</div>';
								}
							$temp .= '	
								<div class="premium_clear"></div>
							</div>
							';																		
						$temp .= '	
							<div class="premium_clear"></div>
						</div>
						';
					}
					
					$temp .= '<textarea name="'. $name .'" class="premium_editor_textarea" id="pn_'. $name .'" style="height: '. $height .'px;">'. pn_strip_text($default) .'</textarea>';
					
				$temp .= '
				<div class="premium_editor_data">
					<div class="premium_editor_words">'. __('Count words','premium') .': <span>0</span></div>
					<div class="premium_editor_symb">'. __('Count symbols','premium') .': <span>0</span></div>
						<div class="premium_clear"></div>
				</div>	
					</div>';
				$temp .= '</div>';		
			} 			
			
			return $temp;
		}	
		
		function input_password($name='', $default='', $atts = array(), $ml=0){
			echo $this->get_input_password($name, $default, $atts, $ml);
		}	

		function get_input_password($name='', $default='', $atts = array(), $ml=0){
			$temp = '';
			
			$name = pn_string($name);
			$default = pn_string($default);
			$ml = intval($ml);
			$atts = (array)$atts;
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }
			
			if(isset($atts['class'])){
				$atts['class'] .= ' premium_input';
			} else {
				$atts['class'] = 'premium_input';
			}

			if(!isset($atts['id'])){ $atts['id'] = 'pn_'. $name; }
			if(!isset($atts['name'])){ $atts['name'] = $name; }
			if(!isset($atts['value'])){ $atts['value'] = pn_strip_input($default); }
			if(!isset($atts['type'])){ $atts['type'] = 'text'; }
				
			$temp .= '<div class="'. $wrap_class .'">';
				$temp .= '
				<div class="input_password_wrap">
					<input '. $this->prepare_attr($atts) .' />
					<div class="input_password_generate"></div>
					<div class="premium_clear"></div>
				</div>';
			$temp .= '</div>';											
			
			return $temp;
		}		
		
		function input($name='', $default='', $atts = array(), $ml=0){
			echo $this->get_input($name, $default, $atts, $ml);
		}	

		function get_input($name='', $default='', $atts = array(), $ml=0){
			$temp = '';
			
			$name = pn_string($name);
			$default = pn_string($default);
			$ml = intval($ml);
			$atts = (array)$atts;
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }
			
			if(isset($atts['class'])){
				$atts['class'] .= ' premium_input';
			} else {
				$atts['class'] = 'premium_input';
			}
			if(!isset($atts['type'])){ $atts['type'] = 'text'; }
			
			if(function_exists('is_ml') and is_ml() and $ml == 1){
				if(isset($atts['id'])){ unset($atts['id']); }
				
				$langs = get_langs_ml();
				$admin_lang = get_admin_lang();
				
				$temp .= '
				<div class="multi_wrapper">';
				
					$temp .= $this->ml_head($name);
				
					$value_ml = get_value_ml($default);
					foreach($langs as $key){ 
						$cl = '';
						if($key == $admin_lang){ $cl = 'active'; }	
						$val = '';
						if(isset($value_ml[$key])){
							$val = $value_ml[$key];
						}
						
						if(!isset($atts['name'])){ $atts['name'] = $name .'_'. $key; }
						if(!isset($atts['value'])){ $atts['value'] = pn_strip_input($val); }
						
						$temp .= '			
						<div class="premium_wrap_multi '. $cl .'" id="tab_'. $name .'_'. $key .'">
							<div class="'. $wrap_class .'">
								<input '. $this->prepare_attr($atts) .' />
							</div>		
						</div>
						';
						
						unset($atts['name']);
						unset($atts['value']);
					} 				
							
				$temp .= '</div>';	

			} else {
				if(!isset($atts['id'])){ $atts['id'] = 'pn_'. $name; }
				if(!isset($atts['name'])){ $atts['name'] = $name; }
				if(!isset($atts['value'])){ $atts['value'] = pn_strip_input($default); }
				
				$temp .= '<div class="'. $wrap_class .'">';
				$temp .= '<input '. $this->prepare_attr($atts) .' />';
				$temp .= '</div>';											
			}
			
			return $temp;
		}		
		
		function datetime_input($name='', $default='', $atts = array()){
			echo $this->get_datetime_input($name, $default, $atts);
		}
		
		function get_datetime_input($name='', $default='', $atts = array()){
			$temp = '';
			$name = pn_string($name);
			$default = pn_string($default);
			$atts = (array)$atts;
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }
			
			if(isset($atts['class'])){
				$atts['class'] .= ' pn_datetimepicker premium_input big_input';
			} else {
				$atts['class'] = 'pn_datetimepicker premium_input big_input';
			}			
			
			if(!isset($atts['id'])){ $atts['id'] = 'pn_'. $name; }
			if(!isset($atts['type'])){ $atts['type'] = 'text'; }
			if(!isset($atts['name'])){ $atts['name'] = $name; }
			if(!isset($atts['autocomplete'])){ $atts['autocomplete'] = 'off'; }
			
			if($default){
				$dforv = get_pn_time($default,'d.m.Y H:i');
			} else {
				$dforv = date('d.m.Y H:i',current_time('timestamp'));
			}	

			if(!isset($atts['value'])){ $atts['value'] = pn_strip_input($dforv); }
			
			$temp .= '<div class="'. $wrap_class .'">';
			$temp .= '<input '. $this->prepare_attr($atts) .' />';
			$temp .= '</div>';			
		
			return $temp;	
		}

		function date_input($name='', $default='', $atts = array()){
			echo $this->get_date_input($name, $default, $atts);
		}
		
		function get_date_input($name='', $default='', $atts = array()){
			$temp = '';
			$name = pn_string($name);
			$default = pn_string($default);
			$atts = (array)$atts;
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }
			
			if(isset($atts['class'])){
				$atts['class'] .= ' pn_datepicker premium_input big_input';
			} else {
				$atts['class'] = 'pn_datepicker premium_input big_input';
			}			
			
			if(!isset($atts['id'])){ $atts['id'] = 'pn_'. $name; }
			if(!isset($atts['type'])){ $atts['type'] = 'text'; }
			if(!isset($atts['name'])){ $atts['name'] = $name; }
			if(!isset($atts['autocomplete'])){ $atts['autocomplete'] = 'off'; }
			
			if($default){
				$dforv = get_pn_date($default,'d.m.Y');
			} else {
				$dforv = date('d.m.Y', current_time('timestamp'));
			}			
			
			if(!isset($atts['value'])){ $atts['value'] = pn_strip_input($dforv); }
			
			$temp .= '<div class="'. $wrap_class .'">';
			$temp .= '<input '. $this->prepare_attr($atts) .' />';
			$temp .= '</div>';			
		
			return $temp;	
		}
		
		function time_input($name='', $default='', $atts = array()){
			echo $this->get_time_input($name, $default, $atts);
		}
		
		function get_time_input($name='', $default='', $atts = array()){
			$temp = '';
			$name = pn_string($name);
			$default = pn_string($default);
			$atts = (array)$atts;
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }
			
			if(isset($atts['class'])){
				$atts['class'] .= ' pn_timepicker premium_input big_input';
			} else {
				$atts['class'] = 'pn_timepicker premium_input big_input';
			}			
			
			if(!isset($atts['id'])){ $atts['id'] = 'pn_'. $name; }
			if(!isset($atts['type'])){ $atts['type'] = 'text'; }
			if(!isset($atts['name'])){ $atts['name'] = $name; }
			
			if($default){
				$dforv = get_pn_date($default,'H:i');
			} else {
				$dforv = date('H:i', current_time('timestamp'));
			}		

			if(!isset($atts['value'])){ $atts['value'] = pn_strip_input($dforv); }
			
			$temp .= '<div class="'. $wrap_class .'">';
			$temp .= '<input '. $this->prepare_attr($atts) .' />';
			$temp .= '</div>';			
		
			return $temp;	
		}		

		function checkbox($name='', $text='', $value='', $default='', $atts = array()){
			echo $this->get_checkbox($name, $text, $value, $default, $atts);
		}
		
		function get_checkbox($name='', $text='', $value='', $default='', $atts = array()){
			$temp = '';
			$name = pn_string($name);
			$default = pn_string($default);
			$atts = (array)$atts;
			
			$wrap_class = trim(is_isset($atts, 'wrap_class'));
			if(!$wrap_class){ $wrap_class = 'premium_wrap_standart'; }			
			
			if(!isset($atts['id'])){ $atts['id'] = 'pn_'. $name; }
			
			$checked = '';
			if(is_array($default)){
				if(in_array($value, $default)){
					$atts['checked'] = 'checked';
				}		
			} else {
				if($default == $value){
					$atts['checked'] = 'checked';
				}		
			}
									
			if(!isset($atts['type'])){ $atts['type'] = 'checkbox'; }
			if(!isset($atts['name'])){ $atts['name'] = $name; }
			if(!isset($atts['value'])){ $atts['value'] = $value; }
									
			$temp .= '<div class="'. $wrap_class .'">';
			$temp .= '<label><input '. $this->prepare_attr($atts) .' />'. $text .'</label>';
			$temp .= '</div>';			
		
			return $temp;	
		}		
		
		function help($title, $content=''){
			echo $this->get_help($title, $content);
		}
		
		function get_help($title, $content=''){
			$temp = '
			<div class="premium_wrap_help">
				<div class="premium_helptitle"><span>'. $title .'</span></div>
				<div class="premium_helpcontent">'. $content .'</div>
			</div>
			';		
			return $temp;
		}
		
		function warning($content=''){
			echo $this->get_warning($content);
		}
		
		function get_warning($content=''){
			$temp = '
			<div class="premium_wrap_warning">'. $content .'<div class="premium_clear"></div></div>
			';		
			return $temp;
		}

		function textfield($content='', $atts=array()){
			echo $this->get_textfield($content, $atts);
		}
		
		function get_textfield($content='', $atts=array()){
			$temp = '
			<div class="premium_wrap_standart">'. $content .'<div class="premium_clear"></div></div>
			';		
			return $temp;
		}		

		function h3($title='', $submit=''){
			echo $this->get_h3($title, $submit);	
		}	

		function get_h3($title='', $submit=''){	
			$temp = '<div class="premium_h3_wrap">';			
			$temp .= '<div class="premium_h3">'. $title .'</div>';
			if($submit){
				$temp .= '<div class="premium_h3submit"><input type="submit" formtarget="_top" name="" class="premium_button" value="'. pn_strip_input($submit) .'" /></div>';
			}			
			$temp .= '<div class="premium_clear"></div></div>';			
			return $temp;
		}	

		function line(){
			echo $this->get_line();		
		}
		
		function get_line(){
			$temp = '';
			$temp .= '<div class="premium_line"></div>';
			return $temp;
		}		
		
 		function wp_editor($name, $default, $rows, $media, $ml=0){
			$ml = intval($ml);
			if(function_exists('is_ml') and is_ml() and $ml == 1){
 				$langs = get_langs_ml();
				$admin_lang = get_admin_lang();
				?>	
				<div class="multi_wrapper">
					<?php echo $this->ml_head($name); ?>		
					<?php 
					$value_ml = get_value_ml($default);
					foreach($langs as $key){ 
						$cl = '';
						if($key == $admin_lang){ $cl = 'active'; }
									
						$val = '';
						if(isset($value_ml[$key])){
							$val = $value_ml[$key];
						}
						?>				
						<div class="premium_wrap_multi <?php echo $cl; ?>" id="tab_<?php echo $name;?>_<?php echo $key; ?>">
							<div class="premium_wrap_standart">
												
								<?php 		
								$settings['wpautop'] = true;
								$settings['media_buttons'] = $media;
								$settings['teeny'] = true;
								$settings['tinymce'] = true;
								$settings['textarea_rows'] = $rows;
								wp_editor(pn_strip_text($val), $name.'_'.$key ,$settings); 
								?>								

							</div>	
						</div>
					<?php } ?>
				</div>				
			<?php  
			} else {
				$default = pn_strip_text(ctv_ml($default));

				echo '<div class="premium_wrap_standart">';
			
				$settings = array();
				$settings['wpautop'] = true;
				$settings['media_buttons'] = $media;
				$settings['teeny'] = true;
				$settings['tinymce'] = true;
				$settings['textarea_rows'] = $rows;
				wp_editor($default,$name,$settings); 	
			
				echo '</div>';		
			}

		}

 		function back_menu($back_menu, $data){
			$page = pn_strip_input(is_param_get('page'));
			$back_menu = apply_filters('pn_admin_back_menu_'.$page, $back_menu, $data);
			$back_menu = (array)$back_menu;
			
			$html = '
			<div class="premium_backmenu">';
			
				foreach($back_menu as $item){ 
					$opt_data = is_isset($item, 'atts');
					$html .= '
					<a href="'. is_isset($item,'link') .'" '. $opt_data .'>'. is_isset($item,'title') .'</a>
					';
				} 
				
			$html .= '
					<div class="premium_clear"></div>
			</div>';	
			echo $html;
		}	
		
		function select_box($place, $selects, $title=''){
			$html = '
			<div class="premium_selectbox">
				'. $title .' &rarr;
						
				<select name="" onchange="location = this.options[this.selectedIndex].value;" autocomplete="off">
					'; 
					foreach($selects as $item){ 
						$opt_data = is_isset($item,'opt_data');
						$html .= '
						<option value="'. is_isset($item,'link') .'" '. selected(is_isset($item,'default'), $place, false) .' '. $opt_data .'>'. is_isset($item,'title') .'</option>
						';
					} 
					$html .= '
				</select>				
			</div>';	
			echo $html;
		} 

		function selects_box($place, $place2, $selects, $selects2, $title=''){
			$html = '
			<div class="premium_selectbox">
				'. $title .' 
						
				<select name="" onchange="location = this.options[this.selectedIndex].value;" autocomplete="off">
					'; 
					if(is_array($selects)){
						foreach($selects as $item){ 
							$opt_data = is_isset($item,'opt_data');
							$html .= '
							<option value="'. is_isset($item,'link') .'" '. selected(is_isset($item,'default'), $place, false) .' '. $opt_data .'>'. is_isset($item,'title') .'</option>
							';
						} 
					}
					$html .= '
				</select>
				&rarr;
				<select name="" onchange="location = this.options[this.selectedIndex].value;" autocomplete="off">
					'; 
					if(is_array($selects2)){
						foreach($selects2 as $item){ 
							$opt_data = is_isset($item,'opt_data');
							$html .= '
							<option value="'. is_isset($item,'link') .'" '. selected(is_isset($item,'default'), $place2, false) .' '. $opt_data .'>'. is_isset($item,'title') .'</option>
							';
						} 
					}
					$html .= '
				</select>				
			</div>';	
			echo $html;
		}  		
		
		function error_form($text, $signal='', $back_url=''){
			$back_url = trim($back_url);
			$signal = trim($signal);
			if(!$signal){ $signal = 'error'; }
			$form_method = trim(is_param_post('form_method'));
			if($form_method == 'ajax'){
				$log = array();
				$log['status'] = 'error';
				$log['status_code'] = '1'; 
				$log['status_text']= $text;
				if($back_url){
					$log['url']= get_safe_url($back_url); 
				}
				echo json_encode($log);
				exit;				
			} else {
				pn_display_mess($text, $text, $signal);				
			}			
		}		
		
		function send_header(){
			$form_method = trim(is_param_post('form_method'));
			if($form_method == 'ajax'){
				header('Content-Type: application/json; charset=utf-8');			
			} else {
				header('Content-Type: text/html; charset=utf-8');				
			}
		}		
		
		function answer_form($back_url){
			$form_method = trim(is_param_post('form_method'));
			if($form_method == 'ajax'){
				$log = array();
				$log['status'] = 'success';
				$log['status_code'] = '0'; 
				$log['status_text'] = '';
				$log['url']= get_safe_url($back_url); 
				echo json_encode($log);
				exit;				
			} else {
				wp_redirect(get_safe_url($back_url));
				exit;				
			}
		}
		
  		function init_form_js(){
		global $init_page_form;	
			$init_page_form = intval($init_page_form);
			$init_page_form++;

 			if($init_page_form == 1){
 			?>
			<script type="text/javascript">
			jQuery(function($){
				$('.admin_ajax_form').ajaxForm({
					dataType:  'json',
					beforeSubmit: function(a,f,o) {
						f.addClass('thisactive');
						$('.thisactive').find('.premium_ajax_loader').show();
						$('#premium_ajax').show();
					},
					error: function(res, res2, res3) {
						<?php do_action('pn_js_error_response', 'form'); ?>
					},
					success: function(res) {
						if(res['status'] == 'error'){
							if(res['status_text']){
								$('#premium_reply_wrap').html('<div class="premium_reply pn_error js_reply_wrap"><div class="premium_reply_close js_reply_close"></div>'+ res['status_text'] +'</div>');
								var ftop = $('#premium_reply_wrap').offset().top - 100;
								$('body,html').animate({scrollTop: ftop},500);
							}
						}			
						if(res['status'] == 'success'){
							if(res['status_text']){
								$('#premium_reply_wrap').html('<div class="premium_reply pn_success js_reply_wrap"><div class="premium_reply_close js_reply_close"></div>'+ res['status_text'] +'</div>');
							}
						}	

						<?php do_action('admin_ajax_form_jsresult'); ?>
						
						if(res['url']){
							window.location.href = res['url']; 
						} else {
							$('.thisactive').find('.premium_ajax_loader').hide();
							$('#premium_ajax').hide();
						}
						$('.thisactive').removeClass('thisactive');
					}
				});	
			});	 		
			</script>
			<?php 
			} 			
		}
		
  		function init_tabform_js(){
		global $init_page_tabform;	
			$init_page_tabform = intval($init_page_tabform);
			$init_page_tabform++;

 			if($init_page_tabform == 1){
 			?>
			<script type="text/javascript">
			jQuery(function($){
				
				function tabs_show(form_id, id){
					Cookies.set("current_tab_"+form_id, id);
					$('#tabform_'+form_id).find('.one_tabs_menu').removeClass('current');
					$('#tabform_'+form_id).find('.one_tabs_body').hide();
					$('#tabform_'+form_id).find('.one_tabs_menu[data-id='+id+']').addClass('current');
					$('#tabform_'+form_id).find('.add_tabs_select option[data-id='+id+']').prop('selected', true);
					$('#tabform_'+form_id).find('.one_tabs_body[data-id='+id+']').show();		
				}
				
				$(document).on('change', '.add_tabs_select select', function(){
					var form_id = $(this).parents('.js_tabform_wrap').attr('id').replace('tabform_','');
					var id = $(this).find('option:selected').attr('data-id');
					tabs_show(form_id, id);
				});
				
				$(document).on('click', '.one_tabs_menu', function(){ 
					var form_id = $(this).parents('.js_tabform_wrap').attr('id').replace('tabform_','');
					var id = $(this).attr('data-id');
					tabs_show(form_id, id);	
					return false;
				});				
				
			});	 		
			</script>
			<?php 
			} 			
		}		
		
  		function init_tab_form($params=array()){

			$method = trim(is_isset($params, 'method'));
			$target = trim(is_isset($params, 'target'));
			$form_target = '';
			if($target == 'blank'){
				$form_target = 'target="_blank"';
			}
			$form_link = trim(is_isset($params, 'form_link'));		

			$tabs = is_isset($params, 'tabs');

			$button_title = trim(is_isset($params, 'button_title'));
			if(!$button_title){ $button_title = __('Save','premium'); }
			
			$m = 'post';
			if($method == 'get'){
				$m = 'get';
			}
			
			if(!$form_link){ $form_link = pn_link('', $m); }			
			
			$form_m = 'post';
			$form_class = '';
			
			if($method == 'get'){
				$form_m = 'get';				
			}
			if($method == 'ajax'){
				$form_class = 'admin_ajax_form';				
			}	
			$key = is_isset($params, 'key');
			
			$hidden_data = is_isset($params, 'hidden_data');
 			?>
			<div class="premium_body_wrap">
				<form method="<?php echo $form_m; ?>" class="<?php echo $form_class; ?>" <?php echo $form_target; ?> action="<?php echo $form_link; ?>">
					<div class="premium_ajax_loader"></div>
					<input type="hidden" name="form_method" value="<?php echo $method; ?>" />

					<?php 
					if(is_array($hidden_data)){ 
						foreach($hidden_data as $hd_key => $hd_value){ 
					?>
						<input type="hidden" name="<?php echo $hd_key; ?>" value="<?php echo $hd_value; ?>" />
					<?php
						}
					}
					?>
					<?php wp_referer_field(); ?>
					
					<div class="premium_body js_tabform_wrap" id="tabform_<?php echo $key; ?>">
						<div class="premium_standart_div">
							<div class="add_tabs_pagetitle"><?php echo is_isset($params, 'page_title'); ?></div>
							<?php
							$current_tab = pn_strip_input(get_pn_cookie('current_tab_' . $key)); 
							?>
							
							<?php 
							if(is_array($tabs)){ 
							?>
							<div class="add_tabs_wrap">
								
								<div class="add_tabs_select">
									<select name="" autocomplete="off">
									<?php $rs=0; foreach($tabs as $tab_key => $tab_title){ $rs++; ?>
										<option <?php if(!$current_tab and $rs == 1 or $current_tab == $tab_key){ ?>selected="selected"<?php } ?> data-id="<?php echo $tab_key; ?>" value=""><?php echo strip_tags($tab_title); ?></div>
									<?php } ?>
									</select>
								</div>
								
								<div class="add_tabs_menu">
									<?php $rs=0; foreach($tabs as $tab_key => $tab_title){ $rs++; ?>
										<div class="one_tabs_menu <?php if(!$current_tab and $rs == 1 or $current_tab == $tab_key){ ?>current<?php } ?>" data-id="<?php echo $tab_key; ?>"><?php echo $tab_title; ?></div>
									<?php } ?>
								</div>			
								
								<div class="add_tabs_body">
							
									<?php $rs = 0; foreach($tabs as $tab_key => $tab_title){ $rs++; ?>
										<div class="one_tabs_body" <?php if(!$current_tab and $rs == 1 or $current_tab == $tab_key){ ?>style="display: block;"<?php } ?> data-id="<?php echo $tab_key; ?>">
											<div class="add_tabs_div">
											
												<?php do_action($key . '_' . $tab_key, is_isset($params, 'data'), is_isset($params, 'data_id')); ?>
												
												<div class="add_tabs_line">
													<div class="add_tabs_submit">
														<input type="submit" name="" class="button" value="<?php echo $button_title; ?>" />
													</div>
												</div>

											</div>
										</div>
									<?php } ?>
								
								</div>
									<div class="premium_clear"></div>
							</div>
							<?php } ?>							
							
						</div>
					</div>
				</form>
			</div>
			<?php   
			
			$this->init_form_js();
			$this->init_tabform_js();
		}		
		
  		function init_form($params=array(), $options=''){
			
			$filter = trim(is_isset($params, 'filter'));
			$method = trim(is_isset($params, 'method'));
			$target = trim(is_isset($params, 'target'));
			$form_target = '';
			if($target == 'blank'){
				$form_target = 'target="_blank"';
			}
			$form_link = trim(is_isset($params, 'form_link'));
			$data = is_isset($params, 'data');		

			$button_title = trim(is_isset($params, 'button_title'));
			if(!$button_title){ $button_title = __('Save','premium'); }
			
			$m = 'post';
			if($method == 'get'){
				$m = 'get';
			}
			
			if(!$form_link){ $form_link = pn_link('', $m); }

			if(!is_array($options)){
				$options = array();
			}
			if($filter){
				$options = apply_filters($filter, $options, $data);
			}			

			$options['bottom_title'] = array(
				'view' => 'h3',
				'title' => '',
				'submit' => $button_title,
			);			
			
			$form_m = 'post';
			$form_class = '';
			
			if($method == 'get'){
				$form_m = 'get';				
			}
			if($method == 'ajax'){
				$form_class = 'admin_ajax_form';				
			}			
 			?>
			<div class="premium_body_wrap">
				<form method="<?php echo $form_m; ?>" class="<?php echo $form_class; ?>" <?php echo $form_target; ?> action="<?php echo $form_link; ?>">
					<div class="premium_ajax_loader"></div>
					<input type="hidden" name="form_method" value="<?php echo $method; ?>" />
					<?php wp_referer_field(); ?>

					<div class="premium_body">
						<div class="premium_standart_div">
							<?php $this->form_prepare_options($options); ?>
						</div>
					</div>
				</form>
			</div>
			<?php 
			$this->init_form_js();
		} 	 
		
 		function form_prepare_options($options){
			$options = (array)$options;
			foreach($options as $option){
				$view = trim(is_isset($option,'view'));
				$title = trim(is_isset($option,'title'));
				$name = trim(is_isset($option,'name'));
				$default = is_isset($option,'default');
				$class = trim(is_isset($option,'class'));
				$media = intval(is_isset($option,'media'));
				$rows = intval(is_isset($option,'rows'));
				$ml = intval(is_isset($option,'ml'));
				
				if($view == 'h3'){
					$submit = trim(is_isset($option,'submit'));
					$this->h3($title, $submit);
				} elseif($view == 'clear_table'){
					$html = '
					</div>				
				</div>
				<div class="premium_body">
					<div class="premium_standart_div">';			
					echo $html;
				} elseif($view == 'user_func'){
					$func = trim(is_isset($option,'func'));
					$func_data = is_isset($option,'func_data');
					if($func){
						call_user_func($func, $func_data);
					}
				} elseif($view == 'hidden_input'){
					$this->hidden_input($name, $default);					
				} elseif($view == 'line'){
					echo '<div class="premium_standart_line '. $class .'">';
					$this->line();
					echo '</div>';
				} elseif($view == 'help'){
					echo '<div class="premium_standart_line '. $class .'">';
					$this->help($title, $default);
					echo '</div>';
				} elseif($view == 'warning'){
					echo '<div class="premium_standart_line '. $class .'">';
					$this->warning($default);
					echo '</div>';
				} elseif($view == 'wp_editor'){
					echo '<div class="premium_standart_line '. $class .'">';			
					echo '<div class="premium_stline_left">';
					if($title){
						echo '<div class="premium_stline_left_ins">'; 
							echo '<label class="js_line_label" data-for="'. $name .'">'. $title .'</label>';
						echo '</div>';
					}						
					echo '</div>';	
					echo '<div class="premium_standart_line '. $class .'">';
					echo '<div class="premium_stline_right" id="pnline_'. $name .'"><div class="premium_stline_right_ins">';
					$this->wp_editor($name, $default, $rows, $media, $ml);
					echo '</div>';					
					echo '<div class="premium_clear"></div></div></div>';
					echo '<div class="premium_clear"></div></div>';
				} else {
					$temp = '
					<div class="premium_standart_line '. $class .'">';			
					$temp .= '<div class="premium_stline_left">';
					if($title){
						$temp .= '<div class="premium_stline_left_ins">'; 
							$temp .= '<label class="js_line_label" data-for="'. $name .'">'. $title .'</label>';
						$temp .= '</div>';
					}						
					$temp .= '</div>';					
					$temp .= '<div class="premium_stline_right" id="pnline_'. $name .'"><div class="premium_stline_right_ins">';
						
						$add_options = is_isset($option,'add_options'); if(!is_array($add_options)){ $add_options = array(); }
						$d_cl = 0;
						if(count($add_options) > 0){
							$d_cl = 1;
						}
						
						$temp .= $this->set_form_prepare_options($option, $d_cl);
						
						global $wrap_ind;
						$wrap_ind = 0;
						foreach($add_options as $add_option){ $wrap_ind++;
							$temp .= $this->set_form_prepare_options($add_option, 1);
						}

					$temp .= '<div class="premium_clear"></div></div></div>';
					$temp .= '
						<div class="premium_clear"></div>
					</div>';
					echo $temp;
				}
			}
		}
		
		function set_form_prepare_options($option, $wrap=0){
			$view = trim(is_isset($option,'view'));
			$title = trim(is_isset($option,'title'));
			$name = trim(is_isset($option,'name'));
			$default = is_isset($option,'default');
			$media = trim(is_isset($option,'media'));
			$rows = intval(is_isset($option,'rows'));
			$ml = intval(is_isset($option,'ml'));
			$atts = is_isset($option,'atts');
			
			$temp = '';
			if($wrap == 1){
				global $wrap_ind;
				$temp .= '<div class="premium_stline_once"><div class="premium_stline_title st'. $wrap_ind .'">'. $title .'</div>';
			}
			
			if(!is_array($atts)){
				$atts = array();
			}
				
				if($view == 'input'){ /**/
					$temp .= $this->get_input($name, $default, $atts, $ml); 
				} elseif($view == 'input_password'){ /**/
					$atts['autocomplete'] = 'off';
					$temp .= $this->get_input_password($name, $default, $atts, $ml);					
				} elseif($view == 'inputbig'){ /**/
					if(isset($atts['class'])){
						$atts['class'] .= 'big_input';
					} else {
						$atts['class'] = 'big_input';
					}
					$temp .= $this->get_input($name, $default, $atts, $ml);	
				} elseif($view == 'select'){ /**/
					$sel_options = is_isset($option,'options');	
					$temp .= $this->get_select($name, $sel_options, $default, $atts);						
				} elseif($view == 'uploader'){ /**/
					$temp .= $this->get_uploader($name, $default, $atts, $ml);
				} elseif($view == 'colorpicker'){ /**/
					$temp .= $this->get_colorpicker($name, $default, $atts);					
				} elseif($view == 'textarea'){ /**/
					$word_count = intval(is_isset($option,'word_count'));
					$temp .= $this->get_textarea($name, $default, $rows, $atts, $ml, $word_count);
				} elseif($view == 'editor'){ /**/
					$word_count = intval(is_isset($option,'word_count'));
					$tags = is_isset($option,'tags');
					$standart_tags = intval(is_isset($option,'standart_tags'));
					$temp .= $this->get_editor($name, $default, $rows, $atts, $tags, $standart_tags, $media, $ml, $word_count);						
				} elseif($view == 'datetime'){ /**/
					$atts['autocomplete'] = 'off';
					$temp .= $this->get_datetime_input($name, $default, $atts);
				} elseif($view == 'date'){ /**/
					$atts['autocomplete'] = 'off';
					$temp .= $this->get_date_input($name, $default, $atts);
				} elseif($view == 'time'){ /**/
					$atts['autocomplete'] = 'off';
					$temp .= $this->get_time_input($name, $default, $atts);					
				} elseif($view == 'select_search'){ /**/
					$sel_options = is_isset($option,'options');
					$temp .= $this->get_select_search($name, $sel_options, $default, $atts);						
				} elseif($view == 'textfield'){	 /**/
					$temp .= $this->get_textfield($default, $atts);
				} elseif($view == 'checkbox'){	 /**/
					$second_title = is_isset($option,'second_title');
					$value = is_isset($option,'value');
					$temp .= $this->get_checkbox($name, $second_title, $value, $default, $atts);
				}			
			
			if($wrap == 1){
				$temp .= '</div>';
			}
			
			return $temp;
		}
		
		function strip_options($filter, $method='post', $options=''){
			$new = array();
			$filter = trim($filter);
			if(!is_array($options)){
				$options = array();
			}	
			if($filter){
				$options = apply_filters($filter, $options, '');
			}	
			foreach($options as $option){
				$name = trim(is_isset($option,'name'));
				$work = trim(is_isset($option,'work'));
				$ml = intval(is_isset($option,'ml'));
				if($name and $work){
					if($ml and function_exists('is_ml') and is_ml()){
						if($method == 'post'){
							$val = is_param_post_ml($name);
						} else {
							$val = is_param_get_ml($name);
						}
					} else {
						if($method == 'post'){
							$val = is_param_post($name);
						} else {
							$val = is_param_get($name);
						}						
					}		
					if($work == 'int'){
						$new[$name] = intval($val);
					} elseif($work == 'none'){
						$new[$name] = $val;						
					} elseif($work == 'input'){
						$new[$name] = pn_strip_input($val);
					} elseif($work == 'sum'){
						$new[$name] = is_sum($val);	
					} elseif($work == 'percent'){
						$percent = 0;
						if(strstr($val, '%')){
							$percent = 1;
						}
						$val = str_replace('%','',$val);
						$val = is_sum($val);
						if($percent == 1){
							$val .= '%';
						}
						$new[$name] = $val;	
					} elseif($work == 'text'){
						$new[$name] = pn_strip_text($val);					
					} elseif($work == 'email'){
						$new[$name] = is_email($val);					
					} elseif($work == 'input_array'){
						$new[$name] = pn_strip_input_array($val);
					} elseif($work == 'symbols'){
						$new[$name] = pn_strip_symbols($val);					
					}
				}
			}	
			return $new;
		}

		function sort_js($trigger, $link){
			?>
			<script type="text/javascript">
			jQuery(function($){									   
				$("<?php echo $trigger; ?>").sortable({ 
					opacity: 0.6, 
					cursor: 'move',
					revert: true,
					update: function() {
						$('#premium_ajax').show();
						
						var order = $(this).sortable("serialize"); 
						$.post("<?php echo $link; ?>", order, function(theResponse){
							$('#premium_ajax').hide();
						}); 															 
					}	 				
				});
			});	
			</script>			
			<?php
		} 

 		function get_sort_ul($items, $num){
			$html = '';
			
			if(isset($items[$num]) and is_array($items[$num])){
				if(count($items[$num]) > 0){
			
					$html .= '
					<ul>';

					foreach($items[$num] as $item){ 
						$item_id = is_isset($item,'id');
						$html .= '
						<li id="number_'. is_isset($item,'number') .'">
							<div class="premium_sort_block">'. is_isset($item,'title') .'</div>
								<div class="premium_clear"></div> 
								'. $this->get_sort_ul($items, $item_id) .' 					
						</li>		
						';
					} 
				
					$html .= '
					</ul>';
				}
			} 
			
			return $html;
		}		
		
 		function sort_one_screen($items, $title=''){
			$title = trim($title);
			if(!$title){ $title = __('Put in the correct order','premium'); }
			
			$html = '
			<div class="premium_sort_wrap">
				<div class="premium_sort_title">'. $title .'</div> 
				<div class="premium_sort thesort">
				'. $this->get_sort_ul($items,0) .'
				</div>
					<div class="premium_clear"></div>
			</div>';
			
			echo $html;
		} 		
	}
}