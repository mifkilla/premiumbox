<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('widgets_init', 'parsers_register_widgets');
function parsers_register_widgets(){
	class pn_parsers_Widget extends WP_Widget { 
		
		public function __construct($id_base = false, $widget_options = array(), $control_options = array()){
			parent::__construct('get_pn_parsers', __('Rates parser','pn'), $widget_options = array(), $control_options = array());
		}
		
		public function widget($args, $instance){
			extract($args);

			global $wpdb;	
			
			if(is_ml()){
				$lang = get_locale();
				$title = pn_strip_input(is_isset($instance,'title'.$lang));
			} else {
				$title = pn_strip_input(is_isset($instance,'title'));	
			}
			if(!$title){ $title = __('Rates parser','pn'); }
			

			$html = '';
			$r=0;
			
			$cbr = is_isset($instance,'cbr');
			if(!is_array($cbr)){ $cbr = array(); }
			
			$date = __('No','pn');
			$time_parser = get_option('time_new_parser');
			if($time_parser){
				$date_format = get_option('date_format');
				$time_format = get_option('time_format');
				$date = date("{$date_format}, {$time_format}",$time_parser);
			}		
			
			$curs_parser = get_parser_pairs();
			$parsers = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."parser_pairs ORDER BY menu_order ASC");
			 
			foreach($parsers as $item){
				if(in_array($item->id,$cbr)){ $r++;
									
					if($r%2 == 0){
						$oddeven = 'even';
					} else {
						$oddeven = 'odd';
					}			
							
					$p_title1 = str_replace('[copy]', '',pn_strip_input($item->title_pair_give));
					$p_title2 = str_replace('[copy]', '',pn_strip_input($item->title_pair_get));
							
					$p_birg = str_replace('[copy]', '',pn_strip_input($item->title_birg));
									
					$curs1 = is_out_sum(get_parser_course($item->pair_give, $curs_parser), 12 ,'course');
					$curs2 = is_out_sum(get_parser_course($item->pair_get, $curs_parser), 12 ,'course');
							
					$temp_html = '
					<div class="widget_cbr_line '. $oddeven .'">
								
						<div class="widget_cbr_left">
							<div class="widget_cbr_title">'. $p_title1 .'/'. $p_title2 .'</div>
							<div class="widget_cbr_birg">'. $p_birg .'</div>
						</div>
						<div class="widget_cbr_curs">
							<div class="widget_cbr_onecurs">'. $curs1 .' '. $p_title1 .'</div>
							<div class="widget_cbr_onecurs">'. $curs2 .' '. $p_title2 .'</div>
						</div>

							<div class="clear"></div>
					</div>		
					';
							
					$html .= apply_filters('parser_widget_one', $temp_html, $item->id, $r, $p_title1, $p_title2, $p_birg, $curs1, $curs2, $item);
						
				}
			}
			
			$widget = '
				<div class="widget_cbr_div">
					<div class="widget_cbr_div_ins">
						<div class="widget_cbr_div_title">
							<div class="widget_cbr_div_title_ins">
								'. $title .'
							</div>
						</div>
						
						'. $html .'
						
						<div class="cbr_update">
							'. __('Update time','pn') .': '. $date .'
						</div>
						
					</div>
				</div>
			';		
			
			$widget = apply_filters('cbr_widget_block', $widget, $title, $html, $time_parser);
			echo $widget;
		
		}


		public function form($instance){ 
		?>
			<?php if(is_ml()){ 
				$langs = get_langs_ml();
				foreach($langs as $key){
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title_'.$key); ?>"><strong><?php _e('Title'); ?> (<?php echo get_title_forkey($key); ?>): </strong></label><br />
				<input type="text" name="<?php echo $this->get_field_name('title'.$key); ?>" id="<?php $this->get_field_id('title'.$key); ?>" class="widefat" value="<?php echo is_isset($instance,'title'.$key); ?>">
			</p>		
				<?php } ?>
			
			<?php } else { ?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><strong><?php _e('Title'); ?>: </strong></label><br />
				<input type="text" name="<?php echo $this->get_field_name('title'); ?>" id="<?php $this->get_field_id('title'); ?>" class="widefat" value="<?php echo is_isset($instance,'title'); ?>">
			</p>
			<?php } ?>
			
			<div style="border: 1px solid #ddd; border-radius: 3px; padding: 10px; margin: 0 0 10px 0;">
				<?php 
				$cbr = is_isset($instance,'cbr');
				if(!is_array($cbr)){ $cbr = array(); }
				
				global $wpdb;
				$parsers = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."parser_pairs ORDER BY menu_order ASC");

				$scroll_lists = array();
				if(is_array($parsers)){
					foreach($parsers as $item){
						$checked = 0;
						if(in_array($item->id, $cbr)){
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => pn_strip_input($item->title_pair_give).'-'.pn_strip_input($item->title_pair_get).' ('.pn_strip_input(ctv_ml($item->title_birg)).')',
							'checked' => $checked,
							'value' => $item->id,
						);
					}	
				}	
				echo get_check_list($scroll_lists, $this->get_field_name('cbr').'[]', '','200', 1);				
				?>
			</div>						
		<?php
		} 
	}

	register_widget('pn_parsers_Widget');
}