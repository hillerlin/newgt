/*
SQLyog Community Edition- MySQL GUI v6.5 Beta1
MySQL - 5.1.73 : Database - gt
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

create database if not exists `gt`;

USE `gt`;

/*Table structure for table `gt_admin` */

DROP TABLE IF EXISTS `gt_admin`;

CREATE TABLE `gt_admin` (
  `admin_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` tinyint(3) unsigned NOT NULL COMMENT '角色id',
  `last_login_ip` varchar(15) NOT NULL DEFAULT '0.0.0.0' COMMENT '最后登录ip',
  `admin_name` varchar(15) NOT NULL COMMENT '管理员登录账号',
  `admin_password` varchar(32) NOT NULL COMMENT '管理员登录密码',
  `real_name` varchar(32) NOT NULL DEFAULT '' COMMENT '姓名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '账号状态',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `login_times` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `is_supper` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否超级管理员',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'user表id',
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='管理员表';

/*Data for the table `gt_admin` */

insert  into `gt_admin`(`admin_id`,`role_id`,`last_login_ip`,`admin_name`,`admin_password`,`real_name`,`status`,`add_time`,`last_login_time`,`login_times`,`is_supper`,`uid`) values (1,1,'192.168.0.8','admin','21232f297a57a5a743894a0e4a801fc3','acas',1,0,1460110277,65,1,0),(3,2,'127.0.0.1','admin123','21232f297a57a5a743894a0e4a801fc3','acas',1,1458734448,1459905758,5,0,1),(4,14,'192.168.0.8','admin111','bbad8d72c1fac1d081727158807a8798','大boss',1,1458734668,1459321702,2,0,1),(6,16,'127.0.0.1','admin555','21232f297a57a5a743894a0e4a801fc3','2222',1,1458892400,1459828980,5,0,0),(8,2,'0.0.0.0','admin1','21232f297a57a5a743894a0e4a801fc3','大boss',1,1459475562,0,0,0,0),(9,2,'0.0.0.0','admin2','21232f297a57a5a743894a0e4a801fc3','',1,1459475574,0,0,0,0),(10,2,'0.0.0.0','admin3','21232f297a57a5a743894a0e4a801fc3','',1,1459475589,0,0,0,0),(11,2,'0.0.0.0','admin4','21232f297a57a5a743894a0e4a801fc3','',1,1459475601,0,0,0,0),(12,2,'0.0.0.0','admin5','21232f297a57a5a743894a0e4a801fc3','',1,1459475618,0,0,0,0),(13,2,'0.0.0.0','admin6','21232f297a57a5a743894a0e4a801fc3','',1,1459475643,0,0,0,0),(14,2,'0.0.0.0','admin7','21232f297a57a5a743894a0e4a801fc3','',1,1459475656,0,0,0,0),(15,2,'0.0.0.0','admin8','21232f297a57a5a743894a0e4a801fc3','',1,1459475669,0,0,0,0),(16,2,'0.0.0.0','admin9','21232f297a57a5a743894a0e4a801fc3','',1,1459475700,0,0,0,0),(17,2,'0.0.0.0','admin10','21232f297a57a5a743894a0e4a801fc3','',1,1459478293,0,0,0,0),(18,2,'0.0.0.0','admin11','21232f297a57a5a743894a0e4a801fc3','',1,1459478305,0,0,0,0);

/*Table structure for table `gt_admin_role` */

DROP TABLE IF EXISTS `gt_admin_role`;

CREATE TABLE `gt_admin_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL COMMENT '管理员id',
  `role_id` tinyint(3) unsigned NOT NULL COMMENT '角色id',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `up_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `gt_admin_role` */

insert  into `gt_admin_role`(`id`,`admin_id`,`role_id`,`add_time`,`up_time`) values (1,1,1,0,0),(2,1,2,0,0);

/*Table structure for table `gt_auth` */

DROP TABLE IF EXISTS `gt_auth`;

CREATE TABLE `gt_auth` (
  `role_id` tinyint(3) NOT NULL,
  `menu_id` smallint(6) NOT NULL,
  KEY `role_id` (`role_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `gt_auth` */

insert  into `gt_auth`(`role_id`,`menu_id`) values (1,2),(1,3),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(2,3),(2,9),(2,12),(2,18),(2,19),(2,20),(2,23),(2,24),(2,25),(7,11),(7,13),(7,14),(13,3),(13,9),(13,12),(13,18),(14,3),(14,9),(14,12),(14,18),(15,2),(15,3),(15,6),(15,7),(15,8),(15,9),(15,10),(15,11),(15,12),(15,13),(15,14),(15,16),(15,17),(15,18),(15,19),(15,20),(15,21),(15,22),(15,23),(15,24),(15,25),(16,3),(16,9),(16,12),(16,21),(16,22);

/*Table structure for table `gt_company` */

DROP TABLE IF EXISTS `gt_company`;

CREATE TABLE `gt_company` (
  `company_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(256) NOT NULL COMMENT '公司名',
  `company_mobile` varchar(16) DEFAULT NULL COMMENT '联系人手机号',
  `company_phone` varchar(16) DEFAULT NULL COMMENT '联系人座机号',
  `company_linker` varchar(64) DEFAULT NULL COMMENT '公司联系人',
  `company_email` varchar(32) NOT NULL DEFAULT '' COMMENT '联系人邮箱',
  `company_address` varchar(256) DEFAULT NULL COMMENT '公司地址',
  `company_remark` varchar(156) DEFAULT NULL COMMENT '公司备注',
  `bank_name` varchar(256) DEFAULT NULL COMMENT '开户行',
  `bank_no` varchar(32) DEFAULT NULL COMMENT '开户卡号',
  `gt_uid` int(11) NOT NULL COMMENT '国投的跟进人',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态1正常',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`company_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `gt_company` */

insert  into `gt_company`(`company_id`,`company_name`,`company_mobile`,`company_phone`,`company_linker`,`company_email`,`company_address`,`company_remark`,`bank_name`,`bank_no`,`gt_uid`,`status`,`addtime`) values (1,'湿哒哒','123444555',NULL,NULL,'',NULL,NULL,NULL,NULL,0,1,0),(2,'某VR公司','13565657878','','马先生','','','','','',0,1,0);

/*Table structure for table `gt_department` */

DROP TABLE IF EXISTS `gt_department`;

CREATE TABLE `gt_department` (
  `dept_id` mediumint(5) unsigned NOT NULL AUTO_INCREMENT,
  `department` varchar(32) NOT NULL DEFAULT '' COMMENT '部门',
  `dept_desc` varchar(256) NOT NULL DEFAULT '' COMMENT '部门描述',
  PRIMARY KEY (`dept_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `gt_department` */

insert  into `gt_department`(`dept_id`,`department`,`dept_desc`) values (1,'it部','');

/*Table structure for table `gt_dept` */

DROP TABLE IF EXISTS `gt_dept`;

CREATE TABLE `gt_dept` (
  `dept_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(256) NOT NULL COMMENT '公司名称',
  `dept_name` varchar(256) NOT NULL COMMENT '部门名称',
  `dept_linker` varchar(64) DEFAULT NULL COMMENT '部门负责人',
  `addtime` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`dept_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `gt_dept` */

/*Table structure for table `gt_finance_order` */

DROP TABLE IF EXISTS `gt_finance_order`;

CREATE TABLE `gt_finance_order` (
  `oid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fp_id` int(10) unsigned NOT NULL COMMENT '认购项目id',
  `mid` int(10) NOT NULL COMMENT '会员id',
  `money` decimal(15,2) NOT NULL COMMENT '认购金额',
  `addtime` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `gt_finance_order` */

insert  into `gt_finance_order`(`oid`,`fp_id`,`mid`,`money`,`addtime`,`status`) values (1,1,1,'1000.00',0,0),(2,1,1,'9000.00',0,0),(3,1,2,'30000.00',1460086700,0);

/*Table structure for table `gt_finance_project` */

DROP TABLE IF EXISTS `gt_finance_project`;

CREATE TABLE `gt_finance_project` (
  `fp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pro_id` int(10) unsigned NOT NULL COMMENT '对应的项目id',
  `finance_money` decimal(20,4) NOT NULL COMMENT '待融资金额',
  `max_money` decimal(13,4) NOT NULL COMMENT '单个用户最大认购金额',
  `left_money` decimal(20,4) NOT NULL COMMENT '剩余待认购金',
  `endtime` int(10) unsigned NOT NULL COMMENT '截止日期',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否发布1发布',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间戳',
  PRIMARY KEY (`fp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `gt_finance_project` */

insert  into `gt_finance_project`(`fp_id`,`pro_id`,`finance_money`,`max_money`,`left_money`,`endtime`,`status`,`addtime`) values (1,19,'9000000.0000','1000000.0000','9000000.0000',1461945600,1,1459999742);

/*Table structure for table `gt_member` */

DROP TABLE IF EXISTS `gt_member`;

CREATE TABLE `gt_member` (
  `mid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_name` varchar(64) NOT NULL COMMENT '账号',
  `member_password` varchar(32) NOT NULL COMMENT '密码',
  `linkman_name` varchar(32) NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `member_level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '客户的级别',
  `member_birth` int(11) NOT NULL COMMENT '客户的生日',
  `member_mobile` varchar(16) NOT NULL COMMENT '客户的手机号',
  `member_phone` varchar(16) NOT NULL COMMENT '客户的座机',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '国投的跟进人',
  `member_email` varchar(128) NOT NULL COMMENT '客户的联系邮箱',
  `company_name` varchar(128) NOT NULL DEFAULT '0' COMMENT '公司名称',
  `member_remark` varchar(256) DEFAULT NULL COMMENT '备注',
  `bank_name` varchar(256) DEFAULT NULL COMMENT '客户的银行名称',
  `bank_no` varchar(32) DEFAULT NULL COMMENT '客户的银行卡号',
  `credit_line` decimal(15,2) NOT NULL COMMENT '最大认购金额',
  `frozen_credit` decimal(15,2) NOT NULL COMMENT '认购中的额度',
  `rate` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '这个会员单位默认的利润',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1正常',
  `addtime` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`mid`),
  UNIQUE KEY `member_name` (`member_name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `gt_member` */

insert  into `gt_member`(`mid`,`member_name`,`member_password`,`linkman_name`,`member_level`,`member_birth`,`member_mobile`,`member_phone`,`admin_id`,`member_email`,`company_name`,`member_remark`,`bank_name`,`bank_no`,`credit_line`,`frozen_credit`,`rate`,`status`,`addtime`) values (1,'damailicai','21232f297a57a5a743894a0e4a801fc3','ABC',0,0,'13888888888','',0,'','大麦理财',NULL,NULL,NULL,'20000000.00','10000.00','0.00',1,NULL),(2,'lujinsuo','','刘小姐',0,0,'13666666666','',0,'hujo@1633.com','陆金所',NULL,NULL,NULL,'500000.00','30000.00','0.00',1,NULL),(3,'yoyo','21232f297a57a5a743894a0e4a801fc3','ABC',0,0,'13888886666','',0,'','财付通',NULL,NULL,NULL,'500000.00','0.00','0.00',1,1460097156);

/*Table structure for table `gt_menu` */

DROP TABLE IF EXISTS `gt_menu`;

CREATE TABLE `gt_menu` (
  `menu_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(50) NOT NULL COMMENT '菜单名称',
  `pid` smallint(6) NOT NULL COMMENT '父级id',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型 1:菜单 2:操作点',
  `module_name` varchar(20) NOT NULL COMMENT '模块名',
  `action_name` varchar(20) NOT NULL COMMENT '操作名',
  `class_name` varchar(20) DEFAULT NULL COMMENT '图标样式名',
  `data` varchar(120) NOT NULL COMMENT 'url参数',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `often` tinyint(1) NOT NULL DEFAULT '0',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0::禁用 1:启用',
  PRIMARY KEY (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

/*Data for the table `gt_menu` */

insert  into `gt_menu`(`menu_id`,`menu_name`,`pid`,`type`,`module_name`,`action_name`,`class_name`,`data`,`remark`,`often`,`sort`,`status`) values (2,'系统',0,1,'','',NULL,'','',0,255,1),(3,'项目管理',0,1,'','',NULL,'','',0,255,1),(6,'核心',2,1,'','',NULL,'','',0,255,1),(7,'权限管理',6,1,'Role','index',NULL,'','',0,255,1),(8,'菜单管理',6,1,'Menu','index',NULL,'','',0,255,1),(9,'项目',3,1,'Project','index','','','',0,255,1),(10,'管理员',6,1,'Admin','index','','','',0,1,1),(11,'财务管理',0,1,'','','','','',0,255,1),(12,'项目立项',9,1,'Project','start','','','',0,255,1),(13,'财务审核',11,1,'','','','','',0,255,1),(14,'入账',13,1,'','','','','啊啊啊啊',0,255,1),(16,'测试模块',11,1,'','','','','',0,255,1),(17,'测试模块3',11,1,'','','','','',0,255,1),(18,'项目审核',9,1,'Project','auditList','','','',0,255,1),(19,'债权',3,1,'','','','','',0,255,1),(20,'债权管理',19,1,'ProjectDebt','index','','','',0,255,1),(21,'基础资料',3,1,'','','','','',0,1,1),(22,'客户资料',21,1,'Company','index','','','',0,255,1),(23,'项目跟踪',9,1,'Project','follow','','','',0,255,1),(24,'项目完结',9,1,'Project','finish','','','',0,255,1),(25,'项目列表',9,1,'Project','index','','','',0,255,1),(29,'单位列表',31,1,'Member','index','','','',0,255,1),(30,'融资管理',0,1,'','','','','',0,255,1),(31,'会员单位',30,1,'','','','','',0,255,1),(32,'融资列表',30,1,'','','','','融资的项目列表',0,255,1),(33,'发布项目',32,1,'FinanceProject','index','','fstatus=0','等待融资中的项目列表',0,255,1);

/*Table structure for table `gt_process` */

DROP TABLE IF EXISTS `gt_process`;

CREATE TABLE `gt_process` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `process_name` varchar(16) NOT NULL COMMENT '流程名称',
  `process_desc` varchar(128) NOT NULL DEFAULT '' COMMENT '流程描述',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `gt_process` */

/*Table structure for table `gt_process_log` */

DROP TABLE IF EXISTS `gt_process_log`;

CREATE TABLE `gt_process_log` (
  `id` mediumint(5) unsigned NOT NULL AUTO_INCREMENT,
  `pro_id` tinyint(3) unsigned NOT NULL COMMENT '总流程id',
  `admin_id` int(10) unsigned NOT NULL COMMENT '处理人',
  `status` tinyint(1) unsigned NOT NULL COMMENT '0未通过1通过',
  `opinion` varchar(128) NOT NULL DEFAULT '' COMMENT '意见',
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

/*Data for the table `gt_process_log` */

insert  into `gt_process_log`(`id`,`pro_id`,`admin_id`,`status`,`opinion`,`addtime`) values (7,9,1,1,'哈哈哈哈',1459306848),(8,9,1,1,'继续',1459311184),(9,9,1,1,'',1459323005),(10,9,1,1,'',1459323110),(11,13,1,1,'同意',1459418602),(12,13,1,1,'123',1459423103),(13,13,1,1,'打的',1459423117),(14,13,1,1,'过',1459423241),(15,18,6,1,'ssss',1459836973),(16,18,3,1,'very good',1459837137),(18,19,6,1,'SSS',1459838796),(19,18,3,0,'不通过',1459839186),(20,19,3,0,'',1459847307);

/*Table structure for table `gt_project` */

DROP TABLE IF EXISTS `gt_project`;

CREATE TABLE `gt_project` (
  `pro_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pro_no` varchar(64) DEFAULT NULL COMMENT '项目编号',
  `pro_title` varchar(256) NOT NULL COMMENT '项目标题',
  `pro_desc` text COMMENT '项目简介',
  `pro_content` text COMMENT '项目内容',
  `pro_account` decimal(20,4) NOT NULL COMMENT '融资金额',
  `pro_real_money` decimal(20,4) NOT NULL DEFAULT '0.0000' COMMENT '实际借款金额',
  `pro_step` tinyint(1) DEFAULT '1' COMMENT '0:审核未通过 1:提交未处理  5项目部审核通过 8 风控部门审核通过',
  `admin_id` int(11) NOT NULL COMMENT '国投的跟进人',
  `company_id` int(11) NOT NULL COMMENT '公司',
  `pro_linker` int(11) DEFAULT NULL COMMENT '提交人',
  `addtime` int(11) NOT NULL COMMENT '添加时间',
  `submit_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0草稿1提交',
  `role_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '当前状态处理角色',
  `is_loan` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否放款0未放款1放款',
  PRIMARY KEY (`pro_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

/*Data for the table `gt_project` */

insert  into `gt_project`(`pro_id`,`pro_no`,`pro_title`,`pro_desc`,`pro_content`,`pro_account`,`pro_real_money`,`pro_step`,`admin_id`,`company_id`,`pro_linker`,`addtime`,`submit_status`,`role_id`,`is_loan`) values (9,'ABC1','项目一',NULL,NULL,'200000.0000','111111.0000',3,1,1,1111,0,1,0,0),(10,'ABC2','张三','这是一个神奇的项目','这是一个神奇的项目','200000.0000','111111.0000',1,1,1,NULL,0,0,0,0),(13,'sdfjalkdfj','李四项目1','啊打发打发','啊手动阀第三方','2000.0000','10000.0000',5,1,1,NULL,1459328740,1,1,0),(18,'test4','测试4','测试4','测试4','356666.0000','0.0000',0,0,1,6,1459836263,1,0,0),(19,'TEST5','测试5','测试5','测试5','35546456.0000','10000000.0000',NULL,0,1,3,1459838200,1,0,1),(20,NULL,'',NULL,NULL,'0.0000','0.0000',1,0,0,3,1459849310,0,0,0);

/*Table structure for table `gt_project_attachment` */

DROP TABLE IF EXISTS `gt_project_attachment`;

CREATE TABLE `gt_project_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '附件id',
  `file_id` tinyint(3) unsigned NOT NULL COMMENT '文件夹id',
  `pro_id` int(10) unsigned NOT NULL COMMENT '项目id',
  `path` varchar(128) NOT NULL COMMENT '文件路径',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `doc_name` varchar(32) NOT NULL DEFAULT '' COMMENT '文件名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

/*Data for the table `gt_project_attachment` */

insert  into `gt_project_attachment`(`id`,`file_id`,`pro_id`,`path`,`addtime`,`doc_name`) values (21,7,18,'/Uploads/project/attachment/pro-18/jbzl-zj-57037db48f363.pdf',1459846580,'5. 业务操作流程图 8.5.pdf'),(28,7,13,'/Uploads/project/attachment/pro-13/jbzl-zj-57077d68c8859.pdf',1460108648,'1.保理合同.pdf'),(31,7,13,'/Uploads/project/attachment/pro-13/jbzl-zj-5707804f02cc0.jpg',1460109391,'u=1457437487,655486635&fm=111&gp');

/*Table structure for table `gt_project_debt` */

DROP TABLE IF EXISTS `gt_project_debt`;

CREATE TABLE `gt_project_debt` (
  `debt_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `debt_no` varchar(32) DEFAULT NULL COMMENT '债权的编号',
  `pro_id` int(10) unsigned NOT NULL COMMENT '项目ID',
  `admin_id` int(10) unsigned NOT NULL COMMENT '录入人的ID',
  `start_time` int(10) unsigned NOT NULL COMMENT '开始时间',
  `end_time` int(10) unsigned NOT NULL COMMENT '结束时间',
  `debt_account` decimal(20,4) unsigned NOT NULL COMMENT '债权金额',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '如果是换质，这里是置换前的债权ID',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 正常 0作废 2已还款 ',
  `debt_repay_account` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '已还款金额',
  `debt_pay_account` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '公司打开给融资方多少',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`debt_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Data for the table `gt_project_debt` */

insert  into `gt_project_debt`(`debt_id`,`debt_no`,`pro_id`,`admin_id`,`start_time`,`end_time`,`debt_account`,`parent_id`,`status`,`debt_repay_account`,`debt_pay_account`,`addtime`) values (1,'213123',13,1,2147483647,2147483647,'100.0000',0,1,'0.0000','0.0000',2147483647),(2,'1212',19,1,1459849762,1459849762,'10000.0000',0,0,'0.0000','0.0000',1459849762),(3,'1213',19,1,1459849762,1459849762,'500000.0000',0,1,'0.0000','0.0000',1459849824),(4,'1214',19,1,1459849762,1459849762,'200000.0000',0,1,'0.0000','0.0000',1459850266),(5,'1215',19,1,1459849762,1459849762,'20000.0000',0,1,'0.0000','0.0000',1459851532),(8,'1213',19,3,1460822400,1461945600,'10000.0000',0,1,'0.0000','0.0000',1459851999),(9,'12134',19,3,1459872000,1462464000,'255555.0000',2,1,'0.0000','0.0000',1459915401),(11,'1313',19,3,1459872000,1465142400,'55555.0000',2,1,'0.0000','0.0000',1459915813);

/*Table structure for table `gt_project_file` */

DROP TABLE IF EXISTS `gt_project_file`;

CREATE TABLE `gt_project_file` (
  `file_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `pid` tinyint(3) unsigned NOT NULL COMMENT '父文件夹',
  `is_file` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否为文件夹',
  `is_document` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为文件',
  `file_name` varchar(32) NOT NULL DEFAULT '' COMMENT '文件夹/文件名称',
  `short_name` varchar(16) NOT NULL DEFAULT '' COMMENT '文件夹简写，使用拼音',
  PRIMARY KEY (`file_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `gt_project_file` */

insert  into `gt_project_file`(`file_id`,`pid`,`is_file`,`is_document`,`file_name`,`short_name`) values (2,1,1,0,'基本资料','jbzl'),(3,1,1,0,'财务资料','cwzl'),(4,1,1,0,'信用资料','xyzl'),(5,1,1,0,'业务资料','ywzl'),(6,1,1,0,'立项申请','lxzl'),(7,2,1,0,'证件','jbzl-zj'),(8,2,1,0,'章程','zc'),(9,2,1,0,'验资报告','yz'),(10,2,1,0,'公司及股东简介、股权架构','gs'),(1,0,1,0,'资料包模板','zlbmb');

/*Table structure for table `gt_project_note` */

DROP TABLE IF EXISTS `gt_project_note`;

CREATE TABLE `gt_project_note` (
  `note_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pro_id` int(10) unsigned NOT NULL COMMENT '项目ID',
  `gt_uid` int(10) unsigned NOT NULL COMMENT '国投用户ID',
  `note_desc` text COMMENT '意见',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '状态 2正常',
  `pro_status` tinyint(1) DEFAULT NULL COMMENT '提意见时项目属于的状态',
  `note_agree` tinyint(1) NOT NULL DEFAULT '0' COMMENT '通过与否',
  `addtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`note_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `gt_project_note` */

/*Table structure for table `gt_role` */

DROP TABLE IF EXISTS `gt_role`;

CREATE TABLE `gt_role` (
  `role_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `pid` tinyint(3) unsigned NOT NULL COMMENT '上级权限组',
  `role_name` varchar(50) NOT NULL DEFAULT '' COMMENT '权限组名',
  `role_des` varchar(256) NOT NULL DEFAULT '' COMMENT '权限说明',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用(0:是 1:否)',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

/*Data for the table `gt_role` */

insert  into `gt_role`(`role_id`,`pid`,`role_name`,`role_des`,`sort`,`status`,`add_time`) values (1,0,'超级管理员','',0,1,0),(2,14,'项目跟进员','',0,1,0),(7,0,'财务专员','',0,1,0),(13,0,'财务部','',0,1,0),(14,15,'项目部','',0,1,0),(15,0,'总经理','',0,1,0),(16,14,'项目经理','',0,1,0);

/*Table structure for table `gt_user` */

DROP TABLE IF EXISTS `gt_user`;

CREATE TABLE `gt_user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `leader_uid` int(11) NOT NULL DEFAULT '0' COMMENT '领导的UID',
  `user_name` varchar(64) NOT NULL COMMENT '用户名',
  `nick_name` varchar(64) NOT NULL COMMENT '昵称',
  `user_mobile` varchar(16) DEFAULT NULL COMMENT '手机号',
  `user_phone` varchar(16) DEFAULT NULL COMMENT '座机号',
  `linker_name` varchar(64) DEFAULT NULL COMMENT '紧急联系人',
  `linker_mobile` varchar(16) DEFAULT NULL COMMENT '紧急联系人手机号',
  `social_no` varchar(32) DEFAULT NULL COMMENT '社保号',
  `fund_no` varchar(32) DEFAULT NULL COMMENT '公积金账号',
  `fund_bank_name` varchar(128) DEFAULT NULL COMMENT '公积金银行',
  `fund_bank_no` varchar(128) DEFAULT NULL COMMENT '公积金银行卡号',
  `entry_time` int(11) NOT NULL DEFAULT '0' COMMENT '入职日期',
  `over_time` int(11) NOT NULL DEFAULT '0' COMMENT '离职日期',
  `birth_time` int(11) NOT NULL DEFAULT '0' COMMENT '生日',
  `user_email` varchar(128) DEFAULT NULL COMMENT '邮箱',
  `dept_id` int(11) NOT NULL COMMENT '部门',
  `remark` varchar(256) DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT '2' COMMENT '0 冻结 2正常 1离职',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `gt_user` */

insert  into `gt_user`(`uid`,`leader_uid`,`user_name`,`nick_name`,`user_mobile`,`user_phone`,`linker_name`,`linker_mobile`,`social_no`,`fund_no`,`fund_bank_name`,`fund_bank_no`,`entry_time`,`over_time`,`birth_time`,`user_email`,`dept_id`,`remark`,`status`) values (1,0,'哈哈','哈哈','13888888888','13888888888',NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,1,NULL,2);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
