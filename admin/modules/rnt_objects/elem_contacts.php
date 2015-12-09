<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TContactsElement extends TElems {

	######################
	var $elem_name  = "rnt_objects";			//�������� elema
	var $elem_table = "rnt_objects";			//�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(						//��������� ���������
		'manager'			=> array('���������� ����',	'Name',),
		'phone_number'		=> array('��������',		'Phone number'),
		'manager_company'	=> array('��������',		'Company'),
		'manager_link'		=> array('������',			'Link'),
		'manager_email'		=> array('E-mail',			'E-mail'),
	);

	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
		'columns' => array(
			'manager'			=>	array('type'	=>	'text',			'size'		=> '50',),
			'manager_email'		=>	array('type'	=>	'text',			'size'		=> '40',),
			'phone_number'		=>	array('type'	=>	'textarea',		'cols'		=> '40',	'rows' => '2'),
			'manager_company'	=>	array('type'	=>	'text',			'size'		=> '50',),
			'manager_link'		=>	array('type'	=>	'text',			'size'		=> '40',),
		 ),
		 'id_field' => 'id',
	 );

	var $elem_where="";
	var $elem_req_fields = array();
	var $script="";
	#####################################

}
?>