<?php
class TGuestbookEd extends TWorkingCopyBase {
	var $name     = 'guestbook';
	var $table    = 'guestbook';
	var $tabs     = array();
	var $actions  = array();

	############################################
	function TGuestbookEd() {
		global $str;

		TTable::TTable();

	####### Массив строковых констант ##########
        if (!empty($_GET['id'])){
                $temp = $this->getValue('SELECT title FROM guestbook WHERE id='.$_GET['id']);
        }
        else { $temp = '';}
		$str[get_class($this)] = array(
			'saved'		    => array('Информация была успешно сохранена','Info has been successfully saved',),
			'basic_caption'	=> array('Сообщение','Message'),
			'basic_tab'	    => array($temp,'Message'),
            'title'         => array($temp,'Message'),
		);
	}

    ####### Возвращает список закладок #########
        function getTabs() {
            return array();
        }
}
$GLOBALS['object_editor_submodule'] = &new TGuestbookEd();

?>