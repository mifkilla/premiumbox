<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('admin_menu_lang') and is_admin()){

	add_action('admin_menu', 'admin_menu_lang');
	function admin_menu_lang(){
		$plugin = get_plugin_class();	
		add_submenu_page("options-general.php", __('Language settings','pn'), __('Language settings','pn'), 'administrator', "all_lang", array($plugin, 'admin_temp'));
	}

	add_action('pn_adminpage_title_all_lang', 'def_pn_adminpage_title_all_lang');
	function def_pn_adminpage_title_all_lang($page){
		_e('Language settings','pn');
	}

	add_filter( 'whitelist_options', 'lang_whitelist_options' );
	function lang_whitelist_options($whitelist_options){
		if(isset($whitelist_options['general'])){	
			$key = array_search('WPLANG',$whitelist_options['general']);
			if(isset($whitelist_options['general'][$key])){
				unset($whitelist_options['general'][$key]);
			}		
		}
		return $whitelist_options;
	}	
	
 	add_action('admin_footer', 'lang_admin_lang_footer');
	function lang_admin_lang_footer(){
		$screen = get_current_screen();
		if($screen->id == 'options-general'){
			?>
			<script type="text/javascript">
			jQuery(function($){
				$('#WPLANG').parents('tr').hide();
			});
			</script>
			<?php
		}			
	}	

	add_filter('all_lang_option', 'def_all_lang_option', 1);
	function def_all_lang_option($options){
	global $wpdb;	
		
		$langs = apply_filters('pn_site_langs', array());
		
		$lang = get_option('pn_lang');
		if(!is_array($lang)){ $lang = array(); }		
		
		$admin_lang = is_isset($lang,'admin_lang');
		if(!$admin_lang){
			$admin_lang = get_locale();
		}		
		
		$site_lang = is_isset($lang,'site_lang');
		if(!$site_lang){
			$site_lang = get_locale();
		}		
		
		$multisite_lang = array();
		if(isset($lang['multisite_lang'])){
			$multisite_lang = $lang['multisite_lang'];
		}
		if(!is_array($multisite_lang)){ $multisite_lang = array(); }		
		
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Language settings','pn'),
			'submit' => __('Save','pn'),
		);	
		if(is_ml()){
			$options['lang_redir'] = array(
				'view' => 'select',
				'title' => __('User language auto detecting','pn'),
				'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
				'default' => is_isset($lang,'lang_redir'),
				'name' => 'lang_redir',
				'work' => 'int',
			);
			$options[] = array(
				'view' => 'line',
			);
		}
		$options['admin_lang'] = array(
			'view' => 'select',
			'title' => __('Admin-panel language','pn'),
			'options' => $langs,
			'default' => $admin_lang,
			'name' => 'admin_lang',
			'work' => 'input',
		);
		$options['site_lang'] = array(
			'view' => 'select',
			'title' => __('Website language','pn'),
			'options' => $langs,
			'default' => $site_lang,
			'name' => 'site_lang',
			'work' => 'input',
		);		
		if(is_ml()){
			$options[] = array(
				'view' => 'line',
			);							
			$options['multisite_lang'] = array(
				'view' => 'user_func',
				'func_data' => array(
					'langs' => $langs,
					'multisite_lang' => $multisite_lang,
					'site_lang' => $site_lang,
				),
				'func' => 'pn_multisite_lang_option',
				'work' => 'input_array',
			);	
		}
		
		return $options;
	}	
	
 	add_action('pn_adminpage_content_all_lang','def_pn_adminpage_content_all_lang');
	function def_pn_adminpage_content_all_lang(){
		
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_lang_option',
			'method' => 'ajax',
			'data' => '',
			'form_link' => '',
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form);		
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('#pn_site_lang').on('change', function(){
			$('.multisite_lang').show();
			
			var vale = $(this).val();
			$('#multisite_lang_'+vale).hide().find('input').prop('checked', true);
		});	
	});
	</script>		
	<?php  
	}

	function pn_multisite_lang_option($data){
		$langs = $data['langs'];			
		$temp = '
		<div class="premium_standart_line" id="pn_multilingual_area">
			<div class="premium_stline_left"><div class="premium_stline_left_ins">'. __('Multilingualism','pn') .'</div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">';
				
					foreach($langs as $key => $title){ 
						$style = '';
						if($key == $data['site_lang']){
							$style = 'display: none;';
						}
						$checked = '';
						if(in_array($key, $data['multisite_lang']) or $data['site_lang'] == $key){ 
							$checked = 'checked="checked"';
						}							
						$temp .= '<div id="multisite_lang_'. $key .'" class="multisite_lang" style="'. $style .'"><label><input type="checkbox" name="multisite_lang[]" '. $checked .' value="'. $key .'" /> '. $title .'</label></div>';
					}
					
					$temp .= '	
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>';			
		echo $temp;	
	}

	add_action('premium_action_all_lang','def_premium_action_all_lang');
	function def_premium_action_all_lang(){

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));

		$data = $form->strip_options('all_lang_option', 'post');
				
		$old_multi = is_ml();
		$multi = 1;
		$old_site_lang = get_site_lang();
		$old_admin_lang = get_locale();
		
		$lang = get_option('pn_lang');
		$lang['admin_lang'] = $admin_lang = $data['admin_lang'];
		$lang['site_lang'] = $site_lang = $data['site_lang'];
		$lang['multisite_lang'] = is_param_post('multisite_lang');
		$lang['lang_redir'] = is_param_post('lang_redir');
		update_option('pn_lang',$lang);
				
		do_action('all_lang_option_post', $data);
			
		do_action('change_site_lang', $old_site_lang, $site_lang, $old_multi, $multi);		
		do_action('change_admin_lang', $old_admin_lang, $admin_lang, $old_multi, $multi);			
				
		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
				
		$form->answer_form($back_url);							
	} 	
}

if(!function_exists('lang_template_redirect')){
	add_action('template_redirect','lang_template_redirect');
	function lang_template_redirect(){
	global $pn_lang;
		if(!is_admin() and is_ml()){
			$lang_redir = intval(is_isset($pn_lang, 'lang_redir'));
			if($lang_redir){
				$first_redirect = intval(get_pn_cookie('first_redirect'));
				if($first_redirect != 1){
					add_pn_cookie('first_redirect', 1);
					$your_lang_arr = explode(',',is_isset($_SERVER,'HTTP_ACCEPT_LANGUAGE'));
					$your_lang = str_replace('-','_',$your_lang_arr[0]);
					$now_locale = get_locale();
					if($your_lang and $your_lang != $now_locale){
						$langs = get_langs_ml();	
						foreach($langs as $lang){
							if($lang == $your_lang){
								$url = lang_self_link($your_lang);
								wp_redirect($url);
								exit;	
							}	
						}			
					}
				}
			}
		}
	}
}