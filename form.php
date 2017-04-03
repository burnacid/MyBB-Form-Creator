<?php

define('THIS_SCRIPT', "form.php");
define('IN_MYBB', 1);
require "./global.php";
require_once "./inc/class_formcreator.php";
require_once MYBB_ROOT . "inc/datahandlers/pm.php";
require_once MYBB_ROOT . "inc/datahandlers/post.php";

$formcreator = new formcreator();

if ($formcreator->get_form($mybb->input['formid'])) {

    if ($formcreator->check_allowed() && $formcreator->active == 1) {
        add_breadcrumb($formcreator->name, "form.php?formid=" . $formcreator->formid);
        $display = true;

        if ($formcreator->width) {
            $stylewidth = "width:" . $formcreator->width . ";";
        }

        if ($formcreator->labelwidth) {
            $stylelabelwidth = "width:" . $formcreator->labelwidth . ";";
        }

        if ($formcreator->class) {
            $styleclass = $formcreator->class;
        }

        $formcreator->get_fields();

        if ($mybb->request_method == "post") {
            $error_array = array();

            foreach ($formcreator->fields as $field) {
                $field->default = $mybb->input["field_" . $field->fieldid];

                if ($field->required && empty($mybb->input["field_" . $field->fieldid])) {
                    $error_array[] = "'" . $field->name . "' is empty!";
                }

                if ($field->regex && !preg_match("/" . $field->regex . "/", $mybb->input["field_" . $field->fieldid])) {
                    $error_array[] = "'" . $field->name . "' did not match the expected input!";
                }
            }

            if (count($error_array) != 0) {
                $errors = inline_error($error_array);

            } else {
                $display = false;
                
                $message = $formcreator->parse_output();
                
                // Send PM single user
                if ($formcreator->pmusers) {
                    $users = explode(",", $formcreator->pmusers);

                    foreach ($users as $user) {
                        if ($user_data = get_user($user)) {
                            $pmhandler = new PMDataHandler();
                            $pmhandler->admin_override = true;

                            $pm = array(
                                "subject" => "Form submittion: " . $formcreator->name,
                                "message" => $message,
                                "icon" => "-1",
                                "toid" => $user_data['uid'],
                                "fromid" => $mybb->user['uid'],
                                "do" => '',
                                "pmid" => '');
                            $pm['options'] = array(
                                "signature" => "0",
                                "disablesmilies" => "0",
                                "savecopy" => "0",
                                "readreceipt" => "0",
                                "allow_html" => 1);

                            $pmhandler->set_data($pm);
                            if ($pmhandler->validate_pm()) {
                                $pmhandler->insert_pm();
                            }
                        }
                    }
                }

                // Send PM groups
                if (count($formcreator->pmgroups) != 0 AND !empty($formcreator->pmgroups[0])) {
                    $group_members = get_usergroup_users($formcreator->pmgroups);

                    foreach ($group_members as $user) {
                        $pmhandler = new PMDataHandler();
                        $pmhandler->admin_override = true;
                        
                        $pm = array(
                            "subject" => "Form submittion: " . $formcreator->name,
                            "message" => $message,
                            "icon" => "-1",
                            "toid" => $user['uid'],
                            "fromid" => $mybb->user['uid'],
                            "do" => '',
                            "pmid" => '');
                        $pm['options'] = array(
                            "signature" => "0",
                            "disablesmilies" => "0",
                            "savecopy" => "0",
                            "readreceipt" => "0",
                            "allow_html" => 1);

                        $pmhandler->set_data($pm);
                        if ($pmhandler->validate_pm()) {
                            $pmhandler->insert_pm();
                        }
                    }
                }

            }
        }

        if ($display) {
            $formtitle = $formcreator->name;

            $formcontent = $formcreator->build_form();
        }

    } elseif ($formcreator->active == 0) {
        add_breadcrumb($formcreator->name, "form.php?formid=" . $formcreator->formid);

        $formtitle = "Form disabled";

        $formcontent = '<tr><td class="trow1" colspan="2">This form has been disabled for use!</td></tr>';
    } else {
        add_breadcrumb($formcreator->name, "form.php?formid=" . $formcreator->formid);

        $formtitle = "Access Denied";

        $formcontent = '<tr><td class="trow1" colspan="2">You are not allowed to use this form!</td></tr>';
    }
} else {
    add_breadcrumb("Form Creator", "form.php");

    $formtitle = "Form Creator";
    $formcontent = '<tr><td class="trow1" colspan="2">The form you are looking for doesn\'t exist!</td></tr>';
}

eval("\$form = \"" . $templates->get("formcreator_container") . "\";");

eval("\$html = \"" . $templates->get("formcreator") . "\";");

output_page($html);

?>