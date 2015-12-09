<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TFeaturesElement extends TElems{

	######################
	var $elem_name  = "elem_features";  					//�������� elema
	var $elem_table = "elem_features";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //��������� ���������
			'caption'	=> array('���������','Params',),
			'add'	=> array('OK','add',),
			'h_add'	=> array('����� ��������','New param',),
			'h_edit'	=> array('��������','Param',),
			'name'	=> array('��������', 'Title',),
			'value'	=> array('��������','Value',),
		);
	var $order = " ORDER BY priority ";
	var $window_size="Width=500, Height=180";
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	  'columns' =>  array(
		'name'=>array(
			  'type'  =>'text',
		),
		'value'=>array(
			  'type'  =>'text',
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
	var $elem_req_fields = array('name','value',);
	var $script;
	var $columns;
	########################
	function ElemInit(){
	 $this->columns = array(
		array(
			'select'	 => 'id',
			'display' => 'ids',
			'type'    => 'checkbox',
			'width'	 => '1px',
		),
		array(
			'select'	 => 'name',
			'display' => 'name',
			'flags'   => FLAG_SEARCH,
		),
		array(
			'select'	 => 'value',
			'display' => 'value',
		),
	);
	 TElems::ElemInit();
	}
	########################
	//���������� ����� ����������� � ����
	function ElemRedactB($fld){
		if (empty($_POST['ctime'])) $_POST['ctime'] = date('YmdHis'); # timestamp (14)
		 return $fld;
	}
	######################
}
?>