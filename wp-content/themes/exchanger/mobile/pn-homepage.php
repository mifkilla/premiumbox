<?php 
if( !defined( 'ABSPATH')){ exit(); }

mobile_theme_include('header');

$plugin = get_plugin_class();

$mho_change = get_theme_option('mho_change', array('wtitle','wtext','ititle','itext','blocreviews','lastobmen','hidecurr','partners','blocknews','catnews','advantages'));
?>
<div class="homepage_wrap">

	<?php
	if($mho_change['wtext']){
	?>
	<div class="home_wtext_wrap">
		<div class="home_wtext_ins">
			<div class="home_wtext_block">
				<div class="home_wtext_title"><?php echo pn_strip_input($mho_change['wtitle']); ?></div>
				<div class="home_wtext_div">
					<div class="text">
						<?php echo apply_filters('the_content',$mho_change['wtext']); ?>
							<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>
	</div>		
	<?php } ?>

	<div class="xchange_table_wrap">
		<?php if(function_exists('the_exchange_home_mobile')){ the_exchange_home_mobile(); }  ?>
	</div>	
	
	<?php
	if($mho_change['itext']){
	?>
	<div class="home_text_wrap">
		<div class="home_text_ins">
			<div class="home_text_abs"></div>
			<div class="home_text_block">
				<div class="home_text_title"><?php echo pn_strip_input($mho_change['ititle']); ?></div>
				<div class="home_text_div">
					<div class="text">
						<?php echo apply_filters('the_content',$mho_change['itext']); ?>
						<div class="clear"></div>
					</div>
				</div>
			</div>	
		</div>
	</div>	
	<?php } 
	?>		
	
	<?php 
	if($mho_change['blocreviews'] == 1 and function_exists('list_reviews')){ 
		$review_url = $plugin->get_page('reviews');
		$data_posts = list_reviews(3);	
	?>
	<div class="home_reviews_wrap">
		<div class="home_reviews_ins">
			<div class="home_reviews_title"><?php _e('Reviews','pntheme'); ?></div>
				
			<div class="home_reviews_div_wrap">
				
				<?php 
				$reviews_date_format = apply_filters('reviews_date_format', get_option('date_format').', '.get_option('time_format'));	
				foreach($data_posts as $item){ 
					
					$site = esc_url($item->user_site);
					$site1 = $site2 = '';
					if($site){
						$site1 = '<a href="'. $site .'" rel="nofollow noreferrer noopener" target="_blank">';
						$site2 = '</a>';
					}				
				?>
					
					<div class="home_reviews_one">
						<div class="home_reviews_abs"></div>
						<div class="home_reviews_date"><?php echo $site1 . pn_strip_input($item->user_name) . $site2; ?>, <?php echo get_pn_time($item->review_date , $reviews_date_format); ?></div>
							<div class="clear"></div>
						<div class="home_reviews_content"><?php echo wp_trim_words(pn_strip_input($item->review_text), 15); ?></div>
					</div>			
					
				<?php } ?>
				
				<div class="clear"></div>
			</div>
				
			<a href="<?php echo $review_url; ?>" class="home_reviews_more"><?php _e('All reviews','pntheme'); ?></a>
				<div class="clear"></div>
		</div>
	</div>
	<?php }	?>	
	
	<?php if($mho_change['lastobmen'] == 1 and function_exists('get_last_bids')){ ?>
	<div class="home_lchange_wrap">
		<div class="home_lchange_wrap_ins">
			<div class="home_lchange_block">
				<div class="home_lchange_title"><?php _e('Last exchanges','pntheme'); ?></div>
			
				<div class="home_lchange_div_wrap">
					<div class="home_lchange_div">
					
						<?php
						$last_bids = get_last_bids('success', 2);
						foreach($last_bids as $last_bid){	
						?>					
						<div class="home_lchange_one">
							<div class="home_lchange_date"><?php echo $last_bid['createdate']; ?></div>
							<div class="home_lchange_body">
								<div class="home_lchange_why">
									<div class="home_lchange_ico currency_logo" style="background-image: url(<?php echo $last_bid['logo_give']; ?>);"></div>
									<div class="home_lchange_txt">
										<div class="home_lchange_sum"><?php echo is_out_sum($last_bid['sum_give'], $last_bid['decimal_give'], 'all'); ?></div>
										<div class="home_lchange_name"><?php echo $last_bid['vtype_give']; ?></div>
									</div>
										<div class="clear"></div>
								</div>
										
								<div class="home_lchange_arr"></div>
										
								<div class="home_lchange_why">
									<div class="home_lchange_ico currency_logo" style="background-image: url(<?php echo $last_bid['logo_get']; ?>);"></div>
									<div class="home_lchange_txt">
										<div class="home_lchange_sum"><?php echo is_out_sum($last_bid['sum_get'], $last_bid['decimal_get'], 'all'); ?></div>
										<div class="home_lchange_name"><?php echo $last_bid['vtype_get']; ?></div>
									</div>
								</div>				
									<div class="clear"></div>
							</div>	
						</div>
						<?php
						}
						?>
						<div class="clear"></div>
					</div>
				</div>	
			</div>
		</div>
	</div>	
	<?php } ?>	
	
	<?php 
	if($mho_change['blocknews'] == 1){  
		$blog_url = get_blog_url();

		$catnews = intval($mho_change['catnews']);
		$args = array(
			'post_type' => 'post',
			'posts_per_page' => 3
		);	
		if($catnews){
			$args['cat'] = $catnews;
		}

		$data_posts = get_posts($args);
		?>
		<div class="home_news_wrap">
			<div class="home_news">
				<div class="home_news_block">
					<div class="home_news_title"><?php _e('News','pntheme'); ?></div>
					
					<div class="home_news_div_wrap">
						<div class="home_news_div">
						<?php 
						$date_format = get_option('date_format');
						foreach($data_posts as $item){ ?>
						
							<div class="home_news_one">
								<div class="home_news_one_abs"></div>
								<div class="home_news_one_ins">
									<div class="home_news_one_date"><?php echo get_the_time($date_format, $item->ID); ?></div>
										<div class="clear"></div>
									<div class="home_news_one_content"><a href="<?php echo get_permalink($item->ID); ?>"><?php echo pn_strip_input(ctv_ml($item->post_title)); ?></a></div>
								</div>
							</div>			
						
						<?php } ?>
					
							<div class="clear"></div>
						</div>
					</div>
					
					<a href="<?php echo $blog_url; ?>" class="home_news_more"><?php _e('All news','pntheme'); ?></a>
						<div class="clear"></div>
				</div>
			</div>
		</div>
	<?php } ?>	
	
	<?php  
	if(function_exists('list_view_currencies')){
		$hidecurr = explode(',',is_isset($mho_change,'hidecurr'));
		$currencies = list_view_currencies('', $hidecurr);
		if(count($currencies) > 0){
		?>
		<div class="home_reserv_block">
			<div class="home_reserv_title"><?php _e('Currency reserve','pntheme'); ?></div>
					
			<div class="home_reserv_many">
				<div class="home_reserv_many_ins">
					
					<?php $r=0; 
					foreach($currencies as $currency){ $r++; 
						$cl = '';
						if($r%2==0){ $cl = 'reserv_right'; }
					?> 
					<div class="one_home_reserv <?php echo $cl; ?>"> 
						<div class="one_home_reserv_ico currency_logo" style="background-image: url(<?php echo $currency['logo']; ?>);"></div>
						<div class="one_home_reserv_block">
							<div class="one_home_reserv_title">
								<?php echo $currency['title']; ?>
							</div>
							<div class="one_home_reserv_sum">
								<?php echo is_out_sum($currency['reserv'], $currency['decimal'], 'reserv'); ?>
							</div>
						</div>
							<div class="clear"></div>
					</div>
						<?php if($r%2==0){ ?><div class="clear"></div><?php } ?>
					<?php } ?>
					
					<div class="clear"></div>
				</div>	
			</div> 
		</div>	
		<?php 
		} 
	}
	?>
	
	<?php 
 	if(function_exists('get_advantages') and $mho_change['advantages'] == 1){
		$advantages = get_advantages(); 
		if(is_array($advantages) and count($advantages) > 0){			
	?>	
	<div class="home_advantages_wrap">
		<div class="home_advantages_ins">
			<div class="home_advantages_block">
				<div class="home_advantages_blocktitle"><?php _e('Advantages','pntheme'); ?></div>

				<div class="home_adv_div_wrap">
					<div class="home_adv_div">
					
						<?php  
						$r=0;
						foreach($advantages as $item){  $r++;
							$link = esc_url(ctv_ml($item->link));
							$img = is_ssl_url(pn_strip_input($item->img));
							$title = pn_strip_input(ctv_ml($item->title));
							$content = pn_strip_input(ctv_ml($item->content));
							?>
								<div class="home_advantages_one">
									<?php if($link){ ?><a href="<?php echo $link; ?>" rel="nofollow noreferrer noopener" target="_blank"><?php } ?>
										<div class="home_advantages_img" style="background: url(<?php echo $img; ?>) no-repeat center center;"></div>
										<div class="home_advantages_title"><?php echo $title; ?></div>
										<div class="home_advantages_content"><?php echo $content; ?></div>
									<?php if($link){ ?></a><?php } ?>
								</div>
							<?php  
							if($r%3 == 0){ ?><div class="clear"></div><?php }
						}
						?>

						<div class="clear"></div>
					</div>
				</div>
			</div>	
		</div>
	</div>
	<?php
		}
	}
	?>	
	
	<?php 
	if(function_exists('get_partners') and $mho_change['partners'] == 1){
		$partners = get_partners(); 
		if(is_array($partners) and count($partners) > 0){			
	?>			
	<div class="home_partner_wrap">
		<div class="home_partner_div">
			<div class="home_partner_title"><?php _e('Partners','pntheme'); ?></div>
			<?php  
			foreach($partners as $item){ 
				$link = esc_url(ctv_ml($item->link));
				?>
				<div class="home_partner_one">
					<?php if($link){ ?><a href="<?php echo $link; ?>" rel="nofollow noreferrer noopener" target="_blank"><?php } ?>
						<img src="<?php echo is_ssl_url(pn_strip_input($item->img)); ?>" alt="" />
					<?php if($link){ ?></a><?php } ?>
				</div>
				<?php  
			}
			?>
				<div class="clear"></div>
		</div>	
	</div>
	<?php
		}
	}
	?>	
	
</div>	
<?php   
mobile_theme_include('footer');