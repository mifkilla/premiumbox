<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('premium_request_ppbalans','def_premium_ppbalans');
function def_premium_ppbalans(){ 
global $wpdb, $premiumbox;

	$premiumbox->up_mode();

	$user_id = intval(is_param_get('user_id'));	
	if($user_id > 0){
		if(current_user_can('administrator') or current_user_can('pn_pp')){
	
			$lang = get_locale();
	
			$my_dir = wp_upload_dir();
			$path = $my_dir['basedir'].'/';		
			
			$file = $path.'partnermoney-'. $user_id . '-' . date('Y-m-d-H-i') .'.csv';           
			$fs = @fopen($file, 'w');
			
			$all_amount = 0;
			$partner_amount = 0;
			$archive_partner_amount = 0;
			$pay_amount = 0;
			
			$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."exchange_bids WHERE pcalc='1' AND ref_id = '$user_id' AND status='success' ORDER BY id DESC");
			$exch = '';
			foreach($items as $item){
				$partner_amount = $partner_amount + $item->partner_sum;
				$all_amount = $all_amount + $item->partner_sum;
				$exch .= $item->id . ';' . $item->partner_sum . ';;'."\n";
			}
			
			$content = get_cptgn(__('Order ID','pn')) . ';' . get_cptgn(__('Partner reward','pn')) . ';;'."\n";
			$content .= $exch;
			$content .= get_cptgn(__('Final amount of partner reward','pn')) . ';'. $partner_amount .';;'."\n";
			
			$aexch = '';
			$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."archive_exchange_bids");
			if($query == 1){
				$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."archive_exchange_bids WHERE ref_id = '$user_id' AND status='success' ORDER BY id DESC");
				foreach($items as $item){
					$arch = @unserialize($item->archive_content);
					$pcalc = intval(is_isset($arch, 'pcalc'));
					if($pcalc == 1){
						$partner_sum = is_isset($arch, 'summp');
						if(isset($arch['partner_sum'])){
							$partner_sum = $arch['partner_sum'];
						}
						$archive_partner_amount = $archive_partner_amount + $partner_sum;
						$all_amount = $all_amount + $partner_sum;
						$aexch .= $item->bid_id . ';' . $partner_sum . ';'. $item->id .';'."\n";
					}
				}
			}	
			
			if($aexch){
				$content .= get_cptgn(__('Order ID','pn')) . ';' . get_cptgn(__('Partner reward','pn')) . ';'. get_cptgn(__('Archived order ID','pn')) .';'."\n";
				$content .= $aexch;
				$content .= get_cptgn(__('Final amount of partner reward for archived orders','pn')) . ';'. $archive_partner_amount .';;';
			}
			
			$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."user_payouts WHERE auto_status = '1' AND user_id = '$user_id' AND status IN('0','1') ORDER BY id DESC");
			$pays = '';
			foreach($items as $item){
				$sum = $item->pay_sum_or;
				$pay_amount = $pay_amount + $sum;
				$all_amount = $all_amount - $sum;
				$status = 'wait';
				if($item->status == 1){
					$status = 'pay';
				}
				$pays .= $item->id . ';' . $sum . ';'. $status .';'."\n";
			}			
			
			if($pays){
				$content .= get_cptgn(__('Payout ID','pn')) . ';' . get_cptgn(__('Payout amount','pn')) . ';'. get_cptgn(__('Payout status','pn')) .';'."\n";
				$content .= $pays;
				$content .= get_cptgn(__('Final payout amount','pn')) . ';'. $pay_amount .';;'."\n";
			}
			$content .= get_cptgn(__('Final balance','pn')) . ';'. $all_amount .';;'."\n";

			
			@fwrite($fs, $content);
			@fclose($fs);	
		
			if(is_file($file)) {
				if (ob_get_level()) {
					ob_end_clean();
				}
				if($lang == 'ru_RU'){
					header('Content-Type: text/html; charset=CP1251');
				} else {
					header('Content-Type: text/html; charset=UTF8');
				}
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename=' . basename($file));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));
				readfile($file);
				unlink($file);
				exit;
			} else {
				pn_display_mess(__('Error! Cannot create file','pn'));
			}
		} else {
			pn_display_mess(__('Error! Insufficient privileges','pn'));
		}
	} else {
		pn_display_mess(__('Error! Missing user ID','pn'));
	}		
}	