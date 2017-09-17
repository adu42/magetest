<?php

class Dock_Dock_Model_System_Config_Source_Layout_Screen_Width_WideCustom
{
    public function toOptionArray()
    {
		return array(
			array('value' => '768',		'label' => Mage::helper('dock')->__('768 px')),
			array('value' => '992',		'label' => Mage::helper('dock')->__('992 px')),
			array('value' => '1200',	'label' => Mage::helper('dock')->__('1200 px')),
			array('value' => '1440',	'label' => Mage::helper('dock')->__('1440 px')),
			array('value' => '1680',	'label' => Mage::helper('dock')->__('1680 px')),
			array('value' => '1920',	'label' => Mage::helper('dock')->__('1920 px')),
			array('value' => 'full',	'label' => Mage::helper('dock')->__('Full width')),
			array('value' => 'custom',	'label' => Mage::helper('dock')->__('Custom width...'))
        );
    }
}
