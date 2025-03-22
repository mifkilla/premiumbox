<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('pn_plugin_api', 'telegram_pn_plugin_api');
function telegram_pn_plugin_api(){
global $wpdb, $premiumbox;	

	$api_action = trim(pn_string(is_param_get('api_action')));
	if($api_action == 'telegram'){
		
		status_header(200);
		
		$premiumbox->up_mode();
		
		$tdata = get_option('telegram_settings');
		if(!is_array($tdata)){ $tdata = array(); }
		$token = pn_strip_input(is_isset($tdata, 'token'));
		
		if(!$token){
			die('error token');
		}
		
		$telegram_token = trim(pn_string(is_param_get('telegram_token')));
		if($telegram_token != $token){
			die('error token 2');
		}
		
		$request = @file_get_contents('php://input');
		$res = @json_decode($request, true);
		
		$class = new TelegramBot($token, is_isset($tdata, 'bot_logs'), is_isset($tdata, 'answer_logs'));
		$class->create_log($res, 1);
		
		$now = array();
		if(isset($res['callback_query'])){
			if(isset($res['callback_query']['from']['id'])){
				$now['chat_id'] = $res['callback_query']['from']['id'];
			}
			if(isset($res['callback_query']['from']['is_bot'])){
				$now['is_bot'] = $res['callback_query']['from']['is_bot'];
			}
			if(isset($res['callback_query']['from']['first_name'])){
				$now['first_name'] = $res['callback_query']['from']['first_name'];
			}
			if(isset($res['callback_query']['from']['language_code'])){
				$now['language_code'] = $res['callback_query']['from']['language_code'];
			}
			if(isset($res['callback_query']['from']['username'])){
				$now['username'] = $res['callback_query']['from']['username'];
			}
			if(isset($res['callback_query']['data'])){
				$now['text'] = $res['callback_query']['data'];
			}
			$now['callback'] = 1;
		} elseif(isset($res['message'])){
			if(isset($res['message']['from']['id'])){
				$now['chat_id'] = $res['message']['from']['id'];
			}
			if(isset($res['message']['from']['is_bot'])){	
				$now['is_bot'] = $res['message']['from']['is_bot'];
			}
			if(isset($res['message']['from']['first_name'])){	
				$now['first_name'] = $res['message']['from']['first_name'];
			}
			if(isset($res['message']['from']['language_code'])){	
				$now['language_code'] = $res['message']['from']['language_code'];
			}
			if(isset($res['message']['from']['username'])){	
				$now['username'] = $res['message']['from']['username'];
			}
			if(isset($res['message']['text'])){	
				$now['text'] = $res['message']['text'];
			}
			$now['callback'] = 0;
		}
	
		$callback = intval(is_isset($now, 'callback'));
		
		$is_bot = intval(is_isset($now, 'is_bot'));
		if($is_bot == 1){
			die('your bot');
		}
		
		$chat_id = intval(is_isset($now, 'chat_id'));
		if($chat_id < 1){
			die('no chat id');
		}	
		
		$lang = mb_strtolower(pn_strip_input(is_isset($now, 'language_code')));
		if($lang){
			$lang = $lang.'_'.mb_strtoupper($lang);
		} 
		
		$first_name = pn_strip_input(is_isset($now, 'first_name'));
		
		$login = mb_strtolower(pn_strip_input(is_isset($now, 'username')));
		
		$command = trim(is_isset($now,'text'));
		
		//$wpdb->query("DELETE FROM ".$wpdb->prefix."telegram"); exit;
		
		$chat = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."telegram WHERE telegram_chat_id = '$chat_id'");
		if(!isset($chat->id)){
			$arr = array();
			$arr['telegram_chat_id'] = $chat_id;
			$arr['telegram_login'] = $login;
			$arr['create_date'] = current_time('mysql');
			$wpdb->insert($wpdb->prefix ."telegram", $arr);
			$arr['id'] = $wpdb->insert_id;
			$chat = (object)$arr;
		}
		if(isset($chat->id)){
		
			$data = @unserialize(is_isset($chat,'data'));
			
			$arr = array();
			$up = 0;
			
			if($command == '/start'){ 
				$start = intval(is_isset($data, 'command_start'));
				if($start != 1){
					$up = 1;
					$welcome_text = pn_strip_text(ctv_ml(is_isset($tdata,'welocome_text'), $lang));
					if(!$welcome_text){ $welcome_text = 'Hi, chat ID: [chat_id]'; }
					$welcome_text = str_replace('[first_name]',$first_name, $welcome_text);
					$welcome_text = str_replace('[chat_id]',$chat_id, $welcome_text);
					
					$class->send('text', $chat_id, $welcome_text);
					$data['command_start'] = 1;
				}
			}
			
			if(!$login){
				$no_login = intval(is_isset($data, 'no_login'));
				if($no_login != 1){
					$up = 1;
					$nologin_text = pn_strip_text(ctv_ml(is_isset($tdata,'nologin_text'), $lang));
					if(!$nologin_text){ $nologin_text = 'please, add your login in telegram'; }
					$nologin_text = str_replace('[first_name]',$first_name, $nologin_text);
	
					$class->send('text', $chat_id, $nologin_text);
					$data['no_login'] = 1;
				}
			}
			
			$telegram_login = pn_strip_input(is_isset($chat, 'telegram_login'));
			if($login != $telegram_login){
				if($login){
					$yeslogin_text = pn_strip_text(ctv_ml(is_isset($tdata,'yeslogin_text'), $lang));
					if(!$yeslogin_text){ $yeslogin_text = 'login added!'; }
					$yeslogin_text = str_replace('[first_name]',$first_name, $yeslogin_text);
					$yeslogin_text = str_replace('[login]',$login, $yeslogin_text);
					$class->send('text', $chat_id, $yeslogin_text);					
				}
				
				$telegram_login = $login;
				$up = 1;
				$chat = pn_object_replace($chat, array('telegram_login' => $login));
				$arr['telegram_login'] = $login;
			}				
			
			if($telegram_login){
				/* go work! */
			}
			
			$arr['data'] = @serialize($data);
			
			if($up == 1){
				$wpdb->update($wpdb->prefix ."telegram", $arr, array('id'=> $chat->id));	
			}
		}
	}
}