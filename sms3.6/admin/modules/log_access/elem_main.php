<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainElement extends TElems
{

    var $elem_name = "elem_main";
    var $elem_table = "elem_news";
    var $elem_type = "single";
    var $elem_str = array(
        'name' => array('���������', 'Title',),
        'image_small' => array('����������� ���������', 'Image small',),
        'alt_small' => array('Alt ��� ����������� ����������', 'Alt for image small'),
        'image' => array('����������� �������', 'Image large',),
        'alt' => array('Alt ��� ����������� ��������', 'Alt for image large'),
        'date' => array('����', 'Date',),
        'description' => array('��������', 'Description',),
        'text' => array('�����', 'Text',),
        'visible' => array('����������', 'Visible',),
        'hot' => array('����������', 'Hot',),
        'send' => array('��������', 'Send',),
        'group_submit' => array('��������', 'Update',),
        'group_str' => array('��������� ����������', 'Groups',),
        'region_id' => array('��� ������', 'Region (tag)'),
    );
    //���� ��� ������� �� ���� �����
    var $elem_fields = array(
        'columns' => array(
            'visible' => array(
                'type' => 'checkbox',
            ),
            'hot' => array(
                'type' => 'checkbox',
            ),
            'date' => array(
                'type' => 'input_calendar',
                'display' => array(
                    'func' => 'get_date',
                ),
            ),
            'name' => array(
                'type' => 'text',
                'size' => '57',
            ),
            'image_small' => array(
                'type' => 'input_image',
                'display' => array(
                    'size' => array('220', '220'),
                ),
            ),
            'alt_small' => array(
                'type' => 'text',
                'size' => '40',
            ),
            'image' => array(
                'type' => 'input_image',
                'display' => array(
                    'friend' => 'image_small',
                    'size' => array('220'),
                ),
            ),
            'alt' => array(
                'type' => 'text',
                'size' => '40',
            ),
            'region_id' => array(
                'type' => 'multi_select',
                'func' => 'get_regions',
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => '3',
                'cols' => '54',
                'display' => array(
                    'colspan' => true,
                )
            ),
            'text' => array(
                'type' => 'fck',
                'toolbar' => 'Common',
                'size' => array('97%', '400'),
                'display' => array(
                    'colspan' => true,
                )
            ),
            'priority' => array(
                'type' => 'hidden',
            ),
            'send' => array(
                'type' => 'hidden',
            ),
        ),
        'id_field' => 'id',
        'folder' => 'news'
    );
    var $elem_where = "";
    var $script = "";
    var $elem_req_fields = array('name',);

    function ElemRedactBefore($row) {
        if (empty($_POST['ctime'])) $_POST['ctime'] = date('Y-m-d H:i:s'); 
        return $row;
    }

    function get_date($v) {
        if (isset($v['value'])) return $v['value'];
        else return date("Y-m-d H:i");
    }

    function get_regions() {
        return array(-1 => '��� ���� ��������') + (array)sql_getRows('SELECT id, tag FROM region_tags WHERE root_id = ' . ROOT_ID . ' ORDER BY IF (priority = 0 OR priority IS NULL, 1, 0), tag', true);
    }
}