<?php
// имя модуля
$module_name = 'voting';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TVoting', 'modules/'.$module_name.'/voting.class');

?>