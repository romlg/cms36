<?php

/**
 * Модель результатов опросов
 */
class SurveyResultsModel
{

    public $survey_id;
    public $result_view;

    protected $allowed_results_view = array('percnt', 'cnt');

    protected $table = 'surveys';
    protected $table_quests = 'surveys_quests';
    protected $table_variants = 'surveys_quest_variants';
    protected $table_log = 'surveys_log';
    protected $table_users = 'surveys_users';
    protected $table_free = 'surveys_free_answ';

    /**
     * Конструктор
     * @param int $survey_id
     * @return bool
     */
    function SurveyResultsModel(int $survey_id) {
        if (!$survey_id) return false;
        $this->survey_id = (int)$survey_id;
        $this->result_view = $this->getResultsView();
        return true;
    }

    /**
     * Вернуть массив с общими результатами опроса
     *
     * @return array
     */
    function getResults() {

        $data = array();
        $data['view'] = $this->result_view;
        $data['total'] = sql_getRows("SELECT id_quest, COUNT(*) as cnt FROM " . $this->table_log . " WHERE id_survey = " . $this->survey_id . " GROUP BY id_quest");

        // кол-во результатов по id вопрос
        foreach ($data['total'] as $k=>$v) {
            $data['total'][$v['id_quest']]=$v;
            unset($data['total'][$k]);
        }

        $data['questions'] = sql_getRows("SELECT * FROM " . $this->table_quests . " WHERE id_survey = " . $this->survey_id . " ORDER BY priority", true);

        global $surveys_dictonaries; // список справочников из настроек

        foreach ($data['questions'] as $k => $quest) {

            if($quest['type']=='catalog') {
                // данные для типа опроса, справочник
                $catVar = sql_getRow('SELECT
                                        sqv.id,
                                        sqv.id_survey,
                                        sqv.text
                                    FROM surveys_quest_variants sqv
                                    WHERE sqv.id_survey=' . (int)$quest['id_survey'] . '
                                    AND sqv.id_quest=' . (int)$quest['id']);

                $data['questions'][$k]['variants'] = sql_getRows(
                    "SELECT
                    v.".$surveys_dictonaries[$catVar['text']]['value'].",
                    v.".$surveys_dictonaries[$catVar['text']]['name']." as text,
                    COUNT(l.text) as cnt,"
                        . (isset($data['total'][$k]['cnt']) ? "ROUND(100 * COUNT(l.text) / "
                        . $data['total'][$k]['cnt'] . ") as percent" : "'0' as percent") . "
                    FROM " . $surveys_dictonaries[$catVar['text']]['table'] . " AS v
                    LEFT JOIN " . $this->table_log . " AS l ON ( l.text = v.id)
                    WHERE l.id_quest=" . $quest['id'] . " GROUP BY v.id");

            } else {
                $vars = sql_getRows($q = "SELECT  v.id, v.text, COUNT(l.id_variant) as cnt,  " . (isset($data['total'][$k]['cnt']) ? "ROUND(100 * COUNT(l.id_variant) / " . $data['total'][$k]['cnt'] . ") as percent" : "'0' as percent") . ", v.free_form as free_form
                    FROM " . $this->table_variants . " AS v
                    LEFT JOIN " . $this->table_log . " AS l ON ( l.id_variant = v.id && v.id_quest = l.id_quest )
                    WHERE v.id_quest = " . $quest['id'] . " GROUP BY v.id");
                $data['questions'][$k]['variants'] = $vars;
                foreach ($vars as $v => $var) {
                    // Вариант ответа в свободной форме
                    if ($var['free_form']) {
                        $free_answ = sql_getRows("SELECT text FROM " . $this->table_free . " WHERE id_variant = " . $var['id']);
                        $data['questions'][$k]['variants'][$v]['free_answ'] = $free_answ;
                    }
                }

            }

        }
        return $data;
    }

    /**
     * Вернуть массив с результатами в срезе по конкретному вопросу
     *
     * @param int $quest_id
     * @return array|bool
     */
    function getMoreResults(int $quest_id) {
        if (!$this->survey_id OR !$quest_id) return false;

        $data = array();
        $data['view'] = $this->result_view;
        $data['question'] = sql_getRow("SELECT * FROM " . $this->table_quests . " WHERE id = " . $quest_id);
        $data['questions'] = sql_getRows("SELECT id, text, type FROM " . $this->table_quests . " WHERE id <> " . $quest_id . " && id_survey = " . $this->survey_id . " ORDER BY priority", true);

        $query = (in_array($data['question']['type'], array('single', 'multi'))) ?
                "SELECT * FROM " . $this->table_variants . " WHERE id_quest = " . $quest_id :
                "SELECT *, text as id FROM " . $this->table_log . " WHERE id_quest = " . $quest_id . " GROUP BY text ORDER BY text";
        $data['rows'] = sql_getRows($query);
        foreach ($data['rows'] as $key => $row) {
            $res = sql_getRows($query = "SELECT IF(l2.id_variant IS NOT NULL, l2.id_variant, l2.text) AS id_variant, l2.id_quest, COUNT(IF(l2.id_variant IS NOT NULL, l2.id_variant, l2.text)) cnt
            FROM " . $this->table_log . " as l1
            LEFT JOIN " . $this->table_log . " as l2 ON(l2.id_user = l1.id_user)
            WHERE IF(l1.id_variant IS NOT NULL, l1.id_variant, l1.text) = '" . $row['id'] . "' AND l2.id_quest<>" . $quest_id . " GROUP BY IF(l2.id_variant IS NOT NULL, l2.id_variant, l2.text)");
            $re = array();
            $total = array();
            foreach ($res as $r) {
                $re[$r['id_variant']] = $r['cnt'];
                $total[$r['id_quest']] += $r['cnt'];
            }
            foreach ($data['questions'] as $qid => $quest) {
                $query = (in_array($quest['type'], array('single', 'multi'))) ?
                        "SELECT * FROM " . $this->table_variants . " WHERE id_quest = " . $quest['id'] :
                        "SELECT *, text AS id FROM " . $this->table_log . " WHERE id_quest = " . $quest['id'] . " GROUP BY text ORDER BY text";
                $data['rows'][$key]['questions'][$qid]['vars'] = sql_getRows($query);
                $data['rows'][$key]['questions'][$qid]['text'] = $quest['text'];

                foreach ($data['rows'][$key]['questions'][$qid]['vars'] as $k => $variant) {
                    $_id = in_array($quest['type'], array('single', 'multi')) ? $variant['id'] : $variant['id_variant'];
                    $data['rows'][$key]['questions'][$qid]['vars'][$k]['cnt'] = $re[$_id];
                    $data['rows'][$key]['questions'][$qid]['vars'][$k]['percent'] = isset($total[$qid]) && isset($re[$_id]) ? round(100 * $re[$_id] / $total[$qid]) : 0;
                }
            }
        }
        $data['qid'] = $quest_id;
        return $data;
    }

    /**
     * Как отображать результаты - в кол-ве проголосовавших или в %
     * @return string
     */
    function getResultsView() {
        return (isset($_GET['view']) AND in_array($_GET['view'], $this->allowed_results_view)) ? mysql_real_escape_string($_GET['view']) : 'percnt';
    }
}