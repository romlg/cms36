<?php

define ('USE_ED_VERSION', '1.0.2');

class TVoting extends TTable {

	var $name = "voting";
	var $table = "voting";
    var $selector = true;

	######################

	function TVoting() {
		global $str, $actions;
		TTable::TTable();
		 // Массив строковых констант
		 $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title'                 => array('Голосование','Voting',),
            'name'                  => array('Название','Product Code',),
            'date'                  => array('Дата создания','Create Date',),
            'description'           => array('Краткое описание','Short Description',),
            'product_type'          => array('Тип продукта','Type',),
            'image'                 => array('Картинка','Image',),
            'visible'               => array('Показывать','Visible', ),
            'open'                  => array('Открыто','Open', ),
            'comment'               => array('Подробное описание','Description',),
            ''                      => array('-','-',),
            'saved'                 => array('Продукт был успешно сохранён','The product has been saved successfully',),
            'delete'                => array('Удалить','Delete', ),
		));
		 // Массив экшенов
		$actions[$this->name] = array(
			'edit' => &$actions['table']['edit'],
			'create' => &$actions['table']['create'],
			'moveup' => &$actions['table']['moveup'],
			'movedown' => &$actions['table']['movedown'],
			'delete' => array(
				'Удалить',
				'Delete',
				'link' => 'cnt.deleteItems(\''.$this->name.'\')',
				'img' => 'icon.delete.gif',
				'display' => 'none',
			),
		);
	}

	function Show() {
        if (!empty($GLOBALS['_POST'])) {
                $actions = get('actions', '', 'p');
                if ($actions) return $this->$actions();
        }
		require_once(core('ajax_table'));

		$ret['thisname'] = $this->name;

		$ret['table'] = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'date',
					'display'	=> 'date',
                    'type'      => 'text',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'name',
					'display'	=> 'name',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'visible',
					'display'	=> 'visible',
					'type'		=> 'visible',
					'align'		=> 'center',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'open',
					'display'	=> 'open',
					'type'		=> 'visible',
					'align'		=> 'center',
					'flags'		=> FLAG_SORT,
				),
			),
			'where'		=> "lang='".lang()."' AND root_id=".domainRootID(),
            'orderby'  => 'priority',
			'params'	=> array('page' => $this->name, 'do' => 'show', 'move' => 0),
			'dblclick'	=> 'editItem(id)',
			'click'		=> 'ID = cb.value',
		), $this);
		return $this->Parse($ret, $this->name.'.tmpl');
	}
}

$GLOBALS['voting'] = & Registry::get('TVoting');

?>