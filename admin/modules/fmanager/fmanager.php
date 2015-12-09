<?
class TFmanager extends TTable {

    var $name = 'fmanager';

    function TFmanager() {
        global $str, $actions;
        TTable::TTable();

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Файлы и изображения', 'Files and images',),
        ));

        $actions[$this->name] = array();
    }

    function Show() {
        $ret['url'] = "./third/kcfinder/browse.php?CKEditorFuncNum=2&langCode=ru";
        return $this->Parse($ret, $this->name.'.tmpl');
    }
}

$GLOBALS['fmanager'] = & Registry::get('TFmanager');
?>