<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	add_action('pn_adminpage_title_pn_add_cf', 'def_adminpage_title_pn_add_cf');
	function def_adminpage_title_pn_add_cf(){
	global $bd_data, $wpdb;	
		
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$bd_data = '';
		
		if($item_id){
			$bd_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."direction_custom_fields WHERE id='$item_id'");
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

	add_action('pn_adminpage_content_pn_add_cf','def_adminpage_content_pn_add_cf');
	function def_adminpage_content_pn_add_cf(){
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
			'link' => admin_url('admin.php?page=pn_cf'),
			'title' => __('Back to list','pn')
		);
		if($data_id){
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_cf'),
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
		
		$cf_auto = array();
		$cf_auto[0] = '---'.__('No','pn').'---';
		$cf_auto_list = apply_filters('user_fields_in_website', array());
		foreach($cf_auto_list as $cf_k => $cf_v){
			$cf_auto[$cf_k] = is_isset($cf_v, 'title');
		}		
			
		$options['cf_auto'] = array(
			'view' => 'select',
			'title' => __('Autofill','pn'),
			'options' => $cf_auto,
			'default' => is_isset($bd_data, 'cf_auto'),
			'name' => 'cf_auto',
			'class' => 'thevib thevib0 '.$cl1,
		);
			
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
		$options['cf'] = array(
			'view' => 'user_func',
			'name' => 'cfgive',
			'func_data' => $bd_data,
			'func' => 'pn_cf_add_init_cf',
			'work' => 'input_array',
		);		
		$params_form = array(
			'filter' => 'pn_cf_addform',
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

	function pn_cf_add_init_cf($bd_data){
	global $wpdb;

		$directions = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions ORDER BY site_order1 ASC");
		
		$cf_id = intval(is_isset($bd_data,'id'));
		
		$ins = array();
		$items = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."cf_directions WHERE cf_id='$cf_id'");
		foreach($items as $item){
			$ins[] = $item->direction_id;
		}
	?>
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Add field to exchange directions','pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
					if(is_array($directions)){
						foreach($directions as $direction){
							$checked = 0;
							if(in_array($direction->id, $ins)){
								$checked = 1;
							}
							$scroll_lists[] = array(
								'title' => pn_strip_input($direction->tech_name) . pn_item_status($direction, 'direction_status') . pn_item_basket($direction),
								'checked' => $checked,
								'value' => $direction->id,
							);
						}	
					}	
					echo get_check_list($scroll_lists, 'cf[]','','',1);
					?>			
					<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>	
	<?php	
	}

	add_action('premium_action_pn_add_cf','def_premium_action_pn_add_cf');
	function def_premium_action_pn_add_cf(){
	global $wpdb;

		only_post();
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator','pn_directions'));	
		
		$data_id = intval(is_param_post('data_id'));
		$last_data = '';
		if($data_id > 0){
			$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "direction_custom_fields WHERE id='$data_id'");
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
		$array['vid'] = $vid = intval(is_param_post('vid'));
		$array['uniqueid'] = pn_strip_input(is_param_post('uniqueid'));
		$array['cf_req'] = intval(is_param_post('cf_req'));	
		if($vid == 1){
			$array['cf_auto'] = 0;
			$array['datas'] = pn_strip_input(is_param_post_ml('datas'));		
		} else {
			$array['cf_auto'] = pn_strip_input(is_param_post_ml('cf_auto'));
			$array['datas'] = '';	
		}
		
		$ui = wp_get_current_user();
		$user_id = intval(is_isset($ui, 'ID'));

		$array['edit_date'] = current_time('mysql');
		$array['edit_user_id'] = $user_id;
		$array['auto_status'] = 1;			

		$array = apply_filters('pn_cf_addform_post',$array, $last_data);
		
		if($data_id){
			$res = apply_filters('item_cf_edit_before', pn_ind(), $data_id, $array, $last_data);
			if($res['ind'] == 1){
				$result = $wpdb->update($wpdb->prefix.'direction_custom_fields', $array, array('id' => $data_id));
				do_action('item_cf_edit', $data_id, $array, $last_data, $result);
			} else { $form->error_form(is_isset($res,'error')); }
		} else {
			$res = apply_filters('item_cf_add_before', pn_ind(), $array);
			if($res['ind'] == 1){
				$array['create_date'] = current_time('mysql');
				$result = $wpdb->insert($wpdb->prefix.'direction_custom_fields', $array);
				$data_id = $wpdb->insert_id;
				if($result){
					do_action('item_cf_add', $data_id, $array);
				}
			} else { $form->error_form(is_isset($res,'error')); }
		}
		
		if($data_id){
			$cfs_del = array();
			$cf_currency = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."cf_directions WHERE cf_id='$data_id'");
			foreach($cf_currency as $cf_item){
				$cfs_del[$cf_item->direction_id] = $cf_item->direction_id;
			}	
			if(isset($_POST['cf']) and is_array($_POST['cf'])){
				$cf = $_POST['cf'];	
				foreach($cf as $index => $direction_id){
					$direction_id = intval($direction_id);
					if(!in_array($direction_id,$cfs_del)){		
						$arr = array();
						$arr['direction_id'] = $direction_id;
						$arr['cf_id'] = $data_id;
						$arr['place_id'] = 0;
						$wpdb->insert($wpdb->prefix.'cf_directions', $arr);	
					} else {
						unset($cfs_del[$direction_id]);
					}
				}
			}	
			foreach($cfs_del as $direction_id){
				$wpdb->query("DELETE FROM ".$wpdb->prefix."cf_directions WHERE direction_id = '$direction_id' AND cf_id='$data_id'");			
			}		
		}	

		$url = admin_url('admin.php?page=pn_add_cf&item_id='. $data_id .'&reply=true');
		$form->answer_form($url);
	}
}	