<?php

require_once(elem('objects/objects_ed'));

class TObjects_CommerceEd extends TObjects_Ed {

	var $name = 'objects_commerce';
}

Registry::set('object_editor_submodule', Registry::get('TObjects_CommerceEd'));

?>