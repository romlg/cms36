<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TLanguageElement extends TElems
{

    var $elem_name = "elem_language";
    var $elem_table = "sites_langs";
    var $elem_type = "multi";
    var $elem_class = "sites_langs";
    var $elem_str = array(
        'name' => array('��������', 'Title',),
        'descr' => array('��������', 'Description',),
        'locale' => array('������', 'Locale',),
        'charset' => array('���������', 'Charset',),
        'root_id' => array('ID', 'ID',),
    );
    var $order = " ORDER BY priority ";

    //���� ��� ������� �� ���� �����
    var $elem_fields = array(
        'id_field' => 'pid',
        'type' => 'multi',
    );

    var $elem_where = "";
    var $elem_req_fields = array();
    var $script = "";
}
