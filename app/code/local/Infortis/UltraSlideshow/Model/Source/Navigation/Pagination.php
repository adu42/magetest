<?php

class Infortis_UltraSlideshow_Model_Source_Navigation_Pagination
{
	public function toOptionArray()
	{
		return array(
			array('value' => '',		'label' => Mage::helper('ultraslideshow')->__('Disabled')),
			array('value' => '1',		'label' => Mage::helper('ultraslideshow')->__('Style 1')),
			array('value' => '2',		'label' => Mage::helper('ultraslideshow')->__('Style 2')),
		);
	}
}
