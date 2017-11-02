<?php

class formcreator
{
    public $formid;
    public $name;
    public $allowedgidtype;
    public $allowedgid;
    public $allgroups;
    public $limitusage;
    public $active;
    public $pmusers;
    public $pmgroups;
    public $mail;
    public $fid;
    public $tid;
    public $uid;
    public $prefix;
    public $overridebutton;
    public $class;
    public $width;
    public $labelwidth;
    public $subjecttemplate;
    public $messagetemplate;
    public $customsuccess;
    public $settings;

    public $fields;

    private $output;

    private $error;

    // Table structures from Array
    public $formcreator_fields = array(
        "fc_forms" => array(
            array(
                "Field" => "formid",
                "Type" => "int(11)",
                "NULL" => 0,
                "AI" => 1),
            array(
                "Field" => "name",
                "Type" => "varchar(255)",
                "NULL" => 0),
            array(
                "Field" => "active",
                "Type" => "tinyint(1)",
                "NULL" => 0),
            array(
                "Field" => "fid",
                "Type" => "int(11)",
                "NULL" => 0),
            array(
                "Field" => "tid",
                "Type" => "int(11)",
                "NULL" => 0),
            array(
                "Field" => "overridebutton",
                "Type" => "tinyint(1)",
                "NULL" => 0),
            array(
                "Field" => "class",
                "Type" => "varchar(255)",
                "NULL" => 1),
            array(
                "Field" => "subjecttemplate",
                "Type" => "varchar(255)",
                "NULL" => 1),
            array(
                "Field" => "messagetemplate",
                "Type" => "text",
                "NULL" => 1),
            array("Field" => "settings", "Type" => "text")),
        "fc_fields" => array(
            array(
                "Field" => "fieldid",
                "Type" => "int(11)",
                "NULL" => 0,
                "AI" => 1),
            array(
                "Field" => "formid",
                "Type" => "int(11)",
                "NULL" => 0),
            array(
                "Field" => "name",
                "Type" => "varchar(255)",
                "NULL" => 0),
            array(
                "Field" => "description",
                "Type" => "varchar(2000)",
                "NULL" => 1),
            array(
                "Field" => "type",
                "Type" => "int(11)",
                "NULL" => 0),
            array(
                "Field" => "default",
                "Type" => "varchar(2000)",
                "NULL" => 1),
            array(
                "Field" => "required",
                "Type" => "tinyint(1)",
                "NULL" => 1),
            array(
                "Field" => "order",
                "Type" => "int(11)",
                "NULL" => 1),
            array(
                "Field" => "class",
                "Type" => "varchar(50)",
                "NULL" => 1),
            array("Field" => "settings", "Type" => "text")),

        "fc_formusage" => array(
            array(
                "Field" => "formid",
                "Type" => "int(11)",
                "NULL" => 0),
            array(
                "Field" => "uid",
                "Type" => "int(11)",
                "NULL" => 0),
            array(
                "Field" => "ref",
                "Type" => "int(11)",
                "NULL" => 0),
            array(
                "Field" => "datetime",
                "Type" => "timestamp",
                "NULL" => 0)));

    public $types = array(
        1 => "Textbox (single line)",
        2 => "Textarea (multi line)",
        3 => "Select (single)",
        4 => "Select (multiple)",
        5 => "Radio Buttons",
        6 => "Checkboxes",
        7 => "Date",

        13 => "Attachment",
        14 => "Multiple Attachments",
        15 => "MyBB Editor",

        8 => "Seperator",
        9 => "Header",
        10 => "HTML block",

        12 => "Captcha",

        11 => "Submit button");
    public function get_form($formid)
    {
        global $db;
        $query = $db->simple_select("fc_forms", "*", "formid = " . intval($formid));
        if ($db->num_rows($query) == 1)
        {
            $formdata = $db->fetch_array($query);
            $formdata['allowedgid'] = explode(",", $formdata['allowedgid']);
            $formdata['pmgroups'] = explode(",", $formdata['pmgroups']);
            $this->load_data($formdata);
            return $formdata;
        }
        else
        {
            return false;
        }
    }

    public function field_in_table($table, $fieldname)
    {
        $tabledata = $this->formcreator_fields[$table];

        foreach ($tabledata as $field)
        {
            if ($field['Field'] == $fieldname)
            {
                return true;
            }
        }

        return false;
    }

    public function build_form()
    {
        global $templates, $stylelabelwidth;
        $output = "";
        foreach ($this->fields as $field)
        {
            $fieldname = $field->name;
            $fieldoutput = "";
            if ($field->required)
            {
                $fieldname .= "<em>*</em>";
            }

            if ($field->description)
            {
                $fielddescription = "<br /><small>" . $field->description . "</small>";
            }
            else
            {
                $fielddescription = "";
            }

            switch ($field->type)
            {
                case 1:
                    $fieldoutput = $field->output_textbox();
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                    break;
                case 2:
                    $fieldoutput = $field->output_textarea();
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                    break;
                case 3:
                    $fieldoutput = $field->output_select();
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                    break;
                case 4:
                    $fieldoutput = $field->output_select(true);
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                    break;
                case 5:
                    $fieldoutput = $field->output_radio();
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                    break;
                case 6:
                    $fieldoutput = $field->output_checkbox();
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                    break;
                case 7:
                    $fieldoutput = $field->output_dateselect();
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                    break;
                case 8:
                    if ($this->settings['width'])
                    {
                        $stylewidth = "width:" . $this->settings['width'] . ";";
                    }

                    if ($this->class)
                    {
                        $styleclass = $this->class;
                    }

                    eval('$output .= "' . $templates->get("formcreator_field_seperator") . '";');
                    break;
                case 9:
                    $fieldoutput = $field->output_header();
                    eval('$output .= "' . $templates->get("formcreator_field_header") . '";');
                    break;
                case 10:
                    $fieldoutput = $field->settings['html'];
                    eval('$output .= "' . $templates->get("formcreator_field_html") . '";');
                    break;
                case 11:
                    $fieldoutput = $field->output_submit();
                    eval('$output .= "' . $templates->get("formcreator_field_submit") . '";');
                    break;
                case 12:
                    $fieldoutput = $field->output_captcha();
                    eval('$output .= "' . $templates->get("formcreator_field_captcha") . '";');
                    break;
                case 13:
                    $fieldoutput = $field->output_attachment();
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                    break;
                case 14:
                    $fieldoutput = $field->output_attachments();
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                    break;
                case 15:
                    $fieldoutput = $field->output_editor();
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                    break;
            }
        }

        return $output;
    }

    public function check_allowed()
    {
        global $mybb;
        if ($this->settings['allowedgidtype'] == -1)
        {
            return true;
        }

        if (!empty($mybb->user['additionalgroups']))
        {
            $current_groups = $mybb->user['additionalgroups'] . "," . $mybb->user['usergroup'];
        }
        else
        {
            $current_groups = $mybb->user['usergroup'];
        }

        $current_groups = explode(",", $current_groups);
        if (array_intersect($this->settings['allowedgid'], $current_groups))
        {
            if ($this->settings['allowedgidtype'] == 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            if ($this->settings['allowedgidtype'] == 0)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }

    public function get_type_name($type)
    {
        if (key_exists(intval($type), $this->types))
        {
            return $this->types[intval($type)];
        }
        else
        {
            return false;
        }
    }

    public function insert_form()
    {
        global $db;
        if ($this->settings['allowedgid'] && is_array($this->settings['allowedgid']))
        {
            $this->settings['allowedgid'] = implode(",", $this->settings['allowedgid']);
        }

        if ($this->settings['pmgroups'] && is_array($this->settings['pmgroups']))
        {
            $this->settings['pmgroups'] = implode(",", $this->settings['pmgroups']);
        }

        $this->escape_data();
        $result = $db->insert_query("fc_forms", $this->get_data());
        if ($result)
        {
            $this->formid = $result;
            return $result;
        }
        else
        {
            return false;
        }
    }

    public function update_template()
    {
        global $db;
        
        $this->settings = json_decode($this->settings);
        
        if ($this->settings['allowedgid'] && is_array($this->settings['allowedgid']))
        {
            $this->settings['allowedgid'] = implode(",", $this->settings['allowedgid']);
        }

        if ($this->settings['pmgroups'] && is_array($this->settings['pmgroups']))
        {
            $this->settings['pmgroups'] = implode(",", $this->settings['pmgroups']);
        }

        $this->escape_data();
        if (empty($this->subjecttemplate))
        {
            $this->subjecttemplate = "";
        }

        if (empty($this->messagetemplate))
        {
            $this->messagetemplate = "";
        }

        $template_data = array("subjecttemplate" => $this->subjecttemplate, "messagetemplate" => $this->messagetemplate);
        $result = $db->update_query("fc_forms", $template_data, "formid = " . $this->formid);
        return $result;
    }

    public function update_form()
    {
        global $db;
        if ($this->settings['allowedgid'])
        {
            $this->settings['allowedgid'] = implode(",", $this->settings['allowedgid']);
        }

        if ($this->settings['pmgroups'])
        {
            $this->settings['pmgroups'] = implode(",", $this->settings['pmgroups']);
        }

        $this->escape_data();
        $result = $db->update_query("fc_forms", $this->get_data(), "formid = " . $this->formid);
        return $result;
    }

    public function delete_form()
    {
        global $db;
        if ($db->delete_query("fc_fields", "formid = " . $this->formid))
        {
            if ($db->delete_query("fc_forms", "formid = " . $this->formid))
            {
                $db->delete_query("fc_formusage", "formid = " . $this->formid);
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }

    }

    public function escape_data()
    {
        global $db;
        $this->formid = intval($this->formid);
        $this->name = $db->escape_string($this->name);
        $this->active = intval($this->active);
        $this->fid = intval($this->fid);
        $this->tid = intval($this->tid);
        $this->overridebutton = intval($this->overridebutton);
        $this->class = $db->escape_string($this->class);
        $this->subjecttemplate = $db->escape_string($this->subjecttemplate);
        $this->messagetemplate = $db->escape_string($this->messagetemplate);
        $this->settings = $db->escape_string(json_encode($this->settings));
    }

    public function load_data($data)
    {
        $this->formid = $data['formid'];
        $this->name = $data['name'];
        $this->active = $data['active'];
        $this->tid = $data['tid'];
        $this->fid = $data['fid'];
        $this->overridebutton = $data['overridebutton'];
        $this->class = $data['class'];
        $this->subjecttemplate = $data['subjecttemplate'];
        $this->messagetemplate = $data['messagetemplate'];
        
        if (!is_array($data['settings']))
        {
            $this->settings = json_decode($data['settings'], true);
        }
        else
        {
            $this->settings = $data['settings'];
        }
        
        if(!is_array($this->settings['allowedgid'])){
            $this->settings['allowedgid'] = explode(',',$this->settings['allowedgid']);
        }
        
        if(!is_array($this->settings['pmgroups'])){
            $this->settings['pmgroups'] = explode(',',$this->settings['pmgroups']);
        }
    }

    public function get_data()
    {
        if ($this->formid)
        {
            $data['formid'] = $this->formid;
        }

        $data['name'] = $this->name;
        $data['active'] = $this->active;
        $data['fid'] = $this->fid;
        $data['tid'] = $this->tid;
        $data['overridebutton'] = $this->overridebutton;
        $data['class'] = $this->class;
        $data['settings'] = $this->settings;
        return $data;
    }

    public function get_fields()
    {
        global $db;
        $query = $db->simple_select("fc_fields", "*", "formid = " . intval($this->formid), array("order_by" => "`order`"));
        while ($field_data = $db->fetch_array($query))
        {
            $field = new formcreator_field();
            $field->load_data($field_data);
            $this->fields[] = $field;
        }

        if (count($this->fields) != 0)
        {
            return $this->fields;
        }
        else
        {
            return false;
        }
    }

    public function clear_error()
    {
        $this->error = "";
    }

    public function is_error()
    {
        if (empty($this->error))
        {
            return false;
        }
        else
        {
            return $this->error;
        }
    }

    public function add_error($string)
    {
        if ($this->error == "")
        {
            $this->error = $string;
        }
        else
        {
            $this->error .= "<br />" . $string;
        }
    }

    public function order_field($fieldid, $order)
    {
        global $db;
        return $db->update_query("fc_fields", array("order" => intval($order)), "fieldid = " . intval($fieldid) . " and formid = " . $this->formid);
    }

    public function get_field_names()
    {
        $result = array();
        foreach ($this->fields as $field)
        {
            $result[$field->fieldid] = $field->name;
        }

        return $result;
    }

    public function get_field_values()
    {
        global $mybb;
        $result = array();
        foreach ($this->fields as $field)
        {
            $value = $mybb->input['field_' . $field->fieldid];
            if (is_array($value))
            {
                $value = implode(",", $value);
            }

            if (empty($value))
            {
                $value = "Unknown";
            }

            $result[$field->fieldid] = $value;
        }

        return $result;
    }

    public function parse_subject()
    {
        global $templates, $mybb, $ref;
        if (empty($this->subjecttemplate))
        {
            return "Form submission: " . $this->name;
        }
        else
        {
            $this->subjecttemplate = str_replace('"', '\"', $this->subjecttemplate);
            $username = $mybb->user['username'];
            $uid = $mybb->user['uid'];
            $formname = $this->name;
            $fieldname = $this->get_field_names();
            $fieldvalue = $this->get_field_values();
            eval("\$output = \"" . $this->subjecttemplate . "\";");
            return $output;
        }
    }

    public function parse_output()
    {
        global $db, $mybb, $ref;
        $output = "";
        if (empty($this->messagetemplate))
        {
            foreach ($this->fields as $field)
            {
                if (in_array($field->type, array(
                    1,
                    2,
                    5,
                    7,
                    15)))
                {
                    $output .= "[b]" . $field->name . "[/b]: " . $mybb->input["field_" . $field->fieldid] . "\n[hr]";
                }
                elseif (in_array($field->type, array(
                    4,
                    6,
                    3)))
                {
                    $output .= "[b]" . $field->name . "[/b]: " . implode(",", $mybb->input["field_" . $field->fieldid]) . "\n[hr]";
                }
            }
        }
        else
        {
            $username = $mybb->user['username'];
            $uid = $mybb->user['uid'];
            $fieldname = $this->get_field_names();
            $fieldvalue = $this->get_field_values();
            $this->messagetemplate = str_replace('"', '\"', $this->messagetemplate);
            eval("\$output = \"" . $this->messagetemplate . "\";");
        }

        return $output;
    }

    public function get_next_ref()
    {
        global $db;

        $query = $db->simple_select("fc_formusage", "*", "formid = '" . $this->formid . "'", array(
            "order_by" => "ref",
            "order_dir" => "DESC",
            "LIMIT" => 1));
        if ($db->num_rows($query) != 0)
        {
            $lastrow = $db->fetch_array($query);
        }
        else
        {
            $lastrow = 0;
        }

        return $lastrow['ref'] + 1;
    }

    public function log_usage()
    {
        global $db, $mybb;

        $data = array(
            "formid" => $this->formid,
            "uid" => $mybb->user['uid'],
            "ref" => $this->get_next_ref());

        $db->insert_query("fc_formusage", $data);
    }

    public function check_usage_limit_reached()
    {
        global $db, $mybb;

        if ($this->settings['limitusage'] == 0)
        {
            return false;
        }

        $query = $db->simple_select("fc_formusage", "*", "formid = '" . $this->formid . "' AND uid = '" . $mybb->user['uid'] . "'");
        $timesused = $db->num_rows($query);

        if ($timesused >= $this->settings['limitusage'])
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

class formcreator_field
{
    public $fieldid;
    public $formid;
    public $name;
    public $description;
    public $placeholder;
    public $maxlength;
    public $type;
    public $options;
    public $default;
    public $required;
    public $regex;
    public $regexerror;
    public $order;
    public $size;
    public $cols;
    public $rows;
    public $class;
    public $format;
    public $settings;

    public function escape_data()
    {
        global $db;
        $this->fieldid = intval($this->fieldid);
        $this->formid = intval($this->formid);
        $this->name = $db->escape_string($this->name);
        $this->description = $db->escape_string($this->description);
        $this->type = intval($this->type);
        $this->default = $db->escape_string($this->default);
        $this->required = intval($this->required);
        $this->order = intval($this->order);
        $this->class = $db->escape_string($this->class);
        $this->settings = $db->escape_string(json_encode($this->settings));
    }

    public function load_data($data)
    {
        $this->fieldid = $data['fieldid'];
        $this->formid = $data['formid'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->type = $data['type'];
        $this->default = $data['default'];
        $this->required = $data['required'];
        $this->order = $data['order'];
        $this->class = $data['class'];

        if (!is_array($data['settings']))
        {
            $this->settings = json_decode($data['settings'], true);
        }
        else
        {
            $this->settings = $data['settings'];
        }
    }

    public function get_data()
    {
        if ($this->fieldid)
        {
            $data['fieldid'] = $this->fieldid;
        }

        $data['formid'] = $this->formid;
        $data['name'] = $this->name;
        $data['description'] = $this->description;
        $data['type'] = $this->type;
        $data['default'] = $this->default;
        $data['required'] = $this->required;
        $data['order'] = $this->order;
        $data['class'] = $this->class;
        $data['settings'] = $this->settings;
        return $data;
    }

    public function show_admin_field($name)
    {
        if ($this->type)
        {
            if ($this->type == 1)
            {
                $show = array(
                    "name",
                    "description",
                    "placeholder",
                    "maxlength",
                    "default",
                    "required",
                    "regex",
                    "size",
                    "class");
            }
            elseif ($this->type == 2)
            {
                $show = array(
                    "name",
                    "description",
                    "placeholder",
                    "maxlength",
                    "default",
                    "required",
                    "regex",
                    "cols",
                    "rows",
                    "class");
            }
            elseif ($this->type == 3)
            {
                $show = array(
                    "name",
                    "description",
                    "options",
                    "required",
                    "class");
            }
            elseif ($this->type == 4)
            {
                $show = array(
                    "name",
                    "description",
                    "options",
                    "required",
                    "class",
                    "size");
            }
            elseif ($this->type == 5)
            {
                $show = array(
                    "name",
                    "description",
                    "options",
                    "required",
                    "class");
            }
            elseif ($this->type == 6)
            {
                $show = array(
                    "name",
                    "description",
                    "options",
                    "required",
                    "class");
            }
            elseif ($this->type == 7)
            {
                $show = array(
                    "name",
                    "description",
                    "format",
                    "default",
                    "required",
                    "class");
            }
            elseif ($this->type == 8)
            {
                $show = array("name");
            }
            elseif ($this->type == 9)
            {
                $show = array("name", "description");
            }
            elseif ($this->type == 10)
            {
                $show = array(
                    "name",
                    "html",
                    "class");
            }
            elseif ($this->type == 11)
            {
                $show = array("name", "class");
            }
            elseif ($this->type == 12)
            {
                $show = array("name");
            }
            elseif ($this->type == 13)
            {
                $show = array(
                    "name",
                    "description",
                    "required",
                    "class");
            }
            elseif ($this->type == 14)
            {
                $show = array(
                    "name",
                    "description",
                    "required",
                    "class");
            }
            elseif ($this->type == 15)
            {
                $show = array(
                    "name",
                    "description",
                    "required",
                    "rows");
            }
            else
            {
                $show = array();
            }

            return in_array($name, $show);
        }
        else
        {
            return false;
        }
    }

    public function clear_error()
    {
        $this->error = "";
    }

    public function is_error()
    {
        if (empty($this->error))
        {
            return false;
        }
        else
        {
            return $this->error;
        }
    }

    public function add_error($string)
    {
        if ($this->error == "")
        {
            $this->error = $string;
        }
        else
        {
            $this->error .= "<br />" . $string;
        }
    }

    public function insert_field()
    {
        global $db;
        $this->escape_data();
        $query = $db->simple_select("fc_fields", "`order`", "formid = " . $this->formid, array("order_by" => "`order`", "order_dir" => "DESC"));
        if ($db->num_rows($query) == 0)
        {
            $this->order = 0;
        }
        else
        {
            $lastfield = $db->fetch_array($query);
            $this->order = $lastfield['order'] + 1;
        }

        $result = $db->insert_query("fc_fields", $this->get_data());
        if ($result)
        {
            $this->fieldid = $result;
            return $result;
        }
        else
        {
            return false;
        }
    }

    public function update_field()
    {
        global $db;
        $this->escape_data();
        $old = new formcreator_field();
        $old_data = $old->get_field($this->fieldid);
        $this->order = $old_data['order'];
        $result = $db->update_query("fc_fields", $this->get_data(), "fieldid = " . $this->fieldid);
        return $result;
    }

    public function delete_field()
    {
        global $db;
        if ($db->delete_query("fc_fields", "fieldid = " . $this->fieldid))
        {
            return true;
        }
        else
        {
            return false;
        }

    }

    public function get_field($fieldid)
    {
        global $db;
        $query = $db->simple_select("fc_fields", "*", "fieldid = " . intval($fieldid));
        if ($db->num_rows($query) == 1)
        {
            $fielddata = $db->fetch_array($query);
            $this->load_data($fielddata);
            return $fielddata;
        }
        else
        {
            return false;
        }
    }

    public function output_textbox()
    {
        if ($this->settings['size'])
        {
            $size = "size='" . $this->settings['size'] . "'";
        }

        if ($this->settings['placeholder'])
        {
            $placeholder = "placeholder ='" . $this->settings['placeholder'] . "'";
        }

        if ($this->settings['maxlength'] != 0)
        {
            $maxlength = "maxlength ='" . $this->settings['maxlength'] . "'";
        }

        return "<input type='text' value='" . $this->default . "' name='field_" . $this->fieldid . "' class='textbox " . $this->class . "' " . $size . " " . $placeholder .
            " " . $maxlength . " />";
    }

    public function output_textarea()
    {
        if ($this->class)
        {
            $class = "class='" . $this->class . "'";
        }

        if ($this->settings['rows'])
        {
            $rows = "rows='" . $this->settings['rows'] . "'";
        }

        if ($this->settings['cols'])
        {
            $cols = "cols='" . $this->settings['cols'] . "'";
        }

        if ($this->settings['placeholder'])
        {
            $placeholder = "placeholder ='" . $this->settings['placeholder'] . "'";
        }

        if ($this->settings['maxlength'] != 0)
        {
            $maxlength = "maxlength ='" . $this->settings['maxlength'] . "'";
        }

        return "<textarea name='field_" . $this->fieldid . "' " . $class . " " . $rows . " " . $cols . " " . $placeholder . " " . $maxlength . ">" . $this->
            default . "</textarea>";
    }

    public function output_select($multi = false)
    {
        global $lang;

        $options = explode("\n", $this->settings['options']);
        if ($this->class)
        {
            $class = "class='" . $this->class . "'";
        }

        if ($multi)
        {
            $multi = "multiple='multiple'";
        }

        if ($this->settings['size'] != 0)
        {
            $size = "size='" . $this->settings['size'] . "'";
        }

        $output = "<select name='field_" . $this->fieldid . "[]' " . $class . " " . $multi . " " . $size . ">";
        if (!$multi)
        {
            $output .= "<option value=''>- " . $lang->fc_select_option . " -</option>";
        }

        foreach ($options as $option)
        {
            if (is_array($this->default))
            {
                if (in_array(trim($option), $this->default))
                {
                    $selected = "selected='selected'";
                }
                else
                {
                    $selected = "";
                }
            }

            $output .= "<option value='" . trim($option) . "' " . $selected . ">" . $option . "</option>";
        }

        $output .= "</select>";
        return $output;
    }

    public function output_dateselect()
    {
        if ($this->class)
        {
            $class = $this->class;
        }

        $output = "<input type='text' id='field_" . $this->fieldid . "' value='" . $this->default . "' name='field_" . $this->fieldid .
            "' class='textbox dateselect " . $this->class . "' />";
        if (empty($this->settings['format']))
        {
            $output .= "<script>
        	  $( function() {
        		$( \"#field_" . $this->fieldid . "\" ).datepicker();
        	  } );
          </script>";
        }
        else
        {
            $output .= "<script>
        	  $( function() {
        		$( \"#field_" . $this->fieldid . "\" ).datepicker({
          dateFormat: '" . $this->settings['format'] . "'
        });
        	  } );
          </script>";
        }


        return $output;
    }

    public function output_header()
    {
        if ($this->description)
        {
            return "<strong>" . $this->name . "</strong><br /><small>" . $this->description . "</small>";
        }
        else
        {
            return "<strong>" . $this->name . "</strong>";
        }
    }

    public function output_radio()
    {
        $options = explode("\n", $this->settings['options']);
        if ($this->class)
        {
            $class = "class='" . $this->class . "'";
        }

        $output = "";
        foreach ($options as $option)
        {
            if ($this->default == trim($option))
            {
                $checked = "checked='checked'";
            }
            else
            {
                $checked = "";
            }

            $output .= "<input type='radio' name='field_" . $this->fieldid . "' id='" . $this->name . "_" . $option . "' value='" . trim($option) . "' " . $checked .
                " /><label for='" . $this->name . "_" . $option . "'>" . $option . "<label><br />";
        }

        return $output;
    }

    public function output_checkbox()
    {
        $options = explode("\n", $this->settings['options']);
        if ($this->class)
        {
            $class = "class='" . $this->class . "'";
        }

        $output = "";
        foreach ($options as $option)
        {
            if (is_array($this->default))
            {
                if (in_array(trim($option), $this->default))
                {
                    $checked = "checked='checked'";
                }
                else
                {
                    $checked = "";
                }
            }

            $output .= "<input type='checkbox' name='field_" . $this->fieldid . "[]' id='" . $this->name . "_" . $option . "' value='" . trim($option) . "' " . $checked .
                " /><label for='" . $this->name . "_" . $option . "'>" . $option . "<label><br />";
        }

        return $output;
    }

    public function output_submit()
    {
        if ($this->class)
        {
            $class = "class='" . $this->class . "'";
        }

        return "<input type='submit' value='" . $this->name . "' " . $class . " />";
    }

    public function output_captcha()
    {
        global $mybb;
        if ($this->class)
        {
            $class = "class='" . $this->class . "'";
        }

        $captcha = new captcha();
        $captcha->type = $mybb->settings['captchaimage'];
        if ($captcha->type == 1)
        {
            $captcha->captcha_template = "formcreator_captcha";
            $captcha->build_captcha();
        }
        elseif ($captcha->type == 2 || $captcha->type == 4)
        {
            if ($captcha->type == 2)
            {
                $captcha->captcha_template = "formcreator_recaptcha";
            }
            elseif ($captcha->type == 4)
            {
                $captcha->captcha_template = "formcreator_nocaptcha";
            }

            $captcha->build_recaptcha();
        }
        else
        {
            echo "error";
        }

        return $captcha->html;
    }

    public function output_attachment()
    {
        return "<input type='file' value='" . $this->default . "' name='field_" . $this->fieldid . "' class='fileupload " . $this->class . "' />";
    }

    public function output_attachments()
    {
        return "<input type='file' value='" . $this->default . "' name='field_" . $this->fieldid . "[]' class='fileupload " . $this->class .
            "' multiple='multiple' />";
    }

    public function output_editor()
    {
        if ($this->settings['rows'])
        {
            $rows = "rows='" . $this->settings['rows'] . "'";
        }
        else
        {
            $rows = "rows='20'";
        }

        $code = build_mycode_inserter("field_" . $this->fieldid, true);

        return "<textarea " . $rows . " name='field_" . $this->fieldid . "' id='field_" . $this->fieldid . "' />" . $this->default . "</textarea>\n" . $code;
    }
}

?>