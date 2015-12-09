<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TQuestionsBaseElement extends TElems
{

    var $elem_name = "elem_questions";
    var $elem_table = "surveys_quests";
    var $elem_type = "multi";
    var $elem_class = "surveys_questions";
    var $elem_str = array(
        'text' => array('Текст вопроса', 'Title',),
        'priority' => array('Приоритет', 'Priority',),
        'req' => array('Обязательный', 'Req',),
    );
    var $order = " ORDER BY priority ASC";

    //поля для выборки из базы элема
    var $elem_fields = array(
        'id_field' => 'id_survey',
        'type' => 'multi',
        'folder' => '',
    );

    var $elem_where = "";
    var $elem_req_fields = array('text');
    var $script = "";
}