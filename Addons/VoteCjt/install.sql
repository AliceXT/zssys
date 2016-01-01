CREATE TABLE IF NOT EXISTS `wp_vote` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`keyword`  varchar(50) NOT NULL  COMMENT '关键词',
`title`  varchar(100) NOT NULL  COMMENT '投票标题',
`description`  text NOT NULL  COMMENT '投票描述',
`picurl`  int(10) unsigned NOT NULL   COMMENT '封面图片',
`type`  char(10) NOT NULL  DEFAULT 0 COMMENT '选择类型',
`start_date`  int(10) NOT NULL  COMMENT '开始日期',
`end_date`  int(10) NOT NULL  COMMENT '结束日期',
`is_img`  tinyint(2) NOT NULL  DEFAULT 0 COMMENT '文字/图片投票',
`vote_count`  int(10) unsigned NOT NULL   DEFAULT 0 COMMENT '投票数',
`cTime`  int(10) NOT NULL  COMMENT '投票创建时间',
`mTime`  int(10) NOT NULL  COMMENT '更新时间',
`token`  varchar(255) NOT NULL  COMMENT 'Token',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_vote` (`id`,`keyword`,`title`,`description`,`picurl`,`type`,`start_date`,`end_date`,`is_img`,`vote_count`,`cTime`,`mTime`,`token`) VALUES ('33','长生不老','长生不老是不是好事？','主办单位：芳芳庄园\r\n我是投票描述','365','0','1439451300','1439481300','1','1','1439455252','0','gh_d7839bfd0b0a');
INSERT INTO `wp_vote` (`id`,`keyword`,`title`,`description`,`picurl`,`type`,`start_date`,`end_date`,`is_img`,`vote_count`,`cTime`,`mTime`,`token`) VALUES ('47','你好1111','官网','官网','0','0','1439448600','1440687000','1','1','1439632571','0','gh_d7839bfd0b0a');
INSERT INTO `wp_vote` (`id`,`keyword`,`title`,`description`,`picurl`,`type`,`start_date`,`end_date`,`is_img`,`vote_count`,`cTime`,`mTime`,`token`) VALUES ('48','safawefawew','模型测试','','371','0','1439451300','1440863700','1','0','1439777660','0','gh_d7839bfd0b0a');
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`) VALUES ('vote','投票','0','','1','{"1":["keyword","title","description","picurl","type","start_date","end_date"]}','1:基础','','','','','id:投票ID\r\nkeyword:关键词\r\ntitle:投票标题\r\ntype|get_name_by_status:类型\r\nis_img|get_name_by_status:状态\r\nvote_count:投票数\r\nids:操作:show&id=[id]|预览,[EDIT]&id=[id]|编辑,[DELETE]|删除','20','title','description','1388930292','1401017026','1','MyISAM');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('keyword','关键词','varchar(50) NOT NULL','string','','用户在微信里回复此关键词将会触发此投票。','1','','0','1','1','1392969972','1388930888','keyword_unique','1','此关键词已经存在，请换成别的关键词再试试','function','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('title','投票标题','varchar(100) NOT NULL','string','','','1','','0','1','1','1388931041','1388931041','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('description','投票描述','text NOT NULL','textarea','','','1','','0','0','1','1400633517','1388931173','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('picurl','封面图片','int(10) unsigned NOT NULL ','picture','','支持JPG、PNG格式，较好的效果为大图360*200，小图200*200','1','','0','0','1','1388931285','1388931285','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('type','选择类型','char(10) NOT NULL','radio','0','','1','0:单选\r\n1:多选','0','1','1','1388936429','1388931487','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('start_date','开始日期','int(10) NOT NULL','datetime','','','1','','0','0','1','1388931734','1388931734','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('end_date','结束日期','int(10) NOT NULL','datetime','','','1','','0','0','1','1388931769','1388931769','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('is_img','文字/图片投票','tinyint(2) NOT NULL','radio','0','','0','0:文字投票\r\n1:图片投票','0','1','1','1389081985','1388931941','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('vote_count','投票数','int(10) unsigned NOT NULL ','num','0','','0','','0','0','1','1388932035','1388932035','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('cTime','投票创建时间','int(10) NOT NULL','datetime','','','0','','0','1','1','1388932128','1388932128','','1','','regex','time','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('mTime','更新时间','int(10) NOT NULL','datetime','','','0','','0','0','1','1390634006','1390634006','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('token','Token','varchar(255) NOT NULL','string','','','0','','0','0','1','1391397388','1391397388','','3','','regex','','3','function');
UPDATE `wp_attribute` SET model_id= (SELECT MAX(id) FROM `wp_model`) WHERE model_id=0;

CREATE TABLE IF NOT EXISTS `wp_vote_option` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`vote_id`  int(10) unsigned NOT NULL   COMMENT '投票ID',
`name`  varchar(255) NOT NULL  COMMENT '选项标题',
`image`  int(10) unsigned NOT NULL   COMMENT '图片选项',
`opt_count`  int(10) unsigned NOT NULL   DEFAULT 0 COMMENT '当前选项投票数',
`order`  int(10) unsigned NOT NULL   DEFAULT 0 COMMENT '选项排序',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('63','33','是好事','366','1','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('64','33','不是好事','367','0','2');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('65','38','水果世界','0','0','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('66','39','水果世界','371','1','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('67','39','选项2','372','0','2');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('68','40','果园2','375','0','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('69','40','水果世界','376','0','2');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('70','41','果园2','377','1','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('71','41','水果世界','367','0','2');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('72','42','果园2','379','0','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('73','42','水果世界','368','0','2');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('74','43','果园2','0','0','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('75','43','水果世界','0','0','2');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('76','44','果园2','0','0','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('77','44','水果世界','0','0','2');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('78','45','果园2','0','0','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('79','45','水果世界','0','0','2');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('80','46','果园2','0','0','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('81','46','水果世界','0','0','2');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('82','47','果园2','377','0','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('83','47','水果世界','372','1','2');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('84','48','果园2','373','0','1');
INSERT INTO `wp_vote_option` (`id`,`vote_id`,`name`,`image`,`opt_count`,`order`) VALUES ('85','48','水果世界','366','0','2');
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`) VALUES ('vote_option','投票选项','0','','1','','1:基础','','','','','','10','','','1388933346','1388933346','1','MyISAM');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('vote_id','投票ID','int(10) unsigned NOT NULL ','num','','','4','','0','1','1','1388982678','1388933478','','3','','regex','$_REQUEST['vote_id']','3','string');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('name','选项标题','varchar(255) NOT NULL','string','','','1','','0','1','1','1388933552','1388933552','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('image','图片选项','int(10) unsigned NOT NULL ','picture','','','5','','0','0','1','1388984467','1388933679','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('opt_count','当前选项投票数','int(10) unsigned NOT NULL ','num','0','','0','','0','0','1','1388933860','1388933860','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('order','选项排序','int(10) unsigned NOT NULL ','num','0','','1','','0','0','1','1388933951','1388933951','','3','','regex','','3','function');
UPDATE `wp_attribute` SET model_id= (SELECT MAX(id) FROM `wp_model`) WHERE model_id=0;

CREATE TABLE IF NOT EXISTS `wp_vote_log` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`vote_id`  int(10) unsigned NOT NULL   COMMENT '投票ID',
`user_id`  int(10) NOT NULL   COMMENT '用户ID',
`token`  varchar(255) NOT NULL  COMMENT '用户TOKEN',
`options`  varchar(255) NOT NULL  COMMENT '选择选项',
`cTime`  int(10) NOT NULL  COMMENT '创建时间',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_vote_log` (`id`,`vote_id`,`user_id`,`token`,`options`,`cTime`) VALUES ('21','33','1','gh_d7839bfd0b0a','63','1439451496');
INSERT INTO `wp_vote_log` (`id`,`vote_id`,`user_id`,`token`,`options`,`cTime`) VALUES ('22','39','1','gh_d7839bfd0b0a','66','1439624903');
INSERT INTO `wp_vote_log` (`id`,`vote_id`,`user_id`,`token`,`options`,`cTime`) VALUES ('23','41','1','gh_d7839bfd0b0a','70','1439627753');
INSERT INTO `wp_vote_log` (`id`,`vote_id`,`user_id`,`token`,`options`,`cTime`) VALUES ('24','47','1','gh_d7839bfd0b0a','83','1439639016');
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`) VALUES ('vote_log','投票记录','0','','1','','1:基础','','','','','','10','','','1388934136','1388934136','1','MyISAM');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('vote_id','投票ID','int(10) unsigned NOT NULL ','num','','','0','','0','1','1','1388934189','1388934189','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('user_id','用户ID','int(10) NOT NULL ','num','','','0','','0','0','1','1388934265','1388934265','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('token','用户TOKEN','varchar(255) NOT NULL','string','','','0','','0','0','1','1388934296','1388934296','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('options','选择选项','varchar(255) NOT NULL','string','','','0','','0','1','1','1388934351','1388934351','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('cTime','创建时间','int(10) NOT NULL','datetime','','','0','','0','0','1','1388934413','1388934392','','3','','regex','time','3','function');
UPDATE `wp_attribute` SET model_id= (SELECT MAX(id) FROM `wp_model`) WHERE model_id=0;

CREATE TABLE IF NOT EXISTS `wp_vote_forms` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`vote_id`  int(10) NOT NULL  COMMENT '投票ID',
`forms_id`  int(10) NOT NULL  COMMENT '表单ID',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_vote_forms` (`id`,`forms_id`,`vote_id`) VALUES ('1','18','54');
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`) VALUES ('vote_forms','VoteCjt记录投票和表单的关系','0','','1','','1:基础','','','','','','10','','','1441098199','1441098199','1','MyISAM');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('vote_id','投票ID','int(10) NOT NULL','num','','','1','','0','1','1','1441098288','1441098288','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('forms_id','表单ID','int(10) NOT NULL','num','','','1','','0','1','1','1441098266','1441098266','','3','','regex','','3','function');
UPDATE `wp_attribute` SET model_id= (SELECT MAX(id) FROM `wp_model`) WHERE model_id=0;

CREATE TABLE IF NOT EXISTS `wp_vote_option_link` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`value_id`  int(10) NOT NULL  COMMENT '报名ID',
`opt_id`  int(10) NOT NULL  COMMENT '选项ID',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_vote_option_link` (`id`,`opt_id`,`value_id`) VALUES ('1','97','79');
INSERT INTO `wp_vote_option_link` (`id`,`opt_id`,`value_id`) VALUES ('2','98','80');
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`) VALUES ('vote_option_link','投票选项链接','0','','1','','1:基础','','','','','','10','','','1441095828','1441095828','1','MyISAM');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('value_id','报名ID','int(10) NOT NULL','num','','','1','','0','1','1','1441095978','1441095978','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('opt_id','选项ID','int(10) NOT NULL','num','','','1','','0','1','1','1441095886','1441095886','','3','','regex','','3','function');
UPDATE `wp_attribute` SET model_id= (SELECT MAX(id) FROM `wp_model`) WHERE model_id=0;