<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Qiwi new[:en_US][ru_RU:]Qiwi new[:ru_RU]
description: [en_US:]Qiwi new merchant[:en_US][ru_RU:]мерчант Qiwi new[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_qiwinew')){
	class merchant_qiwinew extends Merchant_Premiumbox { 

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			add_action('init_bid_merchant', array($this,'init_merchant'));
			add_filter('merchant_pay_button', array($this,'pay_button'),99,5);
			add_filter('get_text_pay', array($this,'get_text_pay'), 99, 3);
		}
		
		function get_map(){
			$map = array(
				'API_TOKEN_KEY'  => array(
					'title' => '[en_US:]Token[:en_US][ru_RU:]Токен[:ru_RU]',
					'view' => 'input',	
				),
				'API_WALLET'  => array(
					'title' => '[en_US:]Qiwi wallet number without +[:en_US][ru_RU:]Номер кошелька Qiwi без +[:ru_RU]',
					'view' => 'input',
				),			
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('API_TOKEN_KEY','API_WALLET');
			return $arrs;
		}		

		function options($options, $data, $id, $place){ 
			
			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, array('check_api','enableip'));				
			
			$text = '
			<div><strong>Cron:</strong> <a href="'. get_mlink($id.'_cron' . hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_cron' . hash_url($id)) .'</a></div>			
			';

			$options['private_line'] = array(
				'view' => 'line',
			);			

			$options['qcomment'] = array(
				'view' => 'select',
				'title' => __('Remove parentheses for order ID in description','pn'),
				'options' => array('0'=> __('No','pn'), '1'=> __('Yes','pn')),
				'default' => is_isset($data, 'qcomment'),
				'name' => 'qcomment',
				'work' => 'int',
			);

			$options['vnaccount'] = array(
				'view' => 'select',
				'title' => __('Use wallets from Currency accounts section','pn'),
				'options' => array('0'=> __('No','pn'), '1'=> __('Yes','pn')),
				'default' => is_isset($data, 'vnaccount'),
				'name' => 'vnaccount',
				'work' => 'int',
			);

			$options['providerid'] = array(
				'view' => 'select',
				'title' => __('Payment method','pn'),
				'options' => array('0'=> 'Qiwi Wallet', '1963'=> 'Visa(RU)', '21013'=> 'MasterCard(RU)'),
				'default' => is_isset($data, 'providerid'),
				'name' => 'providerid',
				'work' => 'int',
			);			

			$options[] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;	
		}				

		function get_text_pay($text, $m_id, $item){
			$script = get_mscript($m_id);
			if($script and $script == $this->name){
				$m_data = get_merch_data($m_id);
				$qcomment = intval(is_isset($m_data, 'qcomment'));
				if($qcomment != 1){
					$text = str_replace(array('[id]','[exchange_id]'),'('. $item->id .')', $text);
				}
			}
			return $text;
		}

		function init_merchant($m_id){
			global $bids_data;
			$script = get_mscript($m_id);
			if($script and $script == $this->name and isset($bids_data->id)){
				$m_defin = $this->get_file_data($m_id);
				$m_data = get_merch_data($m_id);
				$vnaccount = intval(is_isset($m_data, 'vnaccount'));
				if($vnaccount != 1){
					$bids_data = update_bid_tb($bids_data->id, 'to_account', is_deffin($m_defin,'API_WALLET'), $bids_data); 
				}
			}	
		}

		function pay_button($merchant_pay_button, $m_in, $sum_to_pay, $now_bids_data, $direction){
			global $bids_data;
			
			$script = get_mscript($m_in);
			if($script and $script == $this->name){
				$m_defin = $this->get_file_data($m_in);
				$m_data = get_merch_data($m_in);
				
				global $wpdb;
				
				$pay_sum = is_sum($sum_to_pay, 2); 
				$comment = get_text_pay($this->name, $bids_data, $pay_sum);						
						
				$vnaccount = intval(is_isset($m_data, 'vnaccount'));
				$providerid = intval(is_isset($m_data, 'providerid'));
				if(!$providerid){ $providerid = 99; }
							
				$qiwi_account = pn_maxf_mb(pn_strip_input(is_isset($bids_data,'to_account')),500);
				$qiwi_account = trim(str_replace('+','',$qiwi_account));
			
				$pay_sum = sprintf("%0.2F",$pay_sum);
				$sum = explode('.',$pay_sum);	

				$currency = 643; 
				//'643'=>'RUB',
				//'840'=>'USD',
				//'978'=>'EUR'					
				
				$merchant_pay_button = '<a href="https://qiwi.com/payment/form/'. $providerid .'?extra%5B%27account%27%5D=+'. $qiwi_account .'&amountInteger='. $sum[0] .'&amountFraction='. $sum[1] .'&extra%5B%27comment%27%5D='. $comment .'&currency='. $currency .'&blocked[0]=account&blocked[1]=comment&blocked[2]=sum" target="_blank" rel="noreferrer noopener" class="success_paybutton">'. __('Make a payment','pn') .'</a>';
			}
			return $merchant_pay_button;			
		}

		function cron($m_id, $m_defin, $m_data){
			global $wpdb;

			$show_error = intval(is_isset($m_data, 'show_error'));
			$qcomment = intval(is_isset($m_data, 'qcomment'));
			
			try {
				$class = new QIWI_API($m_id, is_deffin($m_defin,'API_WALLET'), is_deffin($m_defin,'API_TOKEN_KEY'));
				$orders = $class->get_history(date('c',strtotime('-30 days')),date('c',strtotime('+1 day')), $qcomment);
				$this->logs($orders);
				if(is_array($orders)){
					$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status IN('new','coldpay') AND m_in LIKE '$m_id'");
					foreach($items as $item){
						$currency = $item->currency_code_give;
						$qiwi_account = pn_maxf_mb(pn_strip_input(is_isset($item,'to_account')),500);
						foreach($orders as $order_key => $res){
							$id = $res['trans_id'];
							$res_status = $res['status'];
							$res_st = array('SUCCESS', 'WAITING');
							if($id == $item->id and $res['total_currency_sym'] == $currency and in_array($res_status, $res_st)){
								if($res_status == 'SUCCESS'){
									$set_status = 'realpay';
								} else {
									$set_status = 'coldpay';
								}
			
								$data = get_data_merchant_for_id($id, $item);
								
								$in_sum = $res['sum_amount'];
								$in_sum = is_sum($in_sum,2);
								$err = $data['err'];
								$status = $data['status'];
								$bid_m_id = $data['m_id'];
								$bid_m_script = $data['m_script']; 
								
								$bid_currency = $data['currency'];
								
								$pay_purse = is_pay_purse(is_isset($res, 'account'), $m_data, $m_id);
									
								$bid_sum = is_sum($data['pay_sum'],2);	
								$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $m_id);
								
								$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
								$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
								$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
								$invalid_check = intval(is_isset($m_data, 'check'));								
								
								if($err == 0 and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name){
									if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){
										if(!check_trans_in($bid_m_id, $res['qiwi_id'], $id)){
											
											unset($orders[$order_key]);
											
											$params = array( 
												'pay_purse' => $pay_purse,
												'sum' => $in_sum,
												'bid_sum' => $bid_sum,
												'bid_corr_sum' => $bid_corr_sum,
												'bid_status' => array('new','coldpay'),
												'to_account' => $qiwi_account,
												'trans_in' => $res['qiwi_id'],
												'invalid_ctype' => $invalid_ctype,
												'invalid_minsum' => $invalid_minsum,
												'invalid_maxsum' => $invalid_maxsum,
												'invalid_check' => $invalid_check,
												'm_place' => $m_id,
												'm_id' => $m_id,
												'm_data' => $m_data,
												'm_defin' => $m_defin,
											);
											set_bid_status($set_status, $id, $params, $data['direction_data']);  														
										} else {
											$this->logs($id . ' Error check trans in!');
										}										
									} else {
										$this->logs($id . ' The payment amount is less than the provisions');
									}		 		 
								} else {
									$this->logs($id . ' bid error');
								}
							}
						}
					}
				}
			}
			catch (Exception $e)
			{
				$this->logs($e->getMessage());
				if($show_error and current_user_can('administrator')){
					die($e->getMessage());
				}
			}			
		}
		
	}
}

new merchant_qiwinew(__FILE__, 'Qiwi new');