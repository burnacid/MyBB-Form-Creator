<?php

define('THIS_SCRIPT', "form.php");
define('IN_MYBB', 1);
require "./global.php";
require_once "./inc/class_formcreator.php";
require_once "./inc/class_captcha.php";
require_once MYBB_ROOT . "inc/datahandlers/pm.php";
require_once MYBB_ROOT . "inc/datahandlers/post.php";
require_once MYBB_ROOT . "inc/functions_upload.php";

$lang->load("formcreator");

$formcreator = new formcreator();

if ($formcreator->get_form($mybb->input['formid'])) {

    if ($formcreator->check_allowed() && $formcreator->active == 1) {
        
        if(!$formcreator->check_usage_limit_reached()){
        
        add_breadcrumb($formcreator->name, "form.php?formid=" . $formcreator->formid);
        $display = true;

        if ($formcreator->settings['width']) {
            $stylewidth = "width:" . $formcreator->settings['width'] . ";";
        }

        if ($formcreator->settings['labelwidth']) {
            $stylelabelwidth = "width:" . $formcreator->settings['labelwidth'] . ";";
        }

        if ($formcreator->class) {
            $styleclass = $formcreator->class;
        }

        $formcreator->get_fields();

        if ($mybb->request_method == "post") {
            
            $errors = "";
            $error_array = array();
            $files = array();
            $prefix = ""; 
            
            foreach ($formcreator->fields as $field) {
                $field->default = $mybb->input["field_" . $field->fieldid];

                if ($field->required && empty($mybb->input["field_" . $field->fieldid]) && $field->type != 13 && $field->type != 14) {
                    $error_array[] = "'" . $field->name . "' ".$lang->fc_is_empty;
                }elseif($field->required && $field->type == 3 && empty($mybb->input["field_" . $field->fieldid][0])) {
                    $error_array[] = "'" . $field->name . "' ".$lang->fc_no_option_selected;
                }

                if ($field->settings['regex'] && !preg_match("/" . $field->settings['regex'] . "/", $mybb->input["field_" . $field->fieldid])) {
                    if(!empty($field->settings['regexerror'])){
                        
                    }else{
                        $error_array[] = $lang->fc_no_attachment;
                    }
                    
                }
                
                if ($field->type == 12) {
                    $captcha = new captcha();
                    if ($captcha->validate_captcha() == false) {
                        // CAPTCHA validation failed
                        foreach ($captcha->get_errors() as $error) {
                            $error_array[] = $error;
                        }
                    }
                }elseif($field->type == 13 or $field->type == 14){
                    if(!empty($_FILES["field_" . $field->fieldid]["name"]) && $field->type == 13){
                        $files[count($files)] = $_FILES["field_".$field->fieldid];
                    }elseif(!empty($_FILES["field_" . $field->fieldid]["name"][0]) && $field->type == 14){
                        $files = array_merge($files, reArrayFiles($_FILES["field_" . $field->fieldid], count($files)));
                    }elseif($field->required){
                        $error_array[] = $lang->fc_no_attachment;
                    }else{
                        $error_array[] = $lang->fc_oops;
                    }
                }elseif($field->type == 16){
                    $prefix = intval($mybb->input["field_" . $field->fieldid]);
                }
            }
            
            $posthash = "";
            
            if(count($files) != 0){
                if($formcreator->fid != 0){
                    $fid = $formcreator->fid;
                }elseif($formcreator->tid != 0){
                    $t = get_thread($formcreator->tid);
                    $fid = $t['fid'];
                    $tid = $formcreator->tid;
                }
                $forum['fid'] = $fid;
                
                $posthash = sha1(time()+(rand(1,1000) / 1000));
                $mybb->input['posthash'] = $posthash;
                foreach($files as $file){
                    $attach_result = upload_attachment($file);
                    if(!empty($attach_result["error"])){
                        $error_array[] = $attach_result["error"];
                    }
                }
            }
                
            if (count($error_array) != 0) {
                $errors = inline_error($error_array);
                if(count($files) != 0){
                    remove_attachments(0,$posthash);
                }
            } else {
                $display = false;
                $ref = $formcreator->get_next_ref();
                $post_errors = array();

                $subject = $formcreator->parse_subject();
                $message = $formcreator->parse_output();
                
                $formcreator->log_usage();
                $uid = $mybb->user['uid'];
                $username = $mybb->user['username'];
                
                if($mybb->user['usergroup'] == 1 || empty($username)){
                    $username = "Guest";
                }

                if (!empty($formcreator->settings['uid'])) {
                    if ($user = get_user($formcreator->settings['uid'])) {
                        $uid = $user['uid'];
                        $username = $user['username'];
                    } elseif ($formcreator->settings['uid'] == -1) {
                        $uid = -1;
                        $username = "Form Creator Bot";
                    }
                }

                // Send PM single user
                if ($formcreator->settings['pmusers']) {
                    $users = explode(",", $formcreator->settings['pmusers']);

                    foreach ($users as $user) {
                        if ($user_data = get_user($user)) {
                            $pmhandler = new PMDataHandler();
                            $pmhandler->admin_override = true;

                            $pm = array(
                                "subject" => $subject,
                                "message" => $message,
                                "icon" => $formcreator->settings['posticon'],
                                "toid" => $user_data['uid'],
                                "fromid" => $uid,
                                "do" => '',
                                "pmid" => '');
                            $pm['options'] = array(
                                "signature" => $formcreator->settings['signature'],
                                "disablesmilies" => "0",
                                "savecopy" => "0",
                                "readreceipt" => "0",
                                "allow_html" => 1);

                            $pmhandler->set_data($pm);
                            if ($pmhandler->validate_pm()) {
                                $pmhandler->insert_pm();
                            } else {
                                $post_errors = array_merge($post_errors, $pmhandler->get_friendly_errors());
                            }
                        }
                    }
                }

                // Send PM groups
                if (count($formcreator->settings['pmgroups']) != 0 and !empty($formcreator->settings['pmgroups'][0])) {
                    $group_members = get_usergroup_users($formcreator->settings['pmgroups']);

                    foreach ($group_members as $user) {
                        $pmhandler = new PMDataHandler();
                        $pmhandler->admin_override = true;

                        $pm = array(
                            "subject" => $subject,
                            "message" => $message,
                            "icon" => $formcreator->settings['posticon'],
                            "toid" => $user['uid'],
                            "fromid" => $uid,
                            "do" => '',
                            "pmid" => '');
                        $pm['options'] = array(
                            "signature" => $formcreator->settings['settings'],
                            "disablesmilies" => "0",
                            "savecopy" => "0",
                            "readreceipt" => "0",
                            "allow_html" => 1);

                        $pmhandler->set_data($pm);
                        if ($pmhandler->validate_pm()) {
                            $pmhandler->insert_pm();
                        } else {
                            $post_errors = array_merge($post_errors, $pmhandler->get_friendly_errors());
                        }
                    }
                }


                // Mail content
                /*
                if ($formcreator->settings['mail']) {
                if ($mybb->user['uid']) {
                $mybb->input['fromemail'] = $mybb->user['email'];
                $mybb->input['fromname'] = $mybb->user['username'];
                } else {
                $mybb->input['fromemail'] = $mybb->user['email'];
                //$mybb->input['fromname'] = $mybb->settings[''];
                }

                if ($mybb->settings['mail_handler'] == 'smtp') {
                $from = $mybb->input['fromemail'];
                } else {
                $from = "{$mybb->input['fromname']} <{$mybb->input['fromemail']}>";
                }

                $message = $lang->sprintf($lang->email_emailuser, $to_user['username'], $mybb->input['fromname'], $mybb->settings['bbname'], $mybb->settings['bburl'],
                $mybb->get_input('message'));
                my_mail($to_user['email'], $mybb->get_input('subject'), $message, $from, "", "", false, "text", "", $mybb->input['fromemail']);

                if ($mybb->settings['mail_logging'] > 0) {
                // Log the message
                $log_entry = array(
                "subject" => $db->escape_string($mybb->get_input('subject')),
                "message" => $db->escape_string($mybb->get_input('message')),
                "dateline" => TIME_NOW,
                "fromuid" => $mybb->user['uid'],
                "fromemail" => $db->escape_string($mybb->input['fromemail']),
                "touid" => $to_user['uid'],
                "toemail" => $db->escape_string($to_user['email']),
                "tid" => 0,
                "ipaddress" => $db->escape_binary($session->packedip),
                "type" => 1);
                $db->insert_query("maillogs", $log_entry);
                }
                }
                */

                // Post in Thread
                if ($formcreator->settings) {
                    if ($thread = get_thread($formcreator->tid)) {
                        
                        $mybb->input['action'] = "do_newreply";
                        
                        $posthandler = new PostDataHandler();
                        $posthandler->action = "post";
                        $posthandler->admin_override = true;

                        $new_post = array(
                            "fid" => $thread['fid'],
                            "tid" => $thread['tid'],
                            "subject" => $subject,
                            "icon" => $formcreator->settings['posticon'],
                            "uid" => $uid,
                            "username" => $username,
                            "message" => $message,
                            "ipaddress" => $session->packedip,
                            "posthash" => $posthash);

                        // Set up the thread options
                        $new_post['options'] = array(
                            "signature" => $formcreator->settings['signature'],
                            "emailnotify" => '',
                            "disablesmilies" => '0');

                        $posthandler->set_data($new_post);

                        if ($posthandler->validate_post()) {
                            $post_info = $posthandler->insert_post();
                            $pid = $post_info['pid'];
                            
                            $post = get_post($pid);

                            $forumpermissions = forum_permissions($thread['fid']);

                            if ($forumpermissions['canviewthreads'] == 1 && $post['visible'] == 1) {
                                $url = get_post_link($pid, $thread['tid']);
                            }
                        } else {
                            $post_errors = array_merge($post_errors, $posthandler->get_friendly_errors());
                        }
                    }
                }

                // Thread in Forum
                if ($formcreator->fid) {
                    if ($forum = get_forum($formcreator->fid)) {
                        
                        $mybb->input['action'] = "do_newthread";
                        $fid = $forum['fid'];
                        
                        $posthandler = new PostDataHandler();
                        $posthandler->action = "thread";
                        $posthandler->admin_override = true;
                        
                        if(empty($prefix)){
                            $prefix = $formcreator->settings['prefix'];
                        }
                        
                        $new_thread = array(
                            "fid" => $forum['fid'],
                            "subject" => $subject,
                            "prefix" => $prefix,
                            "icon" => $formcreator->settings['posticon'],
                            "uid" => $uid,
                            "username" => $username,
                            "message" => $message,
                            "ipaddress" => $session->packedip,
                            "posthash" => $posthash);

                        // Set up the thread options
                        $new_thread['options'] = array(
                            "signature" => $formcreator->settings['signature'],
                            "emailnotify" => '',
                            "disablesmilies" => '0');

                        $posthandler->set_data($new_thread);

                        if ($posthandler->validate_thread()) {
                            $thread_info = $posthandler->insert_thread();
                            $tid = $thread_info['tid'];
                            
                            $thread = get_thread($tid);
                            $post = get_post($thread['firstpost']);

                            $forumpermissions = forum_permissions($forum['fid']);

                            if ($forumpermissions['canviewthreads'] == 1 && $post['visible'] == 1) {
                                $url = get_thread_link($tid);
                            }
                        } else {
                            $post_errors = array_merge($post_errors, $posthandler->get_friendly_errors());
                        }
                    }
                }
                
                if(count($post_errors) != 0){
                    $errors .= inline_error($post_errors);
                } elseif(!empty($formcreator->settings['customsuccess'])) {
                    redirect($formcreator->settings['customsuccess'], $lang->fc_submitted, "", false);
                } elseif ($url) {
                    redirect($url, $lang->fc_submitted, "", false);
                } else {
                    redirect($mybb->settings['bburl'], $lang->fc_submitted, "", false);
                }

            }
        }

        if ($display && count($formcreator->fields) != 0) {
            $formtitle = $formcreator->name;

            $formcontent = $formcreator->build_form();
        } elseif (count($formcreator->fields) == 0) {
            $formtitle = $formcreator->name;

            $formcontent = '<tr><td class="trow1" colspan="2">'.$lang->fc_no_fields.'</td></tr>';
        }
        
        }else{
            add_breadcrumb($formcreator->name, "form.php?formid=" . $formcreator->formid);

            $formtitle = $lang->fc_limit_reached_title;
    
            $formcontent = '<tr><td class="trow1" colspan="2">'.$lang->fc_limit_reached.'</td></tr>';
        }
    } elseif ($formcreator->active == 0) {
        add_breadcrumb($formcreator->name, "form.php?formid=" . $formcreator->formid);

        $formtitle = $lang->fc_form_disabled_title;

        $formcontent = '<tr><td class="trow1" colspan="2">'.$lang->fc_form_disabled.'</td></tr>';
    } else {
        add_breadcrumb($formcreator->name, "form.php?formid=" . $formcreator->formid);

        $formtitle = $lang->fc_access_denied;

        $formcontent = '<tr><td class="trow1" colspan="2">'.$lang->fc_form_no_permissions.'</td></tr>';
    }
} else {
    add_breadcrumb($lang->formcreator, "form.php");

    $formtitle = $lang->formcreator;
    $formcontent = '<tr><td class="trow1" colspan="2">'.$lang->fc_no_form_found.'</td></tr>';
}

eval("\$form = \"" . $templates->get("formcreator_container") . "\";");

eval("\$html = \"" . $templates->get("formcreator") . "\";");

output_page($html);

?>