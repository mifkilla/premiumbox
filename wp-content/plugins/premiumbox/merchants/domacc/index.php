<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Internal account[:en_US][ru_RU:]Внутренний счет[:ru_RU]
description: [en_US:]merchant for internal account[:en_US][ru_RU:]мерчант для внутреннего счета[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_domacc')){
	class merchant_domacc extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			add_filter('merchant_pay_button', array($this,'pay_button'),99,5); 
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
			}
		}

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'corr');
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'type');
			$options = pn_array_unset($options, 'help_type');
			$options = pn_array_unset($options, 'enableip');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'check_api');
			$options = pn_array_unset($options, 'center_title');
			$options = pn_array_unset($options, 'check');
			$options = pn_array_unset($options, 'invalid_ctype');
			$options = pn_array_unset($options, 'invalid_minsum');
			$options = pn_array_unset($options, 'invalid_maxsum');
			$options = pn_array_unset($options, 'show_error');
			$options = pn_array_unset($options, 'pagenote');	
			
			return $options;
		}		

		function pay_button($merchant_pay_button, $m_in, $sum_to_pay, $bids_data, $direction){
			$script = get_mscript($m_in);
			if($script and $script == $this->name){
				$merchant_pay_button = '<a href="'. get_mlink($m_in .'_status') .'?hash='. is_bid_hash($bids_data->hashed) .'" target="_blank" rel="noreferrer noopener" class="success_paybutton">'. __('Make a payment','pn') .'</a>';
			}
			return $merchant_pay_button;			
		}		
		
		function merchant_status(){
		global $wpdb;
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);
	
			$hashed = is_bid_hash(is_param_get('hash'));
			$ui = wp_get_current_user();
			$user_id = intval($ui->ID);	
			if($user_id){
				if($hashed){
					$obmen = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE hashed='$hashed'");
					if(isset($obmen->id)){
						if($obmen->status == 'new'){
							if(is_true_userhash($obmen)){
								$direction_id = intval($obmen->direction_id);
								$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE direction_status='1' AND auto_status='1' AND id='$direction_id'");
								$m_id = apply_filters('get_merchant_id','', $direction, $obmen);
								$script = get_mscript($m_id);
								if($script and $script == $this->name and function_exists('get_user_domacc')){
									$now_sum = get_user_domacc($user_id, $obmen->currency_code_id_give);
									if($now_sum >= $obmen->sum1c){
										$wpdb->update($wpdb->prefix.'exchange_bids', array('domacc1'=>'1'), array('id'=>$obmen->id));
										
										$params = array(
											'sum' => $obmen->sum1c,
											'bid_sum' => $obmen->sum1c,
											'bid_corr_sum' => $obmen->sum1c,
											'm_place' => $m_id,
											'm_id' => $m_id,
											'm_data' => $m_data,
											'm_defin' => $m_defin,
										);
										
										$status_realpay = apply_filters('merchant_status_reaplpay', 'realpay', $obmen, $m_id, $m_defin, $m_data);
										
										set_bid_status($status_realpay, $obmen->id, $params, $direction); 	
										 
									} else {
										pn_display_mess(__('Not enough money','pn'));
									}
								} else {
									pn_display_mess(__('Merchant is disabled','pn'));
								}
							} else {
								pn_display_mess(__('Browser hash does not match the required hash','pn'));
							}
						}
					}
				}			
			}	 
	
			$url = get_bids_url($hashed);
			wp_redirect($url);
			exit;			
		}
	}
}

new merchant_domacc(__FILE__, __('Internal account','pn'));