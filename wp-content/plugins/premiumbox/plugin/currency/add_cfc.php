<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_add_cfc', 'def_adminpage_title_pn_add_cfc');
	function def_adminpage_title_pn_add_cfc(){
	global $bd_data, $wpdb;	
		
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$bd_data = '';
		
		if($item_id){
			$bd_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."currency_custom_fields WHERE id='$item_id'");
			if(isset($bd_data->id)){
				$data_id = $bd_data->id;
			}	
		}		
		
		if($data_id){
			_e('Edit custom field','pn');
		} else {
			_e('Add custom field','pn');
		}	
	}

	add_action('pn_adminpage_content_pn_add_cfc','def_adminpage_content_pn_add_cfc');
	function def_adminpage_content_pn_add_cfc(){
	global $bd_data, $wpdb;

		$form = new PremiumForm();

		$data_id = intval(is_isset($bd_data,'id'));
		if($data_id){
			$title = __('Edit custom field','pn');
		} else {
			$title = __('Add custom field','pn');
		}
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_cfc'),
			'title' => __('Back to list','pn')
		);
		if($data_id){
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_cfc'),
				'title' => __('Add new','pn')
			);	
		}
		$form->back_menu($back_menu, $bd_data);	

		$options = array();
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'data_id',
			'default' => $data_id,
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save','pn'),
		);
		$options['status'] = array(
			'view' => 'select',
			'title' => __('Status','pn'),
			'options' => array('1'=>__('active field','pn'),'0'=>__('inactive field','pn')),
			'default' => is_isset($bd_data, 'status'),
			'name' => 'status',
		);	
		$options['tech_name'] = array(
			'view' => 'inputbig',
			'title' => __('Custom field name (technical)','pn'),
			'default' => is_isset($bd_data, 'tech_name'),
			'name' => 'tech_name',
			'ml' => 1,
		);		
		$options['cf_name'] = array(
			'view' => 'inputbig',
			'title' => __('Custom field name','pn'),
			'default' => is_isset($bd_data, 'cf_name'),
			'name' => 'cf_name',
			'work' => 'input',
			'ml' => 1,
		);
		$options['uniqueid'] = array(
			'view' => 'inputbig',
			'title' => __('Unique ID','pn'),
			'default' => is_isset($bd_data, 'uniqueid'),
			'name' => 'uniqueid',
			'work' => 'input',
		);			
		$options['cf_hidden'] = array(
			'view' => 'select',
			'title' => __('Data visibility in order placed on a website','pn'),
			'options' => array('0'=>__('do not show data','pn'),'1'=>__('hide data','pn'),'2'=>__('do not hide first 4 symbols','pn'),'3'=>__('do not hide last 4 symbols','pn'),'4'=>__('do not hide first 4 symbols and the last 4 symbols','pn')),
			'default' => is_isset($bd_data, 'cf_hidden'),
			'name' => 'cf_hidden',
		);
		$options['line0'] = array(
			'view' => 'line',
		);		
		$options['vid'] = array(
			'view' => 'select',
			'title' => __('Custom field type','pn'),
			'options' => array('0'=> __('Text input field','pn'), '1'=> __('Options','pn')),
			'default' => is_isset($bd_data, 'vid'),
			'name' => 'vid',
		);	
		$vid = intval(is_isset($bd_data, 'vid'));
		if($vid == 0){
			$cl1 = '';
			$cl2 = 'pn_hide';
		} else {
			$cl1 = 'pn_hide';
			$cl2 = '';			
		}	
			
		$options['cf_req'] = array(
			'view' => 'select',
			'title' => __('Required field','pn'),
			'options' => array('1'=>__('Yes','pn'),'0'=>__('No','pn')),
			'default' => is_isset($bd_data, 'cf_req'),
			'name' => 'cf_req',
		);	
		$options['datas'] = array(
			'view' => 'textarea',
			'title' => __('Options (at the beginning of a new line)','pn'),
			'default' => is_isset($bd_data, 'datas'),
			'name' => 'datas',
			'rows' => '12',
			'ml' => 1,
			'class' => 'thevib thevib1 '.$cl2
		);		
		
		$options['line1'] = array(
			'view' => 'line',
		);
		$options['cfgive'] = array(
			'view' => 'user_func',
			'name' => 'cfgive',
			'func_data' => $bd_data,
			'func' => 'pn_add_cfc_init_give',
			'work' => 'input_array',
		);
		$options['cfget'] = array(
			'view' => 'user_func',
			'name' => 'cfget',
			'func_data' => $bd_data,
			'func' => 'pn_add_cfc_init_get',
			'work' => 'input_array',
		);	
		$params_form = array(
			'filter' => 'pn_cfc_addform',
			'method' => 'ajax',
			'data' => $bd_data,
			'button_title' => __('Save','pn'),
		);
		$form->init_form($params_form, $options);			
	?>
	<script type="text/javascript">
	jQuery(function($){ 
		$('#pn_vid').on('change', function(){
			var id = $(this).val();
			$('.thevib').hide();
			$('.thevib' + id).show();
			$('.premium_body').trigger('resize');
		});
	});
	</script>	
	<?php
	} 

	function pn_add_cfc_init_give($bd_data){ 
	global $wpdb;

		$currency = list_currency(__('No item','pn'));	
		
		$cf_id = intval(is_isset($bd_data,'id'));
		
		$ins = array();
		$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."cf_currency WHERE cf_id='$cf_id' AND place_id='1'");
		foreach($items as $item){
			$ins[] = $item->currency_id;
		}
	?>
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Add for currency Send','pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
					if(is_array($currency)){
						foreach($currency as $curr_id => $curr_title){
							if($curr_id > 0){
								$checked = 0;
								if(in_array($curr_id, $ins)){
									$checked = 1;
								}
								$scroll_lists[] = array(
									'title' => $curr_title,
									'checked' => $checked,
									'value' => $curr_id,
								);
							}
						}	
					}	
					echo get_check_list($scroll_lists, 'cfgive[]','','',1);
					?>			
				<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>				
	<?php
	}

	function pn_add_cfc_init_get($bd_data){
	global $wpdb;	

		$currency = list_currency(__('No item','pn'));	
		
		$cf_id = intval(is_isset($bd_data,'id'));
		
		$ins = array();
		$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."cf_currency WHERE cf_id='$cf_id' AND place_id='2'");
		foreach($items as $item){
			$ins[] = $item->currency_id;
		}	
	?>
		<div class="premium_standart_line">
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Add for currency Receive','pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
					if(is_array($currency)){
						foreach($currency as $curr_id => $curr_title){
							if($curr_id > 0){
								$checked = 0;
								if(in_array($curr_id, $ins)){
									$checked = 1;
								}
								$scroll_lists[] = array(
									'title' => $curr_title,
									'checked' => $checked,
									'value' => $curr_id,
								);
							}
						}	
					}	
					echo get_check_list($scroll_lists, 'cfget[]','','',1);
					?>			
				<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>				
	<?php
	} 

	add_action('premium_action_pn_add_cfc','def_premium_action_pn_add_cfc');
	function def_premium_action_pn_add_cfc(){
	global $wpdb;	

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_currency'));	
		
		$data_id = intval(is_param_post('data_id'));
		
		$last_data = '';
		if($data_id > 0){
			$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_custom_fields WHERE id='$data_id'");
			if(!isset($last_data->id)){
				$data_id = 0;
			}
		}	
		
		$array = array();
		$array['cf_name'] = pn_strip_input(is_param_post_ml('cf_name'));
		if(!$array['cf_name']){
			$form->error_form(__('Error! Custom field name not entered','pn'));
		}
		$tech_name = pn_strip_input(is_param_post_ml('tech_name'));
		if(!$tech_name){
			$tech_name = $array['cf_name'];
		}
		$array['tech_name'] = $tech_name;					
		$array['vid'] = intval(is_param_post('vid'));
		$array['uniqueid'] = pn_strip_input(is_param_post('uniqueid'));
		$array['cf_hidden'] = intval(is_param_post('cf_hidden'));
		$array['cf_req'] = intval(is_param_post('cf_req'));
		if($array['vid'] == 0){			
			$array['datas'] = '';	
		} else {				
			$array['datas'] = pn_strip_input(is_param_post_ml('datas'));		
		}
			
		$ui = wp_get_current_user();
		$user_id = intval(is_isset($ui, 'ID'));

		$array['edit_date'] = current_time('mysql');
		$array['edit_user_id'] = $user_id;
		$array['auto_status'] = 1;			
		$array['status'] = intval(is_param_post('status'));

		$array = apply_filters('pn_cfc_addform_post',$array, $last_data);
			
		if($data_id){
			$res = apply_filters('item_cfc_edit_before', pn_ind(), $data_id, $array, $last_data);
			if($res['ind'] == 1){
				$result = $wpdb->update($wpdb->prefix.'currency_custom_fields', $array, array('id' => $data_id));
				do_action('item_cfc_edit', $data_id, $array, $last_data, $result);
			} else { $form->error_form(is_isset($res,'error')); }
		} else {
			$res = apply_filters('item_cfc_add_before', pn_ind(), $array);
			if($res['ind'] == 1){
				$array['create_date'] = current_time('mysql');
				$result = $wpdb->insert($wpdb->prefix.'currency_custom_fields', $array);
				$data_id = $wpdb->insert_id;
				if($result){
					do_action('item_cfc_add', $data_id, $array);
				}
			} else { $form->error_form(is_isset($res,'error')); }
		}
		
		if($data_id){
			
			$cfs_del = array();
			$cf_currency = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."cf_currency WHERE cf_id='$data_id' AND place_id='1'");
			foreach($cf_currency as $cf_item){
				$cfs_del[$cf_item->currency_id] = $cf_item->currency_id;
			}	
			if(isset($_POST['cfgive']) and is_array($_POST['cfgive'])){
				$cf = $_POST['cfgive'];	
				foreach($cf as $index => $curr_id){
					$curr_id = intval($curr_id);
					if(!in_array($curr_id,$cfs_del)){		
						$arr = array();
						$arr['currency_id'] = $curr_id;
						$arr['cf_id'] = $data_id;
						$arr['place_id'] = 1;
						$wpdb->insert($wpdb->prefix.'cf_currency', $arr);	
					} else {
						unset($cfs_del[$curr_id]);
					}
				}
			}	
			foreach($cfs_del as $currency_id){
				$wpdb->query("DELETE FROM ".$wpdb->prefix."cf_currency WHERE currency_id = '$currency_id' AND cf_id='$data_id' AND place_id='1'");			
			}
			
			$cfs_del = array();
			$cf_currency = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."cf_currency WHERE cf_id='$data_id' AND place_id='2'");
			foreach($cf_currency as $cf_item){
				$cfs_del[$cf_item->currency_id] = $cf_item->currency_id;
			}	
			if(isset($_POST['cfget']) and is_array($_POST['cfget'])){
				$cf = $_POST['cfget'];	
				foreach($cf as $index => $curr_id){
					$curr_id = intval($curr_id);
					if(!in_array($curr_id,$cfs_del)){		
						$arr = array();
						$arr['currency_id'] = $curr_id;
						$arr['cf_id'] = $data_id;
						$arr['place_id'] = 2;
						$wpdb->insert($wpdb->prefix.'cf_currency', $arr);	
					} else {
						unset($cfs_del[$curr_id]);
					}
				}
			}	
			foreach($cfs_del as $currency_id){
				$wpdb->query("DELETE FROM ".$wpdb->prefix."cf_currency WHERE currency_id = '$currency_id' AND cf_id='$data_id' AND place_id='2'");			
			}		
			
		}	

		$url = admin_url('admin.php?page=pn_add_cfc&item_id='. $data_id .'&reply=true');
		$form->answer_form($url);
	}
}