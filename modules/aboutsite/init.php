<?php

// имя модуля
$module_name = 'aboutsite';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TAboutSite', 'modules/'.$module_name.'/aboutsite.class');

?>