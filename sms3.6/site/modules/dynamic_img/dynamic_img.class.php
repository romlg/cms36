<?php

/**
 * Модуль "Смены изображений"
 */
class TDynamic_img {

    var $table = 'dynamic_img';

    /*
     * Слайдер на главной
     */
    function dynamic_img() {
        $images = sql_getRows("SELECT * FROM `" . $this->table . "`
        WHERE
            image != '' AND
            visible = '1' AND
            FIND_IN_SET('" . ROOT_ID . "', root_ids)
        ORDER BY priority, name", true);
        foreach ($images AS $key => $value) {
            if (!is_file(substr($value['image'], 1))) unset($images[$key]);
        }
        return array('slider' => $images);
    }
}

?>