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
          `gid` text NOT NULL,
          `active` tinyint(1) NOT NULL,
          `pmusers` varchar(255) NOT NULL,
          `pmgroups` varchar(255) NOT NULL,
          `mail` text NOT NULL,
          PRIMARY KEY (`formid`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
    }

    if (!$db->table_exists('fc_fields')) {
        $db->write_query("CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "fc_fields` (
          `fieldid` int(11) NOT NULL AUTO_INCREMENT,
          `formid` int(11) NOT NULL DEFAULT '0',
          `name` varchar(255) NOT NULL DEFAULT '0',
          `description` varchar(2000) NOT NULL DEFAULT '0',
          `type` int(11) NOT NULL DEFAULT '0',
          `options` varchar(2000) NOT NULL DEFAULT '0',
          `default` varchar(2000) NOT NULL DEFAULT '0',
          `required` tinyint(1) NOT NULL DEFAULT '0',
          `regex` varchar(255) NOT NULL DEFAULT '0',
          `order` int(11) NOT NULL DEFAULT '0',
          `size` int(11) NOT NULL DEFAULT '0',
          `cols` int(11) NOT NULL DEFAULT '0',
          `rows` int(11) NOT NULL DEFAULT '0',
          `class` varchar(50) NOT NULL DEFAULT '0',
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

?>