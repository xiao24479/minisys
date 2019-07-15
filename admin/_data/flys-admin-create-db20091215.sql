/*
SQLyog Community Edition- MySQL GUI v6.5 Beta1
MySQL - 5.0.27-community-nt-log : Database - flys
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*Table structure for table `admin_help` */

DROP TABLE IF EXISTS `admin_help`;

CREATE TABLE `admin_help` (
  `id` int(11) NOT NULL auto_increment COMMENT 'id',
  `key_word` varchar(255) default NULL COMMENT '关键词',
  `comments` text COMMENT '说明内容',
  `available` tinyint(1) default '1' COMMENT '0无效，1有效',
  PRIMARY KEY  (`id`),
  KEY `keyword` (`key_word`),
  KEY `available` (`available`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `admin_help` */

/*Table structure for table `admin_login_log` */

DROP TABLE IF EXISTS `admin_login_log`;

CREATE TABLE `admin_login_log` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `login_name` varchar(20) default NULL,
  `login_datetime` datetime default NULL,
  `logout_datetime` datetime default NULL,
  `ip` varchar(15) default NULL,
  `session_id` varchar(256) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='管理员登陆日志';

/*Data for the table `admin_login_log` */

insert  into `admin_login_log`(`id`,`user_id`,`login_name`,`login_datetime`,`logout_datetime`,`ip`,`session_id`) values (1,1,'admin','2009-12-04 11:25:38','0000-00-00 00:00:00','127.0.0.1','ff74708bee6917f3825f168a98b3ba1d'),(2,57,'test','2009-12-04 14:12:32','0000-00-00 00:00:00','127.0.0.1','ee132ac15aa1f75658a17290f33085db'),(3,1,'admin','2009-12-07 15:16:03','0000-00-00 00:00:00','127.0.0.1','767c87e90eee7a44114bf9d822397b56'),(4,1,'admin','2009-12-08 11:45:44','0000-00-00 00:00:00','127.0.0.1','6265a32ba50f31148de133fdccebdb1a');

/*Table structure for table `admin_rights` */

DROP TABLE IF EXISTS `admin_rights`;

CREATE TABLE `admin_rights` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  `url` varchar(255) default NULL COMMENT '连接的url',
  `comment` varchar(255) default NULL,
  `available` tinyint(1) default '1',
  `code` int(5) default '0' COMMENT '编码,非为第一级别',
  `father_code` int(5) default '0' COMMENT '上一级别的code,0为第一级别',
  `sort` decimal(2,1) default '0.0',
  `project` varchar(20) default NULL COMMENT '项目名称',
  `is_show` tinyint(1) default '1' COMMENT '是否显示菜单,0不显示，1显示',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=107 DEFAULT CHARSET=utf8 COMMENT='系统权限表配置表';

/*Data for the table `admin_rights` */

insert  into `admin_rights`(`id`,`name`,`url`,`comment`,`available`,`code`,`father_code`,`sort`,`project`,`is_show`) values (1,'权限管理','','',1,1,0,'1.0','admin',1),(2,'角色管理','main.php?m=roleList&a=show_role','',1,0,1,'0.0','admin',1),(3,'功能角色','main.php?m=roleRights&a=show','',1,0,1,'0.0','admin',1),(4,'用户角色','main.php?m=userRole&a=showUserRole','',1,0,1,'0.0','admin',1),(5,'菜单导出','main.php?m=rights&a=export','',1,0,1,'0.0','admin',1),(6,'sql脚本运行','main.php?m=rights&a=execute','',1,0,1,'0.0','admin',1),(7,'管理员登录日志','main.php?m=log&a=show','',1,0,1,'0.0','admin',1),(105,'demo','','',0,91607,0,'0.0','demo',0),(106,'test','../demo/main.php/test/abc.html','',1,0,91607,'0.0','demo',0);

/*Table structure for table `admin_role` */

DROP TABLE IF EXISTS `admin_role`;

CREATE TABLE `admin_role` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  `comment` varchar(255) default NULL,
  `project` varchar(20) default 'admin' COMMENT '项目名称',
  `available` tinyint(1) default '1' COMMENT '是否有效:1 or 0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='用户角色表';

/*Data for the table `admin_role` */

insert  into `admin_role`(`id`,`name`,`comment`,`project`,`available`) values (1,'超级用户','系统超级用户','admin',1);

/*Table structure for table `admin_role_rights` */

DROP TABLE IF EXISTS `admin_role_rights`;

CREATE TABLE `admin_role_rights` (
  `id` int(11) NOT NULL auto_increment,
  `role_id` int(11) default NULL,
  `rights_id` int(11) default NULL,
  `can_add` tinyint(1) default '1' COMMENT '添加的权限',
  `can_delete` tinyint(1) default '1' COMMENT '删除的权限',
  `can_edit` tinyint(1) default '1' COMMENT '编辑的权限',
  `can_view` tinyint(1) default '1' COMMENT '查看的权限',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=281 DEFAULT CHARSET=utf8 COMMENT='角色权限表';

/*Data for the table `admin_role_rights` */

insert  into `admin_role_rights`(`id`,`role_id`,`rights_id`,`can_add`,`can_delete`,`can_edit`,`can_view`) values (269,1,1,1,0,1,1),(270,1,2,1,0,1,1),(271,1,3,1,0,1,1),(272,1,4,1,0,1,1),(273,1,5,1,0,1,1),(274,1,6,1,0,1,1),(275,1,7,1,0,1,1),(280,1,106,1,1,1,1),(279,1,105,1,1,1,1);

/*Table structure for table `admin_user` */

DROP TABLE IF EXISTS `admin_user`;

CREATE TABLE `admin_user` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  `login_name` varchar(50) default NULL,
  `login_passwd` varchar(255) default NULL COMMENT 'md5后的密码',
  `email` varchar(255) default NULL,
  `cell_phone` varchar(20) default NULL,
  `comment` varchar(255) default NULL,
  `ip` varchar(256) default '*',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COMMENT='用户表';

/*Data for the table `admin_user` */

insert  into `admin_user`(`id`,`name`,`login_name`,`login_passwd`,`email`,`cell_phone`,`comment`,`ip`) values (1,'admin','admin','098f6bcd4621d373cade4e832627b4f6','hfvyu','','12378','*');

/*Table structure for table `admin_user_role` */

DROP TABLE IF EXISTS `admin_user_role`;

CREATE TABLE `admin_user_role` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `role_id` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COMMENT='用户与角色关联表';

/*Data for the table `admin_user_role` */

insert  into `admin_user_role`(`id`,`user_id`,`role_id`) values (1,1,1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
