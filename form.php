<?php

define('THIS_SCRIPT', "form.php");
define('IN_MYBB', 1);
require "./global.php";
require_once "./inc/class_formcreator.php";

$formcreator = new formcreator();

if ($formcreator->get_form($mybb->input['formid']))
{
    add_breadcrumb($formcreator->name, "form.php?formid=" . $formcreator->formid);

    $formtitle = $formcreator->name;
    
    $formcontent = $formcreator->build_form();
    
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