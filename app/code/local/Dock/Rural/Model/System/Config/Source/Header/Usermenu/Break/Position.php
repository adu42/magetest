<?php

class Dock_Rural_Model_System_Config_Source_Header_Usermenu_Break_Position
{
    public function toOptionArray()
    {
		return array(
			array('value' => '0',	'label' => Mage::helper('rural')->__('No Additional Line Break')),
			array('value' => '30',	'label' => Mage::helper('rural')->__('Before Cart Drop-Down Block')),
			array('value' => '31',	'label' => Mage::helper('rural')->__('After Cart Drop-Down Block')),
			array('value' => '32',	'label' => Mage::helper('rural')->__('Before Compare Block')),
			array('value' => '33',	'label' => Mage::helper('rural')->__('After Compare Block')),
			array('value' => '34',	'label' => Mage::helper('rural')->__('Before Top Links')),
			array('value' => '35',	'label' => Mage::helper('rural')->__('After Top Links')),
        );
    }
}