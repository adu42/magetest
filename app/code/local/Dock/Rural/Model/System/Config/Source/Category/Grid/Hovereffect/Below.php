<?php

class Dock_Rural_Model_System_Config_Source_Category_Grid_Hovereffect_Below
{
    public function toOptionArray()
    {
        return array(
            array('value' => '',     'label' => Mage::helper('dock')->__('')),
			array('value' => '640',  'label' => Mage::helper('dock')->__('640 px')),
			array('value' => '480',  'label' => Mage::helper('dock')->__('480 px')),
			array('value' => '320',  'label' => Mage::helper('dock')->__('320 px')),
        );
    }
}