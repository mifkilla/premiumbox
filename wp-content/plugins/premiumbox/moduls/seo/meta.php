<?php
if( !defined( 'ABSPATH')){ exit(); }

/* news and pages */
if(!function_exists('seo_metabox')){
	add_action("admin_menu", "seo_metabox");
	function seo_metabox($post_id) {
		if (function_exists("add_meta_box")) {
			$args = array('public' => true);
			$post_types = get_post_types($args,'names');
			if(is_array($post_types)){
				foreach($post_types as $pt){
					if($pt != 'attachment'){
						add_meta_box("pn_seo_id", __('Seo','pn'), "pn_seo_box", $pt, "normal");
					}
				}
			}
		}
	}
} 

if(!function_exists('pn_seo_box')){
	function pn_seo_box($post){
		$post_id = $post->ID;
		
		$form = new PremiumForm();
				
		$seo_title = get_post_meta($post_id, 'seo_title', true); 
		$seo_key = get_post_meta($post_id, 'seo_key', true); 
		$seo_descr = get_post_meta($post_id, 'seo_descr', true);

		$ogp_title = get_post_meta($post_id, 'ogp_title', true);
		$ogp_descr = get_post_meta($post_id, 'ogp_descr', true);
		
		$atts_input = array();
		$atts_input['class'] = 'big_input';
		?>
		<input type="hidden" name="pn_seo_box" value="1" />
			
			<p><strong><?php _e('Page title','pn'); ?></strong>
			<?php $form->input('seo_title' , $seo_title, $atts_input, 1); ?>
			</p>
			
			<p><strong><?php _e('Page keywords','pn'); ?></strong>
			<?php $form->textarea('seo_key', $seo_key, '3', '', 1, 1); ?>
			</p>		
			
			<p><strong><?php _e('Page description','pn'); ?></strong>
			<?php $form->textarea('seo_descr', $seo_descr, '6', '', 1, 1); ?>
			</p>

			<p><strong><?php _e('OGP title','pn'); ?></strong>
			<?php $form->input('ogp_title' , $ogp_title, $atts_input, 1); ?>
			</p>
			<p><strong><?php _e('OGP description','pn'); ?></strong>
			<?php $form->textarea('ogp_descr', $ogp_descr, '6', '', 1, 1); ?>
			</p>		
		<?php
	}
}

if(!function_exists('edit_post_seo')){
	add_action("edit_post", "edit_post_seo");
	function edit_post_seo($post_id){
		if(!current_user_can('edit_post', $post_id )){
			return $post_id;
		}
		if(isset($_POST['pn_seo_box'])){					
			$seo_title = pn_strip_input(is_param_post_ml('seo_title'));
			update_post_meta($post_id, 'seo_title', $seo_title) or add_post_meta($post_id, 'seo_title', $seo_title, true);	
			
			$seo_key = pn_strip_input(is_param_post_ml('seo_key'));
			update_post_meta($post_id, 'seo_key', $seo_key) or add_post_meta($post_id, 'seo_key', $seo_key, true);

			$seo_descr = pn_strip_input(is_param_post_ml('seo_descr'));
			update_post_meta($post_id, 'seo_descr', $seo_descr) or add_post_meta($post_id, 'seo_descr', $seo_descr, true);	

			$ogp_title = pn_strip_input(is_param_post_ml('ogp_title'));
			update_post_meta($post_id, 'ogp_title', $ogp_title) or add_post_meta($post_id, 'ogp_title', $ogp_title, true);		
			
			$ogp_descr = pn_strip_input(is_param_post_ml('ogp_descr'));
			update_post_meta($post_id, 'ogp_descr', $ogp_descr) or add_post_meta($post_id, 'ogp_descr', $ogp_descr, true);		
		}	
	}
} 
/* end news and pages */

/* category and tags */
if(!function_exists('all_posttypes_set_seo')){
	add_action('init','all_posttypes_set_seo', 10000);
	function all_posttypes_set_seo(){
		$taxonomies = get_taxonomies('','objects');
		if(is_array($taxonomies)){
			$not = array('nav_menu','link_category','post_format');
			foreach($taxonomies as $tax){    
				$name = $tax->name;
				if(!in_array($name,$not)){	
					add_action($name . '_add_form_fields', 'add_form_fields_seo');
					add_action($name . '_edit_form', 'edit_form_fields_seo');
					add_action('edit_' . $name, 'edit_tags_seo');
					add_action('created_' . $name, 'edit_tags_seo');
				}
			}
		}
	}
} 
		 
if(!function_exists('add_form_fields_seo')){		 
	function add_form_fields_seo($tag){
		$form = new PremiumForm();
		
		$atts_input = array();
		$atts_input['class'] = 'big_input';
	?>
		<input type="hidden" name="tag_seo_filter" value="1" />
			
		<div class="form-field term-name-wrap">
			<label><?php _e('Page title','pn'); ?></label>
			<?php $form->input('seo_title' , '', $atts_input, 1); ?>
		</div>
		<div class="form-field term-name-wrap">
			<label><?php _e('Page keywords','pn'); ?></label>
			<?php $form->textarea('seo_key', '', '3', '', 1, 1); ?>
		</div>
		<div class="form-field term-name-wrap">
			<label><?php _e('Page description','pn'); ?></label>
			<?php $form->textarea('seo_descr', '', '6', '', 1, 1); ?>
		</div>	
		
		<div class="form-field term-name-wrap">
			<label><?php _e('OGP title','pn'); ?></label>
			<?php $form->input('ogp_title', '', $atts_input, 1); ?>
		</div>
		<div class="form-field term-name-wrap">
			<label><?php _e('OGP description','pn'); ?></label>
			<?php $form->textarea('ogp_descr', '', '6', '', 1, 1); ?>
		</div>	
	<?php 	
	}
} 

if(!function_exists('edit_form_fields_seo')){
	function edit_form_fields_seo($tag){
		$form = new PremiumForm();
		
		$term_id = $tag->term_id;
		$seo_title = get_term_meta($term_id, 'seo_title', true); 
		$seo_key = get_term_meta($term_id, 'seo_key', true); 
		$seo_descr = get_term_meta($term_id, 'seo_descr', true);

		$ogp_title = get_term_meta($term_id, 'ogp_title', true);
		$ogp_descr = get_term_meta($term_id, 'ogp_descr', true);
		
		$atts_input = array();
		$atts_input['class'] = 'big_input';
	?>
		<input type="hidden" name="tag_seo_filter" value="1" />
			
		<table class="form-table">
			<tr class="form-field term-name-wrap">
				<th scope="row"><label><?php _e('Page title','pn'); ?></label></th>
				<td>
					<?php $form->input('seo_title' , $seo_title, $atts_input, 1); ?>
				</td>
			</tr>
			<tr class="form-field term-name-wrap">
				<th scope="row"><label><?php _e('Page keywords','pn'); ?></label></th>
				<td>
					<?php $form->textarea('seo_key', $seo_key, '3', '', 1, 1); ?>
				</td>
			</tr>
			<tr class="form-field term-name-wrap">
				<th scope="row"><label><?php _e('Page description','pn'); ?></label></th>
				<td>
					<?php $form->textarea('seo_descr', $seo_descr, '6', '', 1, 1); ?>
				</td>
			</tr>

			<tr class="form-field term-name-wrap">
				<th scope="row"><label><?php _e('OGP title','pn'); ?></label></th>
				<td>
					<?php $form->input('ogp_title' , $ogp_title, $atts_input, 1); ?>
				</td>
			</tr>
			<tr class="form-field term-name-wrap">
				<th scope="row"><label><?php _e('OGP description','pn'); ?></label></th>
				<td>
					<?php $form->textarea('ogp_descr', $ogp_descr, '6', '', 1, 1); ?>
				</td>
			</tr>		
		</table>
	<?php	
	}
} 

if(!function_exists('edit_tags_seo')){
	function edit_tags_seo($id){
		if(isset($_POST['tag_seo_filter'])){
			$seo_title = pn_strip_input(is_param_post_ml('seo_title'));
			update_term_meta($id, 'seo_title', $seo_title);	
			
			$seo_key = pn_strip_input(is_param_post_ml('seo_key'));
			update_term_meta($id, 'seo_key', $seo_key);

			$seo_descr = pn_strip_input(is_param_post_ml('seo_descr'));
			update_term_meta($id, 'seo_descr', $seo_descr);
			
			$ogp_title = pn_strip_input(is_param_post_ml('ogp_title'));
			update_term_meta($id, 'ogp_title', $ogp_title);			

			$ogp_descr = pn_strip_input(is_param_post_ml('ogp_descr'));
			update_term_meta($id, 'ogp_descr', $ogp_descr);		
		}
	} 
}
/* end category and tags */

/* canonical */
if(!function_exists('seo_rel_canonical')){
	remove_action('wp_head', 'rel_canonical');
	add_action( 'wp_head', 'seo_rel_canonical');
	function seo_rel_canonical() {
		if (is_404()){
			return;
		}	
		$link_arr = explode('?',lang_self_link());
		$link = $link_arr[0];
		echo "<link rel='canonical' href='$link' />\n";
	}
}

if(!function_exists('get_seo_keys')){
	function get_seo_keys($keys) {
		$keys_arr = explode(',', $keys);
		$new_keys = array();
		foreach($keys_arr as $key){
			$key = trim($key);
			if($key){
				$new_keys[] = $key;
			}
		}
		$new_keys = array_unique($new_keys);
		return join(',', $new_keys);
	}
}

/* keywords */
if(!function_exists('wp_head_seo')){
	add_action('wp_head' , 'wp_head_seo');
	function wp_head_seo(){
		global $wp_query;
		
		if (is_404()){
			return;
		}
		
		$plugin = get_plugin_class();

		$key = '';
		$descr = '';
		$ogp_title = '';
		$ogp_descr = '';
		$ogp_image = '';
		
		$sitename = pn_site_name();
		
		$pn_seo_name = '';
		if(is_front_page()){
			$pn_seo_name = 'home';	
		} elseif(is_home()){
			$pn_seo_name = 'post';
		} elseif(is_post_type_archive() and is_object($wp_query)){	
			$pn_seo_name = $wp_query->query['post_type'];	
		} elseif(is_category() or is_tag() or is_tax()){
			$pn_seo_name = 'term';
		} elseif(is_singular() or is_page()){
			$pn_seo_name = 'single';
		}
		$pn_seo_name = apply_filters('pn_seo_name', $pn_seo_name);
		
		if($pn_seo_name == 'term'){
			$term_data = get_queried_object();
			if(isset($term_data->term_id)){
				$term_id = $term_data->term_id;
				
				$key = pn_strip_input(ctv_ml(get_term_meta($term_id, 'seo_key', true))); 
				$descr = pn_strip_input(ctv_ml(get_term_meta($term_id, 'seo_descr', true)));
				if(!$descr){
					$descr = pn_strip_input(is_isset($term_data, 'description'));
				}
				if(!$descr){
					$descr = __('Information by theme','pn') . ' ' . pn_strip_input(is_isset($term_data, 'name'));
				}				

				$ogp_title = pn_strip_input(ctv_ml(get_term_meta($term_id, 'ogp_title', true)));
				if(!$ogp_title){ $ogp_title = pn_strip_input(is_isset($term_data, 'name')); }
				$ogp_descr = pn_strip_input(ctv_ml(get_term_meta($term_id, 'ogp_descr', true)));	
				if(!$ogp_descr){ $ogp_descr = pn_strip_input(is_isset($term_data, 'description')); }
				if(!$ogp_descr){ $ogp_descr = __('Information by theme','pn') . ' ' . pn_strip_input(is_isset($term_data, 'name')); }
			}
		} elseif($pn_seo_name == 'single'){
			global $post;
			if(isset($post->ID)){
				$post_id = intval($post->ID);
				
				$key = pn_strip_input(ctv_ml(get_post_meta($post_id, 'seo_key', true))); 
				$descr = pn_strip_input(ctv_ml(get_post_meta($post_id, 'seo_descr', true)));
				if(!$descr){
					$descr = esc_html(wp_trim_words(strip_tags(ctv_ml($post->post_content)),10,'...'));
				}
					
				$ogp_title = pn_strip_input(ctv_ml(get_post_meta($post_id, 'ogp_title', true))); 
				if(!$ogp_title){ $ogp_title = esc_html(ctv_ml($post->post_title)); }
				$ogp_descr = pn_strip_input(ctv_ml(get_post_meta($post_id, 'ogp_descr', true)));
				if(!$ogp_descr){ $ogp_descr = esc_html(wp_trim_words(strip_tags(ctv_ml($post->post_content)),10,'...')); }
					
				$image_url = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'thumbnail');
				$ogp_image = pn_strip_input(is_isset($image_url,0));			
			}
		} elseif($pn_seo_name) {
			$key = pn_strip_input(ctv_ml($plugin->get_option('seo', $pn_seo_name . '_key')));
			$descr = pn_strip_input(ctv_ml($plugin->get_option('seo', $pn_seo_name . '_descr')));
				
			$ogp_image = pn_strip_input($plugin->get_option('seo','ogp_'. $pn_seo_name .'_img'));
			
			$ogp_title = pn_strip_input(ctv_ml($plugin->get_option('seo','ogp_'. $pn_seo_name .'_title')));
			if(!$ogp_title){ $ogp_title = pn_strip_input($sitename); }
			
			$ogp_descr = pn_strip_input(ctv_ml($plugin->get_option('seo','ogp_'. $pn_seo_name .'_descr')));
			if(!$ogp_descr){ $ogp_descr = pn_strip_input(get_option('blogdescription')); }
		}
		
 		if($pn_seo_name){
			if(!$ogp_image){ $ogp_image = $plugin->get_option('seo','ogp_def_img'); }
			$ogp_image = is_ssl_url($ogp_image);
	?><meta name="keywords" content="<?php echo get_seo_keys($key); ?>" />
<meta name="description" content="<?php echo $descr; ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<?php echo lang_self_link(); ?>" />
<meta property="og:site_name" content="<?php echo $sitename; ?>" />
<?php if($ogp_descr){ ?>
<meta property="og:description" content="<?php echo $ogp_descr; ?>" />
<?php } ?>
<?php if($ogp_title){ ?>
<meta property="og:title" content="<?php echo $ogp_title; ?>" />
<?php } ?>
<?php if($ogp_image){ ?>
<meta property="og:image" content="<?php echo $ogp_image; ?>" />
<?php } 
		} 
		$ya_meta = pn_strip_input($plugin->get_option('seo','ya_meta'));
		if($ya_meta){
		?>
<meta name="yandex-verification" content="<?php echo $ya_meta; ?>" />
<?php 
		}
		$gl_meta = pn_strip_input($plugin->get_option('seo','gl_meta'));
		if($gl_meta){
		?>
<meta name="google-site-verification" content="<?php echo $gl_meta; ?>" />
<?php
		}
	}
} 
/* end keywords */	

/* title */
if(!function_exists('wp_title_seo')){
	add_filter('wp_title' , 'wp_title_seo', 99);
	function wp_title_seo($title) {
		global $wp_query;
		
		if (is_404()){
			return $title;
		}
		
		$plugin = get_plugin_class();
		
		$sitename = pn_site_name();
		
		$pn_seo_name = '';
		if(is_front_page()){
			$pn_seo_name = 'home';	
		} elseif(is_home()){
			$pn_seo_name = 'post';	
		} elseif(is_post_type_archive() and is_object($wp_query)){	
			$pn_seo_name = $wp_query->query['post_type'];	
		} elseif(is_category() or is_tag() or is_tax()){
			$pn_seo_name = 'term';
		} elseif(is_singular() or is_page()){
			$pn_seo_name = 'single';
		}
		$pn_seo_name = apply_filters('pn_seo_name', $pn_seo_name);

		if($pn_seo_name == 'term'){
			$term_data = get_queried_object();
			if(isset($term_data->term_id)){
				$term_id = $term_data->term_id;
			
				$seo_title = pn_strip_input(ctv_ml(get_term_meta($term_id, 'seo_title', true)));
				if($seo_title){
					$seo_title = str_replace('[sitename]', $sitename ,$seo_title);
					return $seo_title;
				}
			}
		} elseif($pn_seo_name == 'single'){
			global $post;
			if(isset($post->ID)){
				$item_id = intval($post->ID);
				$post_type = trim($post->post_type);
				
				$seo_title = pn_strip_input(ctv_ml(get_post_meta($item_id, 'seo_title', true)));
				if($seo_title){
					$seo_title = str_replace('[sitename]', $sitename ,$seo_title);
					return $seo_title;
				}
				
				$seo_title = pn_strip_input(ctv_ml($plugin->get_option('seo', $post_type . '_temp')));
				if($seo_title){
					$item_title = pn_strip_input(ctv_ml($post->post_title));
					$seo_title = str_replace('[sitename]', $sitename ,$seo_title);
					$seo_title = str_replace('[title]', $item_title ,$seo_title);
					return $seo_title;
				}
			}
		} elseif($pn_seo_name) {
			$seo_title = pn_strip_input(ctv_ml($plugin->get_option('seo', $pn_seo_name . '_title')));
			if($seo_title){
				$seo_title = str_replace('[sitename]', $sitename ,$seo_title);
				return $seo_title;
			}	
		}						
					
		return $title;
	} 
}
/* end title */	

if(!function_exists('wp_footer_seo')){
	add_action('wp_footer' , 'wp_footer_seo');
	function wp_footer_seo(){
		$plugin = get_plugin_class();
		$ya_metrika = pn_strip_input($plugin->get_option('seo','ya_metrika'));
		if($ya_metrika){
		?>
	<!-- Yandex.Metrika counter -->
	<script type="text/javascript" >
	   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
	   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
	   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

	   ym(<?php echo $ya_metrika; ?>, "init", {
			clickmap:true,
			trackLinks:true,
			accurateTrackBounce:true,
			webvisor:true
	   });
	</script>
	<noscript><div><img src="https://mc.yandex.ru/watch/<?php echo $ya_metrika; ?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->
		<?php
		}
		$gglanalytic = pn_strip_input($plugin->get_option('seo','gglanalytic'));
		if($gglanalytic){
		?>
	<!-- Global site tag (gtag.js) - Google Analytics --> 
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $gglanalytic; ?>"></script> 
	<script>
		window.dataLayer = window.dataLayer || [];   
		function gtag(){dataLayer.push(arguments);}   
		gtag('js', new Date());   
		gtag('config', '<?php echo $gglanalytic; ?>'); 
	</script>
		<?php
		}
	}
}

if(!function_exists('seo_seodata_post')){
	add_action('seodata_post' , 'seo_seodata_post', 10, 2);
	function seo_seodata_post($post, $place=''){
	global $or_template_directory;
	
		$post_id = $post->ID;
		$plugin = get_plugin_class();
		$image_arr = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'thumbnail');
		$image = pn_strip_input(is_isset($image_arr,0));
		if(!$image){ $image = $plugin->get_option('seo','post_img'); }
		if(!$image){ $image = $or_template_directory . '/logo.png'; }
		$image_w = intval(is_isset($image_arr,1)); if(!$image_w){ $image_w = 100; }
		$image_h = intval(is_isset($image_arr,2)); if(!$image_h){ $image_h = 60; }
		
		$author = pn_strip_input(ctv_ml($plugin->get_option('seo','post_author')));
		if(!$author){ $author = pn_site_name(); }
		
		$name = pn_strip_input(ctv_ml($plugin->get_option('seo','post_name')));
		if(!$name){ $name = pn_site_name(); }	

		$address = pn_strip_input(ctv_ml($plugin->get_option('seo','post_address')));
		$telephone = pn_strip_input(ctv_ml($plugin->get_option('seo','post_telephone')));
		
		if($place and $place == 'single'){
			?>
			<a href="<?php echo get_permalink($post_id); ?>" style="display: none;" itemprop="url"></a>
			<?php
		}
		?>
			<meta itemprop="name" content="<?php echo pn_strip_input(ctv_ml($post->post_title)); ?>">
			<meta itemprop="headline" content="<?php echo pn_strip_input(ctv_ml($post->post_title)); ?>">
			<meta itemprop="image" content="<?php echo $image; ?>">
			<meta itemprop="datePublished" content="<?php echo get_the_time('Y-m-d', $post); ?>">
			<meta itemprop="dateModified" content="<?php echo get_pn_date($post->post_modified, 'Y-m-d'); ?>">
			<meta itemprop="author" content="<?php echo $author; ?>">
			<div style="display: none;" itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
				<div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
					<meta itemprop="image" content="<?php echo $image; ?>">
					<a href="<?php echo $image; ?>" style="display: none;" itemprop="url"></a>
					<span itemprop="width"><?php echo $image_w; ?></span>
					<span itemprop="height"><?php echo $image_h; ?></span>
				</div>
				<meta itemprop="name" content="<?php echo $name; ?>">
				<meta itemprop="address" content="<?php echo $address; ?>">
				<meta itemprop="telephone" content="<?php echo $telephone; ?>">
			</div>			
		<?php
	}
}	