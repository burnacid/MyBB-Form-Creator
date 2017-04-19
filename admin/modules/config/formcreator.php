<?php

require_once MYBB_ROOT . "inc/class_formcreator.php";

$page->add_breadcrumb_item("Form Creator", "index.php?module=config-formcreator");


$sub_tabs['formcreator_forms'] = array(
    'title' => 'View Forms',
    'link' => 'index.php?module=config-formcreator',
    'description' => 'View all forms created for this website');
$sub_tabs['formcreator_add'] = array(
    'title' => 'Create New Form',
    'link' => 'index.php?module=config-formcreator&amp;action=add',
    'description' => 'Create a new form for this website');
$sub_tabs['formcreator_export'] = array(
    'title' => 'Export',
    'link' => 'index.php?module=config-formcreator&amp;action=export',
    'description' => 'Export forms for backuping or sharing');
$sub_tabs['formcreator_import'] = array(
    'title' => 'Import',
    'link' => 'index.php?module=config-formcreator&amp;action=import',
    'description' => 'Import forms from a export code');

if ($mybb->get_input('action') == "edit") {
    $sub_tabs['formcreator_edit'] = array(
        'title' => 'Edit Form',
        'link' => 'index.php?module=config-formcreator&amp;action=edit&amp;formid=' . $mybb->input['formid'],
        'description' => "Change the settings of the form");
}

if ($mybb->get_input('action') == "output") {
    $sub_tabs['formcreator_output'] = array(
        'title' => 'Form Output Template',
        'link' => 'index.php?module=config-formcreator&amp;action=output&amp;formid=' . $mybb->input['formid'],
        'description' => "Change the output template for this form. Leave fields empty to use the default outputs");
}

if ($mybb->get_input('action') == 'fields' or $mybb->get_input('action') == 'addfield' or $mybb->get_input('action') == 'editfield' or $mybb->
    get_input('action') == 'deletefield') {
    $sub_tabs['formcreator_fields'] = array(
        'title' => 'View Form Fields',
        'link' => 'index.php?module=config-formcreator&amp;action=fields&amp;formid=' . $mybb->input['formid'],
        'description' => "Change the form fields. Add/Edit or Delete");
    $sub_tabs['formcreator_addfield'] = array(
        'title' => 'Add Field',
        'link' => 'index.php?module=config-formcreator&amp;action=addfield&amp;formid=' . $mybb->input['formid'],
        'description' => "Add a field");
}

if ($mybb->get_input('action') == 'editfield') {
    $sub_tabs['formcreator_editfield'] = array(
        'title' => 'Edit Field',
        'link' => 'index.php?module=config-formcreator&amp;action=editfield&amp;formid=' . $mybb->input['formid'] . '&amp;fieldid=' . $mybb->input['fieldid'],
        'description' => "Edit a field");
}

if ($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit') {

    $formcreator = new formcreator();

    if ($mybb->get_input('action') == 'edit') {
        if ($formcreator->get_form($mybb->input['formid']) == false) {
            flash_message("The form you tried to edit doesn't exist!", 'error');
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
            $formcreator->add_error("Form Name is empty!");
        }

        if (!isset($formcreator->allowedgidtype)) {
            $formcreator->add_error("The way allowed groups are handled wasn't set");
        }

        if (empty($formcreator->allowedgid) && ($formcreator->allowedgidtype == 0 or $formcreator->allowedgidtype == 1)) {
            $formcreator->add_error("There were no allowed groups selected!");
        }

        if ($mybb->get_input('action') == 'add') {
            if ($error = $formcreator->is_error()) {
                $page->extra_messages[] = array("type" => "error", "message" => $error);
            } else {
                if ($formid = $formcreator->insert_form()) {
                    log_admin_action($formcreator->formid, $formcreator->name);

                    flash_message("The form is added succesfully. You can now configure fields.", 'success');
                    admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formid);
                } else {
                    flash_message("Oops something went wrong!", 'error');
                    admin_redirect("index.php?module=config-formcreator");
                }
            }
        } elseif ($mybb->get_input('action') == 'edit') {
            if ($error = $formcreator->is_error()) {
                $page->extra_messages[] = array("type" => "error", "message" => $error);
            } else {
                if ($formcreator->update_form()) {
                    log_admin_action($formcreator->formid, $formcreator->name);

                    flash_message("The form is edited succesfully.", 'success');
                    admin_redirect("index.php?module=config-formcreator");
                } else {
                    flash_message("Oops something went wrong!", 'error');
                    admin_redirect("index.php?module=config-formcreator");
                }
            }
        } else {
            flash_message("Oops something went wrong!", 'error');
            admin_redirect("index.php?module=config-formcreator");
        }
    }

    if ($mybb->get_input('action') == 'add') {
        $page->add_breadcrumb_item("Add Form", "");
        $page->output_header("Add Form");
        $page->output_nav_tabs($sub_tabs, 'formcreator_add');
    } elseif ($mybb->get_input('action') == 'edit') {
        $page->add_breadcrumb_item("Edit Form", "");
        $page->output_header("Edit Form");
        $page->output_nav_tabs($sub_tabs, 'formcreator_edit');
    }

    $form_container = new FormContainer("Create a new Form");
    $form_container->output_row("Form Name <em>*</em>", "The title of the form", $form->generate_text_box('name', $formcreator->name, array('id' => 'name')),
        'name');

    $radioboxes = "";

    if ($formcreator->allowedgidtype == -1) {
        $option = array("checked" => 1);
    } else {
        $option = array();
    }
    $radioboxes .= $form->generate_radio_button("allowedgidtype", -1, "Allow ALL groups", $option) . "<br />";

    if ($formcreator->allowedgidtype == 0) {
        $option = array("checked" => 1);
    } else {
        $option = array();
    }
    $radioboxes .= $form->generate_radio_button("allowedgidtype", 0, "Allow selected groups", $option) . "<br />";

    if ($formcreator->allowedgidtype == 1) {
        $option = array("checked" => 1);
    } else {
        $option = array();
    }
    $radioboxes .= $form->generate_radio_button("allowedgidtype", 1, "Allow all BUT selected groups", $option);

    $form_container->output_row("Allowed Groups <em>*</em>", "Which groups are allowed to use this form", $radioboxes . "<br /><br />" . $form->
        generate_group_select("allowedgid[]", $formcreator->allowedgid, array("multiple" => true)));
    $form_container->output_row("Status <em>*</em>", "Is this form active yes or no?", $form->generate_yes_no_radio("active", $formcreator->active));
    $form_container->end();

    $form_container = new FormContainer("Process Options");
    $form_container->output_row("Send PM to user(s)",
        "Send a PM to the User IDs defined here. If you do not want to trigger a PM leave this empty. Multiple users comma seperated.", $form->
        generate_text_box("pmusers", $formcreator->pmusers));
    $form_container->output_row("Send PM to Groups",
        "Send a PM to the Users within the selected groups. If you do not want to trigger a group PM select nothing.", $form->generate_group_select("pmgroups[]",
        $formcreator->pmgroups, array("multiple" => true)));
    $form_container->output_row("Post within forum", "Create a new thread within the selected forum", $form->generate_forum_select("fid", $formcreator->
        fid, array('main_option' => "- DISABLED -"), true));

    $query = $db->simple_select("threadprefixes", "*");
    $prefixes = array(0 => "- None -");
    while ($prefix = $db->fetch_array($query)) {
        $prefixes[$prefix['pid']] = $prefix['prefix'];
    }

    $form_container->output_row("Thread prefix",
        "Select a thread prefix for the thread that will be made. Only has use when option for Post within forum is set.", $form->generate_select_box("prefix",
        $prefixes, $formcreator->prefix));

    $form_container->output_row("Post within thread", "Create a post within the given thread ID", $form->generate_numeric_field("tid", $formcreator->tid));
    $form_container->output_row("Post as user",
        "Which user is used to post a thread, post or reply. (leave empty to use the user who submits the form, set to -1 to use the Form Creator Bot as user)",
        $form->generate_numeric_field("uid", $formcreator->uid));
    /*
    $form_container->output_row("Send Mail to",
    "Send a mail to the following E-mail address(es). Leave empty if you don't like to send a email. One address per line.<span style='color:red;font-weight: bold;'> (currently disabled)</span>",
    $form->generate_text_area("mail", $formcreator->mail));
    */
    $form_container->end();

    $form_container = new FormContainer("Form Layout");
    $form_container->output_row("Form table width", "Set the width of the table containing the form (either in pixels or percentage, e.g. 100px or 75% )",
        $form->generate_text_box("width", $formcreator->width));
    $form_container->output_row("Label column width",
        "Set the width of the table column containing the field labels (either in pixels or percentage, e.g. 100px or 75% )", $form->generate_text_box("labelwidth",
        $formcreator->labelwidth));
    $form_container->output_row("Class", "Set the class of the table containing the form", $form->generate_text_box("class", $formcreator->class));
    $form_container->end();

    if ($mybb->get_input('action') == 'edit') {
        $buttons[] = $form->generate_submit_button("Update Form");
    } else {
        $buttons[] = $form->generate_submit_button("Create Form");
    }
    $form->output_submit_wrapper($buttons);
    $form->end();


} elseif ($mybb->get_input('action') == 'delete') {
    $formcreator = new formcreator();

    if (!$formcreator->get_form($mybb->input['formid'])) {
        flash_message("The form you are trying to delete doesn't exist", 'error');
        admin_redirect("index.php?module=config-formcreator");
    }

    if ($mybb->input['no']) {
        admin_redirect("index.php?module=config-formcreator");
    }

    if ($mybb->request_method == "post") {

        if ($formcreator->delete_form()) {
            log_admin_action($formcreator->formid, $formcreator->name);

            flash_message("The form was succesfully deleted", 'success');
            admin_redirect("index.php?module=config-formcreator");
        } else {
            flash_message("Oops something went wrong!", 'error');
            admin_redirect("index.php?module=config-formcreator");
        }
    } else {
        $page->output_confirm_action("index.php?module=config-formcreator&action=delete&formid=" . $formcreator->formid,
            "Are you sure you would like to delete '" . $formcreator->name . "'");
    }

} elseif ($mybb->get_input('action') == 'output') {
    $formcreator = new formcreator();

    $page->add_breadcrumb_item("Form Output Template", "");
    $page->output_header("Form Output Template");
    $page->output_nav_tabs($sub_tabs, 'formcreator_output');

    if (!$formcreator->get_form($mybb->input['formid'])) {
        flash_message("The form output you are trying to edit doesn't exist", 'error');
        admin_redirect("index.php?module=config-formcreator");
    }

    if ($mybb->request_method == "post") {
        $formcreator->subjecttemplate = $mybb->input['subjecttemplate'];
        $formcreator->messagetemplate = $mybb->input['messagetemplate'];

        $testsubject = str_replace("'", "\'", $mybb->input['subjecttemplate']);
        $testmessage = str_replace("'", "\'", $mybb->input['subjecttemplate']);

        if (check_template($testsubject)) {
            flash_message("Validation of the subject template failed!", 'error');
            admin_redirect("index.php?module=config-formcreator&action=output&formid=" . $formcreator->formid);
        }

        if (check_template($testmessage)) {
            flash_message("Validation of the message template failed!", 'error');
            admin_redirect("index.php?module=config-formcreator&action=output&formid=" . $formcreator->formid);
        }

        if ($formcreator->update_template()) {
            flash_message("The form output template has been updated", 'success');
            admin_redirect("index.php?module=config-formcreator&action=output&formid=" . $formcreator->formid);
        } else {
            flash_message("Oops something went wrong!", 'error');
            admin_redirect("index.php?module=config-formcreator");
        }
    }

    $formcreator->get_fields();

    if (count($formcreator->fields) == 0) {
        flash_message("This form doesn't have any fields yet. Please add fields before you change the output template.", 'error');
        admin_redirect("index.php?module=config-formcreator");
    }

    echo "<script src='jscripts/formcreator.js'></script>";

    $legend = "<a href='javascript:addToTemplate(\"{\$formname}\");'>Form Name</a><br /><br />";
    foreach ($formcreator->fields as $field) {
        $legend .= "(ID:" . $field->fieldid . ") " . $field->name . ": ";
        $legend .= "<a href='javascript:addToTemplate(\"{\$fieldname[" . $field->fieldid . "]}\");'>Field Name</a> | <a href='javascript:addToTemplate(\"{\$fieldvalue[" .
            $field->fieldid . "]}\");'>Field Value</a><br />";
    }

    $form = new Form("index.php?module=config-formcreator&amp;action=output&amp;formid=" . $mybb->input['formid'], "post");
    $form_container = new FormContainer("Edit Output Template");
    $form_container->output_row("Subject template", "Please enter in the template string for the subject. Copy any variables from the template.", $form->
        generate_text_box("subjecttemplate", $formcreator->subjecttemplate));
    $form_container->output_row("Message template",
        "Please enter in the template for the message. You can use MyCode and the variables by clicking the legend.", $form->generate_text_area("messagetemplate",
        $formcreator->messagetemplate, array(
        "style" => "width: 98%;",
        "rows" => 20,
        "id" => "msgtemplate")) . "<br /><br /><strong>Add Variables:<br /></strong><small>" . $legend . "</small>");

    $form_container->end();

    $buttons[] = $form->generate_submit_button("Edit Output Template");

    $form->output_submit_wrapper($buttons);
    $form->end();

} elseif ($mybb->get_input('action') == 'addfield' || $mybb->get_input('action') == 'editfield') {
    $field = new formcreator_field();

    if ($mybb->request_method == "post" && !isset($mybb->input['fieldselect'])) {
        $field->load_data($mybb->input);

        $field->clear_error();

        if ($field->show_admin_field('name') && empty($field->name)) {
            $field->add_error("Field name is empty");
        }

        if ($field->show_admin_field('options') && empty($field->options)) {
            $field->add_error("There were no options entered");
        }

        if ($field->show_admin_field('html') && empty($field->html)) {
            $field->add_error("HTML block can't be empty");
        }

        if ($mybb->get_input('action') == 'addfield') {
            if ($error = $field->is_error()) {
                $page->extra_messages[] = array("type" => "error", "message" => $error);
            } else {
                if ($fieldid = $field->insert_field()) {
                    log_admin_action($field->formid, $fieldid, $field->name);

                    flash_message("The field is added succesfully", 'success');
                    admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $field->formid);
                } else {
                    flash_message("Oops something went wrong!", 'error');
                    admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $field->formid);
                }
            }
        } elseif ($mybb->get_input('action') == 'editfield') {
            if ($error = $field->is_error()) {
                $page->extra_messages[] = array("type" => "error", "message" => $error);
            } else {
                if ($fieldid = $field->update_field()) {
                    log_admin_action($field->formid, $fieldid, $field->name);

                    flash_message("The field is updated succesfully", 'success');
                    admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $field->formid);
                } else {
                    flash_message("Oops something went wrong!", 'error');
                    admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $field->formid);
                }
            }
        }
    } elseif ($mybb->request_method != "post" && isset($mybb->input['fieldid'])) {
        if ($field->get_field($mybb->input['fieldid'])) {

        } else {
            flash_message("Oops something went wrong!", 'error');
            admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . intval($mybb->input['formid']));
        }
    } elseif ($mybb->request_method == "post" && isset($mybb->input['type'])) {
        $field->type = intval($mybb->input['type']);
    }

    if ($mybb->get_input('action') == 'editfield') {
        $page->add_breadcrumb_item("Form Fields", "index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $mybb->input['formid']);
        $page->add_breadcrumb_item("Edit Field", "");
        $page->output_header("Edit Field");
        $page->output_nav_tabs($sub_tabs, 'formcreator_editfield');

        $form = new Form("index.php?module=config-formcreator&amp;action=editfield&amp;formid=" . $mybb->input['formid'] . "&amp;fieldid=" . $field->fieldid,
            "post");
    } else {
        $page->add_breadcrumb_item("Form Fields", "index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $mybb->input['formid']);
        $page->add_breadcrumb_item("Add Field", "");
        $page->output_header("Add Field");
        $page->output_nav_tabs($sub_tabs, 'formcreator_addfield');

        $form = new Form("index.php?module=config-formcreator&amp;action=addfield&amp;formid=" . $mybb->input['formid'], "post");
    }

    $formcreator = new formcreator();

    if ($formcreator->get_form($mybb->input['formid'])) {
        if ($fieldtype = $formcreator->get_type_name($field->type) or isset($field->fieldid)) {
            $form_container = new FormContainer("Add " . $fieldtype);
            echo $form->generate_hidden_field("type", $field->type);

            if ($field->show_admin_field("name")) {
                $form_container->output_row("Name <em>*</em>", "Please enter a field name", $form->generate_text_box("name", $field->name));
            }
            if ($field->show_admin_field("description")) {
                $form_container->output_row("Description", "Write a description for the field", $form->generate_text_area("description", $field->description));
            }
            if ($field->show_admin_field("options")) {
                $form_container->output_row("Options <em>*</em>", "Please enter the options for the field. One option per line", $form->generate_text_area("options",
                    $field->options));
            }
            if ($field->show_admin_field("format")) {
                $form_container->output_row("Format",
                    "Please enter the format for the field (e.g. for dates use jQuery <a href='http://api.jqueryui.com/datepicker/#utility-formatDate'>dateformat</a>)", $form->
                    generate_text_box("format", $field->format));
            }
            if ($field->show_admin_field("default")) {
                $form_container->output_row("Default", "Enter the default value for this field", $form->generate_text_box("default", $field->default));
            }
            if ($field->show_admin_field("required")) {
                $form_container->output_row("Required", "Select if the field is required to fill.", $form->generate_yes_no_radio("required", $field->required));
            }
            if ($field->show_admin_field("regex")) {
                $form_container->output_row("Regex", "Enter a Regex to check the entered value is to the requested format", $form->generate_text_box("regex", $field->
                    regex));
            }
            if ($field->show_admin_field("size")) {
                $form_container->output_row("Size", "Enter the size of the field", $form->generate_numeric_field("size", $field->size));
            }
            if ($field->show_admin_field("cols")) {
                $form_container->output_row("Cols", "Enter the size in cols of the field", $form->generate_numeric_field("cols", $field->cols));
            }
            if ($field->show_admin_field("rows")) {
                $form_container->output_row("Rows", "Enter the size in rows of the field", $form->generate_numeric_field("rows", $field->rows));
            }
            if ($field->show_admin_field("class")) {
                $form_container->output_row("Class", "Enter a class for the field container", $form->generate_text_box("class", $field->class));
            }
            if ($field->show_admin_field("html")) {
                $form_container->output_row("HTML Block <em>*</em>", "Enter the HTML code you would like to display", $form->generate_text_area("html", $field->html,
                    array(
                    "rows" => "30",
                    "cols" => "300",
                    "style" => "width:97%;")));
            }
        } else {
            $form_container = new FormContainer("Add Field");
            echo $form->generate_hidden_field("fieldselect", 1);
            $form_container->output_row("Field type", "Select what type of field you would like to add.", $form->generate_select_box("type", $formcreator->types));
        }

        $form_container->end();

        if ($mybb->get_input('action') == 'editfield') {
            $buttons[] = $form->generate_submit_button("Update Field");
        } else {
            $buttons[] = $form->generate_submit_button("Create Field");
        }
        $form->output_submit_wrapper($buttons);
        $form->end();

    } else {
        flash_message("You are trying to add a field to a form that doesn't exist!", 'error');
        admin_redirect("index.php?module=config-formcreator");
    }


} elseif ($mybb->get_input('action') == 'deletefield') {
    $formcreator = new formcreator();
    $field = new formcreator_field();

    if (!$formcreator->get_form($mybb->input['formid'])) {
        flash_message("The field's form you are trying to delete doesn't exist", 'error');
        admin_redirect("index.php?module=config-formcreator");
    }

    if (!$field->get_field($mybb->input['fieldid'])) {
        flash_message("The field you are trying to delete doesn't exist", 'error');
        admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formcreator->formid);
    }

    if ($mybb->input['no']) {
        admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formcreator->formid);
    }

    if ($mybb->request_method == "post") {

        if ($field->delete_field()) {
            log_admin_action($formcreator->formid, $formcreator->name);

            flash_message("The field was succesfully deleted", 'success');
            admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formcreator->formid);
        } else {
            flash_message("Oops something went wrong!", 'error');
            admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formcreator->formid);
        }
    } else {
        $page->output_confirm_action("index.php?module=config-formcreator&action=deletefield&formid=" . $formcreator->formid . "&amp;fieldid=" . $field->
            fieldid, "Are you sure you would like to delete '" . $field->name . "'");
    }

} elseif ($mybb->get_input('action') == 'orderfields') {
    $formcreator = new formcreator();

    if ($mybb->request_method == "post" && $formcreator->get_form($mybb->input['formid'])) {
        foreach ($mybb->input['fields'] as $key => $value) {
            $formcreator->order_field($key, $value);
        }

        flash_message("Order saved", 'success');
        admin_redirect("index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $formcreator->formid);
    } else {
        flash_message("Oops something went wrong!", 'error');
        admin_redirect("index.php?module=config-formcreator");
    }
} elseif ($mybb->get_input('action') == 'export') {
    $formcreator = new formcreator();

    $page->add_breadcrumb_item("Export Forms", "");
    $page->output_header("Export forms");
    $page->output_nav_tabs($sub_tabs, 'formcreator_export');

    if ($mybb->request_method == "post" && count($mybb->input['forms'])) {
        foreach ($mybb->input['forms'] as $form) {
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
                    unset($data['prefix']);
                    unset($data['mail']);
                }

                $formcreator->get_fields();

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

                $output_array[] = $data;

            } else {
                flash_message("Oops something went wrong!", 'error');
                admin_redirect("index.php?module=config-formcreator");
            }
        }

        $form_container = new FormContainer("Export");
        $form = new Form("index.php?module=config-formcreator&amp;action=export", "post");

        $form_container->output_row("Export data", "Copy and save this to a file or use this to import it else where.", $form->generate_text_area("export",
            json_encode($output_array), array("style" => "width:98%;", "rows" => 25)));

        $form_container->end();
        $form->end();
    } else {
        $form_container = new FormContainer("Export forms");
        $form = new Form("index.php?module=config-formcreator&amp;action=export", "post");

        $query = $db->simple_select("fc_forms", "formid,name");
        if ($db->num_rows($query) == 0) {
            flash_message("You have no forms that can be exported!", 'error');
            admin_redirect("index.php?module=config-formcreator");
        } else {
            while ($form_data = $db->fetch_array($query)) {
                $forms .= $form->generate_check_box("forms[]", $form_data['formid'], $form_data['name']) . "<br/>";
            }

            $form_container->output_row("Forms <em>*</em>", "Which forms do you like to export?", $forms);
            $form_container->output_row("Export Permissions",
                "Do you like to export the permissions? Set this to 'OFF' if you are going to import this on other forums.", $form->generate_on_off_radio("permissions"));
            $form_container->output_row("Export Process Options",
                "Do you like to export the process options? Set this to 'OFF' if you are going to import this on other forums.", $form->generate_on_off_radio("process"));

            $form_container->end();

            $buttons[] = $form->generate_submit_button("Export Forms");

            $form->output_submit_wrapper($buttons);
            $form->end();
        }
    }
} elseif ($mybb->get_input('action') == 'import') {
    $formcreator = new formcreator();

    $page->add_breadcrumb_item("Import Forms", "");
    $page->output_header("Import forms");
    $page->output_nav_tabs($sub_tabs, 'formcreator_import');


    if ($mybb->request_method == "post" && !empty($mybb->input['import'])) {
        $import = json_decode($mybb->input['import'], true);

        if (count($import)) {
            foreach ($import as $form) {
                $fields = $form['fields'];

                $formcreator->load_data($form);

                if ($formid = $formcreator->insert_form()) {
                    if (count($fields) != 0) {
                        foreach ($fields as $field_data) {
                            $field_data['formid'] = $formid;

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
                }
                $count_forms++;
            }

            flash_message("Forms imported (" . $count_forms . " forms and " . $count_fields . " fields)", 'success');
            admin_redirect("index.php?module=config-formcreator");
        } else {
            flash_message("No forms found to import", 'error');
            admin_redirect("index.php?module=config-formcreator&amp;action=import");
        }
    } else {
        $form_container = new FormContainer("Import forms");
        $form = new Form("index.php?module=config-formcreator&amp;action=import", "post");

        $form_container->output_row("Import code <em>*</em>", "Enter the import code.", $form->generate_text_area("import", "", array("style" => "width:98%;",
                "rows" => 25)));

        $form_container->end();

        $buttons[] = $form->generate_submit_button("Import Forms");

        $form->output_submit_wrapper($buttons);
        $form->end();
    }
} elseif ($mybb->get_input('action') == 'fields') {
    $page->add_breadcrumb_item("Form Fields", "");
    $page->output_header("Form Fields");
    $page->output_nav_tabs($sub_tabs, 'formcreator_fields');

    $formcreator = new formcreator();
    if ($formcreator->get_form($mybb->input['formid'])) {

        $formcreator->get_fields();

        $table = new Table;
        $table->construct_cell('<strong>Name</strong>: ' . $formcreator->name, array("width" => "50%"));
        $table->construct_cell("<strong>URL</strong>: <a href='" . $mybb->settings['bburl'] . "/form.php?formid=" . $formcreator->formid . "'>" . $mybb->
            settings['bburl'] . "/form.php?formid=" . $formcreator->formid . "</a>", array("width" => "50%"));
        $table->construct_row();

        $usernames = "";
        $users_array = explode(",", $formcreator->pmusers);
        foreach ($users_array as $uid) {
            if ($pmuser = get_user($uid)) {
                $usernames .= "<br /><a href='" . $mybb->settings['bburl'] . "/members.php?uid=" . $pmuser['uid'] . "'>" . $pmuser['username'] . "</a>";
            }
        }

        if ($usernames == "") {
            $usernames = "<br />(No users selected)";
        }

        $table->construct_cell('<strong>Send PM to Users</strong>: ' . $usernames);

        $usergroups = "";
        foreach ($formcreator->pmgroups as $gid) {
            if ($pmgroup = get_usergroup($gid)) {
                $usergroups .= "<br />" . $pmgroup['title'];
            }
        }

        if ($usergroups == "") {
            $usergroups = "<br />(No groups selected)";
        }

        $table->construct_cell('<strong>Send PM to Usergroups</strong>: ' . $usergroups);
        $table->construct_row();

        if ($forum = get_forum($formcreator->fid)) {
            $forumlink = "<a href='" . $mybb->settings['bburl'] . "/" . get_forum_link($formcreator->fid) . "'>" . $forum['name'] . "</a>";
        } elseif ($formcreator->fid == -1) {
            $forumlink = "(No forum selected)";
        } else {
            $forumlink = "Forum doesn't exist";
        }

        $table->construct_cell('<strong>Create Thread in Forum</strong>: <br />' . $forumlink);

        if ($formcreator->mail == "") {
            $mail = "(No mail selected)";
        } else {
            $mail = nl2br($formcreator->mail);
        }

        $table->construct_cell('<strong>Send Mail to</strong>: <br />' . $mail);

        $table->construct_row();

        $table->output("Form Info");

        $table = new Table;
        $table->construct_header("Field Name / Description");
        $table->construct_header("Type", array("style" => "width: 300px;"));
        $table->construct_header("Order", array("style" => "width: 100px;"));
        $table->construct_header("", array("style" => "width: 125px;"));

        $form = new Form("index.php?module=config-formcreator&amp;action=orderfields&amp;formid=" . $formcreator->formid, "post");

        if (count($formcreator->fields) == 0) {
            $table->construct_cell("<div align='center'>This form has no fields yet!</div>", array("colspan" => "4"));
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
                $popup->add_item("Edit Field", "index.php?module=config-formcreator&amp;action=editfield&amp;formid=" . $field->formid . "&amp;fieldid=" . $field->
                    fieldid);
                $popup->add_item("Delete Field", "index.php?module=config-formcreator&amp;action=deletefield&amp;formid=" . $field->formid . "&amp;fieldid=" . $field->
                    fieldid);

                $table->construct_cell($popup->fetch(), array("style" => "text-align:center;"));
                $table->construct_row();
            }
        }

        $table->output("Fields");

        $buttons[] = $form->generate_submit_button("Save Order");
        $form->output_submit_wrapper($buttons);
        $form->end();

    } else {
        flash_message("The form you are looking for doesn't exist!", 'error');
        admin_redirect("index.php?module=config-formcreator");
    }

} else {

    $page->output_header("Form Creator");
    $page->output_nav_tabs($sub_tabs, 'formcreator_forms');

    $table = new Table;
    $table->construct_header("Form name", array());
    $table->construct_header("Active", array());
    $table->construct_header("Link / URL", array());
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
        $table->construct_cell('<div align="center">No forms</div>', array('colspan' => 4));
        $table->construct_row();
        $table->output("Forms");
    } else {
        while ($form = $db->fetch_array($query)) {

            $link_fields = "index.php?module=config-formcreator&amp;action=fields&amp;formid=" . $form['formid'];

            $table->construct_cell("<a href='" . $link_fields . "'>" . $form['name'] . "</a>");
            if ($form['active'] == 0) {
                $active = "No";
            } elseif ($form['active'] == 1) {
                $active = "Yes";
            }
            $table->construct_cell($active);
            $table->construct_cell("<a href='" . $mybb->settings['bburl'] . "/form.php?formid=" . $form['formid'] . "'>" . $mybb->settings['bburl'] .
                "/form.php?formid=" . $form['formid'] . "</a>");

            $popup = new PopupMenu("form_{$form['formid']}", $lang->options);
            $popup->add_item("Edit Form", "index.php?module=config-formcreator&amp;action=edit&amp;formid=" . $form['formid']);
            $popup->add_item("Delete Form", "index.php?module=config-formcreator&amp;action=delete&amp;formid=" . $form['formid']);
            $popup->add_item("View Fields", $link_fields);
            $popup->add_item("Change Output Template", "index.php?module=config-formcreator&amp;action=output&amp;formid=" . $form['formid']);

            $table->construct_cell($popup->fetch(), array('class' => 'align_center'));

            $table->construct_row();
        }
        $table->output("Forms");

        echo draw_admin_pagination($pagenr, 10, $total, "index.php?module=config-formcreator");
    }
}

$page->output_footer();

?>