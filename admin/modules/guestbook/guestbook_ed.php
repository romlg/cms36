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

	####### ������ ��������� �������� ##########
        if (!empty($_GET['id'])){
                $temp = $this->getValue('SELECT title FROM guestbook WHERE id='.$_GET['id']);
        }
        else { $temp = '';}
		$str[get_class($this)] = array(
			'saved'		    => array('���������� ���� ������� ���������','Info has been successfully saved',),
			'basic_caption'	=> array('���������','Message'),
			'basic_tab'	    => array($temp,'Message'),
            'title'         => array($temp,'Message'),
		);
	}

    ####### ���������� ������ �������� #########
        function getTabs() {
            return array();
        }
}
$GLOBALS['object_editor_submodule'] = &new TGuestbookEd();

?>