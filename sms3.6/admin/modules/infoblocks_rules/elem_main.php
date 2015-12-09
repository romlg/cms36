<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    public $editable = array();
    public $elem_name = "elem_main";
    public $elem_table = "infoblocks_rules";
    public $elem_type = "single";
    public $elem_str = array(
        'url' => array('URL (��� http://)', 'URL (without http://)'),
        'active' => array('��������� ����� �� ����� ������', 'Active'),
    );
    //���� ��� ������� �� ���� �����
    public $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'url' => array(
                'type' => 'text',
            ),
            'active' => array(
                'type' => 'radio',
                'option' => array('1' => '���������', '0' => '���������'),
            ),
            'note' => array(
                'type' => 'words',
                'value' => '- ���� ���������� ���� �� ���� ������� �� ���������� - �� ��������� ��������� ���� �������� ������������.<br/>- ���� ��� ������� ����������� ���� �����, �� ����������� ������� ����������.',
            ),
        ),
        'id_field' => 'id',
    );
    public $elem_where = "";
    public $elem_req_fields = array();
    public $script = '';

}