<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    public $editable = array();
    public $elem_name = "elem_main";
    public $elem_table = "infoblocks_rules";
    public $elem_type = "single";
    public $elem_str = array(
        'url' => array('URL (без http://)', 'URL (without http://)'),
        'active' => array('Разрешить показ по этому адресу', 'Active'),
    );
    //поля для выборки из базы элема
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
                'option' => array('1' => 'разрешить', '0' => 'запретить'),
            ),
            'note' => array(
                'type' => 'words',
                'value' => '- если поставлено хотя бы одно условие на разрешение - на остальных страницах блок перестаёт показываться.<br/>- если два условия перекрывают друг друга, то срабатывает условие разрешения.',
            ),
        ),
        'id_field' => 'id',
    );
    public $elem_where = "";
    public $elem_req_fields = array();
    public $script = '';

}