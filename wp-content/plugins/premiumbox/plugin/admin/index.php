<?php
if( !defined( 'ABSPATH')){ exit(); }
 
add_action('wp_dashboard_setup', 'premiumbox_license_wp_dashboard_setup');
function premiumbox_license_wp_dashboard_setup() {
	wp_add_dashboard_widget('premiumbox_license_pn_dashboard_widget', __('License Info','pn'), 'premiumbox_dashboard_license_pn_in_admin_panel');
}
function premiumbox_dashboard_license_pn_in_admin_panel(){
global $wpdb;

	$text = __('No data available','pn');
	$end_time = get_pn_license_time();
	if($end_time){
		$time = current_time('timestamp');
		$cou_days = ceil(($end_time - $time) / 24 / 60 / 60);
		$cou_days = intval($cou_days);
		if($cou_days == 0){
			$text = ' <span class="bred_dash">'. sprintf(__('License validity period expires today. License renewal <a href="%s" target="_blank">instructions</a>','pn'), 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/prodlenie-litsenzii/') .'</span>';
		} elseif($cou_days <= 7){
			$text = ' <span class="bred">'.sprintf(__('Days till license expiration date: %1s days. License renewal <a href="%2s" target="_blank">instructions</a>','pn'), $cou_days, 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/prodlenie-litsenzii/').'</span>';
		} else {
			$text = ' '.sprintf(__('Days till license expiration date: %1s days. License renewal <a href="%2s" target="_blank">instructions</a>','pn'), $cou_days, 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/prodlenie-litsenzii/');
		}
	}
	echo $text;
}

add_action('after_pn_adminpage_title','after_pn_adminpage_title_premiumbox_license', 20, 2);
function after_pn_adminpage_title_premiumbox_license(){ 
	
	$end_time = get_pn_license_time();
	if($end_time){
		$time = current_time('timestamp');
		$cou_days = ceil(($end_time - $time) / 24 / 60 / 60);
		$cou_days = intval($cou_days);
		$text = '';
		if($cou_days == 0){
			$text = sprintf(__('License validity period expires today. License renewal <a href="%s" target="_blank">instructions</a>','pn'), 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/prodlenie-litsenzii/');
		} elseif($cou_days <= 7){
			$text = sprintf(__('Days till license expiration date: %1s days. License renewal <a href="%2s" target="_blank">instructions</a>','pn'), $cou_days, 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/prodlenie-litsenzii/');
		} 
		$form = new PremiumForm();
		if($text){
			echo '<div style="padding: 0 0 20px 0;">';
			$form->warning($text);
			echo '</div>';
		}
	}
}

add_filter('admin_footer_text', 'premiumbox_admin_footer_text', 1);
function premiumbox_admin_footer_text($text){
	$text .= '<div>&copy; '. get_copy_date('2015') .' <strong>Premium Exchanger</strong>.';
	$end_time = get_pn_license_time();
	if($end_time){
		$time = current_time('timestamp');
		$cou_days = ceil(($end_time - $time) / 24 / 60 / 60);
		$cou_days = intval($cou_days);
		if($cou_days == 0){
			$text .= ' (<span class="bred">'. sprintf(__('License validity period expires today. License renewal <a href="%s" target="_blank">instructions</a>','pn'), 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/prodlenie-litsenzii/') .'</span>)';
		} elseif($cou_days <= 7){
			$text .= ' (<span class="bred">'.sprintf(__('Days till license expiration date: %1s days. License renewal <a href="%2s" target="_blank">instructions</a>','pn'), $cou_days, 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/prodlenie-litsenzii/').'</span>)';
		} else {
			$text .= ' ('.sprintf(__('Days till license expiration date: %1s days. License renewal <a href="%2s" target="_blank">instructions</a>','pn'), $cou_days, 'https://premiumexchanger.com/'. get_lang_key(get_admin_lang()) .'/wiki/prodlenie-litsenzii/').')';
		}
	}
	$text .= '</div>';
	
	return $text;
}

add_filter('login_headerurl', 'premiumbox_login_headerurl');
function premiumbox_login_headerurl($login_header_url){
	$login_header_url = 'https://premiumexchanger.com/';
	return $login_header_url;
}

add_filter('login_headertext', 'premiumbox_login_headertext');
function premiumbox_login_headertext($login_header_title){
	$login_header_title = 'PremiumExchanger';
	return $login_header_title;
}

add_action('login_head','premiumbox_login_head');
function premiumbox_login_head(){
global $premiumbox;	
?>
<style>
.login h1 a{
	height: 108px;
	width: 108px;
	background: url(<?php echo $premiumbox->plugin_url; ?>images/admin-logo.png) no-repeat center center;	
}
</style>
<?php
}