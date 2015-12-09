<?php

// имя модуля
$module_name = 'banners';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TBanners', 'modules/'.$module_name.'/banners.class');

?>