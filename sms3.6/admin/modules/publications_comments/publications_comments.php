<?php

/**
 * Модуль "Комментарии к публикациям"
 */
class TPublications_comments extends TTable
{

    var $name = 'publications_comments';
    var $table = 'publications_comments';
    var $elements = array();
    var $columns_default = ""; // поля для отображения в подключаемом elem-е, если не заданы свои
    var $selector = true;

    ########################

    function TPublications_comments() {
        global $actions, $str;

        TTable::TTable();

        $actions[$this->name] = array(
            'edit' => &$actions['table']['edit'],
            'delete' => &$actions['table']['delete'],
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

        if ((int)$_GET['id']) {
            $temp = sql_getValue("SELECT text FROM " . $this->table . " WHERE id=" . (int)$_GET['id']);
            if ($temp) {
                $temp = strip_tags($temp);
                $temp = substr($temp, 0, 30);
            }
        } else {
            $temp = "Новый комментарий";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Комментарии к публикациям', 'Publications comments',),
            'title_editform' => array("Комментарий: " . $temp, 'Publication : ' . $temp,),

            'date' => array('Дата', 'Date',),
            'publication' => array('Публикация', 'Publication',),
            'user_name' => array('Пользователь', 'User',),
            'visible' => array('Показывать', 'Visible',),
            'text' => array('Текст', 'Text',),

            'saved' => array(
                'Даные были успешно сохранены',
                'Data has been saved successfully',
            ),
        ));
    }

    function Show() {
        if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }

        // строим таблицу
        require_once (core('list_table'));
        $data['table'] = list_table(array(
            'columns' => array(
                array(
                    'select' => 'c.id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => 'UNIX_TIMESTAMP(c.date)',
                    'as' => 'date',
                    'display' => 'date',
                    'flags' => FLAG_SORT | FLAG_FILTER,
                    'filter_type' => 'date',
                    'filter_value' => 'date',
                    'type' => 'date',
                ),
                array(
                    'select' => 'IF(p.name IS NOT NULL, p.name, "-")',
                    'as' => 'publication',
                    'display' => 'publication',
                    'type' => 'publication_link',
                ),
                array(
                    'select' => 'IF(a.name IS NOT NULL, a.name, c.name)',
                    'as' => 'user_name',
                    'display' => 'user_name',
                    'flags' => FLAG_SEARCH | FLAG_SORT,
                    'type' => 'user_link'
                ),
                array(
                    'select' => 'c.text',
                    'type' => 'text',
                    'as' => 'descr',
                    'display' => 'text',
                ),
                array(
                    'select' => 'c.visible',
                    'display' => 'visible',
                    'type' => 'visible',
                    'flags' => FLAG_SORT | FLAG_FILTER,
                    'filter_type' => 'array',
                    'filter_value' => array('') + array('1' => 'Да', '2' => 'Нет'),
                    'filter_field' => 'IF(c.visible=0,2,1)'
                ),
                array(
                    'select' => 'c.user_id',
                ),
                array(
                    'select' => 'c.publication_id',
                ),
                array(
                    'select' => 'c.text',
                    'flags' => FLAG_SEARCH
                ),
            ),
            'from' => $this->table . " as c
			LEFT JOIN `auth_users` as a ON a.id = c.user_id
			LEFT JOIN `publications` as p ON p.id = c.publication_id
			LEFT JOIN `tree` as t ON t.id = p.pid
			",
            'where' => '(t.root_id=' . domainRootId() . ' OR t.root_id IS NULL)',
            'orderby' => 'c.date DESC',
            // всегда передается это
            'params' => array('page' => $this->name, 'do' => 'show'),
            'dblclick' => 'editItem(p.id)',
            'click' => 'ID=cb.value',
            //'_sql' => true,
        ), $this);

        $this->AddStrings($data);
        return $this->Parse($data, LIST_TEMPLATE);
    }

    /**
     * Отображение времени
     * @param $value
     * @param $column
     * @param $row
     * @return string
     */
    function table_get_date(&$value, &$column, &$row) {
        return date("d.m.Y H:i", $value);
    }

    /**
     * Ссылка на карточку публикации
     * @param $value
     * @param $column
     * @param $row
     * @return string
     */
    function table_get_publication_link(&$value, &$column, &$row) {
        if (!$row['publication_id']) return '';
        return "<a href='/admin/editor.php?page=publications&id={$row['publication_id']}' title='Карточка публикации' target='_blank'>{$value}</a>";
    }

    /**
     * Ссылка на карточку пользователя
     * @param $value
     * @param $column
     * @param $row
     * @return string
     */
    function table_get_user_link(&$value, &$column, &$row) {
        if (!$row['user_id']) return $value;
        return "<a href='/admin/editor.php?page=site_users&id={$row['user_id']}' title='Карточка пользователя' target='_blank'>{$value}</a>";
    }

    /**
     * Первые N символов комментария
     * @param $value
     * @param $column
     * @param $row
     * @return string
     */
    function table_get_text(&$value, &$column, &$row) {
        $value = strip_tags($value);
        if (strlen($value) < 200) return $value;
        return substr($value, 0, 200) . '...';
    }

}

$GLOBALS['publications_comments'] = & Registry::get('TPublications_comments');