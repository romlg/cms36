<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TRsspagesElement extends TElems{

	######################
	var $elem_name  = "elem_rsspages";  					//�������� elema
	var $elem_table = "rss_pages";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //��������� ���������
		'url' 	  => array('�����','url',),
		'module'  => array('������','module',),
	);
	var $order = " ORDER BY priority ";
	var $window_size="Width=500, Height=220";
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	  'columns' =>  array(
		'url'=>array(
			  'type'  =>'input_url',
		 ),
		 'module'=>array(
			  'type'  => 'select',
			  'func'  => 'get_modules',
		 ),
	  ),
   'id_field' => 'pid',
   'type' => 'multi',
	);
	var $elem_where="";
	var $elem_req_fields = array('url','module');
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
			'select'  => 'url',
			'display' => 'url',
		),
		array(
			'select'  => 'module',
			'display' => 'module',
			'type'    => 'module',
		),
	);
	 TElems::ElemInit();
	}
	
	function table_get_module(&$value){
		return $GLOBALS['cfg']['rss_modules'][$value]['name'][langid()];
	}
	
	function get_modules(){
		global $cfg;
		$modules = array();
		foreach ($cfg['rss_modules'] as $k=>$v){
			$modules[$k] = $v['name'][langid()];
		}
		
		return $modules;
	}
	########################
}
?>