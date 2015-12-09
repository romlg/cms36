<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');
define ('USE_ED_VERSION', '1.0.2');
class TMainBaseElement extends TElems
{

    ######################
    var $elem_name = "elem_main"; //название elema
    var $elem_table = "tree"; //название таблицы elema (DEFAULT $elem_name)
    var $elem_type = "single";
    var $elem_str = array( //строковые константы
        'name' => array('Заголовок', 'Name'),
        'page' => array('URL', 'URL'),
        'visible' => array('Показывать в меню', 'Visible'),
        'redirect' => array('Перенаправлять на раздел', 'Redirect to'),
        'is_link' => array('Раздел - ссылка', 'Link'),
    );
    //поля для выборки из базы элема
    var $elem_fields = array(
        'columns' => array(
            'pid' => array(
                'type' => 'hidden',
                'display' => array(
                    'func' => 'get_pid',
                ),
            ),
            'type' => array(
                'type' => 'hidden',
                'display' => array(
                    'func' => 'get_type',

                ),
            ),
            'priority' => array(
                'type' => 'hidden',
            ),
            'name' => array(
                'type' => 'text',
                'size' => 40,
            ),
            'page' => array(
                'type' => 'text',
                'size' => 40,
            ),
            'is_link' => array(
                'type' => 'checkbox',
            ),
            'redirect' => array(
                'type' => 'text',
                'size' => 40,
            ),
            'visible' => array(
                'type' => 'checkbox',
            ),
        ),
        'id_field' => 'id',
        'folder' => 'content'
    );
    var $elem_where = "";
    var $elem_req_fields = array('name');
    var $script = "
    $(document).ready(function(){
        $('#fld\\\\[tab0\\\\]\\\\[is_link\\\\]').change(function(){
            var parent_dl = $(this).closest('dl.tabs');
            if ($(this).is(':checked')) {
                parent_dl.find('dt#tab0').click();
                parent_dl.find('dt:not(#tab0)').hide();
                $('#tr_fld\\\\[tab0\\\\]\\\\[redirect\\\\]').show();
                $('#fld\\\\[tab0\\\\]\\\\[redirect\\\\]').focus();
            } else {
                $('#tr_fld\\\\[tab0\\\\]\\\\[redirect\\\\]').hide();
                parent_dl.find('dt:not(#tab0), tr_fld\\\\[tab0\\\\]\\\\[redirect\\\\]').show();
            }
        });
        $('#fld\\\\[tab0\\\\]\\\\[is_link\\\\]').change();
    });
    ";

    ########################
    function get_pid() {
        $pid = (int)get('pid');
        return $pid ? $pid : sql_getValue("SELECT pid FROM `tree` WHERE id='" . (int)get('id') . "'");
    }

    function get_type() {
        $type = get('type', '', 'g');
        return $type ? $type : sql_getValue("SELECT type FROM `tree` WHERE id='" . (int)get('id') . "'");
    }

    function ElemInit() {
        $columns = sql_getRows("SHOW COLUMNS FROM `tree`", true);
        if (!isset($columns['redirect'])) {
            sql_query("ALTER TABLE tree ADD redirect VARCHAR( 255 ) NOT NULL;");
        }
        if (!isset($columns['is_link'])) {
            sql_query("ALTER TABLE tree ADD is_link TINYINT( 1 ) NOT NULL DEFAULT '0';");
        }
        parent::ElemInit();
    }
    
    function getWCfromDb($id) {
        $row = parent::getWCfromDb($id);
        if ($row['redirect']) 
            $row['is_link'] = 1;
        return $row;
    }
    
    /**
     * Вызывается перед сохранением в базу
     *
     * @param array $fld
     * @return array
     */
    function ElemRedactBefore($fld) {
        if (!$fld['is_link'])
            $fld['redirect']='';

        $fld = parent::ElemRedactBefore($fld);

        $error = "";

        $page_id = get('id', 0, 'gp');
        $page_pid = get('pid', 0, 'gp');

        if ($page_pid) {
            $parent = sql_getRow("SELECT dir, pids, level, root_id FROM " . $this->elem_table . " WHERE id=" . $page_pid);
        } else {
            $page_pid = sql_getValue("SELECT pid FROM `" . $this->elem_table . "` WHERE id=" . $page_id);
            $parent = sql_getRow("SELECT dir, pids, level, root_id FROM " . $this->elem_table . " WHERE id=" . $page_pid);
        }

        if (!$page_id) { // создание нового раздела
            $auto = sql_getRow("SHOW TABLE STATUS LIKE '" . $this->elem_table . "'");
            if ($auto['Auto_increment']) $new_id = $auto['Auto_increment'];

            if ($page_pid == $parent['root_id']) {
                $fld['level'] = 1;
            } else {
                $fld['level'] = $parent['level'] + 1;
            }

            $fld['priority'] = sql_getValue("SELECT MAX(priority) FROM `" . $this->elem_table . "` WHERE pid=" . $page_pid) + 1;
        }

        if (!$fld['page']) $fld['page'] = $page_id ? $page_id : $new_id;
        else $fld['page'] = GetPureName(trim($fld['page']));
        $fld['page'] = strtolower($fld['page']);

        if ($page_pid != $parent['root_id']) {
            # pids
            $pids = explode('/', $parent['pids']);
            if (!$pids[0]) array_shift($pids);
            if (!$pids[count($pids) - 1]) array_pop($pids);
            $pids[] = $page_pid;
            $fld['pids'] = '/' . join('/', $pids) . '/';

            # dir
            $fld['dir'] = $parent['dir'] . $fld['page'] . '/';
        } else {
            # pids
            $fld['pids'] = '/' . $page_pid . '/';
            $fld['dir'] = '/' . $fld['page'] . '/';
        }
        //укажем root_id
        $fld['root_id'] = $parent['root_id'];

        # изменим next у родителя
        sql_query("UPDATE tree SET next='1' WHERE id='" . (isset($parent['id']) ? $parent['id'] : $page_pid) . "'");

        # Проверка на существование dir
        $check = sql_getValue("SELECT id FROM " . $this->elem_table . " WHERE root_id='" . $parent['root_id'] . "' AND dir='" . $fld['dir'] . "'");
        if ($check && $check != $page_id) {
            $error_tab = $k;
            $error = "Раздел с таким URL уже существует";
        }
        return array('fld' => $fld, '_error_text' => $error);
    }

}