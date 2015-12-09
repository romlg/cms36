<?php

/**
 * Модуль публикаций
 */
class TPublicationsBase extends TTable
{

    var $name = 'publications';
    var $table = 'publications';
    var $elements = array(
        'elem_meta',
        'elem_gallery',
        'elem_file'
    );
    var $columns_default = ""; // поля для отображения в подключаемом elem-е, если не заданы свои
    var $selector = true;
    var $where_extra = '';

    ########################

    function TPublicationsBase() {
        global $actions, $str;

        TTable::TTable();

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
        );
        if ((int)$_GET['id']) {

            // проверим есть ли таблица сайтов и языков
            $tables = sql_getRows("SHOW TABLES ");
            if (in_array('sites_langs', $tables)) {
                $sql = "SELECT CONCAT('http://', sites.name, '/', sites_langs.name, tree.dir) FROM sites, sites_langs, tree, publications WHERE tree.id=publications.pid AND tree.root_id=sites_langs.root_id AND sites.id=sites_langs.pid AND publications.id='" . (int)$_GET['id'] . "'";
            }
            elseif (in_array('sites', $tables)) {
                $sql = "SELECT CONCAT('http://', sites.name, tree.dir) FROM sites, tree, publications WHERE tree.id=publications.pid AND tree.root_id=sites.root_id AND publications.id='" . (int)$_GET['id'] . "'";
            }
            else {
                $sql = "SELECT tree.dir FROM tree, publications WHERE tree.id=publications.pid AND publications.id='" . (int)$_GET['id'] . "'";
            }
            $dir = sql_getValue($sql);

            if ($dir) {
                $click = $dir . "p/" . (int)$_GET['id'];
                $click = "newWin = window.open('" . $click . "', '_blank', 'menubar=yes,toolbar=yes,location=yes,directories=yes,resizable=yes,scrollbars=yes,status=yes');newWin.focus();";
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
        }
        else {
            $temp = "Новая публикация";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Публикации', 'Publications',),
            'title_editform' => array("Публикация: " . $temp, 'Publication : ' . $temp,),

            'date' => array('Дата', 'Date',),
            'name' => array('Название', 'Title',),
            'dir' => array('Название раздела', 'Section Name',),
            'visible' => array('Показывать', 'Visible',),
            'pid' => array('Раздел', 'Section',),
            'showInSite' => array('Просмотр на сайте', 'Show in site',),

            'saved' => array(
                'Даные были успешно сохранены',
                'Data has been saved successfully',
            ),
        ));

        global $site_domains;
        foreach ($site_domains as $s) {
            foreach ($s['langs'] as $l) {
                $str[get_class_name($this)][$s['name'] . ' (' . $l['descr'] . ')'] = array($s['name'] . ' (' . $l['descr'] . ')', $s['name'] . ' (' . $l['descr'] . ')');
            }
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
                'select' => 'name',
                'display' => 'name',
                'flags' => FLAG_SEARCH,
            ),
            array(
                'select' => 'date',
                'type' => 'date',
                'display' => 'date',
                'flags' => FLAG_SORT,
            ),
        );
    }

    function Show() {
        if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }

        // проверим есть ли pid и если есть то относится ли к нужному root_id
        $filter = get('filter', array());
        if ($filter['all_pids']) {
            $page_root_id = sql_getValue("SELECT root_id FROM `tree` WHERE id='".(int)$filter['all_pids']."'");
            if ($page_root_id != domainRootID()) $_GET['filter']['all_pids'] = 0;
        }

        $tree = array();
        $root_id = domainRootId();
        if ($root_id > 0 && allowDomainForUser($root_id)) {
            $tree = $tree + $this->getPages((int)$root_id);
            $temp = $tree;
            $tree = array();
            foreach ($temp as $val) $tree[$val['id']] = $val['name'];
        }
        $filter_pid_value = array('' => 'все') + $tree;

        // строим таблицу
        require_once (core('list_table'));
        $data['table'] = list_table(array(
            'columns' => array(
                array(
                    'select' => 'p.id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => 'p.name',
                    'display' => 'name',
                    'flags' => FLAG_SORT | FLAG_SEARCH,
                ),
                array(
                    'select' => 'UNIX_TIMESTAMP(p.date)',
                    'as' => 'date',
                    'display' => 'date',
                    'flags' => FLAG_SORT | FLAG_FILTER,
                    'filter_type' => 'date',
                    'filter_value' => 'date',
                    'type' => 'date',
                ),
                array(
                    'select' => 'p.pid',
                    'display' => 'pid',
                    'type' => 'dir',
                ),
                (($root_id > 0)?
                array(
                    'select' => 'CAST(CONCAT(p.pid,\',\',p.pids) AS CHAR)',
                    'as' => 'all_pids',
                    'flags' => FLAG_FILTER,
                    'filter_type' => 'array',
                    'filter_value' => $filter_pid_value,
                    'filter_display' => 'pid',
                    'filter_rule' => 'find_in_set',
                )
                :
                array(
                    'select' => 'CAST(CONCAT(p.pid,\',\',p.pids) AS CHAR)',
                    'as' => 'all_pids',
                )),
                array(
                    'select' => 'p.visible',
                    'display' => 'visible',
                    'type' => 'visible',
                    'flags' => FLAG_SORT | FLAG_FILTER,
                    'filter_type' => 'array',
                    'filter_value' => array('') + array('1' => 'Да', '2' => 'Нет'),
                    'filter_field' => 'IF(p.visible=0,2,1)'
                ),
                array(
                    'select' => 'p.id',
                    'display' => 'showInSite',
                    'type' => 'show',
                ),
                array(
                    'select' => 't.dir',
                ),
                array(
                    'select' => 't.root_id',
                ),
                array(
                    'select' => 't.name',
                    'as' => 't_name',
                    'flags' => FLAG_SEARCH
                ),
                array(
                    'select' => 'p.notice',
                    'flags' => FLAG_SEARCH
                ),
                array(
                    'select' => 'p.text',
                    'flags' => FLAG_SEARCH
                ),
            ),
            'from' => $this->table . " as p
			LEFT JOIN `tree` as t ON t . id = p . pid
			",
            'where' => ((domainRootId() > 0) ? ' (t.root_id=' . domainRootId() . ' OR t.root_id IS NULL OR FIND_IN_SET(' . domainRootId() . ', (SELECT GROUP_CONCAT( DISTINCT CAST(root_id AS CHAR)) FROM tree WHERE FIND_IN_SET(id, CONCAT(p.pid, ",", p.pids)))) )' : '') . $this->where_extra,
            'orderby' => 'p.date DESC',
            // всегда передается это
            'params' => array('page' => $this->name, 'do' => 'show'),
            'dblclick' => 'editItem(p.id)',
            'click' => 'ID=cb.value',
            //'_sql' => true,
        ), $this);

        $data['table'] .= "<script type='text/javascript'>
        $('ul.navPanel').find('a:first').attr('onclick','').click(function(){
            $('.createbox').show().find('input[type=text]').focus();
        });
        </script>
        <style type='text/css'>
        .createbox {
            width:307px;
            height:100px;
            background-color:#fff;
            border:2px solid #FAAE3E;
            position:fixed;
            left:550px;
            top:400px;
            display:none;
            padding:10px;
            box-shadow:5px 5px 5px rgba(0,0,0,0.5);
        }
            .createbox .close {
                text-decoration:none;
                position:relative;
                top:-7px;
                left:304px;
                font-size:16px;
                color:#f00;
            }
        </style>
        <div class='createbox'>
            <a href='javascript:void(0);' onclick='$(\".createbox\").hide();' class='close'>X</a>
            <form action='' method='post'>
                <input type='hidden' name='page' value='{$this->name}' />
                <input type='hidden' name='do' value='editCreate' />
                <!--input type='hidden' name='ref' value='/admin/editor.php?page={$this->name}' /-->
                <label>Введите название новой публикации:</label>
                <input type='text' class='text' name='fld[name]' id='fld_name' value='' />
                <a href='javascript:void(0);' onclick='if($(this).parent().find(\"#fld_name\").val()) $(this).parent().submit(); else alert(\"Вы не ввели название публикации\");' class='button' style='position:relative;left:90px;top:5px;'><span>Создать</span></a>
            </form>
        </div>";

        $this->AddStrings($data);
        return $this->Parse($data, LIST_TEMPLATE);
    }

    function editCreate() {
        $name = str_replace("&", "=+=+=+=", $_POST['fld']['name']);
        $name = htmlspecialchars($name);
        $name = str_replace("=+=+=+=", "&", $name);
        $id = sql_insert($this->table, array('name' => $name, 'date' => date('Y-m-d H:i:s')));
        if (is_int($id)) {
            HeaderExit("/admin/editor.php?page={$this->name}&id=" . $id);
        }
        else {
            die($id);
        }
    }

    // дерево разделов для select списка
    function getPages($root_id, $type) {
        $tree = array();
        $maxlevel = (int)sql_getValue("SELECT MAX(`level`) FROM `tree` WHERE `visible`>-1 AND `root_id`='" . (int)$root_id . "'");
        if (!$maxlevel) return false;
        $ptr =& $tree;
        for ($i = 1; $i <= $maxlevel; $i++) {
            $sql = "SELECT `id`,`pid`,`name` FROM `tree` WHERE `visible`>-1 AND `root_id`='" . (int)$root_id . "' AND `level`=$i AND `visible`>0 ORDER BY `priority` ASC";
            $rows[$i] = sql_getRows($sql, false);
            if (isset($rows[$i - 1])) {
                foreach (array_keys($rows[$i]) as $c) {
                    foreach (array_keys($rows[$i - 1]) as $p) {
                        if ($rows[$i][$c]['pid'] == $rows[$i - 1][$p]['id']) $rows[$i - 1][$p]['items'][] =& $rows[$i][$c];
                    }
                }
            }
        }
        $pages = array();
        $this->recursive(&$pages, $rows[1], 1, $type);
        return $pages;
    }

    function recursive($ret, $items, $lvl = 1, $type) {
        $margin_once = "---";
        $margin = "";
        for ($i = 1; $i < $lvl; $i++) $margin .= $margin_once;
        foreach ($items as $item) {
            $ret[$item['id']] = array(
                'id' => $item['id'],
                'name' => $margin . ' ' . $item['name'],
                'type' => $type,
            );
            if (isset($item['items'])) $this->recursive(&$ret, $item['items'], $lvl + 1, $type);
        }
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
     * Отображение раздела (главного)
     * @param $value
     * @param $column
     * @param $row
     * @return string
     */
    function table_get_dir(&$value, &$column, &$row) {
        if (!$row['dir']) return "";
        $tables = sql_getRows("SHOW TABLES ");
        if (in_array('sites', $tables) && in_array('sites_langs', $tables)) {
            $site_name = sql_getValue("SELECT CONCAT(sites.name, '/', sites_langs.name) FROM sites, sites_langs WHERE sites.id=sites_langs.pid AND sites_langs.root_id='" . (int)$row['root_id'] . "'");
        }
        else {
            $site_name = "";
        }
        $publSiteUrl = ($site_name) ? "http://" . $site_name . $row['dir'] : "";
        $res = "<a href = '/admin/?page={$this->name}&filter%5Ball_pids%5D={$row['pid']}' >{$row['t_name']}</a > ";
        $res .= "&nbsp;&nbsp;<a href='{$publSiteUrl}' title='Посмотреть на сайте' target='_blank'><img src='/admin/images/icons/icon.preview.png' /></a>";
        return $res;
    }

    /**
     *
     * Урл публикации на сайте
     *
     * @param $value
     * @param $column
     * @param $row
     * @return string
     */
    function table_get_show(&$value, &$column, &$row) {
        if (!$row['dir']) return "";
        $tables = sql_getRows("SHOW TABLES ");
        if (in_array('sites', $tables) && in_array('sites_langs', $tables)) {
            $site_name = sql_getValue("SELECT CONCAT(sites.name, '/', sites_langs.name) FROM sites, sites_langs WHERE sites.id=sites_langs.pid AND sites_langs.root_id='" . (int)$row['root_id'] . "'");
        }
        else {
            $site_name = "";
        }
        $url = ($site_name) ? "http://" . $site_name . $row['dir'] . 'p/' . $value : $row['dir'] . 'p/' . $value;
        $res = "<a href='{$url}' title='Посмотреть на сайте' target='_blank'><img src='/admin/images/icons/icon.preview.png' /></a>";
        return $res;
    }

}

?>