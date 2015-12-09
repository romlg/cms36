<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');
class TNewsElement extends TElems {
	######################
	var $elem_name  = "elem_news";  					//�������� elema
	var $elem_table = "elem_news";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //��������� ���������
		'add'	 	=> array('��������','Add',),
		'title'	    => array('�������','News',),
		'name'	    => array('��������','name',),
		'caption'	=> array('�������','News',),
		'h_add'	    => array('����� ��������','New Document',),
		'h_edit'	=> array('�������','News',),
		'name'	    => array('���������','Title',),
		'image'	    => array('�����������','Image',),
		'date'	    => array('����','Date',),
		'description'=> array('��������','Description',),
		'text'		 => array('�����',	'Text',),
		'visible'	 => array('����������','Visible',),
	);
	
	var $order = " ORDER BY priority ";
	var $window_size = "dialogwidth=550px; dialogheight:600px;";
	//���� ��� ������� �� ���� �����
var $elem_fields = array(
'columns' =>  array(
	'visible'=> array(
		'type'  =>'checkbox',
	),
	'date' => array(
		'type' => 'input_calendar',
		'display' => array(
			'func'=>'get_Date',
			),
	),
	'name'=>array(
	  'type'    => 'text',
	  'size'    => '57',
	 ),
	 'description'=>array(
	   'type'    =>'textarea',
	   'rows'    => '3',
	   'cols'    => '54',
	   'display' => array(
			'colspan' => true,
			)
	   ),
	 'text'=>array(
	   'type'    =>'fck',
	   'toolbar' => 'Common',
	   'size'    => array('100%','270'),
	   'display' => array(
			'colspan' => true,
			)
	   ),
	 'priority'=>array(
	   'type'  =>'hidden',
	  ),

   ),
   'title'	=> '�������',
   'id_field' => 'pid',
   'type' => 'multi',
);
	var $elem_where="";
	var $elem_req_fields = array('name');
	var $script;
	var $columns;

	########################
	
	function ElemInit(){
	 $this->columns = array(
		array(
			'select'	=> 'id',
			'display'	=> 'ids',
			'type'		=> 'checkbox',
			'width'		=> '1px',
		),
		array(
			'select'	=> 'name',
			'display'	=> 'name',
			'flags'		=> FLAG_SEARCH | FLAG_SORT,
		),
		array(
			'select'	=> 'date',
			'as'		=> 'date',
			'display'	=> 'date',
			'width'		=> '1%',
			'type'		=> 'datetime',
			'flags'		=> FLAG_SORT,
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

	function get_Date($v) {
		if (isset($v['value'])) return $v['value'];
		else return date("Y-m-d H:i");
	}
	
}
?>