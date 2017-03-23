<?php

class formcreator
{
    public $formid;
    public $name;
    public $allowedgid;
    public $active;
    public $pmusers;
    public $pmgroups;
    public $mail;
    public $fid;

    public $fields;

    private $error;

    public static $types = array(
        0 => "Textbox (single line)",
        1 => "Textarea (multi line)",
        2 => "Select (single)",
        3 => "Select (multiple)",
        4 => "Radio Buttons",
        5 => "Checkboxs",
        5 => "Captcha",
        6 => "Seperator",
        7 => "Header",
        8 => "HTML block");

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

    public function get_form_fields($formid)
    {

    }

    public function insert_form()
    {
        global $db;

        $this->allowedgid = implode(",", $this->allowedgid);
        $this->pmgroups = implode(",", $this->pmgroups);

        $this->escape_data();

        $result = $db->insert_query("fc_forms", $this->get_data());
        if ($result) {
            $this->formid = $result;

            return $result;
        } else {
            return false;
        }
    }

    public function update_form()
    {
        global $db;

        $this->allowedgid = implode(",", $this->allowedgid);
        $this->pmgroups = implode(",", $this->pmgroups);

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
        $this->allowedgid = $db->escape_string($this->allowedgid);
        $this->active = intval($this->active);
        $this->pmusers = $db->escape_string($this->pmusers);
        $this->pmgroups = $db->escape_string($this->pmgroups);
        $this->fid = intval($this->fid);
        $this->mail = $db->escape_string($this->mail);
    }

    public function load_data($data)
    {
        $this->formid = $data['formid'];
        $this->name = $data['name'];
        $this->allowedgid = $data['allowedgid'];
        $this->active = $data['active'];
        $this->pmusers = $data['pmusers'];
        $this->pmgroups = $data['pmgroups'];
        $this->fid = $data['fid'];
        $this->mail = $data['mail'];
    }

    public function get_data()
    {
        if ($this->formid) {
            $data['formid'] = $this->formid;
        }

        $data['name'] = $this->name;
        $data['allowedgid'] = $this->allowedgid;
        $data['active'] = $this->active;
        $data['pmusers'] = $this->pmusers;
        $data['pmgroups'] = $this->pmgroups;
        $data['fid'] = $this->fid;
        $data['mail'] = $this->mail;

        return $data;
    }

    public function get_fields()
    {
        global $db;

        $query = $db->simple_select("fc_fields", "fieldid", "formid = " . intval($this->formid), array("order_by" => "`order`"));
        while ($field = $db->fetch_array($query)) {
            $this->fields[] = $field['fieldid'];
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
    public $order;
    public $size;
    public $cols;
    public $rows;
    public $class;


}

?>