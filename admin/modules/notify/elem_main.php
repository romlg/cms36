<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    var $editable = array();
    var $elem_name = "elem_main";
    var $elem_table = "notify_events";
    var $elem_type = "single";
    var $elem_str = array(
        'subject' => array('Тема', 'Subject'),
        'template' => array('Шаблон <span style="color:#999;">(изменение значений в {} может привести к некорректной отправке уведомлений)</span>', 'Template'),
        'mails' => array('Адреса <span style="color:#999;">(через запятую)</span>', 'Emails'),
        'replyto' => array('Обратный адрес', 'Reply-to'),
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
