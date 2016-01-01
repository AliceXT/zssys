DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='forms_cjt' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='forms_cjt' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_forms_cjt`;
DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='forms_cjt_value' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='forms_cjt_value' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_forms_cjt_value`;
DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='forms_cjt_attribute' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='forms_cjt_attribute' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_forms_cjt_attribute`;