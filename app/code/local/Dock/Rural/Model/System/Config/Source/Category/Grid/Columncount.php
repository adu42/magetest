<?php

class Dock_Rural_Model_System_Config_Source_Category_Grid_Columncount
{
    public function toOptionArray()
    {
        return array(
			array('value' => 2, 'label' => Mage::helper('dock')->__('2')),
			array('value' => 3, 'label' => Mage::helper('dock')->__('3')),
			array('value' => 4, 'label' => Mage::helper('dock')->__('4')),
			array('value' => 5, 'label' => Mage::helper('dock')->__('5')),
			array('value' => 6, 'label' => Mage::helper('dock')->__('6')),
            array('value' => 7, 'label' => Mage::helper('dock')->__('7')),
            array('value' => 8, 'label' => Mage::helper('dock')->__('8')),
        );
    }
}