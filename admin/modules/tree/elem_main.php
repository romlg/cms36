<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');
define ('USE_ED_VERSION', '1.0.2');
class TMainElement extends TElems {

	######################
	var $elem_name  = "elem_main";  					//�������� elema
	var $elem_table = "tree";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //��������� ���������
		'name'				=> array('���������',			'Name'),
		'page'				=> array('URL',					'URL'),
		'redirect'			=> array('������',				'Link'),
		'visible'			=> array('����������',			'Visible'),
		'visible_top_search'=> array('�������� ����� ������ ������','Show search form on top page'),
		'right_column'		=> array('���������� ������ �������',	'Show right column'),
		'description'		=> array('�������� ��� ������',	'Description'),
		'image'				=> array('����������� ��� ������',	'Image'),
	);
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	  'columns' => array(
		'pid'=>array(
			'type'  => 'hidden',
		),
		'priority'=>array(
			'type'  => 'hidden',
		),
		'name'=>array(
			'type'  => 'text',
			'size'  => 40,
		),
		'page'=>array(
			'type'  =>'text',
			'size'  => 20,
		),
		'redirect'=>array(
			'type'  =>'input_url',
			'size'  => 20,
		),
		/*'description'=>array(
			'type'  =>'textarea',
			'cols'  => 40,
			'rows'  => 4,
		),
		'image' => array(
			'type'		=> 'input_image',
			'display'	=> array(
				'size'	=> array('150'),
			),
		),*/
		'visible'=>array(
			'type'  =>'checkbox',
		),
		'visible_top_search'=>array(
			'type'  =>'checkbox',
		),
		/*'right_column'=>array(
			'type'  =>'checkbox',
		),*/
	  ),
	  'id_field'	=> 'id',
	  'folder'		=> 'content',
	);
	var $elem_where="";
	var $elem_req_fields = array('name');
	var $script;

	########################
	
}
?>