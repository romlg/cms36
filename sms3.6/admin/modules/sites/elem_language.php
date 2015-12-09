<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TLanguageElement extends TElems
{

    var $elem_name = "elem_language";
    var $elem_table = "sites_langs";
    var $elem_type = "multi";
    var $elem_class = "sites_langs";
    var $elem_str = array(
        'name' => array('Название', 'Title',),
        'descr' => array('Описание', 'Description',),
        'locale' => array('Локаль', 'Locale',),
        'charset' => array('Кодировка', 'Charset',),
        'root_id' => array('ID', 'ID',),
    );
    var $order = " ORDER BY priority ";

    //поля для выборки из базы элема
    var $elem_fields = array(
        'id_field' => 'pid',
        'type' => 'multi',
    );

    var $elem_where = "";
    var $elem_req_fields = array();
    var $script = "";
}
