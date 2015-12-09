<?php

// имя модуля
$module_name = 'site';

require_once (base('modules/'.$module_name.'/tree_utils.class'));
require_once (base('modules/'.$module_name.'/main.class'));
require_once (base('modules/'.$module_name.'/page.class'));

$module_loader = & Registry::get('TModuleLoader');
$module_loader->registerClassFile('TMenu', 'modules/'.$module_name.'/menu');
$module_loader->registerClassFile('TUserAuth', 'modules/'.$module_name.'/user_auth.class');

?>