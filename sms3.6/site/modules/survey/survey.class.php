<?php

require_once PATH_COMMON . 'models/SurveyResultsModel.php';

/**
 * ������ "������"
 */
class TSurvey
{

    public $params;

    protected $page_obj;
    protected $table = 'surveys';
    protected $table_quests = 'surveys_quests';
    protected $table_variants = 'surveys_quest_variants';
    protected $table_log = 'surveys_log';
    protected $table_users = 'surveys_users';
    protected $table_free = 'surveys_free_answ';

    protected $geo_dbname = 'stat';
    protected $geo_tablename = 'stat_ipgeobase';

    /**
     * @var bool ���������, ����� ���� ������ �� ��� �������
     */
    protected $check_all_answers = false;

    public function __construct() {
        $this->page_obj = &Registry::get('TPage');
    }

    /**
     * �������� �������� ������ (�������������)
     * $params => array(
     *      'url' => 'survey',              // url � �������� � �������
     *      'show_list' => false,           // ���������� ������ �� ������ ���������� �������
     *      'show_block' => false,          // ������� ���� � ��������� �������� �������
     *      'show_archive' => false,        // ���������� ������ �� �����
     *      'show_quest_results' => true,   // ���������� ������ �� ���������� ������������ ��������
     * )
     *
     * @param array $params
     * @return mixed
     */
    function survey(&$params) {
        $this->params = $params;
        $this->page_obj = &Registry::get('TPage');

        if (!isset($this->params['url']) OR !$this->params['url']) $this->params['url'] = "survey";
        if (isset($this->params['show_block']) AND $this->params['show_block']) return $this->showBlock();
        switch ($this->page_obj->content['page']) {
            case 'archive':
                $ret = $this->getArchive(); // �������� ������
                break;
            case 'list':
                $ret = $this->getList(); // �������� ������ �������
                break;
            case 'surveydo':
                $ret = $this->surveydo(); // ���-�� �������� ��������
                break;
            case 'thanks':
                $ret = false;
                if ($_GET['from'] == 'survey_popup') {
                    echo "<script>
                        window.opener.location = '/" . ($this->params['url']) . "/thanks?id=" . (int)$_GET['id'] . "';
                        window.close();
	    		    </script>";
                }
                break;
            default:
                $ret = false;
        }

        $id = (int)get('id', '', 'g');
        if ($id AND !$ret) {
            // ������ ������ �������� ����������/���������� � ���������� �����
            $survey = $this->getSurvey($id);
            if (!$survey) return false;

            $ret['survey'] = $survey;
            $ret['survey']['total'] = $this->qrySurveyTotal($id);
            $show = $this->whatShow($survey);
            switch ($show) {
                case 'questions':
                    $ret['survey']['questions'] = $this->getQuests($id);
                    break;
                case 'results':
                    $resultsModel = new SurveyResultsModel($id);
                    $ret['survey']['results'] = $resultsModel->getResults();
                    break;
                case 'results_more':
                    $quest_id = (int)get('qid', 0, 'g');
                    $resultsModel = new SurveyResultsModel($id);
                    $ret['survey']['results_more'] = $resultsModel->getMoreResults($quest_id);
                    break;
                case 'none':
                    break;
            }
        }

        $ret['current_answers'] = isset($_SESSION['survey']['data']) ? $_SESSION['survey']['data'] : array();
        $ret['current_free_answers'] = isset($_SESSION['survey']['free']) ? $_SESSION['survey']['free'] : array();
        $ret['current_catalog_answers'] = isset($_SESSION['survey']['catalog']) ? $_SESSION['survey']['catalog'] : array();
        $_SESSION['survey']['data'] = $_SESSION['survey']['free'] = array();

        if ($ret) {
            $ret['params'] = $this->params;
            return $ret;
        } else redirect('/' . $this->params['url'] . '/list/');
    }

    /**
     * ����������� ����� � ��������� ��� ������������
     *
     * @return mixed
     */
    function showBlock() {
        // ���� ��������� �����������, �� ������� �� ����������
        $survey = $this->getLastSurvey(true);
        if (!$survey) {
            // ���� �� �����, �� ���� ������ ���������
            $survey = $this->getLastSurvey(false);
        }

        if (!$survey) return false;
        $survey = array_shift($survey);

        $show = $this->whatShow($survey);
        if ($show == 'none') return false;

        switch ($show) {
            case 'questions':
                $survey['questions'] = $this->getQuests($survey['id']);
                break;
            case 'results' :
                $survey['results'] = $this->getResult($survey['id']);
                break;
        }
        $current_answers = isset($_SESSION['survey']['data']) ? $_SESSION['survey']['data'] : array();
        $current_free_answers = isset($_SESSION['survey']['free']) ? $_SESSION['survey']['free'] : array();
        $current_catalog_answers = isset($_SESSION['survey']['catalog']) ? $_SESSION['survey']['catalog'] : array();
        $_SESSION['survey']['data'] = $_SESSION['survey']['free'] = array();

        return array("current_survey" => $survey, "params" => $this->params, "current_answers" => $current_answers, "current_free_answers" => $current_free_answers, "current_catalog_answers" => $current_catalog_answers);
    }

    /**
     * ������� ����������, ��� ��� ������� ����������� ����� ���������� - �������, ������, ������
     *
     * @param array $survey
     * @return string
     */
    function whatShow($survey) {
        if ($survey['closed'] == '0' && !$this->isVoted($survey['id'])) {
            return 'questions'; // ������� �������
        }
        if (($survey['show_results'] == 'always') || ($survey['show_results'] == 'after_answer' && $this->isVoted($survey['id'])) || ($survey['show_results'] == 'after_close' && $survey['closed'] == '1')) {
            return (isset($_GET['qid']) AND $_GET['qid']) ? 'results_more' : 'results'; // ������� ����������
        }
        return 'none';
    }

    /**
     * ���������� ������ �������� ��� �����������
     *
     * @param int $id
     * @return array
     */
    function getQuests($id) {
        $survey = $this->getSurvey($id, 'AND closed=0');
        if (!$survey) return false;

        static $_surveys_questions;
        if (!isset($_surveys_questions)) $_surveys_questions = array();

        if (!isset($_surveys_questions[$id])) {
            $questions = sql_getRows("SELECT * FROM " . $this->table_quests . " WHERE id_survey=" . $id . " ORDER BY priority");
            $ids = array();
            foreach ($questions as $quest) $ids[] = $quest['id'];
            $_variants = sql_getRows("SELECT * FROM " . $this->table_variants . " WHERE id_survey=" . $id . " AND id_quest IN (" . implode(",", $ids) . ") ORDER BY id_quest, priority");
            $variants = array();
            foreach ($_variants as $var) {
                $variants[$var['id_quest']][] = $var;
            }
            foreach ($questions as $k => $quest) {
                $vars = isset($variants[$quest['id']]) ? $variants[$quest['id']] : array();
                $i = 0;
                foreach ($vars as $va) {
                    if ($va['free_form']) {
                        $va['free_form'] = array('id_quest' => $quest['id'], 'id' => $va['id']);
                    } else unset($va['free_form']);
                    $questions[$k]['variants'][$i] = $va;
                    $questions[$k]['variants'][$i]['type'] = $quest['type'] == 'multi' ? 'checkbox' : 'radio';
                    if ($quest['type'] == 'text' OR $quest['type'] == 'textarea') {
                        $questions[$k]['variants'][$i]['type'] = $quest['type'];
                    } elseif ($quest['type'] == 'catalog') {
                        $questions[$k]['variants'][$i]['type'] = $quest['type'];
                        $questions[$k]['variants'][$i]['list'] = $this->getDictonaryValues($questions[$k]['variants'][$i]['text']);
                    } elseif ($quest['type'] == 'multi') {
                        $questions[$k]['variants'][$i]['type'] = 'checkbox';
                    } else {
                        $questions[$k]['variants'][$i]['type'] = 'radio';
                    }
                    $i++;
                }
            }
            $_surveys_questions[$id] = $questions;
        }

        return $_surveys_questions[$id];
    }

    /**
     * ������ ��������� ��� ���� catalog,
     *
     * @param      $dict
     * @param null $visible
     *
     * @return bool
     */
    public function getDictonaryValues($dict, $visible=null) {
        global $surveys_dictonaries;
        if(isset($surveys_dictonaries[$dict])) {
            if ($visible !== null) {
                $visible = ' AND visible' .(int)$visible;
            } else {
                $visible = '';
            }
            $res = sql_getRows('SELECT '
                                . $surveys_dictonaries[$dict]['name'] . ' as name, '
                                . $surveys_dictonaries[$dict]['value'] . ' as value
                                FROM ' . $surveys_dictonaries[$dict]['table'] . ' WHERE 1=1 ' . $visible . ' ORDER BY ' . $surveys_dictonaries[$dict]['name']);
        }
        return (!empty($res)) ? $res : false;
    }

    /**
     * ��������� ������
     *
     * @param int $id - ID ������
     * @return mixed
     */
    function getResult($id) {
        $survey = $this->getSurvey($id);
        if (!$survey) return false;

        $total = sql_getRows("SELECT id_quest, COUNT(*) as cnt FROM " . $this->table_log . " WHERE id_survey=" . $id . " GROUP BY id_quest");
        $quests = sql_getRows("SELECT * FROM " . $this->table_quests . " WHERE id_survey=" . $id . " ORDER BY priority");
        foreach ($quests as $k => $quest) {
            $rows = sql_getRows("SELECT v.id, v.text, COUNT(l.id_variant) as cnt, ROUND(100*COUNT(l.id_variant)/" . (!isset($total[$k]['cnt']) ? 1 : $total[$k]['cnt']) . ") as percent
    	        FROM " . $this->table_variants . " AS v
                LEFT JOIN " . $this->table_log . " AS l ON ( l.id_variant = v.id && v.id_quest = l.id_quest )
                WHERE v.id_quest=" . $quest['id'] . " GROUP BY v.id ORDER BY v.priority");
            $quests[$k]['variants'] = $rows;
        }

        return $quests;
    }

    /**
     * ���������� ������ � ������� �� id
     *
     * @param int $id
     * @param string $where
     * @return array
     */
    function getSurvey($id, $where = '') {
        static $_surveys;
        if (!isset($_surveys)) $_surveys = array();
        $index = md5($where);
        if (!isset($_surveys[$id][$index])) {
            $survey = sql_getRow('SELECT * FROM ' . $this->table . ' WHERE id = ' . (int)$id . ($where ? ' ' . $where : null));
            if ($survey['date_from'] == '0000-00-00') $survey['date_from'] = false;
            if ($survey['date_till'] == '0000-00-00') $survey['date_till'] = false;
            $_surveys[$id][$index] = $survey;
        }
        return $_surveys[$id][$index];
    }

    /**
     * ���������� ��������� �����
     *
     * @param bool $isOnlyUnvoted = true - ������ ����� ������������
     * @return array
     */
    function getLastSurvey($isOnlyUnvoted = false) {
        $qry_only = '';
        if ($isOnlyUnvoted) {
            $list = $this->votedList();
            if (empty($list)) $qry_only = ''; else $qry_only = ' AND id NOT IN(' . implode(',', $list) . ')';
        }
        $sql = "SELECT * FROM " . $this->table . "
	    WHERE 
	       closed=0 AND 
	       root_id=" . ROOT_ID . "
	       " . $qry_only . "
	    ORDER BY date_from DESC LIMIT 1";
        return sql_getRows($sql);
    }

    /**
     * ���������� id ������, ��� �������� ������� ����� ������,
     * ������� �� closed � �� �� ������� ������������ ��� �� �������
     * @return mixed
     */
    function getPopupSurvey() {
        $list = $this->votedList();
        if (!$list) $qry_only = ''; else $qry_only = " AND id NOT IN (" . implode(',', $list) . ")";

        return sql_getValue("SELECT id FROM " . $this->table . " WHERE closed=0 AND show_popup=1 AND root_id=" . ROOT_ID . "" . $qry_only . " ORDER BY date_from DESC LIMIT 1");
    }

    /**
     * ������� ����� �������
     * @return array
     */
    function getArchive() {
        $sql = "SELECT * FROM " . $this->table . "
	    WHERE root_id=" . ROOT_ID . "
	    AND closed=1
	    ORDER BY date_from DESC";
        return array('rows' => sql_getRows($sql));
    }

    /**
     * ������� ������ ���������� �������
     * @return array
     */
    function getList() {
        $sql = "SELECT * FROM " . $this->table . "
	    WHERE root_id=" . ROOT_ID . "
	    AND closed=0 
	    ORDER BY date_from DESC";
        return array('rows' => sql_getRows($sql));
    }

    /**
     * ���������� ���������� ���������� ��������������� � ������ ������
     *
     * @param int $id
     * @return int
     */
    function qrySurveyTotal($id) {
        return (int)sql_getValue("SELECT COUNT(DISTINCT(id_user)) FROM " . $this->table_log . " WHERE id_survey=" . $id);
    }

    /**
     * �������� - ���������� �� ������ ����� ��� ���
     *
     * @param int $id
     * @return bool
     */
    function isVoted($id) {
        $c = sql_getValue("SELECT COUNT(*) FROM " . $this->table . " WHERE id=" . (int)$id . " AND closed = 1");
        return in_array($id, $this->votedList()) || $c;
    }

    /**
     * ���������� ������ id �������, �� ������� ��� ����������
     *
     * @return array
     */
    function votedList() {
        $site_survey = get('site_survey', array(), 'c');
        if (!$site_survey) return array();

        $value = unserialize($site_survey);
        return array_keys($value);
    }

    /**
     * ��������� ������� ������ "��������"
     * @return array
     */
    function surveydo() {
        $id = (int)get('id', 0, 'p');
        if (!$id) return false;

        $item = get('item', array(), 'p');
        $from = get('from', '', 'p');
        $free = get('free', array(), 'p');
        $catalog = get('catalog', array(), 'p');

        // �������� ������ � ������, ����� �������� �� � ������, ���� �� ��� ������ ���������
        $_SESSION['survey']['data'] = $item;
        $_SESSION['survey']['free'] = $free;
        $_SESSION['survey']['catalog'] = $catalog;

        // ��������, �� ��������� �� ��� ���� ������������ � ���� ������
        $value = array();
        if ($_COOKIE['site_survey']) {
            $value = unserialize($_COOKIE['site_survey']);
            if ($value[$id]) {
                // ������� ��� ������������
                return array('error' => $this->page_obj->tpl->get_config_vars('msg_alredy_answered'));
            }
        }

        // ��������, ����� ���� ������ �� �������
        $cols = sql_getRows("SHOW COLUMNS FROM " . $this->table_quests, true);
        if (!isset($cols['req'])) {
            // ��� ������� req, ������ ���������, ����� ���� ������ �� ��� �������
            $this->check_all_answers = true;
        }
        $rows = sql_getRows("SELECT * FROM " . $this->table_quests . " WHERE id_survey=" . $id);
        foreach ($rows as $v) {
            if ($v['req'] || $this->check_all_answers) {
                $empty = false;
                if (in_array($v['type'], array('text', 'textarea'))) {
                    if (empty($free[$item[$v['id']][0]])) $empty = true;
                } elseif ($v['type']=='catalog') {
                   if (empty($catalog[$v['id']])) $empty = true;
                } else {
                    if (!in_array($v['id'], array_keys($item))) $empty = true;
                }
                if ($empty) {
                    $msg = $this->check_all_answers ? 'survey_msg_not_all_answers' : 'survey_msg_no_req_answer';
                    if (!$this->check_all_answers) {
                        $message = sql_getValue("SELECT value FROM strings WHERE name='msg_no_req_answer' AND module='survey' AND root_id=" . getMainRootID());
                        if (!$message) {
                            sql_query("INSERT INTO strings (`name`, `lang`, `value`, `def`, `module`, `root_id`) VALUES ('msg_no_req_answer', 'ru', '�������� �� ������������ �������, ���������� ����������.', '�������� �� ������������ �������, ���������� ����������.', 'survey', '" . getMainRootID() . "')");
                        }
                    }
                    return redirect($from . "?id=" . $id . "&message=" . $msg);
                }
            }
        }

        if ($this->saveVote($id, $item, $free) === false) {
            return redirect($from . "?id=" . $id . "&message=msg_fail");
        }

        // ������ �������� �� ���
        // TODO: ��������� �� ��� ������ �����
        $value[$id] = true;
        setcookie('site_survey', serialize($value), time() + 3600 * 24 * 365, '/');
        setcookie('site_survey_js', implode(',', array_keys($value)), time() + 3600 * 24 * 365, '/');

        redirect($from . '?id=' . $id . '&message=survey_accepted');
    }

    /**
     * ���������� ������ � ��
     * @param $id
     * @param       $item
     * @param array $free
     *
     * @return int|bool
     */
    function saveVote($id, $item, $free) {

        $catalog = get('catalog', array(), 'p');

        sql_query('BEGIN');
        
        include_once PATH_COMMON . '/classes/geo.php';
        
        $geo = new Geo(array('dbname' => $this->geo_dbname, 'tablename' => $this->geo_tablename));
        $real_ip = $geo->get_ip();
        $ip_data = $geo->get_value();

        $columns = sql_getRows("SHOW COLUMNS FROM `".$this->table_users."`", true);
        if (!isset($columns['region']))
            sql_query("ALTER TABLE `".$this->table_users."` ADD region VARCHAR( 255 ) NOT NULL;");
        if (!isset($columns['city']))
            sql_query("ALTER TABLE `".$this->table_users."` ADD city VARCHAR( 255 ) NOT NULL;");
        if (!isset($columns['district']))
            sql_query("ALTER TABLE `".$this->table_users."` ADD district VARCHAR( 255 ) NOT NULL;");
        if (!isset($columns['country']))
            sql_query("ALTER TABLE `".$this->table_users."` ADD country VARCHAR( 255 ) NOT NULL;");
        
        // ������� � ������ ������ ����������������
        $user_id = sql_insert($this->table_users, array(
            'id_survey' => $id,
            'ip' => $real_ip,
            'city' => $ip_data['city'],
            'region' => $ip_data['region'],
            'district' => $ip_data['district'],
            'country' => $ip_data['country'],
        ));
        if (!is_int($user_id)) {
            sql_query('ROLLBACK');
            return false;
        }
        touch_cache($this->table_users);

        // ���� ������ ����� � ��������� �����
        if ($free) {
            foreach ($item AS $val) {
                foreach ($val AS $k => $id_var) {
                    if (array_key_exists($id_var, $free)) {
                        $_id = sql_insert($this->table_free, array(
                            'id_variant' => (int)$id_var,
                            'id_user' => $user_id,
                            'text' => $free[$id_var]
                        ));
                        if (!is_int($_id)) {
                            sql_query('ROLLBACK');
                            return false;
                        }
                        touch_cache($this->table_free);
                    }
                }
            }
        }

        // ������ ��������
        $rows = sql_getRows("SELECT id FROM " . $this->table_quests . " WHERE id_survey=" . $id);

        // ������� ��������� � ���
        foreach ($rows as $k => $v) {
            foreach ($item[$v] as $variant) {
                $_id = sql_insert($this->table_log, array(
                    'id_survey' => $id,
                    'id_quest' => $v,
                    'id_variant' => $variant,
                    'id_user' => $user_id,
                    'text' => isset($free[$variant]) ? $free[$variant] : '',
                ));
                if (!is_int($_id)) {
                    sql_query('ROLLBACK');
                    return false;
                }
                touch_cache($this->table_log);
            }

            foreach ($catalog[$v] as $k=>$variant) {
                $_id = sql_insert(
                    $this->table_log,
                    array(
                         'id_survey'  => $id,
                         'id_quest'   => $v,
                         'id_variant' => $k,
                         'id_user'    => $user_id,
                         'text'       => $variant,
                    )
                );
                if (!is_int($_id)) {
                    sql_query('ROLLBACK');
                    return false;
                }
                touch_cache($this->table_log);
            }
        }

        $query = 'UPDATE ' . $this->table . ' SET answ_cnt=answ_cnt+1 WHERE id =' . $id;
        sql_query($query);
        touch_cache($this->table);

        sql_query('COMMIT');
        return $user_id;
    }

}

?>