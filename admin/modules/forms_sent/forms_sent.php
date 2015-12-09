<?php

class TForms_sent extends TTable
{
    var $name = 'forms_sent';
    var $table = 'forms_sent';
    var $columns_default = ""; // поля для отображения в подключаемом elem-е, если не заданы свои
    var $elements = array('elem_text');

    ########################

    function TForms_sent() {
        global $actions, $str;

        TTable::TTable();

        $actions[$this->name] = array(
            'edit' => &$actions['table']['edit'],
            'delete' => &$actions['table']['delete'],
        );

        $actions[$this->name . '.editform'] = array(
            'cancel' => array(
                'title' => array(
                    'ru' => 'Выход',
                    'en' => 'Exit',
                ),
                'onclick' => 'window.location=\'/admin/?page=' . $this->name . '\'',
                'img' => 'icon.close.gif',
                'display' => 'block',
                'show_title' => true,
            ),
        );

        if ((int)$_GET['id']) {
            $temp = sql_getValue("SELECT date FROM " . $this->table . " WHERE id=" . (int)$_GET['id']);
        } else {
            $temp = "Добавить";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'saved' => array(
                'Даные были успешно сохранены',
                'Data has been saved successfully',
            ),
            'title'			=> array('Письма','Forms'),
            'title_editform' => array("Письмо от " . $temp, 'Form: ' . $temp,),
			'add'			=> array('Добавление','Add'),
			'edit'			=> array('Редактирование','Edit'),
            'date'			=> array('Дата отправки','Date'),
            'email'			=> array('Адресаты','Emails'),
            'page_name'		=> array('Раздел','Page'),
            'text'			=> array('Текст','text'),
        ));

        // Здесь описываются поля по умолчанию для отображения списка
        $this->columns_default = array(
            array(
                'select' => 'id',
                'display' => 'ids',
                'type' => 'checkbox',
                'width' => '1px',
            ),
            array(
                'select' => 'date',
                'display' => 'date',
                'flags' => FLAG_SEARCH | FLAG_SORT,
            ),
            array(
                'select' => 'email',
                'display' => 'email',
                'flags' => FLAG_SEARCH | FLAG_SORT,
            ),
        );
        
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
                    'select' => 'm.id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => 'm.date',
                    'display' => 'date',
                    'flags' => FLAG_SORT,
                    'type' => 'text',
                ),
                array(
                    'select' => 'm.email',
                    'display' => 'email',
                ),
                array(
                    'select' => 'm.page_name',
                    'display' => 'page_name',
                    'type' => 'page',
                    'flags' => FLAG_SORT,
                ),
                array(
                    'select' => 'm.page_url',
                ),
                array(
                    'select' => 'm.text',
                    'display' => 'text',
                    'type' => 'text_content',
                    'flags' => FLAG_SEARCH,
                ),
            ),
            'from' => $this->table . " AS m",
            'orderby' => 'm.date DESC',
            'params' => array('page' => $this->name, 'do' => 'show'),
            'click' => 'ID=cb.value',
            //'_sql' => true,
        ), $this);

        $this->AddStrings($data);
        return $this->Parse($data, LIST_TEMPLATE);
    }

    /**
     * Отображение ссылки на раздел
     * @param $value
     * @param $column
     * @param $row
     * @return string
     */
    function table_get_page(&$value, &$column, &$row) {
        if (!$row['page_url'] && !$row['page_name']) return "";
        $res = "<a href = '{$row['page_url']}' target='_blank'>{$row['page_name']}</a>";
        $res .= "&nbsp;&nbsp;<a href = '{$row['page_url']}' target='_blank'><img src='/admin/images/icons/icon.preview.png' /></a>";
        return $res;
    }

    /**
     * Отображение текста
     * @param $value
     * @param $column
     * @param $row
     * @return string
     */
    function table_get_text_content(&$value, &$column, &$row) {
        return strip_tags(str_replace('<br />', " \r\n", $row['text']));
    }
}

$GLOBALS['forms_sent'] = & Registry::get('TForms_sent');

?>