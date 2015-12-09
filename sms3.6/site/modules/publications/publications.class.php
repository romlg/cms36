<?php

include_once "phpThumb/image.class.php";
include_once PATH_COMMON .  "models/Publication.php";

/**
 *
 * ������ ����������, �������� ���, ��� �������� ����������.
 * ������, ����������� �����.
 *
 */
class TPublications
{

    /**
     * @var string  �������� �������� � url, �������� � ���� ������, ��� � � ����� site.cfg.php
     */
    var $_url = 'item';

    /**
     * @var string  ������� � ���� � ������������
     */
    var $_table = 'publications';

    /**
     * @var string  ������� � �����������
     */
    var $_table_blocks = 'infoblocks';

    /**
     * @var string �� ������ ���� �����������
     */
    var $_orderby = 'date DESC';

    /**
     * @var string �������������� ������� �������
     */
    var $_where = '';

    /**
     * @var string �������� ������ � ������� ����������
     */
    var $_model = 'Publication';

    /**
     * ��������� ���������� ����������
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
     * ���������� ������ �� ����� ����������
     * ������������ �� url : /{��� ������}/{��� ������}/.../item/{publication_id}/
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
            // ���������� ����������� � ����������
            return $this->newComment($publication, $_POST['publication_comment']);
        }

        // ���� ���������� ���������
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

        // ����� ��� ���������� ���������� ������� ������
        $page = & Registry::get('TPage');
        $url = $this->_getPidURL($params['url']);

        //������� ��� �������� print � ���� � ����������, ���� ������ � ���� ��� ������� ���������
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

            // ��������, ��������� �� ���������� � ����� �������, ���� ��� ������� �� ��� ������
            if ($current_page['id'] != $publication->getPid() && !in_array($current_page['id'], explode(",", $publication->get("pids")))) {
                redirect($url);
            }

            if ($current_page['id'] != $publication->getPid()) { // ��������� �� �� ��������� ���� ����������
                $view->assign(array('canonical' => $publication->getMainPath()));
            }
        }
        // ��������� �������� �������� �� �������� ����������
        $page->content['name'] = $publication->getName();
        $view->assign(array('content' => $page->content));

        // ����-����
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
     * ���������� ������ �����������
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
     * ���������� ���������� � �������� ���������� ��� ���������
     * @param array
     */
    function onePublNavig($data, &$params) {
        $tree = & Registry::get('TTreeUtils');
        $tree->setRootId(ROOT_ID);
        $pid = end($tree->getPidsByUrl($this->_getPidURL()));
        // ��������� ������ �� ���������� �������
        $prev = sql_getRow("SELECT * FROM " . $params['table'] . " WHERE visible>0 AND (pid='" . $pid['id'] . "' OR FIND_IN_SET('" . $pid['id'] . "', pids)) AND date>'" . $data['date'] . "' ORDER BY date ASC, name ASC LIMIT 0, 1");
        if ($prev) $prev['url'] = $this->getPathToPublication($prev['id']);

        // ��������� ������ �� ��������� �������
        $next = sql_getRow("SELECT * FROM " . $params['table'] . " WHERE visible>0 AND (pid='" . $pid['id'] . "' OR FIND_IN_SET('" . $pid['id'] . "', pids)) AND date<'" . $data['date'] . "' ORDER BY date DESC, name ASC LIMIT 0, 1");
        if ($next) $next['url'] = $this->getPathToPublication($next['id']);

        return array('prev' => $prev, 'next' => $next);
    }

    /**
     * ���������� ������ ����������, ����������� � �������� �������
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
     * ���������� ������ ���������� ��� ��������� ���������
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
     * ���������� �� �������� �� ������ ��������?
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
        // ���������� ����� �� ��������
        foreach ($rules as $rule) {
            $rule['url'] = $this->cleanURL($rule['url']);
            $rule['url_without_lang'] = $this->cleanLang($rule['url']);
            if ($rule['url'] != $rule['url_without_lang']) {
                // � ������� ������ � ���� ������ ����
                $url_rule = $rule['url'];
                $url_current = $url;
            } else {
                // ��� ��� �����, ������ ��������� �� ���� �������� �������
                $url_rule = $rule['url_without_lang'];
                $url_current = $url_without_lang;
            }
            if (substr($url_rule, -1) == '*') { // � ����� ���� ������� ���������
                $url_part = substr($url_rule, 0, -1);
                // �������� ��� ���� ������� ��� ������ url
                if (substr($url_current, 0, strlen($url_part)) != $url_part && $rule['active'] == 1) {
                    $unset = true;
                }
                // �������� ��� ���� �� �������� ��� ������� url
                if (substr($url_current, 0, strlen($url_part)) == $url_part && $rule['active'] == 0) {
                    $unset = true;
                }
            } else {
                // �������� ��� ���� ������� ��� ������ url
                if ($url_current != $url_rule && $rule['active'] == 1) {
                    $unset = true;
                }
                // �������� ��� ���� �� �������� ��� ������� url
                if ($url_current == $url_rule&& $rule['active'] == 0) {
                    $unset = true;
                }
            }
        }
        // ��������� ����� �� ��������, ��� �� ��������� ����������
        foreach ($rules as $rule) {
            $rule['url'] = $this->cleanURL($rule['url']);
            $rule['url_without_lang'] = $this->cleanLang($rule['url']);
            if ($rule['url'] != $rule['url_without_lang']) {
                // � ������� ������ � ���� ������ ����
                $url_rule = $rule['url'];
                $url_current = $url;
            } else {
                // ��� ��� �����, ������ ��������� �� ���� �������� �������
                $url_rule = $rule['url_without_lang'];
                $url_current = $url_without_lang;
            }
            // �������� ��� ���� ������� ��� ������� url
            if ($url_current == $url_rule && $rule['active'] == 1) {
                $unset = false;
            }
        }
        return !$unset;
    }

    /**
     * ���������� ��� � ���� aa/bb/cc (��� ������� � ���������� �����, ��� ������� ������)
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
     * ���������� ��� ��� �����
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
     * ������ ����������
     * @param array $params ��������� ������
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
     * ���������� ������ ����������, ������������� ����� ��������� sql-�������
     *
     * @param array $params |   ��������� ������
     * @return array        |   ������
     */
    function _publicationsList($params = array()) {

        $params = $this->setParams($params);

        // �������������� ��������� �������
        $where = '1';
        if (isset($params['where']) && !empty($params['where'])) {
            $where .= ' AND ' . $params['where'];
        }
        if (!isset($params['join'])) $params['join'] = "";

        // ����������� �������
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
     * ���������� ���� �� ������
     * @param $id
     * @return string
     */
    function getPathToPublication($id) {
        $model = new $this->_model($id);
        return $model->getPath();
    }

    /**
     * ���������� ������� ���� �� ������
     * @param $id
     * @return string
     */
    function getMainPathToPublication($id) {
        $model = new $this->_model($id);
        return $model->getMainPath();
    }

    /**
     * ���������� ������������� ����������
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
     * ���������� ���� ��  �������, � ������� � ������ ������ ������������� ����������
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