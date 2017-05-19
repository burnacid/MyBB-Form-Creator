<?php

class formcreator
{
    public $formid;
    public $name;
    public $allowedgidtype;
    public $allowedgid;
    public $allgroups;
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

    public $fields;

    private $output;

    private $error;

    // Table structures from Array
    public $formcreator_fields = array("fc_forms" => array(
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
                "Field" => "allowedgidtype",
                "Type" => "int(11)",
                "NULL" => 0),
            array(
                "Field" => "allowedgid",
                "Type" => "text",
                "NULL" => 1),
            array(
                "Field" => "active",
                "Type" => "tinyint(1)",
                "NULL" => 0),
            array(
                "Field" => "pmusers",
                "Type" => "varchar(255)",
                "NULL" => 1),
            array(
                "Field" => "pmgroups",
                "Type" => "varchar(255)",
                "NULL" => 1),
            array(
                "Field" => "fid",
                "Type" => "int(11)",
                "NULL" => 1),
            array(
                "Field" => "tid",
                "Type" => "int(11)",
                "NULL" => 1),
            array(
                "Field" => "uid",
                "Type" => "int(11)",
                "NULL" => 1),
            array(
                "Field" => "prefix",
                "Type" => "int(11)",
                "NULL" => 1),
            array(
                "Field" => "overridebutton",
                "Type" => "tinyint(1)",
                "NULL" => 1),
            array(
                "Field" => "mail",
                "Type" => "text",
                "NULL" => 1),
            array(
                "Field" => "width",
                "Type" => "varchar(50)",
                "NULL" => 1),
            array(
                "Field" => "labelwidth",
                "Type" => "varchar(50)",
                "NULL" => 1),
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
                "NULL" => 1)), "fc_fields" => array(
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
                "Field" => "format",
                "Type" => "varchar(255)",
                "NULL" => 1),
            array(
                "Field" => "options",
                "Type" => "varchar(2000)",
                "NULL" => 1),
            array(
                "Field" => "default",
                "Type" => "varchar(2000)",
                "NULL" => 1),
            array(
                "Field" => "required",
                "Type" => "tinyint(1)",
                "NULL" => 1),
            array(
                "Field" => "regex",
                "Type" => "text",
                "NULL" => 1),
            array(
                "Field" => "regexerror",
                "Type" => "varchar(500)",
                "NULL" => 1),
            array(
                "Field" => "order",
                "Type" => "int(11)",
                "NULL" => 1),
            array(
                "Field" => "size",
                "Type" => "int(11)",
                "NULL" => 1),
            array(
                "Field" => "cols",
                "Type" => "int(11)",
                "NULL" => 1),
            array(
                "Field" => "rows",
                "Type" => "int(11)",
                "NULL" => 1),
            array(
                "Field" => "class",
                "Type" => "varchar(50)",
                "NULL" => 1),
            array("Field" => "html", "Type" => "text")));
            
    public $types = array(
        1 => "Textbox (single line)",
        2 => "Textarea (multi line)",
        3 => "Select (single)",
        4 => "Select (multiple)",
        5 => "Radio Buttons",
        6 => "Checkboxes",
        7 => "Date",
        8 => "Seperator",
        9 => "Header",
        10 => "HTML block",
        11 => "Submit button",
        12 => "Captcha");
    public function get_form($formid)
    {
        global $db;
        $query = $db->simple_select("fc_forms", "*", "formid = " . intval($formid));
        if ($db->num_rows($query) == 1) {
            $formdata = $db->fetch_array($query);
            $formdata['allowedgid'] = explode(",", $formdata['allowedgid']);
            $formdata['pmgroups'] = explode(",", $formdata['pmgroups']);
            $this->load_data($formdata);
            return $formdata;
        } else {
            return false;
        }
    }

    public function build_form()
    {
        global $templates, $stylelabelwidth;
        $output = "";
        foreach ($this->fields as $field) {
            $fieldname = $field->name;
            $fieldoutput = "";
            if ($field->required) {
                $fieldname .= "<em>*</em>";
            }

            if ($field->description) {
                $fielddescription = "<br /><small>" . $field->description . "</small>";
            } else {
                $fielddescription = "";
            }

            switch ($field->type) {
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
                    if ($this->width) {
                        $stylewidth = "width:" . $this->width . ";";
                    }

                    if ($this->class) {
                        $styleclass = $this->class;
                    }

                    eval('$output .= "' . $templates->get("formcreator_field_seperator") . '";');
                    break;
                case 9:
                    $fieldoutput = $field->output_header();
                    eval('$output .= "' . $templates->get("formcreator_field_header") . '";');
                    break;
                case 10:
                    $fieldoutput = $field->html;
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
            }
        }

        return $output;
    }

    public function check_allowed()
    {
        global $mybb;
        if ($this->allowedgidtype == -1) {
            return true;
        }

        if (!empty($mybb->user['additionalgroups'])) {
            $current_groups = $mybb->user['additionalgroups'] . "," . $mybb->user['usergroup'];
        } else {
            $current_groups = $mybb->user['usergroup'];
        }

        $current_groups = explode(",", $current_groups);
        if (array_intersect($this->allowedgid, $current_groups)) {
            if ($this->allowedgidtype == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($this->allowedgidtype == 0) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function get_type_name($type)
    {
        if (key_exists(intval($type), $this->types)) {
            return $this->types[intval($type)];
        } else {
            return false;
        }
    }

    public function insert_form()
    {
        global $db;
        if ($this->allowedgid && is_array($this->allowedgid)) {
            $this->allowedgid = implode(",", $this->allowedgid);
        }

        if ($this->pmgroups && is_array($this->pmgroups)) {
            $this->pmgroups = implode(",", $this->pmgroups);
        }

        $this->escape_data();
        $result = $db->insert_query("fc_forms", $this->get_data());
        if ($result) {
            $this->formid = $result;
            return $result;
        } else {
            return false;
        }
    }

    public function update_template()
    {
        global $db;
        if ($this->allowedgid && is_array($this->allowedgid)) {
            $this->allowedgid = implode(",", $this->allowedgid);
        }

        if ($this->pmgroups && is_array($this->pmgroups)) {
            $this->pmgroups = implode(",", $this->pmgroups);
        }

        $this->escape_data();
        if (empty($this->subjecttemplate)) {
            $this->subjecttemplate = "";
        }

        if (empty($this->messagetemplate)) {
            $this->messagetemplate = "";
        }

        $template_data = array("subjecttemplate" => $this->subjecttemplate, "messagetemplate" => $this->messagetemplate);
        $result = $db->update_query("fc_forms", $template_data, "formid = " . $this->formid);
        return $result;
    }

    public function update_form()
    {
        global $db;
        if ($this->allowedgid) {
            $this->allowedgid = implode(",", $this->allowedgid);
        }

        if ($this->pmgroups) {
            $this->pmgroups = implode(",", $this->pmgroups);
        }

        $this->escape_data();
        $result = $db->update_query("fc_forms", $this->get_data(), "formid = " . $this->formid);
        return $result;
    }

    public function delete_form()
    {
        global $db;
        if ($db->delete_query("fc_fields", "formid = " . $this->formid)) {
            if ($db->delete_query("fc_forms", "formid = " . $this->formid)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function escape_data()
    {
        global $db;
        $this->formid = intval($this->formid);
        $this->name = $db->escape_string($this->name);
        $this->allowedgidtype = intval($this->allowedgidtype);
        $this->allowedgid = $db->escape_string($this->allowedgid);
        $this->active = intval($this->active);
        $this->pmusers = $db->escape_string($this->pmusers);
        $this->pmgroups = $db->escape_string($this->pmgroups);
        $this->fid = intval($this->fid);
        $this->tid = intval($this->tid);
        $this->uid = intval($this->uid);
        $this->prefix = intval($this->prefix);
        $this->overridebutton = intval($this->overridebutton);
        $this->mail = $db->escape_string($this->mail);
        $this->width = $db->escape_string($this->width);
        $this->labelwidth = $db->escape_string($this->labelwidth);
        $this->class = $db->escape_string($this->class);
        $this->subjecttemplate = $db->escape_string($this->subjecttemplate);
        $this->messagetemplate = $db->escape_string($this->messagetemplate);
    }

    public function load_data($data)
    {
        $this->formid = $data['formid'];
        $this->name = $data['name'];
        $this->allowedgidtype = $data['allowedgidtype'];
        $this->allowedgid = $data['allowedgid'];
        $this->active = $data['active'];
        $this->pmusers = $data['pmusers'];
        $this->pmgroups = $data['pmgroups'];
        $this->fid = $data['fid'];
        $this->tid = $data['tid'];
        $this->uid = $data['uid'];
        $this->prefix = $data['prefix'];
        $this->overridebutton = $data['overridebutton'];
        $this->mail = $data['mail'];
        $this->width = $data['width'];
        $this->labelwidth = $data['labelwidth'];
        $this->class = $data['class'];
        $this->subjecttemplate = $data['subjecttemplate'];
        $this->messagetemplate = $data['messagetemplate'];
    }

    public function get_data()
    {
        if ($this->formid) {
            $data['formid'] = $this->formid;
        }

        $data['name'] = $this->name;
        $data['allowedgidtype'] = $this->allowedgidtype;
        $data['allowedgid'] = $this->allowedgid;
        $data['active'] = $this->active;
        $data['pmusers'] = $this->pmusers;
        $data['pmgroups'] = $this->pmgroups;
        $data['fid'] = $this->fid;
        $data['tid'] = $this->tid;
        $data['uid'] = $this->uid;
        $data['prefix'] = $this->prefix;
        $data['overridebutton'] = $this->overridebutton;
        $data['mail'] = $this->mail;
        $data['width'] = $this->width;
        $data['labelwidth'] = $this->labelwidth;
        $data['class'] = $this->class;
        return $data;
    }

    public function get_fields()
    {
        global $db;
        $query = $db->simple_select("fc_fields", "*", "formid = " . intval($this->formid), array("order_by" => "`order`"));
        while ($field_data = $db->fetch_array($query)) {
            $field = new formcreator_field();
            $field->load_data($field_data);
            $this->fields[] = $field;
        }

        if (count($this->fields) != 0) {
            return $this->fields;
        } else {
            return false;
        }
    }

    public function clear_error()
    {
        $this->error = "";
    }

    public function is_error()
    {
        if (empty($this->error)) {
            return false;
        } else {
            return $this->error;
        }
    }

    public function add_error($string)
    {
        if ($this->error == "") {
            $this->error = $string;
        } else {
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
        foreach ($this->fields as $field) {
            $result[$field->fieldid] = $field->name;
        }

        return $result;
    }

    public function get_field_values()
    {
        global $mybb;
        $result = array();
        foreach ($this->fields as $field) {
            $value = $mybb->input['field_' . $field->fieldid];
            if (is_array($value)) {
                $value = implode(",", $value);
            }

            if (empty($value)) {
                $value = "Unknown";
            }

            $result[$field->fieldid] = $value;
        }

        return $result;
    }

    public function parse_subject()
    {
        global $templates, $mybb;
        if (empty($this->subjecttemplate)) {
            return "Form submission: " . $this->name;
        } else {
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
        global $db, $mybb;
        $output = "";
        if (empty($this->messagetemplate)) {
            foreach ($this->fields as $field) {
                if (in_array($field->type, array(
                    1,
                    2,
                    5,
                    7))) {
                    $output .= "[b]" . $field->name . "[/b]: " . $mybb->input["field_" . $field->fieldid] . "\n[hr]";
                } elseif (in_array($field->type, array(
                    4,
                    6,
                    3))) {
                    $output .= "[b]" . $field->name . "[/b]: " . implode(",", $mybb->input["field_" . $field->fieldid]) . "\n[hr]";
                }
            }
        } else {
            $username = $mybb->user['username'];
            $uid = $mybb->user['uid'];
            $fieldname = $this->get_field_names();
            $fieldvalue = $this->get_field_values();
            $this->messagetemplate = str_replace('"', '\"', $this->messagetemplate);
            eval("\$output = \"" . $this->messagetemplate . "\";");
        }

        return $output;
    }
}

class formcreator_field
{
    public $fieldid;
    public $formid;
    public $name;
    public $description;
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
    public function escape_data()
    {
        global $db;
        $this->fieldid = intval($this->fieldid);
        $this->formid = intval($this->formid);
        $this->name = $db->escape_string($this->name);
        $this->description = $db->escape_string($this->description);
        $this->type = intval($this->type);
        $this->options = $db->escape_string($this->options);
        $this->default = $db->escape_string($this->default);
        $this->required = intval($this->required);
        $this->regex = $db->escape_string($this->regex);
        $this->regexerror = $db->escape_string($this->regexerror);
        $this->order = intval($this->order);
        $this->size = intval($this->size);
        $this->cols = intval($this->cols);
        $this->rows = intval($this->rows);
        $this->class = $db->escape_string($this->class);
        $this->html = $db->escape_string($this->html);
        $this->format = $db->escape_string($this->format);
    }

    public function load_data($data)
    {
        $this->fieldid = $data['fieldid'];
        $this->formid = $data['formid'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->type = $data['type'];
        $this->options = $data['options'];
        $this->default = $data['default'];
        $this->required = $data['required'];
        $this->regex = $data['regex'];
        $this->regexerror = $data['regexerror'];
        $this->order = $data['order'];
        $this->size = $data['size'];
        $this->cols = $data['cols'];
        $this->rows = $data['rows'];
        $this->class = $data['class'];
        $this->html = $data['html'];
        $this->format = $data['format'];
    }

    public function get_data()
    {
        if ($this->fieldid) {
            $data['fieldid'] = $this->fieldid;
        }

        $data['formid'] = $this->formid;
        $data['name'] = $this->name;
        $data['description'] = $this->description;
        $data['type'] = $this->type;
        $data['options'] = $this->options;
        $data['default'] = $this->default;
        $data['required'] = $this->required;
        $data['regex'] = $this->regex;
        $data['regexerror'] = $this->regexerror;
        $data['order'] = $this->order;
        $data['size'] = $this->size;
        $data['cols'] = $this->cols;
        $data['rows'] = $this->rows;
        $data['class'] = $this->class;
        $data['html'] = $this->html;
        $data['format'] = $this->format;
        return $data;
    }

    public function show_admin_field($name)
    {
        if ($this->type) {
            if ($this->type == 1) {
                $show = array(
                    "name",
                    "description",
                    "default",
                    "required",
                    "regex",
                    "size",
                    "class");
            } elseif ($this->type == 2) {
                $show = array(
                    "name",
                    "description",
                    "default",
                    "required",
                    "regex",
                    "cols",
                    "rows",
                    "class");
            } elseif ($this->type == 3) {
                $show = array(
                    "name",
                    "description",
                    "options",
                    "required",
                    "class");
            } elseif ($this->type == 4) {
                $show = array(
                    "name",
                    "description",
                    "options",
                    "required",
                    "class",
                    "size");
            } elseif ($this->type == 5) {
                $show = array(
                    "name",
                    "description",
                    "options",
                    "required",
                    "class");
            } elseif ($this->type == 6) {
                $show = array(
                    "name",
                    "description",
                    "options",
                    "required",
                    "class");
            } elseif ($this->type == 7) {
                $show = array(
                    "name",
                    "description",
                    "format",
                    "default",
                    "required",
                    "class");
            } elseif ($this->type == 8) {
                $show = array("name");
            } elseif ($this->type == 9) {
                $show = array("name", "description");
            } elseif ($this->type == 10) {
                $show = array(
                    "name",
                    "html",
                    "class");
            } elseif ($this->type == 11) {
                $show = array("name", "class");
            } elseif ($this->type == 12) {
                $show = array("name");
            } else {
                $show = array();
            }

            return in_array($name, $show);
        } else {
            return false;
        }
    }

    public function clear_error()
    {
        $this->error = "";
    }

    public function is_error()
    {
        if (empty($this->error)) {
            return false;
        } else {
            return $this->error;
        }
    }

    public function add_error($string)
    {
        if ($this->error == "") {
            $this->error = $string;
        } else {
            $this->error .= "<br />" . $string;
        }
    }

    public function insert_field()
    {
        global $db;
        $this->escape_data();
        $query = $db->simple_select("fc_fields", "`order`", "formid = " . $this->formid, array("order_by" => "`order`", "order_dir" => "DESC"));
        if ($db->num_rows($query) == 0) {
            $this->order = 0;
        } else {
            $lastfield = $db->fetch_array($query);
            $this->order = $lastfield['order'] + 1;
        }

        $result = $db->insert_query("fc_fields", $this->get_data());
        if ($result) {
            $this->fieldid = $result;
            return $result;
        } else {
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
        if ($db->delete_query("fc_fields", "fieldid = " . $this->fieldid)) {
            return true;
        } else {
            return false;
        }

    }

    public function get_field($fieldid)
    {
        global $db;
        $query = $db->simple_select("fc_fields", "*", "fieldid = " . intval($fieldid));
        if ($db->num_rows($query) == 1) {
            $fielddata = $db->fetch_array($query);
            $this->load_data($fielddata);
            return $fielddata;
        } else {
            return false;
        }
    }

    public function output_textbox()
    {
        if ($this->size) {
            $size = "size='" . $this->size . "'";
        }

        return "<input type='text' value='" . $this->default . "' name='field_" . $this->fieldid . "' class='textbox " . $this->class . "' " . $size . " />";
    }

    public function output_textarea()
    {
        if ($this->class) {
            $class = "class='" . $this->class . "'";
        }

        if ($this->rows) {
            $rows = "rows='" . $this->rows . "'";
        }

        if ($this->cols) {
            $cols = "cols='" . $this->cols . "'";
        }

        return "<textarea name='field_" . $this->fieldid . "' " . $class . " " . $rows . " " . $cols . ">" . $this->default . "</textarea>";
    }

    public function output_select($multi = false)
    {
        $options = explode("\n", $this->options);
        if ($this->class) {
            $class = "class='" . $this->class . "'";
        }

        if ($multi) {
            $multi = "multiple='multiple'";
        }

        if ($this->size != 0) {
            $size = "size='" . $this->size . "'";
        }

        $output = "<select name='field_" . $this->fieldid . "[]' " . $class . " " . $multi . " " . $size . ">";
        if (!$multi) {
            $output .= "<option value=''>- Select option -</option>";
        }

        foreach ($options as $option) {
            if (is_array($this->default)) {
                if (in_array(trim($option), $this->default)) {
                    $selected = "selected='selected'";
                } else {
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
        if ($this->class) {
            $class = $this->class;
        }

        $output = "<input type='text' id='field_" . $this->fieldid . "' value='" . $this->default . "' name='field_" . $this->fieldid .
            "' class='textbox dateselect " . $this->class . "' />";
        if (empty($this->format)) {
            $output .= "<script>
        	  $( function() {
        		$( \"#field_" . $this->fieldid . "\" ).datepicker();
        	  } );
          </script>";
        } else {
            $output .= "<script>
        	  $( function() {
        		$( \"#field_" . $this->fieldid . "\" ).datepicker({
          dateFormat: '" . $this->format . "'
        });
        	  } );
          </script>";
        }


        return $output;
    }

    public function output_header()
    {
        if ($this->description) {
            return "<strong>" . $this->name . "</strong><br /><small>" . $this->description . "</small>";
        } else {
            return "<strong>" . $this->name . "</strong>";
        }
    }

    public function output_radio()
    {
        $options = explode("\n", $this->options);
        if ($this->class) {
            $class = "class='" . $this->class . "'";
        }

        $output = "";
        foreach ($options as $option) {
            if ($this->default == trim($option)) {
                $checked = "checked='checked'";
            } else {
                $checked = "";
            }

            $output .= "<input type='radio' name='field_" . $this->fieldid . "' id='" . $this->name . "_" . $option . "' value='" . trim($option) . "' " . $checked .
                " /><label for='" . $this->name . "_" . $option . "'>" . $option . "<label><br />";
        }

        return $output;
    }

    public function output_checkbox()
    {
        $options = explode("\n", $this->options);
        if ($this->class) {
            $class = "class='" . $this->class . "'";
        }

        $output = "";
        foreach ($options as $option) {
            if (is_array($this->default)) {
                if (in_array(trim($option), $this->default)) {
                    $checked = "checked='checked'";
                } else {
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
        if ($this->class) {
            $class = "class='" . $this->class . "'";
        }

        return "<input type='submit' value='" . $this->name . "' " . $class . " />";
    }

    public function output_captcha()
    {
        global $mybb;
        if ($this->class) {
            $class = "class='" . $this->class . "'";
        }

        $captcha = new captcha();
        $captcha->type = $mybb->settings['captchaimage'];
        if ($captcha->type == 1) {
            $captcha->captcha_template = "formcreator_captcha";
            $captcha->build_captcha();
        } elseif ($captcha->type == 2 || $captcha->type == 4) {
            if ($captcha->type == 2) {
                $captcha->captcha_template = "formcreator_recaptcha";
            } elseif ($captcha->type == 4) {
                $captcha->captcha_template = "formcreator_nocaptcha";
            }

            $captcha->build_recaptcha();
        } else {
            echo "error";
        }

        return $captcha->html;
    }
}

?>