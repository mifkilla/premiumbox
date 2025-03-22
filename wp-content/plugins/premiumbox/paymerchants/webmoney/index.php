<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Webmoney[:en_US][ru_RU:]Webmoney[:ru_RU]
description: [en_US:]Webmoney automatic payouts[:en_US][ru_RU:]авто выплаты Webmoney[:ru_RU]
version: 2.2
*/

if(!class_exists('AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_webmoney')){
	class paymerchant_webmoney extends AutoPayut_Premiumbox{

		function __construct($file, $title)
		{
			parent::__construct($file, $title);	
		}	
		
		function get_map(){
			$map = array(
				'AP_WEBMONEY_WMID'  => array(
					'title' => '[en_US:]WMID[:en_US][ru_RU:]WMID[:ru_RU]',
					'view' => 'input',	
				),
				'AP_WEBMONEY_KEYPATH'  => array(
					'title' => '[en_US:]File name of key .kwm[:en_US][ru_RU:]Имя файла ключей .kwm[:ru_RU]',
					'view' => 'input',	
				),				
				'AP_WEBMONEY_KEYPASS'  => array(
					'title' => '[en_US:]Password of key file .kwm[:en_US][ru_RU:]Пароль от файла ключей .kwm[:ru_RU]',
					'view' => 'input',	
				),				
				'AP_WEBMONEY_WMZ_PURSE'  => array(
					'title' => '[en_US:]WMZ wallet number[:en_US][ru_RU:]WMZ кошелек[:ru_RU]',
					'view' => 'input',	
				),
				'AP_WEBMONEY_WMR_PURSE'  => array(
					'title' => '[en_US:]WMR wallet number[:en_US][ru_RU:]WMR кошелек[:ru_RU]',
					'view' => 'input',	
				),
				'AP_WEBMONEY_WME_PURSE'  => array(
					'title' => '[en_US:]WME wallet number[:en_US][ru_RU:]WME кошелек[:ru_RU]',
					'view' => 'input',	
				),
				'AP_WEBMONEY_WMU_PURSE'  => array(
					'title' => '[en_US:]WMU wallet number[:en_US][ru_RU:]WMU кошелек[:ru_RU]',
					'view' => 'input',	
				),
				'AP_WEBMONEY_WMB_PURSE'  => array(
					'title' => '[en_US:]WMB wallet number[:en_US][ru_RU:]WMB кошелек[:ru_RU]',
					'view' => 'input',	
				),
				'AP_WEBMONEY_WMY_PURSE'  => array(
					'title' => '[en_US:]WMY wallet number[:en_US][ru_RU:]WMY кошелек[:ru_RU]',
					'view' => 'input',	
				),
				'AP_WEBMONEY_WMG_PURSE'  => array(
					'title' => '[en_US:]WMG wallet number[:en_US][ru_RU:]WMG кошелек[:ru_RU]',
					'view' => 'input',	
				),
				'AP_WEBMONEY_WMX_PURSE'  => array(
					'title' => '[en_US:]WMX wallet number[:en_US][ru_RU:]WMX кошелек[:ru_RU]',
					'view' => 'input',	
				),
				'AP_WEBMONEY_WMK_PURSE'  => array(
					'title' => '[en_US:]WMK wallet number[:en_US][ru_RU:]WMK кошелек[:ru_RU]',
					'view' => 'input',	
				),
				'AP_WEBMONEY_WML_PURSE'  => array(
					'title' => '[en_US:]WML wallet number[:en_US][ru_RU:]WML кошелек[:ru_RU]',
					'view' => 'input',	
				),
				'AP_WEBMONEY_WMH_PURSE'  => array(
					'title' => '[en_US:]WMH wallet number[:en_US][ru_RU:]WMH кошелек[:ru_RU]',
					'view' => 'input',	
				),				
			);
			return $map;
		}
		
		function settings_list(){
			$arrs = array();
			$arrs[] = array('AP_WEBMONEY_WMID','AP_WEBMONEY_KEYPASS');
			return $arrs;
		}

		function options($options, $data, $id, $place){
			
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'checkpay');
			$options = pn_array_unset($options, 'error_status');
						
			$html_request = '';
			$num_request = intval(get_option('old_webmoney_id'));
			$new_request = intval(is_isset($data, 'num_request'));
			if($num_request > 0 and $new_request < 1){
				$html_request = ' ('. $num_request . ')';
			}			
			
			$options[] = array(
				'view' => 'input',
				'title' => __('Current payment ID','pn') . $html_request,
				'default' => is_isset($data, 'num_request'),
				'name' => 'num_request',
				'work' => 'int',
			);								
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin){
			
			$purses = array(
				$m_id.'_1' => is_deffin($m_defin,'AP_WEBMONEY_WMZ_PURSE'),
				$m_id.'_2' => is_deffin($m_defin,'AP_WEBMONEY_WMR_PURSE'),
				$m_id.'_3' => is_deffin($m_defin,'AP_WEBMONEY_WME_PURSE'),
				$m_id.'_4' => is_deffin($m_defin,'AP_WEBMONEY_WMU_PURSE'),
				$m_id.'_5' => is_deffin($m_defin,'AP_WEBMONEY_WMB_PURSE'),
				$m_id.'_6' => is_deffin($m_defin,'AP_WEBMONEY_WMY_PURSE'),
				$m_id.'_7' => is_deffin($m_defin,'AP_WEBMONEY_WMG_PURSE'),
				$m_id.'_8' => is_deffin($m_defin,'AP_WEBMONEY_WMX_PURSE'),
				$m_id.'_9' => is_deffin($m_defin,'AP_WEBMONEY_WMK_PURSE'),
				$m_id.'_10' => is_deffin($m_defin,'AP_WEBMONEY_WML_PURSE'),
				$m_id.'_11' => is_deffin($m_defin,'AP_WEBMONEY_WMH_PURSE'),
			);
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin){
		global $premiumbox;	
			$sum = 0;				
			$purses = $this->get_reserve_lists($m_id, $m_defin);				
			$purse = trim(is_isset($purses, $code));
			if($purse){						
				try {					
					$oWMXI = new WMXI( $premiumbox->plugin_dir .'paymerchants/'. $this->name .'/classed/wmxi.crt', 'UTF-8' );
					$oWMXI->Classic( is_deffin($m_defin,'AP_WEBMONEY_WMID'), array( 'pass' => is_deffin($m_defin,'AP_WEBMONEY_KEYPASS'), 'file' => $this->replace_constant($m_defin,'AP_WEBMONEY_KEYPATH') ) );
						
					$aResponse = $oWMXI->X9( is_deffin($m_defin,'AP_WEBMONEY_WMID') )->toObject();
					$server_reply = is_isset($aResponse, 'retval');
					if($server_reply == '0'){
								
						if(isset($aResponse->purses->purse)){
							$wmid_purses = $aResponse->purses->purse;
								
							$rezerv = '-1';
								
							foreach($wmid_purses as $wp){
								if( $wp->pursename == $purse ){
									$rezerv = (string)$wp->amount;
									break;
								}
							}						
								
							if($rezerv != '-1'){
								$sum = $rezerv;
							}
								
						}

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
			
			$vtype = mb_strtoupper($item->currency_code_get);
			$vtype = str_replace(array('WMZ','USD'),'Z',$vtype);
			$vtype = str_replace(array('RUR','WMR','RUB'),'R',$vtype);
			$vtype = str_replace(array('WME','EUR'),'E',$vtype);
			$vtype = str_replace(array('WMU','UAH'),'U',$vtype);
			$vtype = str_replace(array('WMB','BYR'),'B',$vtype);
			$vtype = str_replace(array('WMY','UZS'),'Y',$vtype);
			$vtype = str_replace(array('WMG','GLD'),'G',$vtype);
			$vtype = str_replace(array('WMX','BTC'),'X',$vtype);
			$vtype = str_replace(array('WMK','KZT'),'K',$vtype);
			$vtype = str_replace(array('WML','LTC'),'L',$vtype);
			$vtype = str_replace(array('WMH','BCH'),'H',$vtype);
					
			$enable = array('Z','R','E','U','B','Y','G','X','K','L','H');
			if(!in_array($vtype, $enable)){
				$error[] = __('Wrong currency code','pn'); 
			}						
						
			$account = $item->account_get;
			$account = mb_strtoupper($account);
			if(!is_wm_purse($account, $vtype)){
				$error[] = __('Client wallet type does not match with currency code','pn');
			}		
					
			$site_purse = is_deffin($m_defin,'AP_WEBMONEY_WM'. $vtype .'_PURSE');
					
			$site_purse = mb_strtoupper($site_purse);
			if(!is_wm_purse($site_purse, $vtype)){
				$error[] = __('Your account set on website does not match with currency code','pn');
			}	

			$sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
		
			if(count($error) == 0){
				global $premiumbox;
				
				$result = $this->set_ap_status($item, $test);				
				if($result){					
					
					$notice = get_text_paymerch($m_id, $item);
					if(!$notice){ $notice = sprintf(__('ID order %s','pn'), $item->id); }
					$notice = trim(pn_maxf($notice,245));
							
					if(is_file($premiumbox->plugin_dir .'paymerchants/'. $this->name .'/classed/wmxi.crt') and is_deffin($m_defin,'AP_WEBMONEY_KEYPASS') and $this->replace_constant($m_defin,'AP_WEBMONEY_KEYPATH')){
							
						$num_request = intval(is_isset($paymerch_data, 'num_request'));
						$num_request = $num_request + 1;
						
						$save_data = get_option('paymerchants_data');
						$save_data = (array)$save_data;
						$save_data[$m_id]['num_request'] = $num_request;
						update_option('paymerchants_data', $save_data);							 					
							
						try{
							
							$oWMXI = new WMXI( $premiumbox->plugin_dir .'paymerchants/'. $this->name .'/classed/wmxi.crt', 'UTF-8' );
							$oWMXI->Classic( is_deffin($m_defin,'AP_WEBMONEY_WMID'), array( 'pass' => is_deffin($m_defin,'AP_WEBMONEY_KEYPASS'), 'file' => $this->replace_constant($m_defin,'AP_WEBMONEY_KEYPATH') ) );
								
							$aResponse = $oWMXI->X2($num_request, $site_purse, $account, $sum , 0, '', $notice, 0, 0)->toObject();
							$server_reply = is_isset($aResponse, 'retval');
								
							if($server_reply != '0'){
								$error[] = is_isset($aResponse, 'retdesc').' Code:'.$server_reply;
								$pay_error = 1;
							} 
								
						}
						catch (Exception $e)
						{
							$error[] = $e->getMessage();
							$pay_error = 1;
						} 
							
					} else {
						$error[] = 'Error interfaice';
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
					'from_account' => $site_purse,
					'trans_out' => $trans_id,
					'system' => 'user',
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('success', $item_id, $params,$direction); 						
						
				if($place == 'admin'){
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
				}  		
			}
		}				
		
	}
}

global $premiumbox;
$path = get_extension_file(__FILE__);
$premiumbox->file_include($path . '/classed/wmxicore.class');	
$premiumbox->file_include($path . '/classed/wmxi.class');
$premiumbox->file_include($path . '/classed/wmxiresult.class');
$premiumbox->file_include($path . '/classed/wmsigner.class');

new paymerchant_webmoney(__FILE__, 'Webmoney');