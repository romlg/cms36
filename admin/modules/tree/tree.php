<?
class TTree extends TTable
{

    var $name = 'tree';
    var $table = 'tree';
    var $hideInvisible = false;
    var $selector = false;
    var $domain_selector = false;
    var $pids = array();

    var $department_id = 0;
    var $department_root = true;

    function TTree() {
        global $str, $actions;

        TTable::TTable();

        $actions[$this->name] = array(
            'create' => array(
                'Создать раздел первого уровня',
                'Create',
                'link' => 'cnt.createItem(event)',
                'img' => 'icon.create.gif',
                'display' => 'none',
            ),
            'recycle' => array(
                'Корзина',
                'Recycle Bin',
                'link' => 'showRecycle',
                'img' => 'icon.trash.gif',
                'display' => 'block',
                'show_title' => true,
                'multiaction' => 'false',
            ),
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
        );
        if ((int)$_GET['id']) {
            // проверим есть ли таблица сайтов и языков
            $tables = sql_getRows("SHOW TABLES ");
            if (in_array ('sites_langs', $tables)) {
                $sql = "SELECT CONCAT('http://', sites.name, '/', sites_langs.name, tree.dir) FROM sites, sites_langs, tree WHERE tree.root_id=sites_langs.root_id AND sites.id=sites_langs.pid AND tree.id='".(int)$_GET['id']."'";
            } elseif(in_array ('sites', $tables)) {
                $sql = "SELECT CONCAT('http://', sites.name, tree.dir) FROM sites, tree WHERE tree.root_id=sites.root_id AND tree.id='".(int)$_GET['id']."'";
            } else {
                $sql = "SELECT dir FROM tree WHERE id='".(int)$_GET['id']."'";
            }
            $dir = sql_getValue($sql);

            $click = "newWin = window.open('" . $dir . "', '_blank', 'menubar=yes,toolbar=yes,location=yes,directories=yes,resizable=yes,scrollbars=yes,status=yes');newWin.focus();";
            $actions[$this->name . '.editform']['view_on_site'] = array(
                'title' => array(
                    'ru' => 'Смотреть на сайте ',
                    'en' => 'View on the site',
                ),
                'onclick' => $click,
                'img' => 'icon.preview.png',
                'display' => 'block',
                'show_title' => true,
            );
        }
        $actions[$this->name . '.editform']['cancel'] = array(
            'title' => array(
                'ru' => 'Отмена',
                'en' => 'Cancel',
            ),
            'onclick' => 'window.location=\'/admin/?page=' . $this->name . '\'',
            'img' => 'icon.close.gif',
            'display' => 'block',
            'show_title' => true,
        );


        if ((int)$_GET['id']) {
            $temp = sql_getValue("SELECT name FROM " . $this->table . " WHERE id=" . (int)$_GET['id']);
        } else {
            $temp = "Новый раздел";
        }

        # языковые константы
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Разделы сайта', 'Pages',),
            'title_editform' => array("Разделы сайта : " . $temp, 'Pages : ' . $temp,),
            'create_title' => array('Создание страницы', 'Create page',),
            'loading' => array('Загрузка...', 'Loading...',),
            'name' => array('Базовый заголовок', 'Base Title',),
            'type' => array('Тип', 'Type',),
            'c_del' => array('Уверены, что хотите удалить раздел ', 'Are you sure to delete page ',),
            'invisible_hint' => array('Раздел не показывается', 'Invisible page',),
            'deny_id' => array('Запрещенный раздел', 'Deny page',),
            'nocreate' => array('В данном разделе нельзя создавать другие разделы', 'You cannot create new page here',),

            '_invisible' => array('скрытый', 'invisible',),
            'edit_this' => array('Редактировать', 'Edit',),
            '_protected' => array('служебный', 'protected',),
            '_hidden' => array('недоступен', 'hidden',),
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
            'link_icon_open' => array('/admin/images/icons/icon.link.gif', '/admin/images/icons/icon.link.gif',),

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

        global $user;
        $department_id = (int)sql_getValue('SELECT department_id FROM admins WHERE id=' . $user['id']);
        if ($department_id) {
            $tree_row = sql_getRow("SELECT * FROM tree WHERE id=" . (int)$department_id);
            if ($tree_row && $tree_row['id'] != $tree_row['pid']) {
                $this->department_root = false;
            }
            $this->department_id = $department_id;
        }
    }

    /**
     * Основная функция показа дерева
     * @return string
     */
    function Show() {
        $id = (int)get('id');
        $hidden = isset($_GET['hidden']) ? (int)$_GET['hidden'] : 1;

        $ret['path'] = $this->GetPath($id);
        $ret['trees'] = $this->GetAllTree();
        $ret['id'] = $id;
        $ret['hidden'] = $hidden;

        $columns = sql_getRows("SHOW columns FROM tree LIKE 'is_link'");
        if (empty($columns)) {
            mysql_query("ALTER TABLE  `tree` ADD  `is_link` TINYINT( 1 ) NOT NULL DEFAULT  '0'");
        }

        $ret['STR_LOADING'] = $this->str('loading');
        return $this->Parse($ret, 'tree.tmpl');
    }

    /**
     * Выбирает верхние уровни (где id=pid=root_id)
     * @param array $params - дополнительные параметры
     * @return string
     */
    function GetAllTree($params = array()) {
        global $user, $site_domains;
        $id = isset($params['id']) ? $params['id'] : (int)get('id');
        $hidden = isset($_GET['hidden']) ? (int)$_GET['hidden'] : 1;

        $tree = "";

        if (defined('LANG_SELECT') && LANG_SELECT) $name_select = "IF (name_" . lang() . " <> '', name_" . lang() . ", name_" . LANG_DEFAULT . ") as name";
        else $name_select = "name";

        $deny_ids = '';
        if (!is_root() && !empty($user['deny_ids'])) {
            $deny_ids = $user['deny_ids'];
        }

        $sql = "
			SELECT
                id, $name_select, next, visible, pid, priority, page, type, root_id, pids, level, dir, protected
			FROM
				" . $this->table . "
			WHERE
                " . ($this->department_root ? "pid=id" . ($this->department_id ? " AND id = $this->department_id" : "") : " id = " . $this->department_id) . "
                " . ($deny_ids ? " AND id NOT IN (" . $deny_ids . ")" : "") . "
			ORDER BY
				priority, name
		";
        $rows = sql_getRows($sql);

        if ($rows) {
            $hidden_url = !$hidden ? "&hidden=0" : "";
            foreach ($rows as $row) {
                $items = array();

                if (count($rows) > 1) {
                    $site_name = getSiteByRootID($row['root_id']);
                    if ($site_name) {
                        $_lang = getLangByRootID($row['root_id']);
                        $row['name'] = $site_name . " - " . $site_domains[$site_name]['descr'] . ' (' . ($_lang ? $_lang : $row['name']) . ')';
                    }
                }

                $items[$row['id']] = $this->getOneItem($id, $row, $row['root_id'], $hidden_url, $params);

                if ($row['id'] == $id || count($rows) == 1 || (is_array($this->pids) && $this->pids['0'] == $row['root_id'] && in_array($row['id'], $this->pids))) {
                    $subtree = $this->GetTree($id, -1, $hidden, $row['root_id'], $params);
                    // создадим эффект вложенности
                    $items[$row['id']]['subtree'] = '<ul>' . $subtree . '</ul>';
                }

                $items_data = array();
                $items_data['items'] = $items;

                $tree_items['tree'] = array();
                $tpl = 'tree/tree.items.tmpl';
                if ($params['copy']) $tpl = 'tree/tree.items_copy.tmpl';
                elseif ($params['treeurl']) $tpl = 'tree/treeurl/treeurl.items.tmpl';
                elseif ($params['treeid']) $tpl = 'tree/treeid/treeid.items.tmpl';
                elseif ($params['checkboxtree']) $tpl = 'tree/treecheck/treecheck.items.tmpl';
                $tree_items['tree'] = Parse($items_data, $tpl);

                $tree[] = Parse($tree_items, 'tree/tree.full.tmpl');
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
     * @param int $hidden - если hidden=0, то не показываем скрытые разделы
     * @param int root_id
     * @param array $params
     * @return unknown
     */
    function GetTree($id, $level = -1, $hidden = 1, $root_id, $params = array()) {
        global $user;

        $level++;

        $root_id = $root_id ? $root_id : domainRootID();

        $deny_ids = '';
        if (!is_root() && !empty($user['deny_ids'])) {
            $deny_ids = $user['deny_ids'];
        }

        if (defined('LANG_SELECT') && LANG_SELECT) $name_select = "IF (name_" . lang() . " <> '', name_" . lang() . ", name_" . LANG_DEFAULT . ") as name";
        else $name_select = "name";

        if ($this->department_id) {
            $sql = "
    			SELECT
    				id, $name_select, next, visible, pid, priority, page, type, root_id, pids, level, dir, protected, is_link
    			FROM
    				" . $this->table . "
    			WHERE
                    pid<>id
                    " . (($this->pids[0] == $this->department_id && (int)$this->pids[$level]) ? " AND pid=" . (int)$this->pids[$level] : ' AND pid=' . $this->department_id) . "
                    " . (!$hidden ? " AND visible>'0'" : " AND visible>='0'") . "
    				" . ($deny_ids ? " AND id NOT IN (" . $deny_ids . ")" : "") . "
    			ORDER BY
    				priority, name
    		";
        } else {
            $sql = "
			SELECT
				id, $name_select, next, visible, pid, priority, page, type, root_id, pids, level, dir, protected, is_link
			FROM
				" . $this->table . "
			WHERE
                root_id='" . $root_id . "'
                " . (($this->pids[0] == $root_id && (int)$this->pids[$level]) ? " AND pid=" . (int)$this->pids[$level] : ' AND pid=' . $root_id) . " AND pid<>id
                " . (!$hidden ? " AND visible>'0'" : " AND visible>='0'") . "
				" . ($deny_ids ? " AND id NOT IN (" . $deny_ids . ")" : "") . "
			ORDER BY
				priority, name
		";
        }
        $rows = sql_getRows($sql);

        $items = array();
        if ($rows) {
            $hidden_url = !$hidden ? "&hidden=0" : "";
            foreach ($rows as $row) {
                $items[$row['id']] = $this->getOneItem($id, $row, $root_id, $hidden_url, $params);
                if ($this->department_root) {
                    if (is_array($this->pids) && $this->pids['0'] == $root_id && in_array($row['id'], $this->pids) && $row['id'] != $row['pid']) {
                        $items[$row['id']]['subtree'] = $this->GetTree($id, $level, $hidden, $root_id, $params);
                    }
                } else {
                    if (is_array($this->pids) && $this->pids['0'] == $this->department_id && in_array($row['id'], $this->pids) && $row['id'] != $row['pid']) {
                        $items[$row['id']]['subtree'] = $this->GetTree($id, $level, $hidden, $root_id, $params);
                    }
                }
            }
        }

        $items_data['items'] = &$items;
        if (count($rows) && $level > 0) {
            $items_data['ul_open'] = '<ul>';
            $items_data['ul_close'] = '</ul>';
        }

        $tpl = 'tree/tree.items.tmpl';
        if ($params['copy']) $tpl = 'tree/tree.items_copy.tmpl';
        elseif ($params['treeurl']) $tpl = 'tree/treeurl/treeurl.items.tmpl';
        elseif ($params['treeid']) $tpl = 'tree/treeid/treeid.items.tmpl';
        elseif ($params['checkboxtree']) $tpl = 'tree/treecheck/treecheck.items.tmpl';
        $tree = Parse($items_data, $tpl);
        return $tree;
    }

    /**
     * Возвращает массив данных об одном узле
     * @param $id - текущий выбранный раздел
     * @param $row - тот раздел, который рисуем
     * @param $root_id
     * @param string $hidden_url
     * @param array $params
     * @return array
     */
    function getOneItem($id, $row, $root_id, $hidden_url = '', $params) {
        # выбранный элемент
        $dbclk_href = "editor.php?page=" . $this->name . "&id=" . $row['id'] . $hidden_url;

        # плюс / минус иконка
        # открытая / закрытая папка
        $plus = '';
        $plus_href = "?page=" . $this->name . $hidden_url;
        if ($params['copy']) { // деревья для окна копирования
            $plus_href .= "&do=CopyDlg&src=" . $params['src'];
        }
        elseif ($params['treeurl']) { // деревья для подстановки урла
            $plus_href = "?page=tree/treeurl" . $hidden_url;
            if (isset($_GET['fld_target_id'])) {
                $plus_href .= '&fld_target_id=' . $_GET['fld_target_id'];
            }
            if (isset($_GET['fld_target_name'])) {
                $plus_href .= '&fld_target_name=' . $_GET['fld_target_name'];
            }
            if (isset($_GET['fieldname'])) {
                $plus_href .= '&fieldname=' . $_GET['fieldname'];
            }
        }
        elseif ($params['treeid']) { // деревья для подстановки ID раздела
            $plus_href = "?page=tree/treeid" . $hidden_url;
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
        }
        elseif ($params['checkboxtree']) { // деревья для подстановки ID раздела
            $plus_href = "?page=tree/treecheck" . $hidden_url;
            if (isset($_GET['fieldname'])) {
                $plus_href .= '&fieldname=' . $_GET['fieldname'];
            }
            /*if (isset($_GET['target_ids'])) {
                $plus_href .= '&target_ids=' . $_GET['target_ids'];
            }*/
        }

        if (is_array($this->pids) && $this->pids[0] == $root_id && (in_array($row['id'], $this->pids)) || (in_array(0, $this->pids) && $row['id'] == $root_id)) {
            if ($row['next']) {
                if ($row['id'] != $row['pid']) $plus_href .= "&id=" . $row['pid'];
                if ($params['checkboxtree']) {
                    $plus = "<a href='" . $plus_href . "' onclick='changeBranch(this); return false;' class='icoMinus' title='свернуть'>свернуть</a>";
                } else {
                    $plus = "<a href='" . $plus_href . "' class='icoMinus' title='свернуть'>свернуть</a>";
                }
            }
            if ($this->str($row['type'] . '_icon_open') != $row['type'] . '_icon_open') {
                $icon = $this->str($row['type'] . '_icon_open');
            } else {
                $icon = '/admin/images/icons/' . $GLOBALS['cfg']['types'][$root_id][$row['type']]['icon'];
            }
        } else {
            if ($row['next']) {
                $plus_href .= "&id=" . $row['id'];
                if ($params['checkboxtree']) {
                    $plus = "<a href='" . $plus_href . "' onclick='changeBranch(this); return false;' class='icoPlus' title='развернуть'>развернуть</a>";
                } else {
                    $plus = "<a href='" . $plus_href . "' class='icoPlus' title='развернуть'>развернуть</a>";
                }
            }
            if ($this->str($row['type'] . '_icon_closed') != $row['type'] . '_icon_closed') {
                $icon = $this->str($row['type'] . '_icon_closed');
            } else {
                $icon = '/admin/images/icons/' . $GLOBALS['cfg']['types'][$root_id][$row['type']]['icon'];
            }
        }

        $class = $buttons = $href = $title = '';

        if (!$params) {
            if ($row['id'] == $id) {
                // Текущий открытый элемент
                $class = ' open';
                $buttons = $this->GetButtons($row, 0, $hidden_url, $root_id);
                $href = "editor.php?page=" . $this->name . "&id=" . $row['id'] . $hidden_url;
                $title = $this->str('edit_this');
            } else {
                // Не открытый элемент
                $href = "?page=" . $this->name . "&id=" . $row['id'] . $hidden_url;
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
        elseif ($params['treeurl'] || $params['treeid']) {
            if ($row['id'] == $id) {
                $class = ' open';
                $title = $this->str('edit_this');
            } else {
                $title = $this->str('select');
            }
        }
        elseif ($params['checkboxtree']) {
            $href = "javascript:checkbox_click(" . $row['id'] . ")";
        }

        $notes = array();
        # служебный элемент?
        if ($row['protected'] && $this->str('_protected')) {
            $notes[] = $this->str('_protected');
        }
        # скрытый элемент?
        if ($row['visible'] == 0 && $this->str('_invisible')) {
            $notes[] = $this->str('_invisible');
            $class .= ' hide';
        }
        # недоступный элемент?
        if ($row['visible'] < 0) {
            $notes[] = $this->str('_hidden');
            $class .= ' hide';
        }
        if ($notes) {
            $note = join(', ', $notes);
        } else {
            $note = '';
        }

        $item = $row;
        $item['dbclk_href'] = $dbclk_href;
        $item['plus'] = $plus;
        $item['icon'] = $row['is_link'] ? $this->str('link_icon_open') : $icon;
        $item['class'] = $class;
        $item['buttons'] = $buttons;
        $item['href'] = $href;
        $item['title'] = $title;
        $item['note'] = $note;
        if ($params['treeurl']) {
            $item['java_href'] = "javascript:selecturl(" . $row['id'] . ", \"" . addslashes($row['name']) . "\", \"" . $row['dir'] . "\")";
        }
        elseif ($params['treeid']) {
            $item['java_href'] = "javascript:selectid(" . $row['id'] . ", \"" . addslashes($row['name']) . "\", \"" . $row['dir'] . "\")";
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
     * @param bool $html
     * @return string
     */
    function GetPath($id, $do = '', $root_href = '', $html = true) {

        global $site_domains;
        if (count($site_domains) > 1 && !$id) return '';

        if ($id) {
            $root_id = sql_getValue("SELECT root_id FROM tree WHERE id='" . $id . "'");
        }
        if (!$root_id && $this->department_root) return '';

        if ($do) $do = '&do=' . $do;
        $root_href = $root_href ? $root_href : "?page=" . $this->name . $do;

        $site = '';
        foreach ($site_domains as $s) {
            foreach ($s['langs'] as $l) {
                if ($l['root_id'] == $root_id) {
                    $site = $s['name'];
                    break;
                }
            }
        }

        if ($this->department_root) {
            $name = sql_getValue("SELECT name FROM tree WHERE id=" . $root_id);
            $path = "<a href='" . $root_href . "'>" . $site . $name . "</a>";
            if (!$id) {
                $this->pids[] = $root_id;
                return $path;
            }

            $current = sql_getRow("SELECT id, name, pids FROM " . $this->table . " WHERE id=" . (int)$id);
            $this->pids = $this->GetPids($current['pids']);

            if (!in_array($id, $this->pids)) {
                $this->pids[] = $id;
            }
        } else {
            if ($id) {
                $current = sql_getRow("SELECT id, name, pids FROM " . $this->table . " WHERE id=" . (int)$id);
                $this->pids = $this->GetPids($current['pids']);
                foreach ($this->pids as $k => $v) {
                    if ($v != $this->department_id) {
                        unset($this->pids[$k]);
                    } else {
                        break;
                    }
                }
                $this->pids = array_values($this->pids);
                $this->pids[] = $id;
            } else {
                $this->pids[] = $this->department_id;
            }
        }

        $rows = sql_query("SELECT id, name FROM " . $this->table . " WHERE id IN (" . join(',', $this->pids) . ") ORDER BY level ASC, id ASC, priority ASC");
        if ($rows) {
            $path_html = "";
            $path = array();
            while ($row = mysql_fetch_assoc($rows)) {
                if ($row['id'] == $id) {
                    $path_html .= "<span>" . $row['name'] . "</span>";
                } else {
                    $path_html .= "<a href='/admin/?page=" . $this->name . "&id=" . $row['id'] . $do . "'>" . $row['name'] . "</a>";
                }
                $path[] = str_replace('"', '&quot;', $row['name']);
            }
        } else {
            echo 'GetPath err: ' . mysql_error();
        }
        //$path_html.= "<span>".$current['name']."</span>";

        return $html ? $path_html : implode(" / ", $path);
    }


    ######################
    #
    # prints current buttons
    #
    function GetButtons($current, $count = 0, $hidden_url = '', $root_id) {
        $id = (int)$_GET['id'];
        if (!$id) return "";

        $root_id = $root_id ? $root_id : domainRootID();
        # generate type links
        $buttons = '';

        //цикл по типам разделов, с иконками
        $tree_types = &$GLOBALS['cfg']['types'][$root_id];
        $nested = $GLOBALS['cfg']['types'][$root_id][$current['type']]['nested'];
        foreach ($tree_types AS $key => $value) {
            if ($key != 'home' && in_array($key, $nested)) {
                $buttons .= "<a title='" . $this->str('create_title') . ": " . $value[0] . "' href='editor.php?page=" . $this->name . "&id=0&type=" . $key . "&pid=" . $id . $hidden_url . "' class='icoNav' style='background-image:url(/admin/images/icons/" . $value['icon'] . ");'>" . $this->str('create_title') . ": " . $value[0] . "</a>";
            }
        }

        // Удалить
        $buttons .= "<a title='" . $this->str('delete') . "' href='?do=Delete&page=" . $this->name . "&id=$id&pid=" . $current["pid"] . $hidden_url . "' onclick='return confirm(\"" . $this->str('del_folder') . ": " . addslashes($current["name"]) . "?\")' class='icoNav' style='background-image:url(/admin/images/tree/ico_del.gif);'>" . $this->str('delete') . "</a>";

        // Копировать
        $buttons .= "<a title='" . $this->str('copy') . "' href='javascript:window.open(\"dialog.php?page=" . $this->name . "&do=CopyDlg&src=" . $id . "&id=" . $id . "\",\"treecopy\",\"scrollbars=yes, resizable=yes, width=400, height=420\").focus()' class='icoNav' style='background-image:url(/admin/images/tree/ico_copy.gif);'>" . $this->str('copy') . "</a>";

        // Превью
        $lang = getLangByRootID($root_id);
        $site = getSiteByRootID($root_id);
        $site = $site ? $site : $_SERVER['HTTP_HOST'];
        //$dir_lang = $lang != LANG_DEFAULT ? '/' . $lang : '';
        $dir_lang = '/' . $lang;
        $buttons .= "<a title='" . $this->str('preview') . "' href='http://" . $site . $dir_lang . $current['dir'] . "' class='icoNav' style='background-image:url(/admin/images/tree/ico_see.gif);' target=_blank>" . $this->str('preview') . "</a>";

        // Приоритет
        $max_priority = sql_getValue('SELECT MAX(priority) FROM ' . $this->table . ' WHERE pid=' . $current['pid'] . ' AND id<>pid AND root_id=' . $root_id);
        $min_priority = sql_getValue('SELECT MIN(priority) FROM ' . $this->table . ' WHERE pid=' . $current['pid'] . ' AND id<>pid AND root_id=' . $root_id);

        $buttons .= "<a title='" . $this->str('parity') . " (" . $current['priority'] . ")' href='?page=" . $this->name . "&do=enum&pid=" . $current['pid'] . "' class='icoNav' style='background-image:url(/admin/images/tree/ico_sorting.gif);'>" . $this->str('parity') . " (" . $current['priority'] . ")</a>";
        if ($current['priority'] > $min_priority) {
            $buttons .= "<a title='" . $this->str('up') . "' href='?page=" . $this->name . "&do=editswap&id=" . $id . "&src=" . $current['priority'] . "&trg=" . ($current['priority'] - 1) . "&pid=" . $current['pid'] . "' class='icoNav' style='background-image:url(/admin/images/tree/ico_up.gif);'>" . $this->str('up') . "</a>";
        }
        if ($current['priority'] < $max_priority) {
            $buttons .= "<a title='" . $this->str('down') . "' href='?page=" . $this->name . "&do=editswap&id=" . $id . "&src=" . $current['priority'] . "&trg=" . ($current['priority'] + 1) . "&pid=" . $current['pid'] . "' class='icoNav' style='background-image:url(/admin/images/tree/ico_down.gif);'>" . $this->str('down') . "</a>";
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
        $src_page = sql_getValue('SELECT page FROM ' . $this->table . ' WHERE id = ' . $src);

        $ret['from'] = $this->str('from');
        $ret['from_path'] = $this->GetPath($src, 'CopyDlg&src=' . $src);
        $ret['to'] = $this->str('to');
        $ret['to_path'] = $this->GetPath($id, 'CopyDlg&src=' . $src);

        $ret['copy_to'] = $this->str('copy_to');
        $ret['copy'] = $this->str('copy');
        $ret['copy_disabled'] = (in_array($src_page, $dir) || $src == $id ? 'DISABLED' : '');
        $ret['move'] = $this->str('move');
        $ret['move_disabled'] = (in_array($src_page, $dir) || $src == $id ? 'DISABLED' : '');

        $ret['id'] = $id;
        $ret['src'] = $src;
        $ret['name'] = $this->name;
        $ret['trees'] = $this->GetAllTree(array('id' => $id, 'src' => $src, 'copy' => true));

        return $this->Parse($ret, 'tree.copy.tmpl');
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
            $root_id = sql_getValue("SELECT root_id FROM " . $this->table . " WHERE id='" . (int)$trg . "'");
            $this->Validate(0, '', 0, array(), $root_id);
        } elseif (isset($_GET['copy']) && $src) {
            # копирование (каскадное)
            $this->CopyTree($src, $trg);
            $root_id = sql_getValue("SELECT root_id FROM " . $this->table . " WHERE id='" . (int)$trg . "'");
            $this->Validate(0, '', 0, array(), $root_id);
        } else {
            page_error($this->str('e_unknown'));
            return;
        }

        echo "
		Done. Closing window...
		<script type='text/javascript'>
		if (opener) opener.location.href = '/admin/?page=tree&id=" . $trg . "';
		window.close();
		</script>
		";
    }

    /**
     * Возвращает имя колонки с типом autoincrement
     * @param $columns
     * @return string|false
     */
    function isAutoIncrement($columns) {
        foreach ($columns as $key => $val) {
            if ($val['Extra'] == 'auto_increment') return $val['Field'];
        }
        return false;
    }

    /**
     * Проставляет root_id в ветке
     * @param $src
     * @param int $pid
     */
    function repaintRoot_id($src, $pid = 0) {
        if ($pid == 0) {
            $pid = $src['id'];
        }
        sql_query("UPDATE tree SET root_id=" . $src['root_id'] . " WHERE id=" . $pid);
        $home = sql_getRows("SELECT id FROM tree WHERE pid = " . $pid);
        foreach ($home as $v) {
            # по рекурсии обновляем все поля
            $this->repaintRoot_id($src, $v);
        }
    }

    /**
     * Перемещает раздел
     * @param $src_id
     * @param $trg_id
     */
    function MoveTree($src_id, $trg_id) {
        $src = sql_getRow("SELECT * FROM " . $this->table . " WHERE id='" . $src_id . "'");
        $trg = sql_getRow("SELECT * FROM " . $this->table . " WHERE id='" . $trg_id . "'");

        # Проверяем root_id  перед вставкой
        $pid = $trg['id'];
        $err = sql_getValue("SELECT root_id FROM tree WHERE id = " . $pid);
        $err = sql_getErrNo();
        if (!$err) { //если есть поле root_id
            do {
                $home = sql_getRow("SELECT pid,root_id FROM tree WHERE id = " . $pid);
                // если все таки не нашли то останавливаемся , когда добежали до корня
                if ($pid == $home['pid']) {
                    $home['root_id'] = $pid;
                    break;
                }
                $pid = $home['pid'];
            }
            while ($pid);

            $src['root_id'] = $home['root_id'];

            # обновляем root_id для всех вложенных
            $this->repaintRoot_id($src);
        }

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
    function Validate($pid = 0, $page = '', $level = 0, $data = array(), $root_id) {
        $level++;
        $root_id = $root_id ? $root_id : domainRootID();
        $pid = $pid ? $pid : $root_id;
        $data['pids'][$level] = $pid;
        if ($page) $data['page'][$level] = $page;

        if (defined('LANG_SELECT') && LANG_SELECT) $name_select = "IF (name_" . lang() . " <> '', name_" . lang() . ", name_" . LANG_DEFAULT . ") as name";
        else $name_select = "name";

        $rows = sql_query("SELECT id, pid, $name_select, type, page FROM " . $this->table . " WHERE pid=" . $pid . " AND id<>pid ORDER BY priority,name");
        if ($rows) {
            while ($row = mysql_fetch_assoc($rows)) {
                $sets = array();

                # nextid
                $sets[] = 'next=0';

                # pids
                $sets[] = "pids='/" . join("/", $data["pids"]) . "/'";

                # levels
                $sets[] = 'level=' . $level;

                # dir
                if (!$row['page']) {
                    $row['page'] = $row['id'];
                    $sets[] = "page='" . $row['page'] . "'";
                }
                if (sizeof($data['page'])) $sets[] = "dir='/" . join("/", $data['page']) . "/" . $row['page'] . "/'";
                else $sets[] = "dir='/" . $row["page"] . "/'";

                $res = sql_query("UPDATE " . $this->table . " SET " . join(', ', $sets) . " WHERE id=" . $row['id']);
                if ($row['pid']) {
                    $res = sql_query("UPDATE " . $this->table . " SET next=" . $row['id'] . " WHERE id=" . $row['pid']);
                }

                $this->check_id[] = $row['id'];
                $this->Validate($row['id'], $row['page'], $level, $data, $root_id);
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

        $root_id = sql_getValue("SELECT root_id FROM " . $this->table . " WHERE id='" . (int)$trg . "'");

        # Вставляем
        $src_row['dir'] = '';
        if ($src_row['page'] && (!is_numeric($src_row['page']) || $src_row['page'] == '404')) {
            $src_row['page'] = preg_replace('/(.*?)\d*$/', '\1', $src_row['page']);
            $count = sql_getValue('SELECT COUNT(1) FROM ' . $this->table . '
            WHERE page = "' . $src_row['page'] . '" AND root_id="' . $root_id . '" AND pid="' . $trg . '"');
            $src_row['page'] = $count ? $src_row['page'] . $count : $src_row['page'];
        } else {
            $src_row['page'] = $next_id;
        }

        $src_row['dir'] = 'tmp_' . count($counter);
        $src_row['root_id'] = $root_id;

        $columns = sql_getRows("SHOW columns FROM tree");
        foreach ($columns as $col) {
            if ($col['Null'] == 'YES' && !$src_row[$col['Field']]) {
                unset($src_row[$col['Field']]);
            }
        }

        $trg = sql_insert($this->table, $src_row);

        // копируем все элементы
        $src_cfg = $GLOBALS['cfg']['types'][$src_row['root_id']][$src_row['type']];
        foreach ($src_cfg['elements'] as $elem) {
            $tables = sql_getRows("SHOW tables LIKE '" . $elem . "'");
            if ($tables) {
                $src_elems = sql_getRows("SELECT * FROM " . $elem . " WHERE pid=" . $src);
                $columns = sql_getRows("SHOW columns FROM " . $elem);
                $auto_increment_column = $this->isAutoIncrement($columns);
                foreach ($src_elems as $src_elem) {
                    // Удаляем id только если он не нужен, т.е. если колонка id auto_increment
                    // Иначе нужно оставить, например, в случае копирования elem_product
                    if ($auto_increment_column) {
                        $auto_increment_column_value = $src_elem[$auto_increment_column];
                        unset($src_elem[$auto_increment_column]);
                    }
                    $src_elem['pid'] = $trg;
                    $new_elem_id = sql_insert($elem, $src_elem);
                    if (!is_int($new_elem_id)) {
                        // error
                    }
                    if ($elem == 'elem_form') {
                        $form_elems = sql_getRows("SELECT * FROM elem_form_elems WHERE pid=" . $auto_increment_column_value);
                        if ($form_elems) {
                            foreach ($form_elems as $f) {
                                $save_f_id = $f['id'];
                                $f['pid'] = $new_elem_id;
                                unset($f['id']);

                                $f_data = array();
                                foreach ($f as $k2 => $v2) {
                                    $f_data['`' . $k2 . '`'] = $v2;
                                }

                                $__id = sql_insert('elem_form_elems', $f_data);
                                if (is_int($__id)) {
                                    $form_values = sql_getRows("SELECT * FROM elem_form_values WHERE pid=" . $save_f_id);
                                    if ($form_values) {
                                        foreach ($form_values as $f2) {
                                            $f2['pid'] = $__id;
                                            unset($f2['id']);
                                            sql_insert('elem_form_values', $f2);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        # Ищем дочерние ветви и запускаем заново эту функцию
        $rows = sql_getColumn("SELECT id FROM " . $this->table . " WHERE pid=" . $src);
        if ($rows) foreach ($rows as $row) $this->CopyTree($row, $trg, false);
    }

    ######################
    #
    function Delete() {
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;

        $pid = sql_getValue("SELECT pid FROM " . $this->table . " WHERE id=" . $id);
        $res = sql_query("UPDATE " . $this->table . " SET visible=-1 WHERE id=" . $id);

        # проставляем next
        if ($res) {
            $count = (int)sql_getValue("SELECT COUNT(*) FROM " . $this->table . " WHERE pid=" . $pid . " AND visible>=0");
            sql_query("UPDATE " . $this->table . " SET next=" . $count . " WHERE id=" . $pid);
            if (!$count) {
                $pid = sql_getValue("SELECT pid FROM " . $this->table . " WHERE id=" . $pid);
            }
            touch_cache($this->table);
        }

        return "<script>location.href='/admin/?page=" . $this->name . "&id=" . $pid . "';</script>";
    }

    ######################
    // на случай если где-то остались старые вызовы функции
    function Swap() {
        return EditSwap();
    }

    function EditSwap() {
        # (src)id, (desired)trg, (current)src, pid
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $trg = isset($_GET['trg']) ? (int)$_GET['trg'] : 0;
        $src = isset($_GET['src']) ? (int)$_GET['src'] : 0;
        $pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
        $root_id = sql_getValue("SELECT root_id FROM " . $this->table . " WHERE id='" . $id . "'");

        if ($id && ($trg * $src)) {

            # Получаем id элемента для замены
            $trg_id = sql_getValue("SELECT id FROM " . $this->table . " WHERE priority=" . $trg . " AND pid=" . $pid . " AND root_id='" . $root_id . "'");
            if (!$trg_id) {
                // Делаем автонумерацию, если ошибка
                $this->Enum($pid, true);
                $trg_id = sql_getValue("SELECT id FROM " . $this->table . " WHERE priority=" . $trg . " AND pid=" . $pid . " AND root_id='" . $root_id . "'");
                // Если нумерация не помогла, выдаем ошибку.
                if (!$trg_id) {
                    die('TTree.EditSwap:[no target id]');
                }
            }

            # Заменяем target
            mysql_query("UPDATE " . $this->table . " SET priority=" . $src . " WHERE id=" . $trg_id) or die('TTree.EditSwap: update trg failed');

            # Заменяем src
            mysql_query("UPDATE " . $this->table . " SET priority=" . $trg . " WHERE id=" . $id) or die('TTree.EditSwap: update src failed');
        }
        return "<script>location.href='/admin/?page=" . $this->name . "&id=" . $id . "';</script>";
    }

    ######################
    function Enum($pid = false, $correct_priority = false) {
        $can_redirect = $pid === false;
        if ($can_redirect) $pid = isset($_GET['pid']) ? $_GET['pid'] : 0;
        $counter = 1;
        $root_id = sql_getValue("SELECT root_id FROM " . $this->table . " WHERE id='" . $pid . "'");
        if (!$root_id) die("TTree.Enum unknown root_id");

        if ($correct_priority) {
            $rows = mysql_query("SELECT id FROM " . $this->table . " WHERE pid=" . $pid . " AND root_id='" . $root_id . "' AND id<>pid ORDER BY priority, name");
        } else {
            $rows = mysql_query("SELECT id FROM " . $this->table . " WHERE pid=" . $pid . " AND root_id='" . $root_id . "' AND id<>pid ORDER BY name");
        }
        if ($rows) while ($row = mysql_fetch_assoc($rows)) {
            mysql_query("UPDATE " . $this->table . " SET priority=" . $counter . " WHERE id=" . $row['id']);
            $counter++;
        }
        if ($can_redirect) return "<script>location.href='" . $_SERVER['HTTP_REFERER'] . "';</script>";
    }

    /**
     * Формирование html-кода для input_treeid
     */
    function showTreeLinkHtml() {
        $id = (int)get('id', 0, 'p');
        $fld = get('fld', '', 'p');
        $path_id = get('path', '', 'p');
        $html = "";
        if (!$id) {

        } else {
            global $site_domains;
            $row = sql_getRow("SELECT * FROM tree WHERE id=" . $id);
            $path = '';
            if (count($site_domains) > 1) $path .= getSiteByRootID($row['root_id']) . ' (' . getLangByRootID($row['root_id']) . '): ';
            $path .= $this->getPath($id, '', '', false);
            $html .= '<div class="textBox">';
            $html .= '<a href="javascript:void(0);" onclick="reset_selectid(\'' . str_replace(array("[", "]"), array("\\\\[", "\\\\]"), $fld) . '\');"><img src="/admin/images/tree/ico_del.gif" style="float:left" alt="Удалить"></a>&nbsp;';
            $html .= $path;
            $html .= '&nbsp;<a href="' . (count($site_domains) > 1 ? 'http://' . getSiteByRootID($row['root_id']) . '/' . getLangByRootID($row['root_id']) . $row['dir'] . $path_id : $row['dir'] . $path_id) . '" target="_blank"><img src="/admin/images/tree/ico_see.gif" alt="Просмотр"></a>';
            $html .= '</div><div class="textBox"><a href="./dialog.php?page=tree/treeid&fieldname=' . $fld . '&formname=&returnid=treeid_' . $fld . '" onclick="frame_button(this); return false;">Изменить раздел</a></div>';
        }

        echo json_encode(array('html' => iconv('windows-1251', 'utf-8', $html)));
        die();
    }

    /**
     * Формирование html-кода для input_treecheck
     */
    function showTreeLinksHtml() {
        $ids = get('ids', array(), 'p');
        $fld = get('fld', '', 'p');
        $path_id = get('path', '', 'p');
        $html = "";
        if (!$ids) {

        } else {
            global $site_domains;
            $rows = sql_getRows("SELECT * FROM tree WHERE id IN (" . $ids . ")", true);
            $ids = explode(",", $ids);
            foreach ($ids as $id) {
                $id = (int)trim($id);
                if (!$id) continue;
                $path = '';
                if (count($site_domains) > 1) $path .= getSiteByRootID(@$rows[$id]['root_id']) . ' (' . getLangByRootID(@$rows[$id]['root_id']) . '): ';
                $path .= $this->getPath($id, '', '', false);
                $html .= '<div class="textBox">';
                $html .= '<a href="javascript:void(0);" onclick="remove_from_treecheck(\'' . $id . '\', \'' . str_replace(array("[", "]"), array("\\\\[", "\\\\]"), $fld) . '\', this);"><img src="/admin/images/tree/ico_del.gif" style="float:left" alt="Удалить"></a>&nbsp;';
                $html .= $path;
                $html .= '&nbsp;<a href="' . (count($site_domains) > 1 ? 'http://' . getSiteByRootID(@$rows[$id]['root_id']) . '/' . getLangByRootID(@$rows[$id]['root_id']) . $rows[$id]['dir'] . $path_id : @$rows[$id]['dir'] . $path_id ) . '" target="_blank"><img src="/admin/images/tree/ico_see.gif" alt="Просмотр"></a>';
                $html .= '</div>';
            }
        }

        echo json_encode(array('html' => iconv('windows-1251', 'utf-8', $html)));
        die();
    }
}

$GLOBALS['tree'] = & Registry::get('TTree');