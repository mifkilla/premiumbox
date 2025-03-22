<?php
if( !defined( 'ABSPATH')){ exit(); }
 
if(is_admin()){
	add_action('admin_menu', 'admin_menu_reserve_settings', 100);
	function admin_menu_reserve_settings(){
	global $premiumbox;	
		add_submenu_page("pn_config", __('Reserve settings','pn'), __('Reserve settings','pn'), 'administrator', "pn_reserve_settings", array($premiumbox, 'admin_temp'));
	}

	add_action('pn_adminpage_title_pn_reserve_settings', 'pn_adminpage_title_pn_reserve_settings');
	function pn_adminpage_title_pn_reserve_settings($page){
		_e('Reserve settings','pn');
	} 

	add_action('pn_adminpage_content_pn_reserve_settings','def_pn_adminpage_content_pn_reserve_settings');
	function def_pn_adminpage_content_pn_reserve_settings(){
	global $wpdb, $premiumbox;

		$form = new PremiumForm();

		$bid_status_list = apply_filters('bid_status_list',array());
		
		$reserv_out = $premiumbox->get_option('reserv', 'out');
		if(!is_array($reserv_out)){ $reserv_out = array(); }
		
		$reserv_in = $premiumbox->get_option('reserv', 'in');
		if(!is_array($reserv_in)){ $reserv_in = array(); }
		
		$reserv_auto = $premiumbox->get_option('reserv', 'auto');
		if(!is_array($reserv_auto)){ $reserv_auto = array(); }	
	?>
	<div class="premium_body">
		<form method="post" action="<?php the_pn_link('','post'); ?>">
			<?php
			$form->h3(__('Reserve settings','pn'), __('Save','pn'));	
			?>		
			<div class="premium_standart_line">
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Change reserve Send when Order has status','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<?php 
						$scroll_lists = array();
						if(is_array($bid_status_list)){
							foreach($bid_status_list as $key => $val){
								$checked = 0;
								if(in_array($key,$reserv_in)){
									$checked = 1;
								}
								$scroll_lists[] = array(
									'title' => $val,
									'checked' => $checked,
									'value' => $key,
								);
							}	
						}	
						echo get_check_list($scroll_lists, 'reserv_in[]','','300',1);				
						?>
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>

			<div class="premium_standart_line">
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Change reserve Receive when Order has status','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<?php 
						$scroll_lists = array();
						if(is_array($bid_status_list)){
							foreach($bid_status_list as $key => $val){
								$checked = 0;
								if(in_array($key,$reserv_out)){
									$checked = 1;
								}
								$scroll_lists[] = array(
									'title' => $val,
									'checked' => $checked,
									'value' => $key,
								);
							}	
						}	
						echo get_check_list($scroll_lists, 'reserv_out[]','','300',1);				
						?>
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>

			<div class="premium_standart_line">
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Change reserve Receive when reserve formula function or auto reserve for currency To receive is enabled and when Order possesses own status','pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
						<?php 
						$scroll_lists = array();
						if(is_array($bid_status_list)){
							foreach($bid_status_list as $key => $val){
								$checked = 0;
								if(in_array($key,$reserv_auto)){
									$checked = 1;
								}
								$scroll_lists[] = array(
									'title' => $val,
									'checked' => $checked,
									'value' => $key,
								);
							}	
						}	
						echo get_check_list($scroll_lists, 'reserv_auto[]','','300',1);				
						?>
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>			
			
			<?php	
				do_action('pn_reserve_settings_option');
					
				$form->h3('', __('Save','pn'));								
			?>		
		</form>	
	</div>	
	<?php
	} 

	add_action('premium_action_pn_reserve_settings','def_premium_action_pn_reserve_settings');
	function def_premium_action_pn_reserve_settings(){
	global $wpdb, $premiumbox;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$l_arrs = array('auto','out','in');
		foreach($l_arrs as $l_arr){
			$new_reserv = array();
			$reserv = is_param_post('reserv_'.$l_arr);
			if(is_array($reserv)){
				foreach($reserv as $v){
					$v = is_status_name($v);
					if($v){
						$new_reserv[] = $v;
					}
				}
			}
			$premiumbox->update_option('reserv', $l_arr, $new_reserv);
		}

		do_action('pn_reserve_settings_option_post');

		$url = admin_url('admin.php?page=pn_reserve_settings&reply=true');
		$form->answer_form($url);
	} 
}	