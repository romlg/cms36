<?php

class Registry {
	
	function &get($classname = ''){
		$classname = strtolower($classname);
		global $_objects;
		
		if (!isset($_objects[$classname])){
			if (class_exists($classname)){
				$_objects[$classname] = &new $classname();	
			} else {
				echo "<b>не могу найти класс ".$classname."</b>";
				die();
			}
		}
		return $_objects[$classname];		
		
	}
	
	//--------------------------------------------------------------------
	
	function set($classname, &$object){	
		$classname = strtolower($classname);
		$GLOBALS['_objects'][$classname] = $object;		
	}
	
	//--------------------------------------------------------------------

}

?>