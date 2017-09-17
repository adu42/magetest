<?php

class Dock_Dock_Model_System_Config_Source_Css_Background_Positiony
{
    public function toOptionArray()
    {
		return array(
			array('value' => 'top',		'label' => Mage::helper('dock')->__('top')),
            array('value' => 'center',	'label' => Mage::helper('dock')->__('center')),
            array('value' => 'bottom',	'label' => Mage::helper('dock')->__('bottom'))
        );
    }
}