<?php

class TWorkingCopy extends TTable {

	var $insIds = array();
	//класс элема субмодуля
	var $elem_object;
	########################

	function TWorkingCopy() {

	}

	########################

	// подгружает файл с элемом и агррегирует класс элема в объект субмодуля
	function loadTab($tabname) {

		global $str;
		$tab_cfg = $this->getTabCfg($tabname);

		if (!$tab_cfg) {
			return false;
		}
		if ($tab_cfg['type'] == 'elem') {
			$elem = $tab_cfg['conf']['elem'];
			list(,$tmp) = explode('_', $elem);
			include_once (elem($this->submodule.'/'.$elem));

			$class_name = 'T'.ucfirst($tmp).'Element';
			$this->elem_object = & Registry::get($class_name);
			$this->elem_object->id = get('id', 0, 'pg');
			$this->elem_object->page = get('page', 0, 'pg');
			$this->elem_object->esId = get('esId', 0, 'pg');
			$this->elem_object->tab = get('tab', 'tab_', 'pg');
			$this->elem_object->name = $this->submodule;
			//$this->elem_object->frame = get('frame', 'cnt', 'pg');

			$this->elem_object->ElemInit();

			if (!empty($this->elem_object->str)) {
				$str[get_class_name($this)] = array_merge($str[get_class_name($this)], $this->elem_object->str);
			}
		}

	}

	######################

	function unloadTab($tabname) {
		//$this->elem_object;
	}

	######################

	// удаляет Working Copy объекта из сессии
	function clearSession($id, $esId) {
		cache_remove($this->oed_cache_id.$id.'_'.$esId);
	}

	######################

	function getesId() {
		global $esId;
		return $esId;
	}

	######################

	// удаляет элемы из сессии
	function DeleteElems() {
		$id = (int)get('id', 0, 'pg');
		$ids = get('ids', array(), 'pg');
		$rows = $this->getWC($id);
		foreach ($ids as $k => $v) {
			unset($rows[$k]);
		}
		$this->saveWC($id, $this->getesId(), $rows);
		return "<script>if (window.parent) window.parent.location += '&esId=".$this->getesId()."' ;</script>";
	}

	################

	function _make_priority(&$rows) {
		$priority = 0;
		foreach ($rows as $k => $v) {
			$rows[$k]['priority'] = ++$priority;
		}
	}

	################

	function _compare_priority($a, $b) {
		if ($a['priority'] == $b['priority']) {
			return 0;
		}
		return ($a['priority'] > $b['priority']) ? 1 : -1;
	}

	################

	function SwapElems() {

		$id = (int)get('id', 0, 'pg');
		$ids = get('ids', array(), 'pg');
		$move = (int)get('move', 0, 'pg');

		if (!is_array($ids)) {
			$ids = array($ids);
		}

		// если сдвигаем вниз, идем с конца
		if ($move > 0) {
			$ids = array_reverse($ids, true);
		}

		// получаем список элемов из сесии
		$rows = $this->getWC($id);
//pr($rows);
		// цикл по всем id
		foreach ($ids as $k => $v) {
			// находим нужный элемент
			$src = &$rows[$v];
			$sess_ids = array_keys($rows);
			// если move < 0, переворачиваем массив ключей в сессии
			if ($move < 0) {
				$sess_ids = array_reverse($sess_ids, true);
			}
			while(current($sess_ids) != $v) {
				next($sess_ids);
			}
			// находим соседний
			if ($next_id = next($sess_ids)) {
				$trg = &$rows[$next_id];

				// у найденных элементов меняем местами priority
				$p = $src['priority'];
				$src['priority'] = $trg['priority'];
				$trg['priority'] = $p;

				// сортируем полученный массив по полю priority
				uasort($rows, array($this, '_compare_priority'));
			}
		}

		// сохраняем в сессию
		$this->saveWC($id, $this->esId, $rows);

		return "<script>if (window.parent) if (window.parent.navigate) window.parent.navigate(); else window.parent.location.reload();</script>";
	}

	################

	// возвращает Working Copy объекта
	function getWC($id = 0, $elem_id = 0) {

		$rows = array();
		// получаем запись из сессии
		//$rows = $this->getWCfromSession($id, $this->esId);

		// если создаем и в сессии пусто, инициализируем пустую запись в сессии
//		if (!$rows && !$id) {
//			$this->saveWC($id, $this->esId, $rows);
//			return $rows;
//		}
		// если в сессии пусто, получаем запись из БД
		if (method_exists($this->elem_object, 'getWCfromDb')) {
			$rows = $this->elem_object->getWCfromDb($id);
//			$this->saveWC($id, $this->esId, $rows);
		}

		// @todo ??? возможно надо переделать
		if (!$elem_id) {
			return $rows;
		}

		$row = array();
		if (isset($rows[$elem_id])) {
			$row = $rows[$elem_id];
		}
		return $row;
	}

	######################

	//возвращает текущую копию объекта для редактирования из сессии
	//на входе - id объекта и id сессии редактирования объекта
	function getWCfromSession($id, $esId) {
		$copy = cache_get($this->oed_cache_id.$id.'_'.$esId);
		if (!isset($copy[$this->tab])) {
			return false;
		}
		$ret = $copy[$this->tab];
		if (get_magic_quotes_gpc()) {
			foreach ($ret as $key=>$val) {
            	if (is_array($val)) {
					foreach ($val as $k=>$v) {
						$ret[$key][$k] = stripslashes($v);
					}
				}
				else {
					$ret[$key] = stripslashes($val);
				}
			}
		}
		return $ret;
	}

	######################

	// вызывается для сохранения working copy в сессию
	function saveWC($id, $esId, $wc) {
		$copy = cache_get($this->oed_cache_id.$id.'_'.$esId);
		$copy[$this->tab] = $wc;
		cache_save($this->oed_cache_id.$id.'_'.$esId, $copy);

	}

	########################

	// обработка пришедших из формы значений, складывание из со значениями в сессии
	function handleChanges($id, $esId, $fld, $elem_id) {
		$wc = $this->getWCfromSession($id, $esId);
		$target_wc = &$wc;
		if ($elem_id != -1) {
			$fld = &$fld[$elem_id];
			if (!strpos($elem_id, '_') && $elem_id == 0) {
				$ids = array_keys($wc);
				$ks = array(0);
				foreach ($ids as $k => $v) {
					if (!strpos($v, '_')) {
						continue;
					}
					list(,$n) = explode('_', $v);
					$ks[] = $n;
				}
				$elem_id = 'new_'.(max($ks) + 1);
				// Колонка с ID родителя не обязательно должна называться pid
				$id_field = isset($this->elem_object->elem_fields['id_field']) && $this->elem_object->elem_fields['id_field'] ? $this->elem_object->elem_fields['id_field'] : 'pid';
				$wc[$elem_id] = array(
					$id_field => $id,
					'id' => $elem_id,
					'priority' => 0,
				);
			}
			$target_wc = &$wc[$elem_id];
		}

		$tab = $this->getTabCfg($this->tab);

		/*
		загружаем все файлы на сервер в темповую папку
		*/
		 /*
		if (isset($_FILES['fld'])) {
			//скидываем файлы в темповую директорию
			if (isset($_COOKIE[session_name()])){
				 $sesId = $_COOKIE[session_name()];
			} else if (isset($_COOKIE['sid'])){
				$sesId = $_COOKIE['sid'];
			}

			$folder = CACHE_DATA_DIR.$sesId.'/files';

			pr($fld);
			pr($_FILES['fld']);
		}
     # берем elem_id для типа Multi
	  if ($this->elem_type == 'multi') {
          $elem_id = $_POST['elem_id'];
      }
	  $pst_files = isset($elem_id) ? $_FILES['fld']['name'][$elem_id] : $_FILES['fld']['name'];
	  foreach ($pst_files as $k=>$v) {
          # выбор директории для закачивания:
		  if (isset($this->elem_fields['columns'][$k]['display']['folder'])) {
              $dir = $this->elem_fields['columns'][$k]['display']['folder']; //Директория для закачивания собственная
		  }
          else {
              $dir = $this->elem_fields['folder']; //Директория для закачивания глобальная
		  }
          $size = $this->elem_fields['columns'][$k]['display']['size']; //размер файла

		  if (!empty($v[0])) {  # закачка и ресайз с компьютера пользователя
              # запихиваем в массив $files данные для закачки
              $files[$k] = array(
                    //'value'    => $fld[$k],   // текущее значение
                    'name'     => $v['0'],
                    'type'     => isset($elem_id) ? $_FILES['fld']['type'][$elem_id][$k]['0']     : $_FILES['fld']['type'][$k]['0'],
                    'tmp_name' => isset($elem_id) ? $_FILES['fld']['tmp_name'][$elem_id][$k]['0'] : $_FILES['fld']['tmp_name'][$k]['0'],
                    'error'    => isset($elem_id) ? $_FILES['fld']['error'][$elem_id][$k]['0']    : $_FILES['fld']['error'][$k]['0'],
                    'size'     => isset($elem_id) ? $_FILES['fld']['size'][$elem_id][$k]['0']     : $_FILES['fld']['size'][$k]['0'],
              );
              # закачиваем и ресайзим
              $this->Download( $files[$k], $dir, $size, $k);
          }*/


		// @trick для мультиэлемов
			// когда уходим с формы со списком, не должна вызыватся preHandleChanges
			//if (method_exists($this, 'preHandleChanges') && ($elem_id != -1 || ($elem_id == -1 && !$tab['conf']['next']))) {
			//	$fld = $this->preHandleChanges($id, $fld);
			//}
		//}
		if (!defined('USE_ED_VERSION') || USE_ED_VERSION == '1.0.0') {
			// @trick для мультиэлемов

			// когда уходим с формы со списком, не должна вызыватся preHandleChanges
			if (method_exists($this->elem_object, 'preHandleChanges') && ($elem_id != -1 || ($elem_id == -1 && !$tab['conf']['next']))) {
				$fld = $this->elem_object->preHandleChanges($id, $fld);
			}
		}
		/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/

		if ($elem_id != -1 || ($elem_id == -1 && !$tab['conf']['next'])){
			if (method_exists($this->elem_object,'ElemRedactS'))
				$fld = $this->elem_object->ElemRedactS($fld);
		}

		foreach ($fld as $k => $v) {
			$target_wc[$k] = $v;
		}

		if ($tab['conf']['next']) {
			$this->_make_priority($wc);
		}

		return $wc;
	}

	#####################

	// сохраняет данные из сесии в БД, вызывая обраотчики из элемов
	function save($id, $esId, $wc) {
		$old_id = $id;

		$this->unloadTab($this->tab);
		$old_tab = $this->tab;

		$error = '';
		$error_tab = false;
		// формируем массив всех вкладок
		$tabs = array('tab_' => $this->getTabCfg('tab_')) + $this->tabs;
		// 1. Проверка на ошибки
		// ...
		// 2. Если есть ошибки, загружаем нужную вкладку и выходим
		// ...
		// 3. Цикл по всем вкладкам
		// запуск транзакции
		sql_query('BEGIN');

		foreach ($tabs as $k => $v) {
			if ($error) {
				continue;
			}
			$this->tab = $k;
			$this->loadTab($k);

			// 3.1. MultiElem
			if ($v['conf']['next']) {
				// 3.1.1. Выбор из сессии
				$rows_sess = $this->getWCfromSession($id, $this->esId);//$this->getWC($id);
				if ($rows_sess !== false){
					$ids_sess = array_keys($rows_sess);
					// 3.1.2. Выбор из БД
					$rows_db = $this->elem_object->getWCfromDb($id);
					$ids_db = array_keys($rows_db);
					// 3.1.3. Сравнение на предмет удаленных
					$to_del = array_diff($ids_db, $ids_sess);
					// 3.1.4. Удаление
					foreach ($to_del as $del_id) {
						$res = $this->elem_object->ElemDel($del_id);
						// ошибка
						if (!is_bool($res)) {
							$error_tab = $k;
							$error = $res;
							break;
						}
					}

					if ($error) {
						// завершение
						$this->unloadTab($k);
						continue;
					}
				}
				if (!empty($rows_sess)){
					// 3.1.5. Сравнение на предмет добавленных
					$to_add = array_diff($ids_sess, $ids_db);

					// 3.1.6. Добавление

					foreach ($to_add as $add_id) {
						$res = $this->elem_object->ElemAdd($id, $rows_sess[$add_id]);
						// ошибка
						if (!is_int($res)) {
							$error_tab = $k;
							$error = $res;
							break;
						}
						$this->insIds[$this->tab][$res] = $rows_sess[$add_id];
					}
					if ($error) {
						// завершение
						$this->unloadTab($k);
						continue;
					}

					// 3.1.7. Edit оставшихся
					$to_edit = array_diff($ids_sess, $to_del, $to_add);

					foreach ($to_edit as $edit_id) {
						if (count($rows_sess[$edit_id]) - 1 == count($rows_db[$edit_id]) && count($this->elem_object->elem_fields['columns'])+1 != count($rows_db[$edit_id]) && (count($this->elem_object->elem_fields['columns'])+2 > count($rows_db[$edit_id]))){
							//последнее условие экспериментально 12.04.07
							continue;
						}
						$elem_id = $rows_sess[$edit_id]['id'];
						$res = $this->elem_object->ElemEdit($id, $rows_sess[$edit_id], $elem_id);
						// ошибка
						if (mysql_errno()){
							echo "Mysql error on elem '".$this->tabs[$this->tab]['conf']['elem']."' :".mysql_error()."\r\n";
						}
						if (!is_int($res)) {
							$error_tab = $k;
							$error = $res;
							break;
						}
						$this->insIds[$this->tab][$res] = $rows_sess[$edit_id];
					}
				}
				// завершение
				$this->unloadTab($k);
				continue;
			}

			// 3.2. Elem
			// 3.2.1. Выбор из сессии
			$row = $this->getWCfromSession($id, $this->esId);//$this->getWC($id);
			if (!$row) {
				// завершение
				$this->unloadTab($k);
				continue;
			}
			// 3.2.2. Запись в БД
			$res = $this->elem_object->ElemEdit($id, $row);
			if (!is_int($res)) {
				$error_tab = $k;
				$error = $res;
			}
			elseif ($k == 'tab_') {
				// при создании нового объекта мы получаем новый идентификатор,
				// который должны использовать в дальнейшем, как pid для элемов
				$id = $res;
				$this->insIds[$this->tab][$res] = $row;
			}
			// завершение
			$this->unloadTab($k);
		}
		$this->tab = $old_tab;
		// не все ок
		if ($error_tab) {
			sql_query('ROLLBACK');
			$_POST['newTab'] = $error_tab;
			return "<script>if (window.top) window.top.disable_loading();alert('".addslashes($error)."');</script>".$this->newTab($old_id, $this->esId);
		}

		// все ок
		// закрытие транзакции
		sql_query('COMMIT');
		if ($_POST['page']=='tree'){
			$this->tab="tab_";
			$temp = $this->getWCfromSession($old_id, $esId);
			$pid = $temp['pid'];
			$reload = 'if (window.top.opener && window.top.opener.reloadNode) window.top.opener.reloadNode('.$pid.');
			else if (window.parent.reloadNode) window.parent.reloadNode('.$pid.');
			else if (window.top.opener) {
			     var loc = window.top.opener.location;
			     var re = /page=tree/i;
			     if (loc.search(re) != -1)
			     window.top.opener.location.reload();
			}
			else alert("reload failed");';
		}
		else {
			//if ($v['conf']['next'])
				$reload = 'if (window.top.opener) { if (window.top.opener.navigate) window.top.opener.navigate(); else window.top.opener.location.reload(); }';
			//else
			//	$reload = 'if (window.top.opener) window.top.opener.location.reload();';
		}
		// очищаем сессию для того, чтобы при apply в сессию загрузились уже новые данные
		$this->clearSession($old_id, $this->esId);

		$act = get('act2', '', 'pg');
		$apply = ($act == 'apply');
		$close = !$apply ? 'window.top.close();' : '';
		if ($apply) {
			$oed_vars =  cache_get($this->oed_query_cache_id);
			$addition = '';
			if (!empty($oed_vars['first'])){ $addition = '&first='.$oed_vars['first'];}
			if (!empty($oed_vars['last'])){ $addition .= '&last='.$oed_vars['last'];}

			$reload .= ($old_id == 0) ? 'if (window.top) window.top.location.href = \''.BASE.'/ed.php?page='.$this->submodule.'&id='.$id.$addition.'\';' : 'if (window.parent && navigator.userAgent.toLowerCase().indexOf("msie") != -1) {window.parent.location.reload();}';
		}
		$script = $reload."if (window.top) {alert('".$this->str('saved')."');}".$close;
		return "<script>if (window.top) window.top.disable_loading();".$script."</script>";
	}

	######################

	// возвращает параметры вкладки с именем $tabname
	function getTabCfg($tabname) {
		if ($tabname == 'tab_') {
			$cfg = array(
				'type' => 'elem',
				'conf' => array(
					'elem' => 'elem_main',
					'next' => 0,
					'target' => 'cnt',
				),
			);

			if (isset($this->elem_main_next)){
				$cfg['conf']['next'] = $this->elem_main_next;
			}

			return $cfg;
		}
		if (empty($this->tabs[$tabname]) || !is_array($this->tabs[$tabname])) {
			return false;
		}
		return $this->tabs[$tabname];
	}

	######################

	// возвращает ссылку на вкладку с именем $tabname
	function getTabLink($tabname, $id, $esId) {
		$elem_id = get('n_'.$tabname.'_id', 0, 'pg');
		$tab = $this->getTabCfg($tabname);
		$page = $this->submodule;
		$target = ($tab['conf']['target'] == 'cnt') ? 'frame=cnt&' : '';
		$last = get('last', '', 'p');
		$link = 'ed.php?'.$target.'page='.$page.'&tab='.$tabname.'&id='.$id.($elem_id ? '&elem_id='.$elem_id : '').'&esId='.$esId.($last ? '&last='.$last : '');

		return $link;
	}

	######################

	// возвращает js для открытия новой вкладки
	function newTab($id, $esId) {
		$newTab = get('newTab', '', 'p');
		$link = $this->getTabLink($newTab, $id, $esId);
		$tab = $this->getTabCfg($newTab);
		$target = ($tab['conf']['target'] == 'cnt') ? 'window.top.frames[\'cnt\'].' : 'window.top.';

		return "<script>if (window.top) window.top.elemActionsHide(); ".$target."document.location.href = '".$link."'</script>";
		//exit();
	}

	#######################

	// "закрывает" вкладку, сохраняя данные, переключая вкладку или закрывая редактор
	function closeTab() {
		$id = (int)get('id', 0, 'pg');
		$elem_id = get('elem_id', -1, 'pg');
		$esId = $this->getesId();

		$fld = array_key_exists('fld', $_POST) ? $_POST['fld'] : array();
		$wc = $this->handleChanges($id, $esId, $fld, $elem_id);

		// сохранение в сесиию
		$this->saveWC($id, $esId, $wc);
		$act = get('act2', '', 'pg');

		// новая вкладка
		if ($act == 'newTab') {
			return $this->newTab($id, $esId);
		}

		// сохранение изменений
		if ($act == 'save' || $act == 'apply') {
			$save = $this->save($id, $esId, $wc);
			//------------------------------------------------------------------------------
			// добавляем возможность вызова какой-либо функции перед закрытием редактора
			// передается в get или post как "last"
			$oed_vars =  cache_get($this->oed_query_cache_id);

			if (!empty($oed_vars['last'])){
				$func = $oed_vars['last'];
				$params = array(
					'id'     => $id,
					'esId'   => $esId,
					'wc'     => array( get('tab', '0', 'p') => $wc),
					'insIds' => $this->insIds,
					'query'  => $oed_vars,
				);
				$text = $this->$func( $params);
				if (isset($text['error']) && $text['error'] == true){
					$save = $text['message'];
				}
			}
			if ($act == 'save'){
				cache_remove($this->oed_query_cache_id);
			}
			//------------------------------------------------------------------------------
			return $save;
		}

		// отмена изменений
		if ($act == 'cancel') {
			$this->clearSession($id, $esId);
			return "<script>if (top.opener && top.opener.focusItem) top.opener.focusItem(); window.top.close();</script>";
		}
	}

	#######################

	// отображает закладку
	function ShowTab($tabname = '', $method = '') {
		$tab = $this->getTabCfg($tabname);
		if (!$tab) {
			return '404. Tab "'.$tabname.'" not found';
		}
		if ($tab['type'] == 'elem') {
			if (empty($method)) {
				if ($tab['conf']['next']) {
					return $this->elem_object->ElemList();
				}
				return $this->elem_object->ElemForm();
			}
			return call_user_func(array(&$this->elem_object, $method));
		}
		// show custom tab
		return 'Custom tab "'.$tabname.'". Not implemented yet, sorry.';
	}

	#######################

}

?>