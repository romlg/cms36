<?php

class TMeta
{

    /**
     * ��������� � ��� �������� meta ����
     */
    function show_meta($params) {
        $ret['meta'] = array();
        $page_obj = & Registry::get('TPage');
        $meta = isset($page_obj->content['elem_meta']) ? $page_obj->content['elem_meta'] : array();

        /**
         * @var TRusoft_View $tpl_obj
         */
        $tpl_obj = & Registry::get('TRusoft_View');
        $title = $tpl_obj->get_config_vars('meta_title');

        // ���� ����� title - �� ������ ���
        if (!empty($meta['title'])) {
            $ret['meta']['title'] = $meta['title'];
        } else {
            //��� ��������: �������� �������, �������� ������� ������, ..., meta_title
            $title_array = array();
            foreach ($page_obj->pids as $key => $val) {
                if ($val['page'] == 'home') continue;
                $title_array[] = $val['name'];
            }
            $title_array = array_reverse($title_array);
            $ret['meta']['title'] = $title_array;
            if ($title) $ret['meta']['title'][] = $title;
            $ret['meta']['title'] = implode(', ', $ret['meta']['title']);
        }

        // ���� ������ �������� ��������, ������ ���, ����� ����� �� ��������
        if (!empty($meta['description'])) {
            $ret['meta']['description'] = $meta['description'];
        } else {
            $ret['meta']['description'] = $this->get_page_description($page_obj, $tpl_obj);
        }
        if (!$ret['meta']['description'] || $ret['meta']['description'] == '...') {
            $title_array = array_reverse($title_array);
            $ret['meta']['description'] = $title_array;
            if ($title) $ret['meta']['description'][] = $title;
            $ret['meta']['description'] = implode(", ", $ret['meta']['description']);
        }
        $ret['meta']['description'] = preg_replace('#\[\[FORM\s([^\]]*)\]\]#is', '', $ret['meta']['description']);
        $ret['meta']['description'] = preg_replace('#\[\[FORMPOPUP\s([^\]]*)\]\]#is', '', $ret['meta']['description']);

        // ���� ������ �������� �����, ������ ��, ����� ����� �� ��������
        if (!empty($meta['keywords'])) {
            $ret['meta']['keywords'] = $meta['keywords'];
        } else {
            //��� �������: (�� �������� �������), (�� �������� ������-������) ..., meta_title
            $res = array();
            $pids = $page_obj->pids;
            $pids = array_reverse($pids);
            foreach ($pids as $key => $val) {
                if ($val['page'] == 'home') continue;
                $res[] = $this->do_keywords($val['name'], $tpl_obj);
            }
            $ret['meta']['keywords'] = $res;
            if ($title) $ret['meta']['keywords'][] = $title;
            $ret['meta']['keywords'] = implode(', ', $ret['meta']['keywords']);
        }
        return $ret;
    }

    /**
     * ��������� �������� ���� �� ������
     *
     * @param string $data
     * @return string
     */
    function do_keywords($data, $tpl_obj) {
        setlocale(LC_CTYPE, 'rus_RUS');
        $lenght = 1000;
        $res = array();
        $data = preg_replace('/(&\w+;)|\'/', ' ', strtolower(strip_tags($data)));
        $words = preg_split('/(\s+)|([\.\,\:\(\)\"\'\!\;' . chr(171) . chr(187) . '])/m', $data);

        $stop = $tpl_obj->get_config_vars('auto_keywords_stop');
        $stop = preg_split('/(\s+)|([\.\,\:\(\)\"\'\!\;])/m', $stop . ' ' . $res);
        foreach ($words as $n => $word) {
            if (strlen($word) < 2 ||
                    /*(int)$word ||*/
                    strpos($word, '/') !== false ||
                    strpos($word, '@') !== false ||
                    strpos($word, '_') !== false ||
                    strpos($word, '=') !== false ||
                    in_array(strtolower($word), $stop)
            ) unset($words[$n]);
        }
        $words = array_merge($words, array());

        // �������� ��� ��������� ������������ ���� �� ������� $words
        $res[] = strtolower($data);
        for ($i = count($words) - 1; $i >= 0; $i--) {
            for ($j = 0; $j < count($words); $j++) {
                if ($i == $j) continue;
                if (!in_array($words[$i] . ' ' . $words[$j], $res)) {
                    $res[] = strtolower($words[$i] . ' ' . $words[$j]);
                }
                if (strlen(implode(', ', $res)) > $lenght) {
                    break 2;
                }
            }
        }
        return implode(', ', $res);
    }

    function get_page_description($page, $tpl_obj) {
        $lenght = $tpl_obj->get_config_vars('auto_description_len');
        if (!$lenght) $lenght = 300;
        $description = isset($page->content['elem_text']['text']) ? $page->content['elem_text']['text'] : "";
        if (isset($page->content['elem_module']['module'])) {
            $func = 'get' . $page->content['elem_module']['module'] . 'Meta';
            if (method_exists($this, $func)) $description .= $this->$func($page);
        }

        $description = strip_tags($description);
        // �������� \n \r \t
        $description = str_replace(array(chr(13), chr(10), chr(9)), '', $description);
        // �������� ������ �������
        $description = preg_replace("/([\s\x{0}\x{0B}]+)/i", " ", trim($description));
        $description = substr($description, 0, $lenght) . '...';
        return $description;
    }

}

?>