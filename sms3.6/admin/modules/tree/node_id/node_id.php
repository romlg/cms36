<?php

require_once (module('tree'));

class TNodeId extends TTree {
	var $value;

	var $name = 'tree/node_id';

	function TNodeId(){
		TTree::TTree();
		global $str;
		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title' => array(
				'Выберите раздел',
				'Select page',
			),
			'not_selected' => array(
				'Не выбран раздел',
				'Node is not selected',
			),
		));

		//$this->hideInvisible = true;
	}

	function Show() {
		$ret['field_name'] = get('name', '');
		$ret['sid'] = 0;

		$this->sid = 0;
		$this->AddStrings($ret);
		$ret['tree'] = $this->GetTree();
		$ret['STR_LOADING'] = $this->str('loading');
		return Parse($ret, $this->name.'/tree.node_id.tmpl');
	}

	function _get_item_data(&$row, &$bars, &$_bars, &$c, &$count, &$loaded) {
		$c++;
		//$loaded = is_array($pids) && in_array($row['id'], $pids) ? 1 : 0;
		$suffix = ($c == $count) ? 'last' : 'whole';
		$_bars = $bars.(($c == $count) ? 's' : 'l');
		$type_icon = isset($GLOBALS['cfg']['types'][$row['root_id']][$row['type']]['icon']) ? $GLOBALS['cfg']['types'][$row['root_id']][$row['type']]['icon'] : 'icon.tree.gif';

		$item_data = $row;

		// Если это рутовый узел, то название показываем так: "имя сайта - имя страницы"
		if ($item_data['id'] == $item_data['pid'])
			$item_data['name'] = getSiteByRootID($item_data['root_id'])." - ".$item_data['name'];

			$item_data['icon'] = $type_icon;
		$item_data['_bars'] = $_bars;
		$item_data['loaded'] = $loaded;
		$item_data['suffix'] = $suffix;

		$item_data['target'] = 'tree_toc';
		$item_data['plus_onclick'] = 'event.cancelBubble = true;return expandNode('.$row['id'].', true);';
		$item_data['onclick'] = 'event.cancelBubble = true;doSelect('.$row['id'].'); return expandNode('.$row['id'].')';
		$item_data['ondblclick'] = 'selectID('.$row['id'].')';
		$item_data['onactivate'] = 'itemActivate(this)';
		$item_data['onfocus'] = 'itemActivate(this)';
		$item_data['ondeactivate'] = 'itemDeactivate(this)';
		$item_data['ondragstart'] = '';
		$item_data['ondragenter'] = '';
		$item_data['ondragleave'] = '';
		$item_data['ondragover'] = '';
		$item_data['ondrop'] = '';
		$item_data['href'] = 'page.php?page=tree/node_id&do=getnode&id='.$row['id'].'&bars='.$_bars.'&value='.$this->value;

		$item_data['bars'] = preg_split('//', $bars, -1, PREG_SPLIT_NO_EMPTY);

		return $item_data;
	}
}

$GLOBALS['tree__node_id'] =  & Registry::get('TNodeId');

?>