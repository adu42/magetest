<?php

class Dock_Rural_Model_System_Config_Source_Header_Position_PrimaryTopUserMenu
{
    public function toOptionArray()
    {
		return array(
			array('value' => 'topLeft',				'label' => Mage::helper('rural')->__('Top, Left')),
			array('value' => 'topCentral',			'label' => Mage::helper('rural')->__('Top, Central')),
			array('value' => 'topRight',			'label' => Mage::helper('rural')->__('Top, Right')),
			array('value' => 'primLeftCol',			'label' => Mage::helper('rural')->__('Primary, Left Column')),
			array('value' => 'primCentralCol',		'label' => Mage::helper('rural')->__('Primary, Central Column')),
			array('value' => 'primRightCol',		'label' => Mage::helper('rural')->__('Primary, Right Column')),
			array('value' => 'userMenu',			'label' => Mage::helper('rural')->__('Inside User Menu')),
        );
    }
}
