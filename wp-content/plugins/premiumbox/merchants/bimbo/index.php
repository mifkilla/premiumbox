<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]BimBo[:en_US][ru_RU:]BimBo[:ru_RU]
description: [en_US:]BimBo merchant[:en_US][ru_RU:]мерчант BimBo[:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_bimbo')){
	class merchant_bimbo extends Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			add_action('init_bid_merchant', array($this,'init_merchant'));
			add_filter('merchant_pay_button', array($this,'pay_button'),99,5);
			add_filter('merchant_formstep_after', array($this,'formstep_after'),99,3);
			add_action('premium_merchant_'. $this->name .'_paystatus', array($this,'merchant_paystatus'));
		}

		function options($options, $data, $id, $place){
				
			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'check_api');	
			$options = pn_array_unset($options, 'type');
			$options = pn_array_unset($options, 'help_type');
			$options = pn_array_unset($options, 'corr');
			$options = pn_array_unset($options, 'center_title');
			$options = pn_array_unset($options, 'check');
			$options = pn_array_unset($options, 'invalid_ctype');
			$options = pn_array_unset($options, 'invalid_minsum');
			$options = pn_array_unset($options, 'invalid_maxsum');
			$options = pn_array_unset($options, 'enableip');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'show_error');
			
			$options['private_line'] = array(
				'view' => 'line',
			);			
			
			$options['paymethod'] = array(
				'view' => 'select',
				'title' => __('Payment method','pn'),
				'options' => array('0'=>__('Link','pn'), '1'=>__('Account','pn')),
				'default' => is_isset($data, 'paymethod'),
				'name' => 'paymethod',
				'work' => 'int',
			);			
			
			$options['link'] = array(
				'view' => 'inputbig',
				'title' => __('Link','pn'),
				'default' => is_isset($data, 'link'),
				'name' => 'link',
				'work' => 'input',
				'ml' => 1,
			);

			$options['accnum'] = array(
				'view' => 'inputbig',
				'title' => __('Account number','pn'),
				'default' => is_isset($data, 'accnum'),
				'name' => 'accnum',
				'work' => 'input',
			);			
			
			return $options;	
		}									

		function formstep_after($content, $m_id, $direction){
		global $bids_data;
		
			$data = get_merch_data($m_id);
			$paymethod = intval(is_isset($data, 'paymethod'));
			$script = get_mscript($m_id);
			if($script and $script == $this->name and $paymethod == 0){
				
				$temp = '
				<div class="block_warning_merch">
					<div class="block_warning_merch_ins">		
						<p>'. __('Attention! Click "Paid", if you have already paid the request.','pn') .'</p>		
					</div>
				</div>
							
				<div class="block_paybutton_merch">
					<div class="block_paybutton_merch_ins">				
						<a href="'. get_mlink($this->name . '_paystatus') .'?hash='. is_bid_hash($bids_data->hashed) .'" class="merch_paybutton iam_pay_bids">'. __('Paid','pn') .'</a>				
					</div>
				</div>							
				';	

				return $temp;
			}
			
			return $content;
		}	

		function merchant_paystatus(){
		global $wpdb;	
			$hashed = is_bid_hash(is_param_get('hash'));
			if($hashed){
				$obmen = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE hashed='$hashed'");
				if(isset($obmen->id)){
					$en_status = array('new','techpay','coldpay');
					if(in_array($obmen->status, $en_status)){
						if(is_true_userhash($obmen)){					
							$direction_id = intval($obmen->direction_id);
							$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE direction_status='1' AND auto_status='1' AND id='$direction_id'");
							$m_id = apply_filters('get_merchant_id','', $direction, $obmen);
							$script = get_mscript($m_id);
							if($script and $script == $this->name){
								$arr = array('status'=>'payed','edit_date'=>current_time('mysql'));
								$result = $wpdb->update($wpdb->prefix.'exchange_bids', $arr, array('id'=>$obmen->id));
								if($result == 1){ 
									$old_status = $obmen->status;
									$obmen = pn_object_replace($obmen, $arr);
									$obmen = apply_filters('change_bidstatus', $obmen, 'payed', $this->name, 'user', $old_status, $direction); 
								}
							}
						}
					} 
				}
			} 
				$url = get_bids_url($hashed);
				wp_redirect($url);
				exit;	
		}		
		
		function pay_button($merchant_pay_button, $m_in, $sum_to_pay, $bids_data, $direction){
			$script = get_mscript($m_in);
			if($script and $script == $this->name){
				$m_defin = $this->get_file_data($m_in);
				$m_data = get_merch_data($m_in);
				$paymethod = intval(is_isset($m_data, 'paymethod'));
				if($paymethod == 0){
					$url = trim(ctv_ml(is_isset($m_data, 'link')));
					$merchant_pay_button = '
					<a href="'. $url .'" target="_blank" rel="noreferrer noopener" class="success_paybutton">'. __('Make a payment','pn') .'</a>
					';
				} else {
					$merchant_pay_button = '
					<a href="'. get_mlink($this->name . '_paystatus') .'?hash='. is_bid_hash($bids_data->hashed) .'" class="success_paybutton iam_pay_bids">'. __('Paid','pn') .'</a>
					';
				}
			}
			return $merchant_pay_button;
		}	

		function init_merchant($m_id){
			global $bids_data;
			$script = get_mscript($m_id);
			if($script and $script == $this->name and isset($bids_data->id)){
				$m_data = get_merch_data($m_id);
				$accnum = pn_strip_input(is_isset($m_data, 'accnum'));
				$bids_data = update_bid_tb($bids_data->id, 'to_account', $accnum, $bids_data);
			}	
		}
	}
}

new merchant_bimbo(__FILE__, 'BimBo');