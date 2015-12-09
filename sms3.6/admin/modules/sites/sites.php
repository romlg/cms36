<?php

include_once 'sms3.6/admin/modules/tree/tree.php';

class TSites extends TTree
{

    var $name = 'sites';
    var $table = 'sites';
    var $selector = false;
    var $elements = array(
        'elem_language',
    );

    function TSites() {
        global $str, $actions;

        TTable::TTable();

        $actions['sites'] = array(
            'edit' => &$actions['table']['edit'],
            'create' => &$actions['table']['create'],
            'delete' => &$actions['table']['delete'],
            'copy' => array(
                'Копировать',
                'Copy',
                'link' => 'copyItems',
                'img' => 'icon.copy.gif',
                'multiaction' => true
            ),
        );

        $actions[$this->name . '.editform'] = array(
            'save' => array(
                'title' => array(
                    'ru' => 'Сохранить',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].submit(); return false;',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'cancel' => array(
                'title' => array(
                    'ru' => 'Отмена',
                    'en' => 'Cancel',
                ),
                'onclick' => 'window.location=\'/admin/?page=' . $this->name . '\'',
                'img' => 'icon.close.gif',
                'display' => 'block',
                'show_title' => true,
            ),
        );

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Сайты', 'Sites',),
            'descr' => array('Описание', 'Description'),
            'root_id' => array('ID', 'ID'),
            'saved' => array('Сохранение прошло успешно', 'Successful saved'),
            'name' => array('Название', 'Name',),
            'copy' => array('Копирование прошло успешно', 'Successful copy'),
            'deleted' => array('Удаление прошло успешно', 'Successful copy'),
        ));
    }

    function Show() {
        if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }

        $ret['thisname'] = $this->name . '.editform';

        require_once (core('list_table'));
        $ret['table'] = list_table(array(
            'columns' => array(
                array(
                    'select' => 's.id',
                    'as' => 'id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => 's.name',
                    'display' => 'name',
                    'flags' => FLAG_SORT,
                ),
                array(
                    'select' => 'CONCAT(s.descr, \' - \', l.descr)',
                    'as' => 'descr',
                    'display' => 'descr',
                    'lang_select' => LANG_SELECT,
                    'flags' => FLAG_SORT,
                ),
                array(
                    'select' => 't.root_id',
                    'display' => 'root_id',
                    'flags' => FLAG_SORT,
                ),
            ),
            'from' => $this->table . " as s
            LEFT JOIN sites_langs as l ON l.pid=s.id
            LEFT JOIN tree as t ON t.root_id=l.root_id AND t.id=t.pid",
            'where' => "",
            'params' => array('page' => $this->name, 'do' => 'show', 'move' => 0),
            'orderby' => 't.priority',
            'click' => 'ID=cb.value',
            'dblclick' => 'editItem(id)',
            //'_sql'		=> 1,
        ), $this);
        $this->AddStrings($ret);
        $ret['thisname2'] = str_replace('/', '', $this->name);
        return $this->Parse($ret, 'sites.tmpl');

    }

    /**
     * Удаление языковой версии
     * @return string
     */
    function Delete() {
        $ids = (array)get('id', array(), 'g');
        sql_query('BEGIN');
        foreach ($ids as $key => $id) {
            $row = sql_getRow("SELECT * FROM sites_langs WHERE id=" . $id);

            // Удаляем из дерева все разделы с таким root_id
            sql_query("DELETE FROM tree WHERE root_id=" . $row['root_id']);

            // Удаляем из таблицы sites_langs
            sql_delete("sites_langs", $id);

            $count = (int)sql_getValue("SELECT COUNT(1) FROM sites_langs WHERE pid=" . $row['pid']);
            if (!$count) {
                // Удаляем из таблицы sites
                sql_delete('sites', $row['pid']);
            }
        }
        sql_query('COMMIT');

        touch_cache('sites');
        touch_cache('tree');

        return "<script type='text/javascript'>location.href = '/admin/?page=" . $this->name . "';</script>";
    }

    /**
     * Копирование сайта
     * @return string
     */
    function editCopy() {
        $ids = (array)get('id', array(), 'g');
        if (!$ids) echo "<script type='text/javascript'>location.href = '/admin/?page=" . $this->name . "';</script>";

        set_time_limit(60);

        sql_query('BEGIN');

        foreach ($ids as $key => $id) {

            // Получаем данные по сайту
            $data = sql_getRow("SELECT * FROM sites WHERE id=" . $id);

            unset($data['id']);
            $number = sql_getValue("SELECT COUNT(id) FROM sites WHERE name LIKE '" . $data['name'] . "%'");
            $data['name'] .= "_" . $number;

            // Копируем эти данные в новую строку
            $new_site_id = sql_insert('sites', $data);

            if (!is_int($new_site_id)) {
                sql_query('ROLLBACK');
                echo $this->str('error') . ': ' . $new_site_id;
                die();
            }

            // Копируем языки
            $langs = sql_getRows("SELECT name, descr, locale, charset, priority, root_id FROM sites_langs WHERE pid=" . $id);
            if (!empty($langs)) {

                foreach ($langs as $lang_row) {
                    $old_root = $lang_row['root_id'];

                    $new_root_id = (int)sql_getValue("SELECT MIN(root_id) FROM tree WHERE 1") - 1;
                    if ($new_root_id <= 0) {
                        $new_root_id = (int)sql_getValue("SELECT MAX(id) FROM tree WHERE 1") + 1;
                    }
                    $lang_row['pid'] = $new_site_id;
                    $lang_row['root_id'] = $new_root_id;

                    $new_lang_id = sql_insert('sites_langs', $lang_row);
                    if (!is_int($new_lang_id)) {
                        sql_query('ROLLBACK');
                        echo $this->str('error') . ': ' . $new_lang_id;
                        die();
                    }

                    // Создаем в дереве корневой элемент
                    $tree_row = sql_getRow("SELECT * FROM tree WHERE id=" . $old_root);
                    if (!$tree_row) {
                        sql_query('ROLLBACK');
                        echo 'no row in tree for root_id=' . $old_root;
                        die();
                    }
                    $tree_row['id'] = $tree_row['pid'] = $tree_row['root_id'] = $new_root_id;
                    $tree_row['pids'] = '/' . $new_root_id . '/';
                    $tree_row['priority'] = (int)sql_getValue("SELECT MAX(priority) FROM tree WHERE id=pid") + 1;
                    $_id = sql_insert('tree', $tree_row);
                    if (!is_int($_id)) {
                        sql_query('ROLLBACK');
                        echo $this->str('error') . ': ' . $_id;
                        die();
                    }

                    // Копируем разделы
                    $this->table = 'tree';
                    $rows = sql_getColumn("SELECT id FROM tree WHERE pid=" . $old_root . " AND id<>pid");
                    if ($rows) foreach ($rows as $row) {
                        $this->CopyTree($row, $new_root_id, true);
                    }
                    $this->Validate(0, '', 0, array(), $new_root_id);
                    $this->table = 'sites';
                }
            }
        }

        sql_query('COMMIT');
        touch_cache('sites');
        touch_cache('tree');

        return "<script type='text/javascript'>location.href = '/admin/?page=" . $this->name . "';</script>";
    }
}

$GLOBALS['sites'] = & Registry::get('TSites');