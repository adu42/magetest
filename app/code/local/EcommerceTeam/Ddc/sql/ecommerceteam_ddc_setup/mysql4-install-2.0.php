<?php
	/*
	* Magento Delivery Date & Customer Comment Extension
	*
	* @copyright:	EcommerceTeam (http://www.ecommerce-team.com)
	* @version:	2.0
	*
	*/

$installer = $this;
$installer->startSetup();

/*
$installer->run(
"CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales_ddc_order')}` (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `delivery_fee_label`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
  `delivery_fee_amount` decimal(8,2) NOT NULL,
  `base_delivery_fee_amount` decimal(8,2) NOT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `customer_comment` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`entity_id`,`order_id`),
  KEY `FK_sales_ddc_order` (`order_id`),
  CONSTRAINT `FK_sales_ddc_order` FOREIGN KEY (`order_id`) REFERENCES `{$installer->getTable('sales_flat_order')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
");
*/

$installer->run(
"CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales_ddc_quote')}` (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quote_id` int(10) unsigned NOT NULL,
  `delivery_fee_label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `delivery_fee_amount` decimal(8,2) NOT NULL,
  `base_delivery_fee_amount` decimal(8,2) NOT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `customer_comment` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`entity_id`,`quote_id`),
  KEY `FK_sales_ddc_quote` (`quote_id`),
  CONSTRAINT `FK_sales_ddc_quote` FOREIGN KEY (`quote_id`) REFERENCES `{$installer->getTable('sales_flat_quote')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
");

$installer->run("
    ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `delivery_fee_label`  VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
    ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `delivery_fee_amount` DECIMAL( 10, 2 ) NULL;
    ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `base_delivery_fee_amount` DECIMAL( 10, 2 ) NULL;
    ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `delivery_date` datetime DEFAULT NULL;
");


$installer->run("
    ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `delivery_fee_label`  VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
    ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `delivery_fee_amount` DECIMAL( 10, 2 ) NULL;
    ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `base_delivery_fee_amount` DECIMAL( 10, 2 ) NULL;
    ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `delivery_date` datetime DEFAULT NULL;
");

$installer->run("
    ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `delivery_fee_amount_invoiced` DECIMAL( 10, 2 ) NULL;
    ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `base_delivery_fee_amount_invoiced` DECIMAL( 10, 2 ) NULL;
");

$installer->run("
    ALTER TABLE  `".$this->getTable('sales/invoice')."` ADD  `delivery_fee_label`  VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
    ALTER TABLE  `".$this->getTable('sales/invoice')."` ADD  `delivery_fee_amount` DECIMAL( 10, 2 ) NULL;
    ALTER TABLE  `".$this->getTable('sales/invoice')."` ADD  `base_delivery_fee_amount` DECIMAL( 10, 2 ) NULL;
");

$installer->run("
    ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `delivery_fee_amount_refunded` DECIMAL( 10, 2 ) NULL;
    ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `base_delivery_fee_amount_refunded` DECIMAL( 10, 2 ) NULL;
    ALTER TABLE  `".$this->getTable('sales/creditmemo')."` ADD  `delivery_fee_label`  VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
    ALTER TABLE  `".$this->getTable('sales/creditmemo')."` ADD  `delivery_fee_amount` DECIMAL( 10, 2 ) NULL;
    ALTER TABLE  `".$this->getTable('sales/creditmemo')."` ADD  `base_delivery_fee_amount` DECIMAL( 10, 2 ) NULL;
");
$installer->run("
    ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `is_mobile` tinyint(3) NULL;
    ALTER TABLE  `".$this->getTable('sales/quote')."` ADD  `is_mobile` tinyint(3) NULL;
");
$installer->endSetup();