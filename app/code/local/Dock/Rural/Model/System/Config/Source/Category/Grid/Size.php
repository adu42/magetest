<?php

class Dock_Rural_Model_System_Config_Source_Category_Grid_Size
{
	public function toOptionArray()
	{
		return array(
			array('value' => '',	'label' => Mage::helper('dock')->__('Default')),
			array('value' => 's',	'label' => Mage::helper('dock')->__('Size S')),
			array('value' => 'xs',	'label' => Mage::helper('dock')->__('Size XS')),
		);
	}
}