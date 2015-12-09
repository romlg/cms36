<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TFileElement extends TElems{

	######################
	var $elem_name  = "elem_file";  					//�������� elema
	var $elem_table = "elem_pfile";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //��������� ���������
			'add'	 => array('OK','add',),
			'caption'=> array('�����','Files',),
			'h_add'	 => array('����� ����','New File',),
			'h_edit' => array('����','File',),
			'name'	 => array('���������','Title',),
			'fname'	=> array('��� �����','Filename',),
		);
	var $order = " ORDER BY priority ";
	var $window_size="Width=500, Height=180";
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	  'columns' =>  array(
		'name'=>array(
			'type'  =>'text',
		),
		'fname'=>array(
			'type'  =>'input_file',
		),
		'visible'=>array(
			'type'  =>'checkbox',
			'value' =>'1',
		),
		'priority'=>array(
			'type'  =>'hidden',
		),
	  ),
	  'id_field' => 'pid',
	  'type' => 'multi',
	);
	var $elem_where="";
	var $elem_req_fields = array();
	var $script;
	var $columns;
	########################
	function ElemInit(){
	 $this->columns = array(
		array(
			'select'  => 'id',
			'display' => 'ids',
			'type'    => 'checkbox',
			'width'	  => '1px',
		),
		array(
			'select'  => 'name',
			'display' => 'name',
			'flags'   => FLAG_SEARCH,
		),
		array(
			'select'  => 'fname',
			'display' => 'fname',
		),
	);
	 TElems::ElemInit();
	}
	########################
	//���������� ����� ����������� � ����
	function ElemRedactB($fld){
		if (empty($_POST['ctime'])) $_POST['ctime'] = date('YmdHis'); # timestamp (14)
		foreach ($fld as $k=>$v){
			$fld[$k] = e($v);
		}

		
		 return $fld;
	}
	######################
}
?>