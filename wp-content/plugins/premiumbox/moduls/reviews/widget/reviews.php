<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('reviews_register_widgets')){
	add_action('widgets_init', 'reviews_register_widgets');
	function reviews_register_widgets(){
		class pn_reviews_Widget extends WP_Widget { 
			
			public function __construct($id_base = false, $widget_options = array(), $control_options = array()){
				parent::__construct('get_pn_reviews', __('Reviews','pn'), $widget_options = array(), $control_options = array());
			}
			
			public function widget($args, $instance){
				extract($args);

				global $wpdb;
				$plugin = get_plugin_class();
				
				if(is_ml()){
					$lang = get_locale();
					$title = pn_strip_input(is_isset($instance,'title'.$lang));
				} else {
					$title = pn_strip_input(is_isset($instance,'title'));
				}
				if(!$title){ $title = __('Reviews','pn'); }
				
				$count = intval(is_isset($instance,'count'));
				if($count < 1){ $count=5; }
				
				$deduce = intval($plugin->get_option('reviews','deduce'));
				
				$where = get_reviews_where();
				$countpost = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."reviews WHERE auto_status = '1' AND review_status = 'publish' $where");
				$data_posts = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."reviews WHERE auto_status = '1' AND review_status = 'publish' $where ORDER BY review_date DESC limit $count");
				
				$hidepost = $countpost-$count; if($hidepost < 1){ $hidepost = 0; }
				
				$review_url = $plugin->get_page('reviews');		
				
				$reviews = '';
				$r=0;
				
				$reviews_date_format = apply_filters('reviews_date_format', get_option('date_format').', '.get_option('time_format'));
				
				foreach($data_posts as $item){ $r++;

					$fclass = '';
					if($r == 1){ $fclass='first'; }
							
					$lclass = '';
					if($r == $count){ $lclass='last'; }
							
					if($r%2 == 0){
						$oddeven = 'even';
					} else {
						$oddeven = 'odd';
					}	
					
					$site = esc_url($item->user_site);
					$site1 = $site2 = '';
					if($site){
						$site1 = '<a href="'. $site .'" target="_blank" rel="nofollow noreferrer noopener">';
						$site2 = '</a>';
					}		
					
					$temps = apply_filters('reviews_widget_one', '', $item, $count, $r, $reviews_date_format);
					if(!trim($temps)){
						$temps = '
						<div class="widget_reviews_line '. $fclass .' '. $lclass .' '. $oddeven .'">
							<div class="widget_reviews_author">'. $site1 . pn_strip_input($item->user_name) . $site2 . ',</div>
							<div class="widget_reviews_date">'. get_pn_time( $item->review_date, $reviews_date_format) .'</div>
								<div class="clear"></div>
							<div class="widget_reviews_content">'. wp_trim_words(pn_strip_input($item->review_text), 15) .'</div>
						</div>		
						';	
					}
					
					$reviews .= $temps;
					
				} 		
				
				$array = array(
					'[countpost]' => $countpost,
					'[hidepost]' => $hidepost,
					'[count]' => $count,
					'[title]' => $title,
					'[url]' => $review_url,
					'[reviews]' => $reviews,
				);

				$widget = '
				<div class="widget widget_reviews_div">
					<div class="widget_ins">
						<div class="widget_title">
							<div class="widget_titlevn">
								[title]
							</div>
						</div>
						<div class="widget_items">
							[reviews]
						</div>
						<div class="widget_reviews_more_wrap">
							<a href="[url]" class="widget_reviews_more">'. __('All reviews','pn') .'</a>
								<div class="clear"></div>
						</div>
					</div>
				</div>
				';		
				
				$widget = apply_filters('reviews_widget_block', $widget);
				$widget = get_replace_arrays($array, $widget);
				echo $widget;
			}

			public function form($instance){ 
			?>
				<?php if(is_ml()){ 
					$langs = get_langs_ml();
					foreach($langs as $key){
				?>
				<p>
					<label for="<?php echo $this->get_field_id('title_'.$key); ?>"><strong><?php _e('Widget title','pn'); ?> (<?php echo get_title_forkey($key); ?>): </strong></label><br />
					<input type="text" name="<?php echo $this->get_field_name('title'.$key); ?>" id="<?php $this->get_field_id('title'.$key); ?>" class="widefat" value="<?php echo is_isset($instance,'title'.$key); ?>">
				</p>		
					<?php } ?>
				
				<?php } else { ?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>"><strong><?php _e('Widget title','pn'); ?>: </strong></label><br />
					<input type="text" name="<?php echo $this->get_field_name('title'); ?>" id="<?php $this->get_field_id('title'); ?>" class="widefat" value="<?php echo is_isset($instance,'title'); ?>">
				</p>
				<?php } ?>
				
				<p>
					<label for="<?php echo $this->get_field_id('count'); ?>"><strong><?php _e('Count reviews','pn'); ?>: </strong></label><br />
					<input type="text" name="<?php echo $this->get_field_name('count'); ?>" id="<?php $this->get_field_id('count'); ?>" class="widefat" value="<?php echo is_isset($instance,'count'); ?>">
				</p>		
			<?php
			} 
		}

		register_widget('pn_reviews_Widget');
	}
}