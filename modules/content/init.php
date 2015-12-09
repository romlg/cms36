<?php
// имя модуля
$module_name = 'content';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TContent', 'modules/'.$module_name.'/content.class');

?>