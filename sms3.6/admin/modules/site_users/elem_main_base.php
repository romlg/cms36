<?php

/**
 *
 * Модуль "Пользователи сайта" (главный элемент формы)
 *
 * @package    admin/modules
 */
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainBaseElement extends TElems
{

    var $editable = array();
    var $elem_name = "elem_main";
    var $elem_table = "auth_users";
    var $elem_type = "single";
    var $elem_str = array(
        'login'	=> array('Логин', 'Login',),
        'name'	=> array('Имя', 'Name',),
        'post'	=> array('Должность', 'Post',),
        'email'	=> array('Email', 'Email',),
        'image'	=> array('Изображение', 'Photo',),
        'password'	=> array('Установить пароль', 'Set Password',),
        'password2'	=> array('Пароль еще раз', 'Password Again',),
        'visible' => array('Разрешен вход на сайт', 'Authorized'),
        'auth' => array('Подтвердил e-mail', 'Confirm e-mail'),
    );
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'name' => array(
                'type' => 'text',
            ),
            'email' => array(
                'type' => 'text',
            ),
            'login' => array(
                'type' => 'text',
            ),
            'post' => array(
                'type' => 'text',
            ),
            'image' => array(
                'type'    => 'input_image',
            ),
            'password' => array(
                'type' => 'password',
                'db_field'	=> false,
            ),
            'password2' => array(
                'type' => 'password',
                'db_field'	=> false,
            ),
            'auth' => array(
                'type' => 'checkbox',
            ),
            'visible' => array(
                'type' => 'checkbox',
            ),
        ),
        'id_field' => 'id',
        'folder' => 'users'
    );
    var $elem_req_fields = array('name');
    var $script = '';

    ########################

    function ElemRedactBefore($fld) {
        if (!empty($fld['password']) && $fld['password'] == $fld['password2']) {
            $fld['password'] = md5($fld['password']);
            $this->elem_fields['columns']['password']['db_field'] = true;
        }
        if (!$fld['email']) $fld['email'] = 'NULL';
        return parent::ElemRedactBefore($fld);
    }
}

?>