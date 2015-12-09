<?php

require_once(elem('objects/objects_ed'));

class TObjects_HouseEd extends TObjects_Ed {

	var $name = 'objects_house';
}

Registry::set('object_editor_submodule', Registry::get('TObjects_HouseEd'));

?>