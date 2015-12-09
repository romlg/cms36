<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	######################
	var $elem_name  = "elem_main";  					//�������� elema
	var $elem_table = "guestbook";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //��������� ���������
	'name'          => array('��������',                  'Name',),
	'date'          => array('���� ��������',                  'Date',),
	'message'       => array('�����',                      'Message',),
    'visible'       => array('����������',         'Visible',),
    'visiblemain'       => array('���������� �� �������',         'Visible main',),
	);
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	  'columns' => array(
		'id'      => array(
		        'type'    => 'hidden',
		),
        'date' => array(
        	'type' => 'input_calendar',
        	'display' => array(
        		'func'=>'getCurrentDate',
        		),
        ),
		'name'    => array(
		  'type'        => 'text',
		  'size'        => 50,
		  'maxlength'   => 150,
		),

		'message'    => array(
                'type'       => 'fck',
                'toolbar'    => 'Small',
                'size'       => array('100%','100'),
		),

		'visible'=>array(
		  'type'  =>'checkbox',
		),
          'visiblemain'=>array(
              'type'  =>'checkbox',
          ),
	  ),
        'folder' => 'guestbook',
        'id_field' => 'id',
	);
	var $elem_where="";
	var $elem_req_fields = array('name');
	var $script = "";

    function getCurrentDate($v) {
        return isset($v['value']) ? $v['value'] : date('Y-m-d H:i:s');
    }
}
?>