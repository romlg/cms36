<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TRuleBaseElement extends TElems
{

    ######################
    public $elem_name = "elem_rule";
    public $elem_table = "infoblocks_rules";
    public $elem_type = "multi";
    public $elem_class = "infoblocks_rules";

    public $elem_str = array(
        'url' => array('URL', 'URL'),
        'active' => array('Разрешить показ по этому адресу', 'Active'),
    );
    //поля для выборки из базы элема
    public $elem_fields = array(
        'id_field' => 'pid',
    );
    public $elem_where = "";
    public $elem_req_fields = array();
    public $script = '';

    ####################################################
    function ElemInit() {
        $this->columns = array(
            array(
                'select' => 'id',
                'display' => 'id',
                'type' => 'checkbox',
            ),
            array(
                'select' => 'url',
                'display' => 'url',
                'flags' => FLAG_SORT | FLAG_SEARCH,
            ),
            array(
                'select' => 'active',
                'display' => 'active',
                'type' => 'visible',
                'flags' => FLAG_SORT,
            ),
        );
        TElems::ElemInit();
    }

}