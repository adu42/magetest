<?php

class Dock_Dock_Model_System_Config_Source_Design_Font_Google_Subset
{
	public function toOptionArray()
	{
		return array(
			array('value' => 'cyrillic',			'label' => Mage::helper('dock')->__('Cyrillic')),
			array('value' => 'cyrillic-ext',		'label' => Mage::helper('dock')->__('Cyrillic Extended')),
			array('value' => 'greek',				'label' => Mage::helper('dock')->__('Greek')),
			array('value' => 'greek-ext',			'label' => Mage::helper('dock')->__('Greek Extended')),
			array('value' => 'khmer',				'label' => Mage::helper('dock')->__('Khmer')),
			array('value' => 'latin',				'label' => Mage::helper('dock')->__('Latin')),
			array('value' => 'latin-ext',			'label' => Mage::helper('dock')->__('Latin Extended')),
			array('value' => 'vietnamese',			'label' => Mage::helper('dock')->__('Vietnamese')),
		);
	}
}