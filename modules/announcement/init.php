<?php

// имя модуля
$module_name = 'announcement';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TAnnouncement', 'modules/'.$module_name.'/announcement.class');

?>