<?php

class Dock_Rural_Model_System_Config_Source_Header_Position_All
{
    public function toOptionArray()
    {
    	/**
    	 * @deprecated
    	 */
		return array(
			array('value' => 'primLeftCol',			'label' => Mage::helper('rural')->__('Left Column')),
			array('value' => 'primCentralCol',		'label' => Mage::helper('rural')->__('Central Column')),
			array('value' => 'primRightCol',		'label' => Mage::helper('rural')->__('Right Column')),
			array('value' => 'userMenu',			'label' => Mage::helper('rural')->__('Inside User Menu...')),
        );
    }
}