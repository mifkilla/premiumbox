<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Yandex money[:en_US][ru_RU:]Yandex money[:ru_RU]
description: [en_US:]Yandex money automatic payouts[:en_US][ru_RU:]авто выплаты Yandex money[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_yamoney')){
	class paymerchant_yamoney extends AutoPayut_Premiumbox{

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);	
			
			add_action('before_paymerchant_admin',array($this,'before_paymerchant_admin'), 10, 3);
			
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_ap_'. $id .'_verify', array($this,'merchant_verify'));
			}			
			
			add_action('ext_paymerchants_delete', array($this, 'del_dostup_files'), 10, 2);
		}
		
		function get_map(){
			$map = array(
				'AP_YANDEX_MONEY_ACCOUNT'  => array(
					'title' => '[en_US:]Account wallet number[:en_US][ru_RU:]Номер кошелька[:ru_RU]',
					'view' => 'input',	
				),
				'AP_YANDEX_MONEY_APP_ID'  => array(
					'title' => '[en_US:]Application ID[:en_US][ru_RU:]Идентификатор приложения[:ru_RU]',
					'view' => 'input',	
				),
				'AP_YANDEX_MONEY_APP_KEY'  => array(
					'title' => '[en_US:]OAuth2[:en_US][ru_RU:]OAuth2[:ru_RU]',
					'view' => 'input',	
				),				
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('AP_YANDEX_MONEY_ACCOUNT');
			return $arrs;
		}	

		function before_paymerchant_admin($now_script, $data, $data_id){
			if($now_script and $now_script == $this->name){ 
				$m_defin = $this->get_file_data($data_id);
				$class = new AP_YaMoney(is_deffin($m_defin,'YANDEX_MONEY_APP_ID'), is_deffin($m_defin,'YANDEX_MONEY_APP_KEY'), $this->name, $data_id);
				$token = $class->get_token();
				if($token){
					echo '<div class="premium_reply pn_success">'. sprintf(__('The application has been authenticated. If necessary, click on the link to <a href="%s" target="_blank" rel="noreferrer noopener">re-authenticate the application</a>.','pn'), get_mlink('ap_'. $data_id .'_verify').'?get_restart=1') . '</div>';
				} else {
					echo '<div class="premium_reply pn_error">'. sprintf(__('For correct operation, <a href="%s" target="_blank" rel="noreferrer noopener">authenticate the application</a>.','pn'), get_mlink('ap_'. $data_id .'_verify')) .'</div>';
				}		
			}
		}
		
		function merchant_verify(){
			$m_id = key_for_url('_verify', 'ap_'); 
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_paymerch_data($m_id);
			
			if(current_user_can('administrator') or current_user_can('pn_merchants')){
				if( isset( $_GET['code'] ) ) {
					$class = new AP_YaMoney(is_deffin($m_defin,'AP_YANDEX_MONEY_APP_ID'), is_deffin($m_defin,'AP_YANDEX_MONEY_APP_KEY'), $this->name, $m_id);
					$token = $class->auth();
					if($token){
						$res = $class->accountInfo($token);
						if(!isset($res['account'])){
							pn_display_mess(__('No data received from the payment system','pn'));
						} elseif($res['account'] != is_deffin($m_defin,'AP_YANDEX_MONEY_ACCOUNT') ){
							pn_display_mess(sprintf(__('Authorization can me made from account %s','pn'), is_deffin($m_defin,'AP_YANDEX_MONEY_ACCOUNT')));
						} else {
							$class->update_token($token);
							wp_redirect(admin_url('admin.php?page=pn_add_paymerchants&item_key='. $m_id .'&reply=true'));
							exit;
						}
					} else {
						pn_display_mess(__('Retry','pn'));
					}
				} else {
					$class = new AP_YaMoney(is_deffin($m_defin,'AP_YANDEX_MONEY_APP_ID'), is_deffin($m_defin,'AP_YANDEX_MONEY_APP_KEY'), $this->name, $m_id);
					$res = $class->accountInfo();
					if( !isset( $res['account'] ) or $res['account'] != is_deffin($m_defin,'AP_YANDEX_MONEY_ACCOUNT') or isset($_GET['get_restart']) and $_GET['get_restart'] == 1){	
						header( 'Location: https://money.yandex.ru/oauth/authorize?client_id='. is_deffin($m_defin,'AP_YANDEX_MONEY_APP_ID') .'&response_type=code&redirect_uri='. urlencode( get_mlink('ap_'. $m_id .'_verify') ) .'&scope=account-info operation-history operation-details payment-p2p payment-shop ');
						exit();	
					} else {	
						pn_display_mess(__('Payment system is configured','pn'), __('Payment system is configured','pn'),'true');	
					}
				}
			} else {
				pn_display_mess(__('Error! Insufficient privileges','pn'));	
			}
		}		

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'error_status');

			$opt = array(
				'0' => __('Account','pn'),
				'1' => __('Card','pn'),
			);			
			$options['variant'] = array(
				'view' => 'select',
				'title' => __('Transaction type','pn'),
				'options' => $opt,
				'default' => intval(is_isset($data, 'variant')),
				'name' => 'variant',
				'work' => 'int',
			);	
			
			$text = '
			<div><strong>'. __('Enter address to create new application','pn') .':</strong> <a href="https://money.yandex.ru/myservices/new.xml" target="_blank">https://money.yandex.ru/myservices/new.xml</a>.</div>
			<div><strong>Redirect URI:</strong> <a href="'. get_mlink('ap_'. $id .'_verify') .'" target="_blank">'. get_mlink('ap_'. $id .'_verify') .'</a></div>				
			';
			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);		
			
			return $options;
		}					

		function get_reserve_lists($m_id, $m_defin){
			$list = array();
			$list[$m_id.'_1'] = is_deffin($m_defin,'AP_YANDEX_MONEY_ACCOUNT');
			return $list;									
		}

		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			if($code == $m_id.'_1'){	
				try {
					
					$oClass = new AP_YaMoney(is_deffin($m_defin,'AP_YANDEX_MONEY_APP_ID'), is_deffin($m_defin,'AP_YANDEX_MONEY_APP_KEY'), $this->name, $m_id);
					$res = $oClass->accountInfo();
					if(is_array($res) and isset($res['balance'])){
								
						$rezerv = trim((string)$res['balance']);
						$sum = $rezerv;						
								
					} 
						
				}
				catch (Exception $e)
				{
							
				} 				
			}
			
			return $sum;			
		}		

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){
			$item_id = $item->id;
			$trans_id = 0;			
			
			$variant = intval(is_isset($paymerch_data, 'variant'));
			
			$vtype = mb_strtoupper($item->currency_code_get);
			$vtype = str_replace('RUR','RUB',$vtype);
					
			$enable = array('RUB');
			if(!in_array($vtype, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}						
						
			$account = $item->account_get;
			$account = mb_strtoupper($account);
			if (!preg_match("/^[0-9]{5,20}$/", $account, $matches )) {
				$error[] = __('Client wallet type does not match with currency code','pn');
			}							

			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
					
			if(count($error) == 0){

				$result = $this->set_ap_status($item, $test);				
				if($result){				
					
					$notice = get_text_paymerch($m_id, $item);
					if(!$notice){ $notice = sprintf(__('ID order %s','pn'), $item->id); }
					$notice = trim(pn_maxf($notice,150));
						
					try {
						$oClass = new AP_YaMoney(is_deffin($m_defin,'AP_YANDEX_MONEY_APP_ID'), is_deffin($m_defin,'AP_YANDEX_MONEY_APP_KEY'), $this->name, $m_id);
						
						if($variant == 0){
						
							$reguest_id = $oClass->addPay($account, $sum, 2, $notice, $item->id);
							if($reguest_id){
								$trans_id = $reguest_id;
								$res = $oClass->processPay($reguest_id);
								if($res['error'] == 1){
									$error[] = __('Payout error','pn');
									$pay_error = 1;
								} else {
									$trans_id = $res['payment_id'];
								}
							} else {
								$error[] = 'Error interfaice';
								$pay_error = 1;
							} 	

						} else {
							
							$card_key = $oClass->get_card_key($account);
							if($card_key){
								$reguest_id = $oClass->requestPay($card_key, $sum, 2);
								if($reguest_id){
									$trans_id = $reguest_id;
									$res = $oClass->processPay($reguest_id);
									if($res['error'] == 1){
										$error[] = __('Payout error','pn');
										$pay_error = 1;
									} else {
										$trans_id = $res['payment_id'];
									}
								} else {
									$error[] = 'Error interfaice (requestPay)';
									$pay_error = 1;
								} 	
							} else {
								$error[] = 'Error interfaice (get_card_key)';
								$pay_error = 1;
							}
							
						}
							
					}
					catch (Exception $e)
					{
						$error[] = $e;
						$pay_error = 1;
					} 
							
				} else {
					$error[] = 'Database error';
				}						
									
			}
					
			if(count($error) > 0){
				$this->reset_ap_status($error, $pay_error, $item, $place, $test);
			} else {
						
				$params = array(
					'from_account' => is_deffin($m_defin,'AP_YANDEX_MONEY_ACCOUNT'),
					'trans_out' => $trans_id,
					'system' => 'user',
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('success', $item_id, $params, $direction);	 					
						 
				if($place == 'admin'){
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
				}
						
			}			
		}

		function del_dostup_files($script, $id){
			if($script == 'yamoney'){
				global $premiumbox;
				$file = $premiumbox->plugin_dir . 'paymerchants/'. $script .'/dostup/access_token_'. $id .'.php';
				if(file_exists($file)){
					@unlink($file);
				}
			}
		}		
	}
}

new paymerchant_yamoney(__FILE__, 'Yandex money');