<?php

include_once "phpThumb/image.class.php";
include_once PATH_COMMON .  "models/Publication.php";

/**
 *
 * Модуль публикаций, содержит все, что касается публикаций.
 * Список, отображение одной.
 *
 */
class TPublications
{

    /**
     * @var string  название элемента в url, правится в двух местах, тут и в файле site.cfg.php
     */
    var $_url = 'item';

    /**
     * @var string  таблица в базе с публикациями
     */
    var $_table = 'publications';

    /**
     * @var string  таблица с инфоблоками
     */
    var $_table_blocks = 'infoblocks';

    /**
     * @var string по какому полю сортировать
     */
    var $_orderby = 'date DESC';

    /**
     * @var string дополнительные условия выборки
     */
    var $_where = '';

    /**
     * @var string название класса с моделью публикации
     */
    var $_model = 'Publication';

    /**
     * Установка внутренних параметров
     * @param $params
     */
    function setParams($params) {
        if (!isset($params['url'])) $params['url'] = $this->_url;
        if (!isset($params['model'])) $params['model'] = $this->_model;
        if (!isset($params['table'])) $params['table'] = $this->_table;
        if (!isset($params['orderby'])) $params['orderby'] = $this->_orderby;
        if (!isset($params['where'])) $params['where'] = $this->_where;
        return $params;
    }

    /**
     * Возвращает данные об одной публикации
     * Показывается по url : /{что угодно}/{что угодно}/.../item/{publication_id}/
     *
     * @param $params
     * @return array
     */
    function publication(&$params) {

        $params = $this->setParams($params);

        $id = $this->_getItemId();
        if (!$id) redirect('/404');

        $publication = new $params['model']($id);
        if (!$publication->getData()) redirect('/404');

        if (isset($_POST['publication_comment'])) {
            // сохранение комментария к публикации
            return $this->newComment($publication, $_POST['publication_comment']);
        }

        // если показывать навигацию
        if (isset($params['navig']) && isset($params['navig']['show']) && $params['navig']['show']) {
            $navig = $this->onePublNavig($publication->getData(), $params);
        }

        /**
         * @var TTreeUtils $utils
         */
        $utils = & Registry::get('TTreeUtils');

        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');

        // Пидсы для построения правильных хлебных крошек
        $page = & Registry::get('TPage');
        $url = $this->_getPidURL($params['url']);

        //костыль для проверки print в пути к публикации, надо узнать у Маши как сделать правильно
        if (substr($url, 0, 7) == '/print/') {
            $url = substr($url, 6);
        }

        $pids = $utils->getPidsByUrl($url);
        if ($pids) {
            $page->pids = $pids;
            $page->pids[] = array(
                'name' => $publication->getName()
            );
            $current_page = end($pids);

            // проверим, привязана ли публикация к этому разделу, если нет переход на сам раздел
            if ($current_page['id'] != $publication->getPid() && !in_array($current_page['id'], explode(",", $publication->get("pids")))) {
                redirect($url);
            }

            if ($current_page['id'] != $publication->getPid()) { // находимся не по основному урлу публикации
                $view->assign(array('canonical' => $publication->getMainPath()));
            }
        }
        // Подменяем название страницы на название публикации
        $page->content['name'] = $publication->getName();
        $view->assign(array('content' => $page->content));

        // Мета-теги
        $meta = $publication->getMeta();
        if ($meta['title']) $page->content['elem_meta']['title'] = $meta['title'];
        if ($meta['description']) $page->content['elem_meta']['description'] = $meta['description'];
        if ($meta['keywords']) $page->content['elem_meta']['keywords'] = $meta['keywords'];

        if ($navig) {
            return array('publication' => $publication, 'baseUrl' => $url, 'navig' => $navig);
        } else {
            return array('publication' => $publication, 'baseUrl' => $url);
        }
    }

    /**
     * Сохранение нового комментария
     * @param $publication
     * @param $data
     * @throws Exception
     */
    function newComment($publication, $data) {
        $page_obj = & Registry::get('TPage');
        try {
            if (empty($data['comment'])) {
                throw new Exception('publications_comment_empty_text');
            }

            $user_id = 0;
            /**
             * @var TAuth $auth
             */
            $auth = & Registry::get('TAuth');
            $user = $auth->getCurrentUser();
            if ($page_obj->tpl->messages['publications_comment_only_users'] && !$user) {
                throw new Exception('publications_comment_no_user');
            }
            if ($user) $user_id = $user->getId();

            $data['comment'] = iconv('utf-8', 'windows-1251', $data['comment']);
            $data['name'] = iconv('utf-8', 'windows-1251', $data['name']);

            $_id = $publication->newComment($data['comment'], $user_id, $data['pid'], $data['name']);
            if (!is_int($_id)) {
                throw new Exception($_id);
            }

            $mail_data = array(
                'site_name' => $_SERVER['HTTP_HOST'],
                'href' => $data['dir'],
                'text' => nl2br($data['comment'])
            );
            Notify("SEND_NEW_PUBLICATION_COMMENT", false, $mail_data);

            $success = true;

            $msg = 'publications_comment_success';

        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $success = false;
        }

        $message = (isset($page_obj->tpl->messages[$msg])) ? $page_obj->tpl->get_config_vars($msg) : $page_obj->tpl->get_config_vars('msg_fail');

        ob_clean();
        header('Content-type: application/json; charset=utf-8');
        echo json_encode(array(
            'success' => $success,
            'message' => iconv('windows-1251', 'utf-8', $message),
            'visible' => $page_obj->tpl->messages['publications_comment_moderate'] ? 0 : 1
        ));
        die();
    }

    /**
     * Возвращает предыдущую и следущую публикации для навигации
     * @param array
     */
    function onePublNavig($data, &$params) {
        $tree = & Registry::get('TTreeUtils');
        $tree->setRootId(ROOT_ID);
        $pid = end($tree->getPidsByUrl($this->_getPidURL()));
        // формируем ссылку на предыдущую новость
        $prev = sql_getRow("SELECT * FROM " . $params['table'] . " WHERE visible>0 AND (pid='" . $pid['id'] . "' OR FIND_IN_SET('" . $pid['id'] . "', pids)) AND date>'" . $data['date'] . "' ORDER BY date ASC, name ASC LIMIT 0, 1");
        if ($prev) $prev['url'] = $this->getPathToPublication($prev['id']);

        // формируем ссылку на следующую новость
        $next = sql_getRow("SELECT * FROM " . $params['table'] . " WHERE visible>0 AND (pid='" . $pid['id'] . "' OR FIND_IN_SET('" . $pid['id'] . "', pids)) AND date<'" . $data['date'] . "' ORDER BY date DESC, name ASC LIMIT 0, 1");
        if ($next) $next['url'] = $this->getPathToPublication($next['id']);

        return array('prev' => $prev, 'next' => $next);
    }

    /**
     * Возвращает список публикаций, привязанных к текущему разделу
     *
     * @param $params
     * @return array
     */
    function showPublications(&$params) {
        $params = $this->setParams($params);

        $offset = (int)get('offset', 0, 'g');
        $page = & Registry::get('TPage');
        $limit = (int)$page->tpl->get_config_vars("{$params['table']}_list_limit");
        if (!$limit) $limit = 10;

        $_params = array(
            'limit' => $limit,
            'offset' => $offset,
            'where' => "visible=1 AND (`pid` = '{$page->content['id']}' || FIND_IN_SET('{$page->content['id']}', `pids`))" . ($params['where'] ? " AND " . $params['where'] : "")
        );
        if (isset($params['model'])) $_params['model'] = $params['model'];
        if (isset($params['orderby'])) $_params['orderby'] = $params['orderby'];
        $res = $this->_publicationsList($_params);
        $res['content_publ_list'] = isset($res['list']) ? $res['list'] : array();
        unset($res['list']);

        if (isset($res['total']) && $res['total'] > $limit) {
            /**
             * @var TContent $content
             */
            $content = & Registry::get('TContent');
            $res['navig'] = $content->getNavigation($res['total'], $limit, $offset, (($page->lang != LANG_DEFAULT) ? $page->lang . "/" . $page->content['href'] : $page->content['href']));
        }

        return $res;
    }

    /**
     * Возвращает массив инфоблоков для заданного положения
     * @param $params
     * @return array
     */
    function showInfoblocks(&$params) {
        $rows = array();

        $params = $this->setParams($params);

        if (isset($params['position'])) {
            $sql = "SELECT * FROM `{$this->_table_blocks}` as b
                    WHERE `visible` = 1 AND `position` = '{$params['position']}' AND root_id=" . ROOT_ID .
                    ((isset($params['random']) && $params['random'])?" ORDER BY RAND()":" ORDER BY `priority`").
                    ((isset($params['limit']) && $params['limit'])?" LIMIT ".(int)$params['limit']:"");
            $rows = sql_getRows($sql);

            if ($rows) {
                foreach ($rows as $num => $data) {
                    if (!$this->checkInfoblockRules($data['id'])) {
                        unset($rows[$num]);
                        continue;
                    }
                    if ($data['publ_announce']) {
                        $rows[$num]['publications'] = $this->_publicationsListForInfoblock(array(
                            'pids' => $data['publ_pids'],
                            'limit' => $data['publ_count'],
                            'date_from' => $data['publ_date_from'],
                            'date_to' => $data['publ_date_to'],
                            'publ_orderby' => isset($params['publ_orderby']) ? $params['publ_orderby'] : 'date DESC',
                            'model' => isset($params['model']) ? $params['model'] : $this->_model
                        ));
                    }
                }
            }
        }
        return array(
            'list' => $rows,
            'count' => count($rows),
        );
    }

    /**
     * Показывать ли инфоблок на данной странице?
     * @param $id
     * @return bool
     */
    function checkInfoblockRules($id) {
        $rules = sql_getRows("SELECT * FROM infoblocks_rules WHERE pid=" . $id);
        if (!$rules) return true;

        $url = $_SERVER['REQUEST_URI'];
        $url = $this->cleanURL($url);
        $url_without_lang = $this->cleanLang($url);
        $unset = false;
        // выключение блока по правилам
        foreach ($rules as $rule) {
            $rule['url'] = $this->cleanURL($rule['url']);
            $rule['url_without_lang'] = $this->cleanLang($rule['url']);
            if ($rule['url'] != $rule['url_without_lang']) {
                // в правиле показа в урле указан язык
                $url_rule = $rule['url'];
                $url_current = $url;
            } else {
                // урл без языка, значит применяем ко всем языковым версиям
                $url_rule = $rule['url_without_lang'];
                $url_current = $url_without_lang;
            }
            if (substr($url_rule, -1) == '*') { // в конце урла указана звездочка
                $url_part = substr($url_rule, 0, -1);
                // проверка что блок включен для других url
                if (substr($url_current, 0, strlen($url_part)) != $url_part && $rule['active'] == 1) {
                    $unset = true;
                }
                // проверка что блок не выключен для данного url
                if (substr($url_current, 0, strlen($url_part)) == $url_part && $rule['active'] == 0) {
                    $unset = true;
                }
            } else {
                // проверка что блок включен для других url
                if ($url_current != $url_rule && $rule['active'] == 1) {
                    $unset = true;
                }
                // проверка что блок не выключен для данного url
                if ($url_current == $url_rule&& $rule['active'] == 0) {
                    $unset = true;
                }
            }
        }
        // включение блока по правилам, что бы перекрыть выключение
        foreach ($rules as $rule) {
            $rule['url'] = $this->cleanURL($rule['url']);
            $rule['url_without_lang'] = $this->cleanLang($rule['url']);
            if ($rule['url'] != $rule['url_without_lang']) {
                // в правиле показа в урле указан язык
                $url_rule = $rule['url'];
                $url_current = $url;
            } else {
                // урл без языка, значит применяем ко всем языковым версиям
                $url_rule = $rule['url_without_lang'];
                $url_current = $url_without_lang;
            }
            // проверка что блок включен для данного url
            if ($url_current == $url_rule && $rule['active'] == 1) {
                $unset = false;
            }
        }
        return !$unset;
    }

    /**
     * Возвращает урл в виде aa/bb/cc (без первого и последнего слеша, без двойных слешей)
     * @param $url
     * @return string
     */
    function cleanURL($url) {
        if (strpos($url, '?') !== false) $url = substr($url, 0, strpos($url, '?'));
        if ($url == '/') return $url;

        $url = explode('/', $url);
        foreach ($url as $k => $v) {
            if (empty($v)) unset($url[$k]);
        }
        return implode('/', $url);
    }

    /**
     * Возвращает урл без языка
     * @param $url
     * @return string
     */
    function cleanLang($url) {
        $url = $this->cleanURL($url);

        global $langs;

        $url = explode('/', $url);
        if (in_array($url[0], $langs)) unset($url[0]);
        $ret = implode('/', $url);
        if (empty($ret)) $ret = '/';
        return $ret;
    }

    /**
     * Список публикаций
     * @param array $params параметры списка
     * @return array
     */
    function _publicationsListForInfoblock($params = array()) {
        $params = $this->setParams($params);
        $limit = (int)($params['limit']) ? (int)$params['limit'] : 10;
        if (!$limit) return array();
        $where = "1";
        if ($params['pids']) {
            $whereArr = array();
            $arr = explode(",", $params['pids']);
            $arr = array_diff($arr, array(''));
            if (!is_array($arr)) {
                $arr = array($arr);
            }
            if (is_array($arr)) {
                foreach ($arr as $tree_id)
                {
                    $whereArr[] = "(`pid` = '{$tree_id}' || FIND_IN_SET('{$tree_id}', `pids`))";
                }
            }
            if ($whereArr) {
                $where = "(".implode(" || ", $whereArr).")";
            }
        }
        else {
            $where .= " AND pid IS NOT NULL";
            $where .= " AND (SELECT root_id FROM tree WHERE id=`{$params['table']}`.pid)=" . ROOT_ID;
        }
        if (isset($params['date_to']) && $params['date_to'] != '0000-00-00 00:00:00') {
            $where .= " AND `date`<='{$params['date_to']}'";
        }

        if (isset($params['date_from']) && $params['date_from'] != '0000-00-00 00:00:00') {
            $where .= " AND `date`>='{$params['date_from']}'";
        }

        $offset = (int)($params['offset']) ? (int)$params['offset'] : 0;
        $sql = "SELECT * FROM `{$params['table']}`
        WHERE `visible` = 1 AND {$where}
        ORDER BY {$params['publ_orderby']} LIMIT {$offset}, {$limit}";

        $rows = sql_getRows($sql);
        $list = array();
        if ($rows) {
            foreach ($rows as $num => $data)
            {
                $obj = new $params['model']($data['id']);
                $pids = $data['pids'] ? explode(",", $data['pids']) : array();
                $pids[] = $data['pid'];
                $pids = array_unique($pids);

                $par_pids = explode(",", $params['pids']);
                $intersect = array_intersect ($pids, $par_pids);
                $obj->pageid = current($intersect);
                if (!$obj->getData()) continue;

                $list[] = $obj;
            }
        }
        return $list;
    }

    /**
     * Возвращает список публикаций, настраивается через параметры sql-запроса
     *
     * @param array $params |   параметры списка
     * @return array        |   список
     */
    function _publicationsList($params = array()) {

        $params = $this->setParams($params);

        // Дополнительные параметры выборки
        $where = '1';
        if (isset($params['where']) && !empty($params['where'])) {
            $where .= ' AND ' . $params['where'];
        }
        if (!isset($params['join'])) $params['join'] = "";

        // Составление запроса
        $sql = "SELECT * FROM `{$params['table']}` {$params['join']}
        WHERE {$where}
        ORDER BY {$params['orderby']}";

        if (isset($params['limit']) && $params['limit'] != -1) {
            $sql .= " LIMIT {$params['offset']}, {$params['limit']}";
        }

        $rows = sql_getRows($sql);
        if (!$rows) return array();

        $total = sql_getValue("SELECT COUNT(1) FROM `{$params['table']}` {$params['join']}
        WHERE {$where}");

        $list = array();
        foreach ($rows as $num => $data)
        {
            $item = new $params['model']($data['id']);
            if (!$item->getData()) continue;

            $list[] = $item;
        }
        return array('list' => $list, 'total' => $total);
    }

    /**
     * Возвращает путь до статьи
     * @param $id
     * @return string
     */
    function getPathToPublication($id) {
        $model = new $this->_model($id);
        return $model->getPath();
    }

    /**
     * Возвращает главный путь до статьи
     * @param $id
     * @return string
     */
    function getMainPathToPublication($id) {
        $model = new $this->_model($id);
        return $model->getMainPath();
    }

    /**
     * Возвращает идентификатор публикации
     * @return int
     */
    function _getItemId() {
        $currentDir = $_SERVER['REQUEST_URI'];
        $currentArr = explode("/", $currentDir);

        $item = $currentArr[count($currentArr) - 1];
        if (!$item) {
            $item = $currentArr[count($currentArr) - 2];
        }
        return (int)$item;
    }

    /**
     * Возвращает путь до  раздела, в котором в данный момент просматривают публикацию
     * @return array
     */
    function _getPidURL($url = '') {
        $currentDir = $_SERVER['REQUEST_URI'];
        $currentArr = explode("/", $currentDir);
        if (!$url) $url = $this->_url;

        $ret = array();
        foreach ($currentArr as $p) {
            if ($p == $url) break;
            if ($p == lang()) continue;
            if (!empty($p)) $ret[] = $p;
        }
        return '/' . implode('/', $ret);
    }
}