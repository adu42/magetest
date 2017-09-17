<?php

class Dock_Rural_Model_System_Config_Source_Header_Position_PrimaryMenuContainer
{
    public function toOptionArray()
    {
		return array(
			array('value' => 'menuContainer',		'label' => Mage::helper('rural')->__('Full Width Menu Container')),
			array('value' => 'topCentral',			'label' => Mage::helper('rural')->__('Top, Central')),
			array('value' => 'primLeftCol',			'label' => Mage::helper('rural')->__('Primary, Left Column')),
			array('value' => 'primCentralCol',		'label' => Mage::helper('rural')->__('Primary, Central Column')),
			array('value' => 'primRightCol',		'label' => Mage::helper('rural')->__('Primary, Right Column')),
        );
    }
}
