DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='shop_user' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='shop_user' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_shop_user`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='shop_settled' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='shop_settled' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp__shop_settled`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='shop_return_order' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='shop_return_order' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_shop_return_order`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='shop_product_dis' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='shop_product_dis' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_shop_product_dis`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='shop_order' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='shop_order' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_shop_order`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='shop_product' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='shop_product' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_shop_product`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='shop_get_cash' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='shop_get_cash' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_shop_get_cash`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='shop_distribute' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='shop_distribute' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_shop_distribute`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='shop_footer' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='shop_footer' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_shop_footer`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='shop_comments' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='shop_comments' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_shop_comments`;