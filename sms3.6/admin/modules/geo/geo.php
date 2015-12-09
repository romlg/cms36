<?php

include_once 'sms3.6/admin/modules/custom_tree/custom_tree.php';

class TGeo extends TCustom_tree
{
    var $name = 'geo';
    var $table = 'geo';

    function TGeo() {
        global $str, $actions;

        TCustom_tree::TCustom_tree();

        if ((int)$_GET['id']) {
            $temp = sql_getValue("SELECT name FROM " . $this->table . " WHERE id=" . (int)$_GET['id']);
        } else {
            $temp = "Новый район";
        }

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('География', 'Geo',),
            'title_editform' => array("Район: " . $temp, 'Region: ' . $temp,),
        ));
    }
}


$GLOBALS['geo'] = & Registry::get('TGeo');