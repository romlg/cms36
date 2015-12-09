<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    var $editable = array();
    var $elem_name = "elem_main";
    var $elem_table = "notify_events";
    var $elem_type = "single";
    var $elem_str = array(
        'subject' => array('����', 'Subject'),
        'template' => array('������ <span style="color:#999;">(��������� �������� � {} ����� �������� � ������������ �������� �����������)</span>', 'Template'),
        'mails' => array('������ <span style="color:#999;">(����� �������)</span>', 'Emails'),
        'replyto' => array('�������� �����', 'Reply-to'),
    );
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'subject' => array(
                'type' => 'text',
            ),
            'template' => array(
                'type' => 'fck',
                'size' => array('100%', '250'),
            ),
            'mails' => array(
                'type' => 'text',
            ),
            'replyto' => array(
                'type' => 'text',
            ),
        ),
        'id_field' => 'id',
    );
    var $elem_req_fields = array();

}
