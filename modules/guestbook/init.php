<?php
// имя модуля
$module_name = 'guestbook';

$module_loader = &TModuleLoader::create();
$module_loader->registerClassFile('TGuestbook', 'modules/'.$module_name.'/guestbook.class');

?>