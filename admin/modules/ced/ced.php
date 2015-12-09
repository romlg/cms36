<?php

# Content Editor
require_once elem_inc('ced/ced_base');

class TCEd extends TCEd_base {

	var $name = 'ced';
	var $table = 'tree';
	var $selector = true; # show lang selector

	######################

	function TCEd() {
		TCEd_base::TCEd_base();
	}
}
$GLOBALS['ced'] = & Registry::get('TCEd');
?>