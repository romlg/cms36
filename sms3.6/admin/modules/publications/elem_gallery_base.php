<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TGalleryBaseElement extends TElems
{

    var $elem_name = "elem_gallery";
    var $elem_table = "publications_gallery";
    var $elem_type = "multi";
    var $elem_class = "gallery";
    var $elem_str = array(
        'image_large' => array('������� ��������', 'Large image',),
        'image_small' => array('��������� ��������', 'Small image',),
        'name' => array('��������', 'Title',),
        'visible' => array('����������', 'Visible',),
    );
    var $order = " ORDER BY priority ";

    var $elem_fields = array(
        'id_field' => 'pid',
        'type' => 'multi',
        'folder' => 'publications'
    );

    var $elem_where = "";
    var $elem_req_fields = array('name', 'image_small',);
    var $script = "";
}