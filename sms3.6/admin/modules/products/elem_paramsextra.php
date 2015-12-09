<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TParamsExtraElement extends TElems{

	######################
	var $elem_name  = "elem_paramsextra";  		  //название elema
	var $elem_table = "products_params_extra";        //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                            //строковые константы
                        'caption'       => array('Описание',                   'Description'),
                        'header'        => array('Название',                   'Text'),
                        'count'         => array('кол-во',                     'Count'),
                        'value'         => array('Значение',                   'value'),
                        'saved'         => array('Страница успешно сохранена', 'Page saved successfully'),
                        'C_DEL'         => array('Удалить',                    'Delete'),
		);
	var $order = " ORDER BY priority";
	var $window_size="Width=500, Height=190";
	//поля для выборки из базы элема
	var $elem_fields = array(
	  'columns' =>  array(
                 'pid'=>array(
                          'type'  =>'hidden',
                 ),
		'header'=>array(
			  'type'  =>'text',
		  ),
		'count'=>array(
			  'type'  =>'text',
		  ),
		'value'=>array(
			  'type'  =>'text',
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
                     'select'   => 'id',
                     'display'  => 'ids',
                     'type'     => 'checkbox',
             ),
             array(
                     'select'   => 'header',
                     'display'  => 'header',
                     'type'     => 'edit_extra',
             ),
             array(
                     'select'   => 'count',
                     'display'  => 'count',
                     'type'     => 'edit',
             ),
             array(
                     'select'   => 'value',
                     'display'  => 'value',
                     'type'     => 'edit',
             ),
	);
	 TElems::ElemInit();
	}
	
	
	function ElemRedactB($row){
		foreach ($row as $k=>$v){
			$row[$k] = mysql_escape_string($v);
		}
		return $row;
	}
	########################
        function table_get_edit(&$value, &$column, &$row) {
                $size = isset($column['size']) ? $column['size'] : '';
                $maxlength = isset($column['maxlength']) ? $column['maxlength'] : '';
                $text_align = isset($column['text-align']) ? $column['text-align'] : 'left';
                return "<input onkeypress='modified(1)' onpaste='modified(1)' type=text name='fld[".$row['id']."][{$column['display']}]' value='$value' size='$size' maxlength='$maxlength' style='text-align: $text_align'><input type=image src='images/s.gif' width=1 height=1>";
        }
        ########################
        function table_get_edit_extra(&$value, &$column, &$row) {
                $pid = get('id',0, 'pg');
                $size = isset($column['size']) ? $column['size'] : '';
                $maxlength = isset($column['maxlength']) ? $column['maxlength'] : '';
                $text_align = isset($column['text-align']) ? $column['text-align'] : 'left';
                return "<input onkeypress='modified(1)' onpaste='modified(1)' type=text name='fld[".$row['id']."][{$column['display']}]' value='$value' size='$size' maxlength='$maxlength' style='text-align: $text_align'><input type='hidden' name='fld[".$row['id']."][id]' value='".$row['id']."'><input type='hidden' name='fld[".$row['id']."][pid]' value='".$pid."'>  <input type=image src='images/s.gif' width=1 height=1>";
        }


}
?>