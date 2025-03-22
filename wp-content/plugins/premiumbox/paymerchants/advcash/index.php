<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]AdvCash[:en_US][ru_RU:]AdvCash[:ru_RU]
description: [en_US:]AdvCash automatic payouts[:en_US][ru_RU:]авто выплаты AdvCash[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_advcash')){
	class paymerchant_advcash extends AutoPayut_Premiumbox{
		function __construct($file, $title)
		{
			parent::__construct($file, $title);					
		}

		function get_map(){
			$map = array(
				'API_NAME'  => array(
					'title' => '[en_US:]API name[:en_US][ru_RU:]Имя API[:ru_RU]',
					'view' => 'input',	
				),
				'ACCOUNT_EMAIL'  => array(
					'title' => '[en_US:]Account e-mail[:en_US][ru_RU:]E-mail аккаунта[:ru_RU]',
					'view' => 'input',
				),
				'API_PASSWORD'  => array(
					'title' => '[en_US:]Password API[:en_US][ru_RU:]Пароль API[:ru_RU]',
					'view' => 'input',
				),
				'U_WALLET'  => array(
					'title' => '[en_US:]U wallet nubmer (without spaces)[:en_US][ru_RU:]U номер кошелька (без пробелов)[:ru_RU]',
					'view' => 'input',
				),
				'E_WALLET'  => array(
					'title' => '[en_US:]E wallet nubmer (without spaces)[:en_US][ru_RU:]E номер кошелька (без пробелов)[:ru_RU]',
					'view' => 'input',
				),
				'R_WALLET'  => array(
					'title' => '[en_US:]R wallet nubmer (without spaces)[:en_US][ru_RU:]R номер кошелька (без пробелов)[:ru_RU]',
					'view' => 'input',
				),
				'G_WALLET'  => array(
					'title' => '[en_US:]G wallet nubmer (without spaces)[:en_US][ru_RU:]G номер кошелька (без пробелов)[:ru_RU]',
					'view' => 'input',
				),
				'H_WALLET'  => array(
					'title' => '[en_US:]H wallet nubmer (without spaces)[:en_US][ru_RU:]H номер кошелька (без пробелов)[:ru_RU]',
					'view' => 'input',
				),
				'T_WALLET'  => array(
					'title' => '[en_US:]T wallet nubmer (without spaces)[:en_US][ru_RU:]T номер кошелька (без пробелов)[:ru_RU]',
					'view' => 'input',
				),
				'B_WALLET'  => array(
					'title' => '[en_US:]B wallet nubmer (without spaces)[:en_US][ru_RU:]B номер кошелька (без пробелов)[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('API_NAME','ACCOUNT_EMAIL','API_PASSWORD');
			return $arrs;
		}	

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'error_status');
			
			$n_options[] = array(
				'view' => 'warning',
				'default' => sprintf(__('Use only latin symbols in payment notes. Maximum: %s characters.','pn'), 100),
			);		
			$opts = array(
				'0' => __('Wallet','pn'),
				'1' => __('E-mail','pn'),
				'2' => __('Bitcoin','pn'),
				'3' => __('Capitalist','pn'),
				'4' => __('Ecoin','pn'),
				'6' => __('Paxum','pn'),
				'7' => __('Payeer','pn'),
				'8' => __('Perfect Money','pn'),
				'9' => __('Webmoney','pn'),
				'10' => __('Qiwi','pn'),
				'11' => __('Yandex Money','pn'),
				'13' => __('AdvCash Card Virtual','pn'),
				'14' => __('AdvCash Card Plastic','pn'),
			);
			$n_options['methodpay'] = array(
				'view' => 'select',
				'title' => __('Transaction type','pn'),
				'options' => $opts,
				'default' => intval(is_isset($data, 'methodpay')),
				'name' => 'methodpay',
				'work' => 'int',
			);					
			$options = pn_array_insert($options, 'note', $n_options); 
			
			return $options;
		}	

		function get_reserve_lists($m_id, $m_defin){
			$purses = array(
				$m_id.'_1' => is_deffin($m_defin,'U_WALLET'),
				$m_id.'_2' => is_deffin($m_defin,'E_WALLET'),
				$m_id.'_3' => is_deffin($m_defin,'R_WALLET'),
				$m_id.'_4' => is_deffin($m_defin,'G_WALLET'),
				$m_id.'_5' => is_deffin($m_defin,'H_WALLET'),
				$m_id.'_6' => is_deffin($m_defin,'T_WALLET'),
				$m_id.'_7' => is_deffin($m_defin,'B_WALLET'),
			);	
			return $purses; 
		}

		function update_reserve($code, $m_id, $m_defin){
			$sum = 0;
			
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if($purse){
				try {
						
					$merchantWebService = new MerchantWebService();
					$arg0 = new authDTO();
					$arg0->apiName = is_deffin($m_defin,'API_NAME');
					$arg0->accountEmail = is_deffin($m_defin,'ACCOUNT_EMAIL');
					$arg0->authenticationToken = $merchantWebService->getAuthenticationToken(is_deffin($m_defin,'API_PASSWORD'));

					$getBalances = new getBalances();
					$getBalances->arg0 = $arg0;					
					
					$getBalancesResponse = $merchantWebService->getBalances($getBalances);
	
					$this->logs($getBalancesResponse);
	
					$balances = array();
					if(is_object($getBalancesResponse) and isset($getBalancesResponse->return) and is_array($getBalancesResponse->return)){
						foreach($getBalancesResponse->return as $item){
							$id = trim((string)$item->id);
							$amount = trim((string)$item->amount);
							$balances[$id] = $amount;
						}
					}					
					
					$rezerv = '-1';
								
					foreach($balances as $pursename => $amount){
						if($pursename == $purse){
							$rezerv = trim((string)$amount);
							break;
						}
					}
								
					if($rezerv != '-1'){
						$sum = $rezerv;
					}						
				}
				catch (Exception $e)
				{
					$this->logs($e->getMessage());		
				}
			}			
						
			return $sum;
		}		
		
		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){

			$trans_id = 0;
			$item_id = $item->id;
			
			$currency = mb_strtoupper($item->currency_code_get);
			$currency = str_replace(array('GBP'),'G',$currency);
			$currency = str_replace(array('USD'),'U',$currency);
			$currency = str_replace(array('EUR'),'E',$currency);
			$currency = str_replace(array('RUR','RUB'),'R',$currency);
			$currency = str_replace(array('UAH'),'H',$currency);
			$currency = str_replace(array('KZT'),'T',$currency);
			$currency = str_replace(array('BRL'),'B',$currency);
					
			$send_type = mb_strtoupper($item->currency_code_get);
			$send_type = str_replace(array('RUR','RUB'),'RUR',$send_type);
					
			$enable = array('G','U','E','R','H','T','B');
			if(!in_array($currency, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}	
					
			$account = $item->account_get;
					
			$method_pay = intval(is_isset($paymerch_data, 'methodpay'));
					
			$em_checks = array(1,13,14);
			if(in_array($method_pay, $em_checks)){
				if(!is_email($account)){
					$error[] = __('Client wallet type does not match with currency code','pn');
				}							
			} elseif($method_pay == 0) {
				$account = mb_strtoupper($account);
				if(!preg_match("/^{$currency}[0-9]{0,20}$/", $account, $matches )) {
					$error[] = __('Client wallet type does not match with currency code','pn');
				}	
			}
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
	
			if(count($error) == 0){
					
				$result = $this->set_ap_status($item, $test);	
				if($result){
					
					$notice = get_text_paymerch($m_id, $item);
					if(!$notice){ $notice = sprintf(__('ID order %s','pn'), $item->id); }
					$notice = trim(pn_maxf($notice,100));
						
					try {
						$merchantWebService = new MerchantWebService();
						$arg0 = new authDTO();
						$arg0->apiName = is_deffin($m_defin,'API_NAME');
						$arg0->accountEmail = is_deffin($m_defin,'ACCOUNT_EMAIL');
						$arg0->authenticationToken = $merchantWebService->getAuthenticationToken(is_deffin($m_defin,'API_PASSWORD'));					
						
						if($method_pay == 0 or $method_pay == 1){
									
							$arg1 = new sendMoneyRequest();
							$arg1->amount = $sum;
							$arg1->currency = $send_type;
							if($method_pay){
								$arg1->email = $account;
							} else {
								$arg1->walletId = $account;
							}
							$arg1->note = $notice;
							$arg1->savePaymentTemplate = false;

							$validationSendMoney = new validationSendMoney();
							$validationSendMoney->arg0 = $arg0;
							$validationSendMoney->arg1 = $arg1;

							$sendMoney = new sendMoney();
							$sendMoney->arg0 = $arg0;
							$sendMoney->arg1 = $arg1;
							$merchantWebService->validationSendMoney($validationSendMoney);
							$Response = $merchantWebService->sendMoney($sendMoney);
									
						} elseif($method_pay == 13 or $method_pay == 14){
									
							$cardType = 'VIRTUAL';
							if($method_pay == 14){
								$cardType = 'PLASTIC';
							}
									
							$arg1 = new advcashCardTransferRequest();
							$arg1->amount = $sum;
							$arg1->currency = $send_type;
							$arg1->email = $account;
							$arg1->cardType = $cardType;
							$arg1->note = $notice;
							$arg1->savePaymentTemplate = false;

							$validationSendMoneyToAdvcashCard = new validationSendMoneyToAdvcashCard();
							$validationSendMoneyToAdvcashCard->arg0 = $arg0;
							$validationSendMoneyToAdvcashCard->arg1 = $arg1;

							$sendMoneyToAdvcashCard = new sendMoneyToAdvcashCard();
							$sendMoneyToAdvcashCard->arg0 = $arg0;
							$sendMoneyToAdvcashCard->arg1 = $arg1;
									
							$merchantWebService->validationSendMoneyToAdvcashCard($validationSendMoneyToAdvcashCard);
							$Response = $merchantWebService->sendMoneyToAdvcashCard($sendMoneyToAdvcashCard);
									
						} else {
									
							$ecurrencies = array(
								'2' => 'BITCOIN',
								'3' => 'CAPITALIST',
								'4' => 'ECOIN',
								//'5' => 'OKPAY',
								'6' => 'PAXUM',
								'7' => 'PAYEER',
								'8' => 'PERFECT_MONEY',
								'9' => 'WEB_MONEY',
								'10' => 'QIWI',
								'11' => 'YANDEX_MONEY',	
								//'12' => 'PAYZA',
							);
										
							$ecurrency = is_isset($ecurrencies, $method_pay);	
									
							$arg1 = new withdrawToEcurrencyRequest();
							$arg1->amount = $sum;
							//$arg1->btcAmount = 0.01;
							$arg1->currency = $send_type;
							$arg1->ecurrency = $ecurrency;
							$arg1->receiver = $account;
							$arg1->note = $notice;
							$arg1->savePaymentTemplate = true;

							$validationSendMoneyToEcurrency = new validationSendMoneyToEcurrency();
							$validationSendMoneyToEcurrency->arg0 = $arg0;
							$validationSendMoneyToEcurrency->arg1 = $arg1;

							$sendMoneyToEcurrency = new sendMoneyToEcurrency();
							$sendMoneyToEcurrency->arg0 = $arg0;
							$sendMoneyToEcurrency->arg1 = $arg1;
							$merchantWebService->validationSendMoneyToEcurrency($validationSendMoneyToEcurrency);
							$Response = $merchantWebService->sendMoneyToEcurrency($sendMoneyToEcurrency);
									
						}
						
						if(is_object($Response) and isset($Response->return)){
							$trans_id = trim((string)$Response->return);
						} else {
							$error[] = __('Payout error','pn') . print_r($Response, true);
							$pay_error = 1;								
						}
							
					}
					catch (Exception $e)
					{
						$error[] = $e->getMessage();
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
	}
}

new paymerchant_advcash(__FILE__, 'AdvCash');