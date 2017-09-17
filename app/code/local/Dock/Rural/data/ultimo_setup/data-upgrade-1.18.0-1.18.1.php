<?php

$installer = $this;
$installer->startSetup();



Mage::getSingleton('rural/cssgen_generator')->generateCss('grid',   NULL, NULL);
Mage::getSingleton('rural/cssgen_generator')->generateCss('layout', NULL, NULL);
Mage::getSingleton('rural/cssgen_generator')->generateCss('design', NULL, NULL);



$version = Mage::getConfig()->getModuleConfig('Dock_Rural')->version;
Mage::log("[Rural " . $version . "] Setup finished.", null, "Dock_Rural.log");



$installer->endSetup();
