<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TImageElement extends TElems {

	######################
	var $elem_name  = "elem_image";  					//�������� elema
	var $elem_table = "rnt_objects";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //��������� ���������
		'image'			=> array('���� (640�480)','Image (640x480)',),
		'image_small'	=> array('���� (250�250)','Image (250x250)',),
		'image100'		=> array('���� (156�156)','Image (156x156)',),
		'plan'			=> array('���������� (640�480)','Plan (640�480)',),
		'plan_small'	=> array('���������� (50�36)','Plan (50x36)',),
	);

	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
		'columns' => array(
			'image' => array(
				'type' => 'input_image',
				'display' => array(
					'friend'=>'image_small',
					'size' => array('640', '480'),
				),
			),
			'image_small' => array(
				'type' => 'input_image',
				'friend'=>'image100',
				'display' => array(
					'size' => array('250', '250'),
				),
			),
			'image100' => array(
				'type' => 'input_image',
				'display' => array(
					'size' => array('156', '156'),
				),
			),
			'plan' => array(
				'type' => 'input_image',
				'display' => array(
					'friend'=>'plan_small',
					'size' => array('640', '480'),
				),
			),
			'plan_small' => array(
				'type' => 'input_image',
				'display' => array(
					'size' => array('50', '36'),
				),
			),
		),
		'id_field' => 'id',
		'folder'	=> 'rnt_objects',
	 );

	var $elem_where="";
	var $elem_req_fields = array();
	var $script="";
	#####################################

}
?>