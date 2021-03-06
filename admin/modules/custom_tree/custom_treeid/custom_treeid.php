<?php

require_once (module('custom_tree'));

class TCustom_treeId extends TCustom_tree
{
    var $value;

    function TCustom_treeId() {
        TCustom_tree::TCustom_tree();
        global $str;
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array(
                '�������� ������',
                'Select page',
            ),
            'treeid' => array(
                '��� �������� ������ ���������� ������� �������',
                'For creating a link an item must be selected',
            ),
        ));
        if (isset($_GET['table'])) $this->table = get('table', '', 'g');
        if (!$this->table) echo '�� ������� �������� �������.';
    }

    function Show() {
        $id = (int)get('id');

        $ret = array();
        $this->AddStrings($ret);

        $ret['path'] = $this->GetPath($id);
        $ret['treeid'] = $this->GetAllTree(array('id' => $id, 'treeid' => true));
        $ret['fld'] = $this->getQuoteJqueryString(get('fieldname'));
        $ret['returnid'] = $this->getQuoteJqueryString(get('returnid'));
        $ret['name'] = $this->name;
        $ret['id'] = $id;

        return Parse($ret, 'custom_tree/custom_treeid/custom_treeid.tmpl');
    }

    //***********************************
    function getQuoteJqueryString($string) {
        $search = array("!", '"', "#", "$", "%", "&", "'", "(", ")", "*", "+",
            ",", ".", "/", ":", ";", "<", "=", ">", "?", "@", "[", "]", "^",
            "`", "{", "|", "}", "~");

        $replace = array("\\\!", '\\\"', "\\\#", "\\\$", "\\\%", "\\\&", "\\\'", "\\\(", "\\\)", "\\\*",
            "\\\+", "\\\,", "\\\.", "\\\/", "\\\:", "\\\;", "\\\<", "\\\=", "\\\>", "\\\?", "\\\@", "\\\[", "\\\]",
            "\\\^", "\\\`", "\\\{", "\\\|", "\\\}", "\\\~");

        return str_replace($search, $replace, $string);
    }
}

$GLOBALS['custom_tree__custom_treeid'] = & Registry::get('TCustom_treeId');