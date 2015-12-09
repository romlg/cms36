<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');
class TSearchTemplateElement extends TElems {
######################
	var $elem_name  = 'elem_searchtemplate';
	var $elem_table = 'elem_searchtemplate';
	var $elem_type  = "multi";
	var $columns;
	var $order = " ORDER BY priority ";
	var $window_size="Width=510, Height=260";
	var $elem_where='';
	var $elem_req_fields = array('name');
	var $columns;
	########################
	var $elem_str   = array(                     //строковые константы
	  'title'      => array('Добавление поискового шаблона'		       ,'Add search templates',),
	  'caption'    => array('Поисковые шаблоны'                            ,'Search templates',),
	  'h_add'      => array('Добавление поискового шаблона'                ,'Add search template',),
	  'h_edit'     => array('Новость'                                      ,'News',),
	  'name'       => array('Заголовок'                                    ,'Title',),
	  'date'       => array('Дата'                                         ,'Date',),
	  'text_ru'    => array('Текст'                                        ,'Text',),
	  'text_en'    => array('Текст'                                        ,'Text',),
	  'visible'    => array('Показывать'                                   ,'Visible',),
	  'saved'      => array('Шаблон успешно сохранен'                      ,'Template saved successfully',),
	  'e_no_title' => array('Вы не заполнили заголовок'                    ,'Title cannot be empty',),
	  'c_del'      => array('Уверены, что хотите удалить выбранный шаблон?','Are you sure to delete this template?',),
	  'st_sdat'    => array('Сдать'                                        ,'To take in',),
	  'st_prodat'  => array('Продать'                                      ,'To sell',),
	  'st_arenda'  => array('Под аренду'                                   ,'For rent',),
	  'obj_type as obj_type0'   => array('Тип объекта'                     ,'Object type',),
	  'obj_type as obj_type1'   => array('Тип объекта'                     ,'Object type',),
	  'sell_type'  => array('Тип сделки'                                   ,'Is for',),
	  'district_id'=> array('Город'                                        ,'City',),
	  'square_from'=> array('Площадь от'                                   ,'Square from',),
	  'square_to'  => array('Площадь до'                                   ,'Square to',),
	  'all'        => array(' -- все -- '                                  ,' -- all -- ',),
	);
  //поля для выборки из базы элема
	var $elem_fields = array(
	 'columns' => array(
		 'name'=>array(
			'type'  =>'text',
		 ),
		 'sell_type'=>array(
			 'type'  =>'select',
			 'onChange' => "javascript:Onsql_select(this.selectedIndex)",
			 'func'  => 'sell_type_select',
		 ),
		 'obj_type as obj_type0'=>array(
			 'type'  =>'select',
			 'func'  => 'obj_type0_select',
		 ),
		 'obj_type as obj_type1'=>array(
			 'type'  =>'select',
			 'func'  => 'obj_type1_select',
		 ),
		 'square_from'=>array(
			 'type'  =>'select',
			 'func'  => 'square_from_select',
		 ),
		 'square_to'=>array(
			 'type'  =>'select',
			 'func'  => 'square_to_select',
		 ),
		 'district_id'=>array(
			 'type'  =>'select',
			 'func'  => 'district_id_select',
		 ),
		'ctime'=>array(
			'type'  =>'hidden',
		 ),
		 'visible'=>array(
			'type'  =>'checkbox',
		 ),
	   ),
	   'id_field' => 'pid',
	   'type' => 'multi',
	   'title'=> 'title',
	);
	var $script = "
	var obj_type0;
	var obj_type1;
window.onload = function(){ldelim}
	{foreach from=\$obj item=v key=k}
		{if \$k == 'obj_type as obj_type0'}
			\n
			obj_type0 = document.getElementById('tr_{\$v.display.elem}');
			obj_type0.style.display = 'none';
		{/if}
		{if \$k == 'obj_type as obj_type1'}
			\n
			obj_type1 = document.getElementById('tr_{\$v.display.elem}');
			obj_type1.style.display = 'block';
		{/if}
	{/foreach}
	Onsql_select({if \$obj.sell_type.value==2}1{else}0{/if});
{rdelim}

function Onsql_select(select){ldelim}
	switch (select)
	{ldelim}case 0:
		obj_type1.style.display = 'none';
		obj_type0.style.display = 'block';
		break;
	case 1:
		obj_type0.style.display = 'none';
		obj_type1.style.display = 'block';
		break;
	{rdelim}
	{rdelim}
";
	######################
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
	function sell_type_select() {
		return sql_getRows("SELECT id,name FROM obj_transaction ORDER BY name", true);
	}
	########################
	function obj_type0_select() {
		return sql_getRows("
		SELECT b.id AS id,
		IF (
		a.id = b.id, b.name, CONCAT( '---', b.name )
		) AS name
		FROM obj_types AS a
		LEFT JOIN obj_types AS b ON a.id = b.pid
		WHERE a.id = a.pid
		AND a.id IS NOT NULL
		AND b.id IS NOT NULL
		AND b.belong = 1
		ORDER BY a.id ", true);
	}
	function obj_type1_select() {
		return sql_getRows("
		SELECT b.id AS id,
		IF (
		a.id = b.id, b.name, CONCAT( '---', b.name )
		) AS name
		FROM obj_types AS a
		LEFT JOIN obj_types AS b ON a.id = b.pid
		WHERE a.id = a.pid
		AND a.id IS NOT NULL
		AND b.id IS NOT NULL
		AND b.belong = 2
		ORDER BY a.id ", true);
	}
	########################
	function district_id_select() {
		return sql_getRows("SELECT id, name FROM `obj_locat_districts` WHERE id = pid", true);
	}
	########################
	function square_from_select() {
		return  sql_getRows("SELECT `id`,`value` FROM `obj_search_square` WHERE `position`=0 ORDER BY value", true);
	}
	########################
	function square_to_select() {
		return  sql_getRows("SELECT `id`,`value` FROM `obj_search_square` WHERE `position`=1 ORDER BY value", true);
	}
	########################
	function ElemRedactB($fld) {
	 if ($fld['sell_type']=='1') {
		$fld['obj_type'] = $fld['obj_type as obj_type0'];
	 }
	 elseif($fld['sell_type']=='2'){
		$fld['obj_type'] = $fld['obj_type as obj_type1'];
	 }
	 unset($fld['obj_type as obj_type0']);
	 unset($fld['obj_type as obj_type1']);
	 unset($fld['obj_type0']);
	 unset($fld['obj_type1']);
		return $fld;
	}
	########################
}
?>