<?php

// имя модуля
$module_name = 'meta';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TMeta', 'modules/'.$module_name.'/meta.class');

?>