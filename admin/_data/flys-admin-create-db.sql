/*
SQLyog Community Edition- MySQL GUI v6.5 Beta1
MySQL - 5.0.27-community-nt-log : Database - flys
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

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
  `sort` int(2) default '0',
  `project` varchar(20) default NULL COMMENT '项目名称',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=104 DEFAULT CHARSET=utf8 COMMENT='系统权限表配置表';

/*Data for the table `admin_rights` */

insert  into `admin_rights`(`id`,`name`,`url`,`comment`,`available`,`code`,`father_code`,`sort`,`project`) values (1,'权限管理','','',1,1,0,0,'admin'),(2,'角色管理','admin.php?m=roleList&a=show_role','',1,0,1,0,'admin'),(3,'功能角色管理','admin.php?m=roleRights&a=show','',1,0,1,1,'admin'),(4,'用户角色管理','admin.php?m=userRole&a=showUserRole','',1,0,1,3,'admin'),(5,'菜单导出','admin.php?m=rights&a=export','',1,0,1,4,'admin'),(6,'sql脚本运行','admin.php?m=rights&a=execute','',1,0,1,10,'admin'),(7,'管理员登录日志','admin.php?m=log&a=show','',1,0,1,11,'admin');

/*Table structure for table `admin_role` */

DROP TABLE IF EXISTS `admin_role`;

CREATE TABLE `admin_role` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  `comment` varchar(255) default NULL,
  `project` varchar(20) default 'admin' COMMENT '项目名称',
  `available` tinyint(1) default '1' COMMENT '是否有效:1 or 0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='用户角色表';

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
) ENGINE=MyISAM AUTO_INCREMENT=276 DEFAULT CHARSET=utf8 COMMENT='角色权限表';

/*Data for the table `admin_role_rights` */

insert  into `admin_role_rights`(`id`,`role_id`,`rights_id`,`can_add`,`can_delete`,`can_edit`,`can_view`) values (269,1,1,1,0,1,1),(270,1,2,1,0,1,1),(271,1,3,1,0,1,1),(272,1,4,1,0,1,1),(273,1,5,1,0,1,1),(274,1,6,1,0,1,1),(275,1,7,1,0,1,1);

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
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COMMENT='用户表';

/*Data for the table `admin_user` */

insert  into `admin_user`(`id`,`name`,`login_name`,`login_passwd`,`email`,`cell_phone`,`comment`,`ip`) values (1,'admin','admin','2d14f0a7ff531d44baa35a0f98ea9f39','hfvyu','','12378','*');

/*Table structure for table `admin_user_role` */

DROP TABLE IF EXISTS `admin_user_role`;

CREATE TABLE `admin_user_role` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `role_id` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8 COMMENT='用户与角色关联表';

/*Data for the table `admin_user_role` */

insert  into `admin_user_role`(`id`,`user_id`,`role_id`) values (1,1,1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
