<?php

$module_name = 'cabinet';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TCabinet', 'modules/'.$module_name.'/cabinet.class');
$module_loader->registerClassFile('TBills', 'modules/'.$module_name.'/bills.class');

?>