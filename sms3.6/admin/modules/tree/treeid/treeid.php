<?php

require_once (module('tree'));

class TTreeId extends TTree
{
    var $value;

    function TTreeId() {
        TTree::TTree();
        global $str;
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array(
                'Выберите раздел',
                'Select page',
            ),
            'treeid' => array(
                'Для указания ссылки необходимо выбрать элемент',
                'For creating a link an item must be selected',
            ),
        ));

        $this->hideInvisible = false;
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

        return Parse($ret, 'tree/treeid/treeid.tmpl');
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

$GLOBALS['tree__treeid'] = & Registry::get('TTreeId');