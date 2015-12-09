<?php
require_once module(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	######################
	var $elem_name  = "elem_main";  					//�������� elema
	var $elem_table = "solutions_types";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //��������� ���������
                        'name'                   => array('�������� ����',              'Type name',),
                        'description'            => array('��������',                	'Description',),
                        'visible'                => array('�������� ��� ������ � ���������','Visible on website',),
                        'saved'                  => array('�������� ������� ���������',     'Page saved successfully',),
                        'loading'                => array('��������...',                    'Loading...',),
	);
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	  'columns' => array(
		'id' => array(
			'type'       => 'hidden',
		),
		'name'=>array(
			'type'       => 'text',
			'size'       => 30,
			'maxlength'  => 50,
		),
		'description'=>array(
			'type'       => 'textarea',
			'rows'       => 5,
			'cols' 		 => 23,
		),
		/*'visible'=>array(
			'type'  =>'checkbox',
		),*/
	  ),
	  'id_field' => 'id',
	);
	var $elem_where="";
	var $elem_req_fields = array('name');

}
?>