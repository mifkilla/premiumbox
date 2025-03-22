<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Internal account[:en_US][ru_RU:]Внутренний счет[:ru_RU]
description: [en_US:]auto payouts for internal account[:en_US][ru_RU:]авто выплаты для внутреннего счета[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_domacc')){
	class paymerchant_domacc extends AutoPayut_Premiumbox{
		function __construct($file, $title)
		{
			parent::__construct($file, $title);	
		}
		
		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'max');
			$options = pn_array_unset($options, 'max_sum');
			$options = pn_array_unset($options, 'max_month');
			$options = pn_array_unset($options, 'where_sum');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'error_status');
			
			return $options;
		}		

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){
		global $wpdb;
		
			$trans_id = 0;
			$item_id = $item->id;

			if(count($error) == 0){
				$result = $this->set_ap_status($item, $test);
				if($result){
					$wpdb->update($wpdb->prefix.'exchange_bids', array('domacc2'=>'1'), array('id'=>$item_id));
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

new paymerchant_domacc(__FILE__, 'Internal account');