<?php

class TGallery extends TTable
{

    var $name = 'gallery';
    var $table = 'elem_gallery';
    var $columns_default = ""; // поля для отображения в подключаемом elem-е, если не заданы свои

    ########################

    function TGallery() {
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
            $temp = sql_getValue("SELECT name FROM " . $this->table . " WHERE id=" . (int)$_GET['id']);
        } else {
            $temp = "Новое изображение";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Галерея', 'Gallery',),
            'title_editform' => array("Галерея: " . $temp, 'Gallery: ' . $temp,),

            'image_large' => array('Большая картинка', 'Large image',),
            'image_small' => array('Маленькая картинка', 'Small image',),
            'name' => array('Название', 'Title',),
            'visible' => array('Показывать', 'Visible',),

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
                'select' => 'name',
                'display' => 'name',
                'flags' => FLAG_SEARCH,
            ),
            array(
                'select' => 'image_small',
                'type' => 'smallimagepath',
                'display' => 'image_small',
            ),
            array(
                'select' => 'image_large',
                'type' => 'smallimagepath',
                'display' => 'image_large',
            ),
            array(
                'select' => 'priority',
                'display' => 'priority',
                'type' => 'edit_priority'
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
                    'select' => 'name',
                    'display' => 'name',
                    'flags' => FLAG_SEARCH,
                ),
                array(
                    'select' => 'image_small',
                    'type' => 'smallimagepath',
                    'display' => 'image_small',
                ),
                array(
                    'select' => 'image_large',
                    'type' => 'smallimagepath',
                    'display' => 'image_large',
                ),
                array(
                    'select' => 'visible',
                    'display' => 'visible',
                    'type' => 'visible',
                    'flags' => FLAG_SORT,
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

    // Здесь описываются функции для полей по умолчанию для отображения списка
    function table_get_smallimagepath(&$value, &$column, &$row) {
        if (!$value) return '';
        $script = "";
        static $script_count;
        if (!$script_count != 1) {
            $script_count = 1;
            $script = "
            function show_preview(obj, id) {
                var obj_href = document.getElementById(id).value;
                var obj_init = $(obj).attr('init');
                if (typeof(obj_init)=='undefined') {
                    $(obj).fancybox({
                        'href' : obj_href,
                        'centerOnScroll': true,
                        'autoScale'     : false,
                        'transitionIn'	: 'none',
                        'transitionOut'	: 'none',
                        'hideOnOverlayClick' : false
                    });
                    $(obj).attr('init', 1);
                    $(obj).click();
                }
            }";
        }
        return $script . '
        <input type="hidden" value="' . $value . '" id="image_' . $column['select'] . '_' . $row['id'] . '">
        <a href="javascript:void(0);" name="preview[\'' . $row['id'] . '\']" onclick="show_preview(this, \'image_' . $column['select'] . '_' . $row['id'] . '\'); return false;">' . $value . '</a>
        ';
    }
}

$GLOBALS['gallery'] = & Registry::get('TGallery');

?>