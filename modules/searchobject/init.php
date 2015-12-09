<?php

// имя модуля
$module_name = 'searchobject';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TSearchObject', 'modules/'.$module_name.'/searchobject.class');

?>