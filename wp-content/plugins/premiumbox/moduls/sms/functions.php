<?php
if( !defined( 'ABSPATH')){ exit(); }

function get_smsgate_data($m_id){
global $pn_smsgate_data;
	if(!is_array($pn_smsgate_data)){
		$pn_smsgate_data = (array)get_option('smsgate_data');
	}
	return is_isset($pn_smsgate_data,$m_id);
}

if(!class_exists('SmsGate_Premiumbox')){
	class SmsGate_Premiumbox extends Ext_Premium {
		function __construct($file, $title)
		{
			if(is_array($title)){
				return; /*deprecated */
			}				
			
			global $premiumbox;
			parent::__construct($file, $title, 'smsgate', $premiumbox);
			
			add_filter('pn_sms_send', array($this, 'send_sms'), 10, 3);
		}

		public function send($data, $html, $to){
			return 0;
		}

		public function send_sms($send, $html, $to){
			if($send != 1){
				$ids = $this->get_ids('sms', $this->name);
				foreach($ids as $id){
					$file_data = $this->get_file_data($id);
					$res = $this->send($file_data, $html, $to);
					if($res == 1){
						return 1;
						break;
					}
				}	
			}
			return $send;
		}
	}
}	