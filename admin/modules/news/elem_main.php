<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems {

    var $elem_name = "elem_main";
    var $elem_table = "publications";
    var $elem_type = 'single';
    var $elem_str = array(
        'name' => array('���������', 'Title',),
        'date' => array('����','Date',),
        'notice' => array('�����','Announcement'),
        'text' => array('�����','Text',),
        'visible' => array('��������','Visible',),
        'image' => array('�����������','Preview',),
    );

    //���� ��� ������� �� ���� �����
    var $elem_fields = array(
        'columns' => array(
            'id' => array('type' => 'hidden',),
            'name' => array('type' => 'text',),
            'date' => array(
                'type' => 'input_calendar',
                'display' => array('func' => 'getCurrentDate',),
            ),
            'notice' => array('type' => 'fck',),
            'text' => array('type' => 'fck',),
            'visible' => array('type' => 'checkbox',),
            'image' => array('type' => 'input_image',),
        ),
        //'folder' => 'news',
        'id_field' => 'id',
    );
    var $elem_where = "";
    var $elem_req_fields = array('name');
    var $script = "";


    function getCurrentDate($v) {
        return isset($v['value']) ? $v['value'] : date('Y-m-d H:i:s');
    }

}

?>