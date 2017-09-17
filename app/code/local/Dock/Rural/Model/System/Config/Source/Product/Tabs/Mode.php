<?php

class Dock_Rural_Model_System_Config_Source_Product_Tabs_Mode
{
    public function toOptionArray()
    {
    	//Important: note the order of values - "Tabs" moved to first position
		return array(
			array('value' => 3,		'label' => Mage::helper('rural')->__('Tabs')),
			array('value' => 1,		'label' => Mage::helper('rural')->__('Tabs/Accordion')),
			array('value' => 2,		'label' => Mage::helper('rural')->__('Accordion')),
        );
    }
}