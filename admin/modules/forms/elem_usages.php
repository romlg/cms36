<?php

require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TUsagesElement extends TElems
{
    var $elem_name = "elem_usages";
    var $elem_table = "forms";
    var $elem_type = "single";
    var $elem_str = array(
        'title' => array('Тема', 'Title'),
        'text' => array('Текст', 'Text'),
    );
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'hash' => array(
                'type' => 'hidden',
            ),
            'text' => array(
                'type' => 'words',
            ),
        ),
        'id_field' => 'id',
        'folder' => 'forms'
    );
    var $elem_req_fields = array();

    function ElemForm() {
        $row = $this->getObject();
        // добавляет в шаблон дефолтные строковые константы
        $this->AddStrings($row);
        return $this->Parse($row, 'forms_usages.tmpl');
    }

    ########################

    function ElemEdit($id, $row) {
        return 1;
    }
}

?>