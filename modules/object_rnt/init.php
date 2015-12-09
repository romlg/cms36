<?php
// имя модуля
$module_name = 'rnt_object';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TRntObject', 'modules/'.$module_name.'/rnt_object.class');

?>