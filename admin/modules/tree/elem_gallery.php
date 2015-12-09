<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TGalleryElement extends TElems
{

    var $elem_name = "elem_gallery";
    var $elem_table = "elem_gallery";
    var $elem_type = "multi";
    var $elem_class = "gallery";
    var $elem_str = array(
        'image_large' => array('Большая картинка', 'Large image',),
        'image_small' => array('Маленькая картинка', 'Small image',),
        'name' => array('Название', 'Title',),
        'visible' => array('Показывать', 'Visible',),
    );
    var $order = " ORDER BY priority ";

    var $elem_fields = array(
        'id_field' => 'pid',
        'type' => 'multi',
        'folder' => 'content'
    );

    var $elem_where = "";
    var $elem_req_fields = array();
    var $script = "";
}