DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='hccard' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='hccard' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_hccard`;
DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='hccard_message' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='hccard_message' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_hccard_message`;
DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='hccard_link' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='hccard_link' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_hccard_link`;
DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='hccard_company' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='hccard_company' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_hccard_company`;