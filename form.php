<?php

define('THIS_SCRIPT', "form.php");
define('IN_MYBB', 1);
require "./global.php";
require_once "./inc/class_formcreator.php";

$formcreator = new formcreator();

if ($formcreator->get_form($mybb->input['formid']))
{
    if($formcreator->check_allowed() && $forumcreator->active == 1){
        
        if($mybb->request_method == "post"){
            
        }
        
        add_breadcrumb($formcreator->name, "form.php?formid=" . $formcreator->formid);

        $formtitle = $formcreator->name;
        
        $formcontent = $formcreator->build_form();
    }elseif($forumcreator->active == 0){
        add_breadcrumb($formcreator->name, "form.php?formid=" . $formcreator->formid);
        
        $formtitle = "Form disabled";
        
        $formcontent = '<tr><td class="trow1" colspan="2">This form has been disabled for use!</td></tr>';
    }else{
        add_breadcrumb($formcreator->name, "form.php?formid=" . $formcreator->formid);
        
        $formtitle = "Access Denied";
        
        $formcontent = '<tr><td class="trow1" colspan="2">You are not allowed to use this form!</td></tr>';
    }
}
else
{
    add_breadcrumb("Form Creator", "form.php");
    
    $formtitle = "Form Creator";
    $formcontent = '<tr><td class="trow1" colspan="2">The form you are looking for doesn\'t exist!</td></tr>';
}

eval("\$form = \"" . $templates->get("formcreator_container") . "\";");

eval("\$html = \"" . $templates->get("formcreator") . "\";");

output_page($html);

?>