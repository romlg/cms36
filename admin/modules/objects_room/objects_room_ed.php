<?php

require_once(elem('objects/objects_ed'));

class TObjects_RoomEd extends TObjects_Ed {

	var $name = 'objects_room';
}

Registry::set('object_editor_submodule', Registry::get('TObjects_RoomEd'));

?>