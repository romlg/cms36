<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TDocumentElement extends TElems {
	######################
	var $elem_name  = "elem_document";  					//�������� elema
	var $elem_table = "elem_document";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //��������� ���������
		'add'	 	=> array('��������','Add',),
		'title'	    => array('��������','Document',),
		'name'	    => array('��������','name',),
		'hot'	    => array('�������','hot',),
		'caption'	=> array('���������','Documents',),
		'h_add'	    => array('����� ��������','New Document',),
		'h_edit'	=> array('��������','Document',),
		'name'	    => array('���������','Title',),
		'image'	    => array('�����������','Image',),
		'date'	    => array('����','Date',),
		'description'=> array('������� ��������','Description',),
		'text'		 => array('�����',	'Text',),
		'visible'	 => array('����������','Visible',),
	);
	
	var $order = " ORDER BY priority ";
	var $window_size="Width=500, Height=400";
	//���� ��� ������� �� ���� �����
var $elem_fields = array(
'columns' =>  array(
	'name'=>array(
	  'type'    => 'text',
	//'name'    => 'qwer',
	//'display' => array(
	//    'value' => �������� checkboxa ����� ��  ������-default =1
	//    'value2' => �������� checkboxa ����� �� �� ������-default =0
	//    'colspan'=>true ������ ���� ����� � ��� �������
	//    'name'=> ��� ��� ��������
	//    'ckeacked' =>true, -  ��� �������� �������, ����� �� cheched cheackbox
	//),
	 ),
	 'visible'=>array(
	   'type'  =>'checkbox',
	   ),
	 'description'=>array(
	   'type'    =>'textarea',
	   'rows'    => '4',
	   'cols'    => '54',
	   'display' => array(
			'colspan' => true,
			)
	   ),
	 'text'=>array(
	   'type'    =>'fck',
	   'toolbar' => 'Common',
	   'size'    => array('100%','150'),
	   'display' => array(
			'colspan' => true,
			)
	   ),
	 'priority'=>array(
	   'type'  =>'hidden',
	  ),

   ),
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