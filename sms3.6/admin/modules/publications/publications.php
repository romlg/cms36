<?php

require_once 'sms3.6/admin/modules/publications/publications_base.php';

/**
 * Модуль публикаций
 */
class TPublications extends TPublicationsBase
{
}

$GLOBALS['publications'] = & Registry::get('TPublications');

?>