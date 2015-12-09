<?php

require_once(elem('objects/objects_ed'));

class TObjects_NewbuildEd extends TObjects_Ed {

	var $name = 'objects_newbuild';
}

Registry::set('object_editor_submodule', Registry::get('TObjects_NewbuildEd'));

?>