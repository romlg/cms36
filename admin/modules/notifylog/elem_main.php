<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

    var $editable = array();
    var $elem_name  = "elem_main";
    var $elem_table = "notify_log";
    var $elem_type  = "single";
    var $elem_str = array(
        'event'       => array('�������',          'Event'),
        'email'       => array('�����',            'Email'),
        'date'        => array('����',             'Date'),
        'text'        => array('�����',            'Text'),
    );
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'event'=>array(
                'type'       => 'text',
                'size'       => 60,
                'readonly'   => 1,
            ),
            'date'=>array(
                'type'       => 'text',
                'size'       => 60,
                'readonly'   => 1,
            ),
            'email'=>array(
                'type'       => 'text',
                'size'       => 60,
                'readonly'   => 1,
            ),
            'text'=>array(
                'type'  =>'fck',
                'toolbar'=> 'Common',
                'size'   => array('100%','250'),
                'display'=> array(
                    'colspan' => true,
                ),
            ),
        ),
        'id_field' => 'id',
    );
    var $elem_req_fields=array('event');

}
?>