<?php

$lang->load("formcreator");

$sub_tabs['formcreator_forms'] = array(
    'title' => 'View Forms',
    'link' => 'index.php?module=config-formcreator',
    'description' => 'View all forms created for this website');
$sub_tabs['formcreator_new'] = array(
    'title' => 'Create New Form',
    'link' => 'index.php?module=config-formcreator&amp;action=new',
    'description' => 'Create a new form for this website');

switch ($mybb->get_input('action')) {
    case '':
        $sub_tabs['formcreator_'] = array(
            'title' => '',
            'link' => 'index.php?module=config-formcreator&amp;action=',
            'description' => "");
        break;
}

if ($mybb->get_input('action') == '') {
    $page->add_breadcrumb_item($lang->trashbin, "");
    $page->output_header($lang->trashbin);
    $page->output_nav_tabs($sub_tabs, 'formcreator_');

    
} else {

    $page->add_breadcrumb_item("Form Creator", "");
    $page->output_header("Form Creator");
    $page->output_nav_tabs($sub_tabs, 'formcreator_forms');

    $table = new Table;
    $table->construct_header("Form name", array());
    $table->construct_header("Form Status", array());
    $table->construct_header("Form Link", array());
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
        "order_by" => "deletetime",
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