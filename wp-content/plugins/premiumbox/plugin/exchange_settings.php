<?php
if( !defined( 'ABSPATH')){ exit(); }
  
if(is_admin()){
	add_action('admin_menu', 'admin_menu_exchange_settings');
	function admin_menu_exchange_settings(){
	global $premiumbox;	
		add_submenu_page("pn_config", __('Exchange settings','pn'), __('Exchange settings','pn'), 'administrator', "pn_exchange_settings", array($premiumbox, 'admin_temp'));
	}

	add_action('pn_adminpage_title_pn_exchange_settings', 'pn_adminpage_title_pn_exchange_settings');
	function pn_adminpage_title_pn_exchange_settings($page){
		_e('Exchange settings','pn');
	} 

	add_action('pn_adminpage_content_pn_exchange_settings','def_pn_adminpage_content_pn_exchange_settings');
	function def_pn_adminpage_content_pn_exchange_settings(){
	global $wpdb, $premiumbox;

		$form = new PremiumForm();

			$options = array();
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => __('All settings','pn'),
				'submit' => __('Save','pn'),
			);
			$tablevids = array(
				'0'=> sprintf(__('Table %1s','pn'),'1'),
				'1'=> sprintf(__('Table %1s','pn'),'2'),
				'2'=> sprintf(__('Table %1s','pn'),'3'),
				'3'=> sprintf(__('Table %1s','pn'),'4'),
				'4'=> sprintf(__('Table %1s','pn'),'5'),
				'99'=> __('Exchange form','pn'),
			);
			$tablevids = apply_filters('exchange_tablevids_list', $tablevids);
			$options['tablevid'] = array(
				'view' => 'select',
				'title' => __('Exchange pairs table type','pn'),
				'options' => $tablevids,
				'default' => $premiumbox->get_option('exchange','tablevid'),
				'name' => 'tablevid',
			);
			$options['exch_method'] = array(
				'view' => 'select',
				'title' => __('Exchange type','pn'),
				'options' => array('0'=>__('On a new page','pn'),'1'=>__('On a main page','pn')),
				'default' => $premiumbox->get_option('exchange','exch_method'),
				'name' => 'exch_method',
			);
			$options['tablenothome'] = array(
				'view' => 'select',
				'title' => __('If non-existent direction is selected','pn'),
				'options' => array('0'=>__('Show error','pn'),'1'=>__('Show nearest','pn')),
				'default' => $premiumbox->get_option('exchange','tablenothome'),
				'name' => 'tablenothome',
			);
			$options['tableselecthome'] = array(
				'view' => 'select',
				'title' => __('Display in home exchange form','pn'),
				'options' => array('0'=>__('All currencies','pn'),'1'=>__('Only available currencies for exchange','pn')),
				'default' => $premiumbox->get_option('exchange','tableselecthome'),
				'name' => 'tableselecthome',
			);		
			$options['hidecurrtype'] = array(
				'view' => 'select',
				'title' => __('Hide currency codes above table for selecting exchange direction','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => $premiumbox->get_option('exchange','hidecurrtype'),
				'name' => 'hidecurrtype',
			);		
			$options['tableajax'] = array(
				'view' => 'select',
				'title' => __('Enable AJAX for currency selection table','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => $premiumbox->get_option('exchange','tableajax'),
				'name' => 'tableajax',
			);			
			if(get_settings_second_logo() == 1){
				$options['tableicon'] = array(
					'view' => 'select',
					'title' => __('Show PS logo in exchange table','pn'),
					'options' => array('0'=>__('Main logo','pn'),'1'=>__('Additional logo','pn')),
					'default' => $premiumbox->get_option('exchange','tableicon'),
					'name' => 'tableicon',
				);
			}
			
			$options[] = array(
				'view' => 'line',
			);			
			$options['tableselect'] = array(
				'view' => 'select',
				'title' => __('Display in exchange form','pn'),
				'options' => array('0'=>__('All currencies','pn'),'1'=>__('Only available currencies for exchange','pn')),
				'default' => $premiumbox->get_option('exchange','tableselect'),
				'name' => 'tableselect',
			);		
			$options['tablenot'] = array(
				'view' => 'select',
				'title' => __('If non-existent direction is selected from exchange form','pn'),
				'options' => array('0'=>__('Show error','pn'),'1'=>__('Show nearest','pn')),
				'default' => $premiumbox->get_option('exchange','tablenot'),
				'name' => 'tablenot',
			);		
			$options['enable_step2'] = array(
				'view' => 'select',
				'title' => __('Use exchange step №2, where user confirms his details','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => $premiumbox->get_option('exchange','enable_step2'),
				'name' => 'enable_step2',
			);	
			$options[] = array(
				'view' => 'line',
			);		
			$options['allow_dev'] = array(
				'view' => 'select',
				'title' => __('Allow to manage order using another browser','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => $premiumbox->get_option('exchange','allow_dev'),
				'name' => 'allow_dev',
			);	
			$options[] = array(
				'view' => 'line',
			);
			$exsum = array(
				'0' => __('Amount To send','pn'),
				'1' => __('Amount To send with add. fees','pn'),
				'2' => __('Amount To send with add. fees and PS fees','pn'),
				'6' => __('Amount To send for reserve','pn'),
				'3' => __('Amount Receive','pn'),
				'4' => __('Amount To receive with add. fees','pn'),
				'5' => __('Amount To receive with add. fees and PS fees','pn'),
				'7' => __('Amount To receive for reserve','pn'),
			);	
			$options['exch_exsum'] = array(
				'view' => 'select',
				'title' => __('Amount needed to be exchanged is','pn'),
				'options' => $exsum,
				'default' => $premiumbox->get_option('exchange','exch_exsum'),
				'name' => 'exch_exsum',
			);	
			$options['admin_mail'] = array(
				'view' => 'select',
				'title' => __('Send e-mail notifications to admin if admin changes status of order on his own','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => $premiumbox->get_option('exchange','admin_mail'),
				'name' => 'admin_mail',
			);				
			$options[] = array(
				'view' => 'line',
			);				
			$options[] = array(
				'view' => 'h3',
				'title' => '',
				'submit' => __('Save','pn'),
			);
			$options[] = array(
				'view' => 'line',
			);
			$options['an1_hidden'] = array(
				'view' => 'select',
				'title' => __('Data visibility in order for Sending','pn'),
				'options' => array('0'=>__('do not show data','pn'),'1'=>__('hide data','pn'),'2'=>__('do not hide first 4 symbols','pn'),'3'=>__('do not hide last 4 symbols','pn'),'4'=>__('do not hide first 4 symbols and the last 4 symbols','pn')),
				'default' => $premiumbox->get_option('exchange','an1_hidden'),
				'name' => 'an1_hidden',
			);
			$options['an2_hidden'] = array(
				'view' => 'select',
				'title' => __('Data visibility in order for Receiving','pn'),
				'options' => array('0'=>__('do not show data','pn'),'1'=>__('hide data','pn'),'2'=>__('do not hide first 4 symbols','pn'),'3'=>__('do not hide last 4 symbols','pn'),'4'=>__('do not hide first 4 symbols and the last 4 symbols','pn')),
				'default' => $premiumbox->get_option('exchange','an2_hidden'),
				'name' => 'an2_hidden',
			);
			$options['an_hidden'] = array(
				'view' => 'select',
				'title' => __('Data visibility in order for personal information','pn'),
				'options' => array('0'=>__('do not show data','pn'),'1'=>__('hide data','pn'),'2'=>__('do not hide first 4 symbols','pn'),'3'=>__('do not hide last 4 symbols','pn'),'4'=>__('do not hide first 4 symbols and the last 4 symbols','pn')),
				'default' => $premiumbox->get_option('exchange','an_hidden'),
				'name' => 'an_hidden',
			);	
			$options[] = array(
				'view' => 'line',
			);				
			$options['flysum'] = array(
				'view' => 'select',
				'title' => __('Calculate "in an instant"','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => $premiumbox->get_option('exchange','flysum'),
				'name' => 'flysum',
			);
			$options['reservdopcom'] = array(
				'view' => 'select',
				'title' => __('Disable adding additional commission to reserve','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => $premiumbox->get_option('exchange','reservdopcom'),
				'name' => 'reservdopcom',
			);			
			$options[] = array(
				'view' => 'line',
			);		
			$options['mhead_style'] = array(
				'view' => 'select',
				'title' => __('Style of page header used for redirecting','pn'),
				'options' => array('0'=>__('White style','pn'),'1'=>__('Black style','pn')),
				'default' => $premiumbox->get_option('exchange','mhead_style'),
				'name' => 'mhead_style',
			);
			$options[] = array(
				'view' => 'line',
			);
			$options['m_ins'] = array(
				'view' => 'select',
				'title' => __('If there are no payment instructions given to merchant then','pn'),
				'options' => array('0'=>__('Nothing to be shown','pn'),'1'=>__('Show relevant payment instructions of exchange direction','pn')),
				'default' => $premiumbox->get_option('exchange','m_ins'),
				'name' => 'm_ins',
			);
			$options['mp_ins'] = array(
				'view' => 'select',
				'title' => __('If there are no instructions for automatic payments mode then','pn'),
				'options' => array('0'=>__('Nothing to be shown','pn'),'1'=>__('Show relevant payment instructions of exchange direction','pn')),
				'default' => $premiumbox->get_option('exchange','mp_ins'),
				'name' => 'mp_ins',
			);	
			$options[] = array(
				'view' => 'line',
			);	
			$options['avsumbig'] = array(
				'view' => 'select',
				'title' => __('Make payout if received amount is more than required','pn'),
				'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
				'default' => $premiumbox->get_option('exchange','avsumbig'),
				'name' => 'avsumbig',
			);		
			$options[] = array(
				'view' => 'user_func',
				'func_data' => array(),
				'func' => 'pn_av_option',
			);	

			$params_form = array(
				'filter' => 'pn_exchange_settings_option',
				'method' => 'ajax',
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options);
		
	} 

	function pn_av_option(){
		
		$bid_status_list = apply_filters('bid_status_list',array());
		
		$av_status_button = get_option('av_status_button');
		if(!is_array($av_status_button)){ $av_status_button = array(); }
		
		$av_status_timeout = get_option('av_status_timeout');
		if(!is_array($av_status_timeout)){ $av_status_timeout = array(); }	
					
		$in = array('realpay','verify','payed');
		?>
		
			<div class="premium_standart_line">
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Display button "Transfer" if order status is','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<?php 
						$scroll_lists = array();
						if(is_array($bid_status_list)){
							foreach($bid_status_list as $key => $val){
								if(in_array($key, $in)){
									$checked = 0;
									if(in_array($key,$av_status_button)){
										$checked = 1;
									}
									$scroll_lists[] = array(
										'title' => $val,
										'checked' => $checked,
										'value' => $key,
									);
								}
							}	
						}	
						echo get_check_list($scroll_lists, 'av_status_button[]');				
						?>
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>	
		
			<div class="premium_standart_line">
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Perform payout for frozen orders if status of the order is','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<?php 
						$scroll_lists = array();
						if(is_array($bid_status_list)){
							foreach($bid_status_list as $key => $val){
								if(in_array($key, $in)){
									$checked = 0;
									if(in_array($key,$av_status_timeout)){
										$checked = 1;
									}
									$scroll_lists[] = array(
										'title' => $val,
										'checked' => $checked,
										'value' => $key,
									);
								}
							}	
						}	
						echo get_check_list($scroll_lists, 'av_status_timeout[]');				
						?>
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>	
		<?php				
	}

	add_action('premium_action_pn_exchange_settings','def_premium_action_pn_exchange_settings');
	function def_premium_action_pn_exchange_settings(){
	global $wpdb, $premiumbox;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$av_status_button = array();
		$array = is_param_post('av_status_button');
		if(is_array($array)){
			foreach($array as $v){
				$v = is_status_name($v);
				if($v){
					$av_status_button[] = $v;
				}
			}
		}
		update_option('av_status_button',$av_status_button);	
		
		$av_status_timeout = array();
		$array = is_param_post('av_status_timeout');
		if(is_array($array)){
			foreach($array as $v){
				$v = is_status_name($v);
				if($v){
					$av_status_timeout[] = $v;
				}
			}
		}
		update_option('av_status_timeout',$av_status_timeout);	
		
		$options = array(
			'allow_dev','tablevid','exch_method','tableicon','tablenot','tableselect','exch_exsum','flysum','enable_step2','reservdopcom',
			'admin_mail','an1_hidden','an2_hidden','an_hidden', 
			'mhead_style','m_ins','mp_ins','avsumbig',
			'tablenothome','tableselecthome','hidecurrtype','tableajax',
		);
		foreach($options as $key){
			$val = pn_strip_input(is_param_post($key));
			$premiumbox->update_option('exchange', $key, $val);
		}			
				
		do_action('pn_exchange_settings_option_post');
		
		$url = admin_url('admin.php?page=pn_exchange_settings&reply=true');
		$form->answer_form($url);
	}
}	