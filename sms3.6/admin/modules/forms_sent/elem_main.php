<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{
	######################
    var $elem_name = "elem_main";
    var $elem_table = "forms_sent";
    var $elem_type = "single";
    var $elem_str = array(
        'date'		=> array('���� ��������','Date'),
        'email'		=> array('��������','Emails'),
        'subject'	=> array('����','Subject'),
        'page_name'	=> array('������','Page name'),
        'page_url'	=> array('������ �� ������','Page url'),
        'text'		=> array('�����','Text'),
        'attach'	=> array('����������� �����','Attached files'),
        'result'	=> array('��������� ��������','Result'),
    );

    //���� ��� ������� �� ���� �����
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'date' => array(
                'type' => 'text',
                'readonly' => true,
            ),
            'email' => array(
                'type' => 'textarea',
                'size'   => array('100','50'),
                'readonly' => true,
            ),
            'subject' => array(
                'type' => 'textarea',
                'size'   => array('100','50'),
                'readonly' => true,
            ),
            'page_name' => array(
                'type' => 'text',
                'readonly' => true,
            ),
            'page_url' => array(
                'type' => 'text',
                'readonly' => true,
            ),
            'result' => array(
                'type' => 'text',
                'readonly' => true,
            ),
        ),
        'folder' => 'forms_sent',
        'id_field' => 'id',
    );
    
    var $elem_where = "";
    var $elem_req_fields = array();
    var $script = "";
    //	var $sql = true;
}
