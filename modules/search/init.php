<?php

// имя модуля
$module_name = 'search';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TSearch', 'modules/'.$module_name.'/search.class');

?>