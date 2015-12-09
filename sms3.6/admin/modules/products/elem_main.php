<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	######################
	var $elem_name  = "elem_main";  					//�������� elema
	var $elem_table = "products";                //�������� ������� elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                       //��������� ���������
        'name'                   => array('�������� ������',                'Product name',),
        'visible'                => array('�������� ��� ������ � ���������','Visible on website',),
        'saved'                  => array('�������� ������� ���������',     'Page saved successfully',),
        'loading'                => array('��������...',                    'Loading...',),
        'description'            => array('��������',                       'Description',),
        'art'                    => array('�������',                        'Artikul',),
        'art2'                   => array('�������2',                       'Artikul2',),
        'available'              => array('���� � �������',                 'Available on stock',),
        'manufacturer_id'        => array('�������������',                  'Manufacturer',),
        'discount_group_id'      => array('��������� ������',               'Discount group',),
        'product_type_id'        => array('��� ��������',                   'Type',),
        'solution_id'        	 => array('��� ������',                     'Composition type',),        
        'price'                  => array('����',                           'Price',),
        'weight'                 => array('���, ��',                        'Weight, Kg',),
        'required'               => array('������������ ����',              'Required fields',),
        'reload'                 => array('����� ���� ������� ����������.', 'Require save',),
        'new'                    => array('�������',                        'New',),
        'top10'                  => array('������� �����',                  'Top10',),
        'hot'                    => array('���������������',                'Hot',),
	);
	//���� ��� ������� �� ���� �����
	var $elem_fields = array(
	  'columns' => array(
		'id' => array(
			'type'       => 'hidden',
		),
		'name'=>array(
			'type'       => 'text',
			'size'       => 30,
						'maxlength'  => 99,
		),
		'visible'=>array(
			'type'  =>'checkbox',
		),
		'available'=>array(
				'type'  =>'checkbox',
		),
		'art'=>array(
				'type'       => 'text',
				'size'       => 20,
				'maxlength'  => 29,
		),
		'art2'=>array(
				'type'       => 'text',
				'size'       => 20,
				'maxlength'  => 29,
		),
		'description'=>array(
				'type'       => 'textarea',
				'cols'       => 40,
				'rows'       => 3,
		),
		'manufacturer_id'=>array(
				'type'       => 'select',
				'func'       => 'get_manufacturers',
		),
		'discount_group_id'=>array(
				'type'       => 'select',
				'func'       => 'get_discount_group',
		),
		'product_type_id'=>array(
				'type'       => 'select',
				'func'       => 'get_product_type',
				'onChange'   => 'changeType(this.value)',
		),
		'solution_id'=>array(
				'type'       => 'select',
				'func'       => 'get_solutions_type',
				'onChange'   => 'changeType(this.value)',
		),  
		'price'=>array(
				'type'       => 'text',
				'size'       => 6,
				'maxlength'  => 10,
		),
		'weight'=>array(
				'type'       => 'text',
				'size'       => 10,
				'maxlength'  => 10,
		),
		'new'=>array(
				'type'  =>'checkbox',
		),
		'top10'=>array(
				'type'  =>'checkbox',
		),
		'hot'=>array(
				'type'  =>'checkbox',
		),
	  ),
	  'id_field' => 'id',
	);
	var $elem_where="";
	var $elem_req_fields = array('name');
	var $script = '
        {literal}
            function changeType(type_id){
                    alert("����� ���� ������� ����������.");
                    window.top.enable_loading();
                    document.forms.editform.act2.value="apply";
                    document.forms.editform.submit();
            }
       
          {/literal}
        
        ';
		
	########################
	function ElemRedactB($row){
		foreach ($row as $k=>$v){
			$row[$k] = e($v);
		}
		if ($row['solution_id'] == 0){
			$sql = "DELETE FROM product_composition WHERE product_id = ".$row['id'];
			sql_query($sql);
		}
		return $row;
	}
	
	########################
    function get_manufacturers(){
            return array('NULL'=>'-')+sql_GetRows("SELECT id, name FROM manufacturers WHERE visible>0 ORDER BY priority, name", true);
    }
    ########################
    function get_discount_group(){
            return sql_GetRows("SELECT id, name FROM product_discount_groups ORDER BY priority, name", true);
    }
    ########################
    function get_product_type(){
            return array('0'=>'�� ������')+sql_GetRows("SELECT id, name FROM product_types ORDER BY priority, name", true);
    }
    ########################
    function get_solutions_type(){
    		$sol_id = sql_getValue("SELECT solution_id FROM products WHERE id=".$_GET['id']);
    		if ($sol_id){
    			$this->script .= "
    			{literal}
    				var handler = window.onload;
			        window.onload = function(){
			        	handler();
			        	hideRow('tr_fld[product_type_id]');
			        	hideRow('tr_fld[manufacturer_id]');
			        	hideRow('tr_fld[price]');
			        	hideRow('tr_fld[weight]');
			        }
    			{/literal}
    			";
    		}
            $rows = sql_GetRows("SELECT id, name FROM solutions_types ORDER BY priority, name", true);
            return array('0' => '�� ������') + $rows;                
    }        
    ########################
    function ElemRedactS($fld){
            //my_query('UPDATE products SET product_type_id='.$fld['product_type_id'].' WHERE id='.$fld['id']);
    return $fld;
    }

}
?>