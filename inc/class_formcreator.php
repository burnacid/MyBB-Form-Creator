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

    private function get_order_column_sql()
    {
        global $db;

        $db_type = strtolower((string)$db->type);
        if ($db_type == 'pgsql' || $db_type == 'sqlite') {
            return '"order"';
        }

        return '`order`';
    }

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
        16 => "Prefix Selection",

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
        if ($db->num_rows($query) == 1) {
            $formdata = $db->fetch_array($query);
            $this->load_data($formdata);
            return $formdata;
        } else {
            return false;
        }
    }

    public function field_in_table($table, $fieldname)
    {
        $tabledata = $this->formcreator_fields[$table];

        foreach ($tabledata as $field) {
            if ($field['Field'] == $fieldname) {
                return true;
            }
        }

        return false;
    }

    public function build_summary()
    {
        global $templates, $stylelabelwidth;
        $output = "";
        
        if($this->settings['summaryparsed']){
            $parser = new postParser;

            $parser_options = array(
                'allow_html' => 'no',
                'allow_mycode' => 'yes',
                'allow_smilies' => 'yes',
                'allow_imgcode' => 'yes',
                'filter_badwords' => 'yes',
                'nl2br' => 1);

            $output = $parser->parse_message($this->parse_output(), $parser_options);;
        }else{
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
                
                if(is_array($field->default)){
                    $fieldoutput = implode(", ", $field->default);
                }else{
                    $fieldoutput = $field->default;
                }
                
                
                if(!in_array($field->type,array(8,9,10,11,12,13,14))){
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                }
                
            }
        }
        
        return $output;
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
                    if ($this->settings['width']) {
                        $stylewidth = "width:" . $this->settings['width'] . ";";
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
                case 16:
                    $fieldoutput = $field->output_prefix();
                    eval('$output .= "' . $templates->get("formcreator_field") . '";');
                    break;
            }
        }

        return $output;
    }

    public function check_allowed()
    {
        global $mybb;
        if ($this->settings['allowedgidtype'] == -1) {
            return true;
        }

        if (!empty($mybb->user['additionalgroups'])) {
            $current_groups = $mybb->user['additionalgroups'] . "," . $mybb->user['usergroup'];
        } else {
            $current_groups = $mybb->user['usergroup'];
        }

        $current_groups = explode(",", $current_groups);
        if (array_intersect($this->settings['allowedgid'], $current_groups)) {
            if ($this->settings['allowedgidtype'] == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($this->settings['allowedgidtype'] == 0) {
                return false;
            } else {
                return true;
            }
        }
    }
    
    public function check_summary()
    {
        global $mybb;

        $formdata = array();
        if (isset($mybb->input['formdata']) && is_array($mybb->input['formdata'])) {
            $formdata = $mybb->input['formdata'];
        }
        
        if($this->settings['showsummary'] == 1){
            if(empty($formdata['data']) or empty($formdata['checksum'])){
                return 2;
            }else{
                if (hash("SHA256",$formdata['data']) == $formdata['checksum']){
                    return 1;
                }else{
                    return 0;
                }
            }
        }else{
            return 1;
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
        if ($this->settings['allowedgid'] && is_array($this->settings['allowedgid'])) {
            $this->settings['allowedgid'] = implode(",", $this->settings['allowedgid']);
        }

        if ($this->settings['pmgroups'] && is_array($this->settings['pmgroups'])) {
            $this->settings['pmgroups'] = implode(",", $this->settings['pmgroups']);
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

        if (!is_array($this->settings)) {
            $this->settings = json_decode($this->settings, true);
        }

        if (!is_array($this->settings)) {
            $this->settings = array();
        }

        $this->settings = array_merge($this->default_settings(), $this->settings);

        if ($this->settings['allowedgid'] && is_array($this->settings['allowedgid'])) {
            $this->settings['allowedgid'] = implode(",", $this->settings['allowedgid']);
        }

        if ($this->settings['pmgroups'] && is_array($this->settings['pmgroups'])) {
            $this->settings['pmgroups'] = implode(",", $this->settings['pmgroups']);
        }

        $this->escape_data();
        if (empty($this->subjecttemplate)) {
            $this->subjecttemplate = "";
        }

        if (empty($this->messagetemplate)) {
            $this->messagetemplate = "";
        }

        $template_data = array("subjecttemplate" => $this->subjecttemplate,
                "messagetemplate" => $this->messagetemplate);
        $result = $db->update_query("fc_forms", $template_data, "formid = " . $this->
            formid);
        return $result;
    }

    public function update_form()
    {
        global $db;
        if ($this->settings['allowedgid']) {
            $this->settings['allowedgid'] = implode(",", $this->settings['allowedgid']);
        }

        if ($this->settings['pmgroups']) {
            $this->settings['pmgroups'] = implode(",", $this->settings['pmgroups']);
        }

        $this->escape_data();
        $result = $db->update_query("fc_forms", $this->get_data(), "formid = " . $this->
            formid);
        return $result;
    }

    public function delete_form()
    {
        global $db;
        if ($db->delete_query("fc_fields", "formid = " . $this->formid)) {
            if ($db->delete_query("fc_forms", "formid = " . $this->formid)) {
                $db->delete_query("fc_formusage", "formid = " . $this->formid);
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
        $this->formid = isset($data['formid']) ? intval($data['formid']) : 0;
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->active = isset($data['active']) ? intval($data['active']) : 0;
        $this->tid = isset($data['tid']) ? intval($data['tid']) : 0;
        $this->fid = isset($data['fid']) ? intval($data['fid']) : 0;
        $this->overridebutton = isset($data['overridebutton']) ? intval($data['overridebutton']) : 0;
        $this->class = isset($data['class']) ? $data['class'] : '';
        $this->subjecttemplate = isset($data['subjecttemplate']) ? $data['subjecttemplate'] : '';
        $this->messagetemplate = isset($data['messagetemplate']) ? $data['messagetemplate'] : '';

        $raw_settings = '';
        if (isset($data['settings'])) {
            $raw_settings = $data['settings'];
        }

        if (!is_array($raw_settings)) {
            $this->settings = json_decode($raw_settings, true);
        } else {
            $this->settings = $raw_settings;
        }

        if (!is_array($this->settings)) {
            $this->settings = array();
        }

        $this->settings = array_merge($this->default_settings(), $this->settings);

        if (!is_array($this->settings['allowedgid'])) {
            if (strlen(trim((string)$this->settings['allowedgid'])) == 0) {
                $this->settings['allowedgid'] = array();
            } else {
                $this->settings['allowedgid'] = explode(',', $this->settings['allowedgid']);
            }
        }

        if (!is_array($this->settings['pmgroups'])) {
            if (strlen(trim((string)$this->settings['pmgroups'])) == 0) {
                $this->settings['pmgroups'] = array();
            } else {
                $this->settings['pmgroups'] = explode(',', $this->settings['pmgroups']);
            }
        }
    }

    private function default_settings()
    {
        return array(
            'allowedgidtype' => -1,
            'allowedgid' => array(),
            'customdenied' => '',
            'limitusage' => 0,
            'pmusers' => '',
            'pmgroups' => array(),
            'prefix' => 0,
            'uid' => '',
            'customsuccess' => '',
            'signature' => 0,
            'posticon' => 0,
            'showsummary' => 0,
            'summaryparsed' => 0,
            'customsummary' => '',
            'width' => '',
            'labelwidth' => '',
            'mail' => ''
        );
    }

    public function get_data()
    {
        if ($this->formid) {
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

        $order_column = $this->get_order_column_sql();
        $query = $db->simple_select("fc_fields", "*", "formid = " . intval($this->
            formid), array("order_by" => $order_column));

        $this->fields = array();

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

        $order_column = $this->get_order_column_sql();
        return $db->write_query("UPDATE " . TABLE_PREFIX . "fc_fields SET " . $order_column . " = " . intval($order) .
            " WHERE fieldid = " . intval($fieldid) . " and formid = " . intval($this->formid));
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
            if (isset($mybb->input['field_' . $field->fieldid])) {
                $value = $mybb->input['field_' . $field->fieldid];
            } else {
                $value = '';
            }

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
        global $templates, $mybb, $ref;
        if (empty($this->subjecttemplate)) {
            $output = "Form submission: " . $this->name;
        } else {
            $this->subjecttemplate = str_replace('"', '\"', $this->subjecttemplate);
            $username = $mybb->user['username'];
            $uid = $mybb->user['uid'];
            $formname = $this->name;
            $fieldname = $this->get_field_names();
            $fieldvalue = $this->get_field_values();
            eval("\$output = \"" . $this->subjecttemplate . "\";");
        }

        return substr($output, 0, 120);
    }

    public function parse_output()
    {
        global $db, $mybb, $ref;
        $output = "";
        if (empty($this->messagetemplate)) {
            foreach ($this->fields as $field) {
                if (in_array($field->type, array(
                    1,
                    2,
                    5,
                    7,
                    15))) {
                    $output .= "[b]" . $field->name . "[/b]: " . $mybb->input["field_" . $field->
                        fieldid] . "\n[hr]";
                } elseif (in_array($field->type, array(
                    4,
                    6,
                    3))) {
                    $field_input = array();
                    if (isset($mybb->input["field_" . $field->fieldid]) && is_array($mybb->input["field_" . $field->fieldid])) {
                        $field_input = $mybb->input["field_" . $field->fieldid];
                    }

                    $output .= "[b]" . $field->name . "[/b]: " . implode(",", $field_input) . "\n[hr]";
                }
            }
        } else {
            $user = $mybb->user;
            unset($user['password']);
            unset($user['salt']);
            unset($user['loginkey']);

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

        $query = $db->simple_select("fc_formusage", "*", "formid = '" . $this->formid .
            "'", array(
            "order_by" => "ref",
            "order_dir" => "DESC",
            "LIMIT" => 1));
        if ($db->num_rows($query) != 0) {
            $lastrow = $db->fetch_array($query);
        } else {
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
            "ref" => $this->get_next_ref(),
            // Required for strict SQL modes where timestamp columns have no implicit default.
            "datetime" => date('Y-m-d H:i:s', TIME_NOW));

        $db->insert_query("fc_formusage", $data);
    }

    public function check_usage_limit_reached()
    {
        global $db, $mybb;

        if ($this->settings['limitusage'] == 0) {
            return false;
        }

        $query = $db->simple_select("fc_formusage", "*", "formid = '" . $this->formid .
            "' AND uid = '" . $mybb->user['uid'] . "'");
        $timesused = $db->num_rows($query);

        if ($timesused >= $this->settings['limitusage']) {
            return true;
        } else {
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
    public $error;

    private function get_order_column_sql()
    {
        global $db;

        $db_type = strtolower((string)$db->type);
        if ($db_type == 'pgsql' || $db_type == 'sqlite') {
            return '"order"';
        }

        return '`order`';
    }

    private function get_identifier_sql($identifier)
    {
        global $db;

        $db_type = strtolower((string)$db->type);
        if ($db_type == 'pgsql' || $db_type == 'sqlite') {
            return '"' . str_replace('"', '""', $identifier) . '"';
        }

        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function build_field_sql_data()
    {
        $data = $this->get_data();

        $numeric_fields = array('fieldid', 'formid', 'type', 'required', 'order');
        $result = array();

        foreach ($data as $column => $value) {
            if (in_array($column, $numeric_fields)) {
                $result[$column] = intval($value);
            } else {
                $result[$column] = "'" . $value . "'";
            }
        }

        return $result;
    }

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
        $this->fieldid = isset($data['fieldid']) ? intval($data['fieldid']) : 0;
        $this->formid = isset($data['formid']) ? intval($data['formid']) : 0;
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->description = isset($data['description']) ? $data['description'] : '';
        $this->type = isset($data['type']) ? intval($data['type']) : 0;
        $this->default = isset($data['default']) ? $data['default'] : '';
        $this->required = isset($data['required']) ? intval($data['required']) : 0;
        $this->order = isset($data['order']) ? intval($data['order']) : 0;
        $this->class = isset($data['class']) ? $data['class'] : '';

        $raw_settings = '';
        if (isset($data['settings'])) {
            $raw_settings = $data['settings'];
        }

        if (!is_array($raw_settings)) {
            $this->settings = json_decode($raw_settings, true);
        } else {
            $this->settings = $raw_settings;
        }

        if (!is_array($this->settings)) {
            $this->settings = array();
        }

        $this->settings = array_merge($this->default_settings(), $this->settings);

        if (!is_array($this->settings['selector'])) {
            if (strlen(trim((string)$this->settings['selector'])) == 0) {
                $this->settings['selector'] = array();
            } else {
                $this->settings['selector'] = explode(',', $this->settings['selector']);
            }
        }
    }

    private function default_settings()
    {
        return array(
            'placeholder' => '',
            'maxlength' => 0,
            'options' => '',
            'selector' => array(),
            'format' => '',
            'regex' => '',
            'regexerror' => '',
            'size' => 0,
            'cols' => 0,
            'rows' => 0,
            'resize' => 0,
            'html' => ''
        );
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
        $data['default'] = $this->default;
        $data['required'] = $this->required;
        $data['order'] = $this->order;
        $data['class'] = $this->class;
        $data['settings'] = $this->settings;
        return $data;
    }

    public function show_admin_field($name)
    {
        if ($this->type) {
            if ($this->type == 1) {
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
            } elseif ($this->type == 2) {
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
                    "resize",
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
            } elseif ($this->type == 13) {
                $show = array(
                    "name",
                    "description",
                    "required",
                    "class");
            } elseif ($this->type == 14) {
                $show = array(
                    "name",
                    "description",
                    "required",
                    "class");
            } elseif ($this->type == 15) {
                $show = array(
                    "name",
                    "description",
                    "required",
                    "rows");
            } elseif ($this->type == 16) {
                $show = array(
                    "name",
                    "description",
                    "selector",
                    "required",
                    "class");
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
        $order_column = $this->get_order_column_sql();
        $query = $db->simple_select("fc_fields", $order_column . " AS displayorder", "formid = " . $this->formid,
            array("order_by" => $order_column, "order_dir" => "DESC"));
        if ($db->num_rows($query) == 0) {
            $this->order = 0;
        } else {
            $lastfield = $db->fetch_array($query);
            $this->order = $lastfield['displayorder'] + 1;
        }

        $sql_data = $this->build_field_sql_data();
        $columns = array();
        $values = array();

        foreach ($sql_data as $column => $value) {
            if ($column == 'fieldid') {
                continue;
            }

            $columns[] = $this->get_identifier_sql($column);
            $values[] = $value;
        }

        $db->write_query("INSERT INTO " . TABLE_PREFIX . "fc_fields (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ")");
        $result = intval($db->insert_id());

        if ($result <= 0) {
            $query = $db->simple_select("fc_fields", "MAX(" . $this->get_identifier_sql('fieldid') . ") AS lastid", "formid = " . intval($this->formid));
            $result = intval($db->fetch_field($query, 'lastid'));
        }

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

        $sql_data = $this->build_field_sql_data();
        unset($sql_data['fieldid']);

        $set_clauses = array();
        foreach ($sql_data as $column => $value) {
            $set_clauses[] = $this->get_identifier_sql($column) . " = " . $value;
        }

        return $db->write_query("UPDATE " . TABLE_PREFIX . "fc_fields SET " . implode(",", $set_clauses) .
            " WHERE " . $this->get_identifier_sql('fieldid') . " = " . intval($this->fieldid));
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
        $size = "";
        $placeholder = "";
        $maxlength = "";

        if ($this->settings['size']) {
            $size = "size='" . $this->settings['size'] . "'";
        }

        if ($this->settings['placeholder']) {
            $placeholder = "placeholder ='" . $this->settings['placeholder'] . "'";
        }

        if ($this->settings['maxlength'] != 0) {
            $maxlength = "maxlength ='" . $this->settings['maxlength'] . "'";
        }

        return "<input type='text' value='" . $this->default . "' name='field_" . $this->
            fieldid . "' class='textbox " . $this->class . "' " . $size . " " . $placeholder .
            " " . $maxlength . " />";
    }

    public function output_textarea()
    {
        $class = "";
        $rows = "";
        $cols = "";
        $placeholder = "";
        $maxlength = "";

        if ($this->class) {
            $class = "class='" . $this->class . "'";
        }

        if ($this->settings['rows']) {
            $rows = "rows='" . $this->settings['rows'] . "'";
        }

        if ($this->settings['cols']) {
            $cols = "cols='" . $this->settings['cols'] . "'";
        }

        if ($this->settings['placeholder']) {
            $placeholder = "placeholder ='" . $this->settings['placeholder'] . "'";
        }

        if ($this->settings['maxlength'] != 0) {
            $maxlength = "maxlength ='" . $this->settings['maxlength'] . "'";
        }


        if ($this->settings['resize'] == 1) {
            $resize = "style='resize: both;'";
        } else {
            $resize = "style='resize: none;'";
        }

        return "<textarea name='field_" . $this->fieldid . "' " . $class . " " . $rows .
            " " . $cols . " " . $placeholder . " " . $maxlength . " " . $resize . ">" . $this->
            default . "</textarea>";
    }

    public function output_select($multi = false)
    {
        global $lang;

        $options = explode("\n", $this->settings['options']);
        $class = "";
        $size = "";
        $is_multi = (bool)$multi;

        if ($this->class) {
            $class = "class='" . $this->class . "'";
        }

        if ($is_multi) {
            $multi = "multiple='multiple'";
        } else {
            $multi = "";
        }

        if ($this->settings['size'] != 0) {
            $size = "size='" . $this->settings['size'] . "'";
        }

        $output = "<select name='field_" . $this->fieldid . "[]' " . $class . " " . $multi .
            " " . $size . ">";
        if (!$is_multi) {
            $output .= "<option value=''>- " . $lang->fc_select_option . " -</option>";
        }

        foreach ($options as $option) {
            $selected = "";
            if (is_array($this->default)) {
                if (in_array(trim($option), $this->default)) {
                    $selected = "selected='selected'";
                }
            }

            $output .= "<option value='" . trim($option) . "' " . $selected . ">" . $option .
                "</option>";
        }

        $output .= "</select>";
        return $output;
    }

    public function output_prefix()
    {
        global $lang;

        $class = "";
        $size = "";

        if ($this->class) {
            $class = "class='" . $this->class . "'";
        }

        if ($this->settings['size'] != 0) {
            $size = "size='" . $this->settings['size'] . "'";
        }

        $output = "<select name='field_" . $this->fieldid . "' " . $class .
            " " . $size . ">";
        $output .= "<option value=''>- " . $lang->fc_select_option . " -</option>";

        $prefixes = build_prefixes();

        foreach ($this->settings['selector'] as $option) {
            $selected = "";
            if (is_array($this->default)) {
                if (in_array(trim($option), $this->default)) {
                    $selected = "selected='selected'";
                }
            }

            $output .= "<option value='" . trim($option) . "' " . $selected . ">" . $prefixes[$option]['prefix'] .
                "</option>";
        }

        $output .= "</select>";
        return $output;
    }

    public function output_dateselect()
    {
        if ($this->class) {
            $class = $this->class;
        }

        $output = "<input type='text' id='field_" . $this->fieldid . "' value='" . $this->
            default . "' name='field_" . $this->fieldid . "' class='textbox dateselect " . $this->
            class . "' />";
        if (empty($this->settings['format'])) {
            $output .= "<script>
        	  $( function() {
        		$( \"#field_" . $this->fieldid . "\" ).datepicker();
        	  } );
          </script>";
        } else {
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
        if ($this->description) {
            return "<strong>" . $this->name . "</strong><br /><small>" . $this->description .
                "</small>";
        } else {
            return "<strong>" . $this->name . "</strong>";
        }
    }

    public function output_radio()
    {
        $options = explode("\n", $this->settings['options']);

        $output = "";
        foreach ($options as $option) {
            if ($this->default == trim($option)) {
                $checked = "checked='checked'";
            } else {
                $checked = "";
            }

            $output .= "<input type='radio' name='field_" . $this->fieldid . "' id='" . $this->
                name . "_" . $option . "' value='" . trim($option) . "' " . $checked .
                " /><label for='" . $this->name . "_" . $option . "'>" . $option .
                "</label><br />";
        }

        return $output;
    }

    public function output_checkbox()
    {
        $options = explode("\n", $this->settings['options']);

        $output = "";
        foreach ($options as $option) {
            $checked = "";
            if (is_array($this->default)) {
                if (in_array(trim($option), $this->default)) {
                    $checked = "checked='checked'";
                }
            }

            $output .= "<input type='checkbox' name='field_" . $this->fieldid . "[]' id='" .
                $this->name . "_" . $option . "' value='" . trim($option) . "' " . $checked .
                " /><label for='" . $this->name . "_" . $option . "'>" . $option .
                "</label><br />";
        }

        return $output;
    }

    public function output_submit()
    {
        $class = "";
        if ($this->class) {
            $class = "class='" . $this->class . "'";
        }

        return "<input type='submit' value='" . $this->name . "' " . $class . " />";
    }

    public function output_captcha()
    {
        global $mybb;

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

    public function output_attachment()
    {
        return "<input type='file' value='" . $this->default . "' name='field_" . $this->
            fieldid . "' class='fileupload " . $this->class . "' />";
    }

    public function output_attachments()
    {
        return "<input type='file' value='" . $this->default . "' name='field_" . $this->
            fieldid . "[]' class='fileupload " . $this->class . "' multiple='multiple' />";
    }

    public function output_editor()
    {
        if ($this->settings['rows']) {
            $rows = "rows='" . $this->settings['rows'] . "'";
        } else {
            $rows = "rows='20'";
        }

        if ($this->settings['resiz'] == 1) {
            $resize = "style='resize: both;'";
        } else {
            $resize = "style='resize: none;'";
        }

        $code = build_mycode_inserter("field_" . $this->fieldid, true);

        return "<textarea " . $rows . " " . $resize . " name='field_" . $this->fieldid .
            "' id='field_" . $this->fieldid . "' />" . $this->default . "</textarea>\n" . $code;
    }
}

?>
