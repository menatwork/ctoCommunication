- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************
 
--
-- Table `tl_ctocom_cache`
--

CREATE TABLE `tl_ctocom_cache` (
  `id` int(10) unsigned NOT NULL auto_increment,  
  `uid` varchar(255) NOT NULL default '',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `prime` text NULL,
  `generator` text NULL,
  `public_key` text NULL,
  `private_key` text NULL,
  `shared_secret_key` text NULL,
  PRIMARY KEY  (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;