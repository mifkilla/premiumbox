<?php
if( !defined( 'ABSPATH')){ exit(); }

function x19_info_for_wm($wm){
	$arr = array();
	$arr['err']=0;
	$arr['wmid'] = '';
	$curl_options = array(
		CURLOPT_TIMEOUT => 10,
		CURLOPT_CONNECTTIMEOUT => 10,
	);	
	$result = get_curl_parser('https://passport.webmoney.ru/asp/CertView.asp?purse='.$wm, $curl_options, 'moduls', 'x19');
	if(!$result['err']){
		$out = $result['output'];
		if(strstr($out, 'Object moved')){
			$arr['err']=1;
		} else {
			$urlwmid = '';
			if(preg_match('/WebMoney.Events" href="(.*?)">/s',$out, $item)){
				$urlwmid = trim($item[1]);
			}
			$wmid = explode('?',$urlwmid);
			$wmid = trim(is_isset($wmid,1));
			if($wmid){
				$arr['wmid'] = $wmid;
			} else {
				$arr['err'] = 1;	
			}
		}
	} else {
		$arr['err']=1;
	}		
	
	return $arr;
}

function wmid_with_purse($object, $purse){
	$res = $object->X8('', $purse)->toArray();
	$retval = intval(is_isset($res, 'retval'));
	$darr = array('wmid'=>'', 'result'=> print_r($res, true));
	if($retval == 1 and isset($res['testwmpurse']['wmid'])){
		if(isset($res['testwmpurse']['wmid']['@attributes'])){
			$darr['wmid'] = pn_maxf_mb(pn_strip_input($res['testwmpurse']['wmid'][0]),250);
		} else {
			$darr['wmid'] = pn_maxf_mb(pn_strip_input($res['testwmpurse']['wmid']),250);
		}
	}
		return $darr;
}

function x19_create_log($dir_id, $log_text){
global $premiumbox;
	
	$log = intval($premiumbox->get_option('x19', 'logs'));
	if($log){
		$logs = get_array_option($premiumbox, 'x19_logs');
		
		if(count($logs) > 200 and isset($logs[0])){
			unset($logs[0]);
		}
		
		$logs[] = current_time('mysql') . ';'. $dir_id . ';' . $log_text .';';
		
		update_array_option($premiumbox, 'x19_logs', $logs);
	}
}