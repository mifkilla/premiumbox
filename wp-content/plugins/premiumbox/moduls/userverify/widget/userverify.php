<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('userverify_register_widgets')){
add_action('widgets_init', 'userverify_register_widgets');
function userverify_register_widgets(){
	class userverify_Widget extends WP_Widget { 
			
			public function __construct($id_base = false, $widget_options = array(), $control_options = array()){
				parent::__construct('get_userverify', __('Verification','pn'), $widget_options = array(), $control_options = array());
			}
			
			public function widget($args, $instance){
				extract($args);

				$plugin = get_plugin_class();
				
				if(is_ml()){
					$lang = get_locale();
					$title = pn_strip_input(is_isset($instance,'title'.$lang));
					$text1 = pn_strip_input(is_isset($instance,'text1'.$lang));
					$text2 = pn_strip_input(is_isset($instance,'text2'.$lang));
					$text3 = pn_strip_input(is_isset($instance,'text3'.$lang));			
				} else {
					$title = pn_strip_input(is_isset($instance,'title'));
					$text1 = pn_strip_input(is_isset($instance,'text1'));
					$text2 = pn_strip_input(is_isset($instance,'text2'));
					$text3 = pn_strip_input(is_isset($instance,'text3'));			
				}		

				if(!$title){ $title = __('Verification','pn'); }
				if(!$text1){ $text1 = __('Verification confirmed','pn'); }	
				if(!$text2){ $text2 = __('You have to pass verification procedure','pn'); }
				if(!$text3){ $text3 = __('Go to verification','pn'); }
				
				$ui = wp_get_current_user();
				$user_id = intval($ui->ID);
				
				if($user_id){
					$link = $plugin->get_page('userverify');
					$status = intval($plugin->get_option('usve','status'));
					if(isset($ui->user_verify)){
						$user_verify = $ui->user_verify;
						
						$temp = '
						<div class="userverify_widget">
							<div class="userverify_widget_ins">
								<div class="userverify_widget_title">
									<div class="userverify_widget_title_ins">
										'. $title .'
									</div>	
								</div>
								<div class="userverify_widget_body">
						';
						
						if($user_verify == 1){
							$temp .= '<div class="account_verify true">'. $text1 .'</div>';
						} else {
							$temp .= '<div class="account_verify">'. $text2 .'</div>';
						}
						
						if($user_verify == 0 and $status == 1){
							$temp .= '<div class="needverifylink"><a href="'. $link .'">'. $text3 .'</a></div>';	
						}
						
						$temp .= '
								</div>
							</div>
						</div>';
						
						echo apply_filters('userverify_widget_block',$temp, $title, $text1, $text2, $text3, $link, $user_verify, $ui);
					}
				} 
			}

			public function form($instance){ 
			?>
			
				<?php if(is_ml()){ 
					$langs = get_langs_ml();
					foreach($langs as $key){
				?>
				<p>
					<label for="<?php echo $this->get_field_id('title'.$key); ?>"><strong><?php _e('Title'); ?> (<?php echo get_title_forkey($key); ?>): </strong></label><br />
					<input type="text" name="<?php echo $this->get_field_name('title'.$key); ?>" id="<?php $this->get_field_id('title'.$key); ?>" class="widefat" value="<?php echo is_isset($instance,'title'.$key); ?>">
				</p>		
					<?php } ?>
				
				<?php } else { ?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>"><strong><?php _e('Title'); ?>: </strong></label><br />
					<input type="text" name="<?php echo $this->get_field_name('title'); ?>" id="<?php $this->get_field_id('title'); ?>" class="widefat" value="<?php echo is_isset($instance,'title'); ?>">
				</p>
				<?php } ?>
				
				<?php if(is_ml()){ 
					$langs = get_langs_ml();
					foreach($langs as $key){
				?>
				<p>
					<label for="<?php echo $this->get_field_id('text1'.$key); ?>"><strong><?php _e('Text for verified users','pn'); ?> (<?php echo get_title_forkey($key); ?>): </strong></label><br />
					<input type="text" name="<?php echo $this->get_field_name('text1'.$key); ?>" id="<?php $this->get_field_id('text1'.$key); ?>" class="widefat" value="<?php echo is_isset($instance,'text1'.$key); ?>">
				</p>		
					<?php } ?>
				
				<?php } else { ?>
				<p>
					<label for="<?php echo $this->get_field_id('text1'); ?>"><strong><?php _e('Text for verified users','pn'); ?>: </strong></label><br />
					<input type="text" name="<?php echo $this->get_field_name('text1'); ?>" id="<?php $this->get_field_id('text1'); ?>" class="widefat" value="<?php echo is_isset($instance,'text1'); ?>">
				</p>
				<?php } ?>			

				<?php if(is_ml()){ 
					$langs = get_langs_ml();
					foreach($langs as $key){
				?>
				<p>
					<label for="<?php echo $this->get_field_id('text2'.$key); ?>"><strong><?php _e('Text for unverified users','pn'); ?> (<?php echo get_title_forkey($key); ?>): </strong></label><br />
					<input type="text" name="<?php echo $this->get_field_name('text2'.$key); ?>" id="<?php $this->get_field_id('text2'.$key); ?>" class="widefat" value="<?php echo is_isset($instance,'text2'.$key); ?>">
				</p>		
					<?php } ?>
				
				<?php } else { ?>
				<p>
					<label for="<?php echo $this->get_field_id('text2'); ?>"><strong><?php _e('Text for unverified users','pn'); ?>: </strong></label><br />
					<input type="text" name="<?php echo $this->get_field_name('text2'); ?>" id="<?php $this->get_field_id('text2'); ?>" class="widefat" value="<?php echo is_isset($instance,'text2'); ?>">
				</p>
				<?php } ?>

				<?php if(is_ml()){ 
					$langs = get_langs_ml();
					foreach($langs as $key){
				?>
				<p>
					<label for="<?php echo $this->get_field_id('text3'.$key); ?>"><strong><?php _e('Text content in link needed for verification','pn'); ?> (<?php echo get_title_forkey($key); ?>): </strong></label><br />
					<input type="text" name="<?php echo $this->get_field_name('text3'.$key); ?>" id="<?php $this->get_field_id('text3'.$key); ?>" class="widefat" value="<?php echo is_isset($instance,'text3'.$key); ?>">
				</p>		
					<?php } ?>
				
				<?php } else { ?>
				<p>
					<label for="<?php echo $this->get_field_id('text3'); ?>"><strong><?php _e('Text content in link needed for verification','pn'); ?>: </strong></label><br />
					<input type="text" name="<?php echo $this->get_field_name('text3'); ?>" id="<?php $this->get_field_id('text3'); ?>" class="widefat" value="<?php echo is_isset($instance,'text3'); ?>">
				</p>
				<?php } ?>			
				
			<?php 
			}
			
	}
	register_widget('userverify_Widget');
}	
}