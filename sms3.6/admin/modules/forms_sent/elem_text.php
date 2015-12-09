<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TTextElement extends TElems
{
	######################
    var $elem_name = "elem_text";
    var $elem_table = "forms_sent";
    var $elem_type = "single";
    var $elem_str = array();

    //поля для выборки из базы элема
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'text' => array(
                'type' => 'words',
            ),
            'attach' => array(
                'type' => 'words',
            ),
        ),
        'folder' => 'forms_sent',
        'id_field' => 'id',
    );
    
    var $elem_where = "";
    var $elem_req_fields = array();
    var $script = "";
    //	var $sql = true;

    function ElemInit() {
        $id = (int)get('id', 0, 'pg');
        if ($id && $row = sql_getRow("SELECT * FROM " . $this->elem_table . " WHERE id = ".(int)$id)) {
            $this->elem_fields['columns']['text']['value'] = "<h1>Текст:</h1><div class='tabContent'>" . $row['text'] . "</div>";
            $files = unserialize($row['attach']);
            if (!empty($files)) {
                $attach = "
                    <h1>Файлы:</h1>
                    <div class='tabContent'>
                    <table class='ajax_table_main' style='width: 100%'>
                    <tr>
                        <th>Путь</td>
                        <th>Размер (байт)</td>
                    </tr>";
                foreach($files as $file) {
                    $file['name'] = '/' . ltrim($file['name'],'/');
                    $attach .= "
                        <tr>
                            <td><a href='{$file['name']}'>{$file['name']}</a></td>
                            <td>{$file['size']}</td>
                        </tr>
                    ";
                }
                $attach .= "</table></div>";
                $this->elem_fields['columns']['attach']['value'] = $attach;
            }
        }
        return parent::ElemInit();
    }

}
