<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_sci_add_variants')){
		add_action('pn_adminpage_title_all_sci_add_variants', 'def_adminpage_title_all_sci_add_variants');
		function def_adminpage_title_all_sci_add_variants(){
			$id = intval(is_param_get('item_id'));
			if($id){
				_e('Edit option','pn');
			} else {
				_e('Add option','pn');
			}
		}
	}

	if(!function_exists('def_adminpage_content_all_sci_add_variants')){
		add_action('pn_adminpage_content_all_sci_add_variants','def_adminpage_content_all_sci_add_variants');
		function def_adminpage_content_all_sci_add_variants(){
		global $wpdb;

			$form = new PremiumForm();

			$id = intval(is_param_get('item_id'));
			$data_id = 0;
			$data = '';
			
			if($id){
				$data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."sitecaptcha_images WHERE id='$id'");
				if(isset($data->id)){
					$data_id = $data->id;
				}	
			}

			if($data_id){
				$title = __('Edit option','pn');
			} else {
				$title = __('Add option','pn');
			}
			
			$back_menu = array();
			$back_menu['back'] = array(
				'link' => admin_url('admin.php?page=all_sci_variants'),
				'title' => __('Back to list','pn')
			);
			if($data_id){
				$back_menu['add'] = array(
					'link' => admin_url('admin.php?page=all_sci_add_variants'),
					'title' => __('Add new','pn')
				);	
			}
			$form->back_menu($back_menu, $data);

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
			$options['uslov'] = array(
				'view' => 'inputbig',
				'title' => __('Title','pn'),
				'default' => is_isset($data, 'uslov'),
				'name' => 'uslov',
				'ml' => 1,
			);		
			$options['img1'] = array(
				'view' => 'uploader',
				'title' => sprintf('%1s %2s', __('Image','pn'), '1'),
				'default' => is_isset($data, 'img1'),
				'name' => 'img1',
			);
			$options['img2'] = array(
				'view' => 'uploader',
				'title' => sprintf('%1s %2s', __('Image','pn'), '2'),
				'default' => is_isset($data, 'img2'),
				'name' => 'img2',
			);
			$options['img3'] = array(
				'view' => 'uploader',
				'title' => sprintf('%1s %2s', __('Image','pn'), '3'),
				'default' => is_isset($data, 'img3'),
				'name' => 'img3',
			);
			$options['variant'] = array(
				'view' => 'select',
				'title' => __('Right choice','pn'),
				'options' => array('1'=>'1','2'=>'2','3'=>'3'),
				'default' => is_isset($data, 'variant'),
				'name' => 'variant',
			);

			$params_form = array(
				'filter' => 'all_sci_addform',
				'method' => 'ajax',
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options);	
		}
	}

	if(!function_exists('def_premium_action_all_sci_add_variants')){
		add_action('premium_action_all_sci_add_variants','def_premium_action_all_sci_add_variants');
		function def_premium_action_all_sci_add_variants(){
		global $wpdb;

			only_post();
			
			$form = new PremiumForm();
			$form->send_header();
			
			pn_only_caps(array('administrator'));	
				
			$data_id = intval(is_param_post('data_id'));
			$last_data = '';
			if($data_id > 0){
				$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "sitecaptcha_images WHERE id='$data_id'");
				if(!isset($last_data->id)){
					$data_id = 0;
				}
			}		
			
			$array = array();
			$array['uslov'] = pn_strip_input(is_param_post_ml('uslov'));
			$array['img1'] = pn_strip_input(is_param_post('img1'));
			$array['img2'] = pn_strip_input(is_param_post('img2'));
			$array['img3'] = pn_strip_input(is_param_post('img3'));
			$array['variant'] = intval(is_param_post('variant'));

			$array = apply_filters('all_sci_addform_post',$array, $last_data);
			
			if($data_id){
				$res = apply_filters('item_sci_edit_before', pn_ind(), $data_id, $array, $last_data);
				if($res['ind'] == 1){
					$result = $wpdb->update($wpdb->prefix.'sitecaptcha_images', $array, array('id'=>$data_id));
					do_action('item_sci_edit', $data_id, $array, $last_data, $result);
				} else { $form->error_form(is_isset($res,'error')); }
			} else {
				$res = apply_filters('item_sci_add_before', pn_ind(), $array);
				if($res['ind'] == 1){
					$wpdb->insert($wpdb->prefix.'sitecaptcha_images', $array);
					$data_id = $wpdb->insert_id;
					do_action('item_sci_add', $data_id, $array);
				} else { $form->error_form(is_isset($res,'error')); }
			}

			$url = admin_url('admin.php?page=all_sci_add_variants&item_id='. $data_id .'&reply=true');
			$form->answer_form($url);
		}	
	}
}	