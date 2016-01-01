CREATE TABLE IF NOT EXISTS `wp_hccard` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`user_id`  int(10) UNSIGNED NOT NULL  COMMENT '用户ID',
`wechat`  varchar(255) NOT NULL  COMMENT '微信',
`description`  varchar(255)  COMMENT '转发描述',
`info`  text NOT NULL  COMMENT '其他',
`token`  varchar(255) NOT NULL  COMMENT 'token',
`qr_code`  varchar(255) NOT NULL  COMMENT '二维码',
`ownername`  varchar(255) NOT NULL  COMMENT '姓名',
`company_name`  varchar(255) NOT NULL  COMMENT '公司',
`portrait`  int(10) UNSIGNED NOT NULL  COMMENT '头像',
`phone`  varchar(255) NOT NULL  COMMENT '手机',
`tel`  varchar(255) NOT NULL  COMMENT '电话',
`post`  varchar(255) NOT NULL  COMMENT '职位',
`fax`  varchar(255) NOT NULL  COMMENT '传真',
`email`  varchar(255) NOT NULL  COMMENT '邮箱',
`QQ`  varchar(255) NOT NULL  COMMENT 'QQ',
`company_addr`  varchar(255) NOT NULL  COMMENT '公司地址',
`company_url`  varchar(255) NOT NULL  COMMENT '公司网址',
`share_count`  int(10) NOT NULL  DEFAULT 0 COMMENT '分享统计',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_hccard` (`id`,`user_id`,`wechat`,`description`,`info`,`token`,`qr_code`,`ownername`,`company_name`,`portrait`,`phone`,`tel`,`post`,`fax`,`email`,`QQ`,`company_addr`,`company_url`,`share_count`) VALUES ('5','363','XueTing_Fang','很高兴认识你','','gh_d7839bfd0b0a','','方雪婷','昌佳科技','387','13825010619','13825010619','PHP工程师','85838151111','8jing8jing8@163.com','502285815','广东省东莞市溪头村莞太路','http://fffx.coding.io/','0');
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`) VALUES ('hccard','名片','0','','1','{"1":["company_addr","company_url","QQ","email","fax","post","tel","phone","portrait","ownername","company_name","info","wechat","description"]}','1:基础','','','','','id:序号\r\nownername:姓名\r\nphone:手机\r\ntel:电话\r\nQQ:QQ\r\nid:操作:[EDIT]|编辑,[DELETE]|删除,show?id=[id]|预览','10','ownername','','1439877406','1440750565','1','MyISAM');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('user_id','用户ID','int(10) UNSIGNED NOT NULL','num','','','0','','0','0','1','1440470719','1440223141','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wechat','微信','varchar(255) NOT NULL','string','','','1','','0','0','1','1440404784','1440404729','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('description','转发描述','varchar(255)','string','','','1','','0','1','1','1440468744','1439888141','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('info','其他','text NOT NULL','textarea','','','1','','0','0','1','1439878250','1439878250','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('token','token','varchar(255) NOT NULL','string','','','0','','0','0','1','1439877543','1439877543','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('qr_code','二维码','varchar(255) NOT NULL','string','','','0','','0','0','1','1439877727','1439877727','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('ownername','姓名','varchar(255) NOT NULL','string','','','1','','0','1','1','1439877498','1439877498','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('company_name','公司','varchar(255) NOT NULL','string','','','1','','0','0','1','1439877931','1439877931','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('portrait','头像','int(10) UNSIGNED NOT NULL','picture','','','1','','0','1','1','1439877753','1439877753','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('phone','手机','varchar(255) NOT NULL','string','','','1','','0','0','1','1439877811','1439877811','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('tel','电话','varchar(255) NOT NULL','string','','','1','','0','0','1','1439877797','1439877797','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('post','职位','varchar(255) NOT NULL','string','','','1','','0','0','1','1439877777','1439877777','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('fax','传真','varchar(255) NOT NULL','string','','','1','','0','0','1','1439877825','1439877825','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('email','邮箱','varchar(255) NOT NULL','string','','','1','','0','0','1','1439877853','1439877853','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('QQ','QQ','varchar(255) NOT NULL','string','','','1','','0','0','1','1439877838','1439877838','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('company_addr','公司地址','varchar(255) NOT NULL','string','','','1','','0','0','1','1439877872','1439877872','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('company_url','公司网址','varchar(255) NOT NULL','string','','','1','','0','0','1','1439877885','1439877885','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('share_count','分享统计','int(10) NOT NULL','num','0','','0','','0','0','1','1440232526','1440232526','','3','','regex','','3','function');
UPDATE `wp_attribute` SET model_id= (SELECT MAX(id) FROM `wp_model`) WHERE model_id=0;

CREATE TABLE IF NOT EXISTS `wp_hccard_link` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`url`  varchar(255) NOT NULL  COMMENT '外链地址',
`title`  varchar(255) NOT NULL  COMMENT '外链标题',
`hccard_id`  int(10) NOT NULL  COMMENT '名片ID',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_hccard_link` (`id`,`url`,`title`,`hccard_id`) VALUES ('1','www.cjt-cn.com','昌佳科技','7');
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`) VALUES ('hccard_link','名片外链','0','','1','','1:基础','','','','','','10','','','1440379201','1440379201','1','MyISAM');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('url','外链地址','varchar(255) NOT NULL','string','','','1','','0','1','1','1440379330','1440379330','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('title','外链标题','varchar(255) NOT NULL','string','','','1','','0','1','1','1440379354','1440379354','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('hccard_id','名片ID','int(10) NOT NULL','num','','','1','','0','1','1','1441079286','1440379401','','3','','regex','','3','function');
UPDATE `wp_attribute` SET model_id= (SELECT MAX(id) FROM `wp_model`) WHERE model_id=0;

CREATE TABLE IF NOT EXISTS `wp_hccard_company` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`title`  varchar(255) NOT NULL  COMMENT '标题',
`desc`  varchar(255) NOT NULL  COMMENT '描述',
`pic`  int(10) UNSIGNED NOT NULL  COMMENT '图片',
`content`  text NOT NULL  COMMENT '内容',
`hccard_id`  int(10) NOT NULL  COMMENT '名片ID',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_hccard_company` (`id`,`title`,`desc`,`pic`,`content`,`hccard_id`) VALUES ('1','昌佳的介绍','一家新兴的科技公司','396','<p>昌佳科技是一家好公司<br/></p>','5');
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`) VALUES ('hccard_company','公司介绍','0','','1','','1:基础','','','','','','10','','','1440378995','1440378995','1','MyISAM');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('title','标题','varchar(255) NOT NULL','string','','','1','','0','1','1','1440491666','1440379056','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('desc','描述','varchar(255) NOT NULL','string','','','1','','0','0','1','1440379075','1440379075','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('pic','图片','int(10) UNSIGNED NOT NULL','picture','','','1','','0','0','1','1440379100','1440379100','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('content','内容','text NOT NULL','editor','','','1','','0','1','1','1440379149','1440379149','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('hccard_id','名片ID','int(10) NOT NULL','num','','','0','','0','1','1','1440484668','1440379249','','3','','regex','','3','function');
UPDATE `wp_attribute` SET model_id= (SELECT MAX(id) FROM `wp_model`) WHERE model_id=0;

CREATE TABLE IF NOT EXISTS `wp_hccard_message` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`name`  varchar(255) NOT NULL  COMMENT '姓名',
`hccard_id`  int(10) NOT NULL  COMMENT '微名片ID',
`tel`  varchar(255) NOT NULL  COMMENT '联系电话',
`content`  text NOT NULL  COMMENT '内容',
`state`  char(50) NOT NULL  DEFAULT '未查看' COMMENT '状态',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`) VALUES ('hccard_message','名片留言','0','','1','','1:基础','','','','','','10','','','1440223564','1440223564','1','MyISAM');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('name','姓名','varchar(255) NOT NULL','string','','','1','','0','0','1','1440223842','1440223842','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('hccard_id','微名片ID','int(10) NOT NULL','num','','','0','','0','1','1','1440223851','1440223787','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('tel','联系电话','varchar(255) NOT NULL','string','','','1','','0','0','1','1440223963','1440223963','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('content','内容','text NOT NULL','textarea','','','1','','0','1','1','1440224014','1440224014','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('state','状态','char(50) NOT NULL','select','未查看','','0','未查看:未查看\r\n已查看:已查看\r\n\r\n','0','0','1','1440224229','1440224229','','3','','regex','','3','function');
UPDATE `wp_attribute` SET model_id= (SELECT MAX(id) FROM `wp_model`) WHERE model_id=0;