<?php
require_once module(OBJECT_EDITOR_MODULE.'/single_elem');

class TMapElement extends TSingleElem {

	######################
	var $elem_name  = "elem_map";  					//�������� elema
	var $elem_table = "elem_map";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_str = array(                       //��������� ���������
		'map'	=> array('����� ������','',),
	);
	var $elem_where="pid";
	//���� ��� ������� �� ���� �����
	var $elem_fields = array('map'=>array(
		'type'  =>'input_image',
		),
	);
	var $elem_req_fields = array();
	var $script;
}
?>
