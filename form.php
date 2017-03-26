<?php

define('THIS_SCRIPT', "form.php");
define('IN_MYBB', 1);
require "./global.php";
require_once "./inc/class_formcreator.php";

$formcreator = new formcreator();

if($formcreator->get_form($mybb->input['formid'])){

add_breadcrumb($formcreator->name, "form.php");


eval("\$html = \"" . $templates->get("formcreator") . "\";");

output_page($html);

}

?>