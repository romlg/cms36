<?php

class TModules extends TTable {

	var $name = 'modules';
	var $table = 'modules';

	########################

	function TModules() {
		global $actions, $str;

		TTable::TTable();

		$this->post = is_devel() ? array() : array('enabled');

		$actions[$this->name]['edit'] = &$actions['table']['edit'];
		if (is_devel()) {
			$actions[$this->name]['create'] = &$actions['table']['create'];
			$actions[$this->name]['delete'] = array(
				'Удалить',
				'Delete',
				'link'	=> 'cnt.deleteItems(\''.$this->name.'\')',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			);
		}
		$actions[$this->name]['make_conf'] = array(
			'Пересоздать кеш',
			'Recreate Cache',
			'link'	=> 'cnt.makeConf()',
			'img' 	=> 'icon.update.gif',
			'display'	=> 'none',
		);

		$actions[$this->name.'.editform'] = array(
			'close' => &$actions['table']['close'],
		);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Модули сайта',
				'Site Modules',
			),
			'h_add'	=> array(
				'Добавление нового модуля',
				'Add module',
			),
			'h_edit'	=> array(
				'Редактирование модуля',
				'Edit module',
			),
			'name'	=> array(
				'Название',
				'Name',
			),
			'display_ru'	=> array(
				'Отображаемое название (ru)',
				'Display (ru)',
			),
			'display_en'	=> array(
				'Отображаемое название (en)',
				'Display (en)',
			),
			'pid'	=> array(
				'Родительский модуль',
				'Parent Module',
			),
			'type' => array(
				'Тип',
				'Type',
			),
			'required'	=> array(
				'Требуемый',
				'Required',
			),
			'enabled'	=> array(
				'Включен',
				'Enabled',
			),
			'allowed'	=> array(
				'Разрешен',
				'Allowed',
			),
			'saved' => array(
				'Даные были успешно сохранены',
				'Data has been saved successfully',
			),
			'SITE' => array(
				'Модуль сайта',
				'Site module',
			),
			'ADMIN' => array(
				'Модуль системы администрирования',
				'Admin module',
			),
			'write_conf' => array(
				'Кеш-файл успешно обновлен',
				'Cache file has been saved successfully',
			),
			'e_write_conf' => array(
				'Невозможно записать кеш-файл',
				'Cannot write cache file!',
			),
		));
	}

	########################

	function Show() {
		if (!empty($_POST)) {
			$action = get('actions', '', 'p');
			if ($action) {
				if($this->Allow($action)) {
					return $this->$action();
				}
				else {
					return $this->alert_method_not_allowed();
				}
			}
		}

		require_once(core('ajax_table'));

		$data['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'		=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'		=> 'display_'.int_lang(),
					'display'	=> 'name',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'		=> 'name',
					//'display'	=> 'name',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'		=> 'type',
					'display'	=> 'type',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
				),
				array(
					'select'		=> 'allowed',
					'display'	=> is_devel() ? 'allowed' : NULL,
					'type'		=> 'allowed',
					'align'		=> 'center',
				),
				array(
					'select'		=> 'required',
					'display'	=> 'required',
					'type'		=> 'required',
					'align'		=> 'center',
				),
				array(
					'select'		=> 'enabled',
					'display'	=> 'enabled',
					'type'		=> 'enabled',
					'align'		=> 'center',
				),
			),
			'from'		=> $this->table,
			'orderby'	=> 'required DESC, id',
			'params'		=> array('page' => $this->name, 'do' => 'show'),
			'where'		=> !is_devel() ? 'allowed=1' : '',
			'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID = cb.value',
		), $this);

		$this->AddStrings($data);
		return $this->Parse($data, $this->name.'.tmpl');
	}

	########################

	function table_get_required(&$value, &$column, &$row) {
		$checked = !empty($row[$column['display']]) ? 'checked="checked"' : '';
		$disabled = !is_devel() ? 'disabled="disabled"' : 'onclick="return false;"';
		return '<input type="checkbox" name="'.$column['display'].'['.$row['id'].']" value="1" '.$checked.' '.$disabled.' />';
	}

	########################

	function table_get_enabled(&$value, &$column, &$row) {
		$checked = !empty($row['required']) || !empty($row['enabled']) ? 'checked="checked"' : '';
		//$disabled = !empty($row['required']) ? 'disabled="disabled"' : '';
		$disabled = '';
		return '<input type="checkbox" name="'.$column['display'].'['.$row['id'].']" value="1" '.$checked.' '.$disabled.' onclick="return false;" />';
	}

	########################

	function table_get_allowed(&$value, &$column, &$row) {
		$checked = !empty($row['allowed']) || !empty($row['allowed']) ? 'checked="checked"' : '';
		$disabled = !is_devel() ? 'disabled="disabled"' : '';
		return '<input type="checkbox" name="'.$column['display'].'['.$row['id'].']" value="1" '.$checked.' '.$disabled.' onclick="return false;" />';
	}

	########################

	function EditForm() {
		$id = (int)get('id', 0);
		if ($id) {
			$row = $this->getRow($id);
			//$row['readonly'] = 'readonly="readonly"';
			$GLOBALS['title'] = $this->str('h_edit');
		}
		else {
			$row['id'] = $id;
			$row['pid'] = 1;
			$row['type'] = 'SITE,ADMIN';
			$GLOBALS['title'] = $this->str('h_add');
		}

		$this->AddStrings($row);

		$row['disabled'] = !is_devel() ? 'disabled="disabled"' : '';

		$row['allowed'] = !empty($row['allowed']) ? 'checked="checked"' : '';
		$row['required'] = !empty($row['required']) ? 'checked="checked"' : '';
		$row['enabled'] = !empty($row['enabled']) || !empty($row['required']) ? 'checked="checked"' : '';
		$row['pid'] = $this->GetArrayOptions(sql_getRows("SELECT id, name FROM modules ORDER BY id", true), $row['pid'], true);
		$row['type'] = explode(',', $row['type']);
		$row['type'] = $this->GetArrayOptions(array('SITE', 'ADMIN'), $row['type'], false, true, true);

		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function Edit() {
		$id = get('id', 0, 'p');

		if (is_devel() && (int)$id) {
			$row = sql_getRow("SELECT allowed, required FROM ".$this->table." WHERE id=".(int)$id);
			$_POST['fld'] = array_merge($row, $_POST['fld']);
		}

		$required = is_devel() ? array('pid', 'name', 'display_ru', 'display_en', 'type') : array();

		$res = $this->Commit($required);
		$reload = mysql_affected_rows() ? "window.parent.location.reload()" : "";
		if (is_int($res)) {
			if ($this->update_modules_conf()) {
				$update = $this->str('write_conf');
			}
			else {
				$update = $this->str('e_write_conf');
			}
			return "<script>alert('".$this->str('saved')."\\n".$update."'); $reload</script>";
		}
		return $this->Error($res);
	}

	######################

	function EditConf() {
		if (!$this->update_modules_conf()) return $this->Error($this->str('e_write_conf'));
		return "<script>alert('".$this->str('write_conf')."');</script>";
	}
	######################

	function update_modules_conf() {

		$rows = sql_getRows("SELECT name, display_ru, display_en, required, enabled, type FROM ".$this->table." WHERE allowed=1", true);
		//statuslog($rows);

		$filename = '../configs/modules.cfg.php';

		// Let's make sure the file exists and is writable first.
		if (!$handle = fopen($filename, 'w')) return false;

		foreach ($rows as $key => $row) {
			unset($rows[$key]['display_ru']);
			unset($rows[$key]['display_en']);
			$rows[$key]['display'] = array(
				$row['display_ru'],
				$row['display_en'],
			);
		}

		$str = var_export($rows, true);
		$ser = array("'0'", "'1'", "'SITE'", "'ADMIN'", "'SITE,ADMIN'");
		$rep = array('false', 'true', 'SITE', 'ADMIN', 'SITE | ADMIN');
		$str = str_replace($ser, $rep, $str);

		fwrite($handle, sprintf("<?php\n\n// define module type constants\nDEFINE ('SITE', 1);\nDEFINE ('ADMIN', 2);\n\n// define modules\n\$cfg['modules'] = %s;", $str));
		fclose($handle);

		return true;
	}

	######################
}

$GLOBALS['modules'] = & Registry::get('TModules');

?>