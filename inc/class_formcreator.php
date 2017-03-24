<?php

class formcreator
{
    public $formid;
    public $name;
    public $allowedgid;
    public $allgroups;
    public $active;
    public $pmusers;
    public $pmgroups;
    public $mail;
    public $fid;

    public $fields;

    private $error;

    public $types = array(
        1 => "Textbox (single line)",
        2 => "Textarea (multi line)",
        3 => "Select (single)",
        4 => "Select (multiple)",
        5 => "Radio Buttons",
        6 => "Checkboxs",
        7 => "Captcha",
        8 => "Seperator",
        9 => "Header",
        10 => "HTML block");

    public function get_form($formid)
    {
        global $db;

        $query = $db->simple_select("fc_forms", "*", "formid = " . intval($formid));

        if ($db->num_rows($query) == 1)
        {
            $formdata = $db->fetch_array($query);

            if ($formdata['allowedgid'] != -1)
            {
                $formdata['allowedgid'] = explode(",", $formdata['allowedgid']);
            }

            $formdata['pmgroups'] = explode(",", $formdata['pmgroups']);

            $this->load_data($formdata);
            return $formdata;
        }
        else
        {
            return false;
        }
    }

    public function get_form_fields($formid)
    {

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

        if ($this->allowedgid != -1)
        {
            $this->allowedgid = implode(",", $this->allowedgid);
        }
        $this->pmgroups = implode(",", $this->pmgroups);

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

    public function update_form()
    {
        global $db;

        if ($this->allowedgid != -1)
        {
            $this->allowedgid = implode(",", $this->allowedgid);
        }
        $this->pmgroups = implode(",", $this->pmgroups);

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
        if ($this->formid)
        {
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
        $this->order = intval($this->order);
        $this->size = intval($this->size);
        $this->cols = intval($this->cols);
        $this->rows = intval($this->rows);
        $this->class = $db->escape_string($this->class);
        $this->html = $db->escape_string($this->html);
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
        $this->order = $data['order'];
        $this->size = $data['size'];
        $this->cols = $data['cols'];
        $this->rows = $data['rows'];
        $this->class = $data['class'];
        $this->html = $data['html'];
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
        $data['options'] = $this->options;
        $data['default'] = $this->default;
        $data['required'] = $this->required;
        $data['regex'] = $this->regex;
        $data['order'] = $this->order;
        $data['size'] = $this->size;
        $data['cols'] = $this->cols;
        $data['rows'] = $this->rows;
        $data['class'] = $this->class;
        $data['html'] = $this->html;

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
                    "class");
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
                $show = array("name", "description");
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
                $show = array("name", "html");
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
}

?>