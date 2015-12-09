<?php

# Content Editor

class TCEd_base extends TTable {

	var $name = 'ced';
	var $table = 'tree';
	var $selector = true; # show lang selector

	######################

	function TCEd_base() {
		global $str, $actions;

		TTable::TTable();

		$this->window_icons = array();
		if (is_devel()) {
			$this->window_icons['show']['source'] = &$GLOBALS['window_icons']['source'];
		}
		$this->window_icons['show']['help'] = &$GLOBALS['window_icons']['help'];
		$this->window_icons['show']['close'] = &$GLOBALS['window_icons']['close'];

		$actions[$this->name] = array(
			'save' => array(
				'Сохранить',
				'Save',
				'link'	=> 'cnt.document.forms.editform.submit()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'versions' => array(
				'Версии текста',
				'Versions',
				'link'	=> 'cnt.frames[0].versions()',
				'img' 	=> 'icon.versions.gif',
				'display'	=> 'none',
				'show_title' => true,
			),
			'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> 'if (cnt.document.forms.delform.onsubmit()) cnt.document.forms.delform.submit()',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
				'show_title' => true,
			),
			'moveup' => array(
				'Выше',
				'Up',
				'link'	=> 'cnt.frames[\'tmp\'+cnt.TABLE+cnt.ID].location=\'page.php?page='.$this->name.'/\'+cnt.TABLE+\'&do=EditPriority&id[\'+cnt.ID+\']=\'+cnt.ID+\'&move=-1&bars=\'+cnt.document.forms.editform.bars.value',
				'img' 	=> 'icon.moveup.gif',
				'display'	=> 'none',
				'show_title' => true,
			),
			'movedown' => array(
				'Ниже',
				'Down',
				'link'	=> 'cnt.frames[\'tmp\'+cnt.TABLE+cnt.ID].location=\'page.php?page='.$this->name.'/\'+cnt.TABLE+\'&do=EditPriority&id[\'+cnt.ID+\']=\'+cnt.ID+\'&move=1&bars=\'+cnt.document.forms.editform.bars.value',
				'img' 	=> 'icon.movedown.gif',
				'display'	=> 'none',
				'show_title' => true,
			),
			'close' => array(
				'Закрыть',
				'Close',
				'link'	=> 'if (window.top.opener && window.top.opener.focusItem) window.top.opener.focusItem(); window.top.close()',
				'img' 	=> 'icon.close.gif',
				'display'	=> 'block',
				'show_title' => true,
			),
			'add_to_list' => array(
				'Добавить в список',
				'Add To List',
				'link'	=> 'cnt.document.forms.editform.submit()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'none',
				'show_title' => true,
			),
			'changes' => array(
				'Сохранить изменения',
				'Save Changes',
				'link'	=> 'cnt.document.forms.editform.submit()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'none',
				'show_title' => true,
			),
			'add' => array(
				'Добавить',
				'Add',
				'link'	=> 'cnt.editItem()',
				'img' 	=> 'icon.new.gif',
				'display'	=> 'none',
				'show_title' => true,
			),

		);

		# базовые константы

		$str[$this->name] = array(
			'title' => array(
				'Редактирование страницы',
				'Page editing',
			),
			'basic' => array(
				'Основные поля',
				'Basic fields',
			),
			'basic_icon' => 'box.page.gif',
			'basic_caption' => array(
				'Страница',
				'Page',
			),
		);

		# языковые константы
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'name' => array(
				'Заголовок',
				'Title',
			),
			'type' => array(
				'Тип',
				'Type',
			),
			'page' => array(
				'Страница',
				'Page',
			),
			'url' => array(
				'URL',
				'URL',
			),
			'visible' => array(
				'Показывать в меню',
				'Visible in menu',
			),
			'published' => array(
				'Опубликован',
				'Published',
			),
			'restricted' => array(
				'Ограниченный показ',
				'Restricted',
			),
			'saved' => array(
				'Страница успешно сохранена',
				'Page saved successfully',
			),
			'loading' => array(
				'Загрузка...',
				'Loading...',
			),
			'layout'	=> array(
				'Размещение',
				'Layout',
			),
			'l_no_menu_with_banners' => array(
				'Без меню с баннерами',
				'Without menu, with banners',
			),
			'l_no_menu_no_banners' => array(
				'Без меню и баннеров',
				'Without menu and banners',
			),
			'l_txt_menu_with_banners' => array(
				'С текстовым меню и баннерами',
				'With text menu and banners',
			),
			'l_txt_menu_no_banners' => array(
				'С текстовым меню без баннеров',
				'With text menu and no banners',
			),
			'l_g_menu_with_banners' => array(
				'С графическим меню и баннерами',
				'With graphic menu and banners',
			),
			'l_g_menu_no_banners' => array(
				'С графическим меню без баннеров',
				'With graphic menu and no banners',
			),
		));
	}
	
	######################

	function Show() {
		global $directories, $str;

		$id = (int)get('id', 0);
		$row['pid'] = (int)get('pid', 0);
		if ($id) {
			$row = $this->GetRow($id);
            if (defined('LANG_SELECT') && LANG_SELECT) {
                $row['name'] = h($row['name_'.lang()]);
                unset($row['name']);
            } else
                $row['name'] = h($row['name']);
		}

		$row['visible'] = !empty($row['visible']) || !isset($row['visible']) ? 'checked="checked"' : '';
		$row['published'] = !empty($row['published']) ? 'checked="checked"' : '';
		$row['submit'] = $this->str('save');

		$layouts = array();
		if (!empty($directories['layouts']) && is_array($directories['layouts'])) {
			foreach ($directories['layouts'] as $k => $v) {
				$layouts[$k] = $this->str($v);
			}
		}

		if (!empty($row['layout'])) {
			$row['layout'] = $this->GetArrayOptions($layouts, $row['layout'], true);
		}

		//$row['restricted'] = !empty($row['visible']) && $row['visible'] > 1 ? 'checked="checked"' : '';
		//$row['hot'] = !empty($row['hot']) ? 'checked="checked"' : '';
		//$row['STR_HOT'] = $this->str('hot');

		$this->AddStrings($row);
		return Parse($row, 'tree/tree.editform.tmpl');
	}

	######################

	function setRootID($id, $fld) {
		 //$id = get('id','0','p');
		 $root_id = sql_getValue("SELECT root_id FROM tree WHERE id = ".$id);
		 $err = sql_getErrNo(); // проверка на существования поля в таблице( если нет : 1054)
		 if ((!$root_id || $root_id == '0') && !$err){
			// определяем $root_id
			// если root_id нашли у предыдущего
			$home_id = sql_getValue("SELECT root_id FROM tree WHERE id = ".$fld['pid']);
			if ($home_id){
				sql_query("UPDATE tree SET root_id = ".$home_id." WHERE id=".$id);
			} else {
				$pid = $fld['pid'];
				do{
				   $home = sql_getRow("SELECT pid,root_id FROM tree WHERE id = ".$pid);
				   // если все таки не нашли то останавливаемся , когда добежали до корня
				   if ($pid == $home['pid']){
						$home['root_id'] = $pid;
						break;
				   }
				   $pid = $home['pid'];
				}
				while(empty($home['root_id']));
				sql_query("UPDATE tree SET root_id = ".$home['root_id']." WHERE id=".$id);
			}
		 }
	}
	######################

	function Edit() {
		$row = $_POST['fld'];
		$id = (int)get('id', 0, 'p');
		//$enable = "if (window.top.frames.act.frames.cnt.document.forms.editform) window.top.frames.act.frames.cnt.document.forms.editform.subm.disabled=false";
		$enable = "if (window.parent.document.forms.editform) window.parent.document.forms.editform.subm.disabled=false";

		if ($id) {
            if (defined('LANG_SELECT') && LANG_SELECT) {
                $row['name_'.lang()] = $row['name'];
                unset($row['name']);
            }
			$res = $this->Update($id, $row);
			if (is_string($res)) {
				return $this->Error($res);
			}
		}
		else {
			$row['priority'] = sql_getValue("SELECT max(priority)+1 FROM ".$this->table." WHERE pid=".$row['pid']);

            if (!trim($row['name'])) {
                return '<script>alert(\''.sprintf($this->str('e_empty_name'), $this->str('name')).'\');</script>';
            }

            if (defined('LANG_SELECT') && LANG_SELECT) {
                $row['name_'.lang()] = $row['name'];
                unset($row['name']);
            }

			$id = sql_insert($this->table, $row);

			if (!is_int($id) || !$id) {
				return $this->Error($id);
			}

			# присвоение page
			sql_query("UPDATE ".$this->table." SET page=id WHERE id=".$id);

			
			$err = sql_getValue("SELECT root_id FROM tree WHERE id = ".$id);
			$err = sql_getErrNo();
			if (!$err){ //если есть поле root_id
				// выставляем root_id
				$this->setRootID($id, $row);
			}


			$parent = sql_getRow("SELECT pid, next FROM ".$this->table." WHERE id=".$row['pid']);
			if (!$parent['next']) {
				sql_unbuffered_query("UPDATE ".$this->table." SET next=1 WHERE id=".$row['pid']);
				$row['pid'] = $parent['pid']; # если не было "плюса" - обновляем еще на уровень выше
			}
			return '<script>window.parent.reloadNode('.$row['pid'].')</script>';

		}

		return '<script>
		'.$enable.';
		if (window.top.opener && window.top.opener.reloadNode) window.top.opener.reloadNode('.$row['pid'].');
		else if (window.parent.reloadNode) window.parent.reloadNode('.$row['pid'].');
		else alert("reload failed");
		alert("'.$this->str('saved').'");
		</script>';
	}

	######################

	function GetTitle() {
		global $_elems, $_name, $cfg;

		$title = TTable::GetTitle();

		$id = (int)get('id');

        if (defined('LANG_SELECT') && LANG_SELECT)
            $rows = sql_query("SELECT type, IF (name_".lang()." <> '', name_".lang().", name_".LANG_DEFAULT.") as name FROM tree WHERE id=".$id);
        else
            $rows = sql_query("SELECT type, name FROM tree WHERE id=".$id);
        $_elems = array();
		if ($rows) {
			$row = mysql_fetch_row($rows);
			$_elems = !empty($row[0]) && !empty($cfg['types'][$row[0]]['elements']) ? $cfg['types'][$row[0]]['elements'] : $cfg['types']['home']['elements'];
			$_name = $row[1];
			$title.= ' - "'.$row[1].'"';
		}

		return $title;
	}

	######################

	function GetBasicElement($elems) {
		global $cfg, $intlang;
		if (empty($elems)) return;

		# прорисовка дерева елементов
		$tree = '';
		$c = $_bars = $loaded = 0;
		$count = count($elems);
		$pid = get('id'); # tree.id = common elements pid

		$items = array();
		foreach ($elems as $key => $val) {
			$c++;
			$row = array(
				'id' => 0,
				'pid' => $pid,
				'priority' => $c,
				'next' => $cfg['elements'][$val]['next'] ? $val : '',
				'elem' => $val,
				'name' => utf($cfg['elements'][$val][$intlang]),
			);
			$tree .= $this->_get_element_item($row, $bars, $_bars, $c, $count, $loaded)."\n";
			$items[] = $this->_get_element_item_data($row, $bars, $_bars, $c, $count, $loaded);
		}

		$items_data['items'] = &$items;

		$this->AddStrings($items_data);

		$tree = Parse($items_data, 'ced/ced.elements.tmpl');

		return array(
			'STR_BASIC'	=> $GLOBALS['_name'],
			'basic_caption' => str('basic_caption', $this->name),
			'basic_icon' => $GLOBALS['str'][$this->name]['basic_icon'],
			'src' => $GLOBALS['_SERVER']['QUERY_STRING'],
			'tree' => $tree,
		);
	}

	######################

	function _get_element_item_data(&$row, &$bars, &$_bars, &$c, &$count, &$loaded) {

		$item_data = array();

		$loaded = 0;
		$suffix = ($c == $count) ? 'last' : 'whole';
		$_bars = $bars.(($c == $count) ? 's' : 'l');
		//$id = (int)get('id', 0, 'pg');
		//$class = ($id == $row['id']) ? ' class="open"' : '';

		$href = 'cnt.php?page='.$this->name.'/'.$row['elem'].'&id='.$row['id'].'&pid='.$row['pid'].'&bars='.$_bars;
		$plus_href = 'page.php?page='.$this->name.'/'.$row['next'].'&do=load&id='.$row['id'].'&pid='.($row['id'] ? $row['id'] : $row['pid']).'&bars='.$_bars;

		/*$margin = '';
		for ($i = 0; $i < strlen($bars); $i++) {
			$margin .= '<img align="absmiddle" src="images/'.($bars{$i} == 's' ? 's' : 'tree/toc_line').'.gif" width="16" height="16" border="0" hspace="0" vspace="0" alt="" />';
		}*/

		/*$title = strip_tags(h($row['name']));
		if (isset($row['visible']) && $row['visible'] == 0) {
			//$title = '<s>'.$title.'</s>'; // @todo ???
			$title = $title;
		}
		else {
			$title = '<b>'.$title.'</b>';
		}*/

		$item_data = $row;

//		$item_data['target'] = 'tmp'.$this->name;   // нельзя т.е. в шаблоне ifrmae называется TMPCED
		$item_data['target'] = 'tmpced';
		$item_data['href'] = $href;
		$item_data['plus_href'] = $plus_href;
		//$item_data['class'] = $class;
		$item_data['bars'] = $bars;
		$item_data['_bars'] = $_bars;
		$item_data['loaded'] = $loaded;
		$item_data['suffix'] = $suffix;
		//$item_data['margin'] = $margin;
		//$item_data['title'] = $title;
		//$item_data[''] = '';
		$item_data['plus_onclick'] = 'return expandNode(\''.$row['elem'].'\', \''.$row['id'].'\')';
		//$item_data['onclick'] = '';
		$item_data['onactivate'] = 'elemActivate(this)';
		$item_data['ondeactivate'] = 'elemDeactivate(this)';

		/*
		$link = 'cnt.php?page=ced/'.$row['elem'].'&id='.$row['id'].'&pid='.$row['pid'].'&bars='.$_bars;
		if ($row['next']) {
			$icon = '<a href="page.php?page=ced/'.$row['next'].'&do=load&id='.$row['id'].'&pid='.($row['id'] ? $row['id'] : $row['pid']).'&bars='.$_bars.'" target="tmpced" onclick="return expandNode(&quot;'.$row['elem'].'&quot;, &quot;'.$row['id'].'&quot;)"><img id="img_'.$row['elem'].$row['id'].'" align="absmiddle" src="images/tree/toc_'.($loaded ? 'opened' : 'closed').'_'.$suffix.'.gif" width="16" height="16" border="0" hspace="0" vspace="0" alt="" /></a>'.
			"<a$class id='{$row['elem']}{$row['id']}' pid={$row['pid']} elemName='{$row['elem']}' elemId='{$row['id']}' expanded=0 href='$link' target='cnt' onactivate='elemActivate(this)' ondeactivate='elemDeactivate()'>";
		}
		else {
			$icon = "<img align=absmiddle src='images/tree/toc_leaf_$suffix.gif' width=16 height=16 border=0 hspace=0 vspace=0 alt=''>".
			"<a$class id='{$row['elem']}{$row['id']}' pid={$row['pid']} elemName='{$row['elem']}' elemId='{$row['id']}' href='$link' target='cnt' onactivate='elemActivate(this)' ondeactivate='elemDeactivate()'>";
		}

		# отступ элемента
		$margin = '';
		for ($i = 0; $i < strlen($bars); $i++) {
			$margin .= '<img align="absmiddle" src="images/'.($bars{$i} == 's' ? 's' : 'tree/toc_line').'.gif" width="16" height="16" border="0" hspace="0" vspace="0" alt="" />';
		}

		$title = strip_tags(h($row['name']));
		if (isset($row['visible']) && $row['visible'] == 0) {
			//$title = '<s>'.$title.'</s>'; // @todo ???
			$title = $title;
		}
		else {
			$title = '<b>'.$title.'</b>';
		}

		$item = '<div nowrap="nowrap" id="div_'.$row['elem'].$row['id'].'" loaded="'.$loaded.'">'.$margin.$icon.'&nbsp;'.$row['priority'].')&nbsp;'.$title.'</a></div>';
		if ($row['next'] && !$loaded) {
			$item .= '<div id="load_'.$row['elem'].$row['id'].'" class="hide">'.$margin.'<img align="absmiddle" src="images/s.gif" width="16" height="16" border="0" hspace="0" vspace="0" alt="" />&nbsp;<span class="loading">'.$this->str('loading').'</span></div>';
		}
		*/

		return $item_data;
	}

	######################

	function _get_element_item(&$row, &$bars, &$_bars, &$c, &$count, &$loaded) {
		#
		# uses $row[id, name, visible, priority, next, elem, pid]
		#
		$loaded = 0;
		$suffix = ($c == $count) ? 'last' : 'whole';
		$_bars = $bars.(($c == $count) ? 's' : 'l');
		$id = (int)get('id', 0, 'pg');
		$class = ($id == $row['id']) ? ' class="open"' : '';

		$link = 'cnt.php?page='.$this->name.'/'.$row['elem'].'&id='.$row['id'].'&pid='.$row['pid'].'&bars='.$_bars;
		if ($row['next']) {
			$icon = '<a href="page.php?page='.$this->name.'/'.$row['next'].'&do=load&id='.$row['id'].'&pid='.($row['id'] ? $row['id'] : $row['pid']).'&bars='.$_bars.'" target="tmp'.$this->name.'" onclick="return expandNode(&quot;'.$row['elem'].'&quot;, &quot;'.$row['id'].'&quot;)"><img id="img_'.$row['elem'].$row['id'].'" align="absmiddle" src="images/tree/toc_'.($loaded ? 'opened' : 'closed').'_'.$suffix.'.gif" width="16" height="16" border="0" hspace="0" vspace="0" alt="" /></a>'.
			"<a$class id='{$row['elem']}{$row['id']}' pid={$row['pid']} elemName='{$row['elem']}' elemId='{$row['id']}' expanded=0 href='$link' target='cnt' onactivate='elemActivate(this)' ondeactivate='elemDeactivate()'>";
		}
		else {
			$icon = "<img align=absmiddle src='images/tree/toc_leaf_$suffix.gif' width=16 height=16 border=0 hspace=0 vspace=0 alt=''>".
			"<a$class id='{$row['elem']}{$row['id']}' pid={$row['pid']} elemName='{$row['elem']}' elemId='{$row['id']}' href='$link' target='cnt' onactivate='elemActivate(this)' ondeactivate='elemDeactivate()'>";
		}

		# отступ элемента
		$margin = '';
		for ($i = 0; $i < strlen($bars); $i++) {
			$margin .= '<img align="absmiddle" src="images/'.($bars{$i} == 's' ? 's' : 'tree/toc_line').'.gif" width="16" height="16" border="0" hspace="0" vspace="0" alt="" />';
		}

		$title = strip_tags(h($row['name']));
		if (isset($row['visible']) && $row['visible'] == 0) {
			//$title = '<s>'.$title.'</s>'; // @todo ???
			$title = $title;
		}
		else {
			$title = '<b>'.$title.'</b>';
		}

		$item = '<div nowrap="nowrap" id="div_'.$row['elem'].$row['id'].'" loaded="'.$loaded.'">'.$margin.$icon.'&nbsp;'.$row['priority'].')&nbsp;'.$title.'</a></div>';
		if ($row['next'] && !$loaded) {
			$item .= '<div id="load_'.$row['elem'].$row['id'].'" class="hide">'.$margin.'<img align="absmiddle" src="images/s.gif" width="16" height="16" border="0" hspace="0" vspace="0" alt="" />&nbsp;<span class="loading">'.$this->str('loading').'</span></div>';
		}
		return $item;
	}

	######################
}

?>