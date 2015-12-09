<?php
class TVotingEd extends BaseEd {
	var $name     = 'voting';
	var $table    = 'voting';
	var $tabs     = array();
	var $actions  = array();

	############################################
	function TVotingEd() {
		global $str;

		TTable::TTable();
	############### Массив экшенов ################
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
	####### Массив строковых констант ##########
        if (!empty($_GET['id'])){
                $temp = sql_getValue('SELECT name FROM voting WHERE id='.$_GET['id']);
        }
        else { $temp = '';}

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'saved'		    => array('Информация была успешно сохранена','Info has been successfully saved',),
			'basic_caption'	=> array('Голосование','Vote'),
			'basic_tab'	    => array($temp,'Type'),
            'title'         => array($temp,'Type'),
		));
	}

    ####### Возвращает список закладок #########
        function getTabs() {
            $id = get('id', 0, 'gp');

            if (!$id) {
                    return $this->tabs;
            }
            $row = $this->getRow($id);
            $elems = array('elem_variants');

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
Registry::set('object_editor_submodule', Registry::get('TVotingEd'));
?>