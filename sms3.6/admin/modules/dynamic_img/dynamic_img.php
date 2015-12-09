<?php

class TDynamic_img extends TTable {

    var $name = 'dynamic_img';
    var $table = 'dynamic_img';
    var $selector = true;

    function TDynamic_img() {
        global $str, $actions;
        TTable::TTable();

        if ((int)$_GET['id']) {
            $temp = sql_getValue("SELECT name FROM " . $this->table . " WHERE id=" . (int)$_GET['id']);
        } else {
            $temp = "Новое изображение";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Сменяющиеся изображения', 'Dynamic images',),
            'title_editform' => array("Сменяющиеся изображения: " . $temp, 'Dynamic images: ' . $temp,),
            'name' => array('Название', 'Title',),
            'priority' => array('Порядок отображения', 'priority',),
            'image' => array('Изображение', 'image',),
            'alt' => array('Альтернативный текст', 'alt',),
            'link' => array('Название кнопки', 'link',),
            'link_url' => array('Ссылка кнопки', 'link',),
            'visible' => array('Отображать', 'visible',),
            'description' => array('Описание', 'description',),
            'root_ids' => array('На каких сайтах показывать', 'root ids',),
        ));

        $actions[$this->name] = array(
            'create' => &$actions['table']['create'],
            'edit' => &$actions['table']['edit'],
            'delete' => &$actions['table']['delete'],
        );

        $actions[$this->name . '.editform'] = array(
            'apply' => array(
                'title' => array(
                    'ru' => 'Сохранить',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].elements[\'do\'].value=\'apply\'; document.forms[\'editform\'].submit(); return false;',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'save_close' => array(
                'title' => array(
                    'ru' => 'Сохранить и закрыть',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].elements[\'do\'].value=\'save\'; document.forms[\'editform\'].submit(); return false;',
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
            ),
            array(
                'select' => 'visible',
                'display' => 'visible',
                'type' => 'visible',
            ),
            array(
                'select' => 'priority',
                'display' => 'priority',
            ),
        );
    }

    /**
     * Оторажение списка персон
     * @return mixed
     */
    function Show() {
        if (!empty($_POST)) {
            $actions = get('actions', '', 'p');
            if ($actions) {
                return $this->$actions();
            }
        }

        require_once (core('list_table'));
        $ret['table'] = list_table(array(
            'columns' => array(
                array(
                    'select' => 'id',
                    'display' => 'id',
                    'type' => 'checkbox',
                    'width' => '1px',
                ),
                array(
                    'select' => 'name',
                    'display' => 'name',
                    'flags' => FLAG_SEARCH | FLAG_SORT,
                ),
                array(
                    'select'    => 'image',
                    'type'      => 'imagepath',
                    'display'   => 'image',
                ),
                array(
                    'select' => 'visible',
                    'display' => 'visible',
                    'type' => 'visible',
                    'flags' => FLAG_SORT | FLAG_FILTER,
                    'filter_type' => 'array',
                    'filter_value' => array('') + array('1' => 'Да', '2' => 'Нет'),
                    'filter_field' => 'IF(visible=0,2,1)'
                ),
                array(
                    'select' => 'priority',
                    'display' => 'priority',
                    'flags' => FLAG_SEARCH | FLAG_SORT,
                ),
            ),
            'from' => $this->table,
            'where' => (domainRootId()>0) ? '(FIND_IN_SET('.domainRootID().', root_ids) OR root_ids="")' : "",
            'params' => array('page' => $this->name, 'do' => 'show'),
            'orderby' => 'name',
			'script'    => "function pic_preview(obj) {
                obj_href = $(obj).attr('link');
                obj_init = $(obj).attr('init');
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
            }",
        ), $this);

        $ret['thisname'] = $this->name;
        return $this->Parse($ret, LIST_TEMPLATE);
    }

    function table_get_imagepath(&$value, &$column, &$row) {
        return ($value) ? '<a href="javascript:void(0);" link="'.$value.'" onclick="pic_preview(this);"><img src="images/icons/icon.preview.png" title="Просмотр" alt="Просмотр" class="fileView"></a>' : '-- не указано --';
    }
}

$GLOBALS['dynamic_img'] = & Registry::get('TDynamic_img');