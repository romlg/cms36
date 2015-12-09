<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	######################
    var $elem_name  = "elem_main";  					//�������� elema
	var $elem_table = "sites";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //��������� ���������
		'name'		=> array('��������',                'Product name',),
		'descr'		=> array('��������',                'Description'),
		'alias'		=> array('������ (����� �������, ��� ��������!)',  				'Alias'),
		'templates'	=> array('���� � �������� (������������ ����� "templates")',
		'Path to templates (concerning a folder "templates")'),
		'modules'	=> array('���� � ������� (������������ ����� "modules")',
		'Path to modules (concerning a folder "modules")'),
		'saved'		=> array('�������� ������� ���������',     'Page saved successfully',),
	);
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
        'columns' => array(
        	'id'	=> array(
				'type'	=> 'hidden',
          	),
          	'name'	=> array(
          		'type'	=> 'text',
          		'size'	=> 40,
          	),
          	'descr' => array(
          		'type'	=> 'text',
          		'size'	=> 40,
				'lang_select'	=> LANG_SELECT,
          	),
          	'alias' => array(
          		'type'	=> 'textarea',
          		'cols'	=> 30,
          		'rows'	=> 2,
          	),
          	'templates' => array(
          		'type'	=> 'text',
          		'size'	=> 40,
          	),
          	'modules' => array(
          		'type'	=> 'text',
          		'size'	=> 40,
          	),
          	'priority' => array(
          		'type'	=> 'hidden',
          	),
		),
		'id_field' => 'id',
	);
	var $elem_where="";
	var $elem_req_fields = array('name'/*, 'root_id'*/);
	var $script = "";

}
?>