<?php

if (!defined('IN_MYBB'))
    die('This file cannot be accessed directly.');

function formcreator_info()
{
    return array(
        'name' => 'Form Creator',
        'description' => '',
        'website' => 'https://mybb.com',
        'author' => 'S. Lenders',
        'authorsite' => 'http://lenders-it.nl',
        'version' => '0.1',
        'compatibility' => '18*',
        'codename' => 'formcreator');
}

function formcreator_activate()
{
    global $db;

    change_admin_permission('config', 'formcreator', 1);

    $templatearray = array('' => "<html>
<head>
    <title>{\$mybb->settings['bbname']}</title>
    {\$headerinclude}
</head>
<body>
{\$header}

{\$form}
{\$boardstats}

<br class=\"clear\" />
{\$footer}
</body>
</html>", 'container' => '<form action="" method="post" class="{$formclass}">

<table border="0" cellspacing="0" cellpadding="5" class="tborder">
<tbody><tr>
<td class="thead" colspan="2"><strong>{$formtitle}</strong></td>
</tr>
{$formcontent}
</tbody></table>
	
</form>', 'field' => '<tr>
	<td class="trow1">{$fieldname}{$fielddescription}</td>
	<td class="trow1">{$fieldoutput}</td>
</tr>', 'field_html' => '<tr>
	<td class="trow1" colspan="2">{$fieldoutput}</td>
</tr>', 'field_header' => '<tr>
	<td class="thead" colspan="2">{$fieldoutput}</td>
</tr>', 'field_submit' => '<tr>
	<td class="trow1" colspan="2">{$fieldoutput}</td>
</tr>', 'field_seperator' => '</tbody></table><br />
	<td class="thead" colspan="2">{$fieldoutput}</td>
</tr><table border="0" cellspacing="0" cellpadding="5" class="tborder">
<tbody>');

    $group = array('prefix' => $db->escape_string('formcreator'), 'title' => $db->escape_string('Form Creator'));

    // Update or create template group:
    $query = $db->simple_select('templategroups', 'prefix', "prefix='{$group['prefix']}'");

    if ($db->fetch_field($query, 'prefix'))
    {
        $db->update_query('templategroups', $group, "prefix='{$group['prefix']}'");
    }
    else
    {
        $db->insert_query('templategroups', $group);
    }

    // Query already existing templates.
    $query = $db->simple_select('templates', 'tid,title,template', "sid=-2 AND (title='{$group['prefix']}' OR title LIKE '{$group['prefix']}=_%' ESCAPE '=')");

    $templates = $duplicates = array();

    while ($row = $db->fetch_array($query))
    {
        $title = $row['title'];
        $row['tid'] = (int)$row['tid'];

        if (isset($templates[$title]))
        {
            // PluginLibrary had a bug that caused duplicated templates.
            $duplicates[] = $row['tid'];
            $templates[$title]['template'] = false; // force update later
        }
        else
        {
            $templates[$title] = $row;
        }
    }

    // Delete duplicated master templates, if they exist.
    if ($duplicates)
    {
        $db->delete_query('templates', 'tid IN (' . implode(",", $duplicates) . ')');
    }

    // Update or create templates.
    foreach ($templatearray as $name => $code)
    {
        if (strlen($name))
        {
            $name = "formcreator_{$name}";
        }
        else
        {
            $name = "formcreator";
        }

        $template = array(
            'title' => $db->escape_string($name),
            'template' => $db->escape_string($code),
            'version' => 1,
            'sid' => -2,
            'dateline' => TIME_NOW);

        // Update
        if (isset($templates[$name]))
        {
            if ($templates[$name]['template'] !== $code)
            {
                // Update version for custom templates if present
                $db->update_query('templates', array('version' => 0), "title='{$template['title']}'");

                // Update master template
                $db->update_query('templates', $template, "tid={$templates[$name]['tid']}");
            }
        }
        // Create
        else
        {
            $db->insert_query('templates', $template);
        }

        // Remove this template from the earlier queried list.
        unset($templates[$name]);
    }

    // Remove no longer used templates.
    foreach ($templates as $name => $row)
    {
        $db->delete_query('templates', "title='" . $db->escape_string($name) . "'");
    }

}

function formcreator_deactivate()
{
    change_admin_permission('config', 'formcreator', -1);
}

function formcreator_install()
{
    global $db, $mybb;

    if (!$db->table_exists('fc_forms'))
    {
        $db->write_query("CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "fc_forms` (
          `formid` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `allowedgid` text NOT NULL,
          `active` tinyint(1) NOT NULL,
          `pmusers` varchar(255) NOT NULL,
          `pmgroups` varchar(255) NOT NULL,
          `fid` int(11) NOT NULL,
          `mail` text NOT NULL,
          PRIMARY KEY (`formid`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
    }

    if (!$db->table_exists('fc_fields'))
    {
        $db->write_query("CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "fc_fields` (
          `fieldid` int(11) NOT NULL AUTO_INCREMENT,
          `formid` int(11) NOT NULL,
          `name` varchar(255) NOT NULL,
          `description` varchar(2000) DEFAULT NULL,
          `type` int(11) NOT NULL,
          `options` varchar(2000) DEFAULT NULL,
          `default` varchar(2000) DEFAULT NULL,
          `required` tinyint(1) DEFAULT NULL,
          `regex` varchar(255) DEFAULT NULL,
          `order` int(11) DEFAULT NULL,
          `size` int(11) DEFAULT NULL,
          `cols` int(11) DEFAULT NULL,
          `rows` int(11) DEFAULT NULL,
          `class` varchar(50) DEFAULT NULL,
          `html` text,
          PRIMARY KEY (`fieldid`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
    }
}

function formcreator_is_installed()
{
    global $db;

    // If the table exists then it means the plugin is installed because we only drop it on uninstallation
    return $db->table_exists('fc_fields') && $db->table_exists('fc_forms');
}


function formcreator_uninstall()
{
    global $db, $mybb;

    if ($mybb->request_method != 'post')
    {
        global $page;

        $page->output_confirm_action('index.php?module=config-plugins&action=deactivate&uninstall=1&plugin=formcreator',
            "Are you sure you want to uninstall Form Creator? This will delete all existing forms!", "Uninstall Form Creator");
    }

    // This is required so it updates the settings.php file as well and not only the database - they must be synchronized!
    rebuild_settings();

    // Drop tables if desired
    if (!isset($mybb->input['no']))
    {
        $db->drop_table('fc_forms');
        $db->drop_table('fc_fields');

        // Delete template groups.
        $db->delete_query('templategroups', "prefix='formcreator'");

        // Delete templates belonging to template groups.
        $db->delete_query('templates', "title='formcreator' OR title LIKE 'formcreator_%'");
    }
}

$plugins->add_hook('admin_config_menu', 'formcreator_admin_config_menu');
function formcreator_admin_config_menu(&$sub_menu)
{
    $sub_menu[] = array(
        'id' => 'formcreator',
        'title' => 'Form Creator',
        'link' => 'index.php?module=config-formcreator');
}

$plugins->add_hook('admin_config_permissions', 'formcreator_admin_config_permissions');
function formcreator_admin_config_permissions(&$admin_permissions)
{
    $admin_permissions['formcreator'] = "Form Creator: Can edit forms?";
}

$plugins->add_hook('admin_config_action_handler', 'formcreator_admin_config_action_handler');
function formcreator_admin_config_action_handler(&$actions)
{
    $actions['formcreator'] = array(
        'active' => 'formcreator',
        'file' => 'formcreator.php',
        );
}

$plugins->add_hook("build_friendly_wol_location_end", "formcreator_location_end");
function formcreator_location_end(&$plugin_array)
{
    require_once MYBB_ROOT . 'inc/class_formcreator.php';


    if (preg_match("/form\.php/", $plugin_array['user_activity']['location']))
    {
        $url = explode("?", $plugin_array['user_activity']['location']);
        $get_data = explode("&", $url[1]);
        $get_array = array();
        foreach ($get_data as $var)
        {
            $keyvalue = explode("=", $var);
            $get_array[$keyvalue[0]] = $keyvalue[1];
        }

        $formcreator = new formcreator();

        if ($formcreator->get_form($get_array['formid']))
        {
            $plugin_array['user_activity']['activity'] = "Form";
            $plugin_array['location_name'] = "Form: <a href='form.php?formid=" . $formcreator->formid . "'>" . $formcreator->name . "</a>";
        }
    }
}

function get_usergroup($gid)
{
    global $db;

    $query = $db->simple_select("usergroups", "*", "gid = " . intval($gid));

    if ($db->num_rows($query) == 1)
    {
        return $db->fetch_array($query);
    }
    else
    {
        return false;
    }
}

?>