<?php

require_once (module('tree'));

class TTreeUrl extends TTree
{
    var $value;

    function TTreeUrl() {
        TTree::TTree();
        global $str;
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array(
                'Выберите раздел',
                'Select page',
            ),
            'treeurl' => array(
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
        $ret['treeurl'] = $this->GetAllTree(array('id' => $id, 'treeurl' => true));
        $ret['fld'] = $this->getQuoteJqueryString(get('fieldname'));

        $ret['name'] = $this->name;
        $ret['id'] = $id;

        return Parse($ret, 'tree/treeurl/treeurl.tmpl');
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

$GLOBALS['tree__treeurl'] = & Registry::get('TTreeUrl');