<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TParamsElement extends TElems{

	######################
	var $elem_name  = "elem_params";  					//название elema
	var $elem_table = "product_params";                //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
	var $elem_str = array(                       //строковые константы
        'caption'       => array('Описание',                                        'Description'),
        'header'        => array('Название',                                        'Text'),
        'count'         => array('кол-во',                                        'Count'),
        'value'         => array('Значение',                                        'value'),
        'saved'         => array('Страница успешно сохранена',        'Page saved successfully'),
        'name'          => array('Параметр',                                        'Parameter'),
        'C_DEL'         => array('Удалить','Delete'),
	);
	var $order = "";
	var $window_size="";
	//поля для выборки из базы элема
	var $elem_fields = array(
	  'columns' =>  array(
	  ),
          'id_field' => 'product_id',
          'type' => 'single',
	);
	var $elem_where="";
	var $elem_req_fields = array();
	var $columns;

    var $elem_actions = false;
    var $click = '';
    var $dblclick = '';
    var $elem_xxx = array('elem_params');
	########################
    function MyCommit($row){
        //pr($row);
      $res = sql_query('REPLACE INTO product_params(product_id,product_type_param_id,value) VALUES ('.$row['product_id'].', '.$row['id'].', "'.$row['value'].'")');
    return (int)$row['product_id'];

    }
    ########################
    function getWCfromDb($id){

      $type_id = sql_getValue(' SELECT `product_type_id`
                                      FROM `products`
                                      WHERE id ='.$id);
      $row = sql_getRows('
               SELECT pp.product_id,ptp.id,ptp.id as product_type_param_id,ptp.name, pp.value
               FROM product_type_params AS ptp
               LEFT JOIN `product_params` AS pp ON pp.product_type_param_id = ptp.id
               AND pp.product_id ='.$id.'
               WHERE ptp.pid ='.$type_id,false);
      if (!empty($row)) {
         foreach ($row as $k=>$v){
              if (empty($row[$k]['product_id']))
              {
               $row[$k]['product_id'] = $id;
              }
         }
         foreach ($row as $k=>$v){
                $new_row[$row[$k]['id']] = $row[$k];
         }
         return $new_row;
     }

    return $row;
    }
    ########################
	function ElemInit(){
	 $this->columns = array(
        array(
            'select'  => 'name',
            'display' => 'header',
            'width'   => '50%',
        ),
        array(
            'select'  => 'value',
            'as'      => 'value',
            'display' => 'value',
            'type'    => 'edit',
            'width'   => '50%',
        ),
	);
	 TElems::ElemInit();
	}
	########################
    function table_get_edit(&$value, &$column, &$row) {
        $size = isset($column['size']) ? $column['size'] : '';
        $maxlength = isset($column['maxlength']) ? $column['maxlength'] : '';
        $text_align = isset($column['text-align']) ? $column['text-align'] : 'left';
        return "<input type=text name='fld[".$row['id']."][value]' value='$value' size='$size' maxlength='$maxlength' style='text-align: $text_align'><input type='hidden' name='fld[".$row['id']."][product_id]' value='".$row['product_id']."'><input type='hidden' name='fld[".$row['id']."][id]' value='".$row['id']."'><input type='hidden' name='fld[".$row['id']."][name]' value='".$row['name']."'><input type=image src='images/s.gif' width=1 height=1>";
        return $value;
    }
    ######################
    function MyDeleteItems(){
        //pr('product_id-'.$_POST['fld']['id']);
        //pr('product_type_param_id-'.$_POST['id'][0]);
        return true;
    }
    function ElemRedactB($row){
		foreach ($row as $k=>$v){
			$row[$k] = mysql_escape_string($v);
		}
		return $row;
	}

}
?>