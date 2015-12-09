<?php

// имя модуля
$module_name = 'rntannouncement';

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TRntAnnouncement', 'modules/'.$module_name.'/rntannouncement.class');

?>