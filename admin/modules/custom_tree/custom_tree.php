<?
class TCustom_tree extends TTable
{

    var $name = 'custom_tree';
    var $table = '';
    var $selector = false;
    var $pids = array();

    function TCustom_tree() {
        global $str, $actions;

        TTable::TTable();

        $actions[$this->name] = array();

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

        # языковые константы
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Разделы', 'Pages',),
            'title_editform' => array("Разделы: " . $temp, 'Pages: ' . $temp,),
            'create_title' => array('Создать элемент', 'Create',),
            'loading' => array('Загрузка...', 'Loading...',),
            'name' => array('Базовый заголовок', 'Base Title',),
            'c_del' => array('Уверены, что хотите удалить раздел ', 'Are you sure to delete page ',),
            'nocreate' => array('В данном разделе нельзя создавать другие разделы', 'You cannot create new page here',),
            'edit_this' => array('Редактировать', 'Edit',),

            '0_icon_open' => array('/admin/images/icons/icon.folder_open.png', '/admin/images/icons/icon.folder_open.png',),
            '0_icon_closed' => array('/admin/images/icons/icon.folder.png', '/admin/images/icons/icon.folder.png',),
            '1_icon_open' => array('/admin/images/icons/create.gif', '/admin/images/icons/create.gif',),
            '1_icon_closed' => array('/admin/images/icons/create.gif', '/admin/images/icons/create.gif',),
            '2_icon_open' => array('/admin/images/icons/type2_open.gif', '/admin/images/icons/type2_open.gif',),
            '2_icon_closed' => array('/admin/images/icons/type2.gif', '/admin/images/icons/type2.gif',),
            '3_icon_open' => array('/admin/images/icons/type3_open.gif', '/admin/images/icons/type3_open.gif',),
            '3_icon_closed' => array('/admin/images/icons/type3.gif', '/admin/images/icons/type3.gif',),
            'select' => array('Выбрать', 'Select',),

            'text_icon_open' => array('/admin/images/icons/icon.folder_open.png', '/admin/images/icons/icon.folder_open.png',),
            'text_icon_closed' => array('/admin/images/icons/icon.folder.png', '/admin/images/icons/icon.folder.png',),
            'module_icon_open' => array('/admin/images/icons/icon.module.png', '/admin/images/icons/icon.module.png',),
            'module_icon_closed' => array('/admin/images/icons/icon.module.png', '/admin/images/icons/icon.module.png',),
            'home_icon_open' => array('/admin/images/icons/icon.domik.png', '/admin/images/icons/icon.domik.png',),
            'home_icon_closed' => array('/admin/images/icons/icon.domik.png', '/admin/images/icons/icon.domik.png',),
            'catalog_icon_open' => array('/admin/images/icons/icon.orders.gif', '/admin/images/icons/icon.orders.gif',),
            'catalog_icon_closed' => array('/admin/images/icons/icon.orders.gif', '/admin/images/icons/icon.orders.gif',),

            'delete' => array('Удалить', 'Delete',),
            'del_folder' => array('Удалить раздел', 'Delete folder',),
            'copy' => array('Копировать', 'Copy',),
            'preview' => array('Просмотр', 'Preview',),
            'parity' => array('Сбросить приоритеты у всей группы', 'Parity',),
            'up' => array('Поднять', 'Up',),
            'down' => array('Опустить', 'Down',),

            'copy_to' => array('Копировать или перенести', 'Copy or transfer'),
            'from' => array('из', 'from'),
            'to' => array('в', 'to'),
            'move' => array('Перенести', 'Move'),

            'e_move_to_same' => array('Нельзя копировать раздел сам в себя', 'Not copy '),
            'e_unknown' => array('Неопределенный запрос.', 'Not copy '),
            'root' => array('Главная', 'Home page'),
        ));
    }

    /**
     * Основная функция показа дерева
     * @return string
     */
    function Show() {
        $id = (int)get('id');

        $ret['path'] = $this->GetPath($id);
        $ret['trees'] = $this->GetAllTree();
        $ret['id'] = $id;

        $ret['STR_LOADING'] = $this->str('loading');
        return Parse($ret, 'custom_tree/custom_tree.tmpl');
    }

    /**
     * Выбирает верхние уровни (где id=pid)
     * @param array $params - дополнительные параметры
     * @return string
     */
    function GetAllTree($params = array()) {
        global $user;
        $id = isset($params['id']) ? $params['id'] : (int)get('id');

        $tree = "";

        if (defined('LANG_SELECT') && LANG_SELECT) $name_select = "IF (name_" . lang() . " <> '', name_" . lang() . ", name_" . LANG_DEFAULT . ") as name";
        else $name_select = "name";

        $sql = "
			SELECT
                id, $name_select, next, pid, priority, pids, level, dir
			FROM
				" . $this->table . "
			WHERE
                pid=id
			ORDER BY
				priority, name
		";
        $rows = sql_getRows($sql);

        if ($rows) {
            foreach ($rows as $row) {
                $items = array();

                $items[$row['id']] = $this->getOneItem($id, $row, $params);

                if ($row['id'] == $id || (is_array($this->pids) && in_array($row['id'], $this->pids))) {
                    $subtree = $this->GetTree($id, -1, $params);
                    // создадим эффект вложенности
                    $items[$row['id']]['subtree'] = '<ul>' . $subtree . '</ul>';
                }

                $items_data = array();
                $items_data['items'] = $items;

                $tree_items['tree'] = array();
                $tpl = 'custom_tree/custom_tree.items.tmpl';
                if ($params['copy']) $tpl = 'custom_tree/custom_tree.items_copy.tmpl';
                elseif ($params['treeid']) $tpl = 'custom_tree/custom_treeid/custom_treeid.items.tmpl';
                elseif ($params['checkboxtree']) $tpl = 'custom_tree/custom_treecheck/custom_treecheck.items.tmpl';
                $tree_items['tree'] = Parse($items_data, $tpl);

                $tree[] = Parse($tree_items, 'custom_tree/custom_tree.full.tmpl');
            }
        }

        return implode(" ", $tree);
    }

    ######################
    /**
     * Построение дерева
     *
     * @param int $id - от какого элемента строить дерево
     * @param int $level
     * @param array $params
     * @return unknown
     */
    function GetTree($id, $level = -1, $params = array()) {
        global $user;

        $level++;

        if (defined('LANG_SELECT') && LANG_SELECT) $name_select = "IF (name_" . lang() . " <> '', name_" . lang() . ", name_" . LANG_DEFAULT . ") as name";
        else $name_select = "name";

        $sql = "
			SELECT
				id, $name_select, next, pid, priority, pids, level, dir
			FROM
				" . $this->table . "
			WHERE
                pid<>id
                " . (((int)$this->pids[$level]) ? " AND pid=" . (int)$this->pids[$level] : '') . "
			ORDER BY
				priority, name
		";
        $rows = sql_getRows($sql);

        $items = array();
        if ($rows) {
            foreach ($rows as $row) {
                $items[$row['id']] = $this->getOneItem($id, $row, $params);
                if (is_array($this->pids) && in_array($row['id'], $this->pids) && $row['id'] != $row['pid']) {
                    $items[$row['id']]['subtree'] = $this->GetTree($id, $level, $params);
                }
            }
        }

        $items_data['items'] = &$items;
        if (count($rows) && $level > 0) {
            $items_data['ul_open'] = '<ul>';
            $items_data['ul_close'] = '</ul>';
        }

        $tpl = 'custom_tree/custom_tree.items.tmpl';
        if ($params['copy']) $tpl = 'custom_tree/custom_tree.items_copy.tmpl';
        elseif ($params['treeid']) $tpl = 'custom_tree/custom_treeid/custom_treeid.items.tmpl';
        elseif ($params['checkboxtree']) $tpl = 'custom_tree/custom_treecheck/custom_treecheck.items.tmpl';
        $tree = Parse($items_data, $tpl);
        return $tree;
    }

    /**
     * Возвращает массив данных об одном узле
     * @param $id - текущий выбранный раздел
     * @param $row - тот раздел, который рисуем
     * @param array $params
     * @return array
     */
    function getOneItem($id, $row, $params) {
        # выбранный элемент
        $dbclk_href = "editor.php?page=" . $this->name . "&id=" . $row['id'];

        # плюс / минус иконка
        # открытая / закрытая папка
        $plus = '';
        $plus_href = "?page=" . $this->name;
        if ($params['copy']) { // деревья для окна копирования
            $plus_href .= "&do=CopyDlg&src=" . $params['src'];
        }
        elseif ($params['treeid']) { // деревья для подстановки ID раздела
            $plus_href = "?page=custom_tree/custom_treeid";
            if (isset($_GET['fld_target_id'])) {
                $plus_href .= '&fld_target_id=' . $_GET['fld_target_id'];
            }
            if (isset($_GET['fld_target_name'])) {
                $plus_href .= '&fld_target_name=' . $_GET['fld_target_name'];
            }
            if (isset($_GET['fieldname'])) {
                $plus_href .= '&fieldname=' . $_GET['fieldname'];
            }
            if (isset($_GET['returnid'])) {
                $plus_href .= '&returnid=' . $_GET['returnid'];
            }
            if (isset($_GET['table'])) {
                $plus_href .= '&table=' . $_GET['table'];
            }
        }
        elseif ($params['checkboxtree']) { // деревья для подстановки ID раздела
            $plus_href = "?page=custom_tree/custom_treecheck";
            if (isset($_GET['fieldname'])) {
                $plus_href .= '&fieldname=' . $_GET['fieldname'];
            }
            if (isset($_GET['target_ids'])) {
                $plus_href .= '&target_ids=' . $_GET['target_ids'];
            }
            if (isset($_GET['table'])) {
                $plus_href .= '&table=' . $_GET['table'];
            }
        }

        if (is_array($this->pids) && (in_array($row['id'], $this->pids)) || in_array(0, $this->pids)) {
            if ($row['next']) {
                $plus_href .= "&id=" . $row['pid'];
                $plus = "<a href='" . $plus_href . "' class='icoMinus' title='свернуть'>свернуть</a>";
            }
            $icon = $this->str('text_icon_open');
        } else {
            if ($row['next']) {
                $plus_href .= "&id=" . $row['id'];
                $plus = "<a href='" . $plus_href . "' class='icoPlus' title='развернуть'>развернуть</a>";
            }
            $icon = $this->str('text_icon_closed');
        }

        $class = $buttons = $href = $title = '';

        if (!$params) {
            if ($row['id'] == $id) {
                // Текущий открытый элемент
                $class = ' open';
                $buttons = $this->GetButtons($row, 0);
                $href = "editor.php?page=" . $this->name . "&id=" . $row['id'];
                $title = $this->str('edit_this');
            } else {
                // Не открытый элемент
                $href = "?page=" . $this->name . "&id=" . $row['id'];
                $title = $this->str('select');
            }
        }
        elseif ($params['copy']) {
            if ($row['id'] == $id) {
                $class = ' open';
            }
            $href = "?page=" . $this->name . "&do=CopyDlg&id=" . $row['id'] . "&src=" . $params['src'];
            $title = $this->str('select');
        }
        elseif ($params['checkboxtree']) {
            $href = "javascript:checkbox_click(" . $row['id'] . ")";
        }

        $item = $row;
        $item['dbclk_href'] = $dbclk_href;
        $item['plus'] = $plus;
        $item['icon'] = $icon;
        $item['class'] = $class;
        $item['buttons'] = $buttons;
        $item['href'] = $href;
        $item['title'] = $title;
        $item['note'] = $note;
        if ($params['treeid']) {
            $item['java_href'] = "javascript:selectid(" . $row['id'] . ", \"" . addslashes(str_replace(array('"', '&quot;'), '', $row['name'])) . "\", \"" . $row['dir'] . "\")";
        }
        elseif ($params['checkboxtree']) {
            $target_ids = explode(",", get("target_ids", '', 'gp'));
            $item['checkbox'] = in_array($row['id'], $target_ids) ? 'CHECKED' : '';
        }

        return $item;
    }

    /**
     * Строит "хлебные крошки" до страницы
     * @param $id
     * @param string $do
     * @param string $root_href
     * @return string
     */
    function GetPath($id, $do = '', $root_href = '') {
        if ($do) $do = '&do=' . $do;
        $root_href = $root_href ? $root_href : "?page=" . $this->name . $do;

        $main = sql_getRow("SELECT * FROM " . $this->table . " WHERE id=pid");
        $name = $main['name'];
        $path = "<a href='" . $root_href . "'>" . $name . "</a>";
        if (!$id) {
            $this->pids[] = $main['id'];
            return $path;
        }

        $current = sql_getRow("SELECT id, name, pids FROM " . $this->table . " WHERE id=" . (int)$id);
        $this->pids = $this->GetPids($current['pids']);

        if (!in_array($id, $this->pids)) {
            $this->pids[] = $id;
        }

        $rows = sql_query("SELECT id, name FROM " . $this->table . " WHERE id IN (" . join(',', $this->pids) . ") ORDER BY level");
        if ($rows) {
            $path = "";
            while ($row = mysql_fetch_assoc($rows)) {
                if ($sites) {
                    $name = isset($sites[$row['id']]) ? $sites[$row['id']]['name'] . " - " . $row['name'] : $row['name'];
                } else {
                    $name = $row['name'];
                }
                if ($row['id'] == $id) {
                    $path .= "<span>" . $name . "</span>";
                } else {
                    $path .= "<a href='?page=" . $this->name . "&id=" . $row['id'] . $do . "'>" . $name . "</a>";
                }

            }
        } else {
            echo 'GetPath err: ' . mysql_error();
        }
        //$path.= "<span>".$current['name']."</span>";

        return $path;
    }


    ######################
    #
    # prints current buttons
    #
    function GetButtons($current, $count = 0) {
        $id = (int)$_GET['id'];
        if (!$id) return "";

        $buttons = '';

        $buttons .= "<a title='" . $this->str('create_title') . "' href='editor.php?page=" . $this->name . "&id=0&pid=" . $id . "' class='icoNav' style='background-image:url(/admin/images/icons/folder.gif);'>" . $this->str('create_title') . "</a>";

        // Удалить
        $buttons .= "<a title='" . $this->str('delete') . "' href='?do=Delete&page=" . $this->name . "&id=$id&pid=" . $current["pid"] . "' onclick='return confirm(\"" . $this->str('del_folder') . ": " . addslashes($current["name"]) . "?\")' class='icoNav' style='background-image:url(/admin/images/tree/ico_del.gif);'>" . $this->str('delete') . "</a>";

        // Копировать
        $buttons .= "<a title='" . $this->str('copy') . "' href='javascript:window.open(\"dialog.php?page=" . $this->name . "&do=CopyDlg&src=" . $id . "&id=" . $id . "\",\"treecopy\",\"scrollbars=yes, resizable=yes, width=400, height=420\").focus()' class='icoNav' style='background-image:url(/admin/images/tree/ico_copy.gif);'>" . $this->str('copy') . "</a>";

        // Приоритет
        $max_priority = sql_getValue('SELECT MAX(priority) FROM ' . $this->table . ' WHERE pid=' . $current['pid'] . ' AND id<>pid');
        $min_priority = sql_getValue('SELECT MIN(priority) FROM ' . $this->table . ' WHERE pid=' . $current['pid'] . ' AND id<>pid');

        $buttons .= "<a title='" . $this->str('parity') . " (" . $current['priority'] . ")' href='?page=" . $this->name . "&do=enum&pid=" . $current['pid'] . "' class='icoNav' style='background-image:url(/admin/images/tree/ico_sorting.gif);'>" . $this->str('parity') . " (" . $current['priority'] . ")</a>";
        if ($current['priority'] > $min_priority) {
            $buttons .= "<a title='" . $this->str('up') . "' href='?page=" . $this->name . "&do=swap&id=" . $id . "&src=" . $current['priority'] . "&trg=" . ($current['priority'] - 1) . "&pid=" . $current['pid'] . "' class='icoNav' style='background-image:url(/admin/images/tree/ico_up.gif);'>" . $this->str('up') . "</a>";
        }
        if ($current['priority'] < $max_priority) {
            $buttons .= "<a title='" . $this->str('down') . "' href='?page=" . $this->name . "&do=swap&id=" . $id . "&src=" . $current['priority'] . "&trg=" . ($current['priority'] + 1) . "&pid=" . $current['pid'] . "' class='icoNav' style='background-image:url(/admin/images/tree/ico_down.gif);'>" . $this->str('down') . "</a>";
        }

        return $buttons;
    }

    ######################
    function GetPids($pids) {
        if (!$pids) return array();
        # pids
        $pids = explode('/', $pids);
        if (!$pids[0]) array_shift($pids);
        if (!$pids[count($pids) - 1]) array_pop($pids);
        return $pids;
    }

    ######################
    #
    # Окно копирования и перемещения
    #
    function CopyDlg() {
        $id = (int)$_GET['id'];
        $src = (int)$_GET['src'];

        // Смотрим путь назначения
        $dir = explode("/", sql_getValue('SELECT dir FROM ' . $this->table . ' WHERE id = ' . $id));

        $ret['from'] = $this->str('from');
        $ret['from_path'] = $this->GetPath($src, 'CopyDlg&src=' . $src);
        $ret['to'] = $this->str('to');
        $ret['to_path'] = $this->GetPath($id, 'CopyDlg&src=' . $src);

        $ret['copy_to'] = $this->str('copy_to');
        $ret['copy'] = $this->str('copy');
        $ret['copy_disabled'] = (in_array($src, $dir) || $src == $id ? 'DISABLED' : '');
        $ret['move'] = $this->str('move');
        $ret['move_disabled'] = (in_array($src, $dir) || $src == $id ? 'DISABLED' : '');

        $ret['id'] = $id;
        $ret['src'] = $src;
        $ret['name'] = $this->name;
        $ret['trees'] = $this->GetAllTree(array('id' => $id, 'src' => $src, 'copy' => true));

        return Parse($ret, 'custom_tree/custom_tree.copy.tmpl');
    }

    /**
     * Сюда постится форма с CopyDlg() и тут все происходит
     * @return mixed
     */
    function EditProcCopy() {
        @ignore_user_abort();

        $trg = (int)$_GET['trg'] ? (int)$_GET['trg'] : domainRootID();
        $src = (int)$_GET['src'] ? (int)$_GET['src'] : domainRootID();

        if ($src && $src == $trg && isset($_GET['move'])) {
            page_error($this->str('e_move_to_same'));
            return;
        }

        if (isset($_GET['move']) && $src) {
            # перемещение раздела
            $this->MoveTree($src, $trg);
            $this->Validate(0, '', 0, array());
        } elseif (isset($_GET['copy']) && $src) {
            # копирование (каскадное)
            $this->CopyTree($src, $trg);
            $this->Validate(0, '', 0, array());
        } else {
            page_error($this->str('e_unknown'));
            return;
        }

        echo "
		Done. Closing window...
		<script type='text/javascript'>
		if (opener) opener.location.href = '/admin/?page=" . $this->name . "&id=" . $trg . "';
		window.close();
		</script>
		";
    }

    /**
     * Проверяет, является ли колонка автоинкрементируемой
     * @param $columns
     * @param $id
     * @return bool
     */
    function isAutoIncrement($columns, $id) {
        foreach ($columns as $key => $val) {
            if ($val['Field'] != $id) continue;
            if ($val['Extra'] == 'auto_increment') return true;
        }
        return false;
    }

    /**
     * Перемещает раздел
     * @param $src_id
     * @param $trg_id
     */
    function MoveTree($src_id, $trg_id) {
        $src = sql_getRow("SELECT * FROM " . $this->table . " WHERE id='" . $src_id . "'");
        $trg = sql_getRow("SELECT * FROM " . $this->table . " WHERE id='" . $trg_id . "'");

        # Обновляем src
        $ret = sql_query("UPDATE " . $this->table . " SET pid=" . $trg['id'] . " WHERE id=" . $src_id);
        if (!$ret) {
            die('"UPDATE error: ' . addslashes(sql_getError()) . '"');
        }

        # Обновляем parent src next
        $psrc_count = sql_getValue("SELECT COUNT(*) FROM " . $this->table . " WHERE pid=" . $src['pid'] . " AND pid<>id");
        sql_query("UPDATE " . $this->table . " SET next=" . ($psrc_count ? 1 : 0) . " WHERE id=" . $src['pid']);

        # Обновляем parent trg next
        sql_query("UPDATE " . $this->table . " SET next=1 WHERE id=" . $trg['id']);
    }

    ######################
    function Validate($pid = 0, $page = '', $level = 0, $data = array()) {
        $level++;
        $pid = $pid ? $pid : sql_getValue("SELECT id FROM " . $this->table . " WHERE id=pid");
        $data['pids'][$level] = $pid;
        if ($page) $data['page'][$level] = $page;

        if (defined('LANG_SELECT') && LANG_SELECT) $name_select = "IF (name_" . lang() . " <> '', name_" . lang() . ", name_" . LANG_DEFAULT . ") as name";
        else $name_select = "name";

        $rows = sql_query("SELECT id, pid, $name_select FROM " . $this->table . " WHERE pid=" . $pid . " AND id<>pid ORDER BY priority,name");
        if ($rows) {
            while ($row = mysql_fetch_assoc($rows)) {
                $sets = array();

                # nextid
                $sets[] = 'next=0';
                # pids
                $sets[] = "pids='/" . join("/", $data["pids"]) . "/'";
                # levels
                $sets[] = 'level=' . $level;

                if (sizeof($data['page'])) $sets[] = "dir='/" . join("/", $data['page']) . "/" . $row['id'] . "/'";
                else $sets[] = "dir='/" . $row["id"] . "/'";

                $res = sql_query("UPDATE " . $this->table . " SET " . join(', ', $sets) . " WHERE id=" . $row['id']);
                if ($row['pid']) {
                    $res = sql_query("UPDATE " . $this->table . " SET next=" . $row['id'] . " WHERE id=" . $row['pid']);
                }

                $this->check_id[] = $row['id'];
                $this->Validate($row['id'], $row['id'], $level, $data);
            }
            mysql_free_result($rows);
            //            touch('sid/.cache');
        } else die(mysql_error());
    }

    ######################
    #
    # рекурсивная функция для копирования участка дерева в другое место
    #
    function CopyTree($src, $trg, $top = true) {
        static $counter = array();
        static $next_id = 0;
        // запишем что мы уже перенесли (для отмены рекурсии)
        $counter[] = $trg;

        // Если в данный элемент уже копировали, отменяем (для отмены рекурсии)
        if (in_array($src, $counter)) return;

        # Берем source
        $src_row = sql_getRow("SELECT * FROM " . $this->table . " WHERE id='" . $src . "'");

        if (!$next_id) {
            $status = sql_getRow("SHOW TABLE STATUS LIKE '" . $this->table . "'");
            $next_id = $status['Auto_increment'];
        } else {
            $next_id++;
        }

        # Обрабатываем
        unset($src_row['id']);
        $src_row['pid'] = $trg;

        # Вставляем
        $src_row['dir'] = 'tmp_' . count($counter);
        $trg = sql_insert($this->table, $src_row);

        # Ищем дочерние ветви и запускаем заново эту функцию
        $rows = sql_query("SELECT id FROM " . $this->table . " WHERE pid=" . $src);
        if ($rows) while ($row = mysql_fetch_row($rows)) $this->CopyTree($row[0], $trg, false);
    }

    ######################
    #
    function Delete() {
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;

        $pid = sql_getValue("SELECT pid FROM " . $this->table . " WHERE id=" . $id);
        $res = sql_query("DELETE FROM " . $this->table . " WHERE id=" . $id);

        # проставляем next
        if ($res) {
            $count = (int)sql_getValue("SELECT COUNT(*) FROM " . $this->table . " WHERE pid=" . $pid);
            sql_query("UPDATE " . $this->table . " SET next=" . $count . " WHERE id=" . $pid);
            if (!$count) {
                $pid = sql_getValue("SELECT pid FROM " . $this->table . " WHERE id=" . $pid);
            }
        }

        return "<script>location.href='/admin/?page=" . $this->name . "&id=" . $pid . "';</script>";
    }

    ######################
    function Swap() {
        # (src)id, (desired)trg, (current)src, pid
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $trg = isset($_GET['trg']) ? (int)$_GET['trg'] : 0;
        $src = isset($_GET['src']) ? (int)$_GET['src'] : 0;
        $pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;

        if ($id && ($trg * $src)) {

            # Получаем id элемента для замены
            $trg_id = sql_getValue("SELECT id FROM " . $this->table . " WHERE priority=" . $trg . " AND pid=" . $pid . " AND id<>pid");
            if (!$trg_id) {
                // Делаем автонумерацию, если ошибка
                $this->Enum($pid, true);
                $trg_id = sql_getValue("SELECT id FROM " . $this->table . " WHERE priority=" . $trg . " AND pid=" . $pid . " AND id<>pid");
                // Если нумерация не помогла, выдаем ошибку.
                if (!$trg_id) {
                    /// ??????					ErrorExit('TTree.Swap: [no target id] Это значит, что либо не пронумерованы элементы группы или произошла внутренняя ошибка, нужно опять все перенумеровать.');
                }
            }

            # Заменяем target
            mysql_query("UPDATE " . $this->table . " SET priority=" . $src . " WHERE id=" . $trg_id) or die('TCustom_tree.Swap: update trg failed');

            # Заменяем src
            mysql_query("UPDATE " . $this->table . " SET priority=" . $trg . " WHERE id=" . $id) or die('TCustom_tree.Swap: update src failed');
        }
        return "<script>location.href='/admin/?page=" . $this->name . "&id=" . $id . "';</script>";
    }

    ######################
    function Enum($pid = false, $correct_priority = false) {
        $can_redirect = $pid === false;
        if ($can_redirect) $pid = isset($_GET['pid']) ? $_GET['pid'] : 0;
        $counter = 1;
        if ($correct_priority) {
            $rows = mysql_query("SELECT id FROM " . $this->table . " WHERE pid=" . $pid . " AND id<>pid ORDER BY priority DESC, name");
        } else {
            $rows = mysql_query("SELECT id FROM " . $this->table . " WHERE pid=" . $pid . " AND id<>pid ORDER BY name");
        }
        if ($rows) while ($row = mysql_fetch_assoc($rows)) {
            mysql_query("UPDATE " . $this->table . " SET priority=" . $counter . " WHERE id=" . $row['id']);
            $counter++;
        }
        if ($can_redirect) return "<script>location.href='" . $_SERVER['HTTP_REFERER'] . "';</script>";
    }

}

$GLOBALS['custom_tree'] = & Registry::get('TCustom_tree');