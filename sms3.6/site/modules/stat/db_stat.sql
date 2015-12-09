-- phpMyAdmin SQL Dump
-- version 2.6.1-rc2
-- http://www.phpmyadmin.net
-- 
-- Host: 192.168.1.254
-- Generation Time: Apr 06, 2005 at 07:28 PM
-- Server version: 4.0.22
-- PHP Version: 4.3.10
-- 
-- Database: `rusoft-stat`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `stat_agents`
-- 

CREATE TABLE `stat_agents` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `agent` varchar(80) NOT NULL default '',
  `name` varchar(40) NOT NULL default '',
  `os` varchar(30) NOT NULL default '',
  `robot` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `agent` (`agent`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `stat_agents`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `stat_log`
-- 

CREATE TABLE `stat_log` (
  `sess_id` int(10) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `ref_id` mediumint(8) unsigned NOT NULL default '0',
  `page_id` mediumint(8) unsigned NOT NULL default '0',
  `status` smallint(5) unsigned NOT NULL default '0',
  KEY `sess_id` (`sess_id`),
  KEY `page_id` (`page_id`),
  KEY `status` (`status`),
  KEY `time` (`time`,`status`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `stat_log`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `stat_pages`
-- 

CREATE TABLE `stat_pages` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `host` varchar(30) NOT NULL default '',
  `uri` varchar(255) NOT NULL default '',
  `search_ph` varchar(150) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `host_uri` (`host`,`uri`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `stat_pages`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `stat_sessions`
-- 

CREATE TABLE `stat_sessions` (
  `sess_id` int(10) unsigned NOT NULL auto_increment,
  `ip` int(11) NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `time_last` int(10) unsigned NOT NULL default '0',
  `loads` smallint(5) unsigned NOT NULL default '0',
  `client_id` int(10) unsigned NOT NULL default '0',
  `ref_id` mediumint(8) unsigned NOT NULL default '0',
  `first_page` mediumint(8) unsigned NOT NULL default '0',
  `last_page` mediumint(8) unsigned NOT NULL default '0',
  `agent_id` mediumint(8) unsigned NOT NULL default '0',
  `new_visitor` tinyint(1) unsigned NOT NULL default '0',
  `robot` tinyint(1) unsigned NOT NULL default '0',
  `country` char(2) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`sess_id`),
  KEY `ip` (`ip`,`agent_id`),
  KEY `time` (`time`,`robot`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stat_sessions`
--


-- --------------------------------------------------------

--
-- Table structure for table `stat_settings`
--

CREATE TABLE `stat_settings` (
  `name` varchar(20) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`name`)
) TYPE=MyISAM;


