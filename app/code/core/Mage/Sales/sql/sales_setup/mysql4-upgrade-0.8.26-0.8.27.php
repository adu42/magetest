<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Sales_Model_Entity_Setup */

$conn = $installer->getConnection();
/* @var $conn Varien_Db_Adapter_Pdo_Mysql */

$conn->addColumn($installer->getTable('sales_quote'), 'customer_prefix', 'varchar(40) after customer_email');
$conn->addColumn($installer->getTable('sales_quote'), 'customer_middlename', 'varchar(40) after customer_firstname');
$conn->addColumn($installer->getTable('sales_quote'), 'customer_suffix', 'varchar(40) after customer_lastname');

$conn->addColumn($installer->getTable('sales_quote_address'), 'prefix', 'varchar(40) after email');
$conn->addColumn($installer->getTable('sales_quote_address'), 'middlename', 'varchar(40) after firstname');
$conn->addColumn($installer->getTable('sales_quote_address'), 'suffix', 'varchar(40) after lastname');

$installer->addAttribute('order', 'customer_prefix', array('type'=>'varchar', 'visible'=>false));
$installer->addAttribute('order', 'customer_middlename', array('type'=>'varchar', 'visible'=>false));
$installer->addAttribute('order', 'customer_suffix', array('type'=>'varchar', 'visible'=>false));

$installer->addAttribute('order_address', 'prefix', array());
$installer->addAttribute('order_address', 'middlename', array());
$installer->addAttribute('order_address', 'suffix', array());



