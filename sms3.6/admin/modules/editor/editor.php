<?php

class TObjectEditor extends TTable
{

    var $tabs = array();

    //--------------------------------------------------------------------
    // конструктор
    function TObjectEditor() {
        // Конструктор от TTable
        TTable::TTable();

        // Описание кнопок справа в окне
        $this->window_icons = array();
        $this->window_icons['show']['help'] = &$GLOBALS['window_icons']['help'];
        $this->window_icons['show']['help']['onclick'] = 'return showHelp(\'' . $GLOBALS['page'] . '.editform\');';
        if (isset($_POST['do']) && $_POST['do'] == 'changePriority') {
            return $this->changePriority($_POST);
        }
        if (isset($_POST['do']) && $_POST['do'] == 'changeLinkVisible') {
            return $this->changeLinkVisible($_POST);
        }
        if (isset($_GET['do']) && $_GET['do'] == 'copyobjects') {
            return $this->copyobjects($_GET);
        }
    }

    //--------------------------------------------------------------------

    // устанавливаем подмодуль, с которым работает редактор
    function setup($thisname, $submodule) {
        $this->name = $thisname;
        $this->submodule = $submodule;
    }

    //
    function Frame() {
        global $menu, $sections, $page, $user;

        $data = array(
            'page' => $page,
            'act' => $this->editForm(),
        );

        if (isset($_POST['do']) && $_POST['do'] == "save") {
            $result = $this->saveObject();
            echo $result;
            exit; // чтобы не вызывался парсинг во фрейме
        } elseif (isset($_POST['do']) && $_POST['do'] == "apply") {
            $result = $this->saveObject(true);
            echo $result;
            exit; // чтобы не вызывался парсинг во фрейме
        }

        return $this->Parse($data, 'ed.index.tmpl');
    }

    //--------------------------------------------------------------------
    function show() {
        global $menu, $sections, $page, $user;
        $data = array(
            'query' => $GLOBALS['_SERVER']['QUERY_STRING'],
            'STR_CLOSE' => $this->str('close'),

            'menu' => $menu->getMenu($sections),
            'page' => $page,
            'act' => $this->editForm(),
            'login' => $user['fullname'] ? $user['fullname'] : $user['login'],
            'STR_LOGOUT' => str('logout', 'tmenu'),
            'title' => $this->object->GetTitle(),
        );

        if (isset($_POST['do']) && $_POST['do'] == "save") {
            $result = $this->saveObject();
            echo $result;
            exit; // чтобы не вызывался парсинг во фрейме
        } elseif (isset($_POST['do']) && $_POST['do'] == "apply") {
            $result = $this->saveObject(true);
            echo $result;
            exit; // чтобы не вызывался парсинг во фрейме
        }

        return Parse($data, 'index.tmpl');
    }

    // окно формы редактирования
    function editForm() {
        global $actions, $str, $intlang;
        $page = get('page', '', 'gp');
        $id = get('id', 0, 'pg');

        // загружаем список tab и для каждого вызываем loadTab
        include_once (elem($page . "/" . $page));

        $class_name = 'T' . ucfirst($page);
        $this->object = & Registry::get($class_name);

        $act_title = $str[strtolower($class_name)]['title'];
        if (isset($str[strtolower($class_name)]['title_editform'])) {
            $act_title = $str[strtolower($class_name)]['title'] = $str[strtolower($class_name)]['title_editform'];
        }

        // Если это форма редактирования открыта через фрейм, то поищем спец elem для класса
        if (isset($_GET['frame']) && $_GET['frame'] == 'view' && elem($_GET['page'] . "/elem_main_" . $_GET['page_name'])) {
            $this->tabs['tab0'] = array(
                'display' => array(
                    'ru' => 'Основное',
                    'en' => 'Main',
                ),
                'conf' => array(
                    'elem' => "elem_main_" . $_GET['page_name'],
                    'elem_type' => 'single',
                    'target' => 'cnt',
                ),
            );
        } else {
            $this->getTabs();
        }

        // id записи
        $data['id'] = $id;
        // submodule
        $data['thisname'] = $data['page'] = $this->submodule;
        $data['thisname'] = str_replace('/', '', $data['thisname']); // это нужно только для вложенных модулей
        $data['tablename'] = $this->table;
        $data['last'] = get('last', '', 'gp');

        global $oed_vars;
        if (isset($oed_vars['first'])) {
            $data['first'] = $oed_vars['first'];
        }
        if (isset($oed_vars['last'])) {
            $data['last'] = $oed_vars['last'];
        }

        // контент вкладок
        foreach ($this->tabs AS $key => $value) {
            if (isset($_GET['frame']) && $_GET['frame'] == 'view') {
                // Для форм, открытых в fancybox, надо оставить только основной таб
                // Возможно в будущем научимся и другие вкладки сохранять
                if (($value['conf']['elem'] != 'elem_main') && (strpos($value['conf']['elem'], 'elem_main_') === false)) {
                    unset($this->tabs[$key]);
                    continue;
                }
            }

            $this->loadTab($key);

            $tab_cfg = $this->getTabCfg($value);
            if ($value['conf']['elem_type'] != 'single') {
                $data['noform'] = true;
            }
            $data['form_content'][$key]['content'] = $this->ShowTab($key);
            $data['form_content'][$key]['name'] = isset($value['display'][int_lang()]) ? $value['display'][int_lang()] : (isset($this->object->elements_titles[$value['conf']['elem']]) ? $this->object->elements_titles[$value['conf']['elem']][int_langId()] : $value['conf']['elem']);
            $data['form_content'][$key]['select'] = ($key == 'tab0') ? 'select' : '';
        }

        // Если это дерево разделов, то отобразим путь (хлебные крошки)
        if ($page == "tree") {
            $pid = get('pid', 0, 'pg');
            $data['path'] = $this->object->GetPath(($id ? $id : $pid));
        }

        // Сама форма
        $content = $this->Parse($data, 'ed.cnt.tmpl');
        $cnt = Parse(array('content' => $content), 'cnt.tmpl');

        // Кнопки
        $actions = array(
            'title' => $act_title[$intlang],
            'window_icons' => $this->WindowIcons(),
            'actions_bot' => $this->GetActions($actions[$this->object->name . '.editform'], 'bot'),
            'cnt' => $cnt,
        );

        return Parse($actions, 'act.tmpl');
    }

    // подгружает файл с элемом и агррегирует класс элема в объект субмодуля
    function loadTab($tabname) {
        global $str;
        $tab_cfg = $this->getTabCfg($tabname);

        if (!$tab_cfg) {
            return false;
        }
        $elem = $tab_cfg['conf']['elem'];

        list(, $tmp) = explode('_', $elem);
        include_once (elem($this->submodule . '/' . $elem));
        $class_name = 'T' . ucfirst($tmp) . 'Element';

        $this->elem_object[$tabname] = & Registry::get($class_name);
        $this->elem_object[$tabname]->tabname = $tabname;
        $this->elem_object[$tabname]->id = get('id', 0, 'pg');
        $this->elem_object[$tabname]->page = get('page', 0, 'pg');
        $this->elem_object[$tabname]->name = $this->submodule;

        $this->elem_object[$tabname]->ElemInit();

        //проверка загружается ли страница во фрейме, если да то это возможно вкладка через элем
        //и возможно у этого элема указана другая таблица в БД, если да используем её (ту что указана в элеме)
        $page_name = get('page_name', '', 'gp');
        $elem_name = get('elem_name', '', 'gp');
        $is_fancy = get('is_fancy', '', 'gp');

        if ($is_fancy && $elem_name) {

            // это подключение нужного елема
            list(, $tmp) = explode('_', $elem_name);

            if (is_file(elem($page_name . '/' . $elem_name))) {
                include_once (elem($page_name . '/' . $elem_name));
                $class_elem_name = 'T' . ucfirst($tmp) . 'Element';
                $multi_elem_object = & Registry::get($class_elem_name);
                $multi_elem_object->ElemInit();

                if ($multi_elem_object->elem_type != 'link') {
                    $table_elem = $multi_elem_object->elem_table;
                    if ($table_elem) {
                        //нашлась таблица, заменим таблицу класса на ту что указана в элеме
                        $this->elem_object[$tabname]->elem_table = $table_elem;
                    }
                }

                // другие замены
                if (@$multi_elem_object->elem_fields['folder']) {
                    // папка для закачивания фото
                    $this->elem_object[$tabname]->elem_fields['folder'] = $multi_elem_object->elem_fields['folder'];
                }
            }
        }

        if (!empty($this->elem_object[$tabname]->str)) {
            $str[get_class_name($this)] = array_merge($str[get_class_name($this)], $this->elem_object[$tabname]->str);
        }
    }

    function ShowTab($tabname = '', $method = '') {
        $tab = $this->getTabCfg($tabname);
        if (!$tab) {
            return '404. Tab "' . $tabname . '" not found';
        }

        if (empty($method)) {
            if ($tab['conf']['elem_type'] != 'single') {
                return $this->elem_object[$tabname]->ElemList($tabname);
            }
            return $this->elem_object[$tabname]->ElemForm($tabname);
        }
        return call_user_func(array(&$this->elem_object[$tabname], $method));
    }

    function getTabCfg($tabname) {
        if (empty($this->tabs[$tabname]) || !is_array($this->tabs[$tabname])) {
            return false;
        }
        return $this->tabs[$tabname];
    }

    function getTabs() {
        $page = get('page', '', 'gp');
        if (!$page) {
            return $this->tabs;
        }
        $elem_main = "elem_main";

        if ($page == "tree") {
            $id = get('id', 0, 'gp');

            if ((int)$id) {
                $bt = debug_backtrace();
                $file = isset($bt[1]['file']) ? $bt[1]['file'] : '';
                $line = isset($bt[1]['line']) ? $bt[1]['line'] : '';

                $sql = "SELECT * FROM " . $this->object->table . " WHERE id=" . (int)$id;
                $row = sql_getRow($sql, $file, $line);
            } else {
                $row['type'] = get('type', 'text', 'gp');
                $row['root_id'] = isset($_GET['pid']) && $_GET['pid'] > 0 ? sql_getValue("SELECT root_id FROM " . $this->object->table . " WHERE id=" . (int)$_GET['pid']) : getMainRootID();
            }
            $elems = $this->getDifTabs($row);
            array_unshift($elems, $elem_main);
        } else {
            $elems = (!empty($this->object->elements)) ? $this->object->elements : array();
            array_unshift($elems, $elem_main);
        }

        foreach ($elems as $k => $v) {
            list(, $tmp) = explode('_', $v);
            include_once (elem($this->submodule . '/' . $v));
            $class_elem_name = 'T' . ucfirst($tmp) . 'Element';
            $elem_object = & Registry::get($class_elem_name);

            $this->tabs['tab' . $k] = array(
                'display' => array( // elements conf
                    'ru' => ($v == $elem_main) ? 'Основное' : $GLOBALS['cfg']['elements'][$v][0],
                    'en' => ($v == $elem_main) ? 'Main' : $GLOBALS['cfg']['elements'][$v][1],
                ),
                'conf' => array(
                    'elem' => $v,
                    'elem_type' => $elem_object->elem_type,
                    'target' => 'cnt', // cnt | act
                ),
            );
        }
        return $this->tabs;
    }

    //------------------------------------------------------------

    function getDifTabs($row) {
        $elems = $GLOBALS['cfg']['types'][$row['root_id']][$row['type']]['elements'];
        return $elems;
    }

    // строит панель actions
    function GetActions($actions, $pos = '') {
        $frame = get("is_fancy", "");
        if (!empty($pos)) {
            $pos = '_' . $pos;
        }
        $data = array();
        foreach ($actions as $key => $val) {
            // Чтобы у fancybox закрывалось окно а не отображался сайт
            if ($frame && $key == 'cancel') {
                $val['onclick'] = "window.top.parent.$.fancybox.close();";
            }

            $data['actions'][] = array(
                'title' => utf($val['title'][int_lang()]),
                'show_title' => isset($val['show_title']) ? $val['show_title'] : false,
                'onclick' => $val['onclick'],
                'img' => $val['img'],
                'hint' => isset($val['hint']) ? utf($val['hint'][int_lang()]) : '',
                'display' => $val['display'],
                'ondrop' => isset($val['ondrop']) ? $val['ondrop'] : '',
                'ondragenter' => isset($val['ondrop']) ? 'window.event.dataTransfer.dropEffect = \'link\';window.event.returnValue = false' : '',
                'ondragleave' => isset($val['ondrop']) ? 'window.event.returnValue = false' : '',
                'ondragover' => isset($val['ondrop']) ? 'window.event.returnValue = false' : '',
            );
        }
        return $this->Parse($data, 'actions' . $pos . '.tmpl');
    }

    // сохраняет данные и закрывает редактор
    function saveObject($close_wind = false) {
        $id = (int)get('id', 0, 'pg');
        $fld = array_key_exists('fld', $_POST) ? $_POST['fld'] : array();

        // сохранение в базу
        $save = $this->save($id, $fld, $close_wind);

        return $save;
    }

    /**
     * сохраняет данные в БД, вызывая обработчики из элемов
     * @param $id
     * @param $fld
     * @return string
     */
    function save($id, $fld, $close_wind = false) {
        $error = '';
        $error_tab = false;
        $pid = 0; // ID основной сущности

        $reload = false;

        // формируем массив всех вкладок
        $tabs = $this->tabs;

        // 1. Проверка на ошибки
        // ...
        // 2. Если есть ошибки, загружаем нужную вкладку и выходим
        // ...
        // 3. Цикл по всем вкладкам
        // запуск транзакции
        sql_query('BEGIN');

        foreach ($tabs as $k => $v) {
            if ($error) {
                continue;
            }
            $this->tab = $k;
            if ($this->elem_object[$k]->nosave) {
                continue;
            }

            if ($v['conf']['elem_type'] == 'multi') { // MultiElem
                $res = $this->saveElemMulti($id, $pid, $k, $fld[$k]);
                if ($res !== true) {
                    $error_tab = $k;
                    $error = $res;
                    break;
                }
            }
            elseif ($v['conf']['elem_type'] == 'single') { // SingleElem

                if ($this->elem_object[$k]->elem_fields['id_field'] != "id") {
                    $fld[$k][$this->elem_object[$k]->elem_fields['id_field']] = $pid;
                }

                $res = $this->saveElemSingle($id, $k, $fld[$k]);
                if (!is_int($res)) {
                    $error_tab = $k;
                    $error = $res;
                }
                elseif ($k == 'tab0') {
                    // при создании нового объекта мы получаем новый идентификатор,
                    // который должны использовать в дальнейшем, как pid для элемов
                    $pid = $res;
                    $_POST[$this->elem_object[$k]->elem_fields['id_field']] = $pid;
                    // пропишем получившийся id от основной вкладке, для дальнейшего использования
                    $id = $res;
                }
            }
            elseif ($v['conf']['elem_type'] == 'link') { // LinkElem
                $res = $this->saveElemLink($id, $pid, $k, $fld[$k]);
                if ($res !== true) {
                    $error_tab = $k;
                    $error = $res;
                    break;
                }
                if (isset($fld[$k]['ids']) && $fld[$k]['ids']) {
                    $reload = true;
                }
            }

            // Вызовем функцию обработки данных после получения основного id-шника
            $after = $this->elem_object[$k]->ElemRedactAfter($fld[$k], $pid);
            if (is_array($after) && isset($after['error']) && !empty($after['error'])) {
                $error_tab = $k;
                $error = $after['error'];
                break;
            }

            if ($pid && in_array($v['conf']['elem_type'], array('multi', 'link'))) {
                // Пересчет приоритетов
                $id_field = $this->elem_object[$k]->elem_type == 'multi' ? 'id' : $this->elem_object[$k]->elem_fields['id2_field'];
                $this->EnumPriority(
                    $pid,
                    $this->elem_object[$k]->elem_fields['id_field'],
                    $v['conf']['elem_type'] == 'multi' ? $this->elem_object[$k]->elem_table : $this->elem_object[$k]->elem_table_link,
                    $id_field,
                    isset($this->elem_object[$k]->elem_fields['priority_field']) ? $this->elem_object[$k]->elem_fields['priority_field'] : 'priority'
                );
            }

        }

        // не все ок
        if ($error_tab) {
            sql_query('ROLLBACK');
            return "
			<script type='text/javascript' src='js/jquery-1.5.2.min.js'></script>
			<script>window.parent.openTab('$error_tab'); alert('" . addslashes($error) . "');</script>";
        }

        // все ок
        // закрытие транзакции
        sql_query('COMMIT');

        //определим откуда сохранение - обычная форма или во всплывающем фрейме
        $frame = get('frame', '', 'pg');
        if ($frame == 'view') {
            // здесь мы оказываемся когда во вкладке мультиэлема используем кнопку 'создать'
            // открывается форма добавления/редактирования сущности и мы нажимаем кнопку сохранить
            // если мы здесь, то нам надо добавить hidden поле и дорисовать таблицу
            return $this->afterSaveInFrame((int)get('id', 0, 'g'), $pid);
        }

        $page = get('page', '', 'p');
        if ((int)get('id', 0, 'g') && $close_wind) {
            return "<script>alert('Изменения сохранены'); " . ($reload ? "top.location.reload();" : "") . "</script>";
        } else {
            return "<script>parent.location.href='/admin/?page=" . $page . ($page == 'tree' ? "&id=" . $pid : "") . "';</script>";
        }
    }
    //--------------------------------------------------------------------------------------

    /**
     * Сохранение простого элема (сущность не имеет связи с другими сущностями)
     * @param int $id - ID сохраняемого объекта
     * @param string $tab - название закладки
     * @param array $fld - данные для сохранения
     * @return mixed
     */
    function saveElemSingle($id, $tab, $fld) {
        $res = $this->elem_object[$tab]->ElemEdit($id, $fld);
        return $res;
    }

    /**
     * Сохранение мульти-элема: удаление, добавление или простое сохранение
     * Связь через колонку id_field, которая находится в одной из таблиц с сущностями, а не в отдельной таблице
     * @param int $id - ID сохраняемого объекта
     * @param int $pid - ID родительского объекта
     * @param string $tab - название закладки
     * @param array $fld - данные для сохранения
     * @return mixed
     */
    function saveElemMulti($id, $pid, $tab, $fld) {

        // Если есть записи для удаления
        if (isset($fld['del_ids'])) {
            $to_del = $fld['del_ids'];
            $this->elem_object[$tab]->obj_delete = false;
            foreach ($to_del as $del_id) {
                $res = $this->elem_object[$tab]->ElemDel($del_id);
                if (!is_bool($res)) { // ошибка
                    return $res;
                }
            }
        }

        // Если есть для сохранения id-шники то их надо добавить
        if (isset($fld['ids'])) {
            $to_edit = $fld['ids'];
            foreach ($to_edit as $edit_id) {
                $row_ids = sql_getRow("SELECT * FROM " . $this->elem_object[$tab]->elem_table . " WHERE id='" . $edit_id . "'");

                // Отметим NULL поля для нормального сохранения в innodb
                foreach ($row_ids AS $k_r_ids => $v_r_ids) {
                    if (is_null($v_r_ids)) {
                        $row_ids[$k_r_ids] = 'NULL';
                    }
                }

                $row_ids[$this->elem_object[$tab]->elem_fields['id_field']] = $pid;

                $res = $this->elem_object[$tab]->ElemAdd($id, $row_ids);
                if (!is_int($res)) { // ошибка
                    return $res;
                }
            }
        }

        return true;
    }

    /**
     * Сохранение линк-элема: удаление, добавление или простое сохранение
     * Связь через колонку id_field, которая находится в одной из таблиц с сущностями, а не в отдельной таблице
     * @param int $id - ID сохраняемого объекта
     * @param int $pid - ID родительского объекта
     * @param string $tab - название закладки
     * @param array $fld - данные для сохранения
     * @return mixed
     */
    function saveElemLink($id, $pid, $tab, $fld) {
        // Если есть записи для удаления
        if (isset($fld['del_ids'])) {
            $to_del = $fld['del_ids'];
            $this->elem_object[$tab]->obj_delete = false;
            foreach ($to_del as $del_id) {
                $res = $this->elem_object[$tab]->ElemDel($del_id, $id);
                if (!is_bool($res)) { // ошибка
                    return $res;
                }
            }
        }

        // Если есть для сохранения id-шники то их надо добавить
        if (isset($fld['ids'])) {
            $to_edit = $fld['ids'];
            foreach ($to_edit as $edit_id) {
                $res = $this->elem_object[$tab]->ElemAdd($id, array(
                    $this->elem_object[$tab]->elem_fields['id2_field'] => $edit_id,
                    $this->elem_object[$tab]->elem_fields['id_field'] => $pid
                ));
                if (!is_int($res)) { // ошибка
                    return $res;
                }
            }
        }

        return true;
    }

    /**
     * Действия после сохранения формы во всплывающем фрейме (надо дорисовать/обновить таблицу)
     * @param $id
     * @param $pid
     * @return string
     */
    function afterSaveInFrame($id, $pid) {

        if (!(int)$pid || !($id == 0 || $id == $pid)) {
            return "<script type='text/javascript'>alert('Проблемы с получением идентификатора.');</script>";
        }

        //Для рисования строки у таблицы нам надо знать какие поля должны быть выбраны,
        //для этого надо знать какой модуль и какой элем
        $tabname = get('tabname', '', 'gp');
        $page_name = get('page_name', '', 'gp');
        $elem_name = get('elem_name', '', 'gp');
        $object_id = get('pid', 0, 'gp');
        $is_fancy = get('is_fancy', '', 'gp');

        // это подключение нужного елема
        list(, $tmp) = explode('_', $elem_name);

        if (!is_file(elem($page_name . '/' . $elem_name))) {
            return "<script>alert('Не найден elem - " . $elem_name . " у класса " . $page_name . "');</script>";
        }

        include_once (elem($page_name . '/' . $elem_name));
        $class_elem_name = 'T' . ucfirst($tmp) . 'Element';
        $multi_elem_object = & Registry::get($class_elem_name);
        $multi_elem_object->ElemInit();
        $elem_object = $multi_elem_object;
        $table_columns = $multi_elem_object->columns;

        // Выберем из базы нужные поля у только что сохраненного объекта
        $fields = "";
        foreach ($table_columns AS $k => $v) {
            $fields .= $v['select'] . ",";
        }
        $fields = substr($fields, 0, -1);

        $rows = sql_getRows("SELECT id," . $fields . " FROM " . $multi_elem_object->elem_table . " WHERE id=" . $pid);
        if (count($rows)) {
            foreach ($rows AS $kobj => $vobj) {
                $rows[$kobj]['tabname'] = $tabname;
                $rows[$kobj]['onclick'] = $tabname . ".showSelectDiv(" . $vobj['id'] . ", this);";
            }
        }

        //Вызовем обработчик данных таблицы
        require_once (core('table.lib'));
        $list_table = new List_Table;
        $table_columns = $list_table->getAllOptionsParams($table_columns);

        $table = $elem_object->elem_type == 'multi' ? $elem_object->elem_table : $elem_object->elem_table_link;
        $id_field = $elem_object->elem_type == 'multi' ? 'id' : $elem_object->elem_fields['id2_field'];
        $parent = $pid ? (int)sql_getValue("SELECT " . $elem_object->elem_fields['id_field'] . " FROM " . $table . " WHERE " . $id_field . "=" . $pid) : 0;

        foreach ($table_columns AS $k => $v) {
            if ($v['type'] == 'edit_priority' && !$parent) {
                $table_columns[$k]['type'] = 'hidden';
            }
            $table_columns[$k]['this'] = $elem_object;
            $table_columns[$k]['this']->page = $page_name;
            $table_columns[$k]['this']->tabname = $tabname;
        }

        $tableRows = $list_table->getRowsHtml(array(
            'columns' => $table_columns,
            'params' => array('page' => $page_name, 'do' => 'show'),
            'dblclick' => 'editItem(id)',
            'click' => 'ID=cb.value',
        ), $rows, -1, array('page' => $page_name, 'do' => 'show'), $elem_object);

        //Получим верстку строк, таблицы
        $htmlRows = Parse($tableRows + array('id_table_list_name' => 'tbl' . $tabname . $page_name . $object_id), 'table_only_rows.tmpl');

        // Определяем как будем закрывать фрейм
        if ($is_fancy) {
            $ret = "
            <script type='text/javascript'>
                window.top.parent.$.fancybox.close();
            </script>";
        } else {
            $ret = "
            <script type='text/javascript'>
                $('#$id_frame', top.document).css('visibility','hidden');
                $('#$id_frame', top.document).css('width','0');
                $('#$id_frame', top.document).css('height','0');
            </script>";
        }

        if ($id == $pid) {
            // Значит мы редактировали запись и надо перерисовать строку
            $id_table_row = $tabname . "_tbl" . $tabname . $page_name . $object_id . "_" . $pid;
            $id_frame = 'tmp' . $tabname . $page_name . $object_id;

            return "
            <script type='text/javascript' src='./js/jquery-1.5.2.min.js'></script>
            <script type='text/javascript'>
                var new_row = $('" . mysql_escape_string($htmlRows) . "');
                $('#$id_table_row', top.document).html($(new_row).html());
            </script>" . $ret;

        }
        else {
            // Значит мы создали новую запись и надо дорисовать строку у таблицы
            $hidden = '<input type="hidden" value="' . $pid . '" name="fld' . (($tabname) ? "\\[" . $tabname . "\\]" : "") . '\\[ids\\]\\[\\]" id="fld' . (($tabname) ? "\\[" . $tabname . "\\]" : "") . '\\[ids\\]\\[' . $pid . '\\]">';
            $id_table = 'tbl' . $tabname . $page_name . $object_id;
            $id_frame = 'tmp' . $tabname . $page_name . $object_id;

            return "
            <script type='text/javascript' src='./js/jquery-1.5.2.min.js'></script>
            <script type='text/javascript'>$('#editform', top.document).append('$hidden');</script>
            <script type='text/javascript'>$('#$id_table', top.document).append('" . mysql_escape_string($htmlRows) . "');</script>
            " . $ret;
        }
    }

    /**
     * Изменение порядка следования элементов в multi и link элемах
     * @param $data
     * @return void
     */
    function changePriority($data) {

        $page_name = get('page_name', '', 'p');
        $elem_name = get('elem_name', '', 'p');

        // инициализация элема
        $elem_object = $this->initElemByParams($page_name, $elem_name);
        if (is_string($elem_object)) {
            echo json_encode(array('error' => iconv('windows-1251', 'utf-8', $elem_object)));
            die();
        }
        if (!$elem_object->elem_fields['id_field']) {
            echo json_encode(array('error' => iconv('windows-1251', 'utf-8', "Не определен параметр id_field")));
            die();
        }

        // ищем таблицу
        $table = $elem_object->elem_type == 'multi' ? $elem_object->elem_table : $elem_object->elem_table_link;
        if (!$table) {
            echo json_encode(array('error' => iconv('windows-1251', 'utf-8', "Не определена таблица")));
            die();
        }

        $id = (int)get('id', 0, 'p');
        if (!$id) {
            echo json_encode(array('error' => iconv('windows-1251', 'utf-8', "Неизвестный параметр: id")));
            die();
        }
        $direction = get('direction', 0, 'p');
        if (!in_array($direction, array(1, -1))) {
            echo json_encode(array('error' => iconv('windows-1251', 'utf-8', "Неизвестный параметр: direction")));
            die();
        }
        $tabname = get('tab', '', 'p');
        if (!$tabname) {
            echo json_encode(array('error' => iconv('windows-1251', 'utf-8', "Неизвестный параметр: tabname")));
            die();
        }

        // Родительский id, внутри которого идет нумерация priority
        $id_field = $elem_object->elem_type == 'multi' ? 'id' : $elem_object->elem_fields['id2_field'];
        $pid = (int)sql_getValue("SELECT " . $elem_object->elem_fields['id_field'] . " FROM " . $table . " WHERE " . $id_field . "=" . $id);
        if (!$pid) {
            echo json_encode(array('error' => iconv('windows-1251', 'utf-8', "Не определено значение для " . $elem_object->elem_fields['id_field'])));
            die();
        }

        $priority_field = isset($elem_object->elem_fields['priority_field']) ? $elem_object->elem_fields['priority_field'] : 'priority';

        // Исходный приоритет
        $where = "";
        if ($elem_object->elem_type == 'link') {
            $where = " AND " . $elem_object->elem_fields['id_field'] . "=" . $pid;
        }
        $priority = sql_getValue("SELECT " . $priority_field . " FROM " . $table . " WHERE " . $id_field . "=" . $id . $where);
        if ($priority <= 1 && $direction < 0) {
            // некуда уменьшать приоритет
        } elseif ($priority >= sql_getValue("SELECT MAX(" . $priority_field . ") FROM " . $table . " WHERE " . $elem_object->elem_fields['id_field'] . "=" . $pid) && $direction > 0) {
            // некуда увеличивать приоритет
        }
        else {

            // Получаем id элемента для замены
            $trg_id = sql_getValue("SELECT " . $id_field . " FROM " . $table . " WHERE " . $priority_field . "=" . ($priority + $direction) . " AND " . $elem_object->elem_fields['id_field'] . "=" . $pid . " AND " . $id_field . "<>" . $id);
            if (!$trg_id) {
                // Делаем автонумерацию, если ошибка
                $this->EnumPriority($pid, $elem_object->elem_fields['id_field'], $table, $id_field, $priority_field);
                $trg_id = sql_getValue("SELECT " . $id_field . " FROM " . $table . " WHERE " . $priority_field . "=" . $priority . " AND " . $elem_object->elem_fields['id_field'] . "=" . $pid);
                // Если нумерация не помогла, выдаем ошибку.
                if (!$trg_id) {
                    echo json_encode(array('error' => iconv('windows-1251', 'utf-8', "Не определено значение для trg_id")));
                    die();
                }
            }

            // Заменяем priority
            sql_query("UPDATE " . $table . " SET " . $priority_field . "=" . $priority . " WHERE " . $id_field . "=" . $trg_id . $where);
            sql_query("UPDATE " . $table . " SET " . $priority_field . "=" . ($priority + $direction) . " WHERE " . $id_field . "=" . $id . $where);
        }

        $this->EnumPriority($pid, $elem_object->elem_fields['id_field'], $table, $id_field, $priority_field);

        //Вызовем обработчик данных таблицы
        require_once (core('table.lib'));
        $list_table = new List_Table;
        $table_columns = $list_table->getAllOptionsParams($elem_object->columns);

        foreach ($table_columns AS $k => $v) {
            $table_columns[$k]['this'] = $elem_object;
            $table_columns[$k]['this']->page = $page_name;
        }

        $rows = $elem_object->getWCfromDb($pid);

        foreach ($rows as $k => $v) {
            $rows[$k]['tabname'] = $tabname;
            $rows[$k]['onclick'] = $tabname . ".showSelectDiv(" . $v['id'] . ", this);";
        }

        $elem_object->tabname = $tabname;
        $ret = array();
        foreach ($rows as $row) {
            $tableRows = $list_table->getRowsHtml(array(
                'columns' => $table_columns
            ), array($row), -1, array(), $elem_object);
            //Получим верстку строк, таблицы
            $ret[] = array(
                iconv('windows-1251', 'utf-8', Parse($tableRows + array('id_table_list_name' => 'tbl' . $tabname . $page_name . $pid), 'table_only_rows.tmpl'))
            );
        }

        ob_clean();
        echo json_encode(array('ret' => $ret));
        die();
    }

    /**
     * Расстановка priority
     * @param $pid
     * @param $col_name
     * @param $table
     */
    function EnumPriority($pid, $col_name, $table, $col_name2 = 'id', $priority_field = 'priority') {
        $counter = 1;
        $rows = sql_getColumn("SELECT " . $col_name2 . " FROM " . $table . " WHERE " . $col_name . "=" . $pid . " ORDER BY IF(" . $priority_field . ">0, " . $priority_field . ", 999999)");
        foreach ($rows as $id) {
            $id = (int)$id;
            if (!$id) continue;
            sql_query("UPDATE " . $table . " SET " . $priority_field . "=" . $counter . " WHERE " . $col_name2 . "=" . $id);
            $counter++;
        }
    }

    /**
     * Изменение visible у элементов в multi и link элемах
     * @param $data
     */
    function changeLinkVisible($data) {
        $page_name = get('page_name', '', 'p');
        $elem_name = get('elem_name', '', 'p');

        // инициализация элема
        $elem_object = $this->initElemByParams($page_name, $elem_name);
        if (is_string($elem_object)) {
            echo json_encode(array('error' => iconv('windows-1251', 'utf-8', $elem_object)));
            die();
        }
        if (!$elem_object->elem_fields['id_field']) {
            echo json_encode(array('error' => iconv('windows-1251', 'utf-8', "Не определен параметр id_field")));
            die();
        }

        // ищем таблицу
        $table = $elem_object->elem_type == 'multi' ? $elem_object->elem_table : $elem_object->elem_table_link;
        if (!$table) {
            echo json_encode(array('error' => iconv('windows-1251', 'utf-8', "Не определена таблица")));
            die();
        }

        $id = (int)get('id', 0, 'p');
        if (!$id) {
            echo json_encode(array('error' => iconv('windows-1251', 'utf-8', "Неизвестный параметр: id")));
            die();
        }

        $visible = get('visible', '', 'p');
        if (!in_array($visible, array('true', 'false'))) $visible = 'false';
        $visible = $visible == 'true' ? 1 : 0;

        if ($elem_object->elem_type == 'multi') {
            sql_updateId($table, array('visible' => $visible), $id);
        } else {
            $pid = (int)get('id', 0, 'g');
            $id_field = $elem_object->elem_fields['id_field'];
            $id2_field = $elem_object->elem_fields['id2_field'];
            $col_name = $elem_object->elem_fields['visible_field'] ? $elem_object->elem_fields['visible_field'] : 'visible';
            sql_update($table, array($col_name => $visible), "$id2_field = $id AND $id_field = $pid");
        }

        ob_clean();
        echo json_encode(1);
        die();
    }

    /**
     * Инициализирует и возвращает объекта элема
     * @return string|TElems
     */
    function initElemByParams($page_name, $elem_name) {

        if (!$page_name) {
            return "Неизвестный параметр: page_name";
        }
        if (!$elem_name) {
            return "Неизвестный параметр: elem_name";
            die();
        }

        // подключение нужного елема
        list(, $tmp) = explode('_', $elem_name);
        if (!is_file(elem($page_name . '/' . $elem_name))) {
            return "Не найден elem - " . $elem_name . " у класса " . $page_name;
            die();
        }

        include_once (elem($page_name . '/' . $elem_name));
        $class_elem_name = 'T' . ucfirst($tmp) . 'Element';
        $elem_object = & Registry::get($class_elem_name);

        if (!$elem_object) {
            return "Не определен объект elem_object";
            die();
        }

        $elem_object->ElemInit();

        return $elem_object;
    }

    /**
    * Делает копию с выбранных объектов
    */
    function copyobjects($data) {
        $id = get('ids', '', 'g');
        $ids = explode(",",$id);

        $page = get('page', '', 'g');
        // загружаем список tab и для каждого вызываем loadTab
        include_once (elem($page . "/" . $page));

        $class_name = 'T' . ucfirst($page);
        $this->object = & Registry::get($class_name);

        if (!$this->object->table) {
            echo "<script>alert('Копирование не удалось. У сущности не указана основная таблица.'); parent.location.reload();</script>";
            exit;
        }
        if (!isset($this->object->copy_settings)) {
            echo "<script>alert('Копирование не удалось. У сущности не указаны настройки копирования.'); parent.location.reload();</script>";
            exit;
        }

        $copy_id = $this->object->copy_settings['copy_id'] ? $this->object->copy_settings['copy_id'] : 'id';
        $copy_field = $this->object->copy_settings['copy_field'] ? $this->object->copy_settings['copy_field'] : 'name';
        $copy_default = $this->object->copy_settings['copy_default'] ? $this->object->copy_settings['copy_default'] : array();
        $copy_tables = $this->object->copy_settings['copy_tables'] ? $this->object->copy_settings['copy_tables'] : array();

        sql_query('BEGIN');
        foreach ($ids AS $id) {
            $fields = sql_getRows("SHOW COLUMNS FROM `".$this->object->table."`");
            $value = sql_getRow("SELECT * FROM `".$this->object->table."` WHERE ".$copy_id."='".$id."'");
            $sql_fld = array();
            foreach ($fields AS $field) {
                if ($field['Field'] == $copy_id) continue;

                $val = is_null($value[$field['Field']])? 'null' : $value[$field['Field']];
                if (isset($copy_default[$field['Field']])) {
                    $val = $copy_default[$field['Field']];
                }
                if ($field['Field']==$copy_field) {
                    $val .= " copy";
                }
                $sql_fld[$field['Field']] = $val;
            }

            $ret = sql_insert($this->object->table, $sql_fld, false);
            if (!is_int($ret)) {
                // не все ок
                sql_query('ROLLBACK');
                echo "<script>alert('".addslashes($ret)."'); parent.location.reload();</script>";
                exit;
            } else {
                $pid = (int)$ret;
            }

            // если есть копируем содержимое связных таблиц
            if ($copy_tables) {
                $this->copy_tables_objects($copy_tables, $id, $pid);
            }
        }

        // все ок
        // закрытие транзакции
        sql_query('COMMIT');
        echo "<script>alert('Копирование завершено.'); parent.location.reload();</script>";
        exit;
    }

    /**
    *Рекурсивная функция копирования таблиц
    */
    function copy_tables_objects($copy_tables, $id, $pid) {
        foreach ($copy_tables AS $tabl => $tabl_value) {
            if (is_array($tabl_value)) $pid_field = $tabl_value['field'];
            else $pid_field = $tabl_value;

            $rows = sql_getRows("SELECT * FROM `".$tabl."` WHERE ".$pid_field."=".$id);
            $row_fields = sql_getRows("SHOW COLUMNS FROM `".$tabl."`");
            foreach ($rows AS $row) {
                $sql_fld = array();
                foreach ($row_fields AS $row_field) {
                    if ($row_field['Extra'] == 'auto_increment') continue;

                    $val = is_null($row[$row_field['Field']]) ? 'null' : $row[$row_field['Field']];
                    if ($row_field['Field'] == $pid_field) {
                        $val = $pid;
                    }
                    $sql_fld['`'.$row_field['Field'].'`'] = $val;
                }
                $res = sql_insert($tabl, $sql_fld, false);
                if (!is_int($res)) {
                    // не все ок
                    sql_query('ROLLBACK');
                    echo "<script>alert('".addslashes($res)."'); parent.location.reload();</script>";
                    exit;
                }

                if (is_array($tabl_value)) $this->copy_tables_objects($tabl_value['copy_tables'], $row['id'], $res);
            }
        }
    }

}