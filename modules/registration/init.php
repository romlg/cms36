<?php

$module_name = 'registration';

$module_loader = & registry::get('TModuleLoader');
$module_loader->registerClassFile('TRegistration', 'modules/'.$module_name.'/registration.class');

?>