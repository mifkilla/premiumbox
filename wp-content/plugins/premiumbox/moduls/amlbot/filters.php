<?php 
if( !defined( 'ABSPATH')){ exit(); } 

/* direction settings */
add_filter('list_tabs_direction', 'list_tabs_direction_amlbot');
function list_tabs_direction_amlbot($list_tabs){
	$list_tabs['amlbot'] = __('AML Bot','pn');
	return $list_tabs;
}

add_action('tab_direction_amlbot','def_tab_direction_amlbot',20,2);
function def_tab_direction_amlbot($data, $data_id){
	$aml = @json_decode($data->aml, true);
	if(!is_array($aml)){ $aml = array(); }
	
	$aml_give = intval(is_isset($aml, 'aml_give'));
	$aml_get = intval(is_isset($aml, 'aml_get'));
	$aml_merch = intval(is_isset($aml, 'aml_merch'));
	$aml_give_sum = is_sum(is_isset($aml, 'aml_give_sum'));
	$aml_get_sum = is_sum(is_isset($aml, 'aml_get_sum'));
	$aml_merch_sum = is_sum(is_isset($aml, 'aml_merch_sum'));
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Checking account Give','pn'); ?></span></div>
			<div class="premium_wrap_standart">									
				<select name="aml_give" autocomplete="off"> 
					<option value="0" <?php selected($aml_give,0); ?>><?php _e('No','pn'); ?></option>
					<option value="1" <?php selected($aml_give,1); ?>><?php _e('Yes','pn'); ?></option>
					<option value="2" <?php selected($aml_give,2); ?>><?php _e('Yes, and prohibit creating an order','pn'); ?></option>
				</select>
			</div>			
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange amount "from"','pn'); ?></span></div>
			<div class="premium_wrap_standart">								
				<input type="text" name="aml_give_sum" style="width: 100%;" value="<?php echo is_sum($aml_give_sum); ?>" />				
			</div>					
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Checking account Send','pn'); ?></span></div>
			<div class="premium_wrap_standart">									
				<select name="aml_get" autocomplete="off"> 
					<option value="0" <?php selected($aml_get,0); ?>><?php _e('No','pn'); ?></option>
					<option value="1" <?php selected($aml_get,1); ?>><?php _e('Yes','pn'); ?></option>
					<option value="2" <?php selected($aml_get,2); ?>><?php _e('Yes, and prohibit creating an order','pn'); ?></option>
				</select>
			</div>			
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange amount "from"','pn'); ?></span></div>
			<div class="premium_wrap_standart">								
				<input type="text" name="aml_get_sum" style="width: 100%;" value="<?php echo is_sum($aml_get_sum); ?>" />				
			</div>					
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Checking TxID','pn'); ?></span></div>
			<div class="premium_wrap_standart">									
				<select name="aml_merch" autocomplete="off"> 
					<option value="0" <?php selected($aml_merch,0); ?>><?php _e('No','pn'); ?></option>
					<option value="1" <?php selected($aml_merch,1); ?>><?php _e('Yes','pn'); ?></option>
				</select>
			</div>			
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange amount "from"','pn'); ?></span></div>
			<div class="premium_wrap_standart">								
				<input type="text" name="aml_merch_sum" style="width: 100%;" value="<?php echo is_sum($aml_merch_sum); ?>" />				
			</div>					
		</div>		
	</div>	
<?php
}

add_filter('pn_direction_addform_post', 'amlbot_direction_addform_post');
function amlbot_direction_addform_post($array){
	
	$aml = array();
	$aml['aml_give'] = intval(is_param_post('aml_give'));
	$aml['aml_get'] = intval(is_param_post('aml_get'));
	$aml['aml_merch'] = intval(is_param_post('aml_merch'));
	$aml['aml_give_sum'] = is_sum(is_param_post('aml_give_sum'));
	$aml['aml_get_sum'] = is_sum(is_param_post('aml_get_sum'));
	$aml['aml_merch_sum'] = is_sum(is_param_post('aml_merch_sum'));
	$array['aml'] = @json_encode($aml, JSON_UNESCAPED_UNICODE);
	
	return $array;
}
/* end direction settings */

/* check bid */
add_filter('error_bids', 'amlbot_error_bids', 100, 9);
function amlbot_error_bids($error_bids, $account1, $account2, $direction, $vd1, $vd2, $auto_data, $unmetas, $cdata){
global $wpdb, $premiumbox, $aml_bids;	
		
	$aml_bids = array();	
		
	if(count($error_bids['error_fields']) == 0){	
		
		$aml = @json_decode($direction->aml, true);
		if(!is_array($aml)){ $aml = array(); }
	
		$aml_give = intval(is_isset($aml, 'aml_give'));
		$aml_get = intval(is_isset($aml, 'aml_get'));
		$aml_give_sum = is_sum(is_isset($aml, 'aml_give_sum'));
		$aml_get_sum = is_sum(is_isset($aml, 'aml_get_sum'));
		
		$sum_give = is_sum(is_isset($cdata, 'sum1dc'));
		$sum_get = is_sum(is_isset($cdata, 'sum2c'));
		
		$access_id = pn_strip_input($premiumbox->get_option('amlbot','access_id'));
		$access_key = pn_strip_input($premiumbox->get_option('amlbot','access_key'));
		$error_score = intval($premiumbox->get_option('amlbot','error_score'));
		
		$class = new AMLClass($access_id, $access_key);
		
		$v = get_currency_data();
		
		if($aml_give > 0 and $sum_give >= $aml_give_sum and $account1){
			$currency_code = $vd1->currency_code_title;
			$currency_code = currency_code_amlbot($currency_code, $vd1->id, $v);
			$res = $class->verify_address($account1, $currency_code);
			if(isset($res['result'], $res['data']) and $res['result']){
				if(isset($res['data']['riskscore'])){
					$aml_bids['give']['score'] = $score = is_sum($res['data']['riskscore']) * 100;
					$aml_bids['give']['signals'] = is_isset($res['data'],'signals');
					if($aml_give == 2 and $score >= $error_score){
						$error_bids['error_text'][] = __('Error!','pn');
						$error_bids['error_fields']['account1'] = sprintf(__('address has a negative rating (%s)','pn'), $score . '%');
					}
				}
			} else {
				$error_bids['error_text'][] = __('AML module API error!','pn');
				if(current_user_can('administrator')){
					$error_bids['error_fields']['account1'] = pn_strip_input(print_r($res, true));
				} else {
					$error_bids['error_fields']['account1'] = __('AML module API error!','pn');
				}
			}
		}
		
		if($aml_get > 0 and $sum_get >= $aml_get_sum and $account2){
			$currency_code = $vd2->currency_code_title;
			$currency_code = currency_code_amlbot($currency_code, $vd2->id, $v);
			$res = $class->verify_address($account2, $currency_code);
			if(isset($res['result'], $res['data']) and $res['result']){
				if(isset($res['data']['riskscore'])){
					$aml_bids['get']['score'] = $score = is_sum($res['data']['riskscore']) * 100;
					$aml_bids['get']['signals'] = is_isset($res['data'], 'signals');
					if($aml_get == 2 and $score >= $error_score){
						$error_bids['error_text'][] = __('Error!','pn');
						$error_bids['error_fields']['account2'] = sprintf(__('address has a negative rating (%s)','pn'), $score . '%');
					}
				}
			} else {
				$error_bids['error_text'][] = __('AML module API error!','pn');
				if(current_user_can('administrator')){
					$error_bids['error_fields']['account2'] = pn_strip_input(print_r($res, true));
				} else {
					$error_bids['error_fields']['account2'] = __('AML module API error!','pn');
				}
			}
		}

	}

	return $error_bids;
}

add_filter('array_data_create_bids', 'amlbot_array_data_create_bids', 10);
function amlbot_array_data_create_bids($array){
global $aml_bids;
	
	if(is_array($aml_bids) and isset($aml_bids['give'])){
		$aml_give = pn_strip_input_array($aml_bids['give']);
		$array['aml_give'] = @json_encode($aml_give, JSON_UNESCAPED_UNICODE);
	}
	
	if(is_array($aml_bids) and isset($aml_bids['get'])){
		$aml_get = pn_strip_input_array($aml_bids['get']);
		$array['aml_get'] = @json_encode($aml_get, JSON_UNESCAPED_UNICODE);
	}	
	
	return $array;
}
/* end check bid */

/* chech txid from merch */
add_filter('change_bidstatus', 'amlbot_change_bidstatus', 100, 6);
function amlbot_change_bidstatus($item, $set_status, $place, $user_or_system, $old_status, $direction=''){
global $wpdb, $premiumbox;	
	if($place != 'admin_panel'){
		if($set_status == 'realpay' or $set_status == 'verify'){
			$direction_id = intval(is_isset($item, 'direction_id'));
			if(!isset($direction->id)){
				$direction = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."directions WHERE auto_status='1' AND id='$direction_id'");
			}
			if(isset($direction->id)){
				$aml = @json_decode($direction->aml, true);
				if(!is_array($aml)){ $aml = array(); }
				
				$aml_merch = intval(is_isset($aml, 'aml_merch'));
				$aml_merch_sum = is_sum(is_isset($aml, 'aml_merch_sum'));
				
				$sum = is_sum(is_isset($item, 'sum1dc'));
				
				$access_id = pn_strip_input($premiumbox->get_option('amlbot','access_id'));
				$access_key = pn_strip_input($premiumbox->get_option('amlbot','access_key'));
		
				$class = new AMLClass($access_id, $access_key);
				
				$v = get_currency_data();
				
				$address = pn_strip_input($item->to_account);
				$currency_code = pn_strip_input($item->currency_code_give);
				$currency_code = currency_code_amlbot($currency_code, $item->currency_id_give, $v);
				
				$trans_in = pn_strip_input($item->trans_in);
				
				if($aml_merch > 0 and $sum >= $aml_merch_sum and $address and $trans_in){
					$res = $class->verify_trans($address, $currency_code, $trans_in);
					if(isset($res['result'], $res['data']) and $res['result'] and isset($res['data']['riskscore'])){
						$aml_merch = array();
						$aml_merch['score'] = is_sum($res['data']['riskscore']) * 100;
						$aml_merch['signals'] = is_isset($res['data'],'signals');
						$aml_merch = pn_strip_input_array($aml_merch);
						$arr = array();
						$arr['aml_merch'] = @json_encode($aml_merch, JSON_UNESCAPED_UNICODE);
						$wpdb->update($wpdb->prefix ."exchange_bids", $arr, array('id'=>$item->id)); 
						$item = pn_object_replace($item, $arr);
					} 
				}
			}
		}
	}
	return $item;
}	
/* end chech txid from merch */

function get_amltitle($title){
	$arr = array(
	
	);
	if(isset($arr[$title])){
		$title = $arr[$title];
	}
	$title = str_replace('_',' ', $title);
	return $title;
}

function get_amlscore($score){
	$score = intval($score);
	$color = 'e2c501';
	if($score <= 25){
		$color = '02900c';
	}
	if($score > 74){
		$color = 'ff0000';
	}	
	return '<span style="font-weight: 600; color: #'. $color .';">'. $score .'%</span>';
}

function get_amldata($rating_data){
	$temp = '';
	if(is_array($rating_data) and count($rating_data) > 0){
		$temp .= '<div class="amlrat_cont">';
			foreach($rating_data as $key => $zn){
				$znp = $zn * 100;
				$temp .= '<div class="amlrat_line"><strong>'. get_amltitle($key) .'</strong>: '. $znp .'%</div>';
			}
		$temp .= '</div>';
	}
	return $temp;
}

/* admin bids */
add_filter('onebid_col1', 'onebid_col1_amlbot', 0, 4);
function onebid_col1_amlbot($actions, $item, $data_fs, $v){
	
	$assets = get_aml_assets(1);
	$trans_in = pn_strip_input($item->trans_in);
	$address = pn_strip_input($item->to_account);
	$currency_code = $item->currency_code_give;
	if($trans_in and $address and in_array($currency_code, $assets)){
		
		$aml = @json_decode($item->aml_merch, true);
		$score = intval(is_isset($aml, 'score'));
		$signals = is_isset($aml, 'signals');
		
		$nactions = array();
		$nactions['aml_trans_in'] = array(
			'type' => 'text',
			'title' => __('AMLbot Risk','pn'),
			'label' => '<span class="amlrat"><span class="amlrat_title" style="cursor: pointer;">'. get_amlscore($score) .'</span><div class="amlrat_abs" style="display: none;">'. get_amldata($signals) .'</div></span> [<a href="'. pn_link('aml_checkbid') .'&item_id='. $item->id .'&set=0' .'">'. __('Check','pn') .'</a>]',
		);
		$actions = pn_array_insert($actions, 'trans_in', $nactions, 'after');
		
	}
	
	return $actions;
}

add_filter('onebid_col2', 'onebid_col2_amlbot', 0, 4);
function onebid_col2_amlbot($actions, $item, $data_fs, $v){
	
	$assets = get_aml_assets(1);
	$account_give = pn_strip_input($item->account_give);
	$currency_code = $item->currency_code_give;
	if($account_give and in_array($currency_code, $assets)){
		
		$aml = @json_decode($item->aml_give, true);
		$score = intval(is_isset($aml, 'score'));
		$signals = is_isset($aml, 'signals');
		
		$nactions = array();
		$nactions['aml_account_give'] = array(
			'type' => 'text',
			'title' => __('AMLbot Risk','pn'),
			'label' => '<span class="amlrat"><span class="amlrat_title" style="cursor: pointer;">'. get_amlscore($score) .'</span><div class="amlrat_abs" style="display: none;">'. get_amldata($signals) .'</div></span> [<a href="'. pn_link('aml_checkbid') .'&item_id='. $item->id .'&set=1' .'">'. __('Check','pn') .'</a>]',
		);
		$actions = pn_array_insert($actions, 'account_give', $nactions, 'after');
		
	}
	
	return $actions;
}

add_filter('onebid_col3', 'onebid_col3_amlbot', 0, 4);
function onebid_col3_amlbot($actions, $item, $data_fs, $v){
	
	$assets = get_aml_assets(1);
	$account_get = pn_strip_input($item->account_get);
	$currency_code = $item->currency_code_get;
	if($account_get and in_array($currency_code, $assets)){
		
		$aml = @json_decode($item->aml_get, true);
		$score = intval(is_isset($aml, 'score'));
		$signals = is_isset($aml, 'signals');
		
		$nactions = array();
		$nactions['aml_account_get'] = array(
			'type' => 'text',
			'title' => __('AMLbot Risk','pn'),
			'label' => '<span class="amlrat"><span class="amlrat_title" style="cursor: pointer;">'. get_amlscore($score) .'</span><div class="amlrat_abs" style="display: none;">'. get_amldata($signals) .'</div></span> [<a href="'. pn_link('aml_checkbid') .'&item_id='. $item->id .'&set=2' .'">'. __('Check','pn') .'</a>]',
		);
		$actions = pn_array_insert($actions, 'account_get', $nactions, 'after');
		
	}
	
	return $actions;
}
/* end admin bids */

function currency_code_amlbot($currency_code, $currency_id, $v){
	$currency_code = mb_strtoupper($currency_code);
	if($currency_code == 'USDT'){
		if(isset($v[$currency_id])){
			$vd = $v[$currency_id];
			$xml_value = mb_strtoupper(is_xml_value($vd->xml_value));
			if($xml_value == 'USDT'){
				return 'TetherOMNI';
			} elseif($xml_value == 'USDTERC'){
				return 'TetherERC20';			
			} elseif($xml_value == 'USDTTRC'){	
				return 'TetherTRC20';			
			}
		}				
	}
	return $currency_code;
}

add_action('premium_action_aml_checkbid','def_aml_checkbid');
function def_aml_checkbid(){
global $wpdb, $premiumbox;
	if(current_user_can('administrator') or current_user_can('pn_bids')){
		$bid_id = intval(is_param_get('item_id'));
		$item = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE id='$bid_id'");
		if(isset($item->id)){
			$set = intval(is_param_get('set'));
			
			$access_id = pn_strip_input($premiumbox->get_option('amlbot','access_id'));
			$access_key = pn_strip_input($premiumbox->get_option('amlbot','access_key'));
		
			$class = new AMLClass($access_id, $access_key);			
			
			$assets = get_aml_assets();
			
			$v = get_currency_data();
			
			if($set == 1){
				$account = $item->account_give;
				$currency_code = currency_code_amlbot($item->currency_code_give, $item->currency_id_give, $v);
				if($account and in_array($currency_code, $assets)){
					$res = $class->verify_address($account, $currency_code);
					if(isset($res['result'], $res['data']) and $res['result'] and isset($res['data']['riskscore'])){
						$aml = array();
						$aml['score'] = is_sum($res['data']['riskscore']) * 100;
						$aml['signals'] = is_isset($res['data'],'signals');
						$aml = pn_strip_input_array($aml);
						$arr = array();
						$arr['aml_give'] = @json_encode($aml, JSON_UNESCAPED_UNICODE);
						$wpdb->update($wpdb->prefix ."exchange_bids", $arr, array('id'=>$item->id)); 
					} 		
				}
			} elseif($set == 2){
				$account = $item->account_get;
				$currency_code = currency_code_amlbot($item->currency_code_get, $item->currency_id_get, $v);
				if($account and in_array($currency_code, $assets)){
					$res = $class->verify_address($account, $currency_code);
					if(isset($res['result'], $res['data']) and $res['result'] and isset($res['data']['riskscore'])){
						$aml = array();
						$aml['score'] = is_sum($res['data']['riskscore']) * 100;
						$aml['signals'] = is_isset($res['data'],'signals');
						$aml = pn_strip_input_array($aml);
						$arr = array();
						$arr['aml_get'] = @json_encode($aml, JSON_UNESCAPED_UNICODE);
						$wpdb->update($wpdb->prefix ."exchange_bids", $arr, array('id'=>$item->id)); 
					} 		
				}
			} else {
				$address = pn_strip_input($item->to_account);
				$trans_in = pn_strip_input($item->trans_in);
				$currency_code = currency_code_amlbot($item->currency_code_give, $item->currency_id_give, $v);
				if($address and $trans_in and in_array($currency_code, $assets)){
					$res = $class->verify_trans($address, $currency_code, $trans_in);
					if(isset($res['result'], $res['data']) and $res['result'] and isset($res['data']['riskscore'])){
						$aml = array();
						$aml['score'] = is_sum($res['data']['riskscore']) * 100;
						$aml['signals'] = is_isset($res['data'],'signals');
						$aml = pn_strip_input_array($aml);
						$arr = array();
						$arr['aml_merch'] = @json_encode($aml, JSON_UNESCAPED_UNICODE);
						$wpdb->update($wpdb->prefix ."exchange_bids", $arr, array('id'=>$item->id)); 
					} 
				}				
			}
			
			wp_redirect(admin_url('admin.php?page=pn_bids&bidid=' . $bid_id));
			exit;
		}	
	}	
}

/* shortcode */
add_filter('direction_instruction', 'amlbot_direction_instruction', 100010, 3);
function amlbot_direction_instruction($instruction, $txt_name, $direction){
global $wpdb, $premiumbox, $bids_data;	
	$not_status = array('timeline_txt', 'description_txt');
	if(!in_array($txt_name, $not_status) and isset($bids_data->id)){
		
		$amlrisk = '';
		
		$arrs = array(
			'aml_merch' => __('transaction','pn'),
			'aml_give' => __('account Give','pn'),
			'aml_get' => __('account Send','pn')
		);
		$score = 0;
		
		$html = '';
		
		$r=0;
		foreach($arrs as $arr => $title){
			$aml = @json_decode($bids_data->$arr, true);
			if(is_array($aml)){ $r++;
				$now_score = intval(is_isset($aml, 'score'));
				$signals = is_isset($aml, 'signals');
				$score = $score + $now_score;
				$html .= '<div class="amlrat_subwrap" style="padding: 0 0 5px 0; margin: 0 0 5px 0; border-bottom: 1px solid #ddd;">';
				$html .= '<div class="amlrat_subtitle" style="padding: 0 0 5px 0;">'. $title .' - '. get_amlscore($now_score) .'</div>';
				$html .= get_amldata($signals);
				$html .= '</div>';
			}
		}
		
		if($r > 0){
			$score = $score / $r;
			$amlrisk = '<div class="amlrat_span"><a href="https://amlbot.com/ru/chto-my-analiziruem/" target="_blank">' . __('AMLbot Risk','pn') . '</a> (<a href="https://amlbot.com/ru/chto-my-analiziruem/" target="_blank">?</a>) - <span class="amlrat"><span class="amlrat_title" style="cursor: pointer;">'. get_amlscore($score) .'</span>';
			$amlrisk .= '<span class="amlrat_abs" style="display: none;">'. $html .'</span></span></div>';
		}
		
		$instruction = str_replace('[amlrisk]', $amlrisk, $instruction);
	}
	return $instruction;
}

add_filter('direction_instruction_tags', 'amlbot_directions_tags', 1000, 2); 
function amlbot_directions_tags($tags, $key){
	$in_page = array('description_txt','timeline_txt','window_txt');
	if(!in_array($key, $in_page)){
		$tags['amlrisk'] = array(
			'title' => __('AMLbot Risk','pn'),
			'start' => '[amlrisk]',
		);				
	}
	return $tags;
}
/* end shortcode */

/* js */
add_action('wp_footer', 'amlbot_js');
add_action('admin_footer', 'amlbot_js');
function amlbot_js(){
?>	
<script type="text/javascript">
jQuery(function($){ 
	$(document).on('click', '.amlrat_title', function(){
		var rating_title = $(this).html();
		var rating_data = $(this).parents('.amlrat').find('.amlrat_abs').html();
		
		if(rating_data.length > 0){
			$(document).JsWindow('show',{
				id: 'update_info',
				window_class: 'update_window',
				title: '<?php _e('AML Risk','pn'); ?> - ' + rating_title,
				content: rating_data,
				shadow: 1,
			});	
		}
			
		return false;
	});
});	
</script>
<?php
}
/* end js */