<?php

require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TFormElement extends TElems
{

    var $multi_types = array('select', 'radio', 'checkbox');

    function ElemInit() {
        $this->elem_str = array(
            'name' => array('�������� �����', 'Title'),
            'db_table' => array('�������� ������� � ��', 'DB table name'),
            'email' => array('���������� �� ������ (����� �������)', 'Send to (comma-separated)'),
            'visible' => array('���������� �� ��������', 'Visible',),
            'submit_title' => array('������� �� ������ �������� �����', 'Submit title',),
        );
        return parent::ElemInit();
    }

    function getWCfromDb($id) {
        $row = $this->GetRow('SELECT *, ' . $this->getFieldName('name', true) . ', ' . $this->getFieldName('email', true) . ' FROM elem_form WHERE pid=' . $id);
        if ($row['form_id']) {
            $row['elems'] = sql_getRows('SELECT *, ' . $this->getFieldName('text', true) . ' FROM elem_form_elems WHERE pid=' . $row['form_id']);
            foreach ($row['elems'] as $k => $v) {
                $values = sql_getRows('SELECT ' . $this->getFieldName('text', true) . ' FROM elem_form_values WHERE pid=' . $v['id']);
                foreach ($values as $k2 => $v2) {
                    $values[$k2] = str_replace(array("\r\n", "\n", "\r"), " ", $v2);
                }
                $row['elems'][$k]['value'] = $values ? "'" . implode("','", $values) . "'" : "";
            }
        }
        if (empty($row['elems'])) {
            unset($row['form_id']);
            unset($row['elems']);
        }
        return $row;
    }

    function ElemForm() {
        $id = (int)get('id', 0);
        if (!$id) {
            return "����� ��������� ����� ��������� ��������� ������ � �������.";
        }

        $columns = sql_getRows("SHOW COLUMNS FROM `elem_form`", true);
        if (!isset($columns['submit_title'])) {
            sql_query("ALTER TABLE elem_form ADD submit_title VARCHAR( 100 ) NOT NULL COMMENT '������� �� ������ ������';");
        }

        $row = $this->getObject();

        $row['types'] = array(
            'input' => 'Input',
            'textarea' => 'Textarea',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio',
            'select' => 'Select',
            'file' => 'File',
            'captcha' => 'Captcha',
            'headline' => 'Headline',
        );
        $row['check'] = array(
            '0' => ' ��� ',
            'email' => 'E-mail',
            'phone' => '�������',
            'zip' => '������',
            'captcha' => 'Captcha',
        );
        $row['req'] = array(
            '0' => '���',
            '1' => '��',
        );
        $row['show'] = array(
            '0' => '���',
            '1' => '��',
        );

        // ��������� � ������ ��������� ��������� ���������
        $this->AddStrings($row);

        return $this->Parse($row, 'elem_form.tmpl');
    }

    ########################

    function ElemEdit($id, $row) {
        $pid = $id; // ID ��������
        if (!$pid) return 1;

        $fld = get('fld', array(), 'p');
        $row = $fld['genform'];
        $row['pid'] = $pid;

        $id = sql_getValue("SELECT form_id FROM elem_form WHERE pid=" . $pid . " LIMIT 1"); // ID �����

        $error = '';
        sql_query('BEGIN');

        if (!$id) {
            //��������� �����
            $form_id = $this->updateForm($row);
            if (is_int($form_id)) {
            } else {
                $error = $form_id;
            }
        } else {
            // ����������� �����
            $form_id = $this->updateForm($row, $id);
            if ($form_id == $id) {
            } else {
                $error = $form_id;
            }
        }

        if (!$error) {
            $_id = $this->updateFormElements($form_id, $row);
            if ($_id !== true) $error = $_id;
        }

        if ($error) {
            sql_query('ROLLBACK');
            return $error;
        } else {
            sql_query('COMMIT');
            return 1;
        }
    }

    /**
     * ��������/���������� �����
     * @param $data
     * @param int $id
     * @return bool|int
     */
    function updateForm($data, $id = 0) {
        $form_data = array(
            'pid' => $data['pid'],
            $this->getFieldName('name') => $data['name'],
            $this->getFieldName('email') => $data['email'],
            'db_table' => $data['db_table'],
            'visible' => isset($data['visible']) ? $data['visible'] : 0,
            $this->getFieldName('submit_title') => $data['submit_title'],
        );
        if ($id) {
            return sql_updateId('elem_form', $form_data, $id, 'form_id');
        } else {
            return sql_insert('elem_form', $form_data);
        }
    }

    /**
     * ���������� ����� �����
     * @param $form_id
     * @param $elements
     * @return bool|int
     */
    function updateFormElements($form_id, $elements) {
        sql_query("DELETE FROM elem_form_elems WHERE pid={$form_id}");

        $columns = sql_getRows("SHOW COLUMNS FROM `elem_form_elems`", true);
        if (!isset($columns['placeholder'])) {
            sql_query("ALTER TABLE elem_form_elems ADD placeholder VARCHAR( 255 ) NOT NULL ");
        }

        foreach ($elements['select'] as $k => $v) {
            if ($v == 'captcha') $elements['check'][$k] = 'captcha';
            $elem_data = array(
                'pid' => $form_id,
                '`key`' => $k,
                'type' => $v,
                $this->getFieldName('text') => $elements['text'][$k],
                '`check`' => $elements['check'][$k],
                'req' => $elements['req'][$k],
                '`show`' => $elements['show'][$k],
                'db_field' => $elements['db_field'][$k],
                'placeholder' => $elements['placeholder'][$k],
            );

            $epid = sql_insert('elem_form_elems', $elem_data);
            if (!is_int($epid)) {
                return $epid;
            }

            if ($this->isMulti($v)) { // ������-���� (radio, select, checkbox)
                // ��������� ������ ��������
                $res = $this->updateFormElementsValues($elements['textarea'][$k], $epid);
                if ($res !== true) {
                    return $res;
                }
            }
        }
        return true;
    }

    /**
     * ���������� ��������� �������� ��� ����� ����: radio, select, checkbox
     * @param $value
     * @param $epid
     * @return bool|int
     */
    function updateFormElementsValues($value, $epid) {
        $arr = array();
        // �������� ������������������ ',����� ������' �� ','
        $value = ereg_replace("', +'", "','", $value);
        // ������ ���������
        $arr = explode("','", $value);
        $arr[0] = substr($arr[0], 1);
        $arr[count($arr) - 1] = substr($arr[count($arr) - 1], 0, -1);

        sql_query("DELETE FROM elem_form_values WHERE pid=" . $epid);
        if ($arr) {
            foreach ($arr as $value2 => $text2) {
                $_value = $value2 + 1;
                $text2 = str_replace('"', '&quot;', $text2);
                $_data = array(
                    'pid' => $epid,
                    'value' => $_value,
                    $this->getFieldName('text') => $text2
                );
                $_id = sql_insert('elem_form_values', $_data);
                if (!is_int($_id)) return $_id;
            }
        }
        return true;
    }

    function ElemRedactS($fld) {
        if (!isset($fld['visible'])) $fld['visible'] = 0;
        $fld['name'] = str_replace('"', '&quot;', $fld['name']);
        return $fld;
    }

    function isMulti($v) {
        return in_array($v, $this->multi_types);
    }

    function getFieldName($field, $select = false) {
        if (!defined('LANG_SELECT') || LANG_SELECT === false) return $field;
        if (!$select) return '`' . $field . '_' . lang() . '`';
        return 'IF(`' . $field . '_' . lang() . '`<>"", `' . $field . '_' . lang() . '`, `' . $field . '_' . LANG_DEFAULT . '`) AS ' . $field;
    }
}

?>