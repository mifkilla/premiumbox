<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]BitexBook[:en_US][ru_RU:]BitexBook[:ru_RU]
description: [en_US:]BitexBook merchant[:en_US][ru_RU:]мерчант BitexBook[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_bitexbook')){
	class merchant_bitexbook extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status', array($this,'merchant_status'));
			}
		}	
		
		function get_map(){
			$map = array(
				'TOKEN'  => array(
					'title' => '[en_US:]API token[:en_US][ru_RU:]API токен[:ru_RU]',
					'view' => 'input',	
				),			
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('TOKEN');
			return $arrs;
		}			

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'check_api');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'help_resulturl');
			$options = pn_array_unset($options, 'enableip');
			
			return $options;
		}		

  		function bidaction($temp, $m_id, $pay_sum, $item, $direction){ 
		global $bids_data;	
			$script = get_mscript($m_id);
			if($script and $script == $this->name){
			
				$err = is_param_get('err');
				
				if($err == '1'){ 
					$temp .= $this->zone_error(__('You have not entered a coupon code or pin','pn'));
				} 
				if($err == '2'){
					$temp .= $this->zone_error(__('API error','pn'));
				}				
				if($err == '3'){
					$temp .= $this->zone_error(__('Coupon is not valid','pn'));
				} 
				if($err == '4'){ 
					$temp .= $this->zone_error(__('Coupon amount does not match the required amount','pn'));
				} 
				if($err == '5'){
					$temp .= $this->zone_error(__('Coupon currency code does not match the required currency','pn'));	
				} 			
					
				$pagenote = get_pagenote($m_id, $bids_data, $pay_sum);	
				if(!$pagenote){
					$pagenote = __('In order to pay an ID order','pn') .' <b>'. $bids_data->id .'</b>, '. __('enter coupon code valued','pn').' <b><span class="pn_copy copy_item" data-clipboard-text="'. $pay_sum .'">'. $pay_sum . '</span> '. is_site_value($bids_data->currency_code_give).'</b>:';
				}	

				$list_data = array(
					'code' => array(
						'title' => __('Code','pn'),
						'name' => 'code',
					),
					'pin' => array(
						'title' => __('Pin','pn'),
						'name' => 'pin',
					),					
				);
				$descr = '';

				$temp .= $this->zone_form($pagenote, $list_data, $descr, get_mlink($m_id.'_status'), $bids_data->hashed);						
			
			}
			
			return $temp; 		
		}
		
		function error_back($hash, $code){
			$back = get_pn_action('payedmerchant') .'&hash='. is_bid_hash($hash) .'&err=' . $code;
			wp_redirect($back);
			exit;
		}
		
		function merchant_status(){
		global $wpdb;	

			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);

			$hashed = is_bid_hash(is_param_post('hash'));
			$code = trim(is_param_post('code'));
			$pin = trim(is_param_post('pin'));
			if($hashed){
				$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE hashed='$hashed'");
				if(isset($item->id)){
					$id = $item->id;
					$data = get_data_merchant_for_id($id, $item);
					$bid_err = $data['err'];
					$bid_status = $data['status'];
					$bid_m_id = $data['m_id'];
					$bid_m_script = $data['m_script'];
					
					if($bid_err == 0 and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name){
						$en_status = array('new', 'techpay', 'coldpay');
						if(in_array($bid_status, $en_status)){
							
							$bid_currency = $data['currency'];
							$bid_currency = strtoupper(str_replace('RUR','RUB',$bid_currency));
							
							$bid_sum = is_sum($data['pay_sum']);
							$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);							
							
							$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
							$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
							$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
							$invalid_check = intval(is_isset($m_data, 'check'));
							
							if($code and $pin){
								try {
									$res = new BitexBookApi(is_deffin($m_defin,'TOKEN'));
									$info = $res->check_voucher($code, $pin);
									if($info){
										$merch_sum = is_isset($info,'sum');
										$merch_currency = strtoupper(is_isset($info,'currency'));
										$merch_active = intval(is_isset($info,'active'));
										$type = intval(is_isset($info,'type'));
										if($type == 0 and $merch_active == 1){
											if($merch_sum >= $bid_corr_sum or $invalid_minsum > 0){
												if($merch_currency == $bid_currency or $invalid_ctype > 0){
													
													$info = $res->redeem_voucher($code, $pin);
													if($info){
														$merch_sum = is_isset($info,'sum');
														$merch_currency = strtoupper(is_isset($info,'currency'));
														$merch_active = intval(is_isset($info,'active'));
														$type = intval(is_isset($info,'type'));
														if($type == 0 and $merch_active == 1){
															if($merch_sum >= $bid_corr_sum or $invalid_minsum > 0){
																if($merch_currency == $bid_currency or $invalid_ctype > 0){
													
																	$pay_purse = is_pay_purse($code, $m_data, $bid_m_id);
													
																	$params = array(
																		'pay_purse' => $pay_purse,
																		'sum' => $merch_sum,
																		'bid_sum' => $bid_sum,
																		'bid_status' => array('new','techpay','coldpay'),
																		'bid_corr_sum' => $bid_corr_sum,	
																		'currency' => $merch_currency,
																		'bid_currency' => $bid_currency,
																		'invalid_ctype' => $invalid_ctype,
																		'invalid_minsum' => $invalid_minsum,
																		'invalid_maxsum' => $invalid_maxsum,
																		'invalid_check' => $invalid_check,	
																		'm_place' => $bid_m_id,
																		'm_id' => $m_id,
																		'm_data' => $m_data,
																		'm_defin' => $m_defin,
																	);
																	
																	set_bid_status('realpay', $id, $params, $data['direction_data']);		 									
																	 
																	wp_redirect(get_bids_url($hashed));
																	exit;					
													
																} else {
																	$this->error_back($hashed, '5');
																}
															} else {
																$this->error_back($hashed, '4');					
															}
														} else {
															$this->error_back($hashed, '3');											
														}
													} else {
														$this->error_back($hashed, '2');							
													}
													
												} else {
													$this->error_back($hashed, '5');
												}
											} else {
												$this->error_back($hashed, '4');					
											}
										} else {
											$this->error_back($hashed, '3');											
										}
									} else {
										$this->error_back($hashed, '2');							
									}
								}
								catch (Exception $e)
								{
									$this->logs($e->getMessage());
									$show_error = intval(is_isset($m_data, 'show_error'));
									if($show_error and current_user_can('administrator')){
										die($e->getMessage());
									}	
									$this->error_back($hashed, '2');						
								}	
							} else {
								$this->error_back($hashed, '1');				
							}
						} else {
							wp_redirect(get_bids_url($hashed));
							exit;
						}
					} else {
						pn_display_mess(__('Error 3!','pn'));
					}					
				} else {
					pn_display_mess(__('Error 2!','pn'));
				}	
			} else {
				pn_display_mess(__('Error 1!','pn'));
			}					
		}
	}
}

new merchant_bitexbook(__FILE__, 'Bitexbook');