#2012.11.09


insert into `admin_rights` (`name`, `url`, `comment`, `available`, `code`, `father_code`, `sort`, `project`, `is_show`) values('短消息管理','main.php?m=admin&a=messageList','','1','0','1','10','admin','1');
insert into `admin_rights` (`name`, `url`, `comment`, `available`, `code`, `father_code`, `sort`, `project`, `is_show`) values('发短消息','main.php?m=admin&a=sendMess','','1','0','1','9','admin','1');



DROP TABLE IF EXISTS `irpt_notice_role`;

CREATE TABLE `irpt_notice_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `title` varchar(60) DEFAULT '' COMMENT '标题',
  `content` text COMMENT '内容',
  `from_user_id` int(11) DEFAULT '0' COMMENT '发送人ID',
  `from_user_name` varchar(32) DEFAULT '' COMMENT '发送人名称',
  `to_role_id` int(11) DEFAULT '0' COMMENT '接受人角色ID',
  `from_time` int(11) DEFAULT '0' COMMENT '发送时间',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态[0-未读1-已读2-删除]',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='角色消息管理表';



#@20121012
ALTER TABLE `admin_user`     CHANGE `add_time` `add_time` INT(11) DEFAULT '0' NOT NULL COMMENT '添加时间';
ALTER TABLE `admin_user`     ADD COLUMN `create_user_id` INT(11) DEFAULT '0' NOT NULL AFTER `add_time`,     ADD COLUMN `create_user_name` VARCHAR(32) DEFAULT '' NULL AFTER `create_user_id`;

insert into `admin_rights` (`name`, `url`, `comment`, `available`, `code`, `father_code`, `sort`, `project`, `is_show`) values('账户管理权限','main.php?m=userright&a=show','','1','0','1','8','admin','1');