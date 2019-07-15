/*
SQLyog Community Edition- MySQL GUI v6.5 Beta1
MySQL - 5.0.18-log : Database - admin
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

create database if not exists `admin`;

USE `admin`;

/*Table structure for table `news` */

DROP TABLE IF EXISTS `news`;

CREATE TABLE `news` (
  `id` int(11) NOT NULL auto_increment,
  `type` tinyint(2) default '0' COMMENT 'the type of the news',
  `title` varchar(128) default NULL,
  `times` datetime default NULL COMMENT 'the time show in the news.',
  `add_time` datetime default NULL,
  `author` varchar(32) default NULL COMMENT 'the author of the news',
  `source` varchar(32) default NULL COMMENT 'where the news from ',
  `outline` text COMMENT 'out line of the news.',
  `context` text,
  `page_path` varchar(64) default NULL,
  `page_file` varchar(32) default NULL,
  `is_check` tinyint(1) default '0',
  `checker` varchar(64) default NULL,
  `is_public` tinyint(1) default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='the news data.';

/*Data for the table `news` */

insert  into `news`(`id`,`type`,`title`,`times`,`add_time`,`author`,`source`,`outline`,`context`,`page_path`,`page_file`,`is_check`,`checker`,`is_public`) values (3,2,'2343242sadfasfasf','2008-09-09 00:00:00','2008-11-15 13:09:31','asdfas','新郎科技','sadfdfsadfasf','<h1 align=\\\"left\\\">asdfasf</h1>\r\n<div align=\\\"left\\\"><font face=\\\"KaiTi_GB2312\\\">大家好，<strong>武林帝国</strong> <font color=\\\"#ffd700\\\">webgame<img height=\\\"19\\\" alt=\\\"\\\" src=\\\"http://localhost/50dg/_extends/KindEditor/icons/etc_05.gif\\\" width=\\\"19\\\" border=\\\"0\\\" /></font></font></div>\r\n<div align=\\\"left\\\"><font color=\\\"#ffd700\\\"></font>&nbsp;</div>\r\n<div align=\\\"left\\\"><img alt=\\\"\\\" src=\\\"http://localhost/50dg/admin/image/news/200811151226725750281.jpg\\\" border=\\\"0\\\" /></div>\r\n<div align=\\\"left\\\">&nbsp;</div>\r\n<div align=\\\"left\\\">&nbsp;</div>\r\n<div align=\\\"left\\\"><font color=\\\"#ffd700\\\"></font>&nbsp;</div>\r\n<div align=\\\"left\\\"><font color=\\\"#ffd700\\\"></font>&nbsp;</div>','','',0,'阿阿打',1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
