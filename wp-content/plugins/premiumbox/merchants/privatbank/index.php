<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Privat24 History (statement)[:en_US][ru_RU:]Privat24 History (выписка)[:ru_RU]
description: [en_US:]checking out payments history according to the merchant Private24 list[:en_US][ru_RU:]проверка истории платежей по выписке из мерчанта Privat24 [:ru_RU]
version: 2.2
*/

if(!class_exists('Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_privatbank')){
	class merchant_privatbank extends Merchant_Premiumbox { 

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			add_filter('get_text_pay', array($this,'get_text_pay'), 99, 3);
			add_filter('merchant_pay_button', array($this,'pay_button'),99,5);
			add_action('premium_merchant_'. $this->name .'_paystatus', array($this,'merchant_paystatus'));
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id){
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
			}
		}
		
		function get_map(){
			$map = array(
				'CARD_NUM'  => array(
					'title' => '[en_US:]Card number[:en_US][ru_RU:]Номер карты[:ru_RU]',
					'view' => 'input',	
				),
				'MERCHANT_ID'  => array(
					'title' => '[en_US:]Merchant ID[:en_US][ru_RU:]ID мерчант[:ru_RU]',
					'view' => 'input',
				),
				'MERCHANT_KEY'  => array(
					'title' => '[en_US:]Merchant key-password[:en_US][ru_RU:]Ключ-пароль от мерчанта[:ru_RU]',
					'view' => 'input',
				),				
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('MERCHANT_ID','MERCHANT_KEY','CARD_NUM');
			return $arrs;
		}			

		function options($options, $data, $id, $place){  

			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, 'note');
			$options = pn_array_unset($options, 'check_api');

			$text = '
			<div><strong>CRON:</strong> <a href="'. get_mlink($id.'_status' . hash_url($id)) .'" target="_blank" rel="noreferrer noopener">'. get_mlink($id.'_status' . hash_url($id)) .'</a></div>			
			';			
			
			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);				
			
			return $options;	
		}				
		
		function get_text_pay($text, $m_id, $item){
			$script = get_mscript($m_id);
			if($script and $script == $this->name){
				$text = str_replace(array('[id]','[exchange_id]'),'('.$item->id.')',$text);
			}
			return $text;
		}						
		
		function pay_button($merchant_pay_button, $m_in, $sum_to_pay, $bids_data, $direction){
			$script = get_mscript($m_in);
			if($script and $script == $this->name){
				$m_defin = $this->get_file_data($m_in);
			
				$merchant_pay_button = '
				<a href="'. get_mlink($this->name . '_paystatus') .'?hash='. is_bid_hash($bids_data->hashed) .'" class="success_paybutton iam_pay_bids">'. __('Paid','pn') .'</a>
				';
			}
			return $merchant_pay_button;
		}

		function merchant_paystatus(){
		global $wpdb;	
	
			$hashed = is_bid_hash(is_param_get('hash'));
			if($hashed){
				$obmen = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE hashed='$hashed'");
				if(isset($obmen->id)){
					if($obmen->status == 'new'){
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
		
		function merchant_status(){
			global $wpdb;

			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_logs', $this->name, '', $m_id, $m_defin, $m_data);
			
			$show_error = intval(is_isset($m_data, 'show_error'));
			
			try {
				$oClass = new PrivatBankApi($m_id, is_deffin($m_defin,'MERCHANT_ID'),is_deffin($m_defin,'MERCHANT_KEY'));
				$card = is_deffin($m_defin,'CARD_NUM');
				$res = $oClass->get_history($card);
				$this->logs($res);
				if(is_array($res)){
					foreach($res as $bid_id => $bid_data){
						$bid_id = intval($bid_id);
						$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status IN('coldpay','techpay','payed') AND m_in='$m_id' AND id='$bid_id'");
						if(isset($item->id)){
							$currency = mb_strtoupper(is_isset($bid_data,'currency'));
					
							$id = $bid_id;
							$data = get_data_merchant_for_id($id, $item);
							
							$in_sum = is_isset($bid_data,'amount');
							$in_sum = is_sum($in_sum,2);
							$bid_err = $data['err'];
							$bid_status = $data['status'];
							$bid_m_id = $data['m_id'];
							$bid_m_script = $data['m_script'];
							
							$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
							
							$bid_currency = $data['currency'];
												
							$bid_sum = is_sum($data['pay_sum'],2);	
							$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
							
							$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
							$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
							$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
							$invalid_check = intval(is_isset($m_data, 'check'));							
							
							if($bid_err == 0 and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name){
								if($bid_currency == $currency or $invalid_ctype > 0){
									if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){
										$params = array(
											'pay_purse' => $pay_purse,
											'sum' => $in_sum,
											'bid_sum' => $bid_sum,
											'bid_corr_sum' => $bid_corr_sum,
											'bid_status' => array('payed','techpay','coldpay'),
											'to_account' => is_deffin($m_defin,'MERCHANT_ID'),
											'currency' => $currency,
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
										
									} else {
										$this->logs($id . ' The payment amount is less than the provisions');
									}
								} else {
									$this->logs($id . ' Wrong type of currency');
								}
							} else {
								$this->logs($id . ' bid error');
							}				
						}
					}
				}
			}	
			catch( Exception $e ) {
				$this->logs($e->getMessage());
				if($show_error and current_user_can('administrator')){
					echo $e->getMessage();
				}	
			}			
		}
		
	}
}

new merchant_privatbank(__FILE__, 'Privat24 History');