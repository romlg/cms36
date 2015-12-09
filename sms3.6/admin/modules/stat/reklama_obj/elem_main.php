<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');
define ('USE_ED_VERSION', '1.0.2');

class TMainElement extends TElems
{

    ######################
    var $product_pid;
    var $elem_name = "elem_main";
    var $elem_table = "stat_reklama";
    var $elem_type = "single";
    var $elem_str = array( //��������� ���������
        'name' => array('��������', 'Product name',),
        'budget' => array('������', 'Budget'),
        'identifiers' => array('�������������� (����� �������)', 'Identifiers (through a point)'),
        'start_date' => array('���� ������', 'Start date'),
        'end_date' => array('���� ���������', 'End date'),
        'displays_count' => array('���������� �������', 'Displays count'),
        'click_count' => array('���������� ������', 'Click count'),
        'saved' => array('�������� ������� ���������', 'Page saved successfully',),
        'root_id' => array('����', 'Site',),
    );
    //���� ��� ������� �� ���� �����
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'name' => array(
                'type' => 'text',
                'size' => 30,
            ),
            'budget' => array(
                'type' => 'text',
                'size' => 10,
            ),
            'start_date' => array(
                'type' => 'input_calendar',
                'display' => array(
                    'func' => 'get_Date',
                ),
            ),
            'end_date' => array(
                'type' => 'input_calendar',
                'display' => array(
                    'func' => 'get_Date',
                ),
            ),
            'displays_count' => array(
                'type' => 'text',
                'size' => 10,
            ),
            'click_count' => array(
                'type' => 'text',
                'size' => 10,
            ),
            'identifiers' => array(
                'type' => 'textarea',
                'cols' => 30,
                'rows' => 3,
            ),
            'root_id' => array(
                'type' => 'select',
                'func' => 'getSites',
            ),
        ),
        'id_field' => 'id',
    );
    var $elem_where = "";
    var $elem_req_fields = array('name');
    var $script = "";

    function get_Date($v) {
        if (isset($v['value'])) return $v['value'];
        else return date("Y-m-d H:i:s");
    }

    function getSites() {
        global $site_domains;
        $sites = array('' => '-- ��� --');
        foreach ($site_domains as $key => $val) {
            foreach ($val['langs'] as $l) {
                $sites[$l['root_id']] = $val['name'] . ' (' . $l['descr'] . ')';
            }
        }
        return $sites;
    }

/*    function ElemRedactB($fld){
        $identifiers = explode(',', $fld['identifiers']);
        foreach ($identifiers as $key=>$val)
            $identifiers[$key]= trim($val);
        $fld['identifiers'] = serialize($identifiers);
        return $fld;
    } */
}

?>