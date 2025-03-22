<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_x19_config', 'def_adminpage_title_pn_x19_config');
	function def_adminpage_title_pn_x19_config($page){
		_e('X19','pn');
	} 

	add_action('pn_adminpage_content_pn_x19_config','def_adminpage_content_pn_x19_config');
	function def_adminpage_content_pn_x19_config(){	
	global $premiumbox;

		$form = new PremiumForm();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('WMID ownership verification','pn'),
			'submit' => __('Test','pn'),
		);
		$options['purse'] = array(
			'view' => 'inputbig',
			'title' => __('Webmoney account', 'pn'),
			'default' => '',
			'name' => 'purse',
		);	
		$params_form = array(
			'filter' => 'pn_x19_config_test',
			'method' => 'ajax',
			'form_link' => pn_link('x19_test_wmid','post'),
			'button_title' => __('Test','pn'),
		);
		$form->init_form($params_form, $options);	

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Test X19','pn'),
			'submit' => __('Test','pn'),
		);
		$options['account1'] = array(
			'view' => 'inputbig',
			'title' => __('Account To send', 'pn'),
			'default' => '',
			'name' => 'account1',
		);
		$options['account2'] = array(
			'view' => 'inputbig',
			'title' => __('Account To receive', 'pn'),
			'default' => '',
			'name' => 'account2',
		);
		$options['last_name'] = array(
			'view' => 'inputbig',
			'title' => __('Last name', 'pn'),
			'default' => '',
			'name' => 'last_name',
		);
		$options['first_name'] = array(
			'view' => 'inputbig',
			'title' => __('First name', 'pn'),
			'default' => '',
			'name' => 'first_name',
		);
		$options['passport'] = array(
			'view' => 'inputbig',
			'title' => __('Passport number', 'pn'),
			'default' => '',
			'name' => 'passport',
		);
			$array = array(
				'1' => __('Cash','pn') .' -> '. __('Webmoney','pn'),
				'2' => __('Bank account','pn') .' -> '. __('Webmoney','pn'),
				'3' => __('Bank card','pn') .' -> '. __('Webmoney','pn'),
				'4' => __('Money transfer system','pn') .' -> '. __('Webmoney','pn'),
				'5' => __('SMS','pn') .' -> '. __('Webmoney','pn'),
				'6' => __('Webmoney','pn') .' -> '. __('Cash','pn'),
				'7' => __('Webmoney','pn') .' -> '. __('Bank account','pn'),
				'8' => __('Webmoney','pn') .' -> '. __('Bank card','pn'),
				'9' => __('Webmoney','pn') .' -> '. __('Money transfer system','pn'),
				'10' => __('PayPal','pn') .' -> '. __('Webmoney','pn'),
				'11' => __('Skrill (Moneybookers)','pn') .' -> '. __('Webmoney','pn'),
				'12' => __('QIWI','pn') .' -> '. __('Webmoney','pn'),
				'13' => __('Yandex money','pn') .' -> '. __('Webmoney','pn'),
				'14' => __('EasyPay','pn') .' -> '. __('Webmoney','pn'),
				'15' => __('Webmoney','pn') .' -> '. __('PayPal','pn'),
				'16' => __('Webmoney','pn') .' -> '. __('Skrill (Moneybookers)','pn'),
				'17' => __('Webmoney','pn') .' -> '. __('QIWI','pn'),
				'18' => __('Webmoney','pn') .' -> '. __('Yandex money','pn'),
				'19' => __('Webmoney','pn') .' -> '. __('EasyPay','pn'),
				'20' => __('Webmoney','pn') .' -> '. __('Webmoney','pn'),
				'21' => __('Webmoney','pn') .' -> '. __('Bitcoin','pn'),
			);	
		$options['mode'] = array(
			'view' => 'select',
			'title' => __('Status','pn'),
			'options' => $array,
			'default' => '',
			'name' => 'mode',
		);	
		$params_form = array(
			'filter' => 'pn_x19_config_test_mod',
			'method' => 'ajax',
			'form_link' => pn_link('x19_test_mod','post'),
			'button_title' => __('Test','pn'),
		);
		$form->init_form($params_form, $options);


		$options = array();
		$options['sbottom_title'] = array(
			'view' => 'h3',
			'title' => __('X19 logs','pn'),
			'submit' => __('Save','pn'),
		);
		$options['addlog'] = array(
			'view' => 'select',
			'title' => __('Write logs','pn'),
			'options' => array('0'=>__('No','pn'),'1'=>__('Yes','pn')),
			'default' => $premiumbox->get_option('x19', 'logs'),
			'name' => 'addlog',
		);
		
		$logs = get_array_option($premiumbox, 'x19_logs');
		
		$options['logs'] = array(
			'view' => 'textarea',
			'title' => __('Logs','pn'),
			'default' => join("\n", $logs),
			'name' => 'logs',
			'rows' => '30',
			'work' => 'text',
		);		
		
		$options['dellog'] = array(
			'view' => 'checkbox',
			'second_title' => __('Delete logs','pn'),
			'value' => 1,
			'name' => 'dellog',
		);		
		$params_form = array(
			'filter' => 'pn_x19_logs_settings',
			'method' => 'ajax',
			'form_link' => pn_link('x19_logs_settings','post'),
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);
	} 
	
	add_action('premium_action_x19_logs_settings','def_premium_action_x19_logs_settings');
	function def_premium_action_x19_logs_settings(){
	global $wpdb, $premiumbox;
	
		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$addlog = intval(is_param_post('addlog'));
		$premiumbox->update_option('x19', 'logs', $addlog);
		
		$dellog = intval(is_param_post('dellog'));
		if($dellog){
			update_array_option($premiumbox, 'x19_logs', array());
		}
		
		$back_url = is_param_post('_wp_http_referer');
		$back_url .= '&reply=true';
				
		$form->answer_form($back_url);
	}	

	add_action('premium_action_x19_test_mod','def_premium_action_x19_test_mod');
	function def_premium_action_x19_test_mod(){
	global $wpdb;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));

		$x19mod = intval(is_param_post('mode'));
		$passport = pn_maxf_mb(pn_strip_input(is_param_post('passport')),250);
		if($x19mod == 1 or $x19mod == 6){
			if(!$passport){
				$form->error_form(__('You have not specified passport data','pn'));
			}
		}
			
		$schet1 = pn_maxf_mb(pn_strip_input(is_param_post('account1')),250);
		if(!$schet1){
			$form->error_form(__('You have not specified your account Send','pn'));
		}
				
		$schet2 = pn_maxf_mb(pn_strip_input(is_param_post('account2')),250);
		if(!$schet2){
			$form->error_form(__('You have not specified your account Receive','pn'));
		}
				
		$last_name = pn_maxf_mb(pn_strip_input(is_param_post('last_name')),250);
		$first_name = pn_maxf_mb(pn_strip_input(is_param_post('first_name')),250);
			
		if($x19mod > 0){
			$arrwm1 = array(6,7,8,9,15,16,17,18,19,20,21);
					
			if(in_array($x19mod,$arrwm1)){
				$account1 = $schet1;
				$account2 = $schet2;
			} else {
				$account1 = $schet2;
				$account2 = $schet1;
			}
					
			$pursetype = 'WM'.mb_strtoupper(mb_substr($account1,0,1));
			
			$object = WMXI_X19();
			if(is_object($object)){
				
				$darr = wmid_with_purse($object, $account1);
				$wmid = $darr['wmid'];
				
				if($wmid){
		
					$amount = 100; 

					$bank_name = '';
					$bank_account = '';
					$card_number = ''; 
					$emoney_name = '';
					$emoney_id = '';
					$phone = '';
					$pnomer = '';
					$crypto_name='';
					$crypto_address='';
							
					if(!$last_name){
						$form->error_form(__('You have not specified your last name','pn'));
					}
							
					if(!$first_name){
						$form->error_form(__('You have not specified your first name','pn'));
					}
						
					if($x19mod == 1){ /* Наличные в офисе -> WM */
						$type = 1;
						$direction = 2;
						$pnomer = $passport;
					} elseif($x19mod == 2){ /* Банковский счет -> WM */ 
						$type = 3;
						$direction = 2;						
						$bank_name = __('Sberbank RF','pn');
						$bank_account = $schet1;					
					} elseif($x19mod == 3){ /* Банковская карта -> WM */ 
						$type = 4;
						$direction = 2;						
						$bank_name = __('Sberbank RF','pn');
						$card_number = $schet1;					
					} elseif($x19mod == 4){ /* Системы денежных переводов -> WM */
						$type = 2;
						$direction = 2;					
					} elseif($x19mod == 5){ /* SMS -> WM */
						$type = 6;
						$direction = 2;
						$phone = is_phone($schet1);	
					} elseif($x19mod == 6){ /* WM -> Наличные в офисе */
						$type = 1;
						$direction = 1;
						$pnomer = $passport;
					} elseif($x19mod == 7){ /* WM -> Банковский счет */
						$type = 3;
						$direction = 1;
						$bank_name = __('Sberbank RF','pn');
						$bank_account = $schet2;					
					} elseif($x19mod == 8){ /* WM -> Банковская карта */
						$type = 4;
						$direction = 1;
						$bank_name = __('Sberbank RF','pn');
						$card_number = $schet2;					
					} elseif($x19mod == 9){ /* WM -> Системы денежных переводов */
						$type = 2;
						$direction = 1;					
					} elseif($x19mod == 10){ /* PayPal -> WM */
						$type = 5;
						$direction = 2; 
						$emoney_name = 'paypal.com';
						$emoney_id = $schet1;					
					} elseif($x19mod == 11){ /* Skrill (Moneybookers) -> WM */
						$type = 5;
						$direction = 2; 
						$emoney_name = 'moneybookers.com';
						$emoney_id = $schet1;					
					} elseif($x19mod == 12){ /* QIWI Кошелёк -> WM */
						$type = 5;
						$direction = 2; 
						$emoney_name = 'qiwi.ru';
						$emoney_id = is_phone($schet1);					
					} elseif($x19mod == 13){ /* Яндекс.Деньги -> WM */
						$type = 5;
						$direction = 2; 
						$emoney_name = 'money.yandex.ru';
						$emoney_id = $schet1;				
					} elseif($x19mod == 14){ /* EasyPay -> WM */
						$type = 5;
						$direction = 2; 
						$emoney_name = 'easypay.by';
						$emoney_id = $schet1;	
					} elseif($x19mod == 15){ /* WM -> PayPal */
						$type = 5;
						$direction = 1; 
						$emoney_name = 'paypal.com';
						$emoney_id = $schet2;					
					} elseif($x19mod == 16){ /* WM -> Skrill (Moneybookers) */
						$type = 5;
						$direction = 1; 
						$emoney_name = 'moneybookers.com';
						$emoney_id = $schet2;					
					} elseif($x19mod == 17){ /* WM -> QIWI Кошелёк */
						$type = 5;
						$direction = 1; 
						$emoney_name = 'qiwi.ru';
						$emoney_id = is_phone($schet2);					
					} elseif($x19mod == 18){ /* WM -> Яндекс.Деньги */
						$type = 5;
						$direction = 1; 
						$emoney_name = 'money.yandex.ru';
						$emoney_id = $schet2;					
					} elseif($x19mod == 19){ /* WM -> EasyPay */
						$type = 5;
						$direction = 1; 
						$emoney_name = 'easypay.by';
						$emoney_id = $schet2;
					} elseif($x19mod == 21){ /* WM -> Bitcoin */
						$type = 8;
						$direction = 1; 
						$crypto_name = 'bitcoin';
						$crypto_address = $schet2;						
					}
						
					if($x19mod == 20){
						$darr2 = wmid_with_purse($object, $account2);
						$wmid2 = $darr2['wmid'];
						if($wmid2){
							if($wmid != $wmid2){
								$form->error_form(__('Owner own several accounts','pn'));
							} else {
								$form->error_form(__('Owner own several accounts','pn'), 'true');
							}
						} else {
							$form->error_form($darr2['result']);
						}
					} else {	
						try{
							$res = $object->X19($type, $direction, $pursetype, $amount, $wmid, $pnomer, $last_name, $first_name, $bank_name, $bank_account, $card_number, $emoney_name, $emoney_id, $phone, $crypto_name, $crypto_address)->toArray();
							$form->error_form(print_r($res, true));	
						} catch(Exception $e){
							$form->error_form(print_r($e->getMessage(), true));
						}	
					}	
				} else {
					$form->error_form($darr['result']);
				}
			} else {
				$form->error_form('not constant');
			}				
		} else {
			$form->error_form(__('No access to X19 interface. Check settings','pn'));
		}		
	}

	add_action('premium_action_x19_test_wmid','def_premium_action_x19_test_wmid');
	function def_premium_action_x19_test_wmid(){
	global $wpdb;	
	
		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$object = WMXI_X19();
		if(is_object($object)){
			$purse = pn_maxf_mb(pn_strip_input(is_param_post('purse')),250);
			$darr = wmid_with_purse($object, $purse);
			$wmid = $darr['wmid'];
			if($wmid){
				$form->error_form($wmid, 1);
			} else {
				$form->error_form($darr['result']);
			}
		} else {
			$form->error_form(__('No access to X19 interface. Check settings','pn'));
		}		
	}	
}	