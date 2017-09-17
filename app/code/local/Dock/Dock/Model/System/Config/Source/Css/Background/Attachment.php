<?php

class Dock_Dock_Model_System_Config_Source_Css_Background_Attachment
{
    public function toOptionArray()
    {
		return array(
			array('value' => 'fixed',	'label' => Mage::helper('dock')->__('fixed')),
            array('value' => 'scroll',	'label' => Mage::helper('dock')->__('scroll'))
        );
    }
}