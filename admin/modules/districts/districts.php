<?php
define ('SHOW_LEVELS', 6);
class TObjDistricts extends TTable {
	var $name = 'districts';
	var $table = 'obj_locat_districts';
	var $prefix = "----";

	function TObjDistricts() {
		global $str, $actions;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'			=> array('Районы',					'Districts',),
			'name'			=> array('Название района',			'District Title',),
			'saved'			=> array('Данные успешно сохранены','Data has been saved successfully',),
			'parent'		=> array('Родительский район',		'Parent district'),
			'pixels'		=> array('Пиксели на image map',	'Pixels on image map',),
			'image'			=> array('Картинка',				'Image',),
			'district'		=> array('Район',					'District',),
			'city'			=> array('Город',					'City',),
			'belong'		=> array('Округ',					'Belong',),
			'all'			=> array('Все',						'All',),
			'none'			=> array('нет',						'none',),
			'pid1'			=> array('Уровень 1',				'Level 1',),
			'pid2'			=> array('Уровень 2',				'Level 2',),
			'pid3'			=> array('Уровень 3',				'Level 3',),
			'pid4'			=> array('Уровень 4',				'Level 4',),
		));

		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'delete' => array(
				'Удалить',
				'Delete',
				'link'        => 'cnt.deleteItems(\''.$this->name.'\')',
				'img'         => 'icon.delete.gif',
				'display'        => 'none',
			),
		);
	}

	########################

    /**
     * Функция строит многомерный массив районов
     *
     * @param int $id - id текущего района
     * @param int $level - уровень вложенности
     * @return массив
     */
    function getChilds($id=null, $level=0, $levels=SHOW_LEVELS){
		$childs = array();
    	if (!isset($id))
			$childs = sql_getRows("SELECT id, pid, name FROM ".$this->table." WHERE pid IS NULL ORDER BY name", true);
		else {
			$childs = sql_getRows("SELECT id, pid, name FROM ".$this->table." WHERE pid=".$id." ORDER BY name", true);
		}
    	if (!empty($childs) && $level < $levels-1) {
    		foreach ($childs as $key=>$val) {
    			for ($i=1; $i<=$levels; $i++) {
    				$childs[$key]['d_pid'][$i] = isset($childs[$key]['d_pid'][$i-1]) ? sql_getValue('SELECT pid FROM '.$this->table.' WHERE id='.$childs[$key]['d_pid'][$i-1]) : $childs[$key]['pid'];
    				if (!$childs[$key]['d_pid'][$i]) {
    					unset($childs[$key]['d_pid'][$i]);
    					break;
    				}
    			}
    			// Реверс массива пидов
    			$count = count($childs[$key]['d_pid']);
    			for ($j=1; $j<=$count; $j++)
    				$childs[$key]['d'.$j.'.pid'] = $childs[$key]['d_pid'][$count-$j+1];
    			// Пустые значения
    			for ($j=1; $j<=$levels; $j++)
    				if (!isset($childs[$key]['d'.$j.'.pid'])) $childs[$key]['d'.$j.'.pid'] = '';
    			$childs[$key]['name'] = str_pad($val['name'], strlen($val['name']) + $level*strlen($this->prefix), $this->prefix, STR_PAD_LEFT);
    			$childs[$key]['items'] = $this->getChilds($val['id'], $level+1, $levels);
    		}
    	}
    	return $childs;
    }

    function getList($pids, &$res, $full = 0) {
    	foreach ($pids as $key=>$val) {
			if ($full) $res[$val['id']] = $val; else $res[$val['id']] = $val['name'];
			if (isset($val['items'])) {
				$this->getList($pids[$key]['items'], $res, $full);
			}
    	}
    }

    function Show() {
		if (!empty($_POST)) {
			$actions = get('actions', '', 'p');
			if ($actions) {
				return $this->$actions();
			}
		}

		$pids = $this->getChilds();
		$this->getList($pids, $rows, 1);

		$districts = sql_getRows('SELECT id, name FROM '.$this->table.' WHERE pid IS NULL ORDER BY name', true);		

		require_once(core('ajax_table'));
		$ret['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'd4.id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'd4.name',
					'display'	=> 'district',
					'flags'		=> FLAG_SORT | FLAG_SEARCH,
				),
				array(
					'select'	=> 'd1.pid',
					'flags'		=> FLAG_FILTER,
					'filter_display'=> 'pid1',
					'filter_type'	=> 'array',
					'filter_value'	=> array('' => '-- все --') + $districts,
				),
				array(
					'select'	=> 'd2.pid',
				),
				array(
					'select'	=> 'd3.pid',
				),
				array(
					'select'	=> 'd4.pid',
				),
			),
			'from'		=> $this->table.' AS d4 
							LEFT JOIN '.$this->table.' AS d3 ON d4.pid=d3.id
							LEFT JOIN '.$this->table.' AS d2 ON d3.pid=d2.id
							LEFT JOIN '.$this->table.' AS d1 ON d2.pid=d1.id',
			'dataset'	=> $rows,
			'params'	=> array('page' => $this->name, 'do' => 'show'),
			'click'		=> 'ID=cb.value',
			'dblclick'	=> 'editItem(id)',
			'function'	=> 'setFilterData',
			//'_sql'=>1,
		), $this);

		$ret['thisname'] = $this->name;
		return $this->Parse($ret, $this->name.'.tmpl');
	}
	
	//-----------------------------------------------------------------------
	function setFilterData(&$data){
		$pids = array();
		$keys = array();
		if (!session_id()) session_start();
		$old_pids = isset($_SESSION[$_GET['ajax_id']]['districts']) ? $_SESSION[$_GET['ajax_id']]['districts'] : array();
		
		foreach ($data['columns'] as $k=>$v){
			$name = $v['select'];
			if (substr($name, 0, 1) == 'd' && substr($name, 2, 4) == ".pid"){
				$keys[$v['select']] = $k;
			}
		}
		for ($i=1; $i<=count($keys); $i++) {
			$name = 'd'.$i.'.pid';
			$pids[$i] = array(
				'pid' => isset($_GET['filter'][$name]) ? $_GET['filter'][$name] : "",
				'key' => $keys[$name],
				'filter' => (isset($data['columns'][$keys[$name]]['flags']) && $data['columns'][$keys[$name]]['flags'] == FLAG_FILTER)? true : false,
				'name' => $name,
			);		
		}
		
		$flag = false;
		foreach ($pids as $k=>$v){
			if ($flag){
				$pids[$k]['pid'] = "";
				if (isset($_GET['filter'][$pids[$k]['name']])){
					unset($_GET['filter'][$pids[$k]['name']]);					
				}
			} else {
				if (isset($old_pids[$k]['pid']) && $v['pid'] != $old_pids[$k]['pid']){
					$flag = true;
				} 
			}
		}		
		
		$_SESSION[$_GET['ajax_id']]['districts'] = $pids;
		session_write_close();
		
		foreach ($pids as $k=>$v){
			$next_key = $k+1;
			
			if (!empty($v['pid']) && isset($pids[$next_key])){
				$elem = &$data['columns'][$pids[$next_key]['key']];
				$elem['flags'] = FLAG_FILTER;
				$elem['filter_display'] = "pid".$next_key;
				$elem['filter_type'] = 'array';
				
				$childs = $this->getChilds($v['pid'], 0, 1);
				$list = array();
				$this->getList($childs, $list);
				$elem['filter_value'] = array('' => '-- все --') + (is_array($list) ? $list : array());
			}
			if (empty($v['pid']) && isset($pids[$next_key])){
				$elem = &$data['columns'][$pids[$next_key]['key']];
				if (isset($elem['filter_display'])){
					unset($elem['flags']);
					unset($elem['filter_display']);
					unset($elem['filter_type']);
					unset($elem['filter_value']);
					$pids[$next_key]['filter'] = false;
				}
				if (isset($_GET['filter'][$pids[$next_key]['name']])){
					unset($_GET['filter'][$pids[$next_key]['name']]);
					$pids[$next_key]['pid'] = "";
				}
				
			}
			
		}
	}

	########################

	function EditForm() {
		$id = (int)get('id');
		if ($id) {
			$ret = $this->GetRow($id);
		}
		else {
			$ret = array();
		}
		$this->SetDefaultValues($ret);
		$this->AddStrings($ret);

    	$pids = $this->getChilds();
    	$this->getList($pids, $ret['parent'], 0);
    	$ret['parent'] = array('NULL'	=> $this->str('none')) + $ret['parent'];
    	
		return $this->Parse($ret, $this->name.'.editform.tmpl');
	}

    ########################

	function Edit() {
		$id = (int) get('id',0,'p');

		$fld = &$GLOBALS['_POST']['fld'];

		$id = $this->Commit(array('name'));
		$reload = mysql_affected_rows() ? 'window.dialogArguments.document.location.reload()' : '';
		if (!is_int($id)) {
			return $this->Error($id);
		}
		return '<script>alert(\''.$this->str('saved').'\'); '.$reload.'; window.close(); </script>';
	}
}

$GLOBALS['districts'] = &new TObjDistricts();
?>