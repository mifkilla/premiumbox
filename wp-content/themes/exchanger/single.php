<?php  
if( !defined( 'ABSPATH')){ exit(); } 
get_header(); 

global $or_template_directory;
?>
<div class="single_news_wrap">

<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); 

$blog_url = get_blog_url();
?>
						
	<div class="single_news" itemscope itemtype="https://schema.org/NewsArticle">
	
		<?php do_action('seodata_post', $post, 'single'); ?>
			
		<div class="one_news_date">
			<?php the_time(get_option('date_format')); ?>
		</div>
			<div class="clear"></div>
			
		<div class="one_news_content">
			<div class="text" itemprop="articleBody">				
				<?php the_content(); ?>
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
				
			<a href="<?php echo $blog_url;?>" class="one_news_more"><?php _e('Back to news','pntheme'); ?></a>	
				<div class="clear"></div>
		</div>		
	</div>
				
<?php endwhile; ?>	
<?php endif; ?>	

</div>

<?php comments_template( '', true ); ?>				

<?php 

get_footer();?>