<?php

class TStatReklama_ObjEd extends BaseEd {
	##############################################
	var $name     = 'stat/reklama_obj';
	var $table    = 'stat_reklama';
	var $tabs     = array();
	var $actions  = array();
	##############################################
	function TStatReklama_ObjEd() {
		global $str;

		TTable::TTable();

        if (!empty($_GET['id'])){
            $temp = sql_getValue('SELECT name FROM stat_reklama WHERE id='.$_GET['id']);
        }
        else { $temp = '';}

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'saved'		=> array('Информация была успешно сохранена','Info has been successfully saved',),
			'basic_caption'	=> array('Рекламная кампания','Advertising campaign'),
			'basic_tab'	=> array($temp,'Advertising campaign'),
             'title'         => array($temp,'Advertising campaign'),
		));

        $this->actions = array(
            'add' => array(
                'title' => array(
                    'ru' => 'Добавить',
                    'en' => 'Add new',
                ),
                'onclick'    => 'cnt.editElem(0)',
                'img'        => 'icon.create.gif',
                'display'    => 'none',
                'show_title' => true,
            ),
            'edit' => array(
                'title' => array(
                    'ru' => 'Изменить',
                    'en' => 'Edit',
                ),
                'onclick'    => 'cnt.editElem()',
                'img'        => 'icon.edit.gif',
                'display'    => 'none',
                'show_title' => true,
            ),
            'delete' => array(
                'title' => array(
                    'ru' => 'Удалить',
                    'en' => 'Delete',
                ),
                'onclick'    => 'cnt.deleteElems(\''.$this->name.'\')',
                'img'        => 'icon.delete.gif',
                'display'    => 'none',
                'show_title' => true,
            ),
            'moveup' => array(
                'title' => array(
                    'ru' => 'Выше',
                    'en' => 'Up',
                ),
                'onclick'    => 'cnt.swapElems(-1)',
                'img'        => 'icon.moveup.gif',
                'display'    => 'none',
                'show_title' => true,
            ),
            'movedown' => array(
                'title' => array(
                    'ru' => 'Ниже',
                    'en' => 'Down',
                ),
                'onclick'    => 'cnt.swapElems(1)',
                'img'        => 'icon.movedown.gif',
                'display'    => 'none',
                'show_title' => true,
            ),
        );
	}
	####### Возвращает список закладок #########
	function getTabs() {
        return array();
/*        $id = get('id', 0, 'gp');

        if (!$id) {
                return $this->tabs;
        }
        //$row = sql_getRow($id);

        $this->tabs['tab0'] = array(
                'display' => array(
                        'ru' => 'Идентификаторы',
                        'en' => 'Identifiers',
                ),
                'type' => 'elem',
                'conf' => array(
                        'elem' => 'elem_identifiers',
                        'next' => 1,
                        'target' => 'cnt',
                ),
        );

        return $this->tabs; */
	}

	##############################################
}
Registry::set('object_editor_submodule', Registry::get('TStatReklama_ObjEd'));
?>