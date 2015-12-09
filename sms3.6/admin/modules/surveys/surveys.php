<?php

require_once PATH_COMMON . '/models/SurveyResultsModel.php';

/**
 * ������ "������"
 *
 * @package    admin/modules
 */
class TSurveys extends TTable {

    var $name = 'surveys';
    var $table = 'surveys';
    var $elements = array('elem_questions');
    var $allowed_results_view = array('percnt', 'cnt');
    var $exported_fields = array('number', 'date', 'ip', 'city', 'region', 'district', 'country');

    var $geo_dbname = 'stat';
    var $geo_tablename = 'stat_ipgeobase';

    ########################

    function TSurveys() {
        global $actions, $str;

        TTable::TTable();

        $actions[$this->name] = array(
            'create' => &$actions['table']['create'],
            'edit' => &$actions['table']['edit'],
            'delete' => &$actions['table']['delete'],
            'copy' => array (
                0 => '����������',
                1 => 'Copy',
                'link' => 'copyItem',
                'img' => 'icon.copy.gif',
                'display' => 'block',
                'multiaction' => 'false',
            ),
        );

        $actions[$this->name . '.editform'] = array(
            'apply' => array(
                'title' => array(
                    'ru' => '���������',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].elements[\'do\'].value=\'apply\'; document.forms[\'editform\'].submit(); return false;',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'save_close' => array(
                'title' => array(
                    'ru' => '��������� � �������',
                    'en' => 'Save',
                ),
                'onclick' => 'document.forms[\'editform\'].elements[\'do\'].value=\'save\'; document.forms[\'editform\'].submit(); return false;',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'cancel' => array(
                'title' => array(
                    'ru' => '������',
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
            $temp = "����� �����";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('������', 'Surveys',),
            'title_editform' => array("�����: " . $temp, 'Survey: ' . $temp,),

            'name'				=> array('��������',						'Title',),
            'date_from'			=> array('������ ������',					'Date start',),
            'date_till'			=> array('��������� ������',				'Date till',),
            'closed'			=> array('������',							'Closed',),
            'type'				=> array('��� �������',						'Type',),
            'multi'				=> array('� ������������� �������',			'Multi',),
            'single'			=> array('� ������������ �������',			'Single',),
            'author'			=> array('��� �������� �����',				'Author',),
            'show_comments'		=> array('�������� �����������',			'Show comments',),
            'show_popup'		=> array('����������� ���� �� �������',		'Show popup window at home page',),
            'comments'			=> array('�����������',						'�omments',),
            'description'		=> array('��������',						'Description',),
            'questions'			=> array('������ ��������',					'Questions',),
            'quest'				=> array('������',							'Question',),
            'answers'			=> array('������',							'Answers',),
            'add_question'		=> array('�������� ������',					'Add question',),
            'add_answer'		=> array('�������� �����',					'Add answer',),
            'del_question'		=> array('������� ������',					'Delete question',),
            'del_answer'		=> array('������� �����',					'Delete answer',),
            'free_form'			=> array('�&nbsp;���������&nbsp;�����',		'Free&nbsp;form',),
            'results'			=> array('����������',						'Results',),
            'export'			=> array('�������',							'Export',),
            'back'				=> array('�����',							'Back',),
            'result_percnt'		=> array('���������� � %',					'Show in %'),
            'result_cnt'		=> array('���������� ����� ���������������','Show quantity of voted people'),
            'r_on_quest'		=> array('�� ������',						'On question'),
            'r_answered'		=> array('��������',						'answered'),
            'r_from'			=> array('�� ���',							'from them'),
            'dopaste'			=> array('�������� �� ������',				'Paste from buffer'),
            'show_at_sites'		=> array('���������� ����� �� ������',		'Show at sites'),
            'show_results'		=> array('���������� ���������� �� �����',	'Show results'),

            'export_number'	=> array('�����',	'Number'),
            'export_date'	=> array('����',	'Date'),
            'export_ip'		=> array('IP',	'IP'),
            'export_city'	=> array('�����',	'City'),
            'export_region'	=> array('������',	'Region'),
            'export_district'	=> array('�����',	'District'),
            'export_country'	=> array('������',	'Country'),
            'export_cb_no_answer'		=> array('--',	'--'),
            'export_cb_answer'		=> array('++',	'++'),

            'saved' => array(
                '����� ���� ������� ���������', 'Data has been saved successfully',
            ),
        ));

        // ���� �� �������� � �����
        $is_root_id = sql_getValue("SHOW COLUMNS FROM " . $this->table . " LIKE 'root_id'");
        if ($is_root_id == 'root_id') {
            $this->selector = true;
        } else {
            $this->selector = false;
        }

        $cols = sql_getRows("SHOW COLUMNS FROM surveys_quests", true);
        if (!isset($cols['req'])) {
            sql_query("ALTER TABLE surveys_quests ADD req INT( 1 ) UNSIGNED NOT NULL DEFAULT '1'");
        }
        if ($cols['type'] != 'varchar(30)') {
            sql_query("ALTER TABLE surveys_quests CHANGE type type VARCHAR( 30 ) CHARACTER SET cp1251 COLLATE cp1251_general_ci NOT NULL DEFAULT 'multi'");
        }
    }

    ########################

    function Show() {
        if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }

        // ������ �������
        require_once (core('list_table'));
        $data['table'] = list_table(array(
            'columns' => array(
                array(
                    'select' => 's.id',
                    'display' => 'id',
                    'type' => 'checkbox',
                    'width' => '1px',
                ),
                array(
                    'select' => 's.name',
                    'display' => 'name',
                    'flags' => FLAG_SEARCH | FLAG_SORT,
                ),
                array(
                    'select' => 'UNIX_TIMESTAMP(s.date_from)',
                    'as' => 'date_from',
                    'display' => 'date_from',
                    'flags' => FLAG_SORT | FLAG_FILTER,
                    'filter_type' => 'date',
                    'filter_value' => 'date',
                    'type' => 'date',
                ),
                array(
                    'select' => 'UNIX_TIMESTAMP(s.date_till)',
                    'as' => 'date_till',
                    'display' => 'date_till',
                    'flags' => FLAG_SORT | FLAG_FILTER,
                    'filter_type' => 'date',
                    'filter_value' => 'date',
                    'type' => 'date',
                ),
                array(
                    'select' => 'IF(s.closed=1,1,2)',
                    'as' => 'closed',
                    'display' => 'closed',
                    'type' => 'closed',
                    'flags' => FLAG_SORT | FLAG_FILTER,
                    'filter_type' => 'array',
                    'filter_value' => array('') + array('1' => '��', '2' => '���'),
                ),
                array(
                    'select' => 's.id',
                    'display' => 'export',
                    'type' => 'export',
                ),
                array(
                    'select' => 's.id',
                    'display' => 'results',
                    'type' => 'results',
                ),
            ),
            'from' => $this->table . "  AS s",
            'orderby' => 's.id DESC',
            //'groupby' => 's.id',
            'where' => ($this->selector ? 'root_id = ' . domainRootID() : null),
            // ������ ���������� ���
            'params' => array('page' => $this->name, 'do' => 'show'),
            'dblclick' => 'editItem(s.id)',
            'click' => 'ID=cb.value',
            //'_sql' => true,
        ), $this);

        $this->AddStrings($data);
        return $this->Parse($data, $this->name.'.tmpl');
    }

    /**
     * ����� ������ �� �������
     * @param $value
     * @param $column
     * @param $row
     * @return string
     */
    function table_get_export(&$value, &$column, &$row) {
        return "<a href='/admin/?page=" . ($this->name) . "&do=showexport&id=" . ($row['id']) . "'>" . ($this->str('export')) . "</a>";
    }

    /**
     * ����� ������ �� ����������
     * @param $value
     * @param $column
     * @param $row
     * @return string
     */
    function table_get_results(&$value, &$column, &$row) {
        return "<a href='/admin/?page=" . ($this->name) . "&do=showresults&id=" . ($row['id']) . "'>" . ($this->str('results')) . "</a>";
    }

    function table_get_closed(&$value, &$column, &$row) {
        return $value == 1 ? '+' : '-';
    }

    ########################

    /**
     * ����� �����������
     * @return mixed
     */
    function showResults() {
        $id = get('id', 0, 'g');
        if (!$id) return false;

        $resultsModel = new SurveyResultsModel($id);
        $data = $resultsModel->getResults();
        $data['survey'] = sql_getRow('SELECT * FROM surveys WHERE id = ' . $id);
        $data['module_name'] = $this->name;
        $this->AddStrings($data);
        return $this->Parse($data, $this->name.'.results.tmpl');
    }

    /**
     * ����� ����������� �� ������� �������
     * @return mixed
     */
    function showMoreResults() {
        $id = (int)get('id', 0, 'g');
        $qid = (int)get('qid', 0, 'g');
        if (!$id OR !$qid) return false;

        $resultsModel = new SurveyResultsModel($id);
        $data = $resultsModel->getMoreResults($qid);
        $data['survey'] = sql_getRow('SELECT * FROM surveys WHERE id = ' . $id);
        $data['module_name'] = $this->name;
        $this->AddStrings($data);
        return $this->Parse($data, "surveys.moreresults.tmpl");
    }


    function writeArchive($files_contents) {
        $zip = new ZipArchive();

        $fileName = TEMP_FILE_PATH . "/tmp_export_zip_" . date('j_m_Y_h_m_s_u') . ".zip";
        if ($zip->open($fileName, ZIPARCHIVE::CREATE) !== true) {
            echo "Error while creating archive file";
            die();
        }

        foreach($files_contents as $fname=>$fcontent) {
            $zip->addFromString($fname, $fcontent);
        }
        $zip->setArchiveComment('Exported surveys...');
        $zip->close();

        header("Content-Type: application/zip");
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="export.zip"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header("Content-Length: " . filesize($fileName));
        readfile($fileName);
        unlink($fileName);
        exit();
    }

    function __csv_chars($str) {
        //$str = strip_tags($str);
        $str = str_replace(array(PHP_EOL, chr(10), chr(13), '\r' , '\n', '\t', '\x0B', '\0'), '', $str);
        return str_replace(array(';'), array('&#59;'), $str);
    }

    /**
     * ������ ��������� ��� ���� catalog,
     *
     * @param      $dict
     * @param null $visible
     *
     * @return bool
     */
    function getDictonaryValues($dict, $visible=null) {
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
     * ������� �����������
     */
    function showExport() {
        global $surveys_dictonaries; // ������ ������������ �� ��������
    
        include_once PATH_COMMON . '/classes/geo.php';
        $geo = new Geo(array('dbname' => $this->geo_dbname, 'tablename' => $this->geo_tablename));
        
        $columns = sql_getRows("SHOW COLUMNS FROM `surveys_users`", true);
        if (!isset($columns['region']))
            sql_query("ALTER TABLE `surveys_users` ADD region VARCHAR( 255 ) NOT NULL;");
        if (!isset($columns['city']))
            sql_query("ALTER TABLE `surveys_users` ADD city VARCHAR( 255 ) NOT NULL;");
        if (!isset($columns['district']))
            sql_query("ALTER TABLE `surveys_users` ADD district VARCHAR( 255 ) NOT NULL;");
        if (!isset($columns['country']))
            sql_query("ALTER TABLE `surveys_users` ADD country VARCHAR( 255 ) NOT NULL;");
    
        $id = (int)get('id', 0, 'g');
        $export = array();
        $export2 = array();
        $quests = sql_getRows('SELECT id, text, type FROM surveys_quests WHERE id_survey=' . $id);

        // C������ ������� ������ - ���������
        $row = array();
        foreach($this->exported_fields as $fld) {
            $row[] = $this->str('export_' . $fld);
        }
        foreach ($quests as $k => $quest) {
            $row[] = $this->__csv_chars($quest['text']);
            $variants = sql_getRows("SELECT id, text FROM surveys_quest_variants
                WHERE id_quest={$quest['id']} ORDER BY priority");
            foreach ($variants as $v) {
                $v['text'] = $this->__csv_chars($v['text']);
                if ($quest['type'] == 'single' || $quest['type'] == 'multi') {
                    $row[] = $v['text'];
                } elseif ($quest['type'] == 'catalog') {
                    $catalog = $this->getDictonaryValues($v['text']);
                    foreach ($catalog as $item) {
                        $row[] = $item['name'];
                }
            }
            }
            if (in_array($quest['type'], array('single', 'multi', 'catalog'))) $row[] = '';
            $quests[$k]['variants'] = $variants;
        }
        $export[] = implode(';', $row);
        $export2[] = implode(';', $row);

        $users = sql_getRows("SELECT * FROM surveys_users WHERE id_survey = '$id' ORDER BY date", false);

        // C������ ����������
        foreach ($users as $k => $u) {

            $u['number'] = $k + 1;
        
            if (!$u['country'] && !$u['district'] && !$u['region'] && !$u['city'] && $geo->is_valid_ip($u['ip'])) {
                $geo->ip = $u['ip'];
                $ip_data = $geo->get_value();
                $u['city'] = mysql_real_escape_string($ip_data['city']);
                $u['region'] = mysql_real_escape_string($ip_data['region']);
                $u['district'] = mysql_real_escape_string($ip_data['district']);
                $u['country'] = mysql_real_escape_string($ip_data['country']);
                
                sql_query('UPDATE surveys_users SET 
                    city="'.$u['city'].'",
                    region="'.$u['region'].'",
                    district="'.$u['district'].'",
                    country="'.$u['country'].'"
                WHERE id="'.$u['id'].'"');
            }
        
            $sql = "SELECT af.text as text
                        FROM surveys_log as sl LEFT JOIN surveys_free_answ as af
                        ON (af.id_variant=sl.id_variant && af.id_user=sl.id_user)
                        WHERE sl.id_user='{$u['id']}'";

            $row = array();
            foreach($this->exported_fields as $fld) {
                $row[] = $u[$fld];
            }
            $row2 = $row;

            foreach ($quests as $quest) {

                switch ($quest['type']) {
                    case 'single':
                    case 'multi':
                        $row[] = '';
                        $row2[] = '';
                        foreach ($quest['variants'] as $v) {
                            $data = sql_getRow($sql . ' AND sl.id_variant = ' . $v['id']);
                            if (!empty($data)) {
                                $row[] = $this->str('export_cb_answer');
                                if ($data['text'])
                                    $row2[] = $this->__csv_chars($data['text']);
                                else
                                    $row2[] = $v['text'];
                            } else {
                                $row[] = $this->str('export_cb_no_answer');
                                $row2[] = $this->str('export_cb_no_answer');
                            }
                        }
                        $row[] = '';
                        $row2[] = '';
                        break;

                    case 'catalog':
                        $row[] = '';
                        $row2[] = '';

                        $catalog = $this->getDictonaryValues($quest['variants']['0']['text']);

                        // ������ ��� ���� ������, ����������
                        $data = sql_getRow('SELECT
                                    t.'.$surveys_dictonaries[$quest['variants']['0']['text']]['value'].',
                                    t.'.$surveys_dictonaries[$quest['variants']['0']['text']]['name'].'
                                        as text
                                    FROM surveys_log sl
                                    LEFT JOIN '
                                .$surveys_dictonaries[$quest['variants']['0']['text']]['table'].'
                                    as t ON (t.id=sl.text)
                                    WHERE sl.id_user=' . $u['id'] . '
                                    AND sl.id_quest=' . (int)$quest['id']);

                        // ������ ��������� �������
                        foreach ($catalog as $item) {
                            if (!empty($data) && $data[$surveys_dictonaries[$quest['variants']['0']['text']]['value']] == $item['value']) {
                                $row[] = $this->str('export_cb_answer');
                                $row2[] = $this->__csv_chars($item['name']);
                            } else {
                                $row[] = $this->str('export_cb_no_answer');
                                $row2[] = $this->str('export_cb_no_answer');
                            }
                        }

                        $row[] = '';
                        $row2[] = '';
                        break;
                        break;
                    default:
                        foreach ($quest['variants'] as $v) {
                            $text = $this->__csv_chars(sql_getValue($sql . ' AND sl.id_variant = ' . $v['id']));
                            $row[] = $text;
                            $row2[] = $text;
                        }
                }
            }
            $export[] = implode(';', $row);
            $export2[] = implode(';', $row2);
        }

        $this->writeArchive(array(
            'exported_survey_v_check.csv' => implode("\n", $export),
            'exported_survey_v_text.csv' => implode("\n", $export2),
        ));
    }

    /**
     * ����������� ������
     */
    function editCopy() {
        $id = (int)get('id', 0, 'g');
        if(!$id) return false;

        $survey = sql_getRow("SELECT * FROM `surveys` WHERE `id` = '$id' LIMIT 1");
        $quests = sql_getRows("SELECT * FROM `surveys_quests` WHERE `id_survey` = '$id'", true);
        $variants = sql_getRows("SELECT * FROM `surveys_quest_variants` WHERE `id_survey` = '$id'");
        foreach ($variants as $var) {
            $quests[$var['id_quest']]['variants'][$var['id']] = $var;
        }

        mysql_query("BEGIN");
        try {
            // �������� �����
            unset($survey['id']);
            $survey['name'] .= " (����� " . date("d.m.Y H:i") . ")";
            $insid = sql_insert("surveys", $survey);
            if (!$insid) throw new Exception();

            // �������� �������
            foreach ($quests as $quest) {
                unset($quest['id']);
                $quest['id_survey'] = $insid;
                $variants = $quest['variants'];
                unset($quest['variants']);
                $quest_id = sql_insert("surveys_quests", $quest);
                if (!$quest_id) throw new Exception();

                // �������� �������� �������
                foreach ($variants as $variant) {
                    unset($variant['id']);
                    $variant['id_survey'] = $insid;
                    $variant['id_quest'] = $quest_id;
                    $var_id = sql_insert("surveys_quest_variants", $variant);
                    if (!$var_id) throw new Exception();
                }
            }
            mysql_query("COMMIT");
            HeaderExit('/admin/?page=' . $this->name);
        } catch (Exception $exc) {
            echo mysql_error();
            mysql_query("ROLLBACK");
            return;
        }
    }
}

$GLOBALS['surveys'] = & Registry::get('TSurveys');