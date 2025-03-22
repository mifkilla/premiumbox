<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('tab_direction_tab8','tab_direction_tab_x19',99,2);
function tab_direction_tab_x19($data, $data_id){
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('X19','pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$x19mod = intval(get_direction_meta($data_id, 'x19mod')); 
				?>									
				<select name="x19mod" autocomplete="off">
					<?php
					$array = array(
						'0' => '--' . __('No item','pn') . '--',
						'1' => __('Cash','pn') .' -> '. __('Webmoney','pn'),
						'2' => __('Bank account','pn') .' -> '. __('Webmoney','pn'),
						'3' => __('Bank card','pn') .' -> '. __('Webmoney','pn'),
						'4' => __('Money transfer system','pn') .' -> '. __('Webmoney','pn'),
						'5' => __('SMS','pn') .' -> '. __('Webmoney','pn'),
						'6' => __('Webmoney','pn') .' -> '. __('Cash','pn'),
						'7' => __('Webmoney','pn') .' -> '. __('Bank account','pn'),
						'8' => __('Webmoney','pn') .' -> '. __('Bank card','pn'),
						'9' => __('Webmoney','pn') .' -> '. __('Money transfer system','pn'),
						'10' => __('PayPal','pn') .' -> '. __('Webmoney','pn'),
						'11' => __('Skrill (Moneybookers)','pn') .' -> '. __('Webmoney','pn'),
						'12' => __('QIWI','pn') .' -> '. __('Webmoney','pn'),
						'13' => __('Yandex money','pn') .' -> '. __('Webmoney','pn'),
						'14' => __('EasyPay','pn') .' -> '. __('Webmoney','pn'),
						'15' => __('Webmoney','pn') .' -> '. __('PayPal','pn'),
						'16' => __('Webmoney','pn') .' -> '. __('Skrill (Moneybookers)','pn'),
						'17' => __('Webmoney','pn') .' -> '. __('QIWI','pn'),
						'18' => __('Webmoney','pn') .' -> '. __('Yandex money','pn'),
						'19' => __('Webmoney','pn') .' -> '. __('EasyPay','pn'),
						'20' => __('Webmoney','pn') .' -> '. __('Webmoney','pn'),
						'21' => __('Webmoney','pn') .' -> '. __('Bitcoin','pn'),
					);
						
					foreach($array as $key => $arr){
					?>
						<option value="<?php echo $key; ?>" <?php selected($x19mod,$key); ?>><?php echo $arr; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</div>		
	<?php
}

add_action('item_direction_edit','item_direction_edit_x19'); 
add_action('item_direction_add','item_direction_edit_x19');
function item_direction_edit_x19($data_id){
	$x19mod = intval(is_param_post('x19mod'));
	update_direction_meta($data_id, 'x19mod', $x19mod);
}

add_filter('form_bids_account_give','x19_form_bids_account_give',99,3);
function x19_form_bids_account_give($show, $direction, $vd){
	if($show == 0){
		$x19mod = intval(is_isset($direction,'x19mod'));
		if($x19mod > 0){
			$arrwm = array(6,7,8,9,15,16,17,18,19,20,21);
			if(in_array($x19mod,$arrwm)){
				$show = 1;
			}
		}
	}
		return $show;
}

add_filter('form_bids_account_get','x19_form_bids_account_get',99,3);
function x19_form_bids_account_get($show, $direction, $vd){
	if($show == 0){
		$x19mod = intval(is_isset($direction,'x19mod'));
		if($x19mod > 0){
			$arrwm = array(6,7,8,9,15,16,17,18,19,21);
			if(!in_array($x19mod,$arrwm)){
				$show = 1;
			}
		}
	}
		return $show;
}

add_filter('cf_auto_form_value','x19_cf_auto_form_value',1,4);
function x19_cf_auto_form_value($cauv,$value,$item,$direction){
global $wpdb;
	
	$cf_auto = $item->cf_auto;
	$x19mod = intval(is_isset($direction,'x19mod'));
	
	$error = 0;
	
	if($cf_auto == 'first_name' or $cf_auto == 'last_name'){ 
		if(!$value){
			$error = 1;					
		} 
	} 	
	if($x19mod == 1 or $x19mod == 6){
		if($cf_auto == 'user_passport'){ 
			if(!$value){
				$error = 1;					
			} 
		} 
	}
	if($error == 1){
		$cauv = array(
			'error' => 1,
			'error_text' => __('field is not filled in','pn')
		);
	}			
	
	return $cauv;
}

add_filter('error_bids','x19_error_bids',99,9);
function x19_error_bids($error_bids, $account1, $account2, $dir, $vd1, $vd2, $auto_data, $unmetas, $cdata){
	
	$x19mod = intval(is_isset($dir,'x19mod'));
	
	if(count($error_bids['error_text']) == 0 and count($error_bids['error_fields']) == 0){
		if($x19mod > 0){

			$arrwm1 = array(6,7,8,9,15,16,17,18,19,20,21);
			if(in_array($x19mod,$arrwm1)){
				$wmkow = $account1;
				$wmkow2 = $account2;
				$wtype = 1;
			} else {
				$wmkow = $account2;
				$wmkow2 = $account1;
				$wtype = 2;
			}
			
			$object = WMXI_X19();
			if(is_object($object)){	
				
				$pursetype = 'WM'.mb_strtoupper(mb_substr($wmkow,0,1));
				
				$darr = wmid_with_purse($object, $wmkow);
				$wmid = $darr['wmid'];
				
				if($wmid){
					if($x19mod == 20){
						
						$darr2 = wmid_with_purse($object, $wmkow2);
						$wmid2 = $darr2['wmid'];
						
						if($wmid2){
							if($wmid != $wmid2){
								$error_bids['error_fields']['account1'] = __('Wallet belongs to another WMID','pn');
								$error_bids['error_fields']['account2'] = __('Wallet belongs to another WMID','pn');
								$error_bids['error_text'][] = __('Wallet belongs to another WMID','pn');
							}
						} else {
							if($wtype==1){
								$error_bids['error_fields']['account1'] = __('Wallets belong to different WMIDs','pn');
							} else {
								$error_bids['error_fields']['account2'] = __('Wallets belong to different WMIDs','pn');											
							}						
						}
					} else {					
						
						$amount = is_sum(is_isset($cdata, 'sum1dc')); 
						$fname = is_isset($auto_data,'last_name');
						$iname = is_isset($auto_data,'first_name');
						$obmen_pasport = is_isset($auto_data,'user_passport');
						$pnomer = '';
						$bank_name = '';
						$bank_account = '';
						$card_number = ''; 
						$emoney_name = '';
						$emoney_id = '';
						$phone = '';
						$crypto_name='';
						$crypto_address='';
						
						if($x19mod == 1){ /* Наличные в офисе -> WM */
							$type = 1;
							$direction = 2;
							$pnomer = $obmen_pasport;
						} elseif($x19mod == 2){ /* Банковский счет -> WM */ 
							$type = 3;
							$direction = 2;						
							$bank_name = ctv_ml($vd1->psys_title);
							$bank_account = $account1;					
						} elseif($x19mod == 3){ /* Банковская карта -> WM */ 
							$type = 4;
							$direction = 2;						
							$bank_name = ctv_ml($vd1->psys_title);
							$card_number = $account1;					
						} elseif($x19mod == 4){ /* Системы денежных переводов -> WM */
							$type = 2;
							$direction = 2;					
						} elseif($x19mod == 5){ /* SMS -> WM */
							$type = 6;
							$direction = 2;
							$phone = is_phone($account1);	
						} elseif($x19mod == 6){ /* WM -> Наличные в офисе */
							$type = 1;
							$direction = 1;
							$pnomer = $obmen_pasport;
						} elseif($x19mod == 7){ /* WM -> Банковский счет */
							$type = 3;
							$direction = 1;
							$bank_name = ctv_ml($vd2->psys_title);
							$bank_account = $account2;					
						} elseif($x19mod == 8){ /* WM -> Банковская карта */
							$type = 4;
							$direction = 1;
							$bank_name = ctv_ml($vd2->psys_title);
							$card_number = $account2;					
						} elseif($x19mod == 9){ /* WM -> Системы денежных переводов */
							$type = 2;
							$direction = 1;					
						} elseif($x19mod == 10){ /* PayPal -> WM */
							$type = 5;
							$direction = 2; 
							$emoney_name = 'paypal.com';
							$emoney_id = $account1;					
						} elseif($x19mod == 11){ /* Skrill (Moneybookers) -> WM */
							$type = 5;
							$direction = 2; 
							$emoney_name = 'moneybookers.com';
							$emoney_id = $account1;					
						} elseif($x19mod == 12){ /* QIWI Кошелёк -> WM */
							$type = 5;
							$direction = 2; 
							$emoney_name = 'qiwi.ru';
							$emoney_id = is_phone($account1);					
						} elseif($x19mod == 13){ /* Яндекс.Деньги -> WM */
							$type = 5;
							$direction = 2; 
							$emoney_name = 'money.yandex.ru';
							$emoney_id = $account1;				
						} elseif($x19mod == 14){ /* EasyPay -> WM */
							$type = 5;
							$direction = 2; 
							$emoney_name = 'easypay.by';
							$emoney_id = $account1;	
						} elseif($x19mod == 15){ /* WM -> PayPal */
							$type = 5;
							$direction = 1; 
							$emoney_name = 'paypal.com';
							$emoney_id = $account2;					
						} elseif($x19mod == 16){ /* WM -> Skrill (Moneybookers) */
							$type = 5;
							$direction = 1; 
							$emoney_name = 'moneybookers.com';
							$emoney_id = $account2;					
						} elseif($x19mod == 17){ /* WM -> QIWI Кошелёк */
							$type = 5;
							$direction = 1; 
							$emoney_name = 'qiwi.ru';
							$emoney_id = is_phone($account2);					
						} elseif($x19mod == 18){ /* WM -> Яндекс.Деньги */
							$type = 5;
							$direction = 1; 
							$emoney_name = 'money.yandex.ru';
							$emoney_id = $account2;					
						} elseif($x19mod == 19){ /* WM -> EasyPay */
							$type = 5;
							$direction = 1; 
							$emoney_name = 'easypay.by';
							$emoney_id = $account2;
						} elseif($x19mod == 21){ /* WM -> Bitcoin */
							$type = 8;
							$direction = 1; 
							$crypto_name = 'bitcoin';
							$crypto_address = $account2;						
						}
						
						$retval = 101010;
						$res = array('retdesc'=>'no');
						
						try{
							$object = WMXI_X19();
							if(is_object($object)){
								$res = $object->X19($type, $direction, $pursetype, $amount, $wmid, $pnomer, $fname, $iname, $bank_name, $bank_account, $card_number, $emoney_name, $emoney_id, $phone, $crypto_name, $crypto_address)->toArray();
								$retval = is_isset($res,'retval');
								x19_create_log($dir->id, is_isset($res, 'retdesc'));
							} else {
								$retval = 1000;
								$res['retdesc'] = __('X19 interface error','pn');
							}
						} catch(Exception $e){
							$retval = 1000;
							$res['retdesc'] = __('X19 interface error','pn');
						}
							
						if($retval == 0){
							/* 
								$error_bids['error_text'][] = $res['retdesc'];
							*/					
						} elseif($retval == 404){
							$error_bids['error_text'][] = $res['retdesc'];
						} else {
							$error_bids['error_text'][] = $res['retdesc'];
						}
					}
				} else {
					if($wtype==1){
						$error_bids['error_fields']['account1'] = __('Invalid account Send','pn');
					} else {
						$error_bids['error_fields']['account2'] = __('Invalid account Receive','pn');				
					}
				}
			} else {
				$error_bids['error_text'][] = __('No access to X19 interface. Check settings','pn');
			}
		}
	}
		
	return $error_bids;
}