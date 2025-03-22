<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]BlockChain[:en_US][ru_RU:]BlockChain[:ru_RU]
description: [en_US:]BlockChain automatic payouts[:en_US][ru_RU:]авто выплаты BlockChain[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_blockchain')){
	class paymerchant_blockchain extends AutoPayut_Premiumbox{
		function __construct($file, $title)
		{
			parent::__construct($file, $title);
		}

		function get_map(){
			$map = array(
				'WALLET'  => array(
					'title' => '[en_US:]Wallet ID (login)[:en_US][ru_RU:]Идентификатор кошелька (логин)[:ru_RU]',
					'view' => 'input',	
				),
				'API_KEY'  => array(
					'title' => '[en_US:]API key[:en_US][ru_RU:]API ключ[:ru_RU]',
					'view' => 'input',
				),
				'PASS'  => array(
					'title' => '[en_US:]Wallet password[:en_US][ru_RU:]Пароль от кошелька[:ru_RU]',
					'view' => 'input',
				),
				'PASS2'  => array(
					'title' => '[en_US:]Second password from send funds confirmation[:en_US][ru_RU:]Второй пароль от подтверждения отправки средств[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('WALLET','API_KEY','PASS');
			return $arrs;
		}

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'error_status');	
			
			$opt = array(
				'0' => __('manually','pn'),
				'1' => __('regular (1+ hour)','pn'),
				'2' => __('priority (0-60 minutes)','pn'),
			);
			$options['bpriority'] = array(
				'view' => 'select',
				'title' => __('Transaction fee','pn'),
				'options' => $opt,
				'default' => intval(is_isset($data, 'bpriority')),
				'name' => 'bpriority',
				'work' => 'int',
			);			
			
			$options['bpriority_num'] = array(
				'view' => 'inputbig',
				'title' => __('Customize fee manually (sat/byte)','pn'),
				'default' => intval(is_isset($data, 'bpriority_num')),
				'name' => 'bpriority_num',
				'work' => 'int',
			);		

			$html = '<div>'. sprintf(__('Click on the <a href="%s" target="_blank" rel="noreferrer noopener">link</a> to see the current transaction fees.','pn'), 'https://api.blockchain.info/mempool/fees') . '</div>';
			
			$options['blimit_help'] = array(
				'view' => 'help',
				'title' => __('Information about transaction fees','pn'),
				'default' => $html,
			);			
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin){
			$purses = array(
				$this->name.'_1' => is_deffin($m_defin,'WALLET'),
			);
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if($purse){
				try {
					$class = new AP_BlockChain(is_deffin($m_defin,'WALLET'), is_deffin($m_defin,'API_KEY'), is_deffin($m_defin,'PASS'), is_deffin($m_defin,'PASS2'));
					$rezerv = $class->get_balans();
					if(is_numeric($rezerv) and $rezerv != '-1'){
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
			
			$item_id = $item->id;			
			$trans_id = 0;				
			
			$from_account = is_deffin($m_defin,'WALLET');
			
			$bpriority = intval(is_isset($paymerch_data,'bpriority'));
			$bpriority_num = intval(is_isset($paymerch_data,'bpriority_num'));
			
			$class = new AP_BlockChain(is_deffin($m_defin,'WALLET'), is_deffin($m_defin,'API_KEY'), is_deffin($m_defin,'PASS'), is_deffin($m_defin,'PASS2'));
			
			$lists = $class->check_priority();
			
			$arr_p = array(
				'0' => $bpriority_num,
				'1' => trim(is_isset($lists, 'regular')),
				'2' => trim(is_isset($lists, 'priority')),
			);
			
			$now_priority = intval(is_isset($arr_p, $bpriority));			
			
			$vtype = mb_strtoupper($item->currency_code_get);

			$enable = array('BTC');
			if(!in_array($vtype, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}						
						
			$account = trim($item->account_get);
					
			if(!$account){
				$error[] = __('Client wallet type does not match with currency code','pn');						
			}
					
			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 12);
		
			if(count($error) == 0){
				$result = $this->set_ap_status($item, $test);
				if($result){	
					try {
						$res = $class->send_money($account, $sum, $now_priority);
						if($res['error'] == 1){
							$error[] = __('Payout error','pn');
							$pay_error = 1;
						} else {
							$trans_id = $res['trans_id'];
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
					'from_account' => $from_account,
					'trans_out' => $trans_id,
					'system' => 'user',
					'm_place' => $modul_place. ' ' . $m_id,
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

new paymerchant_blockchain(__FILE__, 'BlockChain');