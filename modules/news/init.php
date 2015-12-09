<?php
// имя модуля
$module_name = 'news';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TNews', 'modules/'.$module_name.'/news.class');

?>