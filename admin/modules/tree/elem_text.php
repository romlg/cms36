<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TTextElement extends TElems {

	######################
	var $elem_name  = "elem_text";  					//�������� elema
	var $elem_table = "elem_text";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(
			'text'		 => array('�����',	'Text',),          //��������� ���������
			);
	var $elem_where="";
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	 'columns' => array(
	  'text'=>array(
		'type'  =>'fck',
			'toolbar'=> 'Common',
			'size'   => array('100%','500'),
			'display'=> array(
				'colspan' => true,
			),
		),
	  ),
	  'id_field' => 'pid',
	);
	var $elem_req_fields = array();
	var $script;
}
?>