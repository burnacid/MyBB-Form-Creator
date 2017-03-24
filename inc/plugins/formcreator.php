<?php

if (!defined('IN_MYBB'))
    die('This file cannot be accessed directly.');

//HOOKS
if (defined('IN_ADMINCP')) {

} else {

}


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
    change_admin_permission('config', 'formcreator', 1);
}

function formcreator_deactivate()
{
    change_admin_permission('config', 'formcreator', -1);
}

function formcreator_install()
{
    global $db, $mybb;

    if (!$db->table_exists('fc_forms')) {
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

    if (!$db->table_exists('fc_fields')) {
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

    if ($mybb->request_method != 'post') {
        global $page;

        $page->output_confirm_action('index.php?module=config-plugins&action=deactivate&uninstall=1&plugin=formcreator',
            "Are you sure you want to uninstall Form Creator? This will delete all existing forms!", "Uninstall Form Creator");
    }

    // This is required so it updates the settings.php file as well and not only the database - they must be synchronized!
    rebuild_settings();

    // Drop tables if desired
    if (!isset($mybb->input['no'])) {
        $db->drop_table('fc_forms');
        $db->drop_table('fc_fields');
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

function get_usergroup($gid)
{
    global $db;

    $query = $db->simple_select("usergroups", "*", "gid = " . intval($gid));

    if ($db->num_rows($query) == 1) {
        return $db->fetch_array($query);
    } else {
        return false;
    }
}

?>