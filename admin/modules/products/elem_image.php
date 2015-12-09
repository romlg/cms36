<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TImageElement extends TElems {

	######################
	var $elem_name  = "elem_image";  					//�������� elema
	var $elem_table = "products";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //��������� ���������
			'image_descr'	=> array('����������� <br>� �������� ��������','Image Description',),
			'image'			=> array('������� �����������','Image',),
			'image_popup'	=> array('����������� <br>� ����������� ����','Popup Image',),
		);
	var $elem_where="";
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	  'columns' => array(
		 'image_descr'=>array(
			'type'  =>'input_image',
                'display' => array(
                    'size'     => array('120', '100'),
                ),
		 ),
		 'image'=>array(
			'type'  =>'input_image',
                 'display' => array(
	                   'friend'   => 'image_descr',
	                   'size'     => array('120', '100'),
                 ),
		 ),
		 'image_popup'=>array(
			'type'  =>'input_image',
                'display' => array(
                     'friend'   => 'image_popup',
                     'size'     => array('200', '200'),
                ),
		 ),
	    ),
          'id_field' => 'id',
          'folder'   => 'products',
	);
	var $elem_req_fields = array();
	var $script;
	
	function ElemRedactB($row){
		foreach ($row as $k=>$v){
			$row[$k] = e($v);
		}
		return $row;
	}
}
?>