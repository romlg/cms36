<?php

class TAboutBlank extends TTable {

	var $name = 'about_blank';

	########################

	function TAboutBlank() {
		global $str, $actions;
		TTable::TTable();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'restore' => array(
				'Восстановить',
				'Restore',
			),
		));
		$actions[$this->name] = array(
			'delete' => array(
				'Восстановить',
				'Restore',
				'link' => 'if(frames.cnt && frames.cnt.document.forms.restoreform)frames.cnt.document.forms.restoreform.subm.click();window.open(\'act.php?'.base64_decode(get('src', '')).'\', \'act\')',
				'img' => 'icon.close.gif',
				'display' => 'block',
			),
		);
	}

	########################

	function Show() {
		$ret['src'] = base64_decode(get('src', ''));
		$this->AddStrings($ret);
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	########################
}

$GLOBALS['about_blank'] = & Registry::get('TAboutBlank');

?>