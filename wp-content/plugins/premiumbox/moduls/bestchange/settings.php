<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_bestchange', 'pn_adminpage_title_pn_bestchange');
	function pn_adminpage_title_pn_bestchange(){
		_e('BestChange parser','pn');
	} 

 	add_action('pn_adminpage_content_pn_bestchange','def_pn_adminpage_content_pn_bestchange');
	function def_pn_adminpage_content_pn_bestchange(){
	global $premiumbox, $wpdb;

		$data = get_option('bestchange');
		if(!is_array($data)){ $data = array(); }

		$form = new PremiumForm();
		
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Settings','pn'),
			'submit' => __('Save','pn'),
		);
		$options['hideid'] = array(
			'view' => 'inputbig',
			'title' => __('Black list of exchangers ID (separate coma)','pn'),
			'default' => is_isset($data, 'hideid'),
			'name' => 'hideid',
			'atts' => array('autocomplete'=>'off'),
		);
		$options['onlyid'] = array(
			'view' => 'inputbig',
			'title' => __('White list of exchangers ID (separate coma)','pn'),
			'default' => is_isset($data, 'onlyid'),
			'name' => 'onlyid',
			'atts' => array('autocomplete'=>'off'),
		);	
		$options['conversion'] = array(
			'view' => 'select',
			'title' => __('Enable conversion rate 1 to XXX','pn'),
			'options' => array('0'=>__('No','pn'), '1'=>__('Yes','pn')),
			'default' => is_isset($data, 'conversion'),
			'name' => 'conversion',
		);		
		$options['test'] = array(
			'view' => 'select',
			'title' => __('Test mode','pn'),
			'default' => is_isset($data, 'test'),
			'options' => array('0'=> __('No','pn'), '1'=> __('Yes','pn')),
			'name' => 'test',
		);
		$options['server'] = array(
			'view' => 'select',
			'title' => __('Server','pn'),
			'default' => $premiumbox->get_option('bcbroker','server'),
			'options' => array('0'=> 'api.bestchange.ru', '1'=> 'api.bestchange.net', '2'=> 'api.bestchange.com'),
			'name' => 'server',
		);
		$options['timeout'] = array(
			'view' => 'inputbig',
			'title' => __('Timeout (sec.)','pn'),
			'default' => $premiumbox->get_option('bcbroker','timeout'),
			'name' => 'timeout',
		);
		$options['timeout_help'] = array(
			'view' => 'help',
			'title' => __('More info','pn'),
			'default' => __('Timeout is the period when the website awaits a response from a third-party service. If no response is received in the preset period, the website will continue running without response. If the duration is not specified or is 0, the standard 20-second timeout is applied. There is no universal value for the timeout, since it depends on the operation speed of a specific service.','pn'),
		);		

		$params_form = array(
			'filter' => 'pn_bestchange_options',
			'method' => 'ajax',
			'data' => $data,
			'form_link' => pn_link('pn_bestchange_save','post'),
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);
	?>
	<form method="post" action="<?php the_pn_link('','post'); ?>">
		<?php wp_referer_field(); ?>
		<div class="premium_body">
			<div class="premium_standart_line"><?php echo $form->h3('', __('Save','pn')); ?></div>
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Select data','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						
						<?php 
						$lists = array();
						$path = $premiumbox->upload_dir . '/bcparser/bm_cy.dat';
						
						if(is_file($path)){
							$fdata = @file_get_contents($path);
							$lists = explode("\n", $fdata);
						}
						
						$in_w = array();
						$works = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."bestchange_currency_codes");
						foreach($works as $work){
							$in_w[] = $work->currency_code_id;
						}
						
						$scroll_lists = array();
						$new_lists = array();
						foreach($lists as $val){
							$in = explode(";",$val);
							$title = get_tgncp($in[2]).' ('. get_tgncp($in[3]) .')';
							$title = pn_strip_input($title);
							$new_lists[$in[0]] = $title;
						}	

						asort($new_lists);
						
						$new_lists = list_checks_top($new_lists, $in_w);
						
						foreach($new_lists as $key => $title){
							$checked = 0;
							if(in_array($key, $in_w)){
								$checked = 1;
							}
							$scroll_lists[] = array(
								'title' => $title,
								'checked' => $checked,
								'value' => $key,
							);
						} 
						echo get_check_list($scroll_lists, 'pars[]','', '500', 1);
						?>						
						
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>	
			<div class="premium_standart_line"><?php echo $form->h3('', __('Save','pn')); ?></div>
		</div>		
	</form>	
	<?php
	}
	 
	add_action('premium_action_pn_bestchange_save','def_premium_action_pn_bestchange_save');
	function def_premium_action_pn_bestchange_save(){
	global $wpdb, $premiumbox;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_bestchange'));
		
		$arr = array();
		$arr['hideid'] = pn_strip_input(is_param_post('hideid'));
		$arr['onlyid'] = pn_strip_input(is_param_post('onlyid'));
		$arr['test'] = intval(is_param_post('test'));
		$arr['conversion'] = intval(is_param_post('conversion'));
		update_option('bestchange', $arr);

		$premiumbox->update_option('bcbroker', 'server', intval(is_param_post('server')));
		$premiumbox->update_option('bcbroker', 'timeout', intval(is_param_post('timeout')));
		
		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
				
		$form->answer_form($back_url);
	}
	 
	add_action('premium_action_pn_bestchange','def_premium_action_pn_bestchange');
	function def_premium_action_pn_bestchange(){
	global $wpdb, $premiumbox;	
		
		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_bestchange'));
		
		$path = $premiumbox->upload_dir . '/bcparser/bm_cy.dat';
		$lists = array();
		if(is_file($path)){
			$fdata = file_get_contents($path);
			$lists = explode("\n", $fdata);
		}	
		
		$pars = is_param_post('pars'); if(!is_array($pars)){ $pars = array(); }
		$wpdb->query("DELETE FROM ".$wpdb->prefix."bestchange_currency_codes");
		
		foreach($lists as $val){
			$in = explode(";",$val);
			if(in_array($in[0], $pars)){
				$arr = array();
				$arr['currency_code_id'] = intval($in[0]);
				$arr['currency_code_title'] = pn_strip_input(get_tgncp($in[2])).' ('. pn_strip_input(get_tgncp($in[3])) .')';
				$wpdb->insert($wpdb->prefix."bestchange_currency_codes", $arr);
			}
		}
		
		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
		
		$form->answer_form($back_url);	
	} 
}	