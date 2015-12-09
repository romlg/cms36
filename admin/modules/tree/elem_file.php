<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TFileElement extends TElems
{

    var $elem_name = "elem_file";
    var $elem_table = "elem_file";
    var $elem_type = "multi";
    var $elem_class = "file";
    var $elem_str = array(
        'name' => array('Заголовок', 'Title',),
        'fname' => array('Имя файла', 'Filename',),
    );
    var $order = " ORDER BY priority ";

    var $elem_fields = array(
        'id_field' => 'pid',
        'type' => 'multi',
        'folder' => 'content'
    );

    var $elem_where = "";
    var $elem_req_fields = array('name', 'fname',);
    var $script = "";
}
