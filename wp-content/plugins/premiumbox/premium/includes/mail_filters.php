<?php
if( !defined( 'ABSPATH')){ exit(); }

if(!function_exists('premium_wp_mail_content_type')){
	add_filter('wp_mail_content_type', 'premium_wp_mail_content_type');
	function premium_wp_mail_content_type(){
		return "text/html";
	}
}

if(!function_exists('premium_html_wp_mail')){
	add_filter('wp_mail', 'premium_html_wp_mail');
	function premium_html_wp_mail($data){
		$data['message'] = ' 
		<html> 
			<head> 
				<title>'. $data['subject'] .'</title> 
			</head> 
			<body>
				'. $data['message'] .'
			</body> 
		</html>';
		return $data;
	}	
}

if(!function_exists('premium_default_wp_mail')){
	add_filter('wp_mail', 'premium_default_wp_mail', 100);
	function premium_default_wp_mail($data){
		global $or_site_url;
		
		$headers = trim(is_isset($data, 'headers'));
		if(!$headers){
			$data['headers'] = "From: ". get_bloginfo('sitename') ." <support@". str_replace(array('http://','https://','www.'),'', $or_site_url) .">\r\n";
		}
		return $data;
	}
}

if(!function_exists('standart_pn_email_send')){
	add_filter('pn_email_send', 'standart_pn_email_send', 10, 6);
	function standart_pn_email_send($result, $recipient_mail='', $subject='', $html='', $sender_name='', $sender_mail=''){
		$headers = '';
		$sender_name = trim($sender_name);
		$sender_mail = trim($sender_mail);
		if($sender_name and $sender_mail){
			$headers = "From: $sender_name <". $sender_mail .">\r\n";
		}		
		$recipient_mails = explode(',', $recipient_mail);
		foreach($recipient_mails as $mail){
			$mail = trim($mail);
			if(is_email($mail)){
				$result = wp_mail($mail, $subject, $html, $headers);
			}
		}		
		
		return $result;
	}
}

if(!function_exists('premium_recovery_mode_email')){
	/* wp-includes/class-wp-recovery-mode-email-service.php */
	add_filter('recovery_mode_email', 'premium_recovery_mode_email', 10, 2);	
	function premium_recovery_mode_email($email, $url){
		if(isset($email['to'])){
			unset($email['to']);
		}
		return $email;
	}
}