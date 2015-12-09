<?php
# mysql login params

define('MYSQL_HOST', '192.168.1.254');
define('MYSQL_DB', 'locmankvartir');
define('MYSQL_LOGIN', 'local');
define('MYSQL_PASSWORD', 'OtkVZp');

@mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PASSWORD) or die(mysql_error());
@mysql_select_db(MYSQL_DB) or die(mysql_error());
mysql_query('set names cp1251');
?>
