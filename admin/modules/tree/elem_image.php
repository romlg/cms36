<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TImageElement extends TElems {

	######################
	var $elem_name  = "elem_image";  					//�������� elema
	var $elem_table = "elem_image";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //��������� ���������
			'image_small'	=> array('��������� ��������','Small Image',),
			'image_medium'	=> array('������� ��������','Medium Image',),
			'image_large'	=> array('������� ��������','Large Image',),
		);
	var $elem_where="";
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	  'columns' => array(
		 'image_small'=>array(
			'type'  =>'input_image',
		 ),
		 'image_medium'=>array(
			'type'  =>'input_image',
		 ),
		 'image_large'=>array(
			'type'  =>'input_image',
		 ),
		),
	);
	var $elem_req_fields = array('image_small','image_medium','image_large',);
	var $script;
}
?>