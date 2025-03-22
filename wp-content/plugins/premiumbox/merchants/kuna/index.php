<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Kuna[:en_US][ru_RU:]Kuna[:ru_RU]
description: [en_US:]Kuna merchant[:en_US][ru_RU:]мерчант Kuna[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_kuna')){
	class merchant_kuna extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$this->curr = array('1'=>'UAH', '42'=>'USD', '43'=> 'RUB');
			
			add_filter('merchant_pay_button', array($this,'pay_button'),99,5);
		}

		function get_map(){
			$map = array(
				'API_KEY'  => array(
					'title' => '[en_US:]Public Key[:en_US][ru_RU:]Публичный ключ[:ru_RU]',
					'view' => 'input',	
				),
				'SECRET_KEY'  => array(
					'title' => '[en_US:]Secret Key[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
					'view' => 'input',
				),					
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('API_KEY','SECRET_KEY');
			return $arrs;
		}

		function options($options, $data, $id, $place){ 
			$m_defin = $this->get_file_data($id);
			
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, array('check_api','pagenote'));
			
			$text = '
			<div><strong>Cron:</strong> <a href="'. get_mlink($id.'_cron' . hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_cron' . hash_url($id)) .'</a></div>			
			';		
			
			$options[] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;	
		}			

		function pay_button($merchant_pay_button, $m_in, $sum_to_pay, $bids_data, $direction){
			$script = get_mscript($m_in);
			if($script and $script == $this->name){
				$m_defin = $this->get_file_data($m_in);
				$m_data = get_merch_data($m_in);
				$show_error = intval(is_isset($m_data, 'show_error'));
			
				$pay_link = trim(get_bids_meta($bids_data->id, 'pay_link'));
				if(!$pay_link){
					$pay_sum = is_sum($sum_to_pay, 2); 
					$currency = mb_strtoupper($bids_data->currency_code_give);
				
					try {
						$class = new Kuna(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin, 'SECRET_KEY'));
						$res = $class->create_order($currency, $pay_sum, get_bids_url($bids_data->hashed));
						if(isset($res['payment_url'], $res['deposit_id'])){
							
							$trans_in = pn_strip_input($res['deposit_id']);
							$bids_data = update_bid_tb($bids_data->id, 'trans_in', $trans_in, $bids_data);
							
							$pay_link = $res['payment_url'];
							update_bids_meta($bids_data->id, 'pay_link', $pay_link);
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

				if($pay_link){
					$merchant_pay_button = '<a href="'. $pay_link .'" target="_blank" rel="noreferrer noopener" class="success_paybutton">'. __('Make a payment','pn') .'</a>';
				} else {
					$merchant_pay_button = '<div class="resultfalse paybutton_error">'. __('Error! Please contact website technical support', 'pn') .'</div>';
				}
			
			}
			return $merchant_pay_button;			
		}

		function cron($m_id, $m_defin, $m_data){
			global $wpdb;

			$show_error = intval(is_isset($m_data, 'show_error'));	
			
			try {
				$class = new Kuna(is_deffin($m_defin,'API_KEY'), is_deffin($m_defin, 'SECRET_KEY'));
				$time = current_time('timestamp');
				$start_time = $time - (2 * DAY_IN_SECONDS);
				$end_time = $time;
				$orders = $class->get_history_orders($start_time, $end_time);
				
				if(is_array($orders)){
					$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'new' AND LENGTH(trans_in) > 1 AND m_in = '$m_id'");
					foreach($items as $item){
						$id = $item->id;
						
						$trans_in = pn_strip_input(is_isset($item,'trans_in'));
						
						if(isset($orders[$trans_in])){
							$res = $orders[$trans_in];
							$res_status = $res['status'];
							$res_currency = $res['currency'];
							$currency = is_isset($this->curr, $res_currency);
							if($res_status == 'done'){
								$data = get_data_merchant_for_id($id, $item);
									
								$in_sum = $res['amount'];
								$in_sum = is_sum($in_sum,2);
								$err = $data['err'];
								$status = $data['status'];
								$bid_m_id = $data['m_id'];
								$bid_m_script = $data['m_script'];  
									
								$bid_currency = $data['currency'];
									
								$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
										
								$bid_sum = is_sum($data['pay_sum'],2);	
								$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
									
								$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
								$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
								$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
								$invalid_check = intval(is_isset($m_data, 'check'));								
									
								if(!check_trans_in($bid_m_id, $res['sn'], $id)){									
									if($err == 0 and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name){
										if($bid_currency == $currency or $invalid_ctype > 0){
											if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){
												$params = array( 
													'pay_purse' => $pay_purse,
													'sum' => $in_sum,
													'bid_sum' => $bid_sum,
													'bid_status' => array('new','techpay','coldpay'),
													'bid_corr_sum' => $bid_corr_sum,
													'currency' => $currency,
													'bid_currency' => $bid_currency,
													'invalid_ctype' => $invalid_ctype,
													'invalid_minsum' => $invalid_minsum,
													'invalid_maxsum' => $invalid_maxsum,
													'invalid_check' => $invalid_check,
													'm_place' => $bid_m_id.'_cron',
													'm_id' => $m_id,
													'm_data' => $m_data,
													'm_defin' => $m_defin,
												);
												set_bid_status('realpay', $id, $params, $data['direction_data']);  														
											} else {
												$this->logs($id . ' The payment amount is less than the provisions');
											}
										} else {
											$this->logs($id.' In the application the wrong status');
										}										
									} else {
										$this->logs($id . ' bid error');
									}
								} else {
									$this->logs($id . ' Error check trans in!');
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

new merchant_kuna(__FILE__, 'Kuna');