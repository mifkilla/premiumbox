<?php  
if( !defined( 'ABSPATH')){ exit(); } 
get_header();

global $or_template_directory;
?>

	<?php if(is_category() or is_tax() or is_tag()){ ?>
		<?php 
		$description = trim(term_description()); 
		if($description){
		?>
			<div class="term_description">
				<div class="text">
					<?php echo apply_filters('the_content',$description); ?>
					<div class="clear"></div>
				</div>	
			</div>
		<?php } ?>
	<?php } ?>	
	
	
	<div class="many_news_wrap">
		<div class="many_news">
		
		<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); 
			$image_arr = wp_get_attachment_image_src(get_post_thumbnail_id(), 'site-thumbnail');
			$image = trim($image_arr[0]);
			$class = '';
			if($image){ $class = 'has_img'; }
			$link = get_permalink($post->ID);
			$title = pn_strip_input(ctv_ml($post->post_title));
		?>	
		
			<div class="one_news" itemscope itemtype="https://schema.org/NewsArticle">
				
				<?php do_action('seodata_post', $post); ?>
				
				<h2 class="one_news_title">
					<a href="<?php echo $link; ?>" rel="bookmark" title="<?php echo $title; ?>"><span><?php the_title(); ?></span></a>
				</h2>
				
				<div class="one_news_date">
					<?php the_time(get_option('date_format')); ?>
				</div>
					<div class="clear"></div>
				
				<div class="one_news_excerpt <?php echo $class; ?>">
					<?php if($image){ ?>
						<div class="one_news_image"><a href="<?php echo $link; ?>" title="<?php echo $title; ?>"><img src="<?php echo $image; ?>" alt="<?php echo $title; ?>" /></a></div>
					<?php } ?>
					<div class="text" itemprop="articleBody">
						<a href="<?php echo $link; ?>" title="<?php echo $title; ?>">
							<?php the_excerpt(); ?>
						</a>	
							<div class="clear"></div>
					</div>
				</div>
				
				<div class="metabox_div">
				
					<div class="metabox_left">
						<div class="metabox_cats">
							<span><?php _e('Category','pntheme'); ?>:</span> <?php the_terms( $post->ID, 'category','<span itemprop="articleSection">',', ','</span>'); ?>
						</div>
						<?php the_tags( '<div class="metabox_tags"><span>'. __('Tags','pntheme') .':</span> ', ', ', '</div>' ); ?>
					</div>
					
					
					<a href="<?php echo $link; ?>" itemprop="url" class="one_news_more" title="<?php echo $title; ?>"><?php _e('Read more','pntheme'); ?></a>
						<div class="clear"></div>
				</div>		
			</div>	
		
		<?php endwhile; ?>
			
		<?php else : ?>
		
		<div class="text">
			<p><?php _e('Unfortunately this section is empty','pntheme'); ?></p>								
		</div>
		
		<?php endif; ?>
		
		</div>
		
		<?php the_pagenavi(); ?>
	</div>
	
<?php 
 
get_footer(); 