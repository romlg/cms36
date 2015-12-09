<?php

class TSurveys_questions extends TTable
{

    var $name = 'surveys_questions';
    var $table = 'surveys_quests';
    var $columns_default = ""; // поля для отображения в подключаемом elem-е, если не заданы свои

    ########################

    function TSurveys_questions() {
        global $actions, $str;

        TTable::TTable();

        $actions[$this->name] = array(
            'create' => &$actions['table']['create'],
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
        } else {
            $temp = "Новый вопрос";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Вопросы', 'Questions',),
            'title_editform' => array("Вопросы: " . $temp, 'Questsion: ' . $temp,),

            'text' => array('Текст вопроса', 'Title',),
            'priority' => array('Приоритет', 'Priority',),
            'req' => array('Обязательный', 'Req',),

            'saved' => array(
                'Даные были успешно сохранены',
                'Data has been saved successfully',
            ),
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
                'select' => 'text',
                'display' => 'text',
                'flags' => FLAG_SEARCH,
            ),
            array(
                'select' => 'req',
                'display' => 'req',
                'type' => 'visible',
            ),
            array(
                'select' => 'priority',
                'display' => 'priority',
                'type' => 'edit_priority',
            ),
        );

    }

    ########################

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
                    'select' => 'id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => 'text',
                    'display' => 'text',
                    'flags' => FLAG_SEARCH,
                ),
                array(
                    'select' => 'priority',
                    'display' => 'priority',
                    'type' => 'edit_priority'
                ),
            ),
            'from' => $this->table,
            'orderby' => 'priority ASC',
            // всегда передается это
            'params' => array('page' => $this->name, 'do' => 'show'),
            'dblclick' => 'editItem(id)',
            'click' => 'ID=cb.value',
            //'_sql' => true,
        ), $this);

        $this->AddStrings($data);
        return $this->Parse($data, LIST_TEMPLATE);
    }

}

$GLOBALS['surveys_questions'] = & Registry::get('TSurveys_questions');

?>