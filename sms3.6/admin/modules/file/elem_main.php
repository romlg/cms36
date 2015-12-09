<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    var $elem_name = "elem_file";
    var $elem_table = "elem_file";
    var $elem_type = "single";
    var $elem_str = array(
        'visible' => array('Показывать', 'Visible',),
        'name' => array('Заголовок', 'Title',),
        'fname' => array('Имя файла', 'Filename',),
    );
    //поля для выборки из базы элема
    var $elem_fields = array(
        'columns' => array(
            'name' => array(
                'type' => 'textarea',
                'rows' => '4',
                'cols' => '30',
            ),
            'fname' => array(
                'type' => 'input_file',
                'size' => '40',
            ),
            'visible' => array(
                'type' => 'checkbox',
                'value' => '1',
            ),
            'priority' => array(
                'type' => 'hidden',
            ),
        ),
        'id_field' => 'id',
        'folder' => 'files'
    );
    var $elem_where = "";
    var $script = "";
    var $elem_req_fields = array('name', 'fname',);
}