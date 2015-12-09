<?php

// имя модуля
$module_name = 'searchrntobject';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TSearchRntObject', 'modules/'.$module_name.'/searchrntobject.class');

?>