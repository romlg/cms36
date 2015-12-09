<?php

// имя модуля
$module_name = 'objectsearch';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TObjectSearch', 'modules/'.$module_name.'/objectsearch.class');

?>