<?php

class TRecycle extends TTable {

	var $name = 'ced/recycle';
	var $table = 'tree';
//	var $module = 'ced/'; # modules prefix

	########################

	function TRecycle() {
		global $str;

		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Корзина', 'Recycle Bin',
			),
			'restore'	=> array(
				'Восстановить', 'Restore',
			),
			'restored'	=> array(
				'Разделы успешно восстановлены', 'Items has been restored successfully',
			),
			'deleted'	=> array(
				'Разделы успешно удалены', 'Items has been deleted successfully',
			),
		));
	}

	########################

	function Show() {
		# универсальный обработчик поста
		if (!empty($_POST)) {
			$action = get('actions', '', 'p');
			if ($action) {
				if($this->Allow($action, 'tree')) {
					return $this->$action();
				}
				else {
					return;
				}
			}
		}
        require_once (core('list_table'));

		$row['delete'] = $this->str('delete');

		$this->AddStrings($row);
		$row['thisname'] = $this->name;

		$row['close'] = $this->str('close');
		$row['reset'] = $this->str('reset');
		$row['table'] = list_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'lang_select' => defined('LANG_SELECT')?LANG_SELECT:0,
					'select'	=> 'name',
					'display'	=> 'name',
				),
			),
			'where'		=> 'visible<0',
			'orderby'	=> 'uptime desc',
            'params'	=> array('page' => $row['thisname'], 'do' => 'show', 'noedit' => true,),
		), $this);

        return $this->Parse($row, 'recycle.show.tmpl');
	}
	########################

	function Edit() {
		$id = get('id', array(), 'gp');

		if (empty($id)) {
			return;
		}

		# Выясняем, нужно ли перегрузить одну ветвь или лучше все
		$pids = sql_getRows('SELECT pid FROM '.$this->table.' WHERE id in ('.join(', ', array_keys($id)).') GROUP BY pid');
		$pid = (count($pids) > 1) ? 0 : $pids[0];

		$r1 = sql_query('update '.$this->table.' set next=1 where id in ('.join(', ', $pids).')');
		$r2 = false;
		if ($r1) {
			$r2 = sql_query('update '.$this->table.' set visible=visible+1 where id in ('.join(', ', array_keys($id)).')');
		}

		if (!$r1 || !$r2) {
			return '<script>alert(\''.$this->str('error').': '.sql_getError().'\')</script>';
		} else {
            return '<script>
                    alert(\''.$this->str('restored').'\');
                    window.parent.top.opener.location.href="/admin/?page=tree&id='.$pid.'";
                    window.close();
                    </script>';
		}
	}
	######################

	function Delete() {
		$id = get('id', array(), 'gp');

		if (empty($id)) {
			return;
		}

		# Выясняем, нужно ли перегрузить одну ветвь или лучше все
		$pids = sql_getRows('SELECT pid FROM '.$this->table.' WHERE id in ('.join(', ', array_keys($id)).') GROUP BY pid');
		$pid = (count($pids) > 1) ? 0 : $pids[0];

		$r1 = sql_query("DELETE FROM ".$this->table." WHERE id IN (".join(', ', array_keys($id)).")");
		if (!$r1) {
			return '<script>alert(\''.$this->str('error').': '.sql_getError().'\')</script>';
		} else {
            return '<script>
                    alert(\''.$this->str('deleted').'\');
                    window.parent.top.opener.location.href="/admin/?page=tree&id='.$pid.'";
                    window.close();
                    </script>';
		}
	}
	######################

	function Info() {
		return array(
			'version'	=> get_revision('$Revision: 1.1 $'),
			'checked'	=> 0,
			'disabled'	=> 0,
			'type'		=> 'checkbox',
		);
	}

	######################
}

$GLOBALS['ced__recycle'] = & Registry::get('TRecycle');

?>