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

	####### ������ ��������� �������� ##########
        if (!empty($_GET['id'])){
                $temp = sql_getValue('SELECT name FROM orders WHERE id='.$_GET['id']);
        }
        else { $temp = '';}
		$str[get_class($this)] = array(
			'saved'		    => array('���������� ���� ������� ���������','Info has been successfully saved',),
			'basic_caption'	=> array('������','Order'),
			'basic_tab'	    => array($temp,'Order'),
            'title'         => array($temp,'Order'),
		);
	}

    ####### ���������� ������ �������� #########
        function getTabs() {
            return array();
        }
}
Registry::set('object_editor_submodule', Registry::get('TOrdersEd'));
?>