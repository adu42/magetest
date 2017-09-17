<?php

class Dock_Dock_Model_System_Config_Source_Design_Icon_Color_Bw
{
    public function toOptionArray()
    {
		return array(
			array('value' => 'b',		'label' => Mage::helper('dock')->__('Black')),
            array('value' => 'w',		'label' => Mage::helper('dock')->__('White'))
        );
    }
}