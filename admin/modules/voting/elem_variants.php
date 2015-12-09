<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');
class TVariantsElement extends TElems{

	######################
	var $elem_name  = "elem_variants";  					//�������� elema
	var $elem_table = "voting_answers";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //��������� ���������
			'count' 	     => array('���������� �������','Voice count',),
			'name' 		     => array('�����','Answer',),
		);
	var $order = " ORDER BY priority ";
	var $window_size="";
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	  'columns' =>  array(
		'name'=>array(
			  'type'  =>'text',
		  ),
		'count'=>array(
			  'type'  =>'text',
		 ),
		 'priority'=>array(
			  'type'  =>'hidden',
		 ),
	  ),
   'id_field' => 'pid',
   'type' => 'multi',
   'title' => '������� ������',
	);
	var $elem_where="";
	var $elem_req_fields = array();
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
			'select'	 => 'count',
			'type'	 => 'text',
			'display' => 'count',
		),
	);
	 TElems::ElemInit();
	}
}
?>