<?php
//require_once(core('ajax_table'));
require_once(core('form'));
require_once('ed.utils.php');

/**
 * Класс оболочка для элемов
 *
 */

class TElems extends TTable
{

    /* отображать sql при запросе к базе */
    var $sql = false;
    /* отображать необходимые данные для debuga */
    var $debug = false;
    /* javascript в запрос */
    var $script = '';
    /* вкл/выкл экшенов на мульти элемах */
    var $elem_actions = true;
    /* события по умолчанию для строк таблицы */
    var $click = 'ID=cb.value';
    var $dblclick = '';
    /* тип элема по умолчанию */
    var $elem_type = 'single';
    /* жесткий изврат:)*/
    var $xxx = false;
    /* массив для построения формы*/
    var $elem_fields;
    /* вкл/выкл сохранение элема, если вдруг элем полностью не надо сохранять */
    var $nosave = false;
    var $tabname;
    var $list_buttons;
    var $obj_delete = 1;

    /**
     * Инициализация элема
     *
     */
    function ElemInit() {
        if (!isset($this->elem_table)) $this->elem_table = $this->elem_name;
        if (!isset($this->elem_fields['id_field'])) $this->elem_fields['id_field'] = 'id';

        if ($this->elem_type == 'multi' || $this->elem_type == 'link') {
            global $multielemactions, $intlang;

            if (!isset($this->list_buttons)) {
                $act = $multielemactions;
            } else {
                $act = $this->list_buttons;
            }

            foreach ($act AS $key => $val) {
                $this->list_buttons[$key] = $val;
                $this->list_buttons[$key]['name'] = $val[$intlang];
                $this->list_buttons[$key]['onclick'] = $this->tabname . '.' . $val['onclick'];

            }

            // Если поля не описаны в элеме, то сходим за ними в указанный класс
            if (empty($this->columns)) {
                if (!empty($this->elem_class)) {
                    $class_name = $this->elem_class;
                } else {
                    $str = explode("_", $this->elem_name);
                    $class_name = (isset($str[1])) ? $str[1] : '';
                }

                include_once (module($class_name));
                $elem_class = & Registry::get("T" . $class_name);
                $this->columns = $elem_class->columns_default;
            }
        }

        // подгружаем строковые константы из элема и общие
        global $str;
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array_merge(array(
            'id' => array('ID', 'ID'),
            'add' => array('Добавить', 'Add'),
            'del' => array('Удалить', 'Delete'),
            'preview' => array('Просмотр', 'Preview'),
            'visible' => array('Показывать', 'Visible',),
            'serv' => array('Загрузить с сервера', 'Download from server...',),
            'server' => array('С сервера...', 'From server...',),
            'komp' => array('Загрузить с компьютера', 'Download from computer',),
            'saved' => array('Сохранение прошло успешно', 'Saved',),
        ), $this->elem_str));
    }


    /**
     * Получение wc из базы
     *
     * @param int $id идентификатор записи в базе
     * @param string $type simple/multi
     * @param string $id_field field для id
     * @return array()
     */
    function getWCfromDb($id, $type = false, $id_field = false) {
        if (!$id) return false;

        if (!$type) $type = $this->elem_type;
        if (!$id_field) $id_field = $this->elem_fields['id_field'];
        if (!$id2_field) $id2_field = $this->elem_fields['id2_field'];
        /*
          формируем запрос к базе,
          поля типа words не брать из базы
          */
        if ($type == 'single') {
            $columns = $this->elem_fields['columns'];
        } else {
            $columns = $this->columns;
        }

        $as = array();

        foreach ($columns as $field => $val) {
            if ($val['type'] == 'words' || isset($val['value'])) {
                $words[$field] = $val['value'];
                continue;
            } elseif (isset($val['db_field']) && $val['db_field'] === false) {
                $words[$field] = $val['value'];
                continue;
            }

            if ($val['type'] == 'text_range') {
                $fields[$field . '0'] = $field . '0';
                $fields[$field . '1'] = $field . '1';
                continue;
            }

            // языковые настройки
            if ($type == 'single') {
                if (isset($val['sql_field']) && $val['sql_field']) {
                    $query = $val['sql_field'] . " AS " . $field;
                } elseif (isset($val['lang_select']) && $val['lang_select']) {
                    $query = "IF (" . $field . "_" . lang() . " <> '', " . $field . "_" . lang() . ", " . $field . "_" . LANG_DEFAULT . ") as " . $field;
                } else $query = $field;
            } elseif ($type == 'multi') {
                if (isset($val['sql_field']) && $val['sql_field']) {
                    $query = $val['sql_field'] . " AS " . $field;
                } elseif (isset($val['lang_select']) && $val['lang_select']) {
                    $query = "IF (" . $val['select'] . "_" . lang() . " <> '', " . $val['select'] . "_" . lang() . ", " . $val['select'] . "_" . LANG_DEFAULT . ") as " . isset($val['as']) ? $val['as'] : $val['select'];
                } else $query = $val['select'] . " as " . (isset($val['as']) ? $val['as'] : $val['select']);
            } elseif ($type == 'link') {
                if (isset($val['sql_field']) && $val['sql_field']) {
                    $query = '`t1`.' . $val['sql_field'] . " AS " . $field;
                } elseif (isset($val['lang_select']) && $val['lang_select']) {
                    $query = "IF (`t1`." . $val['select'] . "_" . lang() . " <> '', `t1`." . $val['select'] . "_" . lang() . ", `t1`." . $val['select'] . "_" . LANG_DEFAULT . ") as " . isset($val['as']) ? $val['as'] : $val['select'];
                } else $query = "`t1`." . $val['select'] . " as " . (isset($val['as']) ? $val['as'] : $val['select']);
                $as[] = isset($val['as']) ? $val['as'] : $val['select'];
            }
            //массив запросов полей
            $fields[$field] = $query;
        }

        if (!$fields) return false;

        $where = (empty($this->elem_where) ? '' : $this->elem_where . ' AND ') . $id_field . '=' . $id;

        // составляем запрос
        $sql = implode(',', $fields) . ' FROM ' . $this->elem_table . ' WHERE ' . $where;

        // проверяем тип формы, и выполняем соответствующий запрос
        if ($type == 'single') {
            $sql = 'SELECT ' . $sql;
            $row = sql_getRow($sql);
            foreach ($words as $k => $v) {
                $row[$k] = $v;
            }
        }
        elseif ($type == 'multi') {
            $sql = 'SELECT id, ' . $sql . ' ' . $this->order;
            $row = sql_getRows($sql, true);
        }
        elseif ($type == 'link') {
            if (!$this->elem_table_link) {
                echo "Не определена таблица для связей (переменная \$elem_table_link)";
            }
            else {
                $sql = "SELECT " . implode(',', $fields) . "
                FROM " . $this->elem_table . " AS t1, " . $this->elem_table_link . " AS t2
                WHERE t1.id=t2.{$id2_field} AND t2.{$id_field}={$id}
                " . $this->order . "";
                if (count($fields) < 3) {
                    $temp = sql_getRows($sql);
                    foreach ($temp as $_v) $row[$_v[$as[0]]] = array($as[0] => $_v[$as[0]], $as[1] => $_v[$as[1]]);
                } else {
                    $row = sql_getRows($sql, true);
                }
            }
        }

        //debug
        if ($this->sql) pr($sql);
        return $row;
    }

    /**
     * Вызывается перед сохранением в базу
     *
     * @param array $fld
     * @return array()
     */
    function ElemRedactBefore($fld) {
        if ($this->elem_type != 'single') return $fld;
        foreach ($fld as $key => $val) {
            if (!isset($this->elem_fields['columns'][$key]) || $this->elem_fields['columns'][$key]['type'] != 'autosuggest') continue;

            $table = $this->elem_fields['columns'][$key]['table'];
            $field = $this->elem_fields['columns'][$key]['field'];

            if ($this->elem_fields['columns'][$key]['langselect']) {
                $field = "IF ({$field}_" . lang() . " <> '', {$field}_" . lang() . ", {$field}_" . LANG_DEFAULT . ")";
            }

            if (!empty($val) && !is_numeric($val)) {

                if ($this->elem_fields['columns'][$key]['multiply'] === false) {
                    $id = sql_getValue("SELECT id FROM {$table} WHERE {$field} = '{$val}'");
                    if ($id) {
                        $fld[$key] = $id;
                    }
                    else if ($this->elem_fields['columns'][$key]['save_new'] === true && $val) {
                        $fld[$key] = sql_insert($table, array($field => $val));
                    }
                } else {
                    $values = explode(',', $val);
                    $array = array();
                    foreach ($values as $v) {
                        $v = trim($v);
                        if (!$v) continue;
                        $id = sql_getValue("SELECT id FROM {$table} WHERE {$field} = '{$v}'");
                        if ($id) {
                            $array[] = $id;
                        }
                        else if ($this->elem_fields['columns'][$key]['save_new'] === true) {
                            $array[] = sql_insert($table, array($field => $v));
                        }
                    }
                    if ($array) $fld[$key] = implode(',', $array);
                }
            }

            if ($this->elem_fields['columns'][$key]['isnull'] == true && !$fld[$key]) {
                $fld[$key] = 'NULL';
            }
        }
        return $fld;
    }

    /**
     * Вызывается после сохранения в БД
     * @param array() $fld
     * @param integer $id
     * @return array()
     */
    function ElemRedactAfter($fld, $id) {
        return $fld;
    }

    /**
     * Получение копии объекта
     *
     * @return array()
     */
    function getObject() {
        $row = array();
        $id = (int)get('id', 0);

        //поулчаем значения полей из сессии
        if ($this->elem_type == 'single') {
            $row['object'] = $this->getWC($id);
        } else {
            $elem_id = get('elem_id', '0');
            $row['object'] = $this->getWC($id, $elem_id);

            //проверяем, есть ли все необходимые поля
            //если нет, то подгружаем из базы
            foreach ($this->columns as $k => $v) {
                if (!isset($row['object'][$k])) {
                    $newObj = $this->getWCfromDb($elem_id, 'single', 'id');
                    foreach ($newObj as $key => $val) {
                        if (!isset($row['object'][$key])) $row['object'][$key] = $val;
                    }
                    break;
                }
            }
            $row['id'] = $id;
            $row['elem_id'] = &$elem_id;
        }
        return $row;
    }


    /**
     * Строит форму простого окна
     *
     * @return html
     */
    function ElemForm($tabname = "") {
        //устанавливаем title для окна мультиэлема
        if (isset($this->elem_fields['title'])) {
            $title = &$this->elem_fields['title'];
            $title = $this->str($title);
        }

        $obj = $this->getObject();

        //обрабатываем объект для построения формы
        foreach ($this->elem_fields['columns'] as $key => $val) {
            //определяем, бралось ли поле c AS или етсь ли пробелы в названии поля
            $asKey = explode(' ', $key);
            if ($asKey[0] == $key) {
                if (isset($obj['object'][$key])) {
                    if ($val['type'] == 'autosuggest') {
                        $field = $val['langselect'] ? "IF ({$val['field']}_" . lang() . " <> '', {$val['field']}_" . lang() . ", {$val['field']}_" . LANG_DEFAULT . ")" : $val['field'];
                        if (!$val['multiply']) {
                            $this->elem_fields['columns'][$key]['value'] = is_numeric($obj['object'][$key]) ? sql_getValue("SELECT {$field} FROM {$val['table']} WHERE id = '{$obj['object'][$key]}'") : $obj['object'][$key];
                        } else {
                            if ($obj['object'][$key]) {
                                $values = sql_getColumn("SELECT {$field} FROM {$val['table']} WHERE id IN ({$obj['object'][$key]})");
                            }
                            $this->elem_fields['columns'][$key]['value'] = $values ? implode(', ', $values) : $obj['object'][$key];
                        }
                    } else {
                        $this->elem_fields['columns'][$key]['value'] = $obj['object'][$key];
                    }
                } else if ($val['type'] == 'text_range') {
                    $this->elem_fields['columns'][$key]['value0'] = $obj['object'][$key . '0'];
                    $this->elem_fields['columns'][$key]['value1'] = $obj['object'][$key . '1'];
                }
            } else {
                if ($obj['object']) {
                    if (isset($obj['object'][$asKey[2]])) {

                        //переопределяем ключ массива
                        $temp_elem_fields = array();
                        $temp_elem_data = $this->elem_fields['columns'][$key];
                        foreach ($this->elem_fields['columns'] as $mkey => $mval) {
                            if ($mkey == $key) {
                                unset($this->elem_fields['columns'][$key]);
                                $temp_elem_fields[$asKey[2]] = $temp_elem_data;
                                continue;
                            }
                            $temp_elem_fields[$mkey] = $mval;
                        }
                        unset($this->elem_fields['columns']);
                        $this->elem_fields['columns'] = $temp_elem_fields;
                        $key = $asKey[2];

                        if (isset($obj['object'][$key])) {
                            $this->elem_fields['columns'][$key]['value'] = $obj['object'][$key];
                        }
                    }
                }
            }
            $this->elem_fields['columns'][$key]['tab'] = $tabname;

            $elem_id_str = ($tabname) ? '[' . $tabname . ']' : '';
            if ($elem_id_str) {
                $elem_id_str = (isset($obj['elem_id'])) ? $elem_id_str . '[' . $obj['elem_id'] . ']' : $elem_id_str . '';
            }

            $this->elem_fields['columns'][$key]['name'] = 'fld' . $elem_id_str . '[' . $key . ']';
            $this->elem_fields['columns'][$key]['display']['elem'] = 'fld' . (($tabname) ? '[' . $tabname . ']' : '') . '[' . $key . ']';
            if (isset($obj['elem_id'])) $this->elem_fields['columns'][$key]['display']['elem_id'] = $obj['elem_id'];
        }

        $obj['type'] = (isset($this->elem_fields['type'])) ? $this->elem_fields['type'] : $this->elem_type;

        if (isset($this->elem_fields['tmp'])) $obj['tmp'] = $this->elem_fields['tmp'];

        if ($this->debug) pr($this->elem_fields);

        $obj['obj'] = form($this->elem_fields, $this);

        $this->AddStrings($obj);
        return Parse($obj, OBJECT_EDITOR_MODULE . '/elems.tmpl');
    }

    /**
     * строим список с элемами
     *
     * @return html
     */
    function ElemList($tabname = "") {
        $id = (int)get('id', 0);
        $rows = array();
        $pid = $this->id;

        $this->table = $this->elem_table;
        // если редактируем

        $rows = $this->getObject();
        if (count($rows['object'])) {
            foreach ($rows['object'] AS $kobj => $vobj) {
                $rows['object'][$kobj]['tabname'] = $tabname;
                $rows['object'][$kobj]['onclick'] = $tabname . ".showSelectDiv(" . $vobj['id'] . ", this);";
            }
        }

        if ($this->elem_type == 'link') unset($this->list_buttons['create']);

        if (isset($this->list_buttons['create']['div_id'])) {
            $this->list_buttons['create']['div_id'] = 'tmp' . $tabname . $this->name . $id;
        }
        if (isset($this->list_buttons['delete']['div_id'])) {
            $this->list_buttons['delete']['div_id'] = 'tmp' . $tabname . $this->name . $id;
        }

        // Если указан класс для использования редактирования берем его
        // Если нет то получаем название класса из имени элема
        if (!empty($this->elem_class)) {
            $class_name = $this->elem_class;
        } else {
            $str = explode("_", $this->elem_name);
            $class_name = (isset($str[1])) ? $str[1] : '';
        }

        if (!elem($class_name . "/" . $class_name)) {
            die ("У multielem - " . $this->elem_name . " не найден класс для подключения создания/редактирования");
        }

        $_GET['limit'] = "-1";
        // Параметр is_fancy=1 обозначает что всплывающий фрейм организован на основе fancybox
        $table = array(
            'columns' => $this->columns,
            'dataset' => isset($rows['object']) ? $rows['object'] : array(),
            'params' => array(
                'id' => $id,
                'page' => $this->name,
                'tab' => $tabname,
                'class' => $class_name,
            ),

            'action' => 'editor.php',
            'click' => $this->click,
            'dblclick' => $this->dblclick,
            'tabframename' => 'tmp' . $tabname . $this->name . $id,
            'id_table_list_name' => 'tbl' . $tabname . $this->name . $id,
            'list_buttons' => $this->list_buttons,
            'script' => isset($this->script) ? $this->script : "",

            'src_frame' => "'./editor.php?page=" . $class_name . "&frame=view&pid=" . $pid . "&tabname=" . $tabname . "&page_name=" . $this->name . "&elem_name=" . $this->elem_name . "&is_fancy=1'",
            'tmpl' => 'table_no_form.tmpl',
            //'_sql' => 1,
        );
        //debug
        if ($this->sql) {
            $table['_sql'] = true;
        }

        require_once (core('list_table'));
        $data['table'] = list_table($table, $this);

        $data['window_size'] = $this->window_size;
        $this->AddStrings($data);
        $data['id'] = $id;
        $data['tab'] = $tabname;
        $data['elem_actions'] = $this->elem_actions;
        $data['thisname2'] = str_replace('/', '', $data['thisname']);
        $data['elem_type'] = $this->elem_type;

        if ($this->elem_type == 'link') {
            $data['autosuggest'] = array(
                'table' => $this->elem_table,
                'field' => @$this->autosuggest['field'],
                'where' => @$this->autosuggest['where'],
                'note' => @$this->autosuggest['note'],
                'lang_select' => LANG_SELECT
            );
        }

        return Parse($data, OBJECT_EDITOR_MODULE . '/list.tmpl');
    }

    /**
     * Блок редактирования данных элема
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     */
    /**
     * Редактирование элема
     *
     * @param int $id
     * @param array() $row
     * @param int $elem_id
     * @return int or string error
     */
    function ElemEdit($id, $row) {
        if (!$this->Allow('edit')) {
            return "У вас нет прав для редактирования этого ресурса.";
        }
        $this->table = $this->elem_table;

        $r = $this->preHandleChanges($id, $row);
        if (is_array($r) && isset($r['_error_text']) && !empty($r['_error_text'])) {
            // При проверке полей нашли ошибку, возвращаем ее
            return $r['_error_text'];
        }
        if (isset($r['fld'])) $row = $r['fld']; else $row = $r;

        // Проверяем многоязыковые поля
        if (defined('LANG_SELECT') && LANG_SELECT) {
            foreach ($row as $key => $val) {
                if (isset($this->elem_fields['columns'][$key]['lang_select']) && $this->elem_fields['columns'][$key]['lang_select']) {
                    $row[$key . "_" . lang()] = $val;
                    unset($row[$key]);
                }
            }
        }
        if (!$this->elem_xxx) {
            $req_fields = array();
            if (defined('LANG_SELECT') && LANG_SELECT) {
                foreach ($this->elem_req_fields as $key => $val) {
                    // Проверяем, это многоязыковое поле или нет
                    if (isset($this->elem_fields['columns'][$val]['lang_select']) && $this->elem_fields['columns'][$val]['lang_select']) {
                        $req_fields[] = $val . "_" . lang();
                    } else $req_fields[] = $val;
                }
            } else $req_fields = $this->elem_req_fields;

            $ret = $this->EditorCommit($req_fields, true, $row, $this->elem_fields['id_field'], $id);
            return $ret;

        } else return $this->MyCommit($row);
    }

    /**
     * Добавление в базу
     *
     * @param int $id идентификатор объекта
     * @param array $row данные для вставки
     * @return int or string error
     */
    function ElemAdd($id, $row, $subkey = '') {
        if (!$this->Allow('edit')) {
            return "У вас нет прав для для добавления элементов.";
        }
        $this->table = $this->elem_table;

        //запускаем обработчик при сохранении в базу
        $r = $this->ElemRedactBefore($row);
        if (is_array($r) && isset($r['_error_text']) && !empty($r['_error_text'])) {
            // При проверке полей нашли ошибку, возвращаем ее
            return $r['_error_text'];
        }
        if (isset($r['fld'])) $fld = $r['fld']; else $fld = $r;

        // Проверяем многоязыковые поля
        if (defined('LANG_SELECT') && LANG_SELECT) {
            foreach ($row as $key => $val) {
                if (isset($this->elem_fields['columns'][$key]['lang_select']) && $this->elem_fields['columns'][$key]['lang_select']) {
                    $row[$key . "_" . lang()] = $val;
                    unset($row[$key]);
                }
            }
        }

        if (!$this->elem_xxx) {
            $req_fields = array();
            if (defined('LANG_SELECT') && LANG_SELECT) {
                foreach ($this->elem_req_fields AS $key => $val) {
                    // Проверяем, это многоязыковое поле или нет
                    if (isset($this->elem_fields['columns'][$val]['lang_select']) && $this->elem_fields['columns'][$val]['lang_select']) {
                        $req_fields[] = $val . "_" . lang();
                    } else $req_fields[] = $val;
                }
            } else $req_fields = $this->elem_req_fields;

            if ($this->elem_type == 'multi') {
                $res = $this->EditorCommit($req_fields, false, $row, 'id', $row['id']);
                return $res;
            } elseif ($this->elem_type == 'single') {
                $ret = $this->EditorCommit($req_fields, true, $row, $this->elem_fields['id_field'], $id);
                return $ret;
            } elseif ($this->elem_type == 'link') {
                $save_table = $this->table;
                $this->table = $this->elem_table_link;
                $ret = $this->EditorCommit(
                    array($this->elem_fields['id2_field'], $this->elem_fields['id_field']),
                    true,
                    $row,
                    $this->elem_fields['id_field'],
                    $row['id']
                );
                $this->table = $save_table;
                return $ret;
            }
        } else return $this->MyCommit($row);
    }

    /**
     * Удаление элема
     *
     * @param int $id
     * @return bool or string error
     */
    function ElemDel($id, $pid = '') {
        if (!$this->Allow('delete')) {
            return "У вас нет прав для удаления этого ресурса.";
        }
        $this->table = $this->elem_table;

        //запускаем обработчик при сохранении в базу
        $r = $this->ElemRedactBefore($id);
        if (is_array($r) && isset($r['_error_text']) && !empty($r['_error_text'])) {
            // При проверке полей нашли ошибку, возвращаем ее
            return $r['_error_text'];
        }

        if (!$this->elem_xxx) {
            if ($this->elem_type == 'link') {
                $save_table = $this->table;
                $this->table = $this->elem_table_link;
                $res = $this->EditorDeleteItems($this->elem_fields['id2_field'], $id, $this->table, $this->elem_fields['id_field'] . "='" . $pid . "'", $this->obj_delete);
                $this->table = $save_table;
            } else {
                $res = $this->EditorDeleteItems('id', $id, $this->table, '', $this->obj_delete, 'pid');
            }
        } else {
            $res = $this->MyDeleteItems();
        }
        return $res;
    }

    /**
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     */

    /**
     * Функция вызывается каждый раз при редактировании(переключении вкладок и нажатии сохранение)
     * Только для single элемов
     *
     * @param int $id
     * @param array $fld
     */
    function preHandleChanges($id, $fld) {
        //определяем действие
        $edut = Registry::get('EdUtils');
        global $files_log;

        // проверка на модуль tree для root_id
        if ($edut->isTree($this)) {
            $edut->setRootID($id, $fld);
        }

        //обрабатываем дружественные файлы для закачки
        $edut->verifyFiles($fld, $this);

        //ресайзим файлы
        $edut->resizeFiles($fld, $this);
        //раскладываем файлы
        $edut->putFiles($fld, $this);

        //запускаем обработчик при сохранении в базу
        $r = $this->ElemRedactBefore($fld);
        if (is_array($r) && isset($r['_error_text']) && !empty($r['_error_text'])) {
            // При проверке полей нашли ошибку, возвращаем ее
            return $r;
        }
        if (isset($r['fld'])) $fld = $r['fld']; else $fld = $r;

        # Замена e-mail
        //замена <a href="mailto:mail@mail2.ru">vetal</a>
        //на <script>ShowMail("mail", "mail2", "ru", "vetal")</script>
        # Замена http
        //замена <a title="title" style="style" href="http://site.ru" target="target" >SITE</a>
        //на <script>ShowHTTP("http://site.ru", "SITE", "target", "style", "title")</script>
        foreach ($fld as $key => $value) {
            if (isset($this->elem_fields['columns'][$key]) && $this->elem_fields['columns'][$key]['db_field'] === false) {
                unset($fld[$key]);
                continue;
            }

            if (isset($this->elem_fields['columns'][$key]) && $this->elem_fields['columns'][$key]['type'] == 'words') {
                unset($fld[$key]);
                continue;
            }
            if (isset($this->elem_fields['columns'][$key]['type']) && $this->elem_fields['columns'][$key]['type'] == 'fck') {
                $fld[$key] = preg_replace(
                    '~<A href="mailto:([\w\-]+)@([\w\-\.]+)\.(\w{2,4})">(\S+@\S+)?([^<]+)?</A>~i',
                    '<script>ShowMail("$1", "$2", "$3", "$5")</script>', $fld[$key]);

                if (REPLACE_HTTP) $fld[$key] = preg_replace(
                    '~<A(\s+title="([^"]*)")*(\s+style="([^"]*)")*\s+href="(http:[^"]+)"(\s+target="([^"]*)")*([^>])*>([^<]+)</A>~i',
                    '<script>ShowHTTP("$5", "$9", "$7", "$4", "$2")</script>', $fld[$key]);
            }
            // htmlspecialchars для полей типа: текст
            if (isset($this->elem_fields['columns'][$key]['type']) && $this->elem_fields['columns'][$key]['type'] == 'text') {
                $value = str_replace("&", "=+=+=+=", $value);
                $fld[$key] = htmlspecialchars($value);
                $fld[$key] = str_replace("=+=+=+=", "&", $fld[$key]);
            }
        }

        return $fld;
    }


    /**
     * получения объекта из БД
     *
     * @param unknown_type $id
     * @param unknown_type $elem_id
     * @return unknown
     */
    function getWC($id = 0, $elem_id = 0) {
        $rows = $this->getWCfromDb($id);

        // @todo ??? возможно надо переделать
        if (!$elem_id) {
            return $rows;
        }

        $row = array();
        if (isset($rows[$elem_id])) {
            $row = $rows[$elem_id];
        }
        return $row;
    }

}


?>