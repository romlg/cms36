<?php

require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    var $multi_types = array('select', 'radio', 'checkbox');

    function ElemInit() {
        $this->elem_str = array(
            'name' => array('Название формы', 'Title'),
            'name_site' => array('Название формы (внутреннее)', 'Title (on site)'),
            'db_table' => array('Название таблицы в БД', 'DB table name'),
            'email' => array('Отправлять на адреса (через запятую)', 'Send to (comma-separated)'),
            'is_popup' => array('Всплывающая форма', 'Popup form',),
            'visible' => array('Отображать на странице', 'Visible',),
            'submit_title' => array('Надпись на кнопке отправки формы', 'Submit title',),
            'code' => array('Код для вставки', 'Code',),
            'lang' => array('Язык формы', 'Lang',),
            'get_code' => array('Получить код', 'Get code',),
            'get_html_code' => array('HTML-код', 'HTML-code',),
        );
        return parent::ElemInit();
    }

    function getWCfromDb($id) {
        $row = $this->GetRow('SELECT *, ' . $this->getFieldName('name', true) . ', ' . $this->getFieldName('email', true) . ' FROM forms WHERE id=' . $id);

        if ($row['id']) {
            $row['elems'] = sql_getRows('SELECT *, ' . $this->getFieldName('text', true) . ' FROM forms_elems WHERE pid=' . $row['id']);
            foreach ($row['elems'] as $k => $v) {
                $values = sql_getRows('SELECT ' . $this->getFieldName('text', true) . ' FROM forms_values WHERE pid=' . $v['id']);
                foreach ($values as $k2 => $v2) {
                    $values[$k2] = str_replace(array("\r\n", "\n", "\r"), " ", $v2);
                }
                $row['elems'][$k]['value'] = $values ? "'" . implode("','", $values) . "'" : "";
            }
        }
        if (empty($row['elems'])) {
            unset($row['id']);
            unset($row['elems']);
        }
        return $row;
    }

    function ElemForm() {
        $columns = sql_getRows("SHOW COLUMNS FROM `forms`", true);
        if (!isset($columns['submit_title'])) {
            sql_query("ALTER TABLE forms ADD submit_title VARCHAR( 100 ) NOT NULL COMMENT 'Надпись на кнопке сабмит';");
        }

        $row = $this->getObject();

        if ($row['object']['hash']) {
            $row['code_normal'] = htmlentities("[[FORM {$row['object']['hash']}]]");
            $row['code_html'] = htmlentities("<script src='?get_form={$row['object']['hash']}' type='text/javascript'></script>");
            $row['code_normal2'] = htmlentities("[[FORMPOPUP {$row['object']['hash']}]]");
            $row['code_html2'] = htmlentities("<script src='?get_form={$row['object']['hash']}&is_popup' type='text/javascript'></script>");
        }
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
            '0' => ' нет ',
            'email' => 'E-mail',
            'phone' => 'Телефон',
            'zip' => 'Индекс',
            'captcha' => 'Captcha',
        );
        $row['req'] = array(
            '0' => 'нет',
            '1' => 'да',
        );
        $row['show'] = array(
            '0' => 'нет',
            '1' => 'да',
        );

        // добавляет в шаблон дефолтные строковые константы
        $this->AddStrings($row);

        return $this->Parse($row, 'forms.tmpl');
    }

    ########################

    function ElemEdit($id, $row) {
        $fld = get('fld', array(), 'p');
        $row = $fld['genform'];

        $error = '';
        sql_query('BEGIN');

        if (!$id) {
            //добавляем форму
            $form_id = $this->updateForm($row);
            if (is_int($form_id)) {
            } else {
                $error = $form_id;
            }
        } else {
            // редактируем форму
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
     * Создание/обновление формы
     * @param $data
     * @param int $id
     * @return bool|int
     */
    function updateForm($data, $id = 0) {
        $form_data = array(
            'hash' => $data['hash'] ? $data['hash'] : sha1(sha1(time()) . $id),
            $this->getFieldName('name') => $data['name'],
            'name_site' => $data['name_site'],
            $this->getFieldName('email') => $data['email'],
            'db_table' => $data['db_table'],
            'is_popup' => 0,
            'lang' => $data['lang'],
            'visible' => isset($data['visible']) ? $data['visible'] : 0,
            $this->getFieldName('submit_title') => $data['submit_title'],
        );
        if ($id) {
            return sql_updateId('forms', $form_data, $id, 'id');
        } else {
            return sql_insert('forms', $form_data);
        }
    }

    /**
     * Сохранение полей формы
     * @param $form_id
     * @param $elements
     * @return bool|int
     */
    function updateFormElements($form_id, $elements) {
        sql_query("DELETE FROM forms_elems WHERE pid={$form_id}");

        $columns = sql_getRows("SHOW COLUMNS FROM `forms_elems`", true);
        if (!isset($columns['placeholder'])) {
            sql_query("ALTER TABLE forms_elems ADD placeholder VARCHAR( 255 ) NOT NULL ");
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

            $epid = sql_insert('forms_elems', $elem_data);
            if (!is_int($epid)) {
                return $epid;
            }

            if ($this->isMulti($v)) { // мульти-поле (radio, select, checkbox)
                // заполняем массив значений
                $res = $this->updateFormElementsValues($elements['textarea'][$k], $epid);
                if ($res !== true) {
                    return $res;
                }
            }
        }
        return true;
    }

    /**
     * Сохранение возможных значений для полей типа: radio, select, checkbox
     * @param $value
     * @param $epid
     * @return bool|int
     */
    function updateFormElementsValues($value, $epid) {
        $arr = array();
        // Заменяем последовательность ',любой символ' на ','
        $value = ereg_replace("', +'", "','", $value);
        // Теперь разбиваем
        $arr = explode("','", $value);
        $arr[0] = substr($arr[0], 1);
        $arr[count($arr) - 1] = substr($arr[count($arr) - 1], 0, -1);

        sql_query("DELETE FROM forms_values WHERE pid=" . $epid);
        if ($arr) {
            foreach ($arr as $value2 => $text2) {
                $_value = $value2 + 1;
                $text2 = str_replace('"', '&quot;', $text2);
                $_data = array(
                    'pid' => $epid,
                    'value' => $_value,
                    $this->getFieldName('text') => $text2
                );
                $_id = sql_insert('forms_values', $_data);
                if (!is_int($_id)) return $_id;
            }
        }
        return true;
    }

    function ElemRedactS($fld) {
        $fld['is_popup'] = 0;
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