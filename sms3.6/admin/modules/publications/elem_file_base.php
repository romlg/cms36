<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TFileBaseElement extends TElems
{

    var $elem_name = "elem_file";
    var $elem_table = "publications_file";
    var $elem_type = "multi";
    var $elem_class = "file";
    var $elem_str = array(
        'name' => array('���������', 'Title',),
        'fname' => array('��� �����', 'Filename',),
    );
    var $order = " ORDER BY priority ";

    //���� ��� ������� �� ���� �����
    var $elem_fields = array(
        'id_field' => 'pid',
        'type' => 'multi',
        'folder' => 'publications',
    );

    var $elem_where = "";
    var $elem_req_fields = array('name', 'fname',);
    var $script = "";
}