-- 
-- ��������� ������� `voting`
-- 

CREATE TABLE `voting` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `visible` tinyint(1) unsigned NOT NULL default '0',
  `open` tinyint(1) NOT NULL default '1',
  `type` enum('radio','checkbox') NOT NULL default 'radio',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(255) NOT NULL default '',
  `ipcheck` enum( 'none', 'check', 'cookie' ) NOT NULL default 'none',
  `hosts` text,
  `lang` enum('ru','en') NOT NULL default 'ru',
  `priority` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) TYPE=InnoDB AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

-- 
-- ��������� ������� `voting_answers`
-- 

CREATE TABLE `voting_answers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `count` int(11) NOT NULL default '0',
  `priority` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) TYPE=InnoDB COMMENT='�������� ������� �����������' AUTO_INCREMENT=11 ;

-- 
-- Constraints for dumped tables
-- 

-- 
-- Constraints for table `voting_answers`
-- 
ALTER TABLE `voting_answers`
  ADD CONSTRAINT `voting_answers_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `voting` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

  
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'current', 'ru', '������� �����������', '������� �����������');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'by', 'ru', '����������� ��', '����������� ��');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'ppl', 'ru', '����� �������', '����� �������');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'list', 'ru', '������ �����������', '������ �����������');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'limit', 'ru', '1', '1');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'results', 'ru', '���������� �����������', '���������� �����������');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'title', 'ru', '�����������', '�����������');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'see_results', 'ru', '&raquo;&raquo;&nbsp;��. ����������', '&raquo;&raquo;&nbsp;��. ����������');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'title', 'en', 'Voting', 'Voting');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'current', 'en', 'Current vote', 'Current vote');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'give_answer', 'ru', '�������������', '�������������');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'give_answer', 'en', 'Give answer', 'Give answer');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'home_results', 'ru', '����������', '����������');
INSERT INTO `strings` (   `module`, `name`, `lang`, `value`, `def`  ) VALUES ( 'voting', 'home_results', 'en', 'Results', 'Results');
