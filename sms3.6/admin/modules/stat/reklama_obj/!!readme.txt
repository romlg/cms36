#################################################
1. ������� �������
#################################################

CREATE TABLE `stat_reklama` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `budget` double(9,2) NOT NULL default '0.00',
  `displays_count` int(10) unsigned NOT NULL default '0',
  `click_count` int(10) unsigned NOT NULL default '0',
  `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `identifiers` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB PACK_KEYS=0;


#################################################
2. � admin.cfg.php �������� � ������ $_stat
#################################################

	'stat/stat_reklama' => array(
		'������ ��������� ��������',
		'Advertising campaign analyze',
		'img' => 'icon.box.gif',
	),

#################################################
3. � admin.cfg.php ���������� ��������� STAT_REKLAMA_REPORT
#################################################

define('STAT_REKLAMA_REPORT', true);


#################################################
4. � admin.cfg.php �������� � ����� �����
#################################################

# unset reklama report
if(!defined('STAT_REKLAMA_REPORT') || STAT_REKLAMA_REPORT==false) {
	unset($_stat['stat/stat_reklama']);
}



