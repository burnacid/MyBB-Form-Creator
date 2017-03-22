<?php

$page->add_breadcrumb_item("Form Creator", "index.php?module=config-formcreator");

$sub_tabs['formcreator_forms'] = array(
    'title' => 'View Forms',
    'link' => 'index.php?module=config-formcreator',
    'description' => 'View all forms created for this website');
$sub_tabs['formcreator_add'] = array(
    'title' => 'Create New Form',
    'link' => 'index.php?module=config-formcreator&amp;action=add',
    'description' => 'Create a new form for this website');

switch ($mybb->get_input('action')) {
    case '':
        $sub_tabs['formcreator_'] = array(
            'title' => '',
            'link' => 'index.php?module=config-formcreator&amp;action=',
            'description' => "");
        break;
}

if ($mybb->get_input('action') == 'a') {
    $page->add_breadcrumb_item($lang->trashbin, "");
    $page->output_header($lang->trashbin);
    $page->output_nav_tabs($sub_tabs, 'formcreator_');


} elseif ($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit') {
    $page->add_breadcrumb_item("Add Form", "");
    $page->output_header("Add Form");
    $page->output_nav_tabs($sub_tabs, 'formcreator_add');

    if ($mybb->request_method == "post") {
        print_r($mybb->input);
    }

    $form = new Form("index.php?module=config-formcreator&amp;action=add", "post");
    $form_container = new FormContainer("Create a new Form");
    $form_container->output_row("Form Name <em>*</em>", "The title of the form", $form->generate_text_box('name', $mybb->input['name'], array('id' =>
            'name')), 'name');
    $form_container->output_row("Allowed Groups <em>*</em>", "Which groups are allowed to use this form", $form->generate_group_select("allowedgid[]", $mybb->
        input['allowedgid'], array("multiple" => true)));
    $form_container->output_row("Status <em>*</em>", "Is this form active yes or no?", $form->generate_yes_no_radio("active", $mybb->input['active']));
    $form_container->end();

    $form_container = new FormContainer("Process Options");
    $form_container->output_row("Send PM to user(s)",
        "Send a PM to the User IDs defined here. If you do not want to trigger a PM leave this empty. Multiple users comma seperated.", $form->
        generate_text_box("pmusers", $mybb->input['pmusers']));
    $form_container->output_row("Send PM to Groups", "Send a PM to the Users within the selected groups. If you do not want to trigger a group PM select nothing.", $form->generate_group_select("pmgroups[]", $mybb->
        input['pmgroups'], array("multiple" => true)));
        $form_container->output_row("Post within forum", "Create a Post within the selected forum", $form->generate_forum_select("fid",$mybb->input['fid'],array('main_option' => "- DISABLED -"),true));
    $form_container->output_row("Send Mail to","Send a mail to the following E-mail address(es). Leave empty if you don't like to send a email. One address per line.",$form->generate_text_area());

    $buttons[] = $form->generate_submit_button("Create Form");
    $form->output_submit_wrapper($buttons);
    $form->end();


} else {

    $page->output_header("Form Creator");
    $page->output_nav_tabs($sub_tabs, 'formcreator_forms');

    $table = new Table;
    $table->construct_header("Form name", array());
    $table->construct_header("Status", array());
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

            $table->construct_cell($form['name']);
            $table->construct_cell("");
            $table->construct_cell("");

            $popup = new PopupMenu("form_{$form['formid']}", $lang->options);
            //$popup->add_item("", "");

            $table->construct_cell($popup->fetch(), array('class' => 'align_center'));

            $table->construct_row();
        }
        $table->output("Forms");

        echo draw_admin_pagination($pagenr, 10, $total, "URL");
    }
}

$page->output_footer();

?>