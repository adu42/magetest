<?php

class Dock_Dock_Model_System_Config_Source_Css_Background_Positionx
{
    public function toOptionArray()
    {
		return array(
			array('value' => 'left',	'label' => Mage::helper('dock')->__('left')),
            array('value' => 'center',	'label' => Mage::helper('dock')->__('center')),
            array('value' => 'right',	'label' => Mage::helper('dock')->__('right'))
        );
    }
}