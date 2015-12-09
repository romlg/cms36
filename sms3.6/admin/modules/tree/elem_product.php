<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');
class TProductElement extends TElems{
	######################
	function ElemInit() {
		$this->str = array(
/*		'add'	 	=> array('Добавить','Add',),
		'title'	    => array('Документ','Document',),
		'name'	    => array('Название','name',),
		'hot'	    => array('Горячий','hot',),
		'name'	    => array('Заголовок','Title',),
		'image'	    => array('Изображение','Image',),
		'date'	    => array('Дата','Date',),
		'description'=> array('Краткое описание','Description',),
		'text'		 => array('Текст',	'Text',),
		'visible'	 => array('Показывать','Visible',),
		'error'	     => array('Ошибка','Error',),*/
		);
	}

	########################

	function getWCfromDb($id) {
		return sql_getRows("SELECT ep.id,ep.pid,p.name,ep.priority
		 FROM elem_product AS ep
		 LEFT JOIN products AS p ON ep.id=p.id
		 LEFT JOIN tree AS t ON ep.pid=t.id WHERE ep.pid=".$id." ORDER BY priority", true);
	}

	######################

	function ElemEdit($id, $row, $elem_id) {
		$this->table = 'elem_product';
		$_POST['id'] = $id;
		$_POST['fld'] = $row;
		unset($_POST['fld']['name']);
		if (sql_query('REPLACE INTO '.$this->table.'(`id`,`pid`,`priority`) VALUES('.$row['id'].','.$row['pid'].','.$row['priority'].') '))
		{
			return $id;
		}
		else {
			return sql_getError();
		}
	}

	######################

	function ElemAdd($id, $row) {
		$this->table = 'elem_product';
		$_POST['id'] = 0;
		$_POST['fld'] = $row;
		unset($_POST['fld']['name']);
		return $this->Commit();
	}

	######################

	function AddProducts() {
	  $pid = get('id',0,'p');
	  $ids = get('ids', array(), 'p');
	  $this->table = 'elem_product';

	  $object = $this->getObject();
	  $rows = $object['object'];
	  foreach ($ids as $k => $v){
		$rows[$v] = array(
			'id'   => $v,
			'pid'  => $pid,
			'name' => sql_getValue('SELECT name FROM products WHERE id='.$v),
			);
	  }
	  $oed = & Registry::get('object_editor_submodule');
	  $oed->saveWC($pid, $oed->getesId(), $rows);

	  unset($_POST['ids']);

	  return "<script>alert('".$this->str('saved')."');window.top.opener.location.reload();window.top.close();</script>";
	}

	######################

	function ElemDel($id) {
		$this->table = 'elem_product';
		$_POST['id'] = array($id);
		return $this->DeleteItems();
	}

	######################

	function ElemList() {
		$id = (int)get('id', 0);
		$rows = array();
		// если редактируем
		if ($id) {
			$object = $this->getObject();
			$rows = $object['object'];
		}
		require_once (core('ajax_table'));

	   $_GET['limit'] = '-1';
		$data['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	 => 'ep.id',
					'as'		 => 'id',
					'display'    => 'ids',
					'type'       => 'checkbox',
					'width'	     => '1px',
				),
				array(
					'select'	 => 'p.name',
					'as'		 => 'name',
					'display'    => 'name',
				),
			),
			'dataset' => $rows,
			'from'    => 'elem_product AS ep
						 LEFT JOIN products AS p ON ep.id=p.id
						 LEFT JOIN tree AS t ON ep.pid=t.id',
			'params'	=> array(
				'id' => $id,
				'page' => $this->name,
				'esId' => $this->esId,
				'tab' => $this->tab,
				'frame' => 'cnt',
			),
			'editform_params' => array(
				'do' => 'closeTab',
				'act2' => '',
				'newTab' => '',
				'move' => '',
				'frame' => 'tmp',
			),
			'action' => 'ed.php',
			'click' 	=> 'ID=cb.value',
			//'dblclick' => 'editElem()',
			'target' => 'tmp'.$this->name.$id,
			//'_sql'=>1,
		), $this);

		$this->AddStrings($data);
		$data['id'] = $id;
		$data['tab'] = $this->tab;
		$data['esId'] = $this->esId;

		return $this->Parse($data, 'elem_product.list.tmpl');
	}

	######################

	function preHandleChanges($id, $fld) {
		return $fld;
	}

	######################

}

?>