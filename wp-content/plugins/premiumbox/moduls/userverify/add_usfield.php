<?php
if( !defined( 'ABSPATH')){ exit(); }

if(is_admin()){
	if(!function_exists('def_adminpage_title_all_add_usfield')){
		add_action('pn_adminpage_title_all_add_usfield', 'def_adminpage_title_all_add_usfield');
		function def_adminpage_title_all_add_usfield(){
			$id = intval(is_param_get('item_id'));
			if($id){
				_e('Edit verification field','pn');
			} else {
				_e('Add verification field','pn');
			}
		}
	}

	if(!function_exists('def_adminpage_content_all_add_usfield')){
		add_action('pn_adminpage_content_all_add_usfield','def_adminpage_content_all_add_usfield');
		function def_adminpage_content_all_add_usfield(){
		global $wpdb;

			$form = new PremiumForm();

			$id = intval(is_param_get('item_id'));
			$data_id = 0;
			$data = '';
			
			if($id){
				$data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."uv_field WHERE id='$id'");
				if(isset($data->id)){
					$data_id = $data->id;
				}	
			}

			if($data_id){
				$title = __('Edit verification field','pn');
			} else {
				$title = __('Add verification field','pn');
			}
			
			$langs = get_langs_ml();
			$the_lang = array();
			$the_lang[0] = __('All','pn');
			foreach($langs as $lan){
				$the_lang[$lan] = get_title_forkey($lan);
			}	
			
			$back_menu = array();
			$back_menu['back'] = array(
				'link' => admin_url('admin.php?page=all_usfield'),
				'title' => __('Back to list','pn')
			);
			if($data_id){
				$back_menu['add'] = array(
					'link' => admin_url('admin.php?page=all_add_usfield'),
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
			$options['title'] = array(
				'view' => 'inputbig',
				'title' => __('Verification field title','pn'),
				'default' => is_isset($data, 'title'),
				'name' => 'title',
				'work' => 'input',
				'ml' => 1,
			);			
			$options['fieldvid'] = array(
				'view' => 'select',
				'title' => __('Verification field type','pn'),
				'options' => array('0'=> __('Text input field','pn'), '1'=> __('File','pn'), '2'=> __('Select','pn')),
				'default' => is_isset($data, 'fieldvid'),
				'name' => 'fieldvid',
			);

			$vid = intval(is_isset($data, 'fieldvid'));
			if($vid == 0){
				$cl1 = '';
				$cl2 = '';
				$cl3 = 'pn_hide';
				$cl4 = 'pn_hide';
			} elseif($vid == 1){	
				$cl1 = 'pn_hide';
				$cl2 = '';
				$cl3 = '';	
				$cl4 = 'pn_hide';
			} else {
				$cl1 = 'pn_hide';
				$cl2 = 'pn_hide';
				$cl3 = 'pn_hide';
				$cl4 = '';
			}		
			
			$cf_auto = array();
			$cf_auto[0] = '---'.__('No','pn').'---';
			$cf_auto_list = apply_filters('user_fields_in_website', array());
			foreach($cf_auto_list as $cf_k => $cf_v){
				$cf_auto[$cf_k] = is_isset($cf_v, 'title');
			}	

			$options['uv_auto'] = array(
				'view' => 'select',
				'title' => __('Autofill','pn'),
				'options' => $cf_auto,
				'default' => is_isset($data, 'uv_auto'),
				'name' => 'uv_auto',
				'class' => 'thevib thevib0 '.$cl1,
			);		
			$options['helps'] = array(
				'view' => 'textarea',
				'title' => __('Tip for field','pn'),
				'default' => is_isset($data, 'helps'),
				'name' => 'helps',
				'rows' => '10',
				'ml' => 1,
				'class' => 'thevib thevib0 thevib1 '. $cl2,
			);	
			$options['eximg'] = array(
				'view' => 'uploader',
				'title' => __('Sample image for client', 'pn'),
				'default' => is_isset($data, 'eximg'),
				'name' => 'eximg',
				'work' => 'input',
				'class' => 'thevib thevib1 '.$cl3,
			);
			$options['datas'] = array(
				'view' => 'textarea',
				'title' => __('Options (at the beginning of a new line)','pn'),
				'default' => is_isset($data, 'datas'),
				'name' => 'datas',
				'rows' => '12',
				'ml' => 1,
				'class' => 'thevib thevib2 '.$cl4,
			);			
			$options['uv_req'] = array(
				'view' => 'select',
				'title' => __('Required field','pn'),
				'options' => array('1'=>__('Yes','pn'),'0'=>__('No','pn')),
				'default' => is_isset($data, 'uv_req'),
				'name' => 'uv_req',
			);		
			$options['locale'] = array(
				'view' => 'select',
				'title' => __('Language','pn'),
				'options' => $the_lang,
				'default' => is_isset($data, 'locale'),
				'name' => 'locale',
			);		
			$options['status'] = array(
				'view' => 'select',
				'title' => __('Status','pn'),
				'options' => array('1'=>__('active field','pn'),'0'=>__('inactive field','pn')),
				'default' => is_isset($data, 'status'),
				'name' => 'status',
			);		
			
			$params_form = array(
				'filter' => 'all_usfield_addform',
				'method' => 'ajax',
				'data' => $data,
				'button_title' => __('Save','pn'),
			);
			$form->init_form($params_form, $options);	
			?>
	<script type="text/javascript">
	jQuery(function($){ 
		$('#pn_fieldvid').on('change', function(){
			var id = $(this).val();
			$('.thevib').hide();
			$('.thevib' + id).show();
			$('.premium_body').trigger('resize');
		});
	});
	</script>	
			<?php
		} 
	}

	if(!function_exists('def_premium_action_all_add_usfield')){
		add_action('premium_action_all_add_usfield','def_premium_action_all_add_usfield');
		function def_premium_action_all_add_usfield(){
		global $wpdb;	

			only_post();
			
			$form = new PremiumForm();
			$form->send_header();
			
			pn_only_caps(array('administrator','pn_userverify'));
			
			$data_id = intval(is_param_post('data_id'));
			$last_data = '';
			if($data_id > 0){
				$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE id='$data_id'");
				if(!isset($last_data->id)){
					$data_id = 0;
				}
			}	
			
			$array = array();
			$array['title'] = pn_strip_input(is_param_post_ml('title'));
			$array['fieldvid'] = intval(is_param_post('fieldvid'));
			$array['uv_auto'] = pn_strip_input(is_param_post('uv_auto'));
			$array['uv_req'] = intval(is_param_post('uv_req'));
			$array['helps'] = pn_strip_input(is_param_post_ml('helps'));
			$array['datas'] = pn_strip_input(is_param_post_ml('datas'));
			$array['locale'] = pn_strip_input(is_param_post('locale'));
			$array['eximg'] = pn_strip_input(is_param_post('eximg'));
			$array['status'] = intval(is_param_post('status'));
			
			$array = apply_filters('all_usfield_addform_post',$array,$last_data);
					
			if($data_id){	
				$res = apply_filters('item_usfield_edit_before', pn_ind(), $data_id, $array, $last_data);
				if($res['ind'] == 1){
					$result = $wpdb->update($wpdb->prefix.'uv_field', $array, array('id'=>$data_id));
					do_action('item_usfield_edit', $data_id, $array, $last_data, $result);
				} else { $form->error_form(is_isset($res,'error')); }
			} else {
				$res = apply_filters('item_usfield_add_before', pn_ind(), $array);
				if($res['ind'] == 1){
					$wpdb->insert($wpdb->prefix.'uv_field', $array);
					$data_id = $wpdb->insert_id;	
					do_action('item_usfield_add', $data_id, $array);
				} else { $form->error_form(is_isset($res,'error')); }
			}

			$url = admin_url('admin.php?page=all_add_usfield&item_id='. $data_id .'&reply=true');
			$form->answer_form($url);
		}
	}
}