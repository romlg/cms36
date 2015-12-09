<?php
class TOrdersEd extends BaseEd {
	var $name     = 'orders';
	var $table    = 'orders';
	var $tabs     = array();
	var $actions  = array();

	############################################
	function TOrdersEd() {
		global $str;

		TTable::TTable();

	####### Массив строковых констант ##########
        if (!empty($_GET['id'])){
                $temp = sql_getValue('SELECT name FROM orders WHERE id='.$_GET['id']);
        }
        else { $temp = '';}
		$str[get_class($this)] = array(
			'saved'		    => array('Информация была успешно сохранена','Info has been successfully saved',),
			'basic_caption'	=> array('Заявки','Order'),
			'basic_tab'	    => array($temp,'Order'),
            'title'         => array($temp,'Order'),
		);
	}

    ####### Возвращает список закладок #########
        function getTabs() {
            return array();
        }
}
Registry::set('object_editor_submodule', Registry::get('TOrdersEd'));
?>