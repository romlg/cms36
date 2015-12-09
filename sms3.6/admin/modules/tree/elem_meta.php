<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TMetaElement extends TElems {
	######################
	var $elem_name  = "elem_meta";  			 //�������� elema
	var $elem_table = "elem_meta";               //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str   = array(                     //��������� ���������
	  'title' 		=> array('��������� ��������','Page Title',),
	  'description' => array('�������� ��������' ,'Page Description',),
	  'keywords'    => array('�������� �����'    ,'Page Keywords',),
	);
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
        'columns' => array(
            'title' => array(
                'type' => 'textarea',
                'rows' => '6',
                'cols' => '35',
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => '6',
                'cols' => '35',
            ),
            'keywords' => array(
                'type' => 'textarea',
                'rows' => '6',
                'cols' => '35',
            ),
        ),
        'id_field' => 'pid',
	);
	var $elem_where='';
	var $elem_req_fields = array();
	var $script;
}

?>