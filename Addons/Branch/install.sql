CREATE TABLE IF NOT EXISTS `wp_branch` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`token`  varchar(255) NOT NULL  COMMENT 'Token',
`openids`  text NOT NULL  COMMENT '合伙人',
`ceo_openids`  text NOT NULL  COMMENT '负责人',
`ctime`  int(10) NOT NULL  COMMENT '创建时间',
`keyword`  varchar(255) NOT NULL  COMMENT '名称',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`) VALUES ('branch','分公司','0','','1','{"1":["openids","ceo_openids","keyword"]}','1:基础','','','','','id:ID\r\nkeyword:名称\r\nceo_openids:负责人\r\nopenids:合伙人\r\nctime|time_format:创建时间\r\nid:操作:[EDIT]|编辑,[DELETE]|删除','10','','','1450919245','1451032157','1','MyISAM');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('token','Token','varchar(255) NOT NULL','string','','','0','','0','0','1','1450919280','1450919280','','3','','regex','get_token','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('openids','合伙人','text NOT NULL','textarea','','填写openid,一行填写一个openid','1','','0','0','1','1451023971','1450919343','checkPartner','3','合伙人中存在用户还不是合伙人的openid','function','openidLinkNickname','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('ceo_openids','负责人','text NOT NULL','textarea','','填写openid,一行填写一个openid','1','','0','0','1','1450923681','1450919464','','3','','regex','openidLinkNickname','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('ctime','创建时间','int(10) NOT NULL','num','','','0','','0','0','1','1450919561','1450919561','','3','','regex','time','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('keyword','名称','varchar(255) NOT NULL','string','','分公司名称','1','','0','0','1','1451032081','1450919603','','3','','regex','','3','function');
UPDATE `wp_attribute` SET model_id= (SELECT MAX(id) FROM `wp_model`) WHERE model_id=0;