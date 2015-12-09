<?php
//------------------------------------------------------------
function cache_save($id, $data, $global = false){
	$cache_obj = & Registry::get('TDataCache');
	return $cache_obj->save($id, $data, $global);
}
//------------------------------------------------------------
function cache_test($id, $global = false){
	$cache_obj = & Registry::get('TDataCache');
	return $cache_obj->test($id, $global);
}
//------------------------------------------------------------
function cache_table_test($id, $tables = array(), $global = false){
	$cache_obj = & Registry::get('TDataCache');
	if (empty($tables)){
		return $cache_obj->test($id, $global);
	}
	return $cache_obj->table_test($id, $tables,$global);
}
//------------------------------------------------------------
function cache_get($id, $global = false){
	$cache_obj = & Registry::get('TDataCache');
	return $cache_obj->get($id, $global);
}
//------------------------------------------------------------
function cache_clear(){
	$cache_obj = & Registry::get('TDataCache');
	return $cache_obj->clear();
}
//------------------------------------------------------------
function cache_remove($id, $global = false){
	$cache_obj = & Registry::get('TDataCache');
	return $cache_obj->_remove($id, $global);
}
//------------------------------------------------------------
function cache_change($id, $data, $global = false){
	$cache_obj = & Registry::get('TDataCache');
	$temp = $cache_obj->get($id, $global);
	if (is_array($temp)) $data = array_merge($temp, $data);
	$cache_obj->save($id, $data, $global);
	return $data;
}
//------------------------------------------------------------

?>