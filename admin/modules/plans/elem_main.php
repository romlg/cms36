<?php

require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	//---------------------------------------------------------------------------------

	var $elem_name  = "elem_main";
	var $elem_type  = "single";
	var $elem_table = "obj_elem_plans_items";
	var $elem_xxx = array('elem_main');

	//поля для выборки из базы элема
	var $elem_fields = array(
		'columns' => array(
			'name'			=> array(
				'type'		=>	'text',
				'size'		=> '40',
			),
			'image'			=> array(
				'type'		=>	'input_image',
				'display'	=> array(
					'size'	=> array('1000', '1000'),
				),
			),
			'pid'			=> array(
				'type'		=> 'hidden',
				'display'	=> array(
					'func'		=> 'getPid',
				),
			),
			'x1'			=> array(
				'type'		=>	'text',
				'size'		=> '10',
			),
			'y1'			=> array(
				'type'		=>	'text',
				'size'		=> '10',
			),
			'x2'			=> array(
				'type'		=>	'text',
				'size'		=> '10',
			),
			'y2'			=> array(
				'type'		=>	'text',
				'size'		=> '10',
			),
		 ),
		 'id_field' => 'id',
	 );
	var $sql = false;
	var $elem_where="";
	var $elem_req_fields = array('image');
	var $script = "";
	//---------------------------------------------------------------------------------

	function getPid() {
		return $_GET['pid'];
	}
	
	function ElemInit(){
		$this->elem_str['image']		= array('Изображение',			'Image');
		$this->elem_str['x1']			= array('x1',					'x1');
		$this->elem_str['y1']			= array('y1',					'y1');
		$this->elem_str['x2']			= array('x2',					'x2');
		$this->elem_str['y2']			= array('y2',					'y2');
		parent::ElemInit();
	}
	
	function MyCommit($row) {
		// Переносим изображение
		if ($row['image'] && getimagesize('..'.$row['image']) && strpos($row['image'], 'plans') === false) {
			$object_id = sql_getValue('SELECT pid FROM obj_elem_plans WHERE id='.$row['pid']);
			$dir = '../files/objects/'.$object_id;
			if (!is_dir($dir)) {
				mkdir($dir);
				mkdir($dir, 0770);
			}
			$dir .= '/plans';
			if (!is_dir($dir)) {
				mkdir($dir);
				mkdir($dir, 0770);
			}
			$new_name = $dir.'/'.basename($row['image']);
			rename('..'.$row['image'], $new_name);
			$row['image'] = substr($new_name, 2);
		}
		if ($_POST['id']) {
			sql_update($this->elem_table, $row, 'id = '.$_POST['id']);
		} else {
			sql_insert($this->elem_table, $row);
		}
		$err = sql_getError();
		if (empty($err)) return 1;
		return $err;
	}

}
?>