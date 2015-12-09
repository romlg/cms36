<?php
###############################################
require_once elem('tree/tree_base');
###############################################
class TTreeEd extends TTreeBase {
	var $tabs = array();
	############################################
	function TTreeEd() {
		parent::TTreeBase();
	}

	############################################
	// Пишет Заголовок
	function GetTitle() {
		$id = get('id', 0, 'gp');

		if (!$id) {
			return $this->str('edit');
		}
		$row = $this->getRow($id);
        if (defined('LANG_SELECT') && LANG_SELECT)
            $row['name'] = $row['name_'.lang()] ? $row['name_'.lang()] : $row['name_'.LANG_DEFAULT];

		$GLOBALS['str'][get_class_name($this)]['basic_tab'][int_langID()] = $row['name'];
		$GLOBALS['str'][get_class_name($this)]['basic_caption'][int_langID()] = $GLOBALS['cfg']['types'][$row['root_id']][$row['type']][int_langID()];
		return $this->str('edit').' - "'.$row['name'].'"';
	}
    
	####### Возвращает список закладок #########
	function getTabs() {
		$id = get('id', 0, 'gp');
		if (!$id) {
			return $this->tabs;
		}
		$row = $this->getRow($id);

		$elems = $GLOBALS['cfg']['types'][$row['root_id']][$row['type']]['elements'];
		if($row['type'] == 'module'){
			$module_name = sql_getValue("SELECT module FROM elem_module WHERE pid=".$id);
			if (isset($GLOBALS['cfg']['function_modules'][$row['root_id']][$module_name]['elements'])){
				$elemsmy = $GLOBALS['cfg']['function_modules'][$row['root_id']][$module_name]['elements'];
				if (!empty($elemsmy)){
					$elems = $elemsmy;
				}
			}
		}

		foreach ($elems as $k => $v) {
			$this->tabs['tab'.$k] = array(
				'display' => array( // elements conf
					'ru' => $GLOBALS['cfg']['elements'][$v][0],
					'en' => $GLOBALS['cfg']['elements'][$v][1],
				),
				'type' => 'elem',
				'conf' => array(
					'elem' => $v,
					'next' => $GLOBALS['cfg']['elements'][$v]['next'], // elements conf
					'target' => 'cnt', // cnt | act
				),
			);
		}
		return $this->tabs;
	}
	################################################
}

 Registry::set('object_editor_submodule', Registry::get('TTreeEd'));

?>