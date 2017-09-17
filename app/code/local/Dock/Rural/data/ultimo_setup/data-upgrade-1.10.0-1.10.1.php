<?php

$installer = $this;
$installer->startSetup();


//Update the field with new default value
$sidePadding = Mage::getStoreConfig('rural_design/page/content_padding_side');
if ($sidePadding)
{
	Mage::getConfig()->saveConfig('rural_design/page/content_padding_side', '12');
}


$installer->endSetup();
