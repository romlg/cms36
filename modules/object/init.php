<?php
// имя модуля
$module_name = 'object';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TObject', 'modules/'.$module_name.'/object.class');

?>