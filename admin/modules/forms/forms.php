<?php

class TForms extends TTable {

    var $name = 'forms';
    var $table = 'forms';
    var $elements = array(
        'elem_usages'
    );
    var $selector = true;
    var $copy_settings = array(
            'copy_id' => 'id',
            'copy_field' => 'name_site',
            'copy_default' => array(
                                'visible' => 0,
                                'hash' => '',
                             ),
            'copy_tables' => array(
                                'forms_elems' => array(
                                    'field' => 'pid',
                                    'copy_tables' => array(
                                        'forms_values' => 'pid',
                                    ),
                                ),
            )
        );

    function TForms() {
        global $str, $actions;
        TTable::TTable();

        if ((int)$_GET['id']) {
            $temp = sql_getValue("SELECT name FROM " . $this->table . " WHERE id=" . (int)$_GET['id']);
        } else {
            $temp = "Новая форма";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Формы', 'Forms',),
            'name_site' => array('Название формы (внутреннее)', 'Title (on site)'),
            'title_editform' => array("Форма: " . $temp, 'Form: ' . $temp,),
            'name' => array('Название', 'Title',),
            'visible' => array('Показывать', 'Visible',),
            'is_popup' => array('Всплывающая форма', 'Popup form',),
            'hash' => array('Код для вставки', 'Code',),
        ));

        $actions[$this->name] = array(
            'create' => &$actions['table']['create'],
            'edit' => &$actions['table']['edit'],
            'delete' => &$actions['table']['delete'],
            'copy' => &$actions['table']['copy'],
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

        if (isset($_GET['id']) && $_GET['id']==0) {
            unset($actions[$this->name . '.editform']['find_form_usage']);
            $this->elements = array();
        }

        // Здесь описываются поля по умолчанию для отображения списка
        $this->columns_default = array(
            array(
                'select' => 'id',
                'display' => 'ids',
                'type' => 'checkbox',
                'width' => '1px',
            ),
            array(
                'select' => 'name_site',
                'display' => 'name_site',
            ),
            array(
                'select' => 'visible',
                'display' => 'visible',
                'type' => 'visible',
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
                    'select' => 'name_site',
                    'display' => 'name_site',
                    'flags' => FLAG_SEARCH | FLAG_SORT,
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
                    'select' => 'hash',
                    'display' => 'hash',
                    'type' => 'code',
                ),
            ),
            'from' => $this->table,
            'params' => array('page' => $this->name, 'do' => 'show'),
            'orderby' => 'name',
        ), $this);

        $ret['table'] .= "
            <script>
                $(document).ready(function(){

                    function setcode(text, ptext){
                        var toret = '';
                        if (text) {
                            switch (ptext) {
                                case true:
                                    toret = 'text_popup_html';
                                    break;
                                case false:
                                    toret = 'text_html';
                                    break;
                                default:
                                    toret = 'text_html';
                                    break;
                            }
                        } else {
                            switch (ptext) {
                                case true:
                                    toret = 'text_popup_normal';
                                    break;
                                case false:
                                    toret = 'text_normal';
                                    break;
                                default:
                                    toret = 'text_normal';
                                    break;
                            }
                        }
                        return toret;
                    }

                    $('.popup_code_shower').change(function(){
                        var text_obj = $('#code_text_'+$(this).attr('popup_code_id'));
                        text_obj.text(text_obj.attr(setcode($('#code_shower_'+$(this).attr('popup_code_id')).is(':checked'), $(this).is(':checked'))));
                    });
                    $('.code_shower').change(function(){
                        var text_obj = $('#code_text_'+$(this).attr('code_id'));
                        text_obj.text(text_obj.attr(setcode($(this).is(':checked'), $('#popup_code_shower_'+$(this).attr('code_id')).is(':checked'))));
                    });

                    $('.code_shower_text').click(function(){
                        $(this).focus().select();
                    });
                    $('.code_shower_href').click(function(){
                        $(this).hide();
                        $(this).next('div').show();
                    });
                });
            </script>
        ";

        $ret['thisname'] = $this->name;
        return $this->Parse($ret, LIST_TEMPLATE);
    }

    function show_usage() {
        global $settings;
        $ret = array(
            'hash' => $_POST['hash'],
            'table_results' => array()
        );
        $code = '"%get_form=' . mysql_real_escape_string($_POST['hash']) . '%"';
        $code2 = '"%[[FORM ' . mysql_real_escape_string($_POST['hash']) . ']]%"';
        $code3 = '"%get_form=' . mysql_real_escape_string($_POST['hash']) . '&is_popup%"';
        $code4 = '"%[[FORMPOPUP ' . mysql_real_escape_string($_POST['hash']) . ']]%"';

        foreach($settings['forms_searching_tables'] as $s_table) {
            if (!$s_table['key']) $s_table['key']='id';
            if (!$s_table['name']) $s_table['name']="''";
            if (!$s_table['dir']) $s_table['dir']="''";
            if (!$s_table['where']) $s_table['where']='1';
            if (!empty($s_table['search_fields'])) {
                $s_table['where'] .= " AND (
                    (" . implode(" LIKE $code ) OR (",$s_table['search_fields']) . " LIKE $code ) OR
                    (" . implode(" LIKE $code2 ) OR (",$s_table['search_fields']) . " LIKE $code2 ) OR
                    (" . implode(" LIKE $code3 ) OR (",$s_table['search_fields']) . " LIKE $code3 ) OR
                    (" . implode(" LIKE $code4 ) OR (",$s_table['search_fields']) . " LIKE $code4 )
                )";
            }
            $sql = "
                SELECT {$s_table['key']} as 'key', {$s_table['name']} as 'name', {$s_table['dir']} as 'dir'
                FROM {$s_table['from']} WHERE {$s_table['where']}
            ";

            $rows = sql_getRows($sql,'key');
            $has_admn_hrefs = false;
            $has_site_hrefs = false;

            if ($s_table['admin_href'] || $s_table['site_href']) {
                foreach($rows as $key=>&$row) {
                    $row['admin_href'] = str_replace(array('{$key}','{$dir}'),array($key,$row['dir']),$s_table['admin_href']);
                    $row['site_href'] = str_replace(array('{$key}','{$dir}'),array($key,$row['dir']),$s_table['site_href']);
                    $has_admn_hrefs |= $row['admin_href']!='';
                    $has_site_hrefs |= $row['site_href']!='';
                }
            }

            $ret['table_results'][] = array(
                'has_admin_hrefs' => $has_admn_hrefs,
                'has_site_hrefs' => $has_site_hrefs,
                'title' => $s_table['title'],
                'items' => $rows
            );
        }

        echo $this->Parse(array('object' => $ret), 'forms_usages_table.tmpl');
        die();
    }

    /**
     * Отображение кода для вставки формы
     * @param $value
     * @param $column
     * @param $row
     * @return string
     */
    function table_get_code(&$value, &$column, &$row) {
        if (!$row['hash']) return "";
        $code_normal = htmlentities("[[FORM {$row['hash']}]]");
        $code_html = htmlentities("<script src=\"?get_form={$row['hash']}\" type=\"text/javascript\"></script>");

        $code_normal2 = htmlentities("[[FORMPOPUP {$row['hash']}]]");
        $code_html2 = htmlentities("<script src=\"?get_form={$row['hash']}&is_popup\" type=\"text/javascript\"></script>");

        $html = "
            <a href='javascript:void(0)' class='code_shower_href'>Получить код для вставки</a>
            <div style='display:none'>
            <textarea id='code_text_{$row['hash']}' class='code_shower_text' text_normal='$code_normal' text_html='$code_html' text_popup_normal='$code_normal2' text_popup_html='$code_html2'>$code_normal</textarea>
            <br style=\"clear: both;\" />
            <label for='code_shower_{$row['hash']}' style=\"width: auto;margin: 3px 5px 0 0;\">HTML-код</label>
            <input type='checkbox' id='code_shower_{$row['hash']}' code_id='{$row['hash']}' class='code_shower' value='1' /><br/>
            <label for='popup_code_shower_{$row['hash']}' style=\"width: auto;margin: 3px 5px 0 0;\">Всплывающая</label>
            <input type='checkbox' id='popup_code_shower_{$row['hash']}' popup_code_id='{$row['hash']}' class='popup_code_shower' value='1' style='margin: 4px 26px 0;' />
            </div>
        ";
        return $html;
    }

}

$GLOBALS['forms'] = & Registry::get('TForms');