DROP TABLE IF EXISTS `auth_users`;
CREATE TABLE `auth_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `login` varchar(32) NOT NULL default '',
  `reg_date` timestamp(14) NOT NULL,
  `enable` tinyint(1) NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `lname` varchar(255) NOT NULL default '',
  `addr` varchar(255) NOT NULL default '',
  `email` varchar(64) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `cell_phone` varchar(255) NOT NULL default '',
  `phone` varchar(255) NOT NULL default '',
  `comp_name` varchar(100) NOT NULL default '',
  `chpass_hash` varchar(32) NOT NULL default '',
  `chpass_hash_date` int(11) unsigned NOT NULL default '0',
  `subscribe` tinyint(1) unsigned NOT NULL default '1',
  `ban` char(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`)
) TYPE=InnoDB;

CREATE TABLE `stat_banlist` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ip` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ip` (`ip`)
) TYPE=MyISAM;

