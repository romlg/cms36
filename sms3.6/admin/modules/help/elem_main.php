<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    ######################
    var $elem_name = "elem_main";
    var $elem_table = "help";
    var $elem_type = "single";
    var $elem_str = array(
        'name' => array('Название', 'Name',),
        'text' => array('Текст помощи', 'Text'),
        'module' => array('Модуль', 'Module',),
    );
    //поля для выборки из базы элема
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'name' => array(
                'type' => 'text',
            ),
            'module' => array(
                'type' => 'text',
            ),
            'text' => array(
                'type' => 'fck',
            ),
        ),
        'id_field' => 'id',
    );
    var $elem_where = "";
    var $elem_req_fields = array('name', 'module');
    var $script = "";
}