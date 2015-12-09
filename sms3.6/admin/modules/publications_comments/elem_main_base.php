<?php

require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainBaseElement extends TElems
{

    var $editable = array();
    var $elem_name = "elem_main";
    var $elem_table = "publications_comments";
    var $elem_type = "single";
    var $elem_str = array(
        'date' => array('Дата', 'Date'),
        'text' => array('Текст', 'Text'),
    );
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'date' => array(
                'type' => 'input_calendar',
                'display' => array(
                    'func' => 'getCurrentDate',
                ),
            ),
            'text' => array(
                'type' => 'fck',
                'size'   => array('100%','300'),
            ),
            'visible' => array(
                'type' => 'checkbox',
            ),
        ),
        'id_field' => 'id',
        'folder' => 'publications_comments'
    );
    var $elem_req_fields = array('text');
    var $script = '';

    function getCurrentDate($v) {
        return isset($v['value']) ? $v['value'] : date('Y-m-d H:i:s');
    }
}