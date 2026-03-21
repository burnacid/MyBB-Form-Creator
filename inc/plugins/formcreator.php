<?php

if (!defined('IN_MYBB'))
    die('This file cannot be accessed directly.');



function formcreator_info()
{
    $donate = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="3A2B883GGPH2U">
<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal � The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
';

    return array(
        'name' => 'Form Creator',
        'description' => 'Plugin for creating fillable forms to be send as PM or to be created as a new thread<p>Although this plugin is completly free donations are greatly appreciated to keep the plugin updated. ' .
            $donate . '</p>',
        'website' => 'https://community.mybb.com/mods.php?action=view&pid=975',
        'author' => 'S. Lenders (burnacid)',
        'authorsite' => 'http://lenders-it.nl',
        'version' => '3.0.0',
        'compatibility' => '18*',
        'codename' => 'formcreator');
}

function formcreator_get_templates(){
    $templatearray = array(
        '' => "<html>
<head>
    <title>{\$mybb->settings['bbname']}</title>
    {\$headerinclude}
    <script src=\"https://code.jquery.com/ui/1.12.1/jquery-ui.js\"></script>
</head>
<body>
{\$header}

{\$form}
{\$boardstats}

<br class=\"clear\" />
{\$footer}
</body>
</html>",
        'container' => '<form action="" method="post" class="{$formclass}" enctype="multipart/form-data">
{$errors}
<table border="0" cellspacing="0" cellpadding="5" class="tborder {$styleclass}" style="{$stylewidth}">
<tbody><tr>
<td class="thead" colspan="2"><strong>{$formtitle}</strong></td>
</tr>
{$formcontent}
</tbody></table>
	
</form>',
        'field' => '<tr>
	<td class="trow1" style="{$stylelabelwidth}">{$fieldname}{$fielddescription}</td>
	<td class="trow1">{$fieldoutput}</td>
</tr>',
        'field_html' => '<tr>
	<td class="trow1" colspan="2">{$fieldoutput}</td>
</tr>',
        'field_header' => '<tr>
	<td class="thead" colspan="2">{$fieldoutput}</td>
</tr>',
        'field_submit' => '<tr>
	<td class="trow1" colspan="2" style="text-align:center;">{$fieldoutput}</td>
</tr>',
        'field_captcha' => '<tr>
	<td class="trow1" colspan="2" style="text-align:center;">{$fieldoutput}</td>
</tr>',
        'field_seperator' => '</tbody></table><br />
	<td class="thead" colspan="2">{$fieldoutput}</td>
</tr><table border="0" cellspacing="0" cellpadding="5" class="tborder {$styleclass}" style="{$stylewidth}">
<tbody>',
        'thread_button' => '<a href="form.php?formid={$formid}" class="button new_thread_button"><span>{$lang->post_thread}</span></a>',
        'thread_newreply' => '<a href="form.php?formid={$formid}" class="button new_reply_button"><span>{$lang->new_reply}</span></a>&nbsp;',
        'thread_newreply_closed' => '<a href="form.php?formid={$formid}" class="button closed_button"><span>{$lang->thread_closed}</span></a>&nbsp;',
        'thread_newthread' => '<a href="form.php?formid={$formid}" class="button new_thread_button"><span>{$lang->post_thread}</span></a>&nbsp;',
        'captcha' => '<fieldset class="trow2">
<script type="text/javascript">
<!--
	lang.captcha_fetch_failure = "{$lang->captcha_fetch_failure}";
// -->
</script>
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/captcha.js?ver=1808"></script>
<legend><strong>{$lang->image_verification}</strong></legend>
<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}">
<tr>
<td><span class="smalltext">{$lang->verification_note}</span></td>
<td rowspan="2" align="center"><img src="captcha.php?action=regimage&amp;imagehash={$imagehash}" alt="{$lang->image_verification}" title="{$lang->image_verification}" id="captcha_img" /><br /><span style="color: red;" class="smalltext">{$lang->verification_subnote}</span>
<script type="text/javascript">
<!--
	if(use_xmlhttprequest == "1")
	{
		document.write(\'<br \/><br \/><input type="button" class="button" tabindex="10000" name="refresh" value="{$lang->refresh}" onclick="return captcha.refresh();" \/>\');
	}
// -->
</script>
</td>
</tr>
<tr>
<td><input type="text" class="textbox" name="imagestring" value="" id="imagestring" style="width: 100%;" /><input type="hidden" name="imagehash" value="{$imagehash}" id="imagehash" /></td>
</tr>
<tr>
	<td id="imagestring_status"  style="display: none;" colspan="2">&nbsp;</td>
</tr>
</table>
</fieldset>',
        'nocaptcha' => '<fieldset class="trow2">
	<legend><strong>{$lang->human_verification}</strong></legend>
	<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}">
	<tr>
		<td><span class="smalltext">{$lang->verification_note_nocaptcha}</span></td>
	</tr>
	<tr>
		<td><script type="text/javascript" src="{$server}"></script><div class="g-recaptcha" data-sitekey="{$public_key}"></div></td>
	</tr>
</table>
</fieldset>',
        'recaptcha' => '<script type="text/javascript">
<!--
	var RecaptchaOptions = {
		theme: \'clean\'
	};
// -->
</script>
<fieldset class="trow2">
	<legend><strong>{$lang->image_verification}</strong></legend>
	<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}">
	<tr>
		<td><span class="smalltext">{$lang->verification_note}</span></td>
	</tr>
	<tr>
		<td><script type="text/javascript" src="{$server}/challenge?k={$public_key}"></script></td>
	</tr>
</table>
</fieldset>',
"summary_buttons"=>'<tr>
	<td class="trow1" colspan="2" style="text-align:center;">
		<form method="post" action="">
			<input value="{$checksum}" name="formdata[checksum]" type="hidden" /><input value=\'{$json_data}\' name="formdata[data]" type="hidden" />
			<input type="button" value="{$lang->fc_back}" onclick="window.history.back()" />
			<input type="submit" value="{$lang->fc_confirm}" />
		</form>
	</td>
</tr>');

    return $templatearray;
}

function formcreator_activate()
{
    global $db, $mybb;
    
    $templatearray = formcreator_get_templates();

    change_admin_permission('config', 'formcreator', 1);

    $group = array('prefix' => $db->escape_string('formcreator'), 'title' => $db->escape_string('Form Creator'));

    // Update or create template group:
    $query = $db->simple_select('templategroups', 'prefix', "prefix='{$group['prefix']}'");

    if ($db->fetch_field($query, 'prefix')) {
        $db->update_query('templategroups', $group, "prefix='{$group['prefix']}'");
    } else {
        $db->insert_query('templategroups', $group);
    }

    // Query already existing templates.
    $query = $db->simple_select('templates', 'tid,title,template', "sid=-2 AND (title='{$group['prefix']}' OR title LIKE '{$group['prefix']}=_%' ESCAPE '=')");

    $templates = $duplicates = array();

    while ($row = $db->fetch_array($query)) {
        $title = $row['title'];
        $row['tid'] = (int)$row['tid'];

        if (isset($templates[$title])) {
            // PluginLibrary had a bug that caused duplicated templates.
            $duplicates[] = $row['tid'];
            $templates[$title]['template'] = false; // force update later
        } else {
            $templates[$title] = $row;
        }
    }

    // Delete duplicated master templates, if they exist.
    if ($duplicates) {
        $db->delete_query('templates', 'tid IN (' . implode(",", $duplicates) . ')');
    }

    // Update or create templates.
    foreach ($templatearray as $name => $code) {
        if (strlen($name)) {
            $name = "formcreator_{$name}";
        } else {
            $name = "formcreator";
        }

        $template = array(
            'title' => $db->escape_string($name),
            'template' => $db->escape_string($code),
            'version' => 1,
            'sid' => -2,
            'dateline' => TIME_NOW);

        // Update
        if (isset($templates[$name])) {
            if ($templates[$name]['template'] !== $code) {
                // Update version for custom templates if present
                $db->update_query('templates', array('version' => 0), "title='{$template['title']}'");

                // Update master template
                $db->update_query('templates', $template, "tid={$templates[$name]['tid']}");
            }
        }
        // Create
        else {
            $db->insert_query('templates', $template);
        }

        // Remove this template from the earlier queried list.
        unset($templates[$name]);
    }

    // Remove no longer used templates.
    foreach ($templates as $name => $row) {
        $db->delete_query('templates', "title='" . $db->escape_string($name) . "'");
    }

    // Add stylesheet
    $tid = 1; // MyBB Master Style
    $name = "formcreator.datepicker.css";
    $styles = file_get_contents(MYBB_ROOT . 'inc/plugins/formcreator/jquery-ui.css');
    $attachedto = "form.php";

    $stylesheet = array(
        'name' => $name,
        'tid' => $tid,
        'attachedto' => $attachedto,
        'stylesheet' => $styles,
        'cachefile' => $name,
        'lastmodified' => TIME_NOW,
        );

    $dbstylesheet = array_map(array($db, 'escape_string'), $stylesheet);

    // Activate children, if present.
    $db->update_query('themestylesheets', array('attachedto' => $dbstylesheet['attachedto']), "name='{$dbstylesheet['name']}'");

    // Update or insert parent stylesheet.
    $query = $db->simple_select('themestylesheets', 'sid', "tid='{$tid}' AND cachefile='{$name}'");
    $sid = intval($db->fetch_field($query, 'sid'));

    if ($sid) {
        $db->update_query('themestylesheets', $dbstylesheet, "sid='$sid'");
    } else {
        $sid = $db->insert_query('themestylesheets', $dbstylesheet);
        $stylesheet['sid'] = intval($sid);
    }

    require_once MYBB_ROOT . $mybb->config['admin_dir'] . '/inc/functions_themes.php';

    if ($stylesheet) {
        cache_stylesheet($stylesheet['tid'], $stylesheet['cachefile'], $stylesheet['stylesheet']);
    }

    update_theme_stylesheet_list($tid, false, true); // includes all children

}

function formcreator_deactivate()
{
    change_admin_permission('config', 'formcreator', -1);
}

function formcreator_get_db_type()
{
    global $db;

    return strtolower((string)$db->type);
}

function formcreator_quote_identifier($identifier, $db_type = '')
{
    if (empty($db_type)) {
        $db_type = formcreator_get_db_type();
    }

    if ($db_type == 'pgsql' || $db_type == 'sqlite') {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    return '`' . str_replace('`', '``', $identifier) . '`';
}

function formcreator_implode_identifiers($identifiers, $db_type = '')
{
    $quoted = array();
    foreach ($identifiers as $identifier) {
        $quoted[] = formcreator_quote_identifier($identifier, $db_type);
    }

    return implode(',', $quoted);
}

function formcreator_map_sql_type($type, $db_type, $is_auto_increment = false)
{
    $type = strtolower(trim($type));

    if ($db_type == 'pgsql') {
        if ($is_auto_increment) {
            return 'SERIAL';
        }

        if (preg_match('/^int\([0-9]+\)$/', $type)) {
            return 'INTEGER';
        }

        if (preg_match('/^tinyint\([0-9]+\)$/', $type)) {
            return 'SMALLINT';
        }

        return strtoupper($type);
    }

    if ($db_type == 'sqlite') {
        if ($is_auto_increment) {
            return 'INTEGER';
        }

        if (preg_match('/^(int|tinyint)\([0-9]+\)$/', $type)) {
            return 'INTEGER';
        }

        if ($type == 'timestamp') {
            return 'TEXT';
        }

        return strtoupper($type);
    }

    return strtoupper($type);
}

function formcreator_get_table_options($db_type = '')
{
    if (empty($db_type)) {
        $db_type = formcreator_get_db_type();
    }

    if ($db_type == 'mysql' || $db_type == 'mysqli') {
        return ' ENGINE=MyISAM DEFAULT CHARSET=utf8';
    }

    return '';
}

function formcreator_install()
{
    global $db;

    $db_type = formcreator_get_db_type();

    if (!$db->table_exists('fc_forms')) {
        $primary_key = array('formid');
        $inline_pk = ($db_type == 'sqlite') ? 'formid' : '';
        $primary_sql = '';

        if (empty($inline_pk)) {
            $primary_sql = ",\n          PRIMARY KEY (" . formcreator_implode_identifiers($primary_key, $db_type) . ")";
        }

        $db->write_query("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "fc_forms (\n"
            . "          " . formcreator_generate_table_fields('fc_forms', $db_type, $inline_pk)
            . $primary_sql
            . "\n        )"
            . formcreator_get_table_options($db_type)
            . ";");
    }

    if (!$db->table_exists('fc_fields')) {
        $primary_key = array('fieldid');
        $inline_pk = ($db_type == 'sqlite') ? 'fieldid' : '';
        $primary_sql = '';

        if (empty($inline_pk)) {
            $primary_sql = ",\n          PRIMARY KEY (" . formcreator_implode_identifiers($primary_key, $db_type) . ")";
        }

        $db->write_query("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "fc_fields (\n"
            . "          " . formcreator_generate_table_fields('fc_fields', $db_type, $inline_pk)
            . $primary_sql
            . "\n        )"
            . formcreator_get_table_options($db_type)
            . ";");
    }

    if (!$db->table_exists('fc_formusage')) {
        $primary_key = array('formid', 'uid', 'ref');

        $db->write_query("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "fc_formusage (\n"
            . "          " . formcreator_generate_table_fields('fc_formusage', $db_type)
            . ",\n          PRIMARY KEY (" . formcreator_implode_identifiers($primary_key, $db_type) . ")"
            . "\n        )"
            . formcreator_get_table_options($db_type)
            . ";");
    }
}

function formcreator_generate_table_fields($table, $db_type = '', $sqlite_inline_primary_key = '')
{
    require_once MYBB_ROOT . 'inc/class_formcreator.php';

    $formcreator = new formcreator();

    if (empty($db_type)) {
        $db_type = formcreator_get_db_type();
    }

    $output = array();

    foreach ($formcreator->formcreator_fields[$table] as $field) {
        $field_name = $field['Field'];
        $is_auto_increment = (!empty($field['AI']) && intval($field['AI']) === 1);
        $quoted_name = formcreator_quote_identifier($field_name, $db_type);

        if ($db_type == 'sqlite' && $is_auto_increment && $sqlite_inline_primary_key === $field_name) {
            $output[] = $quoted_name . ' INTEGER PRIMARY KEY AUTOINCREMENT';
            continue;
        }

        $field_sql = $quoted_name . ' ' . formcreator_map_sql_type($field['Type'], $db_type, $is_auto_increment);

        if ($field['NULL'] == 1 && !$is_auto_increment) {
            $field_sql .= ' DEFAULT NULL';
        } elseif (!$is_auto_increment || $db_type == 'mysql' || $db_type == 'mysqli') {
            $field_sql .= ' NOT NULL';
        }

        if ($is_auto_increment && ($db_type == 'mysql' || $db_type == 'mysqli')) {
            $field_sql .= ' AUTO_INCREMENT';
        }

        $output[] = $field_sql;
    }

    return implode(",\n          ", $output);
}

function formcreator_check_database()
{
    require_once MYBB_ROOT . 'inc/class_formcreator.php';
    
    global $db;
    
    $formcreator = new formcreator();
    $fields = $formcreator->formcreator_fields;

    $required_tables = array('fc_forms', 'fc_fields', 'fc_formusage');

    foreach ($required_tables as $table) {
        if (!$db->table_exists($table)) {
            return array(false, "The database structure doesn't contain the needed tables. Please repair the plugin by reinstalling");
        }

        foreach ($fields[$table] as $field) {
            if (!$db->field_exists($field['Field'], $table)) {
                return array(false, "The table structures have changed. It is adviced to create an export of your forms and reinstall the plugin!");
            }
        }
    }

    return array(true);
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
        $db->drop_table('fc_formusage');

        // Delete template groups.
        $db->delete_query('templategroups', "prefix='formcreator'");

        // Delete templates belonging to template groups.
        $db->delete_query('templates', "title='formcreator' OR title LIKE 'formcreator_%'");
    }
}

$plugins->add_hook('admin_load', 'formcreator_admin_load');
function formcreator_admin_load()
{
    global $page,$lang,$templatearray;
    
    $lang->load('config_formcreator');
    
    require_once MYBB_ROOT . 'inc/class_formcreator.php';
    
    $formcreator = new formcreator();

    $error = formcreator_check_database($formcreator);
        
    if($error[0] == false){
        $page->extra_messages[] = array("type" => "error", "message" => "Form Creator: ". $error[1]);
    }
    
    $missing = check_missing_templates();
    if($missing != 0){
        $page->extra_messages[] = array("type" => "error", "message" => "Form Creator: ". $lang->fc_error_missing_template);
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

$plugins->add_hook('admin_tools_get_admin_log_action', 'formcreator_admin_tools_get_admin_log_action');
function formcreator_admin_tools_get_admin_log_action()
{
    global $lang;
    
    $lang->load('config_formcreator');
}

$plugins->add_hook("build_friendly_wol_location_end", "formcreator_location_end");
function formcreator_location_end(&$plugin_array)
{
    require_once MYBB_ROOT . 'inc/class_formcreator.php';


    if (preg_match("/form\.php/", $plugin_array['user_activity']['location'])) {
        $url = explode("?", $plugin_array['user_activity']['location']);
        $get_data = explode("&", $url[1]);
        $get_array = array();
        foreach ($get_data as $var) {
            $keyvalue = explode("=", $var);
            $get_array[$keyvalue[0]] = $keyvalue[1];
        }

        $formcreator = new formcreator();

        if ($formcreator->get_form($get_array['formid'])) {
            $plugin_array['user_activity']['activity'] = "Form";
            $plugin_array['location_name'] = "Form: <a href='form.php?formid=" . $formcreator->formid . "'>" . $formcreator->name . "</a>";
        }
    }
}

$plugins->add_hook("forumdisplay_get_threads", "formcreator_forumdisplay_get_threads");
function formcreator_forumdisplay_get_threads()
{
    global $foruminfo, $fpermissions, $mybb, $db, $newthread, $lang, $templates;

    $query = $db->simple_select("fc_forms", "*", "fid=" . $foruminfo['fid'] . " AND overridebutton=1");
    if ($db->num_rows($query) == 1) {

        $form = $db->fetch_array($query);
        $formid = $form['formid'];

        if ($foruminfo['type'] == "f" && $foruminfo['open'] != 0 && $fpermissions['canpostthreads'] != 0 && $mybb->user['suspendposting'] == 0) {
            eval("\$newthread = \"" . $templates->get("formcreator_thread_button") . "\";");
        }
    }
}

$plugins->add_hook("showthread_threaded", "formcreator_showthread_buttons");
$plugins->add_hook("showthread_linear", "formcreator_showthread_buttons");
function formcreator_showthread_buttons()
{
    global $forum, $thread, $forumpermissions, $mybb, $db, $newthread, $newreply, $lang, $templates;

    $query = $db->simple_select("fc_forms", "*", "tid=" . $thread['tid'] . " AND overridebutton=1");
    if ($db->num_rows($query) == 1) {

        $form = $db->fetch_array($query);
        $formid = $form['formid'];

        if ($forum['open'] != 0 && $forum['type'] == "f") {
            if ($forumpermissions['canpostthreads'] != 0 && $mybb->user['suspendposting'] != 1) {
                eval("\$newthread = \"" . $templates->get("formcreator_thread_newthread") . "\";");
            }

            // Show the appropriate reply button if this thread is open or closed
            if ($forumpermissions['canpostreplys'] != 0 && $mybb->user['suspendposting'] != 1 && ($thread['closed'] != 1 || is_moderator($forum['fid'],
                "canpostclosedthreads")) && ($thread['uid'] == $mybb->user['uid'] || $forumpermissions['canonlyreplyownthreads'] != 1)) {
                eval("\$newreply = \"" . $templates->get("formcreator_thread_newreply") . "\";");
            } elseif ($thread['closed'] == 1) {
                eval("\$newreply = \"" . $templates->get("formcreator_thread_newreply_closed") . "\";");
            }
        }
    }
}

$plugins->add_hook("showthread_end", "formcreator_showthread_end");
function formcreator_showthread_end()
{
    global $thread, $forumpermissions, $db, $quickreply;

    $query = $db->simple_select("fc_forms", "*", "tid=" . $thread['tid'] . " AND overridebutton=1");
    if ($db->num_rows($query) == 1) {

        $form = $db->fetch_array($query);
        $formid = $form['formid'];

        //Remove quick reply if override is enabled
        $quickreply = "";
    }
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

/*
 * @param $gid int|array
 * @return array|bool
 * @description Returns an array of uid's that are in the usergroup(s) specified by $gid
 * Called by form.php when sending PM's to a group
 */
function get_usergroup_users_uid($gid)
{
    global $db;

    $userarray = array();

    $build_additional_group_condition = function($groupid) use ($db) {
        $groupid = intval($groupid);
        $db_type = formcreator_get_db_type();

        if ($db_type == 'pgsql') {
            return "(',' || COALESCE(additionalgroups, '') || ',') LIKE '%," . $groupid . ",%'";
        }

        if ($db_type == 'sqlite') {
            return "(',' || IFNULL(additionalgroups, '') || ',') LIKE '%," . $groupid . ",%'";
        }

        return "CONCAT(',',additionalgroups,',') LIKE '%," . $groupid . ",%'";
    };

    if (is_array($gid)) {
        $gid = array_values(array_filter(array_map('intval', $gid)));

        if (empty($gid)) {
            return false;
        }

        $additionalwhere = array();
        foreach ($gid as $groupid) {
            $additionalwhere[] = $build_additional_group_condition($groupid);
        }

        $where = "usergroup IN (" . implode(",", $gid) . ")";
        if (!empty($additionalwhere)) {
            $where .= " OR " . implode(" OR ", $additionalwhere);
        }

        $query = $db->simple_select("users", "uid", $where);
    } else {
        $gid = intval($gid);
        $query = $db->simple_select("users", "uid", "usergroup IN (" . $gid . ") OR " . $build_additional_group_condition($gid));
    }

    if ($db->num_rows($query)) {
        $rownum = 0;
        while ($user = $db->fetch_array($query)) {
            $userarray[$rownum] = $user['uid'];
            $rownum++;
        }
        return $userarray;
    } else {
        return false;
    }
}

function reArrayFiles(&$file_post, $current = 0) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i + $current][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}

function check_missing_templates(){
    
    global $db;
    
    $templatearray = formcreator_get_templates();
    
    $group = array('prefix' => $db->escape_string('formcreator'), 'title' => $db->escape_string('Form Creator'));
    
    // Query already existing templates.
    $query = $db->simple_select('templates', 'tid,title,template', "sid=-2 AND (title='{$group['prefix']}' OR title LIKE '{$group['prefix']}=_%' ESCAPE '=')");

    $templates = $duplicates = array();
    $existingtemplates = array();

    while ($row = $db->fetch_array($query)) {
        $existingtemplates[] = $row['title'];
    }
    
    $missing = 0;
    $templatetitles = array_keys($templatearray);
    foreach($templatetitles as $key){
        if(!(in_array($group['prefix']."_".$key,$existingtemplates) || (empty($key) && in_array($group['prefix'],$existingtemplates)))){
            $missing++;
        }
    }
    
    return $missing;
}

?>