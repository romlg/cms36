<?php

require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TPublicationsBaseElement extends TElems
{
    var $editable = array();
    var $elem_name = "elem_publications";
    var $elem_table = "infoblocks";
    var $elem_type = "single";

    var $elem_str = array(
        'publ_pids' => array('�� ��������', 'Of the sections'),
        'publ_count' => array('����������', 'Count'),
        'publ_date_from' => array('���� �', 'Date from'),
        'publ_date_to' => array('���� ��', 'Date to'),
        'publ_announce' => array('������������ ����������', 'Announce')
    );

    var $elem_fields = array(
        'columns' => array(
            'publ_announce' => array(
                'type' => 'checkbox',
            ),
            'publ_pids' => array(
                'type' => 'input_treecheck',
            ),
            'publ_count' => array(
                'type' => 'text',
                'size' => 5,
            ),
            'publ_date_from' => array(
                'type' => 'input_calendar',
            ),
            'publ_date_to' => array(
                'type' => 'input_calendar',
            ),
        ),
	'id_field' => 'id',				
    );

    var $elem_req_fields = array();

    var $script = "";
}