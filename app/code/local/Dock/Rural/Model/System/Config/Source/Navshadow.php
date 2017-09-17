<?php

class Dock_Rural_Model_System_Config_Source_Navshadow
{
    public function toOptionArray()
    {
        return array(
            array('value' => '',                     'label' => Mage::helper('dock')->__('None')),
			array('value' => 'inner-container',      'label' => Mage::helper('dock')->__('Inner container')),
			array('value' => 'bar',                  'label' => Mage::helper('dock')->__('Menu items')),
        );
    }
}