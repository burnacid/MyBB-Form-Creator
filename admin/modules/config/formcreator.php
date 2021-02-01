<?php

require_once MYBB_ROOT . "inc/class_formcreator.php";

$lang->load("config_formcreator");

$page->add_breadcrumb_item($lang->formcreator, "index.php?module=config-formcreator");


$sub_tabs['formcreator_forms'] = array(
    'title' => $lang->fc_view_forms,
    'link' => 'index.php?module=config-formcreator',
    'description' => $lang->fc_view_forms_desc);
$sub_tabs['formcreator_add'] = array(
    'title' => $lang->fc_create_form,
    'link' => 'index.php?module=config-formcreator&amp;action=add',
    'description' => $lang->fc_create_form_desc);
$sub_tabs['formcreator_export'] = array(
    'title' => $lang->fc_export,
    'link' => 'index.php?module=config-formcreator&amp;action=export',
    'description' => $lang->fc_export_desc);
$sub_tabs['formcreator_import'] = array(
    'title' => $lang->fc_import,
    'link' => 'index.php?module=config-formcreator&amp;action=import',
    'description' => $lang->fc_import_desc);

if ($mybb->get_input('action') == "edit") {
    $sub_tabs['formcreator_edit'] = array(
        'title' => $lang->fc_edit_form,
        'link' => 'index.php?module=config-formcreator&amp;action=edit&amp;formid=' . $mybb->input['formid'],
        'description' => $lang->fc_edit_form_desc);
}

if ($mybb->get_input('action') == "output") {
    $sub_tabs['formcreator_output'] = array(
        'title' => $lang->fc_output_template,
        'link' => 'index.php?module=config-formcreator&amp;action=output&amp;formid=' . $mybb->input['formid'],
        'description' => $lang->fc_output_template_desc);
}

if ($mybb->get_input('action') == 'fields' or $mybb->get_input('action') == 'addfield' or $mybb->get_input('action') == 'editfield' or $mybb->
    get_input('action') == 'deletefield') {
    $sub_tabs['formcreator_fields'] = array(
        'title' => $lang->fc_view_form_fields,
        'link' => 'index.php?module=config-formcreator&amp;action=fields&amp;formid=' . $mybb->input['formid'],
        'description' => $lang->fc_view_form_fields_desc);
    $sub_tabs['formcreator_addfield'] = array(
        'title' => $lang->fc_add_field,
        'link' => 'index.php?module=config-formcreator&amp;action=addfield&amp;formid=' . $mybb->input['formid'],
        'description' => $lang->fc_add_field_desc);
}

if ($mybb->get_input('action') == 'editfield') {
    $sub_tabs['formcreator_editfield'] = array(
        'title' => $lang->fc_edit_field,
        'link' => 'index.php?module=config-formcreator&amp;action=editfield&amp;formid=' . $mybb->input['formid'] . '&amp;fieldid=' . $mybb->input['fieldid'],
        'description' => $lang->fc_edit_field_desc);
}

if ($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit') {

    $formcreator = new formcreator();

    if ($mybb->get_input('action') == 'edit') {
        if ($formcreator->get_form($mybb->input['formid']) == false) {
            flash_message($lang->fc_form_edit_not_found, 'error');
            admin_redirect("index.php?module=config-formcreator");
        }

        $form = new Form("index.php?module=config-formcreator&amp;action=edit&amp;formid=" . $formcreator->formid, "post");
    } else {
        $form = new Form("index.php?module=config-formcreator&amp;action=add", "post");
    }

    if ($mybb->request_method == "post") {
        if ($mybb->input['allgroups'] == 1) {
            $mybb->input['allowedgid'] = -1;
        }

        $formcreator->load_data($mybb->input);

        $formcreator->clear_error();

        if (empty($formcreator->name)) {
            $formcreator->add_error($lang->fc_empty_formname);
        }

        if (!isset($formcreator->settings['allowedgidtype'])) {
            $formcreator->add_error($lang->fc_empty_allowed_groups_type);
        }

        if (empty($formcreator->settings['allowedgid']) && ($formcreator->settings['allowedgidtype'] == 0 or $formcreator->settings['allowedgidtype'] == 1)) {
            $formcreator->add_error($lang->fc_empty_allowed_groups);
        }

        if ($mybb->get_input('action') == 'add') {
            if ($error = $formcreator->is_error()) {
                $page->extra_messages[] = array("type" => "error", "message" => $error);
            } else {
                if ($formid = $formcreator->insert_form()) {
                    log_admin_action($formcreator->formid, $formcreator->name);

                    flash_message($lang->fc_success_form_add, 'success');
                    admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formid);
                } else {
                    flash_message($lang->fc_error_oops, 'error');
                    admin_redirect("index.php?module=config-formcreator");
                }
            }
        } elseif ($mybb->get_input('action') == 'edit') {
            if ($error = $formcreator->is_error()) {
                $page->extra_messages[] = array("type" => "error", "message" => $error);
            } else {
                if ($formcreator->update_form()) {
                    log_admin_action($formcreator->formid, $formcreator->name);

                    flash_message($lang->fc_success_form_edit, 'success');
                    admin_redirect("index.php?module=config-formcreator");
                } else {
                    flash_message($lang->fc_error_oops, 'error');
                    admin_redirect("index.php?module=config-formcreator");
                }
            }
        } else {
            flash_message($lang->fc_error_oops, 'error');
            admin_redirect("index.php?module=config-formcreator");
        }
    }

    if ($mybb->get_input('action') == 'add') {
        $page->add_breadcrumb_item($lang->fc_add_form, "");
        $page->output_header($lang->fc_add_form);
        $page->output_nav_tabs($sub_tabs, 'formcreator_add');
    } elseif ($mybb->get_input('action') == 'edit') {
        $page->add_breadcrumb_item($lang->fc_edit_form, "");
        $page->output_header($lang->fc_edit_form);
        $page->output_nav_tabs($sub_tabs, 'formcreator_edit');
    }

    $form_container = new FormContainer($lang->fc_create_new_form);
    $form_container->output_row($lang->fc_form_name." <em>*</em>", $lang->fc_form_name_desc, $form->generate_text_box('name', $formcreator->name, array('id' => 'name')),
        'name');

    $radioboxes = "";

    if ($formcreator->settings['allowedgidtype'] == -1) {
        $option = array("checked" => 1);
    } else {
        $option = array();
    }
    $radioboxes .= $form->generate_radio_button("settings[allowedgidtype]", -1, $lang->fc_allow_all_groups, $option) . "<br />";

    if ($formcreator->settings['allowedgidtype'] == 0) {
        $option = array("checked" => 1);
    } else {
        $option = array();
    }
    $radioboxes .= $form->generate_radio_button("settings[allowedgidtype]", 0, $lang->fc_allow_selected_groups, $option) . "<br />";

    if ($formcreator->settings['allowedgidtype'] == 1) {
        $option = array("checked" => 1);
    } else {
        $option = array();
    }
    $radioboxes .= $form->generate_radio_button("settings[allowedgidtype]", 1, $lang->fc_allow_unselected_groups, $option);

    $form_container->output_row($lang->fc_allowed_groups." <em>*</em>", $lang->fc_allowed_groups_desc, $radioboxes . "<br /><br />" . $form->
        generate_group_select("settings[allowedgid][]", $formcreator->settings['allowedgid'], array("multiple" => true)));
    $form_container->output_row($lang->fc_custom_denied_message, $lang->fc_custom_denied_message_desc, $form->generate_text_area("settings[customdenied]", $formcreator->settings['customdenied']));
    $form_container->output_row($lang->fc_limitusage, $lang->fc_limitusage_desc, $form->generate_numeric_field("settings[limitusage]", $formcreator->settings['limitusage']));
    $form_container->output_row($lang->fc_status." <em>*</em>", $lang->fc_status_desc, $form->generate_yes_no_radio("active", $formcreator->active));
    $form_container->end();

    $form_container = new FormContainer($lang->fc_process_options);
    $form_container->output_row($lang->fc_process_send_pm,
        $lang->fc_process_send_pm_desc, $form->
        generate_text_box("settings[pmusers]", $formcreator->settings['pmusers']));
    $form_container->output_row($lang->fc_process_send_pm_group,
        $lang->fc_process_send_pm_group_desc, $form->generate_group_select("settings[pmgroups][]",
        $formcreator->settings['pmgroups'], array("multiple" => true)));
    $form_container->output_row($lang->fc_process_post_thread, $lang->fc_process_post_thread_desc, $form->generate_forum_select("fid", $formcreator->
        fid, array('main_option' => "- ".$lang->fc_disabled." -"), true));

    $query = $db->simple_select("threadprefixes", "*");
    $prefixes = array(0 => "- ".$lang->fc_none." -");
    while ($prefix = $db->fetch_array($query)) {
        $prefixes[$prefix['pid']] = $prefix['prefix'];
    }

    $form_container->output_row($lang->fc_process_prefix,
        $lang->fc_process_prefix_desc, $form->generate_select_box("settings[prefix]",
        $prefixes, $formcreator->settings['prefix']));

    $form_container->output_row($lang->fc_process_reply_post, $lang->fc_process_reply_post_desc, $form->generate_numeric_field("tid", $formcreator->tid));
    $form_container->output_row($lang->fc_process_post_as,
        $lang->fc_process_post_as_desc,
        $form->generate_numeric_field("settings[uid]", $formcreator->settings['uid']));
    $form_container->output_row($lang->fc_override_button,
        $lang->fc_override_button_desc, $form->
        generate_on_off_radio("overridebutton", $formcreator->overridebutton));
        
    $form_container->output_row($lang->fc_custom_success_page,
        $lang->fc_custom_success_page_desc, $form->
        generate_text_box("settings[customsuccess]", $formcreator->settings['customsuccess']));
        
    $form_container->output_row($lang->fc_process_signature,
        $lang->fc_process_signature_desc, $form->
        generate_on_off_radio("settings[signature]", $formcreator->settings['signature']));
    
    //Post Icons
    if(isset($mybb->input['icon']))
	{
		$icon = $mybb->get_input('icon');
	}

    if($settings['posticon'] == 0 or !isset($settings['posticon'])){
        $iconlist = $form->generate_radio_button("settings[posticon]",0,$lang->fc_none." ",array("checked" => 1));
    }else{
        $iconlist = $form->generate_radio_button("settings[posticon]",0,$lang->fc_none." ");
    }
	
	$no_icons_checked = " checked=\"checked\"";
	// read post icons from cache, and sort them accordingly
	$posticons_cache = $cache->read("posticons");
	$posticons = array();
	foreach($posticons_cache as $posticon)
	{
		$posticons[$posticon['name']] = $posticon;
	}
	krsort($posticons);
    
    foreach($posticons as $dbicon)
	{
		$dbicon['path'] = str_replace("{theme}", $theme['imgdir'], $dbicon['path']);
		$dbicon['path'] = htmlspecialchars_uni($mybb->get_asset_url($dbicon['path']));

		if($formcreator->settings['posticon'] == $dbicon['iid'])
		{
			$checked = 1;
			$no_icons_checked = '';
		}
		else
		{
			$checked = 0;
		}
        $iconlist .= $form->generate_radio_button("settings[posticon]",$dbicon['iid'],"<img style='vertical-align: middle;' src='".$dbicon['path']."' /> ",array("checked" => $checked));
        
        
    }
    
    $form_container->output_row($lang->fc_process_posticon,$lang->fc_process_posticon_desc,$iconlist);
    
    $form_container->output_row($lang->fc_show_summary,
        $lang->fc_show_summary_desc, $form->
        generate_on_off_radio("settings[showsummary]", $formcreator->settings['showsummary']));
        
    $form_container->output_row($lang->fc_summary_parsed,
        $lang->fc_summary_parsed_desc, $form->
        generate_on_off_radio("settings[summaryparsed]", $formcreator->settings['summaryparsed']));
        
    $form_container->output_row($lang->fc_custom_summary_text, $lang->fc_custom_summary_text_desc, $form->generate_text_area("settings[customsummary]", $formcreator->settings['customsummary']));
    
    
    /*
    $form_container->output_row("Send Mail to",
    "Send a mail to the following E-mail address(es). Leave empty if you don't like to send a email. One address per line.<span style='color:red;font-weight: bold;'> (currently disabled)</span>",
    $form->generate_text_area("mail", $formcreator->settings['mail']));
    */
    $form_container->end();

    $form_container = new FormContainer($lang->fc_form_layout);
    $form_container->output_row($lang->fc_form_talbe_width, $lang->fc_form_table_width_desc,
        $form->generate_text_box("settings[width]", $formcreator->settings['width']));
    $form_container->output_row($lang->fc_label_width,
        $lang->fc_lang_width_desc, $form->generate_text_box("settings[labelwidth]",
        $formcreator->settings['labelwidth']));
    $form_container->output_row($lang->fc_class, $lang->fc_class_desc, $form->generate_text_box("class", $formcreator->class));
    $form_container->end();

    if ($mybb->get_input('action') == 'edit') {
        $buttons[] = $form->generate_submit_button($lang->fc_update_form);
    } else {
        $buttons[] = $form->generate_submit_button($lang->fc_create_form);
    }
    $form->output_submit_wrapper($buttons);
    $form->end();


} elseif ($mybb->get_input('action') == 'delete') {
    $formcreator = new formcreator();

    if (!$formcreator->get_form($mybb->input['formid'])) {
        flash_message($lang->fc_form_del_not_found, 'error');
        admin_redirect("index.php?module=config-formcreator");
    }

    if ($mybb->input['no']) {
        admin_redirect("index.php?module=config-formcreator");
    }

    if ($mybb->request_method == "post") {

        if ($formcreator->delete_form()) {
            log_admin_action($formcreator->formid, $formcreator->name);

            flash_message($lang->fc_success_form_delete, 'success');
            admin_redirect("index.php?module=config-formcreator");
        } else {
            flash_message($lang->fc_error_oops, 'error');
            admin_redirect("index.php?module=config-formcreator");
        }
    } else {
        $page->output_confirm_action("index.php?module=config-formcreator&action=delete&formid=" . $formcreator->formid,
            $lang->fc_confirm_form_delete ." '" . $formcreator->name . "'");
    }

} elseif ($mybb->get_input('action') == 'output') {
    $formcreator = new formcreator();

    $page->add_breadcrumb_item($lang->fc_form_output_template, "");
    $page->output_header($lang->fc_form_output_template);
    $page->output_nav_tabs($sub_tabs, 'formcreator_output');

    // Load SCEditor scripts
    if($mybb->version_code < 1821) {
        echo '<link rel="stylesheet" href="' . $mybb->settings['bburl'] . '/jscripts/sceditor/editor_themes/mybb.css" type="text/css" media="all">
    <script type="text/javascript" src="' . $mybb->settings['bburl'] . '/jscripts/sceditor/jquery.sceditor.bbcode.min.js"></script>
    <script type="text/javascript" src="' . $mybb->settings['bburl'] . '/jscripts/bbcodes_sceditor.js"></script>
    <script type="text/javascript" src="' . $mybb->settings['bburl'] . '/jscripts/sceditor/editor_plugins/undo.js"></script>';
    }else{
        echo '<link rel="stylesheet" href="' . $mybb->settings['bburl'] . '/jscripts/sceditor/themes/mybb.css" type="text/css" media="all">
    <script type="text/javascript" src="' . $mybb->settings['bburl'] . '/jscripts/sceditor/jquery.sceditor.bbcode.min.js"></script>
    <script type="text/javascript" src="' . $mybb->settings['bburl'] . '/jscripts/bbcodes_sceditor.js"></script>
    <script type="text/javascript" src="' . $mybb->settings['bburl'] . '/jscripts/sceditor/plugins/undo.js"></script>';
    }

    if (!$formcreator->get_form($mybb->input['formid'])) {
        flash_message($lang->fc_form_output_not_found, 'error');
        admin_redirect("index.php?module=config-formcreator");
    }

    if ($mybb->request_method == "post") {
        $formcreator->subjecttemplate = $mybb->input['subjecttemplate'];
        $formcreator->messagetemplate = $mybb->input['messagetemplate'];

        $testsubject = str_replace("'", "\'", $mybb->input['subjecttemplate']);
        $testmessage = str_replace("'", "\'", $mybb->input['subjecttemplate']);

        if (check_template($testsubject)) {
            flash_message($lang->fc_subject_validation_failed, 'error');
            admin_redirect("index.php?module=config-formcreator&action=output&formid=" . $formcreator->formid);
        }

        if (check_template($testmessage)) {
            flash_message($lang->fc_message_validation_failed, 'error');
            admin_redirect("index.php?module=config-formcreator&action=output&formid=" . $formcreator->formid);
        }

        if ($formcreator->update_template()) {
            flash_message($lang->fc_output_template_success, 'success');
            admin_redirect("index.php?module=config-formcreator&action=output&formid=" . $formcreator->formid);
        } else {
            flash_message($lang->fc_error_oops, 'error');
            admin_redirect("index.php?module=config-formcreator");
        }
    }

    $formcreator->get_fields();

    if (count($formcreator->fields) == 0) {
        flash_message($lang->fc_form_no_fields, 'error');
        admin_redirect("index.php?module=config-formcreator");
    }

    echo "<script>function insertToEditor(text) { $('#msgtemplate').sceditor('instance').insert(text); }</script>";

    $legend = "<a href='javascript:insertToEditor(\"{\$formname}\");'>".$lang->fc_form_name."</a><br />";
    $legend .= $lang->fc_user_info .": <a href='javascript:insertToEditor(\"{\$username}\");'>".$lang->fc_username."</a> | <a href='javascript:insertToEditor(\"{\$uid}\");'>".$lang->fc_id."</a><br /><br />";
    $legend .= $lang->fc_other .": <a href='javascript:insertToEditor(\"{\$ref}\");'>".$lang->fc_reference_number."</a><br /><br />";
    
    foreach ($formcreator->fields as $field) {
        $legend .= "(ID:" . $field->fieldid . ") " . $field->name . ": ";
        $legend .= "<a href='javascript:insertToEditor(\"{\$fieldname[" . $field->fieldid . "]}\");'>".$lang->fc_fieldname."</a> | <a href='javascript:insertToEditor(\"{\$fieldvalue[" .
            $field->fieldid . "]}\");'>".$lang->fc_fieldvalue."</a><br />";
    }

    $form = new Form("index.php?module=config-formcreator&amp;action=output&amp;formid=" . $mybb->input['formid'], "post");
    $form_container = new FormContainer($lang->fc_edit_output_template);
    $form_container->output_row($lang->fc_subject_template, $lang->fc_subject_template_desc, $form->
        generate_text_box("subjecttemplate", $formcreator->subjecttemplate));
    
    $code = build_mycode_inserter("msgtemplate", false);
    
    $form_container->output_row($lang->fc_message_template,
        $lang->fc_message_template_desc, $form->generate_text_area("messagetemplate",
        $formcreator->messagetemplate, array(
        "style" => "width: 98%;",
        "rows" => 20,
        "id" => "msgtemplate")) . $code . "<br /><br /><strong>".$lang->fc_add_variables.":<br /></strong><small>" . $legend . "</small>");

    $form_container->end();

    $buttons[] = $form->generate_submit_button($lang->fc_edit_output_template);

    $form->output_submit_wrapper($buttons);
    $form->end();

} elseif ($mybb->get_input('action') == 'addfield' || $mybb->get_input('action') == 'editfield') {
    $field = new formcreator_field();

    if ($mybb->request_method == "post" && !isset($mybb->input['fieldselect'])) {
        $field->load_data($mybb->input);

        $field->clear_error();

        if ($field->show_admin_field('name') && empty($field->name)) {
            $field->add_error($lang->fc_field_name_empty);
        }

        if ($field->show_admin_field('options') && empty($field->settings['options'])) {
            $field->add_error($lang->fc_options_empty);
        }
        
        if ($field->show_admin_field('selector') && empty($field->settings['selector'])) {
            $field->add_error($lang->fc_options_empty);
        }

        if ($field->show_admin_field('html') && empty($field->settings['html'])) {
            $field->add_error($lang->fc_html_empty);
        }

        if ($mybb->get_input('action') == 'addfield') {
            if ($error = $field->is_error()) {
                $page->extra_messages[] = array("type" => "error", "message" => $error);
            } else {
                if ($fieldid = $field->insert_field()) {
                    log_admin_action($field->formid, $fieldid, $field->name);

                    flash_message($lang->fc_success_field_add, 'success');
                    admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $field->formid);
                } else {
                    flash_message($lang->fc_error_oops, 'error');
                    admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $field->formid);
                }
            }
        } elseif ($mybb->get_input('action') == 'editfield') {
            if ($error = $field->is_error()) {
                $page->extra_messages[] = array("type" => "error", "message" => $error);
            } else {
                if ($field->update_field()) {
                    log_admin_action($field->formid, $field->fieldid, $field->name);

                    flash_message($lang->fc_success_field_edit, 'success');
                    admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $field->formid);
                } else {
                    flash_message($lang->fc_error_oops, 'error');
                    admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $field->formid);
                }
            }
        }
    } elseif ($mybb->request_method != "post" && isset($mybb->input['fieldid'])) {
        if ($field->get_field($mybb->input['fieldid'])) {

        } else {
            flash_message($lang->fc_error_oops, 'error');
            admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . intval($mybb->input['formid']));
        }
    } elseif ($mybb->request_method == "post" && isset($mybb->input['type'])) {
        $field->type = intval($mybb->input['type']);
    }

    if ($mybb->get_input('action') == 'editfield') {
        $page->add_breadcrumb_item($lang->fc_form_fields, "index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $mybb->input['formid']);
        $page->add_breadcrumb_item($lang->fc_edit_field, "");
        $page->output_header($lang->fc_edit_field);
        $page->output_nav_tabs($sub_tabs, 'formcreator_editfield');

        $form = new Form("index.php?module=config-formcreator&amp;action=editfield&amp;formid=" . $mybb->input['formid'] . "&amp;fieldid=" . $field->fieldid,
            "post");
    } else {
        $page->add_breadcrumb_item($lang->fc_form_fields, "index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $mybb->input['formid']);
        $page->add_breadcrumb_item($lang->fc_add_field, "");
        $page->output_header($lang->fc_add_field);
        $page->output_nav_tabs($sub_tabs, 'formcreator_addfield');

        $form = new Form("index.php?module=config-formcreator&amp;action=addfield&amp;formid=" . $mybb->input['formid'], "post");
    }

    $formcreator = new formcreator();

    if ($formcreator->get_form($mybb->input['formid'])) {
        if ($fieldtype = $formcreator->get_type_name($field->type) or isset($field->fieldid)) {
            $form_container = new FormContainer($lang->fc_add." " . $fieldtype);
            echo $form->generate_hidden_field("type", $field->type);

            if ($field->show_admin_field("name")) {
                $form_container->output_row($lang->fc_name." <em>*</em>", $lang->fc_field_name_desc, $form->generate_text_box("name", $field->name));
            }
            if ($field->show_admin_field("description")) {
                $form_container->output_row($lang->fc_description, $lang->fc_field_description_desc, $form->generate_text_area("description", $field->description));
            }
            if ($field->show_admin_field("placeholder")) {
                $form_container->output_row($lang->fc_placeholder, $lang->fc_field_placeholder_desc, $form->generate_text_box("settings[placeholder]", $field->settings['placeholder']));
            }
            if ($field->show_admin_field("maxlength")) {
                $form_container->output_row($lang->fc_maxlength, $lang->fc_field_maxlength_desc, $form->generate_numeric_field("settings[maxlength]", $field->settings['maxlength']));
            }
            if ($field->show_admin_field("options")) {
                $form_container->output_row($lang->fc_options." <em>*</em>", $lang->fc_field_options_desc, $form->generate_text_area("settings[options]",
                    $field->settings['options']));
            }
            if ($field->show_admin_field("selector")) {
                if($field->type==16){
                    $field_name = $lang->fc_prefixes;
                    $field_desc = $lang->fc_field_prefixes_desc;
                    $prefixes = build_prefixes();
                    if(is_array($prefixes)){
                        $options = array();
                        foreach(build_prefixes() as $prefix){
                            $options[$prefix['pid']] = $prefix['prefix'];
                        }
                    }else{
                        $options = array("" => $lang->fc_no_prefixes);
                    }
                }
                
                $form_container->output_row($field_name." <em>*</em>", $field_desc, $form->generate_select_box("settings[selector][]",$options,
                    $field->settings['selector'], array("multiple"=>true)));
            }
            if ($field->show_admin_field("format")) {
                $form_container->output_row($lang->fc_format,
                    $form->fc_field_format_desc ,
                    $form->generate_text_box("settings[format]", $field->settings['format']));
            }
            if ($field->show_admin_field("default")) {
                $form_container->output_row($lang->fc_default, $lang->fc_field_default_desc, $form->generate_text_box("default", $field->default));
            }
            if ($field->show_admin_field("required")) {
                $form_container->output_row($lang->fc_required, $lang->fc_field_required_desc, $form->generate_yes_no_radio("required", $field->required));
            }
            if ($field->show_admin_field("regex")) {
                $form_container->output_row($lang->fc_regex, $lang->fc_field_regex_desc, "<strong>/ ".$form->generate_text_box("settings[regex]", $field->settings['regex'])." /</strong>");
                $form_container->output_row($lang->fc_regex_error, $lang->fc_field_regex_error_desc, $form->generate_text_box("settings[regexerror]", $field->settings['regexerror']));
            }
            if ($field->show_admin_field("size")) {
                $form_container->output_row($lang->fc_size, $lang->fc_field_size_desc, $form->generate_numeric_field("settings[size]", $field->settings['size']));
            }
            if ($field->show_admin_field("cols")) {
                $form_container->output_row($lang->fc_cols, $lang->fc_field_cols_desc, $form->generate_numeric_field("settings[cols]", $field->settings['cols']));
            }
            if ($field->show_admin_field("rows")) {
                $form_container->output_row($lang->fc_rows, $lang->fc_field_rows_desc, $form->generate_numeric_field("settings[rows]", $field->settings['rows']));
            }
            if ($field->show_admin_field("resize")) {
                $form_container->output_row($lang->fc_resize, $lang->fc_field_resize_desc, $form->generate_yes_no_radio("settings[resize]", $field->settings['resize']));
            }
            if ($field->show_admin_field("class")) {
                $form_container->output_row($lang->fc_class, $lang->fc_field_class_desc, $form->generate_text_box("class", $field->class));
            }
            if ($field->show_admin_field("html")) {
                $form_container->output_row($lang->fc_html_block." <em>*</em>", $lang->fc_field_html_block_desc, $form->generate_text_area("settings[html]", $field->settings['html'],
                    array(
                    "rows" => "30",
                    "cols" => "300",
                    "style" => "width:97%;")));
            }
        } else {
            $form_container = new FormContainer($lang->fc_add_field);
            echo $form->generate_hidden_field("fieldselect", 1);
            
            $radio = "<table><tr><th>".$lang->fc_field_type."</th><th>".$lang->fc_field_example."</th></tr>";
            
            foreach($formcreator->types as $key => $value){
                $radio .= "<tr><td>".$form->generate_radio_button("type",$key,$value)."</td><td><img src='".$mybb->settings['bburl']."/images/formcreator/fields/".$key.".jpg' /></td></tr>";
            }
            
            $radio .= "</table>"; 
            
            $form_container->output_row($lang->fc_field_type, $lang->fc_field_type_desc, $radio);
            //$form_container->output_row($lang->fc_field_type, $lang->fc_field_type_desc, $form->generate_select_box("type", $formcreator->types));
        }

        $form_container->end();

        if ($mybb->get_input('action') == 'editfield') {
            $buttons[] = $form->generate_submit_button($lang->fc_button_update_field);
        } else {
            $buttons[] = $form->generate_submit_button($lang->fc_button_create_field);
        }
        $form->output_submit_wrapper($buttons);
        $form->end();

    } else {
        flash_message($lang->fc_add_field_unknown_form, 'error');
        admin_redirect("index.php?module=config-formcreator");
    }


} elseif ($mybb->get_input('action') == 'deletefield') {
    $formcreator = new formcreator();
    $field = new formcreator_field();

    if (!$formcreator->get_form($mybb->input['formid'])) {
        flash_message($lang->fc_delete_field_form_unknown, 'error');
        admin_redirect("index.php?module=config-formcreator");
    }

    if (!$field->get_field($mybb->input['fieldid'])) {
        flash_message($lang->fc_delete_field_unknown, 'error');
        admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formcreator->formid);
    }

    if ($mybb->input['no']) {
        admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formcreator->formid);
    }

    if ($mybb->request_method == "post") {

        if ($field->delete_field()) {
            log_admin_action($field->formid, $field->fieldid, $field->name);

            flash_message($lang->fc_delete_field_success, 'success');
            admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formcreator->formid);
        } else {
            flash_message($lang->error_oops, 'error');
            admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formcreator->formid);
        }
    } else {
        $page->output_confirm_action("index.php?module=config-formcreator&action=deletefield&formid=" . $formcreator->formid . "&amp;fieldid=" . $field->
            fieldid, $lang->fc_confirmation_delete_field ." '" . $field->name . "'");
    }

} elseif ($mybb->get_input('action') == 'orderfields') {
    $formcreator = new formcreator();

    if ($mybb->request_method == "post" && $formcreator->get_form($mybb->input['formid'])) {
        foreach ($mybb->input['fields'] as $key => $value) {
            $formcreator->order_field($key, $value);
        }

        flash_message($lang->fc_order_saved, 'success');
        admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formcreator->formid);
    } else {
        flash_message($lang->error_oops, 'error');
        admin_redirect("index.php?module=config-formcreator");
    }
} elseif ($mybb->get_input('action') == 'export') {
    

    $page->add_breadcrumb_item($lang->fc_export_form, "");
    $page->output_header($lang->fc_export_form);
    $page->output_nav_tabs($sub_tabs, 'formcreator_export');

    if ($mybb->request_method == "post" && count($mybb->input['forms'])) {
        foreach ($mybb->input['forms'] as $form) {
            $formcreator = new formcreator();
            if ($formcreator->get_form($form)) {
                $data = $formcreator->get_data();

                $data['subjecttemplate'] = $formcreator->subjecttemplate;
                $data['messagetemplate'] = $formcreator->messagetemplate;

                unset($data['formid']);

                if ($mybb->input['permissions'] == 0) {
                    unset($data['allowedgid']);
                    $data['allowedgidtype'] = -1;
                }

                if ($mybb->input['process'] == 0) {
                    unset($data['pmusers']);
                    unset($data['pmgroups']);
                    unset($data['fid']);
                    unset($data['tid']);
                    unset($data['uid']);
                    unset($data['overridebutton']);
                    unset($data['prefix']);
                    unset($data['mail']);
                }
                
                $formcreator->get_fields();
                $field_array = array();
                
                if (count($formcreator->fields) != 0) {
                    foreach ($formcreator->fields as $field) {
                        $fielddata = $field->get_data();

                        //unset($fielddata['fieldid']);
                        unset($fielddata['formid']);

                        $field_array[] = $fielddata;
                    }

                    $data['fields'] = $field_array;

                } else {
                    $data['fields'] = array();
                }
                
                if($mybb->input['usagelog'] == 1 && $db->table_exists("fc_formusage")){
                    $query = $db->simple_select("fc_formusage","*", "formid = '".$form."'");
                    $db->num_rows($query);
                    
                    $usagelog = array();
                    
                    while($log = $db->fetch_array($query)){
                        unset($log['formid']);
                        $usagelog[] = $log;
                    }
                    
                    $data['usage'] = $usagelog;
                }

                $output_array[] = $data;

            } else {
                flash_message($lang->error_oops, 'error');
                admin_redirect("index.php?module=config-formcreator");
            }
        }

        $form_container = new FormContainer();
        $form = new Form("index.php?module=config-formcreator&amp;action=export", "post");

        $form_container->output_row($lang->fc_export, $lang->fc_export_description, $form->generate_text_area("export",
            json_encode($output_array), array("style" => "width:98%;", "rows" => 25)));

        $form_container->end();
        $form->end();
    } else {
        $form_container = new FormContainer($lang->fc_export_form);
        $form = new Form("index.php?module=config-formcreator&amp;action=export", "post");

        $query = $db->simple_select("fc_forms", "formid,name");
        if ($db->num_rows($query) == 0) {
            flash_message($lang->fc_no_forms_to_export, 'error');
            admin_redirect("index.php?module=config-formcreator");
        } else {
            while ($form_data = $db->fetch_array($query)) {
                $forms .= $form->generate_check_box("forms[]", $form_data['formid'], $form_data['name']) . "<br/>";
            }

            $form_container->output_row($lang->fc_forms." <em>*</em>", $lang->fc_field_forms_desc, $forms);
            $form_container->output_row($lang->fc_export_perms,
                $lang->fc_export_perms_desc, $form->generate_on_off_radio("permissions"));
            $form_container->output_row($lang->fc_export_process_options,
                $lang->fc_export_process_options_desc, $form->generate_on_off_radio("process"));
            $form_container->output_row($lang->fc_export_usagelog,
                $lang->fc_export_usagelog_desc, $form->generate_on_off_radio("usagelog"));

            $form_container->end();

            $buttons[] = $form->generate_submit_button($lang->fc_export_form);

            $form->output_submit_wrapper($buttons);
            $form->end();
        }
    }
} elseif ($mybb->get_input('action') == 'import') {
    $formcreator = new formcreator();

    $page->add_breadcrumb_item($lang->fc_import_form, "");
    $page->output_header($lang->fc_import_form);
    $page->output_nav_tabs($sub_tabs, 'formcreator_import');


    if ($mybb->request_method == "post" && !empty($mybb->input['import'])) {
        $import = json_decode($mybb->input['import'], true);

        if (count($import)) {
            foreach ($import as $form) {
                $fields = $form['fields'];
                unset($form['fields']);
                
                $settings = $form['settings'];
                
                //Check if field exists in DB else move field to settings
                foreach($form as $key => $value){
                    if(!$formcreator->field_in_table('fc_forms',$key)){
                        if($value != '' and $value != '0'){
                            $settings[$key] = $value;
                        }
                    }else{
                    }
                }
                
                $form['settings'] = $settings;

                $formcreator->load_data($form);

                if ($formid = $formcreator->insert_form()) {
                    if (count($fields) != 0) {
                        foreach ($fields as $field_data) {
                            $field_data['formid'] = $formid;
                            
                            $settings = $field_data['settings'];
                            
                            //Check if field exists in DB else move field to settings
                            foreach($field_data as $key => $value){
                                if(!$formcreator->field_in_table('fc_fields',$key)){
                                    if($value != '' and $value != '0'){
                                        $settings[$key] = $value;
                                    }
                                }else{
                                }
                            }
                            
                            $field_data['settings'] = $settings;

                            $field = new formcreator_field();

                            $oldid = $field_data['fieldid'];
                            unset($field_data['fieldid']);

                            $field->load_data($field_data);

                            $newid = $field->insert_field();

                            $form['subjecttemplate'] = str_replace("\$fieldname[" . $oldid . "]", "\$fieldname[" . $newid . "]", $form['subjecttemplate']);
                            $form['subjecttemplate'] = str_replace("\$fieldvalue[" . $oldid . "]", "\$fieldvalue[" . $newid . "]", $form['subjecttemplate']);
                            $form['messagetemplate'] = str_replace("\$fieldname[" . $oldid . "]", "\$fieldname[" . $newid . "]", $form['messagetemplate']);
                            $form['messagetemplate'] = str_replace("\$fieldvalue[" . $oldid . "]", "\$fieldvalue[" . $newid . "]", $form['messagetemplate']);

                            $count_fields++;
                        }
                    }

                    $formcreator->subjecttemplate = $form['subjecttemplate'];
                    $formcreator->messagetemplate = $form['messagetemplate'];
                    $formcreator->update_template();
                    
                    if($form['usage']){
                        foreach ($form['usage'] as $log) {
                            $log['formid'] = $formid;
                            $db->insert_query("fc_formusage",$log);
                        }
                    }
                }
                $count_forms++;
            }

            flash_message($lang->sprintf($lang->fc_forms_imported, $count_forms, $count_fields), 'success');
            admin_redirect("index.php?module=config-formcreator");
        } else {
            flash_message($lang->fc_no_forms_imported, 'error');
            admin_redirect("index.php?module=config-formcreator&amp;action=import");
        }
    } else {
        $form_container = new FormContainer($lang->fc_import_form);
        $form = new Form("index.php?module=config-formcreator&amp;action=import", "post");

        $form_container->output_row($lang->fc_import_code." <em>*</em>", $lang->fc_import_code_desc, $form->generate_text_area("import", "", array("style" => "width:98%;",
                "rows" => 25)));

        $form_container->end();

        $buttons[] = $form->generate_submit_button($lang->fc_import_form);

        $form->output_submit_wrapper($buttons);
        $form->end();
    }
} elseif ($mybb->get_input('action') == 'fields') {
    $page->add_breadcrumb_item($lang->fc_form_fields, "");
    $page->output_header($lang->fc_form_fields);
    $page->output_nav_tabs($sub_tabs, 'formcreator_fields');

    $formcreator = new formcreator();
    if ($formcreator->get_form($mybb->input['formid'])) {

        $formcreator->get_fields();

        $table = new Table;
        $table->construct_cell('<strong>'.$lang->fc_name.'</strong>: ' . $formcreator->name, array("width" => "50%"));
        $table->construct_cell("<strong>".$lang->fc_url."</strong>: <a href='" . $mybb->settings['bburl'] . "/form.php?formid=" . $formcreator->formid . "'>" . $mybb->
            settings['bburl'] . "/form.php?formid=" . $formcreator->formid . "</a>", array("width" => "50%"));
        $table->construct_row();

        $usernames = "";
        $users_array = explode(",", $formcreator->settings['pmusers']);
        foreach ($users_array as $uid) {
            if ($pmuser = get_user($uid)) {
                $usernames .= "<br /><a href='" . $mybb->settings['bburl'] . "/members.php?uid=" . $pmuser['uid'] . "'>" . $pmuser['username'] . "</a>";
            }
        }

        if ($usernames == "") {
            $usernames = "<br />". $lang->fc_no_users_selected;
        }

        $table->construct_cell('<strong>'.$lang->fc_send_pm_to_users.'</strong>: ' . $usernames);

        $usergroups = "";
        foreach ($formcreator->settings['pmgroups'] as $gid) {
            if ($pmgroup = get_usergroup($gid)) {
                $usergroups .= "<br />" . $pmgroup['title'];
            }
        }

        if ($usergroups == "") {
            $usergroups = "<br />".$lang->fc_no_groups_selected;
        }

        $table->construct_cell('<strong>'.$lang->fc_send_pm_to_usergroups.'</strong>: ' . $usergroups);
        $table->construct_row();

        if ($forum = get_forum($formcreator->fid)) {
            $forumlink = "<a href='" . $mybb->settings['bburl'] . "/" . get_forum_link($formcreator->fid) . "'>" . $forum['name'] . "</a>";
        } elseif ($formcreator->fid == -1) {
            $forumlink = $lang->fc_no_forum_selected;
        } else {
            $forumlink = $lang->fc_forum_doesnt_exist;
        }

        $table->construct_cell('<strong>'.$lang->fc_create_thread_in_forum.'</strong>: <br />' . $forumlink);

        if ($formcreator->settings['mail'] == "") {
            $mail = "(".$lang->fc_no_mail_selected.")";
        } else {
            $mail = nl2br($formcreator->settings['mail']);
        }

        $table->construct_cell('<strong>'.$lang->fc_send_mail_to.'</strong>: <br />' . $mail);

        $table->construct_row();

        $table->output($lang->fc_form_info);

        $table = new Table;
        $table->construct_header($lang->fc_fieldname ." / ". $lang->fc_description);
        $table->construct_header($lang->fc_type, array("style" => "width: 300px;"));
        $table->construct_header($lang->fc_order, array("style" => "width: 100px;"));
        $table->construct_header("", array("style" => "width: 125px;"));

        $form = new Form("index.php?module=config-formcreator&amp;action=orderfields&amp;formid=" . $formcreator->formid, "post");

        if (count($formcreator->fields) == 0) {
            $table->construct_cell("<div align='center'>".$lang->fc_form_has_no_fields."</div>", array("colspan" => "4"));
            $table->construct_row();
        } else {
            foreach ($formcreator->fields as $field) {
                if ($field->required == 1) {
                    $required = "<em style='color:red;'>*</em>";
                } else {
                    $required = "";
                }

                $table->construct_cell("<strong>" . $field->name . $required . "</strong><br /><small>" . $field->description . "</small>");
                $table->construct_cell($formcreator->get_type_name($field->type));
                $table->construct_cell($form->generate_numeric_field("fields[" . $field->fieldid . "]", $field->order, array("style" => "width: 50px;")), array("style" =>
                        "text-align:center;"));

                $popup = new PopupMenu("field_" . $field->fieldid, $lang->options);
                $popup->add_item($lang->fc_edit_field, "index.php?module=config-formcreator&amp;action=editfield&amp;formid=" . $field->formid . "&amp;fieldid=" . $field->
                    fieldid);
                $popup->add_item($lang->fc_delete_field, "index.php?module=config-formcreator&amp;action=deletefield&amp;formid=" . $field->formid . "&amp;fieldid=" . $field->
                    fieldid);

                $table->construct_cell($popup->fetch(), array("style" => "text-align:center;"));
                $table->construct_row();
            }
        }

        $table->output($lang->fc_fields);

        $buttons[] = $form->generate_submit_button($lang->fc_save_order);
        $form->output_submit_wrapper($buttons);
        $form->end();

    } else {
        flash_message($lang->fc_form_does_not_exist, 'error');
        admin_redirect("index.php?module=config-formcreator");
    }

} else {

    $page->output_header($lang->formcreator);
    $page->output_nav_tabs($sub_tabs, 'formcreator_forms');

    $table = new Table;
    $table->construct_header($lang->fc_form_name, array());
    $table->construct_header($lang->fc_active, array());
    $table->construct_header($lang->fc_url, array());
    $table->construct_header("", array());

    $numquery = $db->simple_select('fc_forms', '*', '');
    $total = $db->num_rows($numquery);

    if ($mybb->input['page']) {
        $pagenr = intval($mybb->input['page']);
        $pagestart = (($pagenr - 1) * 10);

        if ((($pagenr - 1) * 10) > $total) {
            $pagenr = 1;
            $pagestart = 0;
        }
    } else {
        $pagenr = 1;
        $pagestart = 0;
    }

    $query = $db->simple_select('fc_forms', '*', '', array(
        "order_by" => "formid",
        "order_dir" => "DESC",
        "limit_start" => $pagestart,
        "limit" => 10));

    if (!$db->num_rows($query)) {
        $table->construct_cell('<div align="center">'.$lang->fc_no_forms.'</div>', array('colspan' => 4));
        $table->construct_row();
        $table->output($lang->fc_forms);
    } else {
        while ($form = $db->fetch_array($query)) {

            $link_fields = "index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $form['formid'];

            $table->construct_cell("<a href='" . $link_fields . "'>" . $form['name'] . "</a>");
            if ($form['active'] == 0) {
                $active = $lang->fc_no;
            } elseif ($form['active'] == 1) {
                $active = $lang->fc_yes;
            }
            $table->construct_cell($active);
            $table->construct_cell("<a href='" . $mybb->settings['bburl'] . "/form.php?formid=" . $form['formid'] . "'>" . $mybb->settings['bburl'] .
                "/form.php?formid=" . $form['formid'] . "</a>");

            $popup = new PopupMenu("form_{$form['formid']}", $lang->options);
            $popup->add_item($lang->fc_edit_form, "index.php?module=config-formcreator&amp;action=edit&amp;formid=" . $form['formid']);
            $popup->add_item($lang->fc_delete_form, "index.php?module=config-formcreator&amp;action=delete&amp;formid=" . $form['formid']);
            $popup->add_item($lang->fc_view_fields, $link_fields);
            $popup->add_item($lang->fc_change_template, "index.php?module=config-formcreator&amp;action=output&amp;formid=" . $form['formid']);

            $table->construct_cell($popup->fetch(), array('class' => 'align_center'));

            $table->construct_row();
        }
        $table->output($lang->fc_forms);

        echo draw_admin_pagination($pagenr, 10, $total, "index.php?module=config-formcreator");
    }
}

$page->output_footer();

?>