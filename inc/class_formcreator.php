<?php

class formcreator
{
    public $formid;
    public $name;
    public $gid;
    public $active;
    public $pmusers;
    public $pmgroups;
    public $mail;
    
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

        $result = $db->update_query("fc_forms", $this->get_data(), "formid = " . $this->formid);

        return $result;
    }

    public function load_data($data)
    {
        global $db;

        $this->formid = intval($data['formid']);
        $this->name = $db->escape_string($data['name']);
        $this->gid = $db->escape_string($data['gid']);
        $this->active = intval($data['active']);
        $this->pmusers = $db->escape_string($data['pmusers']);
        $this->pmgroups = $db->escape_string($data['pmgroups']);
        $this->mail = $db->escape_string($data['mail']);
    }

    public function get_data()
    {
        if ($this->formid) {
            $data['formid'] = $this->formid;
        }

        $data['name'] = $this->name;
        $data['gid'] = $this->gid;
        $data['active'] = $this->active;
        $data['pmusers'] = $this->pmusers;
        $data['pmgroups'] = $this->pmgroups;
        $data['mail'] = $this->mail;

        return $data;
    }


}

class formcreator_field
{


}

?>