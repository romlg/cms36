<?php

/**
 * Модуль инфоблоки
 */
class TInfoblocks extends TTable
{

    var $name = 'infoblocks';
    var $table = 'infoblocks';
    var $elements = array('elem_blockstart', 'elem_publications', 'elem_blockend', 'elem_rule');
    var $elements_titles = array(
        'elem_blockstart' => array('Начало блока', 'Block start'),
        'elem_publications' => array('Публикации', 'Publications'),
        'elem_blockend' => array('Конец блока', 'Block end'),
        'elem_rule' => array('Правила показа', 'Rules'),
    );

    ########################

    function TInfoblocks() {
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
            $temp = "Новый инфоблок";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Инфоблоки', 'Infoblocks',),
            'title_editform' => array("Инфоблок: " . $temp, 'Infoblock: ' . $temp,),

            'name' => array('Заголовок', 'Title',),
            'position' => array('Место', 'Position',),
            'visible' => array('Показывать', 'Visible',),
            'announce' => array('Анонсировать', 'Announce',),
            'root_id' => array('На каком сайте показывать', 'Show at sites'),
            'showurl' => array('Показ', 'Show'),
            'hiddenurl' => array('Запрет', 'Hidden'),
            'saved' => array(
                'Даные были успешно сохранены',
                'Data has been saved successfully',
            ),
        ));
    }

    ########################

    function Show() {
        if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }

        $positions = $this->getFilterPositions();
        if (!$positions) echo "<p style='color: red;'>Не задан массив infoblocks_positions в файле settings.cfg.php</p>";

        // строим таблицу
        require_once (core('list_table'));
        $columns = array(
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
            (domainRootID()>0?
            array(
                'select' => 'p.position',
                'display' => 'position',
                'type' => 'position',
                'flags' => FLAG_FILTER,
                'filter_type' => 'array',
                'filter_value' => array('') + $positions,
            )
            :
            array(
                'select' => 'p.position',
                'display' => 'position',
                'type' => 'position',
                'flags' => FLAG_SEARCH,
            )
            ),
            array(
                'select' => 'IF(p.visible=1,1,2)',
                'as' => 'visible',
                'display' => 'visible',
                'type' => 'visible',
                'flags' => FLAG_SORT | FLAG_FILTER,
                'filter_type' => 'array',
                'filter_value' => array('') + array('1' => 'Да', '2' => 'Нет'),
            ),
            array(
                'select' => 'IF(p.publ_announce=1,1,2)',
                'as' => 'publ_announce',
                'flags' => FLAG_FILTER,
                'filter_type' => 'array',
                'filter_display' => 'announce',
                'filter_value' => array('') + array('1' => 'Да', '2' => 'Нет'),
            ),
            array(
                'select' => 'p.priority',
                'display' => 'priority',
                'flags' => FLAG_SORT,
            ),
            array(
                'select' => 'p.id',
                'display' => 'showurl',
                'type' => 'showurl',
            ),
            array(
                'select' => 'p.id',
                'display' => 'hiddenurl',
                'type' => 'hiddenurl',
            ),
            array(
                'select' => 'p.title',
                'flags' => FLAG_SEARCH,
            ),
            array(
                'select' => 'p.header_text',
                'flags' => FLAG_SEARCH,
            ),
            array(
                'select' => '(SELECT GROUP_CONCAT(",", ir.url) FROM infoblocks_rules AS ir WHERE p.id=ir.pid GROUP BY p.id)',
                'flags' => FLAG_SEARCH,
            ),
        );

        // проверим сколько сайтов, если несколько то выводим колонку
        global $site_domains;
        $current = current($site_domains);
        if (count($site_domains) > 1 || count($current['langs']) > 1) {
            $columns[] = array(
                'select' => 'p.root_id',
                'display' => 'root_id',
                'type' => 'showsites',
            );
        }

        $data['table'] = list_table(array(
            'columns' => $columns,
            'from' => $this->table . " as p",
            'orderby' => 'p.name ASC',
            'where' => (domainRootId()>0)?'root_id=' . domainRootID():'',
            // всегда передается это
            'params' => array('page' => $this->name, 'do' => 'show'),
            'dblclick' => 'editItem(p.id)',
            'click' => 'ID=cb.value',
            //'_sql' => true,
        ), $this);

        $this->AddStrings($data);
        return $this->Parse($data, LIST_TEMPLATE);
    }

    function table_get_position($value, $row, $column) {
        if (!$value) return '';
        global $settings;
        $position = isset($settings['infoblocks_positions'][$value]) ? $settings['infoblocks_positions'][$value] : '';
        return $position;
    }

    function table_get_visible($value, $row, $column) {
        return $value == 1 ? '+' : '-';
    }

    function table_get_showsites($value, $row, $column) {
        $tables = sql_getRows("SHOW TABLES ");
        if (in_array ('sites_langs', $tables)) {
            $sql = "SELECT CONCAT(sites.name, ' (', sites_langs.descr, ')') FROM sites, sites_langs WHERE sites.id=sites_langs.pid AND sites_langs.root_id='".(int)$value."'";
        } elseif(in_array ('sites', $tables)) {
            $sql = "SELECT sites.name FROM sites WHERE root_id='".(int)$value."'";
        }
        $site = sql_getValue($sql);
        return $site ? $site : 'сайт не выбран';
    }

    function table_get_hiddenurl($value, $row, $column) {
        if (!isset($column['root_id'])) {
            $root_id = sql_getValue("SELECT root_id FROM `".$this->table."` WHERE id='".$column['id']."'");
        } else {
            $root_id = $column['root_id'];
        }

        $tables = sql_getRows("SHOW TABLES ");
        if (in_array ('sites_langs', $tables)) {
            $sql = "SELECT sites.name FROM sites, sites_langs WHERE sites.id=sites_langs.pid AND sites_langs.root_id='".(int)$root_id."'";
        } elseif(in_array ('sites', $tables)) {
            $sql = "SELECT sites.name FROM sites WHERE root_id='".(int)$value."'";
        }
        if ($sql) {
            $site = sql_getValue($sql);
            if ($site) {
                $urls = sql_getRows("SELECT url FROM `infoblocks_rules` WHERE pid='".$value."' AND active=0");
                $toRet = "";
                foreach ($urls AS $url) {
                    if (substr($url,0,1)!='/') $url = "/".$url;
                    $toRet .= "<a href='http://".$site.$url."' target=_blank>".$url."</a> <br>";
                }
                return $toRet;
            }
        } else {
            $urls = sql_getRows("SELECT url FROM `infoblocks_rules` WHERE pid='".$value."' AND active=0");
            $toRet = "";
            foreach ($urls AS $url) {
                if (substr($url,0,1)!='/') $url = "/".$url;
                $toRet .= "<a href='".$url."' target=_blank>".$url."</a> <br>";
            }
            return $toRet;
        }
    }

    function table_get_showurl($value, $row, $column) {
        if (!isset($column['root_id'])) {
            $root_id = sql_getValue("SELECT root_id FROM `".$this->table."` WHERE id='".$column['id']."'");
        } else {
            $root_id = $column['root_id'];
        }

        $tables = sql_getRows("SHOW TABLES ");
        if (in_array ('sites_langs', $tables)) {
            $sql = "SELECT sites.name FROM sites, sites_langs WHERE sites.id=sites_langs.pid AND sites_langs.root_id='".(int)$root_id."'";
        } elseif(in_array ('sites', $tables)) {
            $sql = "SELECT sites.name FROM sites WHERE root_id='".(int)$value."'";
        }
        if ($sql) {
            $site = sql_getValue($sql);
            if ($site) {
                $urls = sql_getRows("SELECT url FROM `infoblocks_rules` WHERE pid='".$value."' AND active=1");
                $toRet = "";
                foreach ($urls AS $url) {
                    if (substr($url,0,1)!='/') $url = "/".$url;
                    $toRet .= "<a href='http://".$site.$url."' target=_blank>".$url."</a> <br>";
                }
                return $toRet;
            }
        } else {
            $urls = sql_getRows("SELECT url FROM `infoblocks_rules` WHERE pid='".$value."' AND active=1");
            $toRet = "";
            foreach ($urls AS $url) {
                if (substr($url,0,1)!='/') $url = "/".$url;
                $toRet .= "<a href='".$url."' target=_blank>".$url."</a> <br>";
            }
            return $toRet;
        }
    }

    function getFilterPositions() {
        global $settings;
        $positions = isset($settings['infoblocks_positions']) ? $settings['infoblocks_positions'] : array();
        return $positions;
    }


}

$GLOBALS['infoblocks'] = & Registry::get('TInfoblocks');