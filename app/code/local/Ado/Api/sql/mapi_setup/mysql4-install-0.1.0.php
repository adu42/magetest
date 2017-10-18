<?php
$installer = $this;
$installer->startSetup();
$sql="
DROP TABLE IF EXISTS {$this->getTable('slide')};
CREATE TABLE {$this->getTable('slide')} (
  `slide_id` int(11) auto_increment,
  `identifier` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `show_title` smallint(6) NOT NULL default 0,
  `auto_play` smallint(6) NOT NULL default 2,
  `content` text NULL default '',
  `width` int(11) unsigned NULL,
  `height` int(11) unsigned NULL,
  `delay` int(11) unsigned NULL,
  `status` smallint(6) NOT NULL default 0,
  `active_from` datetime NULL,
  `active_to` datetime NULL,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('slide_item')};
CREATE TABLE {$this->getTable('slide_item')} (
  `slide_item_id` int(11) auto_increment,
  `slide_id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `image_url` varchar(512) NOT NULL default '',
  `thumb_image` varchar(255) NOT NULL default '',
  `thumb_image_url` varchar(512) NOT NULL default '',
  `content` text NULL default '',
  `banner_order` int(11) default 0,
  `link_url` varchar(512) NOT NULL default '#',
  `status` smallint(6) NOT NULL default 0,
  `item_active_from` datetime NULL,
  `item_active_to` datetime NULL,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`banner_item_id`),
  CONSTRAINT `FK_ADO_BANNER_ITEM` FOREIGN KEY (`slide_id`) REFERENCES `{$this->getTable('slide')}` (`slide_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
 "


;
try{
    $installer->run($sql);
}catch(Exception $e){
    Mage::logException($e);
   // @file_put_contents(dirname(__FILE__).'/aa.txt',print_r($e,true),FILE_APPEND);
}
$installer->endSetup();
/*
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute("customer", "rest_hmac_secret_key",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "HMAC",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => "Custom rest_hmac_secret_key"

));

$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "rest_hmac_secret_key");


$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'rest_hmac_secret_key',
    '999'  //sort_order
);

$used_in_forms=array();

$used_in_forms[]="adminhtml_customer";
//$used_in_forms[]="checkout_register";
//$used_in_forms[]="customer_account_create";
//$used_in_forms[]="customer_account_edit";
//$used_in_forms[]="adminhtml_checkout";
$attribute->setData("used_in_forms", $used_in_forms)
    ->setData("is_used_for_customer_segment", true)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 1)
    ->setData("sort_order", 100)
;
$attribute->save();



$installer->endSetup();
*/