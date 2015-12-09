<?php
/**
 *
 * Модуль публикации (главный элемент формы)
 *
 * @package    admin/modules
 *
 * @author     Semenov Alexander
 * @copyright  Rusoft, 09.07.2012
 */
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainBaseElement extends TElems
{

    var $editable = array();
    var $elem_name = "elem_main";
    var $elem_table = "publications";
    var $elem_type = "single";
    var $elem_str = array(
        'notice' => array('Анонс', 'Notice'),
        'date' => array('Дата', 'Date'),
        'text' => array('Текст', 'Text'),
        'image' => array('Изображение', 'Image'),
        'pid' => array('Задать основной раздел публикации', 'Part'),
        'pids' => array('Добавить дополнительные разделы', 'SubPart'),
        'allow_comments' => array('Разрешены комментарии', 'Allow comments'),
    );
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'name' => array(
                'type' => 'text',
                'size' => 255,
            ),
            'date' => array(
                'type' => 'input_calendar',
                'display' => array(
                    'func' => 'getCurrentDate',
                ),
            ),
            'visible' => array(
                'type' => 'checkbox',
            ),
            'allow_comments' => array(
                'type' => 'checkbox',
            ),
            'pid' => array(
                'type' => 'input_treeid',
                'add_path_with_id' => 'p',
            ),
            'pids' => array(
                'type' => 'input_treecheck',
                'depends_show' => 'fld[tab0][pid]', // означает, что поле станет видно, только когда значение pid будет не пустым
                'add_path_with_id' => 'p',
            ),
            'notice' => array(
                'type' => 'fck',
                'size'   => array('100%','300'),
            ),
            'text' => array(
                'type' => 'fck',
                'size'   => array('100%','300'),
            ),
            'image' => array(
                'type' => 'input_image',
            ),
        ),
        'id_field' => 'id',
        'folder' => 'publications'
    );
    var $elem_req_fields = array('name');
    var $script = '';

    function ElemInit() {
        $columns = sql_getRows("SHOW COLUMNS FROM `publications`", true);
        if (!isset($columns['allow_comments'])) {
            unset($this->elem_fields['columns']['allow_comments']);
        }
        return parent::ElemInit();
    }

    function getCurrentDate($v) {
        return isset($v['value']) ? $v['value'] : date('Y-m-d H:i:s');
    }

    function ElemRedactBefore($fld) {
        if (!$fld['pid']) $fld['pid'] = 'NULL';
        return $fld;
    }
}

?>