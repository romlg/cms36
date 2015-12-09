<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    ######################
    var $elem_name = "elem_main"; //название elema
    var $elem_table = "admins"; //название таблицы elema (DEFAULT $elem_name)
    var $elem_type = "single";
    var $elem_str = array( //строковые константы
        'login' => array('Логин', 'Login',),
        'fullname' => array('Имя', 'Name',),
        'email' => array('E-mail', 'E-mail',),
        'phone' => array('Телефон', 'Phone',),
        'cellphone' => array('Сотовый Телефон', 'Cell Phone',),
        'description' => array('Описание', 'Description',),
        'pwd' => array('Установить пароль', 'Set Password',),
        'pwd2' => array('Пароль еще раз', 'Password Again',),
        'group_id' => array('Группа', 'Group',),
        'department_id' => array('Разрешить только этот раздел в Дереве разделов', 'Department ID',),
    );

    //поля для выборки из базы элема
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'login' => array(
                'type' => 'text',
                'size' => 30,
                'maxlength' => 32,
            ),
            'fullname' => array(
                'type' => 'text',
                'size' => 30,
                'maxlength' => 50,
            ),
            'pwd' => array(
                'type' => 'password',
                'size' => 30,
                'maxlength' => 50,
            ),
            'pwd2' => array(
                'type' => 'password',
                'size' => 30,
                'maxlength' => 50,
                'db_field' => false,
            ),
            'email' => array(
                'type' => 'text',
                'size' => 30,
                'maxlength' => 50,
            ),
            'phone' => array(
                'type' => 'text',
                'size' => 30,
                'maxlength' => 50,
            ),
            'description' => array(
                'type' => 'textarea',
                'cols' => 50,
                'rows' => 5,
            ),
            'group_id' => array(
                'type' => 'select',
                'func' => 'getGroups',
            ),
        ),
        'id_field' => 'id',
    );
    var $elem_where = "";
    var $elem_req_fields = array('login');
    var $script = "";

    //var $sql = true;

    function ElemInit() {
        $columns = sql_getRows("SHOW COLUMNS FROM `admins`", true);
        if (isset($columns['department_id'])) {
            $this->elem_fields['columns']['department_id'] = array(
                'type' => 'input_treeid'
            );
        }
        parent::ElemInit();
    }

    ########################
    function getGroups() {
        return array('' => '') + sql_getRows("SELECT id, name FROM admin_groups ORDER BY priority, name", true);
    }

    ########################
    function ElemRedactBefore($fld) {
        if ($fld['pwd'] && $fld['pwd2']) {
            if ($fld['pwd'] != $fld['pwd2']) {
                return array('_error_text' => 'Введенные пароли не совпадают!');
            } else {
                $fld['pwd'] = md5($fld['pwd']);
            }
        } else {
            unset($fld['pwd'], $fld['pwd2']);
        }
        if (!$fld['group_id']) $fld['group_id'] = 'NULL';
        return $fld;
    }

}

?>